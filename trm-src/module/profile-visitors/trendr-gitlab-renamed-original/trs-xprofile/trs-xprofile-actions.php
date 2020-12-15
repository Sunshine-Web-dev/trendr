<?php
/*******************************************************************************
 * Action functions are exactly the same as screen functions, however they do
 * not have a template screen associated with them. Usually they will send the
 * user back to the default screen after execution.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * This function runs when an action is set for a screen:
 * example.com/members/andy/profile/change-portrait/ [delete-portrait]
 *
 * The function will delete the active portrait for a user.
 *
 * @package trendr Xprofile
 * @global object $trs Global trendr settings object
 * @uses trs_core_delete_portrait() Deletes the active portrait for the logged in user.
 * @uses add_action() Runs a specific function for an action when it fires.
 * @uses trs_core_load_template() Looks for and loads a template file within the current member theme (folder/filename)
 */
function xprofile_action_delete_portrait() {
	global $trs;

	if ( !trs_is_user_change_portrait() || !trs_is_action_variable( 'delete-portrait', 0 ) )
		return false;

	// Check the nonce
	check_admin_referer( 'trs_delete_portrait_link' );

	if ( !trs_is_my_profile() && !is_super_admin() )
		return false;

	if ( trs_core_delete_existing_portrait( array( 'item_id' => $trs->displayed_user->id ) ) )
		trs_core_add_message( __( 'Your portrait was deleted successfully!', 'trendr' ) );
	else
		trs_core_add_message( __( 'There was a problem deleting that portrait, please try again.', 'trendr' ), 'error' );

	trs_core_redirect( trm_get_referer() );
}
add_action( 'trs_actions', 'xprofile_action_delete_portrait' );

?>
