<?php
/**
 * Install plugin administration panel.
 *
 * @package Trnder
 * @subpackage Administration
 */
// TODO route this pages via a specific iframe handler instead of the do_action below
if ( !defined( 'IFRAME_REQUEST' ) && isset( $_GET['tab'] ) && ( 'plugin-information' == $_GET['tab'] ) )
	define( 'IFRAME_REQUEST', true );

/** Trnder Administration Bootstrap */
require_once('./admin.php');

if ( ! current_user_can('install_plugins') )
	trm_die(__('You do not have sufficient permissions to install plugins on this site.'));

if ( is_multisite() && ! is_network_admin() ) {
	trm_redirect( network_admin_url( 'plugin-install.php' ) );
	exit();
}

$trm_list_table = _get_list_table('TRM_Plugin_Install_List_Table');
$pagenum = $trm_list_table->get_pagenum();
$trm_list_table->prepare_items();

$title = __('Install Plugins');
$parent_file = 'plugins.php';

trm_enqueue_style( 'plugin-install' );
trm_enqueue_script( 'plugin-install' );
if ( 'plugin-information' != $tab )
	add_thickbox();

$body_id = $tab;

do_action('install_plugins_pre_' . $tab); //Used to override the general interface, Eg, install or plugin information.

add_contextual_help($current_screen,
	'<p>' . sprintf(__('Plugins hook into Trnder to extend its functionality with custom features. Plugins are developed independently from Trnder core by thousands of developers all over the world. All plugins in the official <a href="%s" target="_blank">Trnder.org Plugin Directory</a> are compatible with the license Trnder uses. You can find new plugins to install by searching or browsing the Directory right here in your own Plugins section.'), 'http://trendr.org/extend/module/') . '</p>' .
	'<p>' . __('If you know what you&#8217;re looking for, Search is your best bet. The Search screen has options to search the Trnder.org Plugin Directory for a particular Term, Author, or Tag. You can also search the directory by selecting a popular tags. Tags in larger type mean more plugins have been labeled with that tag.') . '</p>' .
	'<p>' . __('If you just want to get an idea of what&#8217;s available, you can browse Featured, Popular, Newest, and Recently Updated plugins by using the links in the upper left of the screen. These sections rotate regularly.') . '</p>' .
	'<p>' . __('If you want to install a plugin that you&#8217;ve downloaded elsewhere, click Upload in the upper left. You will be prompted to upload the .zip package, and once uploaded, you can activate the new plugin.') . '</p>' .
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.trendr.org/Plugins_Add_New_Screen" target="_blank">Documentation on Installing Plugins</a>') . '</p>' .
	'<p>' . __('<a href="http://trendr.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

include(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/admin-header.php');
?>
<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<?php $trm_list_table->views(); ?>

<br class="clear" />
<?php do_action('install_plugins_' . $tab, $paged); ?>
</div>
<?php
include(ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/admin-footer.php');

