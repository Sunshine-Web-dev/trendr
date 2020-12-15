<?php
/**
 * trendr Groups Loader
 *
 * A groups component, for users to group themselves together. Includes a
 * robust sub-component API that allows Groups to be extended.
 * Comes preconfigured with an activity stream, discussion forums, and settings.
 *
 * @package trendr
 * @sutrsackage Groups Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class TRS_Groups_Component extends TRS_Component {

	/**
	 * Start the groups component creation process
	 *
	 * @since 1.5
	 */
	function __construct() {
		parent::start(
			'groups',
			__( 'User Groups', 'trendr' ),
			TRS_PLUGIN_DIR
		);
	}

	/**
	 * Include files
	 */
	function includes() {
		$includes = array(
			'cache',
			'forums',
			'actions',
			'filters',
			'screens',
			'classes',
			//'widgets',
			'activity',
			'template',
			//'buddybar',
			//'adminbar',
			'functions',
			'notifications'
		);
		parent::includes( $includes );
	}

	/**
	 * Setup globals
	 *
	 * The TRS_GROUPS_SLUG constant is deprecated, and only used here for
	 * backwards compatibility.
	 *
	 * @since 1.5
	 * @global obj $trs
	 */
	function setup_globals() {
		global $trs;

		// Define a slug, if necessary
		if ( !defined( 'TRS_GROUPS_SLUG' ) )
			define( 'TRS_GROUPS_SLUG', $this->id );

		// Global tables for messaging component
		$global_tables = array(
			'table_name'           => $trs->table_prefix . 'trs_groups',
			'table_name_members'   => $trs->table_prefix . 'trs_groups_members',
			'table_name_groupmeta' => $trs->table_prefix . 'trs_groups_groupmeta'
		);

		// All globals for messaging component.
		// Note that global_tables is included in this array.
		$globals = array(
			'path'                  => TRS_PLUGIN_DIR,
			'slug'                  => TRS_GROUPS_SLUG,
			'root_slug'             => isset( $trs->pages->groups->slug ) ? $trs->pages->groups->slug : TRS_GROUPS_SLUG,
			'has_directory'         => true,
			'notification_callback' => 'groups_format_notifications',
			'search_string'         => __( 'Search Groups...', 'trendr' ),
			'global_tables'         => $global_tables
		);

		parent::setup_globals( $globals );

		/** Single Group Globals **********************************************/

		// Are we viewing a single group?
		if ( trs_is_groups_component() && $group_id = TRS_Groups_Group::group_exists( trs_current_action() ) ) {

			$trs->is_single_item  = true;
			$current_group_class = apply_filters( 'trs_groups_current_group_class', 'TRS_Groups_Group' );
			$this->current_group = apply_filters( 'trs_groups_current_group_object', new $current_group_class( $group_id ) );

			// When in a single group, the first action is bumped down one because of the
			// group name, so we need to adjust this and set the group name to current_item.
			$trs->current_item   = trs_current_action();
			$trs->current_action = trs_action_variable( 0 );
			array_shift( $trs->action_variables );

			// Using "item" not "group" for generic support in other components.
			if ( is_super_admin() )
				trs_update_is_item_admin( true, 'groups' );
			else
				trs_update_is_item_admin( groups_is_user_admin( $trs->loggedin_user->id, $this->current_group->id ), 'groups' );

			// If the user is not an admin, check if they are a moderator
			if ( !trs_is_item_admin() )
				trs_update_is_item_mod  ( groups_is_user_mod  ( $trs->loggedin_user->id, $this->current_group->id ), 'groups' );

			// Is the logged in user a member of the group?
			if ( ( is_user_logged_in() && groups_is_user_member( $trs->loggedin_user->id, $this->current_group->id ) ) )
				$this->current_group->is_user_member = true;
			else
				$this->current_group->is_user_member = false;

			// Should this group be visible to the logged in user?
			if ( 'public' == $this->current_group->status || $this->current_group->is_user_member )
				$this->current_group->is_visible = true;
			else
				$this->current_group->is_visible = false;

			// If this is a private or hidden group, does the user have access?
			if ( 'private' == $this->current_group->status || 'hidden' == $this->current_group->status ) {
				if ( $this->current_group->is_user_member && is_user_logged_in() || is_super_admin() )
					$this->current_group->user_has_access = true;
				else
					$this->current_group->user_has_access = false;
			} else {
				$this->current_group->user_has_access = true;
			}

		// Set current_group to 0 to prevent debug errors
		} else {
			$this->current_group = 0;
		}

		// Illegal group names/slugs
		$this->forbidden_names = apply_filters( 'groups_forbidden_names', array(
			'my-groups',
			'create',
			'invites',
			'send-invites',
			'forum',
			'delete',
			'add',
			'admin',
			'request-membership',
			'members',
			'settings',
			'portrait',
			$this->slug,
			$this->root_slug,
		) );

		// If the user was attempting to access a group, but no group by that name was found, 404
		if ( trs_is_groups_component() && empty( $this->current_group ) && !empty( $trs->current_action ) && !in_array( $trs->current_action, $this->forbidden_names ) ) {
			trs_do_404();
			return;
		}

		// Group access control
		if ( trs_is_groups_component() && !empty( $this->current_group ) && !empty( $trs->current_action ) && !$this->current_group->user_has_access ) {
			if ( is_user_logged_in() ) {
				// Off-limits to this user. Throw an error and redirect to the
				// group's home page
				trs_core_no_access( array(
					'message'  => __( 'You do not have access to this group.', 'trendr' ),
					'root'     => trs_get_group_permalink( $trs->groups->current_group ),
					'redirect' => false
				) );
			} else {
				// Allow the user to log in
				trs_core_no_access();
			}
		}

		// Preconfigured group creation steps
		$this->group_creation_steps = apply_filters( 'groups_create_group_steps', array(
			'group-details'  => array(
				'name'       => __( 'Details',  'trendr' ),
				'position'   => 0
			),
			'group-settings' => array(
				'name'       => __( 'Settings', 'trendr' ),
				'position'   => 10
			)
		) );

		// If portrait uploads are not disabled, add portrait option
		if ( !(int)trs_get_option( 'trs-disable-portrait-uploads' ) ) {
			$this->group_creation_steps['group-portrait'] = array(
				'name'     => __( 'Avatar',   'trendr' ),
				'position' => 20
			);
		}

		// If friends component is active, add invitations
		if ( trs_is_active( 'friends' ) ) {
			$this->group_creation_steps['group-invites'] = array(
				'name'     => __( 'Invites', 'trendr' ),
				'position' => 30
			);
		}

		// Groups statuses
		$this->valid_status = apply_filters( 'groups_valid_status', array(
			'public',
			'private',
			'hidden'
		) );

		// Auto join group when non group member performs group activity
		$this->auto_join = defined( 'TRS_DISABLE_AUTO_GROUP_JOIN' ) && TRS_DISABLE_AUTO_GROUP_JOIN ? false : true;
	}

	/**
	 * Setup BuddyBar navigation
	 *
	 * @global obj $trs
	 */
	function setup_nav() {
		global $trs;

		// Add 'Groups' to the main navigation
		$main_nav = array(
			'name'                => sprintf( __( 'Groups <span>%d</span>', 'trendr' ), groups_total_groups_for_user() ),
			'slug'                => $this->slug,
			'position'            => 70,
			'screen_function'     => 'groups_screen_my_groups',
			'default_subnav_slug' => 'my-groups',
			'item_css_id'         => $this->id
		);

		$groups_link = trailingslashit( $trs->loggedin_user->domain . $this->slug );

		// Add the My Groups nav item
		$sub_nav[] = array(
			'name'            => __( 'Memberships', 'trendr' ),
			'slug'            => 'my-groups',
			'parent_url'      => $groups_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'groups_screen_my_groups',
			'position'        => 10,
			'item_css_id'     => 'groups-my-groups'
		);

		// Add the Group Invites nav item
		$sub_nav[] = array(
			'name'            => __( 'Invitations', 'trendr' ),
			'slug'            => 'invites',
			'parent_url'      => $groups_link,
			'parent_slug'     => $this->slug,
			'screen_function' => 'groups_screen_group_invites',
			'user_has_access' =>  trs_is_my_profile(),
			'position'        => 30
		);

		parent::setup_nav( $main_nav, $sub_nav );

		if ( trs_is_groups_component() && trs_is_single_item() ) {

			unset( $main_nav ); unset( $sub_nav );

			// Add 'Groups' to the main navigation
			$main_nav = array(
				'name'                => __( 'Memberships', 'trendr' ),
				'slug'                => $this->current_group->slug,
				'position'            => -1, // Do not show in BuddyBar
				'screen_function'     => 'groups_screen_group_home',
				'default_subnav_slug' => 'home',
				'item_css_id'         => $this->id
			);

			$group_link = trailingslashit( trs_get_root_domain() . '/' . $this->root_slug . '/' . $this->current_group->slug );

			// Add the "Home" subnav item, as this will always be present
			$sub_nav[] = array(
				'name'            =>  _x( 'Home', 'Group home navigation title', 'trendr' ),
				'slug'            => 'home',
				'parent_url'      => $group_link,
				'parent_slug'     => $this->current_group->slug,
				'screen_function' => 'groups_screen_group_home',
				'position'        => 10,
				'item_css_id'     => 'home'
			);

			// If the user is a group mod or more, then show the group admin nav item
			if ( trs_is_item_admin() || trs_is_item_mod() ) {
				$sub_nav[] = array(
					'name'            => __( 'Admin', 'trendr' ),
					'slug'            => 'admin',
					'parent_url'      => $group_link,
					'parent_slug'     => $this->current_group->slug,
					'screen_function' => 'groups_screen_group_admin',
					'position'        => 20,
					'user_has_access' => ( $trs->is_item_admin + (int)$trs->is_item_mod ),
					'item_css_id'     => 'admin'
				);
			}

			// If this is a private group, and the user is not a member, show a "Request Membership" nav item.
			if ( is_user_logged_in() &&
				 !is_super_admin() &&
				 !$this->current_group->is_user_member &&
				 !groups_check_for_membership_request( $trs->loggedin_user->id, $this->current_group->id ) &&
				 $this->current_group->status == 'private'
				) {
				$sub_nav[] = array(
					'name'               => __( 'Request Membership', 'trendr' ),
					'slug'               => 'request-membership',
					'parent_url'         => $group_link,
					'parent_slug'        => $this->current_group->slug,
					'screen_function'    => 'groups_screen_group_request_membership',
					'position'           => 30
				);
			}

			// Forums are enabled and turned on
			if ( $this->current_group->enable_forum && trs_is_active( 'forums' ) ) {
				$sub_nav[] = array(
					'name'            => __( 'Forum', 'trendr' ),
					'slug'            => 'forum',
					'parent_url'      => $group_link,
					'parent_slug'     => $this->current_group->slug,
					'screen_function' => 'groups_screen_group_forum',
					'position'        => 40,
					'user_has_access' => $this->current_group->user_has_access,
					'item_css_id'     => 'forums'
				);
			}

			$sub_nav[] = array(
				'name'            => sprintf( __( 'Members <span>%s</span>', 'trendr' ), number_format( $this->current_group->total_member_count ) ),
				'slug'            => 'members',
				'parent_url'      => $group_link,
				'parent_slug'     => $this->current_group->slug,
				'screen_function' => 'groups_screen_group_members',
				'position'        => 60,
				'user_has_access' => $this->current_group->user_has_access,
				'item_css_id'     => 'members'
			);

			if ( trs_is_active( 'friends' ) && trs_groups_user_can_send_invites() ) {
				$sub_nav[] = array(
					'name'            => __( 'Send Invites', 'trendr' ),
					'slug'            => 'send-invites',
					'parent_url'      => $group_link,
					'parent_slug'     => $this->current_group->slug,
					'screen_function' => 'groups_screen_group_invite',
					'item_css_id'     => 'invite',
					'position'        => 70,
					'user_has_access' => $this->current_group->user_has_access
				);
			}

			parent::setup_nav( $main_nav, $sub_nav );
		}

		if ( isset( $this->current_group->user_has_access ) )
			do_action( 'groups_setup_nav', $this->current_group->user_has_access );
		else
			do_action( 'groups_setup_nav');
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
			$groups_link = trailingslashit( $user_domain . $this->slug );

			// Pending group invites
			$count = groups_get_invites_for_user( $trs->loggedin_user->id );

			if ( !empty( $count->total ) ) {
				$title   = sprintf( __( 'Groups <span class="count">%s</span>',          'trendr' ), $count->total );
				$pending = sprintf( __( 'Pending Invites <span class="count">%s</span>', 'trendr' ), $count->total );
			} else {
				$title   = __( 'Groups',             'trendr' );
				$pending = __( 'No Pending Invites', 'trendr' );
			}

			// Add the "My Account" sub menus
			$trm_admin_nav[] = array(
				'parent' => $trs->my_account_menu_id,
				'id'     => 'my-account-' . $this->id,
				'title'  => $title,
				'href'   => trailingslashit( $groups_link )
			);

			// My Groups
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-memberships',
				'title'  => __( 'Memberships', 'trendr' ),
				'href'   => trailingslashit( $groups_link )
			);

			// Invitations
			$trm_admin_nav[] = array(
				'parent' => 'my-account-' . $this->id,
				'id'     => 'my-account-' . $this->id . '-invites',
				'title'  => $pending,
				'href'   => trailingslashit( $groups_link . 'invites' )
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

		if ( trs_is_groups_component() ) {

			if ( trs_is_my_profile() && !trs_is_single_item() ) {

				$trs->trs_options_title = __( 'Memberships', 'trendr' );

			} else if ( !trs_is_my_profile() && !trs_is_single_item() ) {

				$trs->trs_options_portrait = trs_core_fetch_portrait( array(
					'item_id' => $trs->displayed_user->id,
					'type'    => 'thumb'
				) );
				$trs->trs_options_title  = $trs->displayed_user->fullname;

			// We are viewing a single group, so set up the
			// group navigation menu using the $this->current_group global.
			} else if ( trs_is_single_item() ) {
				$trs->trs_options_title  = $this->current_group->name;
				$trs->trs_options_portrait = trs_core_fetch_portrait( array(
					'item_id'    => $this->current_group->id,
					'object'     => 'group',
					'type'       => 'thumb',
					'portrait_dir' => 'group-portraits',
					'alt'        => __( 'Group Avatar', 'trendr' )
				) );
				if ( empty( $trs->trs_options_portrait ) )
					$trs->trs_options_portrait = '<img src="' . esc_attr( $group->portrait_full ) . '" class="portrait" alt="' . esc_attr( $group->name ) . '" />';
			}
		}

		parent::setup_title();
	}
}
// Create the groups component
$trs->groups = new TRS_Groups_Component();

?>
