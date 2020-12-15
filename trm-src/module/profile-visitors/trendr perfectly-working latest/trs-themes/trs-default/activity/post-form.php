<?php

/**
 * trendr - Activity Post Form
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<form action="<?php trs_activity_post_form_action(); ?>" method="post" id="post-box" name="post-box" role="complementary">

	<?php do_action( 'trs_before_activity_post_form' ); ?>

	<div id="post-intro">
		<a href="<?php echo trs_loggedin_user_domain(); ?>">
			<?php trs_loggedin_user_portrait( 'width=' . trs_core_portrait_thumb_width() . '&height=' . trs_core_portrait_thumb_height() ); ?>
		</a>
	</div>

	<h5><?php if ( trs_is_group() )
			printf( __( "What's new in %s, %s?", 'trendr' ), trs_get_group_name(), trs_get_user_firstname() );
		else
			printf( __( "What's new, %s?", 'trendr' ), trs_get_user_firstname() );
	?></h5>

	<div id="isadDiv" style="    float: right; display:block;">
		promote this post<input type="checkbox" name="isad" id="isad"  >
	</div>

	<div id="duration" style="display:none;    float: right;">
		Duration <input type="number" name="period_in_min" id="period_in_min" value="0">
	</div>

	<div id="post-content">

		<div id="post-inner">
			<textarea name="field" id="field" cols="50" rows="10"><?php if ( isset( $_GET['r'] ) ) : ?>@<?php echo esc_attr( $_GET['r'] ); ?> <?php endif; ?></textarea>
		</div>

		<div id="post-controls">
			<div id="post-submit">
				<input type="submit" name="submit-post" id="submit-post" value="<?php _e( 'Post Update', 'trendr' ); ?>" />
			</div>
			<?php if ( trs_is_active( 'groups' ) && !trs_is_my_profile() && !trs_is_group() ) : ?>

				<div id="field-post-in-box">

					<?php _e( 'Post in', 'trendr' ) ?>:

					<select id="field-post-in" name="field-post-in">
						<option selected="selected" value="0"><?php _e( 'My Profile', 'trendr' ); ?></option>

						<?php if ( trs_has_groups( 'user_id=' . trs_loggedin_user_id() . '&type=alphabetical&max=100&per_page=100&populate_extras=0' ) ) :
							while ( trs_groups() ) : trs_the_group(); ?>

								<option value="<?php trs_group_id(); ?>"><?php trs_group_name(); ?></option>

							<?php endwhile;
						endif; ?>

					</select>
				</div>
				<input type="hidden" id="field-post-object" name="field-post-object" value="groups" />

			<?php elseif ( trs_is_group_home() ) : ?>

				<input type="hidden" id="field-post-object" name="field-post-object" value="groups" />
				<input type="hidden" id="field-post-in" name="field-post-in" value="<?php trs_group_id(); ?>" />

			<?php endif; ?>

			<?php do_action( 'trs_activity_post_form_options' ); ?>

		</div><!-- #post-controls -->
	</div><!-- #post-content -->

	<?php trm_nonce_field( 'post_update', '_key_post_update' ); ?>
	<?php do_action( 'trs_after_activity_post_form' ); ?>

</form><!-- #post-box -->
