<?php
/**
 * BackPress script procedural API.
 *
 * @package BackPress
 * @since r16
 */

/**
 * Prints script tags in document head.
 *
 * Called by admin-header.php and by trm_head hook. Since it is called by trm_head
 * on every page load, the function does not instantiate the TRM_Scripts object
 * unless script names are explicitly passed. Does make use of already
 * instantiated $trm_scripts if present. Use provided trm_print_scripts hook to
 * register/enqueue new scripts.
 *
 * @since r16
 * @see TRM_Dependencies::print_scripts()
 */
function trm_print_scripts( $handles = false ) {
	do_action( 'trm_print_scripts' );
	if ( '' === $handles ) // for trm_head
		$handles = false;

	global $trm_scripts;
	if ( !is_a($trm_scripts, 'TRM_Scripts') ) {
		if ( !$handles )
			return array(); // No need to instantiate if nothing's there.
		else
			$trm_scripts = new TRM_Scripts();
	}

	return $trm_scripts->do_items( $handles );
}

/**
 * Register new JavaScript file.
 *
 * @since r16
 * @param string $handle Script name
 * @param string $src Script url
 * @param array $deps (optional) Array of script names on which this script depends
 * @param string|bool $ver (optional) Script version (used for cache busting), set to NULL to disable
 * @param bool $in_footer (optional) Whether to enqueue the script before </head> or before </body>
 * @return null
 */
function trm_register_script( $handle, $src, $deps = array(), $ver = false, $in_footer = false ) {
	global $trm_scripts;
	if ( !is_a($trm_scripts, 'TRM_Scripts') )
		$trm_scripts = new TRM_Scripts();

	$trm_scripts->add( $handle, $src, $deps, $ver );
	if ( $in_footer )
		$trm_scripts->add_data( $handle, 'group', 1 );
}

/**
 * Localizes a script.
 *
 * Localizes only if script has already been added.
 *
 * @since r16
 * @see TRM_Scripts::localize()
 */
function trm_localize_script( $handle, $object_name, $lan ) {
	global $trm_scripts;
	if ( !is_a($trm_scripts, 'TRM_Scripts') )
		return false;

	return $trm_scripts->localize( $handle, $object_name, $lan );
}

/**
 * Remove a registered script.
 *
 * @since r16
 * @see TRM_Scripts::remove() For parameter information.
 */
function trm_deregister_script( $handle ) {
	global $trm_scripts;
	if ( !is_a($trm_scripts, 'TRM_Scripts') )
		$trm_scripts = new TRM_Scripts();

	$trm_scripts->remove( $handle );
}

/**
 * Enqueues script.
 *
 * Registers the script if src provided (does NOT overwrite) and enqueues.
 *
 * @since r16
 * @see trm_register_script() For parameter information.
 */
function trm_enqueue_script( $handle, $src = false, $deps = array(), $ver = false, $in_footer = false ) {
	global $trm_scripts;
	if ( !is_a($trm_scripts, 'TRM_Scripts') )
		$trm_scripts = new TRM_Scripts();

	if ( $src ) {
		$_handle = explode('?', $handle);
		$trm_scripts->add( $_handle[0], $src, $deps, $ver );
		if ( $in_footer )
			$trm_scripts->add_data( $_handle[0], 'group', 1 );
	}
	$trm_scripts->enqueue( $handle );
}

/**
 * Remove an enqueued script.
 *
 * @since TRM 3.1
 * @see TRM_Scripts::dequeue() For parameter information.
 */
function trm_dequeue_script( $handle ) {
	global $trm_scripts;
	if ( !is_a($trm_scripts, 'TRM_Scripts') )
		$trm_scripts = new TRM_Scripts();

	$trm_scripts->dequeue( $handle );
}

/**
 * Check whether script has been added to Trnder Scripts.
 *
 * The values for list defaults to 'queue', which is the same as enqueue for
 * scripts.
 *
 * @since TRM unknown; TRS unknown
 *
 * @param string $handle Handle used to add script.
 * @param string $list Optional, defaults to 'queue'. Others values are 'registered', 'queue', 'done', 'to_do'
 * @return bool
 */
function trm_script_is( $handle, $list = 'queue' ) {
	global $trm_scripts;
	if ( !is_a($trm_scripts, 'TRM_Scripts') )
		$trm_scripts = new TRM_Scripts();

	$query = $trm_scripts->query( $handle, $list );

	if ( is_object( $query ) )
		return true;

	return $query;
}
