<div class="bboss_ajax_search_item bboss_ajax_search_item_post">
	<a href="<?php the_permalink();?>">
		<div class="item">
			<div class="item-title"><?php the_title();?></div>
			<?php  
                $content = trm_strip_all_tags(get_the_content());
                $trimmed_content = trm_trim_words( $content, 20, '...' ); 
            ?>
			<div class="item-desc"><?php echo $trimmed_content; ?></div>
       
		</div>
	</a>
</div>