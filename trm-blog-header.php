<?php
/**
 * Loads the WordPress environment and template.
 *
 * @package WordPress
 */

if ( !isset($trm_did_header) ) {

	$trm_did_header = true;

	require_once( dirname(__FILE__) . '/initiate.php' );

	trm();

	require_once( ABSPATH . TRMINC . '/template-loader.php' );

}
