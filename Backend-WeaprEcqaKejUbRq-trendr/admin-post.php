<?php
/**
 * Trnder Administration Generic POST Handler.
 *
 * @package Trnder
 * @subpackage Administration
 */

/** We are located in Trnder Administration Screens */
define('TRM_ADMIN', true);

if ( defined('ABSPATH') )
	require_once(ABSPATH . 'initiate.php');
else
	require_once('../initiate.php');

require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/admin.php');

nocache_headers();

do_action('admin_init');

$action = 'admin_post';

if ( !trm_validate_auth_cookie() )
	$action .= '_nopriv';

if ( !empty($_REQUEST['action']) )
	$action .= '_' . $_REQUEST['action'];

do_action($action);

?>