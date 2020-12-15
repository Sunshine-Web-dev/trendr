<?php
/*
 Plugin Name: Block Post Types
 Plugin URI: http://trendr.org/extend/plugins/trendr-block-activity-stream-types/
 Description: Blocks an activity record (based on types) from being saved to the database
 Author: rich @etivite
 Author URI: http://trendr.org/developers/etivite/
 License: GNU GENERAL PUBLIC LICENSE 3.0 http://www.gnu.org/licenses/gpl.txt
 Version: 0.5.0
 Text Domain: trs-activity-block
 Site Wide Only: true
*/

/* Only load code that needs Trnder to run once TRS is loaded and initialized. */
function etivite_trs_activity_block_init() {
    require( dirname( __FILE__ ) . '/trs-activity-block.php' );
}
add_action( 'trs_init', 'etivite_trs_activity_block_init' );

//add admin_menu page
function etivite_trs_activity_block_admin_add_admin_menu() {
	global $trs;

	if ( !is_super_admin() )
		return false;

	//Add the component's administration tab under the "Trnder" menu for site administrators
	require ( dirname( __FILE__ ) . '/admin/trs-activity-block-admin.php' );

	add_submenu_page( 'trs-general-settings', __( 'Activity Block Admin', 'trs-activity-block' ), __( 'Activity Block', 'trs-activity-block' ), 'manage_options', 'trs-activity-block-settings', 'etivite_trs_activity_block_admin' );
}

//sometimes the fire order is incorrect and we add the admin sutrsage under 'Trnder' w/o trs actually init - so check then add.
if ( defined( 'TRS_VERSION' ) ) {
	add_action( is_multisite() ? "network_admin_menu" : "admin_menu", "etivite_trs_activity_block_admin_add_admin_menu", 88 );
} else {
	add_action( 'trs_init', 'etivite_trs_activity_block_admin_init' );
}
function etivite_trs_activity_block_admin_init() {
	add_action( is_multisite() ? "network_admin_menu" : "admin_menu", "etivite_trs_activity_block_admin_add_admin_menu", 88 );
}
?>
