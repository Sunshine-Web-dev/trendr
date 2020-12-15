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

	<div id="skeleton">
		<div class="dimension">

			<?php do_action( 'trs_before_member_plugin_template' ); ?>

			<div id="item-header">

				<?php locate_template( array( 'members/single/member-header.php' ), true ); ?>

			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="contour-select no-ajax" id="object-c" role="navigation">
					<ul>

						<?php trs_get_displayed_user_nav(); ?>

						<?php do_action( 'trs_member_options_nav' ); ?>

					</ul>
				</div>
			</div><!-- #item-nav -->

			<div id="item-body" role="main">

				<?php do_action( 'trs_before_member_body' ); ?>

				<div class="contour-select no-ajax" id="subnav">
					<ul>

						<?php trs_get_options_nav(); ?>

						<?php do_action( 'trs_member_plugin_options_nav' ); ?>

					</ul>
				</div><!-- .contour-select -->

				<h3><?php do_action( 'trs_template_title' ); ?></h3>

				<?php do_action( 'trs_template_content' ); ?>

				<?php do_action( 'trs_after_member_body' ); ?>

			</div><!-- #item-body -->

			<?php do_action( 'trs_after_member_plugin_template' ); ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

<?php get_sidebar( 'trendr' ); ?>
<?php get_footer( 'trendr' ); ?>
