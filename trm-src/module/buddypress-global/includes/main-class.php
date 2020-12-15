<?php 
// Exit if accessed directly
if (!defined('ABSPATH'))
	exit;

if (!class_exists('BuddyBoss_Global_Search_Plugin')):

	/**
	 *
	 * Trendr Global Search Plugin Main Controller
	 * **************************************
	 *
	 *
	 */
	class BuddyBoss_Global_Search_Plugin {
		/* Includes
		 * ===================================================================
		 */

		/**
		 * Most WordPress/Trendr plugin have the includes in the function
		 * method that loads them, we like to keep them up here for easier
		 * access.
		 * @var array
		 */
		private $main_includes = array(
			// Core
			'functions',
			'template',
			'filters',
			'class.BBoss_Global_Search_Helper',
			'plugins/search-cpt/index',
		);

		/**
		 * Admin includes
		 * @var array
		 */
		private $admin_includes = array(
			'admin'
		);

		/* Plugin Options
		 * ===================================================================
		 */
		
		/**
		 * This options array is setup during class instantiation, holds
		 * default and saved options for the plugin.
		 *
		 * @var array
		 */
		public $options = array();

		/**
		 * Is Trendr installed and activated?
		 * @var boolean
		 */
		public $trs_enabled = false;

		/* Version
		 * ===================================================================
		 */

		/**
		 * Plugin codebase version
		 * @var string
		 */
		public $version = '1.0.0';

		/* Paths
		 * ===================================================================
		 */
		public $file		= '';
		public $basename	= '';
		public $plugin_dir	= '';
		public $plugin_url	= '';
		public $lang_dir	= '';
		public $assets_dir	= '';
		public $assets_url	= '';

		/* Magic
		 * ===================================================================
		 */

		/**
		 * Trendr Global Search uses many variables, most of which can be filtered to
		 * customize the way that it works. To prevent unauthorized access,
		 * these variables are stored in a private array that is magically
		 * updated using PHP 5.2+ methods. This is to prevent third party
		 * plugins from tampering with essential information indirectly, which
		 * would cause issues later.
		 *
		 * @see BuddyBoss_Global_Search_Plugin::setup_globals()
		 * @var array
		 */
		private $data;

		/* Singleton
		 * ===================================================================
		 */

		/**
		 * Main Trendr Global Search Instance.
		 *
		 * Insures that only one instance of Trendr Global Search exists in memory at any
		 * one time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 *
		 * @static object $instance
		 * @uses BuddyBoss_Global_Search_Plugin::setup_globals() Setup the globals needed.
		 * @uses BuddyBoss_Global_Search_Plugin::setup_actions() Setup the hooks and actions.
		 * @uses BuddyBoss_Global_Search_Plugin::setup_textdomain() Setup the plugin's language file.
		 * @see buddyboss_global_search()
		 *
		 * @return object BuddyBoss_Global_Search_Plugin
		 */
		public static function instance() {
			// Store the instance locally to avoid private static replication
			static $instance = null;
			
			// Only run these methods if they haven't been run previously
			if (null === $instance) {
				$instance = new BuddyBoss_Global_Search_Plugin();
				$instance->setup_globals();
				$instance->setup_actions();
				$instance->setup_textdomain();
			}

			// Always return the instance
			return $instance;
		}

		/* Magic Methods
		 * ===================================================================
		 */

		/**
		 * A dummy constructor to prevent BuddyBoss Global Search from being loaded more than once.
		 *
		 * @since BuddyBoss Global Search (1.0.0)
		 * @see BuddyBoss_Global_Search_Plugin::instance()
		 * @see $trs
		 */
		private function __construct() { /* Do nothing here */
		}

		/**
		 * A dummy magic method to prevent Trendr Global Search from being cloned.
		 *
		 * @since 1.0.0
		 */
		public function __clone() {
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'trendr-global-search'), '1.7');
		}

		/**
		 * A dummy magic method to prevent Trendr Global Search from being unserialized.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'trendr-global-search'), '1.7');
		}

		/**
		 * Magic method for checking the existence of a certain custom field.
		 *
		 * @since 1.0.0
		 */
		public function __isset($key) {
			return isset($this->data[$key]);
		}

		/**
		 * Magic method for getting Trendr Global Search varibles.
		 *
		 * @since 1.0.0
		 */
		public function __get($key) {
			return isset($this->data[$key]) ? $this->data[$key] : null;
		}

		/**
		 * Magic method for setting Trendr Global Search varibles.
		 *
		 * @since 1.0.0
		 */
		public function __set($key, $value) {
			$this->data[$key] = $value;
		}

		/**
		 * Magic method for unsetting Trendr Global Search variables.
		 *
		 * @since 1.0.0
		 */
		public function __unset($key) {
			if (isset($this->data[$key]))
				unset($this->data[$key]);
		}

		/**
		 * Magic method to prevent notices and errors from invalid method calls.
		 *
		 * @since 1.0.0
		 */
		public function __call($name = '', $args = array()) {
			unset($name, $args);
			return null;
		}

		/* Plugin Specific, Setup Globals, Actions, Includes
		 * ===================================================================
		 */


		/**
		 * Setup Trendr Global Search plugin global variables.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @uses plugin_dir_path() To generate Trendr Global Search plugin path.
		 * @uses plugin_dir_url() To generate Trendr Global Search plugin url.
		 * @uses apply_filters() Calls various filters.
		 */
		private function setup_globals() {
			// DEFAULT CONFIGURATION OPTIONS
			$default_options = $this->default_options;

			$saved_options = get_option('buddyboss_global_search_plugin_options');
			$saved_options = maybe_unserialize($saved_options);

			$this->options = trm_parse_args($saved_options, $default_options);

			/** Versions ************************************************* */
			$this->version = BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_VERSION;

			/** Paths***************************************************** */
			// BuddyBoss Global Search root directory
			$this->file = BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_FILE;
			$this->basename = plugin_basename($this->file);
			$this->plugin_dir = BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR;
			$this->plugin_url = BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_URL;

			// Languages
			$this->lang_dir = dirname($this->basename) . '/languages/';

			// Includes
			$this->includes_dir = $this->plugin_dir . 'includes';
			$this->includes_url = $this->plugin_url . 'includes';

			// Templates
			$this->templates_dir = $this->plugin_dir . 'templates';
			$this->templates_url = $this->plugin_url . 'templates';

			// Assets
			$this->assets_dir = $this->plugin_dir . 'assets';
			$this->assets_url = $this->plugin_url . 'assets';
		}

		/**
		 * Set up the default hooks and actions.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @uses add_action() To add various actions.
		 */
		private function setup_actions() {
			// Admin
			if (( is_admin() || is_network_admin() ) && current_user_can('manage_options')) {
				$this->load_admin();
			}
			
			if( is_multisite() ){
				add_action('init', array($this, 'trs_loaded'));
			} else {
				// Hook into Trendr init
				add_action('trs_loaded', array($this, 'trs_loaded'));
			}
		}

		/**
		 * Load plugin text domain
		 *
		 * @since 1.0.0
		 *
		 * @uses sprintf() Format .mo file
		 * @uses get_locale() Get language
		 * @uses file_exists() Check for language file
		 * @uses load_textdomain() Load language file
		 */
		public function setup_textdomain() {
			$domain = 'trendr-global-search';
			$locale = apply_filters('plugin_locale', get_locale(), $domain);

			//first try to load from trm-contents/languages/plugins/ directory
			load_textdomain($domain, TRM_LANG_DIR . '/plugins/' . $domain . '-' . $locale . '.mo');

			//if not found, then load from buddyboss-global-search/languages/ directory
			load_plugin_textdomain('trendr-global-search', false, $this->lang_dir);
		}

		/**
		 * We require Trendr to run the main components, so we attach
		 * to the 'trs_loaded' action which Trendr calls after it's started
		 * up. This ensures any Trendr related code is only loaded
		 * when Trendr is active.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trs_loaded() {
			global $trs;

			$this->trs_enabled = true;

			$this->load_main();
		}

		/* Load
		 * ===================================================================
		 */

		/**
		 * Include required admin files.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @uses $this->do_includes() Loads array of files in the include folder
		 */
		private function load_admin() {
			$this->do_includes($this->admin_includes);

			$this->admin = BuddyBoss_Global_Search_Admin::instance();
		}

		/**
		 * Include required files.
		 *
		 * @since 1.0.0
		 * @access private
		 *
		 * @uses BuddyBoss_Global_Search_Plugin::do_includes() Loads array of files in the include folder
		 */
		private function load_main() {		
			$this->do_includes($this->main_includes);
			
			$this->search = BBoss_Global_Search_Helper::instance();
			
			// Front End Assets
			if ( ! is_admin() && ! is_network_admin() ){
				add_action( 'trm_enqueue_scripts', array( $this, 'assets' ) );
			}
                        
                    // Remove trs compose message deprecated autocomplete
                       remove_action("trs_enqueue_scripts","messages_add_autocomplete_js");
                    //   remove_action("trm_head","messages_add_autocomplete_css");
		}
		
		/**
		 * Load css/js files
		 * 
		 * @since 1.0.0
		 * @return void
		 */
		public function assets(){
			trm_enqueue_style( 'jquery-ui', $this->assets_url . '/css/jquery-ui.min.css', '1.11.2' );
			//trm_enqueue_style( 'trendr-global-search', $this->assets_url . '/css/trendr-global-search.css', '1.0.0' );
			trm_enqueue_style( 'trendr-global-search', $this->assets_url . '/css/trendr-global-search.min.css', '1.0.0' );
			
			//trm_enqueue_script( 'jquery-js',$this->assets_url . '/js/jquery.js');
			trm_enqueue_script( 'jquery-ai-core',$this->assets_url . '/js/jquery.ui.core.min.js');
			trm_enqueue_script( 'jquery-ai-widget',$this->assets_url . '/js/jquery.ui.widget.min.js');
			//trm_enqueue_script( 'jquery-ai-position',$this->assets_url . '/js/jquery.ui.position.min.js');
			trm_enqueue_script( 'jquery-ai-menu',$this->assets_url . '/js/jquery.ui.menu.min.js');
			trm_enqueue_script( 'jquery-ui-autocomplete',$this->assets_url . '/js/jquery.ui.autocomplete.min.js');
			//trm_enqueue_script( 'trendr-global-search', $this->assets_url . '/js/trendr-global-search.js', array( 'jquery', 'jquery-ui-autocomplete' ), '1.0.0' );
			trm_enqueue_script( 'trendr-global-search-js', $this->assets_url . '/js/trendr-global-search.js' );
			
			if(function_exists("trs_is_messages_component")) {
				// Include the autocomplete JS for composing a message.
				if ( trs_is_messages_component() && trs_is_current_action( 'compose' ) ) {
					add_action( 'trm_head', array($this,'messages_autocomplete_init_jsblock') );
				}
			}
                        
			$data = array(
				'nonce'		=> trm_create_nonce( 'bboss_global_search_ajax' ),
				'action'	=> 'bboss_global_search_ajax',
				'debug'		=> true,//set it to false on production
				'search_url'    => home_url( '/' ),
				'loading_msg'    => __("Loading Suggestions","trendr-global-search")
			);
                        
                        if(isset($_GET["s"])) {
                            $data["search_term"] = $_GET["s"];
                        }
			echo '<script> var BBOSS_GLOBAL_SEARCH = ' . json_encode($data) . '</script>';	
			trm_localize_script( 'trendr-global-search', 'BBOSS_GLOBAL_SEARCH', $data );
                }
                
                /* Print inline JS for initializing the trs messages autocomplete.
                 * Proper updated auto complete code for trendr message compose (replacing autocompletefb script).
                 * @todo : Why this inline code is not at proper file.
                 */
                public function messages_autocomplete_init_jsblock() {
                    ?>
                    
                    <script type="text/javascript">
                            window.user_profiles = Array();
                                jQuery(document).ready(function() {
                                       jQuery(".send-to-input").autocomplete({
                                               source: function(request, response) {
                                                       jQuery("body").data("ac-item-p","even");
                                                       var term = request.term;
                                                       if (term in window.user_profiles) {
                                                               response(window.user_profiles[term]);
                                                               return;
                                                       }
                                                       var data = {
                                                               'action': 'messages_autocomplete_results',
                                                               'search_term': request.term
                                                       };
                                                       $.ajax({
                                                               url: ajaxurl + '?q=' + request.term + '&limit=10',
                                                               data: data,
                                                               success: function(data) {
                                                                       var new_data = Array();
                                                                       d = data.split("\n");
                                                                       jQuery.each(d, function(i, item) {
                                                                               new_data[new_data.length] = item;
                                                                       });
                                                                       if (data != "") { 
                                                                            response(new_data);
                                                                       }
                                                               }
                                                       });
                                               },
                                               minLength: 1,
                                               select: function(event, ui) {
                                                       sel_item = ui.item;
                                                       var d = String(sel_item.label).split(' (');
                                                       var un = d[1].substr(0, d[1].length - 1);
                                                       //check if it already exists;
                                                       if (0 === jQuery('.acfb-holder').find('#un-' + un).length) {
                                                               var ln = '#link-' + un;
                                                               var l = jQuery(ln).attr('href');
                                                               var v = '<li class="selected-user friend-tab" id="un-' + un + '"><span><a href="' + l + '">' + d[0] + '</a></span> <span class="p">X</span></li>';
                                                               if (jQuery(".acfb-holder").find(".friend-tab").length == 0) {
                                                                       var x = jQuery('.acfb-holder').prepend(v);
                                                               } else {
                                                                       var x = jQuery('.acfb-holder').find(".friend-tab").last().after(v);
                                                               }
                                                               jQuery('#send-to-usernames').addClass(un);
                                                       }
                                                       return false;
                                               },
                                               focus: function(event, ui) {
                                                       $(".ui-autocomplete li").removeClass("ui-state-hover");
                                                       $(".ui-autocomplete").find("li:has(a.ui-state-focus)").addClass("ui-state-hover");
                                                       return false;
                                               }
                                       }).data("ui-autocomplete")._renderItem = function(ul, item) {
                                               ul.addClass("ac_results");
                                               if (jQuery("body").data("ac-item-p") == "even"){
                                                    c = "ac_event";
                                                    jQuery("body").data("ac-item-p","odd");
                                                } else {
                                                    c = "ac_odd";
                                                    jQuery("body").data("ac-item-p","even");
                                                }
                                               return $("<li class='"+c+"'>").append("<a>" + item.label + "</a>").appendTo(ul);
                                       };
                                       jQuery(document).on("click", ".selected-user", function() {
                                               jQuery(this).remove();
                                       });
                                       jQuery('#send_message_form').submit(function() {
                                               tosend = Array();
                                               jQuery(".acfb-holder").find(".friend-tab").each(function(i, item) {
                                                       un = $(this).attr("id");
                                                       un = un.replace('un-', '');
                                                       tosend[tosend.length] = un;
                                               });
                                               document.getElementById('send-to-usernames').value = tosend.join(" ");
                                       });
                                });
                    </script>
                    
                    <?php
                }
		
		/* Utility functions
		 * ===================================================================
		 */

		/**
		 * Include required array of files in the includes directory
		 *
		 * @since 1.0.0
		 *
		 * @uses require_once() Loads include file
		 */
		public function do_includes($includes = array()) {
			foreach ((array) $includes as $include) {
				require_once( $this->includes_dir . '/' . $include . '.php' );
			}
		}

		/**
		 * Convenience function to access plugin options, returns false by default
		 *
		 * @since  1.0.0
		 *
		 * @param  string $key Option key

		 * @uses apply_filters() Filters option values with 'buddyboss_global_search_option' &
		 *                       'buddyboss_global_search_option_{$option_name}'
		 * @uses sprintf() Sanitizes option specific filter
		 *
		 * @return mixed Option value (false if none/default)
		 *
		 */
		public function option($key) {
			$key = strtolower($key);
			$option = isset($this->options[$key]) ? $this->options[$key] : null;

			// Apply filters on options as they're called for maximum
			// flexibility. Options are are also run through a filter on
			// class instatiation/load.
			// ------------------------
			// This filter is run for every option
			$option = apply_filters('buddyboss_global_search_option', $option);

			// Option specific filter name is converted to lowercase
			$filter_name = sprintf('buddyboss_global_search_option_%s', strtolower($key));
			$option = apply_filters($filter_name, $option);

			return $option;
		}

	}

// End class BuddyBoss_Global_Search_Plugin

endif;
?>