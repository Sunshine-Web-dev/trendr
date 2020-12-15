<?php
/**
 * Redirects to the Atom feed
 * This file is deprecated and only exists for backwards compatibility
 *
 * @package Trnder
 */

require( './initiate.php' );
trm_redirect( get_bloginfo( 'atom_url' ), 301 );
exit;
?>
