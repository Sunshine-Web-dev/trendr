<?php
if ( !defined( 'TRSB_VERSION' ) ) exit;

/**
 * Friend Check
 * Removed the Add Friend button if the user is blocking us.
 * @since 1.0
 * @version 1.0
 */
function trsb_friend_check( $status, $user_id ) {
	$list = trsb_get_blocked_users( $user_id );
	if ( in_array( trs_loggedin_user_id(), (array) $list ) ) return false;
	return $status;
}

/**
 * Add Friend Action
 * Replicate the friends_action_add_friend() with a custom check
 * to prevent users from constructing new friendship requests to users
 * who block them. Used since even though the button is not shown, you can still
 * construct a request though the URL.
 * @since 1.0
 * @version 1.0
 */
function trsb_friends_action_add_friend() {
	if ( !trs_is_friends_component() || !trs_is_current_action( 'add-friend' ) )
		return false;

	if ( !$potential_friend_id = (int)trs_action_variable( 0 ) )
		return false;

	if ( $potential_friend_id == trs_loggedin_user_id() )
		return false;

	$list = trsb_get_blocked_users( $potential_friend_id );
	if ( in_array( trs_loggedin_user_id(), $list ) ) return false;

	$friendship_status = TRS_Friends_Friendship::check_is_friend( trs_loggedin_user_id(), $potential_friend_id );

	if ( 'not_friends' == $friendship_status ) {

		if ( !check_admin_referer( 'friends_add_friend' ) )
			return false;

		if ( !friends_add_friend( trs_loggedin_user_id(), $potential_friend_id ) ) {
			trs_core_add_message( __( 'Friendship could not be requested.', 'trendr' ), 'error' );
		} else {
			trs_core_add_message( __( 'Friendship requested', 'trendr' ) );
		}

	} else if ( 'is_friend' == $friendship_status ) {
		trs_core_add_message( __( 'You are already friends with this user', 'trendr' ), 'error' );
	} else {
		trs_core_add_message( __( 'You already have a pending friendship request with this user', 'trendr' ), 'error' );
	}

	trs_core_redirect( trm_get_referer() );

	return false;
}
?>