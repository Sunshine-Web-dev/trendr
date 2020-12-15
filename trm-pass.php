<?php
/**
 * Creates the password cookie and redirects back to where the
 * visitor was before.
 *
 * @package Trnder
 */

/** Make sure that the Trnder bootstrap has run before continuing. */
require( dirname(__FILE__) . '/initiate.php');

if ( get_magic_quotes_gpc() )
	$_POST['post_password'] = stripslashes($_POST['post_password']);

// 10 days
setcookie('trm-postpass_' . COOKIEHASH, $_POST['post_password'], time() + 864000, COOKIEPATH);

trm_safe_redirect(trm_get_referer());
exit;
?>
