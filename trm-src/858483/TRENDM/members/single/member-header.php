<?php

/**
 * Trnder - Users Header
 *
 * @package Trnder
 * @sutrsackage trs-default
 */

?>
<?php if ( is_user_logged_in() && trs_displayed_user_id() && ( trs_is_activity_component() ) ) do_action( 'trs_profile_knobs' ); ?>  
<div id="bar_bottom" ><span class="user-name">
	<?php trs_displayed_user_fullname() ?>
<span class="user-call">


</div>
	<span class="activity"><?php trs_last_activity( trs_displayed_user_id() ); ?></span>

	<?php if ( !is_super_admin() && is_user_logged_in() ) : ?>


	<div ><span class="user-call"><a  href="<?php echo trs_get_send_public_message_link() ?>" >@<?php trs_displayed_user_username(); ?></a></div>
</span>



<?php endif; ?>



<?php if ( is_super_admin() ) : ?>


	<div ><span class="user-call"><a  href="<?php echo get_admin_url() ?>" >@<?php trs_displayed_user_username(); ?></a></div>
</span>
<?php endif; ?>

<div id="contour-inner">

<?php do_action( 'trs_after_member_activity_post_form' ); ?>

		<?php if ( trs_is_active( 'activity' ) ) : ?>

			<div id="latest-update">

				<?php trs_activity_latest_update( trs_displayed_user_id() ); ?>

			</div>

		<?php endif; ?>
	<?php do_action( 'trs_before_member_header_meta' ); ?>

 <?php if ( trs_displayed_user_id() ) do_action( 'trs_member_header_actions' ); ?> 
	<div id="controls">
	<div id="knobs">
	
	</div><!-- #knobs -->


</div><!-- #controls -->
</div><!-- #contour-inner -->




<?php do_action( 'template_notices' ); ?>	

<div id="contour-bottom">
	
  <?php                   $args1 = trs_get_user_firstname( $leader_fullname );
 //echo '<h1>' .'<a>'. "Follow ". $args1 .'</a>'.  '&nbsp;'. "to get his public posts in your posts.".'</h5>' ;?>
<?php if ( trs_displayed_user_id() && ( trs_is_activity_component() ) ) do_action('followers_bar' ); ?> 


</div><!-- #contour-bottom -->