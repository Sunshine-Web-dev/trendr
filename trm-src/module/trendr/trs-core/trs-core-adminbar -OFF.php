<?php
/**
 * trendr Core Admin Bar
 *
 * Handles the core functions related to the trendr Admin Bar
 *
 * @package trendr
 * @sutrsackage Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !trs_use_trm_admin_bar() || defined( 'DOING_AJAX' ) )
	return;

/**
 * Adjust the admin bar menus based on which trendr version this is
 *
 * @since trendr (1.5.2)
 */
function trs_core_admin_bar_version_check() {
	if ( '3.2' == trs_get_major_trm_version() ) {
		add_action( 'trs_setup_admin_bar', 'trs_admin_bar_root_site',       3 );
		add_action( 'trs_setup_admin_bar', 'trs_admin_bar_comments_menu',   3 );
		add_action( 'trs_setup_admin_bar', 'trs_admin_bar_appearance_menu', 3 );
		add_action( 'trs_setup_admin_bar', 'trs_admin_bar_updates_menu',    3 );
	}
}
add_action( 'admin_bar_menu', 'trs_core_admin_bar_version_check', 4 );

/**
 * Unhook the trendr core menus.
 *
 * @since trendr (r4151)
 *
 * @uses remove_action
 * @uses is_network_admin()
 * @uses is_user_admin()
 */
function trs_admin_bar_remove_trm_menus() {

	if ( '3.2' == trs_get_major_trm_version() ) {
		remove_action( 'admin_bar_menu', 'trm_admin_bar_my_account_menu', 10 );
		remove_action( 'admin_bar_menu', 'trm_admin_bar_my_sites_menu', 20 );
		remove_action( 'admin_bar_menu', 'trm_admin_bar_dashboard_view_site_menu', 25 );

		// Don't show the 'Edit Page' menu on TRS pages
		if ( !trs_is_blog_page() )
			remove_action( 'admin_bar_menu', 'trm_admin_bar_edit_menu', 30 );

		remove_action( 'admin_bar_menu', 'trm_admin_bar_shortlink_menu', 80 );
		remove_action( 'admin_bar_menu', 'trm_admin_bar_updates_menu', 70 );

		if ( !is_network_admin() && !is_user_admin() ) {
			remove_action( 'admin_bar_menu', 'trm_admin_bar_comments_menu', 50 );
			remove_action( 'admin_bar_menu', 'trm_admin_bar_appearance_menu', 60 );
		}

		remove_action( 'admin_bar_menu', 'trm_admin_bar_updates_menu', 70 );
	}
}
add_action( 'trs_init', 'trs_admin_bar_remove_trm_menus', 2 );

/**
 * Add a menu for the root site of this trendr network
 *
 * @global type $trm_admin_bar
 * @return If in ajax
 */
function trs_admin_bar_root_site() {
	global $trm_admin_bar;

	// Create the root blog menu
	$trm_admin_bar->add_menu( array(
		'id'    => 'trs-root-blog',
		'title' => get_blog_option( trs_get_root_blog_id(), 'blogname' ),
		'href'  => trs_get_root_domain()
	) );

	// Logged in user
	if ( is_user_logged_in() ) {

		// Dashboard links
		if ( is_super_admin() ) {

			// Add site admin link
			$trm_admin_bar->add_menu( array(
				'id' => 'dashboard',
				'parent' => 'trs-root-blog',
				'title' => __( 'Admin Dashboard', 'trendr' ),
				'href' => get_admin_url( trs_get_root_blog_id() )
			) );

			// Add network admin link
			if ( is_multisite() ) {

				// Link to the network admin dashboard
				$trm_admin_bar->add_menu( array(
					'id' => 'network-dashboard',
					'parent' => 'trs-root-blog',
					'title' => __( 'Network Dashboard', 'trendr' ),
					'href' => network_admin_url()
				) );
			}
		}
	}
}

/**
 * Add the "My Sites/[Site Name]" menu and all submenus.
 */
function trs_admin_bar_my_sites_menu() {
	global $trm_admin_bar;

	/* Add the 'My Sites' menu if the user has more than one site. */
	if ( count( $trm_admin_bar->user->blogs ) <= 1 )
		return;

	$trm_admin_bar->add_menu( array( 'id' => 'my-blogs', 'title' => __( 'My Sites' ), 'href' => admin_url( 'my-sites.php' ) ) );

	$default = includes_url( 'images/trmmini-blue.png' );

	foreach ( (array)$trm_admin_bar->user->blogs as $blog ) {
		// @todo Replace with some favicon lookup.
		//$blportrait = '<img src="' . esc_url( blportrait_url( blportrait_domain( $blog->siteurl ), 'img', 16, $default ) ) . '" alt="Blportrait" width="16" height="16" />';
		$blportrait = '<img src="' . esc_url( $default ) . '" alt="' . esc_attr__( 'Blportrait' ) . '" width="16" height="16" class="blportrait"/>';

		$blogname = empty( $blog->blogname ) ? $blog->domain : $blog->blogname;

		$trm_admin_bar->add_menu( array( 'parent' => 'my-blogs', 'id' => 'blog-' . $blog->userblog_id, 'title' => $blportrait . $blogname, 'href' => get_admin_url( $blog->userblog_id ) ) );
		$trm_admin_bar->add_menu( array( 'parent' => 'blog-' . $blog->userblog_id, 'id' => 'blog-' . $blog->userblog_id . '-d', 'title' => __( 'Dashboard' ), 'href' => get_admin_url( $blog->userblog_id ) ) );

		if ( current_user_can_for_blog( $blog->userblog_id, 'edit_posts' ) ) {
			$trm_admin_bar->add_menu( array( 'parent' => 'blog-' . $blog->userblog_id, 'id' => 'blog-' . $blog->userblog_id . '-n', 'title' => __( 'New Post' ), 'href' => get_admin_url( $blog->userblog_id, 'post-new.php' ) ) );
			$trm_admin_bar->add_menu( array( 'parent' => 'blog-' . $blog->userblog_id, 'id' => 'blog-' . $blog->userblog_id . '-c', 'title' => __( 'Manage Comments' ), 'href' => get_admin_url( $blog->userblog_id, 'edit-comments.php' ) ) );
		}

		$trm_admin_bar->add_menu( array( 'parent' => 'blog-' . $blog->userblog_id, 'id' => 'blog-' . $blog->userblog_id . '-v', 'title' => __( 'Visit Site' ), 'href' => get_home_url( $blog->userblog_id ) ) );
	}
}

/**
 * Add edit comments link with awaiting moderation count bubble
 */
function trs_admin_bar_comments_menu( $trm_admin_bar ) {
	global $trm_admin_bar;

	if ( !current_user_can( 'edit_posts' ) )
		return;

	$awaiting_mod = trm_count_comments();
	$awaiting_mod = $awaiting_mod->moderated;

	$awaiting_mod = $awaiting_mod ? "<span id='ab-awaiting-mod' class='pending-count'>" . number_format_i18n( $awaiting_mod ) . "</span>" : '';
	$trm_admin_bar->add_menu( array( 'parent' => 'dashboard', 'id' => 'comments', 'title' => sprintf( __( 'Comments %s' ), $awaiting_mod ), 'href' => admin_url( 'edit-comments.php' ) ) );
}

/**
 * Add "Appearance" menu with widget and nav menu submenu
 */
function trs_admin_bar_appearance_menu() {
	global $trm_admin_bar;

	// You can have edit_theme_options but not switch_themes.
	if ( !current_user_can( 'switch_themes' ) && !current_user_can( 'edit_theme_options' ) )
		return;

	$trm_admin_bar->add_menu( array( 'parent' => 'dashboard', 'id' => 'appearance', 'title' => __( 'Appearance' ), 'href' => admin_url( 'themes.php' ) ) );

	if ( !current_user_can( 'edit_theme_options' ) )
		return;

	if ( current_user_can( 'switch_themes' ) )
		$trm_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'themes', 'title' => __( 'Themes' ), 'href' => admin_url( 'themes.php' ) ) );

	if ( current_theme_supports( 'widgets' ) )
		$trm_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'widgets', 'title' => __( 'Widgets' ), 'href' => admin_url( 'widgets.php' ) ) );

	if ( current_theme_supports( 'menus' ) || current_theme_supports( 'widgets' ) )
		$trm_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'menus', 'title' => __( 'Menus' ), 'href' => admin_url( 'nav-menus.php' ) ) );

	if ( current_theme_supports( 'custom-background' ) )
		$trm_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'background', 'title' => __( 'Background' ), 'href' => admin_url( 'themes.php?page=custom-background' ) ) );

	if ( current_theme_supports( 'custom-header' ) )
		$trm_admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'header', 'title' => __( 'Header' ), 'href' => admin_url( 'themes.php?page=custom-header' ) ) );
}

/**
 * Provide an update link if theme/plugin/core updates are available
 */
function trs_admin_bar_updates_menu() {
	global $trm_admin_bar;

	if ( !current_user_can( 'install_plugins' ) )
		return;

	$plugin_update_count = $theme_update_count = $trendr_update_count = 0;
	$update_plugins = get_site_transient( 'update_plugins' );
	if ( !empty( $update_plugins->response ) )
		$plugin_update_count = count( $update_plugins->response );
	$update_themes = get_site_transient( 'update_themes' );
	if ( !empty( $update_themes->response ) )
		$theme_update_count = count( $update_themes->response );
	/* @todo get_core_updates() is only available on admin page loads
	  $update_trendr = get_core_updates( array('dismissed' => false) );
	  if ( !empty($update_trendr) && !in_array( $update_trendr[0]->response, array('development', 'latest') ) )
	  $trendr_update_count = 1;
	 */

	$update_count = $plugin_update_count + $theme_update_count + $trendr_update_count;

	if ( !$update_count )
		return;

	$update_title = array( );
	if ( !empty( $trendr_update_count ) )
		$update_title[] = sprintf( __( '%d trendr Update' ), $trendr_update_count );
	if ( $plugin_update_count )
		$update_title[] = sprintf( _n( '%d Plugin Update', '%d Plugin Updates', $plugin_update_count ), $plugin_update_count );
	if ( $theme_update_count )
		$update_title[] = sprintf( _n( '%d Theme Update', '%d Theme Updates', $theme_update_count ), $theme_update_count );

	$update_title = !empty( $update_title ) ? esc_attr( implode( ', ', $update_title ) ) : '';

	$update_title = "<span title='$update_title'>";
	$update_title .= sprintf( __( 'Updates %s' ), "<span id='ab-updates' class='update-count'>" . number_format_i18n( $update_count ) . '</span>' );
	$update_title .= '</span>';

	$trm_admin_bar->add_menu( array( 'parent' => 'dashboard', 'id' => 'updates', 'title' => $update_title, 'href' => network_admin_url( 'update-core.php' ) ) );
}

/**
 * Handle the Admin Bar CSS
 */
function trs_core_load_admin_bar_css() {

	if ( !trs_use_trm_admin_bar() )
		return;

	// Admin bar styles
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
		$stylesheet = TRS_PLUGIN_URL . '/trs-core/css/admin-bar.dev.css';
	else
		$stylesheet = TRS_PLUGIN_URL . '/trs-core/css/admin-bar.css';

	trm_enqueue_style( 'trs-admin-bar', apply_filters( 'trs_core_admin_bar_css', $stylesheet ), array( 'admin-bar' ), '20110723' );

	if ( !is_rtl() )
		return;

	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
		$stylesheet = TRS_PLUGIN_URL . '/trs-core/css/admin-bar-rtl.dev.css';
	else
		$stylesheet = TRS_PLUGIN_URL . '/trs-core/css/admin-bar-rtl.css';

	trm_enqueue_style( 'trs-admin-bar-rtl', apply_filters( 'trs_core_admin_bar_rtl_css', $stylesheet ), array( 'trs-admin-bar' ), '20110723' );
}
add_action( 'trs_init', 'trs_core_load_admin_bar_css' );

?>
