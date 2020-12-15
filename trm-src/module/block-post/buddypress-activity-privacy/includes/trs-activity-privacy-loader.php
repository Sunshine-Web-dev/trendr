<?php
/**
 * TRS-Activity Privacy loader
 *
 * @package TRS-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * TRS_Activity_Privacy Class
 */
 //asamir followers option added
class TRS_Activity_Privacy {

	var $profile_activity_privacy_levels = array();
	var $groups_activity_privacy_levels = array();

	var $profile_activity_visibility_levels = array();
	var $groups_activity_visibility_levels = array();

	function __construct() {
		global $trs;

		// Register the visibility levels
		$this->profile_activity_privacy_levels = array(
			'public', 'loggedin', 'adminsonly', 'onlyme', 'followersOnly'
		);

		$this->groups_activity_privacy_levels = array(
			'public', 'loggedin', 'adminsonly', 'onlyme','followersOnly'
		);

		if ( trs_is_active( 'friends' ) ) {
			$this->profile_activity_privacy_levels [] = 'friends';
			$this->groups_activity_privacy_levels [] = 'friends';
		}

		if ( trs_is_active( 'groups' ) ) {
			$this->groups_activity_privacy_levels [] = 'groupfriends';
			$this->groups_activity_privacy_levels [] = 'grouponly';
			$this->groups_activity_privacy_levels [] = 'groupmoderators';
			$this->groups_activity_privacy_levels [] = 'groupadmins';
		}

		//mentioned
		// https://trendr.trac.trendr.org/changeset/7193
		if ( function_exists('trs_activity_do_mentions') ) {
			if ( trs_activity_do_mentions() ) {
				$this->profile_activity_privacy_levels [] = 'mentionedonly';
				$this->groups_activity_privacy_levels [] = 'mentionedonly';
			}
		} else {
			//$this->profile_activity_privacy_levels [] = 'mentionedonly';
			//$this->groups_activity_privacy_levels [] = 'mentionedonly';
		}

		// Register the visibility levels
		$this->profile_activity_visibility_levels  = array(
	        'public' => array(
	            'id'        => 'public',
	            'label'     => __( 'Anyone', 'trs-activity-privacy' ),
	            'default'   => true,
	            'position'  => 10,
	            'disabled'  => false
	        ),
	        'loggedin' => array(
	            'id'        => 'loggedin',
	            'label'     => __( 'Logged In Users', 'trs-activity-privacy' ),
	            'default'   => false,
	            'position'  => 20,
	            'disabled'  => false
	        )
	    );

	    if ( trs_is_active( 'friends' ) ) {
	        $this->profile_activity_visibility_levels['friends'] = array(
	            'id'        => 'friends',
	            'label'     => __( 'My Friends', 'trs-activity-privacy' ),
	            'default'   => false,
	            'position'  => 30,
	            'disabled'  => false
	        );
	    }

		//mentioned
		// https://trendr.trac.trendr.org/changeset/7193
		if ( function_exists('trs_activity_do_mentions') ) {
			if ( trs_activity_do_mentions() ) {
		        $this->profile_activity_visibility_levels['mentionedonly'] = array(
		            'id'        => 'mentionedonly',
		            'label'     => __( 'Mentioned Only', 'trs-activity-privacy' ),
		            'default'   => false,
		            'position'  => 40,
		            'disabled'  => false
		        );
			}
		}else {
			/*
	        $this->profile_activity_visibility_levels['mentionedonly'] = array(
	            'id'        => 'mentionedonly',
	            'label'     => __( 'Mentioned only', 'trs-activity-privacy' ),
	            'default'   => false,
	            'position'  => 40,
	            'disabled'  => false
	        );	*/
		}

	    $this->profile_activity_visibility_levels['adminsonly'] = array(
	        'id'      => 'adminsonly',
	        'label'   => __( 'Admins Only', 'trs-activity-privacy' ),
	        'default' => false,
	        'position'  => 50,
	        'disabled'  => false
	    );

	    $this->profile_activity_visibility_levels['onlyme'] = array(
	        'id'        => 'onlyme',
	        'label'     => __( 'Only Me', 'trs-activity-privacy' ),
	        'default'   => false,
	        'position'  => 60,
	        'disabled'  => false
	    );

	    $this->groups_activity_visibility_levels = array(
	        'public' => array(
	            'id'        => 'public',
	            'label'     => __( 'Anyone', 'trs-activity-privacy' ),
	            'default'   => true,
	            'position'  => 10,
	            'disabled'  => false
	        ),
	        'loggedin' => array(
	            'id'         => 'loggedin',
	            'label'      => __( 'Logged In Users', 'trs-activity-privacy' ),
	            'default'    => false,
	             'position'  => 20,
	            'disabled'   => false
	        )
	    );

	    if ( trs_is_active( 'friends' ) ) {
	        $this->groups_activity_visibility_levels['friends'] = array(
	            'id'        => 'friends',
	            'label'     => __( 'My Friends', 'trs-activity-privacy' ),
	            'default'   => false,
	            'position'  => 30,
	            'disabled'  => false
	        );

	        if ( trs_is_active( 'groups' ) ) {
		        $this->groups_activity_visibility_levels['groupfriends'] = array(
		            'id'        => 'groupfriends',
		            'label'     => __( 'My Friends in Group', 'trs-activity-privacy' ),
		            'default'   => false,
		            'position'  => 40,
	            	'disabled'  => false
		        );
	   		}
	    }
      //followersOnly

      if ( class_exists('TRS_Follow_Component') ) {


            $this->groups_activity_visibility_levels['followersOnly'] = array(
                  'id'        => 'followersOnly',
                  'label'     => __( 'Followers Only', 'trs-activity-privacy' ),
                  'default'   => false,
                  'position'  => 10,
                'disabled'  => false,
              );

              //var_dump(  $this->profile_activity_visibility_levels);
              $this->profile_activity_visibility_levels['followersOnly'] = array(
                    'id'        => 'followersOnly',
                    'label'     => __( 'Followers Only', 'trs-activity-privacy' ),
                    'default'   => false,
                    'position'  => 70,
                  'disabled'  => false,
                );

    }



		//mentioned
		// https://trendr.trac.trendr.org/changeset/7193
		if ( function_exists('trs_activity_do_mentions') ) {
			if ( trs_activity_do_mentions() ) {
		        $this->groups_activity_visibility_levels['mentionedonly'] = array(
		            'id'        => 'mentionedonly',
		            'label'     => __( 'Mentioned Only', 'trs-activity-privacy' ),
		            'default'   => false,
		            'position'  => 50,
		            'disabled'  => false
		        );
			}
		} else {
			/*
	        $this->groups_activity_visibility_levels['mentionedonly'] = array(
	            'id'        => 'mentionedonly',
	            'label'     => __( 'Mentioned only', 'trs-activity-privacy' ),
	            'default'   => false,
	            'position'  => 50,
	            'disabled'  => false
	        );	*/
		}

	    if ( trs_is_active( 'groups' ) ) {
	        $this->groups_activity_visibility_levels['grouponly'] = array(
	            'id'        => 'grouponly',
	            'label'     => __( 'Group Members', 'trs-activity-privacy' ),
	            'default'   => false,
	            'position'  => 60,
	            'disabled'  => false
	        );

	        $this->groups_activity_visibility_levels['groupmoderators'] = array(
	            'id'        => 'groupmoderators',
	            'label'     => __( 'Group Moderators', 'trs-activity-privacy' ),
	            'default'   => false,
	            'position'  => 70,
	            'disabled'  => false
	        );

	        $this->groups_activity_visibility_levels['groupadmins'] = array(
	            'id'        => 'groupadmins',
	            'label'     => __( 'Group Admins', 'trs-activity-privacy' ),
	            'default'   => false,
	            'position'  => 80,
	            'disabled'  => false
	        );
		}


		$this->groups_activity_visibility_levels['adminsonly'] = array(
	        'id'        => 'adminsonly',
	        'label'     => __( 'Admins Only', 'trs-activity-privacy' ),
	        'default'   => false,
	        'position'  => 90,
		    'disabled'  => false,
	    );

	    $this->groups_activity_visibility_levels['onlyme'] = array(
	        'id'        => 'onlyme',
	        'label'     => __( 'Only Me', 'trs-activity-privacy' ),
	        'default'   => false,
	        'position'  => 100,
		    'disabled'  => false,
	    );

		$this->includes();
	}

	function includes() {
		// Files to include
		include( TRS_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/trs-activity-privacy-actions.php' );
		include( TRS_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/trs-activity-privacy-filters.php' );
		include( TRS_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/trs-activity-privacy-template.php' );
		include( TRS_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/trs-activity-privacy-functions.php' );
		include( TRS_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/trs-activity-privacy-cssjs.php' );
		include( TRS_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/trs-activity-privacy-ajax.php' );

		// fix / integration with some plugins
		include( TRS_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/trs-activity-privacy-integrations.php' );

		// As an follow of how you might do it manually, let's include the functions used
		// on the trendr Dashboard conditionally:
		if ( is_super_admin() && ( is_admin() || is_network_admin() ) ) {
			include( TRS_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/trs-activity-privacy-admin.php' );
			$this->admin = new TRSActivityPrivacy_Admin;
		}

	}
}

function trs_activity_privacy_load_core() {
	global $trs, $trs_activity_privacy;

	$trs_activity_privacy = new TRS_Activity_Privacy;
	do_action('trs_activity_privacy_load_core');
}
//add_action( 'trs_loaded', 'trs_activity_privacy_load_core', 5 );
add_action( 'trs_init', 'trs_activity_privacy_load_core', 5 );
