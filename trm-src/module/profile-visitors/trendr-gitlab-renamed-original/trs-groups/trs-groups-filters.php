<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* Apply WordPress defined filters */
add_filter( 'trs_get_group_description',         'trmtexturize' );
add_filter( 'trs_get_group_description_excerpt', 'trmtexturize' );
add_filter( 'trs_get_group_name',                'trmtexturize' );

add_filter( 'trs_get_group_description',         'convert_smilies' );
add_filter( 'trs_get_group_description_excerpt', 'convert_smilies' );

add_filter( 'trs_get_group_description',         'convert_chars' );
add_filter( 'trs_get_group_description_excerpt', 'convert_chars' );
add_filter( 'trs_get_group_name',                'convert_chars' );

add_filter( 'trs_get_group_description',         'trmautop' );
add_filter( 'trs_get_group_description_excerpt', 'trmautop' );

add_filter( 'trs_get_group_description',         'make_clickable', 9 );
add_filter( 'trs_get_group_description_excerpt', 'make_clickable', 9 );

add_filter( 'trs_get_group_name',                    'trm_filter_kses', 1 );
add_filter( 'trs_get_group_permalink',               'trm_filter_kses', 1 );
add_filter( 'trs_get_group_description',             'trs_groups_filter_kses', 1 );
add_filter( 'trs_get_group_description_excerpt',     'trm_filter_kses', 1 );
add_filter( 'groups_group_name_before_save',        'trm_filter_kses', 1 );
add_filter( 'groups_group_description_before_save', 'trm_filter_kses', 1 );

add_filter( 'trs_get_group_description',         'stripslashes' );
add_filter( 'trs_get_group_description_excerpt', 'stripslashes' );
add_filter( 'trs_get_group_name',                'stripslashes' );
add_filter( 'trs_get_group_member_name',         'stripslashes' );
add_filter( 'trs_get_group_member_link',         'stripslashes' );

add_filter( 'groups_new_group_forum_desc', 'trs_create_excerpt' );

add_filter( 'groups_group_name_before_save',        'force_balance_tags' );
add_filter( 'groups_group_description_before_save', 'force_balance_tags' );

add_filter( 'trs_get_total_group_count',      'trs_core_number_format' );
add_filter( 'trs_get_group_total_for_member', 'trs_core_number_format' );
add_filter( 'trs_get_group_total_members',    'trs_core_number_format' );

function trs_groups_filter_kses( $content ) {
	global $allowedtags;

	$groups_allowedtags                  = $allowedtags;
	$groups_allowedtags['a']['class']    = array();
	$groups_allowedtags['img']           = array();
	$groups_allowedtags['img']['src']    = array();
	$groups_allowedtags['img']['alt']    = array();
	$groups_allowedtags['img']['class']  = array();
	$groups_allowedtags['img']['width']  = array();
	$groups_allowedtags['img']['height'] = array();
	$groups_allowedtags['img']['class']  = array();
	$groups_allowedtags['img']['id']     = array();
	$groups_allowedtags['code']          = array();

	$groups_allowedtags = apply_filters( 'trs_groups_filter_kses', $groups_allowedtags );
	return trm_kses( $content, $groups_allowedtags );
}

/**** Filters for group forums ****/

function groups_add_forum_privacy_sql() {
	global $trs;

	// Only filter the forum SQL on group pages or on the forums directory
	add_filter( 'get_topics_fields',     'groups_add_forum_fields_sql' );
	add_filter( 'get_topics_join', 	     'groups_add_forum_tables_sql' );
	add_filter( 'get_topics_where',      'groups_add_forum_where_sql'  );
}
add_filter( 'btrsress_init', 'groups_add_forum_privacy_sql' );

function groups_add_forum_fields_sql( $sql = '' ) {
	return 't.*, g.id as object_id, g.name as object_name, g.slug as object_slug';
}

function groups_add_forum_tables_sql( $sql = '' ) {
	global $trs;

	$sql .= 'JOIN ' . $trs->groups->table_name . ' AS g LEFT JOIN ' . $trs->groups->table_name_groupmeta . ' AS gm ON g.id = gm.group_id ';

	return $sql;
}

function groups_add_forum_where_sql( $sql = '' ) {
	global $trs;

	// Set this for groups
	$parts['groups'] = "(gm.meta_key = 'forum_id' AND gm.meta_value = t.forum_id)";

	// Restrict to public...
	$parts['private'] = "g.status = 'public'";

	/**
	 * ...but do some checks to possibly remove public restriction.
	 *
	 * Decide if private are visible
	 */
	// Are we in our own profile?
	if ( trs_is_my_profile() )
		unset( $parts['private'] );

	// Are we a super admin?
	elseif ( is_super_admin() )
		unset( $parts['private'] );

	// No need to filter on a single item
	elseif ( trs_is_single_item() )
		unset( $parts['private'] );

	// Check the SQL filter that was passed
	if ( !empty( $sql ) )
		$parts['passed'] = $sql;

	// Assemble Voltron
	$parts_string = implode( ' AND ', $parts );

	// Set it to the global filter
	$trs->groups->filter_sql = $parts_string;

	// Return the global filter
	return $trs->groups->filter_sql;
}

function groups_filter_btrsress_caps( $value, $cap, $args ) {
	global $trs;

	if ( is_super_admin() )
		return true;

	if ( 'add_tag_to' == $cap )
		if ( $trs->groups->current_group->user_has_access ) return true;

	if ( 'manage_forums' == $cap && is_user_logged_in() )
		return true;

	return $value;
}
add_filter( 'bb_current_user_can', 'groups_filter_btrsress_caps', 10, 3 );

/**
 * Amends the forum directory's "last active" bbPress SQL query to stop it fetching
 * information we aren't going to use. This speeds up the query.
 *
 * @see BB_Query::_filter_sql()
 * @since 1.5
 */
function groups_filter_forums_root_page_sql( $sql ) {
	return apply_filters( 'groups_filter_btrsress_root_page_sql', 't.topic_id' );
}
add_filter( 'get_latest_topics_fields', 'groups_filter_forums_root_page_sql' );
?>