<?php

/**
 * trendr - Activity Stream Comment
 *
 * This template is used by trs_activity_comments() functions to show
 * each activity.
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php do_action( 'trs_before_activity_comment' ); ?>

<li id="acomment-<?php trs_activity_comment_id(); ?>">
	<div class="acomment-portrait">
		<a href="<?php trs_activity_comment_user_link(); ?>">
			<?php trs_activity_portrait( 'type=full&user_id=' . trs_get_activity_comment_user_id() ); ?>
		</a>
	</div>

	<div class="acomment-meta">
		<?php
		/* translators: 1: user profile link, 2: user name, 3: activity permalink, 3: activity timestamp */
		printf( __( '<a href="%1$s">%2$s</a>, commented <a href="%3$s" class="expand"><span class="time">%4$s</span></a>', 'trendr' ), trs_get_activity_comment_user_link(), trs_get_activity_comment_name(), trs_get_activity_thread_permalink(), trs_get_activity_comment_date_recorded() );
		?>
	</div>

	<div class="acomment-content"><?php trs_activity_comment_content(); ?></div>

	<div class="acomment-options">

		<?php if ( is_user_logged_in() && trs_activity_can_comment_reply( trs_activity_current_comment() ) ) : ?>

			<a href="#acomment-<?php trs_activity_comment_id(); ?>" class="acomment-reply main" id="acomment-reply-<?php trs_activity_id() ?>-from-<?php trs_activity_comment_id() ?>"><?php _e( 'Reply', 'trendr' ); ?></a>

		<?php endif; ?>

		<?php if ( trs_activity_user_can_delete() ) : ?>

			<a href="<?php trs_activity_comment_delete_link(); ?>" class="delete acomment-delete confirm trs-secondary-action" rel="nofollow"><?php _e( 'Delete', 'trendr' ); ?></a>

		<?php endif; ?>

	</div>

	<?php trs_activity_recurse_comments( trs_activity_current_comment() ); ?>
</li>

<?php do_action( 'trs_after_activity_comment' ); ?>
