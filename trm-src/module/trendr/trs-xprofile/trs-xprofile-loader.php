<?php
/**
 * trendr XProfile Loader
 *
 * An extended profile component for users. This allows site admins to create
 * groups of fields for users to enter information about themselves.
 *
 * @package trendr
 * @sutrsackage XProfile Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class TRS_XProfile_Component extends TRS_Component {

	/**
	 * Start the xprofile component creation process
	 *
	 * @since 1.5
	 */
	function __construct() {
		parent::start(
			'xprofile',
			__( 'Extended Profiles', 'trendr' ),
			TRS_PLUGIN_DIR
		);
	}

	/**
	 * Include files
	 */
	function includes() {
		$includes = array(
			'cssjs',
			'cache',
			'actions',
			'activity',
			'screens',
			'classes',
			'filters',
			'template',
			'buddybar',
			'functions',
		);

		if ( is_admin() )
			$includes[] = 'admin';

		parent::includes( $includes );
	}

	/**
	 * Setup globals
	 *
	 * The TRS_XPROFILE_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since 1.5
	 * @global obj $trs
	 */
	function setup_globals() {
		global $trs;

		// Define a slug, if necessary
		if ( !defined( 'TRS_XPROFILE_SLUG' ) )
			define( 'TRS_XPROFILE_SLUG', 'profile' );

		// Assign the base group and fullname field names to constants to use
		// in SQL statements
		define ( 'TRS_XPROFILE_BASE_GROUP_NAME',     stripslashes( $trs->site_options['trs-xprofile-base-group-name']     ) );
		define ( 'TRS_XPROFILE_FULLNAME_FIELD_NAME', stripslashes( $trs->site_options['trs-xprofile-fullname-field-name'] ) );

		// Set the support field type ids
		$this->field_types = apply_filters( 'xprofile_field_types', array(
			'textbox',
			'textarea',
			'radio',
			'checkbox',
			'selectbox',
			'multiselectbox',
			'datebox'
		) );

		// Tables
		$global_tables = array(
			'table_name_data'   => $trs->table_prefix . 'trs_xprofile_data',
			'table_name_groups' => $trs->table_prefix . 'trs_xprofile_groups',
			'table_name_fields' => $trs->table_prefix . 'trs_xprofile_fields',
			'table_name_meta'   => $trs->table_prefix . 'trs_xprofile_meta',
		);

		$globals = array(
			'slug'                  => TRS_XPROFILE_SLUG,
			'has_directory'         => false,
			'notification_callback' => 'xprofile_format_notifications',
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

		// Add 'Profile' to the main navigation
		$main_nav = array(
			'name'                => __( 'Profile', 'trendr' ),
			'slug'                => $this->slug,
			'position'            => 20,
			'screen_function'     => 'xprofile_screen_display_profile',
			'default_subnav_slug' => 'public',
			'item_css_id'         => $this->id
		);

		$profile_link = trailingslashit( $trs->loggedin_user->domain . $this->slug );

		// Add the subnav items to the profile
		$sub_nav[] = array(
			'name'            => __( 'Public', 'trendr' ),
			'slug'            => 'public',
			'parent_url'      => $profile_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'xprofile_screen_display_profile',
			'position'        => 10
		);

		// Edit Profile
		$sub_nav[] = array(
			'name'            => __( 'Edit', 'trendr' ),
			'slug'            => 'edit',
			'parent_url'      => $profile_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'xprofile_screen_edit_profile',
			'position'        => 20
		);

		// Change Avatar
		$sub_nav[] = array(
			'name'            => __( 'Change Avatar', 'trendr' ),
			'slug'            => 'change-profile-photo',
			'parent_url'      => $profile_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'xprofile_screen_change_portrait',
			'position'        => 30
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

			// Profile link
			$profile_link = trailingslashit( $trs->loggedin_user->domain . $this->slug );

			// Add the "Profile" sub menu
			$trm_admin_nav[] = array(
				'parent' => $trs->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => __( 'Profile', 'trendr' ),
				'href'   => trailingslashit( $profile_link )
			);

			// View Profile
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-view',
				'title'  => __( 'View', 'trendr' ),
				'href'   => trailingslashit( $profile_link . 'public' )
			);

			// Edit Profile
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-edit',
				'title'  => __( 'Edit', 'trendr' ),
				'href'   => trailingslashit( $profile_link . 'edit' )
			);

			// Edit Profile
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-change-profile-photo',
				'title'  => __( 'Change Avatar', 'trendr' ),
				'href'   => trailingslashit( $profile_link . 'change-profile-photo' )
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

		if ( trs_is_profile_component() ) {
			if ( trs_is_my_profile() ) {
				$trs->trs_options_title = __( 'My Profile', 'trendr' );
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
// Create the xprofile component
if ( !isset( $trs->profile->id ) )
	$trs->profile = new TRS_XProfile_Component();

?>
