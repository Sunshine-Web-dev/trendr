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
		$target = 'all' == MED_LINKS_TARGET ? 'target="_blank"' : '';		$activity_id = trs_get_activity_id();

		?>
		
            <?php if(IsVideoFile($img)){
                $img = trim($img);
                $thumbnail = preg_replace('~\..*$~', '.jpg', $img);
$video_upload_dir_base_url = get_bloginfo('url').'/'. 'trm-src/uploads/med' .'/' ;
?>

            <img src="<?php echo esc_url(med_get_image_url($activity_blog_id) . $thumbnail) ?> "       onerror="this.onerror=null;this.src='https://placeimg.com/200/300/animals';"data-video="<?php echo esc_url($video_upload_dir_base_url .  $img); ?>" class="video-flip" onerror="this.style.display='none'">


			<?php } else {
				 ?>
			<img src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img));?> " onerror="this.style.display='none'"/>
		</a>
	<?php } ?>
		<?php } ?>

<?php } ?>
</div>


