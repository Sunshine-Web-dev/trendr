<?php
/**
 * trendr Avatars
 *
 * Based on contributions from: Beau Lebens - http://www.dentedreality.com.au/
 * Modified for trendr by: Andy Peatling - http://apeatling.wordpress.com/
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/***
 * Set up the constants we need for portrait support
 */
function trs_core_set_portrait_constants() {
	global $trs;
	
	if ( !defined( 'TRS_AVATAR_THUMB_WIDTH' ) )
		define( 'TRS_AVATAR_THUMB_WIDTH', 50 );

	if ( !defined( 'TRS_AVATAR_THUMB_HEIGHT' ) )
		define( 'TRS_AVATAR_THUMB_HEIGHT', 50 );

	if ( !defined( 'TRS_AVATAR_FULL_WIDTH' ) )
		define( 'TRS_AVATAR_FULL_WIDTH', 150 );

	if ( !defined( 'TRS_AVATAR_FULL_HEIGHT' ) )
		define( 'TRS_AVATAR_FULL_HEIGHT', 150 );

	if ( !defined( 'TRS_AVATAR_ORIGINAL_MAX_WIDTH' ) )
		define( 'TRS_AVATAR_ORIGINAL_MAX_WIDTH', 450 );

	if ( !defined( 'TRS_AVATAR_ORIGINAL_MAX_FILESIZE' ) ) {
		if ( !isset( $trs->site_options['fileupload_maxk'] ) )
			define( 'TRS_AVATAR_ORIGINAL_MAX_FILESIZE', 5120000 ); // 5mb
		else
			define( 'TRS_AVATAR_ORIGINAL_MAX_FILESIZE', $trs->site_options['fileupload_maxk'] * 1024 );
	}

	if ( !defined( 'TRS_AVATAR_DEFAULT' ) )
		define( 'TRS_AVATAR_DEFAULT', TRS_PLUGIN_URL . '/trs-core/images/mystery-man.jpg' );

	if ( !defined( 'TRS_AVATAR_DEFAULT_THUMB' ) )
		define( 'TRS_AVATAR_DEFAULT_THUMB', TRS_PLUGIN_URL . '/trs-core/images/mystery-man-50.jpg' );
}
add_action( 'trs_init', 'trs_core_set_portrait_constants', 3 );

function trs_core_set_portrait_globals() {
	global $trs;
	
	// Dimensions
	$trs->portrait->thumb->width  	   = TRS_AVATAR_THUMB_WIDTH;
	$trs->portrait->thumb->height 	   = TRS_AVATAR_THUMB_HEIGHT;
	$trs->portrait->full->width 	   = TRS_AVATAR_FULL_WIDTH;
	$trs->portrait->full->height 	   = TRS_AVATAR_FULL_HEIGHT;
	
	// Upload maximums
	$trs->portrait->original_max_width    = TRS_AVATAR_ORIGINAL_MAX_WIDTH;
	$trs->portrait->original_max_filesize = TRS_AVATAR_ORIGINAL_MAX_FILESIZE;
	
	// Defaults
	$trs->portrait->thumb->default 	   = TRS_AVATAR_DEFAULT_THUMB;
	$trs->portrait->full->default 	   = TRS_AVATAR_DEFAULT;
	
	// These have to be set on page load in order to avoid infinite filter loops at runtime
	$trs->portrait->upload_path	   = trs_core_portrait_upload_path();
	$trs->portrait->url	   	   = trs_core_portrait_url();
	
	do_action( 'trs_core_set_portrait_globals' );
}
add_action( 'trs_setup_globals', 'trs_core_set_portrait_globals' );

/**
 * trs_core_fetch_portrait()
 *
 * Fetches an portrait from a trendr object. Supports user/group/blog as
 * default, but can be extended to include your own custom components too.
 *
 * @global object $trs Global trendr settings object
 * @global $current_blog WordPress global containing information and settings for the current blog being viewed.
 * @param array $args Determine the output of this function
 * @return string Formatted HTML <img> element, or raw portrait URL based on $html arg
 */
function trs_core_fetch_portrait( $args = '' ) {
	global $trs, $current_blog;

	// Set a few default variables
	$def_object = 'user';
	$def_type   = 'thumb';
	$def_class  = 'portrait';
	$def_alt    = __( 'Avatar Image', 'trendr' );

	// Set the default variables array
	$defaults = array(
		'item_id'    => false,
		'object'     => $def_object, // user/group/blog/custom type (if you use filters)
		'type'       => $def_type,   // thumb or full
		'portrait_dir' => false,       // Specify a custom portrait directory for your object
		'width'      => false,       // Custom width (int)
		'height'     => false,       // Custom height (int)
		'class'      => $def_class,  // Custom <img> class (string)
		'css_id'     => false,       // Custom <img> ID (string)
		'alt'        => $def_alt,    // Custom <img> alt (string)
		'email'      => false,       // Pass the user email (for grportrait) to prevent querying the DB for it
		'no_grav'    => false,       // If there is no portrait found, return false instead of a grav?
		'html'       => true,        // Wrap the return img URL in <img />
		'title'      => ''           // Custom <img> title (string)
	);

	// Compare defaults to passed and extract
	$params = trm_parse_args( $args, $defaults );
	extract( $params, EXTR_SKIP );

	// Set item_id if not passed
	if ( !$item_id ) {
		if ( 'user' == $object )
			$item_id = $trs->displayed_user->id;
		else if ( trs_is_active( 'groups' ) && 'group' == $object )
			$item_id = $trs->groups->current_group->id;
		else if ( 'blog' == $object )
			$item_id = $current_blog->id;

		$item_id = apply_filters( 'trs_core_portrait_item_id', $item_id, $object );

		if ( !$item_id )
			return false;
	}

	// Set portrait_dir if not passed (uses $object)
	if ( !$portrait_dir ) {
		if ( 'user' == $object )
			$portrait_dir = 'portraits';
		else if ( trs_is_active( 'groups' ) && 'group' == $object )
			$portrait_dir = 'group-portraits';
		else if ( 'blog' == $object )
			$portrait_dir = 'blog-portraits';

		$portrait_dir = apply_filters( 'trs_core_portrait_dir', $portrait_dir, $object );

		if ( !$portrait_dir )
			return false;
	}

	// Add an identifying class to each item
	$class .= ' ' . $object . '-' . $item_id . '-portrait';

	// Get item name for alt/title tags
	$item_name = '';

	if ( 'user' == $object )
		$item_name = trs_core_get_user_displayname( $item_id );
	elseif ( 'group' == $object )
		$item_name = trs_get_group_name( new TRS_Groups_Group( $item_id ) );
	elseif ( 'blog' == $object )
		$item_name = get_blog_option( $item_id, 'blogname' );

	$alt = sprintf( $alt, apply_filters( 'trs_core_portrait_alt', $item_name, $item_id, $object ) );

	// Set title tag
	if ( $title )
		$title = " title='" . esc_attr( apply_filters( 'trs_core_portrait_title', $title, $item_id, $object ) ) . "'";
	elseif ( $item_name )
		$title = " title='" . esc_attr( apply_filters( 'trs_core_portrait_title', $item_name, $item_id, $object ) ) . "'";

	// Set CSS ID if passed
	if ( !empty( $css_id ) )
		$css_id = " id='{$css_id}'";

	// Set portrait width
	if ( $width )
		$html_width = " width='{$width}'";
	else
		$html_width = ( 'thumb' == $type ) ? ' width="' . trs_core_portrait_thumb_width() . '"' : ' width="' . trs_core_portrait_full_width() . '"';

	// Set portrait height
	if ( $height )
		$html_height = " height='{$height}'";
	else
		$html_height = ( 'thumb' == $type ) ? ' height="' . trs_core_portrait_thumb_height() . '"' : ' height="' . trs_core_portrait_full_height() . '"';

	// Set portrait URL and DIR based on prepopulated constants
	$portrait_folder_url = apply_filters( 'trs_core_portrait_folder_url', trs_core_portrait_url() . '/' . $portrait_dir . '/' . $item_id, $item_id, $object, $portrait_dir );
	$portrait_folder_dir = apply_filters( 'trs_core_portrait_folder_dir', trs_core_portrait_upload_path() . '/' . $portrait_dir . '/' . $item_id, $item_id, $object, $portrait_dir );

	/****
	 * Look for uploaded portrait first. Use it if it exists.
	 * Set the file names to search for, to select the full size
	 * or thumbnail image.
	 */
	$portrait_size              = ( 'full' == $type ) ? '-trsfull' : '-trsthumb';
	$legacy_user_portrait_name  = ( 'full' == $type ) ? '-portrait2' : '-portrait1';
	$legacy_group_portrait_name = ( 'full' == $type ) ? '-groupportrait-full' : '-groupportrait-thumb';

	// Check for directory
	if ( file_exists( $portrait_folder_dir ) ) {

		// Open directory
		if ( $av_dir = opendir( $portrait_folder_dir ) ) {

			// Stash files in an array once to check for one that matches
			$portrait_files = array();
			while ( false !== ( $portrait_file = readdir($av_dir) ) ) {
				// Only add files to the array (skip directories)
				if ( 2 < strlen( $portrait_file ) )
					$portrait_files[] = $portrait_file;
			}

			// Check for array
			if ( 0 < count( $portrait_files ) ) {

				// Check for current portrait
				foreach( $portrait_files as $key => $value ) {
					if ( strpos ( $value, $portrait_size )!== false )
						$portrait_url = $portrait_folder_url . '/' . $portrait_files[$key];
				}

				// Legacy portrait check
				if ( !isset( $portrait_url ) ) {
					foreach( $portrait_files as $key => $value ) {
						if ( strpos ( $value, $legacy_user_portrait_name )!== false )
							$portrait_url = $portrait_folder_url . '/' . $portrait_files[$key];
					}

					// Legacy group portrait check
					if ( !isset( $portrait_url ) ) {
						foreach( $portrait_files as $key => $value ) {
							if ( strpos ( $value, $legacy_group_portrait_name )!== false )
								$portrait_url = $portrait_folder_url . '/' . $portrait_files[$key];
						}
					}
				}
			}
		}

		// Close the portrait directory
		closedir( $av_dir );

		// If we found a locally uploaded portrait
		if ( isset( $portrait_url ) ) {

			// Return it wrapped in an <img> element
			if ( true === $html ) {
				return apply_filters( 'trs_core_fetch_portrait', '<img src="' . $portrait_url . '" alt="' . esc_attr( $alt ) . '" class="' . esc_attr( $class ) . '"' . $css_id . $html_width . $html_height . $title . ' />', $params, $item_id, $portrait_dir, $css_id, $html_width, $html_height, $portrait_folder_url, $portrait_folder_dir );

			// ...or only the URL
			} else {
				return apply_filters( 'trs_core_fetch_portrait_url', $portrait_url );
			}
		}
	}

	// If no portraits could be found, try to display a grportrait

	// Skips grportrait check if $no_grav is passed
	if ( ! apply_filters( 'trs_core_fetch_portrait_no_grav', $no_grav ) ) {

		// Set grportrait size
		if ( $width )
			$grav_size = $width;
		else if ( 'full' == $type )
			$grav_size = trs_core_portrait_full_width();
		else if ( 'thumb' == $type )
			$grav_size = trs_core_portrait_thumb_width();

		// Set grportrait type
		if ( empty( $trs->grav_default->{$object} ) )
			$default_grav = 'wportrait';
		else if ( 'mystery' == $trs->grav_default->{$object} )
			$default_grav = apply_filters( 'trs_core_mysteryman_src', trs_core_portrait_default(), $grav_size );
		else
			$default_grav = $trs->grav_default->{$object};

		// Set grportrait object
		if ( empty( $email ) ) {
			if ( 'user' == $object ) {
				$email = trs_core_get_user_email( $item_id );
			} else if ( 'group' == $object || 'blog' == $object ) {
				$email = "{$item_id}-{$object}@{trs_get_root_domain()}";
			}
		}

		// Set host based on if using ssl
		if ( is_ssl() )
			$host = 'https://secure.grportrait.com/portrait/';
		else
			$host = 'http://www.grportrait.com/portrait/';

		// Filter grportrait vars
		$email    = apply_filters( 'trs_core_grportrait_email', $email, $item_id, $object );
		$grportrait = apply_filters( 'trs_grportrait_url', $host ) . md5( strtolower( $email ) ) . '?d=' . $default_grav . '&amp;s=' . $grav_size;

	} else {
		// No portrait was found, and we've been told not to use a grportrait.
		$grportrait = apply_filters( "trs_core_default_portrait_$object", TRS_PLUGIN_URL . '/trs-core/images/mystery-man.jpg', $params );
	}

	if ( true === $html )
		return apply_filters( 'trs_core_fetch_portrait', '<img src="' . $grportrait . '" alt="' . esc_attr( $alt ) . '" class="' . esc_attr( $class ) . '"' . $css_id . $html_width . $html_height . $title . ' />', $params, $item_id, $portrait_dir, $css_id, $html_width, $html_height, $portrait_folder_url, $portrait_folder_dir );
	else
		return apply_filters( 'trs_core_fetch_portrait_url', $grportrait );
}

/**
 * Delete an existing portrait
 *
 * Accepted values for $args are:
 *  item_id - item id which relates to the object type.
 *  object - the objetc type user, group, blog, etc.
 *  portrait_dir - The directory where the portraits to be uploaded.
 *
 * @global object $trs trendr global settings
 * @param mixed $args
 * @return bool Success/failure
 */
function trs_core_delete_existing_portrait( $args = '' ) {
	global $trs;

	$defaults = array(
		'item_id'    => false,
		'object'     => 'user', // user OR group OR blog OR custom type (if you use filters)
		'portrait_dir' => false
	);

	$args = trm_parse_args( $args, $defaults );
	extract( $args, EXTR_SKIP );

	if ( !$item_id ) {
		if ( 'user' == $object )
			$item_id = $trs->displayed_user->id;
		else if ( 'group' == $object )
			$item_id = $trs->groups->current_group->id;
		else if ( 'blog' == $object )
			$item_id = $current_blog->id;

		$item_id = apply_filters( 'trs_core_portrait_item_id', $item_id, $object );

		if ( !$item_id ) return false;
	}

	if ( !$portrait_dir ) {
		if ( 'user' == $object )
			$portrait_dir = 'portraits';
		else if ( 'group' == $object )
			$portrait_dir = 'group-portraits';
		else if ( 'blog' == $object )
			$portrait_dir = 'blog-portraits';

		$portrait_dir = apply_filters( 'trs_core_portrait_dir', $portrait_dir, $object );

		if ( !$portrait_dir ) return false;
	}

	$portrait_folder_dir = apply_filters( 'trs_core_portrait_folder_dir', trs_core_portrait_upload_path() . '/' . $portrait_dir . '/' . $item_id, $item_id, $object, $portrait_dir );

	if ( !file_exists( $portrait_folder_dir ) )
		return false;

	if ( $av_dir = opendir( $portrait_folder_dir ) ) {
		while ( false !== ( $portrait_file = readdir($av_dir) ) ) {
			if ( ( preg_match( "/-trsfull/", $portrait_file ) || preg_match( "/-trsthumb/", $portrait_file ) ) && '.' != $portrait_file && '..' != $portrait_file )
				@unlink( $portrait_folder_dir . '/' . $portrait_file );
		}
	}
	closedir($av_dir);

	@rmdir( $portrait_folder_dir );

	do_action( 'trs_core_delete_existing_portrait', $args );

	return true;
}

/**
 * Handles portrait uploading.
 *
 * The functions starts off by checking that the file has been uploaded properly using trs_core_check_portrait_upload().
 * It then checks that the file size is within limits, and that it has an accepted file extension (jpg, gif, png).
 * If everything checks out, crop the image and move it to its real location.
 *
 * @global object $trs trendr global settings
 * @param array $file The appropriate entry the from $_FILES superglobal.
 * @param string $upload_dir_filter A filter to be applied to upload_dir
 * @return bool Success/failure
 * @see trs_core_check_portrait_upload()
 * @see trs_core_check_portrait_type()
 */
function trs_core_portrait_handle_upload( $file, $upload_dir_filter ) {
	global $trs;

	/***
	 * You may want to hook into this filter if you want to override this function.
	 * Make sure you return false.
	 */
	if ( !apply_filters( 'trs_core_pre_portrait_handle_upload', true, $file, $upload_dir_filter ) )
		return true;

	require_once( ABSPATH . '/Backend-WeaprEcqaKejUbRq-trendr/includes/image.php' );
	require_once( ABSPATH . '/Backend-WeaprEcqaKejUbRq-trendr/includes/file.php' );

	$uploadErrors = array(
		0 => __("There is no error, the file uploaded with success", 'trendr'),
		1 => __("Your image was bigger than the maximum allowed file size of: ", 'trendr') . size_format( trs_core_portrait_original_max_filesize() ),
		2 => __("Your image was bigger than the maximum allowed file size of: ", 'trendr') . size_format( trs_core_portrait_original_max_filesize() ),
		3 => __("The uploaded file was only partially uploaded", 'trendr'),
		4 => __("No file was uploaded", 'trendr'),
		6 => __("Missing a temporary folder", 'trendr')
	);

	if ( !trs_core_check_portrait_upload( $file ) ) {
		trs_core_add_message( sprintf( __( 'Your upload failed, please try again. Error was: %s', 'trendr' ), $uploadErrors[$file['file']['error']] ), 'error' );
		return false;
	}

	if ( !trs_core_check_portrait_size( $file ) ) {
		trs_core_add_message( sprintf( __( 'The file you uploaded is too big. Please upload a file under %s', 'trendr'), size_format( trs_core_portrait_original_max_filesize() ) ), 'error' );
		return false;
	}

	if ( !trs_core_check_portrait_type( $file ) ) {
		trs_core_add_message( __( 'Please upload only JPG, GIF or PNG photos.', 'trendr' ), 'error' );
		return false;
	}

	// Filter the upload location
	add_filter( 'upload_dir', $upload_dir_filter, 10, 0 );

	$trs->portrait_admin->original = trm_handle_upload( $file['file'], array( 'action'=> 'trs_portrait_upload' ) );

	// Move the file to the correct upload location.
	if ( !empty( $trs->portrait_admin->original['error'] ) ) {
		trs_core_add_message( sprintf( __( 'Upload Failed! Error was: %s', 'trendr' ), $trs->portrait_admin->original['error'] ), 'error' );
		return false;
	}

	// Get image size
	$size = @getimagesize( $trs->portrait_admin->original['file'] );

	// Check image size and shrink if too large
	if ( $size[0] > trs_core_portrait_original_max_width() ) {
		$thumb = trm_create_thumbnail( $trs->portrait_admin->original['file'], trs_core_portrait_original_max_width() );

		// Check for thumbnail creation errors
		if ( is_trm_error( $thumb ) ) {
			trs_core_add_message( sprintf( __( 'Upload Failed! Error was: %s', 'trendr' ), $thumb->get_error_message() ), 'error' );
			return false;
		}

		// Thumbnail is good so proceed
		$trs->portrait_admin->resized = $thumb;
	}

	// We only want to handle one image after resize.
	if ( empty( $trs->portrait_admin->resized ) )
		$trs->portrait_admin->image->dir = str_replace( trs_core_portrait_upload_path(), '', $trs->portrait_admin->original['file'] );
	else {
		$trs->portrait_admin->image->dir = str_replace( trs_core_portrait_upload_path(), '', $trs->portrait_admin->resized );
		@unlink( $trs->portrait_admin->original['file'] );
	}

	// Check for TRM_Error on what should be an image
	if ( is_trm_error( $trs->portrait_admin->image->dir ) ) {
		trs_core_add_message( sprintf( __( 'Upload failed! Error was: %s', 'trendr' ), $trs->portrait_admin->image->dir->get_error_message() ), 'error' );
		return false;
	}

	// Set the url value for the image
	$trs->portrait_admin->image->url = trs_core_portrait_url() . $trs->portrait_admin->image->dir;

	return true;
}

/**
 * Crop an uploaded portrait
 *
 * $args has the following parameters:
 *  object - What component the portrait is for, e.g. "user"
 *  portrait_dir  The absolute path to the portrait
 *  item_id - Item ID
 *  original_file - The absolute path to the original portrait file
 *  crop_w - Crop width
 *  crop_h - Crop height
 *  crop_x - The horizontal starting point of the crop
 *  crop_y - The vertical starting point of the crop
 *
 * @global object $trs trendr global settings
 * @param mixed $args
 * @return bool Success/failure
 */
function trs_core_portrait_handle_crop( $args = '' ) {
	global $trs;

	$defaults = array(
		'object'        => 'user',
		'portrait_dir'    => 'portraits',
		'item_id'       => false,
		'original_file' => false,
		'crop_w'        => trs_core_portrait_full_width(),
		'crop_h'        => trs_core_portrait_full_height(),
		'crop_x'        => 0,
		'crop_y'        => 0
	);

	$r = trm_parse_args( $args, $defaults );

	/***
	 * You may want to hook into this filter if you want to override this function.
	 * Make sure you return false.
	 */
	if ( !apply_filters( 'trs_core_pre_portrait_handle_crop', true, $r ) )
		return true;

	extract( $r, EXTR_SKIP );

	if ( !$original_file )
		return false;

	$original_file = trs_core_portrait_upload_path() . $original_file;

	if ( !file_exists( $original_file ) )
		return false;

	if ( !$item_id )
		$portrait_folder_dir = apply_filters( 'trs_core_portrait_folder_dir', dirname( $original_file ), $item_id, $object, $portrait_dir );
	else
		$portrait_folder_dir = apply_filters( 'trs_core_portrait_folder_dir', trs_core_portrait_upload_path() . '/' . $portrait_dir . '/' . $item_id, $item_id, $object, $portrait_dir );

	if ( !file_exists( $portrait_folder_dir ) )
		return false;

	require_once( ABSPATH . '/Backend-WeaprEcqaKejUbRq-trendr/includes/image.php' );
	require_once( ABSPATH . '/Backend-WeaprEcqaKejUbRq-trendr/includes/file.php' );

	// Delete the existing portrait files for the object
	trs_core_delete_existing_portrait( array( 'object' => $object, 'portrait_path' => $portrait_folder_dir ) );

	// Make sure we at least have a width and height for cropping
	if ( !(int)$crop_w )
		$crop_w = trs_core_portrait_full_width();

	if ( !(int)$crop_h )
		$crop_h = trs_core_portrait_full_height();

	// Set the full and thumb filenames
	$full_filename  = trm_hash( $original_file . time() ) . '-trsfull.jpg';
	$thumb_filename = trm_hash( $original_file . time() ) . '-trsthumb.jpg';

	// Crop the image
	$full_cropped  = trm_crop_image( $original_file, (int)$crop_x, (int)$crop_y, (int)$crop_w, (int)$crop_h, trs_core_portrait_full_width(), trs_core_portrait_full_height(), false, $portrait_folder_dir . '/' . $full_filename );
	$thumb_cropped = trm_crop_image( $original_file, (int)$crop_x, (int)$crop_y, (int)$crop_w, (int)$crop_h, trs_core_portrait_thumb_width(), trs_core_portrait_thumb_height(), false, $portrait_folder_dir . '/' . $thumb_filename );

	// Remove the original
	@unlink( $original_file );

	return true;
}

/**
 * trs_core_fetch_portrait_filter()
 *
 * Attempts to filter get_portrait function and let trendr have a go
 * at finding an portrait that may have been uploaded locally.
 *
 * @global array $authordata
 * @param string $portrait The result of get_portrait from before-filter
 * @param int|string|object $user A user ID, email address, or comment object
 * @param int $size Size of the portrait image (thumb/full)
 * @param string $default URL to a default image to use if no portrait is available
 * @param string $alt Alternate text to use in image tag. Defaults to blank
 * @return <type>
 */
function trs_core_fetch_portrait_filter( $portrait, $user, $size, $default, $alt = '' ) {
	global $pagenow;

	// Do not filter if inside WordPress options page
	if ( 'options-discussion.php' == $pagenow )
		return $portrait;

	// If passed an object, assume $user->user_id
	if ( is_object( $user ) )
		$id = $user->user_id;

	// If passed a number, assume it was a $user_id
	else if ( is_numeric( $user ) )
		$id = $user;

	// If passed a string and that string returns a user, get the $id
	else if ( is_string( $user ) && ( $user_by_email = get_user_by( 'email', $user ) ) )
		$id = $user_by_email->ID;

	// If somehow $id hasn't been assigned, return the result of get_portrait
	if ( empty( $id ) )
		return !empty( $portrait ) ? $portrait : $default;

	if ( !$alt )
		$alt = __( 'Avatar of %s', 'trendr' );

	// Let trendr handle the fetching of the portrait
	$trs_portrait = trs_core_fetch_portrait( array( 'item_id' => $id, 'width' => $size, 'height' => $size, 'alt' => $alt ) );

	// If trendr found an portrait, use it. If not, use the result of get_portrait
	return ( !$trs_portrait ) ? $portrait : $trs_portrait;
}
add_filter( 'get_portrait', 'trs_core_fetch_portrait_filter', 10, 5 );

/**
 * Has the current portrait upload generated an error?
 *
 * @param array $file
 * @return bool
 */
function trs_core_check_portrait_upload( $file ) {
	if ( isset( $file['error'] ) && $file['error'] )
		return false;

	return true;
}

/**
 * Is the file size of the current portrait upload permitted?
 *
 * @param array $file
 * @return bool
 */
function trs_core_check_portrait_size( $file ) {
	if ( $file['file']['size'] > trs_core_portrait_original_max_filesize() )
		return false;

	return true;
}

/**
 * Does the current portrait upload have an allowed file type?
 *
 * Permitted file types are JPG, GIF and PNG.
 *
 * @param string $file
 * @return bool
 */
function trs_core_check_portrait_type($file) {
	if ( ( !empty( $file['file']['type'] ) && !preg_match('/(jpe?g|gif|png)$/i', $file['file']['type'] ) ) || !preg_match( '/(jpe?g|gif|png)$/i', $file['file']['name'] ) )
		return false;

	return true;
}

/**
 * trs_core_portrait_upload_path()
 *
 * Returns the absolute upload path for the TRM installation
 *
 * @uses trm_upload_dir To get upload directory info
 * @return string Absolute path to TRM upload directory
 */
function trs_core_portrait_upload_path() {
	global $trs;
	
	// See if the value has already been calculated and stashed in the $trs global
	if ( isset( $trs->portrait->upload_path ) ) {
		$basedir = $trs->portrait->upload_path;
	} else {
		// If this value has been set in a constant, just use that
		if ( defined( 'TRS_AVATAR_UPLOAD_PATH' ) ) {
			$basedir = TRS_AVATAR_UPLOAD_PATH;
		} else {
			// Get upload directory information from current site
			$upload_dir = trm_upload_dir();
		
			// Directory does not exist and cannot be created
			if ( !empty( $upload_dir['error'] ) ) {
				$basedir = '';
		
			} else {
				$basedir = $upload_dir['basedir'];
		
				// If multisite, and current blog does not match root blog, make adjustments
				if ( is_multisite() && trs_get_root_blog_id() != get_current_blog_id() )
					$basedir = get_blog_option( trs_get_root_blog_id(), 'upload_path' );
			}
		}
		
		// Stash in $trs for later use
		$trs->portrait->upload_path = $basedir;
	}
	
	return apply_filters( 'trs_core_portrait_upload_path', $basedir );
}

/**
 * trs_core_portrait_url()
 *
 * Returns the raw base URL for root site upload location
 *
 * @uses trm_upload_dir To get upload directory info
 * @return string Full URL to current upload location
 */
function trs_core_portrait_url() {
	global $trs;
	
	// See if the value has already been calculated and stashed in the $trs global
	if ( isset( $trs->portrait->url ) ) {
		$baseurl = $trs->portrait->url;
	} else {
		// If this value has been set in a constant, just use that
		if ( defined( 'TRS_AVATAR_URL' ) ) {
			$baseurl = TRS_AVATAR_URL;
		} else {
			// Get upload directory information from current site
			$upload_dir = trm_upload_dir();
		
			// Directory does not exist and cannot be created
			if ( !empty( $upload_dir['error'] ) ) {
				$baseurl = '';
		
			} else {
				$baseurl = $upload_dir['baseurl'];
		
				// If multisite, and current blog does not match root blog, make adjustments
				if ( is_multisite() && trs_get_root_blog_id() != get_current_blog_id() )
					$baseurl = trailingslashit( get_blog_option( trs_get_root_blog_id(), 'home' ) ) . get_blog_option( trs_get_root_blog_id(), 'upload_path' );
			}
		}
		
		// Stash in $trs for later use
		$trs->portrait->url = $baseurl;
	}

	return apply_filters( 'trs_core_portrait_url', $baseurl );
}

/**
 * Utility function for fetching an portrait dimension setting
 *
 * @package trendr
 * @since 1.5
 *
 * @param str $type 'thumb' for thumbs, otherwise full
 * @param str $h_or_w 'height' for height, otherwise width
 * @return int $dim The dimension
 */
function trs_core_portrait_dimension( $type = 'thumb', $h_or_w = 'height' ) {
	global $trs;
	
	$dim = isset( $trs->portrait->{$type}->{$h_or_w} ) ? (int)$trs->portrait->{$type}->{$h_or_w} : false;
	
	return apply_filters( 'trs_core_portrait_dimension', $dim, $type, $h_or_w );
}

/**
 * Get the portrait thumb width setting
 *
 * @package trendr
 * @since 1.5
 *
 * @return int The thumb width
 */
function trs_core_portrait_thumb_width() {
	return apply_filters( 'trs_core_portrait_thumb_width', trs_core_portrait_dimension( 'thumb', 'width' ) );
}

/**
 * Get the portrait thumb height setting
 *
 * @package trendr
 * @since 1.5
 *
 * @return int The thumb height
 */
function trs_core_portrait_thumb_height() {
	return apply_filters( 'trs_core_portrait_thumb_height', trs_core_portrait_dimension( 'thumb', 'height' ) );
}

/**
 * Get the portrait full width setting
 *
 * @package trendr
 * @since 1.5
 *
 * @return int The full width
 */
function trs_core_portrait_full_width() {
	return apply_filters( 'trs_core_portrait_full_width', trs_core_portrait_dimension( 'full', 'width' ) );
}

/**
 * Get the portrait full height setting
 *
 * @package trendr
 * @since 1.5
 *
 * @return int The full height
 */
function trs_core_portrait_full_height() {
	return apply_filters( 'trs_core_portrait_full_height', trs_core_portrait_dimension( 'full', 'height' ) );
}

/**
 * Get the max width for original portrait uploads
 *
 * @package trendr
 * @since 1.5
 *
 * @return int The width
 */
function trs_core_portrait_original_max_width() {
	global $trs;
	
	return apply_filters( 'trs_core_portrait_original_max_width', (int)$trs->portrait->original_max_width );
}

/**
 * Get the max filesize for original portrait uploads
 *
 * @package trendr
 * @since 1.5
 *
 * @return int The filesize
 */
function trs_core_portrait_original_max_filesize() {
	global $trs;
	
	return apply_filters( 'trs_core_portrait_original_max_filesize', (int)$trs->portrait->original_max_filesize );
}

/**
 * Get the default portrait
 *
 * @package trendr
 * @since 1.5
 *
 * @return int The URL of the default portrait
 */
function trs_core_portrait_default() {
	global $trs;
	
	return apply_filters( 'trs_core_portrait_default', $trs->portrait->full->default );
}

/**
 * Get the default portrait thumb
 *
 * @package trendr
 * @since 1.5
 *
 * @return int The URL of the default portrait thumb
 */
function trs_core_portrait_default_thumb() {
	global $trs;
	
	return apply_filters( 'trs_core_portrait_thumb', $trs->portrait->thumb->default );
}


?>