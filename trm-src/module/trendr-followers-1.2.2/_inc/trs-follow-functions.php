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
 * Start following a user's activity.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int $leader_id The user ID of the person we want to follow.
 *     @type int $follower_id The user ID initiating the follow request.
 * }
 * @return bool
 */
function trs_follow_start_following( $args = '' ) {
	global $trs;

	$r = trm_parse_args( $args, array(
		'leader_id'   => trs_displayed_user_id(),
		'follower_id' => trs_loggedin_user_id()
	) );

	$follow = new TRS_Follow( $r['leader_id'], $r['follower_id'] );

	// existing follow already exists
	if ( ! empty( $follow->id ) ) {
		return false;
	}

	if ( ! $follow->save() ) {
		return false;
	}

	do_action_ref_array( 'trs_follow_start_following', array( &$follow ) );

	return true;
}

/**
 * Stop following a user's activity.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int $leader_id The user ID of the person we want to stop following.
 *     @type int $follower_id The user ID initiating the unfollow request.
 * }
 * @return bool
 */
function trs_follow_stop_following( $args = '' ) {

	$r = trm_parse_args( $args, array(
		'leader_id'   => trs_displayed_user_id(),
		'follower_id' => trs_loggedin_user_id()
	) );

	$follow = new TRS_Follow( $r['leader_id'], $r['follower_id'] );

	if ( empty( $follow->id ) || ! $follow->delete() ) {
		return false;
	}

	do_action_ref_array( 'trs_follow_stop_following', array( &$follow ) );

	return true;
}

/**
 * Check if a user is already following another user.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int $leader_id The user ID of the person we want to check.
 *     @type int $follower_id The user ID initiating the follow request.
 * }
 * @return bool
 */
function trs_follow_is_following( $args = '' ) {

	$r = trm_parse_args( $args, array(
		'leader_id'   => trs_displayed_user_id(),
		'follower_id' => trs_loggedin_user_id()
	) );

	$follow = new TRS_Follow( $r['leader_id'], $r['follower_id'] );

	return apply_filters( 'trs_follow_is_following', (int)$follow->id, $follow );
}

/**
 * Fetch the user IDs of all the followers of a particular user.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int $user_id The user ID to get followers for.
 * }
 * @return array
 */
function trs_follow_get_followers( $args = '' ) {

	$r = trm_parse_args( $args, array(
		'user_id' => trs_displayed_user_id()
	) );

	return apply_filters( 'trs_follow_get_followers', TRS_Follow::get_followers( $r['user_id'] ) );
}

/**
 * Fetch the user IDs of all the users a particular user is following.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int $user_id The user ID to fetch following user IDs for.
 * }
 * @return array
 */
function trs_follow_get_following( $args = '' ) {

	$r = trm_parse_args( $args, array(
		'user_id' => trs_displayed_user_id()
	) );

	return apply_filters( 'trs_follow_get_following', TRS_Follow::get_following( $r['user_id'] ) );
}

/**
 * Get the total followers and total following counts for a user.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of arguments.
 *     @type int $user_id The user ID to grab follow counts for.
 * }
 * @return array [ followers => int, following => int ]
 */
function trs_follow_total_follow_counts( $args = '' ) {

	$r = trm_parse_args( $args, array(
		'user_id' => trs_loggedin_user_id()
	) );

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
 * @since 1.0.0
 *
 * @uses TRS_Follow::delete_all_for_user() Deletes user ID from all following / follower records
 */
function trs_follow_remove_data( $user_id ) {
	do_action( 'trs_follow_before_remove_data', $user_id );

	TRS_Follow::delete_all_for_user( $user_id );

	do_action( 'trs_follow_remove_data', $user_id );
}
add_action( 'trmmu_delete_user',	'trs_follow_remove_data' );
add_action( 'delete_user',	'trs_follow_remove_data' );
add_action( 'make_spam_user',	'trs_follow_remove_data' );
