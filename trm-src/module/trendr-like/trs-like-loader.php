<?php

/*
  Plugin Name: trendr Like
  Plugin URI: http://darrenmeehan.me/
  Description: Adds the ability for users to like content throughout your trendr site.
  Author: Darren Meehan
  Version: 0.3.0
  Author URI: http://darrenmeehan.me
  Text Domain: trendr-like

  Credit: The original plugin was built by Alex Hempton-Smith who did a great job. I hope he's in good
  health and enjoying life.
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/* Only load trendr Like once trendr has loaded and been initialized. */
function trslike_init() {
  // Because we will be using TRS_Component, we require trendr 1.5 or greater.
  if ( version_compare( TRS_VERSION, '1.5', '>' ) ) {
    require_once 'includes/trslike.php';
  }
}

add_action( 'trs_include' , 'trslike_init' );
