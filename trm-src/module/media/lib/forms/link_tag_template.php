<?php
$target = in_array(MED_LINKS_TARGET, array('all', 'external')) ? 'target="_blank"' : '';
?><div class="med_final_link">
	<?php if ($image) { ?>
	<div class="med_link_preview_container">
		<a href="<?php echo esc_url($url);?>" <?php echo $target; ?> ><img src="<?php echo esc_url($image); ?>" /></a>
	</div>
	<?php } ?>
	<div class="med_link_contents">
		<div class="med_link_preview_title"><?php echo $title;?></div>
		<div class="med_link_preview_url">
			<a href="<?php echo esc_url($url);?>" <?php echo $target; ?> ><?php echo $url;?></a>
		</div>
		<div class="med_link_preview_body"><?php echo $body;?></div>
	</div>
</div>