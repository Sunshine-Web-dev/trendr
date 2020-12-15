<?php

/**
 * trendr Activity Classes
 *
 * @package trendr
 * @sutrsackage ActivityClasses
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

Class TRS_Activity_Activity {
	var $id;
	var $item_id;
	var $secondary_item_id;
	var $user_id;
	var $primary_link;
	var $component;
	var $type;
	var $action;
	var $content;
	var $date_recorded;
	var $hide_sitewide = false;
	var $mptt_left;
	var $mptt_right;

	function trs_activity_activity( $id = false ) {
		$this->__construct( $id );
	}

	function __construct( $id = false ) {
		global $trs;

		if ( !empty( $id ) ) {
			$this->id = $id;
			$this->populate();
		}
	}

	function populate() {
		global $trmdb, $trs;

		if ( $row = $trmdb->get_row( $trmdb->prepare( "SELECT * FROM {$trs->activity->table_name} WHERE id = %d", $this->id ) ) ) {
			$this->id                = $row->id;
			$this->item_id           = $row->item_id;
			$this->secondary_item_id = $row->secondary_item_id;
			$this->user_id           = $row->user_id;
			$this->primary_link      = $row->primary_link;
			$this->component         = $row->component;
			$this->type              = $row->type;
			$this->action            = $row->action;
			$this->content           = $row->content;
			$this->date_recorded     = $row->date_recorded;
			$this->hide_sitewide     = $row->hide_sitewide;
			$this->mptt_left         = $row->mptt_left;
			$this->mptt_right        = $row->mptt_right;
		}
	}

	function save() {
		global $trmdb, $trs, $current_user;

		$this->id                = apply_filters_ref_array( 'trs_activity_id_before_save',                array( $this->id,                &$this ) );
		$this->item_id           = apply_filters_ref_array( 'trs_activity_item_id_before_save',           array( $this->item_id,           &$this ) );
		$this->secondary_item_id = apply_filters_ref_array( 'trs_activity_secondary_item_id_before_save', array( $this->secondary_item_id, &$this ) );
		$this->user_id           = apply_filters_ref_array( 'trs_activity_user_id_before_save',           array( $this->user_id,           &$this ) );
		$this->primary_link      = apply_filters_ref_array( 'trs_activity_primary_link_before_save',      array( $this->primary_link,      &$this ) );
		$this->component         = apply_filters_ref_array( 'trs_activity_component_before_save',         array( $this->component,         &$this ) );
		$this->type              = apply_filters_ref_array( 'trs_activity_type_before_save',              array( $this->type,              &$this ) );
		$this->action            = apply_filters_ref_array( 'trs_activity_action_before_save',            array( $this->action,            &$this ) );
		$this->content           = apply_filters_ref_array( 'trs_activity_content_before_save',           array( $this->content,           &$this ) );
		$this->date_recorded     = apply_filters_ref_array( 'trs_activity_date_recorded_before_save',     array( $this->date_recorded,     &$this ) );
		$this->hide_sitewide     = apply_filters_ref_array( 'trs_activity_hide_sitewide_before_save',     array( $this->hide_sitewide,     &$this ) );
		$this->mptt_left         = apply_filters_ref_array( 'trs_activity_mptt_left_before_save',         array( $this->mptt_left,         &$this ) );
		$this->mptt_right        = apply_filters_ref_array( 'trs_activity_mptt_right_before_save',        array( $this->mptt_right,        &$this ) );

		// Use this, not the filters above
		do_action_ref_array( 'trs_activity_before_save', array( &$this ) );

	 if($this->content == '' && $this->user_id == -1){
		 // trs_core_add_message( __( 'That email address is invalid. Check the formatting and try again.', 'trendr' ), 'error' );
return false;
	 }

		if ( !$this->component || !$this->type )
			return false;

		if ( !$this->primary_link )
			$this->primary_link = $trs->loggedin_user->domain;

		// If we have an existing ID, update the activity item, otherwise insert it.
		if ( $this->id )
			$q = $trmdb->prepare( "UPDATE {$trs->activity->table_name} SET user_id = %d, component = %s, type = %s, action = %s, content = %s, primary_link = %s, date_recorded = %s, item_id = %s, secondary_item_id = %s, hide_sitewide = %d WHERE id = %d", $this->user_id, $this->component, $this->type, $this->action, $this->content, $this->primary_link, $this->date_recorded, $this->item_id, $this->secondary_item_id, $this->hide_sitewide, $this->id );
		else
			$q = $trmdb->prepare( "INSERT INTO {$trs->activity->table_name} ( user_id, component, type, action, content, primary_link, date_recorded, item_id, secondary_item_id, hide_sitewide ) VALUES ( %d, %s, %s, %s, %s, %s, %s, %s, %s, %d )", $this->user_id, $this->component, $this->type, $this->action, $this->content, $this->primary_link, $this->date_recorded, $this->item_id, $this->secondary_item_id, $this->hide_sitewide );

		if ( !$trmdb->query( $q ) )
			return false;

		if ( empty( $this->id ) )
			$this->id = $trmdb->insert_id;

		do_action_ref_array( 'trs_activity_after_save', array( &$this ) );

		return true;
	}

	// Static Functions

	function get( $max = false, $page = 1, $per_page = 25, $sort = 'DESC', $search_terms = false, $filter = false, $display_comments = false, $show_hidden = false, $exclude = false, $in = false ) {
		global $trmdb, $trs;

// echo "<pre>";
		// debug_print_backtrace();
// var_dump($filter);
		// Select conditions
		$select_sql = "SELECT a.*, u.user_email, u.user_nicename, u.user_login, u.display_name";

		$from_sql = " FROM {$trs->activity->table_name} a LEFT JOIN {$trmdb->users} u ON a.user_id = u.ID";

		// Where conditions
		$where_conditions = array();

		// Searching
		if ( $search_terms ) {
			$search_terms = $trmdb->escape( $search_terms );
			$where_conditions['search_sql'] = "a.content LIKE '%%" . like_escape( $search_terms ) . "%%'";
		}

		// Filtering
		if ( $filter && $filter_sql = TRS_Activity_Activity::get_filter_sql( $filter ) )
			$where_conditions['filter_sql'] = $filter_sql;

		// Sorting
		if ( $sort != 'ASC' && $sort != 'DESC' )
			$sort = 'DESC';

		// Hide Hidden Items?
		if ( !$show_hidden )
			$where_conditions['hidden_sql'] = "a.hide_sitewide = 0";

		// Exclude specified items
		if ( $exclude )
			$where_conditions['exclude'] = "a.id NOT IN ({$exclude})";

		// The specific ids to which you want to limit the query
		if ( !empty( $in ) ) {
			if ( is_array( $in ) ) {
				$in = implode ( ',', array_map( 'absint', $in ) );
			} else {
				$in = implode ( ',', array_map( 'absint', explode ( ',', $in ) ) );
			}

			$where_conditions['in'] = "a.id IN ({$in})";
		}

		// Alter the query based on whether we want to show activity item
		// comments in the stream like normal comments or threaded below
		// the activity.
		if ( false === $display_comments || 'threaded' === $display_comments )
			$where_conditions[] = "a.type != 'activity_comment'";

		$where_sql = 'WHERE ' . join( ' AND ', $where_conditions );

// echo "<b>".$select_sql.$from_sql.$where_sql."</b><hr>";

		if ( $per_page && $page ) {
			$pag_sql = $trmdb->prepare( "LIMIT %d, %d", intval( ( $page - 1 ) * $per_page ), intval( $per_page ) );
			$activities = $trmdb->get_results( apply_filters( 'trs_activity_get_user_join_filter', $trmdb->prepare( "{$select_sql} {$from_sql} {$where_sql} ORDER BY a.date_recorded {$sort} {$pag_sql}" ), $select_sql, $from_sql, $where_sql, $sort, $pag_sql ) );
		} else {
			$activities = $trmdb->get_results( apply_filters( 'trs_activity_get_user_join_filter', $trmdb->prepare( "{$select_sql} {$from_sql} {$where_sql} ORDER BY a.date_recorded {$sort}" ), $select_sql, $from_sql, $where_sql, $sort ) );
		}

		$total_activities_sql = apply_filters( 'trs_activity_total_activities_sql', $trmdb->prepare( "SELECT count(a.id) FROM {$trs->activity->table_name} a {$where_sql} ORDER BY a.date_recorded {$sort}" ), $where_sql, $sort );

		$total_activities = $trmdb->get_var( $total_activities_sql );

// echo 'total_activities_sql'.$total_activities_sql;
// echo  '<b>'.trs_is_active( 'xprofile' ).'</b>';
		// Get the fullnames of users so we don't have to query in the loop
		if ( trs_is_active( 'xprofile' ) && $activities ) {
			foreach ( (array)$activities as $activity ) {
				if ( (int)$activity->user_id )
					$activity_user_ids[] = $activity->user_id;
			}

			$activity_user_ids = implode( ',', array_unique( (array)$activity_user_ids ) );
			if ( !empty( $activity_user_ids ) ) {
				if ( $names = $trmdb->get_results( $trmdb->prepare( "SELECT user_id, value AS user_fullname FROM {$trs->profile->table_name_data} WHERE field_id = 1 AND user_id IN ({$activity_user_ids})" ) ) ) {
					foreach ( (array)$names as $name )
						$tmp_names[$name->user_id] = $name->user_fullname;

					foreach ( (array)$activities as $i => $activity ) {
						if ( !empty( $tmp_names[$activity->user_id] ) )
							$activities[$i]->user_fullname = $tmp_names[$activity->user_id];
					}

					unset( $names );
					unset( $tmp_names );
				}
			}
		}
		if ( $activities && $display_comments )
			$activities = TRS_Activity_Activity::append_comments( $activities );

		// If $max is set, only return up to the max results
		if ( !empty( $max ) ) {
			if ( (int)$total_activities > (int)$max )
				$total_activities = $max;
		}
// var_dump(array( 'activities' => $activities, 'total' => (int)$total_activities ));
		return array( 'activities' => $activities, 'total' => (int)$total_activities );
	}

	/**
	 * In trendr 1.2.x, this was used to retrieve specific activity stream items (for example, on an activity's permalink page).
	 * As of 1.5.x, use TRS_Activity_Activity::get( ..., $in ) instead.
	 *
	 * @deprecated 1.5
	 * @deprecated Use TRS_Activity_Activity::get( ..., $in ) instead.
	 * @param mixed $activity_ids Array or comma-separated string of activity IDs to retrieve
	 * @param int $max Maximum number of results to return. (Optional; default is no maximum)
	 * @param int $page The set of results that the user is viewing. Used in pagination. (Optional; default is 1)
	 * @param int $per_page Specifies how many results per page. Used in pagination. (Optional; default is 25)
	 * @param string MySQL column sort; ASC or DESC. (Optional; default is DESC)
	 * @param bool $display_comments Retrieve an activity item's associated comments or not. (Optional; default is false)
	 * @return array
	 * @since 1.2
	 */
	function get_specific( $activity_ids, $max = false, $page = 1, $per_page = 25, $sort = 'DESC', $display_comments = false ) {
		_deprecated_function( __FUNCTION__, '1.5', 'Use TRS_Activity_Activity::get( ..., $in ) instead.' );

		return TRS_Activity_Activity::get( $max, $page, $per_page, $sort, false, false, $display_comments, false, false, $activity_ids );
	}

	function get_id( $user_id, $component, $type, $item_id, $secondary_item_id, $action, $content, $date_recorded ) {
		global $trs, $trmdb;

		$where_args = false;

		if ( !empty( $user_id ) )
			$where_args[] = $trmdb->prepare( "user_id = %d", $user_id );

		if ( !empty( $component ) )
			$where_args[] = $trmdb->prepare( "component = %s", $component );

		if ( !empty( $type ) )
			$where_args[] = $trmdb->prepare( "type = %s", $type );

		if ( !empty( $item_id ) )
			$where_args[] = $trmdb->prepare( "item_id = %s", $item_id );

		if ( !empty( $secondary_item_id ) )
			$where_args[] = $trmdb->prepare( "secondary_item_id = %s", $secondary_item_id );

		if ( !empty( $action ) )
			$where_args[] = $trmdb->prepare( "action = %s", $action );

		if ( !empty( $content ) )
			$where_args[] = $trmdb->prepare( "content = %s", $content );

		if ( !empty( $date_recorded ) )
			$where_args[] = $trmdb->prepare( "date_recorded = %s", $date_recorded );

		if ( !empty( $where_args ) )
			$where_sql = 'WHERE ' . join( ' AND ', $where_args );
		else
			return false;

		return $trmdb->get_var( "SELECT id FROM {$trs->activity->table_name} {$where_sql}" );
	}

	function delete( $args ) {
		global $trmdb, $trs;

		$defaults = array(
			'id'                => false,
			'action'            => false,
			'content'           => false,
			'component'         => false,
			'type'              => false,
			'primary_link'      => false,
			'user_id'           => false,
			'item_id'           => false,
			'secondary_item_id' => false,
			'date_recorded'     => false,
			'hide_sitewide'     => false
		);
		$params = trm_parse_args( $args, $defaults );
		extract( $params );

		$where_args = false;

		if ( !empty( $id ) )
			$where_args[] = $trmdb->prepare( "id = %d", $id );

		if ( !empty( $user_id ) )
			$where_args[] = $trmdb->prepare( "user_id = %d", $user_id );

		if ( !empty( $action ) )
			$where_args[] = $trmdb->prepare( "action = %s", $action );

		if ( !empty( $content ) )
			$where_args[] = $trmdb->prepare( "content = %s", $content );

		if ( !empty( $component ) )
			$where_args[] = $trmdb->prepare( "component = %s", $component );

		if ( !empty( $type ) )
			$where_args[] = $trmdb->prepare( "type = %s", $type );

		if ( !empty( $primary_link ) )
			$where_args[] = $trmdb->prepare( "primary_link = %s", $primary_link );

		if ( !empty( $item_id ) )
			$where_args[] = $trmdb->prepare( "item_id = %s", $item_id );

		if ( !empty( $secondary_item_id ) )
			$where_args[] = $trmdb->prepare( "secondary_item_id = %s", $secondary_item_id );

		if ( !empty( $date_recorded ) )
			$where_args[] = $trmdb->prepare( "date_recorded = %s", $date_recorded );

		if ( !empty( $hide_sitewide ) )
			$where_args[] = $trmdb->prepare( "hide_sitewide = %d", $hide_sitewide );

		if ( !empty( $where_args ) )
			$where_sql = 'WHERE ' . join( ' AND ', $where_args );
		else
			return false;

		// Fetch the activity IDs so we can delete any comments for this activity item
		$activity_ids = $trmdb->get_col( $trmdb->prepare( "SELECT id FROM {$trs->activity->table_name} {$where_sql}" ) );

		if ( !$trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->activity->table_name} {$where_sql}" ) ) )
			return false;

		if ( $activity_ids ) {
			TRS_Activity_Activity::delete_activity_item_comments( $activity_ids );
			TRS_Activity_Activity::delete_activity_meta_entries( $activity_ids );

			return $activity_ids;
		}

		return $activity_ids;
	}

	function delete_activity_item_comments( $activity_ids ) {
		global $trs, $trmdb;

		if ( is_array( $activity_ids ) )
			$activity_ids = implode ( ',', array_map( 'absint', $activity_ids ) );
		else
			$activity_ids = implode ( ',', array_map( 'absint', explode ( ',', $activity_ids ) ) );

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->activity->table_name} WHERE type = 'activity_comment' AND item_id IN ({$activity_ids})" ) );
	}

	function delete_activity_meta_entries( $activity_ids ) {
		global $trs, $trmdb;

		if ( is_array( $activity_ids ) )
			$activity_ids = implode ( ',', array_map( 'absint', $activity_ids ) );
		else
			$activity_ids = implode ( ',', array_map( 'absint', explode ( ',', $activity_ids ) ) );

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->activity->table_name_meta} WHERE activity_id IN ({$activity_ids})" ) );
	}

	function append_comments( $activities ) {
		global $trs, $trmdb;

		$activity_comments = array();

		/* Now fetch the activity comments and parse them into the correct position in the activities array. */
		foreach( (array)$activities as $activity ) {
			if ( 'activity_comment' != $activity->type && $activity->mptt_left && $activity->mptt_right )
				$activity_comments[$activity->id] = TRS_Activity_Activity::get_activity_comments( $activity->id, $activity->mptt_left, $activity->mptt_right );
		}

		/* Merge the comments with the activity items */
		foreach( (array)$activities as $key => $activity )
			if ( isset( $activity_comments[$activity->id] ) )
				$activities[$key]->children = $activity_comments[$activity->id];

		return $activities;
	}

	function get_activity_comments( $activity_id, $left, $right ) {
		global $trmdb, $trs;

		if ( !$comments = trm_cache_get( 'trs_activity_comments_' . $activity_id ) ) {
			// Select the user's fullname with the query
			if ( trs_is_active( 'xprofile' ) ) {
				$fullname_select = ", pd.value as user_fullname";
				$fullname_from = ", {$trs->profile->table_name_data} pd ";
				$fullname_where = "AND pd.user_id = a.user_id AND pd.field_id = 1";

			// Prevent debug errors
			} else {
				$fullname_select = $fullname_from = $fullname_where = '';
			}

			// Retrieve all descendants of the $root node
			$descendants = $trmdb->get_results( apply_filters( 'trs_activity_comments_user_join_filter', $trmdb->prepare( "SELECT a.*, u.user_email, u.user_nicename, u.user_login, u.display_name{$fullname_select} FROM {$trs->activity->table_name} a, {$trmdb->users} u{$fullname_from} WHERE u.ID = a.user_id {$fullname_where} AND a.type = 'activity_comment' AND a.item_id = %d AND a.mptt_left BETWEEN %d AND %d ORDER BY a.date_recorded ASC", $activity_id, $left, $right ), $activity_id, $left, $right ) );

			// Loop descendants and build an assoc array
			foreach ( (array)$descendants as $d ) {
				$d->children = array();

				// If we have a reference on the parent
				if ( isset( $ref[ $d->secondary_item_id ] ) ) {
					$ref[ $d->secondary_item_id ]->children[ $d->id ] = $d;
					$ref[ $d->id ] = $ref[ $d->secondary_item_id ]->children[ $d->id ];

				// If we don't have a reference on the parent, put in the root level
				} else {
					$comments[ $d->id ] = $d;
					$ref[ $d->id ] = $comments[ $d->id ];
				}
			}
			trm_cache_set( 'trs_activity_comments_' . $activity_id, $comments, 'trs' );
		}

		return $comments;
	}

	function rebuild_activity_comment_tree( $parent_id, $left = 1 ) {
		global $trmdb, $trs;

		// The right value of this node is the left value + 1
		$right = $left + 1;

		// Get all descendants of this node
		$descendants = TRS_Activity_Activity::get_child_comments( $parent_id );

		// Loop the descendants and recalculate the left and right values
		foreach ( (array)$descendants as $descendant )
			$right = TRS_Activity_Activity::rebuild_activity_comment_tree( $descendant->id, $right );

		// We've got the left value, and now that we've processed the children
		// of this node we also know the right value
		if ( 1 == $left )
			$trmdb->query( $trmdb->prepare( "UPDATE {$trs->activity->table_name} SET mptt_left = %d, mptt_right = %d WHERE id = %d", $left, $right, $parent_id ) );
		else
			$trmdb->query( $trmdb->prepare( "UPDATE {$trs->activity->table_name} SET mptt_left = %d, mptt_right = %d WHERE type = 'activity_comment' AND id = %d", $left, $right, $parent_id ) );

		// Return the right value of this node + 1
		return $right + 1;
	}

	function get_child_comments( $parent_id ) {
		global $trs, $trmdb;

		return $trmdb->get_results( $trmdb->prepare( "SELECT id FROM {$trs->activity->table_name} WHERE type = 'activity_comment' AND secondary_item_id = %d", $parent_id ) );
	}

	function get_recorded_components() {
		global $trmdb, $trs;

		return $trmdb->get_col( $trmdb->prepare( "SELECT DISTINCT component FROM {$trs->activity->table_name} ORDER BY component ASC" ) );
	}

	function get_sitewide_items_for_feed( $limit = 35 ) {
		global $trmdb, $trs;

		$activities = trs_activity_get_sitewide( array( 'max' => $limit ) );

		for ( $i = 0, $count = count( $activities ); $i < $count; ++$i ) {
				$title = explode( '<span', $activities[$i]['content'] );

				$activity_feed[$i]['title'] = trim( strip_tags( $title[0] ) );
				$activity_feed[$i]['link'] = $activities[$i]['primary_link'];
				$activity_feed[$i]['description'] = @sprintf( $activities[$i]['content'], '' );
				$activity_feed[$i]['pubdate'] = $activities[$i]['date_recorded'];
		}

		return $activity_feed;
	}

	function get_in_operator_sql( $field, $items ) {
		global $trmdb;

		// split items at the comma
		$items_dirty = explode( ',', $items );

		// array of prepared integers or quoted strings
		$items_prepared = array();

		// clean up and format each item
		foreach ( $items_dirty as $item ) {
			// clean up the string
			$item = trim( $item );
			// pass everything through prepare for security and to safely quote strings
			$items_prepared[] = ( is_numeric( $item ) ) ? $trmdb->prepare( '%d', $item ) : $trmdb->prepare( '%s', $item );
		}

		// build IN operator sql syntax
		if ( count( $items_prepared ) )
			return sprintf( '%s IN ( %s )', trim( $field ), implode( ',', $items_prepared ) );
		else
			return false;
	}
//asamir edit to support the multiscope task by allow oring between each scope and the other one
	function get_filter_sql( $filter_array ) {
		global $trmdb;
// var_dump($filter_array['object']);
$result = "";
if(strpos($filter_array['object'],',') == false){
	if ( !empty( $filter_array['activities_ids'] ) ) {
		// var_dump($filter_array);
		$activities_ids_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.id', $filter_array['activities_ids'] );
		if ( !empty( $activities_ids_sql ) )
			$filter_sql[] = $activities_ids_sql ;//include  the ReCommended post of my friends
	}
		if ( !empty( $filter_array['user_id'] ) ) {
			$user_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.user_id', $filter_array['user_id'] );
			if ( !empty( $user_sql ) )
				$filter_sql[] = $user_sql;
		}

		if ( !empty( $filter_array['object'] ) ) {
			$object_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.component', $filter_array['object'] );
			if ( !empty( $object_sql ) )
				$filter_sql[] = $object_sql;
		}

		if ( !empty( $filter_array['action'] ) ) {
			$action_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.type', $filter_array['action'] );
			if ( !empty( $action_sql ) )
				$filter_sql[] = $action_sql;
		}

		if ( !empty( $filter_array['primary_id'] ) ) {
			$pid_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.item_id', $filter_array['primary_id'] );
			if ( !empty( $pid_sql ) )
				$filter_sql[] = $pid_sql;
		}

		if ( !empty( $filter_array['secondary_id'] ) ) {
			$sid_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.secondary_item_id', $filter_array['secondary_id'] );
			if ( !empty( $sid_sql ) )
				$filter_sql[] = $sid_sql;
		}

		if ( empty($filter_sql) )
			return false;

		return join( ' AND ', $filter_sql );
	}else{

	if ( !empty( $filter_array['activities_ids'] ) ) {
		// var_dump($filter_array);
		$activities_ids_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.id', $filter_array['activities_ids'] );
		if ( !empty( $activities_ids_sql ) )
			$filter_sql1[] = $activities_ids_sql ;//include  the ReCommended post of my friends
	}

		if ( !empty( $filter_array['user_id'] ) ) {
			$user_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.user_id', $filter_array['user_id'] );
			if ( !empty( $user_sql ) )
				$filter_sql[] = $user_sql . " and a.component not IN ( 'groups' ) ";//execlude the groups post of my friends
		}







		if ( !empty( $filter_array['secondary_id'] ) ) {
			$sid_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.secondary_item_id', $filter_array['secondary_id'] );
			if ( !empty( $sid_sql ) )
				$filter_sql[] = $sid_sql;
		}




			if ( !empty( $filter_array['primary_id'] ) ) {
				if ( !empty( $filter_array['object'] ) ) {
					$object_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.component', $filter_array['object'] );
					if ( !empty( $object_sql ) )
						$filter_sql2[] = $object_sql;
				}

				$pid_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.item_id', $filter_array['primary_id'] );
				if ( !empty( $pid_sql ) )
					$filter_sql2[] = $pid_sql;
			}

			if (count($filter_sql)==0 && count($filter_sql2)==0 && count($filter_sql1)==0)
				return false;

				if ( !empty( $filter_array['action'] ) ) {
					$action_sql = TRS_Activity_Activity::get_in_operator_sql( 'a.type', $filter_array['action'] );

				}


			if (count($filter_sql) ==0 && count($filter_sql1) ==0){
				if ( !empty( $action_sql ) )
					$filter_sql2[] = $action_sql;
					return join( ' AND ', $filter_sql2 ) ;
				}
			if (count($filter_sql1) ==0 && count($filter_sql2) ==0){
					if ( !empty( $action_sql ) )
					$filter_sql[] = $action_sql;
					return join( ' AND ', $filter_sql ) ;
				}
			if (count($filter_sql) ==0 && count($filter_sql2) ==0){
				if ( !empty( $action_sql ) )
				$filter_sql1[] = $action_sql;

					return join( ' AND ', $filter_sql1 ) ;
				}
$Filters=  array();
if (count($filter_sql) !=0){
	if ( !empty( $action_sql ) )
	$filter_sql[] = $action_sql;
	$Filters[] = '('.join( ' AND ', $filter_sql ) . ')';
}
if (count($filter_sql1) !=0){
	if ( !empty( $action_sql ) )
	$filter_sql1[] = $action_sql;

$Filters[] = '('.join( ' AND ', $filter_sql1 ) . ')';
}
if (count($filter_sql2) !=0){
	if ( !empty( $action_sql ) )
	$filter_sql2[] = $action_sql;

$Filters[] = '('.join( ' AND ', $filter_sql2 ) . ')';
}
			return  join(' OR ',$Filters);

	}

	}

	function get_last_updated() {
		global $trs, $trmdb;

		return $trmdb->get_var( $trmdb->prepare( "SELECT date_recorded FROM {$trs->activity->table_name} ORDER BY date_recorded DESC LIMIT 1" ) );
	}

	function total_favorite_count( $user_id ) {
		if ( !$favorite_activity_entries = trs_get_user_meta( $user_id, 'trs_favorite_activities', true ) )
			return 0;

		return count( maybe_unserialize( $favorite_activity_entries ) );
	}

	function check_exists_by_content( $content ) {
		global $trmdb, $trs;

		return $trmdb->get_var( $trmdb->prepare( "SELECT id FROM {$trs->activity->table_name} WHERE content = %s", $content ) );
	}

	function hide_all_for_user( $user_id ) {
		global $trmdb, $trs;

		return $trmdb->get_var( $trmdb->prepare( "UPDATE {$trs->activity->table_name} SET hide_sitewide = 1 WHERE user_id = %d", $user_id ) );
	}
}

?>
