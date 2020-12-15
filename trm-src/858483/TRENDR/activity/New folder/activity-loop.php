<?php

/**
 * trendr - Activity Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - trs_dtheme_object_filter()
 *
 * @package trendr
 * @sutrsackage trs-default
 */

?>

<?php do_action( 'trs_before_activity_loop' ); ?>

<?php if ( trs_has_activities( trs_ajax_querystring( 'activity' ) ) ) : ?>

	<?php /* Show pagination if JS is not enabled, since the "Load More" link will do nothing */ ?>
	<noscript>
		<div class="pagination">
			<div class="pag-count"><?php trs_activity_pagination_count(); ?></div>
			<div class="pagination-links"><?php trs_activity_pagination_links(); ?></div>
		</div>
	</noscript>

	<?php if ( empty( $_POST['page'] ) ) : ?>

		<ul id="publish" class="publish-piece article-piece">

	<?php endif; ?>

	<?php while ( trs_activities() ) : trs_the_activity(); ?>

		<?php locate_template( array( 'activity/entry.php' ), true, false ); ?>

	<?php endwhile; ?>

	<?php if ( trs_activity_has_more_items() ) : ?>

		<li class="infinite">
			<a href="#more"><?php _e( 'Infinite load', 'trendr' ); ?></a>
		</li>

	<?php endif; ?>

	<?php if ( empty( $_POST['page'] ) ) : ?>

		</ul>

	<?php endif; ?>

<?php else : ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, there was no activity found. Please try a different filter.', 'trendr' ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'trs_after_activity_loop' ); ?>

<form action="" name="publish-spiral" id="publish-spiral" method="post">

	<?php trm_nonce_field( 'activity_filter', '_key_activity_filter' ); ?>

</form>