<?php
/**
 * Handles Comment Post to WordPress and prevents duplicate comment posting.
 *
 * @package WordPress
 */

if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
	header('Allow: POST');
	header('HTTP/1.1 405 Method Not Allowed');
	header('Content-Type: text/plain');
	exit;
}

/** Sets up the WordPress Environment. */
require( dirname(__FILE__) . '/initiate.php' );

nocache_headers();

$comment_post_ID = isset($_POST['comment_post_ID']) ? (int) $_POST['comment_post_ID'] : 0;

$post = get_post($comment_post_ID);

if ( empty($post->comment_status) ) {
	do_action('comment_id_not_found', $comment_post_ID);
	exit;
}

// get_post_status() will get the parent status for attachments.
$status = get_post_status($post);

$status_obj = get_post_status_object($status);

if ( !comments_open($comment_post_ID) ) {
	do_action('comment_closed', $comment_post_ID);
	trm_die( __('Sorry, comments are closed for this item.') );
} elseif ( 'trash' == $status ) {
	do_action('comment_on_trash', $comment_post_ID);
	exit;
} elseif ( !$status_obj->public && !$status_obj->private ) {
	do_action('comment_on_draft', $comment_post_ID);
	exit;
} elseif ( post_password_required($comment_post_ID) ) {
	do_action('comment_on_password_protected', $comment_post_ID);
	exit;
} else {
	do_action('pre_comment_on_post', $comment_post_ID);
}

$comment_author       = ( isset($_POST['author']) )  ? trim(strip_tags($_POST['author'])) : null;
$comment_author_email = ( isset($_POST['email']) )   ? trim($_POST['email']) : null;
$comment_author_url   = ( isset($_POST['url']) )     ? trim($_POST['url']) : null;
$comment_content      = ( isset($_POST['comment']) ) ? trim($_POST['comment']) : null;

// If the user is logged in
$user = trm_get_current_user();
if ( $user->exists() ) {
	if ( empty( $user->display_name ) )
		$user->display_name=$user->user_login;
	$comment_author       = trm_slash( $user->display_name );
	$comment_author_email = trm_slash( $user->user_email );
	$comment_author_url   = trm_slash( $user->user_url );
	if ( current_user_can('unfiltered_html') ) {
		if ( trm_create_nonce('unfiltered-html-comment_' . $comment_post_ID) != $_POST['_trm_unfiltered_html_comment'] ) {
			kses_remove_filters(); // start with a clean slate
			kses_init_filters(); // set up the filters
		}
	}
} else {
	if ( get_option('comment_registration') || 'private' == $status )
		trm_die( __('Sorry, you must be logged in to post a comment.') );
}

$comment_type = '';

if ( get_option('require_name_email') && !$user->exists() ) {
	if ( 6 > strlen($comment_author_email) || '' == $comment_author )
		trm_die( __('<strong>ERROR</strong>: please fill the required fields (name, email).') );
	elseif ( !is_email($comment_author_email))
		trm_die( __('<strong>ERROR</strong>: please enter a valid email address.') );
}

if ( '' == $comment_content )
	trm_die( __('<strong>ERROR</strong>: please type a comment.') );

$comment_parent = isset($_POST['comment_parent']) ? absint($_POST['comment_parent']) : 0;

$commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');

$comment_id = trm_new_comment( $commentdata );

$comment = get_comment($comment_id);
do_action('set_comment_cookies', $comment, $user);

$location = empty($_POST['redirect_to']) ? get_comment_link($comment_id) : $_POST['redirect_to'] . '#comment-' . $comment_id;
$location = apply_filters('comment_post_redirect', $location, $comment);

trm_safe_redirect( $location );
exit;