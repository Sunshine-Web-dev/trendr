<?php
/**
 * Used to be the page which displayed the registration form.
 *
 * This file is no longer used in Trnder and is
 * deprecated.
 *
 * @package Trnder
 * @deprecated Use trm_register() to create a registration link instead
 */

require('./initiate.php');
trm_redirect( site_url('enter.php?action=register') );
exit;
?>
