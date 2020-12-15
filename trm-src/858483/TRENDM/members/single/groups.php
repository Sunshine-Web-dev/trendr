<?php

/**
 * trendr - Users Groups
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<div class="contour-select no-ajax" id="contour-box" role="navigation">
	<ul>
		<?php if ( trs_is_my_profile() ) trs_get_options_nav(); ?>

		<?php if ( !trs_is_current_action( 'invites' ) ) : ?>

			<li id="groups-order-select" class="last filter">

				<label for="groups-sort-by"><?php _e( 'Order By:', 'trendr' ); ?></label>
				<select id="groups-sort-by">
					<option value="active"><?php _e( 'Last Active', 'trendr' ); ?></option>
					<option value="popular"><?php _e( 'Most Members', 'trendr' ); ?></option>
					<option value="newest"><?php _e( 'Newly Created', 'trendr' ); ?></option>
					<option value="alphabetical"><?php _e( 'Alphabetical', 'trendr' ); ?></option>

					<?php do_action( 'trs_member_group_order_options' ) ?>

				</select>
			</li>

		<?php endif; ?>

	</ul>
</div><!-- .contour-select -->

<?php

if ( trs_is_current_action( 'invites' ) ) :
	locate_template( array( 'members/single/groups/invites.php' ), true );

else :
	do_action( 'trs_before_member_groups_content' ); ?>

	<div class="groups mygroups">

		<?php locate_template( array( 'groups/groups-loop.php' ), true ); ?>

	</div>

	<?php do_action( 'trs_after_member_groups_content' ); ?>

<?php endif; ?>
