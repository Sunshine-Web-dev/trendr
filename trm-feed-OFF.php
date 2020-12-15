<?php
/**
 * Redirects to the default feed
 * This file is deprecated and only exists for backwards compatibility
 *
 * @package Trnder
 */

require( './initiate.php' );
trm_redirect( get_bloginfo( get_default_feed() . '_url' ), 301 );
exit;
?>
