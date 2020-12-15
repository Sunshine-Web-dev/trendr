<?php

/**
 * trendr - Forums Directory
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php get_header( 'trendr' ); ?>

	<?php do_action( 'trs_before_directory_forums_page' ); ?>

	<div id="skeleton">
		<div class="dimension">

			<?php do_action( 'trs_before_directory_forums' ); ?>

			<form action="" method="post" id="forums-search-form" class="dir-form">

				<h3><?php _e( 'Forums Directory', 'trendr' ); ?><?php if ( is_user_logged_in() ) : ?> &nbsp;<a class="button show-hide-new" href="#new-topic" id="new-topic-button"><?php _e( 'New Topic', 'trendr' ); ?></a><?php endif; ?></h3>

				<?php do_action( 'trs_before_directory_forums_content' ); ?>

				<div id="forums-dir-search" class="dir-search" role="search">

					<?php trs_directory_forums_search_form(); ?>

				</div>
			</form>

			<?php do_action( 'trs_before_topics' ); ?>

			<form action="" method="post" id="forums-directory-form" class="dir-form">

				<div class="contour-select" role="navigation">
					<ul>
						<li class="selected" id="forums-all"><a href="<?php echo trailingslashit( trs_get_root_domain() . '/' . trs_get_forums_root_slug() ); ?>"><?php printf( __( 'All Topics <span>%s</span>', 'trendr' ), trs_get_forum_topic_count() ); ?></a></li>

						<?php if ( is_user_logged_in() && trs_get_forum_topic_count_for_user( trs_loggedin_user_id() ) ) : ?>

							<li id="forums-personal"><a href="<?php echo trailingslashit( trs_loggedin_user_domain() . trs_get_forums_slug() . '/topics' ); ?>"><?php printf( __( 'My Topics <span>%s</span>', 'trendr' ), trs_get_forum_topic_count_for_user( trs_loggedin_user_id() ) ); ?></a></li>

						<?php endif; ?>

						<?php do_action( 'trs_forums_directory_group_types' ); ?>

					</ul>
				</div>

				<div class="contour-select" id="subnav" role="navigation">
					<ul>

						<?php do_action( 'trs_forums_directory_group_sub_types' ); ?>

						<li id="forums-order-select" class="last filter">

							<label for="forums-order-by"><?php _e( 'Order By:', 'trendr' ); ?></label>
							<select id="forums-order-by">
								<option value="active"><?php _e( 'Last Active', 'trendr' ); ?></option>
								<option value="popular"><?php _e( 'Most Posts', 'trendr' ); ?></option>
								<option value="unreplied"><?php _e( 'Unreplied', 'trendr' ); ?></option>

								<?php do_action( 'trs_forums_directory_order_options' ); ?>

							</select>
						</li>
					</ul>
				</div>

				<div id="forums-dir-list" class="forums dir-list" role="main">

					<?php locate_template( array( 'forums/forums-loop.php' ), true ); ?>

				</div>

				<?php do_action( 'trs_directory_forums_content' ); ?>

				<?php trm_nonce_field( 'directory_forums', '_key-forums-filter' ); ?>

			</form>

			<?php do_action( 'trs_after_directory_forums' ); ?>

			<?php do_action( 'trs_before_new_topic_form' ); ?>

			<div id="new-topic-post">

				<?php if ( is_user_logged_in() ) : ?>

					<?php if ( trs_is_active( 'groups' ) && trs_has_groups( 'user_id=' . trs_loggedin_user_id() . '&type=alphabetical&max=100&per_page=100' ) ) : ?>

						<form action="" method="post" id="forum-topic-form" class="standard-form">

							<?php do_action( 'groups_forum_new_topic_before' ) ?>

							<a name="post-new"></a>
							<h5><?php _e( 'Create New Topic:', 'trendr' ); ?></h5>

							<?php do_action( 'template_notices' ); ?>

							<label><?php _e( 'Title:', 'trendr' ); ?></label>
							<input type="text" name="topic_title" id="topic_title" value="" />

							<label><?php _e( 'Content:', 'trendr' ); ?></label>
							<textarea name="topic_text" id="topic_text"></textarea>

							<label><?php _e( 'Tags (comma separated):', 'trendr' ); ?></label>
							<input type="text" name="topic_tags" id="topic_tags" value="" />

							<label><?php _e( 'Post In Group Forum:', 'trendr' ); ?></label>
							<select id="topic_group_id" name="topic_group_id">

								<option value=""><?php /* translators: no option picked in select box */ _e( '----', 'trendr' ); ?></option>

								<?php while ( trs_groups() ) : trs_the_group(); ?>

									<?php if ( trs_group_is_forum_enabled() && ( is_super_admin() || 'public' == trs_get_group_status() || trs_group_is_member() ) ) : ?>

										<option value="<?php trs_group_id(); ?>"><?php trs_group_name(); ?></option>

									<?php endif; ?>

								<?php endwhile; ?>

							</select><!-- #topic_group_id -->

							<?php do_action( 'groups_forum_new_topic_after' ); ?>

							<div class="submit">
								<input type="submit" name="submit_topic" id="submit" value="<?php _e( 'Post Topic', 'trendr' ); ?>" />
								<input type="button" name="submit_topic_cancel" id="submit_topic_cancel" value="<?php _e( 'Cancel', 'trendr' ); ?>" />
							</div>

							<?php trm_nonce_field( 'trs_forums_new_topic' ); ?>

						</form><!-- #forum-topic-form -->

					<?php elseif ( trs_is_active( 'groups' ) ) : ?>

						<div id="message" class="info">

							<p><?php printf( __( "You are not a member of any groups so you don't have any group forums you can post in. To start posting, first find a group that matches the topic subject you'd like to start. If this group does not exist, why not <a href='%s'>create a new group</a>? Once you have joined or created the group you can post your topic in that group's forum.", 'trendr' ), site_url( trs_get_groups_root_slug() . '/create/' ) ) ?></p>

						</div>

					<?php endif; ?>

				<?php endif; ?>
			</div><!-- #new-topic-post -->

			<?php do_action( 'trs_after_new_topic_form' ); ?>

			<?php do_action( 'trs_after_directory_forums_content' ); ?>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->

	<?php do_action( 'trs_after_directory_forums_page' ); ?>

<?php get_sidebar( 'trendr' ); ?>
<?php get_footer( 'trendr' ); ?>
