<?php get_header( 'trendr' ) ?>

	<div id="skeleton">
		<div class="dimension">
			<?php if ( trs_has_groups() ) : while ( trs_groups() ) : trs_the_group(); ?>

			<?php do_action( 'trs_before_group_plugin_template' ) ?>

			<div id="item-header">
				<?php locate_template( array( 'groups/single/group-header.php' ), true ) ?>
			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="contour-select no-ajax" id="object-nav" role="navigation">
					<ul>
						<?php trs_get_options_nav() ?>

						<?php do_action( 'trs_group_plugin_options_nav' ) ?>
					</ul>
				</div>
			</div><!-- #item-nav -->

			<div id="item-body">

				<?php do_action( 'trs_before_group_body' ) ?>

				<?php do_action( 'trs_template_content' ) ?>

				<?php do_action( 'trs_after_group_body' ) ?>
			</div><!-- #item-body -->

			<?php do_action( 'trs_after_group_plugin_template' ) ?>

			<?php endwhile; endif; ?>

		</div><!-- .padder -->
	</div><!-- #skeleton -->

	<?php get_sidebar( 'trendr' ) ?>

<?php get_footer( 'trendr' ) ?>