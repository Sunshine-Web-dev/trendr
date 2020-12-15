		</div> <!-- #container -->

		<?php do_action( 'trs_after_container' ) ?>
		<?php do_action( 'trs_before_footer' ) ?>


			<div id="site-generator" role="contentinfo">
				<?php do_action( 'trs_dtheme_credits' ) ?>
				<p><?php printf( __( 'Proudly powered by <a href="%1$s">trendr</a> and <a href="%2$s">trendr</a>.', 'trendr' ), 'http://trendr.org', 'http://trendr.org' ) ?></p>
			</div>

			<?php do_action( 'trs_footer' ) ?>
		</div><!-- #footer -->

		<?php do_action( 'trs_after_footer' ) ?>

		<?php trm_footer(); ?>
	<?php echo get_num_queries(); ?> queries in <?php timer_stop(1,3); ?> seconds,,, 
<?php echo memory_get_usage(); ?> Bytes Used
	</body>

</html>