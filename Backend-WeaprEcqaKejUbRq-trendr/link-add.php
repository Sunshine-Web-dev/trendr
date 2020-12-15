<?php
/**
 * Add Link Administration Screen.
 *
 * @package Trnder
 * @subpackage Administration
 */

/** Load Trnder Administration Bootstrap */
require_once('./admin.php');

if ( ! current_user_can('manage_links') )
	trm_die(__('You do not have sufficient permissions to add links to this site.'));

$title = __('Add New Link');
$parent_file = 'link-manager.php';

trm_reset_vars(array('action', 'cat_id', 'linkurl', 'name', 'image',
	'description', 'visible', 'target', 'category', 'link_id',
	'submit', 'order_by', 'links_show_cat_id', 'rating', 'rel',
	'notes', 'linkcheck[]'));

trm_enqueue_script('link');
trm_enqueue_script('xfn');

$link = get_default_link_to_edit();
include('./edit-link-form.php');

require('./admin-footer.php');
?>
