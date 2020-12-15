<?php
/**
 * Admin Bar
 *
 * This code handles the building and rendering of the press bar.
 */

function _trm_admin_bar_init() {
	global $trm_admin_bar;

}
add_action( 'init', '_trm_admin_bar_init' ); // Don't remove. Wrong way to disable.

function show_admin_bar( $show ) {
	global $show_admin_bar;
	$show_admin_bar = (bool) $show;
}


function is_admin_bar_showing() {
	global $show_admin_bar, $pagenow;

}


?>