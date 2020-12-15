<?php
/**
 * Comment Moderation Administration Screen.
 *
 * Redirects to edit-comments.php?comment_status=moderated.
 *
 * @package Trnder
 * @subpackage Administration
 */
require_once('../initiate.php');
trm_redirect( admin_url('edit-comments.php?comment_status=moderated') );
exit;
?>
