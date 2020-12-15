<?php
/**
 * Trnder Administration Template Header
 *
 * @package Trnder
 * @subpackage Administration
 */

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
if ( ! defined( 'TRM_ADMIN' ) )
	require_once( './admin.php' );

get_admin_page_title();
$title = esc_html( strip_tags( $title ) );

if ( is_network_admin() )
	$admin_title = __( 'Network Admin' );
elseif ( is_user_admin() )
	
	$admin_title = get_bloginfo( 'name' );



$admin_title = apply_filters( 'admin_title', $admin_title, $title );

trm_user_settings();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<?php

trm_admin_css();


$admin_body_class = preg_replace('/[^a-z0-9_-]+/i', '-', $hook_suffix);
?>
<script type="text/javascript">
//<![CDATA[
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof trmOnload!='function'){trmOnload=func;}else{var oldonload=trmOnload;trmOnload=function(){oldonload();func();}}};
var userSettings = {
		'url': '<?php echo SITECOOKIEPATH; ?>',
		'uid': '<?php if ( ! isset($current_user) ) $current_user = trm_get_current_user(); echo $current_user->ID; ?>',
		'time':'<?php echo time() ?>'
	},
	ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>',
	pagenow = '<?php echo $current_screen->id; ?>',
	typenow = '<?php if ( isset($current_screen->post_type) ) echo $current_screen->post_type; ?>',
	adminpage = '<?php echo $admin_body_class; ?>',
	thousandsSeparator = '<?php echo addslashes( $trm_locale->number_format['thousands_sep'] ); ?>',
	decimalPoint = '<?php echo addslashes( $trm_locale->number_format['decimal_point'] ); ?>',
	isRtl = <?php echo (int) is_rtl(); ?>;
//]]>
</script>
<?php

if ( in_array( $pagenow, array('post.php', 'post-new.php') ) ) {
	trm_enqueue_script('quicktags');
}

do_action('admin_enqueue_scripts', $hook_suffix);
do_action("admin_print_styles-$hook_suffix");
do_action('admin_print_styles');
do_action("admin_print_scripts-$hook_suffix");
do_action('admin_print_scripts');
do_action("admin_head-$hook_suffix");
do_action('admin_head');

if ( get_user_setting('mfold') == 'f' )
	$admin_body_class .= ' folded';



if ( is_rtl() )
	$admin_body_class .= ' rtl';

$admin_body_class .= ' branch-' . str_replace( '.', '-', floatval( $trm_version ) );
$admin_body_class .= ' version-' . str_replace( '.', '-', preg_replace( '/^([.0-9]+).*/', '$1', $trm_version ) );
$admin_body_class .= ' admin-color-' . sanitize_html_class( get_user_option( 'admin_color' ), 'fresh' );

if ( $is_iphone ) { ?>
<style type="text/css">.row-actions{visibility:visible;}</style>
<?php } ?>
</head>
<body class="Backend-WeaprEcqaKejUbRq-trendr no-js <?php echo apply_filters( 'admin_body_class', '' ) . " $admin_body_class"; ?>">
<script type="text/javascript">
//<![CDATA[
(function(){
var c = document.body.className;
c = c.replace(/no-js/, 'js');
document.body.className = c;
})();
//]]>
</script>

<div id="trmwrap">
<?php require(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/menu-header.php'); ?>
<div id="trmcontent">
<div id="trmhead">
<?php
	$blog_name = get_bloginfo('name', 'display');
?>


<h1 id="site-heading" <?php echo $title_class ?>>
	<a href="<?php echo trailingslashit( get_bloginfo( 'url' ) ); ?>" title="<?php esc_attr_e('Visit Site') ?>">
		<span id="site-title"><?php echo $blog_name ?></span>
	</a>
</h1>

<?php

do_action('in_admin_header');

$links = array();

$links[15] = '<a href="' . trm_logout_url() . '" title="' . esc_attr__('Log Out') . '">' . __('Log Out') . '</a>';

$howdy = array_shift( $links );

?>

<div id="trmhead-info">
<div id="user_info">
	<p class="hide-if-js"><?php echo "$howdy | $links_no_js"; ?></p>

	<div class="hide-if-no-js">
		<p><?php echo $howdy; ?></p>
			<ul><?php echo $links_js; ?></ul>
		</div></div>
</div>

</div>

<div id="trmbody">
<?php

?>

<div id="trmbody-content">
<?php
screen_meta($current_screen);


if ( $parent_file == 'options-general.php' )
	require(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/options-head.php');
