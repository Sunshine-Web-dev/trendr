<?php
if ( !defined( 'TRSB_VERSION' ) ) exit;

/**
 * Block Activity View
 * If the user listed is blocking us, we will not be able to see
 * their activities.
 * @since 1.0
 * @version 1.0
 */
function trsb_remove_activity_if_blocked( $update_content ) {
	global $members_template;
	
	if ( !empty( $members_template->member->id ) )
		$user_id = $members_template->member->id;
	elseif ( trs_displayed_user_id() )
		$user_id = trs_displayed_user_id();

	$users_list = trsb_get_blocked_users( $user_id );
	if ( in_array( trs_loggedin_user_id() , $users_list ) && ! current_user_can( TRSB_ADMIN_CAP ) )
		return '';

	return $update_content;
}

/**
 * Filter Activities
 * Runs though the activities and removes activities that we are blockand and those we are blocked by.
 * @since 1.0
 * @version 1.0
 */
function trsb_filter_activities( &$activities, &$args )
{
	if ( ! is_user_logged_in() ) return $activities;
	
	$user_id = trs_loggedin_user_id() ;
	$my_list = trsb_get_blocked_users( $user_id );
	$args['exclude'] = implode( ',', $my_list );

	$removed = 0;

	// Enforce those I block
	foreach ( $activities['activities'] as $num => $activity ) {
		if ( in_array( $activity->user_id, $my_list ) && ! user_can( $activity->user_id, TRSB_ADMIN_CAP ) ) {
			unset( $activities['activities'][ $num ] );
			$removed = $removed+1;
		}
	}

	// Re-organize the array
	$activities['activities'] = array_values( $activities['activities'] );

	// Enforce those who block me
	foreach ( $activities['activities'] as $num => $activity ) {
		$their_list = trsb_get_blocked_users( $activity->user_id );
		if ( in_array( $user_id, $their_list ) && ! user_can( $activity->user_id, TRSB_ADMIN_CAP ) ) {
			unset( $activities['activities'][ $num ] );
			$removed = $removed+1;
		}
	}

	// Re-organize the array
	$activities['activities'] = array_values( $activities['activities'] );

	// Update counter
	if ( $removed > 0 )
		$activities['total'] = $activities['total']-$removed;

	// Return the good news
	return $activities;
}
?>