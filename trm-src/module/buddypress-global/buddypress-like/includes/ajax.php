<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * trs_like_process_ajax()
 *
 * Runs the relevant function depending on what AJAX call has been made.
 *
 */
function trs_like_process_ajax() {

    // ensuring $id only contains an integer
    $id = preg_replace( "/\D/" , "" , $_POST['id'] );

    if ( $_POST['type'] == 'activity_update like' ) {
        trs_like_add_user_like( $id , 'activity_update' );
    }

    die();

}

add_action( 'trm_ajax_activity_like' , 'trs_like_process_ajax' );

/**
 * trs_like_ajax_get_likes()
 *
 */
function trs_like_ajax_get_likes() {

  // ensuring $id only contains an integer
  $id = preg_replace( "/\D/" , "" , $_POST['id'] );

  trs_like_get_some_likes( $id , 'activity_update' );

  die();
}
add_action( 'trm_ajax_trslike_get_likes', 'trs_like_ajax_get_likes', 10, 1);
