<?php
/**
 * TRS Follow Hooks
 *
 * Functions in this file allow this component to hook into trendr so it
 * interacts seamlessly with the interface and existing core components.
 *
 * @package TRS-Follow
 * @sutrsackage Hooks
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/** LOOP INJECTION *******************************************************/

/**
 * Inject $members_template global with follow status for each member in the
 * members loop.
 *
 * Once the members loop has queried and built a $members_template object,
 * fetch all of the member IDs in the object and bulk fetch the following
 * status for all the members in one query.
 *
 * This is significantly more efficient that querying for every member inside
 * of the loop.
 *
 * @since 1.0
 * @todo Use {@link TRS_User_Query} introduced in TRS 1.7 in a future version
 *
 * @global $members_template The members template object containing all fetched members in the loop
 * @uses TRS_Follow::bulk_check_follow_status() Check the following status for more than one member
 * @param $has_members Whether any members where actually returned in the loop
 * @return $has_members Return the original $has_members param as this is a filter function.
 */
function trs_follow_inject_member_follow_status( $has_members ) {
	global $members_template;

	if ( empty( $has_members ) )
		return $has_members;

	$user_ids = array();

	foreach( (array)$members_template->members as $i => $member ) {
		if ( $member->id != trs_loggedin_user_id() )
			$user_ids[] = $member->id;

		$members_template->members[$i]->is_following = false;
	}

	if ( empty( $user_ids ) )
		return $has_members;

	$following = TRS_Follow::bulk_check_follow_status( $user_ids );

	if ( empty( $following ) )
		return $has_members;

	foreach( (array)$following as $is_following ) {
		foreach( (array)$members_template->members as $i => $member ) {
			if ( $is_following->leader_id == $member->id )
				$members_template->members[$i]->is_following = true;
		}
	}

	return $has_members;
}
add_filter( 'trs_has_members', 'trs_follow_inject_member_follow_status' );

/**
 * Inject $members_template global with follow status for each member in the
 * group members loop.
 *
 * Once the group members loop has queried and built a $members_template
 * object, fetch all of the member IDs in the object and bulk fetch the
 * following status for all the group members in one query.
 *
 * This is significantly more efficient that querying for every member inside
 * of the loop.
 *
 * @author r-a-y
 * @since 1.1
 *
 * @global $members_template The members template object containing all fetched members in the loop
 * @uses TRS_Follow::bulk_check_follow_status() Check the following status for more than one member
 * @param $has_members - Whether any members where actually returned in the loop
 * @return $has_members - Return the original $has_members param as this is a filter function.
 */
function trs_follow_inject_group_member_follow_status( $has_members ) {
	global $members_template;

	if ( empty( $has_members ) )
		return $has_members;

	$user_ids = array();

	foreach( (array)$members_template->members as $i => $member ) {
		if ( $member->user_id != trs_loggedin_user_id() )
			$user_ids[] = $member->user_id;

		$members_template->members[$i]->is_following = false;
	}

	if ( empty( $user_ids ) )
		return $has_members;

	$following = TRS_Follow::bulk_check_follow_status( $user_ids );

	if ( empty( $following ) )
		return $has_members;

	foreach( (array)$following as $is_following ) {
		foreach( (array)$members_template->members as $i => $member ) {
			if ( $is_following->leader_id == $member->user_id )
				$members_template->members[$i]->is_following = true;
		}
	}

	return $has_members;
}
add_filter( 'trs_group_has_members', 'trs_follow_inject_group_member_follow_status' );

/** BUTTONS **************************************************************/

/**
 * Add a "Follow User/Stop Following" button to the profile header for a user.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses trs_follow_is_following() Check the following status for a user
 * @uses trs_is_my_profile() Return true if you are looking at your own profile when logged in.
 * @uses is_user_logged_in() Return true if you are logged in.
 */
function trs_follow_add_profile_follow_button() {
	if ( trs_is_my_profile() ) {
		return;
	}

	trs_follow_add_follow_button();
}
add_action( 'trs_member_header_actions', 'trs_follow_add_profile_follow_button' );

/**
 * Add a "Follow User/Stop Following" button to each member shown in the
 * members loop.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @global $members_template The members template object containing all fetched members in the loop
 * @uses is_user_logged_in() Return true if you are logged in.
 */
function trs_follow_add_listing_follow_button() {
	global $members_template;

	if ( $members_template->member->id == trs_loggedin_user_id() )
		return false;

	trs_follow_add_follow_button( 'leader_id=' . $members_template->member->id );
}
add_action( 'trs_directory_members_actions', 'trs_follow_add_listing_follow_button' );

/**
 * Add a "Follow User/Stop Following" button to each member shown in a group
 * members loop.
 *
 * @author r-a-y
 * @since 1.1
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @global $members_template The members template object containing all fetched members in the loop
 */
function trs_follow_add_group_member_follow_button() {
	global $members_template;

	if ( $members_template->member->user_id == trs_loggedin_user_id() || !trs_loggedin_user_id() )
		return false;

	trs_follow_add_follow_button( 'leader_id=' . $members_template->member->user_id );
}
add_action( 'trs_group_members_list_item_action', 'trs_follow_add_group_member_follow_button' );

/** DIRECTORIES **********************************************************/


/** AJAX MANIPULATION ****************************************************/

/**
 * Modify the querystring passed to the activity loop to return only users
 * that the current user is following.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses trs_get_following_ids() Get the user_ids of all users a user is following.
 */
function trs_follow_add_activity_scope_filter( $qs, $object, $filter, $scope ) {
	global $trs;

	// Only filter on directory pages (no action) and the following scope on activity object.
	//if ( ( !empty( $trs->current_action ) && !trs_is_current_action( 'following' ) ) || 'following' != $scope || 'activity' != $object )
	//7-3-18 modified for hashtag to work with follow plugin
	if ( ( !empty( $trs->current_action ) && !trs_is_current_action( TRS_ACTIVITY_HASHTAGS_SLUG) ) || 'following' != $scope || 'activity' != $object )
	
		return $qs;

	// set internal marker noting that our activity scope is applied
	$trs->follow->activity_scope_set = 1;

	$user_id = trs_displayed_user_id() ? trs_displayed_user_id() : trs_loggedin_user_id();

	$following_ids = trs_get_following_ids( array( 'user_id' => $user_id ) );

	// if $following_ids is empty, pass a negative number so no activity can be found
	$following_ids = empty( $following_ids ) ? -1 : $following_ids;

	$qs .= '&user_id=' . $following_ids;

	return apply_filters( 'trs_follow_add_activity_scope_filter', $qs, $filter );
}
add_filter( 'trendr_ajax_call',       'trs_follow_add_activity_scope_filter', 10, 4 );
add_filter( 'trs_legacy_theme_ajax_querystring', 'trs_follow_add_activity_scope_filter', 10, 4 );

/**
 * Modify the querystring passed to the members loop to return only users
 * that the current user is following.
 *
 * @global $trs The global trendr settings variable created in trs_core_setup_globals()
 * @uses trs_get_following_ids() Get the user_ids of all users a user is following.
 */
function trs_follow_add_member_directory_filter( $qs, $object, $filter, $scope  ) {
	global $trs;

	// Only filter on directory pages (no action) and the following scope on members object.
	if ( !empty( $trs->current_action ) || 'following' != $scope || 'members' != $object )
		return $qs;

	$qs .= '&include=' . trs_get_following_ids( array( 'user_id' => trs_loggedin_user_id() ) );

	return apply_filters( 'trs_follow_add_member_directory_filter', $qs, $filter );
}
add_filter( 'trendr_ajax_call',       'trs_follow_add_member_directory_filter', 10, 4 );
add_filter( 'trs_legacy_theme_ajax_querystring', 'trs_follow_add_member_directory_filter', 10, 4 );

/**
 * Filter the members loop on a user's "Following" or "Followers" page.
 *
 * This is done so we can return the users that:
 *   - the current user is following; or
 *   - the users that are following the current user
 *
 * @author r-a-y
 * @since 1.2
 *
 * @param str $qs The querystring for the TRS loop
 * @param str $object The current object for the querystring
 * @return str Modified querystring
 */
function trs_follow_add_member_scope_filter( $qs, $object ) {

	// not on the members object? stop now!
	if ( $object != 'members' )
		return $qs;

	// not on a user page? stop now!
	if ( ! trs_is_user() )
		return $qs;

	// filter the members loop based on the current page
	switch ( trs_current_action() ) {
		case 'following':
			$args = array(
				'include'  => trs_get_following_ids(),
				'per_page' => apply_filters( 'trs_follow_per_page', 20 )
			);

			// make sure we add a separator if we have an existing querystring
			if ( ! empty( $qs ) )
				$qs .= '&';

			// add our follow parameters to the end of the querystring
			$qs .= build_query( $args );

			return $qs;

			break;

		case 'followers' :
			$args = array(
				'include'  => trs_get_follower_ids(),
				'per_page' => apply_filters( 'trs_follow_per_page', 20 )
			);

			// make sure we add a separator if we have an existing querystring
			if ( ! empty( $qs ) )
				$qs .= '&';

			// add our follow parameters to the end of the querystring
			$qs .= build_query( $args );

			return $qs;

			break;

		default :
			return $qs;

			break;
	}

}
add_filter( 'trs_ajax_querystring', 'trs_follow_add_member_scope_filter', 20, 2 );

/**
 * On a user's "Activity > Following" page, set the activity scope to
 * "following".
 *
 * Unfortunately for 3rd-party components, this is the only way to set the
 * scope in {@link trendr_ajax_call()} due to the way that function
 * handles cookies.
 *
 * Yes, this is considered a hack, or more appropriately, a loophole!
 *
 * @author r-a-y
 * @since 1.1.1
 */
function trs_follow_set_activity_following_scope() {
	// set the activity scope to 'following' by faking an ajax request (loophole!)
	$_POST['cookie'] = 'trs-activity-scope%3Dfollowing%3B%20trs-activity-filter%3D-1';

	// reset the dropdown menu to 'Everything'
	@setcookie( 'trs-activity-filter', '-1', 0, '/' );
}
add_action( 'trs_activity_screen_following', 'trs_follow_set_activity_following_scope' );

/**
 * On a user's "Activity > Following" screen, set the activity scope to
 * "following" during AJAX requests ("Load More" button or via activity
 * dropdown filter menu).
 *
 * Unfortunately for 3rd-party components, this is the only way to set the
 * scope in {@link trendr_ajax_call()} due to the way that function
 * handles cookies.
 *
 * Yes, this is considered a hack, or more appropriately, a loophole!
 *
 * @author r-a-y
 * @since 1.1.1
 */
function trs_follow_set_activity_following_scope_on_ajax() {

	// are we in an ajax request?
	//
	// backpat for TRS 1.5 as we can't check the DOING_AJAX constant b/c 1.5
	// doesn't use admin-ajax.php
	$is_ajax = ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' );

	// set the activity scope to 'following'
	if ( trs_is_current_action( 'following' ) && $is_ajax ) {
		// if we have a post value already, let's add our scope to the existing cookie value
		if ( !empty( $_POST['cookie'] ) )
			$_POST['cookie'] .= '%3B%20trs-activity-scope%3Dfollowing';
		else
			$_POST['cookie'] .= 'trs-activity-scope%3Dfollowing';
	}
}
add_action( 'trs_before_activity_loop', 'trs_follow_set_activity_following_scope_on_ajax' );

/**
 * Sets the "RSS" feed URL for the tab on the Sitewide Activity page.
 *
 * This occurs when the "Following" tab is clicked on the Sitewide Activity
 * page or when the activity scope is already set to "following".
 *
 * Only do this for trendr 1.8+.
 *
 * @since 1.2.1
 *
 * @author r-a-y
 * @param string $retval The feed URL.
 * @return string The feed URL.
 */
function trs_follow_alter_activity_feed_url( $retval ) {
	// only available in TRS 1.8+
	if ( ! class_exists( 'TRS_Activity_Feed' ) ) {
		return $retval;
	}

	// this is done b/c we're filtering 'trs_get_sitewide_activity_feed_link' and
	// we only want to alter the feed link for the "RSS" tab
	if ( ! defined( 'DOING_AJAX' ) && ! did_action( 'trs_before_directory_activity' ) ) {
		return $retval;
	}

	// get the activity scope
	$scope = ! empty( $_COOKIE['trs-activity-scope'] ) ? $_COOKIE['trs-activity-scope'] : false;

	if ( $scope == 'following' && trs_loggedin_user_id() ) {
		$retval = trs_loggedin_user_domain() . trs_get_activity_slug() . '/' . constant( 'TRS_FOLLOWING_SLUG' ) . '/feed/';
	}

	return $retval;
}
add_filter( 'trs_get_sitewide_activity_feed_link', 'trs_follow_alter_activity_feed_url' );
add_filter( 'trs_dtheme_activity_feed_url',        'trs_follow_alter_activity_feed_url' );
add_filter( 'trs_legacy_theme_activity_feed_url',  'trs_follow_alter_activity_feed_url' );

/** GETTEXT **************************************************************/

/**
 * Add gettext filter when no activities are found and when using follow scope.
 *
 * @since 1.2.1
 *
 * @author r-a-y
 * @param bool $has_activities Whether the current activity loop has activities.
 * @return bool
 */
function trs_follow_has_activities( $has_activities ) {
	global $trs;

	if ( ! empty( $trs->follow->activity_scope_set ) && ! $has_activities ) {
		add_filter( 'gettext', 'trs_follow_no_activity_text', 10, 2 );
	}

	return $has_activities;
}
add_filter( 'trs_has_activities', 'trs_follow_has_activities', 10, 2 );

/**
 * Modifies 'no activity found' text to be more specific to follow scope.
 *
 * @since 1.2.1
 *
 * @author r-a-y
 * @see trs_follow_has_activities()
 * @param string $translated_text The translated text.
 * @param string $untranslated_text The unmodified text.
 * @return string
 */
function trs_follow_no_activity_text( $translated_text, $untranslated_text ) {
	if ( $untranslated_text == 'Sorry, there was no activity found. Please try a different filter.' ) {
		if ( ! trs_is_user() || trs_is_my_profile() ) {
			$follow_counts = trs_follow_total_follow_counts( array(
				'user_id' => trs_loggedin_user_id()
			) );

			if ( $follow_counts['following'] ) {
				return __( "You are following some users, but they haven't posted yet.", 'trs-follow' );
			} else {
				return __( "You are not following anyone yet.", 'trs-lists' );
			}
		} else {
			global $trs;

			if ( ! empty( $trs->displayed_user->total_follow_counts['following'] ) ) {
				return __( "This user is following some users, but they haven't posted yet.", 'trs-follow' );
			} else {
				return __( "This user isn't following anyone yet.", 'trs-follow' );
			}
		}

	}

	return $translated_text;
}

/**
 * Removes custom gettext filter when using follow scope.
 *
 * @since 1.2.1
 *
 * @author r-a-y
 * @see trs_follow_has_activities()
 */
function trs_follow_after_activity_loop() {
	global $trs;

	if ( ! empty( $trs->follow->activity_scope_set ) ) {
		remove_filter( 'gettext', 'trs_follow_no_activity_text', 10, 2 );
		unset( $trs->follow->activity_scope_set );
	}
}
add_action( 'trs_after_activity_loop', 'trs_follow_after_activity_loop' );