<?php do_action( 'trs_before_group_forum_edit_form' ); ?>

<?php if ( trs_has_forum_topic_posts() ) : ?>

	<form action="<?php trs_forum_topic_action(); ?>" method="post" id="forum-topic-form" class="standard-form">

		<div class="contour-select" id="contour-box" role="navigation">
			<ul>
				<li>
					<a href="#post-topic-reply"><?php _e( 'Reply', 'trendr' ); ?></a>
				</li>

				<?php if ( trs_forums_has_directory() ) : ?>

					<li>
						<a href="<?php trs_forums_directory_permalink(); ?>"><?php _e( 'Forum Directory', 'trendr'); ?></a>
					</li>

				<?php endif; ?>

			</ul>
		</div>

		<div id="topic-meta">
			<h3><?php _e( 'Edit:', 'trendr' ); ?> <?php trs_the_topic_title(); ?> (<?php trs_the_topic_total_post_count(); ?>)</h3>

			<?php if ( trs_group_is_admin() || trs_group_is_mod() || trs_get_the_topic_is_mine() ) : ?>

				<div class="last admin-links">

					<?php trs_the_topic_admin_links(); ?>

				</div>

			<?php endif; ?>

			<?php do_action( 'trs_group_forum_topic_meta' ); ?>

		</div>

		<?php if ( trs_group_is_member() ) : ?>

			<?php if ( trs_is_edit_topic() ) : ?>

				<div id="edit-topic">

					<?php do_action( 'trs_group_before_edit_forum_topic' ); ?>

					<label for="topic_title"><?php _e( 'Title:', 'trendr' ); ?></label>
					<input type="text" name="topic_title" id="topic_title" value="<?php trs_the_topic_title(); ?>" />

					<label for="topic_text"><?php _e( 'Content:', 'trendr' ); ?></label>
					<textarea name="topic_text" id="topic_text"><?php trs_the_topic_text(); ?></textarea>

					<label><?php _e( 'Tags (comma separated):', 'trendr' ) ?></label>
					<input type="text" name="topic_tags" id="topic_tags" value="<?php trs_forum_topic_tag_list() ?>" />

					<?php do_action( 'trs_group_after_edit_forum_topic' ); ?>

					<p class="submit"><input type="submit" name="save_changes" id="save_changes" value="<?php _e( 'Save Changes', 'trendr' ); ?>" /></p>

					<?php trm_nonce_field( 'trs_forums_edit_topic' ); ?>

				</div>

			<?php else : ?>

				<div id="edit-post">

					<?php do_action( 'trs_group_before_edit_forum_post' ); ?>

					<textarea name="post_text" id="post_text"><?php trs_the_topic_post_edit_text(); ?></textarea>

					<?php do_action( 'trs_group_after_edit_forum_post' ) ?>

					<p class="submit"><input type="submit" name="save_changes" id="save_changes" value="<?php _e( 'Save Changes', 'trendr' ); ?>" /></p>

					<?php trm_nonce_field( 'trs_forums_edit_post' ); ?>

				</div>

			<?php endif; ?>

		<?php endif; ?>

	</form><!-- #forum-topic-form -->

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'This topic does not exist.', 'trendr' ); ?></p>
	</div>

<?php endif;?>

<?php do_action( 'trs_after_group_forum_edit_form' ); ?>
