<?php

/**
 * trendr Activity Screens
 *
 * @package trendr
 * @sutrsackage ActivityScreens
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Activity screen index
 *
 * @since 1.5.0
 *
 * @uses trs_displayed_user_id()
 * @uses trs_is_activity_component()
 * @uses trs_current_action()
 * @uses trs_update_is_directory()
 * @uses do_action() To call the 'trs_activity_screen_index' hook
 * @uses trs_core_load_template()
 * @uses apply_filters() To call the 'trs_activity_screen_index' hook
 */
function trs_activity_screen_index() {
	if ( !trs_displayed_user_id() && trs_is_activity_component() && !trs_current_action() ) {
		trs_update_is_directory( true, 'activity' );

		do_action( 'trs_activity_screen_index' );

		trs_core_load_template( apply_filters( 'trs_activity_screen_index', 'activity/index' ) );
	}
}
add_action( 'trs_screens', 'trs_activity_screen_index' );

/**
 * Activity screen 'my activity' index
 *
 * @since 1.0.0
 *
 * @uses do_action() To call the 'trs_activity_screen_my_activity' hook
 * @uses trs_core_load_template()
 * @uses apply_filters() To call the 'trs_activity_template_my_activity' hook
 */
function trs_activity_screen_my_activity() {
	do_action( 'trs_activity_screen_my_activity' );
	trs_core_load_template( apply_filters( 'trs_activity_template_my_activity', 'members/single/home' ) );
}

/**
 * Activity screen 'friends' index
 *
 * @since 1.0.0
 *
 * @uses trs_is_active()
 * @uses trs_update_is_item_admin()
 * @uses is_super_admin()
 * @uses do_action() To call the 'trs_activity_screen_friends' hook
 * @uses trs_core_load_template()
 * @uses apply_filters() To call the 'trs_activity_template_friends_activity' hook
 */
function trs_activity_screen_friends() {
	if ( !trs_is_active( 'friends' ) )
		return false;

	trs_update_is_item_admin( is_super_admin(), 'activity' );
	do_action( 'trs_activity_screen_friends' );
	trs_core_load_template( apply_filters( 'trs_activity_template_friends_activity', 'members/single/home' ) );
}

/**
 * Activity screen 'groups' index
 *
 * @since 1.2.0
 *
 * @uses trs_is_active()
 * @uses trs_update_is_item_admin()
 * @uses is_super_admin()
 * @uses do_action() To call the 'trs_activity_screen_groups' hook
 * @uses trs_core_load_template()
 * @uses apply_filters() To call the 'trs_activity_template_groups_activity' hook
 */
function trs_activity_screen_groups() {
	if ( !trs_is_active( 'groups' ) )
		return false;

	trs_update_is_item_admin( is_super_admin(), 'activity' );
	do_action( 'trs_activity_screen_groups' );
	trs_core_load_template( apply_filters( 'trs_activity_template_groups_activity', 'members/single/home' ) );
}

/**
 * Activity screen 'favorites' index
 *
 * @since 1.2.0
 *
 * @uses trs_update_is_item_admin()
 * @uses is_super_admin()
 * @uses do_action() To call the 'trs_activity_screen_favorites' hook
 * @uses trs_core_load_template()
 * @uses apply_filters() To call the 'trs_activity_template_favorite_activity' hook
 */
function trs_activity_screen_favorites() {
	trs_update_is_item_admin( is_super_admin(), 'activity' );
	do_action( 'trs_activity_screen_favorites' );
	trs_core_load_template( apply_filters( 'trs_activity_template_favorite_activity', 'members/single/home' ) );
}

/**
 * Activity screen 'mentions' index
 *
 * @since 1.2.0
 *
 * @uses trs_update_is_item_admin()
 * @uses is_super_admin()
 * @uses do_action() To call the 'trs_activity_screen_mentions' hook
 * @uses trs_core_load_template()
 * @uses apply_filters() To call the 'trs_activity_template_mention_activity' hook
 */
function trs_activity_screen_mentions() {
	trs_update_is_item_admin( is_super_admin(), 'activity' );
	do_action( 'trs_activity_screen_mentions' );
	trs_core_load_template( apply_filters( 'trs_activity_template_mention_activity', 'members/single/home' ) );
}

/**
 * Removes activity notifications from the notification menu when a user clicks on them and
 * is taken to a specific screen.
 *
 * @since 1.5.0
 *
 * @global object $trs trendr global settings
 * @uses trs_core_delete_notifications_by_type()
 */
function trs_activity_remove_screen_notifications() {
	global $trs;

	trs_core_delete_notifications_by_type( $trs->loggedin_user->id, $trs->activity->id, 'new_at_mention' );
}
add_action( 'trs_activity_screen_my_activity',               'trs_activity_remove_screen_notifications' );
add_action( 'trs_activity_screen_single_activity_permalink', 'trs_activity_remove_screen_notifications' );
add_action( 'trs_activity_screen_mentions',                  'trs_activity_remove_screen_notifications' );

/**
 * Reset the logged-in user's new mentions data when he visits his mentions screen
 *
 * @since 1.5.0
 *
 * @uses trs_is_my_profile()
 * @uses trs_activity_clear_new_mentions()
 * @uses trs_loggedin_user_id()
 */
function trs_activity_reset_my_new_mentions() {
	if ( trs_is_my_profile() )
		trs_activity_clear_new_mentions( trs_loggedin_user_id() );
}
add_action( 'trs_activity_screen_mentions', 'trs_activity_reset_my_new_mentions' );

/**
 * Reset the logged-in user's new mentions data when he visits his mentions screen
 *
 * @since 1.2.0
 *
 * @global object $trs trendr global settings
 * @uses trs_is_activity_component()
 * @uses trs_activity_get_specific()
 * @uses trs_current_action()
 * @uses trs_action_variables()
 * @uses trs_do_404()
 * @uses trs_is_active()
 * @uses groups_get_group()
 * @uses groups_is_user_member()
 * @uses apply_filters_ref_array() To call the 'trs_activity_permalink_access' hook
 * @uses do_action() To call the 'trs_activity_screen_single_activity_permalink' hook
 * @uses trs_core_add_message()
 * @uses is_user_logged_in()
 * @uses trs_core_redirect()
 * @uses site_url()
 * @uses esc_url()
 * @uses trs_get_root_domain()
 * @uses trs_get_activity_root_slug()
 * @uses trs_core_load_template()
 * @uses apply_filters() To call the 'trs_activity_template_profile_activity_permalink' hook
 */
function trs_activity_screen_single_activity_permalink() {
	global $trs;

	// No displayed user or not viewing activity component
	if ( !trs_is_activity_component() )
		return false;

	if ( empty( $trs->current_action ) || !is_numeric( $trs->current_action ) )
		return false;

	// Get the activity details
	$activity = trs_activity_get_specific( array( 'activity_ids' => trs_current_action(), 'show_hidden' => true ) );

	// 404 if activity does not exist
	if ( empty( $activity['activities'][0] ) || trs_action_variables() ) {
		trs_do_404();
		return;

	} else {
		$activity = $activity['activities'][0];
	}

	// Default access is true
	$has_access = true;

	// If activity is from a group, do an extra cap check
	if ( isset( $trs->groups->id ) && $activity->component == $trs->groups->id ) {

		// Activity is from a group, but groups is currently disabled
		if ( !trs_is_active( 'groups') ) {
			trs_do_404();
			return;
		}

		// Check to see if the group is not public, if so, check the
		// user has access to see this activity
		if ( $group = groups_get_group( array( 'group_id' => $activity->item_id ) ) ) {

			// Group is not public
			if ( 'public' != $group->status ) {

				// User is not a member of group
				if ( !groups_is_user_member( $trs->loggedin_user->id, $group->id ) ) {
					$has_access = false;
				}
			}
		}
	}

	// Allow access to be filtered
	$has_access = apply_filters_ref_array( 'trs_activity_permalink_access', array( $has_access, &$activity ) );

	// Allow additional code execution
	do_action( 'trs_activity_screen_single_activity_permalink', $activity, $has_access );

	// Access is specifically disallowed
	if ( false === $has_access ) {

		// User feedback
		trs_core_add_message( __( 'You do not have access to this activity.', 'trendr' ), 'error' );

		// Redirect based on logged in status
		is_user_logged_in() ?
			trs_core_redirect( $trs->loggedin_user->domain ) :
			trs_core_redirect( site_url( 'enter.php?redirect_to=' . esc_url( trs_get_root_domain() . '/' . trs_get_activity_root_slug() . '/p/' . $trs->current_action ) ) );
	}

	trs_core_load_template( apply_filters( 'trs_activity_template_profile_activity_permalink', 'members/single/activity/permalink' ) );
}
add_action( 'trs_screens', 'trs_activity_screen_single_activity_permalink' );

/**
 * Add activity notifications settings to the notifications settings page
 *
 * @since 1.2.0
 *
 * @global object $trs trendr global settings
 * @uses trs_get_user_meta()
 * @uses trs_core_get_username()
 * @uses do_action() To call the 'trs_activity_screen_notification_settings' hook
 */
function trs_activity_screen_notification_settings() {
	global $trs;

	if ( !$mention = trs_get_user_meta( $trs->displayed_user->id, 'notification_activity_new_mention', true ) )
		$mention = 'yes';

	if ( !$reply = trs_get_user_meta( $trs->displayed_user->id, 'notification_activity_new_reply', true ) )
		$reply = 'yes'; ?>

	<table class="notification-settings" id="activity-notification-settings">
		<thead>
			<tr>
				<th class="icon">&nbsp;</th>
				<th class="title"><?php _e( 'Activity', 'trendr' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'trendr' ) ?></th>
				<th class="no"><?php _e( 'No', 'trendr' )?></th>
			</tr>
		</thead>

		<tbody>
			<tr id="activity-notification-settings-mentions">
				<td>&nbsp;</td>
				<td><?php printf( __( 'A member mentions you in an update using "@%s"', 'trendr' ), trs_core_get_username( $trs->displayed_user->id, $trs->displayed_user->userdata->user_nicename, $trs->displayed_user->userdata->user_login ) ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_activity_new_mention]" value="yes" <?php checked( $mention, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_activity_new_mention]" value="no" <?php checked( $mention, 'no', true ) ?>/></td>
			</tr>
			<tr id="activity-notification-settings-replies">
				<td>&nbsp;</td>
				<td><?php _e( "A member replies to an update or comment you've posted", 'trendr' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_activity_new_reply]" value="yes" <?php checked( $reply, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_activity_new_reply]" value="no" <?php checked( $reply, 'no', true ) ?>/></td>
			</tr>

			<?php do_action( 'trs_activity_screen_notification_settings' ) ?>
		</tbody>
	</table>

<?php
}
add_action( 'trs_notification_settings', 'trs_activity_screen_notification_settings', 1 );

?>