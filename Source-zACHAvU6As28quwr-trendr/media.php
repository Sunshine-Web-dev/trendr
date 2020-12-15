<?php
/**
 * Scale down an image to fit a particular size and save a new copy of the image.
 *
 * The PNG transparency will be preserved using the function, as well as the
 * image type. If the file going in is PNG, then the resized image is going to
 * be PNG. The only supported image types are PNG, GIF, and JPEG.
 *
 * Some functionality requires API to exist, so some PHP version may lose out
 * support. This is not the fault of trendr (where functionality is
 * downgraded, not actual defects), but of your PHP version.
 *
 * @since 2.5.0
 *
 * @param string $file Image file path.
 * @param int $max_w Maximum width to resize to.
 * @param int $max_h Maximum height to resize to.
 * @param bool $crop Optional. Whether to crop image or resize.
 * @param string $suffix Optional. File Suffix.
 * @param string $dest_path Optional. New image file path.
 * @param int $jpeg_quality Optional, default is 90. Image quality percentage.
 * @return mixed TRM_Error on failure. String with new destination path.
 */
function image_resize( $file, $max_w, $max_h, $crop = false, $suffix = null, $dest_path = null, $jpeg_quality = 90 ) {

	$image = trm_load_image( $file );
	if ( !is_resource( $image ) )
		return new TRM_Error( 'error_loading_image', $image, $file );

	$size = @getimagesize( $file );
	if ( !$size )
		return new TRM_Error('invalid_image', __('Could not read image size'), $file);
	list($orig_w, $orig_h, $orig_type) = $size;

	$dims = image_resize_dimensions($orig_w, $orig_h, $max_w, $max_h, $crop);
	if ( !$dims )
		return new TRM_Error( 'error_getting_dimensions', __('Could not calculate resized image dimensions') );
	list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $dims;

	$newimage = trm_imagecreatetruecolor( $dst_w, $dst_h );

	imagecopyresampled( $newimage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

	// convert from full colors to index colors, like original PNG.
	if ( IMAGETYPE_PNG == $orig_type && function_exists('imageistruecolor') && !imageistruecolor( $image ) )
		imagetruecolortopalette( $newimage, false, imagecolorstotal( $image ) );

	// we don't need the original in memory anymore
	imagedestroy( $image );

	// $suffix will be appended to the destination filename, just before the extension
	if ( !$suffix )
		$suffix = "{$dst_w}x{$dst_h}";

	$info = pathinfo($file);
	$dir = $info['dirname'];
	$ext = $info['extension'];
	$name = trm_basename($file, ".$ext");

	if ( !is_null($dest_path) and $_dest_path = realpath($dest_path) )
		$dir = $_dest_path;
	$destfilename = "{$dir}/{$name}-{$suffix}.{$ext}";

	if ( IMAGETYPE_GIF == $orig_type ) {
		if ( !imagegif( $newimage, $destfilename ) )
			return new TRM_Error('resize_path_invalid', __( 'Resize path invalid' ));
	} elseif ( IMAGETYPE_PNG == $orig_type ) {
		if ( !imagepng( $newimage, $destfilename ) )
			return new TRM_Error('resize_path_invalid', __( 'Resize path invalid' ));
	} else {
		// all other formats are converted to jpg
		$destfilename = "{$dir}/{$name}-{$suffix}.jpg";
		if ( !imagejpeg( $newimage, $destfilename, apply_filters( 'jpeg_quality', $jpeg_quality, 'image_resize' ) ) )
			return new TRM_Error('resize_path_invalid', __( 'Resize path invalid' ));
	}

	imagedestroy( $newimage );

	// Set correct file permissions
	$stat = stat( dirname( $destfilename ));
	$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
	@ chmod( $destfilename, $perms );

	return $destfilename;
}
/**
 * Load an image from a string, if PHP supports it.
 *
 * @since 2.1.0
 *
 * @param string $file Filename of the image to load.
 * @return resource The resulting image resource on success, Error string on failure.
 */
function trm_load_image( $file ) {
	if ( is_numeric( $file ) )
		$file = get_attached_file( $file );

	if ( ! file_exists( $file ) )
		return sprintf(__('File &#8220;%s&#8221; doesn&#8217;t exist?'), $file);

	if ( ! function_exists('imagecreatefromstring') )
		return __('The GD image library is not installed.');

	// Set artificially high because GD uses uncompressed images in memory
	@ini_set( 'memory_limit', apply_filters( 'image_memory_limit', TRM_MAX_MEMORY_LIMIT ) );
	$image = imagecreatefromstring( file_get_contents( $file ) );

	if ( !is_resource( $image ) )
		return sprintf(__('File &#8220;%s&#8221; is not an image.'), $file);

	return $image;
}

/**
 * Create new GD image resource with transparency support
 *
 * @since 2.9.0
 *
 * @param int $width Image width
 * @param int $height Image height
 * @return image resource
 */
function trm_imagecreatetruecolor($width, $height) {
	$img = imagecreatetruecolor($width, $height);
	if ( is_resource($img) && function_exists('imagealphablending') && function_exists('imagesavealpha') ) {
		imagealphablending($img, false);
		imagesavealpha($img, true);
	}
	return $img;
}

/**
 * API for easily embedding rich media such as videos and images into content.
 *
 * @package Trnder
 * @subpackage Embed
 * @since 2.9.0
 */
class TRM_Embed {
	var $handlers = array();
	var $post_ID;
	var $usecache = true;
	var $linkifunknown = true;

	/**
	 * Constructor
	 */
	function __construct() {
		// Hack to get the [embed] shortcode to run before trmautop()
		add_filter( 'the_content', array(&$this, 'run_shortcode'), 8 );

		// Shortcode placeholder for strip_shortcodes()
		add_shortcode( 'embed', '__return_false' );

		// Attempts to embed all URLs in a post
		if ( get_option('embed_autourls') )
			add_filter( 'the_content', array(&$this, 'autoembed'), 8 );

		// After a post is saved, invalidate the oEmbed cache
		add_action( 'save_post', array(&$this, 'delete_oembed_caches') );

		// After a post is saved, cache oEmbed items via AJAX
		add_action( 'edit_form_advanced', array(&$this, 'maybe_run_ajax_cache') );
	}

	/**
	 * Process the [embed] shortcode.
	 *
	 * Since the [embed] shortcode needs to be run earlier than other shortcodes,
	 * this function removes all existing shortcodes, registers the [embed] shortcode,
	 * calls {@link do_shortcode()}, and then re-registers the old shortcodes.
	 *
	 * @uses $shortcode_tags
	 * @uses remove_all_shortcodes()
	 * @uses add_shortcode()
	 * @uses do_shortcode()
	 *
	 * @param string $content Content to parse
	 * @return string Content with shortcode parsed
	 */
	function run_shortcode( $content ) {
		global $shortcode_tags;

		// Back up current registered shortcodes and clear them all out
		$orig_shortcode_tags = $shortcode_tags;
		remove_all_shortcodes();

		add_shortcode( 'embed', array(&$this, 'shortcode') );

		// Do the shortcode (only the [embed] one is registered)
		$content = do_shortcode( $content );

		// Put the original shortcodes back
		$shortcode_tags = $orig_shortcode_tags;

		return $content;
	}

	/**
	 * If a post/page was saved, then output Javascript to make
	 * an AJAX request that will call TRM_Embed::cache_oembed().
	 */
	function maybe_run_ajax_cache() {
		global $post_ID;

		if ( empty($post_ID) || empty($_GET['message']) || 1 != $_GET['message'] )
			return;

?>
<script type="text/javascript">
/* <![CDATA[ */
	jQuery(document).ready(function($){
		$.get("<?php echo admin_url( 'admin-ajax.php?action=oembed-cache&post=' . $post_ID ); ?>");
	});
/* ]]> */
</script>
<?php
	}

	/**
	 * Register an embed handler. Do not use this function directly, use {@link trm_embed_register_handler()} instead.
	 * This function should probably also only be used for sites that do not support oEmbed.
	 *
	 * @param string $id An internal ID/name for the handler. Needs to be unique.
	 * @param string $regex The regex that will be used to see if this handler should be used for a URL.
	 * @param callback $callback The callback function that will be called if the regex is matched.
	 * @param int $priority Optional. Used to specify the order in which the registered handlers will be tested (default: 10). Lower numbers correspond with earlier testing, and handlers with the same priority are tested in the order in which they were added to the action.
	 */
	function register_handler( $id, $regex, $callback, $priority = 10 ) {
		$this->handlers[$priority][$id] = array(
			'regex'    => $regex,
			'callback' => $callback,
		);
	}

	/**
	 * Unregister a previously registered embed handler. Do not use this function directly, use {@link trm_embed_unregister_handler()} instead.
	 *
	 * @param string $id The handler ID that should be removed.
	 * @param int $priority Optional. The priority of the handler to be removed (default: 10).
	 */
	function unregister_handler( $id, $priority = 10 ) {
		if ( isset($this->handlers[$priority][$id]) )
			unset($this->handlers[$priority][$id]);
	}

	/**
	 * The {@link do_shortcode()} callback function.
	 *
	 * Attempts to convert a URL into embed HTML. Starts by checking the URL against the regex of the registered embed handlers.
	 * If none of the regex matches and it's enabled, then the URL will be given to the {@link TRM_oEmbed} class.
	 *
	 * @uses trm_oembed_get()
	 * @uses trm_parse_args()
	 * @uses trm_embed_defaults()
	 * @uses TRM_Embed::maybe_make_link()
	 * @uses get_option()
	 * @uses current_user_can()
	 * @uses trm_cache_get()
	 * @uses trm_cache_set()
	 * @uses get_post_meta()
	 * @uses update_post_meta()
	 *
	 * @param array $attr Shortcode attributes.
	 * @param string $url The URL attempting to be embeded.
	 * @return string The embed HTML on success, otherwise the original URL.
	 */
	function shortcode( $attr, $url = '' ) {
		global $post;

		if ( empty($url) )
			return '';

		$rawattr = $attr;
		$attr = trm_parse_args( $attr, trm_embed_defaults() );

		// kses converts & into &amp; and we need to undo this
		// See http://core.trac./ticket/11311
		$url = str_replace( '&amp;', '&', $url );

		// Look for known internal handlers
		ksort( $this->handlers );
		foreach ( $this->handlers as $priority => $handlers ) {
			foreach ( $handlers as $id => $handler ) {
				if ( preg_match( $handler['regex'], $url, $matches ) && is_callable( $handler['callback'] ) ) {
					if ( false !== $return = call_user_func( $handler['callback'], $matches, $attr, $url, $rawattr ) )
						return apply_filters( 'embed_handler_html', $return, $url, $attr );
				}
			}
		}

		$post_ID = ( !empty($post->ID) ) ? $post->ID : null;
		if ( !empty($this->post_ID) ) // Potentially set by TRM_Embed::cache_oembed()
			$post_ID = $this->post_ID;

		// Unknown URL format. Let oEmbed have a go.
		if ( $post_ID ) {

			// Check for a cached result (stored in the post meta)
			$cachekey = '_oembed_' . md5( $url . serialize( $attr ) );
			if ( $this->usecache ) {
				$cache = get_post_meta( $post_ID, $cachekey, true );

				// Failures are cached
				if ( '{{unknown}}' === $cache )
					return $this->maybe_make_link( $url );

				if ( !empty($cache) )
					return apply_filters( 'embed_oembed_html', $cache, $url, $attr, $post_ID );
			}

			// Use oEmbed to get the HTML
			$attr['discover'] = ( apply_filters('embed_oembed_discover', false) && author_can( $post_ID, 'unfiltered_html' ) );
			$html = trm_oembed_get( $url, $attr );

			// Cache the result
			$cache = ( $html ) ? $html : '{{unknown}}';
			update_post_meta( $post_ID, $cachekey, $cache );

			// If there was a result, return it
			if ( $html )
				return apply_filters( 'embed_oembed_html', $html, $url, $attr, $post_ID );
		}

		// Still unknown
		return $this->maybe_make_link( $url );
	}

	/**
	 * Delete all oEmbed caches.
	 *
	 * @param int $post_ID Post ID to delete the caches for.
	 */
	function delete_oembed_caches( $post_ID ) {
		$post_metas = get_post_custom_keys( $post_ID );
		if ( empty($post_metas) )
			return;

		foreach( $post_metas as $post_meta_key ) {
			if ( '_oembed_' == substr( $post_meta_key, 0, 8 ) )
				delete_post_meta( $post_ID, $post_meta_key );
		}
	}

	/**
	 * Triggers a caching of all oEmbed results.
	 *
	 * @param int $post_ID Post ID to do the caching for.
	 */
	function cache_oembed( $post_ID ) {
		$post = get_post( $post_ID );

		if ( empty($post->ID) || !in_array( $post->post_type, apply_filters( 'embed_cache_oembed_types', array( 'post', 'page' ) ) ) )
			return;

		// Trigger a caching
		if ( !empty($post->post_content) ) {
			$this->post_ID = $post->ID;
			$this->usecache = false;

			$content = $this->run_shortcode( $post->post_content );
			if ( get_option('embed_autourls') )
				$this->autoembed( $content );

			$this->usecache = true;
		}
	}

	/**
	 * Passes any unlinked URLs that are on their own line to {@link TRM_Embed::shortcode()} for potential embedding.
	 *
	 * @uses TRM_Embed::autoembed_callback()
	 *
	 * @param string $content The content to be searched.
	 * @return string Potentially modified $content.
	 */
	function autoembed( $content ) {
		return preg_replace_callback( '|^\s*(https?://[^\s"]+)\s*$|im', array(&$this, 'autoembed_callback'), $content );
	}

	/**
	 * Callback function for {@link TRM_Embed::autoembed()}.
	 *
	 * @uses TRM_Embed::shortcode()
	 *
	 * @param array $match A regex match array.
	 * @return string The embed HTML on success, otherwise the original URL.
	 */
	function autoembed_callback( $match ) {
		$oldval = $this->linkifunknown;
		$this->linkifunknown = false;
		$return = $this->shortcode( array(), $match[1] );
		$this->linkifunknown = $oldval;

		return "\n$return\n";
	}

	/**
	 * Conditionally makes a hyperlink based on an internal class variable.
	 *
	 * @param string $url URL to potentially be linked.
	 * @return string Linked URL or the original URL.
	 */
	function maybe_make_link( $url ) {
		$output = ( $this->linkifunknown ) ? '<a href="' . esc_attr($url) . '">' . esc_html($url) . '</a>' : $url;
		return apply_filters( 'embed_maybe_make_link', $output, $url );
	}
}
$trm_embed = new TRM_Embed();

/**
 * Register an embed handler. This function should probably only be used for sites that do not support oEmbed.
 *
 * @since 2.9.0
 * @see TRM_Embed::register_handler()
 */
function trm_embed_register_handler( $id, $regex, $callback, $priority = 10 ) {
	global $trm_embed;
	$trm_embed->register_handler( $id, $regex, $callback, $priority );
}

/**
 * Unregister a previously registered embed handler.
 *
 * @since 2.9.0
 * @see TRM_Embed::unregister_handler()
 */
function trm_embed_unregister_handler( $id, $priority = 10 ) {
	global $trm_embed;
	$trm_embed->unregister_handler( $id, $priority );
}

/**
 * Create default array of embed parameters.
 *
 * @since 2.9.0
 *
 * @return array Default embed parameters.
 */
function trm_embed_defaults() {
	if ( !empty($GLOBALS['content_width']) )
		$theme_width = (int) $GLOBALS['content_width'];

	$width = get_option('embed_size_w');

	if ( empty($width) && !empty($theme_width) )
		$width = $theme_width;

	if ( empty($width) )
		$width = 500;

	$height = get_option('embed_size_h');

	if ( empty($height) )
		$height = 700;

	return apply_filters( 'embed_defaults', array(
		'width'  => $width,
		'height' => $height,
	) );
}

/**
 * Based on a supplied width/height example, return the biggest possible dimensions based on the max width/height.
 *
 * @since 2.9.0
 * @uses trm_constrain_dimensions() This function passes the widths and the heights.
 *
 * @param int $example_width The width of an example embed.
 * @param int $example_height The height of an example embed.
 * @param int $max_width The maximum allowed width.
 * @param int $max_height The maximum allowed height.
 * @return array The maximum possible width and height based on the example ratio.
 */
function trm_expand_dimensions( $example_width, $example_height, $max_width, $max_height ) {
	$example_width  = (int) $example_width;
	$example_height = (int) $example_height;
	$max_width      = (int) $max_width;
	$max_height     = (int) $max_height;

	return trm_constrain_dimensions( $example_width * 1000000, $example_height * 1000000, $max_width, $max_height );
}

/**
 * Attempts to fetch the embed HTML for a provided URL using oEmbed.
 *
 * @since 2.9.0
 * @see TRM_oEmbed
 *
 * @uses _trm_oembed_get_object()
 * @uses TRM_oEmbed::get_html()
 *
 * @param string $url The URL that should be embeded.
 * @param array $args Addtional arguments and parameters.
 * @return string The original URL on failure or the embed HTML on success.
 */
function trm_oembed_get( $url, $args = '' ) {
	require_once( ABSPATH . TRMINC . '/class-oembed.php' );
	$oembed = _trm_oembed_get_object();
	return $oembed->get_html( $url, $args );
}

/**
 * Adds a URL format and oEmbed provider URL pair.
 *
 * @since 2.9.0
 * @see TRM_oEmbed
 *
 * @uses _trm_oembed_get_object()
 *
 * @param string $format The format of URL that this provider can handle. You can use asterisks as wildcards.
 * @param string $provider The URL to the oEmbed provider.
 * @param boolean $regex Whether the $format parameter is in a regex format.
 */
function trm_oembed_add_provider( $format, $provider, $regex = false ) {
	require_once( ABSPATH . TRMINC . '/class-oembed.php' );
	$oembed = _trm_oembed_get_object();
	$oembed->providers[$format] = array( $provider, $regex );
}

/**
 * Determines if default embed handlers should be loaded.
 *
 * Checks to make sure that the embeds library hasn't already been loaded. If
 * it hasn't, then it will load the embeds library.
 *
 * @since 2.9.0
 */
function trm_maybe_load_embeds() {
	if ( ! apply_filters( 'load_default_embeds', true ) )
		return;
	trm_embed_register_handler( 'googlevideo', '#http://video\.google\.([A-Za-z.]{2,5})/videoplay\?docid=([\d-]+)(.*?)#i', 'trm_embed_handler_googlevideo' );
}

/**
 * The Google Video embed handler callback. Google Video does not support oEmbed.
 *
 * @see TRM_Embed::register_handler()
 * @see TRM_Embed::shortcode()
 *
 * @param array $matches The regex matches from the provided regex when calling {@link trm_embed_register_handler()}.
 * @param array $attr Embed attributes.
 * @param string $url The original URL that was matched by the regex.
 * @param array $rawattr The original unmodified attributes.
 * @return string The embed HTML.
 */
function trm_embed_handler_googlevideo( $matches, $attr, $url, $rawattr ) {
	// If the user supplied a fixed width AND height, use it
	if ( !empty($rawattr['width']) && !empty($rawattr['height']) ) {
		$width  = (int) $rawattr['width'];
		$height = (int) $rawattr['height'];
	} else {
		list( $width, $height ) = trm_expand_dimensions( 425, 344, $attr['width'], $attr['height'] );
	}

	return apply_filters( 'embed_googlevideo', '<embed type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docid=' . esc_attr($matches[2]) . '&amp;hl=en&amp;fs=true" style="width:' . esc_attr($width) . 'px;height:' . esc_attr($height) . 'px" allowFullScreen="true" allowScriptAccess="always" />', $matches, $attr, $url, $rawattr );
}
?>