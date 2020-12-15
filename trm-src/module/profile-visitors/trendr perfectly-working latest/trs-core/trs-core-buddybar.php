<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Adds a navigation item to the main navigation array used in trendr themes.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 */
function trs_core_new_nav_item( $args = '' ) {
	global $trs;

	$defaults = array(
		'name'                    => false, // Display name for the nav item
		'slug'                    => false, // URL slug for the nav item
		'item_css_id'             => false, // The CSS ID to apply to the HTML of the nav item
		'show_for_displayed_user' => true,  // When viewing another user does this nav item show up?
		'site_admin_only'         => false, // Can only site admins see this nav item?
		'position'                => 99,    // Index of where this nav item should be positioned
		'screen_function'         => false, // The name of the function to run when clicked
		'default_subnav_slug'     => false  // The slug of the default subnav item to select when clicked
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	// If we don't have the required info we need, don't create this subnav item
	if ( empty( $name ) || empty( $slug ) )
		return false;

	// If this is for site admins only and the user is not one, don't create the subnav item
	if ( $site_admin_only && !is_super_admin() )
		return false;

	if ( empty( $item_css_id ) )
		$item_css_id = $slug;

	$trs->trs_nav[$slug] = array(
		'name'                    => $name,
		'slug'                    => $slug,
		'link'                    => $trs->loggedin_user->domain . $slug . '/',
		'css_id'                  => $item_css_id,
		'show_for_displayed_user' => $show_for_displayed_user,
		'position'                => $position,
		'screen_function'         => &$screen_function
	);

 	/***
	 * If this nav item is hidden for the displayed user, and
	 * the logged in user is not the displayed user
	 * looking at their own profile, don't create the nav item.
	 */
	if ( !$show_for_displayed_user && !trs_user_has_access() )
		return false;

	/***
 	 * If the nav item is visible, we are not viewing a user, and this is a root
	 * component, don't attach the default subnav function so we can display a
	 * directory or something else.
 	 */
	if ( ( -1 != $position ) && trs_is_root_component( $slug ) && !trs_displayed_user_id() )
		return;

	// Look for current component
	if ( trs_is_current_component( $slug ) && !trs_current_action() ) {
		if ( !is_object( $screen_function[0] ) )
			add_action( 'trs_screens', $screen_function );
		else
			add_action( 'trs_screens', array( &$screen_function[0], $screen_function[1] ), 3 );

		if ( !empty( $default_subnav_slug ) )
			$trs->current_action = apply_filters( 'trs_default_component_subnav', $default_subnav_slug, $r );

	// Look for current item
	} elseif ( trs_is_current_item( $slug ) && !trs_current_action() ) {
		if ( !is_object( $screen_function[0] ) )
			add_action( 'trs_screens', $screen_function );
		else
			add_action( 'trs_screens', array( &$screen_function[0], $screen_function[1] ), 3 );

		if ( !empty( $default_subnav_slug ) )
			$trs->current_action = apply_filters( 'trs_default_component_subnav', $default_subnav_slug, $r );
	}

	do_action( 'trs_core_new_nav_item', $r, $args, $defaults );
}

/**
 * Modify the default subnav item to load when a top level nav item is clicked.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 */
function trs_core_new_nav_default( $args = '' ) {
	global $trs;

	$defaults = array(
		'parent_slug'     => false, // Slug of the parent
		'screen_function' => false, // The name of the function to run when clicked
		'subnav_slug'     => false  // The slug of the subnav item to select when clicked
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( $function = $trs->trs_nav[$parent_slug]['screen_function'] ) {
		if ( !is_object( $function[0] ) )
			remove_action( 'trs_screens', $function, 3 );
		else
			remove_action( 'trs_screens', array( &$function[0], $function[1] ), 3 );
	}

	$trs->trs_nav[$parent_slug]['screen_function'] =$screen_function;

	if ( $trs->current_component == $parent_slug && !$trs->current_action ) {
		if ( !is_object( $screen_function[0] ) )
			add_action( 'trs_screens', $screen_function );
		else
			add_action( 'trs_screens', array( &$screen_function[0], $screen_function[1] ) );

		if ( $subnav_slug )
			$trs->current_action = $subnav_slug;
	}
}

/**
 * We can only sort nav items by their position integer at a later point in time, once all
 * plugins have registered their navigation items.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 */
function trs_core_sort_nav_items() {
	global $trs;

	if ( empty( $trs->trs_nav ) || !is_array( $trs->trs_nav ) )
		return false;

	foreach ( (array)$trs->trs_nav as $slug => $nav_item ) {
		if ( empty( $temp[$nav_item['position']]) )
			$temp[$nav_item['position']] = $nav_item;
		else {
			// increase numbers here to fit new items in.
			do {
				$nav_item['position']++;
			} while ( !empty( $temp[$nav_item['position']] ) );

			$temp[$nav_item['position']] = $nav_item;
		}
	}

	ksort( $temp );
	$trs->trs_nav =$temp;
}
add_action( 'trm_head',    'trs_core_sort_nav_items' );
add_action( 'admin_head', 'trs_core_sort_nav_items' );

/**
 * Adds a navigation item to the sub navigation array used in trendr themes.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 */
function trs_core_new_subnav_item( $args = '' ) {
	global $trs;

	$defaults = array(
		'name'            => false, // Display name for the nav item
		'slug'            => false, // URL slug for the nav item
		'parent_slug'     => false, // URL slug of the parent nav item
		'parent_url'      => false, // URL of the parent item
		'item_css_id'     => false, // The CSS ID to apply to the HTML of the nav item
		'user_has_access' => true,  // Can the logged in user see this nav item?
		'site_admin_only' => false, // Can only site admins see this nav item?
		'position'        => 90,    // Index of where this nav item should be positioned
		'screen_function' => false, // The name of the function to run when clicked
		'link'            => ''     // The link for the subnav item; optional, not usually required.
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	// If we don't have the required info we need, don't create this subnav item
	if ( empty( $name ) || empty( $slug ) || empty( $parent_slug ) || empty( $parent_url ) || empty( $screen_function ) )
		return false;

	if ( empty( $link ) )
		$link = $parent_url . $slug;

	// If this is for site admins only and the user is not one, don't create the subnav item
	if ( $site_admin_only && !is_super_admin() )
		return false;

	if ( empty( $item_css_id ) )
		$item_css_id = $slug;

	$trs->trs_options_nav[$parent_slug][$slug] = array(
		'name'            => $name,
		'link'            => trailingslashit( $link ),
		'slug'            => $slug,
		'css_id'          => $item_css_id,
		'position'        => $position,
		'user_has_access' => $user_has_access,
		'screen_function' => &$screen_function
	);

	/**
	 * The last step is to hook the screen function for the added subnav item. But this only
	 * needs to be done if this subnav item is the current view, and the user has access to the
	 * subnav item. We figure out whether we're currently viewing this subnav by checking the
	 * following two conditions:
	 *   (1) Either:
	 *	 (a) the parent slug matches the current_component, or
	 *	 (b) the parent slug matches the current_item
	 *   (2) And either:
	 * 	 (a) the current_action matches $slug, or
	 *       (b) there is no current_action (ie, this is the default subnav for the parent nav)
	 *	     and this subnav item is the default for the parent item (which we check by
	 *	     comparing this subnav item's screen function with the screen function of the
	 *	     parent nav item in $trs->trs_nav). This condition only arises when viewing a
	 *	     user, since groups should always have an action set.
	 */

	// If we *don't* meet condition (1), return
	if ( $trs->current_component != $parent_slug && $trs->current_item != $parent_slug )
		return;

	// If we *do* meet condition (2), then the added subnav item is currently being requested
	if ( ( !empty( $trs->current_action ) && $slug == $trs->current_action ) || ( trs_is_user() && empty( $trs->current_action ) && $screen_function == $trs->trs_nav[$parent_slug]['screen_function'] ) ) {

		// Before hooking the screen function, check user access
		if ( $user_has_access ) {
			if ( !is_object( $screen_function[0] ) )
				add_action( 'trs_screens', $screen_function );
			else
				add_action( 'trs_screens', array( &$screen_function[0], $screen_function[1] ) );
		} else {
			// When the content is off-limits, we handle the situation differently
			// depending on whether the current user is logged in
			if ( is_user_logged_in() ) {
				// Off-limits to this user. Throw an error and redirect to the displayed user's domain
				trs_core_no_access( array(
					'message'  => __( 'You do not have access to this page.', 'trendr' ),
					'root'     => trs_displayed_user_domain(),
					'redirect' => false
				) );
			} else {
				// Not logged in. Allow the user to log in, and attempt to redirect
				trs_core_no_access();
			}
		}
	}
}

function trs_core_sort_subnav_items() {
	global $trs;

	if ( empty( $trs->trs_options_nav ) || !is_array( $trs->trs_options_nav ) )
		return false;

	foreach ( (array)$trs->trs_options_nav as $parent_slug => $subnav_items ) {
		if ( !is_array( $subnav_items ) )
			continue;

		foreach ( (array)$subnav_items as $subnav_item ) {
			if ( empty( $temp[$subnav_item['position']]) )
				$temp[$subnav_item['position']] = $subnav_item;
			else {
				// increase numbers here to fit new items in.
				do {
					$subnav_item['position']++;
				} while ( !empty( $temp[$subnav_item['position']] ) );

				$temp[$subnav_item['position']] = $subnav_item;
			}
		}
		ksort( $temp );
		$trs->trs_options_nav[$parent_slug] =$temp;
		unset( $temp );
	}
}
add_action( 'trm_head',    'trs_core_sort_subnav_items' );
add_action( 'admin_head', 'trs_core_sort_subnav_items' );



// **** "Notifications" Menu *********
function trs_adminbar_notifications_menu() {
	global $trs;

	if ( !is_user_logged_in() )
		return false;

	echo '<li id="notify"><a href="' . $trs->loggedin_user->domain . '">';
	_e( '', 'trendr' );

	if ( $notifications = trs_core_get_notifications_for_user( $trs->loggedin_user->id ) ) { ?>
		<span><?php echo count( $notifications ) ?></span>
	<?php
	}	echo '<dl>';


	echo '</a>';

	if ( $notifications ) {
		$counter = 0;
		for ( $i = 0; $i < count($notifications); $i++ ) {
			$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : ''; ?>

			<li<?php echo $alt ?>><?php echo $notifications[$i] ?></li>

			<?php $counter++;
		}

	}

	
}
add_action( 'trs_adminbar_menus', 'trs_adminbar_notifications_menu', 8 );


?>