<?php
/**
 * Install plugin network administration panel.
 *
 * @package Trnder
 * @subpackage Multisite
 * @since 3.1.0
 */

if ( isset( $_GET['tab'] ) && ( 'plugin-information' == $_GET['tab'] ) )
	define( 'IFRAME_REQUEST', true );

/** Load Trnder Administration Bootstrap */
require_once( './admin.php' );

if ( ! is_multisite() )
	trm_die( __( 'Multisite support is not enabled.' ) );

require( '../plugin-install.php' );