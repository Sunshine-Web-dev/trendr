<?php
/**
 * trendr Groups Admin Bar
 *
 * Handles the groups functions related to the WordPress Admin Bar
 *
 * @package trendr
 * @sutrsackage Groups
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Adjust the admin bar menus based on which WordPress version this is
 *
 * @since trendr (1.5.2)
 */
function trs_groups_admin_bar_version_check() {
	switch( trs_get_major_trm_version() ) {
		case 3.2 :
			add_action( 'trs_setup_admin_bar', 'trs_groups_group_admin_menu', 99 );
			break;
		case 3.3 :
		case 3.4 :
		default  :
			remove_action( 'admin_bar_menu', 'trm_admin_bar_edit_menu',  80  );
			add_action( 'admin_bar_menu', 'trs_groups_group_admin_menu', 400 );
			break;		
	}
}
add_action( 'admin_bar_menu', 'trs_groups_admin_bar_version_check', 4 );

/**
 * Adds the Group Admin top-level menu to group pages
 *
 * @package trendr
 * @since 1.5
 *
 * @todo Add dynamic menu items for group extensions
 */
function trs_groups_group_admin_menu() {
	global $trm_admin_bar, $trs;

	// Only show if viewing a group
	if ( !trs_is_group() )
		return false;

	// Only show this menu to group admins and super admins
	if ( !is_super_admin() && !trs_group_is_admin() )
		return false;

	if ( '3.2' == trs_get_major_trm_version() ) {

		// Group portrait
		$portrait = trs_core_fetch_portrait( array(
			'object'     => 'group',
			'type'       => 'thumb',
			'portrait_dir' => 'group-portraits',
			'item_id'    => $trs->groups->current_group->id,
			'width'      => 16,
			'height'     => 16
		) );

		// Unique ID for the 'My Account' menu
		$trs->group_admin_menu_id = ( ! empty( $portrait ) ) ? 'group-admin-with-portrait' : 'group-admin';

		// Add the top-level Group Admin button
		$trm_admin_bar->add_menu( array(
			'id'    => $trs->group_admin_menu_id,
			'title' => $portrait . trs_get_current_group_name(),
			'href'  => trs_get_group_permalink( $trs->groups->current_group )
		) );

	} elseif ( '3.3' == trs_get_major_trm_version() ) {
		
		// Unique ID for the 'My Account' menu
		$trs->group_admin_menu_id = 'group-admin';

		// Add the top-level Group Admin button
		$trm_admin_bar->add_menu( array(
			'id'    => $trs->group_admin_menu_id,
			'title' => __( 'Edit Group', 'trendr' ),
			'href'  => trs_get_group_permalink( $trs->groups->current_group )
		) );
	}

	// Group Admin > Edit details
	$trm_admin_bar->add_menu( array(
		'parent' => $trs->group_admin_menu_id,
		'id'     => 'edit-details',
		'title'  => __( 'Edit Details', 'trendr' ),
		'href'   =>  trs_get_groups_action_link( 'admin/edit-details' )
	) );

	// Group Admin > Group settings
	$trm_admin_bar->add_menu( array(
		'parent' => $trs->group_admin_menu_id,
		'id'     => 'group-settings',
		'title'  => __( 'Edit Settings', 'trendr' ),
		'href'   =>  trs_get_groups_action_link( 'admin/group-settings' )
	) );

	// Group Admin > Group portrait
	if ( !(int)trs_get_option( 'trs-disable-portrait-uploads' ) ) {
		$trm_admin_bar->add_menu( array(
			'parent' => $trs->group_admin_menu_id,
			'id'     => 'group-portrait',
			'title'  => __( 'Edit Avatar', 'trendr' ),
			'href'   =>  trs_get_groups_action_link( 'admin/group-portrait' )
		) );
	}

	// Group Admin > Manage invitations
	if ( trs_is_active( 'friends' ) ) {
		$trm_admin_bar->add_menu( array(
			'parent' => $trs->group_admin_menu_id,
			'id'     => 'manage-invitations',
			'title'  => __( 'Manage Invitations', 'trendr' ),
			'href'   =>  trs_get_groups_action_link( 'send-invites' )
		) );
	}

	// Group Admin > Manage members
	$trm_admin_bar->add_menu( array(
		'parent' => $trs->group_admin_menu_id,
		'id'     => 'manage-members',
		'title'  => __( 'Manage Members', 'trendr' ),
		'href'   =>  trs_get_groups_action_link( 'admin/manage-members' )
	) );

	// Group Admin > Membership Requests
	if ( trs_get_group_status( $trs->groups->current_group ) == 'private' ) {
		$trm_admin_bar->add_menu( array(
			'parent' => $trs->group_admin_menu_id,
			'id'     => 'membership-requests',
			'title'  => __( 'Membership Requests', 'trendr' ),
			'href'   =>  trs_get_groups_action_link( 'admin/membership-requests' )
		) );
	}

	// Delete Group
	$trm_admin_bar->add_menu( array(
		'parent' => $trs->group_admin_menu_id,
		'id'     => 'delete-group',
		'title'  => __( 'Delete Group', 'trendr' ),
		'href'   =>  trs_get_groups_action_link( 'admin/delete-group' )
	) );
}

?>