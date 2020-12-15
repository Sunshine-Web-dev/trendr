<?php
/**
 * Action functions are exactly the same as screen functions, however they do not
 * have a template screen associated with them. Usually they will send the user
 * back to the default screen after execution.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function friends_action_add_friend() {
	if ( !trs_is_friends_component() || !trs_is_current_action( 'add-friend' ) )
		return false;

	if ( !$potential_friend_id = (int)trs_action_variable( 0 ) )
		return false;

	if ( $potential_friend_id == trs_loggedin_user_id() )
		return false;

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
add_action( 'trs_init', 'friends_action_add_friend' );

function friends_action_remove_friend() {
	if ( !trs_is_friends_component() || !trs_is_current_action( 'remove-friend' ) )
		return false;

	if ( !$potential_friend_id = (int)trs_action_variable( 0 ) )
		return false;

	if ( $potential_friend_id == trs_loggedin_user_id() )
		return false;

	$friendship_status = TRS_Friends_Friendship::check_is_friend( trs_loggedin_user_id(), $potential_friend_id );

	if ( 'is_friend' == $friendship_status ) {

		if ( !check_admin_referer( 'friends_remove_friend' ) )
			return false;

		if ( !friends_remove_friend( trs_loggedin_user_id(), $potential_friend_id ) ) {
			trs_core_add_message( __( 'Friendship could not be canceled.', 'trendr' ), 'error' );
		} else {
			trs_core_add_message( __( 'Friendship canceled', 'trendr' ) );
		}

	} else if ( 'is_friends' == $friendship_status ) {
		trs_core_add_message( __( 'You are not yet friends with this user', 'trendr' ), 'error' );
	} else {
		trs_core_add_message( __( 'You have a pending friendship request with this user', 'trendr' ), 'error' );
	}

	trs_core_redirect( trm_get_referer() );

	return false;
}
add_action( 'trs_init', 'friends_action_remove_friend' );

?>