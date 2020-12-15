<?php

/**
 * trendr - Users Forums
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<div class="contour-select no-ajax" id="subnav" role="navigation">
	<ul>
		<?php trs_get_options_nav() ?>

		<li id="forums-order-select" class="last filter">

			<label for="forums-order-by"><?php _e( 'Order By:', 'trendr' ); ?></label>
			<select id="forums-order-by">
				<option value="active"><?php _e( 'Last Active', 'trendr' ); ?></option>
				<option value="popular"><?php _e( 'Most Posts', 'trendr' ); ?></option>
				<option value="unreplied"><?php _e( 'Unreplied', 'trendr' ); ?></option>

				<?php do_action( 'trs_forums_directory_order_options' ); ?>

			</select>
		</li>
	</ul>
</div><!-- .contour-select -->

<?php

if ( trs_is_current_action( 'favorites' ) ) :
	locate_template( array( 'members/single/forums/topics.php' ), true );

else :
	do_action( 'trs_before_member_forums_content' ); ?>

	<div class="forums myforums">

		<?php locate_template( array( 'forums/forums-loop.php' ), true ); ?>

	</div>

	<?php do_action( 'trs_after_member_forums_content' ); ?>

<?php endif; ?>
