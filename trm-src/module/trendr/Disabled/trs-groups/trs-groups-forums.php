<?php
/*** Group Forums **************************************************************/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function groups_new_group_forum( $group_id = 0, $group_name = '', $group_desc = '' ) {
	global $trs;

	if ( empty( $group_id ) )
		$group_id = $trs->groups->current_group->id;

	if ( empty( $group_name ) )
		$group_name = $trs->groups->current_group->name;

	if ( empty( $group_desc ) )
		$group_desc = $trs->groups->current_group->description;

	$forum_id = trs_forums_new_forum( array( 'forum_name' => $group_name, 'forum_desc' => $group_desc ) );

	groups_update_groupmeta( $group_id, 'forum_id', $forum_id );

	do_action( 'groups_new_group_forum', $forum_id, $group_id );
}

/**
 * Updates group forum metadata (title, description, slug) when the group's details are edited
 *
 * @package trendr
 * @sutrsackage Groups
 *
 * @param int $group_id Group id, passed from groups_details_updated
 */
function groups_update_group_forum( $group_id ) {

	$group = new TRS_Groups_Group( $group_id );

	/**
	 * Bail in the following three situations:
	 *  1. Forums are not enabled for this group
	 *  2. The TRS Forum component is not enabled
	 *  3. The built-in bbPress forums are not correctly installed (usually means they've been
	 *     uninstalled)
	 */
	if ( empty( $group->enable_forum ) || !trs_is_active( 'forums' ) || ( function_exists( 'trs_forums_is_installed_correctly' ) && !trs_forums_is_installed_correctly() ) )
		return false;

	$args = array(
		'forum_id'      => groups_get_groupmeta( $group_id, 'forum_id' ),
		'forum_name'    => $group->name,
		'forum_desc'    => $group->description,
		'forum_slug'    => $group->slug
	);

	trs_forums_update_forum( apply_filters( 'groups_update_group_forum', $args ) );
}
add_action( 'groups_details_updated', 'groups_update_group_forum' );

function groups_new_group_forum_post( $post_text, $topic_id, $page = false ) {
	global $trs;

	if ( empty( $post_text ) )
		return false;

	$post_text = apply_filters( 'group_forum_post_text_before_save', $post_text );
	$topic_id  = apply_filters( 'group_forum_post_topic_id_before_save', $topic_id );

	if ( $post_id = trs_forums_insert_post( array( 'post_text' => $post_text, 'topic_id' => $topic_id ) ) ) {
		$topic = trs_forums_get_topic_details( $topic_id );

		$activity_action = sprintf( __( '%1$s replied to the forum topic %2$s in the group %3$s', 'trendr'), trs_core_get_userlink( $trs->loggedin_user->id ), '<a href="' . trs_get_group_permalink( $trs->groups->current_group ) . 'forum/topic/' . $topic->topic_slug .'/">' . esc_attr( $topic->topic_title ) . '</a>', '<a href="' . trs_get_group_permalink( $trs->groups->current_group ) . '">' . esc_attr( $trs->groups->current_group->name ) . '</a>' );
		$activity_content = trs_create_excerpt( $post_text );
		$primary_link = trs_get_group_permalink( $trs->groups->current_group ) . 'forum/topic/' . $topic->topic_slug . '/';

		if ( $page )
			$primary_link .= "?topic_page=" . $page;

		// Record this in activity streams
		groups_record_activity( array(
			'action'            => apply_filters_ref_array( 'groups_activity_new_forum_post_action',       array( $activity_action,  $post_id, $post_text, &$topic ) ),
			'content'           => apply_filters_ref_array( 'groups_activity_new_forum_post_content',      array( $activity_content, $post_id, $post_text, &$topic ) ),
			'primary_link'      => apply_filters( 'groups_activity_new_forum_post_primary_link', "{$primary_link}#post-{$post_id}" ),
			'type'              => 'new_forum_post',
			'item_id'           => $trs->groups->current_group->id,
			'secondary_item_id' => $post_id
		) );

		do_action( 'groups_new_forum_topic_post', $trs->groups->current_group->id, $post_id );

		return $post_id;
	}

	return false;
}

function groups_new_group_forum_topic( $topic_title, $topic_text, $topic_tags, $forum_id ) {
	global $trs;

	if ( empty( $topic_title ) || empty( $topic_text ) )
		return false;

	$topic_title = apply_filters( 'group_forum_topic_title_before_save', $topic_title );
	$topic_text  = apply_filters( 'group_forum_topic_text_before_save', $topic_text );
	$topic_tags  = apply_filters( 'group_forum_topic_tags_before_save', $topic_tags );
	$forum_id    = apply_filters( 'group_forum_topic_forum_id_before_save', $forum_id );

	if ( $topic_id = trs_forums_new_topic( array( 'topic_title' => $topic_title, 'topic_text' => $topic_text, 'topic_tags' => $topic_tags, 'forum_id' => $forum_id ) ) ) {
		$topic = trs_forums_get_topic_details( $topic_id );

		$activity_action = sprintf( __( '%1$s started the forum topic %2$s in the group %3$s', 'trendr'), trs_core_get_userlink( $trs->loggedin_user->id ), '<a href="' . trs_get_group_permalink( $trs->groups->current_group ) . 'forum/topic/' . $topic->topic_slug .'/">' . esc_attr( $topic->topic_title ) . '</a>', '<a href="' . trs_get_group_permalink( $trs->groups->current_group ) . '">' . esc_attr( $trs->groups->current_group->name ) . '</a>' );
		$activity_content = trs_create_excerpt( $topic_text );

		// Record this in activity streams
		groups_record_activity( array(
			'action'            => apply_filters_ref_array( 'groups_activity_new_forum_topic_action',  array( $activity_action,  $topic_text, &$topic ) ),
			'content'           => apply_filters_ref_array( 'groups_activity_new_forum_topic_content', array( $activity_content, $topic_text, &$topic ) ),
			'primary_link'      => apply_filters( 'groups_activity_new_forum_topic_primary_link', trs_get_group_permalink( $trs->groups->current_group ) . 'forum/topic/' . $topic->topic_slug . '/' ),
			'type'              => 'new_forum_topic',
			'item_id'           => $trs->groups->current_group->id,
			'secondary_item_id' => $topic->topic_id
		) );

	  do_action_ref_array( 'groups_new_forum_topic', array( $trs->groups->current_group->id, &$topic ) );

		return $topic;
	}

	return false;
}

function groups_update_group_forum_topic( $topic_id, $topic_title, $topic_text, $topic_tags = false ) {
	global $trs;

	$topic_title = apply_filters( 'group_forum_topic_title_before_save', $topic_title );
	$topic_text  = apply_filters( 'group_forum_topic_text_before_save',  $topic_text  );

	if ( $topic = trs_forums_update_topic( array( 'topic_title' => $topic_title, 'topic_text' => $topic_text, 'topic_id' => $topic_id, 'topic_tags' => $topic_tags ) ) ) {
		// Update the activity stream item
		if ( trs_is_active( 'activity' ) )
			trs_activity_delete_by_item_id( array( 'item_id' => $trs->groups->current_group->id, 'secondary_item_id' => $topic_id, 'component' => $trs->groups->id, 'type' => 'new_forum_topic' ) );

		$activity_action = sprintf( __( '%1$s started the forum topic %2$s in the group %3$s', 'trendr'), trs_core_get_userlink( $topic->topic_poster ), '<a href="' . trs_get_group_permalink( $trs->groups->current_group ) . 'forum/topic/' . $topic->topic_slug .'/">' . esc_attr( $topic->topic_title ) . '</a>', '<a href="' . trs_get_group_permalink( $trs->groups->current_group ) . '">' . esc_attr( $trs->groups->current_group->name ) . '</a>' );
		$activity_content = trs_create_excerpt( $topic_text );

		// Record this in activity streams
		groups_record_activity( array(
			'action'            => apply_filters_ref_array( 'groups_activity_new_forum_topic_action',  array( $activity_action,  $topic_text, &$topic ) ),
			'content'           => apply_filters_ref_array( 'groups_activity_new_forum_topic_content', array( $activity_content, $topic_text, &$topic ) ),
			'primary_link'      => apply_filters( 'groups_activity_new_forum_topic_primary_link', trs_get_group_permalink( $trs->groups->current_group ) . 'forum/topic/' . $topic->topic_slug . '/' ),
			'type'              => 'new_forum_topic',
			'item_id'           => (int)$trs->groups->current_group->id,
			'user_id'           => (int)$topic->topic_poster,
			'secondary_item_id' => $topic->topic_id,
			'recorded_time '    => $topic->topic_time
		) );

		do_action_ref_array( 'groups_update_group_forum_topic', array( &$topic ) );

		return $topic;
	}

	return false;
}

function groups_update_group_forum_post( $post_id, $post_text, $topic_id, $page = false ) {
	global $trs;

	$post_text = apply_filters( 'group_forum_post_text_before_save', $post_text );
	$topic_id  = apply_filters( 'group_forum_post_topic_id_before_save', $topic_id );
	$post      = trs_forums_get_post( $post_id );

	if ( $post_id = trs_forums_insert_post( array( 'post_id' => $post_id, 'post_text' => $post_text, 'post_time' => $post->post_time, 'topic_id' => $topic_id, 'poster_id' => $post->poster_id ) ) ) {
		$topic = trs_forums_get_topic_details( $topic_id );

		$activity_action = sprintf( __( '%1$s replied to the forum topic %2$s in the group %3$s', 'trendr'), trs_core_get_userlink( $post->poster_id ), '<a href="' . trs_get_group_permalink( $trs->groups->current_group ) . 'forum/topic/' . $topic->topic_slug .'">' . esc_attr( $topic->topic_title ) . '</a>', '<a href="' . trs_get_group_permalink( $trs->groups->current_group ) . '">' . esc_attr( $trs->groups->current_group->name ) . '</a>' );
		$activity_content = trs_create_excerpt( $post_text );
		$primary_link = trs_get_group_permalink( $trs->groups->current_group ) . 'forum/topic/' . $topic->topic_slug . '/';

		if ( $page )
			$primary_link .= "?topic_page=" . $page;

		// Fetch an existing entry and update if one exists.
		if ( trs_is_active( 'activity' ) )
			$id = trs_activity_get_activity_id( array( 'user_id' => $post->poster_id, 'component' => $trs->groups->id, 'type' => 'new_forum_post', 'item_id' => $trs->groups->current_group->id, 'secondary_item_id' => $post_id ) );

		// Update the entry in activity streams
		groups_record_activity( array(
			'id'                => $id,
			'action'            => apply_filters_ref_array( 'groups_activity_new_forum_post_action',  array( $activity_action,  $post_text, &$topic, &$forum_post ) ),
			'content'           => apply_filters_ref_array( 'groups_activity_new_forum_post_content', array( $activity_content, $post_text, &$topic, &$forum_post ) ),
			'primary_link'      => apply_filters( 'groups_activity_new_forum_post_primary_link', $primary_link . "#post-" . $post_id ),
			'type'              => 'new_forum_post',
			'item_id'           => (int)$trs->groups->current_group->id,
			'user_id'           => (int)$post->poster_id,
			'secondary_item_id' => $post_id,
			'recorded_time'     => $post->post_time
		) );

		do_action_ref_array( 'groups_update_group_forum_post', array( $post, &$topic ) );

		return $post_id;
	}

	return false;
}

/**
 * Handles the forum topic deletion routine
 *
 * @package trendr
 *
 * @uses trs_activity_delete() to delete corresponding activity items
 * @uses trs_forums_get_topic_posts() to get the child posts
 * @uses trs_forums_delete_topic() to do the deletion itself
 * @param int $topic_id The id of the topic to be deleted
 * @return bool True if the delete routine went through properly
 */
function groups_delete_group_forum_topic( $topic_id ) {
	global $trs;

	// Before deleting the thread, get the post ids so that their activity items can be deleted
	$posts = trs_forums_get_topic_posts( array( 'topic_id' => $topic_id, 'per_page' => -1 ) );

	if ( trs_forums_delete_topic( array( 'topic_id' => $topic_id ) ) ) {
		do_action( 'groups_before_delete_group_forum_topic', $topic_id );

		// Delete the activity stream items
		if ( trs_is_active( 'activity' ) ) {
			// The activity item for the initial topic
			trs_activity_delete( array( 'item_id' => $trs->groups->current_group->id, 'secondary_item_id' => $topic_id, 'component' => $trs->groups->id, 'type' => 'new_forum_topic' ) );

			// The activity item for each post
			foreach ( (array)$posts as $post ) {
				trs_activity_delete( array( 'item_id' => $trs->groups->current_group->id, 'secondary_item_id' => $post->post_id, 'component' => $trs->groups->id, 'type' => 'new_forum_post' ) );
			}
		}

		do_action( 'groups_delete_group_forum_topic', $topic_id );

		return true;
	}

	return false;
}

/**
 * Delete a forum post
 *
 * @package trendr
 *
 * @param int $post_id The id of the post you want to delete
 * @param int $topic_id Optional. The topic to which the post belongs. This value isn't used in the
 *   function but is passed along to do_action() hooks.
 * @return bool True on success.
 */
function groups_delete_group_forum_post( $post_id, $topic_id = false ) {
	global $trs;

	if ( trs_forums_delete_post( array( 'post_id' => $post_id ) ) ) {
		do_action( 'groups_before_delete_group_forum_post', $post_id, $topic_id );

		// Delete the activity stream item
		if ( trs_is_active( 'activity' ) )
			trs_activity_delete( array( 'item_id' => $trs->groups->current_group->id, 'secondary_item_id' => $post_id, 'component' => $trs->groups->id, 'type' => 'new_forum_post' ) );

		do_action( 'groups_delete_group_forum_post', $post_id, $topic_id );

		return true;
	}

	return false;
}

function groups_total_public_forum_topic_count( $type = 'newest' ) {
	return apply_filters( 'groups_total_public_forum_topic_count', TRS_Groups_Group::get_global_forum_topic_count( $type ) );
}

/**
 * Get a total count of all topics of a given status, across groups/forums
 *
 * @package trendr
 * @since 1.5
 *
 * @param str $status 'public', 'private', 'hidden', 'all' Which group types to count
 * @return int The topic count
 */
function groups_total_forum_topic_count( $status = 'public', $search_terms = false ) {
	return apply_filters( 'groups_total_forum_topic_count', TRS_Groups_Group::get_global_topic_count( $status, $search_terms ) );
}

?>