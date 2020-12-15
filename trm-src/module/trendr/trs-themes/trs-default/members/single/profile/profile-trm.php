<?php do_action( 'trs_before_profile_loop_content' ) ?>

<?php $ud = get_userdata( trs_displayed_user_id() ); ?>

<?php do_action( 'trs_before_profile_field_content' ) ?>

	<div class="trs-widget trm-profile">
		<h4><?php trs_is_my_profile() ? _e( 'My Profile', 'trendr' ) : printf( __( "%s's Profile", 'trendr' ), trs_get_displayed_user_fullname() ); ?></h4>

		<table class="trm-profile-fields">

			<?php if ( $ud->display_name ) : ?>

				<tr id="trm_displayname">
					<td class="label"><?php _e( 'Name', 'trendr' ); ?></td>
					<td class="data"><?php echo $ud->display_name; ?></td>
				</tr>

			<?php endif; ?>

			<?php if ( $ud->user_description ) : ?>

				<tr id="trm_desc">
					<td class="label"><?php _e( 'About Me', 'trendr' ); ?></td>
					<td class="data"><?php echo $ud->user_description; ?></td>
				</tr>

			<?php endif; ?>

			<?php if ( $ud->user_url ) : ?>

				<tr id="trm_website">
					<td class="label"><?php _e( 'Website', 'trendr' ); ?></td>
					<td class="data"><?php echo make_clickable( $ud->user_url ); ?></td>
				</tr>

			<?php endif; ?>

			<?php if ( $ud->jabber ) : ?>

				<tr id="trm_jabber">
					<td class="label"><?php _e( 'Jabber', 'trendr' ); ?></td>
					<td class="data"><?php echo $ud->jabber; ?></td>
				</tr>

			<?php endif; ?>

			<?php if ( $ud->aim ) : ?>

				<tr id="trm_aim">
					<td class="label"><?php _e( 'AOL Messenger', 'trendr' ); ?></td>
					<td class="data"><?php echo $ud->aim; ?></td>
				</tr>

			<?php endif; ?>

			<?php if ( $ud->yim ) : ?>

				<tr id="trm_yim">
					<td class="label"><?php _e( 'Yahoo Messenger', 'trendr' ); ?></td>
					<td class="data"><?php echo $ud->yim; ?></td>
				</tr>

			<?php endif; ?>

		</table>
	</div>

<?php do_action( 'trs_after_profile_field_content' ) ?>

<?php do_action( 'trs_profile_field_buttons' ) ?>

<?php do_action( 'trs_after_profile_loop_content' ) ?>
