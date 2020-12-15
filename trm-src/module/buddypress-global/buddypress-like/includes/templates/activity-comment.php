<?php
/**
 * BuddyPress Like - Activty Comment Button
 *
 * This function is used to display the BuddyPress Like button on comments in the activity stream
 *
 * @package BuddyPress Like
 *
 */

/*
 * trslike_activity_comment_button()
 *
 * Outputs Like/Unlike button for activity comments.
 *
 */
function trslike_activity_comment_button() {

    $liked_count = 0;

    if ( is_user_logged_in() ) {

        if ( trs_activity_get_meta( trs_get_activity_comment_id() , 'liked_count' , true ) ) {
            $users_who_like = array_keys( trs_activity_get_meta( trs_get_activity_comment_id() , 'liked_count' , true ) );
            $liked_count = count( $users_who_like );
        }

        if ( ! trs_like_is_liked( trs_get_activity_comment_id(), 'activity_comment', get_current_user_id() ) ) {
            ?>
            <a href="#" class="acomment-reply main like" id="like-activity-<?php echo trs_get_activity_comment_id(); ?>" title="<?php echo trs_like_get_text( 'like_this_item' ); ?>"><?php
               echo trs_like_get_text( 'like' );
                if ( $liked_count ) {
                    echo ' <span><small>' . $liked_count . '</small></span>';
                }
                ?></a>
        <?php } else { ?>
            <a href="#" class="acomment-reply main unlike" id="unlike-activity-<?php echo trs_get_activity_comment_id(); ?>" title="<?php echo trs_like_get_text( 'unlike_this_item' ); ?>"><?php
                echo trs_like_get_text( 'unlike' );
                if ( $liked_count ) {
                    echo ' <span><small>' . $liked_count . '</small></span>';
                }
                ?></a>
            <?php
        }
    }
}
