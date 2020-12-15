<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Add an extra update message to the update plugin notification.
 *
 * @package trendr Core
 */
function trs_core_update_message() {
	echo '<p style="color: red; margin: 3px 0 0 0; border-top: 1px solid #ddd; padding-top: 3px">' . __( 'IMPORTANT: <a href="http://codex.trendr.org/trendr-site-administration/upgrading-trendr/">Read this before attempting to update trendr</a>', 'trendr' ) . '</p>';
}
add_action( 'in_plugin_update_message-trendr/trs-loader.php', 'trs_core_update_message' );

/**
 * Output the tabs in the admin area
 *
 * @since 1.5
 * @param string $active_tab Name of the tab that is active
 */
function trs_core_admin_tabs( $active_tab = '' ) {

	// Declare local variables
	$tabs_html    = '';
	$idle_class   = 'nav-tab';
	$active_class = 'nav-tab nav-tab-active';

	// Setup core admin tabs
	$tabs = array(
		'0' => array(
			'href' => trs_get_admin_url( add_query_arg( array( 'page' => 'trs-general-settings' ), 'admin.php' ) ),
			'name' => __( 'Components', 'trendr' )
		),
		'1' => array(
			'href' => trs_get_admin_url( add_query_arg( array( 'page' => 'trs-page-settings'    ), 'admin.php' ) ),
			'name' => __( 'Pages', 'trendr' )
		),
		'2' => array(
			'href' => trs_get_admin_url( add_query_arg( array( 'page' => 'trs-settings'         ), 'admin.php' ) ),
			'name' => __( 'Settings', 'trendr' )
		)
	);

	// If forums component is active, add additional tab
	if ( trs_is_active( 'forums' ) ) {
		$tabs['3'] = array(
			'href' => trs_get_admin_url( add_query_arg( array( 'page' => 'bb-forums-setup'     ), 'admin.php' ) ),
			'name' => __( 'Forums', 'trendr' )
		);
	}

	// Loop through tabs and build navigation
	foreach( $tabs as $tab_id => $tab_data ) {
		$is_current = (bool) ( $tab_data['name'] == $active_tab );
		$tab_class  = $is_current ? $active_class : $idle_class;
		$tabs_html .= '<a href="' . $tab_data['href'] . '" class="' . $tab_class . '">' . $tab_data['name'] . '</a>';
	}

	// Output the tabs
	echo $tabs_html;

	// Do other fun things
	do_action( 'trs_admin_tabs' );
}

/**
 * Renders the Settings admin panel.
 *
 * @package trendr Core
 * @since {@internal Unknown}}
 */
function trs_core_admin_settings() {
	global $trmdb, $trs;

	$ud = get_userdata( $trs->loggedin_user->id );

	if ( isset( $_POST['trs-admin-submit'] ) && isset( $_POST['trs-admin'] ) ) {
		if ( !check_admin_referer('trs-admin') )
			return false;

		// Settings form submitted, now save the settings.
		foreach ( (array)$_POST['trs-admin'] as $key => $value )
			trs_update_option( $key, $value );

	} ?>

	<div class="wrap">

		<?php screen_icon( 'trendr' ); ?>

		<h2 class="nav-tab-wrapper"><?php trs_core_admin_tabs( __( 'Settings', 'trendr' ) ); ?></h2>

		<?php if ( isset( $_POST['trs-admin'] ) ) : ?>

			<div id="message" class="updated fade">
				<p><?php _e( 'Settings Saved', 'trendr' ); ?></p>
			</div>

		<?php endif; ?>

		<form action="" method="post" id="trs-admin-form">

			<table class="form-table">
				<tbody>

					<?php if ( trs_is_active( 'xprofile' ) ) : ?>

						<tr>
							<th scope="row"><?php _e( 'Disable trendr to trendr profile syncing?', 'trendr' ) ?></th>
							<td>
								<input type="radio" name="trs-admin[trs-disable-profile-sync]"<?php if ( (int)trs_get_option( 'trs-disable-profile-sync' ) ) : ?> checked="checked"<?php endif; ?> id="trs-disable-profile-sync" value="1" /> <?php _e( 'Yes', 'trendr' ) ?> &nbsp;
								<input type="radio" name="trs-admin[trs-disable-profile-sync]"<?php if ( !(int)trs_get_option( 'trs-disable-profile-sync' ) || '' == trs_get_option( 'trs-disable-profile-sync' ) ) : ?> checked="checked"<?php endif; ?> id="trs-disable-profile-sync" value="0" /> <?php _e( 'No', 'trendr' ) ?>
							</td>
						</tr>

					<?php endif; ?>

					<tr>
						<th scope="row"><?php _e( 'Hide admin bar for logged out users?', 'trendr' ) ?></th>
						<td>
							<input type="radio" name="trs-admin[hide-loggedout-adminbar]"<?php if ( (int)trs_get_option( 'hide-loggedout-adminbar' ) ) : ?> checked="checked"<?php endif; ?> id="trs-admin-hide-loggedout-adminbar-yes" value="1" /> <?php _e( 'Yes', 'trendr' ) ?> &nbsp;
							<input type="radio" name="trs-admin[hide-loggedout-adminbar]"<?php if ( !(int)trs_get_option( 'hide-loggedout-adminbar' ) ) : ?> checked="checked"<?php endif; ?> id="trs-admin-hide-loggedout-adminbar-no" value="0" /> <?php _e( 'No', 'trendr' ) ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Disable portrait uploads? (Grportraits will still work)', 'trendr' ) ?></th>
						<td>
							<input type="radio" name="trs-admin[trs-disable-portrait-uploads]"<?php if ( (int)trs_get_option( 'trs-disable-portrait-uploads' ) ) : ?> checked="checked"<?php endif; ?> id="trs-admin-disable-portrait-uploads-yes" value="1" /> <?php _e( 'Yes', 'trendr' ) ?> &nbsp;
							<input type="radio" name="trs-admin[trs-disable-portrait-uploads]"<?php if ( !(int)trs_get_option( 'trs-disable-portrait-uploads' ) ) : ?> checked="checked"<?php endif; ?> id="trs-admin-disable-portrait-uploads-no" value="0" /> <?php _e( 'No', 'trendr' ) ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e( 'Disable user account deletion?', 'trendr' ) ?></th>
						<td>
							<input type="radio" name="trs-admin[trs-disable-account-deletion]"<?php if ( (int)trs_get_option( 'trs-disable-account-deletion' ) ) : ?> checked="checked"<?php endif; ?> id="trs-disable-account-deletion" value="1" /> <?php _e( 'Yes', 'trendr' ) ?> &nbsp;
							<input type="radio" name="trs-admin[trs-disable-account-deletion]"<?php if ( !(int)trs_get_option( 'trs-disable-account-deletion' ) ) : ?> checked="checked"<?php endif; ?> id="trs-disable-account-deletion" value="0" /> <?php _e( 'No', 'trendr' ) ?>
						</td>
					</tr>

					<?php if ( trs_is_active( 'activity' ) ) : ?>

						<tr>
							<th scope="row"><?php _e( 'Disable activity stream commenting on blog and forum posts?', 'trendr' ) ?></th>
							<td>
								<input type="radio" name="trs-admin[trs-disable-blogforum-comments]"<?php if ( (int)trs_get_option( 'trs-disable-blogforum-comments' ) || false === trs_get_option( 'trs-disable-blogforum-comments' ) ) : ?> checked="checked"<?php endif; ?> id="trs-disable-blogforum-comments" value="1" /> <?php _e( 'Yes', 'trendr' ) ?> &nbsp;
								<input type="radio" name="trs-admin[trs-disable-blogforum-comments]"<?php if ( !(int)trs_get_option( 'trs-disable-blogforum-comments' ) ) : ?> checked="checked"<?php endif; ?> id="trs-disable-blogforum-comments" value="0" /> <?php _e( 'No', 'trendr' ) ?>
							</td>
						</tr>

					<?php endif; ?>

					<?php if ( trs_is_active( 'groups' ) ) : ?>

						<tr>
							<th scope="row"><?php _e( 'Restrict group creation to Site Admins?', 'trendr' ) ?></th>
							<td>
								<input type="radio" name="trs-admin[trs_restrict_group_creation]"<?php checked( '1', trs_get_option( 'trs_restrict_group_creation', '0' ) ); ?>id="trs-restrict-group-creation" value="1" /> <?php _e( 'Yes', 'trendr' ) ?> &nbsp;
								<input type="radio" name="trs-admin[trs_restrict_group_creation]"<?php checked( '0', trs_get_option( 'trs_restrict_group_creation', '0' ) ); ?>id="trs-restrict-group-creation" value="0" /> <?php _e( 'No', 'trendr' ) ?>
							</td>
						</tr>

					<?php endif; ?>

					<?php do_action( 'trs_core_admin_screen_fields' ) ?>

				</tbody>
			</table>

			<?php do_action( 'trs_core_admin_screen' ); ?>

			<p class="submit">
				<input class="button-primary" type="submit" name="trs-admin-submit" id="trs-admin-submit" value="<?php _e( 'Save Settings', 'trendr' ); ?>" />
			</p>

			<?php trm_nonce_field( 'trs-admin' ); ?>

		</form>

	</div>

<?php
}

function trs_core_admin_component_setup_handler() {
	global $trmdb, $trs;

	if ( isset( $_POST['trs-admin-component-submit'] ) ) {
		if ( !check_admin_referer('trs-admin-component-setup') )
			return false;

		// Settings form submitted, now save the settings. First, set active components
		if ( isset( $_POST['trs_components'] ) ) {
			// Save settings and upgrade schema
			require( TRS_PLUGIN_DIR . '/trs-core/admin/trs-core-update.php' );
			$trs->active_components = stripslashes_deep( $_POST['trs_components'] );
			trs_core_install( $trs->active_components );

			trs_update_option( 'trs-active-components', $trs->active_components );
		}

		$base_url = trs_get_admin_url(  add_query_arg( array( 'page' => 'trs-general-settings', 'updated' => 'true' ), 'admin.php' ) );

		trm_redirect( $base_url );
	}
}
add_action( 'admin_init', 'trs_core_admin_component_setup_handler' );

function trs_core_admin_pages_setup_handler() {
	global $trmdb, $trs;

	if ( isset( $_POST['trs-admin-pages-submit'] ) || isset( $_POST['trs-admin-pages-single'] ) ) {
		if ( !check_admin_referer( 'trs-admin-pages-setup' ) )
			return false;

		// Then, update the directory pages
		if ( isset( $_POST['trs_pages'] ) ) {

			$directory_pages = array();

			foreach ( (array)$_POST['trs_pages'] as $key => $value ) {
				if ( !empty( $value ) ) {
					$directory_pages[$key] = (int)$value;
				}
			}
			trs_core_update_directory_page_ids( $directory_pages );
		}

		$base_url = trs_get_admin_url( add_query_arg( array( 'page' => 'trs-page-settings', 'updated' => 'true' ), 'admin.php' ) );

		trm_redirect( $base_url );
	}
}
add_action( 'admin_init', 'trs_core_admin_pages_setup_handler' );

/**
 * Renders the Component Setup admin panel.
 *
 * @package trendr Core
 * @since {@internal Unknown}}
 * @uses trs_core_admin_component_options()
 */
function trs_core_admin_component_setup() {
?>

	<div class="wrap">

		<?php screen_icon( 'trendr'); ?>

		<h2 class="nav-tab-wrapper"><?php trs_core_admin_tabs( __( 'Components', 'trendr' ) ); ?></h2>

		<?php if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] ) : ?>

			<div id="message" class="updated fade">

				<p><?php _e( 'Settings Saved', 'trendr' ); ?></p>

			</div>

		<?php endif; ?>

		<form action="" method="post" id="trs-admin-component-form">

			<?php trs_core_admin_component_options(); ?>

			<p class="submit clear">
				<input class="button-primary" type="submit" name="trs-admin-component-submit" id="trs-admin-component-submit" value="<?php _e( 'Save Settings', 'trendr' ) ?>"/>
			</p>

			<?php trm_nonce_field( 'trs-admin-component-setup' ); ?>

		</form>
	</div>

<?php
}

/**
 * Renders the Component Setup admin panel.
 *
 * @package trendr Core
 * @since {@internal Unknown}}
 * @uses trs_core_admin_component_options()
 */
function trs_core_admin_page_setup() {
?>

	<div class="wrap">

		<?php screen_icon( 'trendr'); ?>

		<h2 class="nav-tab-wrapper"><?php trs_core_admin_tabs( __( 'Pages', 'trendr' ) ); ?></h2>

		<?php if ( isset( $_GET['updated'] ) && 'true' === $_GET['updated'] ) : ?>

			<div id="message" class="updated fade">

				<p><?php _e( 'Settings Saved', 'trendr' ); ?></p>

			</div>

		<?php endif; ?>

		<form action="" method="post" id="trs-admin-page-form">

			<?php trs_core_admin_page_options(); ?>

			<p class="submit clear">
				<input class="button-primary" type="submit" name="trs-admin-pages-submit" id="trs-admin-pages-submit" value="<?php _e( 'Save All', 'trendr' ) ?>"/>
			</p>

			<?php trm_nonce_field( 'trs-admin-pages-setup' ); ?>

		</form>
	</div>

<?php
}

/**
 * Creates reusable markup for component setup on the Components and Pages dashboard panel.
 *
 * This markup has been abstracted so that it can be used both during the setup wizard as well as
 * when TRS has been fully installed.
 *
 * @package trendr Core
 * @since 1.5
 */
function trs_core_admin_component_options() {
	global $trs_wizard;

	// Load core functions, if needed
	if ( !function_exists( 'trs_get_option' ) )
		require( TRS_PLUGIN_DIR . '/trs-core/trs-core-functions.php' );

	$active_components = apply_filters( 'trs_active_components', trs_get_option( 'trs-active-components' ) );

	// An array of strings looped over to create component setup markup
	$optional_components = array(
		'xprofile' => array(
			'title'       => __( 'Extended Profiles', 'trendr' ),
			'description' => __( 'Customize your community with fully editable profile fields that allow your users to describe themselves.', 'trendr' )
		),
		'settings' => array(
			'title'       => __( 'Account Settings', 'trendr' ),
			'description' => __( 'Allow your users to modify their account and notification settings directly from within their profiles.', 'trendr' )
		),
		'friends'  => array(
			'title'       => __( 'Friend Connections', 'trendr' ),
			'description' => __( 'Let your users make connections so they can track the activity of others and focus on the people they care about the most.', 'trendr' )
		),
		'messages' => array(
			'title'       => __( 'Private Messaging', 'trendr' ),
			'description' => __( 'Allow your users to talk to each other directly and in private. Not just limited to one-on-one discussions, messages can be sent between any number of members.', 'trendr' )
		),
		'activity' => array(
			'title'       => __( 'Activity Streams', 'trendr' ),
			'description' => __( 'Global, personal, and group activity streams with threaded commenting, direct posting, favoriting and @mentions, all with full RSS feed and email notification support.', 'trendr' )
		),
		'groups'   => array(
			'title'       => __( 'User Groups', 'trendr' ),
			'description' => __( 'Groups allow your users to organize themselves into specific public, private or hidden sections with separate activity streams and member listings.', 'trendr' )
		),
		'forums'   => array(
			'title'       => __( 'Discussion Forums', 'trendr' ),
			'description' => __( 'Full-powered discussion forums built directly into groups allow for more conventional in-depth conversations. NOTE: This will require an extra (but easy) setup step.', 'trendr' )
		),
		'blogs'    => array(
			'title'       => __( 'Site Tracking', 'trendr' ),
			'description' => __( 'Make trendr aware of new posts and new comments from your site.', 'trendr' )
		)
	);

	if ( is_multisite() )
		$optional_components['blogs']['description'] = __( 'Make trendr aware of new sites, new posts and new comments from across your entire network.', 'trendr' );

	// If this is an upgrade from before trendr 1.5, we'll have to convert deactivated
	// components into activated ones
	if ( empty( $active_components ) ) {
		$deactivated_components = trs_get_option( 'trs-deactivated-components' );

		// Trim off namespace and filename
		$trimmed = array();
		foreach ( (array) $deactivated_components as $component => $value ) {
			$trimmed[] = str_replace( '.php', '', str_replace( 'trs-', '', $component ) );
		}

		// Loop through the optional components to create an active component array
		foreach ( (array) $optional_components as $ocomponent => $ovalue ) {
			if ( !in_array( $ocomponent, $trimmed ) ) {
				$active_components[$ocomponent] = 1;
			}
		}
	}

	// Required components
	$required_components = array(
		'core' => array(
			'title'       => __( 'trendr Core', 'trendr' ),
			'description' => __( 'It&#8216;s what makes <del>time travel</del> trendr possible!', 'trendr' )
		),
		'members' => array(
			'title'       => __( 'Community Members', 'trendr' ),
			'description' => __( 'Everything in a trendr community revolves around its members.', 'trendr' )
		),
	);

	// On new install, set all components to be active by default
	if ( !empty( $trs_wizard ) && 'install' == $trs_wizard->setup_type && empty( $active_components ) )
		$active_components = $optional_components;

	?>

	<?php /* The setup wizard uses different, more descriptive text here */ ?>
	<?php if ( empty( $trs_wizard ) ) : ?>

		<h3><?php _e( 'Available Components', 'trendr' ); ?></h3>

		<p><?php _e( 'Each component has a unique purpose, and your community may not need each one.', 'trendr' ); ?></p>

	<?php endif ?>

	<table class="form-table">
		<tbody>

			<?php foreach ( $optional_components as $name => $labels ) : ?>

				<tr valign="top">
					<th scope="row"><?php echo esc_html( $labels['title'] ); ?></th>

					<td>
						<label for="trs_components[<?php echo esc_attr( $name ); ?>]">
							<input type="checkbox" id="trs_components[<?php echo esc_attr( $name ); ?>]" name="trs_components[<?php echo esc_attr( $name ); ?>]" value="1"<?php checked( isset( $active_components[esc_attr( $name )] ) ); ?> />

							<?php echo $labels['description']; ?>

						</label>

					</td>
				</tr>

			<?php endforeach ?>

		</tbody>
	</table>

	<?php if ( empty( $trs_wizard ) ) : ?>

		<h3><?php _e( 'Required Components', 'trendr' ); ?></h3>

		<p><?php _e( 'The following components are required by trendr and cannot be turned off.', 'trendr' ); ?></p>

	<?php endif ?>

	<table class="form-table">
		<tbody>

			<?php foreach ( $required_components as $name => $labels ) : ?>

				<tr valign="top">
					<th scope="row"><?php echo esc_html( $labels['title'] ); ?></th>

					<td>
						<label for="trs_components[<?php echo esc_attr( $name ); ?>]">
							<input type="checkbox" id="trs_components[<?php echo esc_attr( $name ); ?>]" name="" disabled="disabled" value="1"<?php checked( true ); ?> />

							<?php echo $labels['description']; ?>

						</label>

					</td>
				</tr>

			<?php endforeach ?>

		</tbody>
	</table>

	<input type="hidden" name="trs_components[members]" value="1" />

	<?php
}

/**
 * Creates reusable markup for page setup on the Components and Pages dashboard panel.
 *
 * This markup has been abstracted so that it can be used both during the setup wizard as well as
 * when TRS has been fully installed.
 *
 * @package trendr Core
 * @since 1.5
 */
function trs_core_admin_page_options() {
	global $trs;

	// Get the existing TRM pages
	$existing_pages = trs_core_get_directory_page_ids();

	// Set up an array of components (along with component names) that have
	// directory pages.
	$directory_pages = array();

	foreach( $trs->loaded_components as $component_slug => $component_id ) {

		// Only components that need directories should be listed here
		if ( isset( $trs->{$component_id} ) && !empty( $trs->{$component_id}->has_directory ) ) {

			// component->name was introduced in TRS 1.5, so we must provide a fallback
			$component_name = !empty( $trs->{$component_id}->name ) ? $trs->{$component_id}->name : ucwords( $component_id );

			$directory_pages[$component_id] = $component_name;
		}
	}

	$directory_pages = apply_filters( 'trs_directory_pages', $directory_pages );

	?>

	<h3><?php _e( 'Directories', 'trendr' ); ?></h3>

	<p><?php _e( 'Associate a trendr Page with each trendr component directory.', 'trendr' ); ?></p>

	<table class="form-table">
		<tbody>

			<?php foreach ( $directory_pages as $name => $label ) : ?>
				<?php $disabled = !trs_is_active( $name ) ? ' disabled="disabled"' : ''; ?>

				<tr valign="top">
					<th scope="row">
						<label for="trs_pages[<?php echo esc_attr( $name ) ?>]"><?php echo esc_html( $label ) ?></label>
					</th>

					<td>
						<?php if ( !trs_is_root_blog() )
							switch_to_blog( trs_get_root_blog_id() ) ?>

						<?php echo trm_dropdown_pages( array(
							'name'             => 'trs_pages[' . esc_attr( $name ) . ']',
							'echo'             => false,
							'show_option_none' => __( '- None -', 'trendr' ),
							'selected'         => !empty( $existing_pages[$name] ) ? $existing_pages[$name] : false
						) ); ?>

						<a href="<?php echo admin_url( add_query_arg( array( 'post_type' => 'page' ), 'post-new.php' ) ); ?>" class="button-secondary"><?php _e( 'New Page' ); ?></a>
						<input class="button-primary" type="submit" name="trs-admin-pages-single" value="<?php _e( 'Save', 'trendr' ) ?>" />

						<?php if ( !empty( $existing_pages[$name] ) ) : ?>

							<a href="<?php echo get_permalink( $existing_pages[$name] ); ?>" class="button-secondary" target="_trs"><?php _e( 'View' ); ?></a>

						<?php endif; ?>

						<?php if ( !trs_is_root_blog() )
							restore_current_blog() ?>

					</td>
				</tr>


			<?php endforeach ?>

			<?php do_action( 'trs_active_external_directories' ); ?>

		</tbody>
	</table>

	<?php

	// Static pages
	$static_pages = array(
		'register' => __( 'Register', 'trendr' ),
		'activate' => __( 'Activate', 'trendr' ),
	); ?>

	<h3><?php _e( 'Registration', 'trendr' ); ?></h3>

	<p><?php _e( 'Associate trendr Pages with the following trendr Registration pages.', 'trendr' ); ?></p>

	<table class="form-table">
		<tbody>

			<?php foreach ( $static_pages as $name => $label ) : ?>

				<tr valign="top">
					<th scope="row">
						<label for="trs_pages[<?php echo esc_attr( $name ) ?>]"><?php echo esc_html( $label ) ?></label>
					</th>

					<td>
						<?php echo trm_dropdown_pages( array(
							'name'             => 'trs_pages[' . esc_attr( $name ) . ']',
							'echo'             => false,
							'show_option_none' => __( '- None -', 'trendr' ),
							'selected'         => !empty( $existing_pages[$name] ) ? $existing_pages[$name] : false
						) ) ?>

						<a href="<?php echo admin_url( add_query_arg( array( 'post_type' => 'page' ), 'post-new.php' ) ); ?>" class="button-secondary"><?php _e( 'New Page' ); ?></a>
						<input class="button-primary" type="submit" name="trs-admin-pages-single" value="<?php _e( 'Save', 'trendr' ) ?>" />

						<?php if ( !empty( $existing_pages[$name] ) ) : ?>

							<a href="<?php echo get_permalink( $existing_pages[$name] ); ?>" class="button-secondary" target="_trs"><?php _e( 'View' ); ?></a>

						<?php endif; ?>

					</td>
				</tr>

			<?php endforeach ?>

			<?php do_action( 'trs_active_external_pages' ); ?>

		</tbody>
	</table>

	<?php
}

/**
 * Loads admin panel styles and scripts.
 *
 * @package trendr Core
 * @since {@internal Unknown}}
 */
function trs_core_add_admin_menu_styles() {
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
		trm_enqueue_style( 'trs-admin-css', apply_filters( 'trs_core_admin_css', TRS_PLUGIN_URL . '/trs-core/css/admin.dev.css' ), array(), '20110723' );
	else
		trm_enqueue_style( 'trs-admin-css', apply_filters( 'trs_core_admin_css', TRS_PLUGIN_URL . '/trs-core/css/admin.css' ), array(), '20110723' );

	trm_enqueue_script( 'thickbox' );
	trm_enqueue_style( 'thickbox' );
}

?>