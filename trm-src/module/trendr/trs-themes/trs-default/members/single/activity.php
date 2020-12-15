<?php

/**
 * trendr - Users Activity
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<div class="contour-select no-ajax" id="subnav" role="navigation">
	<ul>

		<?php trs_get_options_nav() ?>

		<li id="post-refine" class="last">
			<label for="activity-filter-by"><?php _e( 'Show:', 'trendr' ); ?></label>
			<select id="activity-filter-by">
				<option value="-1"><?php _e( 'Everything', 'trendr' ) ?></option>
				<option value="activity_update"><?php _e( 'Updates', 'trendr' ) ?></option>

				<?php
				if ( !trs_is_current_action( 'groups' ) ) :
					if ( trs_is_active( 'blogs' ) ) : ?>

						<option value="new_blog_post"><?php _e( 'Posts', 'trendr' ) ?></option>
						<option value="new_blog_comment"><?php _e( 'Comments', 'trendr' ) ?></option>

					<?php
					endif;

					if ( trs_is_active( 'friends' ) ) : ?>

						<option value="friendship_accepted,friendship_created"><?php _e( 'Friendships', 'trendr' ) ?></option>

					<?php endif;

				endif;

				if ( trs_is_active( 'forums' ) ) : ?>

					<option value="new_forum_topic"><?php _e( 'Forum Topics', 'trendr' ) ?></option>
					<option value="new_forum_post"><?php _e( 'Forum Replies', 'trendr' ) ?></option>

				<?php endif;

				if ( trs_is_active( 'groups' ) ) : ?>

					<option value="created_group"><?php _e( 'New Groups', 'trendr' ) ?></option>
					<option value="joined_group"><?php _e( 'Group Memberships', 'trendr' ) ?></option>

				<?php endif;

				do_action( 'trs_member_activity_filter_options' ); ?>

			</select>
		</li>
	</ul>
</div><!-- .contour-select -->

<?php do_action( 'trs_before_member_activity_post_form' ); ?>

<?php
if ( is_user_logged_in() && trs_is_my_profile() && ( !trs_current_action() || trs_is_current_action( 'just-me' ) ) )
	locate_template( array( 'activity/post-form.php'), true );

do_action( 'trs_after_member_activity_post_form' );
do_action( 'trs_before_member_activity_content' ); ?>

<div class="activity" role="main">

	<?php locate_template( array( 'activity/activity-loop.php' ), true ); ?>

</div><!-- .activity -->

<?php do_action( 'trs_after_member_activity_content' ); ?>
