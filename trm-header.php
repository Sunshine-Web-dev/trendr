<?php
/**
 * Loads the Trnder environment and template.
 *
 * @package Trnder
 */

if ( !isset($trm_did_header) ) {

	$trm_did_header = true;

	require_once( dirname(__FILE__) . '/initiate.php' );

trm();
if (is_feed() ) {
  die;
}

	require_once( ABSPATH . TRMINC . '/template-loader.php' );

}

?>