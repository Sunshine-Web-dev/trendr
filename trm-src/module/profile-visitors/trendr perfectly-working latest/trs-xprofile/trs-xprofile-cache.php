<?php
/********************************************************************************
 * Caching
 *
 * Caching functions handle the clearing of cached objects and pages on specific
 * actions throughout trendr.
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function xprofile_clear_profile_groups_object_cache( $group_obj ) {
	trm_cache_delete( 'xprofile_groups_inc_empty', 'trs' );
	trm_cache_delete( 'xprofile_group_' . $group_obj->id );
}

function xprofile_clear_profile_data_object_cache( $group_id ) {
	global $trs;
	trm_cache_delete( 'trs_user_fullname_' . $trs->loggedin_user->id, 'trs' );
}

// List actions to clear object caches on
add_action( 'xprofile_groups_deleted_group', 'xprofile_clear_profile_groups_object_cache' );
add_action( 'xprofile_groups_saved_group',   'xprofile_clear_profile_groups_object_cache' );
add_action( 'xprofile_updated_profile',      'xprofile_clear_profile_data_object_cache'   );

// List actions to clear super cached pages on, if super cache is installed
add_action( 'xprofile_updated_profile', 'trs_core_clear_cache' );

?>