<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Checks $trs pages global and looks for directory page
 *
 * @since 1.5
 *
 * @global object $trs Global trendr settings object
 * @return bool True if set, False if empty
 */
function trs_blogs_has_directory() {
	global $trs;

	return (bool) !empty( $trs->pages->blogs->id );
}

function trs_blogs_get_blogs( $args = '' ) {
	global $trs;

	$defaults = array(
		'type'         => 'active', // active, alphabetical, newest, or random
		'user_id'      => false,    // Pass a user_id to limit to only blogs that this user has privilages higher than subscriber on
		'search_terms' => false,    // Limit to blogs that match these search terms
		'per_page'     => 20,       // The number of results to return per page
		'page'         => 1,        // The page to return if limiting per page
	);

	$params = trm_parse_args( $args, $defaults );
	extract( $params, EXTR_SKIP );

	return apply_filters( 'trs_blogs_get_blogs', TRS_Blogs_Blog::get( $type, $per_page, $page, $user_id, $search_terms ), $params );
}

/**
 * Populates the TRS blogs table with existing blogs.
 *
 * @package trendr Blogs
 *
 * @global object $trs trendr global settings
 * @global object $trmdb trendr database object
 * @uses get_users()
 * @uses trs_blogs_record_blog()
 */
function trs_blogs_record_existing_blogs() {
	global $trs, $trmdb;

	// Truncate user blogs table and re-record.
	$trmdb->query( "TRUNCATE TABLE {$trs->blogs->table_name}" );

	if ( is_multisite() )
		$blog_ids = $trmdb->get_col( $trmdb->prepare( "SELECT blog_id FROM {$trmdb->base_prefix}blogs WHERE mature = 0 AND spam = 0 AND deleted = 0" ) );
	else
		$blog_ids = 1;

	if ( $blog_ids ) {
		foreach( (array)$blog_ids as $blog_id ) {
			$users 		= get_users( array( 'blog_id' => $blog_id ) );
			$subscribers 	= get_users( array( 'blog_id' => $blog_id, 'role' => 'subscriber' ) );

			if ( !empty( $users ) ) {
				foreach ( (array)$users as $user ) {
					// Don't record blogs for subscribers
					if ( !in_array( $user, $subscribers ) )
						trs_blogs_record_blog( $blog_id, $user->ID, true );
				}
			}
		}
	}
}

/**
 * Makes trendr aware of a new site so that it can track its activity.
 *
 * @global object $trs trendr global settings
 * @param int $blog_id
 * @param int $user_id
 * @param $bool $no_activity ; optional.
 * @since 1.0
 * @uses TRS_Blogs_Blog
 */
function trs_blogs_record_blog( $blog_id, $user_id, $no_activity = false ) {
	global $trs;

	if ( !$user_id )
		$user_id = $trs->loggedin_user->id;

	$name = get_blog_option( $blog_id, 'blogname' );
	$description = get_blog_option( $blog_id, 'blogdescription' );

	if ( empty( $name ) )
		return false;

	$recorded_blog          = new TRS_Blogs_Blog;
	$recorded_blog->user_id = $user_id;
	$recorded_blog->blog_id = $blog_id;

	$recorded_blog_id = $recorded_blog->save();

	$is_recorded = !empty( $recorded_blog_id ) ? true : false;

	trs_blogs_update_blogmeta( $recorded_blog->blog_id, 'name', $name );
	trs_blogs_update_blogmeta( $recorded_blog->blog_id, 'description', $description );
	trs_blogs_update_blogmeta( $recorded_blog->blog_id, 'last_activity', trs_core_current_time() );

	$is_private = !empty( $_POST['blog_public'] ) && (int)$_POST['blog_public'] ? false : true;
	$is_private = !apply_filters( 'trs_is_new_blog_public', !$is_private );

	// Only record this activity if the blog is public
	if ( !$is_private && !$no_activity ) {
		// Record this in activity streams
		trs_blogs_record_activity( array(
			'user_id'      => $recorded_blog->user_id,
			'action'       => apply_filters( 'trs_blogs_activity_created_blog_action', sprintf( __( '%s created the site %s', 'trendr'), trs_core_get_userlink( $recorded_blog->user_id ), '<a href="' . get_site_url( $recorded_blog->blog_id ) . '">' . esc_attr( $name ) . '</a>' ), $recorded_blog, $name, $description ),
			'primary_link' => apply_filters( 'trs_blogs_activity_created_blog_primary_link', get_site_url( $recorded_blog->blog_id ), $recorded_blog->blog_id ),
			'type'         => 'new_blog',
			'item_id'      => $recorded_blog->blog_id
		) );
	}

	do_action_ref_array( 'trs_blogs_new_blog', array( &$recorded_blog, $is_private, $is_recorded ) );
}
add_action( 'trmmu_new_blog', 'trs_blogs_record_blog', 10, 2 );

/**
 * Updates blogname in trendr blogmeta table
 *
 * @global object $trmdb DB Layer
 * @param string $oldvalue Value before save (not used)
 * @param string $newvalue Value to change meta to
 */
function trs_blogs_update_option_blogname( $oldvalue, $newvalue ) {
	global $trmdb;

	trs_blogs_update_blogmeta( $trmdb->blogid, 'name', $newvalue );
}
add_action( 'update_option_blogname', 'trs_blogs_update_option_blogname', 10, 2 );

/**
 * Updates blogdescription in trendr blogmeta table
 *
 * @global object $trmdb DB Layer
 * @param string $oldvalue Value before save (not used)
 * @param string $newvalue Value to change meta to
 */
function trs_blogs_update_option_blogdescription( $oldvalue, $newvalue ) {
	global $trmdb;

	trs_blogs_update_blogmeta( $trmdb->blogid, 'description', $newvalue );
}
add_action( 'update_option_blogdescription', 'trs_blogs_update_option_blogdescription', 10, 2 );

function trs_blogs_record_post( $post_id, $post, $user_id = 0 ) {
	global $trs, $trmdb;

	$post_id = (int)$post_id;
	$blog_id = (int)$trmdb->blogid;

	if ( !$user_id )
		$user_id = (int)$post->post_author;

	// Stop infinite loops with trendr MU Sitewide Tags.
	// That plugin changed the way its settings were stored at some point. Thus the dual check.
	if ( !empty( $trs->site_options['sitewide_tags_blog'] ) ) {
		$st_options = maybe_unserialize( $trs->site_options['sitewide_tags_blog'] );
		$tags_blog_id = isset( $st_options['tags_blog_id'] ) ? $st_options['tags_blog_id'] : 0;
	} else {
		$tags_blog_id = isset( $trs->site_options['tags_blog_id'] ) ? $trs->site_options['tags_blog_id'] : 0;
	}

	if ( (int)$blog_id == $tags_blog_id && apply_filters( 'trs_blogs_block_sitewide_tags_activity', true ) )
		return false;

	// Don't record this if it's not a post
	if ( !in_array( $post->post_type, apply_filters( 'trs_blogs_record_post_post_types', array( 'post' ) ) ) )
		return false;

	$is_blog_public = apply_filters( 'trs_is_blog_public', (int)get_blog_option( $blog_id, 'blog_public' ) );

	if ( 'publish' == $post->post_status && empty( $post->post_password ) ) {
		if ( $is_blog_public || !is_multisite() ) {
			// Record this in activity streams
			$post_permalink   = get_permalink( $post_id );

			if ( is_multisite() )
				$activity_action  = sprintf( __( '%1$s wrote a new post, %2$s, on the site %3$s', 'trendr' ), trs_core_get_userlink( (int)$post->post_author ), '<a href="' . $post_permalink . '">' . $post->post_title . '</a>', '<a href="' . get_blog_option( $blog_id, 'home' ) . '">' . get_blog_option( $blog_id, 'blogname' ) . '</a>' );
			else
				$activity_action  = sprintf( __( '%1$s wrote a new post, %2$s', 'trendr' ), trs_core_get_userlink( (int)$post->post_author ), '<a href="' . $post_permalink . '">' . $post->post_title . '</a>' );

			$activity_content = $post->post_content;

			trs_blogs_record_activity( array(
				'user_id'           => (int)$post->post_author,
				'action'            => apply_filters( 'trs_blogs_activity_new_post_action',       $activity_action,  $post, $post_permalink ),
				'content'           => apply_filters( 'trs_blogs_activity_new_post_content',      $activity_content, $post, $post_permalink ),
				'primary_link'      => apply_filters( 'trs_blogs_activity_new_post_primary_link', $post_permalink,   $post_id               ),
				'type'              => 'new_blog_post',
				'item_id'           => $blog_id,
				'secondary_item_id' => $post_id,
				'recorded_time'     => $post->post_modified_gmt
			));
		}

		// Update the blogs last activity
		trs_blogs_update_blogmeta( $blog_id, 'last_activity', trs_core_current_time() );
	} else {
		trs_blogs_remove_post( $post_id, $blog_id, $user_id );
	}

	do_action( 'trs_blogs_new_blog_post', $post_id, $post, $user_id );
}
add_action( 'save_post', 'trs_blogs_record_post', 10, 2 );

/**
 * Record blog comment activity. Checks if blog is public and post is not
 * password protected.
 *
 * @global $trs $trs
 * @param int $comment_id
 * @param bool $is_approved
 * @return mixed
 */
function trs_blogs_record_comment( $comment_id, $is_approved = true ) {
	global $trs;

	// Get the users comment
	$recorded_comment = get_comment( $comment_id );

	// Don't record activity if the comment hasn't been approved
	if ( empty( $is_approved ) )
		return false;

	// Don't record activity if no email address has been included
	if ( empty( $recorded_comment->comment_author_email ) )
		return false;

	// Get the user_id from the comment author email.
	$user    = get_user_by( 'email', $recorded_comment->comment_author_email );
	$user_id = (int)$user->ID;

	// If there's no registered user id, don't record activity
	if ( empty( $user_id ) )
		return false;

	// Get blog and post data
	$blog_id                = get_current_blog_id();
	$recorded_comment->post = get_post( $recorded_comment->comment_post_ID );

	if ( empty( $recorded_comment->post ) || is_trm_error( $recorded_comment->post ) )
		return false;

	// If this is a password protected post, don't record the comment
	if ( !empty( $recorded_comment->post->post_password ) )
		return false;

	// Don't record activity if the comment's associated post isn't a trendr Post
	if ( !in_array( $recorded_comment->post->post_type, apply_filters( 'trs_blogs_record_comment_post_types', array( 'post' ) ) ) )
		return false;

	$is_blog_public = apply_filters( 'trs_is_blog_public', (int)get_blog_option( $blog_id, 'blog_public' ) );

	// If blog is public allow activity to be posted
	if ( $is_blog_public ) {

		// Get activity related links
		$post_permalink = get_permalink( $recorded_comment->comment_post_ID );
		$comment_link   = htmlspecialchars( get_comment_link( $recorded_comment->comment_ID ) );

		// Prepare to record in activity streams
		if ( is_multisite() )
			$activity_action = sprintf( __( '%1$s commented on the post, %2$s, on the site %3$s', 'trendr' ), trs_core_get_userlink( $user_id ), '<a href="' . $post_permalink . '">' . apply_filters( 'the_title', $recorded_comment->post->post_title ) . '</a>', '<a href="' . get_blog_option( $blog_id, 'home' ) . '">' . get_blog_option( $blog_id, 'blogname' ) . '</a>' );
		else
			$activity_action = sprintf( __( '%1$s commented on the post, %2$s', 'trendr' ), trs_core_get_userlink( $user_id ), '<a href="' . $post_permalink . '">' . apply_filters( 'the_title', $recorded_comment->post->post_title ) . '</a>' );

		$activity_content	= $recorded_comment->comment_content;

		// Record in activity streams
		trs_blogs_record_activity( array(
			'user_id'           => $user_id,
			'action'            => apply_filters_ref_array( 'trs_blogs_activity_new_comment_action',       array( $activity_action,  &$recorded_comment, $comment_link ) ),
			'content'           => apply_filters_ref_array( 'trs_blogs_activity_new_comment_content',      array( $activity_content, &$recorded_comment, $comment_link ) ),
			'primary_link'      => apply_filters_ref_array( 'trs_blogs_activity_new_comment_primary_link', array( $comment_link,     &$recorded_comment                ) ),
			'type'              => 'new_blog_comment',
			'item_id'           => $blog_id,
			'secondary_item_id' => $comment_id,
			'recorded_time'     => $recorded_comment->comment_date_gmt
		) );

		// Update the blogs last active date
		trs_blogs_update_blogmeta( $blog_id, 'last_activity', trs_core_current_time() );
	}

	return $recorded_comment;
}
add_action( 'comment_post', 'trs_blogs_record_comment', 10, 2 );
add_action( 'edit_comment', 'trs_blogs_record_comment', 10    );

function trs_blogs_manage_comment( $comment_id, $comment_status ) {
	if ( 'spam' == $comment_status || 'hold' == $comment_status || 'delete' == $comment_status || 'trash' == $comment_status )
		return trs_blogs_remove_comment( $comment_id );

	return trs_blogs_record_comment( $comment_id, true );
}
add_action( 'trm_set_comment_status', 'trs_blogs_manage_comment', 10, 2 );

function trs_blogs_add_user_to_blog( $user_id, $role = false, $blog_id = 0 ) {
	global $trmdb;
	
	if ( empty( $blog_id ) ) {
		$blog_id = isset( $trmdb->blogid ) ? $trmdb->blogid : trs_get_root_blog_id();
	}

	if ( empty( $role ) ) {
		$key = $trmdb->get_blog_prefix( $blog_id ). 'capabilities';

		$roles = get_user_meta( $user_id, $key, true );

		if ( is_array( $roles ) )
			$role = array_search( 1, $roles );
		else
			return false;
	}

	if ( $role != 'subscriber' )
		trs_blogs_record_blog( $blog_id, $user_id, true );
}
add_action( 'add_user_to_blog', 'trs_blogs_add_user_to_blog', 10, 3 );
add_action( 'profile_update',   'trs_blogs_add_user_to_blog'        );
add_action( 'user_register',    'trs_blogs_add_user_to_blog'        );

function trs_blogs_remove_user_from_blog( $user_id, $blog_id = 0 ) {
	global $trmdb;

	if ( empty( $blog_id ) )
		$blog_id = $trmdb->blogid;

	trs_blogs_remove_blog_for_user( $user_id, $blog_id );
}
add_action( 'remove_user_from_blog', 'trs_blogs_remove_user_from_blog', 10, 2 );

function trs_blogs_remove_blog( $blog_id ) {
	global $trs;

	$blog_id = (int)$blog_id;
	do_action( 'trs_blogs_before_remove_blog', $blog_id );

	TRS_Blogs_Blog::delete_blog_for_all( $blog_id );

	// Delete activity stream item
	trs_blogs_delete_activity( array( 'item_id' => $blog_id, 'component' => $trs->blogs->id, 'type' => 'new_blog' ) );

	do_action( 'trs_blogs_remove_blog', $blog_id );
}
add_action( 'delete_blog', 'trs_blogs_remove_blog' );

function trs_blogs_remove_blog_for_user( $user_id, $blog_id ) {
	global $trs, $current_user;

	$blog_id = (int)$blog_id;
	$user_id = (int)$user_id;

	do_action( 'trs_blogs_before_remove_blog_for_user', $blog_id, $user_id );

	TRS_Blogs_Blog::delete_blog_for_user( $blog_id, $user_id );

	// Delete activity stream item
	trs_blogs_delete_activity( array( 'item_id' => $blog_id, 'component' => $trs->blogs->id, 'type' => 'new_blog' ) );

	do_action( 'trs_blogs_remove_blog_for_user', $blog_id, $user_id );
}
add_action( 'remove_user_from_blog', 'trs_blogs_remove_blog_for_user', 10, 2 );

function trs_blogs_remove_post( $post_id, $blog_id = 0, $user_id = 0 ) {
	global $trmdb, $trs;

	if ( empty( $trmdb->blogid ) )
		return false;

	$post_id = (int)$post_id;

	if ( !$blog_id )
		$blog_id = (int)$trmdb->blogid;

	if ( !$user_id )
		$user_id = $trs->loggedin_user->id;

	do_action( 'trs_blogs_before_remove_post', $blog_id, $post_id, $user_id );

	// Delete activity stream item
	trs_blogs_delete_activity( array( 'item_id' => $blog_id, 'secondary_item_id' => $post_id, 'component' => $trs->blogs->id, 'type' => 'new_blog_post' ) );

	do_action( 'trs_blogs_remove_post', $blog_id, $post_id, $user_id );
}
add_action( 'delete_post', 'trs_blogs_remove_post' );

function trs_blogs_remove_comment( $comment_id ) {
	global $trmdb, $trs;

	// Delete activity stream item
	trs_blogs_delete_activity( array( 'item_id' => $trmdb->blogid, 'secondary_item_id' => $comment_id, 'type' => 'new_blog_comment' ) );

	do_action( 'trs_blogs_remove_comment', $trmdb->blogid, $comment_id, $trs->loggedin_user->id );
}
add_action( 'delete_comment', 'trs_blogs_remove_comment' );

function trs_blogs_total_blogs() {
	if ( !$count = trm_cache_get( 'trs_total_blogs', 'trs' ) ) {
		$blogs = TRS_Blogs_Blog::get_all();
		$count = $blogs['total'];
		trm_cache_set( 'trs_total_blogs', $count, 'trs' );
	}
	return $count;
}

function trs_blogs_total_blogs_for_user( $user_id = 0 ) {
	global $trs;

	if ( !$user_id )
		$user_id = ( $trs->displayed_user->id ) ? $trs->displayed_user->id : $trs->loggedin_user->id;

	if ( !$count = trm_cache_get( 'trs_total_blogs_for_user_' . $user_id, 'trs' ) ) {
		$count = TRS_Blogs_Blog::total_blog_count_for_user( $user_id );
		trm_cache_set( 'trs_total_blogs_for_user_' . $user_id, $count, 'trs' );
	}

	return $count;
}

function trs_blogs_remove_data_for_blog( $blog_id ) {
	global $trs;

	do_action( 'trs_blogs_before_remove_data_for_blog', $blog_id );

	// If this is regular blog, delete all data for that blog.
	TRS_Blogs_Blog::delete_blog_for_all( $blog_id );

	// Delete activity stream item
	trs_blogs_delete_activity( array( 'item_id' => $blog_id, 'component' => $trs->blogs->id, 'type' => false ) );

	do_action( 'trs_blogs_remove_data_for_blog', $blog_id );
}
add_action( 'delete_blog', 'trs_blogs_remove_data_for_blog', 1 );

function trs_blogs_get_blogs_for_user( $user_id, $show_hidden = false ) {
	return TRS_Blogs_Blog::get_blogs_for_user( $user_id, $show_hidden );
}

function trs_blogs_get_all_blogs( $limit = null, $page = null ) {
	return TRS_Blogs_Blog::get_all( $limit, $page );
}

function trs_blogs_get_random_blogs( $limit = null, $page = null ) {
	return TRS_Blogs_Blog::get( 'random', $limit, $page );
}

function trs_blogs_is_blog_hidden( $blog_id ) {
	return TRS_Blogs_Blog::is_hidden( $blog_id );
}

/*******************************************************************************
 * Blog meta functions
 *
 * These functions are used to store specific blogmeta in one global table,
 * rather than in each blog's options table. Significantly speeds up global blog
 * queries. By default each blog's name, description and last updated time are
 * stored and synced here.
 */

function trs_blogs_delete_blogmeta( $blog_id, $meta_key = false, $meta_value = false ) {
	global $trmdb, $trs;

	if ( !is_numeric( $blog_id ) )
		return false;

	$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

	if ( is_array($meta_value) || is_object($meta_value) )
		$meta_value = serialize($meta_value);

	$meta_value = trim( $meta_value );

	if ( !$meta_key )
		$trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->blogs->table_name_blogmeta} WHERE blog_id = %d", $blog_id ) );
	else if ( $meta_value )
		$trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->blogs->table_name_blogmeta} WHERE blog_id = %d AND meta_key = %s AND meta_value = %s", $blog_id, $meta_key, $meta_value ) );
	else
		$trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->blogs->table_name_blogmeta} WHERE blog_id = %d AND meta_key = %s", $blog_id, $meta_key ) );

	trm_cache_delete( 'trs_blogs_blogmeta_' . $blog_id . '_' . $meta_key, 'trs' );

	return true;
}

function trs_blogs_get_blogmeta( $blog_id, $meta_key = '') {
	global $trmdb, $trs;

	$blog_id = (int) $blog_id;

	if ( !$blog_id )
		return false;

	if ( !empty($meta_key) ) {
		$meta_key = preg_replace('|[^a-z0-9_]|i', '', $meta_key);

		if ( !$metas = trm_cache_get( 'trs_blogs_blogmeta_' . $blog_id . '_' . $meta_key, 'trs' ) ) {
			$metas = $trmdb->get_col( $trmdb->prepare( "SELECT meta_value FROM {$trs->blogs->table_name_blogmeta} WHERE blog_id = %d AND meta_key = %s", $blog_id, $meta_key ) );
			trm_cache_set( 'trs_blogs_blogmeta_' . $blog_id . '_' . $meta_key, $metas, 'trs' );
		}
	} else {
		$metas = $trmdb->get_col( $trmdb->prepare("SELECT meta_value FROM {$trs->blogs->table_name_blogmeta} WHERE blog_id = %d", $blog_id) );
	}

	if ( empty($metas) ) {
		if ( empty($meta_key) )
			return array();
		else
			return '';
	}

	$metas = array_map('maybe_unserialize', (array)$metas);

	if ( 1 == count($metas) )
		return $metas[0];
	else
		return $metas;
}

function trs_blogs_update_blogmeta( $blog_id, $meta_key, $meta_value ) {
	global $trmdb, $trs;

	if ( !is_numeric( $blog_id ) )
		return false;

	$meta_key = preg_replace( '|[^a-z0-9_]|i', '', $meta_key );

	if ( is_string($meta_value) )
		$meta_value = stripslashes($trmdb->escape($meta_value));

	$meta_value = maybe_serialize($meta_value);

	if (empty( $meta_value ) )
		return trs_blogs_delete_blogmeta( $blog_id, $meta_key );

	$cur = $trmdb->get_row( $trmdb->prepare( "SELECT * FROM {$trs->blogs->table_name_blogmeta} WHERE blog_id = %d AND meta_key = %s", $blog_id, $meta_key ) );

	if ( !$cur )
		$trmdb->query( $trmdb->prepare( "INSERT INTO {$trs->blogs->table_name_blogmeta} ( blog_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", $blog_id, $meta_key, $meta_value ) );
	else if ( $cur->meta_value != $meta_value )
		$trmdb->query( $trmdb->prepare( "UPDATE {$trs->blogs->table_name_blogmeta} SET meta_value = %s WHERE blog_id = %d AND meta_key = %s", $meta_value, $blog_id, $meta_key ) );
	else
		return false;

	trm_cache_set( 'trs_blogs_blogmeta_' . $blog_id . '_' . $meta_key, $meta_value, 'trs' );

	return true;
}

function trs_blogs_remove_data( $user_id ) {
	if ( !is_multisite() )
		return false;

	do_action( 'trs_blogs_before_remove_data', $user_id );

	// If this is regular blog, delete all data for that blog.
	TRS_Blogs_Blog::delete_blogs_for_user( $user_id );

	do_action( 'trs_blogs_remove_data', $user_id );
}
add_action( 'trmmu_delete_user',  'trs_blogs_remove_data' );
add_action( 'delete_user',       'trs_blogs_remove_data' );
add_action( 'trs_make_spam_user', 'trs_blogs_remove_data' );
?>