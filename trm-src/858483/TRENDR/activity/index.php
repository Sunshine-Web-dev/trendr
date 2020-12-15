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
