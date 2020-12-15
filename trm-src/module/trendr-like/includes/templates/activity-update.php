<?php
/**
 * trendr Like - Activty Update Button
 *
 * This function is used to display the trendr Like button on updates in the activity stream
 *
 * @package trendr Like
 *
 */

/*
 * trslike_activity_update_button()
 *
 * Outputs Like/Unlike button for activity updates.
 *
 */
function trslike_activity_update_button() {

    $liked_count = 0;

    if ( is_user_logged_in() && trs_get_activity_type() !== 'activity_liked' ) {

        if ( trs_activity_get_meta( trs_get_activity_id(), 'liked_count' , true ) ) {
            $users_who_like = array_keys( trs_activity_get_meta( trs_get_activity_id(), 'liked_count' , true ) );
            $liked_count = count( $users_who_like );
        }

        if ( ! trs_like_is_liked( trs_get_activity_id(), 'activity_update', get_current_user_id() ) ) {
            ?>
            <a href="#" class="button trs-primary-action like " class="button trs-primary-action like " id="like-activity-<?php echo trs_get_activity_id(); ?>" <?php _e( 'Favorite', 'trendr' ) ?> title="<?php esc_attr_e( 'trend this post.', 'trendr' ); ?>">
                <?php
                    echo trs_like_get_text( 'like' );
                    if ( $liked_count ) {
                        echo ' <span>' . $liked_count . '</span>';
                    }
                       if ( !$liked_count ) {
                        echo ' <span class="tr-count">' .     $liked_count = !empty( $liked_count ) ? $liked_count : 0 . '</span>';
                    }
          
                ?>
            </a>
        <?php } else { ?>
            <a href="#" class="button trs-primary-action unlike" id="unlike-activity-<?php echo trs_get_activity_id(); ?>" title="<?php echo trs_like_get_text( 'unlike_this_item' ); ?>">
                <?php
                    echo trs_like_get_text( 'unlike' );
                    if ( $liked_count ) {
                        echo '<span>' . $liked_count . '</span>';
                    }
                ?>
            </a>
            <?php
        }

        // Checking if there are users who like item.
        if ( isset ($users_who_like) ) {
            view_who_likes( trs_get_activity_id(), 'activity_update');
        }
    }
}
