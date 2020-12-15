<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by trendr to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wbcomdesigns.com/
 * @since             1.0.0
 * @package           Trs_Checkins
 *
 * @trendr-plugin
 * Plugin Name:       trendr Check-ins
 * Plugin URI:        https://wbcomdesigns.com/downloads/trendr-checkins/
 * Description:       This plugin allows trendr members to share their location when they are posting activities, just like other social sites, you can add places where you visited.
 * Version:           1.1.0
 * Author:            Wbcom Designs
 * Author URI:        https://wbcomdesigns.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       trs-checkins
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'TRMINC' ) ) {
	die;
}

// Define Plugin Constants.
define( 'TRSCHK_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'TRSCHK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TRS_CHECKINS_PLUGIN_BASENAME',  plugin_basename( __FILE__ ) );
if ( ! defined( 'TRSCHK_TEXT_DOMAIN' ) ) {
	define( 'TRSCHK_TEXT_DOMAIN', 'trs-checkins' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-trs-checkins-activator.php
 */
function activate_trs_checkins() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-trs-checkins-activator.php';
	Trs_Checkins_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-trs-checkins-deactivator.php
 */
function deactivate_trs_checkins() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-trs-checkins-deactivator.php';
	Trs_Checkins_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_trs_checkins' );
register_deactivation_hook( __FILE__, 'deactivate_trs_checkins' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-trs-checkins.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_trs_checkins() {
	$plugin = new Trs_Checkins();
	$plugin->run();
}

/**
 * Check plugin requirement on plugins loaded
 * this plugin requires trendr to be installed and active
 */
add_action( 'plugins_loaded', 'trschk_plugin_init' );

/**
 * Check plugin requirement on plugins loaded,this plugin requires trendr to be installed and active.
 *
 * @since    1.0.0
 */
function trschk_plugin_init() {
	if ( trs_checkins_check_config() ){
		run_trs_checkins();
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'trschk_plugin_links' );
	}
}

function trs_checkins_check_config(){
	global $trs;
	
	$config = array(
		'blog_status'    => false, 
		'network_active' => false, 
		'network_status' => true 
	);
	if ( get_current_blog_id() == trs_get_root_blog_id() ) {
		$config['blog_status'] = true;
	}
	
	$network_plugins = get_site_option( 'active_sitewide_plugins', array() );

	// No Network plugins
	if ( empty( $network_plugins ) )

	// Looking for trendr and trs-activity plugin
	$check[] = $trs->basename;
	$check[] = TRS_CHECKINS_PLUGIN_BASENAME;

	// Are they active on the network ?
	$network_active = array_diff( $check, array_keys( $network_plugins ) );
	
	// If result is 1, your plugin is network activated
	// and not trendr or vice & versa. Config is not ok
	if ( count( $network_active ) == 1 )
		$config['network_status'] = false;

	// We need to know if the plugin is network activated to choose the right
	// notice ( admin or network_admin ) to display the warning message.
	$config['network_active'] = isset( $network_plugins[ TRS_CHECKINS_PLUGIN_BASENAME ] );

	// if trendr config is different than trs-activity plugin
	if ( !$config['blog_status'] || !$config['network_status'] ) {

		$warnings = array();
		if ( !trs_core_do_network_admin() && !$config['blog_status'] ) {
			add_action( 'admin_notices', 'trscheckins_same_blog' );
			$warnings[] = __( 'trendr Check-ins requires to be activated on the blog where trendr is activated.', 'trs-checkins'  );
		}

		if ( trs_core_do_network_admin() && !$config['network_status'] ) {
			add_action( 'admin_notices', 'trscheckins_same_network_config' );
			$warnings[] = __( 'trendr Check-ins and trendr need to share the same network configuration.',  'trs-checkins');
		}
		$trs_active_components = trs_get_option( 'trs-active-components');
		if ( ! array_key_exists( 'activity', $trs_active_components ) ) {
			add_action( $config['network_active'] ? 'network_admin_notices' : 'admin_notices', 'trschk_plugin_require_activity_component_admin_notice' );
			$warnings[] = __( 'Activity component required.',  'trs-checkins');
		}
		if ( ! empty( $warnings ) ) :
			return false;
		endif;
	}
	return true;
}
function trscheckins_same_blog(){
	echo '<div class="error"><p>'
	. esc_html( __( 'trendr Check-ins requires to be activated on the blog where trendr is activated.', 'trs-checkins'  ) )
	. '</p></div>';
}

function trscheckins_same_network_config(){
	echo '<div class="error"><p>'
	. esc_html( __( 'trendr Check-ins and trendr need to share the same network configuration.', 'trs-checkins' ) )
	. '</p></div>';
}

/**
 * Function to through notice when trendr activity component is not activated.
 *
 * @since    1.0.0
 */
function trschk_plugin_require_activity_component_admin_notice() {
	$trschk_plugin = 'trendr Checkin';
	$trs_component = 'trendr\'s Activity Component';

	echo '<div class="error"><p>'
	. sprintf( esc_attr( '%1$s is ineffective now as it requires %2$s to be active.', 'trs-checkins' ), '<strong>' . esc_attr( $trschk_plugin ) . '</strong>', '<strong>' . esc_attr( $trs_component ) . '</strong>' )
	. '</p></div>';
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

/**
 * Function to set plugin actions links.
 *
 * @param    array $links    Plugin settings link array.
 * @since    1.0.0
 */
function trschk_plugin_links( $links ) {
	$trschk_links = array(
		'<a href="' . admin_url( 'admin.php?page=trs-checkins' ) . '">' . __( 'Settings', 'trs-checkins' ) . '</a>',
		'<a href="https://wbcomdesigns.com/contact/" target="_blank">' . __( 'Support', 'trs-checkins' ) . '</a>',
	);
	return array_merge( $links, $trschk_links );
}
