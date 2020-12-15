<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*****************************************************************************
 * Message Box Template Class
 **/
Class TRS_Messages_Box_Template {
	var $current_thread = -1;
	var $current_thread_count;
	var $total_thread_count;
	var $threads;
	var $thread;

	var $in_the_loop;
	var $user_id;
	var $box;

	var $pag_page;
	var $pag_num;
	var $pag_links;

	function trs_messages_box_template( $user_id, $box, $per_page, $max, $type ) {
		$this->__construct( $user_id, $box, $per_page, $max, $type );
	}

	function __construct( $user_id, $box, $per_page, $max, $type ) {
		$this->pag_page = isset( $_GET['mpage'] ) ? intval( $_GET['mpage'] ) : 1;
		$this->pag_num  = isset( $_GET['num'] ) ? intval( $_GET['num'] ) : $per_page;

		$this->user_id  = $user_id;
		$this->box      = $box;
		$this->type     = $type;

		if ( 'notices' == $this->box ) {
			$this->threads = TRS_Messages_Notice::get_notices();
		} else {
			$threads = TRS_Messages_Thread::get_current_threads_for_user( $this->user_id, $this->box, $this->type, $this->pag_num, $this->pag_page );

			$this->threads            = $threads['threads'];
			$this->total_thread_count = $threads['total'];
		}

		if ( !$this->threads ) {
			$this->thread_count       = 0;
			$this->total_thread_count = 0;
		} else {
			$total_notice_count = TRS_Messages_Notice::get_total_notice_count();

			if ( !$max || $max >= (int)$total_notice_count ) {
				if ( 'notices' == $this->box ) {
					$this->total_thread_count = (int)$total_notice_count;
				}
			} else {
				$this->total_thread_count = (int)$max;
			}

			if ( $max ) {
				if ( $max >= count( $this->threads ) ) {
					$this->thread_count = count( $this->threads );
				} else {
					$this->thread_count = (int)$max;
				}
			} else {
				$this->thread_count = count( $this->threads );
			}
		}

		if ( (int)$this->total_thread_count && (int)$this->pag_num ) {
			$this->pag_links = paginate_links( array(
				'base'      => add_query_arg( 'mpage', '%#%' ),
				'format'    => '',
				'total'     => ceil( (int)$this->total_thread_count / (int)$this->pag_num ),
				'current'   => $this->pag_page,
				'prev_text' => _x( '&larr;', 'Message pagination previous text', 'trendr' ),
				'next_text' => _x( '&rarr;', 'Message pagination next text', 'trendr' ),
				'mid_size'  => 1
			) );
		}
	}

	function has_threads() {
		if ( $this->thread_count )
			return true;

		return false;
	}

	function next_thread() {
		$this->current_thread++;
		$this->thread = $this->threads[$this->current_thread];

		return $this->thread;
	}

	function rewind_threads() {
		$this->current_thread = -1;
		if ( $this->thread_count > 0 ) {
			$this->thread = $this->threads[0];
		}
	}

	function message_threads() {
		if ( $this->current_thread + 1 < $this->thread_count ) {
			return true;
		} elseif ( $this->current_thread + 1 == $this->thread_count ) {
			do_action('messages_box_loop_end');
			// Do some cleaning up after the loop
			$this->rewind_threads();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_message_thread() {
		global $trs;

		$this->in_the_loop = true;
		$this->thread = $this->next_thread();

		if ( 'notices' != $trs->current_action ) {
			$last_message_index = count( $this->thread->messages ) - 1;
			$this->thread->messages = array_reverse( (array)$this->thread->messages );

			// Set up the last message data
			if ( count($this->thread->messages) > 1 ) {
				if ( 'inbox' == $this->box ) {
					foreach ( (array)$this->thread->messages as $key => $message ) {
						if ( $trs->loggedin_user->id != $message->sender_id ) {
							$last_message_index = $key;
							break;
						}
					}

				} elseif ( 'sentbox' == $this->box ) {
					foreach ( (array)$this->thread->messages as $key => $message ) {
						if ( $trs->loggedin_user->id == $message->sender_id ) {
							$last_message_index = $key;
							break;
						}
					}
				}
			}

			$this->thread->last_message_id = $this->thread->messages[$last_message_index]->id;
			$this->thread->last_message_date = $this->thread->messages[$last_message_index]->date_sent;
			$this->thread->last_sender_id = $this->thread->messages[$last_message_index]->sender_id;
			$this->thread->last_message_subject = $this->thread->messages[$last_message_index]->subject;
			$this->thread->last_message_content = $this->thread->messages[$last_message_index]->message;
		}

		if ( 0 == $this->current_thread ) // loop has just started
			do_action('messages_box_loop_start');
	}
}

function trs_has_message_threads( $args = '' ) {
	global $trs, $messages_template;

	$defaults = array(
		'user_id' => $trs->loggedin_user->id,
		'box' => 'inbox',
		'per_page' => 10,
		'max' => false,
		'type' => 'all'
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( 'notices' == $trs->current_action && !is_super_admin() ) {
		trm_redirect( $trs->displayed_user->id );
	} else {
		if ( 'inbox' == $trs->current_action )
			trs_core_delete_notifications_by_type( $trs->loggedin_user->id, $trs->messages->id, 'new_message' );

		if ( 'sentbox' == $trs->current_action )
			$box = 'sentbox';

		if ( 'notices' == $trs->current_action )
			$box = 'notices';

		$messages_template = new TRS_Messages_Box_Template( $user_id, $box, $per_page, $max, $type );
	}

	return apply_filters( 'trs_has_message_threads', $messages_template->has_threads(), $messages_template );
}

function trs_message_threads() {
	global $messages_template;
	return $messages_template->message_threads();
}

function trs_message_thread() {
	global $messages_template;
	return $messages_template->the_message_thread();
}

function trs_message_thread_id() {
	echo trs_get_message_thread_id();
}
	function trs_get_message_thread_id() {
		global $messages_template;

		return apply_filters( 'trs_get_message_thread_id', $messages_template->thread->thread_id );
	}

function trs_message_thread_subject() {
	echo trs_get_message_thread_subject();
}
	function trs_get_message_thread_subject() {
		global $messages_template, $message_template_subject;

		return apply_filters( 'trs_get_message_thread_subject', stripslashes_deep( $messages_template->thread->last_message_subject ) );
	}

function trs_message_thread_excerpt() {
	echo trs_get_message_thread_excerpt();
}
	function trs_get_message_thread_excerpt() {
		global $messages_template;

		return apply_filters( 'trs_get_message_thread_excerpt', strip_tags( trs_create_excerpt( $messages_template->thread->last_message_content, 75 ) ) );
	}

function trs_message_thread_from() {
	echo trs_get_message_thread_from();
}
	function trs_get_message_thread_from() {
		global $messages_template, $trs;

		return apply_filters( 'trs_get_message_thread_from', trs_core_get_userlink( $messages_template->thread->last_sender_id ) );
	}

function trs_message_thread_to() {
	echo trs_get_message_thread_to();
}
	function trs_get_message_thread_to() {
		global $messages_template;
		return apply_filters( 'trs_message_thread_to', TRS_Messages_Thread::get_recipient_links($messages_template->thread->recipients) );
	}

function trs_message_thread_view_link() {
	echo trs_get_message_thread_view_link();
}
	function trs_get_message_thread_view_link() {
		global $messages_template, $trs;
		return apply_filters( 'trs_get_message_thread_view_link', trailingslashit( $trs->loggedin_user->domain . $trs->messages->slug . '/view/' . $messages_template->thread->thread_id ) );
	}

function trs_message_thread_delete_link() {
	echo trs_get_message_thread_delete_link();
}
	function trs_get_message_thread_delete_link() {
		global $messages_template, $trs;
		return apply_filters( 'trs_get_message_thread_delete_link', trm_nonce_url( $trs->loggedin_user->domain . $trs->messages->slug . '/' . $trs->current_action . '/delete/' . $messages_template->thread->thread_id, 'messages_delete_thread' ) );
	}

function trs_message_css_class() {
	echo trs_get_message_css_class();
}

	function trs_get_message_css_class() {
		global $messages_template;

		$class = false;

		if ( $messages_template->current_thread % 2 == 1 )
			$class .= 'alt';

		return apply_filters( 'trs_get_message_css_class', trim( $class ) );
	}

function trs_message_thread_has_unread() {
	global $messages_template;

	if ( $messages_template->thread->unread_count )
		return true;

	return false;
}

function trs_message_thread_unread_count() {
	echo trs_get_message_thread_unread_count();
}
	function trs_get_message_thread_unread_count() {
		global $messages_template;

		if ( (int)$messages_template->thread->unread_count )
			return apply_filters( 'trs_get_message_thread_unread_count', $messages_template->thread->unread_count );

		return false;
	}

function trs_message_thread_last_post_date() {
	echo trs_get_message_thread_last_post_date();
}
	function trs_get_message_thread_last_post_date() {
		global $messages_template;

		return apply_filters( 'trs_get_message_thread_last_post_date', trs_format_time( strtotime( $messages_template->thread->last_message_date ) ) );
	}

function trs_message_thread_portrait() {
	echo trs_get_message_thread_portrait();
}
	function trs_get_message_thread_portrait() {
		global $messages_template, $trs;

		return apply_filters( 'trs_get_message_thread_portrait', trs_core_fetch_portrait( array( 'item_id' => $messages_template->thread->last_sender_id, 'type' => 'thumb' ) ) );
	}

function trs_message_thread_view() {
	global $thread_id;

	messages_view_thread($thread_id);
}

function trs_total_unread_messages_count() {
	echo trs_get_total_unread_messages_count();
}
	function trs_get_total_unread_messages_count() {
		return apply_filters( 'trs_get_total_unread_messages_count', TRS_Messages_Thread::get_inbox_count() );
	}

function trs_messages_pagination() {
	echo trs_get_messages_pagination();
}
	function trs_get_messages_pagination() {
		global $messages_template;
		return apply_filters( 'trs_get_messages_pagination', $messages_template->pag_links );
	}

function trs_messages_pagination_count() {
	global $messages_template;

	$start_num = intval( ( $messages_template->pag_page - 1 ) * $messages_template->pag_num ) + 1;
	$from_num = trs_core_number_format( $start_num );
	$to_num = trs_core_number_format( ( $start_num + ( $messages_template->pag_num - 1 ) > $messages_template->total_thread_count ) ? $messages_template->total_thread_count : $start_num + ( $messages_template->pag_num - 1 ) );
	$total = trs_core_number_format( $messages_template->total_thread_count );

	echo sprintf( __( 'Viewing message %1$s to %2$s (of %3$s messages)', 'trendr' ), $from_num, $to_num, $total ); ?><?php
}

/**
 * Echoes the form action for Messages HTML forms
 *
 * @package trendr
 */
function trs_messages_form_action() {
	echo trs_get_messages_form_action();
}
	/**
	 * Returns the form action for Messages HTML forms
	 *
	 * @package trendr
	 *
	 * @return str The form action
	 */
	function trs_get_messages_form_action() {
		return apply_filters( 'trs_get_messages_form_action', trailingslashit( trs_loggedin_user_domain() . trs_get_messages_slug() . '/' . trs_current_action() . '/' . trs_action_variable( 0 ) ) );
	}

function trs_messages_username_value() {
	echo trs_get_messages_username_value();
}
	function trs_get_messages_username_value() {
		if ( isset( $_COOKIE['trs_messages_send_to'] ) ) {
			return apply_filters( 'trs_get_messages_username_value', $_COOKIE['trs_messages_send_to'] );
		} else if ( isset( $_GET['r'] ) && !isset( $_COOKIE['trs_messages_send_to'] ) ) {
			return apply_filters( 'trs_get_messages_username_value', $_GET['r'] );
		}
	}

function trs_messages_subject_value() {
	echo trs_get_messages_subject_value();
}
	function trs_get_messages_subject_value() {
		$subject = '';
		if ( !empty( $_POST['subject'] ) )
			$subject = $_POST['subject'];

		return apply_filters( 'trs_get_messages_subject_value', $subject );
	}

function trs_messages_content_value() {
	echo trs_get_messages_content_value();
}
	function trs_get_messages_content_value() {
		$content = '';
		if ( !empty( $_POST['content'] ) )
			$content = $_POST['content'];

		return apply_filters( 'trs_get_messages_content_value', $content );
	}

function trs_messages_options() {
	global $trs;
?>
	<?php _e( 'Select:', 'trendr' ) ?>
	<select name="message-type-select" id="message-type-select">
		<option value=""></option>
		<option value="read"><?php _e('Read', 'trendr') ?></option>
		<option value="unread"><?php _e('Unread', 'trendr') ?></option>
		<option value="all"><?php _e('All', 'trendr') ?></option>
	</select> &nbsp;
	<?php if ( $trs->current_action != 'sentbox' && $trs->current_action != 'notices' ) : ?>
		<a href="#" id="mark_as_read"><?php _e('Mark as Read', 'trendr') ?></a> &nbsp;
		<a href="#" id="mark_as_unread"><?php _e('Mark as Unread', 'trendr') ?></a> &nbsp;
	<?php endif; ?>
	<a href="#" id="delete_<?php echo $trs->current_action ?>_messages"><?php _e('Delete Selected', 'trendr') ?></a> &nbsp;
<?php
}

function trs_message_is_active_notice() {
	global $messages_template;

	if ( $messages_template->thread->is_active ) {
		echo "<strong>";
		_e( 'Currently Active', 'trendr' );
		echo "</strong>";
	}
}
	function trs_get_message_is_active_notice() {
		global $messages_template;

		if ( $messages_template->thread->is_active )
			return true;

		return false;
	}

function trs_message_notice_id() {
	echo trs_get_message_notice_id();
}
	function trs_get_message_notice_id() {
		global $messages_template;
		return apply_filters( 'trs_get_message_notice_id', $messages_template->thread->id );
	}

function trs_message_notice_post_date() {
	echo trs_get_message_notice_post_date();
}
	function trs_get_message_notice_post_date() {
		global $messages_template;
		return apply_filters( 'trs_get_message_notice_post_date', trs_format_time( strtotime($messages_template->thread->date_sent) ) );
	}

function trs_message_notice_subject() {
	echo trs_get_message_notice_subject();
}
	function trs_get_message_notice_subject() {
		global $messages_template;
		return apply_filters( 'trs_get_message_notice_subject', $messages_template->thread->subject );
	}

function trs_message_notice_text() {
	echo trs_get_message_notice_text();
}
	function trs_get_message_notice_text() {
		global $messages_template;
		return apply_filters( 'trs_get_message_notice_text', $messages_template->thread->message );
	}

function trs_message_notice_delete_link() {
	echo trs_get_message_notice_delete_link();
}
	function trs_get_message_notice_delete_link() {
		global $messages_template, $trs;

		return apply_filters( 'trs_get_message_notice_delete_link', trm_nonce_url( $trs->loggedin_user->domain . $trs->messages->slug . '/notices/delete/' . $messages_template->thread->id, 'messages_delete_thread' ) );
	}

function trs_message_activate_deactivate_link() {
	echo trs_get_message_activate_deactivate_link();
}
	function trs_get_message_activate_deactivate_link() {
		global $messages_template, $trs;

		if ( 1 == (int)$messages_template->thread->is_active ) {
			$link = trm_nonce_url( $trs->loggedin_user->domain . $trs->messages->slug . '/notices/deactivate/' . $messages_template->thread->id, 'messages_deactivate_notice' );
		} else {
			$link = trm_nonce_url( $trs->loggedin_user->domain . $trs->messages->slug . '/notices/activate/' . $messages_template->thread->id, 'messages_activate_notice' );
		}
		return apply_filters( 'trs_get_message_activate_deactivate_link', $link );
	}

function trs_message_activate_deactivate_text() {
	echo trs_get_message_activate_deactivate_text();
}
	function trs_get_message_activate_deactivate_text() {
		global $messages_template;

		if ( 1 == (int)$messages_template->thread->is_active  ) {
			$text = __('Deactivate', 'trendr');
		} else {
			$text = __('Activate', 'trendr');
		}
		return apply_filters( 'trs_message_activate_deactivate_text', $text );
	}

/**
 * Output the messages component slug
 *
 * @package trendr
 * @sutrsackage Messages Template
 * @since 1.5
 *
 * @uses trs_get_messages_slug()
 */
function trs_messages_slug() {
	echo trs_get_messages_slug();
}
	/**
	 * Return the messages component slug
	 *
	 * @package trendr
	 * @sutrsackage Messages Template
	 * @since 1.5
	 */
	function trs_get_messages_slug() {
		global $trs;
		return apply_filters( 'trs_get_messages_slug', $trs->messages->slug );
	}

function trs_message_get_notices() {
	global $userdata;

	$notice = TRS_Messages_Notice::get_active();

	if ( empty( $notice ) )
		return false;

	$closed_notices = trs_get_user_meta( $userdata->ID, 'closed_notices', true );

	if ( !$closed_notices )
		$closed_notices = array();

	if ( is_array($closed_notices) ) {
		if ( !in_array( $notice->id, $closed_notices ) && $notice->id ) {
			?>
			<div id="message" class="info notice" rel="n-<?php echo $notice->id ?>">
				<p>
					<strong><?php echo stripslashes( trm_filter_kses( $notice->subject ) ) ?></strong><br />
					<?php echo stripslashes( trm_filter_kses( $notice->message) ) ?>
					<a href="#" id="close-notice"><?php _e( 'Close', 'trendr' ) ?></a>
				</p>
			</div>
			<?php
		}
	}
}

function trs_send_private_message_link() {
	echo trs_get_send_private_message_link();
}
	function trs_get_send_private_message_link() {
		global $trs;

		if ( trs_is_my_profile() || !is_user_logged_in() )
			return false;

		return apply_filters( 'trs_get_send_private_message_link', trm_nonce_url( $trs->loggedin_user->domain . $trs->messages->slug . '/compose/?r=' . trs_core_get_username( $trs->displayed_user->id, $trs->displayed_user->userdata->user_nicename, $trs->displayed_user->userdata->user_login ) ) );
	}

/**
 * trs_send_private_message_button()
 *
 * Explicitly named function to avoid confusion with public messages.
 *
 * @uses trs_get_send_message_button()
 * @since 1.2.6
 */
function trs_send_private_message_button() {
	echo trs_get_send_message_button();
}

function trs_send_message_button() {
	echo trs_get_send_message_button();
}
	function trs_get_send_message_button() {
		return apply_filters( 'trs_get_send_message_button',
			trs_get_button( array(
				'id'                => 'private_message',
				'component'         => 'messages',
				'must_be_logged_in' => true,
				'block_self'        => true,
				'wrapper_id'        => 'send-private-message',
				'link_href'         => trs_get_send_private_message_link(),
				'link_title'        => __( 'Send a private message to this user.', 'trendr' ),
				'link_text'         => __( '', 'trendr' ),
				'link_class'        => 'send-message',
			) )
		);
	}

function trs_message_loading_image_src() {
	echo trs_get_message_loading_image_src();
}
	function trs_get_message_loading_image_src() {
		global $trs;
		return apply_filters( 'trs_get_message_loading_image_src', $trs->messages->image_base . '/ajax-loader.gif' );
	}

function trs_message_get_recipient_tabs() {
	$recipients = explode( ' ', trs_get_message_get_recipient_usernames() );

	foreach ( $recipients as $recipient ) {
		$user_id = trs_is_username_compatibility_mode() ? trs_core_get_userid( $recipient ) : trs_core_get_userid_from_nicename( $recipient );

		if ( $user_id ) : ?>

			<li id="un-<?php echo esc_attr( $recipient ); ?>" class="friend-tab">
				<span><?php
					echo trs_core_fetch_portrait( array( 'item_id' => $user_id, 'type' => 'thumb', 'width' => 15, 'height' => 15 ) );
					echo trs_core_get_userlink( $user_id );
				?></span>
			</li>

		<?php endif;
	}
}

function trs_message_get_recipient_usernames() {
	echo trs_get_message_get_recipient_usernames();
}
	function trs_get_message_get_recipient_usernames() {
		$recipients = isset( $_GET['r'] ) ? stripslashes( $_GET['r'] ) : '';

		return apply_filters( 'trs_get_message_get_recipient_usernames', $recipients );
	}


/*****************************************************************************
 * Message Thread Template Class
 **/

class TRS_Messages_Thread_Template {
	var $current_message = -1;
	var $message_count;
	var $message;

	var $thread;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;
	var $total_message_count;

	function trs_messages_thread_template( $thread_id, $order ) {
		$this->__construct( $thread_id, $order );
	}

	function __construct( $thread_id, $order ) {
		global $trs;

		$this->thread = new TRS_Messages_Thread( $thread_id, $order );
		$this->message_count = count( $this->thread->messages );

		$last_message_index = $this->message_count - 1;
		$this->thread->last_message_id = $this->thread->messages[$last_message_index]->id;
		$this->thread->last_message_date = $this->thread->messages[$last_message_index]->date_sent;
		$this->thread->last_sender_id = $this->thread->messages[$last_message_index]->sender_id;
		$this->thread->last_message_subject = $this->thread->messages[$last_message_index]->subject;
		$this->thread->last_message_content = $this->thread->messages[$last_message_index]->message;
	}

	function has_messages() {
		if ( $this->message_count )
			return true;

		return false;
	}

	function next_message() {
		$this->current_message++;
		$this->message = $this->thread->messages[$this->current_message];

		return $this->message;
	}

	function rewind_messages() {
		$this->current_message = -1;
		if ( $this->message_count > 0 ) {
			$this->message = $this->thread->messages[0];
		}
	}

	function messages() {
		if ( $this->current_message + 1 < $this->message_count ) {
			return true;
		} elseif ( $this->current_message + 1 == $this->message_count ) {
			do_action('thread_loop_end');
			// Do some cleaning up after the loop
			$this->rewind_messages();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_message() {
		global $message;

		$this->in_the_loop = true;
		$this->message = $this->next_message();

		if ( 0 == $this->current_message ) // loop has just started
			do_action('thread_loop_start');
	}
}

function trs_thread_has_messages( $args = '' ) {
	global $trs, $thread_template, $group_id;

	$defaults = array(
		'thread_id' => false,
		'order' => 'ASC'
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( !$thread_id && trs_is_messages_component() && trs_is_current_action( 'view' ) )
		$thread_id = (int)trs_action_variable( 0 );

	$thread_template = new TRS_Messages_Thread_Template( $thread_id, $order );
	return $thread_template->has_messages();
}

function trs_thread_messages_order() {
	echo trs_get_thread_messages_order();
}

	function trs_get_thread_messages_order() {
		global $thread_template;
		return $thread_template->thread->messages_order;
	}

function trs_thread_messages() {
	global $thread_template;

	return $thread_template->messages();
}

function trs_thread_the_message() {
	global $thread_template;

	return $thread_template->the_message();
}

function trs_the_thread_id() {
	echo trs_get_the_thread_id();
}
	function trs_get_the_thread_id() {
		global $thread_template;

		return apply_filters( 'trs_get_the_thread_id', $thread_template->thread->thread_id );
	}

function trs_the_thread_subject() {
	echo trs_get_the_thread_subject();
}
	function trs_get_the_thread_subject() {
		global $thread_template;

		return apply_filters( 'trs_get_the_thread_subject', $thread_template->thread->last_message_subject );
	}

function trs_the_thread_recipients() {
	echo trs_get_the_thread_recipients();
}
	function trs_get_the_thread_recipients() {
		global $thread_template, $trs;

		$recipient_links = array();

		if ( count( $thread_template->thread->recipients ) >= 5 )
			return apply_filters( 'trs_get_the_thread_recipients', sprintf( __( '%d Recipients', 'trendr' ), count($thread_template->thread->recipients) ) );

		foreach( (array)$thread_template->thread->recipients as $recipient ) {
			if ( $recipient->user_id !== $trs->loggedin_user->id )
				$recipient_links[] = trs_core_get_userlink( $recipient->user_id );
		}

		return apply_filters( 'trs_get_the_thread_recipients', implode( ', ', $recipient_links ) );
	}

function trs_the_thread_message_alt_class() {
	echo trs_get_the_thread_message_alt_class();
}
	function trs_get_the_thread_message_alt_class() {
		global $thread_template;

		if ( $thread_template->current_message % 2 == 1 )
			$class = ' alt';
		else
			$class = '';

		return apply_filters( 'trs_get_the_thread_message_alt_class', $class );
	}

function trs_the_thread_message_sender_portrait( $args = '' ) {
	echo trs_get_the_thread_message_sender_portrait_thumb( $args );
}
	function trs_get_the_thread_message_sender_portrait_thumb( $args = '' ) {
		global $thread_template;

		$defaults = array(
			'type' => 'thumb',
			'width' => false,
			'height' => false,
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		return apply_filters( 'trs_get_the_thread_message_sender_portrait_thumb', trs_core_fetch_portrait( array( 'item_id' => $thread_template->message->sender_id, 'type' => $type, 'width' => $width, 'height' => $height ) ) );
	}

function trs_the_thread_message_sender_link() {
	echo trs_get_the_thread_message_sender_link();
}
	function trs_get_the_thread_message_sender_link() {
		global $thread_template;

		return apply_filters( 'trs_get_the_thread_message_sender_link', trs_core_get_userlink( $thread_template->message->sender_id, false, true ) );
	}

function trs_the_thread_message_sender_name() {
	echo trs_get_the_thread_message_sender_name();
}
	function trs_get_the_thread_message_sender_name() {
		global $thread_template;

		return apply_filters( 'trs_get_the_thread_message_sender_name', trs_core_get_user_displayname( $thread_template->message->sender_id ) );
	}

function trs_the_thread_delete_link() {
	echo trs_get_the_thread_delete_link();
}
	function trs_get_the_thread_delete_link() {
		global $trs;

		return apply_filters( 'trs_get_message_thread_delete_link', trm_nonce_url( $trs->loggedin_user->domain . $trs->messages->slug . '/inbox/delete/' . trs_get_the_thread_id(), 'messages_delete_thread' ) );
	}

function trs_the_thread_message_time_since() {
	echo trs_get_the_thread_message_time_since();
}
	function trs_get_the_thread_message_time_since() {
		global $thread_template;

		return apply_filters( 'trs_get_the_thread_message_time_since', sprintf( __( 'Sent %s', 'trendr' ), trs_core_time_since( strtotime( $thread_template->message->date_sent ) ) ) );
	}

function trs_the_thread_message_content() {
	echo trs_get_the_thread_message_content();
}
	function trs_get_the_thread_message_content() {
		global $thread_template;

		return apply_filters( 'trs_get_the_thread_message_content', $thread_template->message->message );
	}

/** Embeds *******************************************************************/

/**
 * Enable oembed support for Messages.
 *
 * There's no caching as TRS 1.5 does not have a Messages meta API.
 *
 * @see TRS_Embed
 * @since 1.5
 * @todo Add Messages meta?
 */
function trs_messages_embed() {
	add_filter( 'embed_post_id', 'trs_get_message_thread_id' );
}
add_action( 'messages_box_loop_start', 'trs_messages_embed' );
?>
