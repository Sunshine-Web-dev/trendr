<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    Trs_Checkins
 * @sutrsackage Trs_Checkins/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Trs_Checkins
 * @sutrsackage Trs_Checkins/public
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Trs_Checkins_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $trs_checkins    The plugin settings.
	 */
	public $trs_checkins;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		global $trs_checkins;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->trs_checkins = &$trs_checkins;
	}

	/**
	 * Render location pickup html to trendr what's new section.
	 *
	 * @since    1.0.7
	 */
	public function render_location_pickup_html() {
		global $trs_checkins;
		global $allowedposttags;
		$checkin_html = '';
		if ( is_user_logged_in() ) {
		//BEGIN MODIFICATION -- REMOVED GEO LOCATION FROM PROFILE PAGE AND EXPLORE PAGE-- --  TO WORK ONLY ON TEST PAGE 6-4-18

		//			if (  is_user_logged_in()&& trs_is_blog_page('test')  ) {
//END MODIFICATION -- REMOVED GEO LOCATION FROM PROFILE PAGE AND EXPLORE PAGE-- --  TO WORK ONLY ON TEST PAGE 6-4-18

			// Create the checkin html.
			if ( $trs_checkins->apikey ) {
					$checkin_html .= '<div class="trschk-marker-container"><span class="trschk-allow-checkin"><i class="fa fa-map-marker" aria-hidden="true"></i></span></div>';
					$checkin_html .= '<div class="trs-checkins trs-checkin-panel">';
				if ( 'autocomplete' === $trs_checkins->checkin_by ) {
					$checkin_html     .= '<div class="checkin-by-autocomplete">';
						$checkin_html .= '<input type="text" id="trschk-autocomplete-place" placeholder="' . __( 'Start typing your location...', 'trs-checkins' ) . '" />';
						$checkin_html .= '<input type="hidden" id="trschk-checkin-place-lat" />';
						$checkin_html .= '<input type="hidden" id="trschk-checkin-place-lng" />';
						$checkin_html .= '<input type="checkbox" id="trschk-add-as-place" checked />';
						$checkin_html .= '<label for="trschk-add-as-place">' . __( 'Add as my location', 'trs-checkins' ) . '</label>';
						$checkin_html .= '<span class="trschk-place-loader">' . __( 'Saving location...', 'trs-checkins' ) . '<i class="fa fa-refresh fa-spin"></i></span><span class="clear"></span>';
					$checkin_html     .= '</div>';
					$checkin_html     .= '<div class="checkin-by-autocomplete-map" id="checkin-by-autocomplete-map"></div>';
					$checkin_html     .= '<div class="clear"></div>';
				} else {
					$checkin_html     .= '<div class="checkin-by-placetype">';
						$checkin_html .= '<p>' . __( 'Please Wait..', 'trs-checkins' ) . '</p>';
					$checkin_html     .= '</div>';
				}
					$checkin_html .= '</div>';
			}
		}
		echo $checkin_html;
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	//public function enqueue_styles() {
		//BEGIN MODIFICATION -- RECODED TO WORK ONLY ON TEST PAGE 6-1-18

		//if ( trs_is_groups_component() || trs_is_activity_component() || trs_is_profile_component() || strpos( filter_input( INPUT_SERVER, 'REQUEST_URI' ), 'checkin' ) ) {
		//	if (  is_user_logged_in()&& trs_is_blog_page('test')  || strpos( filter_input( INPUT_SERVER, 'REQUEST_URI' ), 'checkin' ) ) {
		//END MODIFICATION -- RECODED TO WORK ONLY ON TEST PAGE


		//	trm_enqueue_style( $this->plugin_name . '-ui-css', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css' );
			//trm_enqueue_style( $this->plugin_name . '-font-awesome', plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css' );
		//	trm_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/trs-checkins-public.css', $this->version, 'all' );
		//}
	//}

	/**trs_is_active( 'posts' ) 
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		//BEGIN MODIFICATION -- RECODED TO WORK ONLY ON TEST PAGE 6-1-18
	if ( trs_is_groups_component() || trs_is_activity_component() || trs_is_profile_component() || strpos( filter_input( INPUT_SERVER, 'REQUEST_URI' ), 'checkin' ) ) {
	//			if (  is_user_logged_in()&& trs_is_blog_page('test') || trs_is_profile_component() || strpos( filter_input( INPUT_SERVER, 'REQUEST_URI' ), 'checkin' ) ) {
		//END MODIFICATION -- RECODED TO WORK ONLY ON TEST PAGE


			global $trs_checkins;
			trm_enqueue_script( 'jquery-ui-accordion' );
			trm_enqueue_script( $this->plugin_name . '-google-places-api', 'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=' . $trs_checkins->apikey, array( 'jquery' ) );
			trm_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/trs-checkins-public.js', $this->version, false );
			if ( is_user_logged_in() ) {
				if ( xprofile_get_field_id_from_name( 'Location' ) ) {
					$trschk_location_id = xprofile_get_field_id_from_name( 'Location' );
					$trschk_loc_xprof   = 'field_' . $trschk_location_id;
				}
			}
			if ( empty( $trschk_loc_xprof ) ) {
				$trschk_loc_xprof = '';
			}
			trm_localize_script(
				$this->plugin_name, 'trschk_public_js_obj', array(
					'ajaxurl'         => admin_url( 'admin-ajax.php' ),
					'checkin_by'      => $trs_checkins->checkin_by,
					'trschk_loc_xprof' => $trschk_loc_xprof,
				)
			);
		}
	}

	/**
	 * Register a new tab in member's profile - Checkin
	 *
	 * @since    1.0.1
	 */
	public function trschk_member_profile_checkin_tab() {
		$displayed_uid  = trs_displayed_user_id();
		$parent_slug    = 'checkin';
		$my_places_link = trs_core_get_userlink( $displayed_uid, false, true ) . $parent_slug . '/my-places';

		trs_core_new_nav_item(
			array(
				'name'                    => __( 'Check-ins', 'trs-checkins' ),
				'slug'                    => 'checkin',
				'screen_function'         => array( $this, 'trschk_checkin_tab_function_to_show_screen' ),
				'position'                => 75,
				'default_subnav_slug'     => 'my-places',
				'show_for_displayed_user' => true,
			)
		);
		trs_core_new_subnav_item(
			array(
				'name'            => __( 'Locations', 'trs-checkins' ),
				'slug'            => 'my-places',
				'parent_url'      => trs_core_get_userlink( $displayed_uid, false, true ) . $parent_slug . '/',
				'parent_slug'     => esc_attr( $parent_slug ),
				'screen_function' => array( $this, 'trschk_checkins_activity_show_screen' ),
				'position'        => 100,
				'link'            => $my_places_link,
			)
		);
	}

	/**
	 * Screen function for listing all my places in menu item
	 */
	public function trschk_checkins_activity_show_screen() {
		add_action( 'trs_template_title', array( $this, 'trschk_checkins_tab_function_to_show_title' ) );
		add_action( 'trs_template_content', array( $this, 'trschk_checkins_tab_function_to_show_content' ) );
		trs_core_load_template( apply_filters( 'trs_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * My Places - Title
	 */
	public function trschk_checkins_tab_function_to_show_title() {
		esc_html_e( 'My Locations', 'trs-checkins' );
	}

	/**
	 * My Places - Content
	 */
	public function trschk_checkins_tab_function_to_show_content() {
		$file = TRSCHK_PLUGIN_PATH . 'public/templates/checkin/trs-checkins-activity.php';
		if ( file_exists( $file ) ) {
			include_once $file;
		}
	}

	/**
	 * Return country from json data
	 *
	 * @param    array $jsondata    Google places api data.
	 */
	public static function google_get_country( $jsondata ) {
		return self::trschk_find_long_name_given_type( 'country', $jsondata['results'][0]['address_components'] );
	}

	/**
	 * Return province from json data
	 *
	 * @param    array $jsondata    Google places api data.
	 */
	public static function google_get_province( $jsondata ) {
		return self::trschk_find_long_name_given_type( 'administrative_area_level_1', $jsondata['results'][0]['address_components'], true );
	}

	/**
	 * Return city from json data
	 *
	 * @param    array $jsondata    Google places api data.
	 */
	public static function google_get_city( $jsondata ) {
		return self::trschk_find_long_name_given_type( 'locality', $jsondata['results'][0]['address_components'] );
	}

	/**
	 * Return street from json data
	 *
	 * @param    array $jsondata    Google places api data.
	 */
	public static function google_get_street( $jsondata ) {
		return self::trschk_find_long_name_given_type( 'street_number', $jsondata['results'][0]['address_components'] ) . ' ' . self::trschk_find_long_name_given_type( 'route', $jsondata['results'][0]['address_components'] );
	}

	/**
	 * Return postal code from json data
	 *
	 * @param    array $jsondata    Google places api data.
	 */
	public static function google_get_postalcode( $jsondata ) {
		return self::trschk_find_long_name_given_type( 'postal_code', $jsondata['results'][0]['address_components'] );
	}

	/**
	 * Return country code from json data
	 *
	 * @param    array $jsondata    Google places api data.
	 */
	public static function google_get_country_code( $jsondata ) {
		return self::trschk_find_long_name_given_type( 'country', $jsondata['results'][0]['address_components'], true );
	}

	/**
	 * Return formatted address from json data
	 *
	 * @param    array $jsondata    Google places api data.
	 */
	public static function google_get_address( $jsondata ) {
		return $jsondata['results'][0]['formatted_address'];
	}

	/**
	 * Searching in Google Geo json, return the long name given the type.
	 * (If short_name is true, return short name)
	 *
	 * @param    string  $type  The type of the place.
	 * @param    array   $array    The place type array.
	 * @param    boolean $short_name    Short name exist.
	 */
	public static function trschk_find_long_name_given_type( $type, $array, $short_name = false ) {
		foreach ( $array as $value ) {
			if ( in_array( $type, $value['types'], true ) ) {
				if ( $short_name ) {
					return $value['short_name'];
				}
				return $value['long_name'];
			}
		}
	}

	/**
	 * Ajax served to save the temporary location
	 */
	public function trschk_save_temp_location() {
		if ( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) && 'trschk_save_temp_location' === filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) ) {
			$args = array(
				'place'           => filter_input( INPUT_POST, 'place', FILTER_SANITIZE_STRING ),
				'latitude'        => filter_input( INPUT_POST, 'latitude', FILTER_SANITIZE_STRING ),
				'longitude'       => filter_input( INPUT_POST, 'longitude', FILTER_SANITIZE_STRING ),
				'add_as_my_place' => filter_input( INPUT_POST, 'add_as_my_place', FILTER_SANITIZE_STRING ),
			);
			trs_update_option( 'trschk_temp_location', $args );
			$response = array( 'message' => 'temp-locaition-saved' );
			trm_send_json_success( $response );
			die;
		}
	}

	/**
	 * Add location xprofile field.
	 *
	 * @since 1.0.1
	 */
	public function trschk_add_location_xprofile_field() {
		if ( xprofile_get_field_id_from_name( 'Location' ) ) {
			return;
		}
		$location_list_args = array(
			'field_group_id' => 1,
			'type'           => 'textbox',
			'name'           => 'Location',
			'description'    => 'Please select your location',
			'is_required'    => false,
			'can_delete'     => true,
			'order_by'       => 'default',
		);
		xprofile_insert_field( $location_list_args );
	}

	/**
	 * Ajax request to save location xprofile field.
	 *
	 * @since 1.0.1
	 */
	public function trschk_save_xprofile_location() {
		if ( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) && 'trschk_save_xprofile_location' === filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) ) {
			$args = array(
				'place'     => filter_input( INPUT_POST, 'place', FILTER_SANITIZE_STRING ),
				'latitude'  => filter_input( INPUT_POST, 'latitude', FILTER_SANITIZE_STRING ),
				'longitude' => filter_input( INPUT_POST, 'longitude', FILTER_SANITIZE_STRING ),
			);
			if ( xprofile_get_field_id_from_name( 'Location' ) ) {
				$trschk_location_id = xprofile_get_field_id_from_name( 'Location' );
				trs_xprofile_update_meta( get_current_user_id(),$trschk_location_id, 'data', 'location', $args );
			}
		}
		exit;
	}

	/**
	 * Function to filter location xprofile field value at profile page.
	 *
	 * @since 1.0.1
	 * @param string $field_value Value for the profile field.
	 * @param string $field_type  Type for the profile field.
	 * @param int    $field_id    ID for the profile field.
	 */
	public function trschk_show_xprofile_location( $field_value, $field_type, $field_id ) {
		if ( xprofile_get_field_id_from_name( 'Location' ) ) {
			$trschk_location_id = xprofile_get_field_id_from_name( 'Location' );
			if ( $field_id === $trschk_location_id ) {
				$loc_xprof_meta = trs_xprofile_get_meta( get_current_user_id(),$trschk_location_id, 'data', 'location' );
				if ( ! empty( $loc_xprof_meta ) && is_array( $loc_xprof_meta ) ) {

					$field_value = '<a class=checkin-loc href="http://maps.google.com/maps/place/' . $loc_xprof_meta['place'] . '/@' . $loc_xprof_meta['latitude'] . ',' . $loc_xprof_meta['longitude'] . '" target="_blank" title="' . $loc_xprof_meta['place'] . '">' . $loc_xprof_meta['place'] . '</a>';
					return $field_value;
				}
			}
			return $field_value;
		} else {
			return $field_value;
		}
	}
//Begin Modification Removed type geo locate 7-11-18
	/**
	 * Function to add checkin activity types.
	 *
	 * @since 1.0.1
	 * @param array $types Value for the profile field.
	 */
	public function trschk_add_checkin_activity_type( $types ) {
		$types[] = 'geo_locate';
		return $types;
	}

	/**
	 * Function to register activity action.
	 *
	 * @since 1.0.1
	 */
	public function custom_plugin_register_activity_actions() {

		$component_id = $trs->activity->id;

		trs_activity_set_action(
			$component_id, 'geo_locate', __( 'Check-ins Update', 'trs-checkins' ), array( $this, 'trs_activity_format_activity_action_geo_locate' ), __( 'Check-ins', 'trs-checkins' ), array( 'member' )
		);
	}

	/**
	 * Format 'activity_update' activity actions.
	 *
	 * @since 1.0.1
	 *
	 * @param string $action   Static activity action.
	 * @param object $activity Activity data object.
	 * @return string $action
	 */
	public function trs_activity_format_activity_action_geo_locate( $action, $activity ) {
		$action = sprintf( __( '%s checked-in', 'trendr' ), trs_core_get_userlink( $activity->user_id ) );

		/**
		 * Filters the formatted activity action update string.
		 *
		 * @since 1.2.0
		 *
		 * @param string               $action   Activity action string value.
		 * @param TRS_Activity_Activity $activity Activity item object.
		 */
		return apply_filters( 'trs_activity_new_checkin_action', $action, $activity );
	}

	/**
	 * Function to set activity type geo_locate.
	 *
	 * @since 1.0.1
	 * @param array $activity_object Activity object.
	 */
	//public function trschk_update_activity_type_checkins( $activity_object ) {

	//	$trschk_temp_location = trs_get_option( 'trschk_temp_location' );
	//	if ( $trschk_temp_location ) {
	//		$activity_object->type = 'geo_locate';
	//	}
	//}
//End Modification Removed type geo locate 7-11-18


//Begin Modification Removed ability to add data location to xprofile  7-11-18

	 public function trschk_update_meta_on_post_update( $content, $user_id, $activity_id ) {
		global $trmdb;
		$place_details = trs_get_option( 'trschk_temp_location' );
		$activity_tbl  = $trmdb->base_prefix . 'trs_activity';

		if ( ! empty( $place_details ) ) {
			$place           = $place_details['place'];
			$longitude       = $place_details['longitude'];
			$latitude        = $place_details['latitude'];
			$add_as_my_place = $place_details['add_as_my_place'];

			$location_html = ' -at <a class=checkin-loc href="http://maps.google.com/maps/place/' . $place . '/@' . $latitude . ',' . $longitude . '" target="_blank" title="' . $place . '">' . $place . '</a>';
			$content      .= $location_html;
			$pos           = strpos( $content, '-at <a class="checkin-loc"' );
			// Update the activity content to post the checkin along with the post update.
			if ( false === $pos ) {
				$trmdb->update(
					$activity_tbl, array( 'content' => $content ), array( 'id' => $activity_id ), array( '%s' ), array( '%d' )
				);

				// Update the location details in activity meta.
				trs_activity_update_meta( $activity_id, 'trschk_place_details', $place_details );
			}
			if ( 'yes' === $add_as_my_place ) {
				$trschk_fav_places = get_user_meta( $user_id, 'trschk_fav_places', true );
				$place_get_url    = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&sensor=false";
				$response         = trm_remote_get( $place_get_url );

				$response_code = trm_remote_retrieve_response_code( $response );

				if ( 200 === $response_code ) {
					$jsondata         = json_decode( trm_remote_retrieve_body( $response ), true );
					$place_visit_date = date( 'Y-m-d', time() );

					if ( $jsondata['results'][0]['formatted_address'] ) {
						$address                      = array();
						$address['latitude']          = $latitude;
						$address['longitude']         = $longitude;
						$address['activity_id']       = $activity_id;
						$address['place']             = $place;
						$address['country']           = self::google_get_country( $jsondata );
						$address['province']          = self::google_get_province( $jsondata );
						$address['city']              = self::google_get_city( $jsondata );
						$address['street']            = self::google_get_street( $jsondata );
						$address['postal_code']       = self::google_get_postalcode( $jsondata );
						$address['country_code']      = self::google_get_country_code( $jsondata );
						$address['formatted_address'] = self::google_get_address( $jsondata );
						$address['visit_date']        = $place_visit_date;

						if ( $trschk_fav_places ) {
							array_push( $trschk_fav_places, $address );
							update_user_meta( $user_id, 'trschk_fav_places', $trschk_fav_places );
						} else {
							$fav_places   = array();
							$fav_places[] = $address;
							update_user_meta( $user_id, 'trschk_fav_places', $fav_places );
						}
					}
				}
			}
			/**
			 * Delete the temp location after posting update,
			 * so that the same place doesn't gets posted
			 * when no checkin is done.
			 */

			delete_option( 'trschk_temp_location' );

		}
	}
	// * @param string $content The actvity content.
	// * @param int    $user_id User id.
	// * @param int    $activity_id Activity id.
	// * @since 1.0.1
	// */

	//END Modification Removed ability to add data location to xprofile  7-11-18

	
	//BEGIN Modification Removed ability to add data location to Groups  7-11-18



	 public function trschk_update_group_meta_on_post_update( $content, $user_id, $group_id, $activity_id ) {
		global $trmdb;
		$place_details = trs_get_option( 'trschk_temp_location' );
		$activity_tbl  = $trmdb->base_prefix . 'trs_activity';

		if ( ! empty( $place_details ) ) {
			$place           = $place_details['place'];
			$longitude       = $place_details['longitude'];
			$latitude        = $place_details['latitude'];
			$add_as_my_place = $place_details['add_as_my_place'];

			$location_html = ' -at <a class=checkin-loc href="http://maps.google.com/maps/place/' . $place . '/@' . $latitude . ',' . $longitude . '" target="_blank" title="' . $place . '">' . $place . '</a>';
			$content      .= $location_html;
			$pos           = strpos( $content, '-at <a class="checkin-loc"' );
			// Update the activity content to post the checkin along with the post update.
			if ( false === $pos ) {
				$trmdb->update(
					$activity_tbl, array( 'content' => $content ), array( 'id' => $activity_id ), array( '%s' ), array( '%d' )
				);

				// Update the location details in activity meta.
				trs_activity_update_meta( $activity_id, 'trschk_place_details', $place_details );
			}

			if ( 'yes' === $add_as_my_place ) {
				$trschk_fav_places = get_user_meta( $user_id, 'trschk_fav_places', true );
				$place_get_url    = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&sensor=false";
				$response         = trm_remote_get( $place_get_url );

				$response_code = trm_remote_retrieve_response_code( $response );

				if ( 200 === $response_code ) {
					$jsondata         = json_decode( trm_remote_retrieve_body( $response ), true );
					$place_visit_date = date( 'Y-m-d', time() );

					if ( $jsondata['results'][0]['formatted_address'] ) {
						$address                      = array();
						$address['latitude']          = $latitude;
						$address['longitude']         = $longitude;
						$address['activity_id']       = $activity_id;
						$address['place']             = $place;
						$address['country']           = self::google_get_country( $jsondata );
						$address['province']          = self::google_get_province( $jsondata );
						$address['city']              = self::google_get_city( $jsondata );
						$address['street']            = self::google_get_street( $jsondata );
						$address['postal_code']       = self::google_get_postalcode( $jsondata );
						$address['country_code']      = self::google_get_country_code( $jsondata );
						$address['formatted_address'] = self::google_get_address( $jsondata );
						$address['visit_date']        = $place_visit_date;

						if ( $trschk_fav_places ) {
							array_push( $trschk_fav_places, $address );
							update_user_meta( $user_id, 'trschk_fav_places', $trschk_fav_places );
						} else {
							$fav_places   = array();
							$fav_places[] = $address;
							update_user_meta( $user_id, 'trschk_fav_places', $fav_places );
						}
					}
				}
			}
			/**
			 * Delete the temp location after posting update,
			 * so that the same place doesn't gets posted
			 * when no checkin is done.
			 */
			delete_option( 'trschk_temp_location' );
		}
	}

	 //* @since 1.0.1
	 //*/
	


	/**
	 * To set activity action for check-in type activity in group.
	 *
	 * @param string $activity_action The group activity action.
	 * @since 1.0.1
	 */
	public function trschk_groups_activity_new_update_action( $activity_action ) {
		global $trs;
		$user_id       = trs_loggedin_user_id();
	$place_details = trs_get_option( 'trschk_temp_location' );
	if ( ! empty( $place_details ) ) {
			$activity_action = sprintf( __( '%1$s checked-in in the group %2$s', 'trendr' ), trs_core_get_userlink( $user_id ), '<a href="' . trs_get_group_permalink( $trs->groups->current_group ) . '">' . esc_attr( $trs->groups->current_group->name ) . '</a>' );
		}
		return $activity_action;
	}
	

	//END Modification Removed ability to add data location to xprofile  7-11-18

	

	//BEGIN Modification Removed ability to add data google maps to activity_updates  7-11-18

	/**
	 * Show mep on checkin activities
	 *
	 * @since 1.0.1
	 */


	public function trschk_show_google_map_in_checkin_activity() {
					
					//BEGIN MODIFICATION --OPTION TO DISABLE MAP LOADS ON SPECIFIC PAGES- --  TO WORK ONLY ON TEST PAGE 6-5-18

			//if (  is_user_logged_in() && !trs_is_blog_page('test')  && !trs_is_activity_component()) {
					//END MODIFICATION --OPTION TO DISABLE MAP LOADS ON SPECIFIC PAGES- --  TO WORK ONLY ON TEST PAGE 6-5-18
		$activity_id = trs_get_activity_id();
		global $trmdb, $trs_checkins;
		$activity_meta_tbl = $trmdb->base_prefix . 'trs_activity_meta';

		$qry    = "SELECT `meta_value` from `$activity_meta_tbl` where `activity_id` = $activity_id AND `meta_key` = 'trschk_place_details'";
		$result = $trmdb->get_results( $qry );
		if ( ! empty( $result ) ) {
			$place         = unserialize( $result[0]->meta_value );
			$apikey        = $trs_checkins->apikey;
			$latitude      = $place['latitude'];
			$longitude     = $place['longitude'];
			$place_get_url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&sensor=false";
			$response      = trm_remote_get( $place_get_url );

			$response_code     = trm_remote_retrieve_response_code( $response );
			$formatted_address = $place['place'];
			if ( 200 === $response_code ) {
				$jsondata = json_decode( trm_remote_retrieve_body( $response ), true );
				if ( isset( $jsondata['results'][0]['formatted_address'] ) ) {
					$formatted_address = self::google_get_address( $jsondata );
				}
			} else {
				$formatted_address = $place['place'];
			}
			$map_url = 'https://www.google.com/maps/embed/v1/place?key=' . $apikey . '&q=' . $formatted_address;
			echo '<div id="trschk-place-map"><iframe frameborder="0" style="border:0" src="' . esc_url( $map_url ) . '" allowfullscreen></iframe></div>';
		}
	}
//}



	 	public function trschk_fetch_places() {
		global $trs_checkins;
		if ( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) && 'trschk_fetch_places' === filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) ) {

			$apikey     = $trs_checkins->apikey;
			$range      = $trs_checkins->google_places_range * 1000;
			$placetypes = implode( '||', $trs_checkins->place_types );

			$latitude    = filter_input( INPUT_POST, 'latitude', FILTER_SANITIZE_STRING );
			$longitude   = filter_input( INPUT_POST, 'longitude', FILTER_SANITIZE_STRING );
			$places_html = '';

			$parameters = array(
				'location' => "$latitude,$longitude",
				'radius'   => $range,
				'key'      => $apikey,
				'type'     => $placetypes,
				'heading'  => true,
				'title'    => true,
			);
			$places_url = 'https://maps.googleapis.com/maps/api/place/nearbysearch/json';
			$url        = add_query_arg( $parameters, esc_url_raw( $places_url ) );

			$response   = trm_remote_get( esc_url_raw( $url ) );

			$response_code = trm_remote_retrieve_response_code( $response );
			if ( 200 === $response_code ) {
				$msg    = __( 'places-found', 'trs-checkins' );
				$places = json_decode( trm_remote_retrieve_body( $response ) );

				if ( ! empty( $places->results ) ) {
					$places_html .= '<ul class="trschk-places-fetched">';
					foreach ( $places->results as $place ) {
						$places_html .= '<li class="trschk-single-place">';
						$places_html .= '<div class="place-icon">';
						$places_html .= '<img height="18px" width="18px" title="' . $place->name . '" src="' . $place->icon . '">';
						$places_html .= '</div>';
						$places_html .= '<div class="place-details">';
						$places_html .= '<b>' . $place->name . '</b>';
						$places_html .= '<div>' . $place->vicinity . '</div>';
						$places_html .= '</div>';
						$places_html .= '<div class="place-actions">';
						$places_html .= '<a href="javascript:void(0);" class="trschk-select-place-to-checkin" data-place_reference="' . $place->reference . '" data-place_id="' . $place->place_id . '">' . __( 'Select', 'trs-checkins' ) . '</a>';
						$places_html .= '</div>';
						$places_html .= '</li>';
					}
					$places_html .= '</ul>';
					$places_html .= '<input type="checkbox" id="trschk-add-as-place" checked />';
					$places_html .= '<label for="trschk-add-as-place" id="trschk-add-my-place-label">' . __( 'Add as my location', 'trs-checkins' ) . '</label>';
					$places_html .= '<div class="trschk-single-location-added"></div>';
					$places_html .= '';
				}
			} else {
				$msg          = __( 'places-not-found', 'trs-checkins' );
				$places_html .= '<p>' . __( 'Places not found !', 'trs-checkins' ) . '</p>';
			}
			$result = array(
				'message' => $msg,
				'html'    => stripslashes( $places_html ),
			);
			echo json_encode( $result );
			die;
		}
	}



	 public function trschk_select_place_to_checkin() {
		if ( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) && 'trschk_select_place_to_checkin' === filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) ) {
			global $trs_checkins, $trmdb;
			$place_id        = filter_input( INPUT_POST, 'place_id', FILTER_SANITIZE_STRING );
			$place_html      = '';
			$options_tbl     = $trmdb->prefix . 'options';

			$parameters = array(
				'placeid' => $place_id,
				'key'     => $trs_checkins->apikey,
			);

			$place_detail_url = 'https://maps.googleapis.com/maps/api/place/details/json';
			$url              = add_query_arg( $parameters, esc_url_raw( $place_detail_url ) );
			$response         = trm_remote_get( esc_url_raw( $url ) );
			$response_code    = trm_remote_retrieve_response_code( $response );
			if ( 200 === $response_code ) {
				$msg   = __( 'place-details-found', 'trs-checkins' );
				$place = json_decode( trm_remote_retrieve_body( $response ) );

				$place_name = $place->result->name;
				$latitude   = $place->result->geometry->location->lat;
				$longitude  = $place->result->geometry->location->lng;

				$args = array(
					'place'           => $place_name,
					'latitude'        => $latitude,
					'longitude'       => $longitude,
					'add_as_my_place' => filter_input( INPUT_POST, 'add_as_my_place', FILTER_SANITIZE_STRING ),
				);

				$href        = 'http://maps.google.com/maps/place/' . $place_name . "/@$latitude,$longitude";
				$place_html .= "<div class='trschk-checkin-temp-location'>-at <a title='" . $place_name . "' href='" . $href . "' target='_blank' id='trschk-temp-location'>" . $place_name . '</a>';
				$place_html .= ' <a href="javascript:void(0);" id="trschk-cancel-checkin" title="' . __( 'Click here to cancel your checkin.', 'trs-checkins' ) . '"><i class="fa fa-times"></i></a>';
				$place_html .= '</div>';
				$place_html .= '<div>';
				$place_html .= '<a class="button" href="javascript:void(0);" id="trschk-show-places-panel">' . __( 'Show Locations', 'trs-checkins' ) . '</a>';
				$place_html .= '</div>';

				$qry    = "SELECT `option_id`, `option_value` from $options_tbl where `option_name` = 'trschk_temp_location'";
				$result = $trmdb->get_results( $qry );
				if ( empty( $result ) ) {
					// Insert the temp location in options table.
					$trmdb->insert(
						$options_tbl, array(
							'option_name'  => 'trschk_temp_location',
							'option_value' => serialize( $args ),
						)
					);
				} else {
					// Update the previously existing temp location in options table.
					$option_id = $result[0]->option_id;
					$trmdb->update(
						$options_tbl, array( 'option_value' => serialize( $args ) ), array( 'option_id' => $option_id )
					);
				}
			} else {
				$msg         = __( 'place-details-not-found', 'trs-checkins' );
				$place_html .= '<p>' . __( 'Place details not found !', 'trs-checkins' ) . '</p>';
			}

			$result = array(
				'message' => $msg,
				'html'    => stripslashes( $place_html ),
			);
			trm_send_json_success( $result );
			die;
		}
	}

		//END Modification Removed ability to add data google maps to activity_updates  7-11-18


	/**
	 * Ajax served to cancel the checkin
	 */
	public function trschk_cancel_checkin() {
		if ( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) && 'trschk_cancel_checkin' === filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) ) {
			global $trmdb;
			$tbl = $trmdb->prefix . 'options';
			$trmdb->delete( $tbl, array( 'option_name' => 'trschk_temp_location' ) );
			$result = array(
				'message' => __( 'Checkin cancelled !', 'trs-checkins' ),
			);
			trm_send_json_success( $result );
			die;
		}
	}

}
