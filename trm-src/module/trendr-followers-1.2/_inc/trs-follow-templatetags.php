<?php
/**
 * TRS Follow Template Tags
 *
 * @package TRS-Follow
 * @sutrsackage Template
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Output a comma-separated list of user_ids for a given user's followers.
 *
 * @param mixed $args Arguments can be passed as an associative array or as a URL argument string
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses trs_get_follower_ids() Returns comma-seperated string of user IDs on success. Integer zero on failure.
 */
function trs_follower_ids( $args = '' ) {
	echo trs_get_follower_ids( $args );
}
	/**
	 * Returns a comma separated list of user_ids for a given user's followers.
	 *
	 * This can then be passed directly into the members loop querystring.
	 * On failure, returns an integer of zero. Needed when used in a members loop to prevent SQL errors.
	 *
	 * Arguments include:
	 * 	'user_id' - The user ID you want to check for followers
	 *
	 * @param mixed $args Arguments can be passed as an associative array or as a URL argument string
	 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
	 * @return Mixed Comma-seperated string of user IDs on success. Integer zero on failure.
	 */
	function trs_get_follower_ids( $args = '' ) {

		$defaults = array(
			'user_id' => trs_displayed_user_id()
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		$ids = implode( ',', (array)trs_follow_get_followers( array( 'user_id' => $user_id ) ) );

		$ids = empty( $ids ) ? 0 : $ids;

 		return apply_filters( 'trs_get_follower_ids', $ids, $user_id );
	}

/**
 * Output a comma-separated list of user_ids for a given user's following.
 *
 * @param mixed $args Arguments can be passed as an associative array or as a URL argument string
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses trs_get_following_ids() Returns comma-seperated string of user IDs on success. Integer zero on failure.
 */
function trs_following_ids( $args = '' ) {
	echo trs_get_following_ids( $args );
}
	/**
	 * Returns a comma separated list of user_ids for a given user's following.
	 *
	 * This can then be passed directly into the members loop querystring.
	 * On failure, returns an integer of zero. Needed when used in a members loop to prevent SQL errors.
	 *
	 * Arguments include:
	 * 	'user_id' - The user ID you want to check for a following
	 *
	 * @param mixed $args Arguments can be passed as an associative array or as a URL argument string
	 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
	 * @return Mixed Comma-seperated string of user IDs on success. Integer zero on failure.
	 */
	function trs_get_following_ids( $args = '' ) {

		$defaults = array(
			'user_id' => trs_displayed_user_id()
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		$ids = implode( ',', (array)trs_follow_get_following( array( 'user_id' => $user_id ) ) );

		$ids = empty( $ids ) ? 0 : $ids;

 		return apply_filters( 'trs_get_following_ids', $ids, $user_id );
	}

/**
 * Output a follow / unfollow button for a given user depending on the follower status.
 *
 * @param mixed $args Arguments can be passed as an associative array or as a URL argument string. See trs_follow_get_add_follow_button() for full arguments.
 * @uses trs_follow_get_add_follow_button() Returns the follow / unfollow button
 * @author r-a-y
 * @since 1.1
 */
function trs_follow_add_follow_button( $args = '' ) {
	echo trs_follow_get_add_follow_button( $args );
}
	/**
	 * Returns a follow / unfollow button for a given user depending on the follower status.
	 *
	 * Checks to see if the follower is already following the leader.  If is following, returns
	 * "Stop following" button; if not following, returns "Follow" button.
	 *
	 * Arguments include:
	 * 	'leader_id'   - The user you want to follow
	 * 	'follower_id' - The user who is initiating the follow request
	 *
	 * @param mixed $args Arguments can be passed as an associative array or as a URL argument string
	 * @return mixed String of the button on success.  Boolean false on failure.
	 * @uses trs_get_button() Renders a button using the TRS Button API
	 * @author r-a-y
	 * @since 1.1
	 */
	function trs_follow_get_add_follow_button( $args = '' ) {
		global $trs, $members_template;

		$defaults = array(
			'leader_id'   => trs_displayed_user_id(),
			'follower_id' => trs_loggedin_user_id()
		);

		$r = trm_parse_args( $args, $defaults );
		extract( $r );

		if ( !$leader_id || !$follower_id )
			return false;

		// if we're checking during a members loop, then follow status is already queried via trs_follow_inject_member_follow_status()
		if ( !empty( $members_template->member ) && $follower_id == trs_loggedin_user_id() && $follower_id == trs_displayed_user_id() ) {
			$is_following = $members_template->member->is_following;
		}
		// else we manually query the follow status
		else {
			$is_following = trs_follow_is_following( array( 'leader_id' => $leader_id, 'follower_id' => $follower_id ) );
		}

		// if the logged-in user is the leader, use already-queried variables
		if ( trs_loggedin_user_id() && $leader_id == trs_loggedin_user_id() ) {
			$leader_domain   = trs_loggedin_user_domain();
			$leader_fullname = trs_get_loggedin_user_fullname();
		}
		// else we do a lookup for the user domain and display name of the leader
		else {
			$leader_domain   = trs_core_get_user_domain( $leader_id );
			$leader_fullname = trs_core_get_user_displayname( $leader_id );
		}

		// setup some variables
		if ( $is_following ) {
			$id        = 'following';
			$action    = 'stop';
			$class     = 'unfollow';
			$link_text = $link_title = sprintf( __( 'Stop Following %s', 'trs-follow' ), apply_filters( 'trs_follow_leader_name', trs_get_user_firstname( $leader_fullname ), $leader_id ) );
		}
		else {
			$id        = 'not-following';
			$action    = 'start';
			$class     = 'follow';
			$link_text = $link_title = sprintf( __( 'Follow %s', 'trs-follow' ), apply_filters( 'trs_follow_leader_name', trs_get_user_firstname( $leader_fullname ), $leader_id ) );
		}

		// setup the button arguments
		$button = array(
			'id'                => $id,
			'component'         => 'follow',
			'must_be_logged_in' => true,
			'block_self'        => empty( $members_template->member ) ? true : false,
			'wrapper_class'     => 'follow-button ' . $id,
			'wrapper_id'        => 'follow-button-' . $leader_id,
			'link_href'         => trm_nonce_url( $leader_domain . $trs->follow->followers->slug . '/' . $action .'/', $action . '_following' ),
			'link_text'         => $link_text,
			'link_title'        => $link_title,
			'link_id'           => $class . '-' . $leader_id,
			'link_class'        => $class
		);

		// Filter and return the HTML button
		return trs_get_button( apply_filters( 'trs_follow_get_add_follow_button', $button, $leader_id, $follower_id ) );
	}