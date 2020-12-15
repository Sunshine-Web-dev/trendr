<?php
/**
 * Plugin Name: Profile Visitors
 * Author: Venu Gopal Chaladi
 * Author URI:http://dhrusya.com
 * Version:1.8
 * Plugin URI:http://dhrusya.com/products
 * Description: Show number of profile views count by other members and recent visitors.
 * License: GPL
 * Tested with Trnder 1.5+
 * Date: 15th October 2014
 * Updated : 17th October 2014
 */

//ini_set( 'display_errors', true );
//error_reporting( E_ALL );
// session_save_path("../") ;

if (!session_id()) {
    session_start();
}


function trs_profile_visitors_init() {
	require( dirname( __FILE__ ) . '/includes/trs_views_core.php' );
}
add_action( 'trs_init', 'trs_profile_visitors_init' );

	
	
function trs_profile_visitors_install() {
   global $trmdb;

   $table_name = $trmdb->prefix . "trs_profile_visitors";
	$sql="CREATE TABLE IF NOT EXISTS $table_name (
  	`id` int(10) NOT NULL AUTO_INCREMENT,
  	`userid` int(10) NOT NULL,
	`viewerid` int(10) NOT NULL,
  	`vdate` datetime NOT NULL,
	`vviews` int(10) NOT NULL DEFAULT '0',
  	PRIMARY KEY (`id`)
	) ;";
	require_once( ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/upgrade.php' );
   dbDelta($sql);
}


register_activation_hook(__FILE__,'trs_profile_visitors_install');


if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, 'trs_profile_visitors_uninstall');

function trs_profile_visitors_uninstall() {
   global $trmdb;
	$table_name = $trmdb->prefix . "trs_profile_visitors";

	$sql="DROP TABLE $table_name;";
   	$trmdb->query($sql);
}
