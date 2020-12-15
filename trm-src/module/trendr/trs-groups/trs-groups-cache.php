<?php
/**
 * trendr Groups Caching
 *
 * Caching functions handle the clearing of cached objects and pages on specific
 * actions throughout trendr.
 *
 * @package trendr
 * @sutrsackage Groups
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function groups_clear_group_object_cache( $group_id ) {
	trm_cache_delete( 'trs_total_group_count', 'trs' );
}
add_action( 'groups_group_deleted',              'groups_clear_group_object_cache' );
add_action( 'groups_settings_updated',           'groups_clear_group_object_cache' );
add_action( 'groups_details_updated',            'groups_clear_group_object_cache' );
add_action( 'groups_group_portrait_updated',       'groups_clear_group_object_cache' );
add_action( 'groups_create_group_step_complete', 'groups_clear_group_object_cache' );

function groups_clear_group_user_object_cache( $group_id, $user_id ) {
	trm_cache_delete( 'trs_total_groups_for_user_' . $user_id );
}
add_action( 'groups_join_group',   'groups_clear_group_user_object_cache', 10, 2 );
add_action( 'groups_leave_group',  'groups_clear_group_user_object_cache', 10, 2 );
add_action( 'groups_ban_member',   'groups_clear_group_user_object_cache', 10, 2 );
add_action( 'groups_unban_member', 'groups_clear_group_user_object_cache', 10, 2 );

/* List actions to clear super cached pages on, if super cache is installed */
add_action( 'groups_join_group',                 'trs_core_clear_cache' );
add_action( 'groups_leave_group',                'trs_core_clear_cache' );
add_action( 'groups_accept_invite',              'trs_core_clear_cache' );
add_action( 'groups_reject_invite',              'trs_core_clear_cache' );
add_action( 'groups_invite_user',                'trs_core_clear_cache' );
add_action( 'groups_uninvite_user',              'trs_core_clear_cache' );
add_action( 'groups_details_updated',            'trs_core_clear_cache' );
add_action( 'groups_settings_updated',           'trs_core_clear_cache' );
add_action( 'groups_unban_member',               'trs_core_clear_cache' );
add_action( 'groups_ban_member',                 'trs_core_clear_cache' );
add_action( 'groups_demote_member',              'trs_core_clear_cache' );
add_action( 'groups_premote_member',             'trs_core_clear_cache' );
add_action( 'groups_membership_rejected',        'trs_core_clear_cache' );
add_action( 'groups_membership_accepted',        'trs_core_clear_cache' );
add_action( 'groups_membership_requested',       'trs_core_clear_cache' );
add_action( 'groups_create_group_step_complete', 'trs_core_clear_cache' );
add_action( 'groups_created_group',              'trs_core_clear_cache' );
add_action( 'groups_group_portrait_updated',       'trs_core_clear_cache' );

?>