<?php
/**
 * Build Network Administration Menu.
 *
 * @package Trnder
 * @subpackage Multisite
 * @since 3.1.0
 */

/* translators: Network menu item */
$menu[0] = array(__('Dashboard'), 'manage_network', 'index.php', '', 'menu-top menu-top-first menu-icon-dashboard', 'menu-dashboard', 'div');

$menu[4] = array( '', 'read', 'separator1', '', 'trm-menu-separator' );

/* translators: Sites menu item */
$menu[5] = array(__('Sites'), 'manage_sites', 'sites.php', '', 'menu-top menu-icon-site', 'menu-site', 'div');
$submenu['sites.php'][5]  = array( __('All Sites'), 'manage_sites', 'sites.php' );
$submenu['sites.php'][10]  = array( _x('Add New', 'site'), 'create_sites', 'site-new.php' );

$menu[10] = array(__('Users'), 'manage_network_users', 'users.php', '', 'menu-top menu-icon-users', 'menu-users', 'div');
$submenu['users.php'][5]  = array( __('All Users'), 'manage_network_users', 'users.php' );
$submenu['users.php'][10]  = array( _x('Add New', 'user'), 'create_users', 'user-new.php' );

if ( current_user_can( 'update_themes' ) ) {
	$plugin_update_count = $theme_update_count = $trendr_update_count = 0;
	$update_themes = get_site_transient( 'update_themes' );
	if ( !empty($update_themes->response) )
		$theme_update_count = count( $update_themes->response );
	$menu[15] = array(sprintf( __( 'Themes %s' ), "<span class='update-plugins count-$theme_update_count'><span class='theme-count'>" . number_format_i18n( $theme_update_count ) . "</span></span>" ), 'manage_network_themes', 'themes.php', '', 'menu-top menu-icon-appearance', 'menu-appearance', 'div' );
} else {
	$menu[15] = array( __( 'Themes' ), 'manage_network_themes', 'themes.php', '', 'menu-top menu-icon-appearance', 'menu-appearance', 'div' );
}
$submenu['themes.php'][5]  = array( __('Installed Themes'), 'manage_network_themes', 'themes.php' );
$submenu['themes.php'][10] = array( _x('Add New', 'theme'), 'install_themes', 'theme-install.php' );
$submenu['themes.php'][15] = array( _x('Editor', 'theme editor'), 'edit_themes', 'theme-editor.php' );

if ( current_user_can( 'update_plugins' ) ) {
	$update_plugins = get_site_transient( 'update_plugins' );
	if ( !empty($update_plugins->response) )
		$plugin_update_count = count( $update_plugins->response );
	$menu[20] = array( sprintf( __( 'Plugins %s' ), "<span class='update-plugins count-$plugin_update_count'><span class='plugin-count'>" . number_format_i18n( $plugin_update_count ) . "</span></span>" ), 'manage_network_plugins', 'plugins.php', '', 'menu-top menu-icon-plugins', 'menu-plugins', 'div');
} else {
	$menu[20] = array( __('Plugins'), 'manage_network_plugins', 'plugins.php', '', 'menu-top menu-icon-plugins', 'menu-plugins', 'div' );
}
$submenu['plugins.php'][5]  = array( __('Installed Plugins'), 'manage_network_plugins', 'plugins.php' );
$submenu['plugins.php'][10] = array( _x('Add New', 'plugin editor'), 'install_plugins', 'plugin-install.php' );
$submenu['plugins.php'][15] = array( _x('Editor', 'plugin editor'), 'edit_plugins', 'plugin-editor.php' );


$menu[25] = array(__('Settings'), 'manage_network_options', 'settings.php', '', 'menu-top menu-icon-settings', 'menu-settings', 'div');
if ( defined( 'MULTISITE' ) && defined( 'TRM_ALLOW_MULTISITE' ) && TRM_ALLOW_MULTISITE ) {
	$submenu['settings.php'][5]  = array( __('Network Settings'), 'manage_network_options', 'settings.php' );
	$submenu['settings.php'][10] = array( __('Network Setup'), 'manage_network_options', 'setup.php' );
}

if ( current_user_can( 'update_core' ) ) {
	$update_trendr = get_core_updates( array('dismissed' => false) );
	if ( !empty($update_trendr) && !in_array( $update_trendr[0]->response, array('development', 'latest') ) )
		$trendr_update_count = 1;
	
	$update_count = $plugin_update_count + $theme_update_count + $trendr_update_count;
	$update_title = array();
	if ( $trendr_update_count )
		$update_title[] = sprintf(__('%d Trnder Update'), $trendr_update_count);
	if ( $plugin_update_count )
		$update_title[] = sprintf(_n('%d Plugin Update', '%d Plugin Updates', $plugin_update_count), $plugin_update_count);
	if ( $theme_update_count )
		$update_title[] = sprintf(_n('%d Theme Update', '%d Theme Updates', $theme_update_count), $theme_update_count);
	
	$update_title = !empty($update_title) ? esc_attr(implode(', ', $update_title)) : '';
	
	$menu[30] = array( sprintf( __( 'Updates %s' ), "<span class='update-plugins count-$update_count' title='$update_title'><span class='update-count'>" . number_format_i18n($update_count) . "</span></span>" ), 'manage_network', 'upgrade.php', '', 'menu-top menu-icon-tools', 'menu-update', 'div' );
} else {
	$menu[30] = array( __( 'Updates' ), 'manage_network', 'upgrade.php', '', 'menu-top menu-icon-tools', 'menu-update', 'div' );
}

$submenu[ 'upgrade.php' ][10] = array( __( 'Available Updates' ), 'update_core',  'update-core.php' );
$submenu[ 'upgrade.php' ][15] = array( __( 'Update Network' ), 'manage_network', 'upgrade.php' );
unset($plugin_update_count, $theme_update_count, $trendr_update_count, $update_count, $update_title, $update_themes, $update_plugins, $update_trendr);


$menu[99] = array( '', 'read', 'separator-last', '', 'trm-menu-separator-last' );

require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/menu.php');

?>