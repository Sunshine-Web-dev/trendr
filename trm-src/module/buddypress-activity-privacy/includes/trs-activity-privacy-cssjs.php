<?php
/**
 * TRS Activity Privacy Css and js enqueue  
 *
 * @package TRS-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Load js files
 */
function trs_activity_privacy_add_js() {
	global $trs;

	if( trs_ap_is_use_custom_styled_selectbox() ) {
		trm_enqueue_script( 'jq-customselect-js', plugins_url( 'js/jquery.customSelect.js' ,  __FILE__ ), array(), false, true );
	}

	trm_enqueue_script( 'trs-activity-privacy-js', plugins_url( 'js/trs-activity-privacy.js' ,  __FILE__ ), array(), false, true );
	
	$visibility_levels = array(
		'custom_selectbox' => trs_ap_is_use_custom_styled_selectbox(),
	    'profil' => trs_get_profile_activity_visibility(),
	    'groups' => trs_get_groups_activity_visibility()
    );

	trm_localize_script( 'trs-activity-privacy-js', 'visibility_levels', $visibility_levels );

}
add_action( 'trm_enqueue_scripts', 'trs_activity_privacy_add_js', 1 );

/**
 * Load css files
 */
function trs_activity_privacy_add_css() {
	// global $trm_styles;

	// $srcs = array_map('basename', (array) trm_list_pluck($trm_styles->registered, 'src') );
	// if ( !in_array('font-awesome.css', $srcs) && !in_array('font-awesome.min.css', $srcs)  ) {
   	if( trs_ap_is_use_fontawsome() ) {
    	trm_enqueue_style( 'trs-activity-privacy-font-awesome-css', plugins_url( 'css/font-awesome/css/font-awesome.min.css' ,  __FILE__ )); 
    	trm_enqueue_style( 'trs-activity-privacy-css', plugins_url( 'css/trs-activity-privacy.css' ,  __FILE__ )); 
	}

	if( !trs_ap_show_privacy_levels_label() ){
	   	$hide_privacy_label_css = ".customSelectInner { display: none !important; }";
        trm_add_inline_style( 'trs-activity-privacy-css', $hide_privacy_label_css );
	}

}
add_action( 'trs_actions', 'trs_activity_privacy_add_css', 1 );