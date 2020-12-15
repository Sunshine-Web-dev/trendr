<?php

/**
 * trendr - Groups Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - trs_dtheme_object_filter()
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php do_action( 'trs_before_groups_loop' ); ?>

<?php if ( trs_has_groups( trs_ajax_querystring( 'groups' ) ) ) : ?>

	<div id="pag-top" class="pagination">

		<div class="pag-count" id="group-dir-count-top">

			<?php trs_groups_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="group-dir-pag-top">

			<?php trs_groups_pagination_links(); ?>

		</div>

	</div>

	<?php do_action( 'trs_before_directory_groups_list' ); ?>

	<ul id="groups-list" class="article-piece" role="main">

	<?php while ( trs_groups() ) : trs_the_group(); ?>

		<li>
			<div class="item-portrait">
				<a href="<?php trs_group_permalink(); ?>"><?php trs_group_portrait( 'type=full&width=50&height=50' ); ?></a>
			</div>

			<div class="item">
				<div class="item-title"><a href="<?php trs_group_permalink(); ?>"><?php trs_group_name(); ?></a></div>
				<div class="item-meta"><span class="activity"><?php printf( __( 'active %s', 'trendr' ), trs_get_group_last_active() ); ?></span></div>

				<div class="item-desc"><?php trs_group_description_excerpt(); ?></div>

				<?php do_action( 'trs_directory_groups_item' ); ?>

			</div>

			<div class="action">

				<?php do_action( 'trs_directory_groups_actions' ); ?>

				<div class="meta">

					<?php trs_group_type(); ?> / <?php trs_group_member_count(); ?>

				</div>

			</div>

			<div class="clear"></div>
		</li>

	<?php endwhile; ?>

	</ul>

	<?php do_action( 'trs_after_directory_groups_list' ); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="group-dir-count-bottom">

			<?php trs_groups_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="group-dir-pag-bottom">

			<?php trs_groups_pagination_links(); ?>

		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'There were no groups found.', 'trendr' ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'trs_after_groups_loop' ); ?>
