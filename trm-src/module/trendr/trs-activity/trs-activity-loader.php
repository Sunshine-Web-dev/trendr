<?php

/**
 * trendr Activity Streams Loader
 *
 * An activity stream component, for users, groups, and blog tracking.
 *
 * @package trendr
 * @sutrsackage ActivityCore
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Main Activity Class
 *
 * @since 1.5.0
 */
class TRS_Activity_Component extends TRS_Component {

	/**
	 * Start the activity component creation process
	 *
	 * @since 1.5.0
	 */
	function TRS_Activity_Component() {
		$this->__construct();
	}

	function __construct() {
		parent::start(
			'activity',
			__( 'Activity Streams', 'trendr' ),
			TRS_PLUGIN_DIR
		);
	}

	/**
	 * Include files
	 *
	 * @since 1.5.0
	 */
	function includes() {
		// Files to include
		$includes = array(
			'actions',
			'screens',
			'filters',
			'classes',
			'template',
			'functions',
			'notifications',
		);

		parent::includes( $includes );
	}

	/**
	 * Setup globals
	 *
	 * The TRS_ACTIVITY_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since 1.5.0
	 *
	 * @global object $trs trendr global settings
	 */
	function setup_globals() {
		global $trs;

		// Define a slug, if necessary
		if ( !defined( 'TRS_ACTIVITY_SLUG' ) )
			define( 'TRS_ACTIVITY_SLUG', $this->id );

		// Global tables for activity component
		$global_tables = array(
			'table_name'      => $trs->table_prefix . 'trs_activity',
			'table_name_meta' => $trs->table_prefix . 'trs_activity_meta',
		);

		// All globals for activity component.
		// Note that global_tables is included in this array.
		$globals = array(
			'path'                  => TRS_PLUGIN_DIR,
			'slug'                  => TRS_ACTIVITY_SLUG,
			'root_slug'             => isset( $trs->pages->activity->slug ) ? $trs->pages->activity->slug : TRS_ACTIVITY_SLUG,
			'has_directory'         => true,
			'search_string'         => __( 'Search Activity...', 'trendr' ),
			'global_tables'         => $global_tables,
			'notification_callback' => 'trs_activity_format_notifications',
		);

		parent::setup_globals( $globals );
	}

	/**
	 * Setup BuddyBar navigation
	 *
	 * @since 1.5.0
	 *
	 * @global object $trs trendr global settings
	 * @uses trs_is_active()
	 * @uses is_user_logged_in()
	 * @uses trs_get_friends_slug()
	 * @uses trs_get_groups_slug()
	 */
	function setup_nav() {
		global $trs;

		// Add 'Activity' to the main navigation
		$main_nav = array(
			'name'                => __( 'Activity', 'trendr' ),
			'slug'                => $this->slug,
			'position'            => 10,
			'screen_function'     => 'trs_activity_screen_my_activity',
			'default_subnav_slug' => 'just-me',
			'item_css_id'         => $this->id
		);

		// Stop if there is no user displayed or logged in
		if ( !is_user_logged_in() && !isset( $trs->displayed_user->id ) )
			return;

		// Determine user to use
		if ( isset( $trs->displayed_user->domain ) )
			$user_domain = $trs->displayed_user->domain;
		elseif ( isset( $trs->loggedin_user->domain ) )
			$user_domain = $trs->loggedin_user->domain;
		else
			return;

		// User link
		$activity_link = trailingslashit( $user_domain . $this->slug );

		// Add the subnav items to the activity nav item if we are using a theme that supports this
		$sub_nav[] = array(
			'name'            => __( 'Personal', 'trendr' ),
			'slug'            => 'just-me',
			'parent_url'      => $activity_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'trs_activity_screen_my_activity',
			'position'        => 10
		);

		// @ mentions
		$sub_nav[] = array(
			'name'            => __( 'Mentions', 'trendr' ),
			'slug'            => 'mentions',
			'parent_url'      => $activity_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'trs_activity_screen_mentions',
			'position'        => 20,
			'item_css_id'     => 'activity-mentions'
		);

		// Favorite activity items
		$sub_nav[] = array(
			'name'            => __( 'Favorites', 'trendr' ),
			'slug'            => 'favorites',
			'parent_url'      => $activity_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'trs_activity_screen_favorites',
			'position'        => 30,
			'item_css_id'     => 'activity-favs'
		);

		// Additional menu if friends is active
		if ( trs_is_active( 'friends' ) ) {
			$sub_nav[] = array(
				'name'            => __( 'Friends', 'trendr' ),
				'slug'            => trs_get_friends_slug(),
				'parent_url'      => $activity_link,
				'parent_slug'     => $this->slug,
				'screen_function' => 'trs_activity_screen_friends',
				'position'        => 40,
				'item_css_id'     => 'activity-friends'
			) ;
		}

		// Additional menu if groups is active
		if ( trs_is_active( 'groups' ) ) {
			$sub_nav[] = array(
				'name'            => __( 'Groups', 'trendr' ),
				'slug'            => trs_get_groups_slug(),
				'parent_url'      => $activity_link,
				'parent_slug'     => $this->slug,
				'screen_function' => 'trs_activity_screen_groups',
				'position'        => 50,
				'item_css_id'     => 'activity-groups'
			);
		}

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up the admin bar
	 *
	 * @since 1.5.0
	 *
	 * @global object $trs trendr global settings
	 * @uses is_user_logged_in()
	 * @uses trailingslashit()
	 * @uses trs_get_total_mention_count_for_user()
	 * @uses trs_loggedin_user_id()
	 * @uses trs_is_active()
	 * @uses trs_get_friends_slug()
	 * @uses trs_get_groups_slug()
	 */
	function setup_admin_bar() {
		global $trs;

		// Prevent debug notices
		$trm_admin_nav = array();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$user_domain   = $trs->loggedin_user->domain;
			$activity_link = trailingslashit( $user_domain . $this->slug );

			// Unread message count
			if ( $count = trs_get_total_mention_count_for_user( trs_loggedin_user_id() ) ) {
				$title = sprintf( __( 'Mentions <span class="count">%s</span>', 'trendr' ), $count );
			} else {
				$title = __( 'Mentions', 'trendr' );
			}

			// Add the "Activity" sub menu
			$trm_admin_nav[] = array(
				'parent' => $trs->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Activity', 'trendr' ),
				'href'   => trailingslashit( $activity_link )
			);

			// Mentions
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-mentions',
				'title'  => $title,
				'href'   => trailingslashit( $activity_link . 'mentions' )
			);

			// Personal
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-personal',
				'title'  => __( 'Personal', 'trendr' ),
				'href'   => trailingslashit( $activity_link )
			);

			// Favorites
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-favorites',
				'title'  => __( 'Favorites', 'trendr' ),
				'href'   => trailingslashit( $activity_link . 'favorites' )
			);

			// Friends?
			if ( trs_is_active( 'friends' ) ) {
				$trm_admin_nav[] = array(
					'parent' => 'my-account-' . $this->id,
					'id'     => 'my-account-' . $this->id . '-friends',
					'title'  => __( 'Friends', 'trendr' ),
					'href'   => trailingslashit( $activity_link . trs_get_friends_slug() )
				);
			}

			// Groups?
			if ( trs_is_active( 'groups' ) ) {
				$trm_admin_nav[] = array(
					'parent' => 'my-account-' . $this->id,
					'id'     => 'my-account-' . $this->id . '-groups',
					'title'  => __( 'Groups', 'trendr' ),
					'href'   => trailingslashit( $activity_link . trs_get_groups_slug() )
				);
			}
		}

		parent::setup_admin_bar( $trm_admin_nav );
	}

	/**
	 * Sets up the title for pages and <title>
	 *
	 * @since 1.5.0
	 *
	 * @global object $trs trendr global settings
	 * @uses trs_is_activity_component()
	 * @uses trs_is_my_profile()
	 * @uses trs_core_fetch_portrait()
	 */
	function setup_title() {
		global $trs;

		// Adjust title based on view
		if ( trs_is_activity_component() ) {
			if ( trs_is_my_profile() ) {
				$trs->trs_options_title = __( 'My Activity', 'trendr' );
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

// Create the activity component
$trs->activity = new TRS_Activity_Component();

?>