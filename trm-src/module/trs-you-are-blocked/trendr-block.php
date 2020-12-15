<?php
/**
 * Plugin Name: TRS | You Are Blocked!
 * Plugin URI: http://www.merovingi.com
 * Description: Let your trendr users block other members from contacting them or viewing their profile. Requires trendr 1.8 or higher.
 * Version: 1.0Beta5
 * Tags: trendr, block, users
 * Author: Gabriel S Merovingi
 * Author URI: http://www.merovingi.com
 * Author Email: info@merovingi.com
 * Requires at least: TRM 3.1
 * Tested up to: TRM 3.6.1
 * Text Domain: trsblock
 * Domain Path: /lang
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
define( 'TRSB_VERSION',      '1.0Beta5' );
define( 'TRSB_THIS',         __FILE__ );
define( 'TRSB_ROOT_DIR',     plugin_dir_path( TRSB_THIS ) );
define( 'TRSB_INCLUDES_DIR', TRSB_ROOT_DIR . 'includes/' );
define( 'TRSB_TEMPLATE_DIR', TRSB_ROOT_DIR . 'templates/' );

/**
 * Load Plugin with trendr
 * @since 1.0
 * @version 1.0
 */
add_action( 'trs_include', 'trsb_load_plugin' );
function trsb_load_plugin() {

	global $trsb_my_list;

	if ( ! defined( 'TRSB_ADMIN_CAP' ) )
		define( 'TRSB_ADMIN_CAP', 'edit_users' );

	require_once( TRSB_INCLUDES_DIR . 'trsb-functions.php' );

	if ( is_user_logged_in() ){
		trm_cache_add( 'trsb', get_user_meta( trs_loggedin_user_id() , '_block', true ), 'trsb_my_block_list' );
//asamir
		//	trm_enqueue_script( 'trs-you-are-blocked-js',  plugin_dir_url( __FILE__ ).'includes/trsb-block.js', array( 'jquery' ) );
	}
	require_once( TRSB_INCLUDES_DIR . 'trsb-actions.php' );
	trsb_handle_actions();
	add_action( 'trs_directory_members_item', 'trsb_insert_block_button_loop' );
	add_action( 'trs_before_member_header_meta',  'trsb_insert_block_button_profile' );

	require_once( TRSB_INCLUDES_DIR . 'trsb-templates.php' );
	//add_action( 'trs_members_screen_display_profile', 'trsb_load_blocked_profile_templates' );
	add_action( 'trs_screens', 'trsb_core_screen_profite', 1 );

	require_once( TRSB_INCLUDES_DIR . 'trsb-queries.php' );
	add_action( 'trs_pre_user_query_construct',     'trsb_adjust_user_query', 1 );
	add_filter( 'trs_get_total_member_count',       'trsb_adjust_total_count' );
	add_filter( 'trs_get_member_latest_update',     'trsb_adjust_latest_update' );
	add_filter( 'trs_activity_content_before_save', 'trsb_adjust_mentions', 1, 2 );
	// add_action( 'trs_activity_before_save', 'validationthemention' );
	require_once( TRSB_INCLUDES_DIR . 'trsb-activities.php' );
	add_filter( 'trs_activity_get',             'trsb_filter_activities', 10, 2 );
	add_filter( 'trs_get_member_latest_update', 'trsb_remove_activity_if_blocked' );

	require_once( TRSB_INCLUDES_DIR . 'trsb-profile.php' );
	add_action( 'trs_setup_nav',   'trsb_setup_navigation' );
	//add_action( 'admin_bar_menu', 'trsb_setup_tool_bar', 110 );

	if ( trs_is_active( 'friends' ) ) {
		require_once( TRSB_INCLUDES_DIR . 'trsb-friends.php' );
		add_filter( 'trs_is_friend', 'trsb_friend_check', 10, 2 );

		remove_action( 'trs_init', 'friends_action_add_friend' );
		add_action( 'trs_init',    'trsb_friends_action_add_friend' );
	}

	if ( trs_is_active( 'messages' ) ) {
		require_once( TRSB_INCLUDES_DIR . 'trsb-messages.php' );
		add_filter( 'trs_messages_recipients',       'trsb_check_message_receipients' );
		add_action( 'messages_message_before_save', 'trsb_before_message_send' );
	}
}
?>
