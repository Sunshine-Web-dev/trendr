<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function trs_forums_is_installed_correctly() {
	global $trs;

	if ( isset( $trs->forums->bbconfig ) && is_file( $trs->forums->bbconfig ) )
		return true;

	return false;
}

/**
 * Checks $trs pages global and looks for directory page
 *
 * @since 1.5
 *
 * @global object $trs Global trendr settings object
 * @return bool True if set, False if empty
 */
function trs_forums_has_directory() {
	global $trs;

	return (bool) !empty( $trs->pages->forums->id );
}

/** Forum Functions ***********************************************************/

function trs_forums_get_forum( $forum_id ) {
	do_action( 'btrsress_init' );
	return bb_get_forum( $forum_id );
}

function trs_forums_new_forum( $args = '' ) {
	do_action( 'btrsress_init' );

	$defaults = array(
		'forum_name'        => '',
		'forum_desc'        => '',
		'forum_parent_id'   => trs_forums_parent_forum_id(),
		'forum_order'       => false,
		'forum_is_category' => 0
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	return bb_new_forum( array( 'forum_name' => stripslashes( $forum_name ), 'forum_desc' => stripslashes( $forum_desc ), 'forum_parent' => $forum_parent_id, 'forum_order' => $forum_order, 'forum_is_category' => $forum_is_category ) );
}

function trs_forums_update_forum( $args = '' ) {
	do_action( 'btrsress_init' );

	$defaults = array(
		'forum_id'          => '',
		'forum_name'        => '',
		'forum_desc'        => '',
		'forum_slug'        => '',
		'forum_parent_id'   => trs_forums_parent_forum_id(),
		'forum_order'       => false,
		'forum_is_category' => 0
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	return bb_update_forum( array( 'forum_id' => (int)$forum_id, 'forum_name' => stripslashes( $forum_name ), 'forum_desc' => stripslashes( $forum_desc ), 'forum_slug' => stripslashes( $forum_slug ), 'forum_parent' => $forum_parent_id, 'forum_order' => $forum_order, 'forum_is_category' => $forum_is_category ) );
}

/** Topic Functions ***********************************************************/

function trs_forums_get_forum_topics( $args = '' ) {
	global $trs;

	do_action( 'btrsress_init' );

	$defaults = array(
		'type'          => 'newest',
		'forum_id'      => false,
		'user_id'       => false,
		'page'          => 1,
		'per_page'      => 15,
		'offset'        => false,
		'number'        => false,
		'exclude'       => false,
		'show_stickies' => 'all',
		'filter'        => false // if $type = tag then filter is the tag name, otherwise it's terms to search on.
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( class_exists( 'BB_Query' ) ) {
		switch ( $type ) {
			case 'newest':
				$query = new BB_Query( 'topic', array( 'forum_id' => $forum_id, 'topic_author_id' => $user_id, 'per_page' => $per_page, 'page' => $page, 'number' => $per_page, 'exclude' => $exclude, 'topic_title' => $filter, 'sticky' => $show_stickies, 'offset' => $offset, 'number' => $number ), 'get_latest_topics' );
				$topics =& $query->results;
				break;

			case 'popular':
				$query = new BB_Query( 'topic', array( 'forum_id' => $forum_id, 'topic_author_id' => $user_id, 'per_page' => $per_page, 'page' => $page, 'order_by' => 't.topic_posts', 'topic_title' => $filter, 'sticky' => $show_stickies, 'offset' => $offset, 'number' => $number ) );
				$topics =& $query->results;
				break;

			case 'unreplied':
				$query = new BB_Query( 'topic', array( 'forum_id' => $forum_id, 'topic_author_id' => $user_id, 'post_count' => 1, 'per_page' => $per_page, 'page' => $page, 'order_by' => 't.topic_time', 'topic_title' => $filter, 'sticky' => $show_stickies, 'offset' => $offset, 'number' => $number ) );
				$topics =& $query->results;
				break;

			case 'tags':
				$query = new BB_Query( 'topic', array( 'forum_id' => $forum_id, 'topic_author_id' => $user_id, 'tag' => $filter, 'per_page' => $per_page, 'page' => $page, 'order_by' => 't.topic_time', 'sticky' => $show_stickies, 'offset' => $offset, 'number' => $number ) );
				$topics =& $query->results;
				break;
		}
	} else {
		$topics = array();
	}

	return apply_filters_ref_array( 'trs_forums_get_forum_topics', array( &$topics, &$r ) );
}

function trs_forums_get_topic_details( $topic_id ) {
	do_action( 'btrsress_init' );

	$query = new BB_Query( 'topic', 'topic_id=' . $topic_id . '&page=1' /* Page override so bbPress doesn't use the URI */ );

	return $query->results[0];
}

function trs_forums_get_topic_id_from_slug( $topic_slug ) {
	do_action( 'btrsress_init' );

	if ( empty( $topic_slug ) )
		return false;

	return bb_get_id_from_slug( 'topic', $topic_slug );
}

function trs_forums_new_topic( $args = '' ) {
	global $trs;

	do_action( 'btrsress_init' );

	$defaults = array(
		'topic_title'            => '',
		'topic_slug'             => '',
		'topic_text'             => '',
		'topic_poster'           => $trs->loggedin_user->id,       // accepts ids
		'topic_poster_name'      => $trs->loggedin_user->fullname, // accept names
		'topic_last_poster'      => $trs->loggedin_user->id,       // accepts ids
		'topic_last_poster_name' => $trs->loggedin_user->fullname, // accept names
		'topic_start_time'       => trs_core_current_time(),
		'topic_time'             => trs_core_current_time(),
		'topic_open'             => 1,
		'topic_tags'             => false, // accepts array or comma delim
		'forum_id'               => 0      // accepts ids or slugs
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$topic_title = strip_tags( $topic_title );

	if ( empty( $topic_title ) || !strlen( trim( $topic_title ) ) )
		return false;

	if ( empty( $topic_poster ) )
		return false;

	if ( trs_core_is_user_spammer( $topic_poster ) || trs_core_is_user_deleted( $topic_poster ) )
		return false;

	if ( empty( $topic_slug ) )
		$topic_slug = sanitize_title( $topic_title );

	if ( !$topic_id = bb_insert_topic( array( 'topic_title' => stripslashes( $topic_title ), 'topic_slug' => $topic_slug, 'topic_poster' => $topic_poster, 'topic_poster_name' => $topic_poster_name, 'topic_last_poster' => $topic_last_poster, 'topic_last_poster_name' => $topic_last_poster_name, 'topic_start_time' => $topic_start_time, 'topic_time' => $topic_time, 'topic_open' => $topic_open, 'forum_id' => (int)$forum_id, 'tags' => $topic_tags ) ) )
		return false;

	// Now insert the first post.
	if ( !trs_forums_insert_post( array( 'topic_id' => $topic_id, 'post_text' => $topic_text, 'post_time' => $topic_time, 'poster_id' => $topic_poster ) ) )
		return false;

	do_action( 'trs_forums_new_topic', $topic_id );

	return $topic_id;
}

function trs_forums_update_topic( $args = '' ) {
	global $trs;

	do_action( 'btrsress_init' );

	$defaults = array(
		'topic_id'    => false,
		'topic_title' => '',
		'topic_text'  => '',
		'topic_tags'  => false
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	// Check if the user is a spammer 
	if ( trs_core_is_user_spammer( $trs->loggedin_user->id ) || trs_core_is_user_deleted( $trs->loggedin_user->id ) ) 
		return false;

	// bb_insert_topic() will append tags, but not remove them. So we remove all existing tags.
	bb_remove_topic_tags( $topic_id );

	if ( !$topic_id = bb_insert_topic( array( 'topic_id' => $topic_id, 'topic_title' => stripslashes( $topic_title ), 'tags' => $topic_tags ) ) )
		return false;

	if ( !$post = bb_get_first_post( $topic_id ) )
		return false;

	// Update the first post
	if ( !$post = trs_forums_insert_post( array( 'post_id' => $post->post_id, 'topic_id' => $topic_id, 'post_text' => $topic_text, 'post_time' => $post->post_time, 'poster_id' => $post->poster_id, 'poster_ip' => $post->poster_ip, 'post_status' => $post->post_status, 'post_position' => $post->post_position ) ) )
		return false;

	return trs_forums_get_topic_details( $topic_id );
}

function trs_forums_sticky_topic( $args = '' ) {
	global $trs;

	do_action( 'btrsress_init' );

	$defaults = array(
		'topic_id' => false,
		'mode'     => 'stick' // stick/unstick
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( 'stick' == $mode )
		return bb_stick_topic( $topic_id );
	else if ( 'unstick' == $mode )
		return bb_unstick_topic( $topic_id );

	return false;
}

function trs_forums_openclose_topic( $args = '' ) {
	global $trs;

	do_action( 'btrsress_init' );

	$defaults = array(
		'topic_id' => false,
		'mode'     => 'close' // stick/unstick
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( 'close' == $mode )
		return bb_close_topic( $topic_id );
	else if ( 'open' == $mode )
		return bb_open_topic( $topic_id );

	return false;
}

function trs_forums_delete_topic( $args = '' ) {
	global $trs;

	do_action( 'btrsress_init' );

	$defaults = array(
		'topic_id' => false
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	return bb_delete_topic( $topic_id, 1 );
}

function trs_forums_total_topic_count() {
	global $bbdb;

	do_action( 'btrsress_init' );

	if ( isset( $bbdb ) ) {
		if ( trs_is_active( 'groups' ) ) {
			$groups_table_sql = groups_add_forum_tables_sql();
			$groups_where_sql = groups_add_forum_where_sql( "t.topic_status = 0" );
		} else {
			$groups_table_sql = '';
			$groups_where_sql = "t.topic_status = 0";
		}
		$count = $bbdb->get_results( $bbdb->prepare( "SELECT t.topic_id FROM {$bbdb->topics} AS t {$groups_table_sql} WHERE {$groups_where_sql}" ) );
		$count = count( (array)$count );
	} else {
		$count = 0;
	}

	return apply_filters( 'trs_forums_total_topic_count', $count );
}

/**
 * Get a total "Topics Started" count for a given user
 *
 * @package trendr
 *
 * @param int $user_id ID of the user being queried. Falls back on displayed user, then loggedin
 * @param str $type The current filter/sort type. 'active', 'popular', 'unreplied'
 * @return int $count The topic count
 */
function trs_forums_total_topic_count_for_user( $user_id = 0, $type = 'active' ) {
	global $trs;

	do_action( 'btrsress_init' );

	if ( !$user_id )
		$user_id = ( $trs->displayed_user->id ) ? $trs->displayed_user->id : $trs->loggedin_user->id;

	if ( class_exists( 'BB_Query' ) ) {
		$args = array(
			'topic_author_id' => $user_id,
			'page' 		  => 1,
			'per_page'	  => -1,
			'count'		  => true
		);

		if ( 'unreplied' == $type )
			$args['post_count'] = 1;

		$query = new BB_Query( 'topic', $args );
		$count = $query->count;
		$query = null;
	} else {
		$count = 0;
	}

	return $count;
}

/**
 * Return the total number of topics replied to by a given user
 *
 * Uses an unfortunate technique to count unique topics, due to limitations in BB_Query.
 *
 * @package trendr
 * @since 1.5
 *
 * @param int $user_id Defaults to displayed user, then to logged-in user
 * @return int $count
 */
function trs_forums_total_replied_count_for_user( $user_id = 0, $type = 'active' ) {
	global $trs;

	do_action( 'btrsress_init' );

	if ( !$user_id )
		$user_id = ( trs_displayed_user_id() ) ? trs_displayed_user_id() : trs_loggedin_user_id();

	if ( !$user_id )
		return 0;

	if ( class_exists( 'BB_Query' ) ) {
		$query = new BB_Query( 'post', array( 'post_author_id' => $user_id, 'page' => 1, 'per_page' => -1, 'count' => true ) );

		// Count the unique topics. No better way to do this in the bbPress query API
		$topics = array();
		foreach( $query->results as $result ) {
			if ( !in_array( $result->topic_id, $topics ) )
				$topics[] = $result->topic_id;
		}

		// Even more unfortunate. If this is filtered by 'unreplied', we have to requery
		if ( 'unreplied' == $type ) {
			$topic_ids = implode( ',', $topics );
			$topics_query = new BB_Query( 'topic', array( 'topic_id' => $topic_ids, 'page' => 1, 'per_page' => -1, 'post_count' => 1 ) );
			$count = count( $topics_query->results );
		} else {
			$count = count( $topics );
		}
		$query = null;
	} else {
		$count = 0;
	}

	return apply_filters( 'trs_forums_total_replied_count_for_user', $count, $user_id );
}

function trs_forums_get_topic_extras( $topics ) {
	global $trs, $trmdb, $bbdb;

	if ( empty( $topics ) )
		return $topics;

	// Get the topic ids
	foreach ( (array)$topics as $topic ) $topic_ids[] = $topic->topic_id;
	$topic_ids = $trmdb->escape( join( ',', (array)$topic_ids ) );

	// Fetch the topic's last poster details
	$poster_details = $trmdb->get_results( $trmdb->prepare( "SELECT t.topic_id, t.topic_last_poster, u.user_login, u.user_nicename, u.user_email, u.display_name FROM {$trmdb->users} u, {$bbdb->topics} t WHERE u.ID = t.topic_last_poster AND t.topic_id IN ( {$topic_ids} )" ) );
	for ( $i = 0, $count = count( $topics ); $i < $count; ++$i ) {
		foreach ( (array)$poster_details as $poster ) {
			if ( $poster->topic_id == $topics[$i]->topic_id ) {
				$topics[$i]->topic_last_poster_email       = $poster->user_email;
				$topics[$i]->topic_last_poster_nicename    = $poster->user_nicename;
				$topics[$i]->topic_last_poster_login       = $poster->user_login;
				$topics[$i]->topic_last_poster_displayname = $poster->display_name;
			}
		}
	}

	// Fetch fullname for the topic's last poster
	if ( trs_is_active( 'xprofile' ) ) {
		$poster_names = $trmdb->get_results( $trmdb->prepare( "SELECT t.topic_id, pd.value FROM {$trs->profile->table_name_data} pd, {$bbdb->topics} t WHERE pd.user_id = t.topic_last_poster AND pd.field_id = 1 AND t.topic_id IN ( {$topic_ids} )" ) );
		for ( $i = 0, $count = count( $topics ); $i < $count; ++$i ) {
			foreach ( (array)$poster_names as $name ) {
				if ( $name->topic_id == $topics[$i]->topic_id )
					$topics[$i]->topic_last_poster_displayname = $name->value;
			}
		}
	}

	// Loop through to make sure that each topic has the proper values set. This covers the
	// case of deleted users
	foreach ( (array)$topics as $key => $topic ) {
		if ( !isset( $topic->topic_last_poster_email ) )
			$topics[$key]->topic_last_poster_email = '';

		if ( !isset( $topic->topic_last_poster_nicename ) )
			$topics[$key]->topic_last_poster_nicename = '';

		if ( !isset( $topic->topic_last_poster_login ) )
			$topics[$key]->topic_last_poster_login = '';

		if ( !isset( $topic->topic_last_poster_displayname ) )
			$topics[$key]->topic_last_poster_displayname = '';
	}

	return $topics;
}

/** Post Functions ************************************************************/

function trs_forums_get_topic_posts( $args = '' ) {
	do_action( 'btrsress_init' );

	$defaults = array(
		'topic_id' => false,
		'page'     => 1,
		'per_page' => 15,
		'order'    => 'ASC'
	);

	$args  = trm_parse_args( $args, $defaults );
	$query = new BB_Query( 'post', $args, 'get_thread' );

	return trs_forums_get_post_extras( $query->results );
}

function trs_forums_get_post( $post_id ) {
	do_action( 'btrsress_init' );
	return bb_get_post( $post_id );
}

function trs_forums_delete_post( $args = '' ) {
	global $trs;

	do_action( 'btrsress_init' );

	$defaults = array(
		'post_id' => false
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	return bb_delete_post( $post_id, 1 );
}

function trs_forums_insert_post( $args = '' ) {
	global $trs;

	do_action( 'btrsress_init' );

	$defaults = array(
		'post_id'       => false,
		'topic_id'      => false,
		'post_text'     => '',
		'post_time'     => trs_core_current_time(),
		'poster_id'     => $trs->loggedin_user->id, // accepts ids or names
		'poster_ip'     => $_SERVER['REMOTE_ADDR'],
		'post_status'   => 0, // use bb_delete_post() instead
		'post_position' => false
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( !$post = trs_forums_get_post( $post_id ) )
		$post_id = false;

	if ( !isset( $topic_id ) )
		$topic_id = $post->topic_id;

	if ( empty( $post_text ) )
		$post_text = $post->post_text;

	if ( !isset( $post_time ) )
		$post_time = $post->post_time;

	if ( !isset( $post_position ) )
		$post_position = $post->post_position;

	if ( empty( $poster_id ) )
		return false;

	if ( trs_core_is_user_spammer( $trs->loggedin_user->id ) || trs_core_is_user_deleted( $trs->loggedin_user->id ) )
		return false;

	$post_id = bb_insert_post( array( 'post_id' => $post_id, 'topic_id' => $topic_id, 'post_text' => stripslashes( trim( $post_text ) ), 'post_time' => $post_time, 'poster_id' => $poster_id, 'poster_ip' => $poster_ip, 'post_status' => $post_status, 'post_position' => $post_position ) );

	if ( !empty( $post_id ) )
		do_action( 'trs_forums_new_post', $post_id );

	return $post_id;
}

function trs_forums_get_post_extras( $posts ) {
	global $trs, $trmdb;

	if ( empty( $posts ) )
		return $posts;

	// Get the user ids
	foreach ( (array)$posts as $post ) $user_ids[] = $post->poster_id;
	$user_ids = $trmdb->escape( join( ',', (array)$user_ids ) );

	// Fetch the poster's user_email, user_nicename and user_login
	$poster_details = $trmdb->get_results( $trmdb->prepare( "SELECT u.ID as user_id, u.user_login, u.user_nicename, u.user_email, u.display_name FROM {$trmdb->users} u WHERE u.ID IN ( {$user_ids} )" ) );

	for ( $i = 0, $count = count( $posts ); $i < $count; ++$i ) {
		foreach ( (array)$poster_details as $poster ) {
			if ( $poster->user_id == $posts[$i]->poster_id ) {
				$posts[$i]->poster_email    = $poster->user_email;
				$posts[$i]->poster_login    = $poster->user_nicename;
				$posts[$i]->poster_nicename = $poster->user_login;
				$posts[$i]->poster_name     = $poster->display_name;
			}
		}
	}

	// Fetch fullname for each poster.
	if ( trs_is_active( 'xprofile' ) ) {
		$poster_names = $trmdb->get_results( $trmdb->prepare( "SELECT pd.user_id, pd.value FROM {$trs->profile->table_name_data} pd WHERE pd.user_id IN ( {$user_ids} )" ) );
		for ( $i = 0, $count = count( $posts ); $i < $count; ++$i ) {
			foreach ( (array)$poster_names as $name ) {
				if ( isset( $topics[$i] ) && $name->user_id == $topics[$i]->user_id )
				$posts[$i]->poster_name = $poster->value;
			}
		}
	}

	return apply_filters( 'trs_forums_get_post_extras', $posts );
}

function trs_forums_get_forum_topicpost_count( $forum_id ) {
	global $trmdb, $bbdb;

	do_action( 'btrsress_init' );

	// Need to find a bbPress function that does this
	return $trmdb->get_results( $trmdb->prepare( "SELECT topics, posts from {$bbdb->forums} WHERE forum_id = %d", $forum_id ) );
}

function trs_forums_filter_caps( $allcaps ) {
	global $trs, $trm_roles, $bb_table_prefix;

	if ( !isset( $trs->loggedin_user->id ) )
		return $allcaps;

	$bb_cap = get_user_meta( $trs->loggedin_user->id, $bb_table_prefix . 'capabilities', true );

	if ( empty( $bb_cap ) )
		return $allcaps;

	$bb_cap = array_keys($bb_cap);
	$bb_cap = $trm_roles->get_role( $bb_cap[0] );
	$bb_cap = $bb_cap->capabilities;

	return array_merge( (array) $allcaps, (array) $bb_cap );
}
add_filter( 'user_has_cap', 'trs_forums_filter_caps' );

/**
 * Returns the parent forum id for the bbPress abstraction layer
 *
 * @package trendr
 * @since 1.5
 *
 * @return int
 */
function trs_forums_parent_forum_id() {
	return apply_filters( 'trs_forums_parent_forum_id', TRS_FORUMS_PARENT_FORUM_ID );
}

/**
 * Should sticky topics be broken out of regular topic order on forum directories?
 *
 * Defaults to false. Define TRS_FORUMS_ENABLE_GLOBAL_DIRECTORY_STICKIES, or filter
 * trs_forums_enable_global_directory_stickies, to change this behavior.
 *
 * @package trendr
 * @since 1.5
 *
 * @return bool True if stickies should be displayed at the top of the global directory, false
 *    otherwise.
 */
function trs_forums_enable_global_directory_stickies() {
	return apply_filters( 'trs_forums_enable_global_directory_stickies', defined( 'TRS_FORUMS_ENABLE_GLOBAL_DIRECTORY_STICKIES' ) && TRS_FORUMS_ENABLE_GLOBAL_DIRECTORY_STICKIES );
}


/********************************************************************************
 * Caching
 *
 * Caching functions handle the clearing of cached objects and pages on specific
 * actions throughout trendr.
 */

// List actions to clear super cached pages on, if super cache is installed
add_action( 'trs_forums_new_forum', 'trs_core_clear_cache' );
add_action( 'trs_forums_new_topic', 'trs_core_clear_cache' );
add_action( 'trs_forums_new_post',  'trs_core_clear_cache' );


/** Embeds *******************************************************************/

/**
 * Grabs the topic post ID and attempts to retrieve the oEmbed cache (if it exists)
 * during the forum topic loop.  If no cache and link is embeddable, cache it.
 *
 * @see TRS_Embed
 * @see trs_embed_forum_cache()
 * @see trs_embed_forum_save_cache()
 * @package trendr_Forums
 * @since 1.5
 */
function trs_forums_embed() {
	add_filter( 'embed_post_id',         'trs_get_the_topic_post_id'         );
	add_filter( 'trs_embed_get_cache',    'trs_embed_forum_cache',      10, 3 );
	add_action( 'trs_embed_update_cache', 'trs_embed_forum_save_cache', 10, 3 );
}
add_action( 'topic_loop_start', 'trs_forums_embed' );

/**
 * Wrapper function for {@link bb_get_postmeta()}.
 * Used during {@link TRS_Embed::parse_oembed()} via {@link trs_forums_embed()}.
 *
 * @package trendr_Forums
 * @since 1.5
 */
function trs_embed_forum_cache( $cache, $id, $cachekey ) {
	return bb_get_postmeta( $id, $cachekey );
}

/**
 * Wrapper function for {@link bb_update_postmeta()}.
 * Used during {@link TRS_Embed::parse_oembed()} via {@link trs_forums_embed()}.
 *
 * @package trendr_Forums
 * @since 1.5
 */
function trs_embed_forum_save_cache( $cache, $cachekey, $id ) {
	bb_update_postmeta( $id, $cachekey, $cache );
}
?>