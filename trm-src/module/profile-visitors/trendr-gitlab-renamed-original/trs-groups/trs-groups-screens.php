<?php
/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of trendr. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function groups_directory_groups_setup() {
	if ( trs_is_groups_component() && !trs_current_action() && !trs_current_item() ) {
		trs_update_is_directory( true, 'groups' );

		do_action( 'groups_directory_groups_setup' );

		trs_core_load_template( apply_filters( 'groups_template_directory_groups', 'groups/index' ) );
	}
}
add_action( 'trs_screens', 'groups_directory_groups_setup', 2 );

function groups_screen_my_groups() {
	global $trs;

	// Delete group request notifications for the user
	if ( isset( $_GET['n'] ) ) {
		trs_core_delete_notifications_by_type( $trs->loggedin_user->id, $trs->groups->id, 'membership_request_accepted' );
		trs_core_delete_notifications_by_type( $trs->loggedin_user->id, $trs->groups->id, 'membership_request_rejected' );
		trs_core_delete_notifications_by_type( $trs->loggedin_user->id, $trs->groups->id, 'member_promoted_to_mod'      );
		trs_core_delete_notifications_by_type( $trs->loggedin_user->id, $trs->groups->id, 'member_promoted_to_admin'    );
	}

	do_action( 'groups_screen_my_groups' );

	trs_core_load_template( apply_filters( 'groups_template_my_groups', 'members/single/home' ) );
}

function groups_screen_group_invites() {
	$group_id = (int)trs_action_variable( 1 );

	if ( trs_is_action_variable( 'accept' ) && is_numeric( $group_id ) ) {
		// Check the nonce
		if ( !check_admin_referer( 'groups_accept_invite' ) )
			return false;

		if ( !groups_accept_invite( trs_loggedin_user_id(), $group_id ) ) {
			trs_core_add_message( __('Group invite could not be accepted', 'trendr'), 'error' );
		} else {
			trs_core_add_message( __('Group invite accepted', 'trendr') );

			// Record this in activity streams
			$group = new TRS_Groups_Group( $group_id );

			groups_record_activity( array(
				'action'  => apply_filters_ref_array( 'groups_activity_accepted_invite_action', array( sprintf( __( '%1$s joined the group %2$s', 'trendr'), trs_core_get_userlink( trs_loggedin_user_id() ), '<a href="' . trs_get_group_permalink( $group ) . '">' . esc_attr( $group->name ) . '</a>' ), trs_loggedin_user_id(), &$group ) ),
				'type'    => 'joined_group',
				'item_id' => $group->id
			) );
		}

		trs_core_redirect( trs_loggedin_user_domain() . trs_get_groups_slug() . '/' . trs_current_action() );

	} else if ( trs_is_action_variable( 'reject' ) && is_numeric( $group_id ) ) {
		// Check the nonce
		if ( !check_admin_referer( 'groups_reject_invite' ) )
			return false;

		if ( !groups_reject_invite( trs_loggedin_user_id(), $group_id ) )
			trs_core_add_message( __('Group invite could not be rejected', 'trendr'), 'error' );
		else
			trs_core_add_message( __('Group invite rejected', 'trendr') );

		trs_core_redirect( trs_loggedin_user_domain() . trs_get_groups_slug() . '/' . trs_current_action() );
	}

	// Remove notifications
	trs_core_delete_notifications_by_type( trs_loggedin_user_id(), 'groups', 'group_invite' );

	do_action( 'groups_screen_group_invites', $group_id );

	trs_core_load_template( apply_filters( 'groups_template_group_invites', 'members/single/home' ) );
}

function groups_screen_group_home() {
	global $trs;

	if ( trs_is_single_item() ) {
		if ( isset( $_GET['n'] ) ) {
			trs_core_delete_notifications_by_type( $trs->loggedin_user->id, $trs->groups->id, 'membership_request_accepted' );
			trs_core_delete_notifications_by_type( $trs->loggedin_user->id, $trs->groups->id, 'membership_request_rejected' );
			trs_core_delete_notifications_by_type( $trs->loggedin_user->id, $trs->groups->id, 'member_promoted_to_mod'      );
			trs_core_delete_notifications_by_type( $trs->loggedin_user->id, $trs->groups->id, 'member_promoted_to_admin'    );
		}

		do_action( 'groups_screen_group_home' );

		trs_core_load_template( apply_filters( 'groups_template_group_home', 'groups/single/home' ) );
	}
}

/**
 * This screen function handles actions related to group forums
 *
 * @package trendr
 */
function groups_screen_group_forum() {
	global $trs;

	if ( !trs_is_active( 'forums' ) || !trs_forums_is_installed_correctly() )
		return false;

	if ( trs_action_variable( 0 ) && !trs_is_action_variable( 'topic', 0 ) ) {
		trs_do_404();
		return;
	}

	if ( !$trs->groups->current_group->user_has_access ) {
		trs_core_no_access();
		return;
	}

	if ( trs_is_single_item() ) {

		// Fetch the details we need
		$topic_slug	= (string)trs_action_variable( 1 );
		$topic_id       = trs_forums_get_topic_id_from_slug( $topic_slug );
		$forum_id       = groups_get_groupmeta( $trs->groups->current_group->id, 'forum_id' );
		$user_is_banned = false;

		if ( !is_super_admin() && groups_is_user_banned( $trs->loggedin_user->id, $trs->groups->current_group->id ) )
			$user_is_banned = true;

		if ( !empty( $topic_slug ) && !empty( $topic_id ) ) {

			// Posting a reply
			if ( !$user_is_banned && !trs_action_variable( 2 ) && isset( $_POST['submit_reply'] ) ) {
				// Check the nonce
				check_admin_referer( 'trs_forums_new_reply' );

				// Auto join this user if they are not yet a member of this group
				if ( trs_groups_auto_join() && !is_super_admin() && 'public' == $trs->groups->current_group->status && !groups_is_user_member( $trs->loggedin_user->id, $trs->groups->current_group->id ) )
					groups_join_group( $trs->groups->current_group->id, $trs->loggedin_user->id );

				$topic_page = isset( $_GET['topic_page'] ) ? $_GET['topic_page'] : false;

				if ( !$post_id = groups_new_group_forum_post( $_POST['reply_text'], $topic_id, $topic_page ) )
					trs_core_add_message( __( 'There was an error when replying to that topic', 'trendr'), 'error' );
				else
					trs_core_add_message( __( 'Your reply was posted successfully', 'trendr') );

				if ( isset( $_SERVER['QUERY_STRING'] ) )
					$query_vars = '?' . $_SERVER['QUERY_STRING'];

				trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'forum/topic/' . $topic_slug . '/' . $query_vars . '#post-' . $post_id );
			}

			// Sticky a topic
			else if ( trs_is_action_variable( 'stick', 2 ) && ( isset( $trs->is_item_admin ) || isset( $trs->is_item_mod ) ) ) {
				// Check the nonce
				check_admin_referer( 'trs_forums_stick_topic' );

				if ( !trs_forums_sticky_topic( array( 'topic_id' => $topic_id ) ) )
					trs_core_add_message( __( 'There was an error when making that topic a sticky', 'trendr' ), 'error' );
				else
					trs_core_add_message( __( 'The topic was made sticky successfully', 'trendr' ) );

				do_action( 'groups_stick_forum_topic', $topic_id );
				trs_core_redirect( trm_get_referer() );
			}

			// Un-Sticky a topic
			else if ( trs_is_action_variable( 'unstick', 2 ) && ( isset( $trs->is_item_admin ) || isset( $trs->is_item_mod ) ) ) {
				// Check the nonce
				check_admin_referer( 'trs_forums_unstick_topic' );

				if ( !trs_forums_sticky_topic( array( 'topic_id' => $topic_id, 'mode' => 'unstick' ) ) )
					trs_core_add_message( __( 'There was an error when unsticking that topic', 'trendr'), 'error' );
				else
					trs_core_add_message( __( 'The topic was unstuck successfully', 'trendr') );

				do_action( 'groups_unstick_forum_topic', $topic_id );
				trs_core_redirect( trm_get_referer() );
			}

			// Close a topic
			else if ( trs_is_action_variable( 'close', 2 ) && ( isset( $trs->is_item_admin ) || isset( $trs->is_item_mod ) ) ) {
				// Check the nonce
				check_admin_referer( 'trs_forums_close_topic' );

				if ( !trs_forums_openclose_topic( array( 'topic_id' => $topic_id ) ) )
					trs_core_add_message( __( 'There was an error when closing that topic', 'trendr'), 'error' );
				else
					trs_core_add_message( __( 'The topic was closed successfully', 'trendr') );

				do_action( 'groups_close_forum_topic', $topic_id );
				trs_core_redirect( trm_get_referer() );
			}

			// Open a topic
			else if ( trs_is_action_variable( 'open', 2 ) && ( isset( $trs->is_item_admin ) || isset( $trs->is_item_mod ) ) ) {
				// Check the nonce
				check_admin_referer( 'trs_forums_open_topic' );

				if ( !trs_forums_openclose_topic( array( 'topic_id' => $topic_id, 'mode' => 'open' ) ) )
					trs_core_add_message( __( 'There was an error when opening that topic', 'trendr'), 'error' );
				else
					trs_core_add_message( __( 'The topic was opened successfully', 'trendr') );

				do_action( 'groups_open_forum_topic', $topic_id );
				trs_core_redirect( trm_get_referer() );
			}

			// Delete a topic
			else if ( empty( $user_is_banned ) && trs_is_action_variable( 'delete', 2 ) && !trs_action_variable( 3 ) ) {
				// Fetch the topic
				$topic = trs_forums_get_topic_details( $topic_id );

				/* Check the logged in user can delete this topic */
				if ( !$trs->is_item_admin && !$trs->is_item_mod && (int)$trs->loggedin_user->id != (int)$topic->topic_poster )
					trs_core_redirect( trm_get_referer() );

				// Check the nonce
				check_admin_referer( 'trs_forums_delete_topic' );

				do_action( 'groups_before_delete_forum_topic', $topic_id );

				if ( !groups_delete_group_forum_topic( $topic_id ) )
					trs_core_add_message( __( 'There was an error deleting the topic', 'trendr' ), 'error' );
				else
					trs_core_add_message( __( 'The topic was deleted successfully', 'trendr' ) );

				do_action( 'groups_delete_forum_topic', $topic_id );
				trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'forum/' );
			}

			// Editing a topic
			else if ( empty( $user_is_banned ) && trs_is_action_variable( 'edit', 2 ) && !trs_action_variable( 3 ) ) {
				// Fetch the topic
				$topic = trs_forums_get_topic_details( $topic_id );

				// Check the logged in user can edit this topic
				if ( !$trs->is_item_admin && !$trs->is_item_mod && (int)$trs->loggedin_user->id != (int)$topic->topic_poster )
					trs_core_redirect( trm_get_referer() );

				if ( isset( $_POST['save_changes'] ) ) {
					// Check the nonce
					check_admin_referer( 'trs_forums_edit_topic' );

					$topic_tags = !empty( $_POST['topic_tags'] ) ? $_POST['topic_tags'] : false;

					if ( !groups_update_group_forum_topic( $topic_id, $_POST['topic_title'], $_POST['topic_text'], $topic_tags ) )
						trs_core_add_message( __( 'There was an error when editing that topic', 'trendr'), 'error' );
					else
						trs_core_add_message( __( 'The topic was edited successfully', 'trendr') );

					do_action( 'groups_edit_forum_topic', $topic_id );
					trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'forum/topic/' . $topic_slug . '/' );
				}

				trs_core_load_template( apply_filters( 'groups_template_group_forum_topic_edit', 'groups/single/home' ) );
			}

			// Delete a post
			else if ( empty( $user_is_banned ) && trs_is_action_variable( 'delete', 2 ) && $post_id = trs_action_variable( 4 ) ) {
				// Fetch the post
				$post = trs_forums_get_post( $post_id );

				// Check the logged in user can edit this topic
				if ( !$trs->is_item_admin && !$trs->is_item_mod && (int)$trs->loggedin_user->id != (int)$post->poster_id )
					trs_core_redirect( trm_get_referer() );

				// Check the nonce
				check_admin_referer( 'trs_forums_delete_post' );

				do_action( 'groups_before_delete_forum_post', $post_id );

				if ( !groups_delete_group_forum_post( $post_id ) )
					trs_core_add_message( __( 'There was an error deleting that post', 'trendr'), 'error' );
				else
					trs_core_add_message( __( 'The post was deleted successfully', 'trendr') );

				do_action( 'groups_delete_forum_post', $post_id );
				trs_core_redirect( trm_get_referer() );
			}

			// Editing a post
			else if ( empty( $user_is_banned ) && trs_is_action_variable( 'edit', 2 ) && $post_id = trs_action_variable( 4 ) ) {
				// Fetch the post
				$post = trs_forums_get_post( $post_id );

				// Check the logged in user can edit this topic
				if ( !$trs->is_item_admin && !$trs->is_item_mod && (int)$trs->loggedin_user->id != (int)$post->poster_id )
					trs_core_redirect( trm_get_referer() );

				if ( isset( $_POST['save_changes'] ) ) {
					// Check the nonce
					check_admin_referer( 'trs_forums_edit_post' );

					$topic_page = isset( $_GET['topic_page'] ) ? $_GET['topic_page'] : false;

					if ( !$post_id = groups_update_group_forum_post( $post_id, $_POST['post_text'], $topic_id, $topic_page ) )
						trs_core_add_message( __( 'There was an error when editing that post', 'trendr'), 'error' );
					else
						trs_core_add_message( __( 'The post was edited successfully', 'trendr') );

					if ( $_SERVER['QUERY_STRING'] )
						$query_vars = '?' . $_SERVER['QUERY_STRING'];

					do_action( 'groups_edit_forum_post', $post_id );
					trs_core_redirect( trs_get_group_permalink( $trs->groups->current_group ) . 'forum/topic/' . $topic_slug . '/' . $query_vars . '#post-' . $post_id );
				}

				trs_core_load_template( apply_filters( 'groups_template_group_forum_topic_edit', 'groups/single/home' ) );
			}

			// Standard topic display
			else {
				if ( !empty( $user_is_banned ) )
					trs_core_add_message( __( "You have been banned from this group.", 'trendr' ) );

				trs_core_load_template( apply_filters( 'groups_template_group_forum_topic', 'groups/single/home' ) );
			}

		// Forum topic does not exist
		} elseif ( !empty( $topic_slug ) && empty( $topic_id ) ) {
			trs_do_404();
			return;

		} else {
			// Posting a topic
			if ( isset( $_POST['submit_topic'] ) && trs_is_active( 'forums' ) ) {
				// Check the nonce
				check_admin_referer( 'trs_forums_new_topic' );

				if ( $user_is_banned ) {
				 	$error_message = __( "You have been banned from this group.", 'trendr' );

				} elseif ( trs_groups_auto_join() && !is_super_admin() && 'public' == $trs->groups->current_group->status && !groups_is_user_member( $trs->loggedin_user->id, $trs->groups->current_group->id ) ) {
					// Auto join this user if they are not yet a member of this group
					groups_join_group( $trs->groups->current_group->id, $trs->loggedin_user->id );
				}

				if ( empty( $_POST['topic_title'] ) )
					$error_message = __( 'Please provide a title for your forum topic.', 'trendr' );
				else if ( empty( $_POST['topic_text'] ) )
					$error_message = __( 'Forum posts cannot be empty. Please enter some text.', 'trendr' );

				if ( empty( $forum_id ) )
					$error_message = __( 'This group does not have a forum setup yet.', 'trendr' );

				if ( isset( $error_message ) ) {
					trs_core_add_message( $error_message, 'error' );
					$redirect = trs_get_group_permalink( $trs->groups->current_group ) . 'forum';
				} else {
					if ( !$topic = groups_new_group_forum_topic( $_POST['topic_title'], $_POST['topic_text'], $_POST['topic_tags'], $forum_id ) ) {
						trs_core_add_message( __( 'There was an error when creating the topic', 'trendr'), 'error' );
						$redirect = trs_get_group_permalink( $trs->groups->current_group ) . 'forum';
					} else {
						trs_core_add_message( __( 'The topic was created successfully', 'trendr') );
						$redirect = trs_get_group_permalink( $trs->groups->current_group ) . 'forum/topic/' . $topic->topic_slug . '/';
					}
				}

				trs_core_redirect( $redirect );
			}

			do_action( 'groups_screen_group_forum', $topic_id, $forum_id );

			trs_core_load_template( apply_filters( 'groups_template_group_forum', 'groups/single/home' ) );
		}
	}
}

function groups_screen_group_members() {
	global $trs;

	if ( $trs->is_single_item ) {
		// Refresh the group member count meta
		groups_update_groupmeta( $trs->groups->current_group->id, 'total_member_count', groups_get_total_member_count( $trs->groups->current_group->id ) );

		do_action( 'groups_screen_group_members', $trs->groups->current_group->id );
		trs_core_load_template( apply_filters( 'groups_template_group_members', 'groups/single/home' ) );
	}
}

function groups_screen_group_invite() {
	global $trs;

	if ( $trs->is_single_item ) {
		if ( trs_is_action_variable( 'send', 0 ) ) {

			if ( !check_admin_referer( 'groups_send_invites', '_key_send_invites' ) )
				return false;

			if ( !empty( $_POST['friends'] ) ) {
				foreach( (array)$_POST['friends'] as $friend ) {
					groups_invite_user( array( 'user_id' => $friend, 'group_id' => $trs->groups->current_group->id ) );
				}
			}

			// Send the invites.
			groups_send_invites( $trs->loggedin_user->id, $trs->groups->current_group->id );
			trs_core_add_message( __('Group invites sent.', 'trendr') );
			do_action( 'groups_screen_group_invite', $trs->groups->current_group->id );
			trs_core_redirect( trs_get_group_permalink( $trs->groups->current_group ) );

		} elseif ( !trs_action_variable( 0 ) ) {
			// Show send invite page
			trs_core_load_template( apply_filters( 'groups_template_group_invite', 'groups/single/home' ) );

		} else {
			trs_do_404();
		}
	}
}

function groups_screen_group_request_membership() {
	global $trs;

	if ( !is_user_logged_in() )
		return false;

	if ( 'private' == $trs->groups->current_group->status ) {
		// If the user has submitted a request, send it.
		if ( isset( $_POST['group-request-send']) ) {
			// Check the nonce
			if ( !check_admin_referer( 'groups_request_membership' ) )
				return false;

			if ( !groups_send_membership_request( $trs->loggedin_user->id, $trs->groups->current_group->id ) ) {
				trs_core_add_message( __( 'There was an error sending your group membership request, please try again.', 'trendr' ), 'error' );
			} else {
				trs_core_add_message( __( 'Your membership request was sent to the group administrator successfully. You will be notified when the group administrator responds to your request.', 'trendr' ) );
			}
			trs_core_redirect( trs_get_group_permalink( $trs->groups->current_group ) );
		}

		do_action( 'groups_screen_group_request_membership', $trs->groups->current_group->id );

		trs_core_load_template( apply_filters( 'groups_template_group_request_membership', 'groups/single/home' ) );
	}
}

function groups_screen_group_activity_permalink() {
	global $trs;

	if ( !trs_is_groups_component() || !trs_is_active( 'activity' ) || ( trs_is_active( 'activity' ) && !trs_is_current_action( trs_get_activity_slug() ) ) || !trs_action_variable( 0 ) )
		return false;

	$trs->is_single_item = true;

	trs_core_load_template( apply_filters( 'groups_template_group_home', 'groups/single/home' ) );
}
add_action( 'trs_screens', 'groups_screen_group_activity_permalink' );

function groups_screen_group_admin() {
	if ( !trs_is_groups_component() || !trs_is_current_action( 'admin' ) )
		return false;

	if ( trs_action_variables() )
		return false;

	trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'admin/edit-details/' );
}

function groups_screen_group_admin_edit_details() {
	global $trs;

	if ( trs_is_groups_component() && trs_is_action_variable( 'edit-details', 0 ) ) {

		if ( $trs->is_item_admin || $trs->is_item_mod  ) {

			// If the edit form has been submitted, save the edited details
			if ( isset( $_POST['save'] ) ) {
				// Check the nonce
				if ( !check_admin_referer( 'groups_edit_group_details' ) )
					return false;

				if ( !groups_edit_base_group_details( $_POST['group-id'], $_POST['group-name'], $_POST['group-desc'], (int)$_POST['group-notify-members'] ) ) {
					trs_core_add_message( __( 'There was an error updating group details, please try again.', 'trendr' ), 'error' );
				} else {
					trs_core_add_message( __( 'Group details were successfully updated.', 'trendr' ) );
				}

				do_action( 'groups_group_details_edited', $trs->groups->current_group->id );

				trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'admin/edit-details/' );
			}

			do_action( 'groups_screen_group_admin_edit_details', $trs->groups->current_group->id );

			trs_core_load_template( apply_filters( 'groups_template_group_admin', 'groups/single/home' ) );
		}
	}
}
add_action( 'trs_screens', 'groups_screen_group_admin_edit_details' );

function groups_screen_group_admin_settings() {
	global $trs;

	if ( trs_is_groups_component() && trs_is_action_variable( 'group-settings', 0 ) ) {

		if ( !$trs->is_item_admin )
			return false;

		// If the edit form has been submitted, save the edited details
		if ( isset( $_POST['save'] ) ) {
			$enable_forum   = ( isset($_POST['group-show-forum'] ) ) ? 1 : 0;

			// Checked against a whitelist for security
			$allowed_status = apply_filters( 'groups_allowed_status', array( 'public', 'private', 'hidden' ) );
			$status         = ( in_array( $_POST['group-status'], (array)$allowed_status ) ) ? $_POST['group-status'] : 'public';

			// Checked against a whitelist for security
			$allowed_invite_status = apply_filters( 'groups_allowed_invite_status', array( 'members', 'mods', 'admins' ) );
			$invite_status	       = in_array( $_POST['group-invite-status'], (array)$allowed_invite_status ) ? $_POST['group-invite-status'] : 'members';

			// Check the nonce
			if ( !check_admin_referer( 'groups_edit_group_settings' ) )
				return false;

			if ( !groups_edit_group_settings( $_POST['group-id'], $enable_forum, $status, $invite_status ) ) {
				trs_core_add_message( __( 'There was an error updating group settings, please try again.', 'trendr' ), 'error' );
			} else {
				trs_core_add_message( __( 'Group settings were successfully updated.', 'trendr' ) );
			}

			do_action( 'groups_group_settings_edited', $trs->groups->current_group->id );

			trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'admin/group-settings/' );
		}

		do_action( 'groups_screen_group_admin_settings', $trs->groups->current_group->id );

		trs_core_load_template( apply_filters( 'groups_template_group_admin_settings', 'groups/single/home' ) );
	}
}
add_action( 'trs_screens', 'groups_screen_group_admin_settings' );

function groups_screen_group_admin_portrait() {
	global $trs;

	if ( trs_is_groups_component() && trs_is_action_variable( 'group-portrait', 0 ) ) {

		// If the logged-in user doesn't have permission or if portrait uploads are disabled, then stop here
		if ( !$trs->is_item_admin || (int)trs_get_option( 'trs-disable-portrait-uploads' ) )
			return false;

		// If the group admin has deleted the admin portrait
		if ( trs_is_action_variable( 'delete', 1 ) ) {

			// Check the nonce
			check_admin_referer( 'trs_group_portrait_delete' );

			if ( trs_core_delete_existing_portrait( array( 'item_id' => $trs->groups->current_group->id, 'object' => 'group' ) ) )
				trs_core_add_message( __( 'Your portrait was deleted successfully!', 'trendr' ) );
			else
				trs_core_add_message( __( 'There was a problem deleting that portrait, please try again.', 'trendr' ), 'error' );

		}

		$trs->portrait_admin->step = 'upload-image';

		if ( !empty( $_FILES ) ) {

			// Check the nonce
			check_admin_referer( 'trs_portrait_upload' );

			// Pass the file to the portrait upload handler
			if ( trs_core_portrait_handle_upload( $_FILES, 'groups_portrait_upload_dir' ) ) {
				$trs->portrait_admin->step = 'crop-image';

				// Make sure we include the jQuery jCrop file for image cropping
				add_action( 'trm_print_scripts', 'trs_core_add_jquery_cropper' );
			}

		}

		// If the image cropping is done, crop the image and save a full/thumb version
		if ( isset( $_POST['portrait-crop-submit'] ) ) {

			// Check the nonce
			check_admin_referer( 'trs_portrait_cropstore' );

			if ( !trs_core_portrait_handle_crop( array( 'object' => 'group', 'portrait_dir' => 'group-portraits', 'item_id' => $trs->groups->current_group->id, 'original_file' => $_POST['image_src'], 'crop_x' => $_POST['x'], 'crop_y' => $_POST['y'], 'crop_w' => $_POST['w'], 'crop_h' => $_POST['h'] ) ) )
				trs_core_add_message( __( 'There was a problem cropping the portrait, please try uploading it again', 'trendr' ) );
			else
				trs_core_add_message( __( 'The new group portrait was uploaded successfully!', 'trendr' ) );

		}

		do_action( 'groups_screen_group_admin_portrait', $trs->groups->current_group->id );

		trs_core_load_template( apply_filters( 'groups_template_group_admin_portrait', 'groups/single/home' ) );
	}
}
add_action( 'trs_screens', 'groups_screen_group_admin_portrait' );

/**
 * This function handles actions related to member management on the group admin.
 *
 * @package trendr
 */
function groups_screen_group_admin_manage_members() {
	global $trs;

	if ( trs_is_groups_component() && trs_is_action_variable( 'manage-members', 0 ) ) {

		if ( !$trs->is_item_admin )
			return false;

		if ( trs_action_variable( 1 ) && trs_action_variable( 2 ) && trs_action_variable( 3 ) ) {
			if ( trs_is_action_variable( 'promote', 1 ) && ( trs_is_action_variable( 'mod', 2 ) || trs_is_action_variable( 'admin', 2 ) ) && is_numeric( trs_action_variable( 3 ) ) ) {
				$user_id = trs_action_variable( 3 );
				$status  = trs_action_variable( 2 );

				// Check the nonce first.
				if ( !check_admin_referer( 'groups_promote_member' ) )
					return false;

				// Promote a user.
				if ( !groups_promote_member( $user_id, $trs->groups->current_group->id, $status ) )
					trs_core_add_message( __( 'There was an error when promoting that user, please try again', 'trendr' ), 'error' );
				else
					trs_core_add_message( __( 'User promoted successfully', 'trendr' ) );

				do_action( 'groups_promoted_member', $user_id, $trs->groups->current_group->id );

				trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'admin/manage-members/' );
			}
		}

		if ( trs_action_variable( 1 ) && trs_action_variable( 2 ) ) {
			if ( trs_is_action_variable( 'demote', 1 ) && is_numeric( trs_action_variable( 2 ) ) ) {
				$user_id = trs_action_variable( 2 );

				// Check the nonce first.
				if ( !check_admin_referer( 'groups_demote_member' ) )
					return false;

				// Stop sole admins from abandoning their group
		 		$group_admins = groups_get_group_admins( $trs->groups->current_group->id );
			 	if ( 1 == count( $group_admins ) && $group_admins[0]->user_id == $user_id )
					trs_core_add_message( __( 'This group must have at least one admin', 'trendr' ), 'error' );

				// Demote a user.
				elseif ( !groups_demote_member( $user_id, $trs->groups->current_group->id ) )
					trs_core_add_message( __( 'There was an error when demoting that user, please try again', 'trendr' ), 'error' );
				else
					trs_core_add_message( __( 'User demoted successfully', 'trendr' ) );

				do_action( 'groups_demoted_member', $user_id, $trs->groups->current_group->id );

				trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'admin/manage-members/' );
			}

			if ( trs_is_action_variable( 'ban', 1 ) && is_numeric( trs_action_variable( 2 ) ) ) {
				$user_id = trs_action_variable( 2 );

				// Check the nonce first.
				if ( !check_admin_referer( 'groups_ban_member' ) )
					return false;

				// Ban a user.
				if ( !groups_ban_member( $user_id, $trs->groups->current_group->id ) )
					trs_core_add_message( __( 'There was an error when banning that user, please try again', 'trendr' ), 'error' );
				else
					trs_core_add_message( __( 'User banned successfully', 'trendr' ) );

				do_action( 'groups_banned_member', $user_id, $trs->groups->current_group->id );

				trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'admin/manage-members/' );
			}

			if ( trs_is_action_variable( 'unban', 1 ) && is_numeric( trs_action_variable( 2 ) ) ) {
				$user_id = trs_action_variable( 2 );

				// Check the nonce first.
				if ( !check_admin_referer( 'groups_unban_member' ) )
					return false;

				// Remove a ban for user.
				if ( !groups_unban_member( $user_id, $trs->groups->current_group->id ) )
					trs_core_add_message( __( 'There was an error when unbanning that user, please try again', 'trendr' ), 'error' );
				else
					trs_core_add_message( __( 'User ban removed successfully', 'trendr' ) );

				do_action( 'groups_unbanned_member', $user_id, $trs->groups->current_group->id );

				trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'admin/manage-members/' );
			}

			if ( trs_is_action_variable( 'remove', 1 ) && is_numeric( trs_action_variable( 2 ) ) ) {
				$user_id = trs_action_variable( 2 );

				// Check the nonce first.
				if ( !check_admin_referer( 'groups_remove_member' ) )
					return false;

				// Remove a user.
				if ( !groups_remove_member( $user_id, $trs->groups->current_group->id ) )
					trs_core_add_message( __( 'There was an error removing that user from the group, please try again', 'trendr' ), 'error' );
				else
					trs_core_add_message( __( 'User removed successfully', 'trendr' ) );

				do_action( 'groups_removed_member', $user_id, $trs->groups->current_group->id );

				trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'admin/manage-members/' );
			}
		}

		do_action( 'groups_screen_group_admin_manage_members', $trs->groups->current_group->id );

		trs_core_load_template( apply_filters( 'groups_template_group_admin_manage_members', 'groups/single/home' ) );
	}
}
add_action( 'trs_screens', 'groups_screen_group_admin_manage_members' );

function groups_screen_group_admin_requests() {
	global $trs;

	if ( trs_is_groups_component() && trs_is_action_variable( 'membership-requests', 0 ) ) {

		if ( !$trs->is_item_admin || 'public' == $trs->groups->current_group->status )
			return false;

		// Remove any screen notifications
		trs_core_delete_notifications_by_type( trs_loggedin_user_id(), $trs->groups->id, 'new_membership_request' );

		$request_action = (string)trs_action_variable( 1 );
		$membership_id  = (int)trs_action_variable( 2 );

		if ( !empty( $request_action ) && !empty( $membership_id ) ) {
			if ( 'accept' == $request_action && is_numeric( $membership_id ) ) {

				// Check the nonce first.
				if ( !check_admin_referer( 'groups_accept_membership_request' ) )
					return false;

				// Accept the membership request
				if ( !groups_accept_membership_request( $membership_id ) )
					trs_core_add_message( __( 'There was an error accepting the membership request, please try again.', 'trendr' ), 'error' );
				else
					trs_core_add_message( __( 'Group membership request accepted', 'trendr' ) );

			} elseif ( 'reject' == $request_action && is_numeric( $membership_id ) ) {
				/* Check the nonce first. */
				if ( !check_admin_referer( 'groups_reject_membership_request' ) )
					return false;

				// Reject the membership request
				if ( !groups_reject_membership_request( $membership_id ) )
					trs_core_add_message( __( 'There was an error rejecting the membership request, please try again.', 'trendr' ), 'error' );
				else
					trs_core_add_message( __( 'Group membership request rejected', 'trendr' ) );
			}

			do_action( 'groups_group_request_managed', $trs->groups->current_group->id, $request_action, $membership_id );
			trs_core_redirect( trs_get_group_permalink( groups_get_current_group() ) . 'admin/membership-requests/' );
		}

		do_action( 'groups_screen_group_admin_requests', $trs->groups->current_group->id );
		trs_core_load_template( apply_filters( 'groups_template_group_admin_requests', 'groups/single/home' ) );
	}
}
add_action( 'trs_screens', 'groups_screen_group_admin_requests' );

function groups_screen_group_admin_delete_group() {
	global $trs;

	if ( trs_is_groups_component() && trs_is_action_variable( 'delete-group', 0 ) ) {

		if ( !$trs->is_item_admin && !is_super_admin() )
			return false;

		if ( isset( $_REQUEST['delete-group-button'] ) && isset( $_REQUEST['delete-group-understand'] ) ) {
			// Check the nonce first.
			if ( !check_admin_referer( 'groups_delete_group' ) )
				return false;

			do_action( 'groups_before_group_deleted', $trs->groups->current_group->id );

			// Group admin has deleted the group, now do it.
			if ( !groups_delete_group( $trs->groups->current_group->id ) ) {
				trs_core_add_message( __( 'There was an error deleting the group, please try again.', 'trendr' ), 'error' );
			} else {
				trs_core_add_message( __( 'The group was deleted successfully', 'trendr' ) );

				do_action( 'groups_group_deleted', $trs->groups->current_group->id );

				trs_core_redirect( trs_loggedin_user_domain() . trs_get_groups_slug() . '/' );
			}

			trs_core_redirect( trs_loggedin_user_domain() . trs_get_groups_slug() );
		}

		do_action( 'groups_screen_group_admin_delete_group', $trs->groups->current_group->id );

		trs_core_load_template( apply_filters( 'groups_template_group_admin_delete_group', 'groups/single/home' ) );
	}
}
add_action( 'trs_screens', 'groups_screen_group_admin_delete_group' );

/**
 * Renders the group settings fields on the Notification Settings page
 *
 * @package trendr
 */
function groups_screen_notification_settings() {
	global $trs;

	if ( !$group_invite = trs_get_user_meta( $trs->displayed_user->id, 'notification_groups_invite', true ) )
		$group_invite  = 'yes';

	if ( !$group_update = trs_get_user_meta( $trs->displayed_user->id, 'notification_groups_group_updated', true ) )
		$group_update  = 'yes';

	if ( !$group_promo = trs_get_user_meta( $trs->displayed_user->id, 'notification_groups_admin_promotion', true ) )
		$group_promo   = 'yes';

	if ( !$group_request = trs_get_user_meta( $trs->displayed_user->id, 'notification_groups_membership_request', true ) )
		$group_request = 'yes';
?>

	<table class="notification-settings" id="groups-notification-settings">
		<thead>
			<tr>
				<th class="icon"></th>
				<th class="title"><?php _e( 'Groups', 'trendr' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'trendr' ) ?></th>
				<th class="no"><?php _e( 'No', 'trendr' )?></th>
			</tr>
		</thead>

		<tbody>
			<tr id="groups-notification-settings-invitation">
				<td></td>
				<td><?php _e( 'A member invites you to join a group', 'trendr' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_groups_invite]" value="yes" <?php checked( $group_invite, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_groups_invite]" value="no" <?php checked( $group_invite, 'no', true ) ?>/></td>
			</tr>
			<tr id="groups-notification-settings-info-updated">
				<td></td>
				<td><?php _e( 'Group information is updated', 'trendr' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_groups_group_updated]" value="yes" <?php checked( $group_update, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_groups_group_updated]" value="no" <?php checked( $group_update, 'no', true ) ?>/></td>
			</tr>
			<tr id="groups-notification-settings-promoted">
				<td></td>
				<td><?php _e( 'You are promoted to a group administrator or moderator', 'trendr' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_groups_admin_promotion]" value="yes" <?php checked( $group_promo, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_groups_admin_promotion]" value="no" <?php checked( $group_promo, 'no', true ) ?>/></td>
			</tr>
			<tr id="groups-notification-settings-request">
				<td></td>
				<td><?php _e( 'A member requests to join a private group for which you are an admin', 'trendr' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_groups_membership_request]" value="yes" <?php checked( $group_request, 'yes', true ) ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_groups_membership_request]" value="no" <?php checked( $group_request, 'no', true ) ?>/></td>
			</tr>

			<?php do_action( 'groups_screen_notification_settings' ); ?>

		</tbody>
	</table>

<?php
}
add_action( 'trs_notification_settings', 'groups_screen_notification_settings' );
?>