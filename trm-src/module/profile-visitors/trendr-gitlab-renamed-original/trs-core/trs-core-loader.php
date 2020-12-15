<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Require all of the trendr core libraries
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-cache.php'     );
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-hooks.php'     );
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-cssjs.php'     );
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-classes.php'   );
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-filters.php'   );
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-portraits.php'   );
//require( TRS_PLUGIN_DIR . '/trs-core/trs-core-widgets.php'   );
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-template.php'  );
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-buddybar.php'  );
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-catchuri.php'  );
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-component.php' );
require( TRS_PLUGIN_DIR . '/trs-core/trs-core-functions.php' );

// Load deprecated functions
require( TRS_PLUGIN_DIR . '/trs-core/deprecated/1.5.php'    );

// Load the TRM admin bar.
if ( !defined( 'TRS_DISABLE_ADMIN_BAR' ) )
	require( TRS_PLUGIN_DIR . '/trs-core/trs-core-adminbar.php'  );

// Move active components from sitemeta, if necessary
// Provides backpat with earlier versions of TRS
if ( is_multisite() && $active_components = get_site_option( 'trs-active-components' ) )
	trs_update_option( 'trs-active-components', $active_components );

/** "And now for something completely different" ******************************/

class TRS_Core extends TRS_Component {

	function __construct() {
		parent::start(
			'_core',
			__( 'trendr Core', 'trendr' )
			, TRS_PLUGIN_DIR
		);

		$this->bootstrap();
	}

	private function bootstrap() {
		global $trs;

		/**
		 * At this point in the stack, trendr core has been loaded but
		 * individual components (friends/activity/groups/etc...) have not.
		 *
		 * The 'trs_core_loaded' action lets you execute code ahead of the
		 * other components.
		 */
		do_action( 'trs_core_loaded' );

		/** Components ********************************************************/

		// Set the included and optional components.
		$trs->optional_components = apply_filters( 'trs_optional_components', array( 'activity', 'blogs', 'forums', 'friends', 'groups', 'messages', 'settings', 'xprofile' ) );

		// Set the required components
		$trs->required_components = apply_filters( 'trs_required_components', array( 'members' ) );

		// Get a list of activated components
		if ( $active_components = trs_get_option( 'trs-active-components' ) ) {
			$trs->active_components      = apply_filters( 'trs_active_components', $active_components );
			$trs->deactivated_components = apply_filters( 'trs_deactivated_components', array_values( array_diff( array_values( array_merge( $trs->optional_components, $trs->required_components ) ), array_keys( $trs->active_components ) ) ) );

		// Pre 1.5 Backwards compatibility
		} elseif ( $deactivated_components = trs_get_option( 'trs-deactivated-components' ) ) {
			// Trim off namespace and filename
			foreach ( (array) $deactivated_components as $component => $value )
				$trimmed[] = str_replace( '.php', '', str_replace( 'trs-', '', $component ) );

			// Set globals
			$trs->deactivated_components = apply_filters( 'trs_deactivated_components', $trimmed );

			// Setup the active components
			$active_components     = array_flip( array_diff( array_values( array_merge( $trs->optional_components, $trs->required_components ) ), array_values( $trs->deactivated_components ) ) );

			// Loop through active components and set the values
			$trs->active_components = array_map( '__return_true', $active_components );

			// Set the active component global
			$trs->active_components = apply_filters( 'trs_active_components', $trs->active_components );

		// Default to all components active
		} else {
			// Set globals
			$trs->deactivated_components = array();

			// Setup the active components
			$active_components     = array_flip( array_values( array_merge( $trs->optional_components, $trs->required_components ) ) );

			// Loop through active components and set the values
			$trs->active_components = array_map( '__return_true', $active_components );

			// Set the active component global
			$trs->active_components = apply_filters( 'trs_active_components', $trs->active_components );
		}

		// Loop through optional components
		foreach( $trs->optional_components as $component )
			if ( trs_is_active( $component ) && file_exists( TRS_PLUGIN_DIR . '/trs-' . $component . '/trs-' . $component . '-loader.php' ) )
				include( TRS_PLUGIN_DIR . '/trs-' . $component . '/trs-' . $component . '-loader.php' );

		// Loop through required components
		foreach( $trs->required_components as $component )
			if ( file_exists( TRS_PLUGIN_DIR . '/trs-' . $component . '/trs-' . $component . '-loader.php' ) )
				include( TRS_PLUGIN_DIR . '/trs-' . $component . '/trs-' . $component . '-loader.php' );

		// Add Core to required components
		$trs->required_components[] = 'core';
	}

	function setup_globals() {
		global $trs;

		/** Database **********************************************************/

		// Get the base database prefix
		if ( empty( $trs->table_prefix ) )
			$trs->table_prefix = trs_core_get_table_prefix();

		// The domain for the root of the site where the main blog resides
		if ( empty( $trs->root_domain ) )
			$trs->root_domain = trs_core_get_root_domain();

		// Fetches all of the core trendr settings in one fell swoop
		if ( empty( $trs->site_options ) )
			$trs->site_options = trs_core_get_root_options();

		// The names of the core WordPress pages used to display trendr content
		if ( empty( $trs->pages ) )
			$trs->pages = trs_core_get_directory_pages();

		/** Admin Bar *********************************************************/

		// Set the 'My Account' global to prevent debug notices
		$trs->my_account_menu_id = false;

		/** Component and Action **********************************************/

		// Used for overriding the 2nd level navigation menu so it can be used to
		// display custom navigation for an item (for example a group)
		$trs->is_single_item = false;

		// Sets up the array container for the component navigation rendered
		// by trs_get_nav()
		$trs->trs_nav            = array();

		// Sets up the array container for the component options navigation
		// rendered by trs_get_options_nav()
		$trs->trs_options_nav    = array();

		// Contains an array of all the active components. The key is the slug,
		// value the internal ID of the component.
		//$trs->active_components = array();

		/** Basic current user data *******************************************/

		// Logged in user is the 'current_user'
		$current_user            = trm_get_current_user();

		// The user ID of the user who is currently logged in.
		$trs->loggedin_user->id   = $current_user->ID;

		/** Avatars ***********************************************************/

		// Fetches the default Grportrait image to use if the user/group/blog has no portrait or grportrait
		$trs->grav_default->user  = apply_filters( 'trs_user_grportrait_default',  $trs->site_options['portrait_default'] );
		$trs->grav_default->group = apply_filters( 'trs_group_grportrait_default', $trs->grav_default->user );
		$trs->grav_default->blog  = apply_filters( 'trs_blog_grportrait_default',  $trs->grav_default->user );

		// Notifications Table
		$trs->core->table_name_notifications = $trs->table_prefix . 'trs_notifications';

		/**
		 * Used to determine if user has admin rights on current content. If the
		 * logged in user is viewing their own profile and wants to delete
		 * something, is_item_admin is used. This is a generic variable so it
		 * can be used by other components. It can also be modified, so when
		 * viewing a group 'is_item_admin' would be 'true' if they are a group
		 * admin, and 'false' if they are not.
		 */
		trs_update_is_item_admin( trs_user_has_access(), 'core' );

		// Is the logged in user is a mod for the current item?
		trs_update_is_item_mod( false,                  'core' );

		do_action( 'trs_core_setup_globals' );
	}

	function setup_nav() {
		global $trs;

		/***
		 * If the extended profiles component is disabled, we need to revert to using the
		 * built in WordPress profile information
		 */
		if ( !trs_is_active( 'xprofile' ) ) {

			// Fallback values if xprofile is disabled
			$trs->core->profile->slug = 'profile';
			$trs->active_components[$trs->core->profile->slug] = $trs->core->profile->slug;

			// Add 'Profile' to the main navigation
			$main_nav = array(
				'name'                => __( 'Profile', 'trendr' ),
				'slug'                => $trs->core->profile->slug,
				'position'            => 20,
				'screen_function'     => 'trs_core_catch_profile_uri',
				'default_subnav_slug' => 'public'
			);

			$profile_link = trailingslashit( $trs->loggedin_user->domain . '/' . $trs->core->profile->slug );

			// Add the subnav items to the profile
			$sub_nav[] = array(
				'name'            => __( 'Public', 'trendr' ),
				'slug'            => 'public',
				'parent_url'      => $profile_link,
				'parent_slug'     => $trs->core->profile->slug,
				'screen_function' => 'trs_core_catch_profile_uri'
			);
		}
	}
}

// Initialize the trendr Core
$trs->core = new TRS_Core();

?>