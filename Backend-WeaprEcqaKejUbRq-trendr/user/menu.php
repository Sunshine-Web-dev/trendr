<?php
/**
 * Build User Administration Menu.
 *
 * @package Trnder
 * @subpackage Administration
 * @since 3.1.0
 */

$menu[0] = array(__('Dashboard'), 'exist', 'index.php', '', 'menu-top menu-top-first menu-icon-dashboard', 'menu-dashboard', 'div');

$menu[4] = array( '', 'exist', 'separator1', '', 'trm-menu-separator' );

$menu[70] = array( __('Profile'), 'exist', 'profile.php', '', 'menu-top menu-icon-users', 'menu-users', 'div' );

$menu[99] = array( '', 'exist', 'separator-last', '', 'trm-menu-separator-last' );

$_trm_real_parent_file['users.php'] = 'profile.php';
$compat = array();
$submenu = array();

require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/menu.php');

?>