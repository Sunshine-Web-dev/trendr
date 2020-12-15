<?php
if ( !defined( 'TRSB_VERSION' ) ) exit;

/**
 * Check Message Receipients
 * Loops though the message receipients and conver them into User IDs
 * so we can loop though and make sure we are not trying to send a message
 * to someone who is blocking us.
 * @since 1.0
 * @version 1.0
 */
function trsb_check_message_receipients( $recipients ) {
	$cui = trs_loggedin_user_id();
	$_list = trsb_get_blocked_users( $cui );
	
	// Loop though receipients and convert them into a list of user IDs
	// Based on messages_new_message()
	$recipient_ids = array();
	foreach ( (array) $recipients as $recipient ) {
		$recipient = trim( $recipient );

		if ( empty( $recipient ) )
			continue;

		$recipient_id = false;

		// input was numeric
		if ( is_numeric( $recipient ) ) {
			// do a check against the user ID column first
			if ( trs_core_get_core_userdata( (int) $recipient ) )
				$recipient_id = (int) $recipient;

			// if that fails, check against the user_login / user_nicename column
			else {
				if ( trs_is_username_compatibility_mode() )
						$recipient_id = trs_core_get_userid( (int) $recipient );
				else
					$recipient_id = trs_core_get_userid_from_nicename( (int) $recipient );
			}

		} else {
			if ( trs_is_username_compatibility_mode() )
				$recipient_id = trs_core_get_userid( $recipient );
			else
				$recipient_id = trs_core_get_userid_from_nicename( $recipient );
		}

		// Make sure we are not trying to send a message to someone we are blocking
		if ( $recipient_id && !in_array( $recipient_id, $_list ) )
			$recipient_ids[] = (int) $recipient_id;
	}
	
	// Remove duplicates
	$recipient_ids = array_unique( (array) $recipient_ids );

	// Loop though the user IDs and check for blocks
	$filtered = array();
	foreach ( (array) $recipient_ids as $user_id ) {
		$list = trsb_get_blocked_users( $user_id );
		if ( !in_array( $cui, (array) $list ) )
			$filtered[] = $user_id;
	}

	return $filtered;
}

/**
 * Check Conversation
 * Checks to make sure that already existing conversations can not be continued
 * once a receipient has blocked us. If there are more then one receipient, the receipient
 * that is blocked are stripped off.
 * @since 1.0
 * @version 1.0
 */
function trsb_before_message_send( $message ) {
	if ( empty( $message->recipients ) ) return;
	$cui = trs_loggedin_user_id();
	$recipients = $message->recipients;
	
	// First make sure we are not sending a new message to someone we selected to block
	$_list = trsb_get_blocked_users( $cui );
	foreach ( $_list as $_user_id ) {
		if ( array_key_exists( $_user_id, $recipients ) )
			unset( $recipients[ $_user_id ] );
	}
	
	// Second make sure that the message receipients are not blocking us
	$filtered = array();
	foreach ( $recipients as $user_id => $receipient ) {
		$list = trsb_get_blocked_users( $user_id );
		if ( !in_array( $cui, (array) $list ) )
			$filtered[ $user_id ] = $receipient;
	}
	
	$message->recipients = $filtered;
}
?>