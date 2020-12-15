<div class="contour-select no-ajax" id="contour-box" role="navigation">
	<ul>
		<li class="feed"><a href="<?php trs_group_activity_feed_link() ?>" title="<?php _e( 'RSS Feed', 'trendr' ); ?>"><?php _e( 'RSS', 'trendr' ) ?></a></li>

		<?php do_action( 'trs_group_activity_syndication_options' ) ?>

		<li id="post-refine" class="last">
			<label for="post-refine-by"><?php _e( 'Show:', 'trendr' ); ?></label> 
			<select id="post-refine-by">
				<option value="-1"><?php _e( 'Everything', 'trendr' ) ?></option>
				<option value="activity_update"><?php _e( 'Updates', 'trendr' ) ?></option>

				<?php if ( trs_is_active( 'forums' ) ) : ?>
					<option value="new_forum_topic"><?php _e( 'Forum Topics', 'trendr' ) ?></option>
					<option value="new_forum_post"><?php _e( 'Forum Replies', 'trendr' ) ?></option>
				<?php endif; ?>

				<option value="joined_group"><?php _e( 'Group Memberships', 'trendr' ) ?></option>

				<?php do_action( 'trs_group_activity_filter_options' ) ?>
			</select>
		</li>
	</ul>
</div><!-- .contour-select -->

<?php do_action( 'trs_before_group_activity_post_form' ) ?>

<?php if ( is_user_logged_in() && trs_group_is_member() ) : ?>
	<?php locate_template( array( 'activity/post-form.php'), true ) ?>
<?php endif; ?>

<?php do_action( 'trs_after_group_activity_post_form' ) ?>
<?php do_action( 'trs_before_group_activity_content' ) ?>

<div class="activity single-group" role="main">
	<?php locate_template( array( 'activity/activity-loop.php' ), true ) ?>
</div><!-- .activity.single-group -->

<?php do_action( 'trs_after_group_activity_content' ) ?>
