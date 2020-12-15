<?php

/**
 * trendr Activity Template Functions
 *
 * @package trendr
 * @sutrsackage ActivityTemplate
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Output the activity component slug
 *
 * @since 1.5.0
 *
 * @uses trs_get_activity_slug()
 */
function trs_activity_slug() {
	echo trs_get_activity_slug();
}
	/**
	 * Return the activity component slug
	 *
	 * @since 1.5.0
	 *
	 * @global object $trs trendr global settings
	 * @uses apply_filters() To call the 'trs_get_activity_slug' hook
	 */
	function trs_get_activity_slug() {
		global $trs;
		return apply_filters( 'trs_get_activity_slug', $trs->activity->slug );
	}

/**
 * Output the activity component root slug
 *
 * @since 1.5.0
 *
 * @uses trs_get_activity_root_slug()
 */
function trs_activity_root_slug() {
	echo trs_get_activity_root_slug();
}
	/**
	 * Return the activity component root slug
	 *
	 * @since 1.5.0
	 *
	 * @global object $trs trendr global settings
	 * @uses apply_filters() To call the 'trs_get_activity_root_slug' hook
	 */
	function trs_get_activity_root_slug() {
		global $trs;
		return apply_filters( 'trs_get_activity_root_slug', $trs->activity->root_slug );
	}

/**
 * Output member directory permalink
 *
 * @since 1.5.0
 *
 * @uses trs_get_activity_directory_permalink()
 */
function trs_activity_directory_permalink() {
	echo trs_get_activity_directory_permalink();
}
	/**
	 * Return member directory permalink
	 *
	 * @since 1.5.0
	 *
	 * @uses traisingslashit()
	 * @uses trs_get_root_domain()
	 * @uses trs_get_activity_root_slug()
	 * @uses apply_filters() To call the 'trs_get_activity_directory_permalink' hook
	 *
	 * @return string Activity directory permalink
	 */
	function trs_get_activity_directory_permalink() {
		return apply_filters( 'trs_get_activity_directory_permalink', trailingslashit( trs_get_root_domain() . '/' . trs_get_activity_root_slug() ) );
	}

/**
 * The main activity template loop
 *
 * This is responsible for loading a group of activity items and displaying them
 *
 * @since 1.0.0
 */
class TRS_Activity_Template {
	var $current_activity = -1;
	var $activity_count;
	var $total_activity_count;
	var $activities;
	var $activity;

	var $in_the_loop;

	var $pag_page;
	var $pag_num;
	var $pag_links;

	var $full_name;

	function trs_activity_template( $page, $per_page, $max, $include, $sort, $filter, $search_terms, $display_comments, $show_hidden, $exclude = false, $in = false ) {
		$this->__construct( $page, $per_page, $max, $include, $sort, $filter, $search_terms, $display_comments, $show_hidden, $exclude, $in );
	}

	function __construct( $page, $per_page, $max, $include, $sort, $filter, $search_terms, $display_comments, $show_hidden, $exclude = false, $in = false ) {
		global $trs;

		$this->pag_page = isset( $_REQUEST['acpage'] ) ? intval( $_REQUEST['acpage'] ) : $page;
		$this->pag_num  = isset( $_REQUEST['num'] ) ? intval( $_REQUEST['num'] ) : $per_page;

		// Check if blog/forum replies are disabled
		$this->disable_blogforum_replies = isset( $trs->site_options['trs-disable-blogforum-comments'] ) ? $trs->site_options['trs-disable-blogforum-comments'] : false;

		// Get an array of the logged in user's favorite activities
		$this->my_favs = maybe_unserialize( trs_get_user_meta( $trs->loggedin_user->id, 'trs_favorite_activities', true ) );

		// Fetch specific activity items based on ID's
		if ( !empty( $include ) )
			$this->activities = trs_activity_get_specific( array( 'activity_ids' => explode( ',', $include ), 'max' => $max, 'page' => $this->pag_page, 'per_page' => $this->pag_num, 'sort' => $sort, 'display_comments' => $display_comments, 'show_hidden' => $show_hidden ) );
		// Fetch all activity items
		else
			$this->activities = trs_activity_get( array( 'display_comments' => $display_comments, 'max' => $max, 'per_page' => $this->pag_num, 'page' => $this->pag_page, 'sort' => $sort, 'search_terms' => $search_terms, 'filter' => $filter, 'show_hidden' => $show_hidden, 'exclude' => $exclude, 'in' => $in ) );

		if ( !$max || $max >= (int)$this->activities['total'] )
			$this->total_activity_count = (int)$this->activities['total'];
		else
			$this->total_activity_count = (int)$max;

		$this->activities = $this->activities['activities'];

		if ( $max ) {
			if ( $max >= count($this->activities) ) {
				$this->activity_count = count( $this->activities );
			} else {
				$this->activity_count = (int)$max;
			}
		} else {
			$this->activity_count = count( $this->activities );
		}

		$this->full_name = $trs->displayed_user->fullname;

		// Fetch parent content for activity comments so we do not have to query in the loop
		foreach ( (array)$this->activities as $activity ) {
			if ( 'activity_comment' != $activity->type )
				continue;

			$parent_ids[] = $activity->item_id;
		}

		if ( !empty( $parent_ids ) )
			$activity_parents = trs_activity_get_specific( array( 'activity_ids' => $parent_ids ) );

		if ( !empty( $activity_parents['activities'] ) ) {
			foreach( $activity_parents['activities'] as $parent )
				$this->activity_parents[$parent->id] = $parent;

			unset( $activity_parents );
		}

		if ( (int)$this->total_activity_count && (int)$this->pag_num ) {
			$this->pag_links = paginate_links( array(
				'base'      => add_query_arg( 'acpage', '%#%' ),
				'format'    => '',
				'total'     => ceil( (int)$this->total_activity_count / (int)$this->pag_num ),
				'current'   => (int)$this->pag_page,
				'prev_text' => _x( '&larr;', 'Activity pagination previous text', 'trendr' ),
				'next_text' => _x( '&rarr;', 'Activity pagination next text', 'trendr' ),
				'mid_size'  => 1
			) );
		}
	}

	function has_activities() {
		if ( $this->activity_count )
			return true;

		return false;
	}

	function next_activity() {
		$this->current_activity++;
		$this->activity = $this->activities[$this->current_activity];

		return $this->activity;
	}

	function rewind_activities() {
		$this->current_activity = -1;
		if ( $this->activity_count > 0 ) {
			$this->activity = $this->activities[0];
		}
	}

	function user_activities() {
		if ( $this->current_activity + 1 < $this->activity_count ) {
			return true;
		} elseif ( $this->current_activity + 1 == $this->activity_count ) {
			do_action('activity_loop_end');
			// Do some cleaning up after the loop
			$this->rewind_activities();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_activity() {
		global $activity;

		$this->in_the_loop = true;
		$this->activity = $this->next_activity();

		if ( is_array( $this->activity ) )
			$this->activity = (object) $this->activity;

		if ( $this->current_activity == 0 ) // loop has just started
			do_action('activity_loop_start');
	}
}

/**
 * Initializes the activity loop.
 *
 * Based on the $args passed, trs_has_activities() populates the $activities_template global.
 *
 * @since 1.0.0
 *
 * @param array $args Arguments for limiting the contents of the activity loop. Can be passed as an associative array or as a URL argument string
 *
 * @global object $activities_template {@link TRS_Activity_Template}
 * @global object $trs trendr global settings
 * @uses groups_is_user_member()
 * @uses trs_current_action()
 * @uses trs_is_current_action()
 * @uses trs_get_activity_slug()
 * @uses trs_action_variable()
 * @uses trm_parse_args()
 * @uses trs_is_active()
 * @uses friends_get_friend_user_ids()
 * @uses groups_get_user_groups()
 * @uses trs_activity_get_user_favorites()
 * @uses apply_filters() To call the 'trs_has_activities' hook
 *
 * @return bool Returns true when activities are found
 */
function trs_has_activities( $args = '' ) {
	global $activities_template, $trs;

	/***
	 * Set the defaults based on the current page. Any of these will be overridden
	 * if arguments are directly passed into the loop. Custom plugins should always
	 * pass their parameters directly to the loop.
	 */
	$user_id     = false;
	$include     = false;
	$exclude     = false;
	$in          = false;
	$show_hidden = false;
	$object      = false;
	$primary_id  = false;

	// User filtering
	if ( !empty( $trs->displayed_user->id ) )
		$user_id = $trs->displayed_user->id;

	// Group filtering
	if ( !empty( $trs->groups->current_group ) ) {
		$object = $trs->groups->id;
		$primary_id = $trs->groups->current_group->id;

		if ( 'public' != $trs->groups->current_group->status && ( groups_is_user_member( $trs->loggedin_user->id, $trs->groups->current_group->id ) || $trs->loggedin_user->is_super_admin ) )
			$show_hidden = true;
	}

	// The default scope should recognize custom slugs
	if ( array_key_exists( $trs->current_action, (array)$trs->loaded_components ) ) {
		$scope = $trs->loaded_components[$trs->current_action];
	}
	else
		$scope = trs_current_action();

	// Support for permalinks on single item pages: /groups/my-group/activity/124/
	if ( trs_is_current_action( trs_get_activity_slug() ) )
		$include = trs_action_variable( 0 );

	// Note: any params used for filtering can be a single value, or multiple values comma separated.
	$defaults = array(
		'display_comments' => 'threaded',   // false for none, stream/threaded - show comments in the stream or threaded under items
		'include'          => $include,     // pass an activity_id or string of IDs comma-separated
		'exclude'          => $exclude,     // pass an activity_id or string of IDs comma-separated
		'in'               => $in,          // comma-separated list or array of activity IDs among which to search
		'sort'             => 'DESC',       // sort DESC or ASC
		'page'             => 1,            // which page to load
		'per_page'         => 20,           // number of items per page
		'max'              => false,        // max number to return
		'show_hidden'      => $show_hidden, // Show activity items that are hidden site-wide?

		// Scope - pre-built activity filters for a user (friends/groups/favorites/mentions)
		'scope'            => $scope,

		// Filtering
		'user_id'          => $user_id,     // user_id to filter on
		'object'           => $object,      // object to filter on e.g. groups, profile, status, friends
		'action'           => false,        // action to filter on e.g. activity_update, new_forum_post, profile_updated
		'primary_id'       => $primary_id,  // object ID to filter on e.g. a group_id or forum_id or blog_id etc.
		'secondary_id'     => false,        // secondary object ID to filter on e.g. a post_id

		// Searching
		'search_terms'     => false         // specify terms to search on
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r );


	

	// If you have passed a "scope" then this will override any filters you have passed.
	if ( 'just-me' == $scope || 'friends' == $scope || 'groups' == $scope || 'favorites' == $scope || 'mentions' == $scope  || 'swa-home' == $scope ) {
		if ( 'just-me' == $scope )
			$display_comments = 'stream';

		// determine which user_id applies
		if ( empty( $user_id ) )
			$user_id = ( !empty( $trs->displayed_user->id ) ) ? $trs->displayed_user->id : $trs->loggedin_user->id;

		// are we displaying user specific activity?
		if ( is_numeric( $user_id ) ) {
			$show_hidden = ( $user_id == $trs->loggedin_user->id && $scope != 'friends' ) ? 1 : 0;

			switch ( $scope ) {
				case 'friends':
					if ( trs_is_active( 'friends' ) )
						$friends = friends_get_friend_user_ids( $user_id );
						if ( empty( $friends ) )
							return false;

						$user_id = implode( ',', (array)$friends );
					break;
				case 'groups':
					if ( trs_is_active( 'groups' ) ) {
						$groups = groups_get_user_groups( $user_id );
						if ( empty( $groups['groups'] ) )
							return false;

						$object = $trs->groups->id;
						$primary_id = implode( ',', (array)$groups['groups'] );

						$user_id = 0;
					}
					break;
				case 'favorites':
					$favs = trs_activity_get_user_favorites( $user_id );
					if ( empty( $favs ) )
						return false;

					$include          = implode( ',', (array)$favs );
					$display_comments = true;
					break;
				case 'mentions':
					$user_nicename    = ( !empty( $trs->displayed_user->id ) ) ? $trs->displayed_user->userdata->user_nicename : $trs->loggedin_user->userdata->user_nicename;
					$user_login       = ( !empty( $trs->displayed_user->id ) ) ? $trs->displayed_user->userdata->user_login : $trs->loggedin_user->userdata->user_login;
					$search_terms     = '@' . trs_core_get_username( $user_id, $user_nicename, $user_login ) . '<'; // Start search at @ symbol and stop search at closing tag delimiter.
					$display_comments = 'stream';
					$user_id = 0;
					break;
			}
		}
	}		$scopes = array();
		//$scopes[] = 'just-me';
		//$scopes[] = 'mentions';
		if ( trs_is_active( 'friends' ) ) {
			$scopes[] = 'friends';
		}
		$scopes = apply_filters( 'trs_activity_home_scopes', $scopes );
		$r['scope'] = $scopes;

'swa-home'==$scopes;

	// Do not exceed the maximum per page
	if ( !empty( $max ) && ( (int)$per_page > (int)$max ) )
		$per_page = $max;

	// Support for basic filters in earlier TRS versions.
	if ( isset( $_GET['afilter'] ) )
		$filter = array( 'object' => $_GET['afilter'] );
	else if ( !empty( $user_id ) || !empty( $object ) || !empty( $action ) || !empty( $primary_id ) || !empty( $secondary_id ) )
		$filter = array( 'user_id' => $user_id, 'object' => $object, 'action' => $action, 'primary_id' => $primary_id, 'secondary_id' => $secondary_id );
	else
		$filter = false;

	$activities_template = new TRS_Activity_Template( $page, $per_page, $max, $include, $sort, $filter, $search_terms, $display_comments, $show_hidden, $exclude, $in );

	return apply_filters( 'trs_has_activities', $activities_template->has_activities(), $activities_template );
}

/**
 * Determines if there are still activities left in the loop.
 *
 * @since 1.0.0
 *
 * @global object $activities_template {@link TRS_Activity_Template}
 * @uses TRS_Activity_Template::user_activities() {@link TRS_Activity_Template::user_activities()}
 *
 * @return bool Returns true when activities are found
 */
function trs_activities() {
	global $activities_template;
	return $activities_template->user_activities();
}

/**
 * Gets the current activity object in the loop
 *
 * @since 1.0.0
 *
 * @global object $activities_template {@link TRS_Activity_Template}
 * @uses TRS_Activity_Template::the_activity() {@link TRS_Activity_Template::the_activity()}
 *
 * @return object The current activity within the loop
 */
function trs_the_activity() {
	global $activities_template;
	return $activities_template->the_activity();
}

/**
 * Outputs the activity pagination count
 *
 * @since 1.0.0
 *
 * @global object $activities_template {@link TRS_Activity_Template}
 * @uses TRS_Activity_Template::the_activity() {@link TRS_Activity_Template::the_activity()}
 */
function trs_activity_pagination_count() {
	echo trs_get_activity_pagination_count();
}

	/**
	 * Returns the activity pagination count
	 *
	 * @since 1.2.0
	 *
	 * @global object $trs trendr global settings
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses trs_core_number_format()
	 *
	 * @return string The pagination text
	 */
	function trs_get_activity_pagination_count() {
		global $trs, $activities_template;

		$start_num = intval( ( $activities_template->pag_page - 1 ) * $activities_template->pag_num ) + 1;
		$from_num  = trs_core_number_format( $start_num );
		$to_num    = trs_core_number_format( ( $start_num + ( $activities_template->pag_num - 1 ) > $activities_template->total_activity_count ) ? $activities_template->total_activity_count : $start_num + ( $activities_template->pag_num - 1 ) );
		$total     = trs_core_number_format( $activities_template->total_activity_count );

		return sprintf( __( 'Viewing item %1$s to %2$s (of %3$s items)', 'trendr' ), $from_num, $to_num, $total );
	}

/**
 * Outputs the activity pagination links
 *
 * @since 1.0.0
 *
 * @uses trs_get_activity_pagination_links()
 */
function trs_activity_pagination_links() {
	echo trs_get_activity_pagination_links();
}

	/**
	 * Outputs the activity pagination links
	 *
	 * @since 1.0.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_pagination_links' hook
	 *
	 * @return string The pagination links
	 */
	function trs_get_activity_pagination_links() {
		global $activities_template;

		return apply_filters( 'trs_get_activity_pagination_links', $activities_template->pag_links );
	}

/**
 * Returns true when there are more activity items to be shown than currently appear
 *
 * @since 1.5.0
 *
 * @global object $activities_template {@link TRS_Activity_Template}
 * @uses apply_filters() To call the 'trs_activity_has_more_items' hook
 *
 * @return bool $has_more_items True if more items, false if not
 */
function trs_activity_has_more_items() {
	global $activities_template;

	$remaining_pages = floor( ( $activities_template->total_activity_count - 1 ) / ( $activities_template->pag_num * $activities_template->pag_page ) );
	$has_more_items  = (int)$remaining_pages ? true : false;

	return apply_filters( 'trs_activity_has_more_items', $has_more_items );
}

/**
 * Outputs the activity count
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_count()
 */
function trs_activity_count() {
	echo trs_get_activity_count();
}

	/**
	 * Returns the activity count
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_count' hook
	 *
	 * @return int The activity count
	 */
	function trs_get_activity_count() {
		global $activities_template;

		return apply_filters( 'trs_get_activity_count', (int)$activities_template->activity_count );
	}

/**
 * Outputs the number of activities per page
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_per_page()
 */
function trs_activity_per_page() {
	echo trs_get_activity_per_page();
}

	/**
	 * Returns the number of activities per page
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_per_page' hook
	 *
	 * @return int The activities per page
	 */
	function trs_get_activity_per_page() {
		global $activities_template;

		return apply_filters( 'trs_get_activity_per_page', (int)$activities_template->pag_num );
	}

/**
 * Outputs the activities title
 *
 * @since 1.0.0
 *
 * @uses trs_get_activities_title()
 */
function trs_activities_title() {
	global $trs_activity_title;

	echo trs_get_activities_title();
}

	/**
	 * Returns the activities title
	 *
	 * @since 1.0.0
	 *
	 * @global string $trs_activity_title
	 * @uses apply_filters() To call the 'trs_get_activities_title' hook
	 *
	 * @return int The activities title
	 */
	function trs_get_activities_title() {
		global $trs_activity_title;

		return apply_filters( 'trs_get_activities_title', $trs_activity_title );
	}

/**
 * {@internal Missing Description}
 *
 * @since 1.0.0
 *
 * @uses trs_get_activities_no_activity()
 */
function trs_activities_no_activity() {
	global $trs_activity_no_activity;

	echo trs_get_activities_no_activity();
}

	/**
	 * {@internal Missing Description}
	 *
	 * @since 1.0.0
	 *
	 * @global string $trs_activity_no_activity
	 * @uses apply_filters() To call the 'trs_get_activities_no_activity' hook
	 *
	 * @return unknown_type
	 */
	function trs_get_activities_no_activity() {
		global $trs_activity_no_activity;

		return apply_filters( 'trs_get_activities_no_activity', $trs_activity_no_activity );
	}

/**
 * Outputs the activity id
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_id()
 */
function trs_activity_id() {
	echo trs_get_activity_id();
}

	/**
	 * Returns the activity id
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_id' hook
	 *
	 * @return int The activity id
	 */
	function trs_get_activity_id() {
		global $activities_template;
		return apply_filters( 'trs_get_activity_id', $activities_template->activity->id );
	}

/**
 * Outputs the activity item id
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_item_id()
 */
function trs_activity_item_id() {
	echo trs_get_activity_item_id();
}

	/**
	 * Returns the activity item id
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_item_id' hook
	 *
	 * @return int The activity item id
	 */
	function trs_get_activity_item_id() {
		global $activities_template;
		return apply_filters( 'trs_get_activity_item_id', $activities_template->activity->item_id );
	}

/**
 * Outputs the activity secondary item id
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_secondary_item_id()
 */
function trs_activity_secondary_item_id() {
	echo trs_get_activity_secondary_item_id();
}

	/**
	 * Returns the activity secondary item id
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_secondary_item_id' hook
	 *
	 * @return int The activity secondary item id
	 */
	function trs_get_activity_secondary_item_id() {
		global $activities_template;
		return apply_filters( 'trs_get_activity_secondary_item_id', $activities_template->activity->secondary_item_id );
	}

/**
 * Outputs the date the activity was recorded
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_date_recorded()
 */
function trs_activity_date_recorded() {
	echo trs_get_activity_date_recorded();
}

	/**
	 * Returns the date the activity was recorded
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_date_recorded' hook
	 *
	 * @return string The date the activity was recorded
	 */
	function trs_get_activity_date_recorded() {
		global $activities_template;
		return apply_filters( 'trs_get_activity_date_recorded', $activities_template->activity->date_recorded );
	}

/**
 * Outputs the activity object name
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_object_name()
 */
function trs_activity_object_name() {
	echo trs_get_activity_object_name();
}

	/**
	 * Returns the activity object name
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_object_name' hook
	 *
	 * @return string The activity object name
	 */
	function trs_get_activity_object_name() {
		global $activities_template;
		return apply_filters( 'trs_get_activity_object_name', $activities_template->activity->component );
	}

/**
 * Outputs the activity type
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_type()
 */
function trs_activity_type() {
	echo trs_get_activity_type();
}

	/**
	 * Returns the activity type
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_type' hook
	 *
	 * @return string The activity type
	 */
	function trs_get_activity_type() {
		global $activities_template;
		return apply_filters( 'trs_get_activity_type', $activities_template->activity->type );
	}

	/**
	 * Outputs the activity action name
	 *
	 * Just a wrapper for trs_activity_type()
	 *
	 * @since 1.2.0
	 * @deprecated 1.5.0
	 *
	 * @todo Properly deprecate in favor of trs_activity_type() and
	 *		 remove redundant echo
	 *
	 * @uses trs_activity_type()
	 */
	function trs_activity_action_name() { echo trs_activity_type(); }

	/**
	 * Returns the activity type
	 *
	 * Just a wrapper for trs_get_activity_type()
	 *
	 * @since 1.2.0
	 * @deprecated 1.5.0
	 *
	 * @todo Properly deprecate in favor of trs_get_activity_type()
	 *
	 * @uses trs_get_activity_type()
	 *
	 * @return string The activity type
	 */
	function trs_get_activity_action_name() { return trs_get_activity_type(); }

/**
 * Outputs the activity user id
 *
 * @since 1.1.0
 *
 * @uses trs_get_activity_user_id()
 */
function trs_activity_user_id() {
	echo trs_get_activity_user_id();
}

	/**
	 * Returns the activity user id
	 *
	 * @since 1.1.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_user_id' hook
	 *
	 * @return int The activity user id
	 */
	function trs_get_activity_user_id() {
		global $activities_template;
		return apply_filters( 'trs_get_activity_user_id', $activities_template->activity->user_id );
	}

/**
 * Outputs the activity user link
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_user_link()
 */
function trs_activity_user_link() {
	echo trs_get_activity_user_link();
}

	/**
	 * Returns the activity user link
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses trs_core_get_user_domain()
	 * @uses apply_filters() To call the 'trs_get_activity_user_link' hook
	 *
	 * @return string $link The activity user link
	 */
	function trs_get_activity_user_link() {
		global $activities_template;

		if ( empty( $activities_template->activity->user_id ) )
			$link = $activities_template->activity->primary_link;
		else
			$link = trs_core_get_user_domain( $activities_template->activity->user_id, $activities_template->activity->user_nicename, $activities_template->activity->user_login );

		return apply_filters( 'trs_get_activity_user_link', $link );
	}

/**
 * Output the portrait of the user that performed the action
 *
 * @since 1.1.0
 *
 * @param array $args
 *
 * @uses trs_get_activity_portrait()
 */
function trs_activity_portrait( $args = '' ) {
	echo trs_get_activity_portrait( $args );
}
	/**
	 * Return the portrait of the user that performed the action
	 *
	 * @since 1.1.0
	 *
	 * @param array $args optional
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @global object $trs trendr global settings
	 * @uses trs_is_single_activity()
	 * @uses trm_parse_args()
	 * @uses apply_filters() To call the 'trs_get_activity_portrait_object_' . $current_activity_item->component hook
	 * @uses apply_filters() To call the 'trs_get_activity_portrait_item_id' hook
	 * @uses trs_core_fetch_portrait()
	 * @uses apply_filters() To call the 'trs_get_activity_portrait' hook
	 *
	 * @return string User portrait
	 */
	function trs_get_activity_portrait( $args = '' ) {
		global $activities_template, $trs;

		// On activity permalink pages, default to the full-size portrait
		$type_default = trs_is_single_activity() ? 'full' : 'thumb';

		$defaults = array(
			'alt'     => __( 'Profile picture of %s', 'trendr' ),
			'class'   => 'portrait',
			'email'   => false,
			'type'    => $type_default,
			'user_id' => false
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( !isset( $height ) && !isset( $width ) ) {
			// Backpat
			if ( isset( $trs->portrait->full->height ) || isset( $trs->portrait->thumb->height ) ) {
				$height = ( 'full' == $type ) ? $trs->portrait->full->height : $trs->portrait->thumb->height;
			} else {
				$height = 20;
			}

			// Backpat
			if ( isset( $trs->portrait->full->width ) || isset( $trs->portrait->thumb->width ) ) {
				$width = ( 'full' == $type ) ? $trs->portrait->full->width : $trs->portrait->thumb->width;
			} else {
				$width = 20;
			}

		}

		// Within the loop, we the current activity should be set first to the
		// current_comment, if available
		$current_activity_item = isset( $activities_template->activity->current_comment ) ? $activities_template->activity->current_comment : $activities_template->activity;

		// Primary activity portrait is always a user, but can be modified via a filter
		$object  = apply_filters( 'trs_get_activity_portrait_object_' . $current_activity_item->component, 'user' );
		$item_id = $user_id ? $user_id : $current_activity_item->user_id;
		$item_id = apply_filters( 'trs_get_activity_portrait_item_id', $item_id );

		// If this is a user object pass the users' email address for Grportrait so we don't have to refetch it.
		if ( 'user' == $object && empty( $user_id ) && empty( $email ) && isset( $activities_template->activity->user_email ) )
			$email = $current_activity_item->user_email;

		return apply_filters( 'trs_get_activity_portrait', trs_core_fetch_portrait( array( 'item_id' => $item_id, 'object' => $object, 'type' => $type, 'alt' => $alt, 'class' => $class, 'width' => $width, 'height' => $height, 'email' => $email ) ) );
	}

/**
 * Output the portrait of the object that action was performed on
 *
 * @since 1.2.0
 *
 * @param array $args optional
 *
 * @uses trs_get_activity_secondary_portrait()
 */
function trs_activity_secondary_portrait( $args = '' ) {
	echo trs_get_activity_secondary_portrait( $args );
}

	/**
	 * Return the portrait of the object that action was performed on
	 *
	 * @since 1.2.0
	 *
	 * @param array $args optional
	 *
	 * @global object $trs trendr global settings
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses trm_parse_args()
	 * @uses get_blog_option()
	 * @uses apply_filters() To call the 'trs_get_activity_secondary_portrait_object_' . $activities_template->activity->component hook
	 * @uses apply_filters() To call the 'trs_get_activity_secondary_portrait_item_id' hook
	 * @uses trs_core_fetch_portrait()
	 * @uses apply_filters() To call the 'trs_get_activity_secondary_portrait' hook
	 *
	 * @return string The secondary portrait
	 */
	function trs_get_activity_secondary_portrait( $args = '' ) {
		global $trs, $activities_template;

		$defaults = array(
			'type'   => 'thumb',
			'width'  => 20,
			'height' => 20,
			'class'  => 'portrait',
			'email'  => false
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		// Set item_id and object (default to user)
		switch ( $activities_template->activity->component ) {
			case 'groups' :
				$object = 'group';
				$item_id = $activities_template->activity->item_id;

				if ( empty( $alt ) )
					$alt = __( 'Group logo of %s', 'trendr' );

				break;
			case 'blogs' :
				$object = 'blog';
				$item_id = $activities_template->activity->item_id;

				if ( !$alt )
					$alt = sprintf( __( 'Site authored by %s', 'trendr' ), get_blog_option( $item_id, 'blogname' ) );

				break;
			case 'friends' :
				$object  = 'user';
				$item_id = $activities_template->activity->secondary_item_id;

				if ( empty( $alt ) )
					$alt = __( 'Profile picture of %s', 'trendr' );

				break;
			default :
				$object  = 'user';
				$item_id = $activities_template->activity->user_id;
				$email = $activities_template->activity->user_email;

				if ( !$alt )
					$alt = __( 'Profile picture of %s', 'trendr' );

				break;
		}

		// Allow object and item_id to be filtered
		$object  = apply_filters( 'trs_get_activity_secondary_portrait_object_' . $activities_template->activity->component, $object );
		$item_id = apply_filters( 'trs_get_activity_secondary_portrait_item_id', $item_id );

		// If we have no item_id or object, there is no portrait to display
		if ( empty( $item_id ) || empty( $object ) )
			return false;

		return apply_filters( 'trs_get_activity_secondary_portrait', trs_core_fetch_portrait( array( 'item_id' => $item_id, 'object' => $object, 'type' => $type, 'alt' => $alt, 'class' => $class, 'width' => $width, 'height' => $height, 'email' => $email ) ) );
	}

/**
 * Output the activity action
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_action()
 */
function trs_activity_action() {
	echo trs_get_activity_action();
}

	/**
	 * Return the activity action
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters_ref_array() To call the 'trs_get_activity_action_pre_meta' hook
	 * @uses trs_insert_activity_meta()
	 * @uses apply_filters_ref_array() To call the 'trs_get_activity_action' hook
	 *
	 * @return string The activity action
	 */
	function trs_get_activity_action() {
		global $activities_template;

		$action = $activities_template->activity->action;
		$action = apply_filters_ref_array( 'trs_get_activity_action_pre_meta', array( $action, &$activities_template->activity ) );

		if ( !empty( $action ) )
			$action = trs_insert_activity_meta( $action );

		return apply_filters_ref_array( 'trs_get_activity_action', array( $action, &$activities_template->activity ) );
	}

/**
 * Output the activity content body
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_content_body()
 */
function trs_activity_content_body() {
	echo trs_get_activity_content_body();
}

	/**
	 * Return the activity content body
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses trs_insert_activity_meta()
	 * @uses apply_filters_ref_array() To call the 'trs_get_activity_content_body' hook
	 *
	 * @return string The activity content body
	 */
	function trs_get_activity_content_body() {
		global $activities_template;

		// Backwards compatibility if action is not being used
		if ( empty( $activities_template->activity->action ) && !empty( $activities_template->activity->content ) )
			$activities_template->activity->content = trs_insert_activity_meta( $activities_template->activity->content );

		return apply_filters_ref_array( 'trs_get_activity_content_body', array( $activities_template->activity->content, &$activities_template->activity ) );
	}

/**
 * Does the activity have content?
 *
 * @since 1.2.0
 *
 * @global object $activities_template {@link TRS_Activity_Template}
 *
 * @return bool True if activity has content, false otherwise
 */
function trs_activity_has_content() {
	global $activities_template;

	if ( !empty( $activities_template->activity->content ) )
		return true;

	return false;
}

/**
 * Output the activity content
 *
 * @since 1.0.0
 * @deprecated 1.5.0
 *
 * @todo properly deprecate this function
 *
 * @uses trs_get_activity_content()
 */
function trs_activity_content() {
	echo trs_get_activity_content();
}

	/**
	 * Return the activity content
	 *
	 * @since 1.0.0
	 * @deprecated 1.5.0
	 *
	 * @todo properly deprecate this function
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses trs_get_activity_action()
	 * @uses trs_get_activity_content_body()
	 * @uses apply_filters() To call the 'trs_get_activity_content' hook
	 *
	 * @return string The activity content
	 */
	function trs_get_activity_content() {
		global $activities_template;

		/**
		 * If you want to filter activity update content, please use
		 * the filter 'trs_get_activity_content_body'
		 *
		 * This function is mainly for backwards comptibility.
		 */

		$content = trs_get_activity_action() . ' ' . trs_get_activity_content_body();
		return apply_filters( 'trs_get_activity_content', $content );
	}

/**
 * Insert activity meta
 *
 * @since 1.2.0
 *
 * @param string $content
 *
 * @global object $activities_template {@link TRS_Activity_Template}
 * @global object $trs trendr global settings
 * @uses trs_core_time_since()
 * @uses apply_filters_ref_array() To call the 'trs_activity_time_since' hook
 * @uses trs_is_single_activity()
 * @uses trs_activity_get_permalink()
 * @uses esc_attr__()
 * @uses apply_filters_ref_array() To call the 'trs_activity_permalink' hook
 * @uses apply_filters() To call the 'trs_insert_activity_meta' hook
 *
 * @return string The activity content
 */
// 6-29-18 Modified permalink to diferenciate directory activity and user activity permalinks for lightbox effect
function trs_insert_activity_meta( $content ) {
	global $activities_template, $trs;

	// Strip any legacy time since placeholders from TRS 1.0-1.1
	$content = str_replace( '<span class="time">%s</span>', '', $content );

	// Insert the time since.
	$time_since = apply_filters_ref_array( 'trs_activity_time_since', array( '<span class="time">' . trs_core_time_since( $activities_template->activity->date_recorded ) . '</span>', &$activities_template->activity ) );


    //if ( !trs_is_single_activity() ) 6-29-18 Removed
	// Insert the permalink lightbox for userdomain
	if ( trs_displayed_user_id() && !trs_is_single_activity() )
		$content = apply_filters_ref_array( 'trs_activity_permalink', array( sprintf( '%1$s <a href="%2$s" class="view  expand " title="%3$s">%4$s</a>', $content, trs_activity_get_permalink( $activities_template->activity->id, $activities_template->activity ), esc_attr__( 'Full View', 'trendr' ), $time_since ), &$activities_template->activity ) );
   //Insert th permalink lightbox for directory activity
     elseif( !trs_displayed_user_id() &&  trs_is_activity_component() && !trs_is_single_activity() )
		$content = apply_filters_ref_array( 'trs_activity_permalink', array( sprintf( '%1$s <a href="%2$s" class="view  expand " title="%3$s">%4$s</a>', $content, site_url( 'activity/'. $activities_template->activity->id, $activities_template->activity ), esc_attr__( 'Full View', 'trendr' ), $time_since ), &$activities_template->activity ) );	

else
		$content .= str_pad( $time_since, strlen( $time_since ) + 2, ' ', STR_PAD_BOTH );

	return apply_filters( 'trs_insert_activity_meta', $content );
}

/**
 * Determine if the current user can delete an activity item
 *
 * @since 1.2.0
 *
 * @param object $activity Optional
 *
 * @global object $activities_template {@link TRS_Activity_Template}
 * @global object $trs trendr global settings
 * @uses apply_filters() To call the 'trs_activity_user_can_delete' hook
 *
 * @return bool True if can delete, false otherwise
 */
function trs_activity_user_can_delete( $activity = false ) {
	global $activities_template, $trs;

	if ( !$activity )
		$activity = $activities_template->activity;

	if ( isset( $activity->current_comment ) )
		$activity = $activity->current_comment;

	$can_delete = false;

	if ( $trs->loggedin_user->is_super_admin )
		$can_delete = true;

	if ( $activity->user_id == $trs->loggedin_user->id )
		$can_delete = true;

	if ( $trs->is_item_admin && $trs->is_single_item )
		$can_delete = true;

	return apply_filters( 'trs_activity_user_can_delete', $can_delete );
}

/**
 * Output the activity parent content
 *
 * @since 1.2.0
 *
 * @param array $args Optional
 *
 * @uses trs_get_activity_parent_content()
 */
function trs_activity_parent_content( $args = '' ) {
	echo trs_get_activity_parent_content($args);
}

	/**
	 * Return the activity content
	 *
	 * @since 1.2.0
	 *
	 * @param array $args Optional
	 *
	 * @global object $trs trendr global settings
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses trm_parse_args()
	 * @uses apply_filters() To call the 'trs_get_activity_parent_content' hook
	 *
	 * @return mixed False on failure, otherwise the activity parent content
	 */
	function trs_get_activity_parent_content( $args = '' ) {
		global $trs, $activities_template;

		$defaults = array(
			'hide_user' => false
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		// Get the ID of the parent activity content
		if ( !$parent_id = $activities_template->activity->item_id )
			return false;

		// Get the content of the parent
		if ( empty( $activities_template->activity_parents[$parent_id] ) )
			return false;

		if ( empty( $activities_template->activity_parents[$parent_id]->content ) )
			$content = $activities_template->activity_parents[$parent_id]->action;
		else
			$content = $activities_template->activity_parents[$parent_id]->action . ' ' . $activities_template->activity_parents[$parent_id]->content;

		// Remove the time since content for backwards compatibility
		$content = str_replace( '<span class="time">%s</span>', '', $content );

		// Remove images
		$content = preg_replace( '/<img[^>]*>/Ui', '', $content );

		return apply_filters( 'trs_get_activity_parent_content', $content );
	}

/**
 * Output whether or not the current activity is in a current user's favorites
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_is_favorite()
 */
function trs_activity_is_favorite() {
	echo trs_get_activity_is_favorite();
}

	/**
	 * Return whether or not the current activity is in a current user's favorites
	 *
	 * @since 1.2.0
	 *
	 * @global object $trs trendr global settings
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_is_favorite' hook
	 *
	 * @return bool True if user favorite, false otherwise
	 */
	function trs_get_activity_is_favorite() {
		global $trs, $activities_template;

 		return apply_filters( 'trs_get_activity_is_favorite', in_array( $activities_template->activity->id, (array)$activities_template->my_favs ) );
	}

/**
 * Echoes the comment markup for an activity item
 *
 * @since 1.2.0
 *
 * @todo deprecate $args param
 *
 * @param string $args Unused. Appears to be left over from an earlier implementation.
 */
function trs_activity_comments( $args = '' ) {
	echo trs_activity_get_comments( $args );
}

	/**
	 * Gets the comment markup for an activity item
	 *
	 * @since 1.2.0
	 *
	 * @todo deprecate $args param
	 *
	 * @todo Given that checks for children already happen in trs_activity_recurse_comments(),
	 *    this function can probably be streamlined or removed.
	 *
	 * @param string $args Unused. Appears to be left over from an earlier implementation.
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @global object $trs trendr global settings
	 * @uses trs_activity_recurse_comments()
	 */
	function trs_activity_get_comments( $args = '' ) {
		global $activities_template, $trs;

		if ( !isset( $activities_template->activity->children ) || !$activities_template->activity->children )
			return false;

		trs_activity_recurse_comments( $activities_template->activity );
	}

		/**
		 * Loops through a level of activity comments and loads the template for each
		 *
		 * Note: The recursion itself used to happen entirely in this function. Now it is
		 * split between here and the comment.php template.
		 *
		 * @since 1.2.0
		 *
		 * @todo remove $counter global
		 *
		 * @param object $comment The activity object currently being recursed
		 *
		 * @global object $activities_template {@link TRS_Activity_Template}
		 * @global object $trs trendr global settings
		 * @uses locate_template()
		 */
		function trs_activity_recurse_comments( $comment ) {
			global $activities_template, $trs, $counter;

			if ( !$comment )
				return false;

			if ( empty( $comment->children ) )
				return false;

			echo '<ul>';
			foreach ( (array)$comment->children as $comment_child ) {
				// Put the comment into the global so it's available to filters
				$activities_template->activity->current_comment = $comment_child;

				$template = locate_template( 'activity/comment.php', false, false );

				// Backward compatibility. In older versions of TRS, the markup was
				// generated in the PHP instead of a template. This ensures that
				// older themes (which are not children of trs-default and won't
				// have the new template) will still work.
				if ( !$template ) {
					$template = TRS_PLUGIN_DIR . '/trs-858583/trs-default/activity/comment.php';
				}

				load_template( $template, false );

				unset( $activities_template->activity->current_comment );
			}
			echo '</ul>';
		}

/**
 * Utility function that returns the comment currently being recursed
 *
 * @since 1.5.0
 *
 * @global object $activities_template {@link TRS_Activity_Template}
 * @uses apply_filters() To call the 'trs_activity_current_comment' hook
 *
 * @return object|bool $current_comment The activity comment currently being displayed. False on failure
 */
function trs_activity_current_comment() {
	global $activities_template;

	$current_comment = !empty( $activities_template->activity->current_comment ) ? $activities_template->activity->current_comment : false;

	return apply_filters( 'trs_activity_current_comment', $current_comment );
}


/**
 * Echoes the id of the activity comment currently being displayed
 *
 * @since 1.5.0
 *
 * @uses trs_get_activity_comment_id()
 */
function trs_activity_comment_id() {
	echo trs_get_activity_comment_id();
}

	/**
	 * Gets the id of the activity comment currently being displayed
	 *
	 * @since 1.5.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_activity_comment_id' hook
	 *
	 * @return int $comment_id The id of the activity comment currently being displayed
	 */
	function trs_get_activity_comment_id() {
		global $activities_template;

		$comment_id = isset( $activities_template->activity->current_comment->id ) ? $activities_template->activity->current_comment->id : false;

		return apply_filters( 'trs_activity_comment_id', $comment_id );
	}

/**
 * Echoes the user_id of the author of the activity comment currently being displayed
 *
 * @since 1.5.0
 *
 * @uses trs_get_activity_comment_user_id()
 */
function trs_activity_comment_user_id() {
	echo trs_get_activity_comment_user_id();
}

	/**
	 * Gets the user_id of the author of the activity comment currently being displayed
	 *
	 * @since 1.5.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_activity_comment_user_id' hook
	 *
	 * @return int|bool $user_id The user_id of the author of the displayed activity comment. False on failure
	 */
	function trs_get_activity_comment_user_id() {
		global $activities_template;

		$user_id = isset( $activities_template->activity->current_comment->user_id ) ? $activities_template->activity->current_comment->user_id : false;

		return apply_filters( 'trs_activity_comment_user_id', $user_id );
	}

/**
 * Echoes the author link for the activity comment currently being displayed
 *
 * @since 1.5.0
 *
 * @uses trs_get_activity_comment_user_link()
 */
function trs_activity_comment_user_link() {
	echo trs_get_activity_comment_user_link();
}

	/**
	 * Gets the author link for the activity comment currently being displayed
	 *
	 * @since 1.5.0
	 *
	 * @uses trs_core_get_user_domain()
	 * @uses trs_get_activity_comment_user_id()
	 * @uses apply_filters() To call the 'trs_activity_comment_user_link' hook
	 *
	 * @return string $user_link The URL of the activity comment author's profile
	 */
	function trs_get_activity_comment_user_link() {
		$user_link = trs_core_get_user_domain( trs_get_activity_comment_user_id() );

		return apply_filters( 'trs_activity_comment_user_link', $user_link );
	}

/**
 * Echoes the author name for the activity comment currently being displayed
 *
 * @since 1.5.0
 *
 * @uses trs_get_activity_comment_name()
 */
function trs_activity_comment_name() {
	echo trs_get_activity_comment_name();
}

	/**
	 * Gets the author name for the activity comment currently being displayed
	 *
	 * The use of the trs_acomment_name filter is deprecated. Please use trs_activity_comment_name
	 *
	 * @since 1.5.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_acomment_name' hook
	 * @uses apply_filters() To call the 'trs_activity_comment_name' hook
	 *
	 * @return string $name The full name of the activity comment author
	 */
	function trs_get_activity_comment_name() {
		global $activities_template;

		$name = apply_filters( 'trs_acomment_name', $activities_template->activity->current_comment->user_fullname, $activities_template->activity->current_comment ); // backward compatibility

		return apply_filters( 'trs_activity_comment_name', $name );
	}

/**
 * Echoes the date_recorded of the activity comment currently being displayed
 *
 * @since 1.5.0
 *
 * @uses trs_get_activity_comment_date_recorded()
 */
function trs_activity_comment_date_recorded() {
	echo trs_get_activity_comment_date_recorded();
}

	/**
	 * Gets the date_recorded for the activity comment currently being displayed
	 *
	 * @since 1.5.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses trs_core_time_since()
	 * @uses apply_filters() To call the 'trs_activity_comment_date_recorded' hook
	 *
	 * @return string|bool $date_recorded Time since the activity was recorded, of the form "%s ago". False on failure
	 */
	function trs_get_activity_comment_date_recorded() {
		global $activities_template;

		if ( empty( $activities_template->activity->current_comment->date_recorded ) )
			return false;

		$date_recorded = trs_core_time_since( $activities_template->activity->current_comment->date_recorded );

		return apply_filters( 'trs_activity_comment_date_recorded', $date_recorded );
	}

/**
 * Echoes the 'delete' URL for the activity comment currently being displayed
 *
 * @since 1.5.0
 *
 * @uses trs_get_activity_comment_delete_link()
 */
function trs_activity_comment_delete_link() {
	echo trs_get_activity_comment_delete_link();
}

	/**
	 * Gets the 'delete' URL for the activity comment currently being displayed
	 *
	 * @since 1.5.0
	 *
	 * @global object $trs trendr global settings
	 * @uses trm_nonce_url()
	 * @uses trs_get_root_domain()
	 * @uses trs_get_activity_slug()
	 * @uses trs_get_activity_comment_id()
	 * @uses apply_filters() To call the 'trs_activity_comment_delete_link' hook
	 *
	 * @return string $link The nonced URL for deleting the current activity comment
	 */
	function trs_get_activity_comment_delete_link() {
		global $trs;

		$link = trm_nonce_url( trs_get_root_domain() . '/' . trs_get_activity_slug() . '/delete/?cid=' . trs_get_activity_comment_id(), 'trs_activity_delete_link' );

		return apply_filters( 'trs_activity_comment_delete_link', $link );
	}

/**
 * Echoes the content of the activity comment currently being displayed
 *
 * @since 1.5.0
 *
 * @uses trs_get_activity_comment_content()
 */
function trs_activity_comment_content() {
	echo trs_get_activity_comment_content();
}

	/**
	 * Gets the content of the activity comment currently being displayed
	 *
	 * The content is run through two filters. trs_get_activity_content will apply all filters
	 * applied to activity items in general. Use trs_activity_comment_content to modify the
	 * content of activity comments only.
	 *
	 * @since 1.5.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_content' hook
	 * @uses apply_filters() To call the 'trs_activity_comment_content' hook
	 *
	 * @return string $content The content of the current activity comment
	 */
	function trs_get_activity_comment_content() {
		global $activities_template;

		$content = apply_filters( 'trs_get_activity_content', $activities_template->activity->current_comment->content );

		return apply_filters( 'trs_activity_comment_content', $content );
	}

/**
 * Echoes the activity comment count
 *
 * @since 1.2.0
 *
 * @uses trs_activity_get_comment_count()
 */
function trs_activity_comment_count() {
	echo trs_activity_get_comment_count();
}

	/**
	 * Gets the content of the activity comment currently being displayed
	 *
	 * The content is run through two filters. trs_get_activity_content will apply all filters
	 * applied to activity items in general. Use trs_activity_comment_content to modify the
	 * content of activity comments only.
	 *
	 * @since 1.2.0
	 *
	 * @todo deprecate $args
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @global object $trs trendr global settings
	 * @uses trs_activity_recurse_comment_count()
	 * @uses apply_filters() To call the 'trs_activity_get_comment_count' hook
	 *
	 * @return int $count The activity comment count. Defaults to zero
	 */
	function trs_activity_get_comment_count( $args = '' ) {
		global $activities_template, $trs;

		if ( !isset( $activities_template->activity->children ) || !$activities_template->activity->children )
			return 0;

		$count = trs_activity_recurse_comment_count( $activities_template->activity );

		return apply_filters( 'trs_activity_get_comment_count', (int)$count );
	}

		/**
		 * Gets the content of the activity comment currently being displayed
		 *
		 * The content is run through two filters. trs_get_activity_content will apply all filters
		 * applied to activity items in general. Use trs_activity_comment_content to modify the
		 * content of activity comments only.
		 *
		 * @since 1.2.0
		 *
		 * @todo investigate why trs_activity_recurse_comment_count() is used while being declared
		 *
		 * @param object $comment Activity comments object
		 *
		 * @global object $activities_template {@link TRS_Activity_Template}
		 * @global object $trs trendr global settings
		 * @uses trs_activity_recurse_comment_count()
		 * @uses apply_filters() To call the 'trs_activity_get_comment_count' hook
		 *
		 * @return int $count The activity comment count.
		 */
		function trs_activity_recurse_comment_count( $comment, $count = 0 ) {
			global $activities_template, $trs;

			if ( !$comment->children )
				return $count;

			foreach ( (array)$comment->children as $comment ) {
				$count++;
				$count = trs_activity_recurse_comment_count( $comment, $count );
			}

			return $count;
		}

/**
 * Echoes the activity comment link
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_comment_link()
 */
function trs_activity_comment_link() {
	echo trs_get_activity_comment_link();
}

	/**
	 * Gets the activity comment link
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_comment_link' hook
	 *
	 * @return string The activity comment link
	 */
	function trs_get_activity_comment_link() {
		global $activities_template;
		return apply_filters( 'trs_get_activity_comment_link', '?ac=' . $activities_template->activity->id . '/#ac-form-' . $activities_template->activity->id );
	}

/**
 * Echoes the activity comment form no javascript display CSS
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_comment_form_nojs_display()
 */
function trs_activity_comment_form_nojs_display() {
	echo trs_get_activity_comment_form_nojs_display();
}

	/**
	 * Gets the activity comment form no javascript display CSS
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 *
	 * @return string|bool The activity comment form no javascript display CSS. False on failure
	 */
	function trs_get_activity_comment_form_nojs_display() {
		global $activities_template;
		if ( isset( $_GET['ac'] ) && $_GET['ac'] == $activities_template->activity->id . '/' )
			return 'style="display: block"';

		return false;
	}

/**
 * Echoes the activity comment form action
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_comment_form_action()
 */
function trs_activity_comment_form_action() {
	echo trs_get_activity_comment_form_action();
}

	/**
	 * Gets the activity comment form action
	 *
	 * @since 1.2.0
	 *
	 * @global object $trs trendr global settings
	 * @uses home_url()
	 * @uses trs_get_activity_root_slug()
	 * @uses apply_filters() To call the 'trs_get_activity_comment_form_action' hook
	 *
	 * @return string The activity comment form action
	 */
	function trs_get_activity_comment_form_action() {
		global $trs;

		return apply_filters( 'trs_get_activity_comment_form_action', home_url( trs_get_activity_root_slug() . '/reply/' ) );
	}

/**
 * Echoes the activity permalink id
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_permalink_id()
 */
function trs_activity_permalink_id() {
	echo trs_get_activity_permalink_id();
}

	/**
	 * Gets the activity permalink id
	 *
	 * @since 1.2.0
	 *
	 * @global object $trs trendr global settings
	 * @uses apply_filters() To call the 'trs_get_activity_permalink_id' hook
	 *
	 * @return string The activity permalink id
	 */
	function trs_get_activity_permalink_id() {
		global $trs;

		return apply_filters( 'trs_get_activity_permalink_id', $trs->current_action );
	}

/**
 * Echoes the activity thread permalink
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_permalink_id()
 */
function trs_activity_thread_permalink() {
	echo trs_get_activity_thread_permalink();
}

	/**
	 * Gets the activity thread permalink
	 *
	 * @since 1.2.0
	 *
	 * @global object $trs trendr global settings
	 * @uses trs_activity_get_permalink()
	 * @uses apply_filters() To call the 'trs_get_activity_thread_permalink' hook
	 *
	 * @return string $link The activity thread permalink
	 */
	function trs_get_activity_thread_permalink() {
		global $trs, $activities_template;

		$link = trs_activity_get_permalink( $activities_template->activity->id, $activities_template->activity );

	 	return apply_filters( 'trs_get_activity_thread_permalink', $link );
	}

/**
 * Echoes the activity favorite link
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_favorite_link()
 */
function trs_activity_favorite_link() {
	echo trs_get_activity_favorite_link();
}

	/**
	 * Gets the activity favorite link
	 *
	 * @since 1.2.0
	 *
	 * @global object $trs trendr global settings
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses trm_nonce_url()
	 * @uses home_url()
	 * @uses trs_get_activity_root_slug()
	 * @uses apply_filters() To call the 'trs_get_activity_favorite_link' hook
	 *
	 * @return string The activity favorite link
	 */
	function trs_get_activity_favorite_link() {
		global $trs, $activities_template;
		return apply_filters( 'trs_get_activity_favorite_link', trm_nonce_url( home_url( trs_get_activity_root_slug() . '/favorite/' . $activities_template->activity->id . '/' ), 'mark_favorite' ) );
	}

/**
 * Echoes the activity unfavorite link
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_unfavorite_link()
 */
function trs_activity_unfavorite_link() {
	echo trs_get_activity_unfavorite_link();
}

	/**
	 * Gets the activity unfavorite link
	 *
	 * @since 1.2.0
	 *
	 * @global object $trs trendr global settings
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses trm_nonce_url()
	 * @uses home_url()
	 * @uses trs_get_activity_root_slug()
	 * @uses apply_filters() To call the 'trs_get_activity_unfavorite_link' hook
	 *
	 * @return string The activity unfavorite link
	 */
	function trs_get_activity_unfavorite_link() {
		global $trs, $activities_template;
		return apply_filters( 'trs_get_activity_unfavorite_link', trm_nonce_url( home_url( trs_get_activity_root_slug() . '/unfavorite/' . $activities_template->activity->id . '/' ), 'unmark_favorite' ) );
	}

/**
 * Echoes the activity CSS class
 *
 * @since 1.0.0
 *
 * @uses trs_get_activity_css_class()
 */
function trs_activity_css_class() {
	echo trs_get_activity_css_class();
}

	/**
	 * Gets the activity CSS class
	 *
	 * @since 1.0.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_activity_mini_activity_types' hook
	 * @uses trs_activity_get_comment_count()
	 * @uses trs_activity_can_comment()
	 * @uses apply_filters() To call the 'trs_get_activity_css_class' hook
	 *
	 * @return string The activity css class
	 */
	function trs_get_activity_css_class() {
		global $activities_template;

		$mini_activity_actions = apply_filters( 'trs_activity_mini_activity_types', array(
			'friendship_accepted',
			'friendship_created',
			'new_blog',
			'joined_group',
			'created_group',
			'new_member'
		) );

		$class = '';
		if ( in_array( $activities_template->activity->type, (array)$mini_activity_actions ) || empty( $activities_template->activity->content ) )
			$class = ' mini';

		if ( trs_activity_get_comment_count() && trs_activity_can_comment() )
			$class .= ' has-comments';

		return apply_filters( 'trs_get_activity_css_class', $activities_template->activity->component . ' ' . $activities_template->activity->type . $class );
	}

/**
 * Display the activity delete link.
 *
 * @since 1.1.0
 *
 * @uses trs_get_activity_delete_link()
 */
function trs_activity_delete_link() {
	echo trs_get_activity_delete_link();
}

	/**
	 * Return the activity delete link.
	 *
	 * @since 1.1.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @global object $trs trendr global settings
	 * @uses trs_get_root_domain()
	 * @uses trs_get_activity_root_slug()
	 * @uses trs_is_activity_component()
	 * @uses trs_current_action()
	 * @uses add_query_arg()
	 * @uses trm_get_referer()
	 * @uses trm_nonce_url()
	 * @uses apply_filters() To call the 'trs_get_activity_delete_link' hook
	 *
	 * @return string $link Activity delete link. Contains $redirect_to arg if on single activity page.
	 */
	function trs_get_activity_delete_link() {
		global $activities_template, $trs;

		$url   = trs_get_root_domain() . '/' . trs_get_activity_root_slug() . '/delete/' . $activities_template->activity->id;
		$class = 'delete-activity';

		// Determine if we're on a single activity page, and customize accordingly
		if ( trs_is_activity_component() && is_numeric( trs_current_action() ) ) {
			$url   = add_query_arg( array( 'redirect_to' => trm_get_referer() ), $url );
			$class = 'delete-activity-single';
		}

		$link = '<a href="' . trm_nonce_url( $url, 'trs_activity_delete_link' ) . '" class="button item-button trs-secondary-action ' . $class . ' confirm" rel="nofollow">' . __( 'Delete', 'trendr' ) . '</a>';
		return apply_filters( 'trs_get_activity_delete_link', $link );
	}

/**
 * Display the activity latest update link.
 *
 * @since 1.2.0
 *
 * @param int $user_id Defaults to 0
 *
 * @uses trs_get_activity_latest_update()
 */
function trs_activity_latest_update( $user_id = 0 ) {
	echo trs_get_activity_latest_update( $user_id );
}

	/**
	 * Return the activity latest update link.
	 *
	 * @since 1.2.0
	 *
	 * @param int $user_id Defaults to 0
	 *
	 * @global object $trs trendr global settings
	 * @uses trs_core_is_user_spammer()
	 * @uses trs_core_is_user_deleted()
	 * @uses trs_get_user_meta()
	 * @uses apply_filters() To call the 'trs_get_activity_latest_update_excerpt' hook
	 * @uses trs_create_excerpt()
	 * @uses trs_get_root_domain()
	 * @uses trs_get_activity_root_slug()
	 * @uses apply_filters() To call the 'trs_get_activity_latest_update' hook
	 *
	 * @return string|bool $latest_update The activity latest update link. False on failure
	 */
	function trs_get_activity_latest_update( $user_id = 0 ) {
		global $trs;

		if ( !$user_id )
			$user_id = $trs->displayed_user->id;

		if ( trs_core_is_user_spammer( $user_id ) || trs_core_is_user_deleted( $user_id ) )
			return false;

		if ( !$update = trs_get_user_meta( $user_id, 'trs_latest_update', true ) )
			return false;

		$latest_update = apply_filters( 'trs_get_activity_latest_update_excerpt', trim( strip_tags( trs_create_excerpt( $update['content'], 358 ) ) ) );
		$latest_update .= ' <a href="' . trs_get_root_domain() . '/' . trs_get_activity_root_slug() . '/p/' . $update['id'] . '/"> ' . __( 'View', 'trendr' ) . '</a>';

		return apply_filters( 'trs_get_activity_latest_update', $latest_update  );
	}

/**
 * Display the activity filter links.
 *
 * @since 1.1.0
 *
 * @param array $args Defaults to false
 *
 * @uses trs_get_activity_filter_links()
 */
function trs_activity_filter_links( $args = false ) {
	echo trs_get_activity_filter_links( $args );
}

	/**
	 * Return the activity filter links.
	 *
	 * @since 1.1.0
	 *
	 * @param array $args Defaults to false
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @global object $trs trendr global settings
	 * @uses trm_parse_args()
	 * @uses TRS_Activity_Activity::get_recorded_components() {@link TRS_Activity_Activity}
	 * @uses esc_attr()
	 * @uses add_query_arg()
	 * @uses remove_query_arg()
	 * @uses apply_filters() To call the 'trs_get_activity_filter_link_href' hook
	 * @uses apply_filters() To call the 'trs_get_activity_filter_links' hook
	 *
	 * @return string|bool $component_links The activity filter links. False on failure
	 */
	function trs_get_activity_filter_links( $args = false ) {
		global $activities_template, $trs;

		$defaults = array(
			'style' => 'list'
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		// Fetch the names of components that have activity recorded in the DB
		$components = TRS_Activity_Activity::get_recorded_components();

		if ( !$components )
			return false;

		foreach ( (array) $components as $component ) {
			/* Skip the activity comment filter */
			if ( 'activity' == $component )
				continue;

			if ( isset( $_GET['afilter'] ) && $component == $_GET['afilter'] )
				$selected = ' class="selected"';
			else
				unset($selected);

			$component = esc_attr( $component );

			switch ( $style ) {
				case 'list':
					$tag = 'li';
					$before = '<li id="afilter-' . $component . '"' . $selected . '>';
					$after = '</li>';
				break;
				case 'paragraph':
					$tag = 'p';
					$before = '<p id="afilter-' . $component . '"' . $selected . '>';
					$after = '</p>';
				break;
				case 'span':
					$tag = 'span';
					$before = '<span id="afilter-' . $component . '"' . $selected . '>';
					$after = '</span>';
				break;
			}

			$link = add_query_arg( 'afilter', $component );
			$link = remove_query_arg( 'acpage' , $link );

			$link = apply_filters( 'trs_get_activity_filter_link_href', $link, $component );

			// Make sure all core internal component names are translatable
			$translatable_components = array( __( 'xprofile', 'trendr'), __( 'friends', 'trendr' ), __( 'groups', 'trendr' ), __( 'status', 'trendr' ), __( 'sites', 'trendr' ) );

			$component_links[] = $before . '<a href="' . esc_attr( $link ) . '">' . ucwords( __( $component, 'trendr' ) ) . '</a>' . $after;
		}

		$link = remove_query_arg( 'afilter' , $link );

		if ( isset( $_GET['afilter'] ) )
			$component_links[] = '<' . $tag . ' id="afilter-clear"><a href="' . esc_attr( $link ) . '">' . __( 'Clear Filter', 'trendr' ) . '</a></' . $tag . '>';

 		return apply_filters( 'trs_get_activity_filter_links', implode( "\n", $component_links ) );
	}

/**
 * Determine if a comment can be made on an activity item
 *
 * @since 1.2.0
 *
 * @global object $activities_template {@link TRS_Activity_Template}
 * @global object $trs trendr global settings
 * @uses trs_get_activity_action_name()
 * @uses apply_filters() To call the 'trs_activity_can_comment' hook
 *
 * @return bool $can_comment Defaults to true
 */
function trs_activity_can_comment() {
	global $activities_template, $trs;

	$can_comment = true;

	if ( false === $activities_template->disable_blogforum_replies || (int)$activities_template->disable_blogforum_replies ) {
		if ( 'new_blog_post' == trs_get_activity_action_name() || 'new_blog_comment' == trs_get_activity_action_name() || 'new_forum_topic' == trs_get_activity_action_name() || 'new_forum_post' == trs_get_activity_action_name() )
			$can_comment = false;
	}

	if ( 'activity_comment' == trs_get_activity_action_name() )
		$can_comment = false;

	return apply_filters( 'trs_activity_can_comment', $can_comment );
}

/**
 * Determine if a comment can be made on an activity reply item
 *
 * @since 1.5.0
 *
 * @param object $comment Activity comment
 *
 * @uses apply_filters() To call the 'trs_activity_can_comment_reply' hook
 *
 * @return bool $can_comment Defaults to true
 */
function trs_activity_can_comment_reply( $comment ) {
	$can_comment = true;

	return apply_filters( 'trs_activity_can_comment_reply', $can_comment, $comment );
}

/**
 * Determine if an favorites are allowed
 *
 * @since 1.5.0
 *
 * @uses apply_filters() To call the 'trs_activity_can_favorite' hook
 *
 * @return bool $can_favorite Defaults to true
 */
function trs_activity_can_favorite() {
	$can_favorite = true;

	return apply_filters( 'trs_activity_can_favorite', $can_favorite );
}

/**
 * Echoes the total favorite count for a specified user
 *
 * @since 1.2.0
 *
 * @param int $user_id Defaults to 0
 *
 * @uses trs_get_total_favorite_count_for_user()
 */
function trs_total_favorite_count_for_user( $user_id = 0 ) {
	echo trs_get_total_favorite_count_for_user( $user_id );
}

	/**
	 * Returns the total favorite count for a specified user
	 *
	 * @since 1.2.0
	 *
	 * @param int $user_id Defaults to 0
	 *
	 * @uses trs_activity_total_favorites_for_user()
	 * @uses apply_filters() To call the 'trs_get_total_favorite_count_for_user' hook
	 *
	 * @return int The total favorite count for a specified user
	 */
	function trs_get_total_favorite_count_for_user( $user_id = 0 ) {
		return apply_filters( 'trs_get_total_favorite_count_for_user', trs_activity_total_favorites_for_user( $user_id ) );
	}

/**
 * Echoes the total mention count for a specified user
 *
 * @since 1.2.0
 *
 * @param int $user_id Defaults to 0
 *
 * @uses trs_get_total_favorite_count_for_user()
 */
function trs_total_mention_count_for_user( $user_id = 0 ) {
	echo trs_get_total_favorite_count_for_user( $user_id );
}

	/**
	 * Returns the total mention count for a specified user
	 *
	 * @since 1.2.0
	 *
	 * @todo remove unnecessary $trs global
	 *
	 * @param int $user_id Defaults to 0
	 *
	 * @uses trs_get_user_meta()
	 * @uses apply_filters() To call the 'trs_get_total_mention_count_for_user' hook
	 *
	 * @return int The total mention count for a specified user
	 */
	function trs_get_total_mention_count_for_user( $user_id = 0 ) {
		global $trs;

		return apply_filters( 'trs_get_total_mention_count_for_user', trs_get_user_meta( $user_id, 'trs_new_mention_count', true ) );
	}

/**
 * Echoes the public message link for displayed user
 *
 * @since 1.2.0
 *
 * @uses trs_get_send_public_message_link()
 */
function trs_send_public_message_link() {
	echo trs_get_send_public_message_link();
}

	/**
	 * Returns the public message link for displayed user
	 *
	 * @since 1.2.0
	 *
	 * @global object $trs trendr global settings
	 * @uses trs_is_my_profile()
	 * @uses is_user_logged_in()
	 * @uses trm_nonce_url()
	 * @uses trs_loggedin_user_domain()
	 * @uses trs_get_activity_slug()
	 * @uses trs_core_get_username()
	 * @uses apply_filters() To call the 'trs_get_send_public_message_link' hook
	 *
	 * @return string The public message link for displayed user
	 */
	function trs_get_send_public_message_link() {
		global $trs;

		if ( trs_is_my_profile() || !is_user_logged_in() )
			return false;
     
		return apply_filters( 'trs_get_send_public_message_link', trm_nonce_url( trs_loggedin_user_domain() . trs_get_activity_slug() . '/?r=' . trs_core_get_username( $trs->displayed_user->id, $trs->displayed_user->userdata->user_nicename, $trs->displayed_user->userdata->user_login ) ) );
	}

/**
 * Echoes the mentioned user display name
 *
 * @since 1.2.0
 *
 * @param int|string User id or username
 *
 * @uses trs_get_mentioned_user_display_name()
 */
function trs_mentioned_user_display_name( $user_id_or_username ) {
	echo trs_get_mentioned_user_display_name( $user_id_or_username );
}

	/**
	 * Returns the mentioned user display name
	 *
	 * @since 1.2.0
	 *
	 * @param int|string User id or username
	 *
	 * @uses trs_core_get_user_displayname()
	 * @uses apply_filters() To call the 'trs_get_mentioned_user_display_name' hook
	 *
	 * @return string The mentioned user display name
	 */
	function trs_get_mentioned_user_display_name( $user_id_or_username ) {
		if ( !$name = trs_core_get_user_displayname( $user_id_or_username ) )
			$name = __( 'a user', 'trendr' );

		return apply_filters( 'trs_get_mentioned_user_display_name', $name, $user_id_or_username );
	}

/**
 * Output button for sending a public message
 *
 * @since 1.2.0
 *
 * @param array $args Optional
 *
 * @uses trs_get_send_public_message_button()
 */
function trs_send_public_message_button( $args = '' ) {
	echo trs_get_send_public_message_button( $args );
}

	/**
	 * Return button for sending a public message
	 *
	 * @since 1.2.0
	 *
	 * @param array $args Optional
	 *
	 * @uses trs_get_send_public_message_link()
	 * @uses trm_parse_args()
	 * @uses trs_get_button()
	 * @uses apply_filters() To call the 'trs_get_send_public_message_button' hook
	 *
	 * @return string The button for sending a public message
	 */
	function trs_get_send_public_message_button( $args = '' ) {
		$defaults = array(
			'id'                => 'public_message',
			'component'         => 'activity',
			'must_be_logged_in' => true,
			'block_self'        => true,
			'wrapper_id'        => 'post-memo',
			'link_href'         => trs_get_send_public_message_link(),
			'link_title'        => __( 'Send a public message on your activity stream.', 'trendr' ),
			'link_text'         => __( 'Public Message', 'trendr' ),
			'link_class'        => 'post-knob memo'
		);

		$button = trm_parse_args( $args, $defaults );

		// Filter and return the HTML button
		return trs_get_button( apply_filters( 'trs_get_send_public_message_button', $button ) );
	}

/**
 * Outputs the activity post form action
 *
 * @since 1.2.0
 *
 * @uses trs_get_activity_post_form_action()
 */
function trs_activity_post_form_action() {
	echo trs_get_activity_post_form_action();
}

	/**
	 * Returns the activity post form action
	 *
	 * @since 1.2.0
	 *
	 * @uses home_url()
	 * @uses trs_get_activity_root_slug()
	 * @uses apply_filters() To call the 'trs_get_activity_post_form_action' hook
	 *
	 * @return string The activity post form action
	 */
	function trs_get_activity_post_form_action() {
		return apply_filters( 'trs_get_activity_post_form_action', home_url( trs_get_activity_root_slug() . '/post/' ) );
	}

/* RSS Feed Template Tags ****************************************************/

/**
 * Outputs the sitewide activity feed link
 *
 * @since 1.0.0
 *
 * @uses trs_get_sitewide_activity_feed_link()
 */
function trs_sitewide_activity_feed_link() {
	echo trs_get_sitewide_activity_feed_link();
}

	/**
	 * Returns the sitewide activity feed link
	 *
	 * @since 1.0.0
	 *
	 * @uses home_url()
	 * @uses trs_get_activity_root_slug()
	 * @uses apply_filters() To call the 'trs_get_sitewide_activity_feed_link' hook
	 *
	 * @return string The sitewide activity feed link
	 */
	function trs_get_sitewide_activity_feed_link() {
		return apply_filters( 'trs_get_sitewide_activity_feed_link', trs_get_root_domain() . '/' . trs_get_activity_root_slug() . '/feed/' );
	}

/**
 * Outputs the member activity feed link
 *
 * @since 1.2.0
 *
 * @uses trs_get_member_activity_feed_link()
 */
function trs_member_activity_feed_link() {
	echo trs_get_member_activity_feed_link();
}

/**
 * Outputs the member activity feed link
 *
 * @since 1.0.0
 * @deprecated 1.2.0
 *
 * @todo properly deprecated in favor of trs_member_activity_feed_link()
 *
 * @uses trs_get_member_activity_feed_link()
 */
function trs_activities_member_rss_link() { echo trs_get_member_activity_feed_link(); }

	/**
	 * Returns the member activity feed link
	 *
	 * @since 1.2.0
	 *
	 * @uses trs_is_profile_component()
	 * @uses trs_is_current_action()
	 * @uses trs_displayed_user_domain()
	 * @uses trs_get_activity_slug()
	 * @uses trs_is_active()
	 * @uses trs_get_friends_slug()
	 * @uses trs_get_groups_slug()
	 * @uses apply_filters() To call the 'trs_get_activities_member_rss_link' hook
	 *
	 * @return string $link The member activity feed link
	 */
	function trs_get_member_activity_feed_link() {
		global $trs;

		if ( trs_is_profile_component() || trs_is_current_action( 'just-me' ) )
			$link = trs_displayed_user_domain() . trs_get_activity_slug() . '/feed/';
		elseif ( trs_is_active( 'friends' ) && trs_is_current_action( trs_get_friends_slug() ) )
			$link = trs_displayed_user_domain() . trs_get_activity_slug() . '/' . trs_get_friends_slug() . '/feed/';
		elseif ( trs_is_active( 'groups'  ) && trs_is_current_action( trs_get_groups_slug()  ) )
			$link = trs_displayed_user_domain() . trs_get_activity_slug() . '/' . trs_get_groups_slug() . '/feed/';
		elseif ( 'favorites' == $trs->current_action )
			$link = trs_displayed_user_domain() . trs_get_activity_slug() . '/favorites/feed/';
		elseif ( 'mentions' == $trs->current_action )
			$link = trs_displayed_user_domain() . trs_get_activity_slug() . '/mentions/feed/';
		else
			$link = '';

		return apply_filters( 'trs_get_activities_member_rss_link', $link );
	}

	/**
	 * Returns the member activity feed link
	 *
	 * @since 1.0.0
	 * @deprecated 1.2.0
	 *
	 * @todo properly deprecated in favor of trs_get_member_activity_feed_link()
	 *
	 * @uses trs_get_member_activity_feed_link()
	 *
	 * @return string The member activity feed link
	 */
	function trs_get_activities_member_rss_link() { return trs_get_member_activity_feed_link(); }


/** Template tags for RSS feed output ****************************************/

/**
 * Outputs the activity feed item guid
 *
 * @since 1.0.0
 *
 * @uses trs_activity_feed_item_guid()
 */
function trs_activity_feed_item_guid() {
	echo trs_get_activity_feed_item_guid();
}

	/**
	 * Returns the activity feed item guid
	 *
	 * @since 1.2.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_feed_item_guid' hook
	 *
	 * @return string The activity feed item guid
	 */
	function trs_get_activity_feed_item_guid() {
		global $activities_template;

		return apply_filters( 'trs_get_activity_feed_item_guid', md5( $activities_template->activity->date_recorded . '-' . $activities_template->activity->content ) );
	}

/**
 * Outputs the activity feed item title
 *
 * @since 1.0.0
 *
 * @uses trs_get_activity_feed_item_title()
 */
function trs_activity_feed_item_title() {
	echo trs_get_activity_feed_item_title();
}

	/**
	 * Returns the activity feed item title
	 *
	 * @since 1.0.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses ent2ncr()
	 * @uses convert_chars()
	 * @uses trs_create_excerpt()
	 * @uses apply_filters() To call the 'trs_get_activity_feed_item_title' hook
	 *
	 * @return string $title The activity feed item title
	 */
	function trs_get_activity_feed_item_title() {
		global $activities_template;

		if ( !empty( $activities_template->activity->action ) )
			$content = $activities_template->activity->action;
		else
			$content = $activities_template->activity->content;

		$content = explode( '<span', $content );
		$title = strip_tags( ent2ncr( trim( convert_chars( $content[0] ) ) ) );

		if ( ':' == substr( $title, -1 ) )
			$title = substr( $title, 0, -1 );

		if ( 'activity_update' == $activities_template->activity->type )
			$title .= ': ' . strip_tags( ent2ncr( trim( convert_chars( trs_create_excerpt( $activities_template->activity->content, 70, array( 'ending' => " [&#133;]" ) ) ) ) ) );

		return apply_filters( 'trs_get_activity_feed_item_title', $title );
	}

/**
 * Outputs the activity feed item link
 *
 * @since 1.0.0
 *
 * @uses trs_get_activity_feed_item_link()
 */
function trs_activity_feed_item_link() {
	echo trs_get_activity_feed_item_link();
}

	/**
	 * Returns the activity feed item link
	 *
	 * @since 1.0.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_feed_item_link' hook
	 *
	 * @return string The activity feed item link
	 */
	function trs_get_activity_feed_item_link() {
		global $activities_template;

		return apply_filters( 'trs_get_activity_feed_item_link', $activities_template->activity->primary_link );
	}

/**
 * Outputs the activity feed item date
 *
 * @since 1.0.0
 *
 * @uses trs_get_activity_feed_item_date()
 */
function trs_activity_feed_item_date() {
	echo trs_get_activity_feed_item_date();
}

	/**
	 * Returns the activity feed item date
	 *
	 * @since 1.0.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses apply_filters() To call the 'trs_get_activity_feed_item_date' hook
	 *
	 * @return string The activity feed item date
	 */
	function trs_get_activity_feed_item_date() {
		global $activities_template;

		return apply_filters( 'trs_get_activity_feed_item_date', $activities_template->activity->date_recorded );
	}

/**
 * Outputs the activity feed item description
 *
 * @since 1.0.0
 *
 * @uses trs_get_activity_feed_item_description()
 */
function trs_activity_feed_item_description() {
	echo trs_get_activity_feed_item_description();
}

	/**
	 * Returns the activity feed item description
	 *
	 * @since 1.0.0
	 *
	 * @global object $activities_template {@link TRS_Activity_Template}
	 * @uses ent2ncr()
	 * @uses convert_chars()
	 * @uses apply_filters() To call the 'trs_get_activity_feed_item_description' hook
	 *
	 * @return string The activity feed item description
	 */
	function trs_get_activity_feed_item_description() {
		global $activities_template;

		if ( empty( $activities_template->activity->action ) )
			$content = $activities_template->activity->content;
		else
			$content = $activities_template->activity->action . ' ' . $activities_template->activity->content;

		return apply_filters( 'trs_get_activity_feed_item_description', ent2ncr( convert_chars( str_replace( '%s', '', $content ) ) ) );
	}

/**
 * Template tag so we can hook activity feed to <head>
 *
 * @since 1.5.0
 *
 * @uses bloginfo()
 * @uses trs_sitewide_activity_feed_link()
 */
function trs_activity_sitewide_feed() {
?>

	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo( 'name' ) ?> | <?php _e( 'Site Wide Activity RSS Feed', 'trendr' ) ?>" href="<?php trs_sitewide_activity_feed_link() ?>" />

<?php
}
add_action( 'trs_head', 'trs_activity_sitewide_feed' );

?>