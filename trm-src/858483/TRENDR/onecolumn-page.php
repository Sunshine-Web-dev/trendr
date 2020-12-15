<?php
/*
 * Template Name: One column, no sidebar
 *
 * A custom page template without sidebar.
 *
 * @package trendr
 * @sutrsackage TRS_Default
 * @since 1.5
 */

get_header() ?>

	<div id="skeleton"">
		<div class="padder one-column">

		<?php do_action( 'trs_before_blog_page' ) ?>

		<div class="page" id="static-page" role="main">

			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

				<h2 class="pagetitle"><?php the_title(); ?></h2>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="entry">

						<?php the_content( __( '<p class="serif">Read the rest of this page &rarr;</p>', 'trendr' ) ); ?>

						<?php trm_link_pages( array( 'before' => '<div class="page-link"><p>' . __( 'Pages: ', 'trendr' ), 'after' => '</p></div>', 'next_or_number' => 'number' ) ); ?>
						<?php edit_post_link( __( 'Edit this page.', 'trendr' ), '<p class="edit-link">', '</p>'); ?>

					</div>

				</div>

			<?php comments_template(); ?>

			<?php endwhile; endif; ?>

		</div><!-- .page -->

		<?php do_action( 'trs_after_blog_page' ) ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

<?php get_footer(); ?>