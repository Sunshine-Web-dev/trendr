<?php
/**
 * Main handler class.
 * Responsible for the overall functionality.
 */
class MedBinder {

	/**
	 * Main entry method.
	 *
	 * @access public
	 * @static
	 */
	public static function serve () {
		$me = new MedBinder;
		$me->add_hooks();
	}

	/**
	 * Image moving and resizing routine.
	 *
	 * Relies on TRM built-in image resizing.
	 *
	 * @param array Image paths to move from temp directory
	 * @return mixed Array of new image paths, or (bool)false on failure.
	 * @access private
	 */
	function move_images ($imgs) {
		if (!$imgs) return false;
		if (!is_array($imgs)) $imgs = array($imgs);

		global $trs;
		$ret = array();

		list($thumb_w,$thumb_h) = Med_Data::get_thumbnail_size();
		
		$processed = 0;
		foreach ($imgs as $img) {
			$processed++;
			if (MED_IMAGE_LIMIT && $processed > MED_IMAGE_LIMIT) break; // Do not even bother to process more.
			if (preg_match('!^https?:\/\/!i', $img)) { // Just add remote images
				$ret[] = esc_url($img);
				continue;
			}
			
			$pfx = $trs->loggedin_user->id . '_' . preg_replace('/[^0-9]/', '-', microtime());
			$tmp_img = realpath(MED_TEMP_IMAGE_DIR . $img);
			$new_img = MED_BASE_IMAGE_DIR . "{$pfx}_{$img}";

			if (@rename($tmp_img, $new_img)) {
				if (function_exists('trm_get_image_editor')) { // New way of resizing the image
					$image = trm_get_image_editor($new_img);
					if (!is_trm_error($image)) {
					//	$thumb_filename  = $image->generate_filename('medt');
					//	$image->resize($thumb_w, $thumb_h, false);
						
						// Alright, now let's rotate if we can
						if (function_exists('exif_read_data')) {
							$exif = exif_read_data($new_img); // Okay, we now have the data
							if (!empty($exif['Orientation']) && 3 === (int)$exif['Orientation']) $image->rotate(180);
							else if (!empty($exif['Orientation']) && 6 === (int)$exif['Orientation']) $image->rotate(-90);
							else if (!empty($exif['Orientation']) && 8 === (int)$exif['Orientation']) $image->rotate(90);
						}
						//$image->save($thumb_filename);

						$image->save($new_img);
					}
				} else { // Old school fallback
					//image_resize($new_img, $thumb_w, $thumb_h, false, 'medt');
				}
				$ret[] = pathinfo($new_img, PATHINFO_BASENAME);
			} else return false; // Rename failure
		}

		return $ret;
	}

	/**
	 * Sanitizes the path and expands it into full form.
	 *
	 * @param string $file Relative file path
	 *
	 * @return mixed Sanitized path, or (bool)false on failure
	 */
	public static function resolve_temp_path ($file) {
		$file = ltrim($file, '/');
		
		// No subdirs in path, so we can do this quick check too
		if ($file !== basename($file)) return false;

		$tmp_path = trailingslashit(trm_normalize_path(realpath(MED_TEMP_IMAGE_DIR)));
		if (empty($tmp_path)) return false;

		$full_path = trm_normalize_path(realpath($tmp_path . $file));
		if (empty($full_path)) return false;

		// Are we still within our defined TMP dir?
		$rx = preg_quote($tmp_path, '/');
		$full_path = preg_match("/^{$rx}/", $full_path)
			? $full_path
			: false
		;
		if (empty($full_path)) return false;

		// Also, does this resolve to an actual file?
		return file_exists($full_path)
			? $full_path
			: false
		;
	}

	/**
	 * Remote page retrieving routine.
	 *
	 * @param string Remote URL
	 * @return mixed Remote page as string, or (bool)false on failure
	 * @access private
	 */
	function get_page_contents ($url) {
		$response = trm_remote_get($url);
		if (is_trm_error($response)) return false;
		return $response['body'];
	}


	/**
	 * Loads needed scripts and TRS_DTheme strings for JS.
	 */
	

	/**
	 * Loads required styles.
	 */
	//function css_load_styles () {
		//trm_enqueue_style('thickbox');
		//trm_enqueue_style('file_uploader_style', MED_PLUGIN_URL . '/css/external/fileuploader.css');
	//	if (!current_theme_supports('med_interface_style')) {
	//		trm_enqueue_style('med_interface_style', MED_PLUGIN_URL . '/css/med_interface.css');
	//	}
		//if (!current_theme_supports('med_toolbar_icons')) {
			//trm_enqueue_style('med_toolbar_icons', MED_PLUGIN_URL . '/css/med_toolbar.css');
		//}
	//}

	/**
	 * Handles video preview requests.
	 */
	function ajax_preview_video () {
		$url = !empty($_POST['data']) ? esc_url($_POST['data']) : false;
		$url = preg_match('/^https?:\/\//i', $url) ? $url : MED_PROTOCOL . $url;
		$warning = __('error reading video url', 'med');
        $response = $url ? __('Processing...', 'med') : $warning;
		$ret = trm_oembed_get($url);
    if ( $url = !$_POST['data'] ) {
        return false;
    }	echo ($ret ? $ret : $warning);	
		exit();
	}

	/**
	 * Handles link preview requests.
	 */
	function ajax_preview_link () {
		$url = !empty($_POST['data']) ? esc_url($_POST['data']) : false;
		$scheme = parse_url($url, PHP_URL_SCHEME);
		if (!$scheme || !preg_match('/^https?$/', $scheme)) {
			$url = "//{$url}";
		}

		//$warning = __('Error parsing your request', 'med');
		$response = $url ? __('Processing...', 'med') : $warning;
		$images = array();
		//$title = $warning;
		//$text = $warning;

		$page = $this->get_page_contents($url);
		if (!function_exists('str_get_html')) require_once(MED_PLUGIN_BASE_DIR . '/lib/external/simple_html_dom.php');
		$html = str_get_html($page);
		$str = $html->find('text');

		if ($str) {
			$image_els = $html->find('img');
			foreach ($image_els as $el) {
				if ($el->width > 100 && $el->height > 1) // Disregard spacers
					$images[] = $el->src;
			}
			$og_image = $html->find('meta[property=og:image]', 0);
			if ($og_image) array_unshift($images, $og_image->content);

			$title = $html->find('title', 0);
			$title = $title ? $title->plaintext: $url;

			$meta_description = $html->find('meta[name=description]', 0);
			$og_description = $html->find('meta[property=og:description]', 0);
			$first_paragraph = $html->find('p', 0);
			if ($og_description && $og_description->content) $text = $og_description->content;
			else if ($meta_description && $meta_description->content) $text = $meta_description->content;
			else if ($first_paragraph && $first_paragraph->plaintext) $text = $first_paragraph->plaintext;
			else $text = $title;
			
			$images = array_filter($images);
		} else {
			$url = '';
		}

		header('Content-type: application/json');
		echo json_encode(array(
			"url" => $url,
			"images" => $images,
			"title" => $title,
			"text" => $text,
		));
		exit();
	}

	/**
	 * Handles image preview requests.
	 * Relies on ./lib/external/file_uploader.php for images upload handling.
	 * Stores images in the temporary storage.
	 */
	function ajax_preview_photo () {
	    $dir = MED_PLUGIN_BASE_DIR . '/img/';
		if (!class_exists('qqFileUploader')) require_once(MED_PLUGIN_BASE_DIR . '/lib/external/file_uploader.php');
		$uploader = new qqFileUploader(self::_get_supported_image_extensions());
		$result = $uploader->handleUpload(MED_TEMP_IMAGE_DIR);
		//header('Content-type: application/json'); // For some reason, IE doesn't like this. Skip.
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
		exit();
	}

	/**
	 * Handles remote images preview
	 */
	function ajax_preview_remote_image () {
		header('Content-type: application/json');
		echo json_encode($_POST['data']);
		exit();
	}

	/**
	 * Clears up the temporary images storage.
	 */
	function ajax_remove_temp_images () {
		header('Content-type: application/json');
		parse_str($_POST['data'], $data);
		$data = is_array($data) ? $data : array('med_photos'=>array());
		foreach ($data['med_photos'] as $file) {
			$path = self::resolve_temp_path($file);
			if (!empty($path)) @unlink($path);
		}
		echo json_encode(array('status'=>'ok'));
		exit();
	}

    /**
     * @param $filename
     */
	function optimizeJpeg($filename = '', $quality = 90) {
        if(!file_exists($filename))
            return false;
        $cmd = sprintf(
            '/usr/bin/jpegoptim -m%s %s 2>&1',
            $quality, $filename
        );

        if(exec($cmd, $err)) {
            return true;
        }
        return $err;
    }

    /**
     * @param string $thumbnail
     * @return bool
     */
	function resizeVideoThumbnail($thumbnail = '') {
	    if(!file_exists($thumbnail))
	        return false;
	    // generate GD resource from source image
	    $src = imagecreatefromstring(file_get_contents($thumbnail));
	    $width = imagesx($src); // get image width
	    $height = imagesy($src); // get image height

	    $ratio = $height / $width; // get ratio of source image

	    $new_width = 413; // can update this width
	    $new_height = $ratio * $new_width; // the height respects the aspect ratio of the source

        // create new GD image to required dimensions
	    $dst = imagecreatetruecolor($new_width, $new_height);
	    // copy and resample source image into the new image
        imagecopyresampled($dst,$src,0,0,0,0,$new_width,$new_height,$width,$height);
        // save new image to file
        imagejpeg($dst, $thumbnail, 95); // you can update the quality factor in the range 0 - 100
        $this->optimizeJpeg($thumbnail);
        return true;
    }

	/**
	 * Create a thumbnail from the uploaded video
	 * @author turturkeykey
     * @param string $video
     * @return string
     */
    function createThumbnailFromVideo($video = '') {
        if(!file_exists($video))
            return false;

        $time = 1;
        $uploadDirectory = dirname($video);
        $thumbnail = preg_replace('~\..*$~', '.jpg', basename($video));

        $cmd = sprintf(
            '/usr/bin/ffmpeg -ss %s -i %s -vframes 1 -q:v 2 %s 2>&1',
            $time, $video, $uploadDirectory . '/' . $thumbnail
        );

        if(exec($cmd, $err)) {
            $this->resizeVideoThumbnail($uploadDirectory . '/' . $thumbnail);
            return $uploadDirectory . '/' . $thumbnail;
        }
        return $err;
    }

	/**
	 * This is where we actually save the activity update.
	 */
	function ajax_update_activity_contents () {
		$med_code = $activity = '';
		$aid = 0;
		$codec = new MedCodec;
		global $trs,$trmdb;
		if (!empty($_POST['data'])) {
			if (!empty($_POST['data']['med_video_url'])) {
				$med_code = $codec->create_video_tag($_POST['data']['med_video_url']);
				//added 6-18-18 creating object tag
			    $type = 'video_post';
			}
			if (!empty($_POST['data']['med_link_url'])) {
				$med_code = $codec->create_link_tag(
					$_POST['data']['med_link_url'],
					$_POST['data']['med_link_title'],
					$_POST['data']['med_link_body'],
					$_POST['data']['med_link_image']
				);
				 //added 6-18-18 creating object tag
				 $type = 'web_post';				
			}
			if (!empty($_POST['data']['med_photos'])) {
				$images = $this->move_images($_POST['data']['med_photos']);
				$med_code = $codec->create_images_tag($images);
				 //added 6-18-18 creating object tag
				// asamir enable other type of post in video
				 if(!empty($images)&& count($images) == 1){
					 	if(IsVideoFile($images[0])){
                            $type = 'video_post';
                            $this->createThumbnailFromVideo(MED_BASE_IMAGE_DIR . $images[0]);
						}else{
                            $type = 'photo_post';
						}

				 }else{
						 $type = 'photo_post';
					 }
			}
		}
		  //Added 6-18-18  gelo location data copatible
	        $place_details = trs_get_option( 'trschk_temp_location' );
            $place           = $place_details['place'];
			$longitude       = $place_details['longitude'];
			$latitude        = $place_details['latitude'];
			$add_as_my_place = $place_details['add_as_my_place'];

			$location_html = ' <div class=geo-head></div><a class=checkin-loc href="" target="_blank" title="' . $place . '">' . $place . '</a>';


        //removed 6-18-18 creating object tag
		//$med_code = apply_filters('med_code_before_save', $med_code);

		// All done creating tags. Now, save the code
		$gid = !empty($_POST['group_id']) && is_numeric($_POST['group_id']) 
			? (int)$_POST['group_id'] 
			: false
		;
		if ($med_code) {

        //removed 6-18-18 creating object tag

        //$content = !empty($_POST['content']) ? $_POST['content'] : '';
		//$content .= "\n{$med_code}";
		//$content = apply_filters('trs_activity_post_update_content', $content);
		//	$aid = $gid ?
		//groups_post_update(array('content' => $content, 'group_id' => $gid))
		//	:
		//trs_activity_post_update(array('content' => $content))
		//	;
			//global $blog_id;

		
    	$content = "\n" . $med_code . @$_POST['content'];
        //Added 6-18-18 creating geo and hashtag compatible

    	if ( ! empty( $place_details ) ) {
        $content = "\n" . $med_code . @$_POST['content'].   $location_html.$hashtags;

        }

		$med_code = apply_filters('med_code_before_save', $med_code);
        //Added 6-18-18 hashtag data compatible // line sensetive- do not move from this line
        $pattern = '/(#\w+)/u';
	    preg_match_all( $pattern, $content, $hashtags );
	    if ( $hashtags ) {
		/* Make sure there's only one instance of each tag */
        $hashtags = array_unique( $hashtags[1] );
		//but we need to watch for edits and if something was already wrapped in html link - thus check for space or word boundary prior
		foreach ( ( array ) $hashtags as $hashtag ) {
        $pattern = "/(^|\s|\b)" . $hashtag . "($|\b)/u" ;

     //unicode support???
	//$pattern = '/(#|\\uFF03)([a-z0-9_\\u00c0-\\u00d6\\u00d8-\\u00f6\\u00f8-\\u00ff]+)/i';
	//$pattern = '/(^|[^0-9A-Z&/]+)(#|\uFF03)([0-9A-Z_]*[A-Z_]+[a-z0-9_\\u00c0-\\u00d6\\u00d8-\\u00f6\\u00f8-\\u00ff]*)/i';
	//the twitter pattern
	//"(^|[^0-9A-Z&/]+)(#|\uFF03)([0-9A-Z_]*[A-Z_]+[a-z0-9_\\u00c0-\\u00d6\\u00d8-\\u00f6\\u00f8-\\u00ff]*)"
       $hashtag_noHash = str_replace( "#" , '' , $hashtag ) ;
       $content = str_replace( $hashtag , ' <a href="' . $trs->root_domain . "/" . $trs->activity->slug . "/" . TRS_ACTIVITY_HASHTAGS_SLUG . "/" . urlencode( htmlspecialchars( $hashtag_noHash ) ) . '" id="tg" >' . $hashtag . '</a>', $content ) ;
 
        }
    }
   if($_POST['story']=="true"){
		
		$trmdb->insert( 'trm_trs_stories', array("user_id" => $trs->loggedin_user->id, "content" => $content,"location"=>$_POST["location_address"] ,"date_recorded"=>trs_core_current_time(),"primary_link"=>trs_core_get_user_domain( $trs->loggedin_user->id ),"type"=>"post_media","categories"=>$_POST["categories"]));
		exit();
	}else{
     //added 6-18-18 creating object tag
	
	$user_id = $trs->loggedin_user->id;
    $userlink = trs_core_get_userlink( $user_id );
	
			$aid = $gid ?

				:

       trs_activity_add(
        array(
          'action' => apply_filters( 'activity_update', sprintf( __( '%s', 'trendr' ), $userlink ), $user_id ),
          'content' => $content,
          'component' => 'activity',
          'type' => $type,
          'user_id' => $user_id,
          'id' => $activity_id 
        )
      )
    ;	      
        global $blog_id;	


			trs_activity_update_meta($aid, 'med_blog_id', $blog_id);
		 // added 6-18-18 geo locattion caopatible
           if ( ! empty( $place_details ) ) {
			trs_activity_update_meta($aid, 'med_blog_id', $blog_id, $activity_id, 'trschk_place_details', $place_details );		}	
		}
		if ($aid) {
												if(isset($_POST['visibility'])){
					$visibility = esc_attr($_POST['visibility']);
					trs_activity_update_meta( $aid, 'activity-privacy', $visibility );
				}

			ob_start();
			if ( trs_has_activities ( 'include=' . $aid ) ) {
				while ( trs_activities() ) {
					trs_the_activity();
					if (function_exists('trs_locate_template')) trs_locate_template( array( 'activity/entry.php' ), true );
					else locate_template( array( 'activity/entry.php' ), true );
				}
			}
			$activity = ob_get_clean();
            // added 6-18-18 geo locattion caopatible
			delete_option( 'trschk_temp_location' );			
		}
		
		trs_activity_update_meta($aid,"categories",$_POST["categories"]);
		$address['place']=$_POST["location_address"];
		$address['latitude']=$_POST['latitude'];
		$address['longitude']=$_POST['longitude'];
		
		header('Content-type: application/json');
		echo json_encode(array(
			'code' => $med_code,
			'id' => $aid,
			'activity' => $activity,
		));
		exit();
	}
	}

	function _add_js_css_hooks () {
		if (!is_user_logged_in()) return false;

		global $trs;

		$show_condition = (bool)(
			// Load the scripts on Activity pages
			(defined('TRS_ACTIVITY_SLUG') && trs_is_activity_component())
			||
			// Load the scripts when Activity page is the Home page
			(defined('TRS_ACTIVITY_SLUG') && 'page' == get_option('show_on_front') && is_front_page() && TRS_ACTIVITY_SLUG == get_option('page_on_front'))
			||
			// Load the script on Group home page
			(defined('TRS_GROUPS_SLUG') && trs_is_groups_component() && 'home' == $trs->current_action)
			||
			apply_filters('med_injection_additional_condition', false)
		);

		if (apply_filters('med_inject_dependencies', $show_condition)) {
			// Step1: Load JS/CSS requirements
			add_action('trm_enqueue_scripts', array($this, 'js_load_scripts'));
			add_action('trm_print_styles', array($this, 'css_load_styles'));

			do_action('med_add_cssjs_hooks');
		}
	}

	/**
	 * Trigger handler when trendr activity is removed.
	 * @param  array $args trendr activity arguments
	 * @return bool Insignificant
	 */
	function remove_activity_images ($args) {
		if (!is_user_logged_in()) return false;
		if (empty($args['id'])) return false;

		$activity = new TRS_Activity_Activity($args['id']);
		if (!is_object($activity) || empty($activity->content)) return false;

		if (!trs_activity_user_can_delete($activity)) return false;
		if (!MedCodec::has_images($activity->content)) return false;

		$matches = array();
		preg_match('/\[med_images\](.*?)\[\/med_images\]/s', $activity->content, $matches);
		if (empty($matches[1])) return false;

		$this->_clean_up_content_images($matches[1], $activity);

		return true;
	}

	/**
	 * Callback for activity images removal
	 * @param  string $content Shortcode content parsed for images
	 * @param  TRS_Activity_Activity Activity which contains the shortcode - used for privilege check 
	 * @return bool
	 */
	private function _clean_up_content_images ($content, $activity) {
		if (!Med_Data::get('cleanup_images')) return false;
		if (!trs_activity_user_can_delete($activity)) return false;

		$images = MedCodec::extract_images($content);
		if (empty($images)) return false;

		foreach ($images as $image) {
			$info = pathinfo(trim($image));
			
			// Make sure we have the info we need
			if (empty($info['filename']) || empty($info['extension'])) continue;
			
			// Make sure we're dealing with the image
			$ext = strtolower($info['extension']);
			if (!in_array($ext, self::_get_supported_image_extensions())) continue;

			// Construct the filenames
			$thumbnail = med_get_image_dir($activity_blog_id) . $info['filename'] . '-medt.' . $ext;
			$full = med_get_image_dir($activity_blog_id) . trim($image);

			// Actually remove the images
			if (file_exists($thumbnail) && is_writable($thumbnail)) @unlink($thumbnail);
			if (file_exists($full) && is_writable($full)) @unlink($full);
		}
		return true;
	}

	/**
	 * Lists supported image extensions
	 * @return array Supported image extensions
	 */
	private static function _get_supported_image_extensions () {
		return array('jpg', 'jpeg', 'png', 'gif','mp4', 'MOV', 'mov');
	}

	/**
	 * This is where the plugin registers itself.
	 */
	function add_hooks () {
        //Added 6-18-18 creating object tag

        /** 
         * Firas Abd Alrahman 2015-09-25
         * Workaround, posting files in ajax hangs chrome if we do not set the 
         * contentType: 'multipart/form-data',
         * but setting this will prevent the browser from setting boundry in header
         * so we let the data go to browser without being formated
         * this will make $_POST / $_FILES empty
         * we read the request as stream then refill
         * this make it working for chrome FF ...etc.. and jquery ajax request...
         * and we only implement if we found the $_POST request is empty
         * look src.js script line 2769 function _upload for qq upload handler
         *  **/
        if(!$_POST)
        {          
            $input = fopen("php://input", "r");
            $temp = tmpfile();
            $realSize = stream_copy_to_stream($input, $temp);
            fclose($input);
            
            fseek($temp, 0);
            $request = @fread($temp,$realSize);
            parse_str($request,$_POST);
        }
		add_action('init', array($this, '_add_js_css_hooks'));

		// Step2: Add AJAX request handlers
		add_action('trm_ajax_med_preview_video', array($this, 'ajax_preview_video'));
		add_action('trm_ajax_med_preview_link', array($this, 'ajax_preview_link'));
		add_action('trm_ajax_med_preview_photo', array($this, 'ajax_preview_photo'));
		add_action('trm_ajax_med_preview_remote_image', array($this, 'ajax_preview_remote_image'));
		add_action('trm_ajax_med_remove_temp_images', array($this, 'ajax_remove_temp_images'));
		add_action('trm_ajax_med_update_activity_contents', array($this, 'ajax_update_activity_contents'));

		do_action('med_add_ajax_hooks');
		
		// Step 3: Register and process shortcodes
		MedCodec::register();
        //Added 6-18-18 creating object tag

	function med_activity_links_filter() {
	//echo '<option value="photo_post">recent photos</option>';
	//echo '<option value="video_post">recent videos</option>';
	//echo '<option value="web_post">Web Posts Only</option>';
}

		function addcustomerfilter(){
				echo '<ul><li><div id="activity_media_filter">
	

			  <input id="c1" type="checkbox" name="group1[]" action="photo_post" />
             
             <label id="c1" for="c1">Photos</label>

			   <input id="c2" type="checkbox" name="group1[]" action="video_post" />
            <label id="c2" for="c2">Videos</label>

			<input id="c3" type="checkbox" name="group1[]" action="web_post" />

		    <label id="c3" for="c3">Links</label>
		  <input id="c0" type="checkbox" name="group1[]" action="activity_update__new_blog_post__new_blog_comment__new_forum_topic__new_forum_post__created_group__joined_group__friendship_accepted__friendship_created__new_member" />
             <label id="c0" for="c0">Texts</label>

				</div>	</li> </ul>';
		}
	//asamir + add custom div to the main activiy view
 			add_action( 'trs_before_container', 'addcustomerfilter' );

			//asamir - do not inlcude the custom filter for this plus plugin
       // add_action( 'trs_activity_filter_options', 'med_activity_links_filter' );

       add_action( 'trs_member_activity_filter_options', 'med_activity_links_filter' );

       //add_action('trs_group_activity_filter_options', 'med_activity_links_filter' );

		if (Med_Data::get('cleanup_images')) {
			add_action('trs_before_activity_delete', array($this, 'remove_activity_images'));
		}
	}
}