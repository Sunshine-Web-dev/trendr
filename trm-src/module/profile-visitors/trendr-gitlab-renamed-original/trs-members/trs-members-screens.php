<?php
/**
 * trendr Member Screens
 *
 * Handlers for member screens that aren't handled elsewhere
 *
 * @package trendr
 * @sutrsackage Members
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Handles the display of the profile page by loading the correct template file.
 *
 * @package trendr Members
 * @uses trs_core_load_template() Looks for and loads a template file within the current member theme (folder/filename)
 */
function trs_members_screen_display_profile() {
	do_action( 'trs_members_screen_display_profile' );
	trs_core_load_template( apply_filters( 'trs_members_screen_display_profile', 'members/single/home' ) );
}

/**
 * Handles the display of the members directory index
 *
 * @global object $trs
 *
 * @uses trs_is_user()
 * @uses trs_is_current_component()
 * @uses do_action()
 * @uses trs_core_load_template()
 * @uses apply_filters()
 */
function trs_members_screen_index() {
	if ( !trs_is_user() && trs_is_members_component() ) {
		trs_update_is_directory( true, 'members' );

		do_action( 'trs_members_screen_index' );

		trs_core_load_template( apply_filters( 'trs_members_screen_index', 'members/index' ) );
	}
}
add_action( 'trs_screens', 'trs_members_screen_index' );


?>
