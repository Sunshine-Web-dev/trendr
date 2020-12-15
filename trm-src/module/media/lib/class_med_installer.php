<?php

/**
 * Handles plugin installation.
 */
class MedInstaller {

	/**
	 * Entry method.
	 *
	 * Handles Plugin installation.
	 *
	 * @access public
	 * @static
	 */
	static function install () {
		$me = new MedInstaller;
		if ($me->prepare_paths()) {
			$me->set_default_options();
		} else $me->kill_default_options();
	}

	/**
	 * Checks to see if the plugin is installed.
	 *
	 * If not, installs it.
	 *
	 * @access public
	 * @static
	 */
	static function check () {
		$is_installed = get_option('med_plugin', false);
		if (!$is_installed) return MedInstaller::install();
		if (!MedInstaller::check_paths()) return MedInstaller::install();
		return true;
	}

	/**
	 * Checks to see if we have the proper paths and if they're writable.
	 *
	 * @access private
	 */
	static function check_paths () {
		if (!file_exists(MED_TEMP_IMAGE_DIR)) return false;
		if (!file_exists(MED_BASE_IMAGE_DIR)) return false;
		if (!is_writable(MED_TEMP_IMAGE_DIR)) return false;
		if (!is_writable(MED_BASE_IMAGE_DIR)) return false;
		return true;
	}

	/**
	 * Prepares paths that will be used.
	 *
	 * @access private
	 */
	function prepare_paths () {
		$ret = true;

		if (!file_exists(MED_TEMP_IMAGE_DIR)) $ret = trm_mkdir_p(MED_TEMP_IMAGE_DIR);
		if (!$ret) return false;

		if (!file_exists(MED_BASE_IMAGE_DIR)) $ret = trm_mkdir_p(MED_BASE_IMAGE_DIR);
		if (!$ret) return false;

		return true;
	}

	/**
	 * (Re)sets Plugin options to defaults.
	 *
	 * @access private
	 */
	function set_default_options () {
		$options = array (
			'installed' => 1,
		);
		update_option('med_plugin', $options);
	}

	/**
	 * Removes plugin default options.
	 */
	function kill_default_options () {
		delete_option('med_plugin');
	}
}