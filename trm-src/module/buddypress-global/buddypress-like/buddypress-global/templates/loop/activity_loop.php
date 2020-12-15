<li class="bboss_search_item bboss_search_item_activity">
	
	<div class="activity-avatar">
		<a href="<?php trs_activity_user_link(); ?>">

			<?php trs_activity_avatar(); ?>

		</a>
	</div>

	<div class="activity-content">

		<div class="activity-header">

			<?php trs_activity_action(); ?>

		</div>

		<?php if ( trs_activity_has_content() ) : ?>

			<div class="activity-inner">

				<?php trs_activity_content_body(); ?>

			</div>

		<?php endif; ?>

	</div>
	
</li>