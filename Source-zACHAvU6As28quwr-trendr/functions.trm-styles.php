<?php
/**
 * BackPress styles procedural API.
 *
 * @package BackPress
 * @since r79
 */

/**
 * Display styles that are in the queue or part of $handles.
 *
 * @since r79
 * @uses do_action() Calls 'trm_print_styles' hook.
 * @global object $trm_styles The TRM_Styles object for printing styles.
 *
 * @param array|bool $handles Styles to be printed. An empty array prints the queue,
 *  an array with one string prints that style, and an array of strings prints those styles.
 * @return bool True on success, false on failure.
 */
function trm_print_styles( $handles = false ) {
	do_action( 'trm_print_styles' );
	if ( '' === $handles ) // for trm_head
		$handles = false;

	global $trm_styles;
	if ( !is_a($trm_styles, 'TRM_Styles') ) {
		if ( !$handles )
			return array(); // No need to instantiate if nothing's there.
		else
			$trm_styles = new TRM_Styles();
	}

	return $trm_styles->do_items( $handles );
}

/**
 * Register CSS style file.
 *
 * @since r79
 * @see TRM_Styles::add() For additional information.
 * @global object $trm_styles The TRM_Styles object for printing styles.
 * @link http://www.w3.org/TR/CSS2/media.html#media-types List of CSS media types.
 *
 * @param string $handle Name of the stylesheet.
 * @param string|bool $src Path to the stylesheet from the root directory of Trnder. Example: '/css/mystyle.css'.
 * @param array $deps Array of handles of any stylesheet that this stylesheet depends on.
 *  (Stylesheets that must be loaded before this stylesheet.) Pass an empty array if there are no dependencies.
 * @param string|bool $ver String specifying the stylesheet version number. Set to NULL to disable.
 *  Used to ensure that the correct version is sent to the client regardless of caching.
 * @param string $media The media for which this stylesheet has been defined.
 */
function trm_register_style( $handle, $src, $deps = array(), $ver = false, $media = 'all' ) {
	global $trm_styles;
	if ( !is_a($trm_styles, 'TRM_Styles') )
		$trm_styles = new TRM_Styles();

	$trm_styles->add( $handle, $src, $deps, $ver, $media );
}

/**
 * Remove a registered CSS file.
 *
 * @since r79
 * @see TRM_Styles::remove() For additional information.
 * @global object $trm_styles The TRM_Styles object for printing styles.
 *
 * @param string $handle Name of the stylesheet.
 */
function trm_deregister_style( $handle ) {
	global $trm_styles;
	if ( !is_a($trm_styles, 'TRM_Styles') )
		$trm_styles = new TRM_Styles();

	$trm_styles->remove( $handle );
}

/**
 * Enqueue a CSS style file.
 *
 * Registers the style if src provided (does NOT overwrite) and enqueues.
 *
 * @since r79
 * @see TRM_Styles::add(), TRM_Styles::enqueue()
 * @global object $trm_styles The TRM_Styles object for printing styles.
 * @link http://www.w3.org/TR/CSS2/media.html#media-types List of CSS media types.
 *
 * @param string $handle Name of the stylesheet.
 * @param string|bool $src Path to the stylesheet from the root directory of Trnder. Example: '/css/mystyle.css'.
 * @param array $deps Array of handles (names) of any stylesheet that this stylesheet depends on.
 *  (Stylesheets that must be loaded before this stylesheet.) Pass an empty array if there are no dependencies.
 * @param string|bool $ver String specifying the stylesheet version number, if it has one. This parameter
 *  is used to ensure that the correct version is sent to the client regardless of caching, and so should be included
 *  if a version number is available and makes sense for the stylesheet.
 * @param string $media The media for which this stylesheet has been defined.
 */
function trm_enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
	global $trm_styles;
	if ( !is_a($trm_styles, 'TRM_Styles') )
		$trm_styles = new TRM_Styles();

	if ( $src ) {
		$_handle = explode('?', $handle);
		$trm_styles->add( $_handle[0], $src, $deps, $ver, $media );
	}
	$trm_styles->enqueue( $handle );
}

/**
 * Remove an enqueued style.
 *
 * @since TRM 3.1
 * @see TRM_Styles::dequeue() For parameter information.
 */
function trm_dequeue_style( $handle ) {
	global $trm_styles;
	if ( !is_a($trm_styles, 'TRM_Styles') )
		$trm_styles = new TRM_Styles();

	$trm_styles->dequeue( $handle );
}

/**
 * Check whether style has been added to Trnder Styles.
 *
 * The values for list defaults to 'queue', which is the same as trm_enqueue_style().
 *
 * @since TRM unknown; TRS unknown
 * @global object $trm_styles The TRM_Styles object for printing styles.
 *
 * @param string $handle Name of the stylesheet.
 * @param string $list Values are 'registered', 'done', 'queue' and 'to_do'.
 * @return bool True on success, false on failure.
 */
function trm_style_is( $handle, $list = 'queue' ) {
	global $trm_styles;
	if ( !is_a($trm_styles, 'TRM_Styles') )
		$trm_styles = new TRM_Styles();

	$query = $trm_styles->query( $handle, $list );

	if ( is_object( $query ) )
		return true;

	return $query;
}
