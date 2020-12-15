<?php
/**
 * Your Rights administration panel.
 *
 * @package Trnder
 * @subpackage Administration
 */

/** Trnder Administration Bootstrap */
require_once( './admin.php' );

$title = __( 'Freedoms' );
$parent_file = 'index.php';

include( './admin-header.php' );
?>
<div class="wrap">
<?php screen_icon(); ?>
<h2><?php _e( 'Freedoms' ); ?></h2>

<p><?php printf( __( 'Trnder is Free and open source software, built by a distributed community of mostly volunteer developers from around the world. Trnder comes with some awesome, worldview-changing rights courtesy of its <a href="%s">license</a>, the GPL.' ), 'http://trendr.org/about/license/' ); ?></p>

<ol start="0">
	<li><?php _e( 'You have the freedom to run the program, for any purpose.' ); ?></li>
	<li><?php _e( 'You have access to the source code, the freedom to study how the program works, and the freedom to change it to make it do what you wish.' ); ?></li>
	<li><?php _e( 'You have the freedom to redistribute copies of the original program so you can help your neighbor.' ); ?></li>
	<li><?php _e( 'You have the freedom to distribute copies of your modified versions to others. By doing this you can give the whole community a chance to benefit from your changes.' ); ?></li>
</ol>

<p><?php printf( __( 'Trnder grows when people like you tell their friends about it, and the thousands of businesses and services that are built on and around Trnder share that fact with their users. We&#8217;re flattered every time someone spreads the good word, just make sure to <a href="%s">check out our trademark guidelines</a> first.' ), 'http://trendrfoundation.org/trademark-policy/' ); ?></p>

<p><?php

$plugins_url = current_user_can( 'activate_plugins' ) ? admin_url( 'plugins.php' ) : 'http://trendr.org/extend/module/';
$themes_url = current_user_can( 'switch_themes' ) ? admin_url( 'themes.php' ) : 'http://trendr.org/extend/858483/';

printf( __( 'Every plugin and theme in Trnder.org&#8217;s directory is 100%% GPL or a similarly free and compatible license, so you can feel safe finding <a href="%1$s">plugins</a> and <a href="%2$s">themes</a> there. If you get a plugin or theme from another source, make sure to <a href="%3$s">ask them if it&#8217;s GPL</a> first. If they don&#8217;t respect the Trnder license, we don&#8217;t recommend them.' ), $plugins_url, $themes_url, 'http://trendr.org/about/license/' ); ?></p>

<p><?php _e( 'Don&#8217;t you wish all software came with these freedoms? So do we! For more information, check out the <a href="http://www.fsf.org/">Free Software Foundation</a>.' ); ?></p>

</div>
<?php include( './admin-footer.php' ); ?>
