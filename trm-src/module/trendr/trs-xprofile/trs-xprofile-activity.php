<?php
/**
 * trendr XProfile Activity & Notification Functions
 *
 * These functions handle the recording, deleting and formatting of activity and
 * notifications for the user and for this specific component.
 *
 * @package trendr
 * @sutrsackage XProfile
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function xprofile_register_activity_actions() {
	global $trs;

	if ( trs_is_active( 'activity' ) )
		return false;

	// Register the activity stream actions for this component
	trs_activity_set_action( $trs->profile->id, 'new_member',      __( 'New member registered', 'trendr' ) );
	trs_activity_set_action( $trs->profile->id, 'updated_profile', __( 'Updated Profile',       'trendr' ) );

	do_action( 'xprofile_register_activity_actions' );
}
add_action( 'trs_register_activity_actions', 'xprofile_register_activity_actions' );

/**
 * Records activity for the logged in user within the profile component so that
 * it will show in the users activity stream (if installed)
 *
 * @package trendr XProfile
 * @param $args Array containing all variables used after extract() call
 * @global $trs The global trendr settings variable created in trs_core_current_times()
 * @uses trs_activity_record() Adds an entry to the activity component tables for a specific activity
 */
function xprofile_record_activity( $args = '' ) {
	global $trs;

	if ( !trs_is_active( 'activity' ) )
		return false;

	$defaults = array (
		'user_id'           => $trs->loggedin_user->id,
		'action'            => '',
		'content'           => '',
		'primary_link'      => '',
		'component'         => $trs->profile->id,
		'type'              => false,
		'item_id'           => false,
		'secondary_item_id' => false,
		'recorded_time'     => trs_core_current_time(),
		'hide_sitewide'     => false
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	return trs_activity_add( array(
		'user_id'           => $user_id,
		'action'            => $action,
		'content'           => $content,
		'primary_link'      => $primary_link,
		'component'         => $component,
		'type'              => $type,
		'item_id'           => $item_id,
		'secondary_item_id' => $secondary_item_id,
		'recorded_time'     => $recorded_time,
		'hide_sitewide'     => $hide_sitewide
	) );
}

/**
 * Deletes activity for a user within the profile component so that
 * it will be removed from the users activity stream and sitewide stream (if installed)
 *
 * @package trendr XProfile
 * @param $args Array containing all variables used after extract() call
 * @global object $trs Global trendr settings object
 * @uses trs_activity_delete() Deletes an entry to the activity component tables for a specific activity
 */
function xprofile_delete_activity( $args = '' ) {
	global $trs;

	if ( trs_is_active( 'activity' ) ) {

		extract( $args );

		trs_activity_delete_by_item_id( array(
			'item_id'           => $item_id,
			'component'         => $trs->profile->id,
			'type'              => $type,
			'user_id'           => $user_id,
			'secondary_item_id' => $secondary_item_id
		) );
	}
}

function xprofile_register_activity_action( $key, $value ) {
	global $trs;

	if ( !trs_is_active( 'activity' ) )
		return false;

	return apply_filters( 'xprofile_register_activity_action', trs_activity_set_action( $trs->profile->id, $key, $value ), $key, $value );
}

/**
 * Adds an activity stream item when a user has uploaded a new portrait.
 *
 * @package trendr XProfile
 * @global object $trs Global trendr settings object
 * @uses trs_activity_add() Adds an entry to the activity component tables for a specific activity
 */
function trs_xprofile_new_portrait_activity() {
	global $trs;

	if ( !trs_is_active( 'activity' ) )
		return false;

	$user_id = apply_filters( 'trs_xprofile_new_portrait_user_id', $trs->displayed_user->id );

	$userlink = trs_core_get_userlink( $user_id );

	trs_activity_add( array(
		'user_id' => $user_id,
		'action' => apply_filters( 'trs_xprofile_new_portrait_action', sprintf( __( '%s changed their profile picture', 'trendr' ), $userlink ), $user_id ),
		'component' => 'profile',
		'type' => 'new_portrait'
	) );
}
add_action( 'xprofile_portrait_uploaded', 'trs_xprofile_new_portrait_activity' );
?>