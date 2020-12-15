<?php
/********************************************************************************
 * Business Functions
 *
 * Business functions are where all the magic happens in trendr. They will
 * handle the actual saving or manipulation of information. Usually they will
 * hand off to a database class for data access, then return
 * true or false on success or failure.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function friends_add_friend( $initiator_userid, $friend_userid, $force_accept = false ) {
	global $trs;

	$friendship = new TRS_Friends_Friendship;

	if ( (int)$friendship->is_confirmed )
		return true;

	$friendship->initiator_user_id = $initiator_userid;
	$friendship->friend_user_id    = $friend_userid;
	$friendship->is_confirmed      = 0;
	$friendship->is_limited        = 0;
	$friendship->date_created      = trs_core_current_time();

	if ( $force_accept )
		$friendship->is_confirmed = 1;

	if ( $friendship->save() ) {

		if ( !$force_accept ) {
			// Add the on screen notification
			trs_core_add_notification( $friendship->initiator_user_id, $friendship->friend_user_id, $trs->friends->id, 'friendship_request' );

			// Send the email notification
			friends_notification_new_request( $friendship->id, $friendship->initiator_user_id, $friendship->friend_user_id );

			do_action( 'friends_friendship_requested', $friendship->id, $friendship->initiator_user_id, $friendship->friend_user_id );
		} else {
			do_action( 'friends_friendship_accepted', $friendship->id, $friendship->initiator_user_id, $friendship->friend_user_id );
		}

		return true;
	}

	return false;
}

function friends_remove_friend( $initiator_userid, $friend_userid ) {
	global $trs;

	$friendship_id = TRS_Friends_Friendship::get_friendship_id( $initiator_userid, $friend_userid );
	$friendship    = new TRS_Friends_Friendship( $friendship_id );

	do_action( 'friends_before_friendship_delete', $friendship_id, $initiator_userid, $friend_userid );

	// Remove the activity stream item for the user who canceled the friendship
	friends_delete_activity( array( 'item_id' => $friendship_id, 'type' => 'friendship_accepted', 'user_id' => $trs->displayed_user->id ) );

	do_action( 'friends_friendship_deleted', $friendship_id, $initiator_userid, $friend_userid );

	if ( $friendship->delete() ) {
		friends_update_friend_totals( $initiator_userid, $friend_userid, 'remove' );

		return true;
	}

	return false;
}

function friends_accept_friendship( $friendship_id ) {
	global $trs;

	$friendship = new TRS_Friends_Friendship( $friendship_id, true, false );

	if ( !$friendship->is_confirmed && TRS_Friends_Friendship::accept( $friendship_id ) ) {
		friends_update_friend_totals( $friendship->initiator_user_id, $friendship->friend_user_id );

		// Remove the friend request notice
		trs_core_delete_notifications_by_item_id( $friendship->friend_user_id, $friendship->initiator_user_id, $trs->friends->id, 'friendship_request' );

		// Add a friend accepted notice for the initiating user
		trs_core_add_notification( $friendship->friend_user_id, $friendship->initiator_user_id, $trs->friends->id, 'friendship_accepted' );

		$initiator_link = trs_core_get_userlink( $friendship->initiator_user_id );
		$friend_link = trs_core_get_userlink( $friendship->friend_user_id );

		// Record in activity streams for the initiator
		//friends_record_activity( array(
			//'user_id'           => $friendship->initiator_user_id,
			//'type'              => 'friendship_created',
			//'action'            => apply_filters( 'friends_activity_friendship_accepted_action', sprintf( __( '%1$s and %2$s are now friends', 'trendr' ), $initiator_link, $friend_link ), $friendship ),
		//	'item_id'           => $friendship_id,
			//'secondary_item_id' => $friendship->friend_user_id
		//) );

		// Record in activity streams for the friend
	//	friends_record_activity( array(
		//	'user_id'           => $friendship->friend_user_id,
		//	'type'              => 'friendship_created',
			//'action'            => apply_filters( 'friends_activity_friendship_accepted_action', sprintf( __( '%1$s and %2$s are now friends', 'trendr' ), $friend_link, $initiator_link ), $friendship ),
		//	'item_id'           => $friendship_id,
			//'secondary_item_id' => $friendship->initiator_user_id,
		//	'hide_sitewide'     => true // We've already got the first entry site wide
	//	) );

		// Send the email notification
		friends_notification_accepted_request( $friendship->id, $friendship->initiator_user_id, $friendship->friend_user_id );

		do_action( 'friends_friendship_accepted', $friendship->id, $friendship->initiator_user_id, $friendship->friend_user_id );

		return true;
	}

	return false;
}

function friends_reject_friendship( $friendship_id ) {
	global $trs;

	$friendship = new TRS_Friends_Friendship( $friendship_id, true, false );

	if ( !$friendship->is_confirmed && TRS_Friends_Friendship::reject( $friendship_id ) ) {
		// Remove the friend request notice
		trs_core_delete_notifications_by_item_id( $friendship->friend_user_id, $friendship->initiator_user_id, $trs->friends->id, 'friendship_request' );

		do_action_ref_array( 'friends_friendship_rejected', array( $friendship_id, &$friendship ) );
		return true;
	}

	return false;
}

function friends_check_friendship( $user_id, $possible_friend_id ) {
	global $trs;

	if ( 'is_friend' == TRS_Friends_Friendship::check_is_friend( $user_id, $possible_friend_id ) )
		return true;

	return false;
}

// Returns - 'is_friend', 'not_friends', 'pending'
function friends_check_friendship_status( $user_id, $possible_friend_id ) {
	return TRS_Friends_Friendship::check_is_friend( $user_id, $possible_friend_id );
}

function friends_get_total_friend_count( $user_id = 0 ) {
	global $trs;

	if ( !$user_id )
		$user_id = ( $trs->displayed_user->id ) ? $trs->displayed_user->id : $trs->loggedin_user->id;

	if ( !$count = trm_cache_get( 'trs_total_friend_count_' . $user_id, 'trs' ) ) {
		$count = trs_get_user_meta( $user_id, 'total_friend_count', true );
		if ( empty( $count ) ) $count = 0;
		trm_cache_set( 'trs_total_friend_count_' . $user_id, $count, 'trs' );
	}

	return apply_filters( 'friends_get_total_friend_count', $count );
}

function friends_check_user_has_friends( $user_id ) {
	$friend_count = friends_get_total_friend_count( $user_id );

	if ( empty( $friend_count ) )
		return false;

	if ( !(int)$friend_count )
		return false;

	return true;
}

function friends_get_friendship_id( $initiator_user_id, $friend_user_id ) {
	return TRS_Friends_Friendship::get_friendship_id( $initiator_user_id, $friend_user_id );
}

function friends_get_friend_user_ids( $user_id, $friend_requests_only = false, $assoc_arr = false, $filter = false ) {
	return TRS_Friends_Friendship::get_friend_user_ids( $user_id, $friend_requests_only, $assoc_arr );
}

function friends_search_friends( $search_terms, $user_id, $pag_num = 10, $pag_page = 1 ) {
	return TRS_Friends_Friendship::search_friends( $search_terms, $user_id, $pag_num, $pag_page );
}

function friends_get_friendship_request_user_ids( $user_id ) {
	return TRS_Friends_Friendship::get_friendship_request_user_ids( $user_id );
}

function friends_get_recently_active( $user_id, $per_page = 0, $page = 0, $filter = '' ) {
	return apply_filters( 'friends_get_recently_active', TRS_Core_User::get_users( 'active', $per_page, $page, $user_id, $filter ) );
}

function friends_get_alphabetically( $user_id, $per_page = 0, $page = 0, $filter = '' ) {
	return apply_filters( 'friends_get_alphabetically', TRS_Core_User::get_users( 'alphabetical', $per_page, $page, $user_id, $filter ) );
}

function friends_get_newest( $user_id, $per_page = 0, $page = 0, $filter = '' ) {
	return apply_filters( 'friends_get_newest', TRS_Core_User::get_users( 'newest', $per_page, $page, $user_id, $filter ) );
}

function friends_get_bulk_last_active( $friend_ids ) {
	return TRS_Friends_Friendship::get_bulk_last_active( $friend_ids );
}

/**
 * Get a list of friends that a user can invite into this group.
 * 
 * Excludes friends that are already in the group, and banned friends if the
 * user is not a group admin.
 *
 * @since 1.0
 * @param int $user_id User ID whose friends to see can be invited
 * @param int $group_id Group to check possible invitations against
 * @return mixed False if no friends, array of users if friends
 */
function friends_get_friends_invite_list( $user_id = 0, $group_id = 0 ) {

	// Default to logged in user id
	if ( empty( $user_id ) )
		$user_id = trs_loggedin_user_id();

	// Only group admins can invited previously banned users
	$user_is_admin = (bool) groups_is_user_admin( $user_id, $group_id );

	// Assume no friends
	$friends = array();

	// Default args
	$args = apply_filters( 'trs_friends_pre_get_invite_list', array(
		'user_id'  => $user_id,
		'type'     => 'alphabetical',
		'per_page' => 0
	) );

	// User has friends
	if ( trs_has_members( $args ) ) {

		/**
		 * Loop through all friends and try to add them to the invitation list.
		 *
		 * Exclude friends that:
		 *     1. are already members of the group
		 *     2. are banned from this group if the current user is also not a
		 *        group admin.
		 */
		while ( trs_members() ) :

			// Load the member
			trs_the_member();

			// Get the user ID of the friend
			$friend_user_id = trs_get_member_user_id();

			// Skip friend if already in the group
			if ( groups_is_user_member( $friend_user_id, $group_id ) )
				continue;

			// Skip friend if not group admin and user banned from group
			if ( ( false === $user_is_admin ) && groups_is_user_banned( $friend_user_id, $group_id ) )
				continue;

			// Friend is safe, so add it to the array of possible friends
			$friends[] = array(
				'id'        => $friend_user_id,
				'full_name' => trs_get_member_name()
			);

		endwhile;
	}

	// If no friends, explicitly set to false
	if ( empty( $friends ) )
		$friends = false;

	// Allow friends to be filtered
	return apply_filters( 'trs_friends_get_invite_list', $friends, $user_id, $group_id );
}

function friends_count_invitable_friends( $user_id, $group_id ) {
	return TRS_Friends_Friendship::get_invitable_friend_count( $user_id, $group_id );
}

function friends_get_friend_count_for_user( $user_id ) {
	return TRS_Friends_Friendship::total_friend_count( $user_id );
}

function friends_search_users( $search_terms, $user_id, $pag_num = 0, $pag_page = 0 ) {
	global $trs;

	$user_ids = TRS_Friends_Friendship::search_users( $search_terms, $user_id, $pag_num, $pag_page );

	if ( !$user_ids )
		return false;

	for ( $i = 0, $count = count( $user_ids ); $i < $count; ++$i )
		$users[] = new TRS_Core_User( $user_ids[$i] );

	return array( 'users' => $users, 'count' => TRS_Friends_Friendship::search_users_count( $search_terms ) );
}

function friends_is_friendship_confirmed( $friendship_id ) {
	$friendship = new TRS_Friends_Friendship( $friendship_id );
	return $friendship->is_confirmed;
}

function friends_update_friend_totals( $initiator_user_id, $friend_user_id, $status = 'add' ) {
	global $trs;

	if ( 'add' == $status ) {
		trs_update_user_meta( $initiator_user_id, 'total_friend_count', (int)trs_get_user_meta( $initiator_user_id, 'total_friend_count', true ) + 1 );
		trs_update_user_meta( $friend_user_id, 'total_friend_count', (int)trs_get_user_meta( $friend_user_id, 'total_friend_count', true ) + 1 );
	} else {
		trs_update_user_meta( $initiator_user_id, 'total_friend_count', (int)trs_get_user_meta( $initiator_user_id, 'total_friend_count', true ) - 1 );
		trs_update_user_meta( $friend_user_id, 'total_friend_count', (int)trs_get_user_meta( $friend_user_id, 'total_friend_count', true ) - 1 );
	}
}

function friends_remove_data( $user_id ) {
	global $trs;

	do_action( 'friends_before_remove_data', $user_id );

	TRS_Friends_Friendship::delete_all_for_user($user_id);

	// Remove usermeta
	trs_delete_user_meta( $user_id, 'total_friend_count' );

	// Remove friendship requests FROM user
	trs_core_delete_notifications_from_user( $user_id, $trs->friends->id, 'friendship_request' );

	do_action( 'friends_remove_data', $user_id );
}
add_action( 'trmmu_delete_user',  'friends_remove_data' );
add_action( 'delete_user',       'friends_remove_data' );
add_action( 'trs_make_spam_user', 'friends_remove_data' );

?>