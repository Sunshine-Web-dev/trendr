<?php

/**
 * trendr - Users Activity
 *		<?php trs_get_options_nav() ?>

 * @package trendr
 * @sutrsackage trs-default
 */

?>

<div class="contour-select no-ajax" id="contour-box" role="navigation">	

</div><!-- .contour-select -->

<?php do_action( 'trs_before_member_activity_post_form' ); ?>

<?php
if ( is_user_logged_in() && trs_is_my_profile() && ( !trs_current_action() || trs_is_current_action( 'just-me' ) ) )
	locate_template( array( 'activity/post-form.php'), true );
do_action( '' );
?>

<div class="activity" role="main">


	<?php locate_template( array( 'activity/activity-loop.php' ), true ); ?>

</div><!-- .activity -->

<?php do_action( 'trs_after_member_activity_content' ); ?>
