<?php
/*
Plugin Name: trendr Automatic Friends
Plugin URI: http://www.stevenword.com/trs-automatic-friends/
Description: Automatically create and accept friendships for specified users upon new user registration. * Requires trendr
Version: 1.6.2
Author: stevenkword
Author URI: http://www.stevenword.com
*/

/*
 Copyright 2009  Steven K Word  (email : stevenword@gmail.com)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Loader function only fires if trendr exists
 * @uses is_admin, add_action
 * @action trs_loaded
 * @return null
 */
function skw_trsaf_loader(){

	/* Load the admin */
	if ( is_admin() ){
		require_once( dirname(__FILE__) . '/includes/admin.php' );
	}

	/* A Hook into TRS Core Activated User */
	//add_action( 'trs_core_activated_user', 'skw_trsaf_create_friendships' );

	/* Do this if the activated user process is bypassed */
	//add_action( 'trs_core_signup_user', 'skw_trsaf_create_friendships' );

	/* Do this the first time a new user logs in */
	add_action( 'trm', 'skw_trsaf_first_login' );

}
add_action( 'trs_loaded', 'skw_trsaf_loader' );

/**
 * New method for creating friendships at first login
 * Prevents conflict with plugins such as "Disable Activation" that bypass the activation process
 *
 * Hook into the 'trm' action and check if the user is logged in
 * and if get_user_meta( $trs->loggedin_user->id, 'last_activity' ) is false.
 * http://trendr.trac.wordpress.org/ticket/3003
 */
function skw_trsaf_first_login(){

	if( ! is_user_logged_in() )
		return;

	global $trs;

	$last_login = get_user_meta( $trs->loggedin_user->id, 'last_activity', true );

	if( ! isset( $last_login ) || empty( $last_login ) )
		skw_trsaf_create_friendships( $trs->loggedin_user->id );

}

/**
 * Create friendships automatically
 * When a initiator user registers for the blog, create initiator friendship with the specified user(s) and autoaccept those friendhips.
 * @global trs
 * @param initiator_user_id
 * @uses get_userdata, get_option, explode, friends_add_friend, get_friend_user_ids, total_friend_count
 * @return null
 */
function skw_trsaf_create_friendships( $initiator_user_id ) {

	global $trs;

	/* Get the user data for the initiatorly registered user. */
	$initiator_user_info = get_userdata( $initiator_user_id );

	/* Get the friend users id(s) */
	$options = get_option( 'skw_trsaf_options' );
	$skw_trsaf_user_ids = $options[ 'skw_trsaf_user_ids' ];

	/* Check to see if the admin options are set*/
	if ( isset( $skw_trsaf_user_ids ) && ! empty( $skw_trsaf_user_ids ) ){

		$friend_user_ids = explode( ',', $skw_trsaf_user_ids );
		foreach ( $friend_user_ids as $friend_user_id ){

			/* Request the friendship */

			if(function_exists("trs_follow_start_following")){
				trs_follow_start_following(array( 'leader_id' => $friend_user_id, 'follower_id' =>  $initiator_user_id ));
					// trs_follow_is_following(  );
			}else  if ( !friends_add_friend( $initiator_user_id, $friend_user_id, $force_accept = true ) ) {
				return false;
			}
			else {
				/* Get friends of $user_id */

				// $friend_ids = TRS_Friends_Friendship::get_friend_user_ids( $initiator_user_id );

				/* Loop through the initiator's friends and update their friend counts */
				// foreach ( (array) $friend_ids as $friend_id ) {
				// 	TRS_Friends_Friendship::total_friend_count( $friend_id );
				// }
				//
				// /* Update initiator friend counts */
				// TRS_Friends_Friendship::total_friend_count( $initiator_user_id );
			}

		}

	}
	return;
}
