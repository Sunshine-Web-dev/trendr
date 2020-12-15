<li class="bboss_search_item bboss_search_item_activity">
	
	<div class="activity-portrait">
		<a href="<?php trs_activity_user_link(); ?>">

			<?php trs_activity_portrait(); ?>

		</a>
	</div>

	<div class="broadcast-field">

		<div class="broadcast-top">

			<?php trs_activity_action(); ?>

		</div>

		<?php if ( trs_activity_has_content() ) : ?>

			<div class="broadcast-inn">

				<?php trs_activity_content_body(); ?>

			</div>

		<?php endif; ?>

	</div>
	
</li>