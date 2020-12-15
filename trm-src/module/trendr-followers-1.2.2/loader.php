<?php
/*
Plugin Name: trendr Follow
Plugin URI: http://trendrInc.org/extend/module/trendr-followers
Description: Follow members on your trendr site with this nifty plugin.
Version: 1.2.2
Author: Andy Peatling, r-a-y
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: trs-follow
Domain Path: /languages
*/

/**
 * TRS Follow
 *
 * @package TRS-Follow
 * @sutrsackage Loader
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Only load the plugin code if trendr is activated.
 */
function trs_follow_init() {
	// some pertinent defines
	define( 'TRS_FOLLOW_DIR', dirname( __FILE__ ) );
	define( 'TRS_FOLLOW_URL', plugin_dir_url( __FILE__ ) );

	// only supported in TRS 1.5+
	if ( version_compare( TRS_VERSION, '1.3', '>' ) ) {
		require( constant( 'TRS_FOLLOW_DIR' ) . '/trs-follow-core.php' );

	// show admin notice for users on TRS 1.2.x
	} else {
		$older_version_notice = sprintf( __( "Hey! TRS Follow v1.2 requires trendr 1.5 or higher.  If you are still using trendr 1.2 and you don't plan on upgrading, use <a href='%s'>TRS Follow v1.1.1 instead</a>.", 'trs-follow' ), 'https://github.com/r-a-y/trendr-followers/archive/1.1.x.zip' );

		add_action( 'admin_notices', create_function( '', "
			echo '<div class=\"error\"><p>' . $older_version_notice . '</p></div>';
		" ) );

		return;
	}
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
function trs_follow_localization() {
	$mofile		= sprintf( 'trs-follow-%s.mo', get_locale() );
	$mofile_global	= trailingslashit( TRM_LANG_DIR ) . $mofile;
	$mofile_local	= plugin_dir_path( __FILE__ ) . 'languages/' . $mofile;

	if ( is_readable( $mofile_global ) )
		return load_textdomain( 'trs-follow', $mofile_global );
	elseif ( is_readable( $mofile_local ) )
		return load_textdomain( 'trs-follow', $mofile_local );
	else
		return false;
}
add_action( 'plugins_loaded', 'trs_follow_localization' );
