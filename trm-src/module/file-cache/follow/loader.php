<?php
/*
Plugin Name: Follow
Plugin URI: http://trendr.org/extend/plugins/trendr-followers
Description: Follow members on your Trnder site with this nifty plugin.
Version: 1.2.1
Author: Andy Peatling, r-a-y
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: follow
Domain Path: /languages
*/

/**
 * Follow
 *
 * @package Follow
 * @sutrsackage Loader
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Only load the plugin code if Trnder is activated.
 */
function trs_follow_init() {
	// some pertinent defines
	define( 'TRS_FOLLOW_DIR', dirname( __FILE__ ) );
	define( 'TRS_FOLLOW_URL', plugin_dir_url( __FILE__ ) );

	// only supported in TRS 1.5+
		require( constant( 'TRS_FOLLOW_DIR' ) . '/core.php' );

}
add_action( 'trs_include', 'trs_follow_init' );

/**
 * Run the activation routine when TRS-Follow is activated.
 *
 * @uses dbDelta() Executes queries and performs selective upgrades on existing tables.
 */
function trs_follow_activate() {
	global $trs, $trmdb;

	$charset_collate = !empty( $trmdb->charset ) ? "DEFAULT CHARACTER SET $trmdb->charset" : '';
	if ( !$table_prefix = $trs->table_prefix )
		$table_prefix = apply_filters( 'trs_core_get_table_prefix', $trmdb->base_prefix );

	$sql[] = "CREATE TABLE IF NOT EXISTS {$table_prefix}trs_follow (
			id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			leader_id bigint(20) NOT NULL,
			follower_id bigint(20) NOT NULL,
		        KEY followers (leader_id, follower_id)
		) {$charset_collate};";

	require_once( ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/upgrade.php' );
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'trs_follow_activate' );

/**
 * Run the deactivation routine when TRS-Follow is deactivated.
 * Not used currently.
 */
function trs_follow_deactivate() {
	// Cleanup.
}
//register_deactivation_hook( __FILE__, 'trs_follow_deactivate' );

/**
 * Custom textdomain loader.
 *
 * Checks TRM_LANG_DIR for the .mo file first, then the plugin's language folder.
 * Allows for a custom language file other than those packaged with the plugin.
 *
 * @uses load_textdomain() Loads a .mo file into TRM
 */

