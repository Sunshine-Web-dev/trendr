<?php
/**
 * trendr Members Admin Bar
 *
 * Handles the member functions related to the trendr Admin Bar
 *
 * @package trendr
 * @sutrsackage Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Adjust the admin bar menus based on which trendr version this is
 *
 * @since trendr (1.5.2)
 */
function trs_members_admin_bar_version_check() {
	switch( trs_get_major_trm_version() ) {
		case 3.2 :
			add_action( 'trs_setup_admin_bar', 'trs_members_admin_bar_my_account_menu',    4    );
			add_action( 'trs_setup_admin_bar', 'trs_members_admin_bar_notifications_menu', 5    );
			add_action( 'trs_setup_admin_bar', 'trs_members_admin_bar_user_admin_menu',    99   );
			add_action( 'trs_setup_admin_bar', 'trs_members_admin_bar_my_account_logout',  9999 );
			break;
		case 3.3 :
		case 3.4 :
		default  :
			add_action( 'trs_setup_admin_bar', 'trs_members_admin_bar_my_account_menu',    4   );
			add_action( 'trs_setup_admin_bar', 'trs_members_admin_bar_notifications_menu', 5   );
			add_action( 'admin_bar_menu',     'trs_members_admin_bar_user_admin_menu',    400 );
			break;		
	}
}
add_action( 'admin_bar_menu', 'trs_members_admin_bar_version_check', 4 );

/**
 * Add the "My Account" menu and all submenus.
 *
 * @since trendr (r4151)
 */
function trs_members_admin_bar_my_account_menu() {
	global $trs, $trm_admin_bar;

	// Bail if this is an ajax request
	if ( defined( 'DOING_AJAX' ) )
		return;

	// Logged in user
	if ( is_user_logged_in() ) {

		if ( '3.2' == trs_get_major_trm_version() ) {

			// User portrait
			$portrait = trs_core_fetch_portrait( array(
				'item_id' => $trs->loggedin_user->id,
				'email'   => $trs->loggedin_user->userdata->user_email,
				'width'   => 16,
				'height'  => 16
			) );

			// Unique ID for the 'My Account' menu
			$trs->my_account_menu_id = ( ! empty( $portrait ) ) ? 'my-account-with-portrait' : 'my-account';

			// Create the main 'My Account' menu
			$trm_admin_bar->add_menu( array(
				'id'    => $trs->my_account_menu_id,
				'title' => $portrait . trs_get_loggedin_user_fullname(),
				'href'  => $trs->loggedin_user->domain
			) );

		} else {

			// Unique ID for the 'My Account' menu
			$trs->my_account_menu_id = 'my-account-trendr';

			// Create the main 'My Account' menu
			$trm_admin_bar->add_menu( array(
				'parent' => 'my-account',
				'id'     => $trs->my_account_menu_id,
				'href'   => $trs->loggedin_user->domain,
				'group'  => true,
				'meta'   => array( 'class' => 'ab-sub-secondary' )
			) );
		}

	// Show login and sign-up links
	} elseif ( !empty( $trm_admin_bar ) ) {

		add_filter ( 'show_admin_bar', '__return_true' );

		// Create the main 'My Account' menu
		$trm_admin_bar->add_menu( array(
			'id'    => 'trs-login',
			'title' => __( 'Log in', 'trendr' ),
			'href'  => trm_login_url()
		) );

		// Sign up
		if ( trs_get_signup_allowed() ) {
			$trm_admin_bar->add_menu( array(
				'id'    => 'trs-register',
				'title' => __( 'Register', 'trendr' ),
				'href'  => trs_get_signup_page()
			) );
		}
	}
}

/**
 * Adds the User Admin top-level menu to user pages
 *
 * @package trendr
 * @since 1.5
 */
function trs_members_admin_bar_user_admin_menu() {
	global $trs, $trm_admin_bar;

	// Only show if viewing a user
	if ( !trs_is_user() )
		return false;

	// Don't show this menu to non site admins or if you're viewing your own profile
	if ( !current_user_can( 'edit_users' ) || trs_is_my_profile() )
		return false;

	if ( '3.2' == trs_get_major_trm_version() ) {

		// User portrait
		$portrait = trs_core_fetch_portrait( array(
			'item_id' => $trs->displayed_user->id,
			'email'   => $trs->displayed_user->userdata->user_email,
			'width'   => 16,
			'height'  => 16
		) );

		// Unique ID for the 'My Account' menu
		$trs->user_admin_menu_id = ( ! empty( $portrait ) ) ? 'user-admin-with-portrait' : 'user-admin';

		// Add the top-level User Admin button
		$trm_admin_bar->add_menu( array(
			'id'    => $trs->user_admin_menu_id,
			'title' => $portrait . trs_get_displayed_user_fullname(),
			'href'  => trs_displayed_user_domain()
		) );

	} elseif ( '3.3' == trs_get_major_trm_version() ) {
		
		// Unique ID for the 'My Account' menu
		$trs->user_admin_menu_id = 'user-admin';

		// Add the top-level User Admin button
		$trm_admin_bar->add_menu( array(
			'id'    => $trs->user_admin_menu_id,
			'title' => __( 'Edit Member', 'trendr' ),
			'href'  => trs_displayed_user_domain()
		) );
	}

	// User Admin > Edit this user's profile
	$trm_admin_bar->add_menu( array(
		'parent' => $trs->user_admin_menu_id,
		'id'     => 'edit-profile',
		'title'  => __( "Edit Profile", 'trendr' ),
		'href'   => trs_get_members_component_link( 'profile', 'edit' )
	) );

	// User Admin > Edit this user's portrait
	$trm_admin_bar->add_menu( array(
		'parent' => $trs->user_admin_menu_id,
		'id'     => 'change-profile-photo',
		'title'  => __( "Edit Avatar", 'trendr' ),
		'href'   => trs_get_members_component_link( 'profile', 'change-profile-photo' )
	) );

	// User Admin > Spam/unspam
	if ( !trs_core_is_user_spammer( trs_displayed_user_id() ) ) {
		$trm_admin_bar->add_menu( array(
			'parent' => $trs->user_admin_menu_id,
			'id'     => 'spam-user',
			'title'  => __( 'Mark as Spammer', 'trendr' ),
			'href'   => trm_nonce_url( trs_displayed_user_domain() . 'admin/mark-spammer/', 'mark-unmark-spammer' ),
			'meta'   => array( 'onclick' => 'confirm(" ' . __( 'Are you sure you want to mark this user as a spammer?', 'trendr' ) . '");' )
		) );
	} else {
		$trm_admin_bar->add_menu( array(
			'parent' => $trs->user_admin_menu_id,
			'id'     => 'unspam-user',
			'title'  => __( 'Not a Spammer', 'trendr' ),
			'href'   => trm_nonce_url( trs_displayed_user_domain() . 'admin/unmark-spammer/', 'mark-unmark-spammer' ),
			'meta'   => array( 'onclick' => 'confirm(" ' . __( 'Are you sure you want to mark this user as not a spammer?', 'trendr' ) . '");' )
		) );
	}

	// User Admin > Delete Account
	$trm_admin_bar->add_menu( array(
		'parent' => $trs->user_admin_menu_id,
		'id'     => 'delete-user',
		'title'  => __( 'Delete Account', 'trendr' ),
		'href'   => trm_nonce_url( trs_displayed_user_domain() . 'admin/delete-user/', 'delete-user' ),
		'meta'   => array( 'onclick' => 'confirm(" ' . __( "Are you sure you want to delete this user's account?", 'trendr' ) . '");' )
	) );
}

/**
 * Build the "Notifications" dropdown
 *
 * @package trendr
 * @since 1.5
 */
function trs_members_admin_bar_notifications_menu() {
	global $trs, $trm_admin_bar;

	if ( !is_user_logged_in() )
		return false;

	if ( $notifications = trs_core_get_notifications_for_user( trs_loggedin_user_id(), 'object' ) ) {
		$menu_title = sprintf( __( 'Notifications <span id="ab-pending-notifications" class="pending-count">%s</span>', 'trendr' ), count( $notifications ) );
	} else {
		$menu_title = __( 'Notifications', 'trendr' );
	}

	if ( '3.2' == trs_get_major_trm_version() ) {

		// Add the top-level Notifications button
		$trm_admin_bar->add_menu( array(
			'id'    => 'trs-notifications',
			'title' => $menu_title,
			'href'  => trs_loggedin_user_domain()
		) );

	} elseif ( '3.3' == trs_get_major_trm_version() ) {
		
		// Add the top-level Notifications button
		$trm_admin_bar->add_menu( array(
			'parent' => 'top-secondary',
			'id'     => 'trs-notifications',
			'title'  => $menu_title,
			'href'   => trs_loggedin_user_domain()
		) );
	}

	if ( !empty( $notifications ) ) {
		foreach ( (array)$notifications as $notification ) {
			$trm_admin_bar->add_menu( array(
				'parent' => 'trs-notifications',
				'id'     => 'notification-' . $notification->id,
				'title'  => $notification->content,
				'href'   => $notification->href
			) );
		}
	} else {
		$trm_admin_bar->add_menu( array(
			'parent' => 'trs-notifications',
			'id'     => 'no-notifications',
			'title'  => __( 'No new notifications', 'trendr' ),
			'href'   => trs_loggedin_user_domain()
		) );
	}

	return;
}

/**
 * Make sure the logout link is at the bottom of the "My Account" menu
 *
 * @since trendr (r4151)
 *
 * @global obj $trs
 * @global obj $trm_admin_bar
 */
function trs_members_admin_bar_my_account_logout() {
	global $trs, $trm_admin_bar;

	// Bail if this is an ajax request
	if ( defined( 'DOING_AJAX' ) )
		return;

	if ( is_user_logged_in() ) {
		// Log out
		$trm_admin_bar->add_menu( array(
			'parent' => $trs->my_account_menu_id,
			'id'     => $trs->my_account_menu_id . '-logout',
			'title'  => __( 'Log Out', 'trendr' ),
			'href'   => trm_logout_url()
		) );
	}
}

?>