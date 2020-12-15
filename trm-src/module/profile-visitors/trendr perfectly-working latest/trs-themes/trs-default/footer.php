		</div> <!-- #container -->

		<?php do_action( 'trs_after_container' ) ?>
		<?php do_action( 'trs_before_footer' ) ?>

		<div id="footer">
			<?php if ( is_active_sidebar( 'first-footer-widget-area' ) || is_active_sidebar( 'second-footer-widget-area' ) || is_active_sidebar( 'third-footer-widget-area' ) || is_active_sidebar( 'fourth-footer-widget-area' ) ) : ?>
				<div id="footer-widgets">
					<?php get_sidebar( 'footer' ) ?>
				</div>
			<?php endif; ?>

			<div id="site-generator" role="contentinfo">
				<?php do_action( 'trs_dtheme_credits' ) ?>
				<p><?php printf( __( 'Proudly powered by <a href="%1$s">WordPress</a> and <a href="%2$s">trendr</a>.', 'trendr' ), 'http://wordpress.org', 'http://trendr.org' ) ?></p>
			</div>

			<?php do_action( 'trs_footer' ) ?>
		</div><!-- #footer -->

		<?php do_action( 'trs_after_footer' ) ?>

		<?php trm_footer(); ?>

	</body>

</html>