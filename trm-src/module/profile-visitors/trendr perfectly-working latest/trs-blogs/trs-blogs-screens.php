<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function trs_blogs_screen_my_blogs() {
	if ( !is_multisite() )
		return false;

	do_action( 'trs_blogs_screen_my_blogs' );

	trs_core_load_template( apply_filters( 'trs_blogs_template_my_blogs', 'members/single/home' ) );
}

function trs_blogs_screen_create_a_blog() {
	if ( !is_multisite() ||  !trs_is_blogs_component() || !trs_is_current_action( 'create' ) )
		return false;

	if ( !is_user_logged_in() || !trs_blog_signup_enabled() )
		return false;

	do_action( 'trs_blogs_screen_create_a_blog' );

	trs_core_load_template( apply_filters( 'trs_blogs_template_create_a_blog', 'blogs/create' ) );
}
add_action( 'trs_screens', 'trs_blogs_screen_create_a_blog', 3 );

function trs_blogs_screen_index() {
	if ( is_multisite() && trs_is_blogs_component() && !trs_current_action() ) {
		trs_update_is_directory( true, 'blogs' );

		do_action( 'trs_blogs_screen_index' );

		trs_core_load_template( apply_filters( 'trs_blogs_screen_index', 'blogs/index' ) );
	}
}
add_action( 'trs_screens', 'trs_blogs_screen_index', 2 );

?>