<?php

/**
 * BuddyPress - Activity Stream (Single Item)
 *
 * This template is used by activity-loop.php and AJAX functions to show
 * each activity.
 *
 * @package BuddyPress
 * @sutrsackage trs-default
 */

?>

<?php do_action( 'trs_before_activity_entry' ); ?>

<li class="<?php trs_activity_css_class(); ?>" id="activity-<?php trs_activity_id(); ?>">
	<div class="activity-avatar">
		<a href="<?php trs_activity_user_link(); ?>">

			<?php trs_activity_avatar(); ?>

		</a>
	</div>

	<div class="activity-content">

		<div class="activity-header">

			<?php trs_activity_action(); ?>

		</div>

		<?php if ( 'activity_comment' == trs_get_activity_type() ) : ?>

			<div class="activity-inreplyto">
				<strong><?php _e( 'In reply to: ', 'trendr' ); ?></strong><?php trs_activity_parent_content(); ?> <a href="<?php trs_activity_thread_permalink(); ?>" class="view" title="<?php _e( 'View Thread / Permalink', 'trendr' ); ?>"><?php _e( 'View', 'trendr' ); ?></a>
			</div>

		<?php endif; ?>

		<?php if ( trs_activity_has_content() ) : ?>

			<div class="activity-inner">

				<?php trs_activity_content_body(); ?>

			</div>

		<?php endif; ?>

		<?php do_action( 'trs_activity_entry_content' ); ?>

		<?php if ( is_user_logged_in() ) : ?>

			<div class="activity-meta">

				<?php if ( trs_activity_can_comment() ) : ?>

					<a href="<?php trs_get_activity_comment_link(); ?>" class="button acomment-reply trs-primary-action" id="acomment-comment-<?php trs_activity_id(); ?>"><?php printf( __( 'Comment <span>%s</span>', 'trendr' ), trs_activity_get_comment_count() ); ?></a>

				<?php endif; ?>

				<?php if ( trs_activity_can_favorite() ) : ?>

					<?php if ( !trs_get_activity_is_favorite() ) : ?>

						<a href="<?php trs_activity_favorite_link(); ?>" class="button fav trs-secondary-action" title="<?php esc_attr_e( 'Mark as Favorite', 'trendr' ); ?>"><?php _e( 'Favorite', 'trendr' ) ?></a>

					<?php else : ?>

						<a href="<?php trs_activity_unfavorite_link(); ?>" class="button unfav trs-secondary-action" title="<?php esc_attr_e( 'Remove Favorite', 'trendr' ); ?>"><?php _e( 'Remove Favorite', 'trendr' ) ?></a>

					<?php endif; ?>

				<?php endif; ?>

				<?php if ( trs_activity_user_can_delete() ) trs_activity_delete_link(); ?>

				<?php do_action( 'trs_activity_entry_meta' ); ?>

			</div>

		<?php endif; ?>

	</div>

	<?php do_action( 'trs_before_activity_entry_comments' ); ?>

	<?php if ( ( is_user_logged_in() && trs_activity_can_comment() ) || trs_activity_get_comment_count() ) : ?>

		<div class="activity-comments">

			<?php trs_activity_comments(); ?>

			<?php if ( is_user_logged_in() ) : ?>

				<form action="<?php trs_activity_comment_form_action(); ?>" method="post" id="ac-form-<?php trs_activity_id(); ?>" class="ac-form"<?php trs_activity_comment_form_nojs_display(); ?>>
					<div class="ac-reply-avatar"><?php trs_loggedin_user_avatar( 'width=' . TRS_AVATAR_THUMB_WIDTH . '&height=' . TRS_AVATAR_THUMB_HEIGHT ); ?></div>
					<div class="ac-reply-content">
						<div class="ac-textarea">
							<textarea id="ac-input-<?php trs_activity_id(); ?>" class="ac-input" name="ac_input_<?php trs_activity_id(); ?>"></textarea>
						</div>
						<input type="submit" name="ac_form_submit" value="<?php _e( 'Post', 'trendr' ); ?>" /> &nbsp; <?php _e( 'or press esc to cancel.', 'trendr' ); ?>
						<input type="hidden" name="comment_form_id" value="<?php trs_activity_id(); ?>" />
					</div>

					<?php do_action( 'trs_activity_entry_comments' ); ?>

					<?php trm_nonce_field( 'new_activity_comment', '_key_new_activity_comment' ); ?>

				</form>

			<?php endif; ?>

		</div>

	<?php endif; ?>

	<?php do_action( 'trs_after_activity_entry_comments' ); ?>

</li>

<?php do_action( 'trs_after_activity_entry' ); ?>
