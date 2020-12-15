<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** Loaded ********************************************************************/

add_action( 'plugins_loaded', 'trs_loaded',  10 );

add_action( 'trs_loaded',      'trs_include', 2  );

add_action( 'trm',             'trs_actions', 3  );

add_action( 'trm',             'trs_screens', 4  );

/** Init **********************************************************************/

// Attach trs_init to WordPress init
add_action( 'init',       'trs_init'                    );

// Parse the URI and set globals
add_action( 'trs_init',    'trs_core_set_uri_globals', 2 );

// Setup component globals
add_action( 'trs_init',    'trs_setup_globals',        4 );

// Setup the navigation menu
add_action( 'trs_init',    'trs_setup_nav',            7 );

// Setup the navigation menu
add_action( 'admin_bar_menu',    'trs_setup_admin_bar'  );

// Setup the title
add_action( 'trs_init',    'trs_setup_title',          9 );

// Setup widgets
add_action( 'trs_loaded',  'trs_setup_widgets'           );

// Setup admin bar
add_action( 'trs_loaded',  'trs_core_load_admin_bar'     );

/** The hooks *****************************************************************/

/**
 * Include files on this action
 */
function trs_include() {
	do_action( 'trs_include' );
}

/**
 * Setup global variables and objects
 */
function trs_setup_globals() {
	do_action( 'trs_setup_globals' );
}

/**
 * Set navigation elements
 */
function trs_setup_nav() {
	do_action( 'trs_setup_nav' );
}

/**
 * Set up trendr implementation of the TRM admin bar
 */
function trs_setup_admin_bar() {
	if ( trs_use_trm_admin_bar() )
		do_action( 'trs_setup_admin_bar' );
}

/**
 * Set the page title
 */
function trs_setup_title() {
	do_action( 'trs_setup_title' );
}

/**
 * Register widgets
 */
function trs_setup_widgets() {
	do_action( 'trs_register_widgets' );
}

/**
 * Initlialize code
 */
function trs_init() {
	do_action( 'trs_init' );
}

/**
 * Attached to plugins_loaded
 */
function trs_loaded() {
	do_action( 'trs_loaded' );
}

/**
 * Attach potential template actions
 */
function trs_actions() {
	do_action( 'trs_actions' );
}

/**
 * Attach potential template screens
 */
function trs_screens() {
	do_action( 'trs_screens' );
}

?>