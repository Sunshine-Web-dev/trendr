<?php
/**
 * TRS Follow Core
 *
 * @package TRS-Follow
 * @sutrsackage Core
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Core class for TRS Follow.
 *
 * Extends the {@link TRS_Component} class.
 *
 * @package TRS-Follow
 * @sutrsackage Classes
 *
 * @since 1.2
 */
class TRS_Follow_Component extends TRS_Component {

	/**
	 * Constructor.
	 *
	 * @global obj $trs trendr instance
	 */
	function __construct() {
		global $trs;

		// let's start the show!
		parent::start(
			'follow',
			__( 'Follow', 'trs-follow' ),
			constant( 'TRS_FOLLOW_DIR' ) . '/_inc'
		);

		// include our files
		$this->includes();

		// setup hooks
		$this->setup_hooks();

		// register our component as an active component in TRS
		$trs->active_components[$this->id] = '1';
	}

	/**
	 * Includes.
	 */
	function includes() {

		// Backpat functions for TRS < 1.7
		if ( ! class_exists( 'TRS_Theme_Compat' ) )
			require( $this->path . '/trs-follow-backpat.php' );

		require( $this->path . '/trs-follow-classes.php' );
		require( $this->path . '/trs-follow-functions.php' );
		require( $this->path . '/trs-follow-screens.php' );
		require( $this->path . '/trs-follow-actions.php' );
		require( $this->path . '/trs-follow-hooks.php' );
		require( $this->path . '/trs-follow-templatetags.php' );
		require( $this->path . '/trs-follow-notifications.php' );
		//require( $this->path . '/trs-follow-widgets.php' );

	}

	/**
	 * Setup globals.
	 *
	 * @global obj $trs trendr instance
	 */
	function setup_globals() {
		global $trs;

		if ( ! defined( 'TRS_FOLLOWERS_SLUG' ) )
			define( 'TRS_FOLLOWERS_SLUG', 'followers' );

		if ( ! defined( 'TRS_FOLLOWING_SLUG' ) )
			define( 'TRS_FOLLOWING_SLUG', 'following' );

		// Set up the $globals array
		$globals = array(
			'notification_callback' => 'trs_follow_format_notifications',
			'global_tables'         => array(
				'table_name' => $trs->table_prefix . 'trs_follow',
			)
		);

		// Let TRS_Component::setup_globals() do its work.
		parent::setup_globals( $globals );

		// register other globals since TRS isn't really flexible enough to add it
		// in the setup_globals() method
		//
		// would rather do away with this, but keeping it for backpat
		$trs->follow->followers = new stdClass;
		$trs->follow->following = new stdClass;
		$trs->follow->followers->slug = constant( 'TRS_FOLLOWERS_SLUG' );
		$trs->follow->following->slug = constant( 'TRS_FOLLOWING_SLUG' );

		// locally cache total count values for logged-in user
		if ( is_user_logged_in() ) {
			$trs->loggedin_user->total_follow_counts = trs_follow_total_follow_counts( array(
				'user_id' => trs_loggedin_user_id()
			) );
		}

		// locally cache total count values for displayed user
		if ( trs_is_user() && ( trs_loggedin_user_id() != trs_displayed_user_id() ) ) {
			$trs->displayed_user->total_follow_counts = trs_follow_total_follow_counts( array(
				'user_id' => trs_displayed_user_id()
			) );
		}

	}

	/**
	 * Setup hooks.
	 */
	function setup_hooks() {
		// javascript hook
		add_action( 'trm_enqueue_scripts', array( $this, 'enqueue_scripts' ), 11 );
	}

	/**
	 * Setup profile / BuddyBar navigation
	 */
	function setup_nav() {
		global $trs;

		// Need to change the user ID, so if we're not on a member page, $counts variable is still calculated
		$user_id = trs_is_user() ? trs_displayed_user_id() : trs_loggedin_user_id();
		$counts  = trs_follow_total_follow_counts( array( 'user_id' => $user_id ) );

		// BuddyBar compatibility
		$domain = trs_displayed_user_domain() ? trs_displayed_user_domain() : trs_loggedin_user_domain();

		/** FOLLOWERS NAV ************************************************/

		trs_core_new_nav_item( array(
			'name'                => sprintf( __( 'Following <span>%d</span>', 'trs-follow' ), $counts['following'] ),
			'slug'                => $trs->follow->following->slug,
			'position'            => apply_filters( 'trs_follow_following_nav_position', 61 ),
			'screen_function'     => 'trs_follow_screen_following',
			'default_subnav_slug' => 'following',
			'item_css_id'         => 'members-following'
		) );

		/** FOLLOWING NAV ************************************************/

		trs_core_new_nav_item( array(
			'name'                => sprintf( __( 'Followers <span>%d</span>', 'trs-follow' ), $counts['followers'] ),
			'slug'                => $trs->follow->followers->slug,
			'position'            => apply_filters( 'trs_follow_followers_nav_position', 62 ),
			'screen_function'     => 'trs_follow_screen_followers',
			'default_subnav_slug' => 'followers',
			'item_css_id'         => 'members-followers'
		) );

		/** ACTIVITY SUBNAV **********************************************/

		// Add activity sub nav item
		if ( trs_is_active( 'activity' ) && apply_filters( 'trs_follow_show_activity_subnav', true ) ) {

			trs_core_new_subnav_item( array(
				'name'            => __( 'Following', 'trs-follow' ),
				'slug'            => constant( 'TRS_FOLLOWING_SLUG' ),
				'parent_url'      => trailingslashit( $domain . trs_get_activity_slug() ),
				'parent_slug'     => trs_get_activity_slug(),
				'screen_function' => 'trs_follow_screen_activity_following',
				'position'        => 21,
				'item_css_id'     => 'activity-following'
			) );
		}

		// BuddyBar compatibility
		add_action( 'trs_adminbar_menus', array( $this, 'group_buddybar_items' ), 3 );

		do_action( 'trs_follow_setup_nav' );

	}

	/**
	 * Set up TRM Toolbar / Admin Bar.
	 *
	 * @global obj $trs trendr instance
	 */
	function setup_admin_bar() {
		// Prevent debug notices
		$trm_admin_nav = array();

		// Menus for logged in user
		if ( is_user_logged_in() ) {
			global $trs;

			// "Follow" parent nav menu
			$trm_admin_nav[] = array(
				'parent' => $trs->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Follow', 'trs-follow' ),
				'href'   => trailingslashit( trs_loggedin_user_domain() . $trs->follow->following->slug )
			);

			// "Following" subnav item
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-following',
				'title'  => __( 'Following', 'trs-follow' ),
				'href'   => trailingslashit( trs_loggedin_user_domain() . $trs->follow->following->slug )
			);

			// "Followers" subnav item
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-followers',
				'title'  => __( 'Followers', 'trs-follow' ),
				'href'   => trailingslashit( trs_loggedin_user_domain() . $trs->follow->followers->slug )
			);

			// "Activity > Following" subnav item
			if ( trs_is_active( 'activity' ) && apply_filters( 'trs_follow_show_activity_subnav', true ) ) {
				$trm_admin_nav[] = array(
					'parent' => 'my-account-activity',
					'id'     => 'my-account-activity-following',
					'title'  => __( 'Following', 'trs-follow' ),
					'href'   => trailingslashit( trs_loggedin_user_domain() . trs_get_activity_slug() . '/' . $trs->follow->following->slug )
				);
			}

		}

		parent::setup_admin_bar( apply_filters( 'trs_follow_toolbar', $trm_admin_nav ) );
	}

	/**
	 * Groups follow nav items together in the BuddyBar.
	 *
	 * For TRS Follow, we use separate nav items for the "Following" and
	 * "Followers" pages, but for the BuddyBar, we want to group them together.
	 *
	 * Because of the way trendr renders both the BuddyBar and profile nav
	 * with the same code, to alter just the BuddyBar, you need to resort to
	 * hacking the $trs global later on.
	 *
	 * This will probably break in future versions of TRS, when that happens we'll
	 * remove this entirely.
	 *
	 * If the TRM Toolbar is in use, this method is skipped.
	 *
	 * @global object $trs trendr global settings
	 * @uses trs_follow_total_follow_counts() Get the following/followers counts for a user.
	 */
	function group_buddybar_items() {
		// don't do this if we're using the TRM Admin Bar / Toolbar
		if ( defined( 'TRS_USE_TRM_ADMIN_BAR' ) && TRS_USE_TRM_ADMIN_BAR )
			return;

		if ( ! trs_loggedin_user_id() )
			return;

		global $trs;

		// get follow nav positions
		$following_position = apply_filters( 'trs_follow_following_nav_position', 61 );
		$followers_position = apply_filters( 'trs_follow_followers_nav_position', 62 );

		// clobberin' time!
		unset( $trs->trs_nav[$following_position] );
		unset( $trs->trs_nav[$followers_position] );
		unset( $trs->trs_options_nav['following'] );
		unset( $trs->trs_options_nav['followers'] );

		// Add the "Follow" nav menu
		$trs->trs_nav[$following_position] = array(
			'name'                    => __( 'Follow', 'trs-follow' ),
			'link'                    => trailingslashit( trs_loggedin_user_domain() . $trs->follow->following->slug ),
			'slug'                    => 'follow',
			'css_id'                  => 'follow',
			'position'                => $following_position,
			'show_for_displayed_user' => 1,
			'screen_function'         => 'trs_follow_screen_followers'
		);

		// "Following" subnav item
		$trs->trs_options_nav['follow'][10] = array(
			'name'            => __( 'Following', 'trs-follow' ),
			'link'            => trailingslashit( trs_loggedin_user_domain() . $trs->follow->following->slug ),
			'slug'            => $trs->follow->following->slug,
			'css_id'          => 'following',
			'position'        => 10,
			'user_has_access' => 1,
			'screen_function' => 'trs_follow_screen_followers'
		);

		// "Followers" subnav item
		$trs->trs_options_nav['follow'][20] = array(
			'name'            => __( 'Followers', 'trs-follow' ),
			'link'            => trailingslashit( trs_loggedin_user_domain() . $trs->follow->followers->slug ),
			'slug'            => $trs->follow->followers->slug,
			'css_id'          => 'followers',
			'position'        => 20,
			'user_has_access' => 1,
			'screen_function' => 'trs_follow_screen_followers'
		);

		// Resort the nav items to account for the late change made above
		ksort( $trs->trs_nav );
	}

	/**
	 * Enqueues the javascript.
	 *
	 * The JS is used to add AJAX functionality when clicking on the follow button.
	 */
	function enqueue_scripts() {
		// Do not enqueue if no user is logged in
		if ( ! is_user_logged_in() )
			return;

		trm_enqueue_script( 'trs-follow-js', constant( 'TRS_FOLLOW_URL' ) . '_inc/trs-follow.js', array( 'jquery' ) );
	}

}

/**
 * Loads the Follow component into the $trs global
 *
 * @package TRS-Follow
 * @global obj $trs trendr instance
 * @since 1.2
 */
function trs_follow_setup_component() {
	global $trs;

	$trs->follow = new TRS_Follow_Component;
}
add_action( 'trs_loaded', 'trs_follow_setup_component' );
