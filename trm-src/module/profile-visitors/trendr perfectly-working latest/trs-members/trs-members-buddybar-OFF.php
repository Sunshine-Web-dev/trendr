<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// **** "Notifications" Menu *********
function trs_adminbar_notifications_menu() {
	global $trs;

	if ( !is_user_logged_in() )
		return false;

	echo '<li id="notify"><a href="' . $trs->loggedin_user->domain . '">';
	_e( '', 'trendr' );

	if ( $notifications = trs_core_get_notifications_for_user( $trs->loggedin_user->id ) ) { ?>
		<span><?php echo count( $notifications ) ?></span>
	<?php
	}

	echo '</a>';
	echo '<ul>';

	if ( $notifications ) {
		$counter = 0;
		for ( $i = 0, $count = count( $notifications ); $i < $count; ++$i ) {
			$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : ''; ?>

			<li<?php echo $alt ?>><?php echo $notifications[$i] ?></li>

			<?php $counter++;
		}
	} else { ?>

		<li><a href="<?php echo $trs->loggedin_user->domain ?>"><?php _e( '.', 'trendr' ); ?></a></li>

	<?php
	}

	echo '</ul>';
	echo '</li>';
}
add_action( 'trs_adminbar_menus', 'trs_adminbar_notifications_menu', 8 );

// **** "Blog Authors" Menu (visible when not logged in) ********
function trs_adminbar_authors_menu() {
	global $trs, $trmdb;

	// Only for multisite
	if ( !is_multisite() )
		return false;

	// Hide on root blog
	if ( $trmdb->blogid == trs_get_root_blog_id() || !trs_is_active( 'blogs' ) )
		return false;

	$blog_prefix = $trmdb->get_blog_prefix( $trmdb->blogid );
	$authors     = $trmdb->get_results( "SELECT user_id, user_login, user_nicename, display_name, user_email, meta_value as caps FROM $trmdb->users u, $trmdb->usermeta um WHERE u.ID = um.user_id AND meta_key = '{$blog_prefix}capabilities' ORDER BY um.user_id" );

	if ( !empty( $authors ) ) {
		// This is a blog, render a menu with links to all authors
		echo '<li id="trs-adminbar-authors-menu"><a href="/">';
		_e('Blog Authors', 'trendr');
		echo '</a>';

		echo '<ul class="author-list">';
		foreach( (array)$authors as $author ) {
			$caps = maybe_unserialize( $author->caps );
			if ( isset( $caps['subscriber'] ) || isset( $caps['contributor'] ) ) continue;

			echo '<li>';
			echo '<a href="' . trs_core_get_user_domain( $author->user_id, $author->user_nicename, $author->user_login ) . '">';
			echo trs_core_fetch_portrait( array( 'item_id' => $author->user_id, 'email' => $author->user_email, 'width' => 15, 'height' => 15 ) ) ;
 			echo ' ' . $author->display_name . '</a>';
			echo '<div class="admin-bar-clear"></div>';
			echo '</li>';
		}
		echo '</ul>';
		echo '</li>';
	}
}
add_action( 'trs_adminbar_menus', 'trs_adminbar_authors_menu', 12 );

/**
 * Adds an admin bar menu to any profile page providing site moderator actions
 * that allow capable users to clean up a users account.
 *
 * @package trendr XProfile
 * @global $trs trendr
 */
function trs_members_adminbar_admin_menu() {
	global $trs;

	// Only show if viewing a user
	if ( !$trs->displayed_user->id )
		return false;

	// Don't show this menu to non site admins or if you're viewing your own profile
	if ( !current_user_can( 'edit_users' ) || trs_is_my_profile() )
		return false; ?>

	<li id="trs-adminbar-adminoptions-menu">

		<a href=""><?php _e( 'Admin Options', 'trendr' ) ?></a>

		<ul>
			<?php if ( trs_is_active( 'xprofile' ) ) : ?>

				<li><a href="<?php trs_members_component_link( 'profile', 'edit' ); ?>"><?php printf( __( "Edit %s's Profile", 'trendr' ), esc_attr( $trs->displayed_user->fullname ) ) ?></a></li>

			<?php endif ?>

			<li><a href="<?php trs_members_component_link( 'profile', 'change-profile-photo' ); ?>"><?php printf( __( "Edit %s's Avatar", 'trendr' ), esc_attr( $trs->displayed_user->fullname ) ) ?></a></li>

			<?php if ( !trs_core_is_user_spammer( $trs->displayed_user->id ) ) : ?>

				<li><a href="<?php echo trm_nonce_url( $trs->displayed_user->domain . 'admin/mark-spammer/', 'mark-unmark-spammer' ) ?>" class="confirm"><?php printf( __( "Mark as Spammer", 'trendr' ), esc_attr( $trs->displayed_user->fullname ) ); ?></a></li>

			<?php else : ?>

				<li><a href="<?php echo trm_nonce_url( $trs->displayed_user->domain . 'admin/unmark-spammer/', 'mark-unmark-spammer' ) ?>" class="confirm"><?php _e( "Not a Spammer", 'trendr' ) ?></a></li>

			<?php endif; ?>

			<li><a href="<?php echo trm_nonce_url( $trs->displayed_user->domain . 'admin/delete-user/', 'delete-user' ) ?>" class="confirm"><?php printf( __( "Delete %s's Account", 'trendr' ), esc_attr( $trs->displayed_user->fullname ) ); ?></a></li>

			<?php do_action( 'trs_members_adminbar_admin_menu' ) ?>

		</ul>
	</li>

	<?php
}
add_action( 'trs_adminbar_menus', 'trs_members_adminbar_admin_menu', 20 );

?>