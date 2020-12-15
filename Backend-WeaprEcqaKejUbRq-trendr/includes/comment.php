<?php
/**
 * Trnder Comment Administration API.
 *
 * @package Trnder
 * @subpackage Administration
 */

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.0.0
 * @uses $trmdb
 *
 * @param string $comment_author Author of the comment
 * @param string $comment_date Date of the comment
 * @return mixed Comment ID on success.
 */
function comment_exists($comment_author, $comment_date) {
	global $trmdb;

	$comment_author = stripslashes($comment_author);
	$comment_date = stripslashes($comment_date);

	return $trmdb->get_var( $trmdb->prepare("SELECT comment_post_ID FROM $trmdb->comments
			WHERE comment_author = %s AND comment_date = %s", $comment_author, $comment_date) );
}

/**
 * Update a comment with values provided in $_POST.
 *
 * @since 2.0.0
 */
function edit_comment() {

	if ( ! current_user_can( 'edit_comment', (int) $_POST['comment_ID'] ) )
		trm_die ( __( 'You are not allowed to edit comments on this post.' ) );

	$_POST['comment_author'] = $_POST['newcomment_author'];
	$_POST['comment_author_email'] = $_POST['newcomment_author_email'];
	$_POST['comment_author_url'] = $_POST['newcomment_author_url'];
	$_POST['comment_approved'] = $_POST['comment_status'];
	$_POST['comment_content'] = $_POST['content'];
	$_POST['comment_ID'] = (int) $_POST['comment_ID'];

	foreach ( array ('aa', 'mm', 'jj', 'hh', 'mn') as $timeunit ) {
		if ( !empty( $_POST['hidden_' . $timeunit] ) && $_POST['hidden_' . $timeunit] != $_POST[$timeunit] ) {
			$_POST['edit_date'] = '1';
			break;
		}
	}

	if ( !empty ( $_POST['edit_date'] ) ) {
		$aa = $_POST['aa'];
		$mm = $_POST['mm'];
		$jj = $_POST['jj'];
		$hh = $_POST['hh'];
		$mn = $_POST['mn'];
		$ss = $_POST['ss'];
		$jj = ($jj > 31 ) ? 31 : $jj;
		$hh = ($hh > 23 ) ? $hh -24 : $hh;
		$mn = ($mn > 59 ) ? $mn -60 : $mn;
		$ss = ($ss > 59 ) ? $ss -60 : $ss;
		$_POST['comment_date'] = "$aa-$mm-$jj $hh:$mn:$ss";
	}

	trm_update_comment( $_POST );
}

/**
 * {@internal Missing Short Description}}
 *
 * @since 2.0.0
 *
 * @param int $id ID of comment to retrieve
 * @return bool|object Comment if found. False on failure.
 */
function get_comment_to_edit( $id ) {
	if ( !$comment = get_comment($id) )
		return false;

	$comment->comment_ID = (int) $comment->comment_ID;
	$comment->comment_post_ID = (int) $comment->comment_post_ID;

	$comment->comment_content = format_to_edit( $comment->comment_content );
	$comment->comment_content = apply_filters( 'comment_edit_pre', $comment->comment_content);

	$comment->comment_author = format_to_edit( $comment->comment_author );
	$comment->comment_author_email = format_to_edit( $comment->comment_author_email );
	$comment->comment_author_url = format_to_edit( $comment->comment_author_url );
	$comment->comment_author_url = esc_url($comment->comment_author_url);

	return $comment;
}

/**
 * Get the number of pending comments on a post or posts
 *
 * @since 2.3.0
 * @uses $trmdb
 *
 * @param int|array $post_id Either a single Post ID or an array of Post IDs
 * @return int|array Either a single Posts pending comments as an int or an array of ints keyed on the Post IDs
 */
function get_pending_comments_num( $post_id ) {
	global $trmdb;

	$single = false;
	if ( !is_array($post_id) ) {
		$post_id_array = (array) $post_id;
		$single = true;
	} else {
		$post_id_array = $post_id;
	}
	$post_id_array = array_map('intval', $post_id_array);
	$post_id_in = "'" . implode("', '", $post_id_array) . "'";

	$pending = $trmdb->get_results( "SELECT comment_post_ID, COUNT(comment_ID) as num_comments FROM $trmdb->comments WHERE comment_post_ID IN ( $post_id_in ) AND comment_approved = '0' GROUP BY comment_post_ID", ARRAY_A );

	if ( $single ) {
		if ( empty($pending) )
			return 0;
		else
			return absint($pending[0]['num_comments']);
	}

	$pending_keyed = array();

	// Default to zero pending for all posts in request
	foreach ( $post_id_array as $id )
		$pending_keyed[$id] = 0;

	if ( !empty($pending) )
		foreach ( $pending as $pend )
			$pending_keyed[$pend['comment_post_ID']] = absint($pend['num_comments']);

	return $pending_keyed;
}

/**
 * Add portraits to relevant places in admin, or try to.
 *
 * @since 2.5.0
 * @uses $comment
 *
 * @param string $name User name.
 * @return string Avatar with Admin name.
 */
function floated_admin_portrait( $name ) {
	global $comment;
	$portrait = get_portrait( $comment, 32 );
	return "$portrait $name";
}

function enqueue_comment_hotkeys_js() {
	if ( 'true' == get_user_option( 'comment_shortcuts' ) )
		trm_enqueue_script( 'jquery-table-hotkeys' );
}
?>