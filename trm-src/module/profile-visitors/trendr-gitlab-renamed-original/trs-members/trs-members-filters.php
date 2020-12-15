<?php
/**
 * trendr Members Filters
 *
 * Member specific filters
 *
 * @package trendr
 * @sutrsackage Member Core
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Load additional sign-up sanitization filters on trs_loaded. These are used
 * to prevent XSS in the trendr sign-up process. You can unhook these to
 * allow for customization of your registration fields, however it is highly
 * recommended that you leave these in place for the safety of your network.
 *
 * @since trendr (r4079)
 * @uses add_filter()
 */
function trs_members_signup_sanitization() {

	// Filters on sign-up fields
	$fields = array (
		'trs_get_signup_username_value',
		'trs_get_signup_email_value',
		'trs_get_signup_with_blog_value',
		'trs_get_signup_blog_url_value',
		'trs_get_signup_blog_title_value',
		'trs_get_signup_blog_privacy_value',
		'trs_get_signup_portrait_dir_value',
	);

	// Add the filters to each field
	foreach( $fields as $filter ) {
		add_filter( $filter, 'esc_html',       1 );
		add_filter( $filter, 'trm_filter_kses', 2 );
		add_filter( $filter, 'stripslashes',   3 );
	}

	// Sanitize email
	add_filter( 'trs_get_signup_email_value', 'sanitize_email' );
}
add_action( 'trs_loaded', 'trs_members_signup_sanitization' );

/**
 * Filter the user profile URL to point to trendr profile edit
 *
 * @since trendr 1.5.2
 *
 * @global trendr $trs
 * @param string $url
 * @param int $user_id
 * @param string $scheme
 * @return string
 */
function trs_members_edit_profile_url( $url, $user_id, $scheme = 'admin' ) {
	global $trs;

	// Default to $url
	$profile_link = $url;

	// If xprofile is active, use profile domain link
	if ( trs_is_active( 'xprofile' ) ) {
		$user_domain  = trs_core_get_user_domain( $user_id );
		$profile_link = trailingslashit( $user_domain . $trs->profile->slug . '/edit' );
	}
	
	return apply_filters( 'trs_members_edit_profile_url', $profile_link, $url, $user_id, $scheme );
}
add_filter( 'edit_profile_url', 'trs_members_edit_profile_url', 10, 3 );

?>