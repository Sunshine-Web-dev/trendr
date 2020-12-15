<?php

/**
 * Action functions are exactly the same as screen functions, however they do
 * not have a template screen associated with them. Usually they will send the
 * user back to the default screen after execution.
 *
 * @package trendr
 * @sutrsackage ActivityActions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Allow core components and dependent plugins to register activity actions
 *
 * @since 1.2.0
 *
 * @uses do_action() To call 'trs_register_activity_actions' hook.
 */
function trs_register_activity_actions() {
	do_action( 'trs_register_activity_actions' );
}
add_action( 'trs_init', 'trs_register_activity_actions', 8 );

/**
 * Allow core components and dependent plugins to register activity actions
 *
 * @since 1.2.0
 *
 * @global object $trs trendr global settings
 * @uses trs_is_activity_component()
 * @uses trs_is_current_action()
 * @uses trs_action_variable()
 * @uses trs_activity_get_specific()
 * @uses trs_is_active()
 * @uses trs_core_get_user_domain()
 * @uses groups_get_group()
 * @uses trs_get_group_permalink()
 * @uses apply_filters_ref_array() To call the 'trs_activity_permalink_redirect_url' hook
 * @uses trs_core_redirect()
 * @uses trs_get_root_domain()
 *
 * @return bool False on failure
 */
function trs_activity_action_permalink_router() {
	global $trs;

	// Not viewing activity
	if ( !trs_is_activity_component() || !trs_is_current_action( 'p' ) )
		return false;

	// No activity to display
	if ( !trs_action_variable( 0 ) || !is_numeric( trs_action_variable( 0 ) ) )
		return false;

	// Get the activity details
	$activity = trs_activity_get_specific( array( 'activity_ids' => trs_action_variable( 0 ), 'show_hidden' => true ) );

	// 404 if activity does not exist
	if ( empty( $activity['activities'][0] ) ) {
		trs_do_404();
		return;

	} else {
		$activity = $activity['activities'][0];
	}

	// Do not redirect at default
	$redirect = false;

	// Redirect based on the type of activity
	if ( trs_is_active( 'groups' ) && $activity->component == $trs->groups->id ) {

		// Activity is a user update
		if ( !empty( $activity->user_id ) ) {
			$redirect = trs_core_get_user_domain( $activity->user_id, $activity->user_nicename, $activity->user_login ) . trs_get_activity_slug() . '/' . $activity->id . '/';

		// Activity is something else
		} else {

			// Set redirect to group activity stream
			if ( $group = groups_get_group( array( 'group_id' => $activity->item_id ) ) ) {
				$redirect = trs_get_group_permalink( $group ) . trs_get_activity_slug() . '/' . $activity->id . '/';
			}
		}

	// Set redirect to users' activity stream
	} else {
		$redirect = trs_core_get_user_domain( $activity->user_id, $activity->user_nicename, $activity->user_login ) . trs_get_activity_slug() . '/' . $activity->id;
	}

	// Allow redirect to be filtered
	if ( !$redirect = apply_filters_ref_array( 'trs_activity_permalink_redirect_url', array( $redirect, &$activity ) ) )
		trs_core_redirect( trs_get_root_domain() );

	// Redirect to the actual activity permalink page
	trs_core_redirect( $redirect );
}
add_action( 'trs_actions', 'trs_activity_action_permalink_router' );

/**
 * Delete specific activity item and redirect to previous page.
 *
 * @since 1.1.0
 *
 * @param int $activity_id Activity id to be deleted. Defaults to 0.
 *
 * @global object $trs trendr global settings
 * @uses trs_is_activity_component()
 * @uses trs_is_current_action()
 * @uses trs_action_variable()
 * @uses check_admin_referer()
 * @uses trs_activity_user_can_delete()
 * @uses do_action() Calls 'trs_activity_before_action_delete_activity' hook to allow actions to be taken before the activity is deleted.
 * @uses trs_activity_delete()
 * @uses trs_core_add_message()
 * @uses do_action() Calls 'trs_activity_action_delete_activity' hook to allow actions to be taken after the activity is deleted.
 * @uses trs_core_redirect()
 *
 * @return bool False on failure
 */
function trs_activity_action_delete_activity( $activity_id = 0 ) {
	global $trs;

	// Not viewing activity or action is not delete
	if ( !trs_is_activity_component() || !trs_is_current_action( 'delete' ) )
		return false;

	if ( empty( $activity_id ) && trs_action_variable( 0 ) )
		$activity_id = (int) trs_action_variable( 0 );

	// Not viewing a specific activity item
	if ( empty( $activity_id ) )
		return false;

	// Check the nonce
	check_admin_referer( 'trs_activity_delete_link' );

	// Load up the activity item
	$activity = new TRS_Activity_Activity( $activity_id );

	// Check access
	if ( empty( $activity->user_id ) || !trs_activity_user_can_delete( $activity ) )
		return false;

	// Call the action before the delete so plugins can still fetch information about it
	do_action( 'trs_activity_before_action_delete_activity', $activity_id, $activity->user_id );

	// Delete the activity item and provide user feedback
	if ( trs_activity_delete( array( 'id' => $activity_id, 'user_id' => $activity->user_id ) ) )
		trs_core_add_message( __( 'Activity deleted successfully', 'trendr' ) );
	else
		trs_core_add_message( __( 'There was an error when deleting that activity', 'trendr' ), 'error' );

	do_action( 'trs_activity_action_delete_activity', $activity_id, $activity->user_id );

	// Check for the redirect query arg, otherwise let TRM handle things
 	if ( !empty( $_GET['redirect_to'] ) )
		trs_core_redirect( esc_url( $_GET['redirect_to'] ) );
	else
		trs_core_redirect( trm_get_referer() );
}
add_action( 'trs_actions', 'trs_activity_action_delete_activity' );

/**
 * Post user/group activity update.
 *
 * @since 1.2.0
 *
 * @global object $trs trendr global settings
 * @uses is_user_logged_in()
 * @uses trs_is_activity_component()
 * @uses trs_is_current_action()
 * @uses check_admin_referer()
 * @uses apply_filters() To call 'trs_activity_post_update_content' hook.
 * @uses apply_filters() To call 'trs_activity_post_update_object' hook.
 * @uses apply_filters() To call 'trs_activity_post_update_item_id' hook.
 * @uses trs_core_add_message()
 * @uses trs_core_redirect()
 * @uses trs_activity_post_update()
 * @uses groups_post_update()
 * @uses trs_core_redirect()
 * @uses apply_filters() To call 'trs_activity_custom_update' hook.
 *
 * @return bool False on failure
 */
function trs_activity_action_post_update() {
	global $trs;

	// Do not proceed if user is not logged in, not viewing activity, or not posting
	if ( !is_user_logged_in() || !trs_is_activity_component() || !trs_is_current_action( 'post' ) )
		return false;

	// Check the nonce
	check_admin_referer( 'post_update', '_key_post_update' );

	// Get activity info
	$content = apply_filters( 'trs_activity_post_update_content', $_POST['field']             );
	$object  = apply_filters( 'trs_activity_post_update_object',  $_POST['field-post-object'] );
	$item_id = apply_filters( 'trs_activity_post_update_item_id', $_POST['field-post-in']     );

	// No activity content so provide feedback and redirect
	if ( empty( $content ) ) {
		trs_core_add_message( __( 'Please enter some content to post.', 'trendr' ), 'error' );
		trs_core_redirect( trm_get_referer() );
	}

	// No existing item_id
	if ( empty( $item_id ) ) {
		$activity_id = trs_activity_post_update( array( 'content' => $content ) );

	// Post to groups object
	} else if ( 'groups' == $object && trs_is_active( 'groups' ) ) {
		if ( (int)$item_id ) {
			$activity_id = groups_post_update( array( 'content' => $content, 'group_id' => $item_id ) );
		}

	// Special circumstance so let filters handle it
	} else {
		$activity_id = apply_filters( 'trs_activity_custom_update', $object, $item_id, $content );
	}

	// Provide user feedback
	if ( !empty( $activity_id ) )
		trs_core_add_message( __( 'Update Posted!', 'trendr' ) );
	else
		trs_core_add_message( __( 'There was an error when posting your update, please try again.', 'trendr' ), 'error' );

	// Redirect
	trs_core_redirect( trm_get_referer() );
}
add_action( 'trs_actions', 'trs_activity_action_post_update' );

/**
 * Post new activity comment.
 *
 * @since 1.2.0
 *
 * @global object $trs trendr global settings
 * @uses is_user_logged_in()
 * @uses trs_is_activity_component()
 * @uses trs_is_current_action()
 * @uses check_admin_referer()
 * @uses apply_filters() To call 'trs_activity_post_comment_activity_id' hook.
 * @uses apply_filters() To call 'trs_activity_post_comment_content' hook.
 * @uses trs_core_add_message()
 * @uses trs_core_redirect()
 * @uses trs_activity_new_comment()
 * @uses trm_get_referer()
 *
 * @return bool False on failure
 */
function trs_activity_action_post_comment() {
	global $trs;

	if ( !is_user_logged_in() || ( trs_is_activity_component() ) || !trs_is_current_action( 'reply' ) )
		return false;

	// Check the nonce
	check_admin_referer( 'new_activity_comment', '_key_new_activity_comment' );

	$activity_id = apply_filters( 'trs_activity_post_comment_activity_id', $_POST['comment_form_id'] );
	$content = apply_filters( 'trs_activity_post_comment_content', $_POST['ac_input_' . $activity_id] );

	if ( empty( $content ) ) {
		trs_core_add_message( __( 'Please do not leave the comment area blank.', 'trendr' ), 'error' );
		trs_core_redirect( trm_get_referer() . '#ac-form-' . $activity_id );
	}

	$comment_id = trs_activity_new_comment( array(
		'content' => $content,
		'activity_id' => $activity_id,
		'parent_id' => $parent_id
	));

	if ( !empty( $comment_id ) )
		trs_core_add_message( __( 'Reply Posted!', 'trendr' ) );
	else
		trs_core_add_message( __( 'There was an error posting that reply, please try again.', 'trendr' ), 'error' );

	trs_core_redirect( trm_get_referer() . '#ac-form-' . $activity_id );
}
add_action( 'trs_actions', 'trs_activity_action_post_comment' );

/**
 * Mark activity as favorite.
 *
 * @since 1.2.0
 *
 * @global object $trs trendr global settings
 * @uses is_user_logged_in()
 * @uses trs_is_activity_component()
 * @uses trs_is_current_action()
 * @uses check_admin_referer()
 * @uses trs_activity_add_user_favorite()
 * @uses trs_action_variable()
 * @uses trs_core_add_message()
 * @uses trs_core_redirect()
 * @uses trm_get_referer()
 *
 * @return bool False on failure
 */
function trs_activity_action_mark_favorite() {
	global $trs;

	if ( !is_user_logged_in() || ( trs_is_activity_component() ) || !trs_is_current_action( 'favorite' ) )
		return false;

	// Check the nonce
	check_admin_referer( 'mark_favorite' );

	if ( trs_activity_add_user_favorite( trs_action_variable( 0 ) ) )
		trs_core_add_message( __( 'Activity marked as favorite.', 'trendr' ) );
	else
		trs_core_add_message( __( 'There was an error marking that activity as a favorite, please try again.', 'trendr' ), 'error' );

	trs_core_redirect( trm_get_referer() . '#activity-' . trs_action_variable( 0 ) );
}
add_action( 'trs_actions', 'trs_activity_action_mark_favorite' );

/**
 * Remove activity from favorites.
 *
 * @since 1.2.0
 *
 * @global object $trs trendr global settings
 * @uses is_user_logged_in()
 * @uses trs_is_activity_component()
 * @uses trs_is_current_action()
 * @uses check_admin_referer()
 * @uses trs_activity_remove_user_favorite()
 * @uses trs_action_variable()
 * @uses trs_core_add_message()
 * @uses trs_core_redirect()
 * @uses trm_get_referer()
 *
 * @return bool False on failure
 */
function trs_activity_action_remove_favorite() {
	global $trs;

	if ( !is_user_logged_in() || ( trs_is_activity_component() ) || !trs_is_current_action( 'unfavorite' ) )
		return false;

	// Check the nonce
	check_admin_referer( 'unmark_favorite' );

	if ( trs_activity_remove_user_favorite( trs_action_variable( 0 ) ) )
		trs_core_add_message( __( 'Activity removed as favorite.', 'trendr' ) );
	else
		trs_core_add_message( __( 'There was an error removing that activity as a favorite, please try again.', 'trendr' ), 'error' );

	trs_core_redirect( trm_get_referer() . '#activity-' . trs_action_variable( 0 ) );
}
add_action( 'trs_actions', 'trs_activity_action_remove_favorite' );

/**
 * Load the sitewide feed.
 *
 * @since 1.0.0
 *
 * @global object $trs trendr global settings
 * @global object $trm_query
 * @uses trs_is_activity_component()
 * @uses trs_is_current_action()
 * @uses trs_is_user()
 * @uses status_header()
 *
 * @return bool False on failure
 */
function trs_activity_action_sitewide_feed() {
	global $trs, $trm_query;

	if ( !trs_is_activity_component() || !trs_is_current_action( 'feed' ) || trs_is_user() || !empty( $trs->groups->current_group ) )
		return false;

	$trm_query->is_404 = false;
	status_header( 200 );

	include_once( 'feeds/trs-activity-sitewide-feed.php' );
	die;
}
add_action( 'trs_actions', 'trs_activity_action_sitewide_feed' );

/**
 * Load a user's personal feed.
 *
 * @since 1.0.0
 *
 * @global object $trs trendr global settings
 * @global object $trm_query
 * @uses trs_is_user_activity()
 * @uses trs_is_current_action()
 * @uses status_header()
 *
 * @return bool False on failure
 */
function trs_activity_action_personal_feed() {
	global $trs, $trm_query;

	if ( !trs_is_user_activity() || !trs_is_current_action( 'feed' ) )
		return false;

	$trm_query->is_404 = false;
	status_header( 200 );

	include_once( 'feeds/trs-activity-personal-feed.php' );
	die;
}
add_action( 'trs_actions', 'trs_activity_action_personal_feed' );

/**
 * Load a user's friends feed.
 *
 * @since 1.0.0
 *
 * @global object $trs trendr global settings
 * @global object $trm_query
 * @uses trs_is_active()
 * @uses trs_is_user_activity()
 * @uses trs_is_current_action()
 * @uses trs_get_friends_slug()
 * @uses trs_is_action_variable()
 * @uses status_header()
 *
 * @return bool False on failure
 */
function trs_activity_action_friends_feed() {
	global $trs, $trm_query;

	if ( !trs_is_active( 'friends' ) || !trs_is_user_activity() || !trs_is_current_action( trs_get_friends_slug() ) || !trs_is_action_variable( 'feed', 0 ) )
		return false;

	$trm_query->is_404 = false;
	status_header( 200 );

	include_once( 'feeds/trs-activity-friends-feed.php' );
	die;
}
add_action( 'trs_actions', 'trs_activity_action_friends_feed' );

/**
 * Load a user's my groups feed.
 *
 * @since 1.2.0
 *
 * @global object $trs trendr global settings
 * @global object $trm_query
 * @uses trs_is_active()
 * @uses trs_is_user_activity()
 * @uses trs_is_current_action()
 * @uses trs_get_groups_slug()
 * @uses trs_is_action_variable()
 * @uses status_header()
 *
 * @return bool False on failure
 */
function trs_activity_action_my_groups_feed() {
	global $trs, $trm_query;

	if ( !trs_is_active( 'groups' ) || !trs_is_user_activity() || !trs_is_current_action( trs_get_groups_slug() ) || !trs_is_action_variable( 'feed', 0 ) )
		return false;

	$trm_query->is_404 = false;
	status_header( 200 );

	include_once( 'feeds/trs-activity-mygroups-feed.php' );
	die;
}
add_action( 'trs_actions', 'trs_activity_action_my_groups_feed' );

/**
 * Load a user's @mentions feed.
 *
 * @since 1.2.0
 *
 * @global object $trs trendr global settings
 * @global object $trm_query
 * @uses trs_is_user_activity()
 * @uses trs_is_current_action()
 * @uses trs_is_action_variable()
 * @uses status_header()
 *
 * @return bool False on failure
 */
function trs_activity_action_mentions_feed() {
	global $trs, $trm_query;

	if ( !trs_is_user_activity() || !trs_is_current_action( 'mentions' ) || !trs_is_action_variable( 'feed', 0 ) )
		return false;

	$trm_query->is_404 = false;
	status_header( 200 );

	include_once( 'feeds/trs-activity-mentions-feed.php' );
	die;
}
add_action( 'trs_actions', 'trs_activity_action_mentions_feed' );

/**
 * Load a user's favorites feed.
 *
 * @since 1.2.0
 *
 * @global object $trs trendr global settings
 * @global object $trm_query
 * @uses trs_is_user_activity()
 * @uses trs_is_current_action()
 * @uses trs_is_action_variable()
 * @uses status_header()
 *
 * @return bool False on failure
 */
function trs_activity_action_favorites_feed() {
	global $trs, $trm_query;

	if ( !trs_is_user_activity() || !trs_is_current_action( 'favorites' ) || !trs_is_action_variable( 'feed', 0 ) )
		return false;

	$trm_query->is_404 = false;
	status_header( 200 );

	include_once( 'feeds/trs-activity-favorites-feed.php' );
	die;
}
add_action( 'trs_actions', 'trs_activity_action_favorites_feed' );

?>
