<?php
/**
 * TRS Follow Functions
 *
 * @package TRS-Follow
 * @sutrsackage Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Start following a user's activity
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses trm_parse_args() Parses arguments from an array or request string.
 * @param $args/leader_id - user ID of user to follow
 * @param $args/follower_id - user ID of the user who follows
 * @return bool
 */
function trs_follow_start_following( $args = '' ) {
	global $trs;

	$defaults = array(
		'leader_id'   => trs_displayed_user_id(),
		'follower_id' => trs_loggedin_user_id()
	);

	$r = trm_parse_args( $args, $defaults );

	$follow = new TRS_Follow;
	$follow->leader_id   = (int) $r['leader_id'];
	$follow->follower_id = (int) $r['follower_id'];

	if ( ! $follow->save() )
		return false;

	// Add a screen count notification
	trs_core_add_notification(
		$r['follower_id'],
		$r['leader_id'],
		$trs->follow->id,
		'new_follow'
	);

	// Add a more specific email notification
	trs_follow_new_follow_email_notification( array(
		'leader_id'   => $r['leader_id'],
		'follower_id' => $r['follower_id']
	) );

	do_action_ref_array( 'trs_follow_start_following', array( &$follow ) );

	return true;
}

/**
 * Stop following a user's activity
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses trm_parse_args() Parses arguments from an array or request string.
 * @param $args/leader_id - user ID of user to stop following
 * @param $args/follower_id - user ID of the user who wants to stop following
 * @return bool
 */
function trs_follow_stop_following( $args = '' ) {

	$defaults = array(
		'leader_id'   => trs_displayed_user_id(),
		'follower_id' => trs_loggedin_user_id()
	);

	$r = trm_parse_args( $args, $defaults );

	$follow = new TRS_Follow( $r['leader_id'], $r['follower_id'] );

	if ( ! $follow->delete() )
		return false;

	do_action_ref_array( 'trs_follow_stop_following', array( &$follow ) );

	return true;
}

/**
 * Check if a user is already following another user.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses trm_parse_args() Parses arguments from an array or request string.
 * @param $args/leader_id - user ID of user to check is being followed
 * @param $args/follower_id - user ID of the user who is doing the following
 * @return bool
 */
function trs_follow_is_following( $args = '' ) {

	$defaults = array(
		'leader_id'   => trs_displayed_user_id(),
		'follower_id' => trs_loggedin_user_id()
	);

	$r = trm_parse_args( $args, $defaults );

	$follow = new TRS_Follow( $r['leader_id'], $r['follower_id'] );

	return apply_filters( 'trs_follow_is_following', (int)$follow->id, $follow );
}

/**
 * Fetch the user_ids of all the followers of a particular user.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses trm_parse_args() Parses arguments from an array or request string.
 * @param $args/user_id - the user ID of the user to get followers for.
 * @return array of user ids
 */
function trs_follow_get_followers( $args = '' ) {

	$defaults = array(
		'user_id' => trs_displayed_user_id()
	);

	$r = trm_parse_args( $args, $defaults );

	return apply_filters( 'trs_follow_get_followers', TRS_Follow::get_followers( $r['user_id'] ) );
}

/**
 * Fetch the user_ids of all the users a particular user is following.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses trm_parse_args() Parses arguments from an array or request string.
 * @param $args/user_id - the user ID of the user to get a list of users followed for.
 * @return array of user ids
 */
function trs_follow_get_following( $args = '' ) {

	$defaults = array(
		'user_id' => trs_displayed_user_id()
	);

	$r = trm_parse_args( $args, $defaults );

	return apply_filters( 'trs_follow_get_following', TRS_Follow::get_following( $r['user_id'] ) );
}

/**
 * Get the total followers and total following counts for a user.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses trm_parse_args() Parses arguments from an array or request string.
 * @param $args/user_id - the user ID of the user to get counts for.
 * @return array [ followers => int, following => int ]
 */
function trs_follow_total_follow_counts( $args = '' ) {

	$defaults = array(
		'user_id' => trs_loggedin_user_id()
	);

	$r = trm_parse_args( $args, $defaults );

	$count = false;

	/* try to get locally-cached values first */

	// logged-in user
	if ( $r['user_id'] == trs_loggedin_user_id() && is_user_logged_in() ) {
		global $trs;

		if ( ! empty( $trs->loggedin_user->total_follow_counts ) ) {
			$count = $trs->loggedin_user->total_follow_counts;
		}

	// displayed user
	} elseif ( $r['user_id'] == trs_displayed_user_id() && trs_is_user() ) {
		global $trs;
		
		if ( ! empty( $trs->displayed_user->total_follow_counts ) ) {
			$count = $trs->displayed_user->total_follow_counts;
		}
	}

	// no cached value, so query for it
	if ( $count === false ) {
		$count = TRS_Follow::get_counts( $r['user_id'] );
	}

	return apply_filters( 'trs_follow_total_follow_counts', $count, $r['user_id'] );
}

/**
 * Removes follow relationships for all users from a user who is deleted or spammed
 *
 * @uses TRS_Follow::delete_all_for_user() Deletes user ID from all following / follower records
 */
function trs_follow_remove_data( $user_id ) {
	global $trs;

	do_action( 'trs_follow_before_remove_data', $user_id );

	TRS_Follow::delete_all_for_user( $user_id );

	// Remove following notifications from user
	trs_core_delete_notifications_from_user( $user_id, $trs->follow->id, 'new_follow' );

	do_action( 'trs_follow_remove_data', $user_id );
}
add_action( 'trmmu_delete_user',	'trs_follow_remove_data' );
add_action( 'delete_user',	'trs_follow_remove_data' );
add_action( 'make_spam_user',	'trs_follow_remove_data' );
