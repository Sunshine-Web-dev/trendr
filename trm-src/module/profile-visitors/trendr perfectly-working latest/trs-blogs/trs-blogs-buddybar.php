<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// *** "My Blogs" Menu ********
function trs_adminbar_blogs_menu() {
	global $trs;

	if ( !is_user_logged_in() || !trs_is_active( 'blogs' ) )
		return false;

	if ( !is_multisite() )
		return false;

	if ( !$blogs = trm_cache_get( 'trs_blogs_of_user_' . $trs->loggedin_user->id . '_inc_hidden', 'trs' ) ) {
		$blogs = trs_blogs_get_blogs_for_user( $trs->loggedin_user->id, true );
		trm_cache_set( 'trs_blogs_of_user_' . $trs->loggedin_user->id . '_inc_hidden', $blogs, 'trs' );
	}

	$counter = 0;
	if ( is_array( $blogs['blogs'] ) && (int)$blogs['count'] ) {

		echo '<li id="trs-adminbar-blogs-menu"><a href="' . trailingslashit( $trs->loggedin_user->domain . trs_get_blogs_slug() ) . '">';

		_e( 'My Sites', 'trendr' );

		echo '</a>';
		echo '<ul>';

		foreach ( (array)$blogs['blogs'] as $blog ) {
			$alt      = ( 0 == $counter % 2 ) ? ' class="alt"' : '';
			$site_url = esc_attr( $blog->siteurl );

			echo '<li' . $alt . '>';
			echo '<a href="' . $site_url . '">' . esc_html( $blog->name ) . '</a>';
			echo '<ul>';
			echo '<li class="alt"><a href="' . $site_url . 'Backend-WeaprEcqaKejUbRq-trendr/">' . __( 'Dashboard', 'trendr' ) . '</a></li>';
			echo '<li><a href="' . $site_url . 'Backend-WeaprEcqaKejUbRq-trendr/post-new.php">' . __( 'New Post', 'trendr' ) . '</a></li>';
			echo '<li class="alt"><a href="' . $site_url . 'Backend-WeaprEcqaKejUbRq-trendr/edit.php">' . __( 'Manage Posts', 'trendr' ) . '</a></li>';
			echo '<li><a href="' . $site_url . 'Backend-WeaprEcqaKejUbRq-trendr/edit-comments.php">' . __( 'Manage Comments', 'trendr' ) . '</a></li>';
			echo '</ul>';

			do_action( 'trs_adminbar_blog_items', $blog );

			echo '</li>';
			$counter++;
		}

		$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : '';

		if ( trs_blog_signup_enabled() ) {
			echo '<li' . $alt . '>';
			echo '<a href="' . trs_get_root_domain() . '/' . trs_get_blogs_root_slug() . '/create/">' . __( 'Create a Site!', 'trendr' ) . '</a>';
			echo '</li>';
		}

		echo '</ul>';
		echo '</li>';
	}
}
add_action( 'trs_adminbar_menus', 'trs_adminbar_blogs_menu', 6 );

?>