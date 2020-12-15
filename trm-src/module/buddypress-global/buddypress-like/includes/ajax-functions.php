<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * trs_like_process_ajax()
 *
 * Runs the relevant function depending on what Ajax call has been made.
 *
 */
function trs_like_process_ajax() {
    global $trs;

    $id = preg_replace( "/\D/" , "" , $_POST['id'] );

    if ( $_POST['type'] == 'button like' ) {
        trs_like_add_user_like( $id , 'activity' );
        add_action( 'view_who_likes' , 'trs_like_get_some_likes' );
    }

    if ( $_POST['type'] == 'button unlike' ) {
        trs_like_remove_user_like( $id , 'activity' );
    }

    if ( $_POST['type'] == 'acomment-reply main like' ) {
        trs_like_add_user_like( $id , 'activity' );
    }

    if ( $_POST['type'] == 'acomment-reply main unlike' ) {
        trs_like_remove_user_like( $id , 'activity' );
    }

    if ( $_POST['type'] == 'button view-likes' ) {
        trs_like_get_likes( $id , 'activity' );
    }

    if ( $_POST['type'] == 'button like_blogpost' ) {
        trs_like_add_user_like( $id , 'blogpost' );
    }

    if ( $_POST['type'] == 'button unlike_blogpost' ) {
        trs_like_remove_user_like( $id , 'blogpost' );
    }

    if ( $_POST['type'] == 'acomment-reply main view-likes' ) {
        trs_like_get_likes( $id , 'activity' );
    }

    die();
}

add_action( 'trm_ajax_activity_like' , 'trs_like_process_ajax' );
