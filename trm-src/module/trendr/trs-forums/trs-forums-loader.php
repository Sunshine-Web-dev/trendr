<?php
/**
 * trendr Forums Loader
 *
 * A discussion forums component. Comes bundled with bbPress stand-alone.
 *
 * @package trendr
 * @sutrsackage Forums Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class TRS_Forums_Component extends TRS_Component {

	/**
	 * Start the forums component creation process
	 *
	 * @since 1.5
	 */
	function __construct() {
		parent::start(
			'forums',
			__( 'Discussion Forums', 'trendr' ),
			TRS_PLUGIN_DIR
		);
	}

	/**
	 * Setup globals
	 *
	 * The TRS_FORUMS_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since 1.5
	 * @global obj $trs
	 */
	function setup_globals() {
		global $trs;

		// Define the parent forum ID
		if ( !defined( 'TRS_FORUMS_PARENT_FORUM_ID' ) )
			define( 'TRS_FORUMS_PARENT_FORUM_ID', 1 );

		// Define a slug, if necessary
		if ( !defined( 'TRS_FORUMS_SLUG' ) )
			define( 'TRS_FORUMS_SLUG', $this->id );

		// The location of the bbPress stand-alone config file
		if ( isset( $trs->site_options['bb-config-location'] ) )
			$this->bbconfig = $trs->site_options['bb-config-location'];

		// All globals for messaging component.
		// Note that global_tables is included in this array.
		$globals = array(
			'path'                  => TRS_PLUGIN_DIR,
			'slug'                  => TRS_FORUMS_SLUG,
			'root_slug'             => isset( $trs->pages->forums->slug ) ? $trs->pages->forums->slug : TRS_FORUMS_SLUG,
			'has_directory'         => true,
			'notification_callback' => 'messages_format_notifications',
			'search_string'         => __( 'Search Forums...', 'trendr' ),
		);

		parent::setup_globals( $globals );
	}

	/**
	 * Include files
	 */
	function includes() {

		// Files to include
		$includes = array(
			'actions',
			'screens',
			'classes',
			'filters',
			'template',
			'functions',
		);

		// Admin area
		if ( is_admin() )
			$includes[] = 'admin';

		// bbPress stand-alone
		if ( !defined( 'BB_PATH' ) )
			$includes[] = 'btrsress-sa';

		parent::includes( $includes );
	}

	/**
	 * Setup BuddyBar navigation
	 *
	 * @global obj $trs
	 */
	function setup_nav() {
		global $trs;

		// Stop if forums haven't been set up yet
		if ( !trs_forums_is_installed_correctly() )
			return;

		// Stop if there is no user displayed or logged in
		if ( !is_user_logged_in() && !isset( $trs->displayed_user->id ) )
			return;

		// Add 'Forums' to the main navigation
		$main_nav = array(
			'name'                => __( 'Forums', 'trendr' ),
			'slug'                => $this->slug,
			'position'            => 80,
			'screen_function'     => 'trs_member_forums_screen_topics',
			'default_subnav_slug' => 'topics',
			'item_css_id'         => $this->id
		);

		// Determine user to use
		if ( isset( $trs->displayed_user->domain ) ) {
			$user_domain = $trs->displayed_user->domain;
			$user_login  = $trs->displayed_user->userdata->user_login;
		} elseif ( isset( $trs->loggedin_user->domain ) ) {
			$user_domain = $trs->loggedin_user->domain;
			$user_login  = $trs->loggedin_user->userdata->user_login;
		} else {
			return;
		}

		// User link
		$forums_link = trailingslashit( $user_domain . $this->slug );

		// Additional menu if friends is active
		$sub_nav[] = array(
			'name'            => __( 'Topics Started', 'trendr' ),
			'slug'            => 'topics',
			'parent_url'      => $forums_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'trs_member_forums_screen_topics',
			'position'        => 20,
			'item_css_id'     => 'topics'
		);

		// Additional menu if friends is active
		$sub_nav[] = array(
			'name'            => __( 'Replied To', 'trendr' ),
			'slug'            => 'replies',
			'parent_url'      => $forums_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'trs_member_forums_screen_replies',
			'position'        => 40,
			'item_css_id'     => 'replies'
		);

		// Favorite forums items. Disabled until future release.
		/*
		$sub_nav[] = array(
			'name'            => __( 'Favorites', 'trendr' ),
			'slug'            => 'favorites',
			'parent_url'      => $forums_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'trs_member_forums_screen_favorites',
			'position'        => 60,
			'item_css_id'     => 'favorites'
		);
		*/

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up the admin bar
	 *
	 * @global obj $trs
	 */
	function setup_admin_bar() {
		global $trs;

		// Prevent debug notices
		$trm_admin_nav = array();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$user_domain = $trs->loggedin_user->domain;
			$user_login  = $trs->loggedin_user->userdata->user_login;
			$forums_link = trailingslashit( $user_domain . $this->slug );

			// Add the "My Account" sub menus
			$trm_admin_nav[] = array(
				'parent' => $trs->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Forums', 'trendr' ),
				'href'   => trailingslashit( $forums_link )
			);

			// Topics
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-topics-started',
				'title'  => __( 'Topics Started', 'trendr' ),
				'href'   => trailingslashit( $forums_link . 'topics' )
			);

			// Replies
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-replies',
				'title'  => __( 'Replies', 'trendr' ),
				'href'   => trailingslashit( $forums_link . 'replies' )
			);

			// Favorites
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-favorite-topics',
				'title'  => __( 'Favorite Topics', 'trendr' ),
				'href'   => trailingslashit( $forums_link . 'favorites' )
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

		// Adjust title based on view
		if ( trs_is_forums_component() ) {
			if ( trs_is_my_profile() ) {
				$trs->trs_options_title = __( 'Forums', 'trendr' );
			} else {
				$trs->trs_options_portrait = trs_core_fetch_portrait( array(
					'item_id' => $trs->displayed_user->id,
					'type'    => 'thumb'
				) );
				$trs->trs_options_title  = $trs->displayed_user->fullname;
			}
		}

		parent::setup_title();
	}
}
// Create the forums component
$trs->forums = new TRS_Forums_Component();

?>