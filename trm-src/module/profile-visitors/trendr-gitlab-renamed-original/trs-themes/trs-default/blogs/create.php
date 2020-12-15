<?php

/**
 * trendr - Create Blog
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php get_header( 'trendr' ); ?>

	<?php do_action( 'trs_before_directory_blogs_content' ); ?>

	<div id="skeleton">
		<div class="dimension" role="main">

		<?php do_action( 'template_notices' ); ?>

			<h3><?php _e( 'Create a Site', 'trendr' ); ?> &nbsp;<a class="button" href="<?php echo trailingslashit( trs_get_root_domain() . '/' . trs_get_blogs_root_slug() ) ?>"><?php _e( 'Site Directory', 'trendr' ); ?></a></h3>

		<?php do_action( 'trs_before_create_blog_content' ); ?>

		<?php if ( trs_blog_signup_enabled() ) : ?>

			<?php trs_show_blog_signup_form(); ?>

		<?php else: ?>

			<div id="message" class="info">
				<p><?php _e( 'Site registration is currently disabled', 'trendr' ); ?></p>
			</div>

		<?php endif; ?>

		<?php do_action( 'trs_after_create_blog_content' ); ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

	<?php do_action( 'trs_after_directory_blogs_content' ); ?>

<?php get_sidebar( 'trendr' ); ?>
<?php get_footer( 'trendr' ); ?>

