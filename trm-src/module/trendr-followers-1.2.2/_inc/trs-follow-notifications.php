<?php
/**
 * TRS Follow Notifications
 *
 * @package TRS-Follow
 * @sutrsackage Notifications
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** NOTIFICATIONS API ***************************************************/

/**
 * Format on screen notifications into something readable by users.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 */
function trs_follow_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
	global $trs;

	do_action( 'trs_follow_format_notifications', $action, $item_id, $secondary_item_id, $total_items, $format );

	switch ( $action ) {
		case 'new_follow':
			$link = $text = false;

			if ( 1 == $total_items ) {
				$text = sprintf( __( '%s is now following you', 'trs-follow' ), trs_core_get_user_displayname( $item_id ) );
				$link = trs_core_get_user_domain( $item_id  ) . '?trsf_read';

			} else {
				$text = sprintf( __( '%d more users are now following you', 'trs-follow' ), $total_items );

				if ( trs_is_active( 'notifications' ) ) {
					$link = trs_get_notifications_permalink();
				} else {
					$link = trs_loggedin_user_domain() . $trs->follow->followers->slug . '/?new';
				}
			}

		break;

		default :
			$link = apply_filters( 'trs_follow_extend_notification_link', false, $action, $item_id, $secondary_item_id, $total_items );
			$text = apply_filters( 'trs_follow_extend_notification_text', false, $action, $item_id, $secondary_item_id, $total_items );
		break;
	}

	if ( ! $link || ! $text ) {
		return false;
	}

	if ( 'string' == $format ) {
		return apply_filters( 'trs_follow_new_followers_notification', '<a href="' . $link . '">' . $text . '</a>', $total_items, $link, $text, $item_id, $secondary_item_id );

	} else {
		$array = array(
			'text' => $text,
			'link' => $link
		);

		return apply_filters( 'trs_follow_new_followers_return_notification', $array, $item_id, $secondary_item_id, $total_items );
	}
}

/**
 * Removes notifications made by a user.
 *
 * @since 1.2.1
 *
 * @param int $user_id The user ID.
 */
function trs_follow_remove_notifications_for_user( $user_id = 0 ) {
	// TRS 1.9+
	if ( trs_is_active( 'notifications' ) ) {
		trs_notifications_delete_all_notifications_by_type( $user_id, trendr()->follow->id, 'new_follow' );

	// TRS < 1.9 - delete notifications the old way
	} elseif ( ! class_exists( 'TRS_Core_Login_Widget' ) ) {
		global $trs;

		trs_core_delete_notifications_from_user( $user_id, $trs->follow->id, 'new_follow' );
	}
}
add_action( 'trs_follow_remove_data', 'trs_follow_remove_notifications_for_user' );

/**
 * Adds notification when a user follows another user.
 *
 * @since 1.2.1
 *
 * @param object $follow The TRS_Follow object.
 */
function trs_follow_notifications_add_on_follow( TRS_Follow $follow ) {
	// Add a screen notification
	//
	// TRS 1.9+
	if ( trs_is_active( 'notifications' ) ) {
		trs_notifications_add_notification( array(
			'item_id'           => $follow->follower_id,
			'user_id'           => $follow->leader_id,
			'component_name'    => trendr()->follow->id,
			'component_action'  => 'new_follow'
		) );

	// TRS < 1.9 - add notifications the old way
	} elseif ( ! class_exists( 'TRS_Core_Login_Widget' ) ) {
		global $trs;

		trs_core_add_notification(
			$follow->follower_id,
			$follow->leader_id,
			$trs->follow->id,
			'new_follow'
		);
	}

	// Add an email notification
	trs_follow_new_follow_email_notification( array(
		'leader_id'   => $follow->leader_id,
		'follower_id' => $follow->follower_id
	) );
}
add_action( 'trs_follow_start_following', 'trs_follow_notifications_add_on_follow' );

/**
 * Removes notification when a user unfollows another user.
 *
 * @since 1.2.1
 *
 * @param object $follow The TRS_Follow object.
 */
function trs_follow_notifications_remove_on_unfollow( TRS_Follow $follow ) {
	// TRS 1.9+
	if ( trs_is_active( 'notifications' ) ) {
		trs_notifications_delete_notifications_by_item_id( $follow->leader_id, $follow->follower_id, trendr()->follow->id, 'new_follow' );

	// TRS < 1.9 - delete notifications the old way
	} elseif ( ! class_exists( 'TRS_Core_Login_Widget' ) ) {
		global $trs;

		trs_core_delete_notifications_by_item_id( $follow->leader_id, $follow->follower_id, $trs->follow->id, 'new_follow' );
	}
}
add_action( 'trs_follow_stop_following', 'trs_follow_notifications_remove_on_unfollow' );

/**
 * Mark notification as read when a logged-in user visits their follower's profile.
 *
 * This is a new feature in trendr 1.9.
 *
 * @since 1.2.1
 */
function trs_follow_notifications_mark_follower_profile_as_read() {
	if ( ! isset( $_GET['trsf_read'] ) ) {
		return;
	}

	// mark notification as read
	if ( trs_is_active( 'notifications' ) ) {
		trs_notifications_mark_notifications_by_item_id( trs_loggedin_user_id(), trs_displayed_user_id(), trendr()->follow->id, 'new_follow' );

	// check if we're not on TRS 1.9
	// if so, delete notification since marked functionality doesn't exist
	} elseif ( ! class_exists( 'TRS_Core_Login_Widget' ) ) {
		global $trs;

		trs_core_delete_notifications_by_item_id( trs_loggedin_user_id(), trs_displayed_user_id(), $trs->follow->id, 'new_follow' );
	}
}
add_action( 'trs_members_screen_display_profile', 'trs_follow_notifications_mark_follower_profile_as_read' );

/**
 * Delete notifications when a logged-in user visits their followers page.
 *
 * Since 1.2.1, when the "X users are now following you" notification appears,
 * users will be redirected to the new notifications unread page instead of
 * the logged-in user's followers page.  This is so users can see who followed
 * them and in the date order in which they were followed.
 *
 * For backwards-compatibility, we still keep the old method of redirecting to
 * the logged-in user's followers page so notifications can be deleted for
 * older versions of trendr.
 *
 * Will probably remove this in a future release.
 *
 * @since 1.2.1
 */
function trs_follow_notifications_delete_on_followers_page() {
	if ( ! isset( $_GET['new'] ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	// TRS 1.9+
	if ( trs_is_active( 'notifications' ) ) {
		trs_notifications_delete_notifications_by_type( trs_loggedin_user_id(), $trs->follow->id, 'new_follow' );

	// TRS < 1.9
	} elseif ( ! class_exists( 'TRS_Core_Login_Widget' ) ) {
		global $trs;

		trs_core_delete_notifications_by_type( trs_loggedin_user_id(), $trs->follow->id, 'new_follow' );
	}
}
add_action( 'trs_follow_screen_followers', 'trs_follow_notifications_delete_on_followers_page' );

/**
 * When we're on the notification's 'read' page, remove 'trsf_read' query arg.
 *
 * Since we are already on the 'read' page, notifications on this page are
 * already marked as read.  So, we no longer need to add our special
 * 'trsf_read' query argument to each notification to determine whether we
 * need to clear it.
 *
 * @since 1.2.1
 */
function trs_follow_notifications_remove_queryarg_from_userlink( $retval ) {
	if ( trs_is_current_action( 'read' ) ) {
		// if notifications loop has finished rendering, stop now!
		// this is so follow notifications in the adminbar are unaffected
		if ( did_action( 'trs_after_member_body' ) ) {
			return $retval;
		}

		$retval = str_replace( '?trsf_read', '', $retval );
	}

	return $retval;
}
add_filter( 'trs_follow_new_followers_notification', 'trs_follow_notifications_remove_queryarg_from_userlink' );

/** SETTINGS ************************************************************/

/**
 * Adds user configurable notification settings for the component.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 */
function trs_follow_screen_notification_settings() {
	if ( !$notify = trs_get_user_meta( trs_displayed_user_id(), 'notification_starts_following', true ) )
		$notify = 'yes';
?>

	<table class="notification-settings" id="follow-notification-settings">
		<thead>
			<tr>
				<th class="icon"></th>
				<th class="title"><?php _e( 'Follow', 'trs-follow' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'trs-follow' ) ?></th>
				<th class="no"><?php _e( 'No', 'trs-follow' )?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td></td>
				<td><?php _e( 'A member starts following your activity', 'trs-follow' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_starts_following]" value="yes" <?php checked( $notify, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_starts_following]" value="no" <?php checked( $notify, 'no', true ) ?>/></td>
			</tr>
		</tbody>

		<?php do_action( 'trs_follow_screen_notification_settings' ); ?>
	</table>
<?php
}
add_action( 'trs_notification_settings', 'trs_follow_screen_notification_settings' );

/** EMAIL ***************************************************************/

/**
 * Send an email to the leader when someone follows them.
 *
 * @uses trs_core_get_user_displayname() Get the display name for a user
 * @uses trs_core_get_user_domain() Get the profile url for a user
 * @uses trs_core_get_core_userdata() Get the core userdata for a user without extra usermeta
 * @uses trm_mail() Send an email using the built in TRM mail class
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 */
function trs_follow_new_follow_email_notification( $args = '' ) {

	$r = trm_parse_args( $args, array(
		'leader_id'   => trs_displayed_user_id(),
		'follower_id' => trs_loggedin_user_id()
	) );

	// Don't send email for yourself!
	if ( $r['follower_id'] === $r['leader_id'] ) {
		return false;
	}

	if ( 'no' == trs_get_user_meta( (int) $r['leader_id'], 'notification_starts_following', true ) )
		return false;

	// Check to see if this leader has already been notified of this follower before
	$has_notified = trs_get_user_meta( $r['follower_id'], 'trs_follow_has_notified', true );

	// Already notified so don't send another email
	if ( in_array( $r['leader_id'], (array) $has_notified ) )
		return false;

	// Not been notified before, update usermeta and continue to mail
	$has_notified[] = $r['leader_id'];
	trs_update_user_meta( $r['follower_id'], 'trs_follow_has_notified', $has_notified );

	$follower_name = trm_specialchars_decode( trs_core_get_user_displayname( $r['follower_id'] ), ENT_QUOTES );
	$follower_link = trs_core_get_user_domain( $r['follower_id'] ) . '?trsf_read';

	$leader_ud = trs_core_get_core_userdata( $r['leader_id'] );

	// Set up and send the message
	$to = $leader_ud->user_email;

	$subject = '[' . trm_specialchars_decode( trs_get_option( 'blogname' ), ENT_QUOTES ) . '] ' . sprintf( __( '%s is now following you', 'trs-follow' ), $follower_name );

	$message = sprintf( __(
'%s is now following your activity.

To view %s\'s profile: %s', 'trs-follow' ), $follower_name, $follower_name, $follower_link );

	// Add notifications link if settings component is enabled
	if ( trs_is_active( 'settings' ) ) {
		$settings_link = trs_core_get_user_domain( $r['leader_id'] ) . TRS_SETTINGS_SLUG . '/notifications/';
		$message .= sprintf( __( '

---------------------
To disable these notifications please log in and go to:
%s', 'trs-follow' ), $settings_link );
	}

	// Send the message
	$to      = apply_filters( 'trs_follow_notification_to', $to );
	$subject = apply_filters( 'trs_follow_notification_subject', $subject, $follower_name );
	$message = apply_filters( 'trs_follow_notification_message', trm_specialchars_decode( $message, ENT_QUOTES ), $follower_name, $follower_link );

	trm_mail( $to, $subject, $message );
}
