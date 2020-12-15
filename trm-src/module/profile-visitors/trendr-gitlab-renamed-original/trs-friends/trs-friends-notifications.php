<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function friends_notification_new_request( $friendship_id, $initiator_id, $friend_id ) {
	global $trs;

	$initiator_name = trs_core_get_user_displayname( $initiator_id );

	if ( 'no' == trs_get_user_meta( (int)$friend_id, 'notification_friends_friendship_request', true ) )
		return false;

	$ud = get_userdata( $friend_id );
	$initiator_ud = get_userdata( $initiator_id );

	$all_requests_link = trs_core_get_user_domain( $friend_id ) . trs_get_friends_slug() . '/requests/';
	$settings_link = trs_core_get_user_domain( $friend_id ) .  trs_get_settings_slug() . '/notifications';

	$initiator_link = trs_core_get_user_domain( $initiator_id );

	// Set up and send the message
	$to       = $ud->user_email;
	$sitename = trm_specialchars_decode( get_blog_option( trs_get_root_blog_id(), 'blogname' ), ENT_QUOTES );
	$subject  = '[' . $sitename . '] ' . sprintf( __( 'New friendship request from %s', 'trendr' ), $initiator_name );

	$message = sprintf( __(
'%1$s wants to add you as a friend.

To view all of your pending friendship requests: %2$s

To view %3$s\'s profile: %4$s

---------------------
', 'trendr' ), $initiator_name, $all_requests_link, $initiator_name, $initiator_link );

	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );

	/* Send the message */
	$to = apply_filters( 'friends_notification_new_request_to', $to );
	$subject = apply_filters( 'friends_notification_new_request_subject', $subject, $initiator_name );
	$message = apply_filters( 'friends_notification_new_request_message', $message, $initiator_name, $initiator_link, $all_requests_link, $settings_link );

	trm_mail( $to, $subject, $message );

	do_action( 'trs_friends_sent_request_email', $friend_id, $subject, $message, $friendship_id, $initiator_id );
}

function friends_notification_accepted_request( $friendship_id, $initiator_id, $friend_id ) {
	global $trs;

	$friendship = new TRS_Friends_Friendship( $friendship_id, false, false );

	$friend_name = trs_core_get_user_displayname( $friend_id );

	if ( 'no' == trs_get_user_meta( (int)$initiator_id, 'notification_friends_friendship_accepted', true ) )
		return false;

	$ud = get_userdata( $initiator_id );

	$friend_link = trs_core_get_user_domain( $friend_id );
	$settings_link = trs_core_get_user_domain( $initiator_id ) . trs_get_settings_slug() . '/notifications';

	// Set up and send the message
	$to       = $ud->user_email;
	$sitename = trm_specialchars_decode( get_blog_option( trs_get_root_blog_id(), 'blogname' ), ENT_QUOTES );
	$subject  = '[' . $sitename . '] ' . sprintf( __( '%s accepted your friendship request', 'trendr' ), $friend_name );

	$message = sprintf( __(
'%1$s accepted your friend request.

To view %2$s\'s profile: %3$s

---------------------
', 'trendr' ), $friend_name, $friend_name, $friend_link );

	$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );

	/* Send the message */
	$to = apply_filters( 'friends_notification_accepted_request_to', $to );
	$subject = apply_filters( 'friends_notification_accepted_request_subject', $subject, $friend_name );
	$message = apply_filters( 'friends_notification_accepted_request_message', $message, $friend_name, $friend_link, $settings_link );

	trm_mail( $to, $subject, $message );

	do_action( 'trs_friends_sent_accepted_email', $initiator_id, $subject, $message, $friendship_id, $friend_id );
}

?>