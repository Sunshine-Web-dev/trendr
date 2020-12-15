<?php

/**
 * trendr Delete Account
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

				<h3><?php _e( 'Delete Account', 'trendr' ); ?></h3>

				<form action="<?php echo trs_displayed_user_domain() . trs_get_settings_slug() . '/delete-account'; ?>" name="account-delete-form" id="account-delete-form" class="standard-form" method="post">

					<div id="message" class="info">
						<p><?php _e( 'WARNING: Deleting your account will completely remove ALL content associated with it. There is no way back, please be careful with this option.', 'trendr' ); ?></p>
					</div>

					<input type="checkbox" name="delete-account-understand" id="delete-account-understand" value="1" onclick="if(this.checked) { document.getElementById('delete-account-button').disabled = ''; } else { document.getElementById('delete-account-button').disabled = 'disabled'; }" /> <?php _e( 'I understand the consequences of deleting my account.', 'trendr' ); ?>

					<?php do_action( 'trs_members_delete_account_before_submit' ); ?>

					<div class="submit">
						<input type="submit" disabled="disabled" value="<?php _e( 'Delete My Account', 'trendr' ) ?>" id="delete-account-button" name="delete-account-button" />
					</div>

					<?php do_action( 'trs_members_delete_account_after_submit' ); ?>

					<?php trm_nonce_field( 'delete-account' ); ?>
				</form>

				<?php do_action( 'trs_after_member_body' ); ?>

			</div><!-- #figure -->

			<?php do_action( 'trs_after_member_settings_template' ); ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->



<?php get_footer( 'trendr' ) ?>