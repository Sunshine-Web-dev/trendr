<?php
/*
Plugin Name: TRS API
Plugin URI: https://github.com/modemlooper/trs-api
Description: json API for BuddyPress. This plugin creates json api endpoints for https://github.com/TRM-API
Author: modemlooper, djpaul
Version: 0.1
Author URI: https://github.com/modemlooper/trs-api
*/


// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'BuddyPress_API' ) ) :
	
	/**
	 * Main BuddyPress API Class
	 */
	class BuddyPress_API {

		/**
		 * Main BuddyPress API Instance.
		 */
		public static function instance() {

			// Store the instance locally to avoid private static replication
			static $instance = null;

			// Only run these methods if they haven't been run previously
			if ( null === $instance ) {
				$instance = new BuddyPress_API;
				$instance->constants();
				$instance->actions();
			}

			// Always return the instance
			return $instance;

		}


		/**
		 * A dummy constructor to prevent BuddyPress API from being loaded more than once.
		 *
		 */
		private function __construct() { /* Do nothing here */ }


		/**
		 * Bootstrap constants.
		 *
		 */
		private function constants() {

			// define api endpint prefix
			if ( ! defined( 'TRS_API_SLUG' ) ) {
				define( 'TRS_API_SLUG', 'trs' );
			}

			// Define a constant that can be checked to see if the component is installed or not.
			if ( ! defined( 'TRS_API_IS_INSTALLED' ) ) {
				define( 'TRS_API_IS_INSTALLED', 1 );
			}

			// Define a constant that will hold the current version number of the component
			// This can be useful if you need to run update scripts or do compatibility checks in the future
			if ( ! defined( 'TRS_API_VERSION' ) ) {
				define( 'TRS_API_VERSION', '0.1' );
			}

			// Define a constant that we can use to construct file paths and url
			if ( ! defined( 'TRS_API_PLUGIN_DIR' ) ) {
				define( 'TRS_API_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
			}

			if ( ! defined( 'TRS_API_PLUGIN_URL' ) ) {
				$plugin_url = plugin_dir_url( __FILE__ );

				// If we're using https, update the protocol. Workaround for TRM13941, TRM15928, TRM19037.
				if ( is_ssl() )
					$plugin_url = str_replace( 'http://', 'https://', $plugin_url );

				define( 'TRS_API_PLUGIN_URL', $plugin_url );
			}

		}


		/**
		 * TRS-API Actions
		 * 
		 * Includes actions for init, activation/deactivation hooks, and admin notices.
		 *
		 */
		private function actions() {

			register_activation_hook( __FILE__, array( $this, 'trs_api_activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'trs_api_deactivate' ) );


			add_action( 'plugins_loaded', array( $this, 'check_if_exists' ), 9999 );
			add_action( 'trs_include', array( $this, 'trs_api_include' ) );
		}
		
		

		/**
		 * check_if_exists function.
		 *
		 * checks for plugin dependency and deactivates if not found
		 * 
		 * @access public
		 * @return void
		 */
		public function check_if_exists() {
		
			// is BuddyPress plugin active? If not, throw a notice and deactivate
			if ( !class_exists( 'BuddyPress' ) ) {
				add_action( 'all_admin_notices', array( $this, 'trs_api_trendr_required' ) );
				return;
			}

			// is JSON API plugin active? If not, throw a notice and deactivate
			if ( !class_exists('TRM_REST_Server') ) {
				add_action( 'all_admin_notices', array( $this, 'trs_api_trm_api_required' ) );
				return;
			}
			
		}


		/**
		 * trs_api_init function.
		 * 
		 * much files, so include
		 *
		 * @access public
		 * @return void
		 */
		public function trs_api_include() {

			// requires TRS 2.0 or greater.
			if ( version_compare( TRS_VERSION, '2.0', '>' ) ) {
				include_once( dirname( __FILE__ ) . '/endpoints/trs-api-core.php' );
				include_once( dirname( __FILE__ ) . '/endpoints/trs-api-activity.php' );
				include_once( dirname( __FILE__ ) . '/endpoints/trs-api-xprofile.php' );
			}

			add_action( 'rest_api_init', array( $this, 'trs_api_init' ), 0 );
		}


		/**
		 * trs_api_activate function.
		 *
		 * @access public
		 * @return void
		 */
		public function trs_api_activate() {
		}


		/**
		 * trs_api_deactivate function.
		 *
		 * @access public
		 * @return void
		 */
		public function trs_api_deactivate() {
		}


		/**
		 * trs_api_trendr_required function.
		 *
		 * @access public
		 * @return void
		 */
		public function trs_api_trendr_required() {
			echo '<div id="message" class="error"><p>'. sprintf( __( '%1$s requires the <a href="https://trendr.org/">BuddyPress plugin</a> to be installed/activated. %1$s has been deactivated.', 'trs-api' ), 'BuddyPress API' ) .'</p></div>';
			deactivate_plugins( plugin_basename( __FILE__ ), true );
		}


		/**
		 * trs_api_trm_api_required function.
		 *
		 * @access public
		 * @return void
		 */
		public function trs_api_trm_api_required() {
			echo '<div id="message" class="error"><p>'. sprintf( __( '%1$s requires the <a href="https://github.com/TRM-API/TRM-API/releases/tag/2.0-beta3">TRM API V2 plugin</a> to be installed/activated. %1$s has been deactivated.', 'trs-api' ), 'BuddyPress API' ) .'</p></div>';
			deactivate_plugins( plugin_basename( __FILE__ ), true );
		}



		/**
		 * create_trs_endpoints function.
		 *
		 * adds BuddyPress data endpoints to TRM-API
		 * 
		 * @access public
		 * @return void
		 */
		public function trs_api_init() {

			/*
			* TRS Core
			*/
			$trs_api_core = new TRS_API_Core;
			$trs_api_core->register_routes();


			/*
			* TRS Activity
			*/
			if ( trs_is_active( 'activity' ) ) {
				$trs_api_activity = new TRS_API_Activity;
				$trs_api_activity->register_routes();
			}

			/*
			* TRS xProfile
			*/
			if ( trs_is_active( 'xprofile' ) ) {
				$trs_api_xprofile = new TRS_API_xProfile;
				register_rest_route( TRS_API_SLUG, '/xprofile', array(
					'methods'         => 'GET',
					'callback'        => array( $trs_api_xprofile, 'get_items' ),
				) );
				register_rest_route( TRS_API_SLUG, '/xprofile/(?P<id>\d+)', array(
					'methods'         => 'GET',
					'callback'        => array( $trs_api_xprofile, 'get_item' ),
				) );
			}


		}


	}

endif;

function trs_api() {
	return BuddyPress_API::instance();
}
trs_api();