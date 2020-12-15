<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * trs_like_button()
 *
 * Outputs the 'Like/Unlike' button.
 *
 */
function trs_like_button( $type = '' ) {

    /* Set the type if not already set, and check whether we are outputting the button on a blogpost or not. */
    if ( ! $type && ! is_single() ) {

        $type = 'activity';

    } 
    if ( $type == 'activity' || $type == 'activity_update' ) {

        // TODO change this to use hook
        trslike_activity_update_button();

    }
}

// Filters to display trendr Like button.
add_action( 'trs_activity_entry_meta' , 'trslike_activity_update_button' );
