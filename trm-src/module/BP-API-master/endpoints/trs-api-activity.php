<?php

class TRS_API_Activity extends TRM_REST_Controller {


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
	
		register_rest_route( TRS_API_SLUG, '/activity', array(
			'methods'         => TRM_REST_Server::READABLE,
			'callback'        => array( $this, 'get_items' ),
			'permission_callback' => array( $this, 'trs_activity_permission' )
		) );
		register_rest_route( TRS_API_SLUG, '/activity/(?P<id>\d+)', array(
			'methods'         => TRM_REST_Server::READABLE,
			'callback'        => array( $this, 'get_item' ),
			'permission_callback' => array( $this, 'trs_activity_permission' ),
		) );
		
	}


	/**
	 * get_items function.
	 * 
	 * @access public
	 * @param array $filter (default: array())
	 * @return void
	 */
	public function get_items( $filter = array() ) {

		$response = $this->get_activity( $filter['filter'] );

		return $response;

	}

	
	/**
	 * get_item function.
	 * 
	 * @access public
	 * @param mixed $request
	 * @return void
	 */
	public function get_item( $request ) {

		$response = 'a single activity item';

		return $response;

	}


	
	/**
	 * get_activity function.
	 * 
	 * @access public
	 * @param mixed $filter
	 * @return void
	 */
	public function get_activity( $filter ) {

		$args = $filter;

		if ( trs_has_activities( $args ) ) {

			while ( trs_activities() ) {

				trs_the_activity();

				$activity = array(
					'avatar'	 		=> trs_core_fetch_avatar( array( 'html' => false, 'item_id' => trs_get_activity_id() ) ),
					'action'	 		=> trs_get_activity_action(),
					'content'	  		=> trs_get_activity_content_body(),
					'activity_id'		=> trs_get_activity_id(),
					'activity_username' => trs_core_get_username( trs_get_activity_user_id() ),
					'user_id'	 		=> trs_get_activity_user_id(),
					'comment_count'  	=> trs_activity_get_comment_count(),
					'can_comment'	 	=> trs_activity_can_comment(),
					'can_favorite'	  	=> trs_activity_can_favorite(),
					'is_favorite'	 	=> trs_get_activity_is_favorite(),
					'can_delete'  		=> trs_activity_user_can_delete()
				);

				$activity = apply_filters( 'trs_json_prepare_activity', $activity );

				$activities[] =	 $activity;

			}

			$data = array(
				'activity' => $activities,
				'has_more_items' => trs_activity_has_more_items()
			);

			$data = apply_filters( 'trs_json_prepare_activities', $data );

		} else {
			return new TRM_Error( 'trs_json_activity', __( 'No Activity Found.', 'trendr' ), array( 'status' => 200 ) );
		}

		$response = new TRM_REST_Response();
		$response->set_data( $data );
		$response = rest_ensure_response( $response );

		return $response;

	}

	
	/**
	 * add_activity function.
	 * 
	 * @access public
	 * @return void
	 */
	public function add_activity() {

		//add activity code here

	}

	
	/**
	 * edit_activity function.
	 * 
	 * @access public
	 * @return void
	 */
	public function edit_activity() {

		//edit activity code here

	}

	
	/**
	 * remove_activity function.
	 * 
	 * @access public
	 * @return void
	 */
	public function remove_activity() {

		//remove activity code here

	}
	
	
	/**
	 * trs_activity_permission function.
	 *
	 * allow permission to access data
	 * 
	 * @access public
	 * @return void
	 */
	public function trs_activity_permission() {
	
		$response = apply_filters( 'trs_activity_permission', true );
		
		return $response;
	
	}

	

}
