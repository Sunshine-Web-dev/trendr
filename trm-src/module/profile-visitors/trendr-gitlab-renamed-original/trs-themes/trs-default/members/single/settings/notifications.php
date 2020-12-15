<?php

/**
 * trendr Notification Settings
 *
 * @package trendr
 * @sutrsackage trs-default
 */
?>

<?php get_header( 'trendr' ) ?>

	<div id="skeleton">
		<div class="dimension">

			<?php do_action( 'trs_before_member_settings_template' ); ?>

			<div id="item-header">

				<?php locate_template( array( 'members/single/member-header.php' ), true ); ?>

			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="contour-select no-ajax" id="object-c" role="navigation">
					<ul>

						<?php trs_get_displayed_user_nav(); ?>

						<?php do_action( 'trs_member_options_nav' ); ?>

					</ul>
				</div>
			</div><!-- #item-nav -->

			<div id="item-body" role="main">

				<?php do_action( 'trs_before_member_body' ); ?>

				<div class="contour-select no-ajax" id="subnav">
					<ul>

						<?php trs_get_options_nav(); ?>

						<?php do_action( 'trs_member_plugin_options_nav' ); ?>

					</ul>
				</div><!-- .contour-select -->

				<h3><?php _e( 'Email Notification', 'trendr' ); ?></h3>

				<?php do_action( 'trs_template_content' ) ?>

				<form action="<?php echo trs_displayed_user_domain() . trs_get_settings_slug() . '/notifications'; ?>" method="post" class="standard-form" id="settings-form">
					<p><?php _e( 'Send a notification by email when:', 'trendr' ); ?></p>

					<?php do_action( 'trs_notification_settings' ); ?>

					<?php do_action( 'trs_members_notification_settings_before_submit' ); ?>

					<div class="submit">
						<input type="submit" name="submit" value="<?php _e( 'Save Changes', 'trendr' ); ?>" id="submit" class="auto" />
					</div>

					<?php do_action( 'trs_members_notification_settings_after_submit' ); ?>

					<?php trm_nonce_field('trs_settings_notifications'); ?>

				</form>

				<?php do_action( 'trs_after_member_body' ); ?>

			</div><!-- #item-body -->

			<?php do_action( 'trs_after_member_settings_template' ); ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

<?php get_sidebar( 'trendr' ) ?>

<?php get_footer( 'trendr' ) ?>