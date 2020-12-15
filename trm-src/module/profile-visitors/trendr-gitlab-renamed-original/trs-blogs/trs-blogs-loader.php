<?php
/**
 * trendr Blogs Streams Loader
 *
 * An blogs stream component, for users, groups, and blog tracking.
 *
 * @package trendr
 * @sutrsackage Blogs Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class TRS_Blogs_Component extends TRS_Component {

	/**
	 * Start the blogs component creation process
	 *
	 * @since 1.5
	 */
	function __construct() {
		parent::start(
			'blogs',
			__( 'Site Tracking', 'trendr' ),
			TRS_PLUGIN_DIR
		);
	}

	/**
	 * Setup globals
	 *
	 * The TRS_BLOGS_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since 1.5
	 * @global obj $trs
	 */
	function setup_globals() {
		global $trs;

		if ( !defined( 'TRS_BLOGS_SLUG' ) )
			define ( 'TRS_BLOGS_SLUG', $this->id );

		// Global tables for messaging component
		$global_tables = array(
			'table_name'          => $trs->table_prefix . 'trs_user_blogs',
			'table_name_blogmeta' => $trs->table_prefix . 'trs_user_blogs_blogmeta',
		);

		// All globals for messaging component.
		// Note that global_tables is included in this array.
		$globals = array(
			'path'                  => TRS_PLUGIN_DIR,
			'slug'                  => TRS_BLOGS_SLUG,
			'root_slug'             => isset( $trs->pages->blogs->slug ) ? $trs->pages->blogs->slug : TRS_BLOGS_SLUG,
			'has_directory'         => is_multisite(), // Non-multisite installs don't need a top-level Sites directory, since there's only one site
			'notification_callback' => 'trs_blogs_format_notifications',
			'search_string'         => __( 'Search sites...', 'trendr' ),
			'autocomplete_all'      => defined( 'TRS_MESSAGES_AUTOCOMPLETE_ALL' ),
			'global_tables'         => $global_tables,
		);

		// Setup the globals
		parent::setup_globals( $globals );
	}

	/**
	 * Include files
	 */
	function includes() {
		// Files to include
		$includes = array(
			'cache',
			'actions',
			'screens',
			'classes',
			'template',
			'activity',
			'functions',
			'buddybar'
		);

		if ( is_multisite() )
			$includes[] = 'widgets';

		// Include the files
		parent::includes( $includes );
	}

	/**
	 * Setup BuddyBar navigation
	 *
	 * @global obj $trs
	 */
	function setup_nav() {
		global $trs;

		/**
		 * Blog/post/comment menus should not appear on single WordPress setups.
		 * Although comments and posts made by users will still show on their
		 * activity stream.
		 */
		if ( !is_multisite() )
			return false;

		// Add 'Sites' to the main navigation
		$main_nav =  array(
			'name'                => sprintf( __( 'Sites <span>%d</span>', 'trendr' ), trs_blogs_total_blogs_for_user() ),
			'slug'                => $this->slug,
			'position'            => 30,
			'screen_function'     => 'trs_blogs_screen_my_blogs',
			'default_subnav_slug' => 'my-blogs',
			'item_css_id'         => $this->id
		);

		// Setup navigation
		parent::setup_nav( $main_nav );
	}

	/**
	 * Set up the admin bar
	 *
	 * @global obj $trs
	 */
	function setup_admin_bar() {
		global $trs;

		/**
		 * Blog/post/comment menus should not appear on single WordPress setups.
		 * Although comments and posts made by users will still show on their
		 * activity stream.
		 */
		if ( !is_multisite() )
			return false;

		// Prevent debug notices
		$trm_admin_nav = array();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			$blogs_link = trailingslashit( $trs->loggedin_user->domain . $this->slug );

			// Add the "Blogs" sub menu
			$trm_admin_nav[] = array(
				'parent' => $trs->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Sites', 'trendr' ),
				'href'   => trailingslashit( $blogs_link )
			);

			// My Blogs
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-my-sites',
				'title'  => __( 'My Sites', 'trendr' ),
				'href'   => trailingslashit( $blogs_link )
			);

		}

		parent::setup_admin_bar( $trm_admin_nav );
	}

	/**
	 * Sets up the title for pages and <title>
	 *
	 * @global obj $trs
	 */
	function setup_title() {
		global $trs;

		// Set up the component options navigation for Blog
		if ( trs_is_blogs_component() ) {
			if ( trs_is_my_profile() ) {
				if ( trs_is_active( 'xprofile' ) ) {
					$trs->trs_options_title = __( 'My Sites', 'trendr' );
				}

			// If we are not viewing the logged in user, set up the current
			// users portrait and name
			} else {
				$trs->trs_options_portrait = trs_core_fetch_portrait( array(
					'item_id' => $trs->displayed_user->id,
					'type'    => 'thumb'
				) );
				$trs->trs_options_title = $trs->displayed_user->fullname;
			}
		}

		parent::setup_title();
	}
}
// Create the blogs component
$trs->blogs = new TRS_Blogs_Component();

?>