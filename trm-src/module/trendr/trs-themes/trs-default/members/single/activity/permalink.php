<?php get_header( 'trendr' ) ?>

<div class="activity no-ajax" role="main">
	<?php if ( trs_has_activities( 'display_comments=threaded&show_hidden=true&include=' . trs_current_action() ) ) : ?>

		<ul id="publish" class="publish-piece article-piece">
		<?php while ( trs_activities() ) : trs_the_activity(); ?>

			<?php locate_template( array( 'activity/entry.php' ), true ) ?>

		<?php endwhile; ?>
		</ul>

	<?php endif; ?>
</div>

<?php get_footer( 'trendr' ) ?>