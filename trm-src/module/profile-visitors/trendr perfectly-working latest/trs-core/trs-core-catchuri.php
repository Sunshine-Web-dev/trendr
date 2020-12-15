<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*
Based on contributions from: Chris Taylor - http://www.stillbreathing.co.uk/
Modified for trendr by: Andy Peatling - http://apeatling.trendr.com/
*/

/**
 * Analyzes the URI structure and breaks it down into parts for use in code.
 * The idea is that trendr can use complete custom friendly URI's without the
 * user having to add new re-write rules.
 *
 * Future custom components would then be able to use their own custom URI structure.
 *
 * @package trendr Core
 * @since trendr (r100)
 *
 * The URI's are broken down as follows:
 *   - http:// domain.com / members / andy / [current_component] / [current_action] / [action_variables] / [action_variables] / ...
 *   - OUTSIDE ROOT: http:// domain.com / sites / trendr / members / andy / [current_component] / [current_action] / [action_variables] / [action_variables] / ...
 *
 *	Example:
 *    - http://domain.com/members/andy/profile/edit/group/5/
 *    - $trs->current_component: string 'xprofile'
 *    - $trs->current_action: string 'edit'
 *    - $trs->action_variables: array ['group', 5]
 *
 */
function trs_core_set_uri_globals() {
	global $trs, $trs_unfiltered_uri, $trs_unfiltered_uri_offset;
	global $current_blog, $trmdb;

	// Create global component, action, and item variables
	$trs->current_component = $trs->current_action = $trs->current_item ='';
	$trs->action_variables = $trs->displayed_user->id = '';

	// Don't catch URIs on non-root blogs unless multiblog mode is on
	if ( !trs_is_root_blog() && !trs_is_multiblog_mode() )
		return false;

	// Fetch all the TRM page names for each component
	if ( empty( $trs->pages ) )
		$trs->pages = trs_core_get_directory_pages();

	// Ajax or not?
	if ( strpos( $_SERVER['REQUEST_URI'], 'initiate.php' ) )
		$path = trs_core_referrer();
	else
		$path = esc_url( $_SERVER['REQUEST_URI'] );

	// Filter the path
	$path = apply_filters( 'trs_uri', $path );

	// Take GET variables off the URL to avoid problems,
	// they are still registered in the global $_GET variable
	if ( $noget = substr( $path, 0, strpos( $path, '?' ) ) )
		$path = $noget;

	// Fetch the current URI and explode each part separated by '/' into an array
	$trs_uri = explode( '/', $path );

	// Loop and remove empties
	foreach ( (array)$trs_uri as $key => $uri_chunk )
		if ( empty( $trs_uri[$key] ) ) unset( $trs_uri[$key] );

	// Running off blog other than root
	if ( is_multisite() && !is_subdomain_install() && ( trs_is_multiblog_mode() || 1 != trs_get_root_blog_id() ) ) {

		// Any subdirectory names must be removed from $trs_uri.
		// This includes two cases: (1) when TRM is installed in a subdirectory,
		// and (2) when TRS is running on secondary blog of a subdirectory
		// multisite installation. Phew!
		if ( $chunks = explode( '/', $current_blog->path ) ) {
			foreach( $chunks as $key => $chunk ) {
				$bkey = array_search( $chunk, $trs_uri );

				if ( $bkey !== false ) {
					unset( $trs_uri[$bkey] );
				}

				$trs_uri = array_values( $trs_uri );
			}
		}
	}

	// Set the indexes, these are incresed by one if we are not on a VHOST install
	$component_index = 0;
	$action_index    = $component_index + 1;

	// Get site path items
	$paths = explode( '/', trs_core_get_site_path() );

	// Take empties off the end of path
	if ( empty( $paths[count( $paths ) - 1] ) )
		array_pop( $paths );

	// Take empties off the start of path
	if ( empty( $paths[0] ) )
		array_shift( $paths );

	// Unset URI indices if they intersect with the paths
	foreach ( (array) $trs_uri as $key => $uri_chunk ) {
		if ( in_array( $uri_chunk, $paths ) ) {
			unset( $trs_uri[$key] );
		}
	}

	// Reset the keys by merging with an empty array
	$trs_uri = array_merge( array(), $trs_uri );

	// If a component is set to the front page, force its name into $trs_uri
	// so that $current_component is populated (unless a specific TRM post is being requested
	// via a URL parameter, usually signifying Preview mode)
	if ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) && empty( $trs_uri ) && empty( $_GET['p'] ) && empty( $_GET['page_id'] ) ) {
		$post = get_post( get_option( 'page_on_front' ) );
		if ( !empty( $post ) ) {
			$trs_uri[0] = $post->post_name;
		}
	}

	// Keep the unfiltered URI safe
	$trs_unfiltered_uri = $trs_uri;

	// Get slugs of pages into array
	foreach ( (array) $trs->pages as $page_key => $trs_page )
		$key_slugs[$page_key] = trailingslashit( '/' . $trs_page->slug );

	// Bail if keyslugs are empty, as TRS is not setup correct
	if ( empty( $key_slugs ) )
		return;

	// Loop through page slugs and look for exact match to path
	foreach ( $key_slugs as $key => $slug ) {
		if ( $slug == $path ) {
			$match      = $trs->pages->{$key};
			$match->key = $key;
			$matches[]  = 1;
			break;
		}
	}

	// No exact match, so look for partials
	if ( empty( $match ) ) {

		// Loop through each page in the $trs->pages global
		foreach ( (array) $trs->pages as $page_key => $trs_page ) {

			// Look for a match (check members first)
			if ( in_array( $trs_page->name, (array) $trs_uri ) ) {

				// Match found, now match the slug to make sure.
				$uri_chunks = explode( '/', $trs_page->slug );

				// Loop through uri_chunks
				foreach ( (array) $uri_chunks as $key => $uri_chunk ) {

					// Make sure chunk is in the correct position
					if ( !empty( $trs_uri[$key] ) && ( $trs_uri[$key] == $uri_chunk ) ) {
						$matches[] = 1;

					// No match
					} else {
						$matches[] = 0;
					}
				}

				// Have a match
				if ( !in_array( 0, (array) $matches ) ) {
					$match      = $trs_page;
					$match->key = $page_key;
					break;
				};

				// Unset matches
				unset( $matches );
			}

			// Unset uri chunks
			unset( $uri_chunks );
		}
	}

	// URLs with TRS_ENABLE_ROOT_PROFILES enabled won't be caught above
	if ( empty( $matches ) && defined( 'TRS_ENABLE_ROOT_PROFILES' ) && TRS_ENABLE_ROOT_PROFILES ) {

		// Make sure there's a user corresponding to $trs_uri[0]
		if ( !empty( $trs->pages->members ) && !empty( $trs_uri[0] ) && $root_profile = get_user_by( 'login', $trs_uri[0] ) ) {

			// Force TRS to recognize that this is a members page
			$matches[]  = 1;
			$match      = $trs->pages->members;
			$match->key = 'members';

			// Without the 'members' URL chunk, trendr won't know which page to load
			// This filter intercepts the TRM query and tells it to load the members page
			add_filter( 'request', create_function( '$query_args', '$query_args["pagename"] = "' . $match->name . '"; return $query_args;' ) );
		}
	}

	// Search doesn't have an associated page, so we check for it separately
	if ( !empty( $trs_uri[0] ) && ( trs_get_search_slug() == $trs_uri[0] ) ) {
		$matches[]   = 1;
		$match       = new stdClass;
		$match->key  = 'search';
		$match->slug = trs_get_search_slug();
	}

	// This is not a trendr page, so just return.
	if ( !isset( $matches ) )
		return false;

	// Find the offset. With $root_profile set, we fudge the offset down so later parsing works
	$slug       = !empty ( $match ) ? explode( '/', $match->slug ) : '';
	$uri_offset = empty( $root_profile ) ? 0 : -1;

	// Rejig the offset
	if ( !empty( $slug ) && ( 1 < count( $slug ) ) ) {
		array_pop( $slug );
		$uri_offset = count( $slug );
	}

	// Global the unfiltered offset to use in trs_core_load_template().
	// To avoid PHP warnings in trs_core_load_template(), it must always be >= 0
	$trs_unfiltered_uri_offset = $uri_offset >= 0 ? $uri_offset : 0;

	// We have an exact match
	if ( isset( $match->key ) ) {

		// Set current component to matched key
		$trs->current_component = $match->key;

		// If members component, do more work to find the actual component
		if ( 'members' == $match->key ) {

			// Viewing a specific user
			if ( !empty( $trs_uri[$uri_offset + 1] ) ) {

				// Switch the displayed_user based on compatbility mode
				if ( trs_is_username_compatibility_mode() )
					$trs->displayed_user->id = (int) trs_core_get_userid( urldecode( $trs_uri[$uri_offset + 1] ) );
				else
					$trs->displayed_user->id = (int) trs_core_get_userid_from_nicename( urldecode( $trs_uri[$uri_offset + 1] ) );

				if ( empty( $trs->displayed_user->id ) ) {
					// Prevent components from loading their templates
					$trs->current_component = '';

					trs_do_404();
					return;
				}

				// If the displayed user is marked as a spammer, 404 (unless logged-
				// in user is a super admin)
				if ( !empty( $trs->displayed_user->id ) && trs_core_is_user_spammer( $trs->displayed_user->id ) ) {
					if ( is_super_admin() ) {
						trs_core_add_message( __( 'This user has been marked as a spammer. Only site admins can view this profile.', 'trendr' ), 'error' );
					} else {
						trs_do_404();
						return;
					}
				}

				// Bump the offset
				if ( isset( $trs_uri[$uri_offset + 2] ) ) {
					$trs_uri                = array_merge( array(), array_slice( $trs_uri, $uri_offset + 2 ) );
					$trs->current_component = $trs_uri[0];

				// No component, so default will be picked later
				} else {
					$trs_uri                = array_merge( array(), array_slice( $trs_uri, $uri_offset + 2 ) );
					$trs->current_component = '';
				}

				// Reset the offset
				$uri_offset = 0;
			}
		}
	}

	// Set the current action
	$trs->current_action = isset( $trs_uri[$uri_offset + 1] ) ? $trs_uri[$uri_offset + 1] : '';

	// Slice the rest of the $trs_uri array and reset offset
	$trs_uri      = array_slice( $trs_uri, $uri_offset + 2 );
	$uri_offset  = 0;

	// Set the entire URI as the action variables, we will unset the current_component and action in a second
	$trs->action_variables = $trs_uri;

	// Remove the username from action variables if this is not a VHOST install
	// @todo - move or remove this all together
	if ( defined( 'VHOST' ) && ( 'no' == VHOST ) && empty( $trs->current_component ) )
		array_shift( $trs_uri );

	// Reset the keys by merging with an empty array
	$trs->action_variables = array_merge( array(), $trs->action_variables );
}

/**
 * trs_core_load_template()
 *
 * Load a specific template file with fallback support.
 *
 * Example:
 *   trs_core_load_template( 'members/index' );
 * Loads:
 *   trm-src/858583/[activated_theme]/members/index.php
 *
 * @package trendr Core
 * @param $username str Username to check.
 * @return false|int The user ID of the matched user, or false.
 */
function trs_core_load_template( $templates ) {
	global $post, $trs, $trmdb, $trm_query, $trs_unfiltered_uri, $trs_unfiltered_uri_offset;

	// Determine if the root object TRM page exists for this request (TODO: is there an API function for this?
	if ( !empty( $trs_unfiltered_uri_offset ) && !$page_exists = $trmdb->get_var( $trmdb->prepare( "SELECT ID FROM {$trmdb->posts} WHERE post_name = %s", $trs_unfiltered_uri[$trs_unfiltered_uri_offset] ) ) )
		return false;

	// Set the root object as the current trm_query-ied item
	$object_id = 0;
	foreach ( (array)$trs->pages as $page ) {
		if ( isset( $trs_unfiltered_uri[$trs_unfiltered_uri_offset] ) && $page->name == $trs_unfiltered_uri[$trs_unfiltered_uri_offset] ) {
			$object_id = $page->id;
		}
	}

	// Make the queried/post object an actual valid page
	if ( !empty( $object_id ) ) {
		$trm_query->queried_object    =get_post( $object_id );
		$trm_query->queried_object_id = $object_id;
		$post                        = $trm_query->queried_object;
	}

	// Fetch each template and add the php suffix
	foreach ( (array)$templates as $template )
		$filtered_templates[] = $template . '.php';

	// Filter the template locations so that plugins can alter where they are located
	if ( $located_template = apply_filters( 'trs_located_template', locate_template( (array) $filtered_templates, false ), $filtered_templates ) ) {
		// Template was located, lets set this as a valid page and not a 404.
		status_header( 200 );
		$trm_query->is_page = true;
		$trm_query->is_404 = false;

		load_template( apply_filters( 'trs_load_template', $located_template ) );
	}

	// Kill any other output after this.
	die;
}

/**
 * trs_core_catch_profile_uri()
 *
 * If the extended profiles component is not installed we still need
 * to catch the /profile URI's and display whatever we have installed.
 *
 */
function trs_core_catch_profile_uri() {
	global $trs;

	if ( !trs_is_active( 'xprofile' ) )
		trs_core_load_template( apply_filters( 'trs_core_template_display_profile', 'members/single/home' ) );
}

/**
 * Catches invalid access to trendr pages and redirects them accordingly.
 *
 * @package trendr Core
 * @since 1.5
 */
function trs_core_catch_no_access() {
	global $trs, $trs_no_status_set, $trm_query;

	// If trs_core_redirect() and $trs_no_status_set is true,
	// we are redirecting to an accessible page, so skip this check.
	if ( $trs_no_status_set )
		return false;

	if ( !isset( $trm_query->queried_object ) && !trs_is_blog_page() ) {
		trs_do_404();
	}
}
add_action( 'trm', 'trs_core_catch_no_access' );

/**
 * Redirects a user to login for TRS pages that require access control and adds an error message (if
 * one is provided).
 * If authenticated, redirects user back to requested content by default.
 *
 * @package trendr Core
 * @since 1.5
 */
function trs_core_no_access( $args = '' ) {
	global $trs;

	$defaults = array(
		'mode'     => '1',			    // 1 = $root, 2 = enter.php
		'message'  => __( 'You must log in to access the page you requested.', 'trendr' ),
		'redirect' => trm_guess_url(),	// the URL you get redirected to when a user successfully logs in
		'root'     => $trs->root_domain	// the landing page you get redirected to when a user doesn't have access
	);

	$r = trm_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	// Apply filters to these variables
	$mode		= apply_filters( 'trs_no_access_mode', $mode, $root, $redirect, $message );
	$redirect	= apply_filters( 'trs_no_access_redirect', $redirect, $root, $message, $mode );
	$root		= trailingslashit( apply_filters( 'trs_no_access_root', $root, $redirect, $message, $mode ) );
	$message	= apply_filters( 'trs_no_access_message', $message, $root, $redirect, $mode );

	switch ( $mode ) {
		// Option to redirect to enter.php
		// Error message is displayed with trs_core_no_access_trm_login_error()
		case 2 :
			if ( $redirect ) {
				trs_core_redirect( trm_login_url( $redirect ) . '&action=trsnoaccess' );
			} else {
				trs_core_redirect( $root );
			}

			break;

		// Redirect to root with "redirect_to" parameter
		// Error message is displayed with trs_core_add_message()
		case 1 :
		default :
			if ( $redirect ) {
				$url = add_query_arg( 'redirect_to', urlencode( $redirect ), $root );
			} else {
				$url = $root;
			}

			if ( $message ) {
				trs_core_add_message( $message, 'error' );
			}

			trs_core_redirect( $url );

			break;
	}
}

/**
 * Adds an error message to enter.php.
 * Hooks into the "trsnoaccess" action defined in trs_core_no_access().
 *
 * @package trendr Core
 * @global $error
 * @since 1.5
 */
function trs_core_no_access_trm_login_error() {
	global $error;

	$error = apply_filters( 'trs_trm_login_error', __( 'You must log in to access the page you requested.', 'trendr' ), $_REQUEST['redirect_to'] );

	// shake shake shake!
	add_action( 'login_head', 'trm_shake_js', 12 );
}
add_action( 'login_form_trsnoaccess', 'trs_core_no_access_trm_login_error' );

?>