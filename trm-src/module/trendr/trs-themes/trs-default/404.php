<?php get_header() ?>

	<div id="skeleton">
		<div class="padder one-column">
			<?php do_action( 'trs_before_404' ); ?>
			<div id="post-0" class="post page-404 error404 not-found" role="main">
				<h2 class="posttitle"><?php _e( "Page not found", 'trendr' ); ?></h2>

				<p><?php _e( "We're sorry, but we can't find the page that you're looking for. Perhaps searching will help.", 'trendr' ); ?></p>
				<?php get_search_form(); ?>

				<?php do_action( 'trs_404' ); ?>
			</div>

			<?php do_action( 'trs_after_404' ) ?>
		</div><!-- .padder -->
	</div><!-- #skeleton -->

<?php get_footer() ?>