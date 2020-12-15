<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * trs_core_admin_menu_icon_css()
 *
 * Add a hover-able icon to the "trendr" Backend-WeaprEcqaKejUbRq-trendr area menu.
 *
 * @package trendr Core
 */
function trs_core_admin_menu_icon_css() {
	global $trs; ?>

	<style type="text/css">
		/* Wizard Icon */
		ul#adminmenu li.toplevel_page_trs-wizard .trm-menu-image a img { display: none; }
		ul#adminmenu li.toplevel_page_trs-wizard .trm-menu-image a { background-image: url( <?php echo plugins_url( 'trendr/trs-core/images/admin_menu_icon.png' ) ?> ) !important; background-position: -1px -32px; }
		ul#adminmenu li.toplevel_page_trs-wizard:hover .trm-menu-image a,
		ul#adminmenu li.toplevel_page_trs-wizard.trm-has-current-submenu .trm-menu-image a {
			background-position: -1px 0;
		}

		/* Settings Icon */
		ul#adminmenu li.toplevel_page_trs-general-settings .trm-menu-image a img { display: none; }
		ul#adminmenu li.toplevel_page_trs-general-settings .trm-menu-image a { background-image: url( <?php echo plugins_url( 'trendr/trs-core/images/admin_menu_icon.png' ) ?> ) !important; background-position: -1px -32px; }
		ul#adminmenu li.toplevel_page_trs-general-settings:hover .trm-menu-image a,
		ul#adminmenu li.toplevel_page_trs-general-settings.trm-has-current-submenu .trm-menu-image a {
			background-position: -1px 0;
		}
	</style>

<?php
}
add_action( 'admin_head', 'trs_core_admin_menu_icon_css' );

function trs_core_confirmation_js() {
	global $trmdb;

	if ( is_multisite() && $trmdb->blogid != trs_get_root_blog_id() )
		return false;

	if ( !trm_script_is( 'jquery' ) )
		trm_enqueue_script( 'jquery' );

	if ( !trm_script_is( 'jquery', 'done' ) )
		trm_print_scripts( 'jquery' ); ?>

	<script type="text/javascript"> jQuery(document).ready( function() { jQuery("a.confirm").click( function() { if ( confirm( '<?php _e( 'Are you sure?', 'trendr' ) ?>' ) ) return true; else return false; }); });</script>

<?php
}
add_action( 'trm_head', 'trs_core_confirmation_js', 100 );

/**
 * trs_core_add_jquery_cropper()
 *
 * Makes sure the jQuery jCrop library is loaded.
 *
 * @package trendr Core
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
 * @package trendr Core
 */
function trs_core_add_cropper_inline_js() {
	global $trs;

	$image = apply_filters( 'trs_inline_cropper_image', getimagesize( trs_core_portrait_upload_path() . $trs->portrait_admin->image->dir ) );
	$aspect_ratio = 1;

	$full_height = trs_core_portrait_full_height();
	$full_width  = trs_core_portrait_full_width();

	// Calculate Aspect Ratio
	if ( $full_height && ( $full_width != $full_height ) )
		$aspect_ratio = $full_width / $full_height;

	$width  = $image[0] / 2;
	$height = $image[1] / 2;
?>

	<script type="text/javascript">
		jQuery(window).load( function(){
			jQuery('#portrait-to-crop').Jcrop({
				onChange: showPreview,
				onSelect: showPreview,
				onSelect: updateCoords,
				aspectRatio: <?php echo $aspect_ratio ?>,
				setSelect: [ 50, 50, <?php echo $width ?>, <?php echo $height ?> ]
			});
			updateCoords({x: 50, y: 50, w: <?php echo $width ?>, h: <?php echo $height ?>});
		});

		function updateCoords(c) {
			jQuery('#x').val(c.x);
			jQuery('#y').val(c.y);
			jQuery('#w').val(c.w);
			jQuery('#h').val(c.h);
		};

		function showPreview(coords) {
			if ( parseInt(coords.w) > 0 ) {
				var rx = <?php echo $full_width; ?> / coords.w;
				var ry = <?php echo $full_height; ?> / coords.h;

				jQuery('#portrait-crop-preview').css({
				<?php if ( $image ) : ?>
					width: Math.round(rx * <?php echo $image[0]; ?>) + 'px',
					height: Math.round(ry * <?php echo $image[1]; ?>) + 'px',
				<?php endif; ?>
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
 * @package trendr Core
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
 * Adds AJAX target URL so themes can access the WordPress AJAX functionality.
 *
 * @package trendr Core
 */
function trs_core_add_ajax_url_js() {
	global $trs;
?>

	<script type="text/javascript">var ajaxurl = "<?php echo site_url( 'initiate.php' ); ?>";</script>

<?php
}
add_action( 'trm_head', 'trs_core_add_ajax_url_js' );

?>