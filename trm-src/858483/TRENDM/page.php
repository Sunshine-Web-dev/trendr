	
<?php get_header() ?>
	<div id="skeleton"">
		<div class="dimension">

		<?php do_action( 'trs_before_blog_page' ) ?>

		<div class="page" id="static-page" role="main">

			<?php if (have_posts()) : while (have_posts()) : the_post(); ?>


				<div id="post-<?php the_ID(); ?>" >

					<div class="entry">

						<?php the_content( __( '<p class="serif">Read the rest of this page &rarr;</p>', 'trendr' ) ); ?>

					
					</div>

				</div>


			<?php endwhile; endif; ?>

		</div><!-- .page -->

		<?php do_action( 'trs_after_blog_page' ) ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->


	

<?php get_footer(); ?>
