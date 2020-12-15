<?php
/********************************************************************************
 * Action Functions
 *
 * Action functions are exactly the same as screen functions, however they do not
 * have a template screen associated with them. Usually they will send the user
 * back to the default screen after execution.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function groups_action_create_group() {
	global $trs;

	// If we're not at domain.org/groups/create/ then return false
	if ( !trs_is_groups_component() || !trs_is_current_action( 'create' ) )
		return false;

	if ( !is_user_logged_in() )
		return false;

 	if ( !trs_user_can_create_groups() ) {
		trs_core_add_message( __( 'Sorry, you are not allowed to create groups.', 'trendr' ), 'error' );
		trs_core_redirect( trailingslashit( trs_get_root_domain() . '/' . trs_get_groups_root_slug() ) );
	}

	// Make sure creation steps are in the right order
	groups_action_sort_creation_steps();

	// If no current step is set, reset everything so we can start a fresh group creation
	$trs->groups->current_create_step = trs_action_variable( 1 );
	if ( !$trs->groups->current_create_step ) {
		unset( $trs->groups->current_create_step );
		unset( $trs->groups->completed_create_steps );

		setcookie( 'trs_new_group_id', false, time() - 1000, COOKIEPATH );
		setcookie( 'trs_completed_create_steps', false, time() - 1000, COOKIEPATH );

		$reset_steps = true;
		trs_core_redirect( trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/create/step/' . array_shift( array_keys( $trs->groups->group_creation_steps ) ) . '/' );
	}

	// If this is a creation step that is not recognized, just redirect them back to the first screen
	if ( !empty( $trs->groups->current_create_step ) && empty( $trs->groups->group_creation_steps[$trs->groups->current_create_step] ) ) {
		trs_core_add_message( __('There was an error saving group details. Please try again.', 'trendr'), 'error' );
		trs_core_redirect( trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/create/' );
	}

	// Fetch the currently completed steps variable
	if ( isset( $_COOKIE['trs_completed_create_steps'] ) && !isset( $reset_steps ) )
		$trs->groups->completed_create_steps = unserialize( stripslashes( $_COOKIE['trs_completed_create_steps'] ) );

	// Set the ID of the new group, if it has already been created in a previous step
	if ( isset( $_COOKIE['trs_new_group_id'] ) ) {
		$trs->groups->new_group_id = $_COOKIE['trs_new_group_id'];
		$trs->groups->current_group = new TRS_Groups_Group( $trs->groups->new_group_id );
	}

	// If the save, upload or skip button is hit, lets calculate what we need to save
	if ( isset( $_POST['save'] ) ) {

		// Check the nonce
		check_admin_referer( 'groups_create_save_' . $trs->groups->current_create_step );

		if ( 'group-details' == $trs->groups->current_create_step ) {
			if ( empty( $_POST['group-name'] ) || empty( $_POST['group-desc'] ) || !strlen( trim( $_POST['group-name'] ) ) || !strlen( trim( $_POST['group-desc'] ) ) ) {
				trs_core_add_message( __( 'Please fill in all of the required fields', 'trendr' ), 'error' );
				trs_core_redirect( trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/create/step/' . $trs->groups->current_create_step . '/' );
			}

			$new_group_id = isset( $trs->groups->new_group_id ) ? $trs->groups->new_group_id : 0;

			if ( !$trs->groups->new_group_id = groups_create_group( array( 'group_id' => $new_group_id, 'name' => $_POST['group-name'], 'description' => $_POST['group-desc'], 'slug' => groups_check_slug( sanitize_title( esc_attr( $_POST['group-name'] ) ) ), 'date_created' => trs_core_current_time(), 'status' => 'public' ) ) ) {
				trs_core_add_message( __( 'There was an error saving group details, please try again.', 'trendr' ), 'error' );
				trs_core_redirect( trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/create/step/' . $trs->groups->current_create_step . '/' );
			}

			groups_update_groupmeta( $trs->groups->new_group_id, 'total_member_count', 1 );
			groups_update_groupmeta( $trs->groups->new_group_id, 'last_activity', trs_core_current_time() );
		}

		if ( 'group-settings' == $trs->groups->current_create_step ) {
			$group_status = 'public';
			$group_enable_forum = 1;

			if ( !isset($_POST['group-show-forum']) ) {
				$group_enable_forum = 0;
			} else {
				// Create the forum if enable_forum = 1
				if ( trs_is_active( 'forums' ) && '' == groups_get_groupmeta( $trs->groups->new_group_id, 'forum_id' ) ) {
					groups_new_group_forum();
				}
			}

			if ( 'private' == $_POST['group-status'] )
				$group_status = 'private';
			else if ( 'hidden' == $_POST['group-status'] )
				$group_status = 'hidden';

			if ( !$trs->groups->new_group_id = groups_create_group( array( 'group_id' => $trs->groups->new_group_id, 'status' => $group_status, 'enable_forum' => $group_enable_forum ) ) ) {
				trs_core_add_message( __( 'There was an error saving group details, please try again.', 'trendr' ), 'error' );
				trs_core_redirect( trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/create/step/' . $trs->groups->current_create_step . '/' );
			}

			// Set the invite status			
			// Checked against a whitelist for security
			$allowed_invite_status = apply_filters( 'groups_allowed_invite_status', array( 'members', 'mods', 'admins' ) );
			$invite_status	       = !empty( $_POST['group-invite-status'] ) && in_array( $_POST['group-invite-status'], (array)$allowed_invite_status ) ? $_POST['group-invite-status'] : 'members';

			groups_update_groupmeta( $trs->groups->new_group_id, 'invite_status', $invite_status );
		}

		if ( 'group-invites' == $trs->groups->current_create_step )
			groups_send_invites( $trs->loggedin_user->id, $trs->groups->new_group_id );

		do_action( 'groups_create_group_step_save_' . $trs->groups->current_create_step );
		do_action( 'groups_create_group_step_complete' ); // Mostly for clearing cache on a generic action name

		/**
		 * Once we have successfully saved the details for this step of the creation process
		 * we need to add the current step to the array of completed steps, then update the cookies
		 * holding the information
		 */
		$completed_create_steps = isset( $trs->groups->completed_create_steps ) ? $trs->groups->completed_create_steps : array();
		if ( !in_array( $trs->groups->current_create_step, $completed_create_steps ) )
			$trs->groups->completed_create_steps[] = $trs->groups->current_create_step;

		// Reset cookie info
		setcookie( 'trs_new_group_id', $trs->groups->new_group_id, time()+60*60*24, COOKIEPATH );
		setcookie( 'trs_completed_create_steps', serialize( $trs->groups->completed_create_steps ), time()+60*60*24, COOKIEPATH );

		// If we have completed all steps and hit done on the final step we
		// can redirect to the completed group
		if ( count( $trs->groups->completed_create_steps ) == count( $trs->groups->group_creation_steps ) && $trs->groups->current_create_step == array_pop( array_keys( $trs->groups->group_creation_steps ) ) ) {
			unset( $trs->groups->current_create_step );
			unset( $trs->groups->completed_create_steps );

			// Once we compelete all steps, record the group creation in the activity stream.
			groups_record_activity( array(
				'action' => apply_filters( 'groups_activity_created_group_action', sprintf( __( '%1$s created the group %2$s', 'trendr'), trs_core_get_userlink( $trs->loggedin_user->id ), '<a href="' . trs_get_group_permalink( $trs->groups->current_group ) . '">' . esc_attr( $trs->groups->current_group->name ) . '</a>' ) ),
				'type' => 'created_group',
				'item_id' => $trs->groups->new_group_id
			) );

			do_action( 'groups_group_create_complete', $trs->groups->new_group_id );

			trs_core_redirect( trs_get_group_permalink( $trs->groups->current_group ) );
		} else {
			/**
			 * Since we don't know what the next step is going to be (any plugin can insert steps)
			 * we need to loop the step array and fetch the next step that way.
			 */
			foreach ( (array)$trs->groups->group_creation_steps as $key => $value ) {
				if ( $key == $trs->groups->current_create_step ) {
					$next = 1;
					continue;
				}

				if ( isset( $next ) ) {
					$next_step = $key;
					break;
				}
			}

			trs_core_redirect( trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/create/step/' . $next_step . '/' );
		}
	}

	// Group portrait is handled separately
	if ( 'group-portrait' == $trs->groups->current_create_step && isset( $_POST['upload'] ) ) {
		if ( !empty( $_FILES ) && isset( $_POST['upload'] ) ) {
			// Normally we would check a nonce here, but the group save nonce is used instead

			// Pass the file to the portrait upload handler
			if ( trs_core_portrait_handle_upload( $_FILES, 'groups_portrait_upload_dir' ) ) {
				$trs->portrait_admin->step = 'crop-image';

				// Make sure we include the jQuery jCrop file for image cropping
				add_action( 'trm_print_scripts', 'trs_core_add_jquery_cropper' );
			}
		}

		// If the image cropping is done, crop the image and save a full/thumb version
		if ( isset( $_POST['portrait-crop-submit'] ) && isset( $_POST['upload'] ) ) {
			// Normally we would check a nonce here, but the group save nonce is used instead

			if ( !trs_core_portrait_handle_crop( array( 'object' => 'group', 'portrait_dir' => 'group-portraits', 'item_id' => $trs->groups->current_group->id, 'original_file' => $_POST['image_src'], 'crop_x' => $_POST['x'], 'crop_y' => $_POST['y'], 'crop_w' => $_POST['w'], 'crop_h' => $_POST['h'] ) ) )
				trs_core_add_message( __( 'There was an error saving the group portrait, please try uploading again.', 'trendr' ), 'error' );
			else
				trs_core_add_message( __( 'The group portrait was uploaded successfully!', 'trendr' ) );
		}
	}

 	trs_core_load_template( apply_filters( 'groups_template_create_group', 'groups/create' ) );
}
add_action( 'trs_actions', 'groups_action_create_group' );

function groups_action_join_group() {
	global $trs;

	if ( !trs_is_single_item() || !trs_is_groups_component() || !trs_is_current_action( 'join' ) )
		return false;

	// Nonce check
	if ( !check_admin_referer( 'groups_join_group' ) )
		return false;

	// Skip if banned or already a member
	if ( !groups_is_user_member( $trs->loggedin_user->id, $trs->groups->current_group->id ) && !groups_is_user_banned( $trs->loggedin_user->id, $trs->groups->current_group->id ) ) {

		// User wants to join a group that is not public
		if ( $trs->groups->current_group->status != 'public' ) {
			if ( !groups_check_user_has_invite( $trs->loggedin_user->id, $trs->groups->current_group->id ) ) {
				trs_core_add_message( __( 'There was an error joining the group.', 'trendr' ), 'error' );
				trs_core_redirect( trs_get_group_permalink( $trs->groups->current_group ) );
			}
		}

		// User wants to join any group
		if ( !groups_join_group( $trs->groups->current_group->id ) )
			trs_core_add_message( __( 'There was an error joining the group.', 'trendr' ), 'error' );
		else
			trs_core_add_message( __( 'You joined the group!', 'trendr' ) );

		trs_core_redirect( trs_get_group_permalink( $trs->groups->current_group ) );
	}

	trs_core_load_template( apply_filters( 'groups_template_group_home', 'groups/single/home' ) );
}
add_action( 'trs_actions', 'groups_action_join_group' );


function groups_action_leave_group() {
	global $trs;

	if ( !trs_is_single_item() || !trs_is_groups_component() || !trs_is_current_action( 'leave-group' ) )
		return false;

	// Nonce check
	if ( !check_admin_referer( 'groups_leave_group' ) )
		return false;

	// User wants to leave any group
	if ( groups_is_user_member( $trs->loggedin_user->id, $trs->groups->current_group->id ) ) {

		// Stop sole admins from abandoning their group
		$group_admins = groups_get_group_admins( $trs->groups->current_group->id );
	 	if ( 1 == count( $group_admins ) && $group_admins[0]->user_id == $trs->loggedin_user->id )
			trs_core_add_message( __( 'This group must have at least one admin', 'trendr' ), 'error' );

		elseif ( !groups_leave_group( $trs->groups->current_group->id ) )
			trs_core_add_message( __( 'There was an error leaving the group.', 'trendr' ), 'error' );
		else
			trs_core_add_message( __( 'You successfully left the group.', 'trendr' ) );

		trs_core_redirect( trs_get_group_permalink( $trs->groups->current_group ) );
	}

	trs_core_load_template( apply_filters( 'groups_template_group_home', 'groups/single/home' ) );
}
add_action( 'trs_actions', 'groups_action_leave_group' );


function groups_action_sort_creation_steps() {
	global $trs;

	if ( !trs_is_groups_component() || !trs_is_current_action( 'create' ) )
		return false;

	if ( !is_array( $trs->groups->group_creation_steps ) )
		return false;

	foreach ( (array)$trs->groups->group_creation_steps as $slug => $step ) {
		while ( !empty( $temp[$step['position']] ) )
			$step['position']++;

		$temp[$step['position']] = array( 'name' => $step['name'], 'slug' => $slug );
	}

	// Sort the steps by their position key
	ksort($temp);
	unset($trs->groups->group_creation_steps);

	foreach( (array)$temp as $position => $step )
		$trs->groups->group_creation_steps[$step['slug']] = array( 'name' => $step['name'], 'position' => $position );
}

function groups_action_redirect_to_random_group() {
	global $trs, $trmdb;

	if ( trs_is_groups_component() && isset( $_GET['random-group'] ) ) {
		$group = groups_get_groups( array( 'type' => 'random', 'per_page' => 1 ) );

		trs_core_redirect( trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/' . $group['groups'][0]->slug . '/' );
	}
}
add_action( 'trs_actions', 'groups_action_redirect_to_random_group' );

function groups_action_group_feed() {
	global $trs, $trm_query;

	if ( !trs_is_active( 'activity' ) || !trs_is_groups_component() || !isset( $trs->groups->current_group ) || !trs_is_current_action( 'feed' ) )
		return false;

	$trm_query->is_404 = false;
	status_header( 200 );

	if ( 'public' != $trs->groups->current_group->status ) {
		if ( !groups_is_user_member( $trs->loggedin_user->id, $trs->groups->current_group->id ) )
			return false;
	}

	include_once( TRS_PLUGIN_DIR . '/trs-activity/feeds/trs-activity-group-feed.php' );
	die;
}
add_action( 'trs_actions', 'groups_action_group_feed' );
?>