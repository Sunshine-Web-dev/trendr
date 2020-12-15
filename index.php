<?php
/**
 * Front to the Trnder application. This file doesn't do anything, but loads
 * trm-header.php which does and tells Trnder to load the theme.
 *
 * @package Trnder
 */

/**
 * Tells Trnder to load the Trnder theme and output it.
 *
 * @var bool
 */
define('TRM_USE_THEMES', true);

/** Loads the Trnder Environment and Template */
require('./trm-header.php');
?>