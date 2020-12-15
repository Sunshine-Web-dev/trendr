<?php

/**
 * trendr - Users Home
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php get_header( 'trendr' ); ?>

	<div id="skeleton">
		<div class="dimension">

			<?php do_action( 'trs_before_member_home_content' ); ?>

			<div id="item-header" role="complementary">

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

			<div id="item-body">

				<?php do_action( 'trs_before_member_body' );

				if ( trs_is_user_activity() || !trs_current_component() ) :
					locate_template( array( 'members/single/activity.php'  ), true );

				 elseif ( trs_is_user_blogs() ) :
					locate_template( array( 'members/single/blogs.php'     ), true );

				elseif ( trs_is_user_friends() ) :
					locate_template( array( 'members/single/friends.php'   ), true );

				elseif ( trs_is_user_groups() ) :
					locate_template( array( 'members/single/groups.php'    ), true );

				elseif ( trs_is_user_messages() ) :
					locate_template( array( 'members/single/messages.php'  ), true );

				elseif ( trs_is_user_profile() ) :
					locate_template( array( 'members/single/profile.php'   ), true );

				elseif ( trs_is_user_forums() ) :
					locate_template( array( 'members/single/forums.php'    ), true );

				elseif ( trs_is_user_settings() ) :
					locate_template( array( 'members/single/settings.php'  ), true );

				// If nothing sticks, load a generic template
				else :
					locate_template( array( 'members/single/plugins.php'   ), true );

				endif;

				do_action( 'trs_after_member_body' ); ?>

			</div><!-- #item-body -->

			<?php do_action( 'trs_after_member_home_content' ); ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

<?php get_sidebar( 'trendr' ); ?>
<?php get_footer( 'trendr' ); ?>
