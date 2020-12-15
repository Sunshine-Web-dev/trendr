<?php

/**
 * trendr - Create Group
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php get_header( 'trendr' ); ?>

	<div id="skeleton"">
		<div class="dimension">

		<form action="<?php trs_group_creation_form_action(); ?>" method="post" id="create-group-form" class="standard-form" enctype="multipart/form-data">
			<h3><?php _e( 'Create a Group', 'trendr' ); ?> &nbsp;<a class="button" href="<?php echo trailingslashit( trs_get_root_domain() . '/' . trs_get_groups_root_slug() ); ?>"><?php _e( 'Groups Directory', 'trendr' ); ?></a></h3>

			<?php do_action( 'trs_before_create_group' ); ?>

			<div class="contour-select no-ajax" id="group-create-tabs" role="navigation">
				<ul>

					<?php trs_group_creation_tabs(); ?>

				</ul>
			</div>

			<?php do_action( 'template_notices' ); ?>

			<div class="item-body" id="group-create-body">

				<?php /* Group creation step 1: Basic group details */ ?>
				<?php if ( trs_is_group_creation_step( 'group-details' ) ) : ?>

					<?php do_action( 'trs_before_group_details_creation_step' ); ?>

					<label for="group-name"><?php _e( 'Group Name (required)', 'trendr' ); ?></label>
					<input type="text" name="group-name" id="group-name" aria-required="true" value="<?php trs_new_group_name(); ?>" />

					<label for="group-desc"><?php _e( 'Group Description (required)', 'trendr' ) ?></label>
					<textarea name="group-desc" id="group-desc" aria-required="true"><?php trs_new_group_description(); ?></textarea>

					<?php
					do_action( 'trs_after_group_details_creation_step' );
					do_action( 'groups_custom_group_fields_editable' ); // @Deprecated

					trm_nonce_field( 'groups_create_save_group-details' ); ?>

				<?php endif; ?>

				<?php /* Group creation step 2: Group settings */ ?>
				<?php if ( trs_is_group_creation_step( 'group-settings' ) ) : ?>

					<?php do_action( 'trs_before_group_settings_creation_step' ); ?>

					<?php if ( trs_is_active( 'forums' ) ) : ?>
						<?php if ( trs_forums_is_installed_correctly() ) : ?>

							<div class="checkbox">
								<label><input type="checkbox" name="group-show-forum" id="group-show-forum" value="1"<?php checked( trs_get_new_group_enable_forum(), true, true ); ?> /> <?php _e( 'Enable discussion forum', 'trendr' ); ?></label>
							</div>

						<?php else : ?>
							<?php if ( is_super_admin() ) : ?>

								<div class="checkbox">
									<label><input type="checkbox" disabled="disabled" name="disabled" id="disabled" value="0" /> <?php printf( __( '<strong>Attention Site Admin:</strong> Group forums require the <a href="%s">correct setup and configuration</a> of a bbPress installation.', 'trendr' ), trs_get_root_domain() . '/Backend-WeaprEcqaKejUbRq-trendr/admin.php?page=bb-forums-setup' ); ?></label>
								</div>

							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>

					<hr />

					<h4><?php _e( 'Privacy Options', 'trendr' ); ?></h4>

					<div class="radio">
						<label><input type="radio" name="group-status" value="public"<?php if ( 'public' == trs_get_new_group_status() || !trs_get_new_group_status() ) { ?> checked="checked"<?php } ?> />
							<strong><?php _e( 'This is a public group', 'trendr' ); ?></strong>
							<ul>
								<li><?php _e( 'Any site member can join this group.', 'trendr' ); ?></li>
								<li><?php _e( 'This group will be listed in the groups directory and in search results.', 'trendr' ); ?></li>
								<li><?php _e( 'Group content and activity will be visible to any site member.', 'trendr' ); ?></li>
							</ul>
						</label>

						<label><input type="radio" name="group-status" value="private"<?php if ( 'private' == trs_get_new_group_status() ) { ?> checked="checked"<?php } ?> />
							<strong><?php _e( 'This is a private group', 'trendr' ); ?></strong>
							<ul>
								<li><?php _e( 'Only users who request membership and are accepted can join the group.', 'trendr' ); ?></li>
								<li><?php _e( 'This group will be listed in the groups directory and in search results.', 'trendr' ); ?></li>
								<li><?php _e( 'Group content and activity will only be visible to members of the group.', 'trendr' ); ?></li>
							</ul>
						</label>

						<label><input type="radio" name="group-status" value="hidden"<?php if ( 'hidden' == trs_get_new_group_status() ) { ?> checked="checked"<?php } ?> />
							<strong><?php _e('This is a hidden group', 'trendr'); ?></strong>
							<ul>
								<li><?php _e( 'Only users who are invited can join the group.', 'trendr' ); ?></li>
								<li><?php _e( 'This group will not be listed in the groups directory or search results.', 'trendr' ); ?></li>
								<li><?php _e( 'Group content and activity will only be visible to members of the group.', 'trendr' ); ?></li>
							</ul>
						</label>
					</div>

					<hr />

					<h4><?php _e( 'Group Invitations', 'trendr' ); ?></h4>

					<p><?php _e( 'Which members of this group are allowed to invite others?', 'trendr' ) ?></p>

					<div class="radio">
						<label>
							<input type="radio" name="group-invite-status" value="members"<?php trs_group_show_invite_status_setting( 'members' ) ?> />
							<strong><?php _e( 'All group members', 'trendr' ) ?></strong>
						</label>

						<label>
							<input type="radio" name="group-invite-status" value="mods"<?php trs_group_show_invite_status_setting( 'mods' ) ?> />
							<strong><?php _e( 'Group admins and mods only', 'trendr' ) ?></strong>
						</label>

						<label>
							<input type="radio" name="group-invite-status" value="admins"<?php trs_group_show_invite_status_setting( 'admins' ) ?> />
							<strong><?php _e( 'Group admins only', 'trendr' ) ?></strong>
						</label>
					</div>

					<hr />

					<?php do_action( 'trs_after_group_settings_creation_step' ); ?>

					<?php trm_nonce_field( 'groups_create_save_group-settings' ); ?>

				<?php endif; ?>

				<?php /* Group creation step 3: Avatar Uploads */ ?>
				<?php if ( trs_is_group_creation_step( 'group-portrait' ) ) : ?>

					<?php do_action( 'trs_before_group_portrait_creation_step' ); ?>

					<?php if ( 'upload-image' == trs_get_portrait_admin_step() ) : ?>

						<div class="left-menu">

							<?php trs_new_group_portrait(); ?>

						</div><!-- .left-menu -->

						<div class="main-column">
							<p><?php _e( "Upload an image to use as an portrait for this group. The image will be shown on the main group page, and in search results.", 'trendr' ); ?></p>

							<p>
								<input type="file" name="file" id="file" />
								<input type="submit" name="upload" id="upload" value="<?php _e( 'Upload Image', 'trendr' ); ?>" />
								<input type="hidden" name="action" id="action" value="trs_portrait_upload" />
							</p>

							<p><?php _e( 'To skip the portrait upload process, hit the "Next Step" button.', 'trendr' ); ?></p>
						</div><!-- .main-column -->

					<?php endif; ?>

					<?php if ( 'crop-image' == trs_get_portrait_admin_step() ) : ?>

						<h3><?php _e( 'Crop Group Avatar', 'trendr' ); ?></h3>

						<img src="<?php trs_portrait_to_crop(); ?>" id="portrait-to-crop" class="portrait" alt="<?php _e( 'Avatar to crop', 'trendr' ); ?>" />

						<div id="portrait-crop-pane">
							<img src="<?php trs_portrait_to_crop(); ?>" id="portrait-crop-preview" class="portrait" alt="<?php _e( 'Avatar preview', 'trendr' ); ?>" />
						</div>

						<input type="submit" name="portrait-crop-submit" id="portrait-crop-submit" value="<?php _e( 'Crop Image', 'trendr' ); ?>" />

						<input type="hidden" name="image_src" id="image_src" value="<?php trs_portrait_to_crop_src(); ?>" />
						<input type="hidden" name="upload" id="upload" />
						<input type="hidden" id="x" name="x" />
						<input type="hidden" id="y" name="y" />
						<input type="hidden" id="w" name="w" />
						<input type="hidden" id="h" name="h" />

					<?php endif; ?>

					<?php do_action( 'trs_after_group_portrait_creation_step' ); ?>

					<?php trm_nonce_field( 'groups_create_save_group-portrait' ); ?>

				<?php endif; ?>

				<?php /* Group creation step 4: Invite friends to group */ ?>
				<?php if ( trs_is_group_creation_step( 'group-invites' ) ) : ?>

					<?php do_action( 'trs_before_group_invites_creation_step' ); ?>

					<?php if ( trs_is_active( 'friends' ) && trs_get_total_friend_count( trs_loggedin_user_id() ) ) : ?>

						<div class="left-menu">

							<div id="invite-list">
								<ul>
									<?php trs_new_group_invite_friend_list(); ?>
								</ul>

								<?php trm_nonce_field( 'groups_invite_uninvite_user', '_key_invite_uninvite_user' ); ?>
							</div>

						</div><!-- .left-menu -->

						<div class="main-column">

							<div id="message" class="info">
								<p><?php _e('Select people to invite from your friends list.', 'trendr'); ?></p>
							</div>

							<?php /* The ID 'friend-list' is important for AJAX support. */ ?>
							<ul id="friend-list" class="article-piece" role="main">

							<?php if ( trs_group_has_invites() ) : ?>

								<?php while ( trs_group_invites() ) : trs_group_the_invite(); ?>

									<li id="<?php trs_group_invite_item_id(); ?>">

										<?php trs_group_invite_user_portrait(); ?>

										<h4><?php trs_group_invite_user_link(); ?></h4>
										<span class="activity"><?php trs_group_invite_user_last_active(); ?></span>

										<div class="action">
											<a class="remove" href="<?php trs_group_invite_user_remove_invite_url(); ?>" id="<?php trs_group_invite_item_id(); ?>"><?php _e( 'Remove Invite', 'trendr' ); ?></a>
										</div>
									</li>

								<?php endwhile; ?>

								<?php trm_nonce_field( 'groups_send_invites', '_key_send_invites' ); ?>

							<?php endif; ?>

							</ul>

						</div><!-- .main-column -->

					<?php else : ?>

						<div id="message" class="info">
							<p><?php _e( 'Once you have built up friend connections you will be able to invite others to your group. You can send invites any time in the future by selecting the "Send Invites" option when viewing your new group.', 'trendr' ); ?></p>
						</div>

					<?php endif; ?>

					<?php trm_nonce_field( 'groups_create_save_group-invites' ); ?>

					<?php do_action( 'trs_after_group_invites_creation_step' ); ?>

				<?php endif; ?>

				<?php do_action( 'groups_custom_create_steps' ); // Allow plugins to add custom group creation steps ?>

				<?php do_action( 'trs_before_group_creation_step_buttons' ); ?>

				<?php if ( 'crop-image' != trs_get_portrait_admin_step() ) : ?>

					<div class="submit" id="previous-next">

						<?php /* Previous Button */ ?>
						<?php if ( !trs_is_first_group_creation_step() ) : ?>

							<input type="button" value="<?php _e( 'Back to Previous Step', 'trendr' ); ?>" id="group-creation-previous" name="previous" onclick="location.href='<?php trs_group_creation_previous_link(); ?>'" />

						<?php endif; ?>

						<?php /* Next Button */ ?>
						<?php if ( !trs_is_last_group_creation_step() && !trs_is_first_group_creation_step() ) : ?>

							<input type="submit" value="<?php _e( 'Next Step', 'trendr' ); ?>" id="group-creation-next" name="save" />

						<?php endif;?>

						<?php /* Create Button */ ?>
						<?php if ( trs_is_first_group_creation_step() ) : ?>

							<input type="submit" value="<?php _e( 'Create Group and Continue', 'trendr' ); ?>" id="group-creation-create" name="save" />

						<?php endif; ?>

						<?php /* Finish Button */ ?>
						<?php if ( trs_is_last_group_creation_step() ) : ?>

							<input type="submit" value="<?php _e( 'Finish', 'trendr' ); ?>" id="group-creation-finish" name="save" />

						<?php endif; ?>
					</div>

				<?php endif;?>

				<?php do_action( 'trs_after_group_creation_step_buttons' ); ?>

				<?php /* Don't leave out this hidden field */ ?>
				<input type="hidden" name="group_id" id="group_id" value="<?php trs_new_group_id(); ?>" />

				<?php do_action( 'trs_directory_groups_content' ); ?>

			</div><!-- .item-body -->

			<?php do_action( 'trs_after_create_group' ); ?>

		</form>

		</div><!-- .dimension -->
	</div><!-- #skeleton -->


<?php get_footer( 'trendr' ); ?>
