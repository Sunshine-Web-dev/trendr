<?php
/**
 * Redirects to the RDF feed
 * This file is deprecated and only exists for backwards compatibility
 *
 * @package Trnder
 */

require( './initiate.php' );
trm_redirect( get_bloginfo( 'rdf_url' ), 301 );
exit;
?>
