<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function xprofile_add_admin_css() {
	if ( !empty( $_GET['page'] ) && strpos( $_GET['page'], 'trs-profile-setup' ) !== false ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			trm_enqueue_style( 'xprofile-admin-css', TRS_PLUGIN_URL . '/trs-xprofile/admin/css/admin.dev.css', array(), '20110723' );
		else
			trm_enqueue_style( 'xprofile-admin-css', TRS_PLUGIN_URL . '/trs-xprofile/admin/css/admin.css', array(), '20110723' );
	}
}
add_action( trs_core_admin_hook(), 'xprofile_add_admin_css' );

function xprofile_add_admin_js() {
	if ( !empty( $_GET['page'] ) && strpos( $_GET['page'], 'trs-profile-setup' ) !== false ) {
		trm_enqueue_script( 'jquery-ui-core' );
		trm_enqueue_script( 'jquery-ui-tabs' );
		trm_enqueue_script( 'jquery-ui-mouse' );
		trm_enqueue_script( 'jquery-ui-draggable' );
		trm_enqueue_script( 'jquery-ui-droppable' );
		trm_enqueue_script( 'jquery-ui-sortable' );

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			trm_enqueue_script( 'xprofile-admin-js', TRS_PLUGIN_URL . '/trs-xprofile/admin/js/admin.dev.js', array( 'jquery', 'jquery-ui-sortable' ), '20110723' );
		else
			trm_enqueue_script( 'xprofile-admin-js', TRS_PLUGIN_URL . '/trs-xprofile/admin/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), '20110723' );
	}
}
add_action( trs_core_admin_hook(), 'xprofile_add_admin_js', 1 );
?>