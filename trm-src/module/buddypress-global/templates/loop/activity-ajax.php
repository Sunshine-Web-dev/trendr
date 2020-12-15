<div class="bboss_ajax_search_item bboss_ajax_search_item_activity">
	<a href='<?php echo trs_activity_thread_permalink() ?>'>
		<div class="item-portrait">
			<?php trs_activity_portrait( array( 'type'=>'full', 'height'=>50 ) ); ?>
		</div>

		<div class="item">
			<div class="item-title">
				<?php echo trm_strip_all_tags( trs_get_activity_action() ); ?>
			</div>

			<?php if ( trs_activity_has_content() ) : ?>
				<div class="item-desc">
					<?php echo buddyboss_global_search_activity_intro( 30 ); ?>
				</div>
			<?php endif; ?>
		</div>
	</a>
</div>