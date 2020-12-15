<?php

/**
 * trendr - Members Directory
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php get_header( 'trendr' ); ?>

	<?php do_action( 'trs_before_directory_members_page' ); ?>

	<div id="skeleton"">
		<div class="dimension">

		<?php do_action( 'trs_before_directory_members' ); ?>

		<form action="" method="post" id="members-directory-form" class="dir-form">

			<h3><?php _e( 'Members Directory', 'trendr' ); ?></h3>

			<?php do_action( 'trs_before_directory_members_content' ); ?>

			<div id="members-dir-search" class="dir-search" role="search">

				<?php trs_directory_members_search_form(); ?>

			</div><!-- #members-dir-search -->

			<div class="contour-select" role="navigation">
				<ul>
					<li id="members-all"><a href="<?php echo trailingslashit( trs_get_root_domain() . '/' . trs_get_members_root_slug() ); ?>"><?php printf( __( 'All Members <span>%s</span>', 'trendr' ), trs_get_total_member_count() ); ?></a></li>


					<?php do_action( 'trs_members_directory_member_types' ); ?>

				</ul>
			</div><!-- .contour-select -->

			<div class="contour-select" id="contour-box" role="navigation">
				<ul>

					<?php do_action( 'trs_members_directory_member_sub_types' ); ?>

					<li id="members-order-select" class="last filter">

						<label for="members-order-by"><?php _e( 'Order By:', 'trendr' ); ?></label>
						<select id="members-order-by">
							<option value="active"><?php _e( 'Last Active', 'trendr' ); ?></option>
							<option value="newest"><?php _e( 'Newest Registered', 'trendr' ); ?></option>

							<?php if ( trs_is_active( 'xprofile' ) ) : ?>

								<option value="alphabetical"><?php _e( 'Alphabetical', 'trendr' ); ?></option>

							<?php endif; ?>

							<?php do_action( 'trs_members_directory_order_options' ); ?>

						</select>
					</li>
				</ul>
			</div>

			<div id="members-dir-list" class="members dir-list">

				<?php locate_template( array( 'members/members-loop.php' ), true ); ?>

			</div><!-- #members-dir-list -->

			<?php do_action( 'trs_directory_members_content' ); ?>

			<?php trm_nonce_field( 'directory_members', '_key-member-filter' ); ?>

			<?php do_action( 'trs_after_directory_members_content' ); ?>

		</form><!-- #members-directory-form -->

		<?php do_action( 'trs_after_directory_members' ); ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

	<?php do_action( 'trs_after_directory_members_page' ); ?>



<?php get_footer( 'trendr' ); ?>
