<?php get_header( 'trendr' ); ?>

	<div id="skeleton">
		<div class="dimension">

		<?php do_action( 'trs_before_activation_page' ) ?>

		<div class="page" id="activate-page">

			<?php if ( trs_account_was_activated() ) : ?>

				<h2 class="widgettitle"><?php _e( 'Account Activated', 'trendr' ) ?></h2>

				<?php do_action( 'trs_before_activate_content' ) ?>

				<?php if ( isset( $_GET['e'] ) ) : ?>
					<p><?php _e( 'Your account was activated successfully! Your account details have been sent to you in a separate email.', 'trendr' ) ?></p>
				<?php else : ?>
					<p><?php _e( 'Your account was activated successfully! You can now log in with the username and password you provided when you signed up.', 'trendr' ) ?></p>
				<?php endif; ?>

			<?php else : ?>

				<h3><?php _e( 'Activate your Account', 'trendr' ) ?></h3>

				<?php do_action( 'trs_before_activate_content' ) ?>

				<p><?php _e( 'Please provide a valid activation key.', 'trendr' ) ?></p>

				<form action="" method="get" class="standard-form" id="activation-form">

					<label for="key"><?php _e( 'Activation Key:', 'trendr' ) ?></label>
					<input type="text" name="key" id="key" value="" />

					<p class="submit">
						<input type="submit" name="submit" value="<?php _e( 'Activate', 'trendr' ) ?>" />
					</p>

				</form>

			<?php endif; ?>

			<?php do_action( 'trs_after_activate_content' ) ?>

		</div><!-- .page -->

		<?php do_action( 'trs_after_activation_page' ) ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

	<?php get_sidebar( 'trendr' ) ?>

<?php get_footer( 'trendr' ); ?>
