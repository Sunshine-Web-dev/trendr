<?php

/**
 * trendr - Blogs Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - trs_dtheme_object_filter()
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php do_action( 'trs_before_blogs_loop' ); ?>

<?php if ( trs_has_blogs( trs_ajax_querystring( 'blogs' ) ) ) : ?>

	<div id="pag-top" class="pagination">

		<div class="pag-count" id="blog-dir-count-top">
			<?php trs_blogs_pagination_count(); ?>
		</div>

		<div class="pagination-links" id="blog-dir-pag-top">
			<?php trs_blogs_pagination_links(); ?>
		</div>

	</div>

	<?php do_action( 'trs_before_directory_blogs_list' ); ?>

	<ul id="blogs-list" class="article-piece" role="main">

	<?php while ( trs_blogs() ) : trs_the_blog(); ?>

		<li>
			<div class="item-portrait">
				<a href="<?php trs_blog_permalink(); ?>"><?php trs_blog_portrait( 'type=thumb' ); ?></a>
			</div>

			<div class="item">
				<div class="item-title"><a href="<?php trs_blog_permalink(); ?>"><?php trs_blog_name(); ?></a></div>
				<div class="item-meta"><span class="activity"><?php trs_blog_last_active(); ?></span></div>

				<?php do_action( 'trs_directory_blogs_item' ); ?>
			</div>

			<div class="action">

				<?php do_action( 'trs_directory_blogs_actions' ); ?>

				<div class="meta">

					<?php trs_blog_latest_post(); ?>

				</div>

			</div>

			<div class="clear"></div>
		</li>

	<?php endwhile; ?>

	</ul>

	<?php do_action( 'trs_after_directory_blogs_list' ); ?>

	<?php trs_blog_hidden_fields(); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="blog-dir-count-bottom">

			<?php trs_blogs_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="blog-dir-pag-bottom">

			<?php trs_blogs_pagination_links(); ?>

		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, there were no sites found.', 'trendr' ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'trs_after_blogs_loop' ); ?>
