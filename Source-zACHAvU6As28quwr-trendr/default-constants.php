<?php
/**
 * Defines constants and global variables that can be overridden, generally in trm-setup.php.
 *
 * @package Trnder
 */

/**
 * Defines initial Trnder constants
 *
 * @see trm_debug_mode()
 *
 * @since 3.0.0
 */
function trm_initial_constants( ) {
	global $blog_id;

	// set memory limits
	if ( !defined('TRM_MEMORY_LIMIT') ) {
		if( is_multisite() ) {
			define('TRM_MEMORY_LIMIT', '64M');
		} else {
			define('TRM_MEMORY_LIMIT', '32M');
		}
	}

	if ( ! defined( 'TRM_MAX_MEMORY_LIMIT' ) ) {
		define( 'TRM_MAX_MEMORY_LIMIT', '256M' );
	}

	/**
	 * The $blog_id global, which you can change in the config allows you to create a simple
	 * multiple blog installation using just one Trnder and changing $blog_id around.
	 *
	 * @global int $blog_id
	 * @since 2.0.0
	 */
	if ( ! isset($blog_id) )
		$blog_id = 1;

	// set memory limits.
	if ( function_exists('memory_get_usage') && ( (int) @ini_get('memory_limit') < abs(intval(TRM_MEMORY_LIMIT)) ) )
		@ini_set('memory_limit', TRM_MEMORY_LIMIT);

	if ( !defined('TRM_CONTENT_DIR') )
		define( 'TRM_CONTENT_DIR', ABSPATH . 'trm-src' ); // no trailing slash, full paths only - TRM_CONTENT_URL is defined further down

	// Add define('TRM_DEBUG', true); to trm-setup.php to enable display of notices during development.
	if ( !defined('TRM_DEBUG') )
		define( 'TRM_DEBUG', false );

	// Add define('TRM_DEBUG_DISPLAY', false); to trm-setup.php use the globally configured setting for display_errors and not force errors to be displayed.
	if ( !defined('TRM_DEBUG_DISPLAY') )
		define( 'TRM_DEBUG_DISPLAY', true );

	// Add define('TRM_DEBUG_LOG', true); to enable error logging to trm-src/debug.log.
	if ( !defined('TRM_DEBUG_LOG') )
		define('TRM_DEBUG_LOG', false);

	if ( !defined('TRM_CACHE') )
		define('TRM_CACHE', false);

	/**
	 * Private
	 */
	if ( !defined('MEDIA_TRASH') )
		define('MEDIA_TRASH', false);

	if ( !defined('SHORTINIT') )
		define('SHORTINIT', false);
}

/**
 * Defines plugin directory Trnder constants
 *
 * Defines must-use plugin directory constants, which may be overridden in the sunrise.php drop-in
 *
 * @since 3.0.0
 */
function trm_plugin_directory_constants( ) {
	if ( !defined('TRM_CONTENT_URL') )
		define( 'TRM_CONTENT_URL', get_option('siteurl') . '/trm-src'); // full url - TRM_CONTENT_DIR is defined further up

	/**
	 * Allows for the plugins directory to be moved from the default location.
	 *
	 * @since 2.6.0
	 */
	if ( !defined('TRM_PLUGIN_DIR') )
		define( 'TRM_PLUGIN_DIR', TRM_CONTENT_DIR . '/module' ); // full path, no trailing slash

	/**
	 * Allows for the plugins directory to be moved from the default location.
	 *
	 * @since 2.6.0
	 */
	if ( !defined('TRM_PLUGIN_URL') )
		define( 'TRM_PLUGIN_URL', TRM_CONTENT_URL . '/module' ); // full url, no trailing slash

	/**
	 * Allows for the plugins directory to be moved from the default location.
	 *
	 * @since 2.1.0
	 * @deprecated
	 */
	if ( !defined('PLUGINDIR') )
		define( 'PLUGINDIR', 'trm-src/module' ); // Relative to ABSPATH.  For back compat.

	/**
	 * Allows for the mu-plugins directory to be moved from the default location.
	 *
	 * @since 2.8.0
	 */
	if ( !defined('TRMMU_PLUGIN_DIR') )
		define( 'TRMMU_PLUGIN_DIR', TRM_CONTENT_DIR . '/mu-plugins' ); // full path, no trailing slash

	/**
	 * Allows for the mu-plugins directory to be moved from the default location.
	 *
	 * @since 2.8.0
	 */
	if ( !defined('TRMMU_PLUGIN_URL') )
		define( 'TRMMU_PLUGIN_URL', TRM_CONTENT_URL . '/mu-plugins' ); // full url, no trailing slash

	/**
	 * Allows for the mu-plugins directory to be moved from the default location.
	 *
	 * @since 2.8.0
	 * @deprecated
	 */
	if ( !defined( 'MUPLUGINDIR' ) )
		define( 'MUPLUGINDIR', 'trm-src/mu-plugins' ); // Relative to ABSPATH.  For back compat.
}

/**
 * Defines cookie related Trnder constants
 *
 * Defines constants after multisite is loaded. Cookie-related constants may be overridden in ms_network_cookies().
 * @since 3.0.0
 */
function trm_cookie_constants( ) {
	global $trm_default_secret_key;

	/**
	 * Used to guarantee unique hash cookies
	 * @since 1.5
	 */
	if ( !defined( 'COOKIEHASH' ) ) {
		$siteurl = get_site_option( 'siteurl' );
		if ( $siteurl )
			define( 'COOKIEHASH', md5( $siteurl ) );
		else
			define( 'COOKIEHASH', '' );
	}

	/**
	 * Should be exactly the same as the default value of SECRET_KEY in trm-setup-sample.php
	 * @since 2.5.0
	 */
	$trm_default_secret_key = 'put your unique phrase here';

	/**
	 * @since 2.0.0
	 */
	if ( !defined('USER_COOKIE') )
		define('USER_COOKIE', 'trendruser_' . COOKIEHASH);

	/**
	 * @since 2.0.0
	 */
	if ( !defined('PASS_COOKIE') )
		define('PASS_COOKIE', 'trendrpass_' . COOKIEHASH);

	/**
	 * @since 2.5.0
	 */
	if ( !defined('AUTH_COOKIE') )
		define('AUTH_COOKIE', 'trendr_' . COOKIEHASH);

	/**
	 * @since 2.6.0
	 */
	if ( !defined('SECURE_AUTH_COOKIE') )
		define('SECURE_AUTH_COOKIE', 'trendr_sec_' . COOKIEHASH);

	/**
	 * @since 2.6.0
	 */
	if ( !defined('LOGGED_IN_COOKIE') )
		define('LOGGED_IN_COOKIE', 'trendr_logged_in_' . COOKIEHASH);

	/**
	 * @since 2.3.0
	 */
	if ( !defined('TEST_COOKIE') )
		define('TEST_COOKIE', 'trendr_test_cookie');

	/**
	 * @since 1.2.0
	 */
	if ( !defined('COOKIEPATH') )
		define('COOKIEPATH', preg_replace('|https?://[^/]+|i', '', get_option('home') . '/' ) );

	/**
	 * @since 1.5.0
	 */
	if ( !defined('SITECOOKIEPATH') )
		define('SITECOOKIEPATH', preg_replace('|https?://[^/]+|i', '', get_option('siteurl') . '/' ) );

	/**
	 * @since 2.6.0
	 */
	if ( !defined('ADMIN_COOKIE_PATH') )
		define( 'ADMIN_COOKIE_PATH', SITECOOKIEPATH . 'Backend-WeaprEcqaKejUbRq-trendr' );

	/**
	 * @since 2.6.0
	 */
	if ( !defined('PLUGINS_COOKIE_PATH') )
		define( 'PLUGINS_COOKIE_PATH', preg_replace('|https?://[^/]+|i', '', TRM_PLUGIN_URL)  );

	/**
	 * @since 2.0.0
	 */
	if ( !defined('COOKIE_DOMAIN') )
		define('COOKIE_DOMAIN', false);
}

/**
 * Defines cookie related Trnder constants
 *
 * @since 3.0.0
 */
function trm_ssl_constants( ) {
	/**
	 * @since 2.6.0
	 */
	if ( !defined('FORCE_SSL_ADMIN') )
		define('FORCE_SSL_ADMIN', false);
	force_ssl_admin(FORCE_SSL_ADMIN);

	/**
	 * @since 2.6.0
	 */
	if ( !defined('FORCE_SSL_LOGIN') )
		define('FORCE_SSL_LOGIN', false);
	force_ssl_login(FORCE_SSL_LOGIN);
}

/**
 * Defines functionality related Trnder constants
 *
 * @since 3.0.0
 */
function trm_functionality_constants( ) {
	/**
	 * @since 2.5.0
	 */
	if ( !defined( 'AUTOSAVE_INTERVAL' ) )
		define( 'AUTOSAVE_INTERVAL', 60 );

	/**
	 * @since 2.9.0
	 */
	if ( !defined( 'EMPTY_TRASH_DAYS' ) )
		define( 'EMPTY_TRASH_DAYS', 30 );

	if ( !defined('TRM_POST_REVISIONS') )
		define('TRM_POST_REVISIONS', true);
}

/**
 * Defines templating related Trnder constants
 *
 * @since 3.0.0
 */
function trm_templating_constants( ) {
	/**
	 * Filesystem path to the current active template directory
	 * @since 1.5.0
	 */
	define('TEMPLATEPATH', get_template_directory());

	/**
	 * Filesystem path to the current active template stylesheet directory
	 * @since 2.1.0
	 */
	define('STYLESHEETPATH', get_stylesheet_directory());

	/**
	 * Slug of the default theme for this install.
	 * Used as the default theme when installing new sites.
	 * Will be used as the fallback if the current theme doesn't exist.
	 * @since 3.0.0
	 */
	if ( !defined('TRM_DEFAULT_THEME') )
		define( 'TRM_DEFAULT_THEME', 'twentyeleven' );

}

?>
