<?php
/**
 * TRS Activity Privacy ajax
 *
 * @package TRS-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register AJAX handlers for a list of actions.
 *
 */
function trs_activity_privacy_register_actions() {
	$actions = array(
		// update filters
		'update_activity_privacy'  => 'update_activity_privacy',
	);

	/**
	 * Register all of these AJAX handlers
	 *
	 * The "trm_ajax_" action is used for logged in users, and "trm_ajax_nopriv_"
	 * executes for users that aren't logged in. This is for backpat with TRS <1.6.
	 */
	foreach( $actions as $name => $function ) {
		add_action( 'trm_ajax_'        . $name, $function );
		add_action( 'trm_ajax_nopriv_' . $name, $function );
	}
}
add_action( 'trs_activity_privacy_load_core', 'trs_activity_privacy_register_actions', 1 );

/**
 * update the privacy
 *
 */
function update_activity_privacy() {
	// Bail if not a POST action
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	global $trs;

	// Sanitize the post object
	$activity_id = esc_attr( $_POST['id'] );
	$visibility = esc_attr( $_POST['visibility'] );

	$is_super_admin = is_super_admin();
    $trs_displayed_user_id = trs_displayed_user_id();
    $trs_loggedin_user_id = trs_loggedin_user_id();

   	$activity = trs_activity_get_specific( array( 'activity_ids' => $activity_id ) );
    // single out the activity
    $activity_single = $activity["activities"][0];

    // if is not a activity group
    if ( !isset( $activity_single->item_id ) || ( $activity_single->item_id == 0 ) ){
    	$levels = trs_get_profile_activity_privacy_levels();


    }
    else{
		    $levels = trs_get_groups_activity_privacy_levels();

    }

    if(function_exists('trs_get_profile__follow_groups_privacy_levels')){
        $levels = trs_get_profile__follow_groups_privacy_levels($levels);
    }

    if( isset( $visibility ) && in_array( $visibility, $levels )
        && ( $is_super_admin || ( $trs_loggedin_user_id == $activity_single->user_id ) ) ) {

	    trs_activity_update_meta( $activity_id, 'activity-privacy', $visibility );
    	//$visibility = trs_activity_get_meta( $activity_id, 'activity-privacy' );
    }
	exit;
}
