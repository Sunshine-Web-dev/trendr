<?php
/**
 * trendr Member Sign-up
 *
 * Functions and filters specific to the member sign-up process
 *
 * @package trendr
 * @sutrsackage Members
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function trs_core_screen_signup() {
	global $trs, $trmdb;

	if ( !trs_is_current_component( 'register' ) )
		return;

	// Not a directory
	trs_update_is_directory( false, 'register' );

	// If the user is logged in, redirect away from here
	if ( is_user_logged_in() ) {
		if ( trs_is_component_front_page( 'register' ) )
			$redirect_to = trs_get_root_domain() . '/' . trs_get_members_root_slug();
		else
			$redirect_to = trs_get_root_domain();

		trs_core_redirect( apply_filters( 'trs_loggedin_register_page_redirect_to', $redirect_to ) );

		return;
	}

	$trs->signup->step = 'request-details';

 	if ( !trs_get_signup_allowed() ) {
		$trs->signup->step = 'registration-disabled';
	}

	// If the signup page is submitted, validate and save
	elseif ( isset( $_POST['signup_submit'] ) ) {

		// Check the nonce
		check_admin_referer( 'trs_new_signup' );

		// Check the base account details for problems
		$account_details = trs_core_validate_user_signup( $_POST['signup_username'], $_POST['signup_email'] );

		// If there are errors with account details, set them for display
		if ( !empty( $account_details['errors']->errors['user_name'] ) )
			$trs->signup->errors['signup_username'] = $account_details['errors']->errors['user_name'][0];

		if ( !empty( $account_details['errors']->errors['user_email'] ) )
			$trs->signup->errors['signup_email'] = $account_details['errors']->errors['user_email'][0];

		// Check that both password fields are filled in
		if ( empty( $_POST['signup_password'] ) || empty( $_POST['signup_password_confirm'] ) )
			$trs->signup->errors['signup_password'] = __( 'Please make sure you enter your password twice', 'trendr' );

		// Check that the passwords match
		if ( ( !empty( $_POST['signup_password'] ) && !empty( $_POST['signup_password_confirm'] ) ) && $_POST['signup_password'] != $_POST['signup_password_confirm'] )
			$trs->signup->errors['signup_password'] = __( 'The passwords you entered do not match.', 'trendr' );

		$trs->signup->username = $_POST['signup_username'];
		$trs->signup->email = $_POST['signup_email'];

		// Now we've checked account details, we can check profile information
		if ( trs_is_active( 'xprofile' ) ) {

			// Make sure hidden field is passed and populated
			if ( isset( $_POST['signup_profile_field_ids'] ) && !empty( $_POST['signup_profile_field_ids'] ) ) {

				// Let's compact any profile field info into an array
				$profile_field_ids = explode( ',', $_POST['signup_profile_field_ids'] );

				// Loop through the posted fields formatting any datebox values then validate the field
				foreach ( (array) $profile_field_ids as $field_id ) {
					if ( !isset( $_POST['field_' . $field_id] ) ) {
						if ( !empty( $_POST['field_' . $field_id . '_day'] ) && !empty( $_POST['field_' . $field_id . '_month'] ) && !empty( $_POST['field_' . $field_id . '_year'] ) )
							$_POST['field_' . $field_id] = date( 'Y-m-d H:i:s', strtotime( $_POST['field_' . $field_id . '_day'] . $_POST['field_' . $field_id . '_month'] . $_POST['field_' . $field_id . '_year'] ) );
					}

					// Create errors for required fields without values
					//8/19/18

                   //Removed required field error generator for signups// now we can sign up
					//if ( xprofile_check_is_required_field( $field_id ) && empty( $_POST['field_' . $field_id] ) )
						//$trs->signup->errors['field_' . $field_id] = __( 'This is a required field', 'trendr' );
				}

			// This situation doesn't naturally occur so bounce to website root
			} else {
				trs_core_redirect( trs_get_root_domain() );
			}
		}

		// Finally, let's check the blog details, if the user wants a blog and blog creation is enabled
		if ( isset( $_POST['signup_with_blog'] ) ) {
			$active_signup = $trs->site_options['registration'];

			if ( 'blog' == $active_signup || 'all' == $active_signup ) {
				$blog_details = trs_core_validate_blog_signup( $_POST['signup_blog_url'], $_POST['signup_blog_title'] );

				// If there are errors with blog details, set them for display
				if ( !empty( $blog_details['errors']->errors['blogname'] ) )
					$trs->signup->errors['signup_blog_url'] = $blog_details['errors']->errors['blogname'][0];

				if ( !empty( $blog_details['errors']->errors['blog_title'] ) )
					$trs->signup->errors['signup_blog_title'] = $blog_details['errors']->errors['blog_title'][0];
			}
		}

		do_action( 'trs_signup_validate' );

		// Add any errors to the action for the field in the template for display.
		if ( !empty( $trs->signup->errors ) ) {
			foreach ( (array)$trs->signup->errors as $fieldname => $error_message )
				add_action( 'trs_' . $fieldname . '_errors', create_function( '', 'echo apply_filters(\'trs_members_signup_error_message\', "<div class=\"error\">' . $error_message . '</div>" );' ) );
		} else {
			$trs->signup->step = 'save-details';

			// No errors! Let's register those deets.
			$active_signup = !empty( $trs->site_options['registration'] ) ? $trs->site_options['registration'] : '';

			if ( 'none' != $active_signup ) {

				// Let's compact any profile field info into usermeta
				$profile_field_ids = explode( ',', $_POST['signup_profile_field_ids'] );

				// Loop through the posted fields formatting any datebox values then add to usermeta
				foreach ( (array) $profile_field_ids as $field_id ) {
					if ( !isset( $_POST['field_' . $field_id] ) ) {
						if ( isset( $_POST['field_' . $field_id . '_day'] ) )
							$_POST['field_' . $field_id] = date( 'Y-m-d H:i:s', strtotime( $_POST['field_' . $field_id . '_day'] . $_POST['field_' . $field_id . '_month'] . $_POST['field_' . $field_id . '_year'] ) );
					}

					if ( !empty( $_POST['field_' . $field_id] ) )
						$usermeta['field_' . $field_id] = $_POST['field_' . $field_id];
				}

				// Store the profile field ID's in usermeta
				$usermeta['profile_field_ids'] = $_POST['signup_profile_field_ids'];

				// Hash and store the password
				$usermeta['password'] = trm_hash_password( $_POST['signup_password'] );

				// If the user decided to create a blog, save those details to usermeta
				if ( 'blog' == $active_signup || 'all' == $active_signup )
					$usermeta['public'] = ( isset( $_POST['signup_blog_privacy'] ) && 'public' == $_POST['signup_blog_privacy'] ) ? true : false;

				$usermeta = apply_filters( 'trs_signup_usermeta', $usermeta );

				// Finally, sign up the user and/or blog
				if ( isset( $_POST['signup_with_blog'] ) && is_multisite() )
					trs_core_signup_blog( $blog_details['domain'], $blog_details['path'], $blog_details['blog_title'], $_POST['signup_username'], $_POST['signup_email'], $usermeta );
				else
					trs_core_signup_user( $_POST['signup_username'], $_POST['signup_password'], $_POST['signup_email'], $usermeta );

				$trs->signup->step = 'completed-confirmation';
			}

			do_action( 'trs_complete_signup' );
		}

	}

	do_action( 'trs_core_screen_signup' );
	trs_core_load_template( apply_filters( 'trs_core_template_register', 'registration/register' ) );
}
add_action( 'trs_screens', 'trs_core_screen_signup' );

function trs_core_screen_activation() {
	global $trs, $trmdb;

	if ( !trs_is_current_component( 'activate' ) )
		return false;

	// Check if an activation key has been passed
	if ( isset( $_GET['key'] ) ) {

		// Activate the signup
		$user = apply_filters( 'trs_core_activate_account', trs_core_activate_signup( $_GET['key'] ) );

		// If there were errors, add a message and redirect
		if ( !empty( $user->errors ) ) {
			trs_core_add_message( $user->get_error_message(), 'error' );
			trs_core_redirect( trailingslashit( trs_get_root_domain() . '/' . $trs->pages->activate->slug ) );
		}

		// Check for an uploaded portrait and move that to the correct user folder
		if ( is_multisite() )
			$hashed_key = trm_hash( $_GET['key'] );
		else
			$hashed_key = trm_hash( $user );

		// Check if the portrait folder exists. If it does, move rename it, move
		// it and delete the signup portrait dir
		if ( file_exists( trs_core_portrait_upload_path() . '/portraits/signups/' . $hashed_key ) )
			@rename( trs_core_portrait_upload_path() . '/portraits/signups/' . $hashed_key, trs_core_portrait_upload_path() . '/portraits/' . $user );

		trs_core_add_message( __( 'Your account is now active!', 'trendr' ) );

		$trs->activation_complete = true;
	}

	if ( '' != locate_template( array( 'registration/activate' ), false ) )
		trs_core_load_template( apply_filters( 'trs_core_template_activate', 'activate' ) );
	else
		trs_core_load_template( apply_filters( 'trs_core_template_activate', 'registration/activate' ) );
}
add_action( 'trs_screens', 'trs_core_screen_activation' );


/********************************************************************************
 * Business Functions
 *
 * Business functions are where all the magic happens in trendr. They will
 * handle the actual saving or manipulation of information. Usually they will
 * hand off to a database class for data access, then return
 * true or false on success or failure.
 */

/**
 * Flush illegal names by getting and setting 'illegal_names' site option
 */
function trs_core_flush_illegal_names() {
	$illegal_names = get_site_option( 'illegal_names' );
	update_site_option( 'illegal_names', $illegal_names );
}

/**
 * Filter the illegal_names site option and make sure it includes a few
 * specific trendr and Multi-site slugs
 *
 * @param array|string $value Illegal names from field
 * @param array|string $oldvalue The value as it is currently
 * @return array Merged and unique array of illegal names
 */
function trs_core_get_illegal_names( $value = '', $oldvalue = '' ) {

	// Make sure $value is array
	if ( empty( $value ) )
		$db_illegal_names = array();
	if ( is_array( $value ) )
		$db_illegal_names = $value;
	elseif ( is_string( $value ) )
		$db_illegal_names = explode( ' ', $value );

	// Add the core components' slugs to the banned list even if their components aren't active.
	$trs_component_slugs = array(
		'groups',
		'members',
		'forums',
		'blogs',
		'activity',
		'profile',
		'friends',
		'search',
		'settings',
		'register',
		'activate'
	);

	// Core constants
	$slug_constants = array(
		'TRS_GROUPS_SLUG',
		'TRS_MEMBERS_SLUG',
		'TRS_FORUMS_SLUG',
		'TRS_BLOGS_SLUG',
		'TRS_ACTIVITY_SLUG',
		'TRS_XPROFILE_SLUG',
		'TRS_FRIENDS_SLUG',
		'TRS_SEARCH_SLUG',
		'TRS_SETTINGS_SLUG',
		'TRS_REGISTER_SLUG',
		'TRS_ACTIVATION_SLUG',
	);
	foreach( $slug_constants as $constant )
		if ( defined( $constant ) )
			$trs_component_slugs[] = constant( $constant );

	// Add our slugs to the array and allow them to be filtered
	$filtered_illegal_names = apply_filters( 'trs_core_illegal_usernames', array_merge( array( 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator' ), $trs_component_slugs ) );

	// Merge the arrays together
	$merged_names           = array_merge( (array)$filtered_illegal_names, (array)$db_illegal_names );

	// Remove duplicates
	$illegal_names          = array_unique( (array)$merged_names );

	return apply_filters( 'trs_core_illegal_names', $illegal_names );
}
add_filter( 'pre_update_site_option_illegal_names', 'trs_core_get_illegal_names', 10, 2 );

/**
 * Validate a user name and email address when creating a new user.
 *
 * @global object $trmdb DB Layer
 * @param string $user_name Username to validate
 * @param string $user_email Email address to validate
 * @return array Results of user validation including errors, if any
 */
function trs_core_validate_user_signup( $user_name, $user_email ) {
	global $trmdb;

	$errors = new TRM_Error();
	$user_email = sanitize_email( $user_email );

	if ( empty( $user_name ) )
		$errors->add( 'user_name', __( 'Please enter a username', 'trendr' ) );

	$maybe = array();
	//preg_match( "/[a-z0-9]+/", $user_name, $maybe );
    //ALLOW CAPITAL LETTERS 8-181-18
    preg_match( "/[A-Za-z0-9]+/", $user_name, $maybe );
	// Make sure illegal names include trendr slugs and values
	trs_core_flush_illegal_names();

	$illegal_names = get_site_option( 'illegal_names' );

	if ( !validate_username( $user_name ) || in_array( $user_name, (array)$illegal_names ) || ( !empty( $maybe[0] ) && $user_name != $maybe[0] ) )
		$errors->add( 'user_name', __( 'Only lowercase letters and numbers allowed', 'trendr' ) );

	if( strlen( $user_name ) < 4 )
		$errors->add( 'user_name',  __( 'Username must be at least 4 characters', 'trendr' ) );

	if ( strpos( ' ' . $user_name, '_' ) != false )
		$errors->add( 'user_name', __( 'Sorry, usernames may not contain the character "_"!', 'trendr' ) );

	// Is the user_name all numeric?
	$match = array();
	preg_match( '/[0-9]*/', $user_name, $match );

	if ( $match[0] == $user_name )
		$errors->add( 'user_name', __( 'Sorry, usernames must have letters too!', 'trendr' ) );

	if ( !is_email( $user_email ) )
		$errors->add( 'user_email', __( 'Please check your email address.', 'trendr' ) );

	if ( function_exists( 'is_email_address_unsafe' ) && is_email_address_unsafe( $user_email ) )
		$errors->add( 'user_email',  __( 'Sorry, that email address is not allowed!', 'trendr' ) );

	$limited_email_domains = get_site_option( 'limited_email_domains', 'trendr' );

	if ( is_array( $limited_email_domains ) && empty( $limited_email_domains ) == false ) {
		$emaildomain = substr( $user_email, 1 + strpos( $user_email, '@' ) );

		if ( in_array( $emaildomain, (array)$limited_email_domains ) == false )
			$errors->add( 'user_email', __( 'Sorry, that email address is not allowed!', 'trendr' ) );
	}

	// Check if the username has been used already.
	if ( username_exists( $user_name ) )
		$errors->add( 'user_name', __( 'Sorry, that username already exists!', 'trendr' ) );

	// Check if the email address has been used already.
	if ( email_exists( $user_email ) )
		$errors->add( 'user_email', __( 'Sorry, that email address is already used!', 'trendr' ) );

	$result = array( 'user_name' => $user_name, 'user_email' => $user_email, 'errors' => $errors );

	// Apply TRMMU legacy filter
	$result = apply_filters( 'trmmu_validate_user_signup', $result );

 	return apply_filters( 'trs_core_validate_user_signup', $result );
}

function trs_core_validate_blog_signup( $blog_url, $blog_title ) {
	if ( !is_multisite() || !function_exists( 'trmmu_validate_blog_signup' ) )
		return false;

	return apply_filters( 'trs_core_validate_blog_signup', trmmu_validate_blog_signup( $blog_url, $blog_title ) );
}

function trs_core_signup_user( $user_login, $user_password, $user_email, $usermeta ) {
	global $trs, $trmdb;

	// Multisite installs have their own install procedure
	if ( is_multisite() ) {
		trmmu_signup_user( $user_login, $user_email, $usermeta );

		// On multisite, the user id is not created until the user activates the account
		// but we need to cast $user_id to pass to the filters
		$user_id = false;

	} else {
		$errors = new TRM_Error();

		$user_id = trm_insert_user( array(
			'user_login' => $user_login,
			'user_pass' => $user_password,
			'display_name' => sanitize_title( $user_login ),
			'user_email' => $user_email
		) );

		if ( empty( $user_id ) ) {
			$errors->add( 'registerfail', sprintf( __('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !', 'trendr' ), get_option( 'admin_email' ) ) );
			return $errors;
		}

		// Update the user status to '2' which we will use as 'not activated' (0 = active, 1 = spam, 2 = not active)
		$trmdb->query( $trmdb->prepare( "UPDATE $trmdb->users SET user_status = 2 WHERE ID = %d", $user_id ) );

		// Set any profile data
		if ( trs_is_active( 'xprofile' ) ) {
			if ( !empty( $usermeta['profile_field_ids'] ) ) {
				$profile_field_ids = explode( ',', $usermeta['profile_field_ids'] );

				foreach( (array)$profile_field_ids as $field_id ) {
					if ( empty( $usermeta["field_{$field_id}"] ) )
						continue;

					$current_field = $usermeta["field_{$field_id}"];
					xprofile_set_field_data( $field_id, $user_id, $current_field );
				}
			}
		}
	}
	$trs->signup->username = $user_login;

	/***
	 * Now generate an activation key and send an email to the user so they can activate their account
	 * and validate their email address. Multisite installs send their own email, so this is only for single blog installs.
	 *
	 * To disable sending activation emails you can user the filter 'trs_core_signup_send_activation_key' and return false.
	 */
	if ( apply_filters( 'trs_core_signup_send_activation_key', true ) ) {
		if ( !is_multisite() ) {
			$activation_key = trm_hash( $user_id );
			update_user_meta( $user_id, 'activation_key', $activation_key );
			trs_core_signup_send_validation_email( $user_id, $user_email, $activation_key );
		}
	}

	do_action( 'trs_core_signup_user', $user_id, $user_login, $user_password, $user_email, $usermeta );

	return $user_id;
}

function trs_core_signup_blog( $blog_domain, $blog_path, $blog_title, $user_name, $user_email, $usermeta ) {
	if ( !is_multisite() || !function_exists( 'trmmu_signup_blog' ) )
		return false;

	return apply_filters( 'trs_core_signup_blog', trmmu_signup_blog( $blog_domain, $blog_path, $blog_title, $user_name, $user_email, $usermeta ) );
}

function trs_core_activate_signup( $key ) {
	global $trs, $trmdb;

	$user = false;

	// Multisite installs have their own activation routine
	if ( is_multisite() ) {
		$user = trmmu_activate_signup( $key );

		// If there were errors, add a message and redirect
		if ( !empty( $user->errors ) ) {
			return $user;
		}

		$user_id = $user['user_id'];

		// Set any profile data
		if ( trs_is_active( 'xprofile' ) ) {
			if ( !empty( $user['meta']['profile_field_ids'] ) ) {
				$profile_field_ids = explode( ',', $user['meta']['profile_field_ids'] );

				foreach( (array)$profile_field_ids as $field_id ) {
					$current_field = isset( $user['meta']["field_{$field_id}"] ) ? $user['meta']["field_{$field_id}"] : false;

					if ( !empty( $current_field ) )
						xprofile_set_field_data( $field_id, $user_id, $current_field );
				}
			}
		}

	} else {
		// Get the user_id based on the $key
		$user_id = $trmdb->get_var( $trmdb->prepare( "SELECT user_id FROM $trmdb->usermeta WHERE meta_key = 'activation_key' AND meta_value = %s", $key ) );

		if ( empty( $user_id ) )
			return new TRM_Error( 'invalid_key', __( 'Invalid activation key', 'trendr' ) );

		// Change the user's status so they become active
		if ( !$trmdb->query( $trmdb->prepare( "UPDATE $trmdb->users SET user_status = 0 WHERE ID = %d", $user_id ) ) )
			return new TRM_Error( 'invalid_key', __( 'Invalid activation key', 'trendr' ) );

		// Notify the site admin of a new user registration
		trm_new_user_notification( $user_id );

		// Remove the activation key meta
		delete_user_meta( $user_id, 'activation_key' );
	}

	// Update the display_name
	trm_update_user( array( 'ID' => $user_id, 'display_name' => trs_core_get_user_displayname( $user_id ) ) );

	// Set the password on multisite installs
	if ( is_multisite() && !empty( $user['meta']['password'] ) )
		$trmdb->query( $trmdb->prepare( "UPDATE $trmdb->users SET user_pass = %s WHERE ID = %d", $user['meta']['password'], $user_id ) );

	// Delete the total member cache
	trm_cache_delete( 'trs_total_member_count', 'trs' );

	do_action( 'trs_core_activated_user', $user_id, $key, $user );

	return $user_id;
}

//function trs_core_new_user_activity( $user ) {
	//if ( empty( $user ) || !trs_is_active( 'activity' ) )
	//	return false;

	//if ( is_array( $user ) )
	//	$user_id = $user['user_id'];
	//else
		//$user_id = $user;

	//if ( empty( $user_id ) )
		//return false;

	//$userlink = trs_core_get_userlink( $user_id );

	//trs_activity_add( array(
		//'user_id'   => $user_id,
		//'action'    => apply_filters( 'trs_core_activity_registered_member_action', sprintf( __( '%s became a registered member', 'trendr' ), $userlink ), $user_id ),
		//'component' => 'xprofile',
		//'type'      => 'new_member'
	//) );
//}
//add_action( 'trs_core_activated_user', 'trs_core_new_user_activity' );

function trs_core_map_user_registration( $user_id ) {
	// Only map data when the site admin is adding users, not on registration.
	if ( !is_admin() )
		return false;

	// Add the user's fullname to Xprofile
	if ( trs_is_active( 'xprofile' ) ) {
		$firstname = get_user_meta( $user_id, 'first_name', true );
		$lastname = ' ' . get_user_meta( $user_id, 'last_name', true );
		$name = $firstname . $lastname;

		if ( empty( $name ) || ' ' == $name )
			$name = get_user_meta( $user_id, 'nickname', true );

		xprofile_set_field_data( 1, $user_id, $name );
	}
}
add_action( 'user_register', 'trs_core_map_user_registration' );

function trs_core_signup_portrait_upload_dir() {
	global $trs;

	if ( !$trs->signup->portrait_dir )
		return false;

	$path  = trs_core_portrait_upload_path() . '/portraits/signups/' . $trs->signup->portrait_dir;
	$newbdir = $path;

	if ( !file_exists( $path ) )
		@trm_mkdir_p( $path );

	$newurl = trs_core_portrait_url() . '/portraits/signups/' . $trs->signup->portrait_dir;
	$newburl = $newurl;
	$newsubdir = '/portraits/signups/' . $trs->signup->portrait_dir;

	return apply_filters( 'trs_core_signup_portrait_upload_dir', array( 'path' => $path, 'url' => $newurl, 'subdir' => $newsubdir, 'basedir' => $newbdir, 'baseurl' => $newburl, 'error' => false ) );
}

function trs_core_signup_send_validation_email( $user_id, $user_email, $key ) {
	$activate_url = trs_get_activation_page() ."?key=$key";
	$activate_url = esc_url( $activate_url );

	$from_name = ( '' == get_option( 'blogname' ) ) ? __( 'trendr', 'trendr' ) : esc_html( get_option( 'blogname' ) );

	$message = sprintf( __( "Thanks for registering! To complete the activation of your account please click the following link:\n\n%s\n\n", 'trendr' ), $activate_url );
	$subject = '[' . $from_name . '] ' . __( 'Activate Your Account', 'trendr' );

	// Send the message
	$to      = apply_filters( 'trs_core_signup_send_validation_email_to',     $user_email, $user_id                );
	$subject = apply_filters( 'trs_core_signup_send_validation_email_subject', $subject,    $user_id                );
	$message = apply_filters( 'trs_core_signup_send_validation_email_message', $message,    $user_id, $activate_url );

	trm_mail( $to, $subject, $message );

	do_action( 'trs_core_sent_user_validation_email', $subject, $message, $user_id, $user_email, $key );
}

// Stop user accounts logging in that have not been activated (user_status = 2)
function trs_core_signup_disable_inactive( $auth_obj, $username ) {
	global $trs, $trmdb;

	if ( !$user_id = trs_core_get_userid( $username ) )
		return $auth_obj;

	$user_status = (int) $trmdb->get_var( $trmdb->prepare( "SELECT user_status FROM $trmdb->users WHERE ID = %d", $user_id ) );

	if ( 2 == $user_status )
		return new TRM_Error( 'trs_account_not_activated', __( '<strong>ERROR</strong>: Your account has not been activated. Check your email for the activation link.', 'trendr' ) );
	else
		return $auth_obj;
}
add_filter( 'authenticate', 'trs_core_signup_disable_inactive', 30, 2 );

// Kill the trm-signup.php if custom registration signup templates are present
function trs_core_trmsignup_redirect() {
	$action = !empty( $_GET['action'] ) ? $_GET['action'] : '';

	// Not at the TRM core signup page and action is not register
	if ( false === strpos( $_SERVER['SCRIPT_NAME'], 'trm-signup.php' ) && ( 'register' != $action ) )
		return;

	// Redirect to sign-up page
	if ( locate_template( array( 'registration/register.php' ), false ) || locate_template( array( 'register.php' ), false ) )
		trs_core_redirect( trs_get_signup_page() );
}
add_action( 'trs_init', 'trs_core_trmsignup_redirect' );
?>