<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function trs_groups_adminbar_admin_menu() {
	global $trs, $groups_template;

	if ( empty( $trs->groups->current_group ) )
		return false;

	// Don't show this menu to non site admins or if you're viewing your own profile
	if ( !current_user_can( 'edit_users' ) || !is_super_admin() || ( !$trs->is_item_admin && !$trs->is_item_mod ) )
		return false; ?>

	<li id="trs-adminbar-adminoptions-menu">
		<a href="<?php trs_groups_action_link( 'admin' ); ?>"><?php _e( 'Admin Options', 'trendr' ); ?></a>

		<ul>
			<li><a href="<?php trs_groups_action_link( 'admin/edit-details' ); ?>"><?php _e( 'Edit Details', 'trendr' ); ?></a></li>

			<li><a href="<?php trs_groups_action_link( 'admin/group-settings' );  ?>"><?php _e( 'Group Settings', 'trendr' ); ?></a></li>

			<?php if ( !(int)trs_get_option( 'trs-disable-portrait-uploads' ) ) : ?>

				<li><a href="<?php trs_groups_action_link( 'admin/group-portrait' ); ?>"><?php _e( 'Group Avatar', 'trendr' ); ?></a></li>

			<?php endif; ?>

			<?php if ( trs_is_active( 'friends' ) ) : ?>

				<li><a href="<?php trs_groups_action_link( 'send-invites' ); ?>"><?php _e( 'Manage Invitations', 'trendr' ); ?></a></li>

			<?php endif; ?>

			<li><a href="<?php trs_groups_action_link( 'admin/manage-members' ); ?>"><?php _e( 'Manage Members', 'trendr' ); ?></a></li>

			<?php if ( $trs->groups->current_group->status == 'private' ) : ?>

				<li><a href="<?php trs_groups_action_link( 'admin/membership-requests' ); ?>"><?php _e( 'Membership Requests', 'trendr' ); ?></a></li>

			<?php endif; ?>

			<li><a class="confirm" href="<?php echo trm_nonce_url( trs_get_group_permalink( $trs->groups->current_group ) . 'admin/delete-group/', 'groups_delete_group' ); ?>&amp;delete-group-button=1&amp;delete-group-understand=1"><?php _e( "Delete Group", 'trendr' ) ?></a></li>

			<?php do_action( 'trs_groups_adminbar_admin_menu' ) ?>

		</ul>
	</li>

	<?php
}
add_action( 'trs_adminbar_menus', 'trs_groups_adminbar_admin_menu', 20 );

?>