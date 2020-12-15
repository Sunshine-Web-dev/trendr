<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class TRS_Friends_Friendship {
	var $id;
	var $initiator_user_id;
	var $friend_user_id;
	var $is_confirmed;
	var $is_limited;
	var $date_created;

	var $is_request;
	var $populate_friend_details;

	var $friend;

	function trs_friends_friendship( $id = null, $is_request = false, $populate_friend_details = true ) {
		$this->__construct( $id, $is_request, $populate_friend_details );
	}

	function __construct( $id = null, $is_request = false, $populate_friend_details = true ) {
		$this->is_request = $is_request;

		if ( $id ) {
			$this->id = $id;
			$this->populate_friend_details = $populate_friend_details;
			$this->populate( $this->id );
		}
	}

	function populate() {
		global $trmdb, $trs, $creds;

		if ( $friendship = $trmdb->get_row( $trmdb->prepare( "SELECT * FROM {$trs->friends->table_name} WHERE id = %d", $this->id ) ) ) {
			$this->initiator_user_id = $friendship->initiator_user_id;
			$this->friend_user_id    = $friendship->friend_user_id;
			$this->is_confirmed      = $friendship->is_confirmed;
			$this->is_limited        = $friendship->is_limited;
			$this->date_created      = $friendship->date_created;
		}

		// if running from ajax.
		if ( !$trs->displayed_user->id )
			$trs->displayed_user->id = $creds['current_userid'];

		if ( $this->populate_friend_details ) {
			if ( $this->friend_user_id == $trs->displayed_user->id ) {
				$this->friend = new TRS_Core_User( $this->initiator_user_id );
			} else {
				$this->friend = new TRS_Core_User( $this->friend_user_id );
			}
		}
	}

	function save() {
		global $trmdb, $trs;

		$this->initiator_user_id = apply_filters( 'friends_friendship_initiator_user_id_before_save', $this->initiator_user_id, $this->id );
		$this->friend_user_id    = apply_filters( 'friends_friendship_friend_user_id_before_save',    $this->friend_user_id,    $this->id );
		$this->is_confirmed      = apply_filters( 'friends_friendship_is_confirmed_before_save',      $this->is_confirmed,      $this->id );
		$this->is_limited        = apply_filters( 'friends_friendship_is_limited_before_save',        $this->is_limited,        $this->id );
		$this->date_created      = apply_filters( 'friends_friendship_date_created_before_save',      $this->date_created,      $this->id );

		do_action_ref_array( 'friends_friendship_before_save', array( &$this ) );

		if ( $this->id ) {
			// Update
			$result = $trmdb->query( $trmdb->prepare( "UPDATE {$trs->friends->table_name} SET initiator_user_id = %d, friend_user_id = %d, is_confirmed = %d, is_limited = %d, date_created = %s ) WHERE id = %d", $this->initiator_user_id, $this->friend_user_id, $this->is_confirmed, $this->is_limited, $this->date_created, $this->id ) );
		} else {
			// Save
			$result = $trmdb->query( $trmdb->prepare( "INSERT INTO {$trs->friends->table_name} ( initiator_user_id, friend_user_id, is_confirmed, is_limited, date_created ) VALUES ( %d, %d, %d, %d, %s )", $this->initiator_user_id, $this->friend_user_id, $this->is_confirmed, $this->is_limited, $this->date_created ) );
			$this->id = $trmdb->insert_id;
		}

		do_action( 'friends_friendship_after_save', array( &$this ) );

		return $result;
	}

	function delete() {
		global $trmdb, $trs;

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->friends->table_name} WHERE id = %d", $this->id ) );
	}

	/* Static Functions */

	function get_friend_user_ids( $user_id, $friend_requests_only = false, $assoc_arr = false ) {
		global $trmdb, $trs;

		if ( $friend_requests_only ) {
			$oc_sql = $trmdb->prepare( "AND is_confirmed = 0" );
			$friend_sql = $trmdb->prepare ( " WHERE friend_user_id = %d", $user_id );
		} else {
			$oc_sql = $trmdb->prepare( "AND is_confirmed = 1" );
			$friend_sql = $trmdb->prepare ( " WHERE (initiator_user_id = %d OR friend_user_id = %d)", $user_id, $user_id );
		}

		$friends = $trmdb->get_results( $trmdb->prepare( "SELECT friend_user_id, initiator_user_id FROM {$trs->friends->table_name} $friend_sql $oc_sql ORDER BY date_created DESC" ) );
		$fids = array();

		for ( $i = 0, $count = count( $friends ); $i < $count; ++$i ) {
			if ( $assoc_arr )
				$fids[] = array( 'user_id' => ( $friends[$i]->friend_user_id == $user_id ) ? $friends[$i]->initiator_user_id : $friends[$i]->friend_user_id );
			else
				$fids[] = ( $friends[$i]->friend_user_id == $user_id ) ? $friends[$i]->initiator_user_id : $friends[$i]->friend_user_id;
		}

		return $fids;
	}

	function get_friendship_id( $user_id, $friend_id ) {
		global $trmdb, $trs;

		return $trmdb->get_var( $trmdb->prepare( "SELECT id FROM {$trs->friends->table_name} WHERE ( initiator_user_id = %d AND friend_user_id = %d ) OR ( initiator_user_id = %d AND friend_user_id = %d ) AND is_confirmed = 1", $user_id, $friend_id, $friend_id, $user_id ) );
	}

	function get_friendship_request_user_ids( $user_id ) {
		global $trmdb, $trs;

		return $trmdb->get_col( $trmdb->prepare( "SELECT initiator_user_id FROM {$trs->friends->table_name} WHERE friend_user_id = %d AND is_confirmed = 0", $user_id ) );
	}

	function total_friend_count( $user_id = 0 ) {
		global $trmdb, $trs;

		if ( !$user_id )
			$user_id = ( $trs->displayed_user->id ) ? $trs->displayed_user->id : $trs->loggedin_user->id;

		/* This is stored in 'total_friend_count' usermeta.
		   This function will recalculate, update and return. */

		$count = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(id) FROM {$trs->friends->table_name} WHERE (initiator_user_id = %d OR friend_user_id = %d) AND is_confirmed = 1", $user_id, $user_id ) );

		// Do not update meta if user has never had friends
		if ( !$count && !trs_get_user_meta( $user_id, 'total_friend_count', true ) )
			return 0;

		trs_update_user_meta( $user_id, 'total_friend_count', (int)$count );
		return (int)$count;
	}

	function search_friends( $filter, $user_id, $limit = null, $page = null ) {
		global $trmdb, $trs;

		// TODO: Optimize this function.

		if ( !$user_id )
			$user_id = $trs->loggedin_user->id;

		$filter = like_escape( $trmdb->escape( $filter ) );

		if ( $limit && $page )
			$pag_sql = $trmdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

		if ( !$friend_ids = TRS_Friends_Friendship::get_friend_user_ids( $user_id ) )
			return false;

		// Get all the user ids for the current user's friends.
		$fids = implode( ',', $friend_ids );

		if ( empty($fids) )
			return false;

		// filter the user_ids based on the search criteria.
		if ( trs_is_active( 'xprofile' ) ) {
			$sql = "SELECT DISTINCT user_id FROM {$trs->profile->table_name_data} WHERE user_id IN ($fids) AND value LIKE '$filter%%' {$pag_sql}";
			$total_sql = "SELECT COUNT(DISTINCT user_id) FROM {$trs->profile->table_name_data} WHERE user_id IN ($fids) AND value LIKE '$filter%%'";
		} else {
			$sql = "SELECT DISTINCT user_id FROM {$trmdb->usermeta} WHERE user_id IN ($fids) AND meta_key = 'nickname' AND meta_value LIKE '$filter%%' {$pag_sql}";
			$total_sql = "SELECT COUNT(DISTINCT user_id) FROM {$trmdb->usermeta} WHERE user_id IN ($fids) AND meta_key = 'nickname' AND meta_value LIKE '$filter%%'";
		}

		$filtered_friend_ids = $trmdb->get_col($sql);
		$total_friend_ids = $trmdb->get_var($total_sql);

		if ( !$filtered_friend_ids )
			return false;

		return array( 'friends' => $filtered_friend_ids, 'total' => (int)$total_friend_ids );
	}

	function check_is_friend( $loggedin_userid, $possible_friend_userid ) {
		global $trmdb, $trs;

		if ( !$loggedin_userid || !$possible_friend_userid )
			return false;

		$result = $trmdb->get_results( $trmdb->prepare( "SELECT id, is_confirmed FROM {$trs->friends->table_name} WHERE (initiator_user_id = %d AND friend_user_id = %d) OR (initiator_user_id = %d AND friend_user_id = %d)", $loggedin_userid, $possible_friend_userid, $possible_friend_userid, $loggedin_userid ) );

		if ( $result ) {
			if ( 0 == (int)$result[0]->is_confirmed ) {
				return 'pending';
			} else {
				return 'is_friend';
			}
		} else {
			return 'not_friends';
		}
	}

	function get_bulk_last_active( $user_ids ) {
		global $trmdb, $trs;

		return $trmdb->get_results( $trmdb->prepare( "SELECT meta_value as last_activity, user_id FROM {$trmdb->usermeta} WHERE meta_key = %s AND user_id IN ( {$user_ids} ) ORDER BY meta_value DESC", trs_get_user_meta_key( 'last_activity' ) ) );
	}

	function accept($friendship_id) {
		global $trmdb, $trs;

	 	return $trmdb->query( $trmdb->prepare( "UPDATE {$trs->friends->table_name} SET is_confirmed = 1, date_created = %s WHERE id = %d AND friend_user_id = %d", trs_core_current_time(), $friendship_id, $trs->loggedin_user->id ) );
	}

	function reject($friendship_id) {
		global $trmdb, $trs;

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->friends->table_name} WHERE id = %d AND friend_user_id = %d", $friendship_id, $trs->loggedin_user->id ) );
	}

	function search_users( $filter, $user_id, $limit = null, $page = null ) {
		global $trmdb, $trs;

		$filter = like_escape( $trmdb->escape( $filter ) );

		$usermeta_table = $trmdb->base_prefix . 'usermeta';
		$users_table = $trmdb->base_prefix . 'users';

		if ( $limit && $page )
			$pag_sql = $trmdb->prepare( " LIMIT %d, %d", intval( ( $page - 1 ) * intval( $limit ) ), intval( $limit ) );

		// filter the user_ids based on the search criteria.
		if ( trs_is_active( 'xprofile' ) ) {
			$sql = $trmdb->prepare( "SELECT DISTINCT d.user_id as id FROM {$trs->profile->table_name_data} d, $users_table u WHERE d.user_id = u.id AND d.value LIKE '$filter%%' ORDER BY d.value DESC $pag_sql" );
		} else {
			$sql = $trmdb->prepare( "SELECT DISTINCT user_id as id FROM $usermeta_table WHERE meta_value LIKE '$filter%%' ORDER BY d.value DESC $pag_sql" );
		}

		$filtered_fids = $trmdb->get_col($sql);

		if ( !$filtered_fids )
			return false;

		return $filtered_fids;
	}

	function search_users_count( $filter ) {
		global $trmdb, $trs;

		$filter = like_escape( $trmdb->escape( $filter ) );

		$usermeta_table = $trmdb->prefix . 'usermeta';
		$users_table = $trmdb->base_prefix . 'users';

		// filter the user_ids based on the search criteria.
		if ( trs_is_active( 'xprofile' ) ) {
			$sql = $trmdb->prepare( "SELECT COUNT(DISTINCT d.user_id) FROM {$trs->profile->table_name_data} d, $users_table u WHERE d.user_id = u.id AND d.value LIKE '$filter%%'" );
		} else {
			$sql = $trmdb->prepare( "SELECT COUNT(DISTINCT user_id) FROM $usermeta_table WHERE meta_value LIKE '$filter%%'" );
		}

		$user_count = $trmdb->get_col($sql);

		if ( !$user_count )
			return false;

		return $user_count[0];
	}

	function sort_by_name( $user_ids ) {
		global $trmdb, $trs;

		if ( !trs_is_active( 'xprofile' ) )
			return false;

		return $trmdb->get_results( $trmdb->prepare( "SELECT user_id FROM {$trs->profile->table_name_data} pd, {$trs->profile->table_name_fields} pf WHERE pf.id = pd.field_id AND pf.name = %s AND pd.user_id IN ( {$user_ids} ) ORDER BY pd.value ASC", trs_xprofile_fullname_field_name() ) );
	}

	function get_random_friends( $user_id, $total_friends = 5 ) {
		global $trmdb, $trs;

		$sql = $trmdb->prepare( "SELECT friend_user_id, initiator_user_id FROM {$trs->friends->table_name} WHERE (friend_user_id = %d || initiator_user_id = %d) && is_confirmed = 1 ORDER BY rand() LIMIT %d", $user_id, $user_id, $total_friends );
		$results = $trmdb->get_results($sql);

		for ( $i = 0, $count = count( $results ); $i < $count; ++$i ) {
			$fids[] = ( $results[$i]->friend_user_id == $user_id ) ? $results[$i]->initiator_user_id : $results[$i]->friend_user_id;
		}

		// remove duplicates
		if ( count( $fids ) > 0 )
			return array_flip(array_flip($fids));
		else
			return false;
	}

	function get_invitable_friend_count( $user_id, $group_id ) {

		// Setup some data we'll use below
		$is_group_admin  = TRS_Groups_Member::check_is_admin( $user_id, $group_id );
		$friend_ids      = TRS_Friends_Friendship::get_friend_user_ids( $user_id );
		$invitable_count = 0;

		for ( $i = 0, $count = count( $friend_ids ); $i < $count; ++$i ) {

			// If already a member, they cannot be invited again
			if ( TRS_Groups_Member::check_is_member( (int) $friend_ids[$i], $group_id ) )
				continue;

			// If user already has invite, they cannot be added
			if ( TRS_Groups_Member::check_has_invite( (int) $friend_ids[$i], $group_id )  )
				continue;

			// If user is not group admin and friend is banned, they cannot be invited
			if ( ( false === $is_group_admin ) && TRS_Groups_Member::check_is_banned( (int) $friend_ids[$i], $group_id ) )
				continue;

			$invitable_count++;
		}

		return $invitable_count;
	}

	function get_user_ids_for_friendship( $friendship_id ) {
		global $trmdb, $trs;

		return $trmdb->get_row( $trmdb->prepare( "SELECT friend_user_id, initiator_user_id FROM {$trs->friends->table_name} WHERE id = %d", $friendship_id ) );
	}

	function delete_all_for_user( $user_id ) {
		global $trmdb, $trs;

		// Get friends of $user_id
		$friend_ids = TRS_Friends_Friendship::get_friend_user_ids( $user_id );

		// Delete all friendships related to $user_id
		$trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->friends->table_name} WHERE friend_user_id = %d OR initiator_user_id = %d", $user_id, $user_id ) );

		// Delete friend request notifications for members who have a notification from this user.
		$trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->core->table_name_notifications} WHERE component_name = 'friends' AND ( component_action = 'friendship_request' OR component_action = 'friendship_accepted' ) AND item_id = %d", $user_id ) );

		// Loop through friend_ids and update their counts
		foreach ( (array)$friend_ids as $friend_id ) {
			TRS_Friends_Friendship::total_friend_count( $friend_id );
		}
	}
}
?>