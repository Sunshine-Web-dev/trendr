<div class="bboss_ajax_search_item bboss_ajax_search_group">
	<a href="<?php trs_group_permalink(); ?>">
		<div class="item-portrait">
			<?php trs_group_portrait( 'type=thumb&width=50&height=50' ); ?>
		</div>

		<div class="item">
			<div class="item-title"><?php trs_group_name(); ?></div>
            <?php 
                $content = trm_strip_all_tags(trs_get_group_description());
                if(strlen($content) > 20)
                {
                	$trimmed_content = substr($content,0,20) . '....'; 
                }
                else
                {
                	$trimmed_content = $content;	
                }
            ?>
			<div class="item-desc"><?php echo $trimmed_content ?></div>
		</div>
	</a>
</div>