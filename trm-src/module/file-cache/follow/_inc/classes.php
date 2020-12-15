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
 * Trnder Follow class.
 *
 * Handles populating and saving follow relationships.
 *
 * @since 1.0.0
 */
class TRS_Follow {
	/**
	 * The follow ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $id = 0;

	/**
	 * The user ID of the person we want to follow.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $leader_id;

	/**
	 * The user ID of the person initiating the follow request.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	var $follower_id;

	/**
	 * Constructor.
	 *
	 * @param int $leader_id The user ID of the user you want to follow.
	 * @param int $follower_id The user ID initiating the follow request.
	 */
	public function __construct( $leader_id = 0, $follower_id = 0 ) {
		if ( ! empty( $leader_id ) && ! empty( $follower_id ) ) {
			$this->leader_id   = (int) $leader_id;
			$this->follower_id = (int) $follower_id;
			$this->populate();
		}
	}

	/**
	 * Populate method.
	 *
	 * Used in constructor.
	 *
	 * @since 1.0.0
	 */
	protected function populate() {
		global $trmdb, $trs;

		if ( $follow_id = $trmdb->get_var( $trmdb->prepare( "SELECT id FROM {$trs->follow->table_name} WHERE leader_id = %d AND follower_id = %d", $this->leader_id, $this->follower_id ) ) ) {
			$this->id = $follow_id;
		}
	}

	/**
	 * Saves a follow relationship into the database.
	 *
	 * @since 1.0.0
	 */
	public function save() {
		global $trmdb, $trs;

		// do not use these filters
		// use the 'trs_follow_before_save' hook instead
		$this->leader_id   = apply_filters( 'trs_follow_leader_id_before_save',   $this->leader_id,   $this->id );
		$this->follower_id = apply_filters( 'trs_follow_follower_id_before_save', $this->follower_id, $this->id );

		do_action_ref_array( 'trs_follow_before_save', array( &$this ) );

		// update existing entry
		if ( $this->id ) {
			$result = $trmdb->query( $trmdb->prepare( "UPDATE {$trs->follow->table_name} SET leader_id = %d, follower_id = %d WHERE id = %d", $this->leader_id, $this->follower_id, $this->id ) );

		// add new entry
		} else {
			$result = $trmdb->query( $trmdb->prepare( "INSERT INTO {$trs->follow->table_name} ( leader_id, follower_id ) VALUES ( %d, %d )", $this->leader_id, $this->follower_id ) );
			$this->id = $trmdb->insert_id;
		}

		do_action_ref_array( 'trs_follow_after_save', array( &$this ) );

		return $result;
	}

	/**
	 * Deletes a follow relationship from the database.
	 *
	 * @since 1.0.0
	 */
	public function delete() {
		global $trmdb, $trs;

		return $trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->follow->table_name} WHERE id = %d", $this->id ) );
	}

	/** STATIC METHODS *****************************************************/

	/**
	 * Get the follower IDs for a given user.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The user ID.
	 * @return array
	 */
	public static function get_followers( $user_id ) {
		global $trs, $trmdb;
		return $trmdb->get_col( $trmdb->prepare( "SELECT follower_id FROM {$trs->follow->table_name} WHERE leader_id = %d", $user_id ) );
	}

	/**
	 * Get the user IDs that a user is following.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The user ID to fetch.
	 * @return array
	 */
	public static function get_following( $user_id ) {
		global $trs, $trmdb;
		return $trmdb->get_col( $trmdb->prepare( "SELECT leader_id FROM {$trs->follow->table_name} WHERE follower_id = %d", $user_id ) );
	}

	/**
	 * Get the follower / following counts for a given user.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The user ID to fetch counts for.
	 * @return array
	 */
	public static function get_counts( $user_id ) {
		global $trs, $trmdb;

		$followers = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(id) FROM {$trs->follow->table_name} WHERE leader_id = %d", $user_id ) );
		$following = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(id) FROM {$trs->follow->table_name} WHERE follower_id = %d", $user_id ) );

		return array( 'followers' => $followers, 'following' => $following );
	}

	/**
	 * Bulk check the follow status for a user against a list of user IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param array $leader_ids The user IDs to check the follow status for.
	 * @param int $user_id The user ID to check against the list of leader IDs.
	 * @return array
	 */
	public static function bulk_check_follow_status( $leader_ids, $user_id = false ) {
		global $trs, $trmdb;

		if ( empty( $user_id ) ) {
			$user_id = trs_loggedin_user_id();
		}

		if ( empty( $user_id ) ) {
			return false;
		}

		$leader_ids = implode( ',', trm_parse_id_list( (array) $leader_ids ) );

		return $trmdb->get_results( $trmdb->prepare( "SELECT leader_id, id FROM {$trs->follow->table_name} WHERE follower_id = %d AND leader_id IN ($leader_ids)", $user_id ) );
	}

	/**
	 * Deletes all follow relationships for a given user.
	 *
	 * @since 1.1.0
	 *
	 * @param int $user_id The user ID
	 */
	public static function delete_all_for_user( $user_id ) {
		global $trs, $trmdb;

		$trmdb->query( $trmdb->prepare( "DELETE FROM {$trs->follow->table_name} WHERE leader_id = %d OR follower_id = %d", $user_id, $user_id ) );
	}
}
