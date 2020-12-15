<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * trs_like_get_settings()
 *
 * Returns settings from the database
 *
 */
function trs_like_get_settings( $option = false ) {

    $settings = get_site_option( 'trs_like_settings' );

    if ( ! $option ) {
        return $settings;
    } else {
        return $settings[$option];
    }
}


add_action( 'init', 'trs_like_remove_favourites' );

function trs_like_remove_favourites() {
    if( trs_like_get_settings('remove_fav_button') == 1 ) {

        add_filter( 'trs_activity_can_favorite', '__return_false', 1 );
        add_filter( 'trs_get_total_favorite_count_for_user', '__return_false', 1 );
        trs_core_remove_nav_item('favorites');

        function trs_like_admin_bar_render_remove_favorites() {
            global $trm_admin_bar;
            $trm_admin_bar->remove_menu('my-account-activity-favorites');
        }
        add_action( 'trm_before_admin_bar_render' , 'trs_like_admin_bar_render_remove_favorites' );
    }
}
