<?php

/*******************
	Load JS Scripts & CSS Styles
*******************/

// Load the Javscript and CSS files.
function iwmp_load_styles() {

	trm_enqueue_style( 'iwmp-styles', plugins_url( '/assets/magnific-popup.css', __FILE__ ) );
	
	// Load JS in footer
	trm_enqueue_script( 'iwmp-scripts', plugins_url( '/assets/jquery.magnific-popup.min.js', __FILE__ ), array( 'jquery' ), '1.0', true );
	//trm_enqueue_script('jquery_resize',plugins_url('assets/jquery.resize.js',__FILE__));
	
}

add_action( 'trm_enqueue_scripts', 'iwmp_load_styles' );

?>