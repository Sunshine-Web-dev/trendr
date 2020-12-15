<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * trs_like_install()
 *
 * Installs or upgrades the database content
 */
function trs_like_install() {
    // build a way to easily change this to other predefined words, eg love, thumbs up
       

    update_site_option( 'trs_like_db_version' , TRS_LIKE_DB_VERSION );
    update_site_option( 'trs_like_settings' , $settings );

    add_action( 'admin_notices' , 'trs_like_updated_notice' );
}

/**
 * trs_like_check_installed()
 *
 * Checks to see if the DB tables exist or if you are running an old version
 * of the component. If it matches, it will run the installation function.
 * This means we don't have to deactivate and then reactivate.
 *
 */
function trs_like_check_installed() {
    global $trmdb;

    if ( ! is_super_admin() ) {
        return false;
    }

    if ( ! get_site_option( 'trs_like_settings' ) || get_site_option( 'trs-like-db-version' ) ) {
        trs_like_install();
    }

    if ( get_site_option( 'trs_like_db_version' ) < TRS_LIKE_DB_VERSION ) {
        trs_like_install();
    }
}

add_action( 'admin_menu' , 'trs_like_check_installed' );


/*
 * The notice we show if the plugin is updated.
 */

function trs_like_updated_notice() {

    if ( ! is_super_admin() ) {
        return false;
    } else {
        echo '<div id="message" class="updated fade"><p style="line-height: 150%">';
        printf( __( '<strong>trendr Like</strong> has been successfully updated to version %s.' , 'trendr-like' ) , TRS_LIKE_VERSION );
        echo '</p></div>';
    }
}


/*
 * The notice we show when the plugin is installed.
 */

function trs_like_install_trendr_notice() {
    echo '<div id="message" class="error fade"><p style="line-height: 150%">';
    _e( '<strong>trendr Like</strong></a> requires the trendr plugin to work. Please <a href="http://trendr.org">install trendr</a> first, or <a href="plugins.php">deactivate trendr Like</a>.' , 'trendr-like' );
    echo '</p></div>';
}
