<?php

/**
 * trendr - Members Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - trs_dtheme_object_filter()
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php do_action( 'trs_before_members_loop' ); ?>

<?php if ( trs_has_members( trs_ajax_querystring( 'members' ) ) ) : ?>

	<div id="pag-top" class="pagination">

		<div class="pag-count" id="member-dir-count-top">

			<?php trs_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-dir-pag-top">

			<?php trs_members_pagination_links(); ?>

		</div>

	</div>

	<?php do_action( 'trs_before_directory_members_list' ); ?>

	<ul id="members-list" class="article-piece" role="main">

	<?php while ( trs_members() ) : trs_the_member(); ?>

		<li>
			<div class="item-portrait">
				<a href="<?php trs_member_permalink(); ?>"><?php trs_member_portrait( 'type=full&width=47px&height=47px'); ?></a>
			</div>

			<div class="item">
				<div class="item-title">
					<a href="<?php trs_member_permalink(); ?>"><?php trs_member_name(); ?></a>

					<?php if ( trs_get_member_latest_update() ) : ?>

						<span class="update"> <?php trs_member_latest_update(); ?></span>

					<?php endif; ?>

				</div>

				<div class="item-meta"><span class="activity"><?php trs_member_last_active(); ?></span></div>

				<?php do_action( 'trs_directory_members_item' ); ?>

				<?php
				 /***
				  * If you want to show specific profile fields here you can,
				  * but it'll add an extra query for each member in the loop
				  * (only one regardless of the number of fields you show):
				  *
				  * trs_member_profile_data( 'field=the field name' );
				  */
				?>
			</div>

			<div class="action">

				<?php do_action( 'trs_directory_members_actions' ); ?>

			</div>

			<div class="clear"></div>
		</li>

	<?php endwhile; ?>

	</ul>

	<?php do_action( 'trs_after_directory_members_list' ); ?>

	<?php trs_member_hidden_fields(); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="member-dir-count-bottom">

			<?php trs_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-dir-pag-bottom">

			<?php trs_members_pagination_links(); ?>

		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( "Sorry, no members were found.", 'trendr' ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'trs_after_members_loop' ); ?>
