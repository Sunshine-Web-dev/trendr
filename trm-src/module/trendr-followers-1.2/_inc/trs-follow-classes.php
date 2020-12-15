<?php
/**
 * TRS Follow Classes
 *
 * @package TRS-Follow
 * @sutrsackage Classes
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class: TRS_Follow
 *
 * Handles populating and saving TRS follow requests.
 *
 * @sutrsackage Classes
 */
class TRS_Follow {
	var $id;
	var $leader_id;
	var $follower_id;

	function trs_follow( $leader_id = false, $follower_id = false ) {
		if ( !empty( $leader_id ) && !empty( $follower_id ) ) {
			$this->leader_id = $leader_id;
			$this->follower_id = $follower_id;
			$this->populate();
		}
	}

	function populate() {
		global $trmdb, $trs;

		if ( $follow_id = $trmdb->get_var( $trmdb->prepare( "SELECT id FROM {$trs->follow->table_name} WHERE leader_id = %d AND follower_id = %d", $this->leader_id, $this->follower_id ) ) )
			$this->id = $follow_id;
	}

	function save() {
		global $trmdb, $trs;

		$this->leader_id = apply_filters( 'trs_follow_leader_id_before_save', $this->leader_id, $this->id );
		$this->follower_id = apply_filters( 'trs_follow_follower_id_before_save', $this->follower_id, $this->id );

		do_action_ref_array( 'trs_follow_before_save', array( &$this ) );

		if ( $this->id )
			$result = $trmdb->query( $trmdb->prepare( "UPDATE {$trs->follow->table_name} SET leader_id = %d, follower_id = %d WHERE id = %d", $this->leader_id, $this->follower_id, $this->id ) );
		else {
			// Save
			$result = $trmdb->query( $trmdb->prepare( "INSERT INTO {$trs->follow->table_name} ( leader_id, follower_id ) VALUES ( %d, %d )", $this->leader_id, $this->follower_id ) );
			$this->id = $trmdb->insert_id;
		}

		do_action_ref_array( 'trs_follow_after_save', array( &$this ) );

		return $result;
	}

	function delete() {
		global $trmdb, $trs;

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->follow->table_name} WHERE id = %d", $this->id ) );
	}

	/* Static Methods */

	function get_followers( $user_id ) {
		global $trs, $trmdb;
		return $trmdb->get_col( $trmdb->prepare( "SELECT follower_id FROM {$trs->follow->table_name} WHERE leader_id = %d", $user_id ) );
	}

	function get_following( $user_id ) {
		global $trs, $trmdb;
		return $trmdb->get_col( $trmdb->prepare( "SELECT leader_id FROM {$trs->follow->table_name} WHERE follower_id = %d", $user_id ) );
	}

	function get_counts( $user_id ) {
		global $trs, $trmdb;

		$followers = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(id) FROM {$trs->follow->table_name} WHERE leader_id = %d", $user_id ) );
		$following = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(id) FROM {$trs->follow->table_name} WHERE follower_id = %d", $user_id ) );

		return array( 'followers' => $followers, 'following' => $following );
	}

	function bulk_check_follow_status( $leader_ids, $user_id = false ) {
		global $trs, $trmdb;

		if ( empty( $user_id ) )
			$user_id = $trs->loggedin_user->id;

		$leader_ids = $trmdb->escape( implode( ',', (array)$leader_ids ) );

		return $trmdb->get_results( $trmdb->prepare( "SELECT leader_id, id FROM {$trs->follow->table_name} WHERE follower_id = %d AND leader_id IN ($leader_ids)", $user_id ) );
	}

	function delete_all_for_user( $user_id ) {
		global $trs, $trmdb;

		// Delete all follow relationships related to $user_id
		$trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->follow->table_name} WHERE leader_id = %d OR follower_id = %d", $user_id, $user_id ) );
	}
}
