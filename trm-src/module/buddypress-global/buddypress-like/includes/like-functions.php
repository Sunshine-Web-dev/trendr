<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * trs_like_is_liked()
 *
 * Checks to see whether the user has liked a given item.
 *
 */
function trs_like_is_liked( $item_id, $type, $user_id) {

    if ( ! $type || ! $item_id ) {
        return false;
    }

    if ( isset( $user_id ) ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
    }

    if ( $type == 'activity_update'  ) {

        $user_likes = get_user_meta( $user_id , 'trs_liked_activities' , true );

    } 

    if ( ! isset( $user_likes ) || ! $user_likes ) {
        return false;
    } elseif ( ! array_key_exists( $item_id , $user_likes ) ) {
        return false;
    } else {
        return true;
    }
}

/**
 * trs_like_add_user_like()
 *
 * Registers that the user likes a given item.
 *
 */
function trs_like_add_user_like( $item_id, $type ) {

    $liked_count = 0;

    if ( ! isset( $user_id ) ) {
        $user_id = get_current_user_id();
    }
    if ( ! $item_id || ! is_user_logged_in() ) {
        return false;
    }

    if ( $type == 'activity_update' ) {

        /* Add to the  users liked activities. */
        $user_likes = get_user_meta( $user_id , 'trs_liked_activities' , true );
        $user_likes[$item_id] = 'activity_liked';
        update_user_meta( $user_id , 'trs_liked_activities' , $user_likes );

        /* Add to the total likes for this activity. */
        $users_who_like = trs_activity_get_meta( $item_id , 'liked_count' , true );
        $users_who_like[$user_id] = 'user_likes';
        trs_activity_update_meta( $item_id , 'liked_count' , $users_who_like );

        $liked_count = count( $users_who_like );
        $group_id = 0;

        // check if this item is in a group or not, assign group id if so
        if ( trs_is_active( 'groups' ) && trs_is_group() ) {
          $group_id = trs_get_current_group_id();
        }

        trs_like_post_to_stream( $item_id , $user_id, $group_id );

    }  
    echo trs_like_get_text( 'unlike' );

    if ( $liked_count ) {
        echo ' <span>' . $liked_count . '</span>';
    }
}


/*
 * trs_like_get_some_likes()
 *
 * Description: Returns a defined number of likers, beginning with more recent.
 *
 */
function trs_like_get_some_likes( $id, $type ) {

  if ( $type == 'activity_update' ) {
    $users_who_like = array_keys((array) (trs_activity_get_meta( $id , 'liked_count' , true )));
  }

  
  if( is_array($users_who_like ) && count( $users_who_like ) >= 1 ){
    rsort( $users_who_like );
    

    foreach( $users_who_like as $user ) { 

      $output .= '<div><ul><li><a href="'. trs_core_get_userlink( $user, false, true ) .'">'. trs_core_fetch_avatar( array( 'item_id' => $user, 'object' => 'user', 'type' => 'thumb', 'class' => 'avatar reshared', 'width' => '40', 'height' => '40' ) ) .'</a></li>';
      
      $step += 1;
    }
    
    $output .= '</ul><br style="clear:both"></div>';
    
    echo $output;
    


    }
    }




/**
 *
 * view_who_likes() hook
 * TODO explain better
 *
 */
function view_who_likes( $id,  $type ) {

    do_action( 'trs_like_before_view_who_likes' );

    do_action( 'view_who_likes', $id, $type );

    do_action( 'trs_like_after_view_who_likes' );

}

// TODO comment why this is here
add_action( 'view_who_likes' , 'trs_like_get_some_likes', 10, 2 );
