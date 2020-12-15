<h3><?php _e( 'Change Photo', 'trendr' ) ?></h3>

<?php do_action( 'trs_before_profile_portrait_upload_content' ) ?>

<?php if ( !(int)trs_get_option( 'trs-disable-portrait-uploads' ) ) : ?>

    <form action="" id="turkey-upload-and-crop" class="standard-form" enctype="multipart/form-data">
        <?php if ( 'upload-image' === trs_get_portrait_admin_step() ) : ?>

            <?php trm_nonce_field( 'trs_portrait_upload' ) ?>

            <p id="turkey-portrait-upload">
                <input type="file" name="turkey-file" id="turkey-file" />
                <input type="submit" name="tukey-upload" id="tukey-upload" value="<?php _e( 'Save', 'trendr' ) ?>" />
                <input type="hidden" name="tukey-action" id="turkey-action" value="trs_portrait_upload" />
            </p>

        <?php endif; ?>
    </form>

	<form action="" method="post" id="portrait-upload-form" class="standard-form" enctype="multipart/form-data">

		<?php if ( 'upload-image' == trs_get_portrait_admin_step() ) : ?>

			<?php trm_nonce_field( 'trs_portrait_upload' ) ?>

			<p id="portrait-upload">
				<input type="file" name="file" id="file" />
				<input type="submit" name="upload" id="upload" value="<?php _e( 'Save', 'trendr' ) ?>" />
				<input type="hidden" name="action" id="action" value="trs_portrait_upload" />
			</p>

			<?php if ( trs_get_user_has_portrait() ) : ?>

				<p><a class="button edit" href="<?php trs_portrait_delete_link() ?>" title="<?php _e( 'Delete Portrait', 'trendr' ) ?>"><?php _e( 'Delete Photo', 'trendr' ) ?></a></p>
			<?php endif; ?>

		<?php endif; ?>

		<?php if ( 'crop-image' == trs_get_portrait_admin_step() ) : ?>


			<img src="<?php trs_portrait_to_crop() ?>" id="portrait-to-crop" class="portrait" alt="<?php _e( 'Portrait to crop', 'trendr' ) ?>" />

			<div id="portrait-crop-pane">
				<img src="<?php trs_portrait_to_crop() ?>" id="portrait-crop-preview" class="portrait" alt="<?php _e( 'Portrait preview', 'trendr' ) ?>" />
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

	

<?php endif; ?>

<?php do_action( 'trs_after_profile_portrait_upload_content' ) ?>
