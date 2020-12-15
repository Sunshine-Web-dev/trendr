<?php
/**
 * Caching functions handle the clearing of cached objects and pages on specific
 * actions throughout trendr.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * REQUIRES TRM-SUPER-CACHE
 *
 * When trm-super-cache is installed this function will clear cached pages
 * so that success/error messages are not cached, or time sensitive content.
 *
 * @package trendr Core
 */
function trs_core_clear_cache() {
	global $cache_path, $cache_filename;

	if ( function_exists( 'prune_super_cache' ) ) {
		do_action( 'trs_core_clear_cache' );
		return prune_super_cache( $cache_path, true );
	}
}

/**
 * Add's 'trs' to global group of network wide cachable objects
 *
 * @package trendr Core
 */
function trs_core_add_global_group() {
	trm_cache_init();
	trm_cache_add_global_groups( array( 'trs' ) );
}
add_action( 'trs_loaded', 'trs_core_add_global_group' );

/**
 * Clears all cached objects for a user, or a user is part of.
 *
 * @package trendr Core
 */
function trs_core_clear_user_object_cache( $user_id ) {
	trm_cache_delete( 'trs_user_' . $user_id, 'trs' );
}

// List actions to clear super cached pages on, if super cache is installed
add_action( 'trm_login',              'trs_core_clear_cache' );
add_action( 'trs_core_render_notice', 'trs_core_clear_cache' );

?>