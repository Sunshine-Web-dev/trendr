<li id="activity-recommend">
  <a href="<?php echo trs_loggedin_user_domain() . trs_get_activity_slug() . '/recommend/'; ?>" title="<?php _e( 'Activity that I have been Recommended in.', 'trendr' ); ?>">
    <?php _e( 'Recommended', 'trendr' ); ?>
  <?php if ( trs_get_total_mention_count_for_user( trs_loggedin_user_id() ) ) : ?>
    <strong><?php printf( __( '<span>%s new</span>', 'trendr' ), trs_get_total_mention_count_for_user( trs_loggedin_user_id() ) ); ?></strong>
  <?php endif; ?></a></li>
