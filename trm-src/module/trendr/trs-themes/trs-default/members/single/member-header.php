<?php

/**
 * trendr - Users Header
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php do_action( 'trs_before_member_header' ); ?>

<div id="item-header-portrait">
	<a href="<?php trs_user_link(); ?>">

		<?php trs_displayed_user_portrait( 'type=full' ); ?>

	</a>
</div><!-- #item-header-portrait -->

<div id="item-header-content">

	<h2>
		<a href="<?php trs_displayed_user_link(); ?>"><?php trs_displayed_user_fullname(); ?></a>
	</h2>

	<span class="user-nicename">@<?php trs_displayed_user_username(); ?></span>
	<span class="activity"><?php trs_last_activity( trs_displayed_user_id() ); ?></span>

	<?php do_action( 'trs_before_member_header_meta' ); ?>

	<div id="item-meta">

		<?php if ( trs_is_active( 'activity' ) ) : ?>

			<div id="latest-update">

				<?php trs_activity_latest_update( trs_displayed_user_id() ); ?>

			</div>

		<?php endif; ?>

		<div id="item-buttons">

			<?php do_action( 'trs_member_header_actions' ); ?>

		</div><!-- #item-buttons -->

		<?php
		/***
		 * If you'd like to show specific profile fields here use:
		 * trs_profile_field_data( 'field=About Me' ); -- Pass the name of the field
		 */
		 do_action( 'trs_profile_header_meta' );

		 ?>

	</div><!-- #item-meta -->

</div><!-- #item-header-content -->

<?php do_action( 'trs_after_member_header' ); ?>

<?php do_action( 'template_notices' ); ?>