<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* Apply WordPress defined filters */
add_filter( 'trs_forums_bbconfig_location', 'trm_filter_kses', 1 );
add_filter( 'trs_forums_bbconfig_location', 'esc_attr', 1 );

add_filter( 'trs_get_the_topic_title', 'trm_filter_kses', 1 );
add_filter( 'trs_get_the_topic_latest_post_excerpt', 'trs_forums_filter_kses', 1 );
add_filter( 'trs_get_the_topic_post_content', 'trs_forums_filter_kses', 1 );

add_filter( 'trs_get_the_topic_title', 'force_balance_tags' );
add_filter( 'trs_get_the_topic_latest_post_excerpt', 'force_balance_tags' );
add_filter( 'trs_get_the_topic_post_content', 'force_balance_tags' );

add_filter( 'trs_get_the_topic_title', 'trmtexturize' );
add_filter( 'trs_get_the_topic_poster_name', 'trmtexturize' );
add_filter( 'trs_get_the_topic_last_poster_name', 'trmtexturize' );
add_filter( 'trs_get_the_topic_post_content', 'trmtexturize' );
add_filter( 'trs_get_the_topic_post_poster_name', 'trmtexturize' );

add_filter( 'trs_get_the_topic_title', 'convert_smilies' );
add_filter( 'trs_get_the_topic_latest_post_excerpt', 'convert_smilies' );
add_filter( 'trs_get_the_topic_post_content', 'convert_smilies' );

add_filter( 'trs_get_the_topic_title', 'convert_chars' );
add_filter( 'trs_get_the_topic_latest_post_excerpt', 'convert_chars' );
add_filter( 'trs_get_the_topic_post_content', 'convert_chars' );

add_filter( 'trs_get_the_topic_post_content', 'trmautop' );
add_filter( 'trs_get_the_topic_latest_post_excerpt', 'trmautop' );

add_filter( 'trs_get_the_topic_post_content', 'stripslashes_deep' );
add_filter( 'trs_get_the_topic_title', 'stripslashes_deep' );
add_filter( 'trs_get_the_topic_latest_post_excerpt', 'stripslashes_deep' );
add_filter( 'trs_get_the_topic_poster_name', 'stripslashes_deep' );
add_filter( 'trs_get_the_topic_last_poster_name', 'stripslashes_deep' );
add_filter( 'trs_get_the_topic_object_name', 'stripslashes_deep' );

add_filter( 'trs_get_the_topic_post_content', 'make_clickable', 9 );

add_filter( 'trs_get_forum_topic_count_for_user', 'trs_core_number_format' );
add_filter( 'trs_get_forum_topic_count', 'trs_core_number_format' );

add_filter( 'trs_get_the_topic_title', 'trs_forums_make_nofollow_filter' );
add_filter( 'trs_get_the_topic_latest_post_excerpt', 'trs_forums_make_nofollow_filter' );
add_filter( 'trs_get_the_topic_post_content', 'trs_forums_make_nofollow_filter' );

function trs_forums_filter_kses( $content ) {
	global $allowedtags;

	$forums_allowedtags = $allowedtags;
	$forums_allowedtags['span'] = array();
	$forums_allowedtags['span']['class'] = array();
	$forums_allowedtags['div'] = array();
	$forums_allowedtags['div']['class'] = array();
	$forums_allowedtags['div']['id'] = array();
	$forums_allowedtags['a']['class'] = array();
	$forums_allowedtags['img'] = array();
	$forums_allowedtags['br'] = array();
	$forums_allowedtags['p'] = array();
	$forums_allowedtags['img']['src'] = array();
	$forums_allowedtags['img']['alt'] = array();
	$forums_allowedtags['img']['class'] = array();
	$forums_allowedtags['img']['width'] = array();
	$forums_allowedtags['img']['height'] = array();
	$forums_allowedtags['img']['class'] = array();
	$forums_allowedtags['img']['id'] = array();
	$forums_allowedtags['code'] = array();
	$forums_allowedtags['blockquote'] = array();

	$forums_allowedtags = apply_filters( 'trs_forums_allowed_tags', $forums_allowedtags );
	return trm_kses( $content, $forums_allowedtags );
}

function trs_forums_filter_tag_link( $link, $tag, $page, $context ) {
	global $trs;

	return apply_filters( 'trs_forums_filter_tag_link', trs_get_root_domain() . '/' . trs_get_forums_root_slug() . '/tag/' . $tag . '/' );
}
add_filter( 'bb_get_tag_link', 'trs_forums_filter_tag_link', 10, 4);

function trs_forums_make_nofollow_filter( $text ) {
	return preg_replace_callback( '|<a (.+?)>|i', 'trs_forums_make_nofollow_filter_callback', $text );
}
	function trs_forums_make_nofollow_filter_callback( $matches ) {
		$text = $matches[1];
		$text = str_replace( array( ' rel="nofollow"', " rel='nofollow'"), '', $text );
		return "<a $text rel=\"nofollow\">";
	}

/**
 * trs_forums_add_forum_topic_to_page_title( $title )
 *
 * Append forum topic to page title
 *
 * @global object $trs
 * @param string $title New page title; see trs_modify_page_title()
 * @param string $title Original page title
 * @param string $sep How to separate the various items within the page title.
 * @param string $seplocation Direction to display title
 * @return string
 * @see trs_modify_page_title()
 */
function trs_forums_add_forum_topic_to_page_title( $title, $original_title, $sep, $seplocation  ) {
	global $trs;

	if ( trs_is_current_action( 'forum' ) && trs_is_action_variable( 'topic', 0 ) )
		if ( trs_has_forum_topic_posts() )
			$title .= trs_get_the_topic_title() . " $sep ";

	return $title;
}
add_filter( 'trs_modify_page_title', 'trs_forums_add_forum_topic_to_page_title', 9, 4 );

/**
 * trs_forums_strip_mentions_on_post_edit( $title )
 *
 * Removes the anchor tag autogenerated for at-mentions when forum topics and posts are edited.
 * Prevents embedded anchor tags.
 *
 * @global object $trs
 * @param string $content
 * @return string $content
 */
function trs_forums_strip_mentions_on_post_edit( $content ) {
	global $trs;

	$content = htmlspecialchars_decode( $content );

	$pattern = "|<a href=&#039;" . trs_get_root_domain() . "/" . trs_get_members_root_slug() . "/[A-Za-z0-9-_\.]+/&#039; rel=&#039;nofollow&#039;>(@[A-Za-z0-9-_\.@]+)</a>|";

	$content = preg_replace( $pattern, "$1", $content );

	return $content;
}
add_filter( 'trs_get_the_topic_post_edit_text', 'trs_forums_strip_mentions_on_post_edit' );
add_filter( 'trs_get_the_topic_text', 'trs_forums_strip_mentions_on_post_edit' );

/**
 * "REPLIED TO" SQL FILTERS
 */

/**
 * Filters the get_topics_distinct portion of the Forums sql when on a user's Replied To page.
 *
 * This filter is added in trs_has_forum_topics()
 *
 * @package trendr
 * @since 1.5
 *
 * @global object $trmdb The WordPress database global
 * @param string $sql
 * @return string $sql
 */
function trs_forums_add_replied_distinct_sql( $sql ) {
	global $trmdb;

	$sql = $trmdb->prepare( "DISTINCT t.topic_id, " );

	return $sql;
}

/**
 * Filters the get_topics_join portion of the Forums sql when on a user's Replied To page.
 *
 * This filter is added in trs_has_forum_topics()
 *
 * @package trendr
 * @since 1.5
 *
 * @global object $bbdb The bbPress database global
 * @global object $trmdb The WordPress database global
 * @param string $sql
 * @return string $sql
 */
function trs_forums_add_replied_join_sql( $sql ) {
	global $bbdb, $trmdb;

	$sql .= $trmdb->prepare( " LEFT JOIN $bbdb->posts p ON p.topic_id = t.topic_id " );

	return $sql;
}

/**
 * Filters the get_topics_where portion of the Forums sql when on a user's Replied To page.
 *
 * This filter is added in trs_has_forum_topics()
 *
 * @package trendr
 * @since 1.5
 *
 * @global object $trmdb The WordPress database global
 * @param string $sql
 * @return string $sql
 */
function trs_forums_add_replied_where_sql( $sql ) {
	global $trmdb;

	$sql .= $trmdb->prepare( " AND p.poster_id = %s ", trs_displayed_user_id() );

	// Remove any topic_author information
	$sql = str_replace( " AND t.topic_poster = '" . trs_displayed_user_id() . "'", '', $sql );

	return $sql;
}

?>