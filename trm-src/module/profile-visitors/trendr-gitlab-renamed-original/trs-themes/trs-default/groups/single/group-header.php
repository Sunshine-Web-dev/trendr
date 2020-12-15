<?php

do_action( 'trs_before_group_header' );

?>

<div id="item-actions">

	<?php if ( trs_group_is_visible() ) : ?>

		<h3><?php _e( 'Group Admins', 'trendr' ); ?></h3>

		<?php trs_group_list_admins();

		do_action( 'trs_after_group_menu_admins' );

		if ( trs_group_has_moderators() ) :
			do_action( 'trs_before_group_menu_mods' ); ?>

			<h3><?php _e( 'Group Mods' , 'trendr' ) ?></h3>

			<?php trs_group_list_mods();

			do_action( 'trs_after_group_menu_mods' );

		endif;

	endif; ?>

</div><!-- #item-actions -->

<div id="item-header-portrait">
	<a href="<?php trs_group_permalink(); ?>" title="<?php trs_group_name(); ?>">

		<?php trs_group_portrait(); ?>

	</a>
</div><!-- #item-header-portrait -->

<div id="item-header-content">
	<h2><a href="<?php trs_group_permalink(); ?>" title="<?php trs_group_name(); ?>"><?php trs_group_name(); ?></a></h2>
	<span class="highlight"><?php trs_group_type(); ?></span> <span class="activity"><?php printf( __( 'active %s', 'trendr' ), trs_get_group_last_active() ); ?></span>

	<?php do_action( 'trs_before_group_header_meta' ); ?>

	<div id="item-meta">

		<?php trs_group_description(); ?>

		<div id="item-buttons">

			<?php do_action( 'trs_group_header_actions' ); ?>

		</div><!-- #item-buttons -->

		<?php do_action( 'trs_group_header_meta' ); ?>

	</div>
</div><!-- #item-header-content -->

<?php
do_action( 'trs_after_group_header' );
do_action( 'template_notices' );
?>