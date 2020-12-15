<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * TRS_Core_User class can be used by any component. It will fetch useful
 * details for any user when provided with a user_id.
 *
 * Example:
 *    $user = new TRS_Core_User( $user_id );
 *    $user_portrait = $user->portrait;
 *	  $user_email = $user->email;
 *    $user_status = $user->status;
 *    etc.
 *
 * @package trendr Core
 */
class TRS_Core_User {

	/**
	 * ID of the user which the object relates to.
	 *
	 * @var integer
	 */
	var $id;

	/**
	 * The URL to the full size of the portrait for the user.
	 *
	 * @var string
	 */
	var $portrait;

	/**
	 * The URL to the thumb size of the portrait for the user.
	 *
	 * @var string
	 */
	var $portrait_thumb;

	/**
	 * The URL to the mini size of the portrait for the user.
	 *
	 * @var string
	 */
	var $portrait_mini;

	/**
	 * The full name of the user
	 *
	 * @var string
	 */
	var $fullname;

	/**
	 * The email for the user.
	 *
	 * @var string
	 */
	var $email;

	/**
	 * The absolute url for the user's profile.
	 *
	 * @var string
	 */
	var $user_url;

	/**
	 * The HTML for the user link, with the link text being the user's full name.
	 *
	 * @var string
	 */
	var $user_link;

	/**
	 * Contains a formatted string when the last time the user was active.
	 *
	 * Example: "active 2 hours and 50 minutes ago"
	 *
	 * @var string
	 */
	var $last_active;

	/* Extras */

	/**
	 * The total number of "Friends" the user has on site.
	 *
	 * @var integer
	 */
	var $total_friends;

	/**
	 * The total number of blog posts posted by the user
	 *
	 * @var integer
	 * @deprecated No longer used
	 */
	var $total_blogs;

	/**
	 * The total number of groups the user is a part of.
	 *
	 * Example: "1 group", "2 groups"
	 *
	 * @var string
	 */
	var $total_groups;

	/**
	 * PHP4 constructor.
	 *
	 * @see TRS_Core_User::__construct()
	 */
	function trs_core_user( $user_id, $populate_extras = false ) {
		$this->__construct( $user_id, $populate_extras );
	}

	/**
	 * Class constructor.
	 *
	 * @param integer $user_id The ID for the user
	 * @param boolean $populate_extras Whether to fetch extra information such as group/friendship counts or not.
	 */
	function __construct( $user_id, $populate_extras = false ) {
		if ( $user_id ) {
			$this->id = $user_id;
			$this->populate();

			if ( $populate_extras )
				$this->populate_extras();
		}
	}

	/**
	 * Populate the instantiated class with data based on the User ID provided.
	 *
	 * @global object $trs Global trendr settings object
	 * @uses trs_core_get_userurl() Returns the URL with no HTML markup for a user based on their user id
	 * @uses trs_core_get_userlink() Returns a HTML formatted link for a user with the user's full name as the link text
	 * @uses trs_core_get_user_email() Returns the email address for the user based on user ID
	 * @uses trs_get_user_meta() TRS function returns the value of passed usermeta name from usermeta table
	 * @uses trs_core_fetch_portrait() Returns HTML formatted portrait for a user
	 * @uses trs_profile_last_updated_date() Returns the last updated date for a user.
	 */
	function populate() {
		global $trs;

		if ( trs_is_active( 'xprofile' ) )
			$this->profile_data = $this->get_profile_data();

		if ( !empty( $this->profile_data ) ) {
			$full_name_field_name = trs_xprofile_fullname_field_name();

			$this->user_url  = trs_core_get_user_domain( $this->id, $this->profile_data['user_nicename'], $this->profile_data['user_login'] );
			$this->fullname  = esc_attr( $this->profile_data[$full_name_field_name]['field_data'] );
			$this->user_link = "<a href='{$this->user_url}' title='{$this->fullname}'>{$this->fullname}</a>";
			$this->email     = esc_attr( $this->profile_data['user_email'] );
		} else {
			$this->user_url  = trs_core_get_user_domain( $this->id );
			$this->user_link = trs_core_get_userlink( $this->id );
			$this->fullname  = esc_attr( trs_core_get_user_displayname( $this->id ) );
			$this->email     = esc_attr( trs_core_get_user_email( $this->id ) );
		}

		// Cache a few things that are fetched often
		trm_cache_set( 'trs_user_fullname_' . $this->id, $this->fullname, 'trs' );
		trm_cache_set( 'trs_user_email_' . $this->id, $this->email, 'trs' );
		trm_cache_set( 'trs_user_url_' . $this->id, $this->user_url, 'trs' );

		$this->portrait       = trs_core_fetch_portrait( array( 'item_id' => $this->id, 'type' => 'full'  ) );
		$this->portrait_thumb = trs_core_fetch_portrait( array( 'item_id' => $this->id, 'type' => 'thumb' ) );
		$this->portrait_mini  = trs_core_fetch_portrait( array( 'item_id' => $this->id, 'type' => 'thumb', 'width' => 30, 'height' => 30 ) );
		$this->last_active  = trs_core_get_last_activity( trs_get_user_meta( $this->id, 'last_activity', true ), __( 'active %s', 'trendr' ) );
	}

	/**
	 * Populates extra fields such as group and friendship counts.
	 *
	 * @global object $trs Global trendr settings object
	 */
	function populate_extras() {
		global $trs;

		if ( trs_is_active( 'friends' ) )
			$this->total_friends = TRS_Friends_Friendship::total_friend_count( $this->id );

		if ( trs_is_active( 'groups' ) ) {
			$this->total_groups = TRS_Groups_Member::total_group_count( $this->id );
			$this->total_groups = sprintf( _n( '%d group', '%d groups', $this->total_groups ), $this->total_groups );
		}
	}

	function get_profile_data() {
		return TRS_XProfile_ProfileData::get_all_for_user( $this->id );
	}

	/** Static Functions ******************************************************/

	function get_users( $type, $limit = 0, $page = 1, $user_id = 0, $include = false, $search_terms = false, $populate_extras = true, $exclude = false, $meta_key = false, $meta_value = false ) {
		global $trmdb, $trs;

		$sql = array();

		$sql['select_main'] = "SELECT DISTINCT u.ID as id, u.user_registered, u.user_nicename, u.user_login, u.display_name, u.user_email";

		if ( 'active' == $type || 'online' == $type || 'newest' == $type  )
			$sql['select_active'] = ", um.meta_value as last_activity";

		if ( 'popular' == $type )
			$sql['select_popular'] = ", um.meta_value as total_friend_count";

		if ( 'alphabetical' == $type )
			$sql['select_alpha'] = ", pd.value as fullname";

		if ( $meta_key ) {
			$sql['select_meta'] = ", umm.meta_key";

			if ( $meta_value )
				$sql['select_meta'] .= ", umm.meta_value";
		}

		$sql['from'] = "FROM $trmdb->users u LEFT JOIN $trmdb->usermeta um ON um.user_id = u.ID";

		// We search against xprofile fields, so we must join the table
		if ( $search_terms && trs_is_active( 'xprofile' ) )
			$sql['join_profiledata_search'] = "LEFT JOIN {$trs->profile->table_name_data} spd ON u.ID = spd.user_id";

		// Alphabetical sorting is done by the xprofile Full Name field
		if ( 'alphabetical' == $type )
			$sql['join_profiledata_alpha'] = "LEFT JOIN {$trs->profile->table_name_data} pd ON u.ID = pd.user_id";

		if ( $meta_key )
			$sql['join_meta'] = "LEFT JOIN {$trmdb->usermeta} umm ON umm.user_id = u.ID";

		$sql['where'] = 'WHERE ' . trs_core_get_status_sql( 'u.' );

		if ( 'active' == $type || 'online' == $type || 'newest' == $type )
			$sql['where_active'] = $trmdb->prepare( "AND um.meta_key = %s", trs_get_user_meta_key( 'last_activity' ) );

		if ( 'popular' == $type )
			$sql['where_popular'] = $trmdb->prepare( "AND um.meta_key = %s", trs_get_user_meta_key( 'total_friend_count' ) );

		if ( 'online' == $type )
			$sql['where_online'] = "AND DATE_ADD( um.meta_value, INTERVAL 5 MINUTE ) >= UTC_TIMESTAMP()";

		if ( 'alphabetical' == $type )
			$sql['where_alpha'] = "AND pd.field_id = 1";

		if ( !empty( $exclude ) )
			$sql['where_exclude'] = "AND u.ID NOT IN ({$exclude})";

		if ( $include ) {
			if ( is_array( $include ) )
				$uids = $trmdb->escape( implode( ',', (array)$include ) );
			else
				$uids = $trmdb->escape( $include );

			if ( !empty( $uids ) )
				$sql['where_users'] = "AND u.ID IN ({$uids})";
		}

		else if ( $user_id && trs_is_active( 'friends' ) ) {
			$friend_ids = friends_get_friend_user_ids( $user_id );
			$friend_ids = $trmdb->escape( implode( ',', (array)$friend_ids ) );

			if ( !empty( $friend_ids ) )
				$sql['where_friends'] = "AND u.ID IN ({$friend_ids})";

			// User has no friends, return false since there will be no users to fetch.
			else
				return false;

		}

		if ( $search_terms && trs_is_active( 'xprofile' ) ) {
			$search_terms             = like_escape( $trmdb->escape( $search_terms ) );
			$sql['where_searchterms'] = "AND spd.value LIKE '%%$search_terms%%'";
		}

		if ( $meta_key ) {
			$sql['where_meta'] = $trmdb->prepare( " AND umm.meta_key = %s", $meta_key );

			// If a meta value is provided, match it
			if ( $meta_value ) {
				$sql['where_meta'] .= $trmdb->prepare( " AND umm.meta_value = %s", $meta_value );
			}
		}

		switch ( $type ) {
			case 'active': case 'online': default:
				$sql[] = "ORDER BY um.meta_value DESC";
				break;
			case 'newest':
				$sql[] = "ORDER BY u.ID DESC";
				break;
			case 'alphabetical':
				$sql[] = "ORDER BY pd.value ASC";
				break;
			case 'random':
				$sql[] = "ORDER BY rand()";
				break;
			case 'popular':
				$sql[] = "ORDER BY CONVERT(um.meta_value, SIGNED) DESC";
				break;
		}

		if ( $limit && $page )
			$sql['pagination'] = $trmdb->prepare( "LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		// Get paginated results
		$paged_users_sql = apply_filters( 'trs_core_get_paged_users_sql', join( ' ', (array)$sql ), $sql );
		$paged_users     = $trmdb->get_results( $paged_users_sql );

		// Re-jig the SQL so we can get the total user count
		unset( $sql['select_main'] );

		if ( !empty( $sql['select_active'] ) )
			unset( $sql['select_active'] );

		if ( !empty( $sql['select_popular'] ) )
			unset( $sql['select_popular'] );

		if ( !empty( $sql['select_alpha'] ) )
			unset( $sql['select_alpha'] );

		if ( !empty( $sql['pagination'] ) )
			unset( $sql['pagination'] );

		array_unshift( $sql, "SELECT COUNT(DISTINCT u.ID)" );

		// Get total user results
		$total_users_sql = apply_filters( 'trs_core_get_total_users_sql', join( ' ', (array)$sql ), $sql );
		$total_users     = $trmdb->get_var( $total_users_sql );

		/***
		 * Lets fetch some other useful data in a separate queries, this will be faster than querying the data for every user in a list.
		 * We can't add these to the main query above since only users who have this information will be returned (since the much of the data is in usermeta and won't support any type of directional join)
		 */
		if ( !empty( $populate_extras ) ) {
			$user_ids = array();

			foreach ( (array)$paged_users as $user )
				$user_ids[] = $user->id;

			$user_ids = $trmdb->escape( join( ',', (array)$user_ids ) );

			// Add additional data to the returned results
			$paged_users = TRS_Core_User::get_user_extras( $paged_users, $user_ids, $type );
		}

		return array( 'users' => $paged_users, 'total' => $total_users );
	}


	/**
	 * Fetches the user details for all the users who username starts with the letter given.
	 *
	 * @global object $trs Global trendr settings object
	 * @global trmdb $trmdb WordPress database object
	 * @param string $letter The letter the users names are to start with.
	 * @param integer $limit The number of users we wish to retrive.
	 * @param integer $page The page number we are currently on, used in conjunction with $limit to get the start position for the limit.
	 * @param boolean $populate_extras Populate extra user fields?
	 * @param string $exclude Comma-separated IDs of users whose results aren't to be fetched.
	 * @return mixed False on error, otherwise associative array of results.
	 * @static
	 */
	function get_users_by_letter( $letter, $limit = null, $page = 1, $populate_extras = true, $exclude = '' ) {
		global $trs, $trmdb;

		$pag_sql = '';
		if ( $limit && $page )
			$pag_sql = $trmdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		// Multibyte compliance
		if ( function_exists( 'mb_strlen' ) ) {
			if ( mb_strlen( $letter, 'UTF-8' ) > 1 || is_numeric( $letter ) || !$letter ) {
				return false;
			}
		} else {
			if ( strlen( $letter ) > 1 || is_numeric( $letter ) || !$letter ) {
				return false;
			}
		}

		$letter     = like_escape( $trmdb->escape( $letter ) );
		$status_sql = trs_core_get_status_sql( 'u.' );

		$exclude_sql = ( !empty( $exclude ) ) ? " AND u.ID NOT IN ({$exclude})" : "";

		$total_users_sql = apply_filters( 'trs_core_users_by_letter_count_sql', $trmdb->prepare( "SELECT COUNT(DISTINCT u.ID) FROM {$trmdb->users} u LEFT JOIN {$trs->profile->table_name_data} pd ON u.ID = pd.user_id LEFT JOIN {$trs->profile->table_name_fields} pf ON pd.field_id = pf.id WHERE {$status_sql} AND pf.name = %s {$exclude_sql} AND pd.value LIKE '$letter%%'  ORDER BY pd.value ASC", trs_xprofile_fullname_field_name() ), $letter );
		$paged_users_sql = apply_filters( 'trs_core_users_by_letter_sql', $trmdb->prepare( "SELECT DISTINCT u.ID as id, u.user_registered, u.user_nicename, u.user_login, u.user_email FROM {$trmdb->users} u LEFT JOIN {$trs->profile->table_name_data} pd ON u.ID = pd.user_id LEFT JOIN {$trs->profile->table_name_fields} pf ON pd.field_id = pf.id WHERE {$status_sql} AND pf.name = %s {$exclude_sql} AND pd.value LIKE '$letter%%' ORDER BY pd.value ASC{$pag_sql}", trs_xprofile_fullname_field_name() ), $letter, $pag_sql );

		$total_users = $trmdb->get_var( $total_users_sql );
		$paged_users = $trmdb->get_results( $paged_users_sql );

		/***
		 * Lets fetch some other useful data in a separate queries, this will be faster than querying the data for every user in a list.
		 * We can't add these to the main query above since only users who have this information will be returned (since the much of the data is in usermeta and won't support any type of directional join)
		 */
		$user_ids = array();
		foreach ( (array)$paged_users as $user )
			$user_ids[] = $user->id;

		$user_ids = $trmdb->escape( join( ',', (array)$user_ids ) );

		/* Add additional data to the returned results */
		if ( $populate_extras )
			$paged_users = TRS_Core_User::get_user_extras( $paged_users, $user_ids );

		return array( 'users' => $paged_users, 'total' => $total_users );
	}

	/**
	 * Get details of specific users from the database
	 *
	 * @global object $trs Global trendr settings object
	 * @global trmdb $trmdb WordPress database object
	 * @param array $user_ids The user IDs of the users who we wish to fetch information on.
	 * @param integer $limit The limit of results we want.
	 * @param integer $page The page we are on for pagination.
	 * @param boolean $populate_extras Populate extra user fields?
	 * @return array Associative array
	 * @static
	 */
	function get_specific_users( $user_ids, $limit = null, $page = 1, $populate_extras = true ) {
		global $trs, $trmdb;

		$pag_sql = '';
		if ( $limit && $page )
			$pag_sql = $trmdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		$user_sql   = " AND user_id IN ( " . $trmdb->escape( $user_ids ) . " ) ";
		$status_sql = trs_core_get_status_sql();

		$total_users_sql = apply_filters( 'trs_core_get_specific_users_count_sql', $trmdb->prepare( "SELECT COUNT(DISTINCT ID) FROM {$trmdb->users} WHERE {$status_sql} AND ID IN ( " . $trmdb->escape( $user_ids ) . " ) " ), $trmdb->escape( $user_ids ) );
		$paged_users_sql = apply_filters( 'trs_core_get_specific_users_count_sql', $trmdb->prepare( "SELECT DISTINCT ID as id, user_registered, user_nicename, user_login, user_email FROM {$trmdb->users} WHERE {$status_sql} AND ID IN ( " . $trmdb->escape( $user_ids ) . " ) {$pag_sql}" ), $trmdb->escape( $user_ids ) );

		$total_users = $trmdb->get_var( $total_users_sql );
		$paged_users = $trmdb->get_results( $paged_users_sql );

		/***
		 * Lets fetch some other useful data in a separate queries, this will be faster than querying the data for every user in a list.
		 * We can't add these to the main query above since only users who have this information will be returned (since the much of the data is in usermeta and won't support any type of directional join)
		 */

		/* Add additional data to the returned results */
		if ( $populate_extras )
			$paged_users = TRS_Core_User::get_user_extras( $paged_users, $user_ids );

		return array( 'users' => $paged_users, 'total' => $total_users );
	}

	/**
	 * Find users who match on the value of an xprofile data.
	 *
	 * @global object $trs Global trendr settings object
	 * @global trmdb $trmdb WordPress database object
	 * @param string $search_terms The terms to search the profile table value column for.
	 * @param integer $limit The limit of results we want.
	 * @param integer $page The page we are on for pagination.
	 * @param boolean $populate_extras Populate extra user fields?
	 * @return array Associative array
	 * @static
	 */
	function search_users( $search_terms, $limit = null, $page = 1, $populate_extras = true ) {
		global $trs, $trmdb;

		$pag_sql = $limit && $page ? $trmdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * intval( $limit ) ), intval( $limit ) ) : '';

		$search_terms = like_escape( $trmdb->escape( $search_terms ) );
		$status_sql   = trs_core_get_status_sql( 'u.' );

		$total_users_sql = apply_filters( 'trs_core_search_users_count_sql', "SELECT COUNT(DISTINCT u.ID) as id FROM {$trmdb->users} u LEFT JOIN {$trs->profile->table_name_data} pd ON u.ID = pd.user_id WHERE {$status_sql} AND pd.value LIKE '%%$search_terms%%' ORDER BY pd.value ASC", $search_terms );
		$paged_users_sql = apply_filters( 'trs_core_search_users_sql', "SELECT DISTINCT u.ID as id, u.user_registered, u.user_nicename, u.user_login, u.user_email FROM {$trmdb->users} u LEFT JOIN {$trs->profile->table_name_data} pd ON u.ID = pd.user_id WHERE {$status_sql} AND pd.value LIKE '%%$search_terms%%' ORDER BY pd.value ASC{$pag_sql}", $search_terms, $pag_sql );

		$total_users = $trmdb->get_var( $total_users_sql );
		$paged_users = $trmdb->get_results( $paged_users_sql );

		/***
		 * Lets fetch some other useful data in a separate queries, this will be faster than querying the data for every user in a list.
		 * We can't add these to the main query above since only users who have this information will be returned (since the much of the data is in usermeta and won't support any type of directional join)
		 */
		foreach ( (array)$paged_users as $user )
			$user_ids[] = $user->id;

		$user_ids = $trmdb->escape( join( ',', (array)$user_ids ) );

		// Add additional data to the returned results
		if ( $populate_extras )
			$paged_users = TRS_Core_User::get_user_extras( $paged_users, $user_ids );

		return array( 'users' => $paged_users, 'total' => $total_users );
	}

	/**
	 * Fetch extra user information, such as friend count and last profile update message.
	 *
	 * Accepts multiple user IDs to fetch data for.
	 *
	 * @global object $trs Global trendr settings object
	 * @global trmdb $trmdb WordPress database object
	 * @param array $paged_users an array of stdClass containing the users
	 * @param string $user_ids the user ids to select information about
	 * @param string $type the type of fields we wish to get
	 * @return mixed False on error, otherwise associative array of results.
	 * @static
	 */
	function get_user_extras( &$paged_users, &$user_ids, $type = false ) {
		global $trs, $trmdb;

		if ( empty( $user_ids ) )
			return $paged_users;

		// Fetch the user's full name
		if ( trs_is_active( 'xprofile' ) && 'alphabetical' != $type ) {
			$names = $trmdb->get_results( $trmdb->prepare( "SELECT pd.user_id as id, pd.value as fullname FROM {$trs->profile->table_name_fields} pf, {$trs->profile->table_name_data} pd WHERE pf.id = pd.field_id AND pf.name = %s AND pd.user_id IN ( {$user_ids} )", trs_xprofile_fullname_field_name() ) );
			for ( $i = 0, $count = count( $paged_users ); $i < $count; ++$i ) {
				foreach ( (array)$names as $name ) {
					if ( $name->id == $paged_users[$i]->id )
						$paged_users[$i]->fullname = $name->fullname;
				}
			}
		}

		// Fetch the user's total friend count
		if ( 'popular' != $type ) {
			$friend_count = $trmdb->get_results( $trmdb->prepare( "SELECT user_id as id, meta_value as total_friend_count FROM {$trmdb->usermeta} WHERE meta_key = %s AND user_id IN ( {$user_ids} )", trs_get_user_meta_key( 'total_friend_count' ) ) );
			for ( $i = 0, $count = count( $paged_users ); $i < $count; ++$i ) {
				foreach ( (array)$friend_count as $fcount ) {
					if ( $fcount->id == $paged_users[$i]->id )
						$paged_users[$i]->total_friend_count = (int)$fcount->total_friend_count;
				}
			}
		}

		// Fetch whether or not the user is a friend
		if ( trs_is_active( 'friends' ) ) {
			$friend_status = $trmdb->get_results( $trmdb->prepare( "SELECT initiator_user_id, friend_user_id, is_confirmed FROM {$trs->friends->table_name} WHERE (initiator_user_id = %d AND friend_user_id IN ( {$user_ids} ) ) OR (initiator_user_id IN ( {$user_ids} ) AND friend_user_id = %d )", $trs->loggedin_user->id, $trs->loggedin_user->id ) );
			for ( $i = 0, $count = count( $paged_users ); $i < $count; ++$i ) {
				foreach ( (array)$friend_status as $status ) {
					if ( $status->initiator_user_id == $paged_users[$i]->id || $status->friend_user_id == $paged_users[$i]->id )
						$paged_users[$i]->is_friend = $status->is_confirmed;
				}
			}
		}

		if ( 'active' != $type ) {
			$user_activity = $trmdb->get_results( $trmdb->prepare( "SELECT user_id as id, meta_value as last_activity FROM {$trmdb->usermeta} WHERE meta_key = %s AND user_id IN ( {$user_ids} )", trs_get_user_meta_key( 'last_activity' ) ) );
			for ( $i = 0, $count = count( $paged_users ); $i < $count; ++$i ) {
				foreach ( (array)$user_activity as $activity ) {
					if ( $activity->id == $paged_users[$i]->id )
						$paged_users[$i]->last_activity = $activity->last_activity;
				}
			}
		}

		// Fetch the user's last_activity
		if ( 'active' != $type ) {
			$user_activity = $trmdb->get_results( $trmdb->prepare( "SELECT user_id as id, meta_value as last_activity FROM {$trmdb->usermeta} WHERE meta_key = %s AND user_id IN ( {$user_ids} )", trs_get_user_meta_key( 'last_activity' ) ) );
			for ( $i = 0, $count = count( $paged_users ); $i < $count; ++$i ) {
				foreach ( (array)$user_activity as $activity ) {
					if ( $activity->id == $paged_users[$i]->id )
						$paged_users[$i]->last_activity = $activity->last_activity;
				}
			}
		}

		// Fetch the user's latest update
		$user_update = $trmdb->get_results( $trmdb->prepare( "SELECT user_id as id, meta_value as latest_update FROM {$trmdb->usermeta} WHERE meta_key = %s AND user_id IN ( {$user_ids} )", trs_get_user_meta_key( 'trs_latest_update' ) ) );
		for ( $i = 0, $count = count( $paged_users ); $i < $count; ++$i ) {
			foreach ( (array)$user_update as $update ) {
				if ( $update->id == $paged_users[$i]->id )
					$paged_users[$i]->latest_update = $update->latest_update;
			}
		}

		return $paged_users;
	}

	/**
	 * Get WordPress user details for a specified user.
	 *
	 * @global trmdb $trmdb WordPress database object
	 * @param integer $user_id User ID
	 * @return array Associative array
	 * @static
	 */
	function get_core_userdata( $user_id ) {
		global $trmdb;

		if ( !$user = $trmdb->get_row( $trmdb->prepare( "SELECT * FROM $trmdb->users WHERE ID = %d LIMIT 1", $user_id ) ) )
			return false;

		return $user;
	}
}


/**
 * TRS_Core_Notification class can be used by any component.
 * It will handle the fetching, saving and deleting of a user notification.
 *
 * @package trendr Core
 */

class TRS_Core_Notification {

	/**
	 * The notification id
	 *
	 * @var integer
	 */
	var $id;

	/**
	 * The ID to which the notification relates to within the component.
	 *
	 * @var integer
	 */
	var $item_id;

	/**
	 * The secondary ID to which the notification relates to within the component.
	 *
	 * @var integer
	 */
	var $secondary_item_id = null;

	/**
	 * The user ID for who the notification is for.
	 *
	 * @var integer
	 */
	var $user_id;

	/**
	 * The name of the component that the notification is for.
	 *
	 * @var string
	 */
	var $component_name;

	/**
	 * The action within the component which the notification is related to.
	 *
	 * @var string
	 */
	var $component_action;

	/**
	 * The date the notification was created.
	 *
	 * @var string
	 */
	var $date_notified;

	/**
	 * Is the notification new or has it already been read.
	 *
	 * @var boolean
	 */
	var $is_new;


	/**
	 * PHP4 constructor
	 *
	 * @param integer $id
	 */
	function trs_core_notification( $id = 0 ) {
		$this->__construct($id);
	}

	/**
	 * Constructor
	 *
	 * @param integer $id
	 */
	function __construct( $id = 0 ) {
		if ( $id ) {
			$this->id = $id;
			$this->populate();
		}
	}

	/**
	 * Fetches the notification data from the database.
	 *
	 * @global object $trs Global trendr settings object
	 * @global trmdb $trmdb WordPress database object
	 */
	function populate() {
		global $trs, $trmdb;

		if ( $notification = $trmdb->get_row( $trmdb->prepare( "SELECT * FROM {$trs->core->table_name_notifications} WHERE id = %d", $this->id ) ) ) {
			$this->item_id = $notification->item_id;
			$this->secondary_item_id = $notification->secondary_item_id;
			$this->user_id           = $notification->user_id;
			$this->component_name    = $notification->component_name;
			$this->component_action  = $notification->component_action;
			$this->date_notified     = $notification->date_notified;
			$this->is_new            = $notification->is_new;
		}
	}

	/**
	 * Update or insert notification details into the database.
	 *
	 * @global object $trs Global trendr settings object
	 * @global trmdb $trmdb WordPress database object
	 * @return bool Success or failure
	 */
	function save() {
		global $trs, $trmdb;

		// Update
		if ( $this->id )
			$sql = $trmdb->prepare( "UPDATE {$trs->core->table_name_notifications} SET item_id = %d, secondary_item_id = %d, user_id = %d, component_name = %s, component_action = %d, date_notified = %s, is_new = %d ) WHERE id = %d", $this->item_id, $this->secondary_item_id, $this->user_id, $this->component_name, $this->component_action, $this->date_notified, $this->is_new, $this->id );

		// Save
		else
			$sql = $trmdb->prepare( "INSERT INTO {$trs->core->table_name_notifications} ( item_id, secondary_item_id, user_id, component_name, component_action, date_notified, is_new ) VALUES ( %d, %d, %d, %s, %s, %s, %d )", $this->item_id, $this->secondary_item_id, $this->user_id, $this->component_name, $this->component_action, $this->date_notified, $this->is_new );

		if ( !$result = $trmdb->query( $sql ) )
			return false;

		$this->id = $trmdb->insert_id;
		return true;
	}

	/** Static functions ******************************************************/

	function check_access( $user_id, $notification_id ) {
		global $trmdb, $trs;

		return $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(id) FROM {$trs->core->table_name_notifications} WHERE id = %d AND user_id = %d", $notification_id, $user_id ) );
	}

	/**
	 * Fetches all the notifications in the database for a specific user.
	 *
	 * @global object $trs Global trendr settings object
	 * @global trmdb $trmdb WordPress database object
	 * @param integer $user_id User ID
	 * @return array Associative array
	 * @static
	 */
	function get_all_for_user( $user_id ) {
		global $trs, $trmdb;

 		return $trmdb->get_results( $trmdb->prepare( "SELECT * FROM {$trs->core->table_name_notifications} WHERE user_id = %d AND is_new = 1", $user_id ) );
	}

	/**
	 * Delete all the notifications for a user based on the component name and action.
	 *
	 * @global object $trs Global trendr settings object
	 * @global trmdb $trmdb WordPress database object
	 * @param integer $user_id
	 * @param string $component_name
	 * @param string $component_action
	 * @static
	 */
	function delete_for_user_by_type( $user_id, $component_name, $component_action ) {
		global $trs, $trmdb;

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->core->table_name_notifications} WHERE user_id = %d AND component_name = %s AND component_action = %s", $user_id, $component_name, $component_action ) );
	}

	/**
	 * Delete all the notifications that have a specific item id, component name and action.
	 *
	 * @global object $trs Global trendr settings object
	 * @global trmdb $trmdb WordPress database object
	 * @param integer $user_id The ID of the user who the notifications are for.
	 * @param integer $item_id The item ID of the notifications we wish to delete.
	 * @param string $component_name The name of the component that the notifications we wish to delete.
	 * @param string $component_action The action of the component that the notifications we wish to delete.
	 * @param integer $secondary_item_id (optional) The secondary item id of the notifications that we wish to use to delete.
	 * @static
	 */
	function delete_for_user_by_item_id( $user_id, $item_id, $component_name, $component_action, $secondary_item_id = false ) {
		global $trs, $trmdb;

		$secondary_item_sql = !empty( $secondary_item_id ) ? $trmdb->prepare( " AND secondary_item_id = %d", $secondary_item_id ) : '';

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->core->table_name_notifications} WHERE user_id = %d AND item_id = %d AND component_name = %s AND component_action = %s{$secondary_item_sql}", $user_id, $item_id, $component_name, $component_action ) );
	}

	/**
	 * Deletes all the notifications sent by a specific user, by component and action.
	 *
	 * @global object $trs Global trendr settings object
	 * @global trmdb $trmdb WordPress database object
	 * @param integer $user_id The ID of the user whose sent notifications we wish to delete.
	 * @param string $component_name The name of the component the notification was sent from.
	 * @param string $component_action The action of the component the notification was sent from.
	 * @static
	 */
	function delete_from_user_by_type( $user_id, $component_name, $component_action ) {
		global $trs, $trmdb;

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->core->table_name_notifications} WHERE item_id = %d AND component_name = %s AND component_action = %s", $user_id, $component_name, $component_action ) );
	}

	/**
	 * Deletes all the notifications for all users by item id, and optional secondary item id, and component name and action.
	 *
	 * @global object $trs Global trendr settings object
	 * @global trmdb $trmdb WordPress database object
	 * @param string $item_id The item id that they notifications are to be for.
	 * @param string $component_name The component that the notifications are to be from.
	 * @param string $component_action The action that the notificationsa are to be from.
	 * @param string $secondary_item_id Optional secondary item id that the notifications are to have.
	 * @static
	 */
	function delete_all_by_type( $item_id, $component_name, $component_action, $secondary_item_id ) {
		global $trs, $trmdb;

		if ( $component_action )
			$component_action_sql = $trmdb->prepare( "AND component_action = %s", $component_action );
		else
			$component_action_sql = '';

		if ( $secondary_item_id )
			$secondary_item_sql = $trmdb->prepare( "AND secondary_item_id = %d", $secondary_item_id );
		else
			$secondary_item_sql = '';

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->core->table_name_notifications} WHERE item_id = %d AND component_name = %s {$component_action_sql} {$secondary_item_sql}", $item_id, $component_name ) );
	}
}

/**
 * TRS_Button
 *
 * API to create trendr buttons
 *
 * @package trendr Core
 * @since 1.2.6
 */
class TRS_Button {
	// Button properties

	/**
	 * The button ID
	 *
	 * @var integer
	 */
	var $id;

	/**
	 * The component name that button belongs to.
	 *
	 * @var string
	 */
	var $component;

	/**
	 * Does the user need to be logged in to see this button?
	 *
	 * @var boolean
	 */
	var $must_be_logged_in;

	/**
	 * True or false if the button should not be displayed while viewing your own profile.
	 *
	 * @var boolean
	 */
	var $block_self;


	// Wrapper

	/**
	 * What type of DOM element to use for a wrapper.
	 *
	 *
	 * @var mixed div|span|p|li, or false for no wrapper
	 */
	var $wrapper;

	/**
	 * The DOM class of the button wrapper
	 *
	 * @var string
	 */
	var $wrapper_class;

	/**
	 * The DOM ID of the button wrapper
	 *
	 * @var string
	 */
	var $wrapper_id;


	// Button

	/**
	 * The destination link of the button
	 *
	 * @var string
	 */
	var $link_href;

	/**
	 * The DOM class of the button link
	 *
	 * @var string
	 */
	var $link_class;

	/**
	 * The DOM ID of the button link
	 *
	 * @var string
	 */
	var $link_id;

	/**
	 * The DOM rel value of the button link
	 *
	 * @var string
	 */
	var $link_rel;

	/**
	 * Title of the button link
	 *
	 * @var string
	 */
	var $link_title;

	/**
	 * The contents of the button link
	 *
	 * @var string
	 */
	var $link_text;


	// HTML result

	var $contents;

	/**
	 * trs_button()
	 *
	 * Builds the button based on passed parameters:
	 *
	 * component: Which component this button is for
	 * must_be_logged_in: Button only appears for logged in users
	 * block_self: Button will not appear when viewing your own profile.
	 * wrapper: div|span|p|li|false for no wrapper
	 * wrapper_id: The DOM ID of the button wrapper
	 * wrapper_class: The DOM class of the button wrapper
	 * link_href: The destination link of the button
	 * link_title: Title of the button
	 * link_id: The DOM ID of the button
	 * link_class: The DOM class of the button
	 * link_rel: The DOM rel of the button
	 * link_text: The contents of the button
	 *
	 * @param array $args
	 * @return bool False if not allowed
	 */
	function trs_button( $args = '' ) {
		$this->__construct($args);
	}

	function __construct( $args = '' ) {

		// Default arguments
		$defaults = array(
			'id'                => '',
			'component'         => 'core',
			'must_be_logged_in' => true,
			'block_self'        => true,

			'wrapper'           => 'div',
			'wrapper_id'        => '',
			'wrapper_class'     => '',

			'link_href'         => '',
			'link_title'        => '',
			'link_id'           => '',
			'link_class'        => '',
			'link_rel'          => '',
			'link_text'         => '',
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		// Required button properties
		$this->id                = $id;
		$this->component         = $component;
		$this->must_be_logged_in = (bool)$must_be_logged_in;
		$this->block_self        = (bool)$block_self;
		$this->wrapper           = $wrapper;

		// $id and $component are required
		if ( empty( $id ) || empty( $component ) )
			return false;

		// No button if component is not active
		if ( !trs_is_active( $this->component ) )
			return false;

		// No button for guests if must be logged in
		if ( true == $this->must_be_logged_in && !is_user_logged_in() )
			return false;

		// No button if viewing your own profile
		if ( true == $this->block_self && trs_is_my_profile() )
			return false;

		// Wrapper properties
		if ( false !== $this->wrapper ) {

			// Wrapper ID
			if ( !empty( $wrapper_id ) )
				$this->wrapper_id    = ' id="' . $wrapper_id . '"';

			// Wrapper class
			if ( !empty( $wrapper_class ) )
				$this->wrapper_class = ' class="generic-button ' . $wrapper_class . '"';
			else
				$this->wrapper_class = ' class="generic-button"';

			// Set before and after
			$before = '<' . $wrapper . $this->wrapper_class . $this->wrapper_id . '>';
			$after  = '</' . $wrapper . '>';

		// No wrapper
		} else {
			$before = $after = '';
		}

		// Link properties
		if ( !empty( $link_id ) )
			$this->link_id    = ' id="' . $link_id . '"';

		if ( !empty( $link_href ) )
			$this->link_href  = ' href="' . $link_href . '"';

		if ( !empty( $link_title ) )
			$this->link_title = ' title="' . $link_title . '"';

		if ( !empty( $link_rel ) )
			$this->link_rel   = ' rel="' . $link_rel . '"';

		if ( !empty( $link_class ) )
			$this->link_class = ' class="' . $link_class . '"';

		if ( !empty( $link_text ) )
			$this->link_text  = $link_text;

		// Build the button
		$this->contents = $before . '<a'. $this->link_href . $this->link_title . $this->link_id . $this->link_rel . $this->link_class . '>' . $this->link_text . '</a>' . $after;

		// Allow button to be manipulated externally
		$this->contents = apply_filters( 'trs_button_' . $component . '_' . $id, $this->contents, $this, $before, $after );
	}

	/**
	 * contents()
	 *
	 * Return contents of button
	 *
	 * @return string
	 */
	function contents() {
		return $this->contents;
	}

	/**
	 * display()
	 *
	 * Output contents of button
	 */
	function display() {
		if ( !empty( $this->contents ) )
			echo $this->contents;
	}
}

/**
 * TRS_Embed
 *
 * Extends TRM_Embed class for use with trendr.
 *
 * @package trendr Core
 * @since 1.5
 * @see TRM_Embed
 */
class TRS_Embed extends TRM_Embed {
	/**
	 * Constructor
	 *
	 * @global unknown $trm_embed
	 */
	function __construct() {
		global $trm_embed;

		// Make sure we populate the TRM_Embed handlers array.
		// These are providers that use a regex callback on the URL in question.
		// Do not confuse with oEmbed providers, which require an external ping.
		// Used in TRM_Embed::shortcode()
		$this->handlers = $trm_embed->handlers;

		if ( trs_use_embed_in_activity() ) {
			add_filter( 'trs_get_activity_content_body', array( &$this, 'autoembed' ), 8 );
			add_filter( 'trs_get_activity_content_body', array( &$this, 'run_shortcode' ), 7 );
		}

		if ( trs_use_embed_in_activity_replies() ) {
			add_filter( 'trs_get_activity_content', array( &$this, 'autoembed' ), 8 );
			add_filter( 'trs_get_activity_content', array( &$this, 'run_shortcode' ), 7 );
		}

		if ( trs_use_embed_in_forum_posts() ) {
			add_filter( 'trs_get_the_topic_post_content', array( &$this, 'autoembed' ), 8 );
			add_filter( 'trs_get_the_topic_post_content', array( &$this, 'run_shortcode' ), 7 );
		}

		if ( trs_use_embed_in_private_messages() ) {
			add_filter( 'trs_get_the_thread_message_content', array( &$this, 'autoembed' ), 8 );
			add_filter( 'trs_get_the_thread_message_content', array( &$this, 'run_shortcode' ), 7 );
		}

		do_action_ref_array( 'trs_core_setup_oembed', array( &$this ) );
	}

	/**
	 * The {@link do_shortcode()} callback function.
	 *
	 * Attempts to convert a URL into embed HTML. Starts by checking the URL against the regex of the registered embed handlers.
	 * Next, checks the URL against the regex of registered {@link TRM_oEmbed} providers if oEmbed discovery is false.
	 * If none of the regex matches and it's enabled, then the URL will be passed to {@link TRS_Embed::parse_oembed()} for oEmbed parsing.
	 *
	 * @uses trm_parse_args()
	 * @uses trm_embed_defaults()
	 * @uses current_user_can()
	 * @uses _trm_oembed_get_object()
	 * @uses TRM_Embed::maybe_make_link()
	 *
	 * @param array $attr Shortcode attributes.
	 * @param string $url The URL attempting to be embeded.
	 * @return string The embed HTML on success, otherwise the original URL.
	 */
	function shortcode( $attr, $url = '' ) {
		if ( empty( $url ) )
			return '';

		$rawattr = $attr;
		$attr = trm_parse_args( $attr, trm_embed_defaults() );

		// kses converts & into &amp; and we need to undo this
		// See http://core.trac.wordpress.org/ticket/11311
		$url = str_replace( '&amp;', '&', $url );

		// Look for known internal handlers
		ksort( $this->handlers );
		foreach ( $this->handlers as $priority => $handlers ) {
			foreach ( $handlers as $hid => $handler ) {
				if ( preg_match( $handler['regex'], $url, $matches ) && is_callable( $handler['callback'] ) ) {
					if ( false !== $return = call_user_func( $handler['callback'], $matches, $attr, $url, $rawattr ) )
						return apply_filters( 'embed_handler_html', $return, $url, $attr );
				}
			}
		}

		// Get object ID
		$id = apply_filters( 'embed_post_id', 0 );

		// Is oEmbed discovery on?
		$attr['discover'] = ( apply_filters( 'trs_embed_oembed_discover', false ) && current_user_can( 'unfiltered_html' ) );

		// Set up a new TRM oEmbed object to check URL with registered oEmbed providers
		require_once( ABSPATH . TRMINC . '/class-oembed.php' );
		$oembed_obj = _trm_oembed_get_object();

		// If oEmbed discovery is true, skip oEmbed provider check
		$is_oembed_link = false;
		if ( !$attr['discover'] ) {
			foreach ( (array)$oembed_obj->providers as $provider_matchmask => $provider ) {
				$regex = ( $is_regex = $provider[1] ) ? $provider_matchmask : '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $provider_matchmask ), '#' ) ) . '#i';

				if ( preg_match( $regex, $url ) )
					$is_oembed_link = true;
			}

			// If url doesn't match a TRM oEmbed provider, stop parsing
			if ( !$is_oembed_link )
				return $this->maybe_make_link( $url );
		}

		return $this->parse_oembed( $id, $url, $attr, $rawattr );
	}

	/**
	 * Base function so TRS components / plugins can parse links to be embedded.
	 * View an example to add support in {@link trs_activity_embed()}.
	 *
	 * @uses apply_filters() Filters cache.
	 * @uses do_action() To save cache.
	 * @uses trm_oembed_get() Connects to oEmbed provider and returns HTML on success.
	 * @uses TRM_Embed::maybe_make_link() Process URL for hyperlinking on oEmbed failure.
	 * @param int $id ID to do the caching for.
	 * @param string $url The URL attempting to be embedded.
	 * @param array $attr Shortcode attributes from {@link TRM_Embed::shortcode()}.
	 * @param array $rawattr Untouched shortcode attributes from {@link TRM_Embed::shortcode()}.
	 * @return string The embed HTML on success, otherwise the original URL.
	 */
	function parse_oembed( $id, $url, $attr, $rawattr ) {
		$id = intval( $id );

		if ( $id ) {
			// Setup the cachekey
			$cachekey = '_oembed_' . md5( $url . serialize( $attr ) );

			// Let components / plugins grab their cache
			$cache = '';
			$cache = apply_filters( 'trs_embed_get_cache', $cache, $id, $cachekey, $url, $attr, $rawattr );

			// Grab cache and return it if available
			if ( !empty( $cache ) ) {
				return apply_filters( 'trs_embed_oembed_html', $cache, $url, $attr, $rawattr );

			// If no cache, ping the oEmbed provider and cache the result
			} else {
				$html = trm_oembed_get( $url, $attr );
				$cache = ( $html ) ? $html : $url;

				// Let components / plugins save their cache
				do_action( 'trs_embed_update_cache', $cache, $cachekey, $id );

				// If there was a result, return it
				if ( $html )
					return apply_filters( 'trs_embed_oembed_html', $html, $url, $attr, $rawattr );
			}
		}

		// Still unknown
		return $this->maybe_make_link( $url );
	}
}
?>