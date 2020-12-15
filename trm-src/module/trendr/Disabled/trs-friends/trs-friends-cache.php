<?php
/**
 * Caching functions handle the clearing of cached objects and pages on specific
 * actions throughout trendr.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function friends_clear_friend_object_cache( $friendship_id ) {
	if ( !$friendship = new TRS_Friends_Friendship( $friendship_id ) )
		return false;

	trm_cache_delete( 'friends_friend_ids_' .    $friendship->initiator_user_id, 'trs' );
	trm_cache_delete( 'friends_friend_ids_' .    $friendship->friend_user_id,    'trs' );
	trm_cache_delete( 'trs_total_friend_count_' . $friendship->initiator_user_id, 'trs' );
	trm_cache_delete( 'trs_total_friend_count_' . $friendship->friend_user_id,    'trs' );
}

function friends_clear_friend_notifications() {
	global $trs;

	if ( isset( $_GET['new'] ) )
		trs_core_delete_notifications_by_type( $trs->loggedin_user->id, $trs->friends->id, 'friendship_accepted' );
}
add_action( 'trs_activity_screen_my_activity', 'friends_clear_friend_notifications' );

// List actions to clear object caches on
add_action( 'friends_friendship_accepted', 'friends_clear_friend_object_cache' );
add_action( 'friends_friendship_deleted',  'friends_clear_friend_object_cache' );

// List actions to clear super cached pages on, if super cache is installed
add_action( 'friends_friendship_rejected',  'trs_core_clear_cache' );
add_action( 'friends_friendship_accepted',  'trs_core_clear_cache' );
add_action( 'friends_friendship_deleted',   'trs_core_clear_cache' );
add_action( 'friends_friendship_requested', 'trs_core_clear_cache' );

?>