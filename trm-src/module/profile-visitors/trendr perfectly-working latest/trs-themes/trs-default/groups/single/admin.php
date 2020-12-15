<div class="contour-select no-ajax" id="subnav" role="navigation">
	<ul>
		<?php trs_group_admin_tabs(); ?>
	</ul>
</div><!-- .contour-select -->

<form action="<?php trs_group_admin_form_action() ?>" name="group-settings-form" id="group-settings-form" class="standard-form" method="post" enctype="multipart/form-data" role="main">

<?php do_action( 'trs_before_group_admin_content' ) ?>

<?php /* Edit Group Details */ ?>
<?php if ( trs_is_group_admin_screen( 'edit-details' ) ) : ?>

	<?php do_action( 'trs_before_group_details_admin' ); ?>

	<label for="group-name"><?php _e( 'Group Name (required)', 'trendr' ); ?></label>
	<input type="text" name="group-name" id="group-name" value="<?php trs_group_name() ?>" aria-required="true" />

	<label for="group-desc"><?php _e( 'Group Description (required)', 'trendr' ); ?></label>
	<textarea name="group-desc" id="group-desc" aria-required="true"><?php trs_group_description_editable() ?></textarea>

	<?php do_action( 'groups_custom_group_fields_editable' ) ?>

	<p>
		<label for="group-notifiy-members"><?php _e( 'Notify group members of changes via email', 'trendr' ); ?></label>
		<input type="radio" name="group-notify-members" value="1" /> <?php _e( 'Yes', 'trendr' ); ?>&nbsp;
		<input type="radio" name="group-notify-members" value="0" checked="checked" /> <?php _e( 'No', 'trendr' ); ?>&nbsp;
	</p>

	<?php do_action( 'trs_after_group_details_admin' ); ?>

	<p><input type="submit" value="<?php _e( 'Save Changes', 'trendr' ) ?>" id="save" name="save" /></p>
	<?php trm_nonce_field( 'groups_edit_group_details' ) ?>

<?php endif; ?>

<?php /* Manage Group Settings */ ?>
<?php if ( trs_is_group_admin_screen( 'group-settings' ) ) : ?>

	<?php do_action( 'trs_before_group_settings_admin' ); ?>

	<?php if ( trs_is_active( 'forums' ) ) : ?>

		<?php if ( trs_forums_is_installed_correctly() ) : ?>

			<div class="checkbox">
				<label><input type="checkbox" name="group-show-forum" id="group-show-forum" value="1"<?php trs_group_show_forum_setting() ?> /> <?php _e( 'Enable discussion forum', 'trendr' ) ?></label>
			</div>

			<hr />

		<?php endif; ?>

	<?php endif; ?>

	<h4><?php _e( 'Privacy Options', 'trendr' ); ?></h4>

	<div class="radio">
		<label>
			<input type="radio" name="group-status" value="public"<?php trs_group_show_status_setting( 'public' ) ?> />
			<strong><?php _e( 'This is a public group', 'trendr' ) ?></strong>
			<ul>
				<li><?php _e( 'Any site member can join this group.', 'trendr' ) ?></li>
				<li><?php _e( 'This group will be listed in the groups directory and in search results.', 'trendr' ) ?></li>
				<li><?php _e( 'Group content and activity will be visible to any site member.', 'trendr' ) ?></li>
			</ul>
		</label>

		<label>
			<input type="radio" name="group-status" value="private"<?php trs_group_show_status_setting( 'private' ) ?> />
			<strong><?php _e( 'This is a private group', 'trendr' ) ?></strong>
			<ul>
				<li><?php _e( 'Only users who request membership and are accepted can join the group.', 'trendr' ) ?></li>
				<li><?php _e( 'This group will be listed in the groups directory and in search results.', 'trendr' ) ?></li>
				<li><?php _e( 'Group content and activity will only be visible to members of the group.', 'trendr' ) ?></li>
			</ul>
		</label>

		<label>
			<input type="radio" name="group-status" value="hidden"<?php trs_group_show_status_setting( 'hidden' ) ?> />
			<strong><?php _e( 'This is a hidden group', 'trendr' ) ?></strong>
			<ul>
				<li><?php _e( 'Only users who are invited can join the group.', 'trendr' ) ?></li>
				<li><?php _e( 'This group will not be listed in the groups directory or search results.', 'trendr' ) ?></li>
				<li><?php _e( 'Group content and activity will only be visible to members of the group.', 'trendr' ) ?></li>
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

	<?php do_action( 'trs_after_group_settings_admin' ); ?>

	<p><input type="submit" value="<?php _e( 'Save Changes', 'trendr' ) ?>" id="save" name="save" /></p>
	<?php trm_nonce_field( 'groups_edit_group_settings' ) ?>

<?php endif; ?>

<?php /* Group Avatar Settings */ ?>
<?php if ( trs_is_group_admin_screen( 'group-portrait' ) ) : ?>

	<?php if ( 'upload-image' == trs_get_portrait_admin_step() ) : ?>

			<p><?php _e("Upload an image to use as an portrait for this group. The image will be shown on the main group page, and in search results.", 'trendr') ?></p>

			<p>
				<input type="file" name="file" id="file" />
				<input type="submit" name="upload" id="upload" value="<?php _e( 'Upload Image', 'trendr' ) ?>" />
				<input type="hidden" name="action" id="action" value="trs_portrait_upload" />
			</p>

			<?php if ( trs_get_group_has_portrait() ) : ?>

				<p><?php _e( "If you'd like to remove the existing portrait but not upload a new one, please use the delete portrait button.", 'trendr' ) ?></p>

				<?php trs_button( array( 'id' => 'delete_group_portrait', 'component' => 'groups', 'wrapper_id' => 'delete-group-portrait-button', 'link_class' => 'edit', 'link_href' => trs_get_group_portrait_delete_link(), 'link_title' => __( 'Delete Avatar', 'trendr' ), 'link_text' => __( 'Delete Avatar', 'trendr' ) ) ); ?>

			<?php endif; ?>

			<?php trm_nonce_field( 'trs_portrait_upload' ) ?>

	<?php endif; ?>

	<?php if ( 'crop-image' == trs_get_portrait_admin_step() ) : ?>

		<h3><?php _e( 'Crop Avatar', 'trendr' ) ?></h3>

		<img src="<?php trs_portrait_to_crop() ?>" id="portrait-to-crop" class="portrait" alt="<?php _e( 'Avatar to crop', 'trendr' ) ?>" />

		<div id="portrait-crop-pane">
			<img src="<?php trs_portrait_to_crop() ?>" id="portrait-crop-preview" class="portrait" alt="<?php _e( 'Avatar preview', 'trendr' ) ?>" />
		</div>

		<input type="submit" name="portrait-crop-submit" id="portrait-crop-submit" value="<?php _e( 'Crop Image', 'trendr' ) ?>" />

		<input type="hidden" name="image_src" id="image_src" value="<?php trs_portrait_to_crop_src() ?>" />
		<input type="hidden" id="x" name="x" />
		<input type="hidden" id="y" name="y" />
		<input type="hidden" id="w" name="w" />
		<input type="hidden" id="h" name="h" />

		<?php trm_nonce_field( 'trs_portrait_cropstore' ) ?>

	<?php endif; ?>

<?php endif; ?>

<?php /* Manage Group Members */ ?>
<?php if ( trs_is_group_admin_screen( 'manage-members' ) ) : ?>

	<?php do_action( 'trs_before_group_manage_members_admin' ); ?>
	
	<div class="trs-widget">
		<h4><?php _e( 'Administrators', 'trendr' ); ?></h4>

		<?php if ( trs_has_members( '&include='. trs_group_admin_ids() ) ) : ?>
		
		<ul id="admins-list" class="article-piece single-line>">
			
			<?php while ( trs_members() ) : trs_the_member(); ?>
			<li>
				<?php echo trs_core_fetch_portrait( array( 'item_id' => trs_get_member_user_id(), 'type' => 'thumb', 'width' => 30, 'height' => 30, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ?>
				<h5>
					<a href="<?php trs_member_permalink(); ?>"> <?php trs_member_name(); ?></a>
					<span class="small">
						<a class="button confirm admin-demote-to-member" href="<?php trs_group_member_demote_link( trs_get_member_user_id() ) ?>"><?php _e( 'Demote to Member', 'trendr' ) ?></a>
					</span>			
				</h5>		
			</li>
			<?php endwhile; ?>
		
		</ul>
		
		<?php endif; ?>

	</div>
	
	<?php if ( trs_group_has_moderators() ) : ?>
		<div class="trs-widget">
			<h4><?php _e( 'Moderators', 'trendr' ) ?></h4>		
			
			<?php if ( trs_has_members( '&include=' . trs_group_mod_ids() ) ) : ?>
				<ul id="mods-list" class="article-piece">
				
					<?php while ( trs_members() ) : trs_the_member(); ?>					
					<li>
						<?php echo trs_core_fetch_portrait( array( 'item_id' => trs_get_member_user_id(), 'type' => 'thumb', 'width' => 30, 'height' => 30, 'alt' => __( 'Profile picture of %s', 'trendr' ) ) ) ?>
						<h5>
							<a href="<?php trs_member_permalink(); ?>"> <?php trs_member_name(); ?></a>
							<span class="small">
								<a href="<?php trs_group_member_promote_admin_link( array( 'user_id' => trs_get_member_user_id() ) ) ?>" class="button confirm mod-promote-to-admin" title="<?php _e( 'Promote to Admin', 'trendr' ); ?>"><?php _e( 'Promote to Admin', 'trendr' ); ?></a>
								<a class="button confirm mod-demote-to-member" href="<?php trs_group_member_demote_link( trs_get_member_user_id() ) ?>"><?php _e( 'Demote to Member', 'trendr' ) ?></a>
							</span>		
						</h5>		
					</li>	
					<?php endwhile; ?>			
				
				</ul>
			
			<?php endif; ?>
		</div>
	<?php endif ?>


	<div class="trs-widget">
		<h4><?php _e("Members", "trendr"); ?></h4>

		<?php if ( trs_group_has_members( 'per_page=15&exclude_banned=false' ) ) : ?>

			<?php if ( trs_group_member_needs_pagination() ) : ?>

				<div class="pagination no-ajax">

					<div id="member-count" class="pag-count">
						<?php trs_group_member_pagination_count() ?>
					</div>

					<div id="member-admin-pagination" class="pagination-links">
						<?php trs_group_member_admin_pagination() ?>
					</div>

				</div>

			<?php endif; ?>

			<ul id="members-list" class="article-piece single-line">
				<?php while ( trs_group_members() ) : trs_group_the_member(); ?>

					<li class="<?php trs_group_member_css_class(); ?>">
						<?php trs_group_member_portrait_mini() ?>

						<h5>
							<?php trs_group_member_link() ?>

							<?php if ( trs_get_group_member_is_banned() ) _e( '(banned)', 'trendr'); ?>

							<span class="small">

							<?php if ( trs_get_group_member_is_banned() ) : ?>

								<a href="<?php trs_group_member_unban_link() ?>" class="button confirm member-unban" title="<?php _e( 'Unban this member', 'trendr' ) ?>"><?php _e( 'Remove Ban', 'trendr' ); ?></a>

							<?php else : ?>

								<a href="<?php trs_group_member_ban_link() ?>" class="button confirm member-ban" title="<?php _e( 'Kick and ban this member', 'trendr' ); ?>"><?php _e( 'Kick &amp; Ban', 'trendr' ); ?></a>
								<a href="<?php trs_group_member_promote_mod_link() ?>" class="button confirm member-promote-to-mod" title="<?php _e( 'Promote to Mod', 'trendr' ); ?>"><?php _e( 'Promote to Mod', 'trendr' ); ?></a>
								<a href="<?php trs_group_member_promote_admin_link() ?>" class="button confirm member-promote-to-admin" title="<?php _e( 'Promote to Admin', 'trendr' ); ?>"><?php _e( 'Promote to Admin', 'trendr' ); ?></a>

							<?php endif; ?>

								<a href="<?php trs_group_member_remove_link() ?>" class="button confirm" title="<?php _e( 'Remove this member', 'trendr' ); ?>"><?php _e( 'Remove from group', 'trendr' ); ?></a>

								<?php do_action( 'trs_group_manage_members_admin_item' ); ?>

							</span>
						</h5>
					</li>

				<?php endwhile; ?>
			</ul>

		<?php else: ?>

			<div id="message" class="info">
				<p><?php _e( 'This group has no members.', 'trendr' ); ?></p>
			</div>

		<?php endif; ?>

	</div>

	<?php do_action( 'trs_after_group_manage_members_admin' ); ?>

<?php endif; ?>

<?php /* Manage Membership Requests */ ?>
<?php if ( trs_is_group_admin_screen( 'membership-requests' ) ) : ?>

	<?php do_action( 'trs_before_group_membership_requests_admin' ); ?>

	<?php if ( trs_group_has_membership_requests() ) : ?>

		<ul id="request-list" class="article-piece">
			<?php while ( trs_group_membership_requests() ) : trs_group_the_membership_request(); ?>

				<li>
					<?php trs_group_request_user_portrait_thumb() ?>
					<h4><?php trs_group_request_user_link() ?> <span class="comments"><?php trs_group_request_comment() ?></span></h4>
					<span class="activity"><?php trs_group_request_time_since_requested() ?></span>

					<?php do_action( 'trs_group_membership_requests_admin_item' ); ?>

					<div class="action">

						<?php trs_button( array( 'id' => 'group_membership_accept', 'component' => 'groups', 'wrapper_class' => 'accept', 'link_href' => trs_get_group_request_accept_link(), 'link_title' => __( 'Accept', 'trendr' ), 'link_text' => __( 'Accept', 'trendr' ) ) ); ?>

						<?php trs_button( array( 'id' => 'group_membership_reject', 'component' => 'groups', 'wrapper_class' => 'reject', 'link_href' => trs_get_group_request_reject_link(), 'link_title' => __( 'Reject', 'trendr' ), 'link_text' => __( 'Reject', 'trendr' ) ) ); ?>

						<?php do_action( 'trs_group_membership_requests_admin_item_action' ); ?>

					</div>
				</li>

			<?php endwhile; ?>
		</ul>

	<?php else: ?>

		<div id="message" class="info">
			<p><?php _e( 'There are no pending membership requests.', 'trendr' ); ?></p>
		</div>

	<?php endif; ?>

	<?php do_action( 'trs_after_group_membership_requests_admin' ); ?>

<?php endif; ?>

<?php do_action( 'groups_custom_edit_steps' ) // Allow plugins to add custom group edit screens ?>

<?php /* Delete Group Option */ ?>
<?php if ( trs_is_group_admin_screen( 'delete-group' ) ) : ?>

	<?php do_action( 'trs_before_group_delete_admin' ); ?>

	<div id="message" class="info">
		<p><?php _e( 'WARNING: Deleting this group will completely remove ALL content associated with it. There is no way back, please be careful with this option.', 'trendr' ); ?></p>
	</div>

	<label><input type="checkbox" name="delete-group-understand" id="delete-group-understand" value="1" onclick="if(this.checked) { document.getElementById('delete-group-button').disabled = ''; } else { document.getElementById('delete-group-button').disabled = 'disabled'; }" /> <?php _e( 'I understand the consequences of deleting this group.', 'trendr' ); ?></label>

	<?php do_action( 'trs_after_group_delete_admin' ); ?>

	<div class="submit">
		<input type="submit" disabled="disabled" value="<?php _e( 'Delete Group', 'trendr' ) ?>" id="delete-group-button" name="delete-group-button" />
	</div>

	<?php trm_nonce_field( 'groups_delete_group' ) ?>

<?php endif; ?>

<?php /* This is important, don't forget it */ ?>
	<input type="hidden" name="group-id" id="group-id" value="<?php trs_group_id() ?>" />

<?php do_action( 'trs_after_group_admin_content' ) ?>

</form><!-- #group-settings-form -->

