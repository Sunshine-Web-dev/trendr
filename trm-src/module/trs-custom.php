<?php
function dont_save( &$activity ) {
 
    $dont_types = array( 'new_member');
 
    if ( empty( $activity->id ) && in_array( $activity->type , $dont_types ) ) {
        $activity->type = '';
 
    }
 
}
add_action( 'trs_activity_before_save', 'dont_save');




/**
	/**
function format_activity_date() {
$activityDate=trs_get_activity_date_recorded();
if ($activityDate>strtotime(24))
{
return date("F j, Y", strtotime($activityDate));
}
else
{$activityDate;}

} 

              if ( trs_has_groups( 'user_id=' . trs_loggedin_user_id()  ) ) :
							while ( trs_groups('groups') ) : trs_the_group();
								endwhile;
						endif;
							 return true;    
add_filter('trs_activity_time_since', 'format_activity_date');


	function blog_posts_by_default( $query_string ) {

global $trs;

if ( !$query_string )

$query_string = '';

if  (trs_is_blog_page('test')){

if ( strpos( $query_string, 'action' ) == 0 )

$query_string .= '&type=activity_update&action=activity_update,photo_post,video_post,geo_locate';
}


if ( (trs_loggedin_user_domain() . trs_get_activity_slug()  ) ) {

if ( strpos( $query_string, 'action' ) == 0 )

$query_string .= '&type=photo_post&action=photo_post,video_post,geo_locate';

}



return $query_string;

}

add_filter( 'trendr_ajax_call', 'blog_posts_by_default' );


	 */  





define ( 'TRS_ENABLE_ROOT_PROFILES', true );

// Customizable Slug
//define ( 'TRS_ACTIVITY_SLUG', 'explore' );
define ( 'TRS_USERS_SLUG', 'users' );
//define ( 'TRS_REGISTER_SLUG', 'account' );
define ( 'TRS_ACTIVATION_SLUG', 'enable' );
//define ( 'TRS_SEARCH_SLUG', 'find' );

//add_filter('trs_activity_can_comment','__return_false');

// jquey google
function no_fav_button() {
	return false;
}
add_filter('trs_activity_can_favorite', 'no_fav_button' );

/**
 * Modify Trnder group search to work on a word-wise basis
 */

define( 'TRS_AVATAR_DEFAULT', '//i2.trm.com/trendr.com/trm-content/image.png' );
add_filter('trs_core_fetch_portrait_no_grav', '__return_true');

// Remove admin from directory
add_action('trs_ajax_querystring','trsdev_exclude_users',20,2);
function trsdev_exclude_users($qs=false,$object=false){
 //list of users to exclude
 if (trs_is_active('activity') && function_exists('trm_cache_add_non_persistent_groups'))
    trm_cache_add_non_persistent_groups(array('trs_activity', 'trs_activity_meta'));
 $excluded_user='1';//comma separated ids of users whom you want to exclude
 
 if($object!='users')//hide for users only
 return $qs;
 
 $args=trm_parse_args($qs);
 
 //check if we are listing friends?, do not exclude in this case
 if(!empty($args['user_id']))
 return $qs;
 
 if(!empty($args['exclude']))
 $args['exclude']=$args['exclude'].','.$excluded_user;
 else
 $args['exclude']=$excluded_user;
 
 $qs=build_query($args);
 
 return $qs;
 
}



/* define the default Profile component */
//Profile Link Backend.
add_filter("user_row_actions","link_to_trs_profile",10,2);//hook our link to row actions
function link_to_trs_profile($actions,$user){
$trs_profile_link=trs_core_get_user_domain($user->ID);
$actions["profile"]="<a href='".$trs_profile_link."'>Profile</a>";//hook our link
return $actions;}
//Dashboard Lockdown.
function block_dashboard() {
    $file = basename($_SERVER['PHP_SELF']);
    if (is_user_logged_in() && is_admin() && !current_user_can('edit_posts') && $file != 'admin-ajax.php'){
        trm_redirect( home_url() );
        exit();
    }}
add_action('init', 'block_dashboard');

//Loggin to Profile page.



//activity_homepage.  Integrated with useagents / for custom redirection mobile and desktop
//function trs_profile_homepage(){
//global $trs;	
//if(is_user_logged_in() && is_front_page())		{
	
//trm_redirect( TRS_ACTIVITY_SLUG . '/following' ) ;	

//}	
		//}
//add_action('trm','trs_profile_homepage');

  // Login via Email
function dr_email_login_authenticate( $user, $username, $password ) {
	if ( is_a( $user, 'TRM_User' ) )
		return $user;
	if ( !empty( $username ) ) {
		$username = str_replace( '&', '&amp;', stripslashes( $username ) );
		$user = get_user_by( 'email', $username );
		if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status )
			$username = $user->user_login;
	}	return trm_authenticate_username_password( null, $username, $password );
}
remove_filter( 'authenticate', 'trm_authenticate_username_password', 20, 3 );
add_filter( 'authenticate', 'dr_email_login_authenticate', 20, 3 );


// Honepot Spam/ Trnder registration form
class pjtrs_honeypot {

	CONST TRSPJ_HONEYPOT_NAME	= 'I would not do that!!';
	CONST TRSPJ_HONEYPOT_ID		= 'caught';

	function __construct() {
		add_action( 'trs_after_signup_profile_fields', array( &$this, 'add_honeypot' ) );
		add_filter( 'trs_core_validate_user_signup', array( &$this, 'check_honeypot' ) );
	}

	function add_honeypot() {
		echo '<div style="display: none;">';
		echo '<input type="text" name="'.apply_filters( 'trspj_honeypot_name', self::TRSPJ_HONEYPOT_NAME ).'" id="'.apply_filters( 'trspj_honeypot_id', self::TRSPJ_HONEYPOT_ID ).'" />';
		echo '</div>';
	}

	function check_honeypot( $result = array() ) {
		global $trs;

		$trspj_honeypot_name = apply_filters( 'trspj_honeypot_name', self::TRSPJ_HONEYPOT_NAME );

		if( isset( $_POST[$trspj_honeypot_name] ) && !empty( $_POST[$trspj_honeypot_name] ) )
$result['errors']->add( 'user_name', apply_filters( 'trspj_honeypot_fail_message', __( "Your action is not permitted." ) ) );
		
		return $result;
	}

}
new pjtrs_honeypot;
/////////////// Remove All update notice

