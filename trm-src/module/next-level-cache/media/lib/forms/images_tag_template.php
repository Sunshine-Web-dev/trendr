<div class="trs-img">
<?php $rel = md5(microtime() . rand());?>
<?php foreach ($images as $img) { ?>
	<?php if (!$img) continue; ?>
	<?php if (preg_match('!^https?:\/\/!i', $img)) { // Remote image ?>
		<img src="<?php echo esc_url($img); ?>" />
	<?php } else { ?>
		<?php $info = pathinfo(trim($img));?>
		<?php $thumbnail = file_exists(med_get_image_dir($activity_blog_id) . $info['filename'] . '-medt.' . strtolower($info['extension'])) ?
			med_get_image_url($activity_blog_id) . $info['filename'] . '-medt.' . strtolower($info['extension'])
			:
			med_get_image_url($activity_blog_id) . trim($img)
		;
		$target = 'all' == MED_LINKS_TARGET ? 'target="_blank"' : '';
		?>
		<a href="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?>" rel="<?php echo $rel;?>" <?php echo $target; ?> >
			<img src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img));?>" />
		</a>
	<?php } ?>
<?php } ?>
</div>