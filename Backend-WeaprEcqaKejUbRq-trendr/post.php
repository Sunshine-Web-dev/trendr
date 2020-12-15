<?php
/**
 * Edit post administration panel.
 *
 * Manage Post actions: post, edit, delete, etc.
 *
 * @package Trnder
 * @subpackage Administration
 */

/** Trnder Administration Bootstrap */
require_once('./admin.php');

$parent_file = 'edit.php';
$submenu_file = 'edit.php';

trm_reset_vars(array('action', 'safe_mode', 'withcomments', 'posts', 'content', 'edited_post_title', 'comment_error', 'profile', 'trackback_url', 'excerpt', 'showcomments', 'commentstart', 'commentend', 'commentorder'));

if ( isset($_GET['post']) )
	$post_id = (int) $_GET['post'];
elseif ( isset($_POST['post_ID']) )
	$post_id = (int) $_POST['post_ID'];
else
	$post_id = 0;
$post_ID = $post_id;
$post = null;
$post_type_object = null;
$post_type = null;
if ( $post_id ) {
	$post = get_post($post_id);
	if ( $post ) {
		$post_type_object = get_post_type_object($post->post_type);
		if ( $post_type_object ) {
			$post_type = $post->post_type;
			$current_screen->post_type = $post->post_type;
			$current_screen->id = $current_screen->post_type;
		}
	}
} elseif ( isset($_POST['post_type']) ) {
	$post_type_object = get_post_type_object($_POST['post_type']);
	if ( $post_type_object ) {
		$post_type = $post_type_object->name;
		$current_screen->post_type = $post_type;
		$current_screen->id = $current_screen->post_type;
	}
}

/**
 * Redirect to previous page.
 *
 * @param int $post_id Optional. Post ID.
 */
function redirect_post($post_id = '') {
	if ( isset($_POST['save']) || isset($_POST['publish']) ) {
		$status = get_post_status( $post_id );

		if ( isset( $_POST['publish'] ) ) {
			switch ( $status ) {
				case 'pending':
					$message = 8;
					break;
				case 'future':
					$message = 9;
					break;
				default:
					$message = 6;
			}
		} else {
				$message = 'draft' == $status ? 10 : 1;
		}

		$location = add_query_arg( 'message', $message, get_edit_post_link( $post_id, 'url' ) );
	} elseif ( isset($_POST['addmeta']) && $_POST['addmeta'] ) {
		$location = add_query_arg( 'message', 2, trm_get_referer() );
		$location = explode('#', $location);
		$location = $location[0] . '#postcustom';
	} elseif ( isset($_POST['deletemeta']) && $_POST['deletemeta'] ) {
		$location = add_query_arg( 'message', 3, trm_get_referer() );
		$location = explode('#', $location);
		$location = $location[0] . '#postcustom';
	} elseif ( 'post-quickpress-save-cont' == $_POST['action'] ) {
		$location = "post.php?action=edit&post=$post_id&message=7";
	} else {
		$location = add_query_arg( 'message', 4, get_edit_post_link( $post_id, 'url' ) );
	}

	trm_redirect( apply_filters( 'redirect_post_location', $location, $post_id ) );
	exit;
}

if ( isset( $_POST['deletepost'] ) )
	$action = 'delete';
elseif ( isset($_POST['trm-preview']) && 'dopreview' == $_POST['trm-preview'] )
	$action = 'preview';

$sendback = trm_get_referer();
if ( strpos($sendback, 'post.php') !== false || strpos($sendback, 'post-new.php') !== false ) {
	$sendback = admin_url('edit.php');
	$sendback .= ( !empty( $post_type ) ) ? '?post_type=' . $post_type : '';
} else {
	$sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), $sendback );
}

switch($action) {
case 'postajaxpost':
case 'post':
case 'post-quickpress-publish':
case 'post-quickpress-save':
	check_admin_referer('add-' . $post_type);

	if ( 'post-quickpress-publish' == $action )
		$_POST['publish'] = 'publish'; // tell write_post() to publish

	if ( 'post-quickpress-publish' == $action || 'post-quickpress-save' == $action ) {
		$_POST['comment_status'] = get_option('default_comment_status');
		$_POST['ping_status'] = get_option('default_ping_status');
	}

	if ( !empty( $_POST['quickpress_post_ID'] ) ) {
		$_POST['post_ID'] = (int) $_POST['quickpress_post_ID'];
		$post_id = edit_post();
	} else {
		$post_id = 'postajaxpost' == $action ? edit_post() : write_post();
	}

	if ( 0 === strpos( $action, 'post-quickpress' ) ) {
		$_POST['post_ID'] = $post_id;
		// output the quickpress dashboard widget
		require_once(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/dashboard.php');
		trm_dashboard_quick_press();
		exit;
	}

	redirect_post($post_id);
	exit();
	break;

case 'edit':
	$editing = true;

	if ( empty( $post_id ) ) {
		trm_redirect( admin_url('post.php') );
		exit();
	}

	$p = $post_id;

	if ( empty($post->ID) )
		trm_die( __('You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?') );

	if ( !current_user_can($post_type_object->cap->edit_post, $post_id) )
		trm_die( __('You are not allowed to edit this item.') );

	if ( 'trash' == $post->post_status )
		trm_die( __('You can&#8217;t edit this item because it is in the Trash. Please restore it and try again.') );

	if ( null == $post_type_object )
		trm_die( __('Unknown post type.') );

	$post_type = $post->post_type;
	if ( 'post' == $post_type ) {
		$parent_file = "edit.php";
		$submenu_file = "edit.php";
		$post_new_file = "post-new.php";
	} else {
		if ( isset( $post_type_object ) && $post_type_object->show_in_menu && $post_type_object->show_in_menu !== true )
			$parent_file = $post_type_object->show_in_menu;
		else
			$parent_file = "edit.php?post_type=$post_type";
		$submenu_file = "edit.php?post_type=$post_type";
		$post_new_file = "post-new.php?post_type=$post_type";
	}

	if ( $last = trm_check_post_lock( $post->ID ) ) {
		add_action('admin_notices', '_admin_notice_post_locked' );
	} else {
		trm_set_post_lock( $post->ID );
		trm_enqueue_script('autosave');
	}

	$title = $post_type_object->labels->edit_item;
	$post = get_post_to_edit($post_id);

	if ( post_type_supports($post_type, 'comments') ) {
		trm_enqueue_script('admin-comments');
		enqueue_comment_hotkeys_js();
	}

	include('./edit-form-advanced.php');

	break;

case 'editattachment':
	check_admin_referer('update-attachment_' . $post_id);

	// Don't let these be changed
	unset($_POST['guid']);
	$_POST['post_type'] = 'attachment';

	// Update the thumbnail filename
	$newmeta = trm_get_attachment_metadata( $post_id, true );
	$newmeta['thumb'] = $_POST['thumb'];

	trm_update_attachment_metadata( $post_id, $newmeta );

case 'editpost':
	check_admin_referer('update-' . $post_type . '_' . $post_id);

	$post_id = edit_post();

	redirect_post($post_id); // Send user on their way while we keep working

	exit();
	break;

case 'trash':
	check_admin_referer('trash-' . $post_type . '_' . $post_id);

	$post = get_post($post_id);

	if ( !current_user_can($post_type_object->cap->delete_post, $post_id) )
		trm_die( __('You are not allowed to move this item to the Trash.') );

	if ( ! trm_trash_post($post_id) )
		trm_die( __('Error in moving to Trash.') );

	trm_redirect( add_query_arg( array('trashed' => 1, 'ids' => $post_id), $sendback ) );
	exit();
	break;

case 'untrash':
	check_admin_referer('untrash-' . $post_type . '_' . $post_id);

	if ( !current_user_can($post_type_object->cap->delete_post, $post_id) )
		trm_die( __('You are not allowed to move this item out of the Trash.') );

	if ( ! trm_untrash_post($post_id) )
		trm_die( __('Error in restoring from Trash.') );

	trm_redirect( add_query_arg('untrashed', 1, $sendback) );
	exit();
	break;

case 'delete':
	check_admin_referer('delete-' . $post_type . '_' . $post_id);

	if ( !current_user_can($post_type_object->cap->delete_post, $post_id) )
		trm_die( __('You are not allowed to delete this item.') );

	$force = !EMPTY_TRASH_DAYS;
	if ( $post->post_type == 'attachment' ) {
		$force = ( $force || !MEDIA_TRASH );
		if ( ! trm_delete_attachment($post_id, $force) )
			trm_die( __('Error in deleting.') );
	} else {
		if ( !trm_delete_post($post_id, $force) )
			trm_die( __('Error in deleting.') );
	}

	trm_redirect( add_query_arg('deleted', 1, $sendback) );
	exit();
	break;

case 'preview':
	check_admin_referer( 'autosave', 'autosavenonce' );

	$url = post_preview();

	trm_redirect($url);
	exit();
	break;

default:
	trm_redirect( admin_url('edit.php') );
	exit();
	break;
} // end switch
include('./admin-footer.php');
?>
