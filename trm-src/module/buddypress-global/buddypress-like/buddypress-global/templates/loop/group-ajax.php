<div class="bboss_ajax_search_item bboss_ajax_search_group">
	<a href="<?php trs_group_permalink(); ?>">
		<div class="item-avatar">
			<?php trs_group_avatar( 'type=thumb&width=50&height=50' ); ?>
		</div>

		<div class="item">
			<div class="item-title"><?php trs_group_name(); ?></div>
            <?php 
                $content = trm_strip_all_tags(trs_get_group_description());
                $trimmed_content = trm_trim_words( $content, 9, '...' );
            ?>
			<div class="item-desc"><?php echo $trimmed_content ?></div>
		</div>
	</a>
</div>