<form action="<?php trs_messages_form_action('compose') ?>" method="post" id="send_message_form" class="standard-form" role="main">

	<?php do_action( 'trs_before_messages_compose_content' ) ?>

	<label for="send-to-input"><?php _e("Send To (Username or Friend's Name)", 'trendr') ?></label>
	<ul class="first acfb-holder">
		<li>
			<?php trs_message_get_recipient_tabs() ?>
			<input type="text" name="send-to-input" class="send-to-input" id="send-to-input" />
		</li>
	</ul>

	<?php if ( is_super_admin() ) : ?>
		<input type="checkbox" id="send-notice" name="send-notice" value="1" /> <?php _e( "This is a notice to all users.", "trendr" ) ?>
	<?php endif; ?>

	<label for="subject"><?php _e( 'Subject', 'trendr') ?></label>
	<input type="text" name="subject" id="subject" value="<?php trs_messages_subject_value() ?>" />

	<label for="skeleton"><?php _e( 'Message', 'trendr') ?></label>
	<textarea name="skeleton" id="message_content" rows="15" cols="40"><?php trs_messages_content_value() ?></textarea>

	<input type="hidden" name="send_to_usernames" id="send-to-usernames" value="<?php trs_message_get_recipient_usernames(); ?>" class="<?php trs_message_get_recipient_usernames() ?>" />

	<?php do_action( 'trs_after_messages_compose_content' ) ?>

	<div class="submit">
		<input type="submit" value="<?php _e( "Send Message", 'trendr' ) ?>" name="send" id="send" />
	</div>

	<?php trm_nonce_field( 'messages_send_message' ) ?>
</form>

<script type="text/javascript">
	document.getElementById("send-to-input").focus();
</script>

