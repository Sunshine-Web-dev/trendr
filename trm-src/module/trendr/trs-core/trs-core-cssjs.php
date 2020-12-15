<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * trs_core_admin_menu_icon_css()
 *
 * Add a hover-able icon to the "Trnder" Backend-WeaprEcqaKejUbRq-trendr area menu.
 *
 * @package Trnder Core
 */



/**
 * trs_core_add_jquery_cropper()
 *
 * Makes sure the jQuery jCrop library is loaded.
 *
 * @package Trnder Core
 */
function trs_core_add_jquery_cropper() {
	trm_enqueue_script( 'jcrop', array( 'jquery' ) );
	add_action( 'trm_head', 'trs_core_add_cropper_inline_js' );
	add_action( 'trm_head', 'trs_core_add_cropper_inline_css' );
}

/**
 * trs_core_add_cropper_inline_js()
 *
 * Adds the inline JS needed for the cropper to work on a per-page basis.
 *
 * @package Trnder Core
 */
function trs_core_add_cropper_inline_js() {
	global $trs;

	$image = apply_filters( 'trs_inline_cropper_image', getimagesize( trs_core_portrait_upload_path() . $trs->portrait_admin->image->dir ) );
	if ( empty( $image ) )
		return;

	//
	$full_height = trs_core_portrait_full_height();
	$full_width  = trs_core_portrait_full_width();

	// Calculate Aspect Ratio
	if ( !empty( $full_height ) && ( $full_width != $full_height ) ) {
		$aspect_ratio = $full_width / $full_height;
	} else {
		$aspect_ratio = 1;
	}

	// Default cropper coordinates
	$crop_left   = $image[0] / 4;
	$crop_top    = $image[1] / 4;
	$crop_right  = $image[0] - $crop_left;
	$crop_bottom = $image[1] - $crop_top; ?>

	<script type="text/javascript">
		<?php if(!is_ajax()):?> 
		    jQuery(window).load( function(){
        <?php endif; ?>
        
			jQuery('#portrait-to-crop').Jcrop({
				onChange: showPreview,
				onSelect: showPreview,
				onSelect: updateCoords,
				aspectRatio: <?php echo $aspect_ratio; ?>,
				setSelect: [ <?php echo $crop_left; ?>, <?php echo $crop_top; ?>, <?php echo $crop_right; ?>, <?php echo $crop_bottom; ?> ]
			});
			updateCoords({x: <?php echo $crop_left; ?>, y: <?php echo $crop_top; ?>, w: <?php echo $crop_right; ?>, h: <?php echo $crop_bottom; ?>});
        
        <?php if(!is_ajax()):?>			
		});
		<?php endif; ?>

		function updateCoords(c) {
			jQuery('#x').val(c.x);
			jQuery('#y').val(c.y);
			jQuery('#w').val(c.w);
			jQuery('#h').val(c.h);
		}

		function showPreview(coords) {
			if ( parseInt(coords.w) > 0 ) {
				var fw = <?php echo $full_width; ?>;
				var fh = <?php echo $full_height; ?>;
				var rx = fw / coords.w;
				var ry = fh / coords.h;


				$( '.broadcast-field' ).hide();
				$( '.standard-form' ).addClass('selected');
				$( '.standard-form.selected' ).css('background','gray');
              $('#nav2').hide();
				$( '#portrait-crop-preview' ).css('width','20%');

				jQuery( '#portrait-crop-preview' ).css({
					width: Math.round(rx * <?php echo $image[0]; ?>) + 'px',
					height: Math.round(ry * <?php echo $image[1]; ?>) + 'px',
					marginLeft: '-' + Math.round(rx * coords.x) + 'px',
					marginTop: '-' + Math.round(ry * coords.y) + 'px'
				});
			}
		}
	</script>

<?php
}

/**
 * trs_core_add_cropper_inline_css()
 *
 * Adds the inline CSS needed for the cropper to work on a per-page basis.
 *
 * @package Trnder Core
 */
function trs_core_add_cropper_inline_css() {
	global $trs;
?>

	<style type="text/css">
		.jcrop-holder { float: left; margin: 0 20px 20px 0; text-align: left; }
		.jcrop-vline, .jcrop-hline { font-size: 0; position: absolute; background: white top left repeat url( <?php echo TRS_PLUGIN_URL ?>/trs-core/images/Jcrop.gif ); }
		.jcrop-vline { height: 100%; width: 1px !important; }
		.jcrop-hline { width: 100%; height: 1px !important; }
		.jcrop-handle { font-size: 1px; width: 7px !important; height: 7px !important; border: 1px #eee solid; background-color: #333; *width: 9px; *height: 9px; }
		.jcrop-tracker { width: 100%; height: 100%; }
		.custom .jcrop-vline, .custom .jcrop-hline { background: yellow; }
		.custom .jcrop-handle { border-color: black; background-color: #C7BB00; -moz-border-radius: 3px; -webkit-border-radius: 3px; }
		#portrait-crop-pane { width: <?php echo trs_core_portrait_full_width() ?>px; height: <?php echo trs_core_portrait_full_height() ?>px; overflow: hidden; }
		#portrait-crop-submit { margin: 20px 0; }
		#portrait-upload-form img, #create-group-form img, #group-settings-form img { border: none !important; }
	</style>

<?php
}

/**
 * trs_core_add_ajax_url_js()
 *
 * Adds AJAX target URL so themes can access the Trnder AJAX functionality.
 *
 * @package Trnder Core
 */
function trs_core_add_ajax_url_js() {
	global $trs;
?>

	<script type="text/javascript">var ajaxurl = "<?php echo site_url( 'initiate.php' ); ?>";</script>

<?php
}
add_action( 'trm_head', 'trs_core_add_ajax_url_js' );

?>