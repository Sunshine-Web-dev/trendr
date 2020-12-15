<?php

/**
 * trendr - Users Friends
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<div class="contour-select no-ajax" id="contour-box" role="navigation">
	<ul>
		<?php if ( trs_is_my_profile() ) trs_get_options_nav(); ?>

		<?php if ( !trs_is_current_action( 'requests' ) ) : ?>

			<li id="members-order-select" class="last filter">

				<label for="members-all"><?php _e( 'Order By:', 'trendr' ) ?></label>
				<select id="members-all">
					<option value="active"><?php _e( 'Last Active', 'trendr' ) ?></option>
					<option value="newest"><?php _e( 'Newest Registered', 'trendr' ) ?></option>
					<option value="alphabetical"><?php _e( 'Alphabetical', 'trendr' ) ?></option>

					<?php do_action( 'trs_member_blog_order_options' ) ?>

				</select>
			</li>

		<?php endif; ?>

	</ul>
</div>

<?php

if ( trs_is_current_action( 'requests' ) ) :
	 locate_template( array( 'members/single/friends/requests.php' ), true );

else :
	do_action( 'trs_before_member_friends_content' ); ?>

	<div class="members friends">

		<?php locate_template( array( 'members/members-loop.php' ), true ); ?>

	</div><!-- .members.friends -->

	<?php do_action( 'trs_after_member_friends_content' ); ?>

<?php endif; ?>
