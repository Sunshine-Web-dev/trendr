<?php
/**
 * The file that defines the global variable of the plugin
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    Trs_Checkins
 * @sutrsackage Trs_Checkins/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Trs_Checkins
 * @sutrsackage Trs_Checkins/admin
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Trs_Checkins_Globals {
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The google places API Key.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $apikey
	 */
	public $apikey;

	/**
	 * The user is allowed to checkin by 2 options : autocomplete or by placetype.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $checkin_by
	 */
	public $checkin_by;

	/**
	 * The variable that defines the range of the place types, default = 5kms.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $google_places_range
	 */
	public $google_places_range;

	/**
	 * The variable that stores all the place types to be fetched during checkin
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $place_types
	 */
	public $place_types;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'trs-checkins';
		$this->version     = '1.0.0';
		$this->setup_plugin_global();
	}

	/**
	 * Include the following files that make up the plugin:
	 *
	 * - Trs_Checkins_Globals.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function setup_plugin_global() {
		global $trs_checkins;
		$trschk_settings = trs_get_option( 'trschk_general_settings' );

		$this->apikey = '';
		if ( isset( $trschk_settings['apikey'] ) ) {
			$this->apikey = $trschk_settings['apikey'];
		}

		$this->checkin_by = 'autocomplete';
		if ( isset( $trschk_settings['checkin_by'] ) ) {
			$this->checkin_by = $trschk_settings['checkin_by'];
		}

		$this->google_places_range = 5;
		if ( isset( $trschk_settings['range'] ) ) {
			$this->google_places_range = $trschk_settings['range'];
		}

		$this->place_types = array();
		if ( isset( $trschk_settings['placetypes'] ) ) {
			$this->place_types = $trschk_settings['placetypes'];
		}
	}
}
