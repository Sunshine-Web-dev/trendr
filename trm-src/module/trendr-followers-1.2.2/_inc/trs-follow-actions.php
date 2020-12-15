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
 * Catches clicks on a "Follow" button and tries to make that happen.
 *
 * @uses check_admin_referer() Checks to make sure the TRM security nonce matches.
 * @uses trs_follow_is_following() Checks to see if a user is following another user already.
 * @uses trs_follow_start_following() Starts a user following another user.
 * @uses trs_core_add_message() Adds an error/success message to be displayed after redirect.
 * @uses trs_core_redirect() Safe redirects the user to a particular URL.
 */
function trs_follow_action_start() {
	global $trs;

	if ( ! trs_is_current_component( $trs->follow->followers->slug ) || ! trs_is_current_action( 'start' ) ) {
		return;
	}

	if ( trs_displayed_user_id() == trs_loggedin_user_id() ) {
		return;
	}

	check_admin_referer( 'start_following' );

	if ( trs_follow_is_following( array( 'leader_id' => trs_displayed_user_id(), 'follower_id' => trs_loggedin_user_id() ) ) ) {
		trs_core_add_message( sprintf( __( 'You are already following %s.', 'trs-follow' ), trs_get_displayed_user_fullname() ), 'error' );

	} else {
		if ( ! trs_follow_start_following( array( 'leader_id' => trs_displayed_user_id(), 'follower_id' => trs_loggedin_user_id() ) ) ) {
			trs_core_add_message( sprintf( __( 'There was a problem when trying to follow %s, please try again.', 'trs-follow' ), trs_get_displayed_user_fullname() ), 'error' );
		} else {
			trs_core_add_message( sprintf( __( 'You are now following %s.', 'trs-follow' ), trs_get_displayed_user_fullname() ) );
		}
	}

	// it's possible that trm_get_referer() returns false, so let's fallback to the displayed user's page
	$redirect = trm_get_referer() ? trm_get_referer() : trs_displayed_user_domain();
	trs_core_redirect( $redirect );
}
add_action( 'trs_actions', 'trs_follow_action_start' );

/**
 * Catches clicks on a "Unfollow" button and tries to make that happen.
 *
 * @uses check_admin_referer() Checks to make sure the TRM security nonce matches.
 * @uses trs_follow_is_following() Checks to see if a user is following another user already.
 * @uses trs_follow_stop_following() Stops a user following another user.
 * @uses trs_core_add_message() Adds an error/success message to be displayed after redirect.
 * @uses trs_core_redirect() Safe redirects the user to a particular URL.
 */
function trs_follow_action_stop() {
	global $trs;

	if ( ! trs_is_current_component( $trs->follow->followers->slug ) || ! trs_is_current_action( 'stop' ) ) {
		return;
	}

	if ( trs_displayed_user_id() == trs_loggedin_user_id() ) {
		return;
	}

	check_admin_referer( 'stop_following' );

	if ( ! trs_follow_is_following( array( 'leader_id' => trs_displayed_user_id(), 'follower_id' => trs_loggedin_user_id() ) ) ) {
		trs_core_add_message( sprintf( __( 'You are not following %s.', 'trs-follow' ), trs_get_displayed_user_fullname() ), 'error' );

	} else {
		if ( ! trs_follow_stop_following( array( 'leader_id' => trs_displayed_user_id(), 'follower_id' => trs_loggedin_user_id() ) ) ) {
			trs_core_add_message( sprintf( __( 'There was a problem when trying to stop following %s, please try again.', 'trs-follow' ), trs_get_displayed_user_fullname() ), 'error' );
		} else {
			trs_core_add_message( sprintf( __( 'You are no longer following %s.', 'trs-follow' ), trs_get_displayed_user_fullname() ) );
		}
	}

	// it's possible that trm_get_referer() returns false, so let's fallback to the displayed user's page
	$redirect = trm_get_referer() ? trm_get_referer() : trs_displayed_user_domain();
	trs_core_redirect( $redirect );
}
add_action( 'trs_actions', 'trs_follow_action_stop' );

/**
 * Add RSS feed support for a user's following activity.
 *
 * eg. example.com/members/USERNAME/activity/following/feed/
 *
 * Only available in trendr 1.8+.
 *
 * @since 1.2.1
 * @author r-a-y
 */
function trs_follow_my_following_feed() {
	// only available in TRS 1.8+
	if ( ! class_exists( 'TRS_Activity_Feed' ) ) {
		return;
	}

	if ( ! trs_is_user_activity() || ! trs_is_current_action( constant( 'TRS_FOLLOWING_SLUG' ) ) || ! trs_is_action_variable( 'feed', 0 ) ) {
		return false;
	}

	global $trs;

	// setup the feed
	$trs->activity->feed = new TRS_Activity_Feed( array(
		'id'            => 'myfollowing',

		/* translators: User's following activity RSS title - "[Site Name] | [User Display Name] | Following Activity" */
		'title'         => sprintf( __( '%1$s | %2$s | Following Activity', 'trs-follow' ), trs_get_site_name(), trs_get_displayed_user_fullname() ),

		'link'          => trailingslashit( trs_displayed_user_domain() . trs_get_activity_slug() . '/' . constant( 'TRS_FOLLOWING_SLUG' ) ),
		'description'   => sprintf( __( "Activity feed for people that %s is following.", 'trendr' ), trs_get_displayed_user_fullname() ),
		'activity_args' => array(
			'user_id'  => trs_get_following_ids(),
			'display_comments' => 'threaded'
		)
	) );
}
add_action( 'trs_actions', 'trs_follow_my_following_feed' );

/** AJAX ACTIONS ***************************************************/

/**
 * AJAX callback when clicking on the "Follow" button to follow a user.
 *
 * @uses check_admin_referer() Checks to make sure the TRM security nonce matches.
 * @uses trs_follow_start_following() Starts a user following another user.
 * @uses trs_follow_is_following() Checks to see if a user is following another user already.
 */
function trs_follow_ajax_action_start() {

	check_admin_referer( 'start_following' );

	// successful follow
	if ( trs_follow_start_following( array( 'leader_id' => $_POST['uid'], 'follower_id' => trs_loggedin_user_id() ) ) ) {
		// output unfollow button
		$output = trs_follow_get_add_follow_button( array(
			'leader_id'   => $_POST['uid'],
			'follower_id' => trs_loggedin_user_id(),
			'wrapper'     => false
		) );

	// failed follow
	} else {
		// output fallback invalid button
		$args = array(
			'id'        => 'invalid',
			'link_href' => 'javascript:;',
			'component' => 'follow',
			'wrapper'   => false
		);

		if ( trs_follow_is_following( array( 'leader_id' => $_POST['uid'], 'follower_id' => trs_loggedin_user_id() ) ) ) {
			$output = trs_get_button( array_merge(
				array( 'link_text' => __( 'Already following', 'trs-follow' ) ),
				$args
			) );
		} else {
			$output = trs_get_button( array_merge(
				array( 'link_text' => __( 'Error following user', 'trs-follow' ) ),
				$args
			) );
		}
	}

	echo $output;

	exit();
}
add_action( 'trm_ajax_trs_follow', 'trs_follow_ajax_action_start' );

/**
 * AJAX callback when clicking on the "Unfollow" button to unfollow a user.
 *
 * @uses check_admin_referer() Checks to make sure the TRM security nonce matches.
 * @uses trs_follow_stop_following() Stops a user following another user.
 * @uses trs_follow_is_following() Checks to see if a user is following another user already.
 */
function trs_follow_ajax_action_stop() {

	check_admin_referer( 'stop_following' );

	// successful unfollow
	if ( trs_follow_stop_following( array( 'leader_id' => $_POST['uid'], 'follower_id' => trs_loggedin_user_id() ) ) ) {
		// output follow button
		$output = trs_follow_get_add_follow_button( array(
			'leader_id'   => $_POST['uid'],
			'follower_id' => trs_loggedin_user_id(),
			'wrapper'     => false
		) );

	// failed unfollow
	} else {
		// output fallback invalid button
		$args = array(
			'id'        => 'invalid',
			'link_href' => 'javascript:;',
			'component' => 'follow',
			'wrapper'   => false
		);

		if ( ! trs_follow_is_following( array( 'leader_id' => $_POST['uid'], 'follower_id' => trs_loggedin_user_id() ) ) ) {
			$output = trs_get_button( array_merge(
				array( 'link_text' => __( 'Not following', 'trs-follow' ) ),
				$args
			) );

		} else {
			$output = trs_get_button( array_merge(
				array( 'link_text' => __( 'Error unfollowing user', 'trs-follow' ) ),
				$args
			) );

		}
	}

	echo $output;

	exit();
}
add_action( 'trm_ajax_trs_unfollow', 'trs_follow_ajax_action_stop' );
