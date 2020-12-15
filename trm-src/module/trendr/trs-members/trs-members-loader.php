<?php
/**
 * trendr Member Loader
 *
 * A members component to help contain all of the user specific slugs
 *
 * @package trendr
 * @sutrsackage Members
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class TRS_Members_Component extends TRS_Component {

	/**
	 * Start the members component creation process
	 *
	 * @since 1.5
	 */
	function __construct() {
		parent::start(
			'members',
			__( 'Members', 'trendr' ),
			TRS_PLUGIN_DIR
		);
	}

	/**
	 * Include files
	 *
	 * @global obj $trs
	 */
	function includes() {
		$includes = array(
			'signup',
			'actions',
			'filters',
			'screens',
			'template',
			'buddybar',
			'adminbar',
			'functions',
			'notifications',
		);
		parent::includes( $includes );
	}

	/**
	 * Setup globals
	 *
	 * The TRS_MEMBERS_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since 1.5
	 * @global obj $trs
	 */
	function setup_globals() {
		global $trs, $current_user, $displayed_user_id;

		// Define a slug, if necessary
		if ( !defined( 'TRS_MEMBERS_SLUG' ) )
			define( 'TRS_MEMBERS_SLUG', $this->id );

		$globals = array(
			'path'          => TRS_PLUGIN_DIR,
			'slug'          => TRS_MEMBERS_SLUG,
			'root_slug'     => isset( $trs->pages->members->slug ) ? $trs->pages->members->slug : TRS_MEMBERS_SLUG,
			'has_directory' => true,
			'search_string' => __( 'Search Members...', 'trendr' ),
		);

		parent::setup_globals( $globals );

		/** Logged in user ****************************************************/

		// Fetch the full name for the logged in user
		$trs->loggedin_user->fullname       = trs_core_get_user_displayname( $trs->loggedin_user->id );

		// Hits the DB on single TRM installs so get this separately
		$trs->loggedin_user->is_super_admin = $trs->loggedin_user->is_site_admin = is_super_admin();

		// The domain for the user currently logged in. eg: http://domain.com/members/andy
		$trs->loggedin_user->domain         = trs_core_get_user_domain( $trs->loggedin_user->id );

		// The core userdata of the user who is currently logged in.
		$trs->loggedin_user->userdata       = trs_core_get_core_userdata( $trs->loggedin_user->id );

		/** Displayed user ****************************************************/

		// The user id of the user currently being viewed:
		// $trs->displayed_user->id is set in /trs-core/trs-core-catchuri.php
		if ( empty( $trs->displayed_user->id ) )
			$trs->displayed_user->id = 0;

		// The domain for the user currently being displayed
		$trs->displayed_user->domain   = trs_core_get_user_domain( $trs->displayed_user->id );

		// The core userdata of the user who is currently being displayed
		$trs->displayed_user->userdata = trs_core_get_core_userdata( $trs->displayed_user->id );

		// Fetch the full name displayed user
		$trs->displayed_user->fullname = trs_core_get_user_displayname( $trs->displayed_user->id );

		/** Profiles Fallback *************************************************/
		if ( !trs_is_active( 'xprofile' ) ) {
			$trs->profile->slug = 'profile';
			$trs->profile->id   = 'profile';
		}

		/** Default Profile Component *****************************************/
		if ( !defined( 'TRS_DEFAULT_COMPONENT' ) ) {
			if ( trs_is_active( 'activity' ) && isset( $trs->pages->activity ) )
				$trs->default_component = trs_get_activity_slug();
			else
				$trs->default_component = ( 'xprofile' == $trs->profile->id ) ? 'profile' : $trs->profile->id;

		} else {
			$trs->default_component = TRS_DEFAULT_COMPONENT;
		}

		if ( !$trs->current_component && $trs->displayed_user->id )
			$trs->current_component = $trs->default_component;
	}

	/**
	 * Setup BuddyBar navigation
	 *
	 * @global obj $trs
	 */
	function setup_nav() {
		global $trs;

		// Add 'Profile' to the main navigation
		if ( !trs_is_active( 'xprofile' ) ) {
			// Don't set up navigation if there's no user
			if ( !is_user_logged_in() && !trs_is_user() )
				return;

			$main_nav = array(
				'name'                => __( 'Profile', 'trendr' ),
				'slug'                => $trs->profile->slug,
				'position'            => 20,
				'screen_function'     => 'trs_members_screen_display_profile',
				'default_subnav_slug' => 'public',
				'item_css_id'         => $trs->profile->id
			);

			// User links
			$user_domain   = ( isset( $trs->displayed_user->domain ) )               ? $trs->displayed_user->domain               : $trs->loggedin_user->domain;
			$user_login    = ( isset( $trs->displayed_user->userdata->user_login ) ) ? $trs->displayed_user->userdata->user_login : $trs->loggedin_user->userdata->user_login;
			$profile_link  = trailingslashit( $user_domain . $trs->profile->slug );

			// Add the subnav items to the profile
			$sub_nav[] = array(
				'name'            => __( 'Public', 'trendr' ),
				'slug'            => 'public',
				'parent_url'      => $profile_link,
				'parent_slug'     => $trs->profile->slug,
				'screen_function' => 'trs_members_screen_display_profile',
				'position'        => 10
			);

			parent::setup_nav( $main_nav, $sub_nav );
		}
	}

	/**
	 * Sets up the title for pages and <title>
	 *
	 * @global obj $trs
	 */
	function setup_title() {
		global $trs;

		if ( trs_is_my_profile() ) {
			$trs->trs_options_title = __( 'You', 'trendr' );
		} elseif( trs_is_user() ) {
			$trs->trs_options_portrait = trs_core_fetch_portrait( array(
				'item_id' => $trs->displayed_user->id,
				'type'    => 'thumb'
			) );
			$trs->trs_options_title  = $trs->displayed_user->fullname;
		}

		parent::setup_title();
	}

}
// Create the users component
$trs->members = new TRS_Members_Component();

?>