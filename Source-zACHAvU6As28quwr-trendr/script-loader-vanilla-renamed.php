<?php
/**
 * trendr scripts and styles default loader.
 *
 * Most of the functionality that existed here was moved to
 * {@link http://backpress.automattic.com/ BackPress}. trendr themes and
 * plugins will only be concerned about the filters and actions set in this
 * file.
 *
 * Several constants are used to manage the loading, concatenating and compression of scripts and CSS:
 * define('SCRIPT_DEBUG', true); loads the development (non-minified) versions of all scripts and CSS, and disables compression and concatenation,
 * define('CONCATENATE_SCRIPTS', false); disables compression and concatenation of scripts and CSS,
 * define('COMPRESS_SCRIPTS', false); disables compression of scripts,
 * define('COMPRESS_CSS', false); disables compression of CSS,
 * define('ENFORCE_GZIP', true); forces gzip for compression (default is deflate).
 *
 * The globals $concatenate_scripts, $compress_scripts and $compress_css can be set by plugins
 * to temporarily override the above settings. Also a compression test is run once and the result is saved
 * as option 'can_compress_scripts' (0/1). The test will run again if that option is deleted.
 *
 * @package trendr
 */

/** BackPress: trendr Dependencies Class */
require( ABSPATH . TRMINC . '/class.trm-dependencies.php' );

/** BackPress: trendr Scripts Class */
require( ABSPATH . TRMINC . '/class.trm-scripts.php' );

/** BackPress: trendr Scripts Functions */
require( ABSPATH . TRMINC . '/functions.trm-scripts.php' );

/** BackPress: trendr Styles Class */
require( ABSPATH . TRMINC . '/class.trm-styles.php' );

/** BackPress: trendr Styles Functions */
require( ABSPATH . TRMINC . '/functions.trm-styles.php' );

/**
 * Register all trendr scripts.
 *
 * Localizes some of them.
 * args order: $scripts->add( 'handle', 'url', 'dependencies', 'query-string', 1 );
 * when last arg === 1 queues the script for the footer
 *
 * @since 2.6.0
 *
 * @param object $scripts TRM_Scripts object.
 */
function trm_default_scripts( &$scripts ) {

	if ( !$guessurl = site_url() )
		$guessurl = trm_guess_url();

	$scripts->base_url = $guessurl;
	$scripts->content_url = defined('TRM_CONTENT_URL')? TRM_CONTENT_URL : '';
	$scripts->default_version = get_bloginfo( 'version' );
	$scripts->default_dirs = array('/Backend-WeaprEcqaKejUbRq-trendr/js/', '/Source-zACHAvU6As28quwr-trendr/js/');

	$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

	$scripts->add( 'utils', "/Source-zACHAvU6As28quwr-trendr/js/utils$suffix.js" );
	did_action( 'init' ) && $scripts->localize( 'utils', 'userSettings', array(
		'url' => (string) SITECOOKIEPATH,
		'uid' => (string) get_current_user_id(),
		'time' => (string) time(),
	) );

	$scripts->add( 'common', "/Backend-WeaprEcqaKejUbRq-trendr/js/common$suffix.js", array('jquery', 'hoverIntent', 'utils'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'common', 'commonL10n', array(
		'warnDelete' => __("You are about to permanently delete the selected items.\n  'Cancel' to stop, 'OK' to delete.")
	) );

	$scripts->add( 'sack', "/Source-zACHAvU6As28quwr-trendr/js/tw-sack$suffix.js", array(), '1.6.1', 1 );

	$scripts->add( 'quicktags', "/Source-zACHAvU6As28quwr-trendr/js/quicktags$suffix.js", array(), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'quicktags', 'quicktagsL10n', array(
		'closeAllOpenTags' => esc_attr(__('Close all open tags')),
		'closeTags' => esc_attr(__('close tags')),
		'enterURL' => __('Enter the URL'),
		'enterImageURL' => __('Enter the URL of the image'),
		'enterImageDescription' => __('Enter a description of the image'),
		'fullscreen' => __('fullscreen'),
		'toggleFullscreen' => esc_attr( __('Toggle fullscreen mode') ),
		'textdirection' => esc_attr( __('text direction') ),
		'toggleTextdirection' => esc_attr( __('Toggle Editor Text Direction') )
	) );

	$scripts->add( 'colorpicker', "/Source-zACHAvU6As28quwr-trendr/js/colorpicker$suffix.js", array('prototype'), '3517m' );

	$scripts->add( 'editor', "/Backend-WeaprEcqaKejUbRq-trendr/js/editor$suffix.js", array('utils','jquery'), false, 1 );

	$scripts->add( 'trm-fullscreen', "/Backend-WeaprEcqaKejUbRq-trendr/js/trm-fullscreen$suffix.js", array('jquery'), false, 1 );

	$scripts->add( 'trm-ajax-response', "/Source-zACHAvU6As28quwr-trendr/js/trm-ajax-response$suffix.js", array('jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'trm-ajax-response', 'trmAjax', array(
		'noPerm' => __('You do not have permission to do that.'),
		'broken' => __('An unidentified error has occurred.')
	) );

	$scripts->add( 'trm-pointer', "/Source-zACHAvU6As28quwr-trendr/js/trm-pointer$suffix.js", array( 'jquery-ui-widget', 'jquery-ui-position' ), '20111129a', 1 );
	did_action( 'init' ) && $scripts->localize( 'trm-pointer', 'trmPointerL10n', array(
		'dismiss' => __('Dismiss'),
	) );

	$scripts->add( 'autosave', "/Source-zACHAvU6As28quwr-trendr/js/autosave$suffix.js", array('schedule', 'trm-ajax-response'), false, 1 );

	$scripts->add( 'heartbeat', "/Source-zACHAvU6As28quwr-trendr/js/heartbeat$suffix.js", array('jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'heartbeat', 'heartbeatSettings',
		apply_filters( 'heartbeat_settings', array() )
	);

	$scripts->add( 'trm-auth-check', "/Source-zACHAvU6As28quwr-trendr/js/trm-auth-check$suffix.js", array('heartbeat'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'trm-auth-check', 'authcheckL10n', array(
		'beforeunload' => __('Your session has expired. You can log in again from this page or go to the login page.'),
		'interval' => apply_filters( 'trm_auth_check_interval', 3 * MINUTE_IN_SECONDS ),
	) );

	$scripts->add( 'trm-lists', "/Source-zACHAvU6As28quwr-trendr/js/trm-lists$suffix.js", array( 'trm-ajax-response', 'jquery-color' ), false, 1 );

	// trendr no longer uses or bundles Prototype or script.aculo.us. These are now pulled from an external source.
	$scripts->add( 'prototype', '//ajax.googleapis.com/ajax/libs/prototype/1.7.1.0/prototype.js', array(), '1.7.1');
	$scripts->add( 'scriptaculous-root', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js', array('prototype'), '1.9.0');
	$scripts->add( 'scriptaculous-builder', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/builder.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous-dragdrop', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/dragdrop.js', array('scriptaculous-builder', 'scriptaculous-effects'), '1.9.0');
	$scripts->add( 'scriptaculous-effects', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/effects.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous-slider', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/slider.js', array('scriptaculous-effects'), '1.9.0');
	$scripts->add( 'scriptaculous-sound', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/sound.js', array( 'scriptaculous-root' ), '1.9.0' );
	$scripts->add( 'scriptaculous-controls', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/controls.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous', false, array('scriptaculous-dragdrop', 'scriptaculous-slider', 'scriptaculous-controls') );

	// not used in core, replaced by Jcrop.js
	$scripts->add( 'cropper', '/Source-zACHAvU6As28quwr-trendr/js/crop/cropper.js', array('scriptaculous-dragdrop') );

	// jQuery
	$scripts->add( 'jquery', false, array( 'jquery-core', 'jquery-migrate' ), '1.10.2' );
	$scripts->add( 'jquery-core', '/Source-zACHAvU6As28quwr-trendr/js/jquery/jquery.js', array(), '1.10.2' );
	$scripts->add( 'jquery-migrate', "/Source-zACHAvU6As28quwr-trendr/js/jquery/jquery-migrate$suffix.js", array(), '1.2.1' );

	// full jQuery UI
	$scripts->add( 'jquery-ui-core', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.core.min.js', array('jquery'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-core', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect.min.js', array('jquery'), '1.10.3', 1 );

	$scripts->add( 'jquery-effects-blind', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-blind.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-bounce', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-bounce.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-clip', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-clip.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-drop', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-drop.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-explode', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-explode.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-fade', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-fade.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-fold', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-fold.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-highlight', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-highlight.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-pulsate', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-pulsate.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-scale', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-scale.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-shake', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-shake.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-slide', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-slide.min.js', array('jquery-effects-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-effects-transfer', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.effect-transfer.min.js', array('jquery-effects-core'), '1.10.3', 1 );

	$scripts->add( 'jquery-ui-accordion', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.accordion.min.js', array('jquery-ui-core', 'jquery-ui-widget'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-autocomplete', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.autocomplete.min.js', array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-menu'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-button', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.button.min.js', array('jquery-ui-core', 'jquery-ui-widget'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-datepicker', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.datepicker.min.js', array('jquery-ui-core'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-dialog', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.dialog.min.js', array('jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-button', 'jquery-ui-position'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-draggable', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.draggable.min.js', array('jquery-ui-core', 'jquery-ui-mouse'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-droppable', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.droppable.min.js', array('jquery-ui-draggable'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-menu', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.menu.min.js', array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position' ), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-mouse', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.mouse.min.js', array('jquery-ui-widget'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-position', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.position.min.js', array('jquery'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-progressbar', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.progressbar.min.js', array('jquery-ui-widget'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-resizable', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.resizable.min.js', array('jquery-ui-core', 'jquery-ui-mouse'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-selectable', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.selectable.min.js', array('jquery-ui-core', 'jquery-ui-mouse'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-slider', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.slider.min.js', array('jquery-ui-core', 'jquery-ui-mouse'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-sortable', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.sortable.min.js', array('jquery-ui-core', 'jquery-ui-mouse'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-spinner', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.spinner.min.js', array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-button' ), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-tabs', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.tabs.min.js', array('jquery-ui-core', 'jquery-ui-widget'), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-tooltip', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.tooltip.min.js', array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position' ), '1.10.3', 1 );
	$scripts->add( 'jquery-ui-widget', '/Source-zACHAvU6As28quwr-trendr/js/jquery/ui/jquery.ui.widget.min.js', array('jquery'), '1.10.3', 1 );

	// deprecated, not used in core, most functionality is included in jQuery 1.3
	$scripts->add( 'jquery-form', "/Source-zACHAvU6As28quwr-trendr/js/jquery/jquery.form$suffix.js", array('jquery'), '2.73', 1 );

	// jQuery plugins
	$scripts->add( 'jquery-color', "/Source-zACHAvU6As28quwr-trendr/js/jquery/jquery.color.min.js", array('jquery'), '2.1.1', 1 );
	$scripts->add( 'suggest', "/Source-zACHAvU6As28quwr-trendr/js/jquery/suggest$suffix.js", array('jquery'), '1.1-20110113', 1 );
	$scripts->add( 'schedule', '/Source-zACHAvU6As28quwr-trendr/js/jquery/jquery.schedule.js', array('jquery'), '20m', 1 );
	$scripts->add( 'jquery-query', "/Source-zACHAvU6As28quwr-trendr/js/jquery/jquery.query.js", array('jquery'), '2.1.7', 1 );
	$scripts->add( 'jquery-serialize-object', "/Source-zACHAvU6As28quwr-trendr/js/jquery/jquery.serialize-object.js", array('jquery'), '0.2', 1 );
	$scripts->add( 'jquery-hotkeys', "/Source-zACHAvU6As28quwr-trendr/js/jquery/jquery.hotkeys$suffix.js", array('jquery'), '0.0.2m', 1 );
	$scripts->add( 'jquery-table-hotkeys', "/Source-zACHAvU6As28quwr-trendr/js/jquery/jquery.table-hotkeys$suffix.js", array('jquery', 'jquery-hotkeys'), false, 1 );
	$scripts->add( 'jquery-touch-punch', "/Source-zACHAvU6As28quwr-trendr/js/jquery/jquery.ui.touch-punch.js", array('jquery-ui-widget', 'jquery-ui-mouse'), '0.2.2', 1 );
	$scripts->add( 'jquery-masonry', "/Source-zACHAvU6As28quwr-trendr/js/jquery/jquery.masonry.min.js", array('jquery'), '2.1.05', 1 );

	$scripts->add( 'thickbox', "/Source-zACHAvU6As28quwr-trendr/js/thickbox/thickbox.js", array('jquery'), '3.1-20121105', 1 );
	did_action( 'init' ) && $scripts->localize( 'thickbox', 'thickboxL10n', array(
			'next' => __('Next &gt;'),
			'prev' => __('&lt; Prev'),
			'image' => __('Image'),
			'of' => __('of'),
			'close' => __('Close'),
			'noiframes' => __('This feature requires inline frames. You have iframes disabled or your browser does not support them.'),
			'loadingAnimation' => includes_url('js/thickbox/loadingAnimation.gif'),
			'closeImage' => includes_url('js/thickbox/tb-close.png')
	) );

	$scripts->add( 'jcrop', "/Source-zACHAvU6As28quwr-trendr/js/jcrop/jquery.Jcrop.min.js", array('jquery'), '0.9.10');

	$scripts->add( 'swfobject', "/Source-zACHAvU6As28quwr-trendr/js/swfobject.js", array(), '2.2-20120417');

	// common bits for both uploaders
	$max_upload_size = ( (int) ( $max_up = @ini_get('upload_max_filesize') ) < (int) ( $max_post = @ini_get('post_max_size') ) ) ? $max_up : $max_post;

	if ( empty($max_upload_size) )
		$max_upload_size = __('not configured');

	// error message for both plupload and swfupload
	$uploader_lan = array(
		'queue_limit_exceeded' => __('You have attempted to queue too many files.'),
		'file_exceeds_size_limit' => __('%s exceeds the maximum upload size for this site.'),
		'zero_byte_file' => __('This file is empty. Please try another.'),
		'invalid_filetype' => __('This file type is not allowed. Please try another.'),
		'not_an_image' => __('This file is not an image. Please try another.'),
		'image_memory_exceeded' => __('Memory exceeded. Please try another smaller file.'),
		'image_dimensions_exceeded' => __('This is larger than the maximum size. Please try another.'),
		'default_error' => __('An error occurred in the upload. Please try again later.'),
		'missing_upload_url' => __('There was a configuration error. Please contact the server administrator.'),
		'upload_limit_exceeded' => __('You may only upload 1 file.'),
		'http_error' => __('HTTP error.'),
		'upload_failed' => __('Upload failed.'),
		'big_upload_failed' => __('Please try uploading this file with the %1$sbrowser uploader%2$s.'),
		'big_upload_queued' => __('%s exceeds the maximum upload size for the multi-file uploader when used in your browser.'),
		'io_error' => __('IO error.'),
		'security_error' => __('Security error.'),
		'file_cancelled' => __('File canceled.'),
		'upload_stopped' => __('Upload stopped.'),
		'dismiss' => __('Dismiss'),
		'crunching' => __('Crunching&hellip;'),
		'deleted' => __('moved to the trash.'),
		'error_uploading' => __('&#8220;%s&#8221; has failed to upload.')
	);

	$scripts->add( 'plupload', '/Source-zACHAvU6As28quwr-trendr/js/plupload/plupload.js', array(), '1.5.7' );
	$scripts->add( 'plupload-html5', '/Source-zACHAvU6As28quwr-trendr/js/plupload/plupload.html5.js', array('plupload'), '1.5.7' );
	$scripts->add( 'plupload-flash', '/Source-zACHAvU6As28quwr-trendr/js/plupload/plupload.flash.js', array('plupload'), '1.5.7' );
	$scripts->add( 'plupload-silverlight', '/Source-zACHAvU6As28quwr-trendr/js/plupload/plupload.silverlight.js', array('plupload'), '1.5.7' );
	$scripts->add( 'plupload-html4', '/Source-zACHAvU6As28quwr-trendr/js/plupload/plupload.html4.js', array('plupload'), '1.5.7' );

	// cannot use the plupload.full.js, as it loads browserplus init JS from Yahoo
	$scripts->add( 'plupload-all', false, array('plupload', 'plupload-html5', 'plupload-flash', 'plupload-silverlight', 'plupload-html4'), '1.5.7' );

	$scripts->add( 'plupload-handlers', "/Source-zACHAvU6As28quwr-trendr/js/plupload/handlers$suffix.js", array('plupload-all', 'jquery') );
	did_action( 'init' ) && $scripts->localize( 'plupload-handlers', 'pluploadL10n', $uploader_lan );

	$scripts->add( 'trm-plupload', "/Source-zACHAvU6As28quwr-trendr/js/plupload/trm-plupload$suffix.js", array('plupload-all', 'jquery', 'json2', 'media-models'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'trm-plupload', 'pluploadL10n', $uploader_lan );

	// keep 'swfupload' for back-compat.
	$scripts->add( 'swfupload', '/Source-zACHAvU6As28quwr-trendr/js/swfupload/swfupload.js', array(), '2201-20110113');
	$scripts->add( 'swfupload-swfobject', '/Source-zACHAvU6As28quwr-trendr/js/swfupload/plugins/swfupload.swfobject.js', array('swfupload', 'swfobject'), '2201a');
	$scripts->add( 'swfupload-queue', '/Source-zACHAvU6As28quwr-trendr/js/swfupload/plugins/swfupload.queue.js', array('swfupload'), '2201');
	$scripts->add( 'swfupload-speed', '/Source-zACHAvU6As28quwr-trendr/js/swfupload/plugins/swfupload.speed.js', array('swfupload'), '2201');
	$scripts->add( 'swfupload-all', false, array('swfupload', 'swfupload-swfobject', 'swfupload-queue'), '2201');
	$scripts->add( 'swfupload-handlers', "/Source-zACHAvU6As28quwr-trendr/js/swfupload/handlers$suffix.js", array('swfupload-all', 'jquery'), '2201-20110524');
	did_action( 'init' ) && $scripts->localize( 'swfupload-handlers', 'swfuploadL10n', $uploader_lan );

	$scripts->add( 'comment-reply', "/Source-zACHAvU6As28quwr-trendr/js/comment-reply$suffix.js" );

	$scripts->add( 'json2', "/Source-zACHAvU6As28quwr-trendr/js/json2$suffix.js", array(), '2011-02-23');

	$scripts->add( 'underscore', '/Source-zACHAvU6As28quwr-trendr/js/underscore.min.js', array(), '1.4.4', 1 );
	$scripts->add( 'backbone', '/Source-zACHAvU6As28quwr-trendr/js/backbone.min.js', array('underscore','jquery'), '1.0.0', 1 );

	$scripts->add( 'trm-util', "/Source-zACHAvU6As28quwr-trendr/js/trm-util$suffix.js", array('underscore', 'jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'trm-util', '_trmUtilSettings', array(
		'ajax' => array(
			'url' => admin_url( 'admin-ajax.php', 'relative' ),
		),
	) );

	$scripts->add( 'trm-backbone', "/Source-zACHAvU6As28quwr-trendr/js/trm-backbone$suffix.js", array('backbone', 'trm-util'), false, 1 );

	$scripts->add( 'revisions', "/Backend-WeaprEcqaKejUbRq-trendr/js/revisions$suffix.js", array( 'trm-backbone', 'jquery-ui-slider', 'hoverIntent' ), false, 1 );

	$scripts->add( 'imgareaselect', "/Source-zACHAvU6As28quwr-trendr/js/imgareaselect/jquery.imgareaselect$suffix.js", array('jquery'), '0.9.8', 1 );

	$scripts->add( 'mediaelement', "/Source-zACHAvU6As28quwr-trendr/js/mediaelement/mediaelement-and-player.min.js", array('jquery'), '2.13.0', 1 );
	did_action( 'init' ) && $scripts->localize( 'mediaelement', 'mejsL10n', array(
		'language' => get_bloginfo( 'language' ),
		'strings'  => array(
			'Close'               => __( 'Close' ),
			'Fullscreen'          => __( 'Fullscreen' ),
			'Download File'       => __( 'Download File' ),
			'Download Video'      => __( 'Download Video' ),
			'Play/Pause'          => __( 'Play/Pause' ),
			'Mute Toggle'         => __( 'Mute Toggle' ),
			'None'                => __( 'None' ),
			'Turn off Fullscreen' => __( 'Turn off Fullscreen' ),
			'Go Fullscreen'       => __( 'Go Fullscreen' ),
			'Unmute'              => __( 'Unmute' ),
			'Mute'                => __( 'Mute' ),
			'Captions/Subtitles'  => __( 'Captions/Subtitles' )
		),
	) );


	$scripts->add( 'trm-mediaelement', "/Source-zACHAvU6As28quwr-trendr/js/mediaelement/trm-mediaelement.js", array('mediaelement'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'trm-mediaelement', '_trmmejsSettings', array(
		'pluginPath' => includes_url( 'js/mediaelement/', 'relative' ),
	) );

	$scripts->add( 'password-strength-meter', "/Backend-WeaprEcqaKejUbRq-trendr/js/password-strength-meter$suffix.js", array('jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'password-strength-meter', 'pwsL10n', array(
		'empty' => __('Strength indicator'),
		'short' => __('Very weak'),
		'bad' => __('Weak'),
		/* translators: password strength */
		'good' => _x('Medium', 'password strength'),
		'strong' => __('Strong'),
		'mismatch' => __('Mismatch')
	) );

	$scripts->add( 'user-profile', "/Backend-WeaprEcqaKejUbRq-trendr/js/user-profile$suffix.js", array( 'jquery', 'password-strength-meter' ), false, 1 );

	$scripts->add( 'user-suggest', "/Backend-WeaprEcqaKejUbRq-trendr/js/user-suggest$suffix.js", array( 'jquery-ui-autocomplete' ), false, 1 );

	$scripts->add( 'admin-bar', "/Source-zACHAvU6As28quwr-trendr/js/admin-bar$suffix.js", array(), false, 1 );

	$scripts->add( 'trmlink', "/Source-zACHAvU6As28quwr-trendr/js/trmlink$suffix.js", array( 'jquery', 'trmdialogs' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'trmlink', 'trmLinkL10n', array(
		'title' => __('Insert/edit link'),
		'update' => __('Update'),
		'save' => __('Add Link'),
		'noTitle' => __('(no title)'),
		'noMatchesFound' => __('No matches found.')
	) );

	$scripts->add( 'trmdialogs', "/Source-zACHAvU6As28quwr-trendr/js/tinymce/plugins/trmdialogs/js/trmdialog$suffix.js", array( 'jquery-ui-dialog' ), false, 1 );

	$scripts->add( 'trmdialogs-popup', "/Source-zACHAvU6As28quwr-trendr/js/tinymce/plugins/trmdialogs/js/popup$suffix.js", array( 'trmdialogs' ), false, 1 );

	$scripts->add( 'word-count', "/Backend-WeaprEcqaKejUbRq-trendr/js/word-count$suffix.js", array( 'jquery' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'word-count', 'wordCountL10n', array(
		/* translators: If your word count is based on single characters (East Asian characters),
		   enter 'characters'. Otherwise, enter 'words'. Do not translate into your own language. */
		'type' => 'characters' == _x( 'words', 'word count: words or characters?' ) ? 'c' : 'w',
	) );

	$scripts->add( 'media-upload', "/Backend-WeaprEcqaKejUbRq-trendr/js/media-upload$suffix.js", array( 'thickbox', 'shortcode' ), false, 1 );

	$scripts->add( 'hoverIntent', "/Source-zACHAvU6As28quwr-trendr/js/hoverIntent$suffix.js", array('jquery'), 'r7', 1 );

	$scripts->add( 'customize-base',     "/Source-zACHAvU6As28quwr-trendr/js/customize-base$suffix.js",     array( 'jquery', 'json2' ), false, 1 );
	$scripts->add( 'customize-loader',   "/Source-zACHAvU6As28quwr-trendr/js/customize-loader$suffix.js",   array( 'customize-base' ), false, 1 );
	$scripts->add( 'customize-preview',  "/Source-zACHAvU6As28quwr-trendr/js/customize-preview$suffix.js",  array( 'customize-base' ), false, 1 );
	$scripts->add( 'customize-controls', "/Backend-WeaprEcqaKejUbRq-trendr/js/customize-controls$suffix.js", array( 'customize-base' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'customize-controls', '_trmCustomizeControlsL10n', array(
		'activate'  => __( 'Save &amp; Activate' ),
		'save'      => __( 'Save &amp; Publish' ),
		'saved'     => __( 'Saved' ),
		'cancel'    => __( 'Cancel' ),
		'close'     => __( 'Close' ),
		'cheatin'   => __( 'Cheatin&#8217; uh?' ),

		// Used for overriding the file types allowed in plupload.
		'allowedFiles' => __( 'Allowed Files' ),
	) );

	$scripts->add( 'accordion', "/Backend-WeaprEcqaKejUbRq-trendr/js/accordion$suffix.js", array( 'jquery' ), false, 1 );

	$scripts->add( 'shortcode', "/Source-zACHAvU6As28quwr-trendr/js/shortcode$suffix.js", array( 'underscore' ), false, 1 );
	$scripts->add( 'media-models', "/Source-zACHAvU6As28quwr-trendr/js/media-models$suffix.js", array( 'trm-backbone' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'media-models', '_trmMediaModelsL10n', array(
		'settings' => array(
			'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
			'post' => array( 'id' => 0 ),
		),
	) );

	// To enqueue media-views or media-editor, call trm_enqueue_media().
	// Both rely on numerous settings, styles, and templates to operate correctly.
	$scripts->add( 'media-views',  "/Source-zACHAvU6As28quwr-trendr/js/media-views$suffix.js",  array( 'utils', 'media-models', 'trm-plupload', 'jquery-ui-sortable' ), false, 1 );
	$scripts->add( 'media-editor', "/Source-zACHAvU6As28quwr-trendr/js/media-editor$suffix.js", array( 'shortcode', 'media-views' ), false, 1 );
	$scripts->add( 'mce-view', "/Source-zACHAvU6As28quwr-trendr/js/mce-view$suffix.js", array( 'shortcode', 'media-models' ), false, 1 );

	if ( is_admin() ) {
		$scripts->add( 'ajaxcat', "/Backend-WeaprEcqaKejUbRq-trendr/js/cat$suffix.js", array( 'trm-lists' ) );
		$scripts->add_data( 'ajaxcat', 'group', 1 );
		did_action( 'init' ) && $scripts->localize( 'ajaxcat', 'catL10n', array(
			'add' => esc_attr(__('Add')),
			'how' => __('Separate multiple categories with commas.')
		) );

		$scripts->add( 'admin-tags', "/Backend-WeaprEcqaKejUbRq-trendr/js/tags$suffix.js", array('jquery', 'trm-ajax-response'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'admin-tags', 'tagslan', array(
			'noPerm' => __('You do not have permission to do that.'),
			'broken' => __('An unidentified error has occurred.')
		));

		$scripts->add( 'admin-comments', "/Backend-WeaprEcqaKejUbRq-trendr/js/edit-comments$suffix.js", array('trm-lists', 'quicktags', 'jquery-query'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'admin-comments', 'adminCommentsL10n', array(
			'hotkeys_highlight_first' => isset($_GET['hotkeys_highlight_first']),
			'hotkeys_highlight_last' => isset($_GET['hotkeys_highlight_last']),
			'replyApprove' => __( 'Approve and Reply' ),
			'reply' => __( 'Reply' )
		) );

		$scripts->add( 'xfn', "/Backend-WeaprEcqaKejUbRq-trendr/js/xfn$suffix.js", array('jquery'), false, 1 );

		$scripts->add( 'postbox', "/Backend-WeaprEcqaKejUbRq-trendr/js/postbox$suffix.js", array('jquery-ui-sortable'), false, 1 );

		$scripts->add( 'post', "/Backend-WeaprEcqaKejUbRq-trendr/js/post$suffix.js", array('suggest', 'trm-lists', 'postbox', 'heartbeat'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'post', 'postL10n', array(
			'ok' => __('OK'),
			'cancel' => __('Cancel'),
			'publishOn' => __('Publish on:'),
			'publishOnFuture' =>  __('Schedule for:'),
			'publishOnPast' => __('Published on:'),
			/* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
			'dateFormat' => __('%1$s %2$s, %3$s @ %4$s : %5$s'),
			'showcomm' => __('Show more comments'),
			'endcomm' => __('No more comments found.'),
			'publish' => __('Publish'),
			'schedule' => __('Schedule'),
			'update' => __('Update'),
			'savePending' => __('Save as Pending'),
			'saveDraft' => __('Save Draft'),
			'private' => __('Private'),
			'public' => __('Public'),
			'publicSticky' => __('Public, Sticky'),
			'password' => __('Password Protected'),
			'privatelyPublished' => __('Privately Published'),
			'published' => __('Published'),
			'comma' => _x( ',', 'tag delimiter' ),
		) );

		$scripts->add( 'link', "/Backend-WeaprEcqaKejUbRq-trendr/js/link$suffix.js", array( 'trm-lists', 'postbox' ), false, 1 );

		$scripts->add( 'comment', "/Backend-WeaprEcqaKejUbRq-trendr/js/comment$suffix.js", array( 'jquery', 'postbox' ) );
		$scripts->add_data( 'comment', 'group', 1 );
		did_action( 'init' ) && $scripts->localize( 'comment', 'commentL10n', array(
			'submittedOn' => __('Submitted on:')
		) );

		$scripts->add( 'admin-gallery', "/Backend-WeaprEcqaKejUbRq-trendr/js/gallery$suffix.js", array( 'jquery-ui-sortable' ) );

		$scripts->add( 'admin-widgets', "/Backend-WeaprEcqaKejUbRq-trendr/js/widgets$suffix.js", array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), false, 1 );

		$scripts->add( 'theme', "/Backend-WeaprEcqaKejUbRq-trendr/js/theme$suffix.js", array( 'jquery' ), false, 1 );

		// @todo: Core no longer uses theme-preview.js. Remove?
		$scripts->add( 'theme-preview', "/Backend-WeaprEcqaKejUbRq-trendr/js/theme-preview$suffix.js", array( 'thickbox', 'jquery' ), false, 1 );

		$scripts->add( 'inline-edit-post', "/Backend-WeaprEcqaKejUbRq-trendr/js/inline-edit-post$suffix.js", array( 'jquery', 'suggest', 'heartbeat' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'inline-edit-post', 'inlineEditL10n', array(
			'error' => __('Error while saving the changes.'),
			'ntdeltitle' => __('Remove From Bulk Edit'),
			'notitle' => __('(no title)'),
			'comma' => _x( ',', 'tag delimiter' ),
		) );

		$scripts->add( 'inline-edit-tax', "/Backend-WeaprEcqaKejUbRq-trendr/js/inline-edit-tax$suffix.js", array( 'jquery' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'inline-edit-tax', 'inlineEditL10n', array(
			'error' => __('Error while saving the changes.')
		) );

		$scripts->add( 'plugin-install', "/Backend-WeaprEcqaKejUbRq-trendr/js/plugin-install$suffix.js", array( 'jquery', 'thickbox' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'plugin-install', 'plugininstallL10n', array(
			'plugin_information' => __('Plugin Information:'),
			'ays' => __('Are you sure you want to install this plugin?')
		) );

		$scripts->add( 'farbtastic', '/Backend-WeaprEcqaKejUbRq-trendr/js/farbtastic.js', array('jquery'), '1.2' );

		$scripts->add( 'iris', '/Backend-WeaprEcqaKejUbRq-trendr/js/iris.min.js', array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1 );
		$scripts->add( 'trm-color-picker', "/Backend-WeaprEcqaKejUbRq-trendr/js/color-picker$suffix.js", array( 'iris' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'trm-color-picker', 'trmColorPickerL10n', array(
			'clear' => __( 'Clear' ),
			'defaultString' => __( 'Default' ),
			'pick' => __( 'Select Color' ),
			'current' => __( 'Current Color' ),
		) );

		$scripts->add( 'dashboard', "/Backend-WeaprEcqaKejUbRq-trendr/js/dashboard$suffix.js", array( 'jquery', 'admin-comments', 'postbox' ), false, 1 );

		$scripts->add( 'list-revisions', "/Source-zACHAvU6As28quwr-trendr/js/trm-list-revisions$suffix.js" );

		$scripts->add( 'media', "/Backend-WeaprEcqaKejUbRq-trendr/js/media$suffix.js", array( 'jquery-ui-draggable' ), false, 1 );

		$scripts->add( 'image-edit', "/Backend-WeaprEcqaKejUbRq-trendr/js/image-edit$suffix.js", array('jquery', 'json2', 'imgareaselect'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'image-edit', 'imageEditL10n', array(
			'error' => __( 'Could not load the preview image. Please reload the page and try again.' )
		));

		$scripts->add( 'set-post-thumbnail', "/Backend-WeaprEcqaKejUbRq-trendr/js/set-post-thumbnail$suffix.js", array( 'jquery' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'set-post-thumbnail', 'setPostThumbnailL10n', array(
			'setThumbnail' => __( 'Use as featured image' ),
			'saving' => __( 'Saving...' ),
			'error' => __( 'Could not set that as the thumbnail image. Try a different attachment.' ),
			'done' => __( 'Done' )
		) );

		// Navigation Menus
		$scripts->add( 'nav-menu', "/Backend-WeaprEcqaKejUbRq-trendr/js/nav-menu$suffix.js", array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'trm-lists', 'postbox' ) );
		did_action( 'init' ) && $scripts->localize( 'nav-menu', 'navMenuL10n', array(
			'noResultsFound' => _x('No results found.', 'search results'),
			'warnDeleteMenu' => __( "You are about to permanently delete this menu. \n 'Cancel' to stop, 'OK' to delete." ),
			'saveAlert' => __('The changes you made will be lost if you navigate away from this page.')
		) );

		$scripts->add( 'custom-header', "/Backend-WeaprEcqaKejUbRq-trendr/js/custom-header.js", array( 'jquery-masonry' ), false, 1 );
		$scripts->add( 'custom-background', "/Backend-WeaprEcqaKejUbRq-trendr/js/custom-background$suffix.js", array( 'trm-color-picker', 'media-views' ), false, 1 );
		$scripts->add( 'media-gallery', "/Backend-WeaprEcqaKejUbRq-trendr/js/media-gallery$suffix.js", array('jquery'), false, 1 );
	}
}

/**
 * Assign default styles to $styles object.
 *
 * Nothing is returned, because the $styles parameter is passed by reference.
 * Meaning that whatever object is passed will be updated without having to
 * reassign the variable that was passed back to the same value. This saves
 * memory.
 *
 * Adding default styles is not the only task, it also assigns the base_url
 * property, the default version, and text direction for the object.
 *
 * @since 2.6.0
 *
 * @param object $styles
 */
function trm_default_styles( &$styles ) {

	if ( ! $guessurl = site_url() )
		$guessurl = trm_guess_url();

	$styles->base_url = $guessurl;
	$styles->content_url = defined('TRM_CONTENT_URL')? TRM_CONTENT_URL : '';
	$styles->default_version = get_bloginfo( 'version' );
	$styles->text_direction = function_exists( 'is_rtl' ) && is_rtl() ? 'rtl' : 'ltr';
	$styles->default_dirs = array('/Backend-WeaprEcqaKejUbRq-trendr/', '/Source-zACHAvU6As28quwr-trendr/css/');

	$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

	$rtl_styles = array( 'Backend-WeaprEcqaKejUbRq-trendr', 'ie', 'media', 'admin-bar', 'customize-controls', 'media-views', 'trm-color-picker' );
	// Any rtl stylesheets that don't have a .min version
	$no_suffix = array( 'farbtastic' );

	$styles->add( 'Backend-WeaprEcqaKejUbRq-trendr', "/Backend-WeaprEcqaKejUbRq-trendr/css/Backend-WeaprEcqaKejUbRq-trendr$suffix.css" );

	$styles->add( 'ie', "/Backend-WeaprEcqaKejUbRq-trendr/css/ie$suffix.css" );
	$styles->add_data( 'ie', 'conditional', 'lte IE 7' );

	// Register "meta" stylesheet for admin colors. All colors-* style sheets should have the same version string.
	$styles->add( 'colors', true, array('Backend-WeaprEcqaKejUbRq-trendr', 'buttons') );

	// do not refer to these directly, the right one is queued by the above "meta" colors handle
	$styles->add( 'colors-fresh', "/Backend-WeaprEcqaKejUbRq-trendr/css/colors-fresh$suffix.css", array('Backend-WeaprEcqaKejUbRq-trendr', 'buttons') );
	$styles->add( 'colors-classic', "/Backend-WeaprEcqaKejUbRq-trendr/css/colors-classic$suffix.css", array('Backend-WeaprEcqaKejUbRq-trendr', 'buttons') );

	$styles->add( 'media', "/Backend-WeaprEcqaKejUbRq-trendr/css/media$suffix.css" );
	$styles->add( 'install', "/Backend-WeaprEcqaKejUbRq-trendr/css/install$suffix.css", array('buttons') );
	$styles->add( 'thickbox', '/Source-zACHAvU6As28quwr-trendr/js/thickbox/thickbox.css', array(), '20121105' );
	$styles->add( 'farbtastic', '/Backend-WeaprEcqaKejUbRq-trendr/css/farbtastic.css', array(), '1.3u1' );
	$styles->add( 'trm-color-picker', "/Backend-WeaprEcqaKejUbRq-trendr/css/color-picker$suffix.css" );
	$styles->add( 'jcrop', "/Source-zACHAvU6As28quwr-trendr/js/jcrop/jquery.Jcrop.min.css", array(), '0.9.10' );
	$styles->add( 'imgareaselect', '/Source-zACHAvU6As28quwr-trendr/js/imgareaselect/imgareaselect.css', array(), '0.9.8' );
	$styles->add( 'admin-bar', "/Source-zACHAvU6As28quwr-trendr/css/admin-bar$suffix.css" );
	$styles->add( 'trm-jquery-ui-dialog', "/Source-zACHAvU6As28quwr-trendr/css/jquery-ui-dialog$suffix.css" );
	$styles->add( 'editor-buttons', "/Source-zACHAvU6As28quwr-trendr/css/editor$suffix.css" );
	$styles->add( 'trm-pointer', "/Source-zACHAvU6As28quwr-trendr/css/trm-pointer$suffix.css" );
	$styles->add( 'customize-controls', "/Backend-WeaprEcqaKejUbRq-trendr/css/customize-controls$suffix.css", array( 'Backend-WeaprEcqaKejUbRq-trendr', 'colors', 'ie' ) );
	$styles->add( 'media-views', "/Source-zACHAvU6As28quwr-trendr/css/media-views$suffix.css", array( 'buttons' ) );
	$styles->add( 'buttons', "/Source-zACHAvU6As28quwr-trendr/css/buttons$suffix.css" );
	$styles->add( 'trm-auth-check', "/Source-zACHAvU6As28quwr-trendr/css/trm-auth-check$suffix.css" );

	$styles->add( 'mediaelement', "/Source-zACHAvU6As28quwr-trendr/js/mediaelement/mediaelementplayer.min.css", array(), '2.13.0' );
	$styles->add( 'trm-mediaelement', "/Source-zACHAvU6As28quwr-trendr/js/mediaelement/trm-mediaelement.css", array( 'mediaelement' ) );

	foreach ( $rtl_styles as $rtl_style ) {
		$styles->add_data( $rtl_style, 'rtl', true );
		if ( $suffix && ! in_array( $rtl_style, $no_suffix ) )
			$styles->add_data( $rtl_style, 'suffix', $suffix );
	}
}

/**
 * Reorder JavaScript scripts array to place prototype before jQuery.
 *
 * @since 2.3.1
 *
 * @param array $js_array JavaScript scripts array
 * @return array Reordered array, if needed.
 */
function trm_prototype_before_jquery( $js_array ) {
	if ( false === $prototype = array_search( 'prototype', $js_array, true ) )
		return $js_array;

	if ( false === $jquery = array_search( 'jquery', $js_array, true ) )
		return $js_array;

	if ( $prototype < $jquery )
		return $js_array;

	unset($js_array[$prototype]);

	array_splice( $js_array, $jquery, 0, 'prototype' );

	return $js_array;
}

/**
 * Load localized data on print rather than initialization.
 *
 * These localizations require information that may not be loaded even by init.
 *
 * @since 2.5.0
 */
function trm_just_in_time_script_localization() {

	trm_localize_script( 'autosave', 'autosaveL10n', array(
		'autosaveInterval' => AUTOSAVE_INTERVAL,
		'savingText' => __('Saving Draft&#8230;'),
		'saveAlert' => __('The changes you made will be lost if you navigate away from this page.'),
		'blog_id' => get_current_blog_id(),
	) );

}

/**
 * Administration Screen CSS for changing the styles.
 *
 * If installing the 'Backend-WeaprEcqaKejUbRq-trendr/' directory will be replaced with './'.
 *
 * The $_trm_admin_css_colors global manages the Administration Screens CSS
 * stylesheet that is loaded. The option that is set is 'admin_color' and is the
 * color and key for the array. The value for the color key is an object with
 * a 'url' parameter that has the URL path to the CSS file.
 *
 * The query from $src parameter will be appended to the URL that is given from
 * the $_trm_admin_css_colors array value URL.
 *
 * @since 2.6.0
 * @uses $_trm_admin_css_colors
 *
 * @param string $src Source URL.
 * @param string $handle Either 'colors' or 'colors-rtl'.
 * @return string URL path to CSS stylesheet for Administration Screens.
 */
function trm_style_loader_src( $src, $handle ) {
	if ( defined('TRM_INSTALLING') )
		return preg_replace( '#^Backend-WeaprEcqaKejUbRq-trendr/#', './', $src );

	if ( 'colors' == $handle || 'colors-rtl' == $handle ) {
		global $_trm_admin_css_colors;
		$color = get_user_option('admin_color');

		if ( empty($color) || !isset($_trm_admin_css_colors[$color]) )
			$color = 'fresh';

		$color = $_trm_admin_css_colors[$color];
		$parsed = parse_url( $src );
		$url = $color->url;

		if ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG )
			$url = preg_replace( '/.min.css$|.min.css(?=\?)/', '.css', $url );

		if ( isset($parsed['query']) && $parsed['query'] ) {
			trm_parse_str( $parsed['query'], $qv );
			$url = add_query_arg( $qv, $url );
		}

		return $url;
	}

	return $src;
}

/**
 * Prints the script queue in the HTML head on admin pages.
 *
 * Postpones the scripts that were queued for the footer.
 * print_footer_scripts() is called in the footer to print these scripts.
 *
 * @since 2.8
 * @see trm_print_scripts()
 */
function print_head_scripts() {
	global $trm_scripts, $concatenate_scripts;

	if ( ! did_action('trm_print_scripts') )
		do_action('trm_print_scripts');

	if ( !is_a($trm_scripts, 'TRM_Scripts') )
		$trm_scripts = new TRM_Scripts();

	script_concat_settings();
	$trm_scripts->do_concat = $concatenate_scripts;
	$trm_scripts->do_head_items();

	if ( apply_filters('print_head_scripts', true) )
		_print_scripts();

	$trm_scripts->reset();
	return $trm_scripts->done;
}

/**
 * Prints the scripts that were queued for the footer or too late for the HTML head.
 *
 * @since 2.8
 */
function print_footer_scripts() {
	global $trm_scripts, $concatenate_scripts;

	if ( !is_a($trm_scripts, 'TRM_Scripts') )
		return array(); // No need to run if not instantiated.

	script_concat_settings();
	$trm_scripts->do_concat = $concatenate_scripts;
	$trm_scripts->do_footer_items();

	if ( apply_filters('print_footer_scripts', true) )
		_print_scripts();

	$trm_scripts->reset();
	return $trm_scripts->done;
}

/**
 * @internal use
 */
function _print_scripts() {
	global $trm_scripts, $compress_scripts;

	$zip = $compress_scripts ? 1 : 0;
	if ( $zip && defined('ENFORCE_GZIP') && ENFORCE_GZIP )
		$zip = 'gzip';

	if ( $concat = trim( $trm_scripts->concat, ', ' ) ) {

		if ( !empty($trm_scripts->print_code) ) {
			echo "\n<script type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n"; // not needed in HTML 5
			echo $trm_scripts->print_code;
			echo "/* ]]> */\n";
			echo "</script>\n";
		}

		$concat = str_split( $concat, 128 );
		$concat = 'load%5B%5D=' . implode( '&load%5B%5D=', $concat );

		$src = $trm_scripts->base_url . "/Backend-WeaprEcqaKejUbRq-trendr/load-scripts.php?c={$zip}&" . $concat . '&ver=' . $trm_scripts->default_version;
		echo "<script type='text/javascript' src='" . esc_attr($src) . "'></script>\n";
	}

	if ( !empty($trm_scripts->print_html) )
		echo $trm_scripts->print_html;
}

/**
 * Prints the script queue in the HTML head on the front end.
 *
 * Postpones the scripts that were queued for the footer.
 * trm_print_footer_scripts() is called in the footer to print these scripts.
 *
 * @since 2.8
 */
function trm_print_head_scripts() {
	if ( ! did_action('trm_print_scripts') )
		do_action('trm_print_scripts');

	global $trm_scripts;

	if ( !is_a($trm_scripts, 'TRM_Scripts') )
		return array(); // no need to run if nothing is queued

	return print_head_scripts();
}

/**
 * Private, for use in *_footer_scripts hooks
 *
 * @since 3.3.0
 */
function _trm_footer_scripts() {
	print_late_styles();
	print_footer_scripts();
}

/**
 * Hooks to print the scripts and styles in the footer.
 *
 * @since 2.8
 */
function trm_print_footer_scripts() {
	do_action('trm_print_footer_scripts');
}

/**
 * Wrapper for do_action('trm_enqueue_scripts')
 *
 * Allows plugins to queue scripts for the front end using trm_enqueue_script().
 * Runs first in trm_head() where all is_home(), is_page(), etc. functions are available.
 *
 * @since 2.8
 */
function trm_enqueue_scripts() {
	do_action('trm_enqueue_scripts');
}

/**
 * Prints the styles queue in the HTML head on admin pages.
 *
 * @since 2.8
 */
function print_admin_styles() {
	global $trm_styles, $concatenate_scripts, $compress_css;

	if ( !is_a($trm_styles, 'TRM_Styles') )
		$trm_styles = new TRM_Styles();

	script_concat_settings();
	$trm_styles->do_concat = $concatenate_scripts;
	$zip = $compress_css ? 1 : 0;
	if ( $zip && defined('ENFORCE_GZIP') && ENFORCE_GZIP )
		$zip = 'gzip';

	$trm_styles->do_items(false);

	if ( apply_filters('print_admin_styles', true) )
		_print_styles();

	$trm_styles->reset();
	return $trm_styles->done;
}

/**
 * Prints the styles that were queued too late for the HTML head.
 *
 * @since 3.3.0
 */
function print_late_styles() {
	global $trm_styles, $concatenate_scripts;

	if ( !is_a($trm_styles, 'TRM_Styles') )
		return;

	$trm_styles->do_concat = $concatenate_scripts;
	$trm_styles->do_footer_items();

	if ( apply_filters('print_late_styles', true) )
		_print_styles();

	$trm_styles->reset();
	return $trm_styles->done;
}

/**
 * @internal use
 */
function _print_styles() {
	global $trm_styles, $compress_css;

	$zip = $compress_css ? 1 : 0;
	if ( $zip && defined('ENFORCE_GZIP') && ENFORCE_GZIP )
		$zip = 'gzip';

	if ( !empty($trm_styles->concat) ) {
		$dir = $trm_styles->text_direction;
		$ver = $trm_styles->default_version;
		$href = $trm_styles->base_url . "/Backend-WeaprEcqaKejUbRq-trendr/load-styles.php?c={$zip}&dir={$dir}&load=" . trim($trm_styles->concat, ', ') . '&ver=' . $ver;
		echo "<link rel='stylesheet' href='" . esc_attr($href) . "' type='text/css' media='all' />\n";

		if ( !empty($trm_styles->print_code) ) {
			echo "<style type='text/css'>\n";
			echo $trm_styles->print_code;
			echo "\n</style>\n";
		}
	}

	if ( !empty($trm_styles->print_html) )
		echo $trm_styles->print_html;
}

/**
 * Determine the concatenation and compression settings for scripts and styles.
 *
 * @since 2.8
 */
function script_concat_settings() {
	global $concatenate_scripts, $compress_scripts, $compress_css;

	$compressed_output = ( ini_get('zlib.output_compression') || 'ob_gzhandler' == ini_get('output_handler') );

	if ( ! isset($concatenate_scripts) ) {
		$concatenate_scripts = defined('CONCATENATE_SCRIPTS') ? CONCATENATE_SCRIPTS : true;
		if ( ! is_admin() || ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) )
			$concatenate_scripts = false;
	}

	if ( ! isset($compress_scripts) ) {
		$compress_scripts = defined('COMPRESS_SCRIPTS') ? COMPRESS_SCRIPTS : true;
		if ( $compress_scripts && ( ! get_site_option('can_compress_scripts') || $compressed_output ) )
			$compress_scripts = false;
	}

	if ( ! isset($compress_css) ) {
		$compress_css = defined('COMPRESS_CSS') ? COMPRESS_CSS : true;
		if ( $compress_css && ( ! get_site_option('can_compress_scripts') || $compressed_output ) )
			$compress_css = false;
	}
}

add_action( 'trm_default_scripts', 'trm_default_scripts' );
add_filter( 'trm_print_scripts', 'trm_just_in_time_script_localization' );
add_filter( 'print_scripts_array', 'trm_prototype_before_jquery' );

add_action( 'trm_default_styles', 'trm_default_styles' );
add_filter( 'style_loader_src', 'trm_style_loader_src', 10, 2 );
