<?php

/**
 * trendr - Users Plugins
 *
 * This is a fallback file that external plugins can use if the template they
 * need is not installed in the current theme. Use the actions in this template
 * to output everything your plugin needs.
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php get_header( 'trendr' ); ?>

	<div id="skeleton"">
		<div class="dimension">

			<?php do_action( 'trs_before_member_plugin_template' ); ?>

			<div id="contour">


			</div><!-- #contour -->

			<div id="contour-n">
				<div class="contour-select no-ajax" id="object-c" role="navigation">
				
				</div>
			</div><!-- #contour-n-->

			<div id="figure" role="main">

				<?php do_action( 'trs_before_member_body' ); ?>

				<div class="contour-select no-ajax" id="contour-box">
					<ul>

						<?php trs_get_options_nav(); ?>

						<?php do_action( 'trs_member_plugin_options_nav' ); ?>

					</ul>
				</div><!-- .contour-select -->

				<h3><?php do_action( 'trs_template_title' ); ?></h3>

				<?php do_action( 'trs_template_content' ); ?>

				<?php do_action( 'trs_after_member_body' ); ?>

			</div><!-- #figure -->

			<?php do_action( 'trs_after_member_plugin_template' ); ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->


<?php get_footer( 'trendr' ); ?>
