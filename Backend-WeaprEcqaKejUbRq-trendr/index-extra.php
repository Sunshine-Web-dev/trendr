<?php
/**
 * Handle default dashboard widgets options AJAX.
 *
 * @package Trnder
 * @subpackage Administration
 */

define('DOING_AJAX', true);

/** Load Trnder Bootstrap */
require_once( './admin.php' );

/** Load Trnder Administration Dashboard API */
require(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/dashboard.php' );

@header( 'Content-Type: ' . get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' ) );
send_nosniff_header();

switch ( $_GET['jax'] ) {

case 'dashboard_incoming_links' :
	trm_dashboard_incoming_links();
	break;

case 'dashboard_primary' :
	trm_dashboard_primary();
	break;

case 'dashboard_secondary' :
	trm_dashboard_secondary();
	break;

case 'dashboard_plugins' :
	trm_dashboard_plugins();
	break;

}

?>