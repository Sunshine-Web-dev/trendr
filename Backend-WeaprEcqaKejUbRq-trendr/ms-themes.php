<?php
/**
 * Multisite themes administration panel.
 *
 * @package Trnder
 * @subpackage Multisite
 * @since 3.0.0
 */

require_once( './admin.php' );

trm_redirect( network_admin_url('themes.php') );
exit;
?>
