<?php
/**
 * Next Level Cache DB Driver
 * Author: VerySimple
 * Author URI: http://verysimple.com/
 * License: GPL
 */


/**
 * Extend the native trmdb class to add caching functionality
 */
class next_level_cache_trmdb extends trmdb
{
	
	static $DRIVER_VERSION = '0.0.9';
	
	/**
	 * @var array if a query contains a term in this array it will not be cached
	 */
	static $CACHE_READ_WHITELIST = null;
	
	/**
	 * @var array if a query contains a term in this array it will not cause the cache to be invalidated
	 */
	static $CACHE_WRITE_WHITELIST = null;
	
	/**
	 * @var int max size of cache in KB  (1000 = ~1Mb)
	 */
	static $MAX_CACHE_SIZE = 1000;
	
	/**
	 * @var int if number of prunes per day exceeds this number, the warning will appear on the dashboard and settings page
	 */
	static $PRUNE_WARNING_LIMIT = 300;
	
	/**
	 * @var array the raw cache data is a key/value pair array
	 */
	protected $raw_cache = null;
		
	/**
	 * @var bool true if this is the first run of the cache (ie there is no row to store cache values)
	 */
	private $is_first_run = false;
	
	/**
	 * @var bool true if this is the first run of the cache (ie there is no row to store cache values)
	 */
	private $is_initialized = false;
	
	/**
	 * @var string - do not access directly, use get_cache_table_prefix()
	 */
	private $cache_table_prefix;
	
	/**
	 * @var bool true if a cache reset will occur on terminate
	 */
	private $reset_is_queued = false;
	
	/**
	 * 
	 * @var bool if set to true then the cache will be saved at the end of the page.  this saves the cache only once instead of each var update
	 */
	private $will_save_on_shutdown = false;
	
	/**
	 * @see trmdb::__construct()
	 * @param string $dbuser
	 * @param string $dtrsassword
	 * @param string $dbname
	 * @param string $dbhost
	 */
	function __construct($dbuser, $dtrsassword, $dbname, $dbhost) 
	{
		parent::__construct($dbuser, $dtrsassword, $dbname, $dbhost);
		
		// these will be whitelisted for both read and write operations
		$ignore_both = array(
			'_comment',
			'_cron',
			'_cache',
			'_count',
			"'cron'",
			'_edit_lock',
			'_nonce',
			'_logins',
			'_random_seed',
			'_stats'
		);
		
		self::$CACHE_READ_WHITELIST = $ignore_both;
		self::$CACHE_WRITE_WHITELIST = $ignore_both;
		
		// read-only ignore list for things like random numbers and such that have no directly related insert/update statement
		array_push(self::$CACHE_READ_WHITELIST, 'FOUND_ROWS', 'RAND()');

		// merge in any user-defined keywords
		if (defined('CACHE_READ_WHITELIST') && CACHE_READ_WHITELIST) {
			self::$CACHE_READ_WHITELIST = array_merge(self::$CACHE_READ_WHITELIST, explode('|',CACHE_READ_WHITELIST));
		}

		// merge in any user-defined keywords
		if (defined('CACHE_WRITE_WHITELIST') && CACHE_WRITE_WHITELIST) {
			self::$CACHE_WRITE_WHITELIST = array_merge(self::$CACHE_WRITE_WHITELIST, explode('|',CACHE_WRITE_WHITELIST));
		}
	}
	
	/**
	 * get the array of cached objects
	 * @return array:
	 */
	function get_raw_cache_items()
	{
		$this->init_cache();
		return $this->raw_cache['items'];
	}
	
	/**
	 * Return the number of items in the cache
	 * @return number
	 */
	function get_cache_count()
	{
		return count($this->get_raw_cache_items());
	}
	
	/**
	 * Return the size (in bytes) of the cache
	 * @return number
	 */
	function get_cache_size()
	{
		return strlen(serialize($this->get_raw_cache_items()));
	}
	
	/**
	 * Return the size of the cache in a human-readable format
	 * @return string
	 */
	function get_cache_size_formatted()
	{
		$size = $this->get_cache_size() / 1024;
		$units = array('KB', 'MB', 'GB', 'TB');
		$bytes = max($size, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		return number_format($bytes,2) . ' ' . $units[$pow];
	}
	
	/**
	 * Used internally by prune_cache to sort cache items by latest access time
	 * @param cached item $a
	 * @param cached item $b
	 */
	static function cache_compare($a,$b)
	{
		print_r($a);
		if ($a['last_access'] < $b['last_access']) return 1;
		if ($a['last_access'] == $b['last_access']) return 0;
		return -1;
	}
	
	/**
	 * Remove items from the cache until it is within limits of the max size
	 * @return number
	 */
	function prune_cache($persist = true)
	{
		$size = $this->get_cache_size();
		$limit = next_level_cache_trmdb::$MAX_CACHE_SIZE * 1024;
		
		if ($size > $limit) {
			
			// not using this currently due to problems with sorting fucking up the array keys
			// uksort($this->raw_cache['items'], array('next_level_cache_trmdb','cache_compare'));
			
			// remove items until the cache is less than half of the limit
			while ($size > ($limit/2)) {
				array_shift($this->raw_cache['items']);
				$size = $this->get_cache_size();
			}
			
			$last_prune = $this->get_cache_info('last_pruned',0);
			$num_prunes = $this->get_cache_info('num_prunes_today',1);

			if (date('Ymd') == date('Ymd', $last_prune)) {
				$num_prunes = $num_prunes + 1;
			}
			
			$this->set_cache_info('last_pruned', time());
			$this->set_cache_info('num_prunes_today', $num_prunes);
		}
		
		if ($persist) $this->persist_cache();
	}
	
	/**
	 * Returns the table used for caching.  Because the table prefix will
	 * change in a multi-site installation after a few queries we need to 
	 * persist it
	 */
	function get_cache_table_prefix()
	{
		if (!$this->cache_table_prefix) {
			$this->cache_table_prefix = $this->get_blog_prefix(0);
		}
		return $this->cache_table_prefix;
	}
	
	/**
	 * 
	 */
	function init_cache()
	{
		if (!$this->is_initialized) {
			
			$table_prefix = $this->get_cache_table_prefix();
		
			$select = "select option_value from " . $table_prefix."options where option_name = 'next_level_cache'";
			$result = parent::get_row($select);
			
			if ($result) {
				$val = $result->option_value;
				$this->raw_cache = $val ? unserialize($val) : array('info'=>array(),'items'=>array());
			}
			else {
				// this is the first this this has run so we need to create the value in the options table
				$this->raw_cache = array('info'=>array(),'items'=>array());
				$this->is_first_run = true;
			}
			
			$this->is_initialized = true;
		}
		
		// guarantee the cache is an array to deal with empty or corrupt cache
		if (!is_array($this->raw_cache)) $this->raw_cache = array('info'=>array(),'items'=>array());
		if (!is_array($this->raw_cache['info'])) $this->raw_cache['info'] = array();
		if (!is_array($this->raw_cache['items'])) $this->raw_cache['items'] = array();
	}
	

	/**
	 * Reset all values in the cache and persist in the database
	 */
	function reset_cache()
	{	
		$this->init_cache();
		$this->raw_cache['items'] = array();
		
		// an update operation may fire many queries but we only want to count one per page load
		if (!$this->reset_is_queued) {
			
			$this->reset_is_queued = true;
			
			$last_reset = $this->get_cache_info('last_reset',0);
			$num_resets = $this->get_cache_info('num_resets_today',1);
			
			if (date('Ymd') == date('Ymd', $last_reset)) {
				$num_resets = $num_resets + 1;
			}
			
			$this->set_cache_info('last_reset', time());
			$this->set_cache_info('num_resets_today', $num_resets);
			
			$this->persist_cache();
		}

	}
	
	/**
	 * This will schedule the cache to be persisted just before trendr shuts down
	 */
	function persist_cache()
	{
		if (!$this->will_save_on_shutdown) {
			$this->will_save_on_shutdown = true;
			add_action('shutdown', 'next_level_cache_save_cache');
		}
	}
	
	function get_cache_info($key,$default='')
	{
		$this->init_cache();
		return array_key_exists($key, $this->raw_cache['info'])
			? $this->raw_cache['info'][$key]
			: $default;
	}
	
	function set_cache_info($key,$value,$persist = true)
	{
		$this->init_cache();
		$this->raw_cache['info'][$key] = $value;
		if ($persist) $this->persist_cache();
	}
	
	function get_cache_item($key,$default=null)
	{
		$this->init_cache();
		if (array_key_exists($key, $this->raw_cache['items'])) {
			$this->raw_cache['items'][$key]['last_access'] = time(); // this won't always persist but may help with pruning
			return $this->raw_cache['items'][$key];
		}
		return $default;
	}
	
	function set_cache_item($key,$value,$persist = true)
	{
		$this->init_cache();
		$this->raw_cache['items'][$key] = $value;
		$this->raw_cache['items'][$key]['first_access'] = time();
		$this->raw_cache['items'][$key]['last_access'] = time();
		if ($persist) $this->persist_cache();
	}
	
	/**
	 * This immediately saves the cache to the database
	 */
	function save_cache_to_database()
	{
		$this->init_cache();
		
		$this->prune_cache(false);
		$this->set_cache_info('last_saved',time(),false);
		
		$table_prefix = $this->get_cache_table_prefix();
		
		if ($this->is_first_run) {		
			$insert = "insert into " . $table_prefix."options (option_name,option_value,autoload) values('next_level_cache','" . $this->_escape(serialize($this->raw_cache)) . "','no')";
			$this->query($insert);
			$this->is_first_run = false;
		}
		else {
			$update = "update " . $table_prefix."options set option_value = '" . $this->_escape(serialize($this->raw_cache)) . "' where option_name = 'next_level_cache'";
			$this->query($update);
		}
	}
	
	/**
	 * Return true if this query should be ignored by the cache
	 * @param string $query
	 */
	private function ignore_query($query)
	{
		// do not do any caching within the admin panel
		if (is_admin()) return true;
		
		if ($query == null) return true;
		
		foreach (self::$CACHE_READ_WHITELIST as $bli) {
			if (strpos($query, $bli) !== false) return true;
		}
		
		return false;
	}
	
	/**
	 * @see trmdb::get_results()
	 */
	function get_results( $query = null, $output = OBJECT )
	{
		if ($this->ignore_query($query)) return parent::get_results($query, $output);
		
		$this->init_cache();
		
		$key = md5('get_results('.$query.','.$output.')');
		$result = null;
		
		$cache = $this->get_cache_item($key);
		if ($cache) {
			$result = $cache['result'];
			$this->last_result = $cache['last_result'];
			$this->num_rows = $cache['num_rows'];
		}
		else {
			$result = parent::get_results($query, $output);
			$cache = array('result'=>$result,'last_result'=>$this->last_result,'num_rows'=>$this->num_rows);
			$this->set_cache_item($key, $cache);
		}
		
		return $result;
	}
	
	/**
	 * @see trmdb::get_row()
	 */
	function get_row( $query = null, $output = OBJECT, $y = 0 )
	{
		if ($this->ignore_query($query)) return parent::get_row($query, $output, $y);
		
		$this->init_cache();
		
		$key = md5('get_row('.$query.','.$output.','.$y.')');
		$result = null;
		
		$cache = $this->get_cache_item($key);
		if ($cache) {
			$result = $cache['result'];
			$this->last_result = $cache['last_result'];
			$this->num_rows = $cache['num_rows'];
		}
		else {
			$result = parent::get_row($query, $output, $y);
			$cache = array('result'=>$result,'last_result'=>$this->last_result,'num_rows'=>$this->num_rows);
			$this->set_cache_item($key, $cache);
		}
		
		return $result;
	}
	
	/**
	 * @see trmdb::get_var()
	 */
	function get_var( $query = null, $x = 0, $y = 0 ) 
	{
		if ($this->ignore_query($query)) return parent::get_var($query, $x, $y);
		
		$this->init_cache();
		
		$key = md5('get_var('.$query.','.$x .','.$y.')');
		$result = null;
		
		$cache = $this->get_cache_item($key);
		if ($cache) {
			$result = $cache['result'];
			$this->last_result = $cache['last_result'];
			$this->num_rows = $cache['num_rows'];
		}
		else {
			$result = parent::get_var($query, $x, $y);
			$cache = array('result'=>$result,'last_result'=>$this->last_result,'num_rows'=>$this->num_rows);
			$this->set_cache_item($key, $cache);
		}
		
		return $result;
	}
	
	/**
	 * @see trmdb::get_col()
	 */
	function get_col( $query = null , $x = 0 ) 
	{
		if ($this->ignore_query($query)) return parent::get_col($query, $x);
		
		$this->init_cache();
		
		$key = md5('get_col(' . $query . ',' . $x . ')');
		$result = null;
		
		$cache = $this->get_cache_item($key);
		if ($cache) {
			$result = $cache['result'];
			$this->last_result = $cache['last_result'];
			$this->num_rows = $cache['num_rows'];
		}
		else {
			$result = parent::get_col($query, $x);
			$cache = array('result'=>$result,'last_result'=>$this->last_result,'num_rows'=>$this->num_rows);
			$this->set_cache_item($key, $cache);
		}
		
		return $result;
	}
	
	/**
	 * @see trmdb::query()
	 */
	function query( $query ) 
	{
		// write operations may need to invalidate the cache
		if ( preg_match( '/^\s*(create|alter|truncate|drop|insert|delete|update|replace)\s/i', $query ) ) {
			
			// do not reset the cache if this query is on the whitelist
			$invalidate_cache = true;
			
			foreach (self::$CACHE_WRITE_WHITELIST as $item) {
				if (strpos($query, $item) !== false) {
					$invalidate_cache = false;
					break;
				}
			}

			if ($invalidate_cache) {
				$this->set_cache_info('last_reset_query', $query);
				$this->reset_cache();
			}
		}
			
		return parent::query($query);
	}
}

/**
 * function declared globally so we can call it from a trendr hook
 */
function next_level_cache_save_cache() {
	global $trmdb;
	$trmdb->save_cache_to_database();
}

/**
 * Swap out the original DB driver with the extended version
 * @var trmdb
 */
if ((!$trmdb) || get_class($trmdb) != 'next_level_cache_trmdb') {
	$trmdb = new next_level_cache_trmdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
}
