<?php
/*******************************************************************************
 * Action Functions
 *
 * Action functions are exactly the same as screen functions, however they do not
 * have a template screen associated with them. Usually they will send the user
 * back to the default screen after execution.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Listens to the $trs component and action variables to determine if the user is viewing the members
 * directory page. If they are, it will set up the directory and load the members directory template.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 * @uses trm_enqueue_script() Loads a JS script into the header of the page.
 * @uses trs_core_load_template() Loads a specific template file.
 */
/**
 * When a site admin selects "Mark as Spammer/Not Spammer" from the admin menu
 * this action will fire and mark or unmark the user and their blogs as spam.
 * Must be a site admin for this function to run.
 *
 * @package trendr Core
 * @param int $user_id Optional user ID to mark as spam
 * @global object $trmdb Global WordPress Database object
 */
function trs_core_action_set_spammer_status( $user_id = 0 ) {
	global $trmdb;

	// Only super admins can currently spam users
	if ( !is_super_admin() || trs_is_my_profile() )
		return;

	// Use displayed user if it's not yourself
	if ( empty( $user_id ) && trs_is_user() )
		$user_id = trs_displayed_user_id();

	// Bail if no user ID
	if ( empty( $user_id ) )
		return;

	// Bail if user ID is super admin
	if ( is_super_admin( $user_id ) )
		return;

	if ( trs_is_current_component( 'admin' ) && ( in_array( trs_current_action(), array( 'mark-spammer', 'unmark-spammer' ) ) ) ) {

		// Check the nonce
		check_admin_referer( 'mark-unmark-spammer' );

		// Get the functions file
		if ( is_multisite() ) {
			require( ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/ms.php' );
		}

		// To spam or not to spam
		$is_spam = trs_is_current_action( 'mark-spammer' ) ? 1 : 0;

		// Get the blogs for the user
		$blogs = get_blogs_of_user( $user_id, true );

		foreach ( (array) $blogs as $key => $details ) {

			// Do not mark the main or current root blog as spam
			if ( 1 == $details->userblog_id || trs_get_root_blog_id() == $details->userblog_id ) {
				continue;
			}

			// Update the blog status
			update_blog_status( $details->userblog_id, 'spam', $is_spam );
		}

		// Finally, mark this user as a spammer
		if ( is_multisite() ) {
			update_user_status( $user_id, 'spam', $is_spam );
		}

		// Always set single site status
		$trmdb->update( $trmdb->users, array( 'user_status' => $is_spam ), array( 'ID' => $user_id ) );

		// Add feedback message
		if ( $is_spam ) {
			trs_core_add_message( __( 'User marked as spammer. Spam users are visible only to site admins.', 'trendr' ) );
		} else {
			trs_core_add_message( __( 'User removed as spammer.', 'trendr' ) );
		}

		// Hide this user's activity
		if ( $is_spam && trs_is_active( 'activity' ) ) {
			trs_activity_hide_user_activity( $user_id );
		}

		// We need a special hook for is_spam so that components can delete data at spam time
		$trs_action = $is_spam ? 'trs_make_spam_user' : 'trs_make_ham_user';
		do_action( $trs_action, trs_displayed_user_id() );

		// Call multisite actions in single site mode for good measure
		if ( !is_multisite() ) {
			$trm_action = $is_spam ? 'make_spam_user' : 'make_ham_user';
			do_action( $trm_action, trs_displayed_user_id() );
		}

		// Allow plugins to do neat things
		do_action( 'trs_core_action_set_spammer_status', trs_displayed_user_id(), $is_spam );

		// Redirect back to where we came from
		trs_core_redirect( trm_get_referer() );
	}
}
add_action( 'trs_actions', 'trs_core_action_set_spammer_status' );

/**
 * Allows a site admin to delete a user from the adminbar menu.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 */
function trs_core_action_delete_user() {
	global $trs;

	if ( !is_super_admin() || trs_is_my_profile() || !$trs->displayed_user->id )
		return false;

	if ( 'admin' == $trs->current_component && 'delete-user' == $trs->current_action ) {
		// Check the nonce
		check_admin_referer( 'delete-user' );

		$errors = false;
		do_action( 'trs_core_before_action_delete_user', $errors );

		if ( trs_core_delete_account( $trs->displayed_user->id ) ) {
			trs_core_add_message( sprintf( __( '%s has been deleted from the system.', 'trendr' ), $trs->displayed_user->fullname ) );
		} else {
			trs_core_add_message( sprintf( __( 'There was an error deleting %s from the system. Please try again.', 'trendr' ), $trs->displayed_user->fullname ), 'error' );
			$errors = true;
		}

		do_action( 'trs_core_action_delete_user', $errors );

		if ( $errors )
			trs_core_redirect( $trs->displayed_user->domain );
		else
			trs_core_redirect( $trs->loggedin_user->domain );
	}
}
add_action( 'trs_actions', 'trs_core_action_delete_user' );

/**
 * Returns the user_id for a user based on their username.
 *
 * @package trendr Core
 * @param $username str Username to check.
 * @return false on no match
 * @return int the user ID of the matched user.
 */
function trs_core_get_random_member() {
	global $trs;

	if ( isset( $_GET['random-member'] ) ) {
		$user = trs_core_get_users( array( 'type' => 'random', 'per_page' => 1 ) );
		trs_core_redirect( trs_core_get_user_domain( $user['users'][0]->id ) );
	}
}
add_action( 'trs_actions', 'trs_core_get_random_member' );
?>