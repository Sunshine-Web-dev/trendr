<?php
/**
 * Plugin Name: trendr
 * Plugin URI:  http://trendr.org
 * Description: Social networking in a box. Build a social network for your company, school, sports team or niche community all based on the power and flexibility of WordPress.
 * Author:      The trendr Community
 * Version:     1.5.4
 * Author URI:  http://trendr.org/community/members/
 * Network:     true
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Constants *****************************************************************/
global $trmdb;

// Define the trendr version
if ( !defined( 'TRS_VERSION' ) )
	define( 'TRS_VERSION', '1.5.4' );

// Define the database version
if ( !defined( 'TRS_DB_VERSION' ) )
	define( 'TRS_DB_VERSION', 3820 );

// Place your custom code (actions/filters) in a file called
// '/module/trs-custom.php' and it will be loaded before anything else.
if ( file_exists( TRM_PLUGIN_DIR . '/trs-custom.php' ) )
	require( TRM_PLUGIN_DIR . '/trs-custom.php' );

// Define on which blog ID trendr should run
if ( !defined( 'TRS_ROOT_BLOG' ) ) {

	// Root blog is the main site on this network
	if ( is_multisite() && !defined( 'TRS_ENABLE_MULTIBLOG' ) ) {
		$current_site = get_current_site();
		$root_blog_id = $current_site->blog_id;

	// Root blog is every site on this network
	} elseif ( is_multisite() && defined( 'TRS_ENABLE_MULTIBLOG' ) ) {
		$root_blog_id = get_current_blog_id();

	// Root blog is the only blog on this network
	} elseif( !is_multisite() ) {
		$root_blog_id = 1;
	}

	define( 'TRS_ROOT_BLOG', $root_blog_id );
}

// Path and URL
if ( !defined( 'TRS_PLUGIN_DIR' ) )
	define( 'TRS_PLUGIN_DIR', TRM_PLUGIN_DIR . '/trendr' );

if ( !defined( 'TRS_PLUGIN_URL' ) )
	define( 'TRS_PLUGIN_URL', plugins_url( 'trendr' ) );

// The search slug has to be defined nice and early because of the way search requests are loaded
if ( !defined( 'TRS_SEARCH_SLUG' ) )
	define( 'TRS_SEARCH_SLUG', 'search' );

// Setup the trendr theme directory
register_theme_directory( TRS_PLUGIN_DIR . '/trs-themes' );

/** Loader ********************************************************************/

// Load the TRM abstraction file so trendr can run on all WordPress setups.
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-trmabstraction.php' );

// Test to see whether this is a new installation or an upgraded version of trendr
if ( !$trs->database_version = get_site_option( 'trs-db-version' ) ) {
	if ( $trs->database_version = get_option( 'trs-db-version' ) ) {
		$trs->is_network_activate = 1;
	} else {
		$trs->database_version = get_site_option( 'trs-core-db-version' );  // TRS 1.2 option
	}
}

// This is a new installation.
if ( empty( $trs->database_version ) ) {
	$trs->maintenance_mode = 'install';
	require( TRS_PLUGIN_DIR . '/trs-core/admin/trs-core-update.php' );

// There is a previous installation
} else {
	// Load core
	require( TRS_PLUGIN_DIR . '/trs-core/trs-core-loader.php' );

	// Check if an update is required
	if ( (int)$trs->database_version < (int)constant( 'TRS_DB_VERSION' ) || isset( $trs->is_network_activate ) ) {
		$trs->maintenance_mode = 'update';
		require( TRS_PLUGIN_DIR . '/trs-core/admin/trs-core-update.php' );
	}
}

/** Activation ****************************************************************/

if ( !function_exists( 'trs_loader_activate' ) ) :
/**
 * Defines TRS's activation routine.
 *
 * Most of TRS's crucial setup is handled by the setup wizard. This function takes care of some
 * issues with incompatible legacy themes, and provides a hook for other functions to know that
 * TRS has been activated.
 *
 * @package trendr Core
*/
function trs_loader_activate() {
	// Force refresh theme roots.
	delete_site_transient( 'theme_roots' );

	if ( !function_exists( 'get_blog_option' ) )
		require ( TRM_PLUGIN_DIR . '/trendr/trs-core/trs-core-trmabstraction.php' );

	if ( !function_exists( 'trs_get_root_blog_id' ) )
		require ( TRM_PLUGIN_DIR . '/trendr/trs-core/trs-core-functions.php' );

	// Switch the user to the new trs-default if they are using the old
	// trs-default on activation.
	if ( 'trs-sn-parent' == get_blog_option( trs_get_root_blog_id(), 'template' ) && 'trs-default' == get_blog_option( trs_get_root_blog_id(), 'stylesheet' ) )
		switch_theme( 'trs-default', 'trs-default' );

	do_action( 'trs_loader_activate' );
}
register_activation_hook( 'trendr/trs-loader.php', 'trs_loader_activate' );
endif;

if ( !function_exists( 'trs_loader_deactivate' ) ) :
// Deactivation Function
function trs_loader_deactivate() {
	do_action( 'trs_loader_deactivate' );
}
register_deactivation_hook( 'trendr/trs-loader.php', 'trs_loader_deactivate' );
endif;

?>
