<?php
/**
 * TRS Follow Actions
 *
 * @package TRS-Follow
 * @sutrsackage Actions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Catches clicks on a "Follow User" button and tries to make that happen.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses check_admin_referer() Checks to make sure the TRM security nonce matches.
 * @uses trs_follow_is_following() Checks to see if a user is following another user already.
 * @uses trs_follow_start_following() Starts a user following another user.
 * @uses trs_core_add_message() Adds an error/success message to be displayed after redirect.
 * @uses trs_core_redirect() Safe redirects the user to a particular URL.
 * @return bool false
 */
function trs_follow_action_start() {
	global $trs;

	if ( !trs_is_current_component( $trs->follow->followers->slug ) || !trs_is_current_action( 'start' ) )
		return false;

	if ( trs_displayed_user_id() == trs_loggedin_user_id() )
		return false;

	check_admin_referer( 'start_following' );

	if ( trs_follow_is_following( array( 'leader_id' => trs_displayed_user_id(), 'follower_id' => trs_loggedin_user_id() ) ) )
		trs_core_add_message( sprintf( __( 'You are already following %s.', 'trs-follow' ), trs_get_displayed_user_fullname() ), 'error' );
	else {
		if ( !trs_follow_start_following( array( 'leader_id' => trs_displayed_user_id(), 'follower_id' => trs_loggedin_user_id() ) ) )
			trs_core_add_message( sprintf( __( 'There was a problem when trying to follow %s, please try again.', 'trs-follow' ), trs_get_displayed_user_fullname() ), 'error' );
		else
			trs_core_add_message( sprintf( __( 'You are now following %s.', 'trs-follow' ), trs_get_displayed_user_fullname() ) );
	}

	// it's possible that trm_get_referer() returns false, so let's fallback to the displayed user's page
	$redirect = trm_get_referer() ? trm_get_referer() : trs_displayed_user_domain();
	trs_core_redirect( $redirect );

	return false;
}
add_action( 'trs_actions', 'trs_follow_action_start' );

/**
 * Catches clicks on a "Stop Following User" button and tries to make that happen.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses check_admin_referer() Checks to make sure the TRM security nonce matches.
 * @uses trs_follow_is_following() Checks to see if a user is following another user already.
 * @uses trs_follow_stop_following() Stops a user following another user.
 * @uses trs_core_add_message() Adds an error/success message to be displayed after redirect.
 * @uses trs_core_redirect() Safe redirects the user to a particular URL.
 * @return bool false
 */
function trs_follow_action_stop() {
	global $trs;

	if ( !trs_is_current_component( $trs->follow->followers->slug ) || !trs_is_current_action( 'stop' ) )
		return false;

	if ( trs_displayed_user_id() == trs_loggedin_user_id() )
		return false;

	check_admin_referer( 'stop_following' );

	if ( !trs_follow_is_following( array( 'leader_id' => trs_displayed_user_id(), 'follower_id' => trs_loggedin_user_id() ) ) )
		trs_core_add_message( sprintf( __( 'You are not following %s.', 'trs-follow' ), trs_get_displayed_user_fullname() ), 'error' );
	else {
		if ( !trs_follow_stop_following( array( 'leader_id' => trs_displayed_user_id(), 'follower_id' => trs_loggedin_user_id() ) ) )
			trs_core_add_message( sprintf( __( 'There was a problem when trying to stop following %s, please try again.', 'trs-follow' ), trs_get_displayed_user_fullname() ), 'error' );
		else
			trs_core_add_message( sprintf( __( 'You are no longer following %s.', 'trs-follow' ), trs_get_displayed_user_fullname() ) );
	}

	// it's possible that trm_get_referer() returns false, so let's fallback to the displayed user's page
	$redirect = trm_get_referer() ? trm_get_referer() : trs_displayed_user_domain();
	trs_core_redirect( $redirect );

	return false;
}
add_action( 'trs_actions', 'trs_follow_action_stop' );

/** AJAX ACTIONS ***************************************************/

/**
 * Allow a user to start following another user by catching an AJAX request.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses check_admin_referer() Checks to make sure the TRM security nonce matches.
 * @uses trs_follow_is_following() Checks to see if a user is following another user already.
 * @uses trs_follow_start_following() Starts a user following another user.
 * @return bool false
 */
function trs_follow_ajax_action_start() {

	check_admin_referer( 'start_following' );

	if ( trs_follow_is_following( array( 'leader_id' => $_POST['uid'], 'follower_id' => trs_loggedin_user_id() ) ) )
		$message = __( 'Already following', 'trs-follow' );
	else {
		if ( !trs_follow_start_following( array( 'leader_id' => $_POST['uid'], 'follower_id' => trs_loggedin_user_id() ) ) )
			$message = __( 'Error following user', 'trs-follow' );
		else
			$message = __( 'You are now following', 'trs-follow' );
	}

	echo $message;

	exit();
}
add_action( 'trm_ajax_trs_follow', 'trs_follow_ajax_action_start' );

/**
 * Allow a user to stop following another user by catching an AJAX request.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses check_admin_referer() Checks to make sure the TRM security nonce matches.
 * @uses trs_follow_is_following() Checks to see if a user is following another user already.
 * @uses trs_follow_stop_following() Stops a user following another user.
 * @return bool false
 */
function trs_follow_ajax_action_stop() {

	check_admin_referer( 'stop_following' );

	if ( !trs_follow_is_following( array( 'leader_id' => $_POST['uid'], 'follower_id' => trs_loggedin_user_id() ) ) )
		$message = __( 'Not following', 'trs-follow' );
	else {
		if ( !trs_follow_stop_following( array( 'leader_id' => $_POST['uid'], 'follower_id' => trs_loggedin_user_id() ) ) )
			$message = __( 'Error unfollowing user', 'trs-follow' );
		else
			$message = __( 'Stopped following', 'trs-follow' );
	}

	echo $message;

	exit();
}
add_action( 'trm_ajax_trs_unfollow', 'trs_follow_ajax_action_stop' );
