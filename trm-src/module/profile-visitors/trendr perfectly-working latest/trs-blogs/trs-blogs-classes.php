<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

Class TRS_Blogs_Blog {
	var $id;
	var $user_id;
	var $blog_id;

	function trs_blogs_blog( $id = null ) {
		$this->__construct( $id );
	}

	function __construct( $id = null ) {
		global $trs, $trmdb;

		$user_id = trs_displayed_user_id();

		if ( $id ) {
			$this->id = $id;
			$this->populate();
		}
	}

	function populate() {
		global $trmdb, $trs;

		$blog = $trmdb->get_row( $trmdb->prepare( "SELECT * FROM {$trs->blogs->table_name} WHERE id = %d", $this->id ) );

		$this->user_id = $blog->user_id;
		$this->blog_id = $blog->blog_id;
	}

	function save() {
		global $trmdb, $trs;

		$this->user_id = apply_filters( 'trs_blogs_blog_user_id_before_save', $this->user_id, $this->id );
		$this->blog_id = apply_filters( 'trs_blogs_blog_id_before_save', $this->blog_id, $this->id );

		do_action_ref_array( 'trs_blogs_blog_before_save', array( &$this ) );

		// Don't try and save if there is no user ID or blog ID set.
		if ( !$this->user_id || !$this->blog_id )
			return false;

		// Don't save if this blog has already been recorded for the user.
		if ( !$this->id && $this->exists() )
			return false;

		if ( $this->id ) {
			// Update
			$sql = $trmdb->prepare( "UPDATE {$trs->blogs->table_name} SET user_id = %d, blog_id = %d WHERE id = %d", $this->user_id, $this->blog_id, $this->id );
		} else {
			// Save
			$sql = $trmdb->prepare( "INSERT INTO {$trs->blogs->table_name} ( user_id, blog_id ) VALUES ( %d, %d )", $this->user_id, $this->blog_id );
		}

		if ( !$trmdb->query($sql) )
			return false;

		do_action_ref_array( 'trs_blogs_blog_after_save', array( &$this ) );

		if ( $this->id )
			return $this->id;
		else
			return $trmdb->insert_id;
	}

	function exists() {
		global $trs, $trmdb;

		return $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(id) FROM {$trs->blogs->table_name} WHERE user_id = %d AND blog_id = %d", $this->user_id, $this->blog_id ) );
	}

	/* Static Functions */

	function get( $type, $limit = false, $page = false, $user_id = 0, $search_terms = false ) {
		global $trs, $trmdb;

		if ( !is_user_logged_in() || ( !is_super_admin() && ( $user_id != $trs->loggedin_user->id ) ) )
			$hidden_sql = "AND wb.public = 1";
		else
			$hidden_sql = '';

		$pag_sql = ( $limit && $page ) ? $trmdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) ) : '';

		$user_sql = !empty( $user_id ) ? $trmdb->prepare( " AND b.user_id = %d", $user_id ) : '';

		switch ( $type ) {
			case 'active': default:
				$order_sql = "ORDER BY bm.meta_value DESC";
				break;
			case 'alphabetical':
				$order_sql = "ORDER BY bm2.meta_value ASC";
				break;
			case 'newest':
				$order_sql = "ORDER BY wb.registered DESC";
				break;
			case 'random':
				$order_sql = "ORDER BY RAND()";
				break;
		}

		if ( !empty( $search_terms ) ) {
			$filter = like_escape( $trmdb->escape( $search_terms ) );
			$paged_blogs = $trmdb->get_results( "SELECT b.blog_id, b.user_id as admin_user_id, u.user_email as admin_user_email, wb.domain, wb.path, bm.meta_value as last_activity, bm2.meta_value as name FROM {$trs->blogs->table_name} b, {$trs->blogs->table_name_blogmeta} bm, {$trs->blogs->table_name_blogmeta} bm2, {$trmdb->base_prefix}blogs wb, {$trmdb->users} u WHERE b.blog_id = wb.blog_id AND b.user_id = u.ID AND b.blog_id = bm.blog_id AND b.blog_id = bm2.blog_id AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql} AND bm.meta_key = 'last_activity' AND bm2.meta_key = 'name' AND bm2.meta_value LIKE '%%$filter%%' {$user_sql} GROUP BY b.blog_id {$order_sql} {$pag_sql}" );
			$total_blogs = $trmdb->get_var( "SELECT COUNT(DISTINCT b.blog_id) FROM {$trs->blogs->table_name} b, {$trmdb->base_prefix}blogs wb, {$trs->blogs->table_name_blogmeta} bm, {$trs->blogs->table_name_blogmeta} bm2 WHERE b.blog_id = wb.blog_id AND bm.blog_id = b.blog_id AND bm2.blog_id = b.blog_id AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql} AND bm.meta_key = 'name' AND bm2.meta_key = 'description' AND ( bm.meta_value LIKE '%%$filter%%' || bm2.meta_value LIKE '%%$filter%%' ) {$user_sql}" );
		} else {
			$paged_blogs = $trmdb->get_results( $trmdb->prepare( "SELECT b.blog_id, b.user_id as admin_user_id, u.user_email as admin_user_email, wb.domain, wb.path, bm.meta_value as last_activity, bm2.meta_value as name FROM {$trs->blogs->table_name} b, {$trs->blogs->table_name_blogmeta} bm, {$trs->blogs->table_name_blogmeta} bm2, {$trmdb->base_prefix}blogs wb, {$trmdb->users} u WHERE b.blog_id = wb.blog_id AND b.user_id = u.ID AND b.blog_id = bm.blog_id AND b.blog_id = bm2.blog_id {$user_sql} AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql} AND bm.meta_key = 'last_activity' AND bm2.meta_key = 'name' GROUP BY b.blog_id {$order_sql} {$pag_sql}" ) );
			$total_blogs = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(DISTINCT b.blog_id) FROM {$trs->blogs->table_name} b, {$trmdb->base_prefix}blogs wb WHERE b.blog_id = wb.blog_id {$user_sql} AND wb.archived = '0' AND wb.spam = 0 AND wb.mature = 0 AND wb.deleted = 0 {$hidden_sql}" ) );
		}

		$blog_ids = array();
		foreach ( (array)$paged_blogs as $blog ) {
			$blog_ids[] = $blog->blog_id;
		}

		$blog_ids = $trmdb->escape( join( ',', (array)$blog_ids ) );
		$paged_blogs = TRS_Blogs_Blog::get_blog_extras( $paged_blogs, $blog_ids, $type );

		return array( 'blogs' => $paged_blogs, 'total' => $total_blogs );
	}

	function delete_blog_for_all( $blog_id ) {
		global $trmdb, $trs;

		trs_blogs_delete_blogmeta( $blog_id );
		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->blogs->table_name} WHERE blog_id = %d", $blog_id ) );
	}

	function delete_blog_for_user( $blog_id, $user_id = null ) {
		global $trmdb, $trs;

		if ( !$user_id )
			$user_id = $trs->loggedin_user->id;

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->blogs->table_name} WHERE user_id = %d AND blog_id = %d", $user_id, $blog_id ) );
	}

	function delete_blogs_for_user( $user_id = null ) {
		global $trmdb, $trs;

		if ( !$user_id )
			$user_id = $trs->loggedin_user->id;

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->blogs->table_name} WHERE user_id = %d", $user_id ) );
	}

	function get_blogs_for_user( $user_id = 0, $show_hidden = false ) {
		global $trs, $trmdb;

		if ( !$user_id )
			$user_id = $trs->displayed_user->id;

		// Show logged in users their hidden blogs.
		if ( !trs_is_my_profile() && !$show_hidden )
			$blogs = $trmdb->get_results( $trmdb->prepare( "SELECT DISTINCT b.blog_id, b.id, bm1.meta_value as name, wb.domain, wb.path FROM {$trs->blogs->table_name} b, {$trmdb->base_prefix}blogs wb, {$trs->blogs->table_name_blogmeta} bm1 WHERE b.blog_id = wb.blog_id AND b.blog_id = bm1.blog_id AND bm1.meta_key = 'name' AND wb.public = 1 AND wb.deleted = 0 AND wb.spam = 0 AND wb.mature = 0 AND wb.archived = '0' AND b.user_id = %d ORDER BY b.blog_id", $user_id ) );
		else
			$blogs = $trmdb->get_results( $trmdb->prepare( "SELECT DISTINCT b.blog_id, b.id, bm1.meta_value as name, wb.domain, wb.path FROM {$trs->blogs->table_name} b, {$trmdb->base_prefix}blogs wb, {$trs->blogs->table_name_blogmeta} bm1 WHERE b.blog_id = wb.blog_id AND b.blog_id = bm1.blog_id AND bm1.meta_key = 'name' AND wb.deleted = 0 AND wb.spam = 0 AND wb.mature = 0 AND wb.archived = '0' AND b.user_id = %d ORDER BY b.blog_id", $user_id ) );

		$total_blog_count = TRS_Blogs_Blog::total_blog_count_for_user( $user_id );

		$user_blogs = array();
		foreach ( (array)$blogs as $blog ) {
			$user_blogs[$blog->blog_id] = new stdClass;
			$user_blogs[$blog->blog_id]->id = $blog->id;
			$user_blogs[$blog->blog_id]->blog_id = $blog->blog_id;
			$user_blogs[$blog->blog_id]->siteurl = ( is_ssl() ) ? 'https://' . $blog->domain . $blog->path : 'http://' . $blog->domain . $blog->path;
			$user_blogs[$blog->blog_id]->name = $blog->name;
		}

		return array( 'blogs' => $user_blogs, 'count' => $total_blog_count );
	}

	function get_blog_ids_for_user( $user_id = 0 ) {
		global $trs, $trmdb;

		if ( !$user_id )
			$user_id = $trs->displayed_user->id;

		return $trmdb->get_col( $trmdb->prepare( "SELECT blog_id FROM {$trs->blogs->table_name} WHERE user_id = %d", $user_id ) );
	}

	function is_recorded( $blog_id ) {
		global $trs, $trmdb;

		return $trmdb->get_var( $trmdb->prepare( "SELECT id FROM {$trs->blogs->table_name} WHERE blog_id = %d", $blog_id ) );
	}

	function total_blog_count_for_user( $user_id = null ) {
		global $trs, $trmdb;

		if ( !$user_id )
			$user_id = $trs->displayed_user->id;

		// If the user is logged in return the blog count including their hidden blogs.
		if ( ( is_user_logged_in() && $user_id == $trs->loggedin_user->id ) || is_super_admin() )
			return $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(DISTINCT b.blog_id) FROM {$trs->blogs->table_name} b LEFT JOIN {$trmdb->base_prefix}blogs wb ON b.blog_id = wb.blog_id WHERE wb.deleted = 0 AND wb.spam = 0 AND wb.mature = 0 AND wb.archived = '0' AND user_id = %d", $user_id) );
		else
			return $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(DISTINCT b.blog_id) FROM {$trs->blogs->table_name} b LEFT JOIN {$trmdb->base_prefix}blogs wb ON b.blog_id = wb.blog_id WHERE wb.public = 1 AND wb.deleted = 0 AND wb.spam = 0 AND wb.mature = 0 AND wb.archived = '0' AND user_id = %d", $user_id) );
	}

	function search_blogs( $filter, $limit = null, $page = null ) {
		global $trmdb, $trs;

		$filter = like_escape( $trmdb->escape( $filter ) );

		if ( !is_super_admin() )
			$hidden_sql = "AND wb.public = 1";

		if ( $limit && $page )
			$pag_sql = $trmdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$paged_blogs = $trmdb->get_results( "SELECT DISTINCT bm.blog_id FROM {$trs->blogs->table_name_blogmeta} bm LEFT JOIN {$trmdb->base_prefix}blogs wb ON bm.blog_id = wb.blog_id WHERE ( ( bm.meta_key = 'name' OR bm.meta_key = 'description' ) AND bm.meta_value LIKE '%%$filter%%' ) {$hidden_sql} AND wb.mature = 0 AND wb.spam = 0 AND wb.archived = '0' AND wb.deleted = 0 ORDER BY meta_value ASC{$pag_sql}" );
		$total_blogs = $trmdb->get_var( "SELECT COUNT(DISTINCT bm.blog_id) FROM {$trs->blogs->table_name_blogmeta} bm LEFT JOIN {$trmdb->base_prefix}blogs wb ON bm.blog_id = wb.blog_id WHERE ( ( bm.meta_key = 'name' OR bm.meta_key = 'description' ) AND bm.meta_value LIKE '%%$filter%%' ) {$hidden_sql} AND wb.mature = 0 AND wb.spam = 0 AND wb.archived = '0' AND wb.deleted = 0 ORDER BY meta_value ASC" );

		return array( 'blogs' => $paged_blogs, 'total' => $total_blogs );
	}

	function get_all( $limit = null, $page = null ) {
		global $trs, $trmdb;

		$hidden_sql = !is_super_admin() ? "AND wb.public = 1" : '';
		$pag_sql = ( $limit && $page ) ? $trmdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) ) : '';

		$paged_blogs = $trmdb->get_results( $trmdb->prepare( "SELECT DISTINCT b.blog_id FROM {$trs->blogs->table_name} b LEFT JOIN {$trmdb->base_prefix}blogs wb ON b.blog_id = wb.blog_id WHERE wb.mature = 0 AND wb.spam = 0 AND wb.archived = '0' AND wb.deleted = 0 {$hidden_sql} {$pag_sql}" ) );
		$total_blogs = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(DISTINCT b.blog_id) FROM {$trs->blogs->table_name} b LEFT JOIN {$trmdb->base_prefix}blogs wb ON b.blog_id = wb.blog_id WHERE wb.mature = 0 AND wb.spam = 0 AND wb.archived = '0' AND wb.deleted = 0 {$hidden_sql}" ) );

		return array( 'blogs' => $paged_blogs, 'total' => $total_blogs );
	}

	function get_by_letter( $letter, $limit = null, $page = null ) {
		global $trs, $trmdb;

		$letter = like_escape( $trmdb->escape( $letter ) );

		if ( !is_super_admin() )
			$hidden_sql = "AND wb.public = 1";

		if ( $limit && $page )
			$pag_sql = $trmdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$paged_blogs = $trmdb->get_results( $trmdb->prepare( "SELECT DISTINCT bm.blog_id FROM {$trs->blogs->table_name_blogmeta} bm LEFT JOIN {$trmdb->base_prefix}blogs wb ON bm.blog_id = wb.blog_id WHERE bm.meta_key = 'name' AND bm.meta_value LIKE '$letter%%' {$hidden_sql} AND wb.mature = 0 AND wb.spam = 0 AND wb.archived = '0' AND wb.deleted = 0 ORDER BY bm.meta_value ASC{$pag_sql}" ) );
		$total_blogs = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(DISTINCT bm.blog_id) FROM {$trs->blogs->table_name_blogmeta} bm LEFT JOIN {$trmdb->base_prefix}blogs wb ON bm.blog_id = wb.blog_id WHERE bm.meta_key = 'name' AND bm.meta_value LIKE '$letter%%' {$hidden_sql} AND wb.mature = 0 AND wb.spam = 0 AND wb.archived = '0' AND wb.deleted = 0 ORDER BY bm.meta_value ASC" ) );

		return array( 'blogs' => $paged_blogs, 'total' => $total_blogs );
	}

	function get_blog_extras( &$paged_blogs, &$blog_ids, $type = false ) {
		global $trs, $trmdb;

		if ( empty( $blog_ids ) )
			return $paged_blogs;

		for ( $i = 0, $count = count( $paged_blogs ); $i < $count; ++$i ) {
			$blog_prefix = $trmdb->get_blog_prefix( $paged_blogs[$i]->blog_id );
			$paged_blogs[$i]->latest_post = $trmdb->get_row( "SELECT post_title, guid FROM {$blog_prefix}posts WHERE post_status = 'publish' AND post_type = 'post' AND id != 1 ORDER BY id DESC LIMIT 1" );
		}

		/* Fetch the blog description for each blog (as it may be empty we can't fetch it in the main query). */
		$blog_descs = $trmdb->get_results( $trmdb->prepare( "SELECT blog_id, meta_value as description FROM {$trs->blogs->table_name_blogmeta} WHERE meta_key = 'description' AND blog_id IN ( {$blog_ids} )" ) );

		for ( $i = 0, $count = count( $paged_blogs ); $i < $count; ++$i ) {
			foreach ( (array)$blog_descs as $desc ) {
				if ( $desc->blog_id == $paged_blogs[$i]->blog_id )
					$paged_blogs[$i]->description = $desc->description;
			}
		}

		return $paged_blogs;
	}

	function is_hidden( $blog_id ) {
		global $trmdb;

		if ( !(int)$trmdb->get_var( $trmdb->prepare( "SELECT DISTINCT public FROM {$trmdb->base_prefix}blogs WHERE blog_id = %d", $blog_id ) ) )
			return true;

		return false;
	}
}
?>