<?php get_header( 'trendr' ); ?>

	<div id="skeleton">
		<div class="dimension">

			<?php if ( trs_has_groups() ) : while ( trs_groups() ) : trs_the_group(); ?>

			<?php do_action( 'trs_before_group_home_content' ) ?>

			<div id="item-header" role="complementary">

				<?php locate_template( array( 'groups/single/group-header.php' ), true ); ?>

			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="contour-select no-ajax" id="object-nav" role="navigation">
					<ul>

						<?php trs_get_options_nav(); ?>

						<?php do_action( 'trs_group_options_nav' ); ?>

					</ul>
				</div>
			</div><!-- #item-nav -->

			<div id="item-body">

				<?php do_action( 'trs_before_group_body' );

				if ( trs_is_group_admin_page() && trs_group_is_visible() ) :
					locate_template( array( 'groups/single/admin.php' ), true );

				elseif ( trs_is_group_members() && trs_group_is_visible() ) :
					locate_template( array( 'groups/single/members.php' ), true );

				elseif ( trs_is_group_invites() && trs_group_is_visible() ) :
					locate_template( array( 'groups/single/send-invites.php' ), true );

					elseif ( trs_is_group_forum() && trs_group_is_visible() && trs_is_active( 'forums' ) && trs_forums_is_installed_correctly() ) :
						locate_template( array( 'groups/single/forum.php' ), true );

				elseif ( trs_is_group_membership_request() ) :
					locate_template( array( 'groups/single/request-membership.php' ), true );

				elseif ( trs_group_is_visible() && trs_is_active( 'activity' ) ) :
					locate_template( array( 'groups/single/activity.php' ), true );

				elseif ( trs_group_is_visible() ) :
					locate_template( array( 'groups/single/members.php' ), true );

				elseif ( !trs_group_is_visible() ) :
					// The group is not visible, show the status message

					do_action( 'trs_before_group_status_message' ); ?>

					<div id="message" class="info">
						<p><?php trs_group_status_message(); ?></p>
					</div>

					<?php do_action( 'trs_after_group_status_message' );

				else :
					// If nothing sticks, just load a group front template if one exists.
					locate_template( array( 'groups/single/front.php' ), true );

				endif;

				do_action( 'trs_after_group_body' ); ?>

			</div><!-- #item-body -->

			<?php do_action( 'trs_after_group_home_content' ); ?>

			<?php endwhile; endif; ?>

		</div><!-- .padder -->
	</div><!-- #skeleton -->

<?php get_sidebar( 'trendr' ); ?>
<?php get_footer( 'trendr' ); ?>
