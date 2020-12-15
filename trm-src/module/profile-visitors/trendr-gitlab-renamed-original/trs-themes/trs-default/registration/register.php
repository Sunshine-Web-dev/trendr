<?php get_header( 'trendr' ) ?>

	<div id="skeleton">
		<div class="dimension">

		<?php do_action( 'trs_before_register_page' ) ?>

		<div class="page" id="register-page">

			<form action="" name="signup_form" id="signup_form" class="standard-form" method="post" enctype="multipart/form-data">

			<?php if ( 'registration-disabled' == trs_get_current_signup_step() ) : ?>
				<?php do_action( 'template_notices' ) ?>
				<?php do_action( 'trs_before_registration_disabled' ) ?>

					<p><?php _e( 'User registration is currently not allowed.', 'trendr' ); ?></p>

				<?php do_action( 'trs_after_registration_disabled' ); ?>
			<?php endif; // registration-disabled signup setp ?>

			<?php if ( 'request-details' == trs_get_current_signup_step() ) : ?>

				<h2><?php _e( 'Create an Account', 'trendr' ) ?></h2>

				<?php do_action( 'template_notices' ) ?>

				<p><?php _e( 'Registering for this site is easy, just fill in the fields below and we\'ll get a new account set up for you in no time.', 'trendr' ) ?></p>

				<?php do_action( 'trs_before_account_details_fields' ) ?>

				<div class="register-section" id="basic-details-section">

					<?php /***** Basic Account Details ******/ ?>

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

					<div class="register-section" id="profile-details-section">

						<h4><?php _e( 'Profile Details', 'trendr' ) ?></h4>

						<?php /* Use the profile field loop to render input fields for the 'base' profile field group */ ?>
						<?php if ( trs_is_active( 'xprofile' ) ) : if ( trs_has_profile( 'profile_group_id=1' ) ) : while ( trs_profile_groups() ) : trs_the_profile_group(); ?>

						<?php while ( trs_profile_fields() ) : trs_the_profile_field(); ?>

							<div class="editfield">

								<?php if ( 'textbox' == trs_get_the_profile_field_type() ) : ?>

									<label for="<?php trs_the_profile_field_input_name() ?>"><?php trs_the_profile_field_name() ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ) ?><?php endif; ?></label>
									<?php do_action( 'trs_' . trs_get_the_profile_field_input_name() . '_errors' ) ?>
									<input type="text" name="<?php trs_the_profile_field_input_name() ?>" id="<?php trs_the_profile_field_input_name() ?>" value="<?php trs_the_profile_field_edit_value() ?>" />

								<?php endif; ?>

								<?php if ( 'textarea' == trs_get_the_profile_field_type() ) : ?>

									<label for="<?php trs_the_profile_field_input_name() ?>"><?php trs_the_profile_field_name() ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ) ?><?php endif; ?></label>
									<?php do_action( 'trs_' . trs_get_the_profile_field_input_name() . '_errors' ) ?>
									<textarea rows="5" cols="40" name="<?php trs_the_profile_field_input_name() ?>" id="<?php trs_the_profile_field_input_name() ?>"><?php trs_the_profile_field_edit_value() ?></textarea>

								<?php endif; ?>

								<?php if ( 'selectbox' == trs_get_the_profile_field_type() ) : ?>

									<label for="<?php trs_the_profile_field_input_name() ?>"><?php trs_the_profile_field_name() ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ) ?><?php endif; ?></label>
									<?php do_action( 'trs_' . trs_get_the_profile_field_input_name() . '_errors' ) ?>
									<select name="<?php trs_the_profile_field_input_name() ?>" id="<?php trs_the_profile_field_input_name() ?>">
										<?php trs_the_profile_field_options() ?>
									</select>

								<?php endif; ?>

								<?php if ( 'multiselectbox' == trs_get_the_profile_field_type() ) : ?>

									<label for="<?php trs_the_profile_field_input_name() ?>"><?php trs_the_profile_field_name() ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ) ?><?php endif; ?></label>
									<?php do_action( 'trs_' . trs_get_the_profile_field_input_name() . '_errors' ) ?>
									<select name="<?php trs_the_profile_field_input_name() ?>" id="<?php trs_the_profile_field_input_name() ?>" multiple="multiple">
										<?php trs_the_profile_field_options() ?>
									</select>

								<?php endif; ?>

								<?php if ( 'radio' == trs_get_the_profile_field_type() ) : ?>

									<div class="radio">
										<span class="label"><?php trs_the_profile_field_name() ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ) ?><?php endif; ?></span>

										<?php do_action( 'trs_' . trs_get_the_profile_field_input_name() . '_errors' ) ?>
										<?php trs_the_profile_field_options() ?>

										<?php if ( !trs_get_the_profile_field_is_required() ) : ?>
											<a class="clear-value" href="javascript:clear( '<?php trs_the_profile_field_input_name() ?>' );"><?php _e( 'Clear', 'trendr' ) ?></a>
										<?php endif; ?>
									</div>

								<?php endif; ?>

								<?php if ( 'checkbox' == trs_get_the_profile_field_type() ) : ?>

									<div class="checkbox">
										<span class="label"><?php trs_the_profile_field_name() ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ) ?><?php endif; ?></span>

										<?php do_action( 'trs_' . trs_get_the_profile_field_input_name() . '_errors' ) ?>
										<?php trs_the_profile_field_options() ?>
									</div>

								<?php endif; ?>

								<?php if ( 'datebox' == trs_get_the_profile_field_type() ) : ?>

									<div class="datebox">
										<label for="<?php trs_the_profile_field_input_name() ?>_day"><?php trs_the_profile_field_name() ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ) ?><?php endif; ?></label>
										<?php do_action( 'trs_' . trs_get_the_profile_field_input_name() . '_errors' ) ?>

										<select name="<?php trs_the_profile_field_input_name() ?>_day" id="<?php trs_the_profile_field_input_name() ?>_day">
											<?php trs_the_profile_field_options( 'type=day' ) ?>
										</select>

										<select name="<?php trs_the_profile_field_input_name() ?>_month" id="<?php trs_the_profile_field_input_name() ?>_month">
											<?php trs_the_profile_field_options( 'type=month' ) ?>
										</select>

										<select name="<?php trs_the_profile_field_input_name() ?>_year" id="<?php trs_the_profile_field_input_name() ?>_year">
											<?php trs_the_profile_field_options( 'type=year' ) ?>
										</select>
									</div>

								<?php endif; ?>

								<?php do_action( 'trs_custom_profile_edit_fields' ) ?>

								<p class="description"><?php trs_the_profile_field_description() ?></p>

							</div>

						<?php endwhile; ?>

						<input type="hidden" name="signup_profile_field_ids" id="signup_profile_field_ids" value="<?php trs_the_profile_group_field_ids() ?>" />

						<?php endwhile; endif; endif; ?>

					</div><!-- #profile-details-section -->

					<?php do_action( 'trs_after_signup_profile_fields' ) ?>

				<?php endif; ?>

				<?php if ( trs_get_blog_signup_allowed() ) : ?>

					<?php do_action( 'trs_before_blog_details_fields' ) ?>

					<?php /***** Blog Creation Details ******/ ?>

					<div class="register-section" id="blog-details-section">

						<h4><?php _e( 'Blog Details', 'trendr' ) ?></h4>

						<p><input type="checkbox" name="signup_with_blog" id="signup_with_blog" value="1"<?php if ( (int) trs_get_signup_with_blog_value() ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'Yes, I\'d like to create a new site', 'trendr' ) ?></p>

						<div id="blog-details"<?php if ( (int) trs_get_signup_with_blog_value() ) : ?>class="show"<?php endif; ?>>

							<label for="signup_blog_url"><?php _e( 'Blog URL', 'trendr' ) ?> <?php _e( '(required)', 'trendr' ) ?></label>
							<?php do_action( 'trs_signup_blog_url_errors' ) ?>

							<?php if ( is_subdomain_install() ) : ?>
								http:// <input type="text" name="signup_blog_url" id="signup_blog_url" value="<?php trs_signup_blog_url_value() ?>" /> .<?php trs_blogs_subdomain_base() ?>
							<?php else : ?>
								<?php echo site_url() ?>/ <input type="text" name="signup_blog_url" id="signup_blog_url" value="<?php trs_signup_blog_url_value() ?>" />
							<?php endif; ?>

							<label for="signup_blog_title"><?php _e( 'Site Title', 'trendr' ) ?> <?php _e( '(required)', 'trendr' ) ?></label>
							<?php do_action( 'trs_signup_blog_title_errors' ) ?>
							<input type="text" name="signup_blog_title" id="signup_blog_title" value="<?php trs_signup_blog_title_value() ?>" />

							<span class="label"><?php _e( 'I would like my site to appear in search engines, and in public listings around this network.', 'trendr' ) ?>:</span>
							<?php do_action( 'trs_signup_blog_privacy_errors' ) ?>

							<label><input type="radio" name="signup_blog_privacy" id="signup_blog_privacy_public" value="public"<?php if ( 'public' == trs_get_signup_blog_privacy_value() || !trs_get_signup_blog_privacy_value() ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'Yes', 'trendr' ) ?></label>
							<label><input type="radio" name="signup_blog_privacy" id="signup_blog_privacy_private" value="private"<?php if ( 'private' == trs_get_signup_blog_privacy_value() ) : ?> checked="checked"<?php endif; ?> /> <?php _e( 'No', 'trendr' ) ?></label>

						</div>

					</div><!-- #blog-details-section -->

					<?php do_action( 'trs_after_blog_details_fields' ) ?>

				<?php endif; ?>

				<?php do_action( 'trs_before_registration_submit_buttons' ) ?>

				<div class="submit">
					<input type="submit" name="signup_submit" id="signup_submit" value="<?php _e( 'Complete Sign Up', 'trendr' ) ?>" />
				</div>

				<?php do_action( 'trs_after_registration_submit_buttons' ) ?>

				<?php trm_nonce_field( 'trs_new_signup' ) ?>

			<?php endif; // request-details signup step ?>

			<?php if ( 'completed-confirmation' == trs_get_current_signup_step() ) : ?>

				<h2><?php _e( 'Sign Up Complete!', 'trendr' ) ?></h2>

				<?php do_action( 'template_notices' ) ?>
				<?php do_action( 'trs_before_registration_confirmed' ) ?>

				<?php if ( trs_registration_needs_activation() ) : ?>
					<p><?php _e( 'You have successfully created your account! To begin using this site you will need to activate your account via the email we have just sent to your address.', 'trendr' ) ?></p>
				<?php else : ?>
					<p><?php _e( 'You have successfully created your account! Please log in using the username and password you have just created.', 'trendr' ) ?></p>
				<?php endif; ?>

				<?php do_action( 'trs_after_registration_confirmed' ) ?>

			<?php endif; // completed-confirmation signup step ?>

			<?php do_action( 'trs_custom_signup_steps' ) ?>

			</form>

		</div>

		<?php do_action( 'trs_after_register_page' ) ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

	<?php get_sidebar( 'trendr' ) ?>

	<script type="text/javascript">
		jQuery(document).ready( function() {
			if ( jQuery('div#blog-details').length && !jQuery('div#blog-details').hasClass('show') )
				jQuery('div#blog-details').toggle();

			jQuery( 'input#signup_with_blog' ).click( function() {
				jQuery('div#blog-details').fadeOut().toggle();
			});
		});
	</script>

<?php get_footer( 'trendr' ) ?>