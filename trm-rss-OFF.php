<?php
/**
 * Redirects to the RSS feed
 * This file is deprecated and only exists for backwards compatibility
 *
 * @package Trnder
 */

require( './initiate.php' );
trm_redirect( get_bloginfo( 'rss_url' ), 301 );
exit;
?>
