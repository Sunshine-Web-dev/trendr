<?php
/*******************************************************************************
 * Caching
 *
 * Caching functions handle the clearing of cached objects and pages on specific
 * actions throughout trendr.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function trs_blogs_clear_blog_object_cache( $blog_id, $user_id ) {
	trm_cache_delete( 'trs_blogs_of_user_' . $user_id, 'trs' );
	trm_cache_delete( 'trs_total_blogs_for_user_' . $user_id, 'trs' );
}

function trs_blogs_format_clear_blog_cache( $recorded_blog_obj ) {
	trs_blogs_clear_blog_object_cache( false, $recorded_blog_obj->user_id );
	trm_cache_delete( 'trs_total_blogs', 'trs' );
}

// List actions to clear object caches on
add_action( 'trs_blogs_remove_blog_for_user', 'trs_blogs_clear_blog_object_cache', 10, 2 );
add_action( 'trs_blogs_new_blog',             'trs_blogs_format_clear_blog_cache', 10, 2 );

// List actions to clear super cached pages on, if super cache is installed
add_action( 'trs_blogs_remove_data_for_blog', 'trs_core_clear_cache' );
add_action( 'trs_blogs_remove_comment',       'trs_core_clear_cache' );
add_action( 'trs_blogs_remove_post',          'trs_core_clear_cache' );
add_action( 'trs_blogs_remove_blog_for_user', 'trs_core_clear_cache' );
add_action( 'trs_blogs_remove_blog',          'trs_core_clear_cache' );
add_action( 'trs_blogs_new_blog_comment',     'trs_core_clear_cache' );
add_action( 'trs_blogs_new_blog_post',        'trs_core_clear_cache' );
add_action( 'trs_blogs_new_blog',             'trs_core_clear_cache' );
add_action( 'trs_blogs_remove_data',          'trs_core_clear_cache' );

?>