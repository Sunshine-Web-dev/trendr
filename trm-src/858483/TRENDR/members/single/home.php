<?php

/**
 * Trnder - Users Home
 *
 * @package Trnder
 * @sutrsackage trs-default
 */
?>
	

<?php get_header( 'trendr' ); ?>
	<div id="skeleton">
		<div class="dimension">



			<div id="contour" role="complementary">
	
				<?php locate_template( array( 'members/single/member-header.php' ), true ); ?>

			</div><!-- #contour -->


			<div id="figure">

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

				//elseif ( trs_is_user_forums() ) :
				//	locate_template( array( 'members/single/forums.php'    ), true );

				elseif ( trs_is_user_settings() ) :
					locate_template( array( 'members/single/settings.php'  ), true );

				// If nothing sticks, load a generic template
				else :
					locate_template( array( 'members/single/module.php'   ), true );

				endif;

				do_action( 'trs_after_member_body' ); ?>

			</div><!-- #figure -->

			<?php do_action( 'trs_after_member_home_content' ); ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

<?php get_footer( 'trendr' ); ?>
