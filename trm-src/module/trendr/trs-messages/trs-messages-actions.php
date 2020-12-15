<?php
/*******************************************************************************
 * Action functions are exactly the same as screen functions, however they do not
 * have a template screen associated with them. Usually they will send the user
 * back to the default screen after execution.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function messages_action_view_message() {
	global $thread_id, $trs;

	if ( !trs_is_messages_component() || !trs_is_current_action( 'view' ) )
		return false;

	$thread_id = (int)trs_action_variable( 0 );

	if ( !$thread_id || !messages_is_valid_thread( $thread_id ) || ( !messages_check_thread_access( $thread_id ) && !is_super_admin() ) )
		trs_core_redirect( trs_displayed_user_domain() . trs_get_messages_slug() );

	// Check if a new reply has been submitted
	if ( isset( $_POST['send'] ) ) {

		// Check the nonce
		check_admin_referer( 'messages_send_message', 'send_message_nonce' );

		// Send the reply
		if ( messages_new_message( array( 'thread_id' => $thread_id, 'subject' => $_POST['subject'], 'content' => $_POST['content'] ) ) )
			trs_core_add_message( __( 'Your reply was sent successfully', 'trendr' ) );
		else
			trs_core_add_message( __( 'There was a problem sending your reply, please try again', 'trendr' ), 'error' );

		trs_core_redirect( trs_displayed_user_domain() . trs_get_messages_slug() . '/view/' . $thread_id . '/' );
	}

	// Mark message read
	messages_mark_thread_read( $thread_id );

	// Decrease the unread count in the nav before it's rendered
	$name = sprintf( __( 'Messages <span>%s</span>', 'trendr' ), trs_get_total_unread_messages_count() );

	$trs->trs_nav[$trs->messages->slug]['name'] = $name;

	do_action( 'messages_action_view_message' );

	trs_core_new_subnav_item( array(
		'name'            => sprintf( __( 'From: %s', 'trendr' ), TRS_Messages_Thread::get_last_sender( $thread_id ) ),
		'slug'            => 'view',
		'parent_url'      => trailingslashit( trs_displayed_user_domain() . trs_get_messages_slug() ),
		'parent_slug'     => trs_get_messages_slug(),
		'screen_function' => true,
		'position'        => 40,
		'user_has_access' => trs_is_my_profile(),
		'link'            => trs_displayed_user_domain() . trs_get_messages_slug() . '/view/' . (int) $thread_id
	) );

	trs_core_load_template( apply_filters( 'messages_template_view_message', 'members/single/home' ) );
}
add_action( 'trs_actions', 'messages_action_view_message' );

function messages_action_delete_message() {
	global $thread_id;

	if ( !trs_is_messages_component() || trs_is_current_action( 'notices' ) || !trs_is_action_variable( 'delete', 0 ) )
		return false;

	$thread_id = trs_action_variable( 1 );

	if ( !$thread_id || !is_numeric( $thread_id ) || !messages_check_thread_access( $thread_id ) ) {
		trs_core_redirect( trs_displayed_user_domain() . trs_get_messages_slug() . '/' . trs_current_action() );
	} else {
		if ( !check_admin_referer( 'messages_delete_thread' ) )
			return false;

		// Delete message
		if ( !messages_delete_thread( $thread_id ) ) {
			trs_core_add_message( __('There was an error deleting that message.', 'trendr'), 'error' );
		} else {
			trs_core_add_message( __('Message deleted.', 'trendr') );
		}
		trs_core_redirect( trs_displayed_user_domain() . trs_get_messages_slug() . '/' . trs_current_action() );
	}
}
add_action( 'trs_actions', 'messages_action_delete_message' );

function messages_action_bulk_delete() {
	global $thread_ids;

	if ( !trs_is_messages_component() || !trs_is_action_variable( 'bulk-delete', 0 ) )
		return false;

	$thread_ids = $_POST['thread_ids'];

	if ( !$thread_ids || !messages_check_thread_access( $thread_ids ) ) {
		trs_core_redirect( trs_displayed_user_domain() . trs_get_messages_slug() . '/' . trs_current_action() );
	} else {
		if ( !check_admin_referer( 'messages_delete_thread' ) )
			return false;

		if ( !messages_delete_thread( $thread_ids ) )
			trs_core_add_message( __('There was an error deleting messages.', 'trendr'), 'error' );
		else
			trs_core_add_message( __('Messages deleted.', 'trendr') );

		trs_core_redirect( trs_displayed_user_domain() . trs_get_messages_slug() . '/' . trs_current_action() );
	}
}
add_action( 'trs_actions', 'messages_action_bulk_delete' );
?>