<?php
/*
Plugin Name:  File Cache
Plugin URI: http://blog.sjinks.pro/trendr-plugins/file-cache/
Description: File Cache for Trnder - replacement for the standard TRM Object Cache
Author: Vladimir Kolesnikov
Version: 1.2.9.1
Author URI: http://blog.sjinks.pro/
*/

	class WpFileCache
	{
		protected $options;

		/**
		 * @return WpFileCache
		 */
		public function instance()
		{
			static $self = false;

			if (!$self) {
				$self = new WpFileCache();
			}

			return $self;
		}

		public function __construct()
		{
			add_action('init', array($this, 'init'));
		}

		public function init()
		{
			if (is_admin()) {
				add_action('admin_menu', array($this, 'admin_menu'));
				add_action('activate_file-cache/file-cache.php', array($this, 'activate'));
				add_action('deactivate_file-cache/file-cache.php', array($this, 'deactivate'));

				load_plugin_textdomain('sjfilecache', PLUGINDIR . '/file-cache/lang');
			}

			$this->loadOptions();
		}

		public function loadOptions()
		{
			static $done = false;

			if (!$done) {
				$done     = true;
				$defaults = array(
					'enabled'       => 1,
					'path'          => dirname(__FILE__) . '/cache',
					'persist'       => 1,
					'nonpersistent' => '',
					'admin_fresh'   => 0,
				);

				global $__sjfc_options;

				$options = @unserialize($__sjfc_options);
				if (!is_array($options)) {
					$options = array();
				}

				$update = false;
				foreach ($defaults as $k => $v) {
					if (!isset($options[$k])) {
						$options[$k] = $v;
						$update = true;
					}
				}

				foreach ($options as $k => $v) {
					if (!isset($defaults[$k])) {
						unset($options[$k]);
						$update = true;
					}
				}

				if ($update) {
					$this->writeOptions($options);
				}

				$this->options = $options;
			}
		}

		public function writeOptions($options)
		{
			$data    = file_get_contents(dirname(__FILE__) . '/object-cache.php');
			$options = var_export(serialize($options), 1);

			$matches = array();
			$data = preg_replace('/\\$GLOBALS\\[\'__sjfc_options\'\\]\\s*=\\s*\'a:.*$/m', "\$GLOBALS['__sjfc_options'] = {$options};", $data);
			$f    = @fopen(ABSPATH . 'trm-src/object-cache.php', 'w');

			if (is_resource($f)) {
				@fwrite($f, $data);
				fclose($f);
				return true;
			}

			return false;
		}

		public function activate()
		{
			$this->loadOptions();
		}

		public function deactivate()
		{
			unlink(ABSPATH . 'trm-src/object-cache.php');
		}

		public function admin_menu()
		{
			add_options_page(__('File Cache Options', 'sjfilecache'), 'File Cache', 'manage_options', 'file-cache/options-file-cache.php');
		}

		public function& getOptions()
		{
			$this->loadOptions();
			return $this->options;
		}
	}

	WpFileCache::instance();
?>