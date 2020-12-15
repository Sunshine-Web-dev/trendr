<?php
/**
 * TRS Activity Privacy Filters
 *
 * @package TRS-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


 /* Check if the loggedin member can view the activity
 * @param  [Activity] $activity          [description]
 * @param  [int] $trs_loggedin_user_id [description]
 * @param [boolean]$is_super_admin             [description]
 * @param [int]$trs_displayed_user_id             [description]
 * @return [boolean]
 */
function trs_visibility_is_activity_invisible( $activity, $trs_loggedin_user_id, $is_super_admin ) {

    if( ($trs_loggedin_user_id == $activity->user_id) ||
        ($is_super_admin &&  trs_ap_is_admin_allowed_to_view_edit_privacy_levels())
    )
    return false;

    $visibility = trs_activity_get_meta( $activity->id, 'activity-privacy' );

    $remove_from_stream = false;

    switch ( $visibility ) {
        //asamir followersOnly
        case 'followersOnly' :
        if(function_exists("trs_follow_is_following")){
            $is_follow = trs_follow_is_following('leader_id='.$activity->user_id.'&follower_id='.$trs_loggedin_user_id );
            if( !$is_follow )
                $remove_from_stream = true;
          }
            break;
        case 'loggedin' :
            if( !$trs_loggedin_user_id )
                $remove_from_stream = true;
            break;

        //My friends
        case 'friends' :
            if ( trs_is_active( 'friends' ) ) {
                $is_friend = friends_check_friendship( $trs_loggedin_user_id, $activity->user_id );
                if( !$is_friend )
                    $remove_from_stream = true;
            }
            break;

        //@Mentioned Only
     //   case 'mentionedonly' :
        //   $usernames = trs_activity_find_mentions( $activity->content );
          //  $is_mentioned = array_key_exists( $trs_loggedin_user_id,  (array)$usernames );

          //  if( !$is_mentioned )
              //  $remove_from_stream = true;
          //  break;

        //My friends in the group
        case 'groupfriends' :
            if ( trs_is_active( 'friends' ) ) {
                $is_friend = friends_check_friendship( $trs_loggedin_user_id, $activity->user_id );
            } else
                 $is_friend = true;

            if ( trs_is_active( 'groups' ) ) {
                $group_is_user_member = groups_is_user_member( $trs_loggedin_user_id, $activity->item_id );
            } else
                return true;

            if( !$is_friend || !$group_is_user_member)
                $remove_from_stream = true;
            break;

        //Only group members
        case 'grouponly' :
            $group_is_user_member = groups_is_user_member( $trs_loggedin_user_id, $activity->item_id );
            if( !$group_is_user_member )
                $remove_from_stream = true;
            break;

        //Only group moderators
        case 'groupmoderators' :
            $group_is_user_mod = groups_is_user_mod( $trs_loggedin_user_id, $activity->item_id );
            if( !$group_is_user_mod )
                $remove_from_stream = true;
            break;

        //Only group admins
        case 'groupadmins' :
            $group_is_user_admin = groups_is_user_admin( $trs_loggedin_user_id, $activity->item_id );
            if( !$group_is_user_admin )
                $remove_from_stream = true;
            break;

        //Only Admins
        case 'adminsonly' :
            if( !$is_super_admin )
                $remove_from_stream = true;
            break;

        //Only Me
        case 'onlyme' :
            if( $trs_loggedin_user_id != $activity->user_id )
                $remove_from_stream = true;
            break;

        default:
            //public
            break;
    }

    // mentioned members can always see the acitivity whatever the privacy level
 //   if ( $visibility != 'mentionedonly' && $trs_loggedin_user_id && $remove_from_stream ){
     //   $usernames = trs_activity_find_mentions( $activity->content );
     //   $is_mentioned = array_key_exists( $trs_loggedin_user_id,  (array)$usernames );
      //  if( $is_mentioned ) {
         //   $remove_from_stream = false;
       // }
   // }

    $remove_from_stream = apply_filters( 'trs_more_visibility_activity_filter', $remove_from_stream, $visibility, $activity);

    return $remove_from_stream;
}

/**
 * trs_visibility_activity_filter
 * @param  [type] $a          [description]
 * @param  [type] $activities [description]
 * @return [type]             [description]
 */
function trs_visibility_activity_filter( $has_activities, $activities ) {
    global $trs;

    $is_super_admin = is_super_admin();
    $trs_displayed_user_id = trs_displayed_user_id();
    $trs_loggedin_user_id = trs_loggedin_user_id();

    foreach ( $activities->activities as $key => $activity ) {

        /*
        if( $trs_loggedin_user_id == $activity->user_id  )
            continue;

        $visibility = trs_activity_get_meta( $activity->id, 'activity-privacy' );
        $remove_from_stream = false;


        switch ( $visibility ) {
            //Logged in users
            case 'loggedin' :
                if( !$trs_loggedin_user_id )
                    $remove_from_stream = true;
                break;

            //My friends
            case 'friends' :
                if ( trs_is_active( 'friends' ) ) {
                    $is_friend = friends_check_friendship( $trs_loggedin_user_id, $activity->user_id );
                    if( !$is_friend )
                        $remove_from_stream = true;
                }
                break;

            //@Mentioned Only
            case 'mentionedonly' :
                $usernames = trs_activity_find_mentions( $activity->content );
                $is_mentioned = array_key_exists( $trs_loggedin_user_id,  (array)$usernames );

                if( !$is_mentioned )
                    $remove_from_stream = true;
                break;

            //My friends in the group
            case 'groupfriends' :
                if ( trs_is_active( 'friends' ) ) {
                    $is_friend = friends_check_friendship( $trs_loggedin_user_id, $activity->user_id );
                } else
                     $is_friend = true;

                if ( trs_is_active( 'groups' ) ) {
                    $group_is_user_member = groups_is_user_member( $trs_loggedin_user_id, $activity->item_id );
                } else
                    return true;

                if( !$is_friend || !$group_is_user_member)
                    $remove_from_stream = true;
                break;

            //Only group members
            case 'grouponly' :
                $group_is_user_member = groups_is_user_member( $trs_loggedin_user_id, $activity->item_id );
                if( !$group_is_user_member )
                    $remove_from_stream = true;
                break;

            //Only group moderators
            case 'groupmoderators' :
                $group_is_user_mod = groups_is_user_mod( $trs_loggedin_user_id, $activity->item_id );
                if( !$group_is_user_mod )
                    $remove_from_stream = true;
                break;

            //Only group admins
            case 'groupadmins' :
                $group_is_user_admin = groups_is_user_admin( $trs_loggedin_user_id, $activity->item_id );
                if( !$group_is_user_admin )
                    $remove_from_stream = true;
                break;

            //Only Admins
            case 'adminsonly' :
                if( !$is_super_admin )
                    $remove_from_stream = true;
                break;

            //Only Me
            case 'onlyme' :
                if( $trs_loggedin_user_id != $activity->user_id )
                    $remove_from_stream = true;
                break;

            default:
                //public
                break;
        }

        // mentioned members can always see the acitivity whatever the privacy level
        if ( $visibility != 'mentionedonly' && $trs_loggedin_user_id && $remove_from_stream ){
            $usernames = trs_activity_find_mentions( $activity->content );
            $is_mentioned = array_key_exists( $trs_loggedin_user_id,  (array)$usernames );
            if( $is_mentioned ) {
                $remove_from_stream = false;
            }
        }

        $remove_from_stream = apply_filters( 'trs_more_visibility_activity_filter', $remove_from_stream, $visibility, $activity);
        */

        $remove_from_stream = trs_visibility_is_activity_invisible( $activity, $trs_loggedin_user_id, $is_super_admin );

        if ( $remove_from_stream && isset( $activities->activity_count ) ) {
            $activities->activity_count = $activities->activity_count - 1;
            unset( $activities->activities[$key] );
        }
    }

    $activities_new = array_values( $activities->activities );
    $activities->activities = $activities_new;

    return $has_activities;
}
add_action( 'trs_has_activities', 'trs_visibility_activity_filter', 10, 2 );

//add_filter( 'trs_get_last_activity', 'trs_activity_privacy_last_activity', 10, 1);
function trs_activity_privacy_last_activity( $last_activity ){
    if( isset($last_activity) ) {
        $has_activities = false;
        $activities = new stdClass();
        $activities->activities = array();
        $activities->activities[] = $last_activity;
        trs_visibility_activity_filter($has_activities, $activities);

        if ( empty($activities) )
            $last_activity = null;
    }

    return $last_activity;
}

add_filter( 'trs_get_activity_latest_update', 'trs_activity_privacy_latest_update', 10, 1);
function trs_activity_privacy_latest_update( $latest_update ){
    $user_id = trs_displayed_user_id();


    if ( !$update = trs_get_user_meta( $user_id, 'trs_latest_update', true ) )
        return $latest_update;

    $activity_id = $update['id'];
    $activity = trs_activity_get_specific( array( 'activity_ids' => $activity_id ) );

    // single out the activity
    $activity_single = $activity["activities"][0];

    $has_activities = false;
    $activities = new stdClass();
    $activities->activities = array();
    $activities->activities[] = $activity_single;

    trs_visibility_activity_filter( $has_activities, $activities );

    if ( empty( $activities->activities ) )
        $latest_update = null;

    return $latest_update;
}

// prevent members to see last activity on members loop
add_filter('trs_get_member_latest_update', 'trs_activity_privacy_member_latest_update',10, 1);
function trs_activity_privacy_member_latest_update( $update_content ){
    $is_super_admin = is_super_admin();
    if( $is_super_admin && trs_ap_is_admin_allowed_to_view_edit_privacy_levels() )
        return $update_content;

    global $members_template;
    $latest_update = trs_get_user_meta( trs_get_member_user_id(), 'trs_latest_update' , true );
    if ( !empty( $latest_update ) ) {
        $activity_id = $latest_update['id'];
        $activities = trs_activity_get_specific( array( 'activity_ids' => $activity_id ) );

        // single out the activity
        $activity = $activities["activities"][0];

        /*
        $has_activities = false;
        $activities = new stdClass();
        $activities->activities = array();
        $activities->activities[] = $activity;

        trs_visibility_activity_filter( $has_activities, $activities );
        if ( empty( $activities->activities ) )
         return '';
        */

        $trs_displayed_user_id = trs_displayed_user_id();
        $trs_loggedin_user_id = trs_loggedin_user_id();

        $remove_from_stream = trs_visibility_is_activity_invisible( $activity, $trs_loggedin_user_id, $is_super_admin );

        if ($remove_from_stream)
            return false;
    }

    return $update_content;
}

// prevent members to see last activity on member header page
add_filter('get_user_metadata', 'trs_activity_privacy_latest_user_update',10, 3);
function trs_activity_privacy_latest_user_update( $retval, $object_id, $meta_key ){
    if ($meta_key == 'trs_latest_update') {
        remove_filter('get_user_metadata', 'trs_activity_privacy_latest_user_update');

        $is_super_admin = is_super_admin();
        $trs_displayed_user_id = trs_displayed_user_id();
        $trs_loggedin_user_id = trs_loggedin_user_id();

        if($is_super_admin && trs_ap_is_admin_allowed_to_view_edit_privacy_levels())
            return $retval;

         $single = false;
         $retval = get_metadata('user', $object_id, $meta_key,  $single);
         if( isset($retval) && is_array($retval) && !empty( $retval) ) {
            $activity_id = $retval[0]['id'];

            $activities = trs_activity_get_specific( array( 'activity_ids' => $activity_id ) );
            $activity = $activities['activities'][0];
            $remove_from_stream = trs_visibility_is_activity_invisible( $activity, $trs_loggedin_user_id, $is_super_admin, $trs_displayed_user_id );
            if ($remove_from_stream) {
                return false;
            }
         }
         return $retval;

    }
}

