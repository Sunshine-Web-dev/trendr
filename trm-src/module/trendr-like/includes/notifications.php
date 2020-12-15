<?php
/**
 * trendr Like Notifications.
 *
 * @package trendrLike
 * @sutrsackage Notifications
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/* Emails *********************************************************************/

/**
 * Send email and TRS notifications when a users update is liked.
 *
 * @since 0.4
 *
 * @uses trs_notifications_add_notification()
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
 * @uses apply_filters() To call the 'trs_like_update_liked_notification_to' hook.
 * @uses apply_filters() To call the 'trs_like_update_liked_notification_subject' hook.
 * @uses apply_filters() To call the 'trs_like_update_liked_notification_message' hook.
 * @uses trm_mail()
 * @uses do_action() To call the 'trs_activity_sent_mention_email' hook.
 *
 * @param int $activity_id      The ID of the activity update.
 * @param int $receiver_user_id The ID of the user who is receiving the notification.
 */
function trs_like_activity_update_notification( $activity_id, $receiver_user_id ) {
  // Don't leave multiple notifications for the same activity item.
	$notifications = TRS_Core_Notification::get_all_for_user( $receiver_user_id, 'all' );

	foreach( $notifications as $notification ) {
		if ( $activity_id == $notification->item_id ) {
			return;
		}
	}

	$activity = new TRS_Activity_Activity( $activity_id );

	$subject = '';
	$message = '';
	$content = '';

	// Now email the user with the contents of the message (if they have enabled email notifications).
  // TODO change 'notification_activity_new_mention' to trs like notification settings options
	if ( 'no' != trs_get_user_meta( $receiver_user_id, 'notification_activity_new_mention', true ) ) {
    // TODO change this to user who liked activty update

    $users_who_like = array_keys((array)(trs_activity_get_meta( $trs_like_id , 'liked_count' , true )));

    if ( count( $users_who_like ) == 1 ) {
      // If only one person likes the current item.

      if ( $receiver_user_id == $users_who_like[0] ) {
        // if the user liked their own update we should do nothing.
      } else {
        $liker_name = trs_core_get_user_displayname( $users_who_like[0] );
        $subject  = trs_get_email_subject( array( 'text' => sprintf( __( '%s liked your update', 'trendr-like' ), $liker_name ) ) );

      }
    } elseif ( count( $users_who_like ) == 2 ) {

        $liker_one = trs_core_get_user_displayname( $users_who_like[0] );
        $liker_two = trs_core_get_user_displayname( $users_who_like[1] );
        $subject  = trs_get_email_subject( array( 'text' => sprintf( __( '%s and %s liked your update', 'trendr-like' ), $liker_one, $liker_two ) ) );

    } elseif ( count ($users_who_like) > 2 ) {

        $others = count ($users_who_like);

        $liker_one = trs_core_get_user_displayname( $users_who_like[$others - 1] );
        $liker_two = trs_core_get_user_displayname( $users_who_like[$others - 2] );

        // TODO comment this better
        // $users_who_like will always be greater than 2 in here
        if ( $users_who_like == 3 ) {
          // if 3 users like an update we remove 1 as we output 2 user names
          // to match the format of - "User1, User2 and 1 other like this"
            $others = $others - 1;
        } else {
        // remove the two named users from the count
            $others = $others - 2;
        }
      //  $string .= '%s, %s and %d ' . _n( 'other', 'others', $others );
        $subject  = trs_get_email_subject( array( 'text' => sprintf( __('%s, %s and %d ' . _n( 'other', 'others', $others ), 'trendr-like' ), $liker_one, $liker_two ) ) );
      //  printf( $string , $one , $two , $others );
    }

    $likers_text =  __( '%s and %s like this.' , 'trendr-like' );

  //  $poster_name = trs_core_get_user_displayname( $activity->user_id );

		$message_link  = trs_activity_get_permalink( $activity_id );
		$settings_slug = function_exists( 'trs_get_settings_slug' ) ? trs_get_settings_slug() : 'settings';
		$settings_link = trs_core_get_user_domain( $receiver_user_id ) . $settings_slug . '/notifications/';

	//	$poster_name = stripslashes( $poster_name );
		$content = trs_activity_filter_kses( strip_tags( stripslashes( $activity->content ) ) );

		// Set up and send the message.
		$ud       = trs_core_get_core_userdata( $receiver_user_id );
		$to       = $ud->user_email;
    //$subject has been declared and assigned its value previously
		//$subject  = trs_get_email_subject( array( 'text' => sprintf( __( '%s liked your update', 'trendr-like' ), $liker_names ) ) );

		if ( trs_is_active( 'groups' ) && trs_is_group() ) {
			$message = sprintf( __(
'%1$s liked your update in the group "%2$s":

"%3$s"

To view your liked update, log in and visit: %4$s

---------------------
', 'trendr-like' ), $poster_name, trs_get_current_group_name(), $content, $message_link );
		} else {
			$message = sprintf( __(
'%1$s liked your update:

"%2$s"

To view your liked update, log in and visit: %3$s

---------------------
', 'trendr-like' ), $poster_name, $content, $message_link );
		}

		// Only show the disable notifications line if the settings component is enabled.
		if ( trs_is_active( 'settings' ) ) {
			$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );
		}

		/**
		 * Filters the user email that the @mention notification will be sent to.
		 *
		 * @since 0.4
		 *
		 * @param string $to User email the notification is being sent to.
		 */
  		$to 	 = apply_filters( 'trs_like_update_liked_notification_to', $to );

		/**
		 * Filters the @mention notification subject that will be sent to user.
		 *
		 * @since 0.4
		 *
		 * @param string $subject     Email notification subject text.
		 * @param string $poster_name Name of the person who made the @mention.
		 */
		$subject = apply_filters( 'trs_like_update_liked_notification_subject', $subject, $poster_name );

		/**
		 * Filters the @mention notification message that will be sent to user.
		 *
		 * @since 0.4
		 *
		 * @param string $message       Email notification message text.
		 * @param string $poster_name   Name of the person who made the @mention.
		 * @param string $content       Content of the liked update.
		 * @param string $message_link  URL permalink for the liked activity update.
		 * @param string $settings_link URL permalink for the user's notification settings area.
		 */
		$message = apply_filters( 'trs_like_update_liked_notification_message', $message, $poster_name, $content, $message_link, $settings_link );

		trm_mail( $to, $subject, $message );
	}

	/**
	 * Fires after the sending of an @mention email notification.
	 *
	 * @since 1.5.0
	 *
	 * @param TRS_Activity_Activity $activity         Activity Item object.
	 * @param string               $subject          Email notification subject text.
	 * @param string               $message          Email notification message text.
	 * @param string               $content          Content of the @mention.
	 * @param int                  $receiver_user_id The ID of the user who is receiving the update.
	 */
	do_action( 'trs_like_sent_update_email', $activity, $subject, $message, $content, $receiver_user_id );
}

/**
 * Send email and TRS notifications when an activity item receives a comment.
 *
 * @since 1.2.0
 *
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
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_to' hook.
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_subject' hook.
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_message' hook.
 * @uses trm_mail()
 * @uses do_action() To call the 'trs_activity_sent_reply_to_update_email' hook.
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_comment_author_to' hook.
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_comment_author_subject' hook.
 * @uses apply_filters() To call the 'trs_activity_new_comment_notification_comment_author_message' hook.
 * @uses do_action() To call the 'trs_activity_sent_reply_to_reply_email' hook.
 *
 * @param int   $comment_id   The comment id.
 * @param int   $commenter_id The ID of the user who posted the comment.
 * @param array $params       {@link trs_activity_new_comment()}.
 * @return bool
 */
function trs_activity_new_comment_notification( $comment_id = 0, $commenter_id = 0, $params = array() ) {

	// Set some default parameters.
	$activity_id = 0;
	$parent_id   = 0;

	extract( $params );

	$original_activity = new TRS_Activity_Activity( $activity_id );

	if ( $original_activity->user_id != $commenter_id && 'no' != trs_get_user_meta( $original_activity->user_id, 'notification_activity_new_reply', true ) ) {
		$poster_name   = trs_core_get_user_displayname( $commenter_id );
		$thread_link   = trs_activity_get_permalink( $activity_id );
		$settings_slug = function_exists( 'trs_get_settings_slug' ) ? trs_get_settings_slug() : 'settings';
		$settings_link = trs_core_get_user_domain( $original_activity->user_id ) . $settings_slug . '/notifications/';

		$poster_name = stripslashes( $poster_name );
		$content = trs_activity_filter_kses( stripslashes($content) );

		// Set up and send the message.
		$ud      = trs_core_get_core_userdata( $original_activity->user_id );
		$to      = $ud->user_email;
		$subject = trs_get_email_subject( array( 'text' => sprintf( __( '%s replied to one of your updates', 'trendr' ), $poster_name ) ) );
		$message = sprintf( __(
'%1$s replied to one of your updates:

"%2$s"

To view your original update and all comments, log in and visit: %3$s

---------------------
', 'trendr' ), $poster_name, $content, $thread_link );

		// Only show the disable notifications line if the settings component is enabled.
		if ( trs_is_active( 'settings' ) ) {
			$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );
		}

		/**
		 * Filters the user email that the new comment notification will be sent to.
		 *
		 * @since 1.2.0
		 *
		 * @param string $to User email the notification is being sent to.
		 */
		$to = apply_filters( 'trs_activity_new_comment_notification_to', $to );

		/**
		 * Filters the new comment notification subject that will be sent to user.
		 *
		 * @since 1.2.0
		 *
		 * @param string $subject     Email notification subject text.
		 * @param string $poster_name Name of the person who made the comment.
		 */
		$subject = apply_filters( 'trs_activity_new_comment_notification_subject', $subject, $poster_name );

		/**
		 * Filters the new comment notification message that will be sent to user.
		 *
		 * @since 1.2.0
		 *
		 * @param string $message       Email notification message text.
		 * @param string $poster_name   Name of the person who made the comment.
		 * @param string $content       Content of the comment.
		 * @param string $thread_link   URL permalink for the activity thread.
		 * @param string $settings_link URL permalink for the user's notification settings area.
		 */
		$message = apply_filters( 'trs_activity_new_comment_notification_message', $message, $poster_name, $content, $thread_link, $settings_link );

		trm_mail( $to, $subject, $message );

		/**
		 * Fires after the sending of a reply to an update email notification.
		 *
		 * @since 1.5.0
		 *
		 * @param int    $user_id      ID of the original activity item author.
		 * @param string $subject      Email notification subject text.
		 * @param string $message      Email notification message text.
		 * @param int    $comment_id   ID for the newly received comment.
		 * @param int    $commenter_id ID of the user who made the comment.
		 * @param array  $params       Arguments used with the original activity comment.
		 */
		do_action( 'trs_activity_sent_reply_to_update_email', $original_activity->user_id, $subject, $message, $comment_id, $commenter_id, $params );
	}

	/*
	 * If this is a reply to another comment, send an email notification to the
	 * author of the immediate parent comment.
	 */
	if ( empty( $parent_id ) || ( $activity_id == $parent_id ) ) {
		return false;
	}

	$parent_comment = new TRS_Activity_Activity( $parent_id );

	if ( $parent_comment->user_id != $commenter_id && $original_activity->user_id != $parent_comment->user_id && 'no' != trs_get_user_meta( $parent_comment->user_id, 'notification_activity_new_reply', true ) ) {
		$poster_name   = trs_core_get_user_displayname( $commenter_id );
		$thread_link   = trs_activity_get_permalink( $activity_id );
		$settings_slug = function_exists( 'trs_get_settings_slug' ) ? trs_get_settings_slug() : 'settings';
		$settings_link = trs_core_get_user_domain( $parent_comment->user_id ) . $settings_slug . '/notifications/';

		// Set up and send the message.
		$ud       = trs_core_get_core_userdata( $parent_comment->user_id );
		$to       = $ud->user_email;
		$subject = trs_get_email_subject( array( 'text' => sprintf( __( '%s replied to one of your comments', 'trendr' ), $poster_name ) ) );

		$poster_name = stripslashes( $poster_name );
		$content = trs_activity_filter_kses( stripslashes( $content ) );

$message = sprintf( __(
'%1$s replied to one of your comments:

"%2$s"

To view the original activity, your comment and all replies, log in and visit: %3$s

---------------------
', 'trendr' ), $poster_name, $content, $thread_link );

		// Only show the disable notifications line if the settings component is enabled.
		if ( trs_is_active( 'settings' ) ) {
			$message .= sprintf( __( 'To disable these notifications please log in and go to: %s', 'trendr' ), $settings_link );
		}

		/**
		 * Filters the user email that the new comment reply notification will be sent to.
		 *
		 * @since 1.2.0
		 *
		 * @param string $to User email the notification is being sent to.
		 */
		$to = apply_filters( 'trs_activity_new_comment_notification_comment_author_to', $to );

		/**
		 * Filters the new comment reply notification subject that will be sent to user.
		 *
		 * @since 1.2.0
		 *
		 * @param string $subject     Email notification subject text.
		 * @param string $poster_name Name of the person who made the comment reply.
		 */
		$subject = apply_filters( 'trs_activity_new_comment_notification_comment_author_subject', $subject, $poster_name );

		/**
		 * Filters the new comment reply notification message that will be sent to user.
		 *
		 * @since 1.2.0
		 *
		 * @param string $message       Email notification message text.
		 * @param string $poster_name   Name of the person who made the comment reply.
		 * @param string $content       Content of the comment reply.
		 * @param string $settings_link URL permalink for the user's notification settings area.
		 * @param string $thread_link   URL permalink for the activity thread.
		 */
		$message = apply_filters( 'trs_activity_new_comment_notification_comment_author_message', $message, $poster_name, $content, $settings_link, $thread_link );

		trm_mail( $to, $subject, $message );

		/**
		 * Fires after the sending of a reply to a reply email notification.
		 *
		 * @since 1.5.0
		 *
		 * @param int    $user_id      ID of the parent activity item author.
		 * @param string $subject      Email notification subject text.
		 * @param string $message      Email notification message text.
		 * @param int    $comment_id   ID for the newly received comment.
		 * @param int    $commenter_id ID of the user who made the comment.
		 * @param array  $params       Arguments used with the original activity comment.
		 */
		do_action( 'trs_activity_sent_reply_to_reply_email', $parent_comment->user_id, $subject, $message, $comment_id, $commenter_id, $params );
	}
}

/**
 * Helper method to map action arguments to function parameters.
 *
 * @since 1.9.0
 *
 * @param int   $comment_id ID of the comment being notified about.
 * @param array $params     Parameters to use with notification.
 */
function trs_activity_new_comment_notification_helper( $comment_id, $params ) {
	trs_activity_new_comment_notification( $comment_id, $params['user_id'], $params );
}
add_action( 'trs_activity_comment_posted', 'trs_activity_new_comment_notification_helper', 10, 2 );

/** Notifications *************************************************************/

/**
 * Format notifications related to activity.
 *
 * @since 1.5.0
 *
 * @uses trs_loggedin_user_domain()
 * @uses trs_get_activity_slug()
 * @uses trs_core_get_user_displayname()
 * @uses apply_filters() To call the 'trs_activity_multiple_at_mentions_notification' hook.
 * @uses apply_filters() To call the 'trs_activity_single_at_mentions_notification' hook.
 * @uses do_action() To call 'activity_format_notifications' hook.
 *
 * @param string $action            The type of activity item. Just 'new_at_mention' for now.
 * @param int    $item_id           The activity ID.
 * @param int    $secondary_item_id In the case of at-mentions, this is the mentioner's ID.
 * @param int    $total_items       The total number of notifications to format.
 * @param string $format            'string' to get a BuddyBar-compatible notification, 'array' otherwise.
 * @return string $return Formatted @mention notification.
 */
function trs_activity_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

	switch ( $action ) {
		case 'new_at_mention':
			$activity_id      = $item_id;
			$poster_user_id   = $secondary_item_id;
			$at_mention_link  = trs_loggedin_user_domain() . trs_get_activity_slug() . '/mentions/';
			$at_mention_title = sprintf( __( '@%s Mentions', 'trendr' ), trs_get_loggedin_user_username() );
			$amount = 'single';

			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( 'You have %1$d new mentions', 'trendr' ), (int) $total_items );
				$amount = 'multiple';
			} else {
				$user_fullname = trs_core_get_user_displayname( $poster_user_id );
				$text =  sprintf( __( '%1$s mentioned you', 'trendr' ), $user_fullname );
			}
		break;
	}

	if ( 'string' == $format ) {

		/**
		 * Filters the @mention notification for the string format.
		 *
		 * This is a variable filter that is dependent on how many items
		 * need notified about. The two possible hooks are trs_activity_single_at_mentions_notification
		 * or trs_activity_multiple_at_mentions_notification.
		 *
		 * @since 1.5.0
		 *
		 * @param string $string          HTML anchor tag for the mention.
		 * @param string $at_mention_link The permalink for the mention.
		 * @param int    $total_items     How many items being notified about.
		 * @param int    $activity_id     ID of the activity item being formatted.
		 * @param int    $poster_user_id  ID of the user posting the mention.
		 */
		$return = apply_filters( 'trs_activity_' . $amount . '_at_mentions_notification', '<a href="' . esc_url( $at_mention_link ) . '" title="' . esc_attr( $at_mention_title ) . '">' . esc_html( $text ) . '</a>', $at_mention_link, (int) $total_items, $activity_id, $poster_user_id );
	} else {

		/**
		 * Filters the @mention notification for any non-string format.
		 *
		 * This is a variable filter that is dependent on how many items need notified about.
		 * The two possible hooks are trs_activity_single_at_mentions_notification
		 * or trs_activity_multiple_at_mentions_notification.
		 *
		 * @since 1.5.0
		 *
		 * @param array  $array           Array holding the content and permalink for the mention notification.
		 * @param string $at_mention_link The permalink for the mention.
		 * @param int    $total_items     How many items being notified about.
		 * @param int    $activity_id     ID of the activity item being formatted.
		 * @param int    $poster_user_id  ID of the user posting the mention.
		 */
		$return = apply_filters( 'trs_activity_' . $amount . '_at_mentions_notification', array(
			'text' => $text,
			'link' => $at_mention_link
		), $at_mention_link, (int) $total_items, $activity_id, $poster_user_id );
	}

	/**
	 * Fires right before returning the formatted activity notifications.
	 *
	 * @since 1.2.0
	 *
	 * @param string $action            The type of activity item.
	 * @param int    $item_id           The activity ID.
	 * @param int    $secondary_item_id @mention mentioner ID.
	 * @param int    $total_items       Total amount of items to format.
	 */
	do_action( 'activity_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return $return;
}

/**
 * Notify a member when their nicename is mentioned in an activity stream item.
 *
 * Hooked to the 'trs_activity_sent_mention_email' action, we piggy back off the
 * existing email code for now, since it does the heavy lifting for us. In the
 * future when we separate emails from Notifications, this will need its own
 * 'trs_activity_at_name_send_emails' equivalent helper function.
 *
 * @since 1.9.0
 *
 * @param object $activity           Activity object.
 * @param string $subject (not used) Notification subject.
 * @param string $message (not used) Notification message.
 * @param string $content (not used) Notification content.
 * @param int    $receiver_user_id   ID of user receiving notification.
 */
function trs_activity_at_mention_add_notification( $activity, $subject, $message, $content, $receiver_user_id ) {
	if ( trs_is_active( 'notifications' ) ) {
		trs_notifications_add_notification( array(
			'user_id'           => $receiver_user_id,
			'item_id'           => $activity->id,
			'secondary_item_id' => $activity->user_id,
			'component_name'    => trendr()->activity->id,
			'component_action'  => 'new_at_mention',
			'date_notified'     => trs_core_current_time(),
			'is_new'            => 1,
		) );
	}
}
add_action( 'trs_activity_sent_mention_email', 'trs_activity_at_mention_add_notification', 10, 5 );

/**
 * Mark at-mention notifications as read when users visit their Mentions page.
 *
 * @since 1.5.0
 *
 * @uses trs_notifications_mark_all_notifications_by_type()
 */
function trs_activity_remove_screen_notifications() {
	if ( ! trs_is_active( 'notifications' ) ) {
		return;
	}

	// Only mark read if you're looking at your own mentions.
	if ( ! trs_is_my_profile() ) {
		return;
	}

	trs_notifications_mark_notifications_by_type( trs_loggedin_user_id(), trendr()->activity->id, 'new_at_mention' );
}
add_action( 'trs_activity_screen_mentions', 'trs_activity_remove_screen_notifications' );

/**
 * Mark at-mention notification as read when user visits the activity with the mention.
 *
 * @since 2.0.0
 *
 * @param TRS_Activity_Activity $activity Activity object.
 */
function trs_activity_remove_screen_notifications_single_activity_permalink( $activity ) {
	if ( ! trs_is_active( 'notifications' ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	// Mark as read any notifications for the current user related to this activity item.
	trs_notifications_mark_notifications_by_item_id( trs_loggedin_user_id(), $activity->id, trendr()->activity->id, 'new_at_mention' );
}
add_action( 'trs_activity_screen_single_activity_permalink', 'trs_activity_remove_screen_notifications_single_activity_permalink' );

/**
 * Delete at-mention notifications when the corresponding activity item is deleted.
 *
 * @since 2.0.0
 *
 * @param array $activity_ids_deleted IDs of deleted activity items.
 */
function trs_activity_at_mention_delete_notification( $activity_ids_deleted = array() ) {
	// Let's delete all without checking if content contains any mentions
	// to avoid a query to get the activity.
	if ( trs_is_active( 'notifications' ) && ! empty( $activity_ids_deleted ) ) {
		foreach ( $activity_ids_deleted as $activity_id ) {
			trs_notifications_delete_all_notifications_by_type( $activity_id, trendr()->activity->id );
		}
	}
}
add_action( 'trs_activity_deleted_activities', 'trs_activity_at_mention_delete_notification', 10 );
