<?php
/**
 * trendr Member Functions
 *
 * Functions specific to the members component.
 *
 * @package trendr
 * @sutrsackage Members
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Checks $trs pages global and looks for directory page
 *
 * @since 1.5
 *
 * @global object $trs Global trendr settings object
 * @return bool True if set, False if empty
 */
function trs_members_has_directory() {
	global $trs;

	return (bool) !empty( $trs->pages->members->id );
}

/**
 * Define the slugs used for trendr pages, based on the slugs of the TRM pages used.
 * These can be overridden manually by defining these slugs in trm-setup.php.
 *
 * The fallback values are only used during initial TRS page creation, when no slugs have been
 * explicitly defined.
 *
 * @package trendr Core Core
 * @global object $trs Global trendr settings object
 */
function trs_core_define_slugs() {
	global $trs;

	// No custom members slug
	if ( !defined( 'TRS_MEMBERS_SLUG' ) )
		if ( !empty( $trs->pages->members ) )
			define( 'TRS_MEMBERS_SLUG', $trs->pages->members->slug );
		else
			define( 'TRS_MEMBERS_SLUG', 'members' );

	// No custom registration slug
	if ( !defined( 'TRS_REGISTER_SLUG' ) )
		if ( !empty( $trs->pages->register ) )
			define( 'TRS_REGISTER_SLUG', $trs->pages->register->slug );
		else
			define( 'TRS_REGISTER_SLUG', 'register' );

	// No custom activation slug
	if ( !defined( 'TRS_ACTIVATION_SLUG' ) )
		if ( !empty( $trs->pages->activate ) )
			define( 'TRS_ACTIVATION_SLUG', $trs->pages->activate->slug );
		else
			define( 'TRS_ACTIVATION_SLUG', 'activate' );

}
add_action( 'trs_setup_globals', 'trs_core_define_slugs' );

/**
 * Return an array of users IDs based on the parameters passed.
 *
 * @package trendr Core
 */
function trs_core_get_users( $args = '' ) {
	global $trs;

	$defaults = array(
		'type'            => 'active', // active, newest, alphabetical, random or popular
		'user_id'         => false,    // Pass a user_id to limit to only friend connections for this user
		'exclude'         => false,    // Users to exclude from results
		'search_terms'    => false,    // Limit to users that match these search terms
		'meta_key'        => false,    // Limit to users who have this piece of usermeta
		'meta_value'      => false,    // With meta_key, limit to users where usermeta matches this value

		'include'         => false,    // Pass comma separated list of user_ids to limit to only these users
		'per_page'        => 20,       // The number of results to return per page
		'page'            => 1,        // The page to return if limiting per page
		'populate_extras' => true,     // Fetch the last active, where the user is a friend, total friend count, latest update
	);

	$params = trm_parse_args( $args, $defaults );
	extract( $params, EXTR_SKIP );

	return apply_filters( 'trs_core_get_users', TRS_Core_User::get_users( $type, $per_page, $page, $user_id, $include, $search_terms, $populate_extras, $exclude, $meta_key, $meta_value ), $params );
}

/**
 * Returns the domain for the passed user: e.g. http://domain.com/members/andy/
 *
 * @package trendr Core
 * @global $current_user trendr global variable containing current logged in user information
 * @param user_id The ID of the user.
 */
function trs_core_get_user_domain( $user_id, $user_nicename = false, $user_login = false ) {
	global $trs;

	if ( empty( $user_id ) )
		return;

	if ( !$domain = trm_cache_get( 'trs_user_domain_' . $user_id, 'trs' ) ) {
		$username = trs_core_get_username( $user_id, $user_nicename, $user_login );

		if ( trs_is_username_compatibility_mode() )
			$username = rawurlencode( $username );

		// If we are using a members slug, include it.
		if ( !defined( 'TRS_ENABLE_ROOT_PROFILES' ) )
			$domain = trs_get_root_domain() . '/' . trs_get_members_root_slug() . '/' . $username;
		else
			$domain = trs_get_root_domain() . '/' . $username;

		// Add a slash at the end, and filter before caching
		$domain = apply_filters( 'trs_core_get_user_domain_pre_cache', trailingslashit( $domain ), $user_id, $user_nicename, $user_login );

		// Cache the link
		if ( !empty( $domain ) )
			trm_cache_set( 'trs_user_domain_' . $user_id, $domain, 'trs' );
	}

	return apply_filters( 'trs_core_get_user_domain', $domain, $user_id, $user_nicename, $user_login );
}

/**
 * Fetch everything in the trm_users table for a user, without any usermeta.
 *
 * @package trendr Core
 * @param user_id The ID of the user.
 * @uses TRS_Core_User::get_core_userdata() Performs the query.
 */
function trs_core_get_core_userdata( $user_id ) {
	if ( empty( $user_id ) )
		return false;

	if ( !$userdata = trm_cache_get( 'trs_core_userdata_' . $user_id, 'trs' ) ) {
		$userdata = TRS_Core_User::get_core_userdata( $user_id );
		trm_cache_set( 'trs_core_userdata_' . $user_id, $userdata, 'trs' );
	}
	return apply_filters( 'trs_core_get_core_userdata', $userdata );
}

/**
 * Returns the user id for the user that is currently being displayed.
 * eg: http://andy.domain.com/ or http://domain.com/andy/
 *
 * @package trendr Core
 * @uses trs_core_get_userid_from_user_login() Returns the user id for the username passed
 * @return The user id for the user that is currently being displayed, return zero if this is not a user home and just a normal blog.
 */
function trs_core_get_displayed_userid( $user_login ) {
	return apply_filters( 'trs_core_get_displayed_userid', trs_core_get_userid( $user_login ) );
}

/**
 * Returns the user_id for a user based on their username.
 *
 * @package trendr Core
 * @param $username str Username to check.
 * @global $trmdb trendr DB access object.
 * @return false on no match
 * @return int the user ID of the matched user.
 */
function trs_core_get_userid( $username ) {
	global $trmdb;

	if ( empty( $username ) )
		return false;

	return apply_filters( 'trs_core_get_userid', $trmdb->get_var( $trmdb->prepare( "SELECT ID FROM $trmdb->users WHERE user_login = %s", $username ) ) );
}

/**
 * Returns the user_id for a user based on their user_nicename.
 *
 * @package trendr Core
 * @param $username str Username to check.
 * @global $trmdb trendr DB access object.
 * @return false on no match
 * @return int the user ID of the matched user.
 */
function trs_core_get_userid_from_nicename( $user_nicename ) {
	global $trmdb;

	if ( empty( $user_nicename ) )
		return false;

	return apply_filters( 'trs_core_get_userid_from_nicename', $trmdb->get_var( $trmdb->prepare( "SELECT ID FROM $trmdb->users WHERE user_nicename = %s", $user_nicename ) ) );
}

/**
 * Returns the username for a user based on their user id.
 *
 * @package trendr Core
 * @param $uid int User ID to check.
 * @global $userdata trendr user data for the current logged in user.
 * @uses get_userdata() trendr function to fetch the userdata for a user ID
 * @return false on no match
 * @return str the username of the matched user.
 */
function trs_core_get_username( $user_id, $user_nicename = false, $user_login = false ) {
	global $trs;

	if ( !$username = trm_cache_get( 'trs_user_username_' . $user_id, 'trs' ) ) {
		// Cache not found so prepare to update it
		$update_cache = true;

		// Nicename and login were not passed
		if ( empty( $user_nicename ) && empty( $user_login ) ) {

			// User ID matches logged in user
			if ( isset( $trs->loggedin_user->id ) && $trs->loggedin_user->id == $user_id ) {
				$userdata =$trs->loggedin_user->userdata;

			// User ID matches displayed in user
			} elseif ( isset( $trs->displayed_user->id ) && $trs->displayed_user->id == $user_id ) {
				$userdata =$trs->displayed_user->userdata;

			// No user ID match
			} else {
				$userdata = false;
			}

			// No match so go dig
			if ( empty( $userdata ) ) {

				// User not found so return false
				if ( !$userdata = trs_core_get_core_userdata( $user_id ) ) {
					return false;
				}
			}

			// Update the $user_id for later
			$user_id       = $userdata->ID;

			// Two possible options
			$user_nicename = $userdata->user_nicename;
			$user_login    = $userdata->user_login;
		}

		// Pull an audible and use the login over the nicename
		if ( trs_is_username_compatibility_mode() )
			$username = $user_login;
		else
			$username = $user_nicename;

	// Username found in cache so don't update it again
	} else {
		$update_cache = false;
	}

	// Check $username for empty spaces and default to nicename if found
	if ( strstr( $username, ' ' ) )
		$username = trs_members_get_user_nicename( $user_id );

	// Add this to cache
	if ( ( true == $update_cache ) && !empty( $username ) )
		trm_cache_set( 'trs_user_username_' . $user_id, $username, 'trs' );

	return apply_filters( 'trs_core_get_username', $username );
}

/**
 * Returns the user_nicename for a user based on their user_id. This should be
 * used for linking to user profiles and anywhere else a sanitized and unique
 * slug to a user is needed.
 *
 * @since trendr (1.5)
 *
 * @package trendr Core
 * @param $uid int User ID to check.
 * @global $userdata trendr user data for the current logged in user.
 * @uses get_userdata() trendr function to fetch the userdata for a user ID
 * @return false on no match
 * @return str the username of the matched user.
 */
function trs_members_get_user_nicename( $user_id ) {
	global $trs;

	if ( !$user_nicename = trm_cache_get( 'trs_members_user_nicename_' . $user_id, 'trs' ) ) {
		$update_cache = true;

		// User ID matches logged in user
		if ( isset( $trs->loggedin_user->id ) && $trs->loggedin_user->id == $user_id ) {
			$userdata =$trs->loggedin_user->userdata;

		// User ID matches displayed in user
		} elseif ( isset( $trs->displayed_user->id ) && $trs->displayed_user->id == $user_id ) {
			$userdata =$trs->displayed_user->userdata;

		// No user ID match
		} else {
			$userdata = false;
		}

		// No match so go dig
		if ( empty( $userdata ) ) {

			// User not found so return false
			if ( !$userdata = trs_core_get_core_userdata( $user_id ) ) {
				return false;
			}
		}

		// User nicename found
		$user_nicename = $userdata->user_nicename;

	// Nicename found in cache so don't update it again
	} else {
		$update_cache = false;
	}

	// Add this to cache
	if ( true == $update_cache && !empty( $user_nicename ) )
		trm_cache_set( 'trs_members_user_nicename_' . $user_id, $user_nicename, 'trs' );

	return apply_filters( 'trs_members_get_user_nicename', $user_nicename );
}

/**
 * Returns the email address for the user based on user ID
 *
 * @package trendr Core
 * @param $uid int User ID to check.
 * @uses get_userdata() trendr function to fetch the userdata for a user ID
 * @return false on no match
 * @return str The email for the matched user.
 */
function trs_core_get_user_email( $uid ) {
	if ( !$email = trm_cache_get( 'trs_user_email_' . $uid, 'trs' ) ) {
		// User exists
		if ( $ud = trs_core_get_core_userdata( $uid ) )
			$email = $ud->user_email;

		// User was deleted
		else
			$email = '';

		trm_cache_set( 'trs_user_email_' . $uid, $email, 'trs' );
	}

	return apply_filters( 'trs_core_get_user_email', $email );
}

/**
 * Returns a HTML formatted link for a user with the user's full name as the link text.
 * eg: <a href="http://andy.domain.com/">Andy Peatling</a>
 * Optional parameters will return just the name or just the URL.
 *
 * @param int $user_id User ID to check.
 * @param $no_anchor bool Disable URL and HTML and just return full name. Default false.
 * @param $just_link bool Disable full name and HTML and just return the URL text. Default false.
 * @return false on no match
 * @return str The link text based on passed parameters.
 * @todo This function needs to be cleaned up or split into separate functions
 */
function trs_core_get_userlink( $user_id, $no_anchor = false, $just_link = false ) {
	$display_name = trs_core_get_user_displayname( $user_id );

	if ( empty( $display_name ) )
		return false;

	if ( $no_anchor )
		return $display_name;

	if ( !$url = trs_core_get_user_domain( $user_id ) )
		return false;

	if ( $just_link )
		return $url;

	return apply_filters( 'trs_core_get_userlink', '<a href="' . $url . '" title="' . $display_name . '">' . $display_name . '</a>', $user_id );
}


/**
 * Fetch the display name for a user. This will use the "Name" field in xprofile if it is installed.
 * Otherwise, it will fall back to the normal TRM display_name, or user_nicename, depending on what has been set.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 * @uses trm_cache_get() Will try and fetch the value from the cache, rather than querying the DB again.
 * @uses get_userdata() Fetches the TRM userdata for a specific user.
 * @uses xprofile_set_field_data() Will update the field data for a user based on field name and user id.
 * @uses trm_cache_set() Adds a value to the cache.
 * @return str The display name for the user in question.
 */
function trs_core_get_user_displayname( $user_id_or_username ) {
	global $trs;

	$fullname = '';

	if ( !$user_id_or_username )
		return false;

	if ( !is_numeric( $user_id_or_username ) )
		$user_id = trs_core_get_userid( $user_id_or_username );
	else
		$user_id = $user_id_or_username;

	if ( !$user_id )
		return false;

	if ( !$fullname = trm_cache_get( 'trs_user_fullname_' . $user_id, 'trs' ) ) {
		if ( trs_is_active( 'xprofile' ) ) {
			$fullname = xprofile_get_field_data( stripslashes( $trs->site_options['trs-xprofile-fullname-field-name'] ), $user_id );

			if ( empty($fullname) ) {
				$ud = trs_core_get_core_userdata( $user_id );

				if ( !empty( $ud->display_name ) )
					$fullname = $ud->display_name;
				elseif ( !empty( $ud->user_nicename ) )
					$fullname = $ud->user_nicename;

				xprofile_set_field_data( 1, $user_id, $fullname );
			}
		} else {
			$ud = trs_core_get_core_userdata($user_id);

			if ( !empty( $ud->display_name ) )
				$fullname = $ud->display_name;
			elseif ( !empty( $ud->user_nicename ) )
				$fullname = $ud->user_nicename;
		}

		if ( !empty( $fullname ) )
			trm_cache_set( 'trs_user_fullname_' . $user_id, $fullname, 'trs' );
	}

	return apply_filters( 'trs_core_get_user_displayname', $fullname, $user_id );
}
add_filter( 'trs_core_get_user_displayname', 'strip_tags', 1 );
add_filter( 'trs_core_get_user_displayname', 'trim'          );
add_filter( 'trs_core_get_user_displayname', 'stripslashes'  );


/**
 * Returns the user link for the user based on user email address
 *
 * @package trendr Core
 * @param $email str The email address for the user.
 * @uses trs_core_get_userlink() trendr function to get a userlink by user ID.
 * @uses get_user_by() trendr function to get userdata via an email address
 * @return str The link to the users home base. False on no match.
 */
function trs_core_get_userlink_by_email( $email ) {
	$user = get_user_by( 'email', $email );
	return apply_filters( 'trs_core_get_userlink_by_email', trs_core_get_userlink( $user->ID, false, false, true ) );
}

/**
 * Returns the user link for the user based on the supplied identifier
 *
 * @param $username str If TRS_ENABLE_USERNAME_COMPATIBILITY_MODE is set, this will be user_login, otherwise it will be user_nicename.
 * @return str The link to the users home base. False on no match.
 */
function trs_core_get_userlink_by_username( $username ) {
	if ( trs_is_username_compatibility_mode() )
		$user_id = trs_core_get_userid( $username );
	else
		$user_id = trs_core_get_userid_from_nicename( $username );

	return apply_filters( 'trs_core_get_userlink_by_username', trs_core_get_userlink( $user_id, false, false, true ) );
}

/**
 * Returns the total number of members for the installation.
 *
 * @package trendr Core
 * @return int The total number of members.
 */
function trs_core_get_total_member_count() {
	global $trmdb, $trs;

	if ( !$count = trm_cache_get( 'trs_total_member_count', 'trs' ) ) {
		$status_sql = trs_core_get_status_sql();
		$count = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(ID) FROM $trmdb->users WHERE {$status_sql}" ) );
		trm_cache_set( 'trs_total_member_count', $count, 'trs' );
	}

	return apply_filters( 'trs_core_get_total_member_count', $count );
}

/**
 * Checks if the user has been marked as a spammer.
 *
 * @package trendr Core
 * @param int $user_id int The id for the user.
 * @return bool True if spammer, False if not.
 */
function trs_core_is_user_spammer( $user_id = 0 ) {
	global $trmdb;

	// No user to check
	if ( empty( $user_id ) )
		return false;

	// Assume user is not spam
	$is_spammer = false;

	// Get user data
	$user = get_userdata( $user_id );

	// No user found
	if ( empty( $user ) ) {
		$is_spammer = false;

	// User found
	} else {

		// Check if spam
		if ( !empty( $user->spam ) )
			$is_spammer = true;

		if ( 1 == $user->user_status )
			$is_spammer = true;
	}

	return apply_filters( 'trs_core_is_user_spammer', (bool) $is_spammer );
}

/**
 * Checks if the user has been marked as deleted.
 *
 * @package trendr Core
 * @param int $user_id int The id for the user.
 * @return bool True if deleted, False if not.
 */
function trs_core_is_user_deleted( $user_id = 0 ) {
	global $trmdb;

	// No user to check
	if ( empty( $user_id ) )
		return false;

	// Assume user is not deleted
	$is_deleted = false;

	// Get user data
	$user = get_userdata( $user_id );

	// No user found
	if ( empty( $user ) ) {
		$is_deleted = true;

	// User found
	} else {

		// Check if deleted
		if ( !empty( $user->deleted ) )
			$is_deleted = true;

		if ( 2 == $user->user_status )
			$is_deleted = true;

	}

	return apply_filters( 'trs_core_is_user_deleted', (bool) $is_deleted );
}

/**
 * Fetch every post that is authored by the given user for the current blog.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 * @global $trmdb trendr user data for the current logged in user.
 * @return array of post ids.
 */
function trs_core_get_all_posts_for_user( $user_id = 0 ) {
	global $trs, $trmdb;

	if ( empty( $user_id ) )
		$user_id = $trs->displayed_user->id;

	return apply_filters( 'trs_core_get_all_posts_for_user', $trmdb->get_col( $trmdb->prepare( "SELECT ID FROM $trmdb->posts WHERE post_author = %d AND post_status = 'publish' AND post_type = 'post'", $user_id ) ) );
}

/**
 * Allows a user to completely remove their account from the system
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 * @uses is_super_admin() Checks to see if the user is a site administrator.
 * @uses trmmu_delete_user() Deletes a user from the system on multisite installs.
 * @uses trm_delete_user() Deletes a user from the system on singlesite installs.
 */
function trs_core_delete_account( $user_id = 0 ) {
	global $trs, $trm_version;

	if ( !$user_id )
		$user_id = $trs->loggedin_user->id;

	// Make sure account deletion is not disabled
	if ( !empty( $trs->site_options['trs-disable-account-deletion'] ) && !$trs->loggedin_user->is_super_admin )
		return false;

	// Site admins cannot be deleted
	if ( is_super_admin( trs_core_get_username( $user_id ) ) )
		return false;

	// Specifically handle multi-site environment
	if ( is_multisite() ) {
		if ( $trm_version >= '3.0' )
			require( ABSPATH . '/Backend-WeaprEcqaKejUbRq-trendr/includes/ms.php' );
		else
			require( ABSPATH . '/Backend-WeaprEcqaKejUbRq-trendr/includes/mu.php' );

		require( ABSPATH . '/Backend-WeaprEcqaKejUbRq-trendr/includes/user.php' );

		return trmmu_delete_user( $user_id );

	// Single site user deletion
	} else {
		require( ABSPATH . '/Backend-WeaprEcqaKejUbRq-trendr/includes/user.php' );
		return trm_delete_user( $user_id );
	}
}

/**
 * Localization safe ucfirst() support.
 *
 * @package trendr Core
 */
function trs_core_ucfirst( $str ) {
	if ( function_exists( 'mb_strtoupper' ) && function_exists( 'mb_substr' ) ) {
		$fc = mb_strtoupper( mb_substr( $str, 0, 1 ) );
		return $fc.mb_substr( $str, 1 );
	} else {
		return ucfirst( $str );
	}
}

/**
 * Strips spaces from usernames that are created using add_user() and trm_insert_user()
 *
 * @package trendr Core
 */
function trs_core_strip_username_spaces( $username ) {
	// Don't alter the user_login of existing users, as it causes user_nicename problems.
	// See http://trac.trendr.org/ticket/2642
	if ( username_exists( $username ) && ( !trs_is_username_compatibility_mode() ) )
		return $username;

	return str_replace( ' ', '-', $username );
}
add_action( 'pre_user_login', 'trs_core_strip_username_spaces' );

/**
 * When a user logs in, check if they have been marked as a spammer. If yes then simply
 * redirect them to the home page and stop them from logging in.
 *
 * @package trendr Core
 * @param $auth_obj The TRM authorization object
 * @param $username The username of the user logging in.
 * @uses get_user_by() Get the userdata object for a user based on their username
 * @uses trs_core_redirect() Safe redirect to a page
 * @return $auth_obj If the user is not a spammer, return the authorization object
 */
function trs_core_boot_spammer( $auth_obj, $username ) {
	global $trs;

	if ( !$user = get_user_by( 'login',  $username ) )
		return $auth_obj;

	if ( ( is_multisite() && (int)$user->spam ) || 1 == (int)$user->user_status )
		return new TRM_Error( 'invalid_username', __( '<strong>ERROR</strong>: Your account has been marked as a spammer.', 'trendr' ) );
	else
		return $auth_obj;
}
add_filter( 'authenticate', 'trs_core_boot_spammer', 30, 2 );

/**
 * Deletes usermeta for the user when the user is deleted.
 *
 * @package trendr Core
 * @param $user_id The user id for the user to delete usermeta for
 * @uses trs_delete_user_meta() deletes a row from the trm_usermeta table based on meta_key
 */
function trs_core_remove_data( $user_id ) {
	// Remove usermeta
	trs_delete_user_meta( $user_id, 'last_activity' );

	// Flush the cache to remove the user from all cached objects
	trm_cache_flush();
}
add_action( 'trmmu_delete_user',  'trs_core_remove_data' );
add_action( 'delete_user',       'trs_core_remove_data' );
add_action( 'trs_make_spam_user', 'trs_core_remove_data' );

function trs_core_can_edit_settings() {
	if ( trs_is_my_profile() )
		return true;

	if ( is_super_admin() || current_user_can( 'edit_users' ) )
		return true;

	return false;
}

?>