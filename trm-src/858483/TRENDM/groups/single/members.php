<?php if ( trs_group_has_members( 'exclude_admins_mods=0' ) ) : ?>

	<?php do_action( 'trs_before_group_members_content' ); ?>

	<div class="contour-select" id="contour-box" role="navigation">
		<ul>

			<?php do_action( 'trs_members_directory_member_sub_types' ); ?>

		</ul>
	</div>

	<div id="pag-top" class="pagination no-ajax">

		<div class="pag-count" id="member-count-top">

			<?php trs_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-pag-top">

			<?php trs_members_pagination_links(); ?>

		</div>

	</div>

	<?php do_action( 'trs_before_group_members_list' ); ?>

	<ul id="member-list" class="article-piece" role="main">

		<?php while ( trs_group_members() ) : trs_group_the_member(); ?>

			<li>
				<a href="<?php trs_group_member_domain(); ?>">

					<?php trs_group_member_portrait_thumb(); ?>

				</a>

				<h5><?php trs_group_member_link(); ?></h5>
				<span class="activity"><?php trs_group_member_joined_since(); ?></span>

				<?php do_action( 'trs_group_members_list_item' ); ?>

				<?php if ( trs_is_active( 'friends' ) ) : ?>

					<div class="action">

						<?php trs_add_friend_button( trs_get_group_member_id(), trs_get_group_member_is_friend() ); ?>

						<?php do_action( 'trs_group_members_list_item_action' ); ?>

					</div>

				<?php endif; ?>
			</li>

		<?php endwhile; ?>

	</ul>

	<?php do_action( 'trs_after_group_members_list' ); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="member-count-bottom">

			<?php trs_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-pag-bottom">

			<?php trs_members_pagination_links(); ?>

		</div>

	</div>

	<?php do_action( 'trs_after_group_members_content' ); ?>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'This group has no members.', 'trendr' ); ?></p>
	</div>

<?php endif; ?>
