
<div class="med_images">
<?php $rel = md5(microtime() . rand());?>
<?php foreach ($images as $img) { ?>
	<?php if (!$img) continue; ?>
	<?php if (preg_match('!^https?:\/\/!i', $img)) { // Remote image ?>
		<img src="<?php echo esc_url($img); ?>" />

	<?php } else {
// var_dump($img);
		?>
		<?php $info = pathinfo(trim($img));?>


		<?php $thumbnail = file_exists(med_get_image_dir($activity_blog_id) . $info['filename'] . '-medt.' . strtolower($info['extension'])) ?
			med_get_image_url($activity_blog_id) . $info['filename'] . '-medt.' . strtolower($info['extension']): '';
		$target = ('all' == med_LINKS_TARGET) ? 'target="_blank"' : '';
		?>
		<a href="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?>" class="<?php echo $use_thickbox; ?>" rel="<?php echo $rel;?>" <?php echo $target; ?> >
			<?php if(IsVideoFile($img)){ ?>
			  <video width="320" height="240" controls><source  src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?>" type="video/mp4"></video>
			<?php } else {
				 ?>
			<img src="<?php if($thumbnail =='') echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); else  echo esc_url($thumbnail);?>"   />
<?php } ?>
		</a>


	<?php } ?>
<?php } ?>
</div>
</div>
