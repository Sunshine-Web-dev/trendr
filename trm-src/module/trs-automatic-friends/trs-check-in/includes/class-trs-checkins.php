<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    Trs_Checkins
 * @sutrsackage Trs_Checkins/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Trs_Checkins
 * @sutrsackage Trs_Checkins/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Trs_Checkins {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Trs_Checkins_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

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

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_globals();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Trs_Checkins_Loader. Orchestrates the hooks of the plugin.
	 * - Trs_Checkins_I18n. Defines internationalization functionality.
	 * - Trs_Checkins_Admin. Defines all hooks for the admin area.
	 * - Trs_Checkins_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with trendr.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-trs-checkins-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-trs-checkins-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-trs-checkins-admin.php';

		/**
		 * The class responsible for defining the global variable of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-trs-checkins-globals.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-trs-checkins-public.php';

		$this->loader = new Trs_Checkins_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Trs_Checkins_I18n class in order to set the domain and to register the hook
	 * with trendr.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Trs_Checkins_I18n();

		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Trs_Checkins_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( trs_core_admin_hook(), $plugin_admin, 'trschk_add_menu_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'trschk_plugin_settings' );
		$this->loader->add_action( 'trs_setup_admin_bar', $plugin_admin, 'trschk_setup_admin_bar_links', 80 );

		// Ajax call for verifying the API Key.
		$this->loader->add_action( 'trm_ajax_trschk_verify_apikey', $plugin_admin, 'trschk_verify_apikey' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Trs_Checkins_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'trm_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'trm_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'trs_setup_nav', $plugin_public, 'trschk_member_profile_checkin_tab' );
		$this->loader->add_action( 'trs_activity_posted_update', $plugin_public, 'trschk_update_meta_on_post_update', 10, 3 );
		$this->loader->add_action( 'trs_groups_posted_update', $plugin_public, 'trschk_update_group_meta_on_post_update', 10, 4 );
		$this->loader->add_filter( 'groups_activity_new_update_action', $plugin_public, 'trschk_groups_activity_new_update_action', 10, 1 );
		$this->loader->add_action( 'trs_activity_entry_content', $plugin_public, 'trschk_show_google_map_in_checkin_activity', 10 );
		$this->loader->add_action( 'trm_ajax_trschk_save_temp_location', $plugin_public, 'trschk_save_temp_location' );
		$this->loader->add_action( 'trm_ajax_trschk_fetch_places', $plugin_public, 'trschk_fetch_places' );
		$this->loader->add_action( 'trm_ajax_trschk_select_place_to_checkin', $plugin_public, 'trschk_select_place_to_checkin' );
		$this->loader->add_action( 'trm_ajax_trschk_cancel_checkin', $plugin_public, 'trschk_cancel_checkin' );
		/*version 1.0.1 update*/
		$this->loader->add_filter( 'trs_activity_check_activity_types', $plugin_public, 'trschk_add_checkin_activity_type', 10, 1 );
		$this->loader->add_action( 'trs_init', $plugin_public, 'trschk_add_location_xprofile_field' );
		$this->loader->add_action( 'trm_ajax_trschk_save_xprofile_location', $plugin_public, 'trschk_save_xprofile_location' );
		$this->loader->add_filter( 'trs_get_the_profile_field_value', $plugin_public, 'trschk_show_xprofile_location', 10, 3 );
		$this->loader->add_action( 'trs_register_activity_actions', $plugin_public, 'custom_plugin_register_activity_actions' );
		$this->loader->add_action( 'trs_activity_before_save', $plugin_public, 'trschk_update_activity_type_checkins', 10, 1 );
		/*version 1.0.7 update*/
		$this->loader->add_action( 'trs_activity_post_form_options', $plugin_public, 'render_location_pickup_html', 0 );
	}

	/**
	 * Registers a global variable of the plugin - trs-checkins
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function define_globals() {
		global $trs_checkins;
		$trs_checkins = new Trs_Checkins_Globals( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * Run the loader to execute all of the hooks with trendr.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * trendr and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Trs_Checkins_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Function to fetch google places.
	 *
	 * @since     1.0.0
	 * @param     string $apikey     Api key.
	 * @param     string $lat        Latitude.
	 * @param     string $lon        Longitude.
	 * @param     string $radius     Radius.
	 * @return    array  $response   Response array.
	 */
	public static function trschk_fetch_google_places( $apikey, $lat = '', $lon = '', $radius = '' ) {
		$places_url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json';
		$parameters = array(
			'location' => "$lat,$lon",
			'radius'   => $radius,
			'key'      => $apikey,
		);
		$url        = add_query_arg( $parameters, esc_url_raw( $places_url ) );
		$response   = trm_remote_get( esc_url_raw( $url ) );
		return $response;
	}

}
