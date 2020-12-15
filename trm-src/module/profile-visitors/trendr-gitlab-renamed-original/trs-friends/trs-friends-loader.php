<?php
/**
 * trendr Friends Streams Loader
 *
 * The friends component is for users to create relationships with each other
 *
 * @package trendr
 * @sutrsackage Friends Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class TRS_Friends_Component extends TRS_Component {

	/**
	 * Start the friends component creation process
	 *
	 * @since 1.5
	 */
	function __construct() {
		parent::start(
			'friends',
			__( 'Friend Connections', 'trendr' ),
			TRS_PLUGIN_DIR
		);
	}

	/**
	 * Include files
	 */
	function includes() {
		// Files to include
		$includes = array(
			'actions',
			'screens',
			'filters',
			'classes',
			'activity',
			'template',
			'functions',
			'notifications',
		);

		parent::includes( $includes );
	}

	/**
	 * Setup globals
	 *
	 * The TRS_FRIENDS_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since 1.5
	 * @global obj $trs
	 */
	function setup_globals() {
		global $trs;

		define ( 'TRS_FRIENDS_DB_VERSION', '1800' );

		// Define a slug, if necessary
		if ( !defined( 'TRS_FRIENDS_SLUG' ) )
			define( 'TRS_FRIENDS_SLUG', $this->id );

		// Global tables for the friends component
		$global_tables = array(
			'table_name'      => $trs->table_prefix . 'trs_friends',
			'table_name_meta' => $trs->table_prefix . 'trs_friends_meta',
		);

		// All globals for the friends component.
		// Note that global_tables is included in this array.
		$globals = array(
			'path'                  => TRS_PLUGIN_DIR,
			'slug'                  => TRS_FRIENDS_SLUG,
			'has_directory'         => false,
			'search_string'         => __( 'Search Friends...', 'trendr' ),
			'notification_callback' => 'friends_format_notifications',
			'global_tables'         => $global_tables
		);

		parent::setup_globals( $globals );
	}

	/**
	 * Setup BuddyBar navigation
	 *
	 * @global obj $trs
	 */
	function setup_nav() {
		global $trs;

		// Add 'Friends' to the main navigation
		$main_nav = array(
			'name'                => sprintf( __( 'Friends <span>%d</span>', 'trendr' ), friends_get_total_friend_count() ),
			'slug'                => $this->slug,
			'position'            => 60,
			'screen_function'     => 'friends_screen_my_friends',
			'default_subnav_slug' => 'my-friends',
			'item_css_id'         => $trs->friends->id
		);

		$friends_link = trailingslashit( $trs->loggedin_user->domain . trs_get_friends_slug() );

		// Add the subnav items to the friends nav item
		$sub_nav[] = array(
			'name'            => __( 'Friendships', 'trendr' ),
			'slug'            => 'my-friends',
			'parent_url'      => $friends_link,
			'parent_slug'     => trs_get_friends_slug(),
			'screen_function' => 'friends_screen_my_friends',
			'position'        => 10,
			'item_css_id'     => 'friends-my-friends'
		);

		$sub_nav[] = array(
			'name'            => __( 'Requests',   'trendr' ),
			'slug'            => 'requests',
			'parent_url'      => $friends_link,
			'parent_slug'     => trs_get_friends_slug(),
			'screen_function' => 'friends_screen_requests',
			'position'        => 20,
			'user_has_access' => trs_is_my_profile()
		);

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
			$user_domain  = $trs->loggedin_user->domain;
			$friends_link = trailingslashit( $user_domain . $this->slug );

			// Pending friend requests
			if ( $count = count( friends_get_friendship_request_user_ids( $trs->loggedin_user->id ) ) ) {
				$title   = sprintf( __( 'Friends <span class="count">%s</span>',          'trendr' ), $count );
				$pending = sprintf( __( 'Pending Requests <span class="count">%s</span>', 'trendr' ), $count );
			} else {
				$title   = __( 'Friends',             'trendr' );
				$pending = __( 'No Pending Requests', 'trendr' );
			}

			// Add the "My Account" sub menus
			$trm_admin_nav[] = array(
				'parent' => $trs->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => $title,
				'href'   => trailingslashit( $friends_link )
			);

			// My Groups
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-friendships',
				'title'  => __( 'Friendships', 'trendr' ),
				'href'   => trailingslashit( $friends_link )
			);

			// Requests
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-requests',
				'title'  => $pending,
				'href'   => trailingslashit( $friends_link . 'requests' )
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

		// Adjust title
		if ( trs_is_friends_component() ) {
			if ( trs_is_my_profile() ) {
				$trs->trs_options_title = __( 'Friendships', 'trendr' );
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
// Create the friends component
$trs->friends = new TRS_Friends_Component();

?>