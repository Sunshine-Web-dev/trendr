<?php
if ( !defined( 'TRSB_VERSION' ) ) exit;

/**
 * Load Blocked Profile Template
 * @since 1.0
 * @version 1.0
 */
function trsb_load_blocked_profile_templates() {
	if ( !is_user_logged_in() ) return;
	
	// Step 1 - Check if the current user is blocking the profile viewed
	$list = trsb_get_blocked_users( trs_loggedin_user_id()  );
	if ( !empty( $list ) && in_array( trs_displayed_user_id(), $list ) ) {
		$template = locate_template( 'members/single/blocked.php', false );
		if ( empty( $template ) )
			load_template( TRSB_TEMPLATE_DIR . '/blocked.php' );
		else
			trs_core_load_template( 'members/single/blocked' );

		exit;
	}

	// Step 2 - Check if the current profile is blocking the current user
	$_list = trsb_get_blocked_users( trs_displayed_user_id() );
	if ( !empty( $_list ) && in_array( trs_loggedin_user_id() , $_list ) ) {
		$template = locate_template( 'members/single/blocking.php', false );
		if ( empty( $template ) )
			load_template( TRSB_TEMPLATE_DIR . '/blocking.php' );
		else
			trs_core_load_template( 'members/single/blocking' );

		exit;
	}
}

function trsb_core_screen_profite() {
	if ( ! trs_is_user() || !is_user_logged_in() ) return;
	if ( trs_is_active( 'activity' ) && trs_is_single_activity() )
		return;

	// Step 1 - Check if the current user is blocking the profile viewed
	$list = trsb_get_blocked_users( trs_loggedin_user_id()  );
	if ( !empty( $list ) && in_array( trs_displayed_user_id(), $list ) ) {
		$template = locate_template( 'members/single/blocked.php', false );
		if ( empty( $template ) )
			load_template( TRSB_TEMPLATE_DIR . '/blocked.php' );
		else
			trs_core_load_template( 'members/single/blocked' );

		exit;
	}

	// Step 2 - Check if the current profile is blocking the current user
	$_list = trsb_get_blocked_users( trs_displayed_user_id() );
	if ( !empty( $_list ) && in_array( trs_loggedin_user_id() , $_list ) ) {
		$template = locate_template( 'members/single/blocking.php', false );
		if ( empty( $template ) )
			load_template( TRSB_TEMPLATE_DIR . '/blocking.php' );
		else
			trs_core_load_template( 'members/single/blocking' );

		exit;
	}
}
?>