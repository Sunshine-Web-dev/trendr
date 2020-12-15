<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

Class TRS_Messages_Thread {
	var $thread_id;
	var $messages;
	var $recipients;
	var $sender_ids;

	var $unread_count;

	function trs_messages_thread ( $thread_id = false, $order = 'ASC' ) {
		$this->__construct( $thread_id, $order);
	}

	function __construct( $thread_id = false, $order = 'ASC' ) {
		if ( $thread_id )
			$this->populate( $thread_id, $order );
	}

	function populate( $thread_id, $order ) {
		global $trmdb, $trs;

		if( 'ASC' != $order && 'DESC' != $order )
			$order= 'ASC';

		$this->messages_order = $order;
		$this->thread_id      = $thread_id;

		if ( !$this->messages = $trmdb->get_results( $trmdb->prepare( "SELECT * FROM {$trs->messages->table_name_messages} WHERE thread_id = %d ORDER BY date_sent " . $order, $this->thread_id ) ) )
			return false;

		foreach ( (array)$this->messages as $key => $message )
			$this->sender_ids[$message->sender_id] = $message->sender_id;

		// Fetch the recipients
		$this->recipients = $this->get_recipients();

		// Get the unread count for the logged in user
		if ( isset( $this->recipients[$trs->loggedin_user->id] ) )
			$this->unread_count = $this->recipients[$trs->loggedin_user->id]->unread_count;
	}

	function mark_read() {
		TRS_Messages_Thread::mark_as_read( $this->thread_id );
	}

	function mark_unread() {
		TRS_Messages_Thread::mark_as_unread( $this->thread_id );
	}

	function get_recipients() {
		global $trmdb, $trs;

		$results = $trmdb->get_results( $trmdb->prepare( "SELECT * FROM {$trs->messages->table_name_recipients} WHERE thread_id = %d", $this->thread_id ) );

		foreach ( (array)$results as $recipient )
			$recipients[$recipient->user_id] = $recipient;

		return $recipients;
	}

	/** Static Functions **/

	function delete( $thread_id ) {
		global $trmdb, $trs;

		$delete_for_user = $trmdb->query( $trmdb->prepare( "UPDATE {$trs->messages->table_name_recipients} SET is_deleted = 1 WHERE thread_id = %d AND user_id = %d", $thread_id, $trs->loggedin_user->id ) );

		// Check to see if any more recipients remain for this message
		// if not, then delete the message from the database.
		$recipients = $trmdb->get_results( $trmdb->prepare( "SELECT id FROM {$trs->messages->table_name_recipients} WHERE thread_id = %d AND is_deleted = 0", $thread_id ) );

		if ( empty( $recipients ) ) {
			// Delete all the messages
			$trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->messages->table_name_messages} WHERE thread_id = %d", $thread_id ) );

			// Delete all the recipients
			$trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->messages->table_name_recipients} WHERE thread_id = %d", $thread_id ) );
		}

		return true;
	}

	function get_current_threads_for_user( $user_id, $box = 'inbox', $type = 'all', $limit = null, $page = null ) {
		global $trmdb, $trs;

		$pag_sql = $type_sql = '';
		if ( $limit && $page )
			$pag_sql = $trmdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		if ( $type == 'unread' )
			$type_sql = $trmdb->prepare( " AND r.unread_count != 0 " );
		elseif ( $type == 'read' )
			$type_sql = $trmdb->prepare( " AND r.unread_count = 0 " );

		if ( 'sentbox' == $box ) {
			$thread_ids = $trmdb->get_results( $trmdb->prepare( "SELECT m.thread_id, MAX(m.date_sent) AS date_sent FROM {$trs->messages->table_name_recipients} r, {$trs->messages->table_name_messages} m WHERE m.thread_id = r.thread_id AND m.sender_id = r.user_id AND m.sender_id = %d AND r.is_deleted = 0 GROUP BY m.thread_id ORDER BY date_sent DESC {$pag_sql}", $user_id ) );
			$total_threads = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT( DISTINCT m.thread_id ) FROM {$trs->messages->table_name_recipients} r, {$trs->messages->table_name_messages} m WHERE m.thread_id = r.thread_id AND m.sender_id = r.user_id AND m.sender_id = %d AND r.is_deleted = 0 ", $user_id ) );
		} else {
			$thread_ids = $trmdb->get_results( $trmdb->prepare( "SELECT m.thread_id, MAX(m.date_sent) AS date_sent FROM {$trs->messages->table_name_recipients} r, {$trs->messages->table_name_messages} m WHERE m.thread_id = r.thread_id AND r.is_deleted = 0 AND r.user_id = %d AND r.sender_only = 0 {$type_sql} GROUP BY m.thread_id ORDER BY date_sent DESC {$pag_sql}", $user_id ) );
			$total_threads = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT( DISTINCT m.thread_id ) FROM {$trs->messages->table_name_recipients} r, {$trs->messages->table_name_messages} m WHERE m.thread_id = r.thread_id AND r.is_deleted = 0 AND r.user_id = %d AND r.sender_only = 0 {$type_sql}", $user_id ) );
		}

		if ( empty( $thread_ids ) )
			return false;

		// Sort threads by date_sent
		foreach( (array)$thread_ids as $thread )
			$sorted_threads[$thread->thread_id] = strtotime( $thread->date_sent );

		arsort( $sorted_threads );

		$threads = false;
		foreach ( (array)$sorted_threads as $thread_id => $date_sent )
			$threads[] = new TRS_Messages_Thread( $thread_id );

		return array( 'threads' => &$threads, 'total' => (int)$total_threads );
	}

	function mark_as_read( $thread_id ) {
		global $trmdb, $trs;

		$sql = $trmdb->prepare( "UPDATE {$trs->messages->table_name_recipients} SET unread_count = 0 WHERE user_id = %d AND thread_id = %d", $trs->loggedin_user->id, $thread_id );
		$trmdb->query($sql);
	}

	function mark_as_unread( $thread_id ) {
		global $trmdb, $trs;

		$sql = $trmdb->prepare( "UPDATE {$trs->messages->table_name_recipients} SET unread_count = 1 WHERE user_id = %d AND thread_id = %d", $trs->loggedin_user->id, $thread_id );
		$trmdb->query($sql);
	}

	function get_total_threads_for_user( $user_id, $box = 'inbox', $type = 'all' ) {
		global $trmdb, $trs;

		$exclude_sender = '';
		if ( $box != 'sentbox' )
			$exclude_sender = ' AND sender_only != 1';

		if ( $type == 'unread' )
			$type_sql = $trmdb->prepare( " AND unread_count != 0 " );
		else if ( $type == 'read' )
			$type_sql = $trmdb->prepare( " AND unread_count = 0 " );

		return (int) $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(thread_id) FROM {$trs->messages->table_name_recipients} WHERE user_id = %d AND is_deleted = 0$exclude_sender $type_sql", $user_id ) );
	}

	function user_is_sender( $thread_id ) {
		global $trmdb, $trs;

		$sender_ids = $trmdb->get_col( $trmdb->prepare( "SELECT sender_id FROM {$trs->messages->table_name_messages} WHERE thread_id = %d", $thread_id ) );

		if ( !$sender_ids )
			return false;

		return in_array( $trs->loggedin_user->id, $sender_ids );
	}

	function get_last_sender( $thread_id ) {
		global $trmdb, $trs;

		if ( !$sender_id = $trmdb->get_var( $trmdb->prepare( "SELECT sender_id FROM {$trs->messages->table_name_messages} WHERE thread_id = %d GROUP BY sender_id ORDER BY date_sent LIMIT 1", $thread_id ) ) )
			return false;

		return trs_core_get_userlink( $sender_id, true );
	}

	function get_inbox_count( $user_id = 0 ) {
		global $trmdb, $trs;

		if ( empty( $user_id ) )
			$user_id = $trs->loggedin_user->id;

		$sql = $trmdb->prepare( "SELECT SUM(unread_count) FROM {$trs->messages->table_name_recipients} WHERE user_id = %d AND is_deleted = 0 AND sender_only = 0", $user_id );
		$unread_count = $trmdb->get_var( $sql );

		if ( empty( $unread_count ) || is_trm_error( $unread_count ) )
			return 0;

		return (int) $unread_count;
	}

	function check_access( $thread_id, $user_id = 0 ) {
		global $trmdb, $trs;

		if ( empty( $user_id ) )
			$user_id = $trs->loggedin_user->id;

		return $trmdb->get_var( $trmdb->prepare( "SELECT id FROM {$trs->messages->table_name_recipients} WHERE thread_id = %d AND user_id = %d", $thread_id, $user_id ) );
	}

	function is_valid( $thread_id ) {
		global $trmdb, $trs;

		return $trmdb->get_var( $trmdb->prepare( "SELECT thread_id FROM {$trs->messages->table_name_messages} WHERE thread_id = %d LIMIT 1", $thread_id ) );
	}

	function get_recipient_links($recipients) {
		if ( count($recipients) >= 5 )
			return count( $recipients ) . __(' Recipients', 'trendr');

		foreach ( (array)$recipients as $recipient )
			$recipient_links[] = trs_core_get_userlink( $recipient->user_id );

		return implode( ', ', (array) $recipient_links );
	}

	// Update Functions

	function update_tables() {
		global $trmdb, $trs;

		$trs_prefix = trs_core_get_table_prefix();
		$errors    = false;
		$threads   = $trmdb->get_results( $trmdb->prepare( "SELECT * FROM {$trs_prefix}trs_messages_threads" ) );

		// Nothing to update, just return true to remove the table
		if ( empty( $threads ) )
			return true;

		foreach( (array)$threads as $thread ) {
			$message_ids = maybe_unserialize( $thread->message_ids );

			if ( !empty( $message_ids ) ) {
				$message_ids = implode( ',', $message_ids );

				// Add the thread_id to the messages table
				if ( !$trmdb->query( $trmdb->prepare( "UPDATE {$trs->messages->table_name_messages} SET thread_id = %d WHERE id IN ({$message_ids})", $thread->id ) ) )
					$errors = true;
			}
		}

		if ( $errors )
			return false;

		return true;
	}
}

Class TRS_Messages_Message {
	var $id;
	var $thread_id;
	var $sender_id;
	var $subject;
	var $message;
	var $date_sent;

	var $recipients = false;

	function trs_messages_message( $id = null ) {
		$this->__construct( $id );
	}

	function __construct( $id = null ) {
		global $trs;

		$this->date_sent = trs_core_current_time();
		$this->sender_id = $trs->loggedin_user->id;

		if ( !empty( $id ) )
			$this->populate( $id );
	}

	function populate( $id ) {
		global $trmdb, $trs;

		if ( $message = $trmdb->get_row( $trmdb->prepare( "SELECT * FROM {$trs->messages->table_name_messages} WHERE id = %d", $id ) ) ) {
			$this->id        = $message->id;
			$this->thread_id = $message->thread_id;
			$this->sender_id = $message->sender_id;
			$this->subject   = $message->subject;
			$this->message   = $message->message;
			$this->date_sent = $message->date_sent;
		}
	}

	function send() {
		global $trmdb, $trs;

		$this->sender_id = apply_filters( 'messages_message_sender_id_before_save', $this->sender_id, $this->id );
		$this->thread_id = apply_filters( 'messages_message_thread_id_before_save', $this->thread_id, $this->id );
		$this->subject   = apply_filters( 'messages_message_subject_before_save',   $this->subject,   $this->id );
		$this->message   = apply_filters( 'messages_message_content_before_save',   $this->message,   $this->id );
		$this->date_sent = apply_filters( 'messages_message_date_sent_before_save', $this->date_sent, $this->id );

		do_action_ref_array( 'messages_message_before_save', array( &$this ) );

		// Make sure we have at least one recipient before sending.
		if ( empty( $this->recipients ) )
			return false;

		$new_thread = false;

		// If we have no thread_id then this is the first message of a new thread.
		if ( empty( $this->thread_id ) ) {
			$this->thread_id = (int)$trmdb->get_var( $trmdb->prepare( "SELECT MAX(thread_id) FROM {$trs->messages->table_name_messages}" ) ) + 1;
			$new_thread = true;
		}

		// First insert the message into the messages table
		if ( !$trmdb->query( $trmdb->prepare( "INSERT INTO {$trs->messages->table_name_messages} ( thread_id, sender_id, subject, message, date_sent ) VALUES ( %d, %d, %s, %s, %s )", $this->thread_id, $this->sender_id, $this->subject, $this->message, $this->date_sent ) ) )
			return false;

		$recipient_ids = array();

		if ( $new_thread ) {
			// Add an recipient entry for all recipients
			foreach ( (array)$this->recipients as $recipient ) {
				$trmdb->query( $trmdb->prepare( "INSERT INTO {$trs->messages->table_name_recipients} ( user_id, thread_id, unread_count ) VALUES ( %d, %d, 1 )", $recipient->user_id, $this->thread_id ) );
				$recipient_ids[] = $recipient->user_id;
			}

			// Add a sender recipient entry if the sender is not in the list of recipients
			if ( !in_array( $this->sender_id, $recipient_ids ) )
				$trmdb->query( $trmdb->prepare( "INSERT INTO {$trs->messages->table_name_recipients} ( user_id, thread_id, sender_only ) VALUES ( %d, %d, 1 )", $this->sender_id, $this->thread_id ) );
		} else {
			// Update the unread count for all recipients
			$trmdb->query( $trmdb->prepare( "UPDATE {$trs->messages->table_name_recipients} SET unread_count = unread_count + 1, sender_only = 0, is_deleted = 0 WHERE thread_id = %d AND user_id != %d", $this->thread_id, $this->sender_id ) );
		}

		$this->id = $trmdb->insert_id;
		messages_remove_callback_values();

		do_action_ref_array( 'messages_message_after_save', array( &$this ) );

		return $this->id;
	}

	function get_recipients() {
		global $trs, $trmdb;

		return $trmdb->get_results( $trmdb->prepare( "SELECT user_id FROM {$trs->messages->table_name_recipients} WHERE thread_id = %d", $this->thread_id ) );
	}

	// Static Functions

	function get_recipient_ids( $recipient_usernames ) {
		if ( !$recipient_usernames )
			return false;

		if ( is_array( $recipient_usernames ) ) {
			for ( $i = 0, $count = count( $recipient_usernames ); $i < $count; ++$i ) {
				if ( $rid = trs_core_get_userid( trim($recipient_usernames[$i]) ) )
					$recipient_ids[] = $rid;
			}
		}

		return $recipient_ids;
	}

	function get_last_sent_for_user( $thread_id ) {
		global $trmdb, $trs;

		return $trmdb->get_var( $trmdb->prepare( "SELECT id FROM {$trs->messages->table_name_messages} WHERE sender_id = %d AND thread_id = %d ORDER BY date_sent DESC LIMIT 1", $trs->loggedin_user->id, $thread_id ) );
	}

	function is_user_sender( $user_id, $message_id ) {
		global $trmdb, $trs;
		return $trmdb->get_var( $trmdb->prepare( "SELECT id FROM {$trs->messages->table_name_messages} WHERE sender_id = %d AND id = %d", $user_id, $message_id ) );
	}

	function get_message_sender( $message_id ) {
		global $trmdb, $trs;
		return $trmdb->get_var( $trmdb->prepare( "SELECT sender_id FROM {$trs->messages->table_name_messages} WHERE id = %d", $message_id ) );
	}
}

Class TRS_Messages_Notice {
	var $id = null;
	var $subject;
	var $message;
	var $date_sent;
	var $is_active;

	function trs_messages_notice( $id = null ) {
		$this->__construct($id);
	}

	function __construct( $id = null ) {
		if ( $id ) {
			$this->id = $id;
			$this->populate($id);
		}
	}

	function populate() {
		global $trmdb, $trs;

		$notice = $trmdb->get_row( $trmdb->prepare( "SELECT * FROM {$trs->messages->table_name_notices} WHERE id = %d", $this->id ) );

		if ( $notice ) {
			$this->subject   = $notice->subject;
			$this->message   = $notice->message;
			$this->date_sent = $notice->date_sent;
			$this->is_active = $notice->is_active;
		}
	}

	function save() {
		global $trmdb, $trs;

		$this->subject = apply_filters( 'messages_notice_subject_before_save', $this->subject, $this->id );
		$this->message = apply_filters( 'messages_notice_message_before_save', $this->message, $this->id );

		do_action_ref_array( 'messages_notice_before_save', array( &$this ) );

		if ( empty( $this->id ) )
			$sql = $trmdb->prepare( "INSERT INTO {$trs->messages->table_name_notices} (subject, message, date_sent, is_active) VALUES (%s, %s, %s, %d)", $this->subject, $this->message, $this->date_sent, $this->is_active );
		else
			$sql = $trmdb->prepare( "UPDATE {$trs->messages->table_name_notices} SET subject = %s, message = %s, is_active = %d WHERE id = %d", $this->subject, $this->message, $this->is_active, $this->id );

		if ( !$trmdb->query( $sql ) )
			return false;

		if ( !$id = $this->id )
			$id = $trmdb->insert_id;

		// Now deactivate all notices apart from the new one.
		$trmdb->query( $trmdb->prepare( "UPDATE {$trs->messages->table_name_notices} SET is_active = 0 WHERE id != %d", $id ) );

		trs_update_user_meta( $trs->loggedin_user->id, 'last_activity', trs_core_current_time() );

		do_action_ref_array( 'messages_notice_after_save', array( &$this ) );

		return true;
	}

	function activate() {
		$this->is_active = 1;
		if ( !$this->save() )
			return false;

		return true;
	}

	function deactivate() {
		$this->is_active = 0;
		if ( !$this->save() )
			return false;

		return true;
	}

	function delete() {
		global $trmdb, $trs;

		$sql = $trmdb->prepare( "DELETE FROM {$trs->messages->table_name_notices} WHERE id = %d", $this->id );

		if ( !$trmdb->query( $sql ) )
			return false;

		return true;
	}

	// Static Functions

	function get_notices() {
		global $trmdb, $trs;

		$notices = $trmdb->get_results( $trmdb->prepare( "SELECT * FROM {$trs->messages->table_name_notices} ORDER BY date_sent DESC" ) );
		return $notices;
	}

	function get_total_notice_count() {
		global $trmdb, $trs;

		$notice_count = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(id) FROM " . $trs->messages->table_name_notices ) );

		return $notice_count;
	}

	function get_active() {
		global $trmdb, $trs;

		$notice_id = $trmdb->get_var( $trmdb->prepare( "SELECT id FROM {$trs->messages->table_name_notices} WHERE is_active = 1") );
		return new TRS_Messages_Notice( $notice_id );
	}
}
?>