<?php

/**
 * Template Name: trendr - Activity Directory
 *
 * @package trendr
 * @sutrsackage Theme
 */

?>

<?php get_header( 'trendr' ); ?>

	<?php do_action( 'trs_before_directory_activity_page' ); ?>

	<div id="skeleton">
		<div class="dimension">

			<?php do_action( 'trs_before_directory_activity' ); ?>

			<?php if ( !is_user_logged_in() ) : ?>

				<h3><?php _e( 'Site Activity', 'trendr' ); ?></h3>

			<?php endif; ?>

			<?php do_action( 'trs_before_directory_activity_content' ); ?>

			<?php if ( is_user_logged_in() ) : ?>

				<?php locate_template( array( 'activity/post-form.php'), true ); ?>

			<?php endif; ?>

			<?php do_action( 'template_notices' ); ?>

			<div class="contour-select activity-type-tabs" role="navigation">
				<ul>
					<?php do_action( 'trs_before_activity_type_tab_all' ); ?>

					<li class="selected" id="activity-all"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/'; ?>" title="<?php _e( 'The public activity for everyone on this site.', 'trendr' ); ?>"><?php printf( __( 'All <span>%s</span>', 'trendr' ), trs_get_total_site_member_count() ); ?></a></li>



					<?php if ( is_user_logged_in() ) : ?>

						<?php do_action( 'trs_before_activity_type_tab_friends' ) ?>

						<?php if ( trs_is_active( 'friends' ) ) : ?>

							<?php if ( trs_get_total_friend_count( trs_loggedin_user_id() ) ) : ?>

								<li id="activity-friends_featuredposts"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/' . trs_get_friends_slug() . '/'; ?>" title="<?php _e( 'The activity of my friends only.', 'trendr' ); ?>"><?php printf( __( 'My Friends <span>%s</span>', 'trendr' ), trs_get_total_friend_count( trs_loggedin_user_id() ) ); ?></a></li>

							<?php endif; ?>

						<?php endif; ?>

						<?php do_action( 'trs_before_activity_type_tab_groups' ) ?>

						<?php if ( trs_is_active( 'groups' ) ) : ?>

							<?php if ( trs_get_total_group_count_for_user( trs_loggedin_user_id() ) ) : ?>

								<li id="activity-groups"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/' . trs_get_groups_slug() . '/'; ?>" title="<?php _e( 'The activity of groups I am a member of.', 'trendr' ); ?>"><?php printf( __( 'My Groups <span>%s</span>', 'trendr' ), trs_get_total_group_count_for_user( trs_loggedin_user_id() ) ); ?></a></li>

							<?php endif; ?>

						<?php endif; ?>

						<?php do_action( 'trs_before_activity_type_tab_favorites' ); ?>

						<?php if ( trs_get_total_favorite_count_for_user( trs_loggedin_user_id() ) ) : ?>

							<li id="activity-favorites"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/favorites/'; ?>" title="<?php _e( "The activity I've marked as a favorite.", 'trendr' ); ?>"><?php printf( __( 'My Favorites <span>%s</span>', 'trendr' ), trs_get_total_favorite_count_for_user( trs_loggedin_user_id() ) ); ?></a></li>

						<?php endif; ?>

						<?php do_action( 'trs_before_activity_type_tab_mentions' ); ?>

						<li id="activity-mentions"><a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/mentions/'; ?>" title="<?php _e( 'Activity that I have been mentioned in.', 'trendr' ); ?>"><?php _e( 'Mentions', 'trendr' ); ?><?php if ( trs_get_total_mention_count_for_user( trs_loggedin_user_id() ) ) : ?> <strong><?php printf( __( '<span>%s new</span>', 'trendr' ), trs_get_total_mention_count_for_user( trs_loggedin_user_id() ) ); ?></strong><?php endif; ?></a></li>

					<?php endif; ?>


					<?php do_action( 'trs_activity_type_tabs' ); ?>
					<?php if ( is_user_logged_in() ){ ?>
					<li id="activity-groups_friends">
						<a href="<?php echo  trs_loggedin_user_domain() . trs_get_activity_slug() . '/freindsandgroups/' ; ?>" title="<?php _e( 'Activity for my Class Year.', 'trendr' ) ?>">
							<?php printf( __( 'Freinds & Groups <span>0</span>', 'trendr' ) ) ?>
						</a>
					</li>
					<li id="activity-following_recommend">
						<a href="<?php echo  trs_loggedin_user_domain() . trs_get_activity_slug() . '/ReCommendedandFollow/' ; ?>" title="<?php _e( 'Activity for my Class Year.', 'trendr' ) ?>">
							<?php printf( __( 'Recommended & Follow <span>0</span>', 'trendr' ) ) ?>
						</a>
					</li>
<?php } ?>
				</ul>
			</div><!-- .contour-select -->

			<div class="contour-select no-ajax" id="subnav" role="navigation">
				<ul>
					<li class="feed"><a href="<?php trs_sitewide_activity_feed_link() ?>" title="<?php _e( 'RSS Feed', 'trendr' ); ?>"><?php _e( 'RSS', 'trendr' ); ?></a></li>

					<?php do_action( 'trs_activity_syndication_options' ); ?>

					<li id="post-refine" class="last">
						<label for="activity-filter-by"><?php _e( 'Show:', 'trendr' ); ?></label>
						<select id="activity-filter-by">
							<option value="-1"><?php _e( 'Recent', 'trendr' ); ?></option>
							<!-- <option value="activity_update"><?php _e( 'Updates', 'trendr' ); ?></option> -->

							<!-- <?php if ( trs_is_active( 'blogs' ) ) : ?>

								<option value="new_blog_post"><?php _e( 'Posts', 'trendr' ); ?></option>
								<option value="new_blog_comment"><?php _e( 'Comments', 'trendr' ); ?></option>

							<?php endif; ?> -->
<!--
							<?php if ( trs_is_active( 'forums' ) ) : ?>

								<option value="new_forum_topic"><?php _e( 'Forum Topics', 'trendr' ); ?></option>
								<option value="new_forum_post"><?php _e( 'Forum Replies', 'trendr' ); ?></option>

							<?php endif; ?> -->
<!--
							<?php if ( trs_is_active( 'groups' ) ) : ?>

								<option value="created_group"><?php _e( 'New Groups', 'trendr' ); ?></option>
								<option value="joined_group"><?php _e( 'Group Memberships', 'trendr' ); ?></option>

							<?php endif; ?> -->

							<!-- <?php if ( trs_is_active( 'friends' ) ) : ?>

								<option value="friendship_accepted,friendship_created"><?php _e( 'Friendships', 'trendr' ); ?></option>

							<?php endif; ?> -->

							<!-- <option value="new_member"><?php _e( 'New Members', 'trendr' ); ?></option> -->

							<?php do_action( 'trs_activity_filter_options' ); ?>

						</select>
					</li>
				</ul>
			</div><!-- .contour-select -->

			<?php do_action( 'trs_before_directory_activity_list' ); ?>

			<div class="activity" role="main">

				<?php locate_template( array( 'activity/activity-loop.php' ), true ); ?>

			</div><!-- .activity -->

			<?php do_action( 'trs_after_directory_activity_list' ); ?>

			<?php do_action( 'trs_directory_activity_content' ); ?>

			<?php do_action( 'trs_after_directory_activity_content' ); ?>

			<?php do_action( 'trs_after_directory_activity' ); ?>

		</div><!-- .padder -->
	</div><!-- #skeleton -->

	<?php do_action( 'trs_after_directory_activity_page' ); ?>

<?php get_footer( 'trendr' ); ?>
