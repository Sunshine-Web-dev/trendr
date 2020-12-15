<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Retrieve an option
 *
 * This is a wrapper for get_blog_option(), which in turn stores settings data (such as trs-pages)
 * on the appropriate blog, given your current setup.
 *
 * The 'trs_get_option' filter is primarily for backward-compatibility.
 *
 * @package trendr
 * @since 1.5
 *
 * @uses trs_get_root_blog_id()
 * @param str $option_name The option to be retrieved
 * @param str $default Optional. Default value to be returned if the option isn't set
 * @return mixed The value for the option
 */
function trs_get_option( $option_name, $default = '' ) {
	$value = get_blog_option( trs_get_root_blog_id(), $option_name, $default );

	return apply_filters( 'trs_get_option', $value );
}

/**
 * Save an option
 *
 * This is a wrapper for update_blog_option(), which in turn stores settings data (such as trs-pages)
 * on the appropriate blog, given your current setup.
 *
 * @package trendr
 * @since 1.5
 *
 * @uses trs_get_root_blog_id()
 * @param str $option_name The option key to be set
 * @param str $value The value to be set
 */
function trs_update_option( $option_name, $value ) {
	update_blog_option( trs_get_root_blog_id(), $option_name, $value );
}

/**
 * Delete an option
 *
 * This is a wrapper for delete_blog_option(), which in turn deletes settings data (such as
 * trs-pages) on the appropriate blog, given your current setup.
 *
 * @package trendr
 * @since 1.5
 *
 * @uses trs_get_root_blog_id()
 * @param str $option_name The option key to be set
 */
function trs_delete_option( $option_name ) {
	delete_blog_option( trs_get_root_blog_id(), $option_name );
}

/**
 * Allow filtering of database prefix. Intended for use in multinetwork installations.
 *
 * @global object $trmdb trendr database object
 * @return string Filtered database prefix
 */
function trs_core_get_table_prefix() {
	global $trmdb;

	return apply_filters( 'trs_core_get_table_prefix', $trmdb->base_prefix );
}

/**
 * Fetches TRS pages from the meta table, depending on setup
 *
 * @package trendr Core
 * @since 1.5
 *
 * @todo Remove the "Upgrading from an earlier version of TRS pre-1.5" block. Temporary measure for
 *       people running trunk installations. Leave for a version or two, then remove.
 */
function trs_core_get_directory_page_ids() {
	$page_ids = trs_get_option( 'trs-pages' );

  	// Upgrading from an earlier version of TRS pre-1.5
	if ( !isset( $page_ids['members'] ) && $ms_page_ids = get_site_option( 'trs-pages' ) ) {
		$page_blog_id = trs_is_multiblog_mode() ? get_current_blog_id() : trs_get_root_blog_id();

		if ( isset( $ms_page_ids[$page_blog_id] ) ) {
			$page_ids = $ms_page_ids[$page_blog_id];

			trs_update_option( 'trs-pages', $page_ids );
		}
  	}

	// Ensure that empty indexes are unset. Should only matter in edge cases
	if ( $page_ids && is_array( $page_ids ) ) {
		foreach( (array)$page_ids as $component_name => $page_id ) {
			if ( empty( $component_name ) || empty( $page_id ) ) {
				unset( $page_ids[$component_name] );
			}
		}
	}

	return apply_filters( 'trs_core_get_directory_page_ids', $page_ids );
}

/**
 * Stores TRS pages in the meta table, depending on setup
 *
 * trs-pages data is stored in site_options (falls back to options on non-MS), in an array keyed by
 * blog_id. This allows you to change your trs_get_root_blog_id() and go through the setup process again.
 *
 * @package trendr Core
 * @since 1.5
 *
 * @param array $blog_page_ids The IDs of the TRM pages corresponding to TRS component directories
 */
function trs_core_update_directory_page_ids( $blog_page_ids ) {
	trs_update_option( 'trs-pages', $blog_page_ids );
}

/**
 * Get trs-pages names and slugs
 *
 * @package trendr Core
 * @since 1.5
 *
 * @return obj $pages Page names, IDs, and slugs
 */
function trs_core_get_directory_pages() {
	global $trmdb, $trs;

	// Set pages as standard class
	$pages = new stdClass;

	// Get pages and IDs
	if ( $page_ids = trs_core_get_directory_page_ids() ) {

		// Always get page data from the root blog, except on multiblog mode, when it comes
		// from the current blog
		$posts_table_name = trs_is_multiblog_mode() ? $trmdb->posts : $trmdb->get_blog_prefix( trs_get_root_blog_id() ) . 'posts';
		$page_ids_sql     = implode( ',', (array)$page_ids );
		$page_names       = $trmdb->get_results( $trmdb->prepare( "SELECT ID, post_name, post_parent, post_title FROM {$posts_table_name} WHERE ID IN ({$page_ids_sql}) AND post_status = 'publish' " ) );

		foreach ( (array)$page_ids as $component_id => $page_id ) {
			foreach ( (array)$page_names as $page_name ) {
				if ( $page_name->ID == $page_id ) {
					$pages->{$component_id}->name  = $page_name->post_name;
					$pages->{$component_id}->id    = $page_name->ID;
					$pages->{$component_id}->title = $page_name->post_title;
					$slug[]                        = $page_name->post_name;

					// Get the slug
					while ( $page_name->post_parent != 0 ) {
						$parent                 = $trmdb->get_results( $trmdb->prepare( "SELECT post_name, post_parent FROM {$posts_table_name} WHERE ID = %d", $page_name->post_parent ) );
						$slug[]                 = $parent[0]->post_name;
						$page_name->post_parent = $parent[0]->post_parent;
					}

					$pages->{$component_id}->slug = implode( '/', array_reverse( (array)$slug ) );
				}

				unset( $slug );
			}
		}
	}

	return apply_filters( 'trs_core_get_directory_pages', $pages );
}

/**
 * Creates a default component slug from a TRM page root_slug
 *
 * Since 1.5, TRS components get their root_slug (the slug used immediately
 * following the root domain) from the slug of a corresponding TRM page.
 *
 * E.g. if your TRS installation at example.com has its members page at
 * example.com/community/people, $trs->members->root_slug will be 'community/people'.
 *
 * By default, this function creates a shorter version of the root_slug for
 * use elsewhere in the URL, by returning the content after the final '/'
 * in the root_slug ('people' in the example above).
 *
 * Filter on 'trs_core_component_slug_from_root_slug' to override this method
 * in general, or define a specific component slug constant (e.g. TRS_MEMBERS_SLUG)
 * to override specific component slugs.
 *
 * @package trendr Core
 * @since 1.5
 *
 * @param str $root_slug The root slug, which comes from $trs->pages->[component]->slug
 * @return str $slug The short slug for use in the middle of URLs
 */
function trs_core_component_slug_from_root_slug( $root_slug ) {
	$slug_chunks = explode( '/', $root_slug );
 	$slug        = array_pop( $slug_chunks );

 	return apply_filters( 'trs_core_component_slug_from_root_slug', $slug, $root_slug );
}

function trs_core_do_network_admin() {
	$do_network_admin = false;

	if ( is_multisite() && !trs_is_multiblog_mode() )
		$do_network_admin = true;

	return apply_filters( 'trs_core_do_network_admin', $do_network_admin );
}

function trs_core_admin_hook() {
	$hook = trs_core_do_network_admin() ? 'network_admin_menu' : 'admin_menu';

	return apply_filters( 'trs_core_admin_hook', $hook );
}

/**
 * Initializes the Backend-WeaprEcqaKejUbRq-trendr area "trendr" menus and sub menus.
 *
 * @package trendr Core
 * @uses is_super_admin() returns true if the current user is a site admin, false if not
 */
function trs_core_admin_menu_init() {
	if ( !is_super_admin() )
		return false;

	add_action( trs_core_admin_hook(), 'trs_core_add_admin_menu', 9 );

	require ( TRS_PLUGIN_DIR . '/trs-core/admin/trs-core-admin.php' );
}
add_action( 'trs_init', 'trs_core_admin_menu_init' );

/**
 * Adds the "trendr" admin submenu item to the Site Admin tab.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 * @uses is_super_admin() returns true if the current user is a site admin, false if not
 * @uses add_submenu_page() TRM function to add a submenu item
 */
function trs_core_add_admin_menu() {
	if ( !is_super_admin() )
		return false;

	// Don't add this version of the admin menu if a TRS upgrade is in progress
 	// See trs_core_update_add_admin_menu()
	if ( defined( 'TRS_IS_UPGRADE' ) && TRS_IS_UPGRADE )
 		return false;

	$hooks = array();

	// Add the administration tab under the "Site Admin" tab for site administrators
	$hooks[] = add_menu_page( __( 'trendr', 'trendr' ), __( 'trendr', 'trendr' ), 'manage_options', 'trs-general-settings', 'trs_core_admin_component_setup', '' );
	$hooks[] = add_submenu_page( 'trs-general-settings', __( 'Components', 'trendr' ), __( 'Components', 'trendr' ), 'manage_options', 'trs-general-settings', 'trs_core_admin_component_setup'  );
	$hooks[] = add_submenu_page( 'trs-general-settings', __( 'Pages',      'trendr' ), __( 'Pages',      'trendr' ), 'manage_options', 'trs-page-settings',    'trs_core_admin_page_setup'       );
	$hooks[] = add_submenu_page( 'trs-general-settings', __( 'Settings',   'trendr' ), __( 'Settings',   'trendr' ), 'manage_options', 'trs-settings',         'trs_core_admin_settings'         );

	// Add a hook for css/js
	foreach( $hooks as $hook )
		add_action( "admin_print_styles-$hook", 'trs_core_add_admin_menu_styles' );
}

/**
 * Print admin messages to admin_notices or network_admin_notices
 *
 * trendr combines all its messages into a single notice, to avoid a preponderance of yellow
 * boxes.
 *
 * @package trendr Core
 * @since 1.5
 *
 * @global object $trs Global trendr settings object
 * @uses is_super_admin() to check current user permissions before showing the notices
 * @uses trs_is_root_blog()
 */
function trs_core_print_admin_notices() {
	global $trs;

	// Only the super admin should see messages
	if ( !is_super_admin() )
		return;

	// On multisite installs, don't show on the Site Admin of a non-root blog, unless
	// do_network_admin is overridden
	if ( is_multisite() && trs_core_do_network_admin() && !trs_is_root_blog() )
		return;

	// Show the messages
	if ( !empty( $trs->admin->notices ) ) {
	?>
		<div id="message" class="updated fade">
			<?php foreach( $trs->admin->notices as $notice ) : ?>
				<p><?php echo $notice ?></p>
			<?php endforeach ?>
		</div>
	<?php
	}
}
add_action( 'admin_notices', 'trs_core_print_admin_notices' );
add_action( 'network_admin_notices', 'trs_core_print_admin_notices' );

/**
 * Add an admin notice to the TRS queue
 *
 * Messages added with this function are displayed in trendr's general purpose admin notices
 * box. It is recommended that you hook this function to admin_init, so that your messages are
 * loaded in time.
 *
 * @package trendr Core
 * @since 1.5
 *
 * @global object $trs Global trendr settings object
 * @param string $notice The notice you are adding to the queue
 */
function trs_core_add_admin_notice( $notice ) {
	global $trs;

	if ( empty( $trs->admin->notices ) ) {
		$trs->admin->notices = array();
	}

	$trs->admin->notices[] = $notice;
}

/**
 * Verify that some TRS prerequisites are set up properly, and notify the admin if not
 *
 * On every Dashboard page, this function checks the following:
 *   - that pretty permalinks are enabled
 *   - that a TRS-compatible theme is activated
 *   - that every TRS component that needs a TRM page for a directory has one
 *   - that no TRM page has multiple TRS components associated with it
 * The administrator will be shown a notice for each check that fails.
 *
 * @package trendr Core
 */
function trs_core_activation_notice() {
	global $trm_rewrite, $trmdb, $trs;

	// Only the super admin gets warnings
	if ( !is_super_admin() )
		return;

	// On multisite installs, don't load on a non-root blog, unless do_network_admin is
	// overridden
	if ( is_multisite() && trs_core_do_network_admin() && !trs_is_root_blog() )
		return;

	// Don't show these messages during setup or upgrade
	if ( isset( $trs->maintenance_mode ) )
		return;

	/**
	 * Check to make sure that the blog setup routine has run. This can't happen during the
	 * wizard because of the order which the components are loaded. We check for multisite here
	 * on the off chance that someone has activated the blogs component and then disabled MS
	 */
	if ( trs_is_active( 'blogs' ) ) {
		$count = $trmdb->get_var( $trmdb->prepare( "SELECT COUNT(*) FROM {$trs->blogs->table_name}" ) );

		if ( !$count )
			trs_blogs_record_existing_blogs();
	}

	/**
	 * Are pretty permalinks enabled?
	 */
	if ( isset( $_POST['permalink_structure'] ) )
		return false;

	if ( empty( $trm_rewrite->permalink_structure ) ) {
		trs_core_add_admin_notice( sprintf( __( '<strong>trendr is almost ready</strong>. You must <a href="%s">update your permalink structure</a> to something other than the default for it to work.', 'trendr' ), admin_url( 'options-permalink.php' ) ) );
	}

	/**
	 * Are you using a TRS-compatible theme?
	 */

	// Get current theme info
	$ct = current_theme_info();

	// The best way to remove this notice is to add a "trendr" tag to
	// your active theme's CSS header.
	if ( !defined( 'TRS_SILENCE_THEME_NOTICE' ) && !in_array( 'trendr', (array)$ct->tags ) ) {
		trs_core_add_admin_notice( sprintf( __( "You'll need to <a href='%s'>activate a <strong>trendr-compatible theme</strong></a> to take advantage of all of trendr's features. We've bundled a default theme, but you can always <a href='%s'>install some other compatible themes</a> or <a href='%s'>update your existing trendr theme</a>.", 'trendr' ), admin_url( 'themes.php' ), network_admin_url( 'theme-install.php?type=tag&s=trendr&tab=search' ), network_admin_url( 'plugin-install.php?type=term&tab=search&s=%22trs-template-pack%22' ) ) );
	}

	/**
	 * Check for orphaned TRS components (TRS component is enabled, no TRM page exists)
	 */

	$orphaned_components = array();
	$trm_page_components  = array();

	// Only components with 'has_directory' require a TRM page to function
	foreach( $trs->loaded_components as $component_id => $is_active ) {
		if ( !empty( $trs->{$component_id}->has_directory ) ) {
			$trm_page_components[] = array(
				'id'   => $component_id,
				'name' => isset( $trs->{$component_id}->name ) ? $trs->{$component_id}->name : ucwords( $trs->{$component_id}->id )
			);
		}
	}

	// Activate and Register are special cases. They are not components but they need TRM pages.
	// If user registration is disabled, we can skip this step.
	if ( trs_get_signup_allowed() ) {
		$trm_page_components[] = array(
			'id'   => 'activate',
			'name' => __( 'Activate', 'trendr' )
		);

		$trm_page_components[] = array(
			'id'   => 'register',
			'name' => __( 'Register', 'trendr' )
		);
	}

	foreach( $trm_page_components as $component ) {
		if ( !isset( $trs->pages->{$component['id']} ) ) {
			$orphaned_components[] = $component['name'];
		}
	}

	if ( !empty( $orphaned_components ) ) {
		$admin_url = trs_get_admin_url( add_query_arg( array( 'page' => 'trs-page-settings' ), 'admin.php' ) );
		$notice    = sprintf( __( 'The following active trendr Components do not have associated trendr Pages: %2$s. <a href="%1$s" class="button-secondary">Repair</a>', 'trendr' ), $admin_url, '<strong>' . implode( '</strong>, <strong>', $orphaned_components ) . '</strong>' );

		trs_core_add_admin_notice( $notice );
	}

	/**
	 * TRS components cannot share a single TRM page. Check for duplicate assignments, and post
	 * a message if found.
	 */
	$dupe_names = array();
	$page_ids   = (array)trs_core_get_directory_page_ids();
	$dupes      = array_diff_assoc( $page_ids, array_unique( $page_ids ) );

	if ( !empty( $dupes ) ) {
		foreach( $dupes as $dupe_component => $dupe_id ) {
			$dupe_names[] = $trs->pages->{$dupe_component}->title;
		}

		// Make sure that there are no duplicate duplicates :)
		$dupe_names = array_unique( $dupe_names );
	}

	// If there are duplicates, post a message about them
	if ( !empty( $dupe_names ) ) {
		$admin_url = trs_get_admin_url( add_query_arg( array( 'page' => 'trs-page-settings' ), 'admin.php' ) );
		$notice    = sprintf( __( 'Each trendr Component needs its own trendr page. The following trendr Pages have more than one component associated with them: %2$s. <a href="%1$s" class="button-secondary">Repair</a>', 'trendr' ), $admin_url, '<strong>' . implode( '</strong>, <strong>', $dupe_names ) . '</strong>' );

		trs_core_add_admin_notice( $notice );
	}
}
add_action( 'admin_init', 'trs_core_activation_notice' );

/**
 * Returns the domain for the root blog.
 * eg: http://domain.com/ OR https://domain.com
 *
 * @package trendr Core
 * @uses get_blog_option() trendr function to fetch blog meta.
 * @return $domain The domain URL for the blog.
 */
function trs_core_get_root_domain() {
	global $trmdb;

	$domain = get_home_url( trs_get_root_blog_id() );

	return apply_filters( 'trs_core_get_root_domain', $domain );
}

/**
 * Get the current GMT time to save into the DB
 *
 * @package trendr Core
 * @since 1.2.6
 */
function trs_core_current_time( $gmt = true ) {
	// Get current time in MYSQL format
	$current_time = current_time( 'mysql', $gmt );

	return apply_filters( 'trs_core_current_time', $current_time );
}

/**
 * Adds a feedback (error/success) message to the TRM cookie so it can be
 * displayed after the page reloads.
 *
 * @package trendr Core
 *
 * @global obj $trs
 * @param str $message Feedback to give to user
 * @param str $type updated|success|error|warning
 */
function trs_core_add_message( $message, $type = '' ) {
	global $trs;

	// Success is the default
	if ( empty( $type ) )
		$type = 'success';

	// Send the values to the cookie for page reload display
	@setcookie( 'trs-message',      $message, time() + 60 * 60 * 24, COOKIEPATH );
	@setcookie( 'trs-message-type', $type,    time() + 60 * 60 * 24, COOKIEPATH );

	/***
	 * Send the values to the $trs global so we can still output messages
	 * without a page reload
	 */
	$trs->template_message      = $message;
	$trs->template_message_type = $type;
}

/**
 * Checks if there is a feedback message in the TRM cookie, if so, adds a
 * "template_notices" action so that the message can be parsed into the template
 * and displayed to the user.
 *
 * After the message is displayed, it removes the message vars from the cookie
 * so that the message is not shown to the user multiple times.
 *
 * @package trendr Core
 * @global $trs_message The message text
 * @global $trs_message_type The type of message (error/success)
 * @uses setcookie() Sets a cookie value for the user.
 */
function trs_core_setup_message() {
	global $trs;

	if ( empty( $trs->template_message ) && isset( $_COOKIE['trs-message'] ) )
		$trs->template_message = $_COOKIE['trs-message'];

	if ( empty( $trs->template_message_type ) && isset( $_COOKIE['trs-message-type'] ) )
		$trs->template_message_type = $_COOKIE['trs-message-type'];

	add_action( 'template_notices', 'trs_core_render_message' );

	@setcookie( 'trs-message',      false, time() - 1000, COOKIEPATH );
	@setcookie( 'trs-message-type', false, time() - 1000, COOKIEPATH );
}
add_action( 'trs_actions', 'trs_core_setup_message', 5 );

/**
 * Renders a feedback message (either error or success message) to the theme template.
 * The hook action 'template_notices' is used to call this function, it is not called directly.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 */
function trs_core_render_message() {
	global $trs;

	if ( isset( $trs->template_message ) && $trs->template_message ) :
		$type = ( 'success' == $trs->template_message_type ) ? 'updated' : 'error'; ?>

		<div id="message" class="<?php echo $type; ?>">
			<p><?php echo stripslashes( esc_attr( $trs->template_message ) ); ?></p>
		</div>

	<?php

		do_action( 'trs_core_render_message' );

	endif;
}

/**
 * Format numbers the trendr way
 *
 * @param str $number
 * @param bool $decimals
 * @return str
 */
function trs_core_number_format( $number, $decimals = false ) {
	// Check we actually have a number first.
	if ( empty( $number ) )
		return $number;

	return apply_filters( 'trs_core_number_format', number_format( $number, $decimals ), $number, $decimals );
}

/**
 * Based on function created by Dunstan Orchard - http://1976design.com
 *
 * This function will return an English representation of the time elapsed
 * since a given date.
 * eg: 2 hours and 50 minutes
 * eg: 4 days
 * eg: 4 weeks and 6 days
 *
 * @package trendr Core
 * @param $older_date int Unix timestamp of date you want to calculate the time since for
 * @param $newer_date int Unix timestamp of date to compare older date to. Default false (current time).
 * @return str The time since.
 */
function trs_core_time_since( $older_date, $newer_date = false ) {

	// Setup the strings
	$unknown_text   = apply_filters( 'trs_core_time_since_unknown_text',   __( 'sometime',  'trendr' ) );
	$right_now_text = apply_filters( 'trs_core_time_since_right_now_text', __( 'right now', 'trendr' ) );
	$ago_text       = apply_filters( 'trs_core_time_since_ago_text',       __( '%s',    'trendr' ) );

	// array of time period chunks
	$chunks = array(
		array( 60 * 60 * 24 * 365 , __( 'Y',   'trendr' ), __( 'Y',   'trendr' ) ),
		array( 60 * 60 * 24 * 30 ,  __( 'M',  'trendr' ), __( 'M',  'trendr' ) ),
		array( 60 * 60 * 24 * 7,    __( 'W',   'trendr' ), __( 'w',   'trendr' ) ),
		array( 60 * 60 * 24 ,       __( 'd',    'trendr' ), __( 'd',    'trendr' ) ),
		array( 60 * 60 ,            __( 'h',   'trendr' ), __( 'h',   'trendr' ) ),
		array( 60 ,                 __( 'm', 'trendr' ), __( 'm', 'trendr' ) ),
		array( 1,                   __( 's', 'trendr' ), __( 's', 'trendr' ) )
	);

	if ( !empty( $older_date ) && !is_numeric( $older_date ) ) {
		$time_chunks = explode( ':', str_replace( ' ', ':', $older_date ) );
		$date_chunks = explode( '-', str_replace( ' ', '-', $older_date ) );
		$older_date  = gmmktime( (int)$time_chunks[1], (int)$time_chunks[2], (int)$time_chunks[3], (int)$date_chunks[1], (int)$date_chunks[2], (int)$date_chunks[0] );
	}


	/**
	 * $newer_date will equal false if we want to know the time elapsed between
	 * a date and the current time. $newer_date will have a value if we want to
	 * work out time elapsed between two known dates.
	 */
	$newer_date = ( !$newer_date ) ? strtotime( trs_core_current_time() ) : $newer_date;

	// Difference in seconds
	$since = $newer_date - $older_date;

	// Something went wrong with date calculation and we ended up with a negative date.
	if ( 0 > $since ) {
		$output = $unknown_text;

	/**
	 * We only want to output two chunks of time here, eg:
	 * x years, xx months
	 * x days, xx hours
	 * so there's only two bits of calculation below:
	 */
	} else {

		// Step one: the first chunk
		for ( $i = 0, $j = count( $chunks ); $i < $j; ++$i ) {
			$seconds = $chunks[$i][0];

			// Finding the biggest chunk (if the chunk fits, break)
			if ( ( $count = floor($since / $seconds) ) != 0 ) {
				break;
			}
		}

		// If $i iterates all the way to $j, then the event happened 0 seconds ago
		if ( !isset( $chunks[$i] ) ) {
			$output = $right_now_text;

		} else {

			// Set output var
			$output = ( 1 == $count ) ? '1 '. $chunks[$i][1] : $count . ' ' . $chunks[$i][2];

			// Step two: the second chunk
			if ( $i + 2 < $j ) {
				$seconds2 = $chunks[$i + 1][0];
				$name2 = $chunks[$i + 1][1];

				if ( ( $count2 = floor( ( $since - ( $seconds * $count ) ) / $seconds2 ) ) != 0 ) {
					// Add to output var
					$output .= ( 1 == $count2 ) ? _x( ',', 'Separator in time since', 'trendr' ) . ' 1 '. $chunks[$i + 1][1] : _x( ',', 'Separator in time since', 'trendr' ) . ' ' . $count2 . ' ' . $chunks[$i + 1][2];
				}
			}

			// No output, so happened right now
			if ( !(int)trim( $output ) ) {
				$output = $right_now_text;
			}
		}
	}

	// Append 'ago' to the end of time if not 'right now'
	if ( $output != $right_now_text ) {
		$output = sprintf( $ago_text, $output );
	}

	return $output;
}

/**
 * Record user activity to the database. Many functions use a "last active" feature to
 * show the length of time since the user was last active.
 * This function will update that time as a usermeta setting for the user every 5 minutes.
 *
 * @package trendr Core
 * @global $userdata trendr user data for the current logged in user.
 * @uses trs_update_user_meta() TRS function to update user metadata in the usermeta table.
 */
function trs_core_record_activity() {
	global $trs;

	if ( !is_user_logged_in() )
		return false;

	$user_id = $trs->loggedin_user->id;

	if ( trs_core_is_user_spammer( $user_id ) || trs_core_is_user_deleted( $user_id ) )
		return false;

	$activity = trs_get_user_meta( $user_id, 'last_activity', true );

	if ( !is_numeric( $activity ) )
		$activity = strtotime( $activity );

	// Get current time
	$current_time = trs_core_current_time();

	if ( empty( $activity ) || strtotime( $current_time ) >= strtotime( '+5 minutes', $activity ) )
		trs_update_user_meta( $user_id, 'last_activity', $current_time );
}
add_action( 'trm_head', 'trs_core_record_activity' );

/**
 * Formats last activity based on time since date given.
 *
 * @package trendr Core
 * @param last_activity_date The date of last activity.
 * @param $before The text to prepend to the activity time since figure.
 * @param $after The text to append to the activity time since figure.
 * @uses trs_core_time_since() This function will return an English representation of the time elapsed.
 */
function trs_core_get_last_activity( $last_activity_date, $string ) {
	if ( !$last_activity_date || empty( $last_activity_date ) )
		$last_active = __( 'not recently active', 'trendr' );
	else
		$last_active = sprintf( $string, trs_core_time_since( $last_activity_date ) );

	return apply_filters( 'trs_core_get_last_activity', $last_active, $last_activity_date, $string );
}

/**
 * Get the path of of the current site.
 *
 * @package trendr Core
 *
 * @global $trs $trs
 * @global object $current_site
 * @return string
 */
function trs_core_get_site_path() {
	global $trs, $current_site;

	if ( is_multisite() )
		$site_path = $current_site->path;
	else {
		$site_path = (array) explode( '/', home_url() );

		if ( count( $site_path ) < 2 )
			$site_path = '/';
		else {
			// Unset the first three segments (http(s)://domain.com part)
			unset( $site_path[0] );
			unset( $site_path[1] );
			unset( $site_path[2] );

			if ( !count( $site_path ) )
				$site_path = '/';
			else
				$site_path = '/' . implode( '/', $site_path ) . '/';
		}
	}

	return apply_filters( 'trs_core_get_site_path', $site_path );
}

/**
 * Performs a status safe trm_redirect() that is compatible with trs_catch_uri()
 *
 * @package trendr Core
 * @global $trs_no_status_set Makes sure that there are no conflicts with status_header() called in trs_core_do_catch_uri()
 * @uses get_themes()
 * @return An array containing all of the themes.
 */
function trs_core_redirect( $location, $status = 302 ) {
	global $trs_no_status_set;

	// Make sure we don't call status_header() in trs_core_do_catch_uri()
	// as this conflicts with trm_redirect()
	$trs_no_status_set = true;

	trm_redirect( $location, $status );
	die;
}

/**
 * Returns the referrer URL without the http(s)://
 *
 * @package trendr Core
 * @return The referrer URL
 */
function trs_core_referrer() {
	$referer = explode( '/', trm_get_referer() );
	unset( $referer[0], $referer[1], $referer[2] );
	return implode( '/', $referer );
}

/**
 * Adds illegal names to TRM so that root components will not conflict with
 * blog names on a subdirectory installation.
 *
 * For example, it would stop someone creating a blog with the slug "groups".
 */
function trs_core_add_illegal_names() {
	update_site_option( 'illegal_names', get_site_option( 'illegal_names' ), array() );
}

/**
 * A javascript free implementation of the search functions in trendr
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 * @param string $slug The slug to redirect to for searching.
 */
function trs_core_action_search_site( $slug = '' ) {
	global $trs;

	if ( !trs_is_current_component( trs_get_search_slug() ) )
		return;

	if ( empty( $_POST['search-terms'] ) ) {
		trs_core_redirect( trs_get_root_domain() );
		return;
	}

	$search_terms = stripslashes( $_POST['search-terms'] );
	$search_which = !empty( $_POST['search-which'] ) ? $_POST['search-which'] : '';
	$query_string = '/?s=';

	if ( empty( $slug ) ) {
		switch ( $search_which ) {
			case 'posts':
				$slug = '';
				$var  = '/?s=';

				// If posts aren't displayed on the front page, find the post page's slug.
				if ( 'page' == get_option( 'show_on_front' ) ) {
					$page = get_post( get_option( 'page_for_posts' ) );

					if ( !is_trm_error( $page ) && !empty( $page->post_name ) ) {
						$slug = $page->post_name;
						$var  = '?s=';
					}
				}
				break;

			case 'blogs':
				$slug = trs_is_active( 'blogs' )  ? trs_get_blogs_root_slug()  : '';
				break;

			case 'forums':
				$slug = trs_is_active( 'forums' ) ? trs_get_forums_root_slug() : '';
				$query_string = '/?fs=';
				break;

			case 'groups':
				$slug = trs_is_active( 'groups' ) ? trs_get_groups_root_slug() : '';
				break;

			case 'members':
			default:
				$slug = trs_get_members_root_slug();
				break;
		}

		if ( empty( $slug ) && 'posts' != $search_which ) {
			trs_core_redirect( trs_get_root_domain() );
			return;
		}
	}

	trs_core_redirect( apply_filters( 'trs_core_search_site', home_url( $slug . $query_string . urlencode( $search_terms ) ), $search_terms ) );
}
add_action( 'trs_init', 'trs_core_action_search_site', 7 );

/**
 * Prints the generation time in the footer of the site.
 *
 * @package trendr Core
 */
function trs_core_print_generation_time() {
?>

<!-- Generated in <?php timer_stop(1); ?> seconds. (<?php echo get_num_queries(); ?> q) -->

	<?php
}
add_action( 'trm_footer', 'trs_core_print_generation_time' );

/**
 * Load the trendr translation file for current language
 *
 * @package trendr Core
 */
function trs_core_load_trendr_textdomain() {
	$locale        = apply_filters( 'trendr_locale', get_locale() );
	$mofile        = sprintf( 'trendr-%s.mo', $locale );
	$mofile_global = TRM_LANG_DIR . '/' . $mofile;
	$mofile_local  = TRS_PLUGIN_DIR . '/trs-languages/' . $mofile;

	if ( file_exists( $mofile_global ) )
		return load_textdomain( 'trendr', $mofile_global );
	elseif ( file_exists( $mofile_local ) )
		return load_textdomain( 'trendr', $mofile_local );
	else
		return false;
}
add_action ( 'trs_init', 'trs_core_load_trendr_textdomain', 2 );

function trs_core_add_ajax_hook() {
	// Theme only, we already have the trm_ajax_ hook firing in Backend-WeaprEcqaKejUbRq-trendr
	if ( !defined( 'TRM_ADMIN' ) && isset( $_REQUEST['action'] ) )
		do_action( 'trm_ajax_' . $_REQUEST['action'] );
}
add_action( 'trs_init', 'trs_core_add_ajax_hook' );



/**
 * When switching from single to multisite we need to copy blog options to
 * site options.
 *
 * @package trendr Core
 * @todo Does this need to be here anymore after the introduction of trs_get_option etc?
 */
function trs_core_activate_site_options( $keys = array() ) {
	global $trs;

	if ( !empty( $keys ) && is_array( $keys ) ) {
		$errors = false;

		foreach ( $keys as $key => $default ) {
			if ( empty( $trs->site_options[ $key ] ) ) {
				$trs->site_options[ $key ] = trs_get_option( $key, $default );

				if ( !trs_update_option( $key, $trs->site_options[ $key ] ) )
					$errors = true;
			}
		}

		if ( empty( $errors ) )
			return true;
	}

	return false;
}

/**
 * trendr uses common options to store configuration settings. Many of these
 * settings are needed at run time. Instead of fetching them all and adding many
 * initial queries to each page load, let's fetch them all in one go.
 *
 * @package trendr Core
 * @todo Use settings API and audit these methods
 */
function trs_core_get_root_options() {
	global $trmdb;

	// These options come from the root blog options table
	$root_blog_options = apply_filters( 'trs_core_site_options', array(

		// trendr core settings
		'trs-deactivated-components'       => serialize( array( ) ),
		'trs-blogs-first-install'          => '0',
		'trs-disable-blogforum-comments'  => '0',
		'trs-xprofile-base-group-name'     => 'Base',
		'trs-xprofile-fullname-field-name' => 'Name',
		'trs-disable-profile-sync'         => '0',
		'trs-disable-portrait-uploads'       => '0',
		'trs-disable-account-deletion'     => '0',
		'trs-disable-blogforum-comments'   => '0',
		'bb-config-location'              => ABSPATH . 'bb-config.php',
		'hide-loggedout-adminbar'         => '0',

		// Useful trendr settings
		'registration'                    => '0',
		'portrait_default'                  => 'mysteryman'
	) );

	$root_blog_option_keys  = array_keys( $root_blog_options );
	$blog_options_keys      = "'" . join( "', '", (array) $root_blog_option_keys ) . "'";
	$blog_options_table	= trs_is_multiblog_mode() ? $trmdb->options : $trmdb->get_blog_prefix( trs_get_root_blog_id() ) . 'options';

	$blog_options_query     = $trmdb->prepare( "SELECT option_name AS name, option_value AS value FROM {$blog_options_table} WHERE option_name IN ( {$blog_options_keys} )" );
	$root_blog_options_meta = $trmdb->get_results( $blog_options_query );

	// On Multisite installations, some options must always be fetched from sitemeta
	if ( is_multisite() ) {
		$network_options = apply_filters( 'trs_core_network_options', array(
			'tags_blog_id'       => '0',
			'sitewide_tags_blog' => '',
			'registration'       => '0',
			'fileupload_maxk'    => '1500'
		) );

		$current_site           = get_current_site();
		$network_option_keys    = array_keys( $network_options );
		$sitemeta_options_keys  = "'" . join( "', '", (array) $network_option_keys ) . "'";
		$sitemeta_options_query = $trmdb->prepare( "SELECT meta_key AS name, meta_value AS value FROM {$trmdb->sitemeta} WHERE meta_key IN ( {$sitemeta_options_keys} ) AND site_id = %d", $current_site->id );
		$network_options_meta   = $trmdb->get_results( $sitemeta_options_query );

		// Sitemeta comes second in the merge, so that network 'registration' value wins
		$root_blog_options_meta = array_merge( $root_blog_options_meta, $network_options_meta );
	}

	// Missing some options, so do some one-time fixing
	if ( empty( $root_blog_options_meta ) || ( count( $root_blog_options_meta ) < count( $root_blog_option_keys ) ) ) {

	// Unset the query - We'll be resetting it soon
	unset( $root_blog_options_meta );

	// Loop through options
	foreach ( $root_blog_options as $old_meta_key => $old_meta_default ) {
		// Clear out the value from the last time around
		unset( $old_meta_value );

		// Get old site option
		if ( is_multisite() )
			$old_meta_value = get_site_option( $old_meta_key );

		// No site option so look in root blog
		if ( empty( $old_meta_value ) )
			$old_meta_value = trs_get_option( $old_meta_key, $old_meta_default );

		// Update the root blog option
		trs_update_option( $old_meta_key, $old_meta_value );

		// Update the global array
		$root_blog_options_meta[$old_meta_key] = $old_meta_value;
	}

	// We're all matched up
	} else {
		// Loop through our results and make them usable
		foreach ( $root_blog_options_meta as $root_blog_option )
			$root_blog_options[$root_blog_option->name] = $root_blog_option->value;

		// Copy the options no the return val
		$root_blog_options_meta = $root_blog_options;

		// Clean up our temporary copy
		unset( $root_blog_options );
	}

	return apply_filters( 'trs_core_get_root_options', $root_blog_options_meta );
}

/**
 * This function originally let plugins add support for pages in the root of the install.
 * These root level pages are now handled by actual trendr pages and this function is now
 * a convenience for compatibility with the new method.
 *
 * @global $trs trendr global settings
 * @param $slug str The slug of the component
 */
function trs_core_add_root_component( $slug ) {
	global $trs;

	if ( empty( $trs->pages ) )
		$trs->pages = trs_core_get_directory_pages();

	$match = false;

	// Check if the slug is registered in the $trs->pages global
	foreach ( (array)$trs->pages as $key => $page ) {
		if ( $key == $slug || $page->slug == $slug )
			$match = true;
	}

	// If there was no match, add a page for this root component
	if ( empty( $match ) ) {
		$trs->add_root[] = $slug;
	}

	// Make sure that this component is registered as requiring a top-level directory
	if ( isset( $trs->{$slug} ) ) {
		$trs->loaded_components[$trs->{$slug}->slug] = $trs->{$slug}->id;
		$trs->{$slug}->has_directory = true;
	}
}

function trs_core_create_root_component_page() {
	global $trs;

	$new_page_ids = array();

	foreach ( (array)$trs->add_root as $slug )
		$new_page_ids[$slug] = trm_insert_post( array( 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_title' => ucwords( $slug ), 'post_status' => 'publish', 'post_type' => 'page' ) );

	$page_ids = array_merge( (array) $new_page_ids, (array) trs_core_get_directory_page_ids() );
	trs_core_update_directory_page_ids( $page_ids );
}

/**
 * Is this the root blog ID?
 *
 * @package trendr
 * @since 1.5
 *
 * @param int $blog_id Optional. Defaults to the current blog id.
 * @return bool $is_root_blog Returns true if this is trs_get_root_blog_id().
 */
function trs_is_root_blog( $blog_id = 0 ) {
	// Assume false
	$is_root_blog = false;

	// Use current blog if no ID is passed
	if ( empty( $blog_id ) )
		$blog_id = get_current_blog_id();

	// Compare to root blog ID
	if ( $blog_id == trs_get_root_blog_id() )
		$is_root_blog = true;

	return apply_filters( 'trs_is_root_blog', (bool) $is_root_blog );
}

/**
 * Is this trs_get_root_blog_id()?
 *
 * @package trendr
 * @since 1.5
 *
 * @param int $blog_id Optional. Defaults to the current blog id.
 * @return bool $is_root_blog Returns true if this is trs_get_root_blog_id().
 */
function trs_get_root_blog_id( $blog_id = false ) {

	// Define on which blog ID trendr should run
	if ( !defined( 'TRS_ROOT_BLOG' ) ) {

		// Root blog is the main site on this network
		if ( is_multisite() && !trs_is_multiblog_mode() ) {
			$current_site = get_current_site();
			$root_blog_id = $current_site->blog_id;

		// Root blog is whatever the current site is (could be any site on the network)
		} elseif ( is_multisite() && trs_is_multiblog_mode() ) {
			$root_blog_id = get_current_blog_id();

		// Root blog is the only blog on this network
		} elseif( !is_multisite() ) {
			$root_blog_id = 1;
		}

		define( 'TRS_ROOT_BLOG', $root_blog_id );

	// Root blog is defined
	} else {
		$root_blog_id = TRS_ROOT_BLOG;
	}

	return apply_filters( 'trs_get_root_blog_id', (int) $root_blog_id );
}

/**
 * Get the meta_key for a given piece of user metadata
 *
 * trendr stores a number of pieces of userdata in the trendr central usermeta table. In
 * order to allow plugins to enable multiple instances of trendr on a single TRM installation,
 * TRS's usermeta keys are filtered with this function, so that they can be altered on the fly.
 *
 * Plugin authors should use TRS's _user_meta() functions, which bakes in trs_get_user_meta_key().
 *    $last_active = trs_get_user_meta( $user_id, 'last_activity', true );
 * If you have to use TRM's _user_meta() functions for some reason, you should use this function, eg
 *    $last_active = get_user_meta( $user_id, trs_get_user_meta_key( 'last_activity' ), true );
 * If using the TRM functions, do not not hardcode your meta keys.
 *
 * @package trendr
 * @since 1.5
 *
 * @uses apply_filters() Filter trs_get_user_meta_key to modify keys individually
 * @param str $key
 * @return str $key
 */
function trs_get_user_meta_key( $key = false ) {
	return apply_filters( 'trs_get_user_meta_key', $key );
}

/**
 * Get a piece of usermeta
 *
 * This is a wrapper for get_user_meta() that allows for easy use of trs_get_user_meta_key(), thereby
 * increasing compatibility with non-standard TRS setups.
 *
 * @package trendr
 * @since 1.5
 *
 * @uses trs_get_user_meta_key() For a filterable version of the meta key
 * @uses get_user_meta() See get_user_meta() docs for more details on parameters
 * @param int $user_id The id of the user whose meta you're fetching
 * @param string $key The meta key to retrieve.
 * @param bool $single Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
 *  is true.
 */
function trs_get_user_meta( $user_id, $key, $single = false ) {
	return get_user_meta( $user_id, trs_get_user_meta_key( $key ), $single );
}

/**
 * Update a piece of usermeta
 *
 * This is a wrapper for update_user_meta() that allows for easy use of trs_get_user_meta_key(),
 * thereby increasing compatibility with non-standard TRS setups.
 *
 * @package trendr
 * @since 1.5
 *
 * @uses trs_get_user_meta_key() For a filterable version of the meta key
 * @uses update_user_meta() See update_user_meta() docs for more details on parameters
 * @param int $user_id The id of the user whose meta you're setting
 * @param string $key The meta key to set.
 * @param mixed $value Metadata value.
 * @param mixed $prev_value Optional. Previous value to check before removing.
 * @return bool False on failure, true if success.
 */
function trs_update_user_meta( $user_id, $key, $value, $prev_value = '' ) {
	return update_user_meta( $user_id, trs_get_user_meta_key( $key ), $value, $prev_value );
}

/**
 * Delete a piece of usermeta
 *
 * This is a wrapper for delete_user_meta() that allows for easy use of trs_get_user_meta_key(),
 * thereby increasing compatibility with non-standard TRS setups.
 *
 * @package trendr
 * @since 1.5
 *
 * @uses trs_get_user_meta_key() For a filterable version of the meta key
 * @uses delete_user_meta() See delete_user_meta() docs for more details on parameters
 * @param int $user_id The id of the user whose meta you're deleting
 * @param string $key The meta key to delete.
 * @param mixed $value Optional. Metadata value.
 * @return bool False for failure. True for success.
 */
function trs_delete_user_meta( $user_id, $key, $value = '' ) {
	return delete_user_meta( $user_id, trs_get_user_meta_key( $key ), $value );
}

/**
 * Are we running username compatibility mode?
 *
 * @package trendr
 * @since 1.5
 *
 * @uses apply_filters() Filter 'trs_is_username_compatibility_mode' to alter
 * @return bool False when compatibility mode is disabled (default); true when enabled
 */
function trs_is_username_compatibility_mode() {
	return apply_filters( 'trs_is_username_compatibility_mode', defined( 'TRS_ENABLE_USERNAME_COMPATIBILITY_MODE' ) && TRS_ENABLE_USERNAME_COMPATIBILITY_MODE );
}

/**
 * Are we running multiblog mode?
 *
 * Note that TRS_ENABLE_MULTIBLOG is different from (but dependent on) TRM Multisite. "Multiblog" is
 * a TRS setup that allows TRS content to be viewed in the theme, and with the URL, of every blog
 * on the network. Thus, instead of having all 'boonebgorges' links go to
 *   http://example.com/members/boonebgorges
 * on the root blog, each blog will have its own version of the same profile content, eg
 *   http://site2.example.com/members/boonebgorges (for subdomains)
 *   http://example.com/site2/members/boonebgorges (for subdirectories)
 *
 * Multiblog mode is disabled by default, meaning that all TRS content must be viewed on the root
 * blog.
 *
 * @package trendr
 * @since 1.5
 *
 * @uses apply_filters() Filter 'trs_is_multiblog_mode' to alter
 * @return bool False when multiblog mode is disabled (default); true when enabled
 */
function trs_is_multiblog_mode() {
	return apply_filters( 'trs_is_multiblog_mode', is_multisite() && defined( 'TRS_ENABLE_MULTIBLOG' ) && TRS_ENABLE_MULTIBLOG );
}

/**
 * Should we use the TRM admin bar?
 *
 * The TRM Admin Bar, introduced in TRM 3.1, is fully supported in trendr as of TRS 1.5.
 *
 * For the TRS 1.5 development cycle, the BuddyBar will remain the default navigation for TRS
 * installations. In the future, this behavior will be changed, so that the TRM Admin Bar is the
 * default.
 *
 * @package trendr
 * @since 1.5
 *
 * @uses apply_filters() Filter 'trs_use_trm_admin_bar' to alter
 * @return bool False when TRM Admin Bar support is disabled (default); true when enabled
 */
function trs_use_trm_admin_bar() {
	return apply_filters( 'trs_use_trm_admin_bar', defined( 'TRS_USE_TRM_ADMIN_BAR' ) && TRS_USE_TRM_ADMIN_BAR );
}

/**
 * Are oembeds allowed in activity items?
 *
 * @return bool False when activity embed support is disabled; true when enabled (default)
 * @since 1.5
 */
function trs_use_embed_in_activity() {
	return apply_filters( 'trs_use_oembed_in_activity', !defined( 'TRS_EMBED_DISABLE_ACTIVITY' ) || !TRS_EMBED_DISABLE_ACTIVITY );
}

/**
 * Are oembeds allwoed in activity replies?
 *
 * @return bool False when activity replies embed support is disabled; true when enabled (default)
 * @since 1.5
 */
function trs_use_embed_in_activity_replies() {
	return apply_filters( 'trs_use_embed_in_activity_replies', !defined( 'TRS_EMBED_DISABLE_ACTIVITY_REPLIES' ) || !TRS_EMBED_DISABLE_ACTIVITY_REPLIES );
}

/**
 * Are oembeds allowed in forum posts?
 *
 * @return bool False when form post embed support is disabled; true when enabled (default)
 * @since 1.5
 */
function trs_use_embed_in_forum_posts() {
	return apply_filters( 'trs_use_embed_in_forum_posts', !defined( 'TRS_EMBED_DISABLE_FORUM_POSTS' ) || !TRS_EMBED_DISABLE_FORUM_POSTS );
}

/**
 * Are oembeds allowed in private messages?
 *
 * @return bool False when form post embed support is disabled; true when enabled (default)
 * @since 1.5
 */
function trs_use_embed_in_private_messages() {
	return apply_filters( 'trs_use_embed_in_private_messages', !defined( 'TRS_EMBED_DISABLE_PRIVATE_MESSAGES' ) || !TRS_EMBED_DISABLE_PRIVATE_MESSAGES );
}

/**
 * Output the correct URL based on trendr and trendr configuration
 *
 * @package trendr
 * @since 1.5
 *
 * @param string $path
 * @param string $scheme
 *
 * @uses trs_get_admin_url()
 */
function trs_admin_url( $path = '', $scheme = 'admin' ) {
	echo trs_get_admin_url( $path, $scheme );
}
	/**
	 * Return the correct URL based on trendr and trendr configuration
	 *
	 * @package trendr
	 * @since 1.5
	 *
	 * @param string $path
	 * @param string $scheme
	 *
	 * @uses trs_core_do_network_admin()
	 * @uses network_admin_url()
	 * @uses admin_url()
	 */
	function trs_get_admin_url( $path = '', $scheme = 'admin' ) {

		// Links belong in network admin
		if ( trs_core_do_network_admin() )
			$url = network_admin_url( $path, $scheme );

		// Links belong in site admin
		else
			$url = admin_url( $path, $scheme );

		return $url;
	}

/** Global Manipulators *******************************************************/

/**
 * Set the $trs->is_directory global
 *
 * @global obj $trs
 * @param bool $is_directory
 * @param str $component
 */
function trs_update_is_directory( $is_directory = false, $component = '' ) {
	global $trs;

	if ( empty( $component ) )
		$component = trs_current_component();

	$trs->is_directory = apply_filters( 'trs_update_is_directory', $is_directory, $component );
}

/**
 * Set the $trs->is_item_admin global
 *
 * @global obj $trs
 * @param bool $is_item_admin
 * @param str $component
 */
function trs_update_is_item_admin( $is_item_admin = false, $component = '' ) {
	global $trs;

	if ( empty( $component ) )
		$component = trs_current_component();

	$trs->is_item_admin = apply_filters( 'trs_update_is_item_admin', $is_item_admin, $component );
}

/**
 * Set the $trs->is_item_mod global
 *
 * @global obj $trs
 * @param bool $is_item_mod
 * @param str $component
 */
function trs_update_is_item_mod( $is_item_mod = false, $component = '' ) {
	global $trs;

	if ( empty( $component ) )
		$component = trs_current_component();

	$trs->is_item_mod = apply_filters( 'trs_update_is_item_mod', $is_item_mod, $component );
}

/**
 * Trigger a 404
 *
 * @global object $trs Global trendr settings object
 * @global TRM_Query $trm_query trendr query object
 * @param string $redirect If 'remove_canonical_direct', remove trendr' "helpful" redirect_canonical action.
 * @since 1.5
 */
function trs_do_404( $redirect = 'remove_canonical_direct' ) {
	global $trs, $trm_query;

	do_action( 'trs_do_404', $redirect );

	$trm_query->set_404();
	status_header( 404 );
	nocache_headers();

	if ( 'remove_canonical_direct' == $redirect )
		remove_action( 'template_redirect', 'redirect_canonical' );
}
?>
