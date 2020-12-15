<?php

	class FileCache
	{
		protected $dir;

		/**
		 * @var array Froups for which directories have already been created
		 */
		private $known_groups = array();

		/**
		 * @var array In-memory Cache
		 */
		private $cache = array();

		private $enabled;
		private $persist;
		private $no_ext_fetch;

		private static $memory_limit;
		private static $memory_low;

		/**
		 * @var array Non-persistent groups
		 */
		protected $np_groups  = array();

		public static function instance($dir = '/tmp', $enabled = true, $persist = true, $no_ext_fetch = false)
		{
			static $self = false;

			if (!$self) {
				$self = new FileCache($dir, $enabled, $persist, $no_ext_fetch);
			}

			return $self;
		}

		private function __construct($path = '/tmp', $enabled = true, $persist = true, $no_ext_fetch = false)
		{
			if (empty($path)) {
				$path = '/tmp';
			}

			$this->dir          = $path;
			$this->enabled      = $enabled;
			$this->persist      = $enabled && $persist;
			$this->no_ext_fetch = $no_ext_fetch;

			if (defined('TRM_FILE_CACHE_LOW_RAM') && function_exists('memory_get_usage')) {
				$limit = trim(ini_get('memory_limit'));
				$mod   = strtolower($limit[strlen($limit)-1]);
				switch ($mod) {
					case 'g': $limit *= 1073741824; break;
					case 'm': $limit *= 1048576; break;
					case 'k': $limit *= 1024; break;
				}

				if ($limit <= 0) {
					$limit = 0;
				}

				self::$memory_limit = $limit;

				$limit = trim(TRM_FILE_CACHE_LOW_RAM);
				$mod   = strtolower($limit[strlen($limit)-1]);
				switch ($mod) {
					case 'g': $limit *= 1073741824; break;
					case 'm': $limit *= 1048576; break;
					case 'k': $limit *= 1024; break;
				}

				self::$memory_low = $limit;
			}
			else {
				self::$memory_limit = 0;
				self::$memory_low   = 0;
			}
		}

		public function add($key, $data, $group)
		{
			if (!$this->enabled) {
				return false;
			}

			if (false !== $this->get($key, $group)) {
				return false;
			}

			return $this->set($key, $data, $group);
		}

		public function delete($key, $group)
		{
			unset($this->cache[$group][$key]);

			if ($this->persist && false === in_array($group, $this->np_groups)) {
				$fname = $this->keyToPath($key, $group);
				return @unlink($fname);
			}

			return true;
		}

		protected function remove_dir($dir, $self)
		{
			$dh = @opendir($dir);
			if (!is_resource($dh)) {
				return;
			}

			while (false !== ($obj = readdir($dh))) {
				if ('.' == $obj || '..' == $obj) {
					continue;
				}

				if (false == @unlink($dir . '/' . $obj)) {
					$this->remove_dir($dir . '/' . $obj, true);
				}
			}

			closedir($dh);
			if ($self) {
				@rmdir($dir);
			}
		}

		public function flush()
		{
			$this->remove_dir($this->dir, false);
			$this->cache = array();
			$this->known_groups = array();
			return true;
		}

		public function set($key, $data, $group)
		{
			if (!$this->enabled) {
				return false;
			}

			if (self::$memory_limit) {
				$usage = memory_get_usage();
				if (self::$memory_limit - $usage < self::$memory_low) {
					@unlink($this->keyToPath($key, $group));
					return false;
				}
			}

			if (is_object($data)) {
				$data = clone($data);
			}

			$this->cache[$group][$key] = $data;
			if ($this->persist && !isset($this->np_groups[$group])) {
				$fname = $this->keyToPath($key, $group);
				return false !== @file_put_contents($fname, serialize($data), LOCK_EX);
			}

			return true;
		}

		public function get($key, $group, $ttl = 3600)
		{
			if (!$this->enabled) {
				return false;
			}

			if (isset($this->cache[$group], $this->cache[$group][$key])) {
				return $this->cache[$group][$key];
			}

			if (!$this->no_ext_fetch && $this->persist && !isset($this->np_groups[$group])) {
				if (self::$memory_limit) {
					$usage = memory_get_usage();
					if (self::$memory_limit - $usage < self::$memory_low) {
						return false;
					}
				}

				$fname = $this->keyToPath($key, $group);
				$files = glob($fname);
				if (empty($files) || !isset($files[0])) {
					return false;
				}

				if (is_readable($files[0])) {
					$result = $files[0];
					if (@filemtime($result) > time() - $ttl) {
						settype($result, 'string');
						$this->cache[$group][$key] = unserialize(@file_get_contents($result, LOCK_EX));
						$result = $this->cache[$group][$key];
						return is_object($result) ? clone($result) : $result;
					}
				}

				@unlink($files[0]);
			}

			return false;
		}

		public function replace($key, $data, $group)
		{
			if (false === $this->get($key, $group)) {
				return false;
			}

			return $this->set($key, $data, $group);
		}

		public function addNonPersistentGroups($groups)
		{
			if (false == is_array($groups)) {
				$groups = array($groups);
			}

			$this->np_groups = array_merge(
				$this->np_groups,
				$groups
			);

			$this->np_groups = array_combine($this->np_groups, $this->np_groups);
		}

		public function clearNonPersistentGroups()
		{
			$this->np_groups = array();
		}

		protected function keyToPath($key, $group)
		{
			if (!isset($this->known_groups[$group])) {
				$dir = $this->dir . '/' . urlencode($group);
				if (!file_exists($dir)) {
					@mkdir($dir);
				}

				$this->known_groups[$group] = true;
			}

			return $this->dir . '/' . urlencode($group) . '/' . urlencode($key) . '.cache';
		}
	}
?>