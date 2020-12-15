<?php

/**
 * Template Name: trendr - Activity Directory
 *
					<li class="selected" id="activity-all"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/'; ?>" title="<?php _e( 'The public activity for everyone on this site.', 'trendr' ); ?>"><?php printf( __( 'All Members <span>%s</span><div class=nul></div>', 'trendr' ), trs_get_total_site_member_count() ); ?></a></li>
 * @package trendr
 * @sutrsackage Theme
 */

?>

<?php get_header( 'trendr' ); ?>

	<?php do_action( 'trs_before_directory_activity_page' ); ?>

	<div id="skeleton"">
		<div class="dimension">

	
			<?php do_action( 'trs_before_directory_activity' ); ?>



			<?php do_action( 'trs_before_directory_activity_content' ); ?>

			<?php if ( is_user_logged_in() ) : ?>

				<?php locate_template( array( 'activity/post-form.php'), true ); ?>

			<?php endif; ?>
			<?php do_action( 'template_notices' ); ?>

			<div class="contour-select activity-type-tabs" role="navigation"> <div class="inner"><div class="contour-inner">
				<ul>
										<?php if ( !is_user_logged_in() ) : ?>

					<?php do_action( 'trs_before_activity_type_tab_all' ); ?>


					<li   id="activity-all"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/'; ?>" title="<?php _e( 'The public activity for everyone on this site.', 'trendr' ); ?>"><?php printf( __( 'All Members <span>%s</span><div class=nul></div>', 'trendr' ), trs_get_total_site_member_count() ); ?></a></li>	
						<?php endif; ?>

					<?php if ( is_user_logged_in() ) : ?>
					<li class="selected" id="activity-following_recommend">
						<a href="<?php echo  trs_loggedin_user_domain() . trs_get_activity_slug() . '/ReCommendedandFollow/' ; ?>" title="<?php _e( 'my followers and recommended posts.', 'trendr' ) ?>">
							<?php printf( __( 'public <span>0</span><div class=nul></div>', 'trendr' ) ) ?>
						</a>
					</li>
											
					<li id="activity-groups_friends">
						<a href="<?php echo  trs_loggedin_user_domain() . trs_get_activity_slug() . '/freindsandgroups/' ; ?>" title="<?php _e( 'my friends and groups posts.', 'trendr' ) ?>">
							<?php printf( __( 'private <span>0</span><div class=nul></div>', 'trendr' ) ) ?>
						</a>
					</li>




						<?php do_action( 'trs_before_activity_type_tab_friends' ) ?>

						<?php if ( trs_is_active( 'friends' ) ) : ?>

							<?php if ( trs_get_total_friend_count( trs_loggedin_user_id() ) ) : ?>

								<li id="activity-friends_featuredposts"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/' . trs_get_friends_slug() . '/'; ?>" title="<?php _e( 'The activity of my friends only.', 'trendr' ); ?>"><?php printf( __( 'My Friends <span>%s</span><div class=nul></div>', 'trendr' ), trs_get_total_friend_count( trs_loggedin_user_id() ) ); ?></a></li>

							<?php endif; ?>

						<?php endif; ?>

						<?php do_action( 'trs_before_activity_type_tab_groups' ) ?>

						<?php if ( trs_is_active( 'groups' ) ) : ?>

							<?php if ( trs_get_total_group_count_for_user( trs_loggedin_user_id() ) ) : ?>

								<li id="activity-groups"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/' . trs_get_groups_slug() . '/'; ?>" title="<?php _e( 'The activity of groups I am a member of.', 'trendr' ); ?>"><?php printf( __( 'My Groups <span>%s</span><div class=nul></div>', 'trendr' ), trs_get_total_group_count_for_user( trs_loggedin_user_id() ) ); ?></a></li>

							<?php endif; ?>

						<?php endif; ?>

						<?php do_action( 'trs_before_activity_type_tab_favorites' ); ?>

						
							<li id="activity-favorites"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/favorites/'; ?>" title="<?php _e( "Posts I've marked as a favorite.", 'trendr' ); ?>"><?php printf( __( 'My Favorites <span>%s</span><div class=nul></div>', 'trendr' ), trs_get_total_favorite_count_for_user( trs_loggedin_user_id() ) ); ?></a></li>


						<?php do_action( 'trs_before_activity_type_tab_mentions' ); ?>

						<li id="activity-mentions"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/mentions/'; ?>"
						 title="<?php _e( "Posts I've been mentioned on", 'trendr' ); ?>"><?php _e( 'Mentions', 'trendr' ); ?><?php if ( trs_get_total_mention_count_for_user( trs_loggedin_user_id() ) ) : ?> <strong><?php printf( __( '<span>%s new</span><div class=nul></div>', 'trendr' ), trs_get_total_mention_count_for_user( trs_loggedin_user_id() ) ); ?></strong><?php endif; ?></a></li>
					<?php endif; ?>

					<?php do_action( 'trs_activity_type_tabs' ); ?>

				</ul>
								</ul>

			</div><!-- .contour-select -->
			</div>
            
			</div><!-- .contour-inner -->

		<div class="dimension-inn">


			<?php do_action( 'trs_before_directory_activity_list' ); ?>

			<div class="activity" role="main">

				<?php locate_template( array( 'activity/activity-loop.php' ), true ); ?>

			</div><!-- .activity -->

			<?php do_action( 'trs_after_directory_activity_list' ); ?>

			<?php do_action( 'trs_directory_activity_content' ); ?>

			<?php do_action( 'trs_after_directory_activity_content' ); ?>

			<?php do_action( 'trs_after_directory_activity' ); ?>
		</div><!-- .dimension-inn -->

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

	<?php do_action( 'trs_after_directory_activity_page' ); ?>

<?php get_footer( 'trendr' ); ?>









