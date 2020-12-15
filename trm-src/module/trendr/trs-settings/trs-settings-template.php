<?php
/**
 * trendr Settings Template Functions
 *
 * @package trendr
 * @sutrsackage Settings Template
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Output the settings component slug
 *
 * @package trendr
 * @sutrsackage Settings Template
 * @since 1.5
 *
 * @uses trs_get_settings_slug()
 */
function trs_settings_slug() {
	echo trs_get_settings_slug();
}
	/**
	 * Return the settings component slug
	 *
	 * @package trendr
	 * @sutrsackage Settings Template
	 * @since 1.5
	 */
	function trs_get_settings_slug() {
		global $trs;
		return apply_filters( 'trs_get_settings_slug', $trs->settings->slug );
	}

/**
 * Output the settings component root slug
 *
 * @package trendr
 * @sutrsackage Settings Template
 * @since 1.5
 *
 * @uses trs_get_settings_root_slug()
 */
function trs_settings_root_slug() {
	echo trs_get_settings_root_slug();
}
	/**
	 * Return the settings component root slug
	 *
	 * @package trendr
	 * @sutrsackage Settings Template
	 * @since 1.5
	 */
	function trs_get_settings_root_slug() {
		global $trs;
		return apply_filters( 'trs_get_settings_root_slug', $trs->settings->root_slug );
	}

?>