<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * trs_like_enqueue_scripts()
 *
 * Includes the terms required by plugins Javascript.
 *
 */
function trs_like_enqueue_scripts() {

    trm_register_script( 'trslike', plugins_url( '/assets/js/trs-like.js', dirname( __FILE__ ) ), array( 'jquery' ), TRS_LIKE_VERSION );

    if ( ! is_admin() ) {

        trm_enqueue_script( 'trslike' );

        trm_localize_script( 'trslike', 'trslikeTerms', array(
                'like'           => trs_like_get_text( 'like' ),
                'unlike'         => trs_like_get_text('unlike'),
                'like_message'   => trs_like_get_text( 'like_this_item' ),
                'unlike_message' => trs_like_get_text( 'unlike_this_item' ),
                'you_like_this'  => trs_like_get_text( 'get_likes_only_liker' ),
                //'fav_remove'     => trs_like_get_settings( 'remove_fav_button' ) == 1 ? '1' : '0'
            )
        );
    }
}
add_action( 'trm_enqueue_scripts' , 'trs_like_enqueue_scripts' );
