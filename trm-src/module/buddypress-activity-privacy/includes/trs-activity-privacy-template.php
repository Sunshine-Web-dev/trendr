<?php
/**
 * Buddypress Activity Privacy Template Tags
 *
 * @package TRS-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * [trs_get_profile_activity_privacy_levels description]
 * @return array the profile activity privacy levels
 */
function trs_get_profile_activity_privacy_levels(){
	global $trs_activity_privacy;

	$profile_activity_privacy_levels = trs_get_option( 'trs_ap_profile_activity_privacy_levels', $trs_activity_privacy->profile_activity_privacy_levels );
	return apply_filters( 'trs_profile_activity_privacy_levels_filter', $profile_activity_privacy_levels );
}

/**
 * [trs_get_groups_activity_privacy_levels description]
 * @return array the groups activity privacy levels
 */
function trs_get_groups_activity_privacy_levels(){
	global $trs_activity_privacy;

	$groups_activity_privacy_levels = trs_get_option( 'trs_ap_groups_activity_privacy_levels', $trs_activity_privacy->groups_activity_privacy_levels );
	return apply_filters( 'trs_groups_activity_privacy_levels_filter', $groups_activity_privacy_levels );
}

/**
 * [trs_get_profile_activity_visibility_levels description]
 * @return array the profile activity visibility levels 
 */
function trs_get_profile_activity_visibility_levels() {
	global $trs_activity_privacy;

	$profile_activity_visibility_levels = trs_get_option( 'trs_ap_profile_activity_visibility_levels', $trs_activity_privacy->profile_activity_visibility_levels );
	return apply_filters( 'trs_profile_activity_visibility_levels_filter', $profile_activity_visibility_levels );
}

/**
 * [trs_get_groups_activity_visibility_levels description]
 * @return array the groups activity visibility levels 
 */
function trs_get_groups_activity_visibility_levels() {
	global $trs_activity_privacy;

	$groups_activity_visibility_levels = trs_get_option( 'trs_ap_groups_activity_visibility_levels', $trs_activity_privacy->groups_activity_visibility_levels );
	return apply_filters( 'trs_groups_activity_visibility_levels_filter', $groups_activity_visibility_levels );
}

/**
 * [trs_profile_activity_visibility description]
 * @return String [description]
 */
function trs_profile_activity_visibility() {
	echo trs_get_profile_activity_visibility();
}

	function trs_get_profile_activity_visibility() {
		global $trs_activity_privacy;

		$visibility_levels = trs_get_profile_activity_visibility_levels();
		//sort visibility_levels by position 
		uasort ($visibility_levels, 'trs_activity_privacy_cmp_position');
		
	    $html = '<select name="activity-privacy" id="activity-privacy">';
	    $html .= '<option selected disabled>' . __( 'Who can see this?', 'trs-activity-privacy' )  .'</option>';
	    foreach ($visibility_levels as $visibility_level) {
	    	if( isset($visibility_level["disabled"]) && $visibility_level["disabled"] )
	    		continue;
	        $html .= '<option class="fa fa-' . $visibility_level["id"] . '" ' . ( $visibility_level['default'] == true ? " selected='selected'" : '' ) . ' value="' . $visibility_level["id"] . '">' . $visibility_level["label"] . '</option>';
	    }
	    $html .= '</select>';

	    return apply_filters( 'trs_get_profil_activity_visibility_filter', $html );
	}

/**
 * [trs_groups_activity_visibility description]
 * @return String [description]
 */
function trs_groups_activity_visibility() {
	echo trs_get_groups_activity_visibility();
}
	
	function trs_get_groups_activity_visibility() {
		global $trs_activity_privacy;

		$visibility_levels = trs_get_groups_activity_visibility_levels();
		//sort visibility_levels by position 
		uasort ($visibility_levels, 'trs_activity_privacy_cmp_position');

	    $html = '<select name="activity-privacy" id="activity-privacy">';
	    $html .= '<option selected disabled>' . __( 'Who can see this?', 'trs-activity-privacy' )  .'</option>';
	    foreach ($visibility_levels as $visibility_level) {
	    	if( isset($visibility_level["disabled"]) && $visibility_level["disabled"])
	    		continue;
	   
	        $html .= '<option  class="fa fa-' . $visibility_level["id"] . '" ' .  ( $visibility_level['default'] == true ? " selected='selected'" : '' ) . ' value="' . $visibility_level["id"] . '">' . $visibility_level["label"] . '</option>';
	        
	    }
	    $html .= '</select>';

	    return apply_filters( 'trs_get_groups_activity_visibility', $html );
	}


function trs_ap_is_admin_allowed_to_view_edit_privacy_levels(){
	return trs_get_option( 'trs_ap_allow_admin_ve_pl', false);
}	

function trs_ap_is_members_allowed_to_edit_privacy_levels(){
	return trs_get_option( 'trs_ap_allow_members_e_pl', true);
}	

function trs_ap_is_use_fontawsome(){
	return trs_get_option( 'trs_ap_use_fontawsome', true);
}	

function trs_ap_is_use_custom_styled_selectbox(){
	return trs_get_option( 'trs_ap_use_custom_styled_selectbox', true);
}

function trs_ap_show_privacy_levels_label(){
	return trs_get_option( 'trs_ap_show_privacy_ll', true);
}

function trs_ap_show_privacy_in_activity_meta(){
	return trs_get_option( 'trs_ap_show_privacy_in_am', true);
}