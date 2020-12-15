<?php

/**
 * trendr Member Settings
 *
 * @package trendr
 * @sutrsackage trs-default
 */
?>

<?php get_header( 'trendr' ) ?>

	<div id="skeleton"">
		<div class="dimension">
			

			<?php do_action( 'trs_before_member_settings_template' ); ?>

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

				<h3><?php _e( 'General Settings', 'trendr' ); ?></h3>

				<?php do_action( 'trs_template_content' ) ?>

				<form action="<?php echo trs_displayed_user_domain() . trs_get_settings_slug() . '/general'; ?>" method="post" class="standard-form" id="settings-form">

					<label for="pwd"><?php _e( 'Current Password <span>(required to update email or change current password)</span>', 'trendr' ); ?></label>
					<input type="password" name="pwd" id="pwd" size="16" value="" class="settings-input small" /> &nbsp;<a href="<?php echo site_url( add_query_arg( array( 'action' => 'lostpassword' ), 'enter.php' ), 'login' ); ?>" title="<?php _e( 'Password Lost and Found', 'trendr' ); ?>"><?php _e( 'Lost your password?', 'trendr' ); ?></a>

					<label for="email"><?php _e( 'Account Email', 'trendr' ); ?></label>
					<input type="text" name="email" id="email" value="<?php echo trs_get_displayed_user_email(); ?>" class="settings-input" />

					<label for="pass1"><?php _e( 'Change Password <span>(leave blank for no change)</span>', 'trendr' ); ?></label>
					<input type="password" name="pass1" id="pass1" size="16" value="" class="settings-input small" /> &nbsp;<?php _e( 'New Password', 'trendr' ); ?><br />
					<input type="password" name="pass2" id="pass2" size="16" value="" class="settings-input small" /> &nbsp;<?php _e( 'Repeat New Password', 'trendr' ); ?>

					<?php do_action( 'trs_core_general_settings_before_submit' ); ?>

					<div class="submit">
						<input type="submit" name="submit" value="<?php _e( 'Save Changes', 'trendr' ); ?>" id="submit-setting" class="auto" />
					</div>

					<?php do_action( 'trs_core_general_settings_after_submit' ); ?>

					<?php trm_nonce_field( 'trs_settings_general' ); ?>

				</form>

				<?php do_action( 'trs_after_member_body' ); ?>

			</div><!-- #figure -->

			<?php do_action( 'trs_after_member_settings_template' ); ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->


<?php get_footer( 'trendr' ) ?>