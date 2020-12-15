<?php

/**
 * trendr - Groups Directory
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php get_header( 'trendr' ); ?>

	<?php do_action( 'trs_before_directory_groups_page' ); ?>

	<div id="skeleton">
		<div class="dimension">

		<?php do_action( 'trs_before_directory_groups' ); ?>

		<form action="" method="post" id="groups-directory-form" class="dir-form">

			<h3><?php _e( 'Groups Directory', 'trendr' ); ?><?php if ( is_user_logged_in() && trs_user_can_create_groups() ) : ?> &nbsp;<a class="button" href="<?php echo trailingslashit( trs_get_root_domain() . '/' . trs_get_groups_root_slug() . '/create' ); ?>"><?php _e( 'Create a Group', 'trendr' ); ?></a><?php endif; ?></h3>

			<?php do_action( 'trs_before_directory_groups_content' ); ?>

			<div id="group-dir-search" class="dir-search" role="search">

				<?php trs_directory_groups_search_form() ?>

			</div><!-- #group-dir-search -->

			<?php do_action( 'template_notices' ); ?>

			<div class="contour-select" role="navigation">
				<ul>
					<li class="selected" id="groups-all"><a href="<?php echo trailingslashit( trs_get_root_domain() . '/' . trs_get_groups_root_slug() ); ?>"><?php printf( __( 'All Groups <span>%s</span>', 'trendr' ), trs_get_total_group_count() ); ?></a></li>

					<?php if ( is_user_logged_in() && trs_get_total_group_count_for_user( trs_loggedin_user_id() ) ) : ?>

						<li id="groups-personal"><a href="<?php echo trailingslashit( trs_loggedin_user_domain() . trs_get_groups_slug() . '/my-groups' ); ?>"><?php printf( __( 'My Groups <span>%s</span>', 'trendr' ), trs_get_total_group_count_for_user( trs_loggedin_user_id() ) ); ?></a></li>

					<?php endif; ?>

					<?php do_action( 'trs_groups_directory_group_filter' ); ?>

				</ul>
			</div><!-- .contour-select -->

			<div class="contour-select" id="subnav" role="navigation">
				<ul>

					<?php do_action( 'trs_groups_directory_group_types' ); ?>

					<li id="groups-order-select" class="last filter">

						<label for="groups-order-by"><?php _e( 'Order By:', 'trendr' ); ?></label>
						<select id="groups-order-by">
							<option value="active"><?php _e( 'Last Active', 'trendr' ); ?></option>
							<option value="popular"><?php _e( 'Most Members', 'trendr' ); ?></option>
							<option value="newest"><?php _e( 'Newly Created', 'trendr' ); ?></option>
							<option value="alphabetical"><?php _e( 'Alphabetical', 'trendr' ); ?></option>

							<?php do_action( 'trs_groups_directory_order_options' ); ?>

						</select>
					</li>
				</ul>
			</div>

			<div id="groups-dir-list" class="groups dir-list">

				<?php locate_template( array( 'groups/groups-loop.php' ), true ); ?>

			</div><!-- #groups-dir-list -->

			<?php do_action( 'trs_directory_groups_content' ); ?>

			<?php trm_nonce_field( 'directory_groups', '_key-groups-filter' ); ?>

			<?php do_action( 'trs_after_directory_groups_content' ); ?>

		</form><!-- #groups-directory-form -->

		<?php do_action( 'trs_after_directory_groups' ); ?>

		</div><!-- .padder -->
	</div><!-- #skeleton -->

	<?php do_action( 'trs_after_directory_groups_page' ); ?>

<?php get_sidebar( 'trendr' ); ?>
<?php get_footer( 'trendr' ); ?>

