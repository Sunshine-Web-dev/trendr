<?php
if ( !defined( 'TRSB_VERSION' ) ) exit;

/**
 * Setup TRS Navigation
 * @since 1.0
 * @version 1.0
 */
function trsb_setup_navigation() {
	global $trs;

	if ( !is_user_logged_in() || ( !current_user_can( TRSB_ADMIN_CAP ) && trs_loggedin_user_id()  != trs_displayed_user_id() ) ) return;

	trs_core_new_subnav_item( array(
		'name'                    => __( 'Blocked Members', 'trsblock' ),
		'slug'                    => 'blocked',
		'parent_url'              => $trs->displayed_user->domain . 'settings/',
		'parent_slug'             => 'settings',
		'screen_function'         => 'trsb_my_blocked_members',
		'show_for_displayed_user' => false
	) );
}

/**
 * Load Blocking Navigation Items
 * @since 1.0
 * @version 1.0
 */
function trsb_my_blocked_members() {
	add_action( 'trs_template_title',   'trsb_my_blocked_title' );
	add_action( 'trs_template_content', 'trsb_my_blocked_members_screen' );
	trs_core_load_template( apply_filters( 'trs_core_template_plugin', 'members/single/plugins' ) );
}

/**
 * Menu Title
 * @since 1.0
 * @version 1.0
 */
function trsb_my_blocked_title() {
	if ( current_user_can( TRSB_ADMIN_CAP ) && trs_loggedin_user_id()  != trs_displayed_user_id() )
		echo __( 'Members this user blocks', 'trsblock' );
	else
		echo __( 'Members you currently block', 'trsblock' );
}

/**
 * My Blocked Members Screen
 * @since 1.0
 * @version 1.0
 */
function trsb_my_blocked_members_screen() {
	$profile_id = trs_displayed_user_id();
	$token = trm_create_nonce( 'unblock-' . $profile_id );
	$list = trsb_get_blocked_users( $profile_id );
	if ( empty( $list ) )
		$list[] = 0; ?>

<ul class="users-blocked">
	<li>
		<th class="user" style="width:70%;"><?php _e( 'User', 'trsblock' ); ?></th>
		<th class="actions" style="width:30%;"><?php _e( 'Actions', 'trsblock' ); ?></th>
	</li>
	</ul>
<?php

	// Loop though our block list
	foreach ( (array) $list as $num => $user_id ) {
		// Zero means list is empty
		if ( $user_id == 0 ) { ?>

		<tr>
			<td colspan="2"><?php _e( 'No users found', 'trsblock' ); ?></td>
		</tr>
<?php
		}
		// Else get user
		else {
			$user = get_user_by( 'id', $user_id );
			// If user has been removed, remove it from our list as well
			if ( $user === false ) {
				trsb_remove_user_from_list( $profile_id, $user_id );
				continue;
			} ?>

		<tr>
			<td class="user"><?php echo $user->display_name; ?></td>
			<td class="actions"><div class="generic-button block-this-user"><a class="activity-button unblock" href="#" data-ref="<?php echo trsb_unblock_link( $profile_id, $num ); ?>"><?php _e( 'Unblock', 'trsblock' ); ?></a></div></td>
		</tr>
<?php
		}
	}
?>

	</tbody>
</table>
<?php
}

function trsb_setup_tool_bar() {
	// Bail if this is an ajax request
	if ( !trs_use_trm_admin_bar() || defined( 'DOING_AJAX' ) )
		return;

	// Only add menu for logged in user
	if ( is_user_logged_in() ) {
		global $trs, $trm_admin_bar;

		// Add secondary parent item for all trendr components
		$trm_admin_bar->add_menu( array(
			'parent' => 'my-account-settings',
			'id'     => 'my-block-list',
			'title'  => __( 'Blocked Members', 'trsblock' ),
			'href'   => trs_loggedin_user_domain() . 'settings/blocked/'
		) );
	}
}
?>
