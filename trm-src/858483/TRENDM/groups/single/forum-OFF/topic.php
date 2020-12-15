<?php do_action( 'trs_before_group_forum_topic' ); ?>

<?php if ( trs_has_forum_topic_posts() ) : ?>

	<form action="<?php trs_forum_topic_action() ?>" method="post" id="forum-topic-form" class="standard-form">

		<div class="contour-select no-ajax" id="contour-box" role="navigation">
			<ul>
				<?php if ( is_user_logged_in() ) : ?>

					<li>
						<a href="<?php trs_forum_topic_new_reply_link() ?>" class="new-reply-link"><?php _e( 'New Reply', 'trendr' ) ?></a>
					</li>

				<?php endif; ?>

				<?php if ( trs_forums_has_directory() ) : ?>

					<li>
						<a href="<?php trs_forums_directory_permalink() ?>"><?php _e( 'Forum Directory', 'trendr') ?></a>
					</li>

				<?php endif; ?>

			</ul>
		</div>

		<div id="topic-meta">
			<h3><?php trs_the_topic_title() ?> (<?php trs_the_topic_total_post_count() ?>)</h3>

			<?php if ( trs_forum_topic_has_tags() ) : ?>

				<div class="topic-tags">

					<?php _e( 'Topic tags:', 'trendr' ) ?> <?php trs_forum_topic_tag_list() ?>

				</div>

			<?php endif; ?>

			<?php if ( trs_group_is_admin() || trs_group_is_mod() || trs_get_the_topic_is_mine() ) : ?>

				<div class="last admin-links">

					<?php trs_the_topic_admin_links() ?>

				</div>

			<?php endif; ?>

			<?php do_action( 'trs_group_forum_topic_meta' ); ?>

		</div>

		<div class="pagination no-ajax">

			<div id="post-count-top" class="pag-count">

				<?php trs_the_topic_pagination_count() ?>

			</div>

			<div class="pagination-links" id="topic-pag-top">

				<?php trs_the_topic_pagination() ?>

			</div>

		</div>

		<?php do_action( 'trs_before_group_forum_topic_posts' ) ?>

		<ul id="topic-post-list" class="article-piece" role="main">
			<?php while ( trs_forum_topic_posts() ) : trs_the_forum_topic_post(); ?>

				<li id="post-<?php trs_the_topic_post_id() ?>" class="<?php trs_the_topic_post_css_class() ?>">
					<div class="poster-meta">
						<a href="<?php trs_the_topic_post_poster_link() ?>">
							<?php trs_the_topic_post_poster_portrait( 'width=40&height=40' ) ?>
						</a>
						<?php echo sprintf( __( '%1$s said %2$s:', 'trendr' ), trs_get_the_topic_post_poster_name(), trs_get_the_topic_post_time_since() ) ?>
					</div>

					<div class="post-content">
						<?php trs_the_topic_post_content() ?>
					</div>

					<div class="admin-links">
						<?php if ( trs_group_is_admin() || trs_group_is_mod() || trs_get_the_topic_post_is_mine() ) : ?>
							<?php trs_the_topic_post_admin_links() ?>
						<?php endif; ?>

						<?php do_action( 'trs_group_forum_post_meta' ); ?>

						<a href="#post-<?php trs_the_topic_post_id() ?>" title="<?php _e( 'Permanent link to this post', 'trendr' ) ?>">#</a>
					</div>
				</li>

			<?php endwhile; ?>
		</ul><!-- #topic-post-list -->

		<?php do_action( 'trs_after_group_forum_topic_posts' ) ?>

		<div class="pagination no-ajax">

			<div id="post-count-bottom" class="pag-count">
				<?php trs_the_topic_pagination_count() ?>
			</div>

			<div class="pagination-links" id="topic-pag-bottom">
				<?php trs_the_topic_pagination() ?>
			</div>

		</div>

		<?php if ( ( is_user_logged_in() && 'public' == trs_get_group_status() ) || trs_group_is_member() ) : ?>

			<?php if ( trs_get_the_topic_is_last_page() ) : ?>

				<?php if ( trs_get_the_topic_is_topic_open() && !trs_group_is_user_banned() ) : ?>

					<div id="post-topic-reply">
						<p id="post-reply"></p>

						<?php if ( trs_groups_auto_join() && !trs_group_is_member() ) : ?>
							<p><?php _e( 'You will auto join this group when you reply to this topic.', 'trendr' ) ?></p>
						<?php endif; ?>

						<?php do_action( 'groups_forum_new_reply_before' ) ?>

						<h4><?php _e( 'Add a reply:', 'trendr' ) ?></h4>

						<textarea name="reply_text" id="reply_text"></textarea>

						<div class="submit">
							<input type="submit" name="submit_reply" id="submit" value="<?php _e( 'Post Reply', 'trendr' ) ?>" />
						</div>

						<?php do_action( 'groups_forum_new_reply_after' ) ?>

						<?php trm_nonce_field( 'trs_forums_new_reply' ) ?>
					</div>

				<?php elseif ( !trs_group_is_user_banned() ) : ?>

					<div id="message" class="info">
						<p><?php _e( 'This topic is closed, replies are no longer accepted.', 'trendr' ) ?></p>
					</div>

				<?php endif; ?>

			<?php endif; ?>

		<?php endif; ?>

	</form><!-- #forum-topic-form -->

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'There are no posts for this topic.', 'trendr' ) ?></p>
	</div>

<?php endif;?>

<?php do_action( 'trs_after_group_forum_topic' ) ?>
