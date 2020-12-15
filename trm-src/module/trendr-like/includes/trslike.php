<?php
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'TRS_LIKE_VERSION' ) ) {
    define( 'TRS_LIKE_VERSION' , '0.3.0' );
}

if ( ! defined( 'TRS_LIKE_DB_VERSION' ) ) {
    define( 'TRS_LIKE_DB_VERSION' , '43' );
}

if ( ! defined( 'TRSLIKE_PATH' ) ) {
    define( 'TRSLIKE_PATH' , plugin_dir_path( dirname( __FILE__ ) ) );
}

add_action('plugins_loaded', 'trs_like_load_textdomain');
function trs_like_load_textdomain() {
	load_plugin_textdomain( 'trendr-like' , false , TRSLIKE_PATH . '/languages/' );
}

/**
 * trs_like_get_text()
 *
 * Returns a custom text string from the database
 *
 */
function trs_like_get_text( $text = false , $type = 'custom' ) {
    //$settings = get_site_option( 'trs_like_settings' );
    //fixed php 7.2x warnning   
    $settings = $trs_like_settings ?: array();

    $text_strings = $settings['text_strings'];
    $string = $text_strings[$text];
    return $string[$type];
}

//if ( is_admin() ) {
   // require_once TRSLIKE_PATH . 'admin/admin.php';
//}
require_once TRSLIKE_PATH . 'includes/button-functions.php';
require_once TRSLIKE_PATH . 'includes/templates/activity-update.php';
//require_once TRSLIKE_PATH . 'includes/templates/activity-comment.php';
//require_once TRSLIKE_PATH . 'includes/templates/blog-post.php';
//require_once TRSLIKE_PATH . 'includes/templates/blog-comment.php';
require_once TRSLIKE_PATH . 'includes/install-functions.php';
require_once TRSLIKE_PATH . 'includes/activity-functions.php';
require_once TRSLIKE_PATH . 'includes/ajax.php';
require_once TRSLIKE_PATH . 'includes/like-functions.php';
require_once TRSLIKE_PATH . 'includes/scripts.php';
require_once TRSLIKE_PATH . 'includes/settings.php';
//require_once TRSLIKE_PATH . 'includes/blogpost.php';
