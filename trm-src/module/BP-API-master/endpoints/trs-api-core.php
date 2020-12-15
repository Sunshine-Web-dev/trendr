<?php

class TRS_API_Core extends TRM_REST_Controller {


	public function __construct() {

	}


	/**
	 * register_routes function.
	 *
	 * Register the routes for the objects of the controller.
	 * 
	 * @access public
	 * @return void
	 */
	public function register_routes() {
	
		register_rest_route( TRS_API_SLUG, '/core/*', array(
			'methods'         => TRM_REST_Server::READABLE,
			'callback'        => array( $this, 'get_item' ),
			'permission_callback' => array( $this, 'core_api_permissions' ),
		) );
	}



	/**
	 * get_item function.
	 *
	 * returns data about a BuddyPress site
	 * 
	 * @access public
	 * @param mixed $request
	 * @return void
	 */
	public function get_item( $request ) {
	
		global $trs;
		$core = array(
			'version'            => $trs->version,
			'active_components'  => $trs->active_components,
			'directory_page_ids' => trs_core_get_directory_page_ids(),
		);
		
		$core = apply_filters( 'core_api_data_filter', $core );
		
		$response = new TRM_REST_Response();
		$response->set_data( $core );
		$response = rest_ensure_response( $response );
		
		return $response;
	
	}
	

	/**
	 * core_api_permissions function.
	 *
	 * allow permission to access core info
	 * 
	 * @access public
	 * @return void
	 */
	public function core_api_permissions() {
			
		$response = apply_filters( 'core_api_permissions', true );
		
		return $response;
	
	}

}
