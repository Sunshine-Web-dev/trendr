<?php
/*
Plugin Name: Next Level Cache
Plugin URI: http://verysimple.com.com/products/nlc
Description: Next Level Cache is all up in your grill, caching your DB queries.
Version: 0.0.9
Author: VerySimple
Author URI: http://verysimple.com/
License: GPL
*/

DEFINE('NEXT_LEVEL_CACHE_VERSION','0.0.9');
DEFINE('NEXT_LEVEL_CACHE_EXPECTED_DRIVER_VERSION','0.0.9');
DEFINE('NEXT_LEVEL_CACHE_URL_ROOT',plugins_url().'/'.str_replace('.php', '/', basename(__FILE__)) );
DEFINE('NEXT_LEVEL_DRIVER_PATH', str_replace('plugins/next-level-cache/','db.php',plugin_dir_path( __FILE__ )));

include_once(plugin_dir_path(__FILE__).'settings.php');
//include_once(plugin_dir_path(__FILE__).'widget.php');

add_filter("plugin_action_links_".plugin_basename( __FILE__ ), 'next_level_settings_link' );

// display debug info in the footer only if specified in trm-config
if (defined('DEBUGQUERIES') && DEBUGQUERIES && defined('SAVEQUERIES') && SAVEQUERIES) {
	add_action('trm_footer', 'next_level_output_debug_sql', 1000);
}
if (defined('DEBUGOPTIONS') && DEBUGOPTIONS && defined('SAVEQUERIES') && SAVEQUERIES) {
	add_action('trm_footer', 'next_level_output_missing_options', 1001);
}

/**
 * output debug information below the footer if specified
 */
function next_level_output_debug_sql()
{
	if (current_user_can( 'manage_options' ) && is_admin() == false) {
		global $trmdb;

		echo "<style>
			th.sql-debug {font-family: courier new, courier; background-color: #eeffee; color: #333;}
			td.sql-debug {font-family: courier new, courier; background-color: #eeffee; color: #333;}
			h2.sql-debug {margin-top: 50px; padding: 12px; font-weight: bold; background-color: #ccffcc; color: #333;}
			</style>\n";
		echo "<h2 class='sql-debug'>SQL QUERY DEBUGING:</h2>";
		echo "<table>";
		echo "<tr>\n";
		echo "<th class='sql-debug'>#</th>\n";
		echo "<th class='sql-debug'>Query</th>\n";
		echo "<th class='sql-debug'>Exec. Time</th>\n";
		echo "<th class='sql-debug'>Source</th>\n";
		echo "</tr>\n";
		
		$count = 1;
		foreach ($trmdb->queries as $query) {
			echo "<tr>\n";
			echo "<td class='sql-debug'>". $count++ ."</td>\n";
			echo "<td class='sql-debug'>". htmlentities($query[0]) ."</td>\n";
			echo "<td class='sql-debug'>". htmlentities($query[1]) ."</td>\n";
			echo "<td class='sql-debug'>". str_replace(",", "<br>", htmlentities($query[2])) ."</td>\n";
			echo "</tr>\n";
		}
		echo "</table>";
	}
}

/**
 * output trendr options that are missing from the trm_options table
 */
function next_level_output_missing_options()
{
	if (current_user_can( 'manage_options' ) && is_admin() == false) {

		global $trmdb;
		echo "<h2 style='background-color: #ffcccc; padding: 12px; color: #333; font-weight: bold;'>OPTIONS QUERY MISSES:</h2>";
		echo "<pre style='background-color: #ffeeee'>";
		$count = 0;
		foreach ($trmdb->queries as $query) {
			list($sql,$time,$source) = $query;
		
			if (strpos($sql, 'SELECT option_value FROM trm_options WHERE option_name') !== false) {
				$count++;
				echo "-- " . $sql . "\n";
				echo str_replace(
						array('SELECT option_value FROM trm_options WHERE option_name = ',' LIMIT 1'),
						array('insert into trm_options (option_name,option_value,autoload) values(',",'','yes');"),
						$sql) . "\n";
			}
		
		}
		
		if ($count > 0) {
			echo "-- run the above insert statements on your database to reduce the number of options queries:\n";
		}
		else {
			echo "no options query misses.  no action is required.";
		}
		
		echo "</pre>";
	}
}
