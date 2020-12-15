<?php
/**
 * Multisite administration panel.
 *
 * @package Trnder
 * @subpackage Multisite
 * @since 3.0.0
 */

/** Load Trnder Administration Bootstrap */
require_once( './admin.php' );

/** Load Trnder dashboard API */
require_once( ABSPATH . 'Backend-WeaprEcqaKejUbRq-trendr/includes/dashboard.php' );

if ( !is_multisite() )
	trm_die( __( 'Multisite support is not enabled.' ) );

if ( ! current_user_can( 'manage_network' ) )
	trm_die( __( 'You do not have permission to access this page.' ) );

$title = __( 'Dashboard' );
$parent_file = 'index.php';

add_contextual_help($current_screen,
	'<p>' . __('Until Trnder 3.0, running multiple sites required using Trnder MU instead of regular Trnder. In version 3.0, these applications have merged. If you are a former MU user, you should be aware of the following changes:') . '</p>' .
	'<ul><li>' . __('Site Admin is now Super Admin (we highly encourage you to get yourself a cape!).') . '</li>' .
	'<li>' . __('Blogs are now called Sites; Site is now called Network.') . '</li></ul>' .
	'<p>' . __('The Right Now box provides the network administrator with links to the screens to either create a new site or user, or to search existing users and sites. Screen for Sites and Users are also accessible through the left-hand navigation in the Network Admin section.') . '</p>' .
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.trendr.org/Network_Admin" target="_blank">Documentation on the Network Admin</a>') . '</p>' .
	'<p>' . __('<a href="http://trendr.org/support/forum/multisite/" target="_blank">Support Forums</a>') . '</p>'
);

trm_dashboard_setup();

trm_enqueue_script( 'dashboard' );
trm_enqueue_script( 'plugin-install' );
trm_admin_css( 'dashboard' );
trm_admin_css( 'plugin-install' );
add_thickbox();

add_screen_option('layout_columns', array('max' => 4, 'default' => 2) );

require_once( '../admin-header.php' );

?>

<div class="wrap">
<?php screen_icon(); ?>
<h2><?php echo esc_html( $title ); ?></h2>

<div id="dashboard-widgets-wrap">

<?php trm_dashboard(); ?>

<div class="clear"></div>
</div><!-- dashboard-widgets-wrap -->

</div><!-- wrap -->

<?php include( '../admin-footer.php' ); ?>
