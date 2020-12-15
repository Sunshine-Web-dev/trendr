<?php
/**
 * NLC Dashboard Widget
 * @version 0.0.4
 * @author: VerySimple http://verysimple.com/
 * @license: GPL
 */

// add the widget hook
add_action('trm_dashboard_setup', array('NLC_Dashboard_Widget','init') );

/**
 * Dashboard widget class for Next Level Cache
 * @author jason
 */
class NLC_Dashboard_Widget {

	/**
	 * The id of this widget.
	 */
	const wid = 'ncl_dashboard_widget';

	/**
	 * Hook to trm_dashboard_setup to add the widget.
	 */
	public static function init() 
	{
		// register the widget settings
		self::update_dashboard_widget_options(
			self::wid,								//The  widget id
			array('example_setting' => 1), 			//Associative array of options & default values
			true									//Add only (will not update existing options)
		);

		// register the widget
		trm_add_dashboard_widget(
			self::wid,								//A unique slug/ID
			__( 'Next Level Cache', 'nlc' ),		//Visible name for the widget
			array('NLC_Dashboard_Widget','widget'),	//Callback for the main widget content
			array('NLC_Dashboard_Widget','config')	//Optional callback for widget configuration content
		);
	}

	/**
	 * Load the widget code
	 */
	public static function widget() 
	{
		global $trmdb;
		$db_driver_class = get_class($trmdb);
		$db_driver_is_loaded = 'next_level_cache_trmdb' == $db_driver_class;
		$driver_version = ($db_driver_is_loaded)
			? next_level_cache_trmdb::$DRIVER_VERSION
			: '';
		
		echo "<div><img style='width: 100%;' alt='Next Level Cache' src='" . NEXT_LEVEL_CACHE_URL_ROOT . "images/logo.gif' /></div>";
		
		if ($driver_version && $driver_version == NEXT_LEVEL_CACHE_EXPECTED_DRIVER_VERSION) {
			$count = $trmdb->get_cache_count();
			$size = $trmdb->get_cache_size_formatted();
			$num_resets_today = $trmdb->get_cache_info('num_resets_today',0);
			$num_prunes_today = $trmdb->get_cache_info('num_prunes_today',0);

			$cache_size_kb = $trmdb->get_cache_size() / 1024;
			$cache_percentage = round( $cache_size_kb / next_level_cache_trmdb::$MAX_CACHE_SIZE * 100, 0);
			
			echo "<p style='background-color: #99CC99; padding: 5px; border-radius: 3px; text-align: center;'>NLC Drop-in version $driver_version is enabled.</p>";
			
			if ($num_prunes_today > next_level_cache_trmdb::$PRUNE_WARNING_LIMIT) {
				echo "<div style='margin: 10px 0px 10px 0px; padding: 25px; background-color: #FFCCCC; border-radius: 5px; text-align: center;'>Warning: The number of prunes is high.</div>";
			}

			echo "<h4>Cache Contents</h4>\n";
			echo "<div>Number of Cached Items: $count</div>";
			echo "<div>Cache Size: ".$size
				. " <div style='display: inline-block; width: 100px;background-color: #ddd; padding: 0px;'><div style='width: "
				. $cache_percentage
				. "px;background-color:#666;height:10px;'></div></div>"
				. "</div>";
			echo "<div>Number of Resets Today: ".$num_resets_today."</div>";
			echo "<div>Number of Prunes Today: ".$num_prunes_today."</div>";
				
		}
		elseif ($driver_version) {
			echo "<p style='background-color: #FFCCCC; padding: 5px; border-radius: 3px;'>WARNING: The DB Drop-in is out of date.  See plugin settings.</p>";
		}
		elseif ($db_driver_class != 'trmdb') {
			echo "<p style='background-color: #FFCCCC; padding: 5px; border-radius: 3px;'>WARNING: A conflicting DB Drop-in is installed.  See plugin settings.</p>";
		}
		else {
			echo "<p style='background-color: #FFCCCC; padding: 5px; border-radius: 3px;'>WARNING: DB Drop-in is not installed. See plugin settings.</p>"; 
		}

	}

	/**
	 * Load widget config code.
	 *
	 * This is what will display when an admin clicks
	 */
	public static function config() 
	{
		echo "<p>NLC configuration options are on the <a href=\"options-general.php?page=nlc/settings.php\">Settings Page</a></p>";
	}

	/**
	 * Gets the options for a widget of the specified name.
	 *
	 * @param string $widget_id Optional. If provided, will only get options for the specified widget.
	 * @return array An associative array containing the widget's options and values. False if no opts.
	 */
	public static function get_dashboard_widget_options( $widget_id='' )
	{
		//Fetch ALL dashboard widget options from the db...
		$opts = get_option( 'dashboard_widget_options' );

		//If no widget is specified, return everything
		if ( empty( $widget_id ) )
			return $opts;

		//If we request a widget and it exists, return it
		if ( isset( $opts[$widget_id] ) )
			return $opts[$widget_id];

		//Something went wrong...
		return false;
	}

	/**
	 * Gets one specific option for the specified widget.
	 * @param $widget_id
	 * @param $option
	 * @param null $default
	 *
	 * @return string
	 */
	public static function get_dashboard_widget_option( $widget_id, $option, $default=NULL ) 
	{

		$opts = self::get_dashboard_widget_options($widget_id);

		//If widget opts dont exist, return false
		if ( ! $opts )
			return false;

		//Otherwise fetch the option or use default
		if ( isset( $opts[$option] ) && ! empty($opts[$option]) )
			return $opts[$option];
		else
			return ( isset($default) ) ? $default : false;

	}

	/**
	 * Saves an array of options for a single dashboard widget to the database.
	 * Can also be used to define default values for a widget.
	 *
	 * @param string $widget_id The name of the widget being updated
	 * @param array $args An associative array of options being saved.
	 * @param bool $add_only If true, options will not be added if widget options already exist
	 */
	public static function update_dashboard_widget_options( $widget_id , $args=array(), $add_only=false )
	{
		//Fetch ALL dashboard widget options from the db...
		$opts = get_option( 'dashboard_widget_options' );

		//Get just our widget's options, or set empty array
		$w_opts = ( isset( $opts[$widget_id] ) ) ? $opts[$widget_id] : array();

		if ( $add_only ) {
			//Flesh out any missing options (existing ones overwrite new ones)
			$opts[$widget_id] = array_merge($args,$w_opts);
		}
		else {
			//Merge new options with existing ones, and add it back to the widgets array
			$opts[$widget_id] = array_merge($w_opts,$args);
		}

		//Save the entire widgets array back to the db
		return update_option('dashboard_widget_options', $opts);
	}

}