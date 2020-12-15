<?php do_action( 'trs_before_member_friend_requests_content' ) ?>

<?php if ( trs_has_members( 'include=' . trs_get_friendship_requests() ) ) : ?>

	<div id="pag-top" class="pagination no-ajax">

		<div class="pag-count" id="member-dir-count-top">

			<?php trs_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-dir-pag-top">

			<?php trs_members_pagination_links(); ?>

		</div>

	</div>

	<ul id="friend-list" class="article-piece" role="main">
		<?php while ( trs_members() ) : trs_the_member(); ?>

			<li id="friendship-<?php trs_friend_friendship_id() ?>">
				<div class="item-portrait">
					<a href="<?php trs_member_link() ?>"><?php trs_member_portrait() ?></a>
				</div>

				<div class="item">
					<div class="item-title"><a href="<?php trs_member_link() ?>"><?php trs_member_name() ?></a></div>
					<div class="item-meta"><span class="activity"><?php trs_member_last_active() ?></span></div>
				</div>

				<?php do_action( 'trs_friend_requests_item' ) ?>

				<div class="action">
					<a class="button accept" href="<?php trs_friend_accept_request_link() ?>"><?php _e( 'Accept', 'trendr' ); ?></a> &nbsp;
					<a class="button reject" href="<?php trs_friend_reject_request_link() ?>"><?php _e( 'Reject', 'trendr' ); ?></a>

					<?php do_action( 'trs_friend_requests_item_action' ) ?>
				</div>
			</li>

		<?php endwhile; ?>
	</ul>

	<?php do_action( 'trs_friend_requests_content' ) ?>

	<div id="pag-bottom" class="pagination no-ajax">

		<div class="pag-count" id="member-dir-count-bottom">

			<?php trs_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-dir-pag-bottom">

			<?php trs_members_pagination_links(); ?>

		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'You have no pending friendship requests.', 'trendr' ); ?></p>
	</div>

<?php endif;?>

<?php do_action( 'trs_after_member_friend_requests_content' ) ?>
