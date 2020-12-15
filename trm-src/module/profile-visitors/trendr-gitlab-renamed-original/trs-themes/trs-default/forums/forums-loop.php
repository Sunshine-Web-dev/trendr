<?php

/**
 * trendr - Forums Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - trs_dtheme_object_filter()
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php do_action( 'trs_before_forums_loop' ); ?>

<?php if ( trs_has_forum_topics( trs_ajax_querystring( 'forums' ) ) ) : ?>

	<div id="pag-top" class="pagination">

		<div class="pag-count" id="topic-count-top">

			<?php trs_forum_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="topic-pag-top">

			<?php trs_forum_pagination(); ?>

		</div>

	</div>

	<?php do_action( 'trs_before_directory_forums_list' ); ?>

	<table class="forum">
		<thead>
			<tr>
				<th id="th-title"><?php _e( 'Topic', 'trendr' ); ?></th>
				<th id="th-postcount"><?php _e( 'Posts', 'trendr' ); ?></th>
				<th id="th-freshness"><?php _e( 'Freshness', 'trendr' ); ?></th>

				<?php do_action( 'trs_directory_forums_extra_cell_head' ); ?>

			</tr>
		</thead>

		<tbody>

			<?php while ( trs_forum_topics() ) : trs_the_forum_topic(); ?>

			<tr class="<?php trs_the_topic_css_class(); ?>">
				<td class="td-title">
					<a class="topic-title" href="<?php trs_the_topic_permalink(); ?>" title="<?php trs_the_topic_title(); ?> - <?php _e( 'Permalink', 'trendr' ); ?>">

						<?php trs_the_topic_title(); ?>

					</a>

					<p class="topic-meta">
						<span class="topic-by"><?php /* translators: "started by [poster] in [forum]" */ printf( __( 'Started by %1$s', 'trendr' ), trs_get_the_topic_poster_portrait( 'height=20&width=20') . trs_get_the_topic_poster_name() ); ?></span>

						<?php if ( !trs_is_group_forum() ) : ?>

							<span class="topic-in">

								<?php
									$topic_in = '<a href="' . trs_get_the_topic_object_permalink() . '">' . trs_get_the_topic_object_portrait( 'type=thumb&width=20&height=20' ) . '</a>' .
													'<a href="' . trs_get_the_topic_object_permalink() . '" title="' . trs_get_the_topic_object_name() . '">' . trs_get_the_topic_object_name() .'</a>';

									/* translators: "started by [poster] in [forum]" */
									printf( __( 'in %1$s', 'trendr' ), $topic_in );
								?>

							</span>

						<?php endif; ?>

					</p>
				</td>
				<td class="td-postcount">
					<?php trs_the_topic_total_posts(); ?>
				</td>
				<td class="td-freshness">
					<span class="time-since"><?php trs_the_topic_time_since_last_post(); ?></span>
					<p class="topic-meta">
						<span class="freshness-author">
							<a href="<?php trs_the_topic_permalink(); ?>"><?php trs_the_topic_last_poster_portrait( 'type=thumb&width=20&height=20' ); ?></a>
							<?php trs_the_topic_last_poster_name(); ?>
						</span>
					</p>
				</td>

				<?php do_action( 'trs_directory_forums_extra_cell' ); ?>

			</tr>

			<?php do_action( 'trs_directory_forums_extra_row' ); ?>

			<?php endwhile; ?>

		</tbody>
	</table>

	<?php do_action( 'trs_after_directory_forums_list' ); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="topic-count-bottom">
			<?php trs_forum_pagination_count(); ?>
		</div>

		<div class="pagination-links" id="topic-pag-bottom">
			<?php trs_forum_pagination(); ?>
		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, there were no forum topics found.', 'trendr' ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'trs_after_forums_loop' ); ?>
