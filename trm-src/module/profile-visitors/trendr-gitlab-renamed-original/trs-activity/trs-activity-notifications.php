<?php

/**
 * trendr Activity Notifications
 *
 * @package trendr
 * @sutrsackage ActivityNotifications
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Sends an email notification and a TRS notification when someone mentions you in an update
 *
 * @since 1.2.0
 *
 * @param int $activity_id The id of the activity update
 * @param int $receiver_user_id The unique user_id of the user who is receiving the update
 *
 * @global object $trs trendr global settings
 * @uses trs_core_add_notification()
 * @uses trs_get_user_meta()
 * @uses trs_core_get_user_displayname()
 * @uses trs_activity_get_permalink()
 * @uses trs_core_get_user_domain()
 * @uses trs_get_settings_slug()
 * @uses trs_activity_filter_kses()
 * @uses trs_core_get_core_userdata()
 * @uses trm_specialchars_decode()
 * @uses get_blog_option()
 * @uses trs_is_active()
 * @uses trs_is_group()
 * @uses trs_get_current_group_name()
 * @uses apply_filters() To call the 'trs_activity_at_message_notification_to' hook
 * @uses apply_filters() To call the 'trs_activity_at_message_notification_subject' hook
 * @uses apply_filters() To call the 'trs_activity_at_message_notification_message' hook
 * @uses trm_mail()
 * @uses do_action() To call the 'trs_activity_sent_mention_email' hook
 */
function trs_activity_at_message_notification( $activity_id, $receiver_user_id ) {
	global $trs;

	$activity = new TRS_Activity_Activity( $activity_id );

	$subject = '';
	$message = '';

	// Add the TRS notification
	trs_core_add_notification( $activity_id, $receiver_user_id, 'activity', 'new_at_mention', $activity->user_id );

	// Now email the user with the contents of the message (if they have enabled email notifications)
	if ( 'no' != trs_get_user_meta( $receiver_user_id, 'notification_activity_new_mention', true ) ) {
		$poster_name = trs_core_get_user_displayname( $activity->user_id );

		$message_link = trs_activity_get_permalink( $activity_id );
		$settings_link = trs_core_get_user_domain( $receiver_user_id ) . trs_get_settings_slug() . '/notifications/';

		$poster_name = stripslashes( $poster_name );
		$content = trs_activity_filter_kses( strip_tags( stripslashes( $activity->content ) ) );

		// Set up and send the message
		$ud       = trs_core_get_core_userdata( $receiver_user_id );
		$to       = $ud->user_email;
		$sitename = trm_specialchars_decode( get_blog_option( trs_get_root_blog_id(), 'blogname' ), ENT_QUOTES );
		$subject  = '[' . $sitename . '] ' . sprintf( __( '%s mentioned you in an update', 'trendr' ), $poster_name );

		if ( trs_is_active( 'groups' ) && trs_is_group() ) {
			$message = sprintf( __(
'%1$s mentioned you in the group "%2$s":

"%3$s"

To view and respond to the message, log in and visit: %4$s

---------------------
', 'trendr' ), $poster_name, trs_get_current_group_name(), $content, $message_link );
		} else {
			$message = sprintf( __(
'%1$s mentioned you in an update:

"%2$s"

To view and respond to the message, log in and visit: %3$s

---------------------
', 'trendr' ), $poster_name, $content, $message_link );
		}

		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );

		/* Send the message */
		$to 	 = apply_filters( 'trs_activity_at_message_notification_to', $to );
		$subject = apply_filters( 'trs_activity_at_message_notification_subject', $subject, $poster_name );
		$message = apply_filters( 'trs_activity_at_message_notification_message', $message, $poster_name, $content, $message_link, $settings_link );

		trm_mail( $to, $subject, $message );
	}

	do_action( 'trs_activity_sent_mention_email', $activity, $subject, $message, $content );
}

/**
 * Sends an email notification and a TRS notification when someone mentions you in an update
 *
 * @since 1.2.0
 *
 * @param int $comment_id The comment id
 * @param int $commenter_id The unique user_id of the user who posted the comment
 * @param array $params {@link trs_activity_new_comment()}
 *
 * @global object $trs trendr global settings
 * @uses trs_get_user_meta()
 * @uses trs_core_get_user_displayname()
 * @uses trs_activity_get_permalink()
 * @uses trs_core_get_user_domain()
 * @uses trs_get_settings_slug()
 * @uses trs_activity_filter_kses()
 * @uses trs_core_get_core_userdata()
 * @uses trm_specialchars_decode()
 * @uses get_blog_option()
 * @uses trs_get_root_blog_id()
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_to' hook
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_subject' hook
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_message' hook
 * @uses trm_mail()
 * @uses do_action() To call the 'trs_activity_sent_reply_to_update_email' hook
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_comment_author_to' hook
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_comment_author_subject' hook
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_comment_author_message' hook
 * @uses do_action() To call the 'trs_activity_sent_reply_to_reply_email' hook
 */
function trs_activity_new_comment_notification( $comment_id, $commenter_id, $params ) {
	global $trs;

	extract( $params );

	$original_activity = new TRS_Activity_Activity( $activity_id );

	if ( $original_activity->user_id != $commenter_id && 'no' != trs_get_user_meta( $original_activity->user_id, 'notification_activity_new_reply', true ) ) {
		$poster_name = trs_core_get_user_displayname( $commenter_id );
		$thread_link = trs_activity_get_permalink( $activity_id );
		$settings_link = trs_core_get_user_domain( $original_activity->user_id ) . trs_get_settings_slug() . '/notifications/';

		$poster_name = stripslashes( $poster_name );
		$content = trs_activity_filter_kses( stripslashes($content) );

		// Set up and send the message
		$ud       = trs_core_get_core_userdata( $original_activity->user_id );
		$to       = $ud->user_email;
		$sitename = trm_specialchars_decode( get_blog_option( trs_get_root_blog_id(), 'blogname' ), ENT_QUOTES );
		$subject = '[' . $sitename . '] ' . sprintf( __( '%s replied to one of your updates', 'trendr' ), $poster_name );

$message = sprintf( __(
'%1$s replied to one of your updates:

"%2$s"

To view your original update and all comments, log in and visit: %3$s

---------------------
', 'trendr' ), $poster_name, $content, $thread_link );

		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );

		/* Send the message */
		$to = apply_filters( 'trs_activity_new_comment_notification_to', $to );
		$subject = apply_filters( 'trs_activity_new_comment_notification_subject', $subject, $poster_name );
		$message = apply_filters( 'trs_activity_new_comment_notification_message', $message, $poster_name, $content, $thread_link, $settings_link );

		trm_mail( $to, $subject, $message );

		do_action( 'trs_activity_sent_reply_to_update_email', $original_activity->user_id, $subject, $message, $comment_id, $commenter_id, $params );
	}

	/***
	 * If this is a reply to another comment, send an email notification to the
	 * author of the immediate parent comment.
	 */
	if ( $activity_id == $parent_id )
		return false;

	$parent_comment = new TRS_Activity_Activity( $parent_id );

	if ( $parent_comment->user_id != $commenter_id && $original_activity->user_id != $parent_comment->user_id && 'no' != trs_get_user_meta( $parent_comment->user_id, 'notification_activity_new_reply', true ) ) {
		$poster_name = trs_core_get_user_displayname( $commenter_id );
		$thread_link = trs_activity_get_permalink( $activity_id );
		$settings_link = trs_core_get_user_domain( $parent_comment->user_id ) . trs_get_settings_slug() . '/notifications/';

		// Set up and send the message
		$ud       = trs_core_get_core_userdata( $parent_comment->user_id );
		$to       = $ud->user_email;
		$sitename = trm_specialchars_decode( get_blog_option( trs_get_root_blog_id(), 'blogname' ), ENT_QUOTES );
		$subject = '[' . $sitename . '] ' . sprintf( __( '%s replied to one of your comments', 'trendr' ), $poster_name );

		$poster_name = stripslashes( $poster_name );
		$content = trs_activity_filter_kses( stripslashes( $content ) );

$message = sprintf( __(
'%1$s replied to one of your comments:

"%2$s"

To view the original activity, your comment and all replies, log in and visit: %3$s

---------------------
', 'trendr' ), $poster_name, $content, $thread_link );

		$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );

		/* Send the message */
		$to = apply_filters( 'trs_activity_new_comment_notification_comment_author_to', $to );
		$subject = apply_filters( 'trs_activity_new_comment_notification_comment_author_subject', $subject, $poster_name );
		$message = apply_filters( 'trs_activity_new_comment_notification_comment_author_message', $message, $poster_name, $content, $settings_link, $thread_link );

		trm_mail( $to, $subject, $message );

		do_action( 'trs_activity_sent_reply_to_reply_email', $original_activity->user_id, $subject, $message, $comment_id, $commenter_id, $params );
	}
}

?>