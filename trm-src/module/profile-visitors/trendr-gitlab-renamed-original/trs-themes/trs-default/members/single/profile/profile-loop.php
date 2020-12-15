<?php do_action( 'trs_before_profile_loop_content' ); ?>

<?php if ( trs_has_profile() ) : ?>

	<?php while ( trs_profile_groups() ) : trs_the_profile_group(); ?>

		<?php if ( trs_profile_group_has_fields() ) : ?>

			<?php do_action( 'trs_before_profile_field_content' ); ?>

			<div class="trs-widget <?php trs_the_profile_group_slug(); ?>">

				<h4><?php trs_the_profile_group_name(); ?></h4>

				<table class="profile-fields">

					<?php while ( trs_profile_fields() ) : trs_the_profile_field(); ?>

						<?php if ( trs_field_has_data() ) : ?>

							<tr<?php trs_field_css_class(); ?>>

								<td class="label"><?php trs_the_profile_field_name(); ?></td>

								<td class="data"><?php trs_the_profile_field_value(); ?></td>

							</tr>

						<?php endif; ?>

						<?php do_action( 'trs_profile_field_item' ); ?>

					<?php endwhile; ?>

				</table>
			</div>

			<?php do_action( 'trs_after_profile_field_content' ); ?>

		<?php endif; ?>

	<?php endwhile; ?>

	<?php do_action( 'trs_profile_field_buttons' ); ?>

<?php endif; ?>

<?php do_action( 'trs_after_profile_loop_content' ); ?>
