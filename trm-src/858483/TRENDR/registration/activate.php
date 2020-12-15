<?php

/**
 * Template Name: trendr - Activity Directory
 *
 * @package trendr
 * @sutrsackage Theme
 */

?>

<?php get_header( 'trendr' ); ?>

	<?php do_action( 'trs_before_directory_activity_page' ); ?>

	<div id="skeleton"">
		<div class="dimension">

	<form action="<?php trs_activity_post_form_action(); ?>" method="post" id="post-box" name="post-box" role="complementary">

	<?php do_action( 'trs_before_activity_post_form' ); ?>


	<div id="post-intro">

	<h5><?php if ( trs_is_group() )
			printf( __( "What's new in %s, %s?", 'trendr' ), trs_get_group_name(), trs_get_user_firstname() );
		else
			printf( __( "What's new, %s?", 'trendr' ), trs_get_user_firstname() );
	?></h5>

	<div id="post-content">
		<div id="post-inner">
			<textarea name="field" id="field" cols="50" rows="10"><?php if ( isset( $_GET['r'] ) ) : ?>@<?php echo esc_attr( $_GET['r'] ); ?> <?php endif; ?></textarea>
		</div>
			<?php do_action( 'trs_activity_post_form_options' ); ?>

		<div id="post-controls">
			<div id="post-submit">
				<input type="submit" name="submit-post" id="submit-post" value="<?php _e( 'Post Update', 'trendr' ); ?>" />
			</div>



</div>
		</div><!-- #post-controls -->
	</div><!-- #post-content -->

	<?php trm_nonce_field( 'post_update', '_key_post_update' ); ?>
	<?php do_action( 'trs_after_activity_post_form' ); ?>

</form><!-- #post-box -->

		


			<?php if ( is_user_logged_in() ) : ?>


			<?php endif; ?>






			<div class="activity" role="main">


			</div><!-- .activity -->


		</div><!-- .dimension -->
	</div><!-- #skeleton -->

	<?php do_action( 'trs_after_directory_activity_page' ); ?>

<?php get_footer( 'trendr' ); ?>









