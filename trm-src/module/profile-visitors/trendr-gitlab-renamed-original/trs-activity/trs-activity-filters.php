<?php

/**
 * The Activity filters
 *
 * @package trendr
 * @sutrsackage ActivityFilters
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Apply WordPress defined filters
add_filter( 'trs_get_activity_action',                'trs_activity_filter_kses', 1 );
add_filter( 'trs_get_activity_content_body',          'trs_activity_filter_kses', 1 );
add_filter( 'trs_get_activity_content',               'trs_activity_filter_kses', 1 );
add_filter( 'trs_get_activity_parent_content',        'trs_activity_filter_kses', 1 );
add_filter( 'trs_get_activity_latest_update',         'trs_activity_filter_kses', 1 );
add_filter( 'trs_get_activity_latest_update_excerpt', 'trs_activity_filter_kses', 1 );
add_filter( 'trs_get_activity_feed_item_description', 'trs_activity_filter_kses', 1 );
add_filter( 'trs_activity_content_before_save',       'trs_activity_filter_kses', 1 );
add_filter( 'trs_activity_action_before_save',        'trs_activity_filter_kses', 1 );

add_filter( 'trs_get_activity_action',                'force_balance_tags' );
add_filter( 'trs_get_activity_content_body',          'force_balance_tags' );
add_filter( 'trs_get_activity_content',               'force_balance_tags' );
add_filter( 'trs_get_activity_latest_update',         'force_balance_tags' );
add_filter( 'trs_get_activity_latest_update_excerpt', 'force_balance_tags' );
add_filter( 'trs_get_activity_feed_item_description', 'force_balance_tags' );
add_filter( 'trs_activity_content_before_save',       'force_balance_tags' );
add_filter( 'trs_activity_action_before_save',        'force_balance_tags' );

add_filter( 'trs_get_activity_action',                'trmtexturize' );
add_filter( 'trs_get_activity_content_body',          'trmtexturize' );
add_filter( 'trs_get_activity_content',               'trmtexturize' );
add_filter( 'trs_get_activity_parent_content',        'trmtexturize' );
add_filter( 'trs_get_activity_latest_update',         'trmtexturize' );
add_filter( 'trs_get_activity_latest_update_excerpt', 'trmtexturize' );

add_filter( 'trs_get_activity_action',                'convert_smilies' );
add_filter( 'trs_get_activity_content_body',          'convert_smilies' );
add_filter( 'trs_get_activity_content',               'convert_smilies' );
add_filter( 'trs_get_activity_parent_content',        'convert_smilies' );
add_filter( 'trs_get_activity_latest_update',         'convert_smilies' );
add_filter( 'trs_get_activity_latest_update_excerpt', 'convert_smilies' );

add_filter( 'trs_get_activity_action',                'convert_chars' );
add_filter( 'trs_get_activity_content_body',          'convert_chars' );
add_filter( 'trs_get_activity_content',               'convert_chars' );
add_filter( 'trs_get_activity_parent_content',        'convert_chars' );
add_filter( 'trs_get_activity_latest_update',         'convert_chars' );
add_filter( 'trs_get_activity_latest_update_excerpt', 'convert_chars' );

add_filter( 'trs_get_activity_action',                'trmautop' );
add_filter( 'trs_get_activity_content_body',          'trmautop' );
add_filter( 'trs_get_activity_content',               'trmautop' );
add_filter( 'trs_get_activity_feed_item_description', 'trmautop' );

add_filter( 'trs_get_activity_action',                'make_clickable', 9 );
add_filter( 'trs_get_activity_content_body',          'make_clickable', 9 );
add_filter( 'trs_get_activity_content',               'make_clickable', 9 );
add_filter( 'trs_get_activity_parent_content',        'make_clickable', 9 );
add_filter( 'trs_get_activity_latest_update',         'make_clickable', 9 );
add_filter( 'trs_get_activity_latest_update_excerpt', 'make_clickable', 9 );
add_filter( 'trs_get_activity_feed_item_description', 'make_clickable', 9 );

add_filter( 'trs_acomment_name',                      'stripslashes_deep' );
add_filter( 'trs_get_activity_action',                'stripslashes_deep' );
add_filter( 'trs_get_activity_content',               'stripslashes_deep' );
add_filter( 'trs_get_activity_content_body',          'stripslashes_deep' );
add_filter( 'trs_get_activity_parent_content',        'stripslashes_deep' );
add_filter( 'trs_get_activity_latest_update',         'stripslashes_deep' );
add_filter( 'trs_get_activity_latest_update_excerpt', 'stripslashes_deep' );
add_filter( 'trs_get_activity_feed_item_description', 'stripslashes_deep' );

// Apply trendr defined filters
add_filter( 'trs_get_activity_content',               'trs_activity_make_nofollow_filter' );
add_filter( 'trs_get_activity_content_body',          'trs_activity_make_nofollow_filter' );
add_filter( 'trs_get_activity_parent_content',        'trs_activity_make_nofollow_filter' );
add_filter( 'trs_get_activity_latest_update',         'trs_activity_make_nofollow_filter' );
add_filter( 'trs_get_activity_latest_update_excerpt', 'trs_activity_make_nofollow_filter' );
add_filter( 'trs_get_activity_feed_item_description', 'trs_activity_make_nofollow_filter' );

add_filter( 'pre_comment_content',                   'trs_activity_at_name_filter' );
add_filter( 'group_forum_topic_text_before_save',    'trs_activity_at_name_filter' );
add_filter( 'group_forum_post_text_before_save',     'trs_activity_at_name_filter' );

add_filter( 'trs_get_activity_parent_content',        'trs_create_excerpt' );

/**
 * Custom kses filtering for activity content
 *
 * @since 1.1.0
 *
 * @param string $content The activity content
 *
 * @uses apply_filters() To call the 'trs_activity_allowed_tags' hook.
 * @uses trm_kses()
 *
 * @return string $content Filtered activity content
 */
function trs_activity_filter_kses( $content ) {
	global $allowedtags;

	$activity_allowedtags = $allowedtags;
	$activity_allowedtags['span']          = array();
	$activity_allowedtags['span']['class'] = array();
	$activity_allowedtags['div']           = array();
	$activity_allowedtags['div']['class']  = array();
	$activity_allowedtags['div']['id']     = array();
	$activity_allowedtags['a']['class']    = array();
	$activity_allowedtags['a']['id']       = array();
	$activity_allowedtags['a']['rel']      = array();
	$activity_allowedtags['img']           = array();
	$activity_allowedtags['img']['src']    = array();
	$activity_allowedtags['img']['alt']    = array();
	$activity_allowedtags['img']['class']  = array();
	$activity_allowedtags['img']['width']  = array();
	$activity_allowedtags['img']['height'] = array();
	$activity_allowedtags['img']['class']  = array();
	$activity_allowedtags['img']['id']     = array();
	$activity_allowedtags['img']['title']  = array();
	$activity_allowedtags['code']          = array();

	$activity_allowedtags = apply_filters( 'trs_activity_allowed_tags', $activity_allowedtags );
	return trm_kses( $content, $activity_allowedtags );
}

/**
 * Finds and links @-mentioned users in the contents of activity items
 *
 * @since 1.2.0
 *
 * @param string $content The activity content
 * @param int $activity_id The activity id
 *
 * @uses trs_activity_find_mentions()
 * @uses trs_is_username_compatibility_mode()
 * @uses trs_core_get_userid_from_nicename()
 * @uses trs_activity_at_message_notification()
 * @uses trs_core_get_user_domain()
 * @uses trs_activity_adjust_mention_count()
 *
 * @return string $content Content filtered for mentions
 */
function trs_activity_at_name_filter( $content, $activity_id = 0 ) {
	$usernames = trs_activity_find_mentions( $content );

	foreach( (array)$usernames as $username ) {
		if ( trs_is_username_compatibility_mode() )
			$user_id = username_exists( $username );
		else
			$user_id = trs_core_get_userid_from_nicename( $username );

		if ( empty( $user_id ) )
			continue;

		// If an activity_id is provided, we can send email and TRS notifications
		if ( $activity_id ) {
			trs_activity_at_message_notification( $activity_id, $user_id );
		}

		$content = preg_replace( '/(@' . $username . '\b)/', "<a href='" . trs_core_get_user_domain( $user_id ) . "' rel='nofollow'>@$username</a>", $content );
	}

	// Adjust the activity count for this item
	if ( $activity_id )
		trs_activity_adjust_mention_count( $activity_id, 'add' );

	return $content;
}

/**
 * Catch mentions in saved activity items
 *
 * @since 1.5.0
 *
 * @param obj $activity
 *
 * @uses remove_filter() To remove the 'trs_activity_at_name_filter_updates' hook.
 * @uses trs_activity_at_name_filter()
 * @uses TRS_Activity_Activity::save() {@link TRS_Activity_Activity}
 */
function trs_activity_at_name_filter_updates( $activity ) {
	// Only run this function once for a given activity item
	remove_filter( 'trs_activity_after_save', 'trs_activity_at_name_filter_updates' );

	// Run the content through the linking filter, making sure to increment mention count
	$activity->content = trs_activity_at_name_filter( $activity->content, $activity->id );

	// Resave the activity with the new content
	$activity->save();
}
add_filter( 'trs_activity_after_save', 'trs_activity_at_name_filter_updates' );

/**
 * Catches links in activity text so rel=nofollow can be added
 *
 * @since 1.2.0
 *
 * @param string $text Activity text
 *
 * @return string $text Text with rel=nofollow added to any links
 */
function trs_activity_make_nofollow_filter( $text ) {
	return preg_replace_callback( '|<a (.+?)>|i', 'trs_activity_make_nofollow_filter_callback', $text );
}

	/**
	 * Adds rel=nofollow to a link
	 *
	 * @since 1.2.0
	 *
	 * @param array $matches
	 *
	 * @return string $text Link with rel=nofollow added
	 */
	function trs_activity_make_nofollow_filter_callback( $matches ) {
		$text = $matches[1];
		$text = str_replace( array( ' rel="nofollow"', " rel='nofollow'"), '', $text );
		return "<a $text rel=\"nofollow\">";
	}

/**
 * Truncates long activity entries when viewed in activity streams
 *
 * @since 1.5.0
 *
 * @param $text The original activity entry text
 *
 * @uses trs_is_single_activity()
 * @uses apply_filters() To call the 'trs_activity_excerpt_append_text' hook
 * @uses apply_filters() To call the 'trs_activity_excerpt_length' hook
 * @uses trs_create_excerpt()
 * @uses trs_get_activity_id()
 * @uses trs_get_activity_thread_permalink()
 * @uses apply_filters() To call the 'trs_activity_truncate_entry' hook
 *
 * @return string $excerpt The truncated text
 */
function trs_activity_truncate_entry( $text ) {
	global $activities_template;

	// The full text of the activity update should always show on the single activity screen
	if ( trs_is_single_activity() )
		return $text;

	$append_text    = apply_filters( 'trs_activity_excerpt_append_text', __( '[Read more]', 'trendr' ) );
	$excerpt_length = apply_filters( 'trs_activity_excerpt_length', 358 );

	// Run the text through the excerpt function. If it's too short, the original text will be
	// returned.
	$excerpt        = trs_create_excerpt( $text, $excerpt_length, array( 'ending' => __( '&hellip;', 'trendr' ) ) );

	// If the text returned by trs_create_excerpt() is different from the original text (ie it's
	// been truncated), add the "Read More" link.
	if ( $excerpt != $text ) {
		$id = !empty( $activities_template->activity->current_comment->id ) ? 'acomment-read-more-' . $activities_template->activity->current_comment->id : 'activity-read-more-' . trs_get_activity_id();

		$excerpt = sprintf( '%1$s<span class="activity-read-more" id="%2$s"><a href="%3$s" rel="nofollow">%4$s</a></span>', $excerpt, $id, trs_get_activity_thread_permalink(), $append_text );
	}

	return apply_filters( 'trs_activity_truncate_entry', $excerpt, $text, $append_text );
}
add_filter( 'trs_get_activity_content_body', 'trs_activity_truncate_entry', 5 );
add_filter( 'trs_get_activity_content', 'trs_activity_truncate_entry', 5 );

?>