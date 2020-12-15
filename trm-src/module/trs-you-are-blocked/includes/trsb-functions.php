<?php
if ( !defined( 'TRSB_VERSION' ) ) exit;


/**
 * Get Blocked Users
 * Returns a given users or the current users list of blocked users.
 * @filter trsb_get_blocked_users
 * @since 1.0
 * @version 1.0
 */
function trsb_get_blocked_users( $user_id = NULL ) {
	if ( $user_id === NULL ) return;
// var_dump($user_id === trs_loggedin_user_id());
	// if ( $user_id === trs_loggedin_user_id()  )
	// 	$list = trm_cache_get( 'trsb', 'trsb_my_block_list' );
	// else
		$list = get_user_meta( $user_id, '_block', true );

	if ( empty( $list ) )
		$list = array();
else
$list = array_unique($list);

	$_list = apply_filters( 'trsb_get_blocked_users', $list, $user_id );
	return array_filter( $_list );
}

/**
 * Get Block Link
 * Returns the link to add a user to the current users blocked list.
 * @filter trsb_block_link
 * @since 1.0
 * @version 1.0
 */
function trsb_block_link( $list_id = 0, $user_id = 0 ) {
	return apply_filters( 'trsb_block_link', add_query_arg( array(
		'action' => 'block',
		'list'   => $list_id,
		'num'    => $user_id,
		'token'  => trm_create_nonce( 'block-' . $list_id )
	) ), $list_id, $user_id );
}

/**
 * Get Unblock Link
 * Returns the link to remove a user from the current users block list.
 * @filter trsb_unblock_link
 * @since 1.0
 * @version 1.0
 */
function trsb_unblock_link( $list_id = 0, $user_id = 0 ) {
	return apply_filters( 'trsb_unblock_link', add_query_arg( array(
		'action' => 'unblock',
		'list'   => $list_id,
		'num'    => $user_id,
		'token'  => trm_create_nonce( 'unblock-' . $list_id )
	) ), $list_id, $user_id );
}

/**
 * Remove ID from List
 * Removes a given ID from a users block list.
 * @filter trsb_remove_user_from_list
 * @since 1.0
 * @version 1.0
 */
function trsb_remove_user_from_list( $list_id = NULL, $id_to_remove = NULL ) {
	$current = trsb_get_blocked_users( $list_id );
	$new = array();
	foreach ( (array) $current as $user_id ) {
		if ( $user_id != $id_to_remove )
			$new[] = $user_id;
	}
	update_user_meta( $list_id, '_block', apply_filters( 'trsb_remove_user_from_list', $new, $list_id, $id_to_remove ) );
}
?>
