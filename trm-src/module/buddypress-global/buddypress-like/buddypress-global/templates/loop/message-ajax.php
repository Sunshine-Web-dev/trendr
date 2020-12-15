<?php global $current_message; ?>
<div class="bboss_ajax_search_item bboss_ajax_search_item_ajax">
	<a href='<?php echo esc_url( trailingslashit( trs_loggedin_user_domain() ) ) . 'messages/view/' . $current_message->thread_id . '/';?>'>
		<div class="item">
			<div class="item-title">
				<?php echo stripslashes( trm_strip_all_tags( $current_message->subject ) );?>
			</div>
			<div class="item-desc">
				<?php _e( 'From:', 'trendr-global-search' ); ?> <?php echo trs_core_get_user_displayname($current_message->sender_id); ?>
			</div>
		</div>
	</a>
</div>