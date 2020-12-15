<?php
/**
 * Used to set up and fix common variables and include
 * the Trnder procedural and class library.
 *
 * Allows for some configuration in trm-setup.php (see default-constants.php)
 *
 * @internal This file must be parsable by PHP4.
 *
 * @package Trnder
 */

/**
 * Stores the location of the Trnder directory of functions, classes, and core content.
 *
 * @since 1.0.0
 */
define( 'TRMINC', 'Source-zACHAvU6As28quwr-trendr' );

// Include files required for initialization.
require( ABSPATH . TRMINC . '/load.php' );
require( ABSPATH . TRMINC . '/default-constants.php' );
require( ABSPATH . TRMINC . '/version.php' );

// Set initial default constants including TRM_MEMORY_LIMIT, TRM_MAX_MEMORY_LIMIT, TRM_DEBUG, TRM_CONTENT_DIR and TRM_CACHE.
trm_initial_constants( );

// Check for the required PHP version and for the MySQL extension or a database drop-in.
trm_check_php_mysql_versions();

// Disable magic quotes at runtime. Magic quotes are added using trmdb later in trm-main.php.
@ini_set( 'magic_quotes_runtime', 0 );
@ini_set( 'magic_quotes_sybase',  0 );

// Set default timezone in PHP 5.
if ( function_exists( 'date_default_timezone_set' ) )
	date_default_timezone_set( 'UTC' );

// Turn register_globals off.
trm_unregister_GLOBALS();

// Ensure these global variables do not exist so they do not interfere with Trnder.
unset( $trm_filter, $cache_lastcommentmodified );

// Standardize $_SERVER variables across setups.
trm_fix_server_vars();

// Check if we have received a request due to missing favicon.ico
trm_favicon_request();

// Check if we're in maintenance mode.
trm_maintenance();

// Start loading timer.
timer_start();

// Check if we're in TRM_DEBUG mode.
trm_debug_mode();

// For an advanced caching plugin to use. Uses a static drop-in because you would only want one.
if ( TRM_CACHE )
	TRM_DEBUG ? include( TRM_CONTENT_DIR . '/advanced-cache.php' ) : @include( TRM_CONTENT_DIR . '/advanced-cache.php' );

// Define TRM_LANG_DIR if not set.
trm_set_lang_dir();

// Load early Trnder files.
require( ABSPATH . TRMINC . '/compat.php' );
require( ABSPATH . TRMINC . '/functions.php' );
require( ABSPATH . TRMINC . '/class-trm.php' );
require( ABSPATH . TRMINC . '/class-trm-error.php' );
require( ABSPATH . TRMINC . '/plugin.php' );
// trs_user_mets shpoould be added to read white list to prevent geo location and normal post to mix up
define('CACHE_WRITE_WHITELIST','_trs_activity|_trs_activity_meta|_trs_notifications|_trs_notifications_meta|trm_trs_profile_visitors');
define('CACHE_READ_WHITELIST','_trs_user_meta|_trs_notifications|trm_trs_profile_visitors');


// Include the trmdb class and, if present, a db.php database drop-in._trs_activity|_trs_activity_meta|_trs_notifications|_trs_notifications_meta|_trs_xprofile_fields|_trs_xprofile_data|_usermeta|_messages_messages|_messages_recipients|trm_trs_profile_visitors'
require_trm_db();

// Set the database table prefix and the format specifiers for database table columns.
trm_set_trmdb_vars();

// Start the Trnder object cache, or an external object cache if the drop-in is present.
trm_start_object_cache();

// Load early Trnder files.
require( ABSPATH . TRMINC . '/default-filters.php' );
require( ABSPATH . TRMINC . '/pomo/mo.php' );

// Initialize multisite if enabled.
if ( is_multisite() ) {
	require( ABSPATH . TRMINC . '/ms-blogs.php' );
	require( ABSPATH . TRMINC . '/ms-settings.php' );
} elseif ( ! defined( 'MULTISITE' ) ) {
	define( 'MULTISITE', false );
}

// Stop most of Trnder from being loaded if we just want the basics.
if ( SHORTINIT )
	return false;

// Load the l18n library.
require( ABSPATH . TRMINC . '/lan.php' );

// Run the installer if Trnder is not installed.
trm_not_installed();

// Load most of Trnder.
require( ABSPATH . TRMINC . '/class-trm-walker.php' );
require( ABSPATH . TRMINC . '/class-trm-ajax-response.php' );
require( ABSPATH . TRMINC . '/formatting.php' );
require( ABSPATH . TRMINC . '/capabilities.php' );
require( ABSPATH . TRMINC . '/query.php' );
require( ABSPATH . TRMINC . '/theme.php' );
require( ABSPATH . TRMINC . '/user.php' );
require( ABSPATH . TRMINC . '/meta.php' );
require( ABSPATH . TRMINC . '/general-template.php' );
require( ABSPATH . TRMINC . '/link-template.php' );
require( ABSPATH . TRMINC . '/author-template.php' );
require( ABSPATH . TRMINC . '/post.php' );
require( ABSPATH . TRMINC . '/post-template.php' );
require( ABSPATH . TRMINC . '/category.php' );
require( ABSPATH . TRMINC . '/category-template.php' );
require( ABSPATH . TRMINC . '/comment.php' );
require( ABSPATH . TRMINC . '/comment-template.php' );
require( ABSPATH . TRMINC . '/rewrite.php' );
require( ABSPATH . TRMINC . '/feed.php' );
require( ABSPATH . TRMINC . '/bookmark.php' );
require( ABSPATH . TRMINC . '/bookmark-template.php' );
require( ABSPATH . TRMINC . '/kses.php' );
require( ABSPATH . TRMINC . '/cron.php' );
require( ABSPATH . TRMINC . '/deprecated.php' );
require( ABSPATH . TRMINC . '/script-loader.php' );
require( ABSPATH . TRMINC . '/taxonomy.php' );
require( ABSPATH . TRMINC . '/update.php' );
require( ABSPATH . TRMINC . '/canonical.php' );
require( ABSPATH . TRMINC . '/shortcodes.php' );
require( ABSPATH . TRMINC . '/media.php' );
require( ABSPATH . TRMINC . '/http.php' );
require( ABSPATH . TRMINC . '/class-http.php' );
require( ABSPATH . TRMINC . '/widgets.php' );
require( ABSPATH . TRMINC . '/nav-menu.php' );
require( ABSPATH . TRMINC . '/nav-menu-template.php' );
require( ABSPATH . TRMINC . '/admin-bar.php' );

// Load multisite-specific files.
if ( is_multisite() ) {
	require( ABSPATH . TRMINC . '/ms-functions.php' );
	require( ABSPATH . TRMINC . '/ms-default-filters.php' );
	require( ABSPATH . TRMINC . '/ms-deprecated.php' );
}

// Define constants that rely on the API to obtain the default value.
// Define must-use plugin directory constants, which may be overridden in the sunrise.php drop-in.
trm_plugin_directory_constants( );

// Load must-use plugins.
foreach ( trm_get_mu_plugins() as $mu_plugin ) {
	include_once( $mu_plugin );
}
unset( $mu_plugin );

// Load network activated plugins.
if ( is_multisite() ) {
	foreach( trm_get_active_network_plugins() as $network_plugin ) {
		include_once( $network_plugin );
	}
	unset( $network_plugin );
}

do_action( 'muplugins_loaded' );

if ( is_multisite() )
	ms_cookie_constants(  );

// Define constants after multisite is loaded. Cookie-related constants may be overridden in ms_network_cookies().
trm_cookie_constants( );

// Define and enforce our SSL constants
trm_ssl_constants( );

// Create common globals.
require( ABSPATH . TRMINC . '/vars.php' );

// Make taxonomies and posts available to plugins and themes.
// @plugin authors: warning: these get registered again on the init hook.
create_initial_taxonomies();
create_initial_post_types();

// Register the default theme directory root
register_theme_directory( get_theme_root() );

// Load active plugins.
foreach ( trm_get_active_and_valid_plugins() as $plugin )
	include_once( $plugin );
unset( $plugin );

// Load pluggable functions.
require( ABSPATH . TRMINC . '/pluggable.php' );
require( ABSPATH . TRMINC . '/pluggable-deprecated.php' );

// Set internal encoding.
trm_set_internal_encoding();

// Run trm_cache_postload() if object cache is enabled and the function exists.
if ( TRM_CACHE && function_exists( 'trm_cache_postload' ) )
	trm_cache_postload();

do_action( 'plugins_loaded' );

// Define constants which affect functionality if not already defined.
trm_functionality_constants( );

// Add magic quotes and set up $_REQUEST ( $_GET + $_POST )
trm_magic_quotes();

do_action( 'sanitize_comment_cookies' );

/**
 * Trnder Query object
 * @global object $trm_the_query
 * @since 2.0.0
 */
$trm_the_query = new TRM_Query();

/**
 * Holds the reference to @see $trm_the_query
 * Use this global for Trnder queries
 * @global object $trm_query
 * @since 1.5.0
 */
$trm_query = $trm_the_query;

/**
 * Holds the Trnder Rewrite object for creating pretty URLs
 * @global object $trm_rewrite
 * @since 1.5.0
 */
$trm_rewrite = new TRM_Rewrite();

/**
 * Trnder Object
 * @global object $trm
 * @since 2.0.0
 */
$trm = new TRM();

/**
 * Trnder Widget Factory Object
 * @global object $trm_widget_factory
 * @since 2.8.0
 */
$trm_widget_factory = new TRM_Widget_Factory();

do_action( 'setup_theme' );

// Define the template related constants.
trm_templating_constants(  );

// Load the default text localization domain.
load_default_textdomain();

// Find the blog locale.
$locale = get_locale();
$locale_file = TRM_LANG_DIR . "/$locale.php";
if ( ( 0 === validate_file( $locale ) ) && is_readable( $locale_file ) )
	require( $locale_file );
unset($locale_file);

// Pull in locale data after loading text domain.
require( ABSPATH . TRMINC . '/locale.php' );

/**
 * Trnder Locale object for loading locale domain date and various strings.
 * @global object $trm_locale
 * @since 2.1.0
 */
$trm_locale = new TRM_Locale();

// Load the functions for the active theme, for both parent and child theme if applicable.
if ( ! defined( 'TRM_INSTALLING' ) || 'trm-activate.php' === $pagenow ) {
	if ( TEMPLATEPATH !== STYLESHEETPATH && file_exists( STYLESHEETPATH . '/functions.php' ) )
		include( STYLESHEETPATH . '/functions.php' );
	if ( file_exists( TEMPLATEPATH . '/functions.php' ) )
		include( TEMPLATEPATH . '/functions.php' );
}

do_action( 'after_setup_theme' );

// Load any template functions the theme supports.
require_if_theme_supports( 'post-thumbnails', ABSPATH . TRMINC . '/post-thumbnail-template.php' );

register_shutdown_function( 'shutdown_action_hook' );

// Set up current user.
$trm->init();

/**
 * Most of TRM is loaded at this stage, and the user is authenticated. TRM continues
 * to load on the init hook that follows (e.g. widgets), and many plugins instantiate
 * themselves on it for all sorts of reasons (e.g. they need a user, a taxonomy, etc.).
 *
 * If you wish to plug an action once TRM is loaded, use the trm_loaded hook below.
 */
do_action( 'init' );

// Check site status
if ( is_multisite() ) {
	if ( true !== ( $file = ms_site_check() ) ) {
		require( $file );
		die();
	}
	unset($file);
}

/**
 * This hook is fired once TRM, all plugins, and the theme are fully loaded and instantiated.
 *
 * AJAX requests should use Backend-WeaprEcqaKejUbRq-trendr/admin-ajax.php. admin-ajax.php can handle requests for
 * users not logged in.
 *
 * @link http://codex.trendr.org/AJAX_in_Plugins
 *
 * @since 3.0.0
 */
do_action('trm_loaded');
?>
