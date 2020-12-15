<h4><?php _e( 'Change Avatar', 'trendr' ) ?></h4>

<?php do_action( 'trs_before_profile_portrait_upload_content' ) ?>

<?php if ( !(int)trs_get_option( 'trs-disable-portrait-uploads' ) ) : ?>

	<p><?php _e( 'Your portrait will be used on your profile and throughout the site. If there is a <a href="http://grportrait.com">Grportrait</a> associated with your account email we will use that, or you can upload an image from your computer.', 'trendr') ?></p>

	<form action="" method="post" id="portrait-upload-form" class="standard-form" enctype="multipart/form-data">

		<?php if ( 'upload-image' == trs_get_portrait_admin_step() ) : ?>

			<?php trm_nonce_field( 'trs_portrait_upload' ) ?>
			<p><?php _e( 'Click below to select a JPG, GIF or PNG format photo from your computer and then click \'Upload Image\' to proceed.', 'trendr' ) ?></p>

			<p id="portrait-upload">
				<input type="file" name="file" id="file" />
				<input type="submit" name="upload" id="upload" value="<?php _e( 'Upload Image', 'trendr' ) ?>" />
				<input type="hidden" name="action" id="action" value="trs_portrait_upload" />
			</p>

			<?php if ( trs_get_user_has_portrait() ) : ?>
				<p><?php _e( "If you'd like to delete your current portrait but not upload a new one, please use the delete portrait button.", 'trendr' ) ?></p>
				<p><a class="button edit" href="<?php trs_portrait_delete_link() ?>" title="<?php _e( 'Delete Avatar', 'trendr' ) ?>"><?php _e( 'Delete My Avatar', 'trendr' ) ?></a></p>
			<?php endif; ?>

		<?php endif; ?>

		<?php if ( 'crop-image' == trs_get_portrait_admin_step() ) : ?>

			<h5><?php _e( 'Crop Your New Avatar', 'trendr' ) ?></h5>

			<img src="<?php trs_portrait_to_crop() ?>" id="portrait-to-crop" class="portrait" alt="<?php _e( 'Avatar to crop', 'trendr' ) ?>" />

			<div id="portrait-crop-pane">
				<img src="<?php trs_portrait_to_crop() ?>" id="portrait-crop-preview" class="portrait" alt="<?php _e( 'Avatar preview', 'trendr' ) ?>" />
			</div>

			<input type="submit" name="portrait-crop-submit" id="portrait-crop-submit" value="<?php _e( 'Crop Image', 'trendr' ) ?>" />

			<input type="hidden" name="image_src" id="image_src" value="<?php trs_portrait_to_crop_src() ?>" />
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />

			<?php trm_nonce_field( 'trs_portrait_cropstore' ) ?>

		<?php endif; ?>

	</form>

<?php else : ?>

	<p><?php _e( 'Your portrait will be used on your profile and throughout the site. To change your portrait, please create an account with <a href="http://grportrait.com">Grportrait</a> using the same email address as you used to register with this site.', 'trendr' ) ?></p>

<?php endif; ?>

<?php do_action( 'trs_after_profile_portrait_upload_content' ) ?>
