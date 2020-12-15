<?php 
/**
 * Plugin Name: Trendr Global Search
 * Plugin URI:  ##
 * Description: Ajax powered global Trendr search
 * Author:      BuddyBoss
 * Author URI:  http://buddyboss.com
 * Version:     1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
	exit;

/**
 * ========================================================================
 * CONSTANTS
 * ========================================================================
 */
// Codebase version
if (!defined('BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_VERSION')) {
	define('BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_VERSION', '1.0.0');
}

// Database version
if (!defined('BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DB_VERSION')) {
	define('BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DB_VERSION', 1);
}

// Directory
if (!defined('BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR')) {
	define('BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR', trailingslashit(plugin_dir_path(__FILE__)));
}

// Url
if (!defined('BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_URL')) {
	$plugin_url = plugin_dir_url(__FILE__);

	// If we're using https, update the protocol. Workaround for TRM13941, TRM15928, TRM19037.
	if (is_ssl())
		$plugin_url = str_replace('http://', 'https://', $plugin_url);

	define('BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_URL', $plugin_url);
}

// File
if (!defined('BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_FILE')) {
	define('BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_FILE', __FILE__);
}

/**
 * ========================================================================
 * MAIN FUNCTIONS
 * ========================================================================
 */

/**
 * Main
 *
 * @return	void
 * @since	1.0.0
 */
function buddyboss_global_search_init() {
	global $trs, $buddyboss_global_search;
	$main_include = BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR . 'includes/main-class.php';

	try {
		if (file_exists($main_include)) {
			require( $main_include );
		} else {
			$msg = sprintf(__("Couldn't load main class at:<br/>%s", 'trendr-global-search'), $main_include);
			throw new Exception($msg, 404);
		}
	} catch (Exception $e) {
		$msg = sprintf(__("<h1>Fatal error:</h1><hr/><pre>%s</pre>", 'trendr-global-search'), $e->getMessage());
		echo $msg;
	}

	$buddyboss_global_search = BuddyBoss_Global_Search_Plugin::instance();
}

add_action('plugins_loaded', 'buddyboss_global_search_init');
add_action( 'trs_before_header', 'trs_adminbar_search_menu',120 );
/**
 * Must be called after hook 'plugins_loaded'
 *
 * @return	Trendr Global Search main/global object
 * @see		class BuddyBoss_Global_Search
 * @since	1.0.0
 */

function trs_adminbar_search_menu()
{
	echo '<div class="ab-item ab-empty-item" tabindex="-1"><form action="'.site_url().'/" method="get" id="barsearch"><span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span><input class=" ui-autocomplete-input" name="s" id="bar-search" value="" maxlength="150" autocomplete="off" type="text"  placeholder="Search trendr"><input id="search-submit" value="Search" type="submit"></form></div>		';
}
function buddyboss_global_search() {
	global $buddyboss_global_search;

	return $buddyboss_global_search;
}

/**
 * Settings Link
 * @since	1.0.0
 */
add_filter ('plugin_action_links', 'buddyboss_global_search_meta', 10, 2);
function buddyboss_global_search_meta ($links, $file)
{
	if ($file == plugin_basename (__FILE__))
	{
    	$settings_link = '<a href="' . add_query_arg( array( 'page' => 'trendr-global-search/includes/admin.php'   ), admin_url( 'options-general.php' ) ) . '">' . esc_html__( 'Settings', 'trendr-global-search' ) . '</a>';
		array_unshift ($links, $settings_link);
	}
	return $links;
}

?>