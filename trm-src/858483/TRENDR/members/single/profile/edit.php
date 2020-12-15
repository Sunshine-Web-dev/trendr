<?php do_action( 'trs_before_profile_edit_content' );

if ( trs_has_profile( 'profile_group_id=' . trs_get_current_profile_group_id() ) ) :
	while ( trs_profile_groups() ) : trs_the_profile_group(); ?>

<form action="<?php trs_the_profile_group_edit_form_action(); ?>" method="post" id="profile-edit-form" class="standard-form <?php trs_the_profile_group_slug(); ?>">

	<?php do_action( 'trs_before_profile_field_content' ); ?>

		<h4><?php printf( __( "Editing '%s' Profile Group", "trendr" ), trs_get_the_profile_group_name() ); ?></h4>

		<ul class="button-nav">

			<?php trs_profile_group_tabs(); ?>

		</ul>

		<div class="clear"></div>

		<?php while ( trs_profile_fields() ) : trs_the_profile_field(); ?>

			<div<?php trs_field_css_class( 'editfield' ) ?>>

				<?php if ( 'textbox' == trs_get_the_profile_field_type() ) : ?>

					<label for="<?php trs_the_profile_field_input_name(); ?>"><?php trs_the_profile_field_name(); ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ); ?><?php endif; ?></label>
					<input type="text" name="<?php trs_the_profile_field_input_name(); ?>" id="<?php trs_the_profile_field_input_name(); ?>" value="<?php trs_the_profile_field_edit_value(); ?>" <?php if ( trs_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>/>

				<?php endif; ?>

				<?php if ( 'textarea' == trs_get_the_profile_field_type() ) : ?>

					<label for="<?php trs_the_profile_field_input_name() ?>"><?php trs_the_profile_field_name(); ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ); ?><?php endif; ?></label>
					<textarea rows="5" cols="40" name="<?php trs_the_profile_field_input_name(); ?>" id="<?php trs_the_profile_field_input_name(); ?>" <?php if ( trs_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>><?php trs_the_profile_field_edit_value(); ?></textarea>

				<?php endif; ?>

				<?php if ( 'selectbox' == trs_get_the_profile_field_type() ) : ?>

					<label for="<?php trs_the_profile_field_input_name(); ?>"><?php trs_the_profile_field_name(); ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ); ?><?php endif; ?></label>
					<select name="<?php trs_the_profile_field_input_name(); ?>" id="<?php trs_the_profile_field_input_name(); ?>" <?php if ( trs_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>
						<?php trs_the_profile_field_options() ?>
					</select>

				<?php endif; ?>

				<?php if ( 'multiselectbox' == trs_get_the_profile_field_type() ) : ?>

					<label for="<?php trs_the_profile_field_input_name() ?>"><?php trs_the_profile_field_name() ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ); ?><?php endif; ?></label>
					<select name="<?php trs_the_profile_field_input_name() ?>" id="<?php trs_the_profile_field_input_name() ?>" multiple="multiple" <?php if ( trs_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>

						<?php trs_the_profile_field_options(); ?>

					</select>

					<?php if ( !trs_get_the_profile_field_is_required() ) : ?>

						<a class="clear-value" href="javascript:clear( '<?php trs_the_profile_field_input_name(); ?>' );"><?php _e( 'Clear', 'trendr' ); ?></a>

					<?php endif; ?>

				<?php endif; ?>

				<?php if ( 'radio' == trs_get_the_profile_field_type() ) : ?>

					<div class="radio">
						<span class="label"><?php trs_the_profile_field_name(); ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ); ?><?php endif; ?></span>

						<?php trs_the_profile_field_options(); ?>

						<?php if ( !trs_get_the_profile_field_is_required() ) : ?>

							<a class="clear-value" href="javascript:clear( '<?php trs_the_profile_field_input_name(); ?>' );"><?php _e( 'Clear', 'trendr' ); ?></a>

						<?php endif; ?>
					</div>

				<?php endif; ?>

				<?php if ( 'checkbox' == trs_get_the_profile_field_type() ) : ?>

					<div class="checkbox">
						<span class="label"><?php trs_the_profile_field_name(); ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ); ?><?php endif; ?></span>

						<?php trs_the_profile_field_options(); ?>
					</div>

				<?php endif; ?>

				<?php if ( 'datebox' == trs_get_the_profile_field_type() ) : ?>

					<div class="datebox">
						<label for="<?php trs_the_profile_field_input_name(); ?>_day"><?php trs_the_profile_field_name(); ?> <?php if ( trs_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'trendr' ); ?><?php endif; ?></label>

						<select name="<?php trs_the_profile_field_input_name(); ?>_day" id="<?php trs_the_profile_field_input_name(); ?>_day" <?php if ( trs_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>

							<?php trs_the_profile_field_options( 'type=day' ); ?>

						</select>

						<select name="<?php trs_the_profile_field_input_name() ?>_month" id="<?php trs_the_profile_field_input_name(); ?>_month" <?php if ( trs_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>

							<?php trs_the_profile_field_options( 'type=month' ); ?>

						</select>

						<select name="<?php trs_the_profile_field_input_name() ?>_year" id="<?php trs_the_profile_field_input_name(); ?>_year" <?php if ( trs_get_the_profile_field_is_required() ) : ?>aria-required="true"<?php endif; ?>>

							<?php trs_the_profile_field_options( 'type=year' ); ?>

						</select>
					</div>

				<?php endif; ?>

				<?php do_action( 'trs_custom_profile_edit_fields' ); ?>

				<p class="description"><?php trs_the_profile_field_description(); ?></p>
			</div>

		<?php endwhile; ?>

	<?php do_action( 'trs_after_profile_field_content' ); ?>

	<div class="submit">
		<input type="submit" name="profile-group-edit-submit" id="profile-group-edit-submit" value="<?php _e( 'Save Changes', 'trendr' ); ?> " />
	</div>

	<input type="hidden" name="field_ids" id="field_ids" value="<?php trs_the_profile_group_field_ids(); ?>" />

	<?php trm_nonce_field( 'trs_xprofile_edit' ); ?>

</form>

<?php endwhile; endif; ?>

<?php do_action( 'trs_after_profile_edit_content' ); ?>
