<div id="message-thread" role="main">

	<?php do_action( 'trs_before_message_thread_content' ) ?>

	<?php if ( trs_thread_has_messages() ) : ?>

		<h3 id="message-subject"><?php trs_the_thread_subject() ?></h3>

		<p id="message-recipients">
			<span class="highlight">

				<?php if ( !trs_get_the_thread_recipients() ) : ?>

					<?php _e( 'You are alone in this conversation.', 'trendr' ); ?>

				<?php else : ?>

					<?php printf( __( 'Conversation between %s and you.', 'trendr' ), trs_get_the_thread_recipients() ) ?>

				<?php endif; ?>

			</span>

			<a class="button confirm" href="<?php trs_the_thread_delete_link() ?>" title="<?php _e( "Delete Message", "trendr" ); ?>"><?php _e( 'Delete', 'trendr' ) ?></a> &nbsp;
		</p>

		<?php do_action( 'trs_before_message_thread_list' ) ?>

		<?php while ( trs_thread_messages() ) : trs_thread_the_message(); ?>

			<div class="message-box <?php trs_the_thread_message_alt_class(); ?>">

				<div class="message-metadata">

					<?php do_action( 'trs_before_message_meta' ) ?>

					<?php trs_the_thread_message_sender_portrait( 'type=thumb&width=30&height=30' ) ?>
					<strong><a href="<?php trs_the_thread_message_sender_link() ?>" title="<?php trs_the_thread_message_sender_name() ?>"><?php trs_the_thread_message_sender_name() ?></a> <span class="activity"><?php trs_the_thread_message_time_since() ?></span></strong>

					<?php do_action( 'trs_after_message_meta' ) ?>

				</div><!-- .message-metadata -->

				<?php do_action( 'trs_before_message_content' ) ?>

				<div class="message-content">

					<?php trs_the_thread_message_content() ?>

				</div><!-- .message-content -->

				<?php do_action( 'trs_after_message_content' ) ?>

				<div class="clear"></div>

			</div><!-- .message-box -->

		<?php endwhile; ?>

		<?php do_action( 'trs_after_message_thread_list' ) ?>

		<?php do_action( 'trs_before_message_thread_reply' ) ?>

		<form id="send-reply" action="<?php trs_messages_form_action() ?>" method="post" class="standard-form">

			<div class="message-box">

				<div class="message-metadata">

					<?php do_action( 'trs_before_message_meta' ) ?>

					<div class="portrait-box">
						<?php trs_loggedin_user_portrait( 'type=thumb&height=30&width=30' ) ?>

						<strong><?php _e( 'Send a Reply', 'trendr' ) ?></strong>
					</div>

					<?php do_action( 'trs_after_message_meta' ) ?>

				</div><!-- .message-metadata -->

				<div class="message-content">

					<?php do_action( 'trs_before_message_reply_box' ) ?>

					<textarea name="skeleton" id="message_content" rows="15" cols="40"></textarea>

					<?php do_action( 'trs_after_message_reply_box' ) ?>

					<div class="submit">
						<input type="submit" name="send" value="<?php _e( 'Send Reply', 'trendr' ) ?>" id="send_reply_button"/>
					</div>

					<input type="hidden" id="thread_id" name="thread_id" value="<?php trs_the_thread_id(); ?>" />
					<input type="hidden" id="messages_order" name="messages_order" value="<?php trs_thread_messages_order(); ?>" />
					<?php trm_nonce_field( 'messages_send_message', 'send_message_nonce' ) ?>

				</div><!-- .message-content -->

			</div><!-- .message-box -->

		</form><!-- #send-reply -->

		<?php do_action( 'trs_after_message_thread_reply' ) ?>

	<?php endif; ?>

	<?php do_action( 'trs_after_message_thread_content' ) ?>

</div>