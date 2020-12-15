<?php

/**
 * Trnder - Activity Stream (Single Item)
 *
 * This template is used by activity-loop.php and AJAX functions to show
 * each activity.
 *
				<?php if ( trs_activity_can_favorite() ) : ?>

					<?php if ( !trs_get_activity_is_favorite() ) : ?>

						<a href="<?php trs_activity_favorite_link(); ?>" class="button fav trs-action" title="<?php esc_attr_e( 'Mark as Favorite', 'trendr' ); ?>"><?php _e( 'Favorite', 'trendr' ) ?></a>

					<?php else : ?>

						<a href="<?php trs_activity_unfavorite_link(); ?>" class="button unfav trs-action" title="<?php esc_attr_e( 'Unfavorite', 'trendr' ); ?>"><?php _e( 'Unfavorite', 'trendr' ) ?></a>


					<?php endif; ?>

				<?php endif; ?>

 * @package Trnder
 * @sutrsackage trs-default
 */

?>

<?php do_action( 'trs_before_activity_entry' ); ?>
<li class="<?php trs_activity_css_class(); ?>" id="activity-<?php trs_activity_id(); ?>">


	<div class="broadcast-field">



		<?php if ( trs_activity_has_content() ) : ?>

			<div class="broadcast-inn">

				<?php if (isset($_POST)){
trs_activity_content_body_return($_REQUEST);
}else{
trs_activity_content_body();
} ?>

			</div>

<div class="broadcast-top">
		</div>	<div class="post">
		<?php trs_activity_action(); ?>

	</div>		
		<a href="<?php trs_activity_user_link(); ?>">
			<?php trs_activity_portrait( 'type=full&width=30&height=30' ) ?>


		</a>	

		<?php endif; ?>

		<?php do_action( 'trs_activity_entry_content' ); ?>


			<div class="broadcast-controls">
		<?php if ( is_user_logged_in() ) : ?>

				<?php if ( trs_activity_can_favorite() ) : ?>

					<?php if ( !trs_get_activity_is_favorite() ) : ?>

						<a href="<?php trs_activity_favorite_link(); ?>" class="button fav trs-action" title="<?php esc_attr_e( 'Favorite', 'trendr' ); ?>"><?php _e( 'Favorite', 'trendr' ) ?></a>

					<?php else : ?>

						<a href="<?php trs_activity_unfavorite_link(); ?>" class="button unfav trs-action" title="<?php esc_attr_e( 'Unfavorite', 'trendr' ); ?>"><?php _e( 'Unfavorite', 'trendr' ) ?></a>


					<?php endif; ?>

				<?php endif; ?>


				<?php if ( trs_activity_user_can_delete() ) trs_activity_delete_link(); ?>
		<?php endif; ?>
		</div>


	</div>
				<?php do_action( 'trs_activity_entry_meta' ); ?>

	



</li>

<?php do_action( 'trs_after_activity_entry' ); ?>
  