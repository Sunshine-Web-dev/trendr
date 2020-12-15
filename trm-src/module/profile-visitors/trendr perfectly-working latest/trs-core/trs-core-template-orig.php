<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Uses the $trs->trs_options_nav global to render out the sub navigation for the current component.
 * Each component adds to its sub navigation array within its own setup_nav() function.
 *
 * This sub navigation array is the secondary level navigation, so for profile it contains:
 *      [Public, Edit Profile, Change Avatar]
 *
 * The function will also analyze the current action for the current component to determine whether
 * or not to highlight a particular sub nav item.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 * @uses trs_get_user_nav() Renders the navigation for a profile of a currently viewed user.
 */
function trs_get_options_nav() {
	global $trs;

	// If we are looking at a member profile, then the we can use the current component as an
	// index. Otherwise we need to use the component's root_slug
	$component_index = !empty( $trs->displayed_user ) ? $trs->current_component : trs_get_root_slug( $trs->current_component );

	if ( !trs_is_single_item() ) {
		if ( !isset( $trs->trs_options_nav[$component_index] ) || count( $trs->trs_options_nav[$component_index] ) < 1 ) {
			return false;
		} else {
			$the_index = $component_index;
		}
	} else {
		if ( !isset( $trs->trs_options_nav[$trs->current_item] ) || count( $trs->trs_options_nav[$trs->current_item] ) < 1 ) {
			return false;
		} else {
			$the_index = $trs->current_item;
		}
	}

	// Loop through each navigation item
	foreach ( (array)$trs->trs_options_nav[$the_index] as $subnav_item ) {
		if ( !$subnav_item['user_has_access'] )
			continue;

		// If the current action or an action variable matches the nav item id, then add a highlight CSS class.
		if ( $subnav_item['slug'] == $trs->current_action ) {
			$selected = ' class="current selected"';
		} else {
			$selected = '';
		}

		// List type depends on our current component
		$list_type = trs_is_group() ? 'groups' : 'personal';

		// echo out the final list item
		echo apply_filters( 'trs_get_options_nav_' . $subnav_item['css_id'], '<li id="' . $subnav_item['css_id'] . '-' . $list_type . '-li" ' . $selected . '><a id="' . $subnav_item['css_id'] . '" href="' . $subnav_item['link'] . '">' . $subnav_item['name'] . '</a></li>', $subnav_item );
	}
}

function trs_get_options_title() {
	global $trs;

	if ( empty( $trs->trs_options_title ) )
		$trs->trs_options_title = __( 'Options', 'trendr' );

	echo apply_filters( 'trs_get_options_title', esc_attr( $trs->trs_options_title ) );
}

/** Avatars *******************************************************************/

/**
 * Check to see if there is an options portrait. An options portrait is an portrait for something
 * like a group, or a friend. Basically an portrait that appears in the sub nav options bar.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 */
function trs_has_options_portrait() {
	global $trs;

	if ( empty( $trs->trs_options_portrait ) )
		return false;

	return true;
}

function trs_get_options_portrait() {
	global $trs;

	echo apply_filters( 'trs_get_options_portrait', $trs->trs_options_portrait );
}

function trs_comment_author_portrait() {
	global $comment;

	if ( function_exists( 'trs_core_fetch_portrait' ) )
		echo apply_filters( 'trs_comment_author_portrait', trs_core_fetch_portrait( array( 'item_id' => $comment->user_id, 'type' => 'thumb' ) ) );
	else if ( function_exists('get_portrait') )
		get_portrait();
}

function trs_post_author_portrait() {
	global $post;

	if ( function_exists( 'trs_core_fetch_portrait' ) )
		echo apply_filters( 'trs_post_author_portrait', trs_core_fetch_portrait( array( 'item_id' => $post->post_author, 'type' => 'thumb' ) ) );
	else if ( function_exists('get_portrait') )
		get_portrait();
}

function trs_portrait_admin_step() {
	echo trs_get_portrait_admin_step();
}
	function trs_get_portrait_admin_step() {
		global $trs;

		if ( isset( $trs->portrait_admin->step ) )
			$step = $trs->portrait_admin->step;
		else
			$step = 'upload-image';

		return apply_filters( 'trs_get_portrait_admin_step', $step );
	}

function trs_portrait_to_crop() {
	echo trs_get_portrait_to_crop();
}
	function trs_get_portrait_to_crop() {
		global $trs;

		if ( isset( $trs->portrait_admin->image->url ) )
			$url = $trs->portrait_admin->image->url;
		else
			$url = '';

		return apply_filters( 'trs_get_portrait_to_crop', $url );
	}

function trs_portrait_to_crop_src() {
	echo trs_get_portrait_to_crop_src();
}
	function trs_get_portrait_to_crop_src() {
		global $trs;

		return apply_filters( 'trs_get_portrait_to_crop_src', str_replace( TRM_CONTENT_DIR, '', $trs->portrait_admin->image->dir ) );
	}

function trs_portrait_cropper() {
	global $trs;

	echo '<img id="portrait-to-crop" class="portrait" src="' . $trs->portrait_admin->image . '" />';
}

function trs_site_name() {
	echo apply_filters( 'trs_site_name', get_bloginfo( 'name', 'display' ) );
}

function trs_get_profile_header() {
	locate_template( array( '/profile/profile-header.php' ), true );
}

function trs_exists( $component_name ) {
	if ( function_exists( $component_name . '_install' ) )
		return true;

	return false;
}

function trs_format_time( $time, $just_date = false, $localize_time = true ) {
	if ( !isset( $time ) || !is_numeric( $time ) )
		return false;

	// Get GMT offset from root blog
	$root_blog_offset = false;
	if ( $localize_time )
		$root_blog_offset = get_blog_option( trs_get_root_blog_id(), 'gmt_offset' );

	// Calculate offset time
	$time_offset = $time + ( $root_blog_offset * 3600 );

	// Current date (January 1, 2010)
	$date = date_i18n( get_option( 'date_format' ), $time_offset );

	// Should we show the time also?
	if ( !$just_date ) {
		// Current time (9:50pm)
		$time = date_i18n( get_option( 'time_format' ), $time_offset );

		// Return string formatted with date and time
		$date = sprintf( __( '%1$s at %2$s', 'trendr' ), $date, $time );
	}

	return apply_filters( 'trs_format_time', $date );
}

function trs_word_or_name( $youtext, $nametext, $capitalize = true, $echo = true ) {
	global $trs;

	if ( $capitalize )
		$youtext = trs_core_ucfirst($youtext);

	if ( $trs->displayed_user->id == $trs->loggedin_user->id ) {
		if ( $echo )
			echo apply_filters( 'trs_word_or_name', $youtext );
		else
			return apply_filters( 'trs_word_or_name', $youtext );
	} else {
		$fullname = (array)explode( ' ', $trs->displayed_user->fullname );
		$nametext = sprintf( $nametext, $fullname[0] );
		if ( $echo )
			echo apply_filters( 'trs_word_or_name', $nametext );
		else
			return apply_filters( 'trs_word_or_name', $nametext );
	}
}

function trs_get_plugin_sidebar() {
	locate_template( array( 'plugin-sidebar.php' ), true );
}

function trs_styles() {
	do_action( 'trs_styles' );
	trm_print_styles();
}

/** Search Form ***************************************************************/

function trs_search_form_action() {
	return apply_filters( 'trs_search_form_action', trs_get_root_domain() . '/' . trs_get_search_slug() );
}

/**
 * Generates the basic search form as used in TRS-Default's header.
 *
 * @global object $trs trendr global settings
 * @return string HTML <select> element
 * @since 1.0
 */
function trs_search_form_type_select() {
	global $trs;

	$options = array();

	if ( trs_is_active( 'xprofile' ) )
		$options['members'] = __( 'Members', 'trendr' );

	if ( trs_is_active( 'groups' ) )
		$options['groups']  = __( 'Groups',  'trendr' );

	if ( trs_is_active( 'blogs' ) && is_multisite() )
		$options['blogs']   = __( 'Blogs',   'trendr' );

	if ( trs_is_active( 'forums' ) && trs_forums_is_installed_correctly() && trs_forums_has_directory() )
		$options['forums']  = __( 'Forums',  'trendr' );

	$options['posts'] = __( 'Posts', 'trendr' );

	if (is_user_logged_in() && isset( $trs->pages->test ) )
		$options['test']  = __( 'test',  'trendr' );

	// Eventually this won't be needed and a page will be built to integrate all search results.
	$selection_box  = '<label for="search-which" class="accessibly-hidden">' . __( ':', 'trendr' ) . '</label>';
	$selection_box .= '<select name="search-which" id="search-which" style="width: auto">';

	$options = apply_filters( 'trs_search_form_type_select_options', $options );
	foreach( (array)$options as $option_value => $option_title )
		$selection_box .= sprintf( '<option value="%s">%s</option>', $option_value, $option_title );

	$selection_box .= '</select>';

	return apply_filters( 'trs_search_form_type_select', $selection_box );
}

/**
 * Get the default text for the search box for a given component.
 *
 * @global object $trs trendr global settings
 * @return string
 * @since 1.5
 */
function trs_search_default_text( $component = '' ) {
	echo trs_get_search_default_text( $component );
}
	function trs_get_search_default_text( $component = '' ) {
		global $trs;

		if ( empty( $component ) )
			$component = trs_current_component();

		$default_text = __( 'Search anything...', 'trendr' );

		// Most of the time, $component will be the actual component ID
		if ( !empty( $component ) ) {
			if ( !empty( $trs->{$component}->search_string ) ) {
				$default_text = $trs->{$component}->search_string;
			} else {
				// When the request comes through AJAX, we need to get the component
				// name out of $trs->pages
				if ( !empty( $trs->pages->{$component}->slug ) ) {
					$key = $trs->pages->{$component}->slug;
					if ( !empty( $trs->{$key}->search_string ) )
						$default_text = $trs->{$key}->search_string;
				}
			}
		}

		return apply_filters( 'trs_get_search_default_text', $default_text, $component );
	}

function trs_custom_profile_boxes() {
	do_action( 'trs_custom_profile_boxes' );
}

function trs_custom_profile_sidebar_boxes() {
	do_action( 'trs_custom_profile_sidebar_boxes' );
}

/**
 * Creates and outputs a button.
 *
 * @param array $args See trs_get_button() for the list of arguments.
 * @see trs_get_button()
 */
function trs_button( $args = '' ) {
	echo trs_get_button( $args );
}
	/**
	 * Creates and returns a button.
	 *
	 * Args:
	 * component: Which component this button is for
	 * must_be_logged_in: Button only appears for logged in users
	 * block_self: Button will not appear when viewing your own profile.
	 * wrapper: div|span|p|li|
	 * wrapper_id: The DOM ID of the button wrapper
	 * wrapper_class: The DOM class of the button wrapper
	 * link_href: The destination link of the button
	 * link_title: Title of the button
	 * link_id: The DOM ID of the button
	 * link_class: The DOM class of the button
	 * link_rel: The DOM rel of the button
	 * link_text: The contents of the button
	 *
	 * @param array $button
	 * @return string
	 * @see trs_add_friend_button()
	 * @see trs_send_public_message_button()
	 * @see trs_send_private_message_button()
	 */
	function trs_get_button( $args = '' ) {
		$button = new TRS_Button( $args );
		return apply_filters( 'trs_get_button', $button->contents, $args, $button );
	}


/**
 * Truncates text.
 *
 * Cuts a string to the length of $length and replaces the last characters
 * with the ending if the text is longer than length.
 *
 * This function is borrowed from CakePHP v2.0, under the MIT license. See
 * http://book.cakephp.org/view/1469/Text#truncate-1625
 *
 * ### Options:
 *
 * - `ending` Will be used as Ending and appended to the trimmed string
 * - `exact` If false, $text will not be cut mid-word
 * - `html` If true, HTML tags would be handled correctly
 * - `filter_shortcodes` If true, shortcodes will be stripped before truncating
 *
 * @package trendr
 *
 * @param string  $text String to truncate.
 * @param integer $length Length of returned string, including ellipsis.
 * @param array $options An array of html attributes and options.
 * @return string Trimmed string.
 */
function trs_create_excerpt( $text, $length = 225, $options = array() ) {
	// Backward compatibility. The third argument used to be a boolean $filter_shortcodes
	$filter_shortcodes_default = is_bool( $options ) ? $options : true;

	$defaults = array(
		'ending'            => __( ' [&hellip;]', 'trendr' ),
		'exact'             => false,
		'html'              => true,
		'filter_shortcodes' => $filter_shortcodes_default
	);
	$r = trm_parse_args( $options, $defaults );
	extract( $r );

	// Save the original text, to be passed along to the filter
	$original_text = $text;

	// Allow plugins to modify these values globally
	$length = apply_filters( 'trs_excerpt_length', $length );
	$ending = apply_filters( 'trs_excerpt_append_text', $ending );

	// Remove shortcodes if necessary
	if ( $filter_shortcodes )
		$text = strip_shortcodes( $text );

	// When $html is true, the excerpt should be created without including HTML tags in the
	// excerpt length
	if ( $html ) {
		// The text is short enough. No need to truncate
		if ( mb_strlen( preg_replace( '/<.*?>/', '', $text ) ) <= $length ) {
			return $text;
		}

		$totalLength = mb_strlen( strip_tags( $ending ) );
		$openTags    = array();
		$truncate    = '';

		// Find all the tags and put them in a stack for later use
		preg_match_all( '/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER );
		foreach ( $tags as $tag ) {
			// Process tags that need to be closed
			if ( !preg_match( '/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s',  $tag[2] ) ) {
				if ( preg_match( '/<[\w]+[^>]*>/s', $tag[0] ) ) {
					array_unshift( $openTags, $tag[2] );
				} else if ( preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag ) ) {
					$pos = array_search( $closeTag[1], $openTags );
					if ( $pos !== false ) {
						array_splice( $openTags, $pos, 1 );
					}
				}
			}
			$truncate .= $tag[1];

			$contentLength = mb_strlen( preg_replace( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3] ) );
			if ( $contentLength + $totalLength > $length ) {
				$left = $length - $totalLength;
				$entitiesLength = 0;
				if ( preg_match_all( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE ) ) {
					foreach ( $entities[0] as $entity ) {
						if ( $entity[1] + 1 - $entitiesLength <= $left ) {
							$left--;
							$entitiesLength += mb_strlen( $entity[0] );
						} else {
							break;
						}
					}
				}

				$truncate .= mb_substr( $tag[3], 0 , $left + $entitiesLength );
				break;
			} else {
				$truncate .= $tag[3];
				$totalLength += $contentLength;
			}
			if ( $totalLength >= $length ) {
				break;
			}
		}
	} else {
		if ( mb_strlen( $text ) <= $length ) {
			return $text;
		} else {
			$truncate = mb_substr( $text, 0, $length - mb_strlen( $ending ) );
		}
	}

	// If $exact is false, we can't break on words
	if ( !$exact ) {
		$spacepos = mb_strrpos( $truncate, ' ' );
		if ( isset( $spacepos ) ) {
			if ( $html ) {
				$bits = mb_substr( $truncate, $spacepos );
				preg_match_all( '/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER );
				if ( !empty( $droppedTags ) ) {
					foreach ( $droppedTags as $closingTag ) {
						if ( !in_array( $closingTag[1], $openTags ) ) {
							array_unshift( $openTags, $closingTag[1] );
						}
					}
				}
			}
			$truncate = mb_substr( $truncate, 0, $spacepos );
		}
	}
	$truncate .= $ending;

	if ( $html ) {
		foreach ( $openTags as $tag ) {
			$truncate .= '</' . $tag . '>';
		}
	}

	return apply_filters( 'trs_create_excerpt', $truncate, $original_text, $length, $options );

}
add_filter( 'trs_create_excerpt', 'stripslashes_deep' );
add_filter( 'trs_create_excerpt', 'force_balance_tags' );

function trs_total_member_count() {
	echo trs_get_total_member_count();
}
	function trs_get_total_member_count() {
		return apply_filters( 'trs_get_total_member_count', trs_core_get_total_member_count() );
	}
	add_filter( 'trs_get_total_member_count', 'trs_core_number_format' );

function trs_blog_signup_allowed() {
	echo trs_get_blog_signup_allowed();
}
	function trs_get_blog_signup_allowed() {
		global $trs;

		if ( !is_multisite() )
			return false;

		$status = $trs->site_options['registration'];
		if ( 'none' != $status && 'user' != $status )
			return true;

		return false;
	}

function trs_account_was_activated() {
	global $trs;

	$activation_complete = !empty( $trs->activation_complete ) ? $trs->activation_complete : false;

	return $activation_complete;
}

function trs_registration_needs_activation() {
	return apply_filters( 'trs_registration_needs_activation', true );
}

/**
 * Allow templates to pass parameters directly into the template loops via AJAX
 *
 * For the most part this will be filtered in a theme's functions.php for example
 * in the default theme it is filtered via trendr_ajax_call()
 *
 * By using this template tag in the templates it will stop them from showing errors
 * if someone copies the templates from the default theme into another trendr theme
 * without coping the functions from functions.php.
 */
function trs_ajax_querystring( $object = false ) {
	global $trs;

	if ( !isset( $trs->ajax_querystring ) )
		$trs->ajax_querystring = '';

	return apply_filters( 'trs_ajax_querystring', $trs->ajax_querystring, $object );
}

/** Template Classes and _is functions ****************************************/

function trs_current_component() {
	global $trs;
	$current_component = !empty( $trs->current_component ) ? $trs->current_component : false;
	return apply_filters( 'trs_current_component', $current_component );
}

function trs_current_action() {
	global $trs;
	$current_action = !empty( $trs->current_action ) ? $trs->current_action : false;
	return apply_filters( 'trs_current_action', $current_action );
}

function trs_current_item() {
	global $trs;
	$current_item = !empty( $trs->current_item ) ? $trs->current_item : false;
	return apply_filters( 'trs_current_item', $current_item );
}

/**
 * Return the value of $trs->action_variables
 *
 * @package trendr
 *
 * @param mixed $action_variables The action variables array, or false if the array is empty
 */
function trs_action_variables() {
	global $trs;
	$action_variables = !empty( $trs->action_variables ) ? $trs->action_variables : false;
	return apply_filters( 'trs_action_variables', $action_variables );
}

/**
 * Return the value of a given action variable
 *
 * @package trendr
 * @since 1.5
 *
 * @param int $position The key of the action_variables array that you want
 * @return str $action_variable The value of that position in the array
 */
function trs_action_variable( $position = 0 ) {
	$action_variables = trs_action_variables();
	$action_variable  = isset( $action_variables[$position] ) ? $action_variables[$position] : false;

	return apply_filters( 'trs_action_variable', $action_variable, $position );
}

function trs_root_domain() {
	echo trs_get_root_domain();
}
	function trs_get_root_domain() {
		global $trs;

		if ( isset( $trs->root_domain ) && !empty( $trs->root_domain ) ) {
			$domain = $trs->root_domain;
		} else {
			$domain          = trs_core_get_root_domain();
			$trs->root_domain = $domain;
		}

		return apply_filters( 'trs_get_root_domain', $domain );
	}

/**
 * Echoes the output of trs_get_root_slug()
 *
 * @package trendr Core
 * @since 1.5
 */
function trs_root_slug( $component = '' ) {
	echo trs_get_root_slug( $component );
}
	/**
	 * Gets the root slug for a component slug
	 *
	 * In order to maintain backward compatibility, the following procedure is used:
	 * 1) Use the short slug to get the canonical component name from the
	 *    active component array
	 * 2) Use the component name to get the root slug out of the appropriate part of the $trs
	 *    global
	 * 3) If nothing turns up, it probably means that $component is itself a root slug
	 *
	 * Example: If your groups directory is at /community/companies, this function first uses
	 * the short slug 'companies' (ie the current component) to look up the canonical name
	 * 'groups' in $trs->active_components. Then it uses 'groups' to get the root slug, from
	 * $trs->groups->root_slug.
	 *
	 * @package trendr Core
	 * @since 1.5
	 *
	 * @global object $trs Global trendr settings object
	 * @param string $component Optional. Defaults to the current component
	 * @return string $root_slug The root slug
	 */
	function trs_get_root_slug( $component = '' ) {
		global $trs;

		$root_slug = '';

		// Use current global component if none passed
		if ( empty( $component ) )
			$component = $trs->current_component;

		// Component is active
		if ( !empty( $trs->active_components[$component] ) ) {
			// Backward compatibility: in legacy plugins, the canonical component id
			// was stored as an array value in $trs->active_components
			$component_name = '1' == $trs->active_components[$component] ? $component : $trs->active_components[$component];

			// Component has specific root slug
			if ( !empty( $trs->{$component_name}->root_slug ) ) {
				$root_slug = $trs->{$component_name}->root_slug;
			}
		}

		// No specific root slug, so fall back to component slug
		if ( empty( $root_slug ) )
			$root_slug = $component;

		return apply_filters( 'trs_get_root_slug', $root_slug, $component );
	}

/**
 * Return the component name based on the current root slug
 *
 * @since trendr {r3923}
 * @global object $trs Global trendr settings object
 * @param str $root_slug Needle to our active component haystack
 * @return mixed False if none found, component name if found
 */
function trs_get_name_from_root_slug( $root_slug = '' ) {
	global $trs;

	// If no slug is passed, look at current_component
	if ( empty( $root_slug ) )
		$root_slug = $trs->current_component;

	// No current component or root slug, so flee
	if ( empty( $root_slug ) )
		return false;

	// Loop through active components and look for a match
	foreach ( $trs->active_components as $component => $id )
		if (	isset( $trs->{$component}->root_slug ) &&
				!empty( $trs->{$component}->root_slug ) &&
				$trs->{$component}->root_slug == $root_slug )
			return $trs->{$component}->name;

	return false;
}

function trs_user_has_access() {
	$has_access = ( is_super_admin() || trs_is_my_profile() ) ? true : false;

	return apply_filters( 'trs_user_has_access', $has_access );
}

/**
 * Output the search slug
 *
 * @package trendr
 * @since 1.5
 *
 * @uses trs_get_search_slug()
 */
function trs_search_slug() {
	echo trs_get_search_slug();
}
	/**
	 * Return the search slug
	 *
	 * @package trendr
	 * @since 1.5
	 */
	function trs_get_search_slug() {
		return apply_filters( 'trs_get_search_slug', TRS_SEARCH_SLUG );
	}

/** is_() functions to determine the current page *****************************/

/**
 * Checks to see whether the current page belongs to the specified component
 *
 * This function is designed to be generous, accepting several different kinds
 * of value for the $component parameter. It checks $component_name against:
 * - the component's root_slug, which matches the page slug in $trs->pages
 * - the component's regular slug
 * - the component's id, or 'canonical' name
 *
 * @package trendr Core
 * @since 1.5
 * @return bool Returns true if the component matches, or else false.
 */
function trs_is_current_component( $component ) {
	global $trs;

	$is_current_component = false;

	// Backward compatibility: 'xprofile' should be read as 'profile'
	if ( 'xprofile' == $component )
		$component = 'profile';

	if ( !empty( $trs->current_component ) ) {
		// First, check to see whether $component_name and the current
		// component are a simple match
		if ( $trs->current_component == $component ) {
			$is_current_component = true;

		// Since the current component is based on the visible URL slug let's
		// check the component being passed and see if its root_slug matches
		} elseif ( isset( $trs->{$component}->root_slug ) && $trs->{$component}->root_slug == $trs->current_component ) {
			$is_current_component = true;

		// Because slugs can differ from root_slugs, we should check them too
		} elseif ( isset( $trs->{$component}->slug ) && $trs->{$component}->slug == $trs->current_component ) {
			$is_current_component = true;

		// Next, check to see whether $component is a canonical,
		// non-translatable component name. If so, we can return its
		// corresponding slug from $trs->active_components.
		} else if ( $key = array_search( $component, $trs->active_components ) ) {
			if ( strstr( $trs->current_component, $key ) )
				$is_current_component = true;

		// If we haven't found a match yet, check against the root_slugs
		// created by $trs->pages, as well as the regular slugs
		} else {
			foreach ( $trs->active_components as $id ) {
				// If the $component parameter does not match the current_component,
				// then move along, these are not the droids you are looking for
				if ( empty( $trs->{$id}->root_slug ) || $trs->{$id}->root_slug != $trs->current_component )
					continue;

				if ( $id == $component ) {
					$is_current_component = true;
					break;
				}
			}
		}

	// Page template fallback check if $trs->current_component is empty
	} elseif ( !is_admin() && is_page() ) {
		global $trm_query;
		$page          = $trm_query->get_queried_object();
		$custom_fields = get_post_custom_values( '_trm_page_template', $page->ID );
		$page_template = $custom_fields[0];

		// Component name is in the page template name
		if ( !empty( $page_template ) && strstr( strtolower( $page_template ), strtolower( $component ) ) )
			$is_current_component = true;
	}

 	return apply_filters( 'trs_is_current_component', $is_current_component, $component );
}

/**
 * Check to see whether the current page matches a given action.
 *
 * Along with trs_is_current_component() and trs_is_action_variable(), this function is mostly used
 * to help determine when to use a given screen function.
 *
 * In TRS parlance, the current_action is the URL chunk that comes directly after the
 * current item slug. E.g., in
 *   http://example.com/groups/my-group/members
 * the current_action is 'members'.
 *
 * @package trendr
 * @since 1.5
 *
 * @param str $action The action being tested against
 * @return bool True if the current action matches $action
 */
function trs_is_current_action( $action = '' ) {
	global $trs;

	if ( $action == $trs->current_action )
		return true;

	return false;
}

/**
 * Check to see whether the current page matches a given action_variable.
 *
 * Along with trs_is_current_component() and trs_is_current_action(), this function is mostly used
 * to help determine when to use a given screen function.
 *
 * In TRS parlance, action_variables are an array made up of the URL chunks appearing after the
 * current_action in a URL. For example,
 *   http://example.com/groups/my-group/admin/group-settings
 * $action_variables[0] is 'group-settings'.
 *
 * @package trendr
 * @since 1.5
 *
 * @param str $action_variable The action_variable being tested against
 * @param int $position The array key you're testing against. If you don't provide a $position,
 *   the function will return true if the $action_variable is found *anywhere* in the action
 *   variables array.
 * @return bool
 */
function trs_is_action_variable( $action_variable = '', $position = false ) {
	$is_action_variable = false;

	if ( false !== $position ) {
		// When a $position is specified, check that slot in the action_variables array
		if ( $action_variable ) {
			$is_action_variable = $action_variable == trs_action_variable( $position );
		} else {
			// If no $action_variable is provided, we are essentially checking to see
			// whether the slot is empty
			$is_action_variable = !trs_action_variable( $position );
		}
	} else {
		// When no $position is specified, check the entire array
		$is_action_variable = in_array( $action_variable, (array)trs_action_variables() );
	}

	return apply_filters( 'trs_is_action_variable', $is_action_variable, $action_variable, $position );
}

function trs_is_current_item( $item = '' ) {
	if ( !empty( $item ) && $item == trs_current_item() )
		return true;

	return false;
}

function trs_is_single_item() {
	global $trs;

	if ( !empty( $trs->is_single_item ) )
		return true;

	return false;
}

function trs_is_item_admin() {
	global $trs;

	if ( !empty( $trs->is_item_admin ) )
		return true;

	return false;
}

function trs_is_item_mod() {
	global $trs;

	if ( !empty( $trs->is_item_mod ) )
		return true;

	return false;
}

function trs_is_directory() {
	global $trs;

	if ( !empty( $trs->is_directory ) )
		return true;

	return false;
}

/**
 * Checks to see if a component's URL should be in the root, not under a
 * member page:
 *
 *   Yes: http://domain.com/groups/the-group
 *   No:  http://domain.com/members/andy/groups/the-group
 *
 * @package trendr Core
 * @return true if root component, else false.
 */
function trs_is_root_component( $component_name ) {
	global $trs;

	if ( !isset( $trs->active_components ) )
		return false;

	foreach ( (array) $trs->active_components as $key => $slug ) {
		if ( $key == $component_name || $slug == $component_name )
			return true;
	}

	return false;
}

/**
 * Checks if the site's front page is set to the specified trendr component
 * page in Backend-WeaprEcqaKejUbRq-trendr's Settings > Reading screen.
 *
 * @global object $trs Global trendr settings object
 * @global $current_blog trendr global for the current blog
 * @param string $component Optional; Name of the component to check for.
 * @return bool True If the specified component is set to be the site's front page.
 * @since 1.5
 */
function trs_is_component_front_page( $component = '' ) {
	global $trs, $current_blog;

	if ( !$component && !empty( $trs->current_component ) )
		$component = $trs->current_component;

	$path = is_main_site() ? trs_core_get_site_path() : $current_blog->path;

	if ( 'page' != get_option( 'show_on_front' ) || !$component || empty( $trs->pages->{$component} ) || $_SERVER['REQUEST_URI'] != $path )
		return false;

	return apply_filters( 'trs_is_component_front_page', ( $trs->pages->{$component}->id == get_option( 'page_on_front' ) ), $component );
}

/**
 * Is this a blog page, ie a non-TRS page?
 *
 * You can tell if a page is displaying TRS content by whether the current_component has been defined
 *
 * @package trendr
 *
 * @return bool True if it's a non-TRS page, false otherwise
 */
function trs_is_blog_page() {
	global $trm_query;

	$is_blog_page = false;

	// Generally, we can just check to see that there's no current component. The one exception
	// is single user home tabs, where $trs->current_component is unset. Thus the addition
	// of the trs_is_user() check.
	if ( !trs_current_component() && !trs_is_user() )
		$is_blog_page = true;

	return apply_filters( 'trs_is_blog_page', $is_blog_page );
}

function trs_is_page( $page ) {
	if ( !trs_is_user() && trs_is_current_component( $page )  )
		return true;

	if ( 'home' == $page )
		return is_front_page();

	return false;
}

/** Components ****************************************************************/

function trs_is_active( $component ) {
	global $trs;

	if ( isset( $trs->active_components[$component] ) )
		return true;

	return false;
}

function trs_is_members_component() {
	if ( trs_is_current_component( 'members' ) )
		return true;

	return false;
}

function trs_is_profile_component() {
	if ( trs_is_current_component( 'xprofile' ) )
		return true;

	return false;
}

function trs_is_activity_component() {
	if ( trs_is_current_component( 'activity' ) )
		return true;

	return false;
}

function trs_is_blogs_component() {
	if ( is_multisite() && trs_is_current_component( 'blogs' ) )
		return true;

	return false;
}

function trs_is_messages_component() {
	if ( trs_is_current_component( 'messages' ) )
		return true;

	return false;
}

function trs_is_friends_component() {
	if ( trs_is_current_component( 'friends' ) )
		return true;

	return false;
}

function trs_is_groups_component() {
	if ( trs_is_current_component( 'groups' ) )
		return true;

	return false;
}

function trs_is_forums_component() {
	if ( trs_is_current_component( 'forums' ) )
		return true;

	return false;
}

function trs_is_settings_component() {
	if ( trs_is_current_component( 'settings' ) )
		return true;

	return false;
}

/** Activity ******************************************************************/

function trs_is_single_activity() {
	global $trs;

	if ( trs_is_activity_component() && is_numeric( $trs->current_action ) )
		return true;

	return false;
}

/** User **********************************************************************/

function trs_is_my_profile() {
	global $trs;

	if ( is_user_logged_in() && $trs->loggedin_user->id == $trs->displayed_user->id )
		$my_profile = true;
	else
		$my_profile = false;

	return apply_filters( 'trs_is_my_profile', $my_profile );
}

function trs_is_user() {
	global $trs;

	if ( !empty( $trs->displayed_user->id ) )
		return true;

	return false;
}

function trs_is_user_activity() {
	if ( trs_is_user() && trs_is_activity_component() )
		return true;

	return false;
}

function trs_is_user_friends_activity() {

	if ( !trs_is_active( 'friends' ) )
		return false;

	$slug = trs_get_friends_slug();

	if ( empty( $slug ) )
		$slug = 'friends';

	if ( trs_is_user_activity() && trs_is_current_action( $slug ) )
		return true;

	return false;
}

function trs_is_user_groups_activity() {

	if ( !trs_is_active( 'groups' ) )
		return false;

	$slug = trs_get_groups_slug();

	if ( empty( $slug ) )
		$slug = 'groups';

	if ( trs_is_user_activity() && trs_is_current_action( $slug ) )
		return true;

	return false;
}

function trs_is_user_profile() {
	if ( trs_is_profile_component() || trs_is_current_component( 'profile' ) )
		return true;

	return false;
}

function trs_is_user_profile_edit() {
	if ( trs_is_profile_component() && trs_is_current_action( 'edit' ) )
		return true;

	return false;
}

function trs_is_user_change_portrait() {
	if ( trs_is_profile_component() && trs_is_current_action( 'change-profile-photo' ) )
		return true;

	return false;
}

/**
 * Is this a user's forums page?
 *
 * @package trendr
 *
 * @return bool
 */
function trs_is_user_forums() {
	if ( trs_is_user() && trs_is_forums_component() )
		return true;

	return false;
}

/**
 * Is this a user's "Topics Started" page?
 *
 * @package trendr
 * @since 1.5
 *
 * @return bool
 */
function trs_is_user_forums_started() {
	if ( trs_is_user_forums() && trs_is_current_action( 'topics' ) )
		return true;

	return false;
}

/**
 * Is this a user's "Replied To" page?
 *
 * @package trendr
 * @since 1.5
 *
 * @return bool
 */
function trs_is_user_forums_replied_to() {
	if ( trs_is_user_forums() && trs_is_current_action( 'replies' ) )
		return true;

	return false;
}

function trs_is_user_groups() {
	if ( trs_is_user() && trs_is_groups_component() )
		return true;

	return false;
}

function trs_is_user_blogs() {
	if ( trs_is_user() && trs_is_blogs_component() )
		return true;

	return false;
}

function trs_is_user_recent_posts() {
	if ( trs_is_user_blogs() && trs_is_current_action( 'recent-posts' ) )
		return true;

	return false;
}

function trs_is_user_recent_commments() {
	if ( trs_is_user_blogs() && trs_is_current_action( 'recent-comments' ) )
		return true;

	return false;
}

function trs_is_user_friends() {
	if ( trs_is_user() && trs_is_friends_component() )
		return true;

	return false;
}

function trs_is_user_friend_requests() {
	if ( trs_is_user_friends() && trs_is_current_action( 'requests' ) )
		return true;

	return false;
}

/**
 * Is this a user's settings page?
 *
 * @package trendr
 *
 * @return bool
 */
function trs_is_user_settings() {
	if ( trs_is_user() && trs_is_settings_component() )
		return true;

	return false;
}

/**
 * Is this a user's General Settings page?
 *
 * @package trendr
 * @since 1.5
 *
 * @return bool
 */
function trs_is_user_settings_general() {
	if ( trs_is_user_settings() && trs_is_current_action( 'general' ) )
		return true;

	return false;
}

/**
 * Is this a user's Notification Settings page?
 *
 * @package trendr
 * @since 1.5
 *
 * @return bool
 */
function trs_is_user_settings_notifications() {
	if ( trs_is_user_settings() && trs_is_current_action( 'notifications' ) )
		return true;

	return false;
}

/**
 * Is this a user's Account Deletion page?
 *
 * @package trendr
 * @since 1.5
 *
 * @return bool
 */
function trs_is_user_settings_account_delete() {
	if ( trs_is_user_settings() && trs_is_current_action( 'delete-account' ) )
		return true;

	return false;
}


/** Groups ******************************************************************/

function trs_is_group() {
	global $trs;

	if ( trs_is_groups_component() && isset( $trs->groups->current_group ) && $trs->groups->current_group )
		return true;

	return false;
}

function trs_is_group_home() {
	if ( trs_is_single_item() && trs_is_groups_component() && ( !trs_current_action() || trs_is_current_action( 'home' ) ) )
		return true;

	return false;
}

function trs_is_group_create() {
	if ( trs_is_groups_component() && trs_is_current_action( 'create' ) )
		return true;

	return false;
}

function trs_is_group_admin_page() {
	if ( trs_is_single_item() && trs_is_groups_component() && trs_is_current_action( 'admin' ) )
		return true;

	return false;
}

function trs_is_group_forum() {
	if ( trs_is_single_item() && trs_is_groups_component() && trs_is_current_action( 'forum' ) )
		return true;

	return false;
}

function trs_is_group_activity() {
	if ( trs_is_single_item() && trs_is_groups_component() && trs_is_current_action( 'activity' ) )
		return true;

	return false;
}

function trs_is_group_forum_topic() {
	if ( trs_is_single_item() && trs_is_groups_component() && trs_is_current_action( 'forum' ) && trs_is_action_variable( 'topic', 0 ) )
		return true;

	return false;
}

function trs_is_group_forum_topic_edit() {
	if ( trs_is_single_item() && trs_is_groups_component() && trs_is_current_action( 'forum' ) && trs_is_action_variable( 'topic', 0 ) && trs_is_action_variable( 'edit', 2 ) )
		return true;

	return false;
}

function trs_is_group_members() {
	if ( trs_is_single_item() && trs_is_groups_component() && trs_is_current_action( 'members' ) )
		return true;

	return false;
}

function trs_is_group_invites() {
	if ( trs_is_groups_component() && trs_is_current_action( 'send-invites' ) )
		return true;

	return false;
}

function trs_is_group_membership_request() {
	if ( trs_is_groups_component() && trs_is_current_action( 'request-membership' ) )
		return true;

	return false;
}

function trs_is_group_leave() {

	if ( trs_is_groups_component() && trs_is_single_item() && trs_is_current_action( 'leave-group' ) )
		return true;

	return false;
}

function trs_is_group_single() {
	if ( trs_is_groups_component() && trs_is_single_item() )
		return true;

	return false;
}

function trs_is_create_blog() {
	if ( trs_is_blogs_component() && trs_is_current_action( 'create' ) )
		return true;

	return false;
}

/** Messages ******************************************************************/

function trs_is_user_messages() {
	if ( trs_is_user() && trs_is_messages_component() )
		return true;

	return false;
}

function trs_is_messages_inbox() {
	if ( trs_is_user_messages() && ( !trs_current_action() || trs_is_current_action( 'inbox' ) ) )
		return true;

	return false;
}

function trs_is_messages_sentbox() {
	if ( trs_is_user_messages() && trs_is_current_action( 'sentbox' ) )
		return true;

	return false;
}

function trs_is_messages_compose_screen() {
	if ( trs_is_user_messages() && trs_is_current_action( 'compose' ) )
		return true;

	return false;
}

function trs_is_notices() {
	if ( trs_is_user_messages() && trs_is_current_action( 'notices' ) )
		return true;

	return false;
}


function trs_is_single( $component, $callback ) {
	if ( trs_is_current_component( $component ) && ( true === call_user_func( $callback ) ) )
		return true;

	return false;
}

/** Registration **************************************************************/

function trs_is_activation_page() {
	if ( trs_is_current_component( 'activate' ) )
		return true;

	return false;
}

function trs_is_register_page() {
	if ( trs_is_current_component( 'register' ) )
		return true;

	return false;
}

/**
 * Use the above is_() functions to output a body class for each scenario
 *
 * @package trendr
 * @sutrsackage Core Template
 *
 * @param array $trm_classes The body classes coming from TRM
 * @param array $custom_classes Classes that were passed to get_body_class()
 * @return array $classes The TRS-adjusted body classes
 */
function trs_the_body_class() {
	echo trs_get_the_body_class();
}
	function trs_get_the_body_class( $trm_classes, $custom_classes = false ) {

		$trs_classes = array();

		/** Pages *************************************************************/

		if ( is_front_page() )
			$trs_classes[] = 'home-page';

		if ( trs_is_directory() )
			$trs_classes[] = 'directory';

		if ( trs_is_single_item() )
			$trs_classes[] = 'single-item';

		/** Components ********************************************************/

		if ( !trs_is_blog_page() ) :
			if ( trs_is_user_profile() )
				$trs_classes[] = 'profile';

			if ( trs_is_activity_component() )
				$trs_classes[] = 'posts';

			if ( trs_is_blogs_component() )
				$trs_classes[] = 'blogs';

			if ( trs_is_messages_component() )
				$trs_classes[] = 'messages';

			if ( trs_is_friends_component() )
				$trs_classes[] = 'friends';

			if ( trs_is_groups_component() )
				$trs_classes[] = 'groups';

			if ( trs_is_settings_component()  )
				$trs_classes[] = 'settings';
		endif;

		/** User **************************************************************/

		if ( !trs_is_directory() ) :
			if ( trs_is_user_blogs() )
				$trs_classes[] = 'my-blogs';

			if ( trs_is_user_groups() )
				$trs_classes[] = 'my-groups';

			if ( trs_is_user_activity() )
				$trs_classes[] = 'profile-page';
		endif;

		if ( trs_is_my_profile() )
			$trs_classes[] = 'my-account';

		if ( trs_is_user_profile() )
			$trs_classes[] = 'my-profile';

		if ( trs_is_user_friends() )
			$trs_classes[] = 'my-friends';

		if ( trs_is_user_messages() )
			$trs_classes[] = 'my-messages';

		if ( trs_is_user_recent_commments() )
			$trs_classes[] = 'recent-comments';

		if ( trs_is_user_recent_posts() )
			$trs_classes[] = 'recent-posts';

		if ( trs_is_user_change_portrait() )
			$trs_classes[] = 'change-profile-photo';

		if ( trs_is_user_profile_edit() )
			$trs_classes[] = 'profile-edit';

		if ( trs_is_user_friends_activity() )
			$trs_classes[] = 'friends-activity';

		if ( trs_is_user_groups_activity() )
			$trs_classes[] = 'groups-activity';

		if ( is_user_logged_in() )
			$trs_classes[] = 'logged-in';

		/** Messages **********************************************************/

		if ( trs_is_messages_inbox() )
			$trs_classes[] = 'inbox';

		if ( trs_is_messages_sentbox() )
			$trs_classes[] = 'sentbox';

		if ( trs_is_messages_compose_screen() )
			$trs_classes[] = 'compose';

		if ( trs_is_notices() )
			$trs_classes[] = 'notices';

		if ( trs_is_user_friend_requests() )
			$trs_classes[] = 'friend-requests';

		if ( trs_is_create_blog() )
			$trs_classes[] = 'create-blog';

		/** Groups ************************************************************/

		if ( trs_is_group_leave() )
			$trs_classes[] = 'leave-group';

		if ( trs_is_group_invites() )
			$trs_classes[] = 'group-invites';

		if ( trs_is_group_members() )
			$trs_classes[] = 'group-members';

		if ( trs_is_group_forum_topic() )
			$trs_classes[] = 'group-forum-topic';

		if ( trs_is_group_forum_topic_edit() )
			$trs_classes[] = 'group-forum-topic-edit';

		if ( trs_is_group_forum() )
			$trs_classes[] = 'group-forum';

		if ( trs_is_group_admin_page() )
			$trs_classes[] = 'group-admin';

		if ( trs_is_group_create() )
			$trs_classes[] = 'group-create';

		if ( trs_is_group_home() )
			$trs_classes[] = 'group-home';

		if ( trs_is_single_activity() )
			$trs_classes[] = 'activity-permalink';

		/** Registration ******************************************************/

		if ( trs_is_register_page() )
			$trs_classes[] = 'registration';

		if ( trs_is_activation_page() )
			$trs_classes[] = 'activation';

		/** Current Component & Action ****************************************/

		if ( !trs_is_blog_page() ) {
			$trs_classes[] = trs_current_component();
			$trs_classes[] = trs_current_action();
		}

		/** Clean up***********************************************************/

		// We don't want trendr blog classes to appear on non-blog pages.
		if ( !trs_is_blog_page() ) {

			// Preserve any custom classes already set
			if ( !empty( $custom_classes ) ) {
				$trm_classes = (array) $custom_classes;
			} else {
				$trm_classes = array();
			}
		}

		// Merge TRM classes with TRS classes
		$classes = array_merge( (array) $trs_classes, (array) $trm_classes );

		// Remove any duplicates
		$classes = array_unique( $classes );

		return apply_filters( 'trs_get_the_body_class', $classes, $trs_classes, $trm_classes, $custom_classes );
	}
	add_filter( 'body_class', 'trs_get_the_body_class', 10, 2 );

?>