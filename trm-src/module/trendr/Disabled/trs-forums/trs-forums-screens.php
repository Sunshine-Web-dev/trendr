<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function trs_forums_directory_forums_setup() {
	global $trs;

	if ( trs_is_forums_component() && ( !trs_current_action() || ( 'tag' == trs_current_action() && trs_action_variables() ) ) && !trs_current_item() ) {
		if ( !trs_forums_has_directory() )
			return false;

		if ( !trs_forums_is_installed_correctly() ) {
			trs_core_add_message( __( 'The forums component has not been set up yet.', 'trendr' ), 'error' );
			trs_core_redirect( trs_get_root_domain() );
		}

		trs_update_is_directory( true, 'forums' );

		do_action( 'btrsress_init' );

		// Check to see if the user has posted a new topic from the forums page.
		if ( isset( $_POST['submit_topic'] ) && trs_is_active( 'forums' ) ) {
			check_admin_referer( 'trs_forums_new_topic' );

			$trs->groups->current_group = groups_get_group( array( 'group_id' => $_POST['topic_group_id'] ) );
			if ( !empty( $trs->groups->current_group->id ) ) {
				// Auto join this user if they are not yet a member of this group
				if ( !is_super_admin() && 'public' == $trs->groups->current_group->status && !groups_is_user_member( $trs->loggedin_user->id, $trs->groups->current_group->id ) )
					groups_join_group( $trs->groups->current_group->id );

				$error_message = '';

				$forum_id = groups_get_groupmeta( $trs->groups->current_group->id, 'forum_id' );
				if ( !empty( $forum_id ) ) {
					if ( empty( $_POST['topic_title'] ) )
						$error_message = __( 'Please provide a title for your forum topic.', 'trendr' );
					else if ( empty( $_POST['topic_text'] ) )
						$error_message = __( 'Forum posts cannot be empty. Please enter some text.', 'trendr' );

					if ( $error_message ) {
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

				} else {
					trs_core_add_message( __( 'Please pick the group forum where you would like to post this topic.', 'trendr' ), 'error' );
					trs_core_redirect( add_query_arg( 'new', '', trs_get_forums_directory_permalink() ) );
				}

			}	 else {
				trs_core_add_message( __( 'Please pick the group forum where you would like to post this topic.', 'trendr' ), 'error' );
				trs_core_redirect( add_query_arg( 'new', '', trs_get_forums_directory_permalink() ) );
			}
		}

		do_action( 'trs_forums_directory_forums_setup' );

		trs_core_load_template( apply_filters( 'trs_forums_template_directory_forums_setup', 'forums/index' ) );
	}
}
add_action( 'trs_screens', 'trs_forums_directory_forums_setup', 2 );

function trs_member_forums_screen_topics() {
	global $trs;

	do_action( 'trs_member_forums_screen_topics' );

	trs_core_load_template( apply_filters( 'trs_member_forums_screen_topics', 'members/single/home' ) );
}

function trs_member_forums_screen_replies() {
	global $trs;

	do_action( 'trs_member_forums_screen_replies' );

	trs_core_load_template( apply_filters( 'trs_member_forums_screen_replies', 'members/single/home' ) );
}

/**
 * Loads the template content for a user's Favorites forum tab.
 *
 * Note that this feature is not fully implemented at the moment.
 *
 * @package trendr Forums
 */
function trs_member_forums_screen_favorites() {
	global $trs;

	do_action( 'trs_member_forums_screen_favorites' );

	trs_core_load_template( apply_filters( 'trs_member_forums_screen_favorites', 'members/single/home' ) );
}

function trs_forums_screen_single_forum() {
	global $trs;

	if ( !trs_is_forums_component() || !trs_is_current_action( 'forum' ) || !trs_action_variable( 0 ) )
		return false;

	do_action( 'trs_forums_screen_single_forum' );

	trs_core_load_template( apply_filters( 'trs_forums_screen_single_forum', 'forums/single/forum' ) );
}
add_action( 'trs_screens', 'trs_forums_screen_single_forum' );

function trs_forums_screen_single_topic() {
	global $trs;

	if ( !trs_is_forums_component() || !trs_is_current_action( 'topic' ) || !trs_action_variable( 0 ) )
		return false;

	do_action( 'trs_forums_screen_single_topic' );

	trs_core_load_template( apply_filters( 'trs_forums_screen_single_topic', 'forums/single/topic' ) );
}
add_action( 'trs_screens', 'trs_forums_screen_single_topic' );
?>