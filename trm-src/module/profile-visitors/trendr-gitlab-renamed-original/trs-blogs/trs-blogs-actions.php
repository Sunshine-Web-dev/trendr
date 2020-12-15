<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function trs_blogs_redirect_to_random_blog() {
	global $trs, $trmdb;

	if ( trs_is_blogs_component() && isset( $_GET['random-blog'] ) ) {
		$blog = trs_blogs_get_random_blogs( 1, 1 );

		trs_core_redirect( get_site_url( $blog['blogs'][0]->blog_id ) );
	}
}
add_action( 'trs_actions', 'trs_blogs_redirect_to_random_blog' );

?>