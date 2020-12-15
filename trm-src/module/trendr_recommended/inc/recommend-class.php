<?php
/**
 * TRS Follow Core
 *
 * @package TRS-Follow
 * @sutrsackage Core
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Core class for TRS Follow.
 *
 * Extends the {@link TRS_Component} class.
 *
 * @package TRS-Follow
 * @sutrsackage Classes
 *
 * @since 1.2
 */
class TRS_Recommend_Component extends TRS_Component {

	/**
	 * Constructor.
	 *
	 * @global obj $trs trendr instance
	 */
	public function __construct() {
		global $trs;

		// setup misc parameters
		$this->params = array(
			'adminbar_myaccount_order' => apply_filters( 'trs_recommend_nav_position', 71 )
		);

		// register our component as an active component in TRS
		$trs->active_components[$this->id] = '1';
	}


	/**
	 * Setup profile / BuddyBar navigation
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
		global $trs;

		// Need to change the user ID, so if we're not on a member page, $counts variable is still calculated
		$user_id = trs_is_user() ? trs_displayed_user_id() : trs_loggedin_user_id();

		// BuddyBar compatibility
		$domain = trs_displayed_user_domain() ? trs_displayed_user_domain() : trs_loggedin_user_domain();

		/** FOLLOWERS NAV ************************************************/

		trs_core_new_nav_item( array(
			'name'                =>  __( 'Recommended', 'trs-recommend' ),
			'slug'                => 'recommended',
			'position'            => $this->params['adminbar_myaccount_order'],
			'screen_function'     => 'trs_recommend_screen',
			'default_subnav_slug' => 'recommend',
			'item_css_id'         => 'recommend-following'
		) );




		/** ACTIVITY SUBNAV **********************************************/

		// Add activity sub nav item
		if ( trs_is_active( 'activity' ) && apply_filters( 'trs_follow_show_activity_subnav', true ) ) {

			trs_core_new_subnav_item( array(
				'name'            => _x( 'Recommended', 'Activity subnav tab', 'trs-recommend' ),
				'slug'            => 'recommend',
				'parent_url'      => trailingslashit( $domain . trs_get_activity_slug() ),
				'parent_slug'     => trs_get_activity_slug(),
				'screen_function' => 'trs_recommend_screen_activity',
				'position'        => 25,
				'item_css_id'     => 'activity-recommend'
			) );
		}

		// BuddyBar compatibility

		do_action( 'trs_follow_setup_nav' );

	}
