<?php
/**
 * Plugin editor network administration panel.
 *
 * @package Trnder
 * @subpackage Multisite
 * @since 3.1.0
 */

/** Load Trnder Administration Bootstrap */
require_once( './admin.php' );

if ( ! is_multisite() )
	trm_die( __( 'Multisite support is not enabled.' ) );

require( '../plugin-editor.php' );
