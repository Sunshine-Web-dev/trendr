<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * trs_core_exclude_pages()
 *
 * Excludes specific pages from showing on page listings, for example the "Activation" page.
 *
 * @package trendr Core
 * @uses trs_is_active() checks if a trendr component is active.
 * @return array The list of page ID's to exclude
 */
function trs_core_exclude_pages( $pages ) {
	global $trs;
	
	if ( trs_is_root_blog() ) {
		if ( !empty( $trs->pages->activate ) )
			$pages[] = $trs->pages->activate->id;
	
		if ( !empty( $trs->pages->register ) )
			$pages[] = $trs->pages->register->id;
	
		if ( !empty( $trs->pages->forums ) && ( !trs_is_active( 'forums' ) || ( trs_is_active( 'forums' ) && trs_forums_has_directory() && !trs_forums_is_installed_correctly() ) ) )
			$pages[] = $trs->pages->forums->id;
	}

	return apply_filters( 'trs_core_exclude_pages', $pages );
}
add_filter( 'trm_list_pages_excludes', 'trs_core_exclude_pages' );

/**
 * trs_core_email_from_name_filter()
 *
 * Sets the "From" name in emails sent to the name of the site and not "WordPress"
 *
 * @package trendr Core
 * @uses get_blog_option() fetches the value for a meta_key in the trm_X_options table
 * @return The blog name for the root blog
 */
function trs_core_email_from_name_filter() {
 	return apply_filters( 'trs_core_email_from_name_filter', trm_specialchars_decode( get_blog_option( trs_get_root_blog_id(), 'blogname' ), ENT_QUOTES ) );
}
add_filter( 'trm_mail_from_name', 'trs_core_email_from_name_filter' );

/**
 * trs_core_email_from_name_filter()
 *
 * Sets the "From" address in emails sent
 *
 * @package trendr Core
 * @return noreply@sitedomain email address
 */
function trs_core_email_from_address_filter() {
	$domain = (array) explode( '/', site_url() );

	return apply_filters( 'trs_core_email_from_address_filter', 'noreply@' . $domain[2] );
}
add_filter( 'trm_mail_from', 'trs_core_email_from_address_filter' );

/**
 * trs_core_allow_default_theme()
 *
 * On multiblog installations you must first allow themes to be activated and show
 * up on the theme selection screen. This function will let the trendr bundled
 * themes show up on the root blog selection screen and bypass this step. It also
 * means that the themes won't show for selection on other blogs.
 *
 * @package trendr Core
 */
function trs_core_allow_default_theme( $themes ) {
	global $trs, $trmdb;

	if ( !is_super_admin() )
		return $themes;

	if ( $trmdb->blogid == trs_get_root_blog_id() ) {
		$themes['trs-default'] = 1;
	}

	return $themes;
}
add_filter( 'allowed_themes', 'trs_core_allow_default_theme' );

/**
 * trs_core_filter_comments()
 *
 * Filter the blog post comments array and insert trendr URLs for users.
 *
 * @package trendr Core
 */
function trs_core_filter_comments( $comments, $post_id ) {
	global $trmdb;

	foreach( (array)$comments as $comment ) {
		if ( $comment->user_id )
			$user_ids[] = $comment->user_id;
	}

	if ( empty( $user_ids ) )
		return $comments;

	$user_ids = implode( ',', $user_ids );

	if ( !$userdata = $trmdb->get_results( $trmdb->prepare( "SELECT ID as user_id, user_login, user_nicename FROM {$trmdb->users} WHERE ID IN ({$user_ids})" ) ) )
		return $comments;

	foreach( (array)$userdata as $user )
		$users[$user->user_id] = trs_core_get_user_domain( $user->user_id, $user->user_nicename, $user->user_login );

	foreach( (array)$comments as $i => $comment ) {
		if ( !empty( $comment->user_id ) ) {
			if ( !empty( $users[$comment->user_id] ) )
				$comments[$i]->comment_author_url = $users[$comment->user_id];
		}
	}

	return $comments;
}
add_filter( 'comments_array', 'trs_core_filter_comments', 10, 2 );

/**
 * trs_core_login_redirect()
 *
 * When a user logs in, always redirect them back to the previous page. NOT the admin area.
 *
 * @package trendr Core
 */
function trs_core_login_redirect( $redirect_to ) {
	global $trs, $trmdb;

	// Don't mess with the redirect if this is not the root blog
	if ( is_multisite() && $trmdb->blogid != trs_get_root_blog_id() )
		return $redirect_to;

	// If the redirect doesn't contain 'Backend-WeaprEcqaKejUbRq-trendr', it's OK
	if ( !empty( $_REQUEST['redirect_to'] ) && false === strpos( $_REQUEST['redirect_to'], 'Backend-WeaprEcqaKejUbRq-trendr' ) )
		return $redirect_to;

	if ( false === strpos( trm_get_referer(), 'enter.php' ) && false === strpos( trm_get_referer(), 'activate' ) && empty( $_REQUEST['nr'] ) )
		return trm_get_referer();

	return trs_get_root_domain();
}
add_filter( 'login_redirect', 'trs_core_login_redirect' );

/***
 * trs_core_filter_user_welcome_email()
 *
 * Replace the generated password in the welcome email.
 * This will not filter when the site admin registers a user.
 *
 * @uses locate_template To see if custom registration files exist
 * @param string $welcome_email Complete email passed through WordPress
 * @return string Filtered $welcome_email with 'PASSWORD' replaced by [User Set]
 */
function trs_core_filter_user_welcome_email( $welcome_email ) {
	/* Don't touch the email if we don't have a custom registration template */
	if ( '' == locate_template( array( 'registration/register.php' ), false ) && '' == locate_template( array( 'register.php' ), false ) )
		return $welcome_email;

	// [User Set] Replaces 'PASSWORD' in welcome email; Represents value set by user
	return str_replace( 'PASSWORD', __( '[User Set]', 'trendr' ), $welcome_email );
}
if ( !is_admin() && empty( $_GET['e'] ) )
	add_filter( 'update_welcome_user_email', 'trs_core_filter_user_welcome_email' );

/***
 * trs_core_filter_blog_welcome_email()
 *
 * Replace the generated password in the welcome email.
 * This will not filter when the site admin registers a user.
 *
 * @uses locate_template To see if custom registration files exist
 * @param string $welcome_email Complete email passed through WordPress
 * @param integer $blog_id ID of the blog user is joining
 * @param integer $user_id ID of the user joining
 * @param string $password Password of user
 * @return string Filtered $welcome_email with $password replaced by [User Set]
 */
function trs_core_filter_blog_welcome_email( $welcome_email, $blog_id, $user_id, $password ) {
	/* Don't touch the email if we don't have a custom registration template */
	if ( '' == locate_template( array( 'registration/register.php' ), false ) && '' == locate_template( array( 'register.php' ), false ) )
		return $welcome_email;

	// [User Set] Replaces $password in welcome email; Represents value set by user
	return str_replace( $password, __( '[User Set]', 'trendr' ), $welcome_email );
}
if ( !is_admin() && empty( $_GET['e'] ) )
	add_filter( 'update_welcome_email', 'trs_core_filter_blog_welcome_email', 10, 4 );

// Notify user of signup success.
function trs_core_activation_signup_blog_notification( $domain, $path, $title, $user, $user_email, $key, $meta ) {

	// Send email with activation link.
	$activate_url = trs_get_activation_page() ."?key=$key";
	$activate_url = esc_url( $activate_url );

	$admin_email = get_site_option( 'admin_email' );

	if ( empty( $admin_email ) )
		$admin_email = 'support@' . $_SERVER['SERVER_NAME'];

	$from_name       = ( '' == get_site_option( 'site_name' ) ) ? 'WordPress' : esc_html( get_site_option( 'site_name' ) );
	$message_headers = "MIME-Version: 1.0\n" . "From: \"{$from_name}\" <{$admin_email}>\n" . "Content-Type: text/plain; charset=\"" . get_option( 'blog_charset' ) . "\"\n";
	$message         = sprintf( __( "Thanks for registering! To complete the activation of your account and blog, please click the following link:\n\n%1\$s\n\n\n\nAfter you activate, you can visit your blog here:\n\n%2\$s", 'trendr' ), $activate_url, esc_url( "http://{$domain}{$path}" ) );
	$subject         = '[' . $from_name . '] ' . sprintf(__( 'Activate %s', 'trendr' ), esc_url( 'http://' . $domain . $path ) );

	// Send the message
	$to              = apply_filters( 'trs_core_activation_signup_blog_notification_to',   $user_email, $domain, $path, $title, $user, $user_email, $key, $meta );
	$subject         = apply_filters( 'trs_core_activation_signup_blog_notification_subject', $subject, $domain, $path, $title, $user, $user_email, $key, $meta );
	$message         = apply_filters( 'trs_core_activation_signup_blog_notification_message', $message, $domain, $path, $title, $user, $user_email, $key, $meta );

	trm_mail( $to, $subject, $message, $message_headers );

	do_action( 'trs_core_sent_blog_signup_email', $admin_email, $subject, $message, $domain, $path, $title, $user, $user_email, $key, $meta );

	// Return false to stop the original TRMMU function from continuing
	return false;
}
if ( !is_admin() )
	add_filter( 'trmmu_signup_blog_notification', 'trs_core_activation_signup_blog_notification', 1, 7 );

function trs_core_activation_signup_user_notification( $user, $user_email, $key, $meta ) {

	$activate_url = trs_get_activation_page() . "?key=$key";
	$activate_url = esc_url($activate_url);
	$admin_email  = get_site_option( 'admin_email' );

	if ( empty( $admin_email ) )
		$admin_email = 'support@' . $_SERVER['SERVER_NAME'];

	// If this is an admin generated activation, add a param to email the
	// user login details
	$email = is_admin() ? '&e=1' : '';

	$from_name       = ( '' == get_site_option( 'site_name' ) ) ? 'WordPress' : esc_html( get_site_option( 'site_name' ) );
	$message_headers = "MIME-Version: 1.0\n" . "From: \"{$from_name}\" <{$admin_email}>\n" . "Content-Type: text/plain; charset=\"" . get_option( 'blog_charset' ) . "\"\n";
	$message         = sprintf( __( "Thanks for registering! To complete the activation of your account please click the following link:\n\n%s\n\n", 'trendr' ), $activate_url . $email );
	$subject         = '[' . $from_name . '] ' . __( 'Activate Your Account', 'trendr' );

	// Send the message
	$to      = apply_filters( 'trs_core_activation_signup_user_notification_to',   $user_email, $user, $user_email, $key, $meta );
	$subject = apply_filters( 'trs_core_activation_signup_user_notification_subject', $subject, $user, $user_email, $key, $meta );
	$message = apply_filters( 'trs_core_activation_signup_user_notification_message', $message, $user, $user_email, $key, $meta );

	trm_mail( $to, $subject, $message, $message_headers );

	do_action( 'trs_core_sent_user_signup_email', $admin_email, $subject, $message, $user, $user_email, $key, $meta );

	// Return false to stop the original TRMMU function from continuing
	return false;
}
if ( !is_admin() || ( is_admin() && empty( $_POST['noconfirmation'] ) ) )
	add_filter( 'trmmu_signup_user_notification', 'trs_core_activation_signup_user_notification', 1, 4 );

/**
 * Filter the page title for trendr pages
 *
 * @global object $trs trendr global settings
 * @global unknown $post
 * @global TRM_Query $trm_query WordPress query object
 * @param string $title Original page title
 * @param string $sep How to separate the various items within the page title.
 * @param string $seplocation Direction to display title
 * @return string new page title
 * @see trm_title()
 * @since 1.5
 */
function trs_modify_page_title( $title, $sep, $seplocation ) {
	global $trs, $post, $trm_query;

	// If this is not a TRS page, just return the title produced by TRM
	if ( trs_is_blog_page() )
		return $title;

	// If this is the front page of the site, return TRM's title
	if ( is_front_page() || is_home() )
		return $title;

	$title = '';

	// Displayed user
	if ( !empty( $trs->displayed_user->fullname ) && !is_404() ) {
		// translators: "displayed user's name | canonicalised component name"
		$title = strip_tags( sprintf( __( '%1$s | %2$s', 'trendr' ), trs_get_displayed_user_fullname(), __( ucwords( trs_current_component() ), 'trendr' ) ) );

	// A single group
	} elseif ( trs_is_active( 'groups' ) && !empty( $trs->groups->current_group ) && !empty( $trs->trs_options_nav[$trs->groups->current_group->slug] ) ) {
		$subnav = isset( $trs->trs_options_nav[$trs->groups->current_group->slug][$trs->current_action]['name'] ) ? $trs->trs_options_nav[$trs->groups->current_group->slug][$trs->current_action]['name'] : '';
		// translators: "group name | group nav section name"
		$title = sprintf( __( '%1$s | %2$s', 'trendr' ), $trs->trs_options_title, $subnav );

	// A single item from a component other than groups
	} elseif ( trs_is_single_item() ) {
		// translators: "component item name | component nav section name | root component name"
		$title = sprintf( __( '%1$s | %2$s | %3$s', 'trendr' ), $trs->trs_options_title, $trs->trs_options_nav[$trs->current_item][$trs->current_action]['name'], trs_get_name_from_root_slug( trs_get_root_slug() ) );

	// An index or directory
	} elseif ( trs_is_directory() ) {
		if ( !trs_current_component() )
			$title = sprintf( __( '%s Directory', 'trendr' ), __( trs_get_name_from_root_slug(), 'trendr' ) );
		else
			$title = sprintf( __( '%s Directory', 'trendr' ), __( trs_get_name_from_root_slug(), 'trendr' ) );

	// Sign up page
	} elseif ( trs_is_register_page() ) {
		$title = __( 'Create an Account', 'trendr' );

	// Activation page
	} elseif ( trs_is_activation_page() ) {
		$title = __( 'Activate your Account', 'trendr' );

	// Group creation page
	} elseif ( trs_is_group_create() ) {
		$title = __( 'Create a Group', 'trendr' );

	// Blog creation page
	} elseif ( trs_is_create_blog() ) {
		$title = __( 'Create a Site', 'trendr' );
	}

	// Some TRS nav items contain item counts. Remove them
	$title = preg_replace( '|<span>[0-9]+</span>|', '', $title );

	return apply_filters( 'trs_modify_page_title', $title . " $sep ", $title, $sep, $seplocation );
}
add_filter( 'trm_title', 'trs_modify_page_title', 10, 3 );
add_filter( 'trs_modify_page_title', 'trmtexturize'     );
add_filter( 'trs_modify_page_title', 'convert_chars'   );
add_filter( 'trs_modify_page_title', 'esc_html'        );

?>