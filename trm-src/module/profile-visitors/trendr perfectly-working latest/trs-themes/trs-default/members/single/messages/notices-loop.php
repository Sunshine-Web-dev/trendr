<?php do_action( 'trs_before_notices_loop' ) ?>

<?php if ( trs_has_message_threads() ) : ?>

	<div class="pagination" id="user-pag">

		<div class="pag-count" id="messages-dir-count">
			<?php trs_messages_pagination_count() ?>
		</div>

		<div class="pagination-links" id="messages-dir-pag">
			<?php trs_messages_pagination() ?>
		</div>

	</div><!-- .pagination -->

	<?php do_action( 'trs_after_notices_pagination' ) ?>
	<?php do_action( 'trs_before_notices' ) ?>

	<table id="message-threads" class="messages-notices">
		<?php while ( trs_message_threads() ) : trs_message_thread(); ?>
			<tr id="notice-<?php trs_message_notice_id() ?>" class="<?php trs_message_css_class(); ?>">
				<td width="1%">
				</td>
				<td width="38%">
					<strong><?php trs_message_notice_subject() ?></strong>
					<?php trs_message_notice_text() ?>
				</td>
				<td width="21%">
					<strong><?php trs_message_is_active_notice() ?></strong>
					<span class="activity"><?php _e("Sent:", "trendr"); ?> <?php trs_message_notice_post_date() ?></span>
				</td>

				<?php do_action( 'trs_notices_list_item' ) ?>

				<td width="10%">
					<a class="button" href="<?php trs_message_activate_deactivate_link() ?>" class="confirm"><?php trs_message_activate_deactivate_text() ?></a>
					<a class="button" href="<?php trs_message_notice_delete_link() ?>" class="confirm" title="<?php _e( "Delete Message", "trendr" ); ?>">x</a>
				</td>
			</tr>
		<?php endwhile; ?>
	</table><!-- #message-threads -->

	<?php do_action( 'trs_after_notices' ) ?>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, no notices were found.', 'trendr' ); ?></p>
	</div>

<?php endif;?>

<?php do_action( 'trs_after_notices_loop' ) ?>