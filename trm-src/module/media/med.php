<?php
/*
Plugin Name: media
Plugin URI: http://premium.trmmudev.org/project/media-embeds-for-trendr-activity
Description: Media sharing.
Version: 1.6.4
Author: TRMMU DEV
Author URI: http://premium.trmmudev.org
WDP ID: 232

Copyright 2009-2011 Incsub (http://incsub.com)
Author - Ve Bailovity (Incsub)
Designed by Brett Sirianni (The Edge)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define ('MED_PLUGIN_SELF_DIRNAME', basename(dirname(__FILE__)));
define ('MED_PROTOCOL', (is_ssl() ? 'https://' : 'http://'));
$GLOBALS['VIDEOS_EXT']= array("mp4","mov");
//supporting function to check the file type using exts
function endsWith($haystack, $needle)
{
    $length = strlen($needle);

    return $length === 0 ||
    (substr($haystack, -$length) === $needle);
}
function IsVideoFile($file)
{
     foreach ($GLOBALS['VIDEOS_EXT'] as $key => $ext) {
       if(endsWith($file,$ext)){
         return true;
       }
     }
     return false;
}
if (defined('TRM_PLUGIN_URL') && defined('TRM_PLUGIN_DIR') && file_exists(TRM_PLUGIN_DIR . '/' . MED_PLUGIN_SELF_DIRNAME . '/' . basename(__FILE__))) {
	define ('MED_PLUGIN_LOCATION', 'subfolder-plugins');
	define ('MED_PLUGIN_BASE_DIR', TRM_PLUGIN_DIR . '/' . MED_PLUGIN_SELF_DIRNAME);
	define ('MED_PLUGIN_URL', str_replace('http://', MED_PROTOCOL, TRM_PLUGIN_URL) . '/' . MED_PLUGIN_SELF_DIRNAME);
	$textdomain_handler = 'load_plugin_textdomain';
} else if (defined('TRM_PLUGIN_URL') && defined('TRM_PLUGIN_DIR') && file_exists(TRM_PLUGIN_DIR . '/' . basename(__FILE__))) {
	define ('MED_PLUGIN_LOCATION', 'plugins');
	define ('MED_PLUGIN_BASE_DIR', TRM_PLUGIN_DIR);
	define ('MED_PLUGIN_URL', str_replace('http://', MED_PROTOCOL, TRM_PLUGIN_URL));
	$textdomain_handler = 'load_plugin_textdomain';
} else {
	// No textdomain is loaded because we can't determine the plugin location.
	// No point in trying to add textdomain to string and/or localizing it.
	trm_die(__('There was an issue determining where media plugin is installed. Please reinstall.'));
}
$textdomain_handler('med', false, MED_PLUGIN_SELF_DIRNAME . '/languages/');

// Override oEmbed width in trm-setup.php
//if (!defined('MED_OEMBED_WIDTH')) define('MED_OEMBED_WIDTH', 450, true); // Don't define by default
// Override image limit in trm-setup.php
if (!defined('MED_IMAGE_LIMIT')) define('MED_IMAGE_LIMIT', 5);
// Override link target preference in trm-setup.php
if (!defined('MED_LINKS_TARGET')) define('MED_LINKS_TARGET', false);


$trm_upload_dir = trm_upload_dir();
define('MED_TEMP_IMAGE_DIR', $trm_upload_dir['basedir'] . '/med/tmp/');
define('MED_TEMP_IMAGE_URL', $trm_upload_dir['baseurl'] . '/med/tmp/');
define('MED_BASE_IMAGE_DIR', $trm_upload_dir['basedir'] . '/med/');
define('MED_BASE_IMAGE_URL', $trm_upload_dir['baseurl'] . '/med/');



// Hook up the installation routine and check if we're really, really set to go
require_once MED_PLUGIN_BASE_DIR . '/lib/class_med_installer.php';
register_activation_hook(__FILE__, array('MedInstaller', 'install'));
MedInstaller::check();

// Require the data wrapper
require_once MED_PLUGIN_BASE_DIR . '/lib/class_med_data.php';

/**
 * Helper functions for going around the fact that
 * trendr is NOT multisite compatible.
 */
function med_get_image_url ($blog_id) {
	if (!defined('TRS_ENABLE_MULTIBLOG') || !TRS_ENABLE_MULTIBLOG) return str_replace('http://', MED_PROTOCOL, MED_BASE_IMAGE_URL);
	if (!$blog_id) return str_replace('http://', MED_PROTOCOL, MED_BASE_IMAGE_URL);
	switch_to_blog($blog_id);
	$trm_upload_dir = trm_upload_dir();
	var_dump($trm_upload_dir);
	restore_current_blog();
	return str_replace('http://', MED_PROTOCOL, $trm_upload_dir['baseurl']) . '/med/';
}
function med_get_image_dir ($blog_id) {
	if (!defined('TRS_ENABLE_MULTIBLOG') || !TRS_ENABLE_MULTIBLOG) return MED_BASE_IMAGE_DIR;
	if (!$blog_id) return MED_BASE_IMAGE_DIR;
	switch_to_blog($blog_id);
	$trm_upload_dir = trm_upload_dir();
	restore_current_blog();
	return $trm_upload_dir['basedir'] . '/med/';
}


/**
 * Includes the core requirements and serves the improved activity box.
 */
function med_plugin_init () {
	require_once(MED_PLUGIN_BASE_DIR . '/lib/class_med_binder.php');
	require_once(MED_PLUGIN_BASE_DIR . '/lib/class_med_codec.php');
	// Group Documents integration
	if (defined('TRS_GROUP_DOCUMENTS_IS_INSTALLED') && TRS_GROUP_DOCUMENTS_IS_INSTALLED) {
		require_once(MED_PLUGIN_BASE_DIR . '/lib/med_group_documents.php');
	}
	if (is_admin()) {
		if (file_exists(MED_PLUGIN_BASE_DIR . '/lib/external/trmmudev-dash-notification.php')) {
			global $trmmudev_notices;
			if (!is_array($trmmudev_notices)) $trmmudev_notices = array();
			$trmmudev_notices[] = array(
				'id' => 232,
				'name' => 'media',
				'screens' => array(
					'settings_page_med-settings',
				),
			);
			require_once MED_PLUGIN_BASE_DIR . '/lib/external/trmmudev-dash-notification.php';
		}
		require_once MED_PLUGIN_BASE_DIR . '/lib/class_med_admin_pages.php';
		Med_Admin::serve();
	}

	do_action('med_init');
	MedBinder::serve();
}
// Only fire off if TRS is actually loaded.
add_action('trs_loaded', 'med_plugin_init');