<?php
/*
Plugin name: TRS Mega Populate
Description: Creates tons of TRS dummy data for performance testing
Author: Boone B Gorges
License: GPLv2
*/

/**
 * Only load if TRS is present
 */
function trsmp_load() {
	include( dirname( __FILE__ ) . '/trs-mega-populate.php' );
}
add_action( 'trs_include', 'trsmp_load' );

?>
