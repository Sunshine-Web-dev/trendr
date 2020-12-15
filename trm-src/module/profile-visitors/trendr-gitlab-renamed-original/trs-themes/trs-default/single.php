<?php get_header() ?>

	<div id="skeleton">
		<div class="dimension">

			<?php do_action( 'trs_before_blog_single_post' ) ?>

			<div class="page" id="blog-single" role="main">

			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="author-box">
			
					</div>

					<div class="post-content">
						<h2 class="posttitle"><?php the_title(); ?></h2>

						<p class="date">
							<?php printf( __( '%1$s <span>in %2$s</span>', 'trendr' ), get_the_date(), get_the_category_list( ', ' ) ); ?>
							<span class="post-utility alignright"><?php edit_post_link( __( 'Edit this entry', 'trendr' ) ); ?></span>
						</p>

						<div class="entry">
							<?php the_content( __( 'Read the rest of this entry &rarr;', 'trendr' ) ); ?>

							<?php trm_link_pages( array( 'before' => '<div class="page-link"><p>' . __( 'Pages: ', 'trendr' ), 'after' => '</p></div>', 'next_or_number' => 'number' ) ); ?>
						</div>

						<p class="postmetadata"><?php the_tags( '<span class="tags">' . __( 'Tags: ', 'trendr' ), ', ', '</span>' ); ?>&nbsp;</p>

						<div class="alignleft"><?php previous_post_link( '%link', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'trendr' ) . '</span> %title' ); ?></div>
						<div class="alignright"><?php next_post_link( '%link', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'trendr' ) . '</span>' ); ?></div>
					</div>

				</div>


			<?php endwhile; else: ?>

				<p><?php _e( 'Sorry, no posts matched your criteria.', 'trendr' ) ?></p>

			<?php endif; ?>

		</div>

		<?php do_action( 'trs_after_blog_single_post' ) ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

	<?php get_sidebar() ?>

<?php get_footer() ?>