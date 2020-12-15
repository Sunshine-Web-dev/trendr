<?php
/**
 * Trnder Administration Bootstrap
 *
 * @package Trnder
 * @subpackage Administration
 */

/**
 * In Trnder Administration Screens
 *
 * @since 2.3.2
 */
if ( ! defined('TRM_ADMIN') )
	define('TRM_ADMIN', TRUE);

if ( ! defined('TRM_NETWORK_ADMIN') )
	define('TRM_NETWORK_ADMIN', FALSE);

if ( ! defined('TRM_USER_ADMIN') )
	define('TRM_USER_ADMIN', FALSE);

if ( ! TRM_NETWORK_ADMIN && ! TRM_USER_ADMIN ) {
	define('TRM_BLOG_ADMIN', TRUE);
}

if ( isset($_GET['import']) && !defined('TRM_LOAD_IMPORTERS') )
	define('TRM_LOAD_IMPORTERS', true);

require_once(dirname(dirname(__FILE__)) . '/initiate.php');

if ( get_option('db_upgraded') ) {
	$trm_rewrite->flush_rules();
	update_option( 'db_upgraded',  false );

	/**
	 * Runs on the next page load after successful upgrade
	 *
	 * @since 2.8
	 */
	do_action('after_db_upgrade');
} elseif ( get_option('db_version') != $trm_db_version ) {
	if ( !is_multisite() ) {
		trm_redirect(admin_url('upgrade.php?_http_referer=' . urlencode(stripslashes($_SERVER['REQUEST_URI']))));
		exit;
	} elseif ( apply_filters( 'do_mu_upgrade', true ) ) {
		/**
		 * On really small MU installs run the upgrader every time,
		 * else run it less often to reduce load.
		 *
		 * @since 2.8.4b
		 */
		$c = get_blog_count();
		if ( $c <= 50 || ( $c > 50 && mt_rand( 0, (int)( $c / 50 ) ) == 1 ) ) {
			require_once( ABSPATH . TRMINC . '/http.php' );
			$response = trm_remote_get( admin_url( 'upgrade.php?step=1' ), array( 'timeout' => 120, 'httpversion' => '1.1' ) );
			do_action( 'after_mu_upgrade', $response );
			unset($response);
		}
		unset($c);
	}
}

require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/admin.php');

auth_redirect();

nocache_headers();

// Schedule trash collection
if ( !trm_next_scheduled('trm_scheduled_delete') && !defined('TRM_INSTALLING') )
	trm_schedule_event(time(), 'daily', 'trm_scheduled_delete');

set_screen_options();

$date_format = get_option('date_format');
$time_format = get_option('time_format');

trm_reset_vars(array('profile', 'redirect', 'redirect_url', 'a', 'text', 'trackback', 'pingback'));

trm_enqueue_script( 'common' );
trm_enqueue_script( 'jquery-color' );

$editing = false;

if ( isset($_GET['page']) ) {
	$plugin_page = stripslashes($_GET['page']);
	$plugin_page = plugin_basename($plugin_page);
}

if ( isset($_GET['post_type']) )
	$typenow = sanitize_key($_GET['post_type']);
else
	$typenow = '';

if ( isset($_GET['taxonomy']) )
	$taxnow = sanitize_key($_GET['taxonomy']);
else
	$taxnow = '';

if ( TRM_NETWORK_ADMIN )
	require(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/network/menu.php');
elseif ( TRM_USER_ADMIN )
	require(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/user/menu.php');
else
	require(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/menu.php');

if ( current_user_can( 'manage_options' ) )
	@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', TRM_MAX_MEMORY_LIMIT ) );

do_action('admin_init');

if ( isset($plugin_page) ) {
	if ( !empty($typenow) )
		$the_parent = $pagenow . '?post_type=' . $typenow;
	else
		$the_parent = $pagenow;
	if ( ! $page_hook = get_plugin_page_hook($plugin_page, $the_parent) ) {
		$page_hook = get_plugin_page_hook($plugin_page, $plugin_page);
		// backwards compatibility for plugins using add_management_page
		if ( empty( $page_hook ) && 'edit.php' == $pagenow && '' != get_plugin_page_hook($plugin_page, 'tools.php') ) {
			// There could be plugin specific params on the URL, so we need the whole query string
			if ( !empty($_SERVER[ 'QUERY_STRING' ]) )
				$query_string = $_SERVER[ 'QUERY_STRING' ];
			else
				$query_string = 'page=' . $plugin_page;
			trm_redirect( admin_url('tools.php?' . $query_string) );
			exit;
		}
	}
	unset($the_parent);
}

$hook_suffix = '';
if ( isset($page_hook) )
	$hook_suffix = $page_hook;
else if ( isset($plugin_page) )
	$hook_suffix = $plugin_page;
else if ( isset($pagenow) )
	$hook_suffix = $pagenow;

set_current_screen();

// Handle plugin admin pages.
if ( isset($plugin_page) ) {
	if ( $page_hook ) {
		do_action('load-' . $page_hook);
		if (! isset($_GET['noheader']))
			require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/admin-header.php');

		do_action($page_hook);
	} else {
		if ( validate_file($plugin_page) )
			trm_die(__('Invalid plugin page'));


		if ( !( file_exists(TRM_PLUGIN_DIR . "/$plugin_page") && is_file(TRM_PLUGIN_DIR . "/$plugin_page") ) && !( file_exists(TRMMU_PLUGIN_DIR . "/$plugin_page") && is_file(TRMMU_PLUGIN_DIR . "/$plugin_page") ) )
			trm_die(sprintf(__('Cannot load %s.'), htmlentities($plugin_page)));

		do_action('load-' . $plugin_page);

		if ( !isset($_GET['noheader']))
			require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/admin-header.php');

		if ( file_exists(TRMMU_PLUGIN_DIR . "/$plugin_page") )
			include(TRMMU_PLUGIN_DIR . "/$plugin_page");
		else
			include(TRM_PLUGIN_DIR . "/$plugin_page");
	}

	include(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/admin-footer.php');

	exit();
} else if (isset($_GET['import'])) {

	$importer = $_GET['import'];

	if ( ! current_user_can('import') )
		trm_die(__('You are not allowed to import.'));

	if ( validate_file($importer) ) {
		trm_redirect( admin_url( 'import.php?invalid=' . $importer ) );
		exit;
	}

	// Allow plugins to define importers as well
	if ( !isset($trm_importers) || !isset($trm_importers[$importer]) || ! is_callable($trm_importers[$importer][2])) {
		if (! file_exists(ABSPATH . "Backend-WeaprEcqaKejUbRq-trendr/import/$importer.php")) {
			trm_redirect( admin_url( 'import.php?invalid=' . $importer ) );
			exit;
		}
		include(ABSPATH . "Backend-WeaprEcqaKejUbRq-trendr/import/$importer.php");
	}

	$parent_file = 'tools.php';
	$submenu_file = 'import.php';
	$title = __('Import');

	if (! isset($_GET['noheader']))
		require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/admin-header.php');

	require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/upgrade.php');

	define('TRM_IMPORTING', true);

	if ( apply_filters( 'force_filtered_html_on_import', false ) )
		kses_init_filters();  // Always filter imported data with kses on multisite.

	call_user_func($trm_importers[$importer][2]);

	include(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/admin-footer.php');

	// Make sure rules are flushed
	global $trm_rewrite;
	$trm_rewrite->flush_rules(false);

	exit();
} else {
	do_action("load-$pagenow");
	// Backwards compatibility with old load-page-new.php, load-page.php,
	// and load-categories.php actions.
	if ( $typenow == 'page' ) {
		if ( $pagenow == 'post-new.php' )
			do_action( 'load-page-new.php' );
		elseif ( $pagenow == 'post.php' )
			do_action( 'load-page.php' );
	}  elseif ( $pagenow == 'edit-tags.php' ) {
		if ( $taxnow == 'category' )
			do_action( 'load-categories.php' );
		elseif ( $taxnow == 'link_category' )
			do_action( 'load-edit-link-categories.php' );
	}
}

if ( !empty($_REQUEST['action']) )
	do_action('admin_action_' . $_REQUEST['action']);

?>
