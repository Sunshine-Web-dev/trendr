<?php get_header() ?>

		<?php do_action( 'trs_before_register_page' ) ?>

		<div class="page" id="register-page">

			<form action="" name="signup_form" id="signup_form" class="standard-form" method="post" enctype="multipart/form-data">

			<?php if ( 'request-details' == trs_get_current_signup_step() ) : ?>

				<?php do_action( 'template_notices' ) ?>

				<p><?php _e( '', 'trnder' ) ?></p>

				<?php do_action( 'trs_before_account_details_fields' ) ?>
<h4><?php _e( 'Account Details', 'trendr' ) ?></h4>

					<label for="signup_username"><?php _e( 'Username', 'trendr' ) ?> <?php _e( '(required)', 'trendr' ) ?></label>
					<?php do_action( 'trs_signup_username_errors' ) ?>
					<input type="text" name="signup_username" id="signup_username" value="<?php trs_signup_username_value() ?>" />

					<label for="signup_email"><?php _e( 'Email Address', 'trendr' ) ?> <?php _e( '(required)', 'trendr' ) ?></label>
					<?php do_action( 'trs_signup_email_errors' ) ?>
					<input type="text" name="signup_email" id="signup_email" value="<?php trs_signup_email_value() ?>" />

					<label for="signup_password"><?php _e( 'Choose a Password', 'trendr' ) ?> <?php _e( '(required)', 'trendr' ) ?></label>
					<?php do_action( 'trs_signup_password_errors' ) ?>
					<input type="password" name="signup_password" id="signup_password" value="" />

					<label for="signup_password_confirm"><?php _e( 'Confirm Password', 'trendr' ) ?> <?php _e( '(required)', 'trendr' ) ?></label>
					<?php do_action( 'trs_signup_password_confirm_errors' ) ?>
					<input type="password" name="signup_password_confirm" id="signup_password_confirm" value="" />

				</div><!-- #basic-details-section -->

				<?php do_action( 'trs_after_account_details_fields' ) ?>

				<?php /***** Extra Profile Details ******/ ?>

				<?php if ( trs_is_active( 'xprofile' ) ) : ?>

					<?php do_action( 'trs_before_signup_profile_fields' ) ?>

					<div class="register-section" id="details-section">


						<?php /* Use the profile field loop to render input fields for the 'base' profile field group */ ?>
						<?php if ( function_exists( 'trs_has_profile' ) ) : if ( trs_has_profile( 'profile_group_id=1' ) ) : while ( trs_profile_groups() ) : trs_the_profile_group(); ?>

						<?php while ( trs_profile_fields() ) : trs_the_profile_field(); ?>

							<div class="editfield">



							



								<?php do_action( 'trs_custom_profile_edit_fields' ) ?>

								<p class="description"><?php trs_the_profile_field_description() ?></p>

							</div>

						<?php endwhile; ?>

						<input type="hidden" name="signup_profile_field_ids" id="signup_profile_field_ids" value="<?php trs_the_profile_group_field_ids() ?>" />

						<?php endwhile; endif; endif; ?>

					</div><!-- #profile-details-section -->

					<?php do_action( 'trs_after_signup_profile_fields' ) ?>

				<?php endif; ?>
				


				<?php do_action( 'trs_before_registration_submit_buttons' ) ?>


				<?php do_action( 'trs_after_registration_submit_buttons' ) ?>

				<?php trm_nonce_field( 'trs_new_signup' ) ?>

			<?php endif; // request-details signup step ?>

			<?php if ( 'completed-confirmation' == trs_get_current_signup_step() ) : ?>

				<h2><?php _e( 'Sign Up Complete!', 'trnder' ) ?></h2>

				<?php do_action( 'template_notices' ) ?>

				<?php if ( trs_registration_needs_activation() ) : ?>
					<p><?php _e( 'You have successfully created your account! To begin using this site you will need to activate your account via the email we have just sent to your address.', 'trnder' ) ?></p>
				<?php else : ?>
					<p><?php _e( 'You have successfully created your account! Please log in using the username and password you have just created.', 'trnder' ) ?></p>
				<?php endif; ?>

				<?php if ( trs_is_active( 'xprofile' ) && !(int)trs_get_option( 'trs-disable-portrait-uploads' ) ) : ?>

					


						<input type="hidden" name="signup_email" id="signup_email" value="<?php trs_signup_email_value() ?>" />
						<input type="hidden" name="signup_username" id="signup_username" value="<?php trs_signup_username_value() ?>" />
						<input type="hidden" name="signup_portrait_dir" id="signup_portrait_dir" value="<?php trs_signup_portrait_dir_value() ?>" />

						<input type="hidden" name="image_src" id="image_src" value="<?php trs_portrait_to_crop_src() ?>" />
						<input type="hidden" id="x" name="x" />
						<input type="hidden" id="y" name="y" />
						<input type="hidden" id="w" name="w" />
						<input type="hidden" id="h" name="h" />

						<?php trm_nonce_field( 'trs_portrait_cropstore' ) ?>

					<?php endif; ?>


			<?php endif; // completed-confirmation signup step ?>

			<?php do_action( 'trs_custom_signup_steps' ) ?>

			</form>

		</div>

		<?php do_action( 'trs_after_register_page' ) ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php do_action( 'trs_after_directory_activity_content' ) ?>



<?php get_footer() ?>