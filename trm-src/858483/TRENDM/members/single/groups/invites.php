<?php do_action( 'trs_before_group_invites_content' ) ?>

<?php if ( trs_has_groups( 'type=invites&user_id=' . trs_loggedin_user_id() ) ) : ?>

	<ul id="group-list" class="invites.article-piece" role="main">

		<?php while ( trs_groups() ) : trs_the_group(); ?>

			<li>
				<div class="item-portrait">
					<a href="<?php trs_group_permalink() ?>"><?php trs_group_portrait( 'type=thumb&width=50&height=50' ) ?></a>
				</div>

				<h4><a href="<?php trs_group_permalink() ?>"><?php trs_group_name() ?></a><span class="small"> - <?php printf( __( '%s members', 'trendr' ), trs_group_total_members( false ) ) ?></span></h4>

				<p class="desc">
					<?php trs_group_description_excerpt() ?>
				</p>

				<?php do_action( 'trs_group_invites_item' ) ?>

				<div class="action">
					<a class="button accept" href="<?php trs_group_accept_invite_link() ?>"><?php _e( 'Accept', 'trendr' ) ?></a> &nbsp;
					<a class="button reject confirm" href="<?php trs_group_reject_invite_link() ?>"><?php _e( 'Reject', 'trendr' ) ?></a>

					<?php do_action( 'trs_group_invites_item_action' ) ?>

				</div>
			</li>

		<?php endwhile; ?>
	</ul>

<?php else: ?>

	<div id="message" class="info" role="main">
		<p><?php _e( 'You have no outstanding group invites.', 'trendr' ) ?></p>
	</div>

<?php endif;?>

<?php do_action( 'trs_after_group_invites_content' ) ?>