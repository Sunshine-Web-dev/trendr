<?php
/*******************************************************************************
 * Screen functions are the controllers of trendr. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function messages_screen_inbox() {
	global $trs;

	if ( trs_action_variables() ) {
		trs_do_404();
		return;
	}

	do_action( 'messages_screen_inbox' );
	trs_core_load_template( apply_filters( 'messages_template_inbox', 'members/single/home' ) );
}

function messages_screen_sentbox() {
	global $trs;

	if ( trs_action_variables() ) {
		trs_do_404();
		return;
	}

	do_action( 'messages_screen_sentbox' );
	trs_core_load_template( apply_filters( 'messages_template_sentbox', 'members/single/home' ) );
}

function messages_screen_compose() {
	global $trs;

	if ( trs_action_variables() ) {
		trs_do_404();
		return;
	}

	// Remove any saved message data from a previous session.
	messages_remove_callback_values();

	// Check if the message form has been submitted
	if ( isset( $_POST['send'] ) ) {

		// Check the nonce
		check_admin_referer( 'messages_send_message' );

		// Check we have what we need
		if ( empty( $_POST['subject'] ) || empty( $_POST['content'] ) ) {
			trs_core_add_message( __( 'There was an error sending that message, please try again', 'trendr' ), 'error' );
		} else {
			// If this is a notice, send it
			if ( isset( $_POST['send-notice'] ) ) {
				if ( messages_send_notice( $_POST['subject'], $_POST['content'] ) ) {
					trs_core_add_message( __( 'Notice sent successfully!', 'trendr' ) );
					trs_core_redirect( $trs->loggedin_user->domain . $trs->messages->slug . '/inbox/' );
				} else {
					trs_core_add_message( __( 'There was an error sending that notice, please try again', 'trendr' ), 'error' );
				}
			} else {
				// Filter recipients into the format we need - array( 'username/userid', 'username/userid' )
				$autocomplete_recipients = explode( ',', $_POST['send-to-input'] );
				$typed_recipients        = explode( ' ', $_POST['send_to_usernames'] );
				$recipients              = array_merge( (array) $autocomplete_recipients, (array) $typed_recipients );
				$recipients              = apply_filters( 'trs_messages_recipients', $recipients );

				// Send the message
				if ( $thread_id = messages_new_message( array( 'recipients' => $recipients, 'subject' => $_POST['subject'], 'content' => $_POST['content'] ) ) ) {
					trs_core_add_message( __( 'Message sent successfully!', 'trendr' ) );
					trs_core_redirect( $trs->loggedin_user->domain . $trs->messages->slug . '/view/' . $thread_id . '/' );
				} else {
					trs_core_add_message( __( 'There was an error sending that message, please try again', 'trendr' ), 'error' );
				}
			}
		}
	}

	do_action( 'messages_screen_compose' );

	trs_core_load_template( apply_filters( 'messages_template_compose', 'members/single/home' ) );
}

function messages_screen_notices() {
	global $notice_id;

	if ( !is_super_admin() )
		return false;

	$notice_id = (int)trs_action_variable( 1 );

	if ( !empty( $notice_id ) && is_numeric( $notice_id ) ) {
		$notice = new TRS_Messages_Notice( $notice_id );

		if ( trs_is_action_variable( 'deactivate', 0 ) ) {
			if ( !$notice->deactivate() ) {
				trs_core_add_message( __('There was a problem deactivating that notice.', 'trendr'), 'error' );
			} else {
				trs_core_add_message( __('Notice deactivated.', 'trendr') );
			}
		} else if ( trs_is_action_variable( 'activate', 0 ) ) {
			if ( !$notice->activate() ) {
				trs_core_add_message( __('There was a problem activating that notice.', 'trendr'), 'error' );
			} else {
				trs_core_add_message( __('Notice activated.', 'trendr') );
			}
		} else if ( trs_is_action_variable( 'delete' ) ) {
			if ( !$notice->delete() ) {
				trs_core_add_message( __('There was a problem deleting that notice.', 'trendr'), 'trendr' );
			} else {
				trs_core_add_message( __('Notice deleted.', 'trendr') );
			}
		}
		trs_core_redirect( trs_loggedin_user_domain() . trs_get_messages_slug() . '/notices' );
	}

	if ( trs_action_variables() ) {
		trs_do_404();
		return;
	}

	do_action( 'messages_screen_notices' );

	trs_core_load_template( apply_filters( 'messages_template_notices', 'members/single/home' ) );
}

function messages_screen_notification_settings() {
	global $trs;

	if ( trs_action_variables() ) {
		trs_do_404();
		return;
	}

	if ( !$new_messages = trs_get_user_meta( $trs->displayed_user->id, 'notification_messages_new_message', true ) )
		$new_messages = 'yes';

	if ( !$new_notices = trs_get_user_meta( $trs->displayed_user->id, 'notification_messages_new_notice', true ) )
		$new_notices  = 'yes';
?>

	<table class="notification-settings" id="messages-notification-settings">
		<thead>
			<tr>
				<th class="icon"></th>
				<th class="title"><?php _e( 'Messages', 'trendr' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'trendr' ) ?></th>
				<th class="no"><?php _e( 'No', 'trendr' )?></th>
			</tr>
		</thead>

		<tbody>
			<tr id="messages-notification-settings-new-message">
				<td></td>
				<td><?php _e( 'A member sends you a new message', 'trendr' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_messages_new_message]" value="yes" <?php checked( $new_messages, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_messages_new_message]" value="no" <?php checked( $new_messages, 'no', true ) ?>/></td>
			</tr>
			<tr id="messages-notification-settings-new-site-notice">
				<td></td>
				<td><?php _e( 'A new site notice is posted', 'trendr' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_messages_new_notice]" value="yes" <?php checked( $new_notices, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_messages_new_notice]" value="no" <?php checked( $new_notices, 'no', true ) ?>/></td>
			</tr>

			<?php do_action( 'messages_screen_notification_settings' ) ?>
		</tbody>
	</table>

<?php
}
add_action( 'trs_notification_settings', 'messages_screen_notification_settings', 2 );

?>