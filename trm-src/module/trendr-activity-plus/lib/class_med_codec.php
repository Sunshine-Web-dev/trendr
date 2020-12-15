<?php
/**
 * Med shortcode coder/decoder class.
 *
 * Responsible for handling all things shortcode:
 * 1) Resgisters shortcode decoding procedures
 * 2) Decodes shortcodes and creates proper markup on post render
 * 3) Encodes requests into shortcodes on post save
 */
class MedCodec {

	/**
	 * Processes link-type shortcode and create proper markup.
	 * Relies on ./forms/link_tag_template.php for markup rendering.
	 */
	function process_link_tag ($atts, $body) {
		extract(shortcode_atts(array(
			'url' => false,
			'title' => false,
			'image' => false,
		), $atts));
		if (!$url) return '';

		$template = locate_template(array('link_tag_template.php'));
		if (empty($template)) $template = MED_PLUGIN_BASE_DIR . '/lib/forms/link_tag_template.php';

		ob_start();
		@include $template;
		$out = ob_get_clean();
		return $out;
	}

	/**
	 * Creates the proper shortcode tag based on the submitted data.
	 */
	function create_link_tag ($url, $title, $body='', $image='') {
		if (!$url) return '';
		$title = $this->_escape_shortcode($title);
		$body = !empty($body) ? $this->_escape_shortcode($body) : $title;
		$title = esc_attr($title);
		$image = esc_url($image);
		$url = esc_url($url);
		return "[med_link url='{$url}' title='{$title}' image='{$image}']{$body}[/med_link]";
	}

	/**
	 * Escape shortcode-breaking characters.
	 *
	 * @param string $string String to process
	 *
	 * @return string
	 */
	private function _escape_shortcode ($string='') {
		if (empty($string)) return $string;

		$string = preg_replace('/' . preg_quote('[', '/') . '/', '&#91;', $string);
		$string = preg_replace('/' . preg_quote(']', '/') . '/', '&#93;', $string);

		return $string;
	}

	/**
	 * Processes video-type shortcode and create proper markup.
	 * Relies on `trm_oembed_get()` for markup rendering.
	 */

	/**
	 * Processes video-type shortcode and create proper markup.
	 * Relies on `trm_oembed_get()` for markup rendering.
	 */
	//function process_video_tag ($atts, $content) {
		//return trm_oembed_get($content, array('width' => Trsfb_Data::get('oembed_width', 450)));
	//}
	/**
	 * Creates the proper shortcode tag based on the submitted data.
	 */
	function create_video_tag ($url) {
		if (!$url) return '';
		$url = preg_match('/^https?:\/\//i', $url) ? $url : MED_PROTOCOL . $url;
					//	$url = str_replace('http://', 'https://', $url ); 

		$url = esc_url($url);
		return "[med_video]{$url}[/med_video]";
	}

	/**
	 * Processes images-type shortcode and create proper markup.
	 * Relies on ./forms/images_tag_template.php for markup rendering.
	 */
	function process_images_tag ($atts, $content) {
		$images = self::extract_images($content);
		//return var_export($images,1);
		$activity_id = trs_get_activity_id();

		global $blog_id;
		$activity_blog_id = $blog_id;
		$use_thickbox = defined('MED_USE_THICKBOX') ? esc_attr(MED_USE_THICKBOX) : 'thickbox';

		if ($activity_id) {
			$activity_blog_id = trs_activity_get_meta($activity_id, 'med_blog_id');
		}

		$template = locate_template(array('images_tag_template.php'));
		if (empty($template)) $template = MED_PLUGIN_BASE_DIR . '/lib/forms/images_tag_template.php';
 
		ob_start();

		@include $template;

		$out = ob_get_clean();

		return $out;
	}

	/**
	 * Creates the proper shortcode tag based on the submitted data.
	 */
	function create_images_tag ($imgs) {
		if (!$imgs) return '';
		if (!is_array($imgs)) $imgs = array($imgs);
		return "[med_images]\n" . join("\n", $imgs) . "\n[/med_images]";
	}

	/**
	 * Wrap shortcode execution in a quick check.
	 *
	 * @param string $content Content to check for shortcode and process accordingly
	 *
	 * @return string
	 */
	public function do_shortcode ($content='') {

		if (false === strpos($content, '[med_')) return $content;

		remove_filter('trs_get_activity_content_body', 'stripslashes_deep', 5); // Drop this because we'll be doing this right now
		$content = stripslashes_deep($content); // ... and process immediately, before allowing shortcode processing

		return do_shortcode($content);
	}

	/**
	 * Registers shotcode processing procedures.
	 */
	function register () {
		$me = new MedCodec;
		add_shortcode('med_link', array($me, 'process_link_tag'));
		add_shortcode('med_video', array($me, 'process_video_tag'));
		add_shortcode('med_images', array($me, 'process_images_tag'));

		// A fix for Ray's "oEmbed for trendr" and similar plugins
		add_filter('trs_get_activity_content_body', array($me, 'do_shortcode'), 1);
		// RSS feed processing
		add_filter('trs_get_activity_feed_item_description', 'do_shortcode');
	}

	/**
	 * Checks whether we have an images list shortcode in content.
	 * @param  string $content String to check
	 * @return boolean
	 */
	public static function has_images ($content) {
		return has_shortcode($content, 'med_images');
	}

	/**
	 * Extracts images from shortcode content.
	 * @param  string $shortcode_content Shortcode contents
	 * @return array
	 */
	public static function extract_images ($shortcode_content) {
		return explode("\n", trim(strip_tags($shortcode_content)));
	}
}
