<?php

class TRS_API_Controller extends TRM_JSON_Controller {


	public function __construct() {
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		$base = $this->get_post_type_base( $this->post_type );

		$posts_args = array(
			'context'          => array(
				'default'      => 'view',
			),
			'page'            => array(
				'default'           => 0,
				'sanitize_callback' => 'absint'
			),
		);

		foreach ( $this->get_allowed_query_vars() as $var ) {
			if ( ! isset( $posts_args[$var] ) ) {
				$posts_args[$var] = array();	
			}
		}

		$trs_api_core = new TRS_API_Core;
		
		var_dump($trs_api_core);
		register_rest_route( TRS_API_SLUG, '/', array(
			'methods'         => TRM_JSON_Server::READABLE,
			'callback'        => array( $trs_api_core, 'get_info' ),
		) );
	}

}
