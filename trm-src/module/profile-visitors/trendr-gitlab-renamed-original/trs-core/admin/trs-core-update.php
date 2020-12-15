<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class TRS_Core_Setup_Wizard {
	var $current_step;
	var $steps;

	var $database_version;
	var $is_network_activate;
	var $new_version;
	var $setup_type;

	function trs_core_setup_wizard() {
		$this->__construct();
	}

	function __construct() {
		global $trs;

		// Ensure that we have access to some utility functions. Must use require_once()
		// because TRS Core is loaded during incremental upgrades
		require_once( TRS_PLUGIN_DIR . '/trs-core/trs-core-functions.php' );

		// Get current DB version
		$this->database_version = !empty( $trs->database_version ) ? (int) $trs->database_version : 0;

		if ( !empty( $trs->is_network_activate ) ) {
			$this->is_network_activate = $trs->is_network_activate;

		} elseif ( !$this->current_step() ) {
			setcookie( 'trs-wizard-step', 0, time() + 60 * 60 * 24, COOKIEPATH );
			$_COOKIE['trs-wizard-step'] = 0;
		}

		$this->new_version  = constant( 'TRS_DB_VERSION' );
		$this->setup_type   = !empty( $trs->maintenance_mode ) ? $trs->maintenance_mode : '';
		$this->current_step = $this->current_step();

		// Remove the admin menu while we update/install
		remove_action( trs_core_admin_hook(), 'trs_core_add_admin_menu', 9 );

		// Call the save method that will save data and modify $current_step
		if ( isset( $_POST['save'] ) )
			$this->save( $_POST['save'] );

		// Build the steps needed for update or new installations
		$this->steps = $this->add_steps();
	}

	function current_step() {
		if ( isset( $_POST['step'] ) ) {
			$current_step = (int)$_POST['step'] + 1;
		} else {
			if ( !empty( $_COOKIE['trs-wizard-step'] ) )
				$current_step = $_COOKIE['trs-wizard-step'];
			else
				$current_step = 0;
		}

		return $current_step;
	}

	function add_steps() {
		global $trm_rewrite;

		// Setup wizard steps
		$steps = array();

		if ( 'install' == $this->setup_type ) {
			$steps = array(
				__( 'Components', 'trendr' ),
				__( 'Pages',      'trendr' ),
				__( 'Permalinks', 'trendr' ),
				__( 'Theme',      'trendr' ),
				__( 'Finish',     'trendr' )
			);

		// Update wizard steps
		} else {
			if ( $this->is_network_activate )
				$steps[] = __( 'Multisite Update', 'trendr' );

			if ( $this->database_version < (int) $this->new_version )
				$steps[] = __( 'Database Update', 'trendr' );

			if ( $this->database_version < 1801 || !trs_core_get_directory_page_ids() ) {
				$steps[] = __( 'Components', 'trendr' );
				$steps[] = __( 'Pages', 'trendr' );
			}

			$steps[] = __( 'Finish', 'trendr' );
		}

		return $steps;
	}

	function save( $step_name ) {

		// Save any posted values
		switch ( $step_name ) {
			case 'db_update': default:
				$result = $this->step_db_update_save();
				break;

			case 'ms_update': default:
				$result = $this->step_ms_update_save();
				break;

			case 'ms_pages': default:
				$result = $this->step_ms_update_save();
				break;

			case 'components': default:
				$result = $this->step_components_save();
				break;

			case 'pages': default:
				$result = $this->step_pages_save();
				break;

			case 'permalinks': default:
				$result = $this->step_permalinks_save();
				break;

			case 'theme': default:
				$result = $this->step_theme_save();
				break;

			case 'finish': default:
				$result = $this->step_finish_save();
				break;
		}

		if ( !$result && $this->current_step )
			$this->current_step--;

		if ( 'finish' != $step_name )
			setcookie( 'trs-wizard-step', (int)$this->current_step, time() + 60 * 60 * 24, COOKIEPATH );
	}

	function html() {

		// Update or Setup
		$type = ( 'update' == $this->setup_type ) ? __( 'Update', 'trendr' ) : __( 'Setup', 'trendr' );

		?>

		<div class="wrap" id="trs-admin">

			<?php screen_icon( 'trendr' ); ?>

			<h2><?php printf( __( 'trendr %s', 'trendr' ), $type ); ?></h2>

			<?php
				do_action( 'trs_admin_notices' );

				$step_count  = count( $this->steps ) - 1;
				$wiz_or_set  = $this->current_step >= $step_count ? 'trs-general-settings' : 'trs-wizard';
				$form_action = trs_core_update_do_network_admin() ? network_admin_url( add_query_arg( array( 'page' => $wiz_or_set ), 'admin.php' ) ) : admin_url( add_query_arg( array( 'page' => $wiz_or_set ), 'admin.php' ) );
			?>

			<form action="<?php echo $form_action; ?>" method="post" id="trs-admin-form">
				<div id="trs-admin-nav">
					<ol>

						<?php foreach( (array)$this->steps as $i => $name ) : ?>

							<li<?php if ( $this->current_step == $i ) : ?> class="current"<?php endif; ?>>
								<?php if ( $this->current_step > $i ) : ?>

									<span class="complete">&nbsp;</span>

								<?php else :

									echo $i + 1 . '. ';

								endif;

								echo esc_attr( $name ) ?>

							</li>

						<?php endforeach; ?>

					</ol>

					<?php if ( __( 'Finish', 'trendr' ) == $this->steps[$this->current_step] ) : ?>

						<div class="prev-next submit clear">
							<input type="submit" value="<?php _e( 'Finish &amp; Activate', 'trendr' ); ?>" name="submit" />
						</div>

					<?php else : ?>

						<div class="prev-next submit clear">
							<input type="submit" value="<?php _e( 'Save &amp; Next', 'trendr' ); ?>" name="submit" />
						</div>

					<?php endif; ?>

				</div>

				<div id="trs-admin-content">

					<?php switch ( $this->steps[$this->current_step] ) {
						case __( 'Database Update', 'trendr') :
							$this->step_db_update();
							break;

						case __( 'Multisite Update', 'trendr') :
							$this->step_ms_update();
							break;

						case __( 'Site Directory', 'trendr') :
							$this->step_ms_update();
							break;

						case __( 'Components', 'trendr') :
							$this->step_components();
							break;

						case __( 'Pages', 'trendr') :
							$this->step_pages();
							break;

						case __( 'Permalinks', 'trendr') :
							$this->step_permalinks();
							break;

						case __( 'Theme', 'trendr') :
							$this->step_theme();
							break;

						case __( 'Finish', 'trendr') :
							$this->step_finish();
							break;

					} ?>

				</div>
			</form>
		</div>

	<?php
	}

	/** Screen methods ********************************************************/

	function step_db_update() {
		if ( !current_user_can( 'activate_plugins' ) )
			return false; ?>

		<p><?php _e( 'Before you can continue using trendr, a few minor adjustments need to be made. These changes are not destructive and will not remove or change any existing settings.', 'trendr' ); ?></p>

		<div class="submit clear">
			<input type="hidden" name="save" value="db_update" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $this->current_step ); ?>" />

			<?php trm_nonce_field( 'trswizard_db_update' ) ?>

		</div>

	<?php
	}

	function step_ms_update() {
		global $trmdb;

		// Make sure that page info is pulled from trs_get_root_blog_id() (except when in
		// multisite mode)
		if ( !empty( $trmdb->blogid ) && ( $trmdb->blogid != trs_get_root_blog_id() ) && ( !defined( 'TRS_ENABLE_MULTIBLOG' ) ) )
			switch_to_blog( trs_get_root_blog_id() );

		if ( !current_user_can( 'activate_plugins' ) )
			return false;

		$active_components = trs_get_option( 'trs-active-components' );

		if ( defined( 'TRS_BLOGS_SLUG' ) )
			$blogs_slug = constant( 'TRS_BLOGS_SLUG' );
		else
			$blogs_slug = 'blogs';

 		// Call up old trs-pages to see if a page has been previously linked to Blogs
		$existing_pages = trs_get_option( 'trs-pages' );

		if ( !empty( $existing_pages['blogs'] ) )
			$existing_blog_page = '&selected=' . $existing_pages['blogs'];
		else
			$existing_blog_page = '';
		?>

		<script type="text/javascript">
			jQuery( document ).ready( function() {
				jQuery( 'select' ).change( function() {
					jQuery( this ).siblings( 'input[@type=radio]' ).click();
				});
			});
		</script>

		<p><?php printf( __( 'trendr has detected a recent change to WordPress Multisite, which allows members of your community to have their own WordPress sites. You can enable or disable this feature at any time at <a href="%s">Network Options</a>.', 'trendr' ), network_admin_url( 'settings.php' ) ); ?></p>

		<p><?php __( "Please select the WordPress page you would like to use to display the site directory. You can either choose an existing page or let trendr auto-create a page for you. If you'd like, you can go to manually create pages now, and return to this step when you are finished.", 'trendr' ) ?></p>

		<p><strong><?php _e( 'Please Note:', 'trendr' ) ?></strong> <?php _e( "If you have manually added trendr navigation links in your theme you may need to remove these from your header.php to avoid duplicate links.", 'trendr' ) ?></p>

		<p><?php _e( 'Would you like to enable site tracking, which tracks blog posts and comments from across your network?', 'trendr' ); ?></p>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><?php _e( "Enable Site Tracking?", 'trendr' ) ?></th>

				<td>
					<label for="trs_components[blogs]">
						<input id="site-tracking-enabled" type="checkbox" id="trs_components[blogs]" name="trs_components[blogs]" value="1"<?php checked( isset( $active_components['blogs'] ) ); ?> />

						<?php _e( "Track new sites, new posts and new comments across your entire network.", 'trendr' ) ?>

					</label>

				</td>
			</tr>

			<tr valign="top" id="site-tracking-page-selector">
				<th scope="row"><?php _e( 'Select a WordPress page for the Sites directory.', 'trendr' ); ?></th>

				<td>
					<p><input type="radio" name="trs_pages[blogs]" checked="checked" value="<?php echo $blogs_slug; ?>" /> <?php _e( 'Automatically create a page at:', 'trendr' ); ?> <?php echo site_url( $blogs_slug ); ?>/</p>
					<p><input type="radio" name="trs_pages[blogs]" value="page" /> <?php _e( 'Use an existing page:', 'trendr' ); ?> <?php echo trm_dropdown_pages( "name=trs-blogs-page&echo=0&show_option_none=" . __( '- Select -', 'trendr' ) . $existing_blog_page ); ?></p>
				</td>
			</tr>

		</table>


		<div class="submit clear">
			<input type="hidden" name="save" value="ms_update" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $this->current_step ); ?>" />

			<?php trm_nonce_field( 'trswizard_ms_update' ); ?>

		</div>

		<?php

		restore_current_blog();
	}

	function step_components() {
		if ( !current_user_can( 'activate_plugins' ) )
			return false;

		if ( !function_exists( 'trs_core_admin_component_options' ) )
			require ( TRM_PLUGIN_DIR . '/trendr/trs-core/admin/trs-core-admin.php' ); ?>

		<p><?php _e( "trendr bundles several individual social components together, each one adding a distinct feature. This first step decides which features are enabled on your site; all features are enabled by default. Don't worry, you can change your mind at any point in the future.", 'trendr' ); ?></p>

		<?php trs_core_admin_component_options(); ?>

		<div class="submit clear">
			<input type="hidden" name="save" value="components" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $this->current_step ); ?>" />

			<?php trm_nonce_field( 'trswizard_components' ); ?>

		</div>

	<?php
	}

	function step_pages() {
		global $trs, $trmdb;

		// Make sure that page info is pulled from trs_get_root_blog_id() (except when in
		// multisite mode)
		if ( !empty( $trmdb->blogid ) && ( $trmdb->blogid != trs_get_root_blog_id() ) && ( !defined( 'TRS_ENABLE_MULTIBLOG' ) ) )
			switch_to_blog( trs_get_root_blog_id() );

		if ( !current_user_can( 'activate_plugins' ) )
			return false;

		$existing_pages = trs_core_update_get_page_meta();

		// Provide empty indexes to avoid PHP errors with trm_dropdown_pages()
		$indexes = array( 'members', 'activity', 'groups', 'forums', 'blogs', 'register', 'activate' );
		foreach ( $indexes as $index ) {
			if ( !isset( $existing_pages[$index] ) )
				$existing_pages[$index] = '';
		}

		if ( !empty( $existing_pages['blogs'] ) )
			$existing_blog_page = '&selected=' . $existing_pages['blogs'];
		else
			$existing_blog_page = '';

		// Get active components
		$active_components = apply_filters( 'trs_active_components', trs_get_option( 'trs-active-components' ) );

		// Check for defined slugs
		$members_slug    = !empty( $trs->members->slug    ) ? $trs->members->slug    : __( 'members',  'trendr' );

		// Groups
		$groups_slug     = !empty( $trs->groups->slug     ) ? $trs->groups->slug     : __( 'groups',   'trendr' );

		// Activity
		$activity_slug   = !empty( $trs->activity->slug   ) ? $trs->activity->slug   : __( 'activity', 'trendr' );

		// Forums
		$forums_slug     = !empty( $trs->forums->slug     ) ? $trs->forums->slug     : __( 'forums',   'trendr' );

		// Blogs
		$blogs_slug      = !empty( $trs->blogs->slug      ) ? $trs->blogs->slug      : __( 'blogs',    'trendr' );

		// Register
		$register_slug   = !empty( $trs->register->slug   ) ? $trs->register->slug   : __( 'register', 'trendr' );

		// Activation
		$activation_slug = !empty( $trs->activation->slug ) ? $trs->activation->slug : __( 'activate', 'trendr' );

		?>

		<script type="text/javascript">
			jQuery( document ).ready( function() {
				jQuery( 'select' ).change( function() {
					jQuery( this ).siblings( 'input[@type=radio]' ).click();
				});
			});
		</script>

		<p><?php _e( 'trendr now uses WordPress pages to display content. This allows you to easily change the names of pages or move them to a sub page.', 'trendr' ); ?></p>

		<p><?php _e( 'Either choose an existing page or let trendr auto-create pages for you. To manually create custom pages, come back to this step once you are finished.', 'trendr' ); ?></p>

		<p><strong><?php _e( 'Please Note:', 'trendr' ); ?></strong> <?php _e( 'If you have manually added trendr navigation links in your theme you may need to remove these from your header.php to avoid duplicate links.', 'trendr' ); ?></p>

		<table class="form-table">

			<tr valign="top">
				<th scope="row">
					<h5><?php _e( 'Members', 'trendr' ); ?></h5>
					<p><?php _e( 'Displays member profiles, and a directory of all site members.', 'trendr' ); ?></p>
				</th>
				<td>
					<p><label><input type="radio" name="trs_pages[members]" <?php checked( empty( $existing_pages['members'] ) ); ?>  value="<?php echo $members_slug; ?>" /> <?php _e( 'Automatically create a page at:', 'trendr' ) ?> <?php echo home_url( $members_slug ); ?>/</label></p>
					<p><label><input type="radio" name="trs_pages[members]" <?php checked( !empty( $existing_pages['members'] ) ); ?> value="page" /> <?php _e( 'Use an existing page:', 'trendr' ); ?> <?php echo trm_dropdown_pages( "name=trs-members-page&echo=0&show_option_none=" . __( '- Select -', 'trendr' ) . "&selected=" . $existing_pages['members'] ); ?></label></p>
				</td>
			</tr>

			<?php if ( isset( $active_components['activity'] ) ) : ?>

				<tr valign="top">
					<th scope="row">
						<h5><?php _e( 'Site Activity', 'trendr' ); ?></h5>
						<p><?php _e( "Displays the activity for the entire site, a member's friends, groups and @mentions.", 'trendr' ); ?></p>
					</th>
					<td>
						<p><label><input type="radio" name="trs_pages[activity]" <?php checked( empty( $existing_pages['activity'] ) ); ?>  value="<?php echo $activity_slug; ?>" /> <?php _e( 'Automatically create a page at:', 'trendr' ); ?> <?php echo home_url( $activity_slug ); ?>/</label></p>
						<p><label><input type="radio" name="trs_pages[activity]" <?php checked( !empty( $existing_pages['activity'] ) ); ?> value="page" /> <?php _e( 'Use an existing page:', 'trendr' ); ?> <?php echo trm_dropdown_pages( "name=trs-activity-page&echo=0&show_option_none=" . __( '- Select -', 'trendr' ) . "&selected=" . $existing_pages['activity'] ); ?></label></p>
					</td>
				</tr>

			<?php endif; ?>

			<?php if ( isset( $active_components['groups'] ) ) : ?>

				<tr valign="top">
					<th scope="row">
						<h5><?php _e( 'Groups', 'trendr' ); ?></h5>
						<p><?php _e( 'Displays individual groups as well as a directory of groups.', 'trendr' ); ?></p>
					</th>
					<td>
						<p><label><input type="radio" name="trs_pages[groups]" <?php checked( empty( $existing_pages['groups'] ) ); ?>  value="<?php echo $groups_slug; ?>" /> <?php _e( 'Automatically create a page at:', 'trendr' ); ?> <?php echo home_url( $groups_slug ); ?>/</label></p>
						<p><label><input type="radio" name="trs_pages[groups]" <?php checked( !empty( $existing_pages['groups'] ) ); ?> value="page" /> <?php _e( 'Use an existing page:', 'trendr' ); ?> <?php echo trm_dropdown_pages( "name=trs-groups-page&echo=0&show_option_none=" . __( '- Select -', 'trendr' ) . "&selected=" . $existing_pages['groups'] ); ?></label></p>
					</td>
				</tr>

			<?php endif; ?>

			<?php if ( isset( $active_components['forums'] ) ) : ?>

				<tr valign="top">
					<th scope="row">
						<h5><?php _e( 'Forums', 'trendr' ); ?></h5>
						<p><?php _e( 'Displays a directory of public forum topics.', 'trendr' ); ?></p>
					</th>
					<td>
						<p><label><input type="radio" name="trs_pages[forums]" <?php checked( empty( $existing_pages['forums'] ) ); ?>  value="<?php echo $forums_slug; ?>" /> <?php _e( 'Automatically create a page at:', 'trendr' ); ?> <?php echo home_url( $forums_slug ); ?>/</label></p>
						<p><label><input type="radio" name="trs_pages[forums]" <?php checked( !empty( $existing_pages['forums'] ) ); ?> value="page" /> <?php _e( 'Use an existing page:', 'trendr' ); ?> <?php echo trm_dropdown_pages( "name=trs-forums-page&echo=0&show_option_none=" . __( '- Select -', 'trendr' ) . "&selected=" . $existing_pages['forums'] ); ?></label></p>
					</td>
				</tr>

			<?php endif; ?>

			<?php /* The Blogs component only needs a directory page when Multisite is enabled */ ?>
			<?php if ( is_multisite() && isset( $active_components['blogs'] ) ) : ?>

				<tr valign="top">
					<th scope="row">
						<h5><?php _e( 'Sites', 'trendr' ); ?></h5>
						<p><?php _e( 'Displays a directory of the sites in your network.', 'trendr' ); ?></p>
					</th>
					<td>
						<p><label><input type="radio" name="trs_pages[blogs]" <?php checked( empty( $existing_pages['blogs'] ) ); ?>  value="<?php echo $blogs_slug; ?>" /> <?php _e( 'Automatically create a page at:', 'trendr' ); ?> <?php echo home_url( $blogs_slug ); ?>/</label></p>
						<p><label><input type="radio" name="trs_pages[blogs]" <?php checked( !empty( $existing_pages['blogs'] ) ); ?> value="page" /> <?php _e( 'Use an existing page:', 'trendr' ); ?> <?php echo trm_dropdown_pages( "name=trs-blogs-page&echo=0&show_option_none=" . __( '- Select -', 'trendr' ) . "&selected=" . $existing_pages['blogs'] ); ?></label></p>
					</td>
				</tr>

			<?php endif; ?>

			<tr valign="top">
				<th scope="row">
					<h5><?php _e( 'Register', 'trendr' ); ?></h5>
					<p><?php _e( 'Displays a site registration page where users can create new accounts.', 'trendr' ); ?></p>
				</th>
				<td>
					<p><label><input type="radio" name="trs_pages[register]" <?php checked( empty( $existing_pages['register'] ) ); ?>  value="<?php echo $register_slug; ?>" /> <?php _e( 'Automatically create a page at:', 'trendr' ) ?> <?php echo home_url( $register_slug ) ?>/</label></p>
					<p><label><input type="radio" name="trs_pages[register]" <?php checked( !empty( $existing_pages['register'] ) ); ?> value="page" /> <?php _e( 'Use an existing page:', 'trendr' ) ?> <?php echo trm_dropdown_pages( "name=trs-register-page&echo=0&show_option_none=" . __( '- Select -', 'trendr' ) . "&selected=" . $existing_pages['register'] ); ?></label></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<h5><?php _e( 'Activate', 'trendr' ); ?></h5>
					<p><?php _e( 'The page users will visit to activate their account once they have registered.', 'trendr' ); ?></p>
				</th>
				<td>
					<p><label><input type="radio" name="trs_pages[activate]" <?php checked( empty( $existing_pages['activate'] ) ); ?>  value="<?php echo $activation_slug; ?>" /> <?php _e( 'Automatically create a page at:', 'trendr' ); ?> <?php echo home_url( $activation_slug ); ?>/</label></p>
					<p><label><input type="radio" name="trs_pages[activate]" <?php checked( !empty( $existing_pages['activate'] ) ); ?> value="page" /> <?php _e( 'Use an existing page:', 'trendr' ); ?> <?php echo trm_dropdown_pages( "name=trs-activate-page&echo=0&show_option_none=" . __( '- Select -', 'trendr' ) . "&selected=" . $existing_pages['activate'] ); ?></label></p>
				</td>
			</tr>
		</table>

		<div class="submit clear">
			<input type="hidden" name="save" value="pages" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $this->current_step ); ?>" />

			<?php trm_nonce_field( 'trswizard_pages' ); ?>

		</div>

		<?php

		restore_current_blog();
	}

	function step_permalinks() {
		if ( !current_user_can( 'activate_plugins' ) )
			return false;

		$prefix              = '';
		$permalink_structure = get_option( 'permalink_structure' );
		$using_permalinks    = ( !empty( $permalink_structure ) ) ? true : false;
		$structures          = array( '', $prefix . '/%year%/%monthnum%/%day%/%postname%/', $prefix . '/%year%/%monthnum%/%postname%/', $prefix . '/archives/%post_id%' );

		// If we're using permalinks already, adjust text accordingly
		if ( $permalink_structure )
			$permalink_setup_text = __( 'Congratulations! You are already using pretty permalinks, which trendr requires. If you\'d like to change your settings, you may do so now. If you\'re happy with your current settings, click Save &amp; Next to continue.', 'trendr' );
		else
			$permalink_setup_text = __( 'To make sure the pages created in the previous step work correctly, pretty permalinks must be active on your site.', 'trendr' );

		if ( !got_mod_rewrite() && !iis7_supports_permalinks() )
			$prefix = '/index.php'; ?>

		<p><?php echo $permalink_setup_text; ?></p>
		<p><?php printf( __( 'Please select the permalink setting you would like to use. For more advanced options please visit the <a href="%s">permalink settings page</a> first, and complete this setup wizard later.', 'trendr' ), admin_url( 'options-permalink.php' ) ); ?>

		<table class="form-table">
			<tr>
				<th><label><input name="permalink_structure" type="radio"<?php if ( empty( $permalink_structure ) || false != strpos( $permalink_structure, $structures[1] ) ) : ?> checked="checked" <?php endif; ?>value="<?php echo esc_attr( $structures[1] ); ?>" class="tog" <?php checked( $structures[1], $permalink_structure ); ?> />&nbsp;<?php _e( 'Day and name' ); ?></label></th>
				<td><code><?php echo get_home_url() . $prefix . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/sample-post/'; ?></code></td>
			</tr>
			<tr>
				<th><label><input name="permalink_structure" type="radio"<?php if ( empty( $permalink_structure ) || false != strpos( $permalink_structure, $structures[2] ) ) : ?> checked="checked" <?php endif; ?> value="<?php echo esc_attr( $structures[2] ); ?>" class="tog" <?php checked( $structures[2], $permalink_structure ); ?> />&nbsp;<?php _e( 'Month and name' ); ?></label></th>
				<td><code><?php echo get_home_url() . $prefix . '/' . date('Y') . '/' . date('m') . '/sample-post/'; ?></code></td>
			</tr>
			<tr>
				<th><label><input name="permalink_structure" type="radio"<?php if ( empty( $permalink_structure ) || false != strpos( $permalink_structure, $structures[3] ) ) : ?> checked="checked" <?php endif; ?> value="<?php echo esc_attr( $structures[3] ); ?>" class="tog" <?php checked( $structures[3], $permalink_structure ); ?> />&nbsp;<?php _e( 'Numeric' ); ?></label></th>
				<td><code><?php echo get_home_url() . $prefix ?>/archives/123</code></td>
			</tr>
		</table>

		<div class="submit clear">
			<input type="hidden" name="save" value="permalinks" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $this->current_step ); ?>" />

			<?php if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && empty( $_POST['skip-htaccess'] ) ) : ?>

				<input type="hidden" name="skip-htaccess" value="skip-htaccess" />

			<?php endif; ?>

			<?php trm_nonce_field( 'trswizard_permalinks' ); ?>

		</div>

	<?php
	}

	function step_theme() {
		if ( !current_user_can( 'activate_plugins' ) )
			return false;

		$installed_plugins = get_plugins();
		$installed_themes  = get_themes();

		$template_pack_installed = false;
		$trs_autotheme_installed  = false;
		$trs_theme_installed      = false;

		foreach ( (array)$installed_plugins as $plugin ) {
			if ( 'trendr Template Pack' == $plugin['Name'] ) {
				$template_pack_installed = true;
			}
		}

		foreach ( (array)$installed_themes as $theme ) {
			foreach ( (array)$theme['Tags'] as $tag ) {
				if ( ( 'trendr Default' != $theme['Name'] ) && ( 'trendr' == $tag ) ) {
					$trs_theme_installed = true;
					$trs_themes[] = $theme;
				}
			}
		}

		// Get theme screenshot
		$current_theme = get_current_theme();
		$screenshot    = '';
		$themes        = get_themes();

		if ( !empty( $themes[$current_theme]['Screenshot'] ) )
			$screenshot = trailingslashit( get_stylesheet_directory_uri() ) . $themes[$current_theme]['Screenshot'];
	?>

		<script type="text/javascript">
			jQuery( document ).ready( function() {
				jQuery( 'select' ).change( function() {
					jQuery( this ).siblings( 'input[@type=radio]' ).click();
				});
			});
		</script>

		<p><?php _e( "trendr introduces a whole range of new screens to display content. To display these screens, you need to decide how you want to handle them in your current theme.", 'trendr' ); ?></p>

		<table class="form-table">
			<tr>
				<th>
					<h5><?php _e( 'Use trendr Default', 'trendr' ); ?></h5>
					<img src="<?php echo plugins_url( '/trendr/trs-themes/trs-default/screenshot.png' ); ?>" alt="<?php _e( 'trendr Default', 'trendr' ); ?>" />
				</th>
				<td>
					<p><?php _e( 'trendr Default contains everything you need to get up and running out of the box. It supports all features and is highly customizable.', 'trendr' ); ?></p>
					<p><strong><?php _e( 'This is the best choice if you do not have an existing WordPress theme, or want to start using trendr immediately.', 'trendr' ); ?></strong></p>
					<p><label><input type="radio" name="theme" value="trs_default" checked="checked" /> <?php _e( 'Yes, please!', 'trendr' ); ?></label></p>
				</td>
			</tr>

			<?php if ( $trs_theme_installed ) : ?>
				<tr>
					<th>
						<h5><?php _e( 'Other themes', 'trendr' ); ?></h5>
						<img src="<?php echo plugins_url( '/trendr/trs-core/images/find.png' ); ?>" alt="<?php _e( 'A trendr theme', 'trendr' ); ?>" />
					</th>
					<td>
						<p><?php _e( "We've found that you already have some other trendr-compatible themes available. To use one of those, pick it from this list.", 'trendr' ); ?></p>
						<p>
							<label>
								<input type="radio" name="theme" value="3rd_party" /> <?php _e( 'Use this theme', 'trendr' ); ?>
							</label>
							<select name="3rd_party_theme">

								<?php foreach( (array) $trs_themes as $theme ) : ?>
									<option value="<?php echo $theme['Template'] . ',' . $theme['Stylesheet']; ?>"><?php echo $theme['Name']; ?></option>
								<?php endforeach; ?>

							</select>
						</p>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<th>
					<h5><?php _e( 'Manually update current theme', 'trendr' ); ?></h5>
					<?php if ( !empty( $screenshot ) ) : ?>
						<img src="<?php echo esc_url( $screenshot ); ?>" alt="<?php _e( 'Your existing theme', 'trendr' ); ?>" />
					<?php endif; ?>
				</th>
				<td>
					<p><?php _e( 'The trendr Template Pack plugin will guide you through the process of manually upgrading your existing WordPress theme. This usually involves following the step-by-step instructions and copying the trendr template files into your theme. This option requires a working knowledge of CSS and HTML, as you will need to tweak the new templates to match your existing theme.', 'trendr' ); ?></p>

					<?php if ( empty( $template_pack_installed ) ) : ?>

						<p><a id="trs-template-pack" class="thickbox onclick button" href="<?php echo network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=trs-template-pack&TB_iframe=true&width=640&height=500' ); ?>"><?php _e( 'Install trendr Template Pack', 'trendr' ); ?></a></p>

					<?php else : ?>

						<p><label><input type="radio" name="theme" value="manual_trm" /> <?php _e( 'Choose this option (go to Appearance &rarr; TRS Compatibility after setup is complete)', 'trendr' ); ?></label></p>
						<p><a id="trs-template-pack" class="button installed disabled" href="javascript:void();"><span></span><?php _e( 'Plugin Installed', 'trendr' ); ?></a></p>

					<?php endif; ?>

				</td>
			</tr>

			<tr>
				<th>
					<h5><?php _e( 'Do not change theme', 'trendr' ) ?></h5>
				</th>
				<td>
					<p><?php _e( "You are happy with your current theme and plan on changing it later.", 'trendr' ); ?></p>
					<p><strong><?php _e( 'This is the best choice if you have a highly customized theme on your site already, and want to later manually integrate trendr into your site.', 'trendr' ); ?></strong></p>

					<p><label><input type="radio" name="theme" value="do_not_change" /> <?php _e( "Don't change my current theme", 'trendr' ); ?></label></p>

				</td>
			</tr>
		</table>

		<div class="submit clear">
			<input type="hidden" name="save" value="theme" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $this->current_step ) ?>" />

			<?php trm_nonce_field( 'trswizard_theme' ) ?>

		</div>

	<?php
	}

	function step_finish() {
		if ( !current_user_can( 'activate_plugins' ) )
			return false;

		// What type of action is happening here?
		$type = ( 'install' == $this->setup_type ) ? __( 'setup', 'trendr' ) : __( 'update', 'trendr' ); ?>

		<p><?php printf( __( "The trendr %1\$s is complete, and your site is ready to go!", 'trendr' ), $type, $type ); ?></p>

		<div class="submit clear">
			<input type="hidden" name="save" value="finish" />
			<input type="hidden" name="step" value="<?php echo esc_attr( $this->current_step ); ?>" />

			<?php trm_nonce_field( 'trswizard_finish' ); ?>

		</div>

	<?php
	}

	/** Save Step Methods *****************************************************/

	function step_db_update_save() {
		global $trs;

		if ( isset( $_POST['submit'] ) ) {
			check_admin_referer( 'trswizard_db_update' );

			// Run the schema install to update tables
			trs_core_install();

			if ( $this->database_version < 1801 )
				$this->update_1_5();

			// Update the active components option early if we're updating
			if ( 'update' == $this->setup_type )
				trs_update_option( 'trs-active-components', $trs->active_components );

			return true;
		}

		return false;
	}

	function step_ms_update_save() {
		global $trmdb;

		if ( isset( $_POST['submit'] ) ) {
			check_admin_referer( 'trswizard_ms_update' );

			if ( !$active_components = trs_get_option( 'trs-active-components' ) )
				$active_components = array();

			// Transfer important settings from blog options to site options
			$options = array(
				'trs-db-version'        => $this->database_version,
				'trs-active-components' => $active_components,
				'portrait-default'       => get_option( 'portrait-default' )
			);
			trs_core_activate_site_options( $options );

			if ( isset( $_POST['trs_components']['blogs'] ) ) {
				$active_components['blogs'] = 1;

				// Make sure that the pages are created on the trs_get_root_blog_id(), no matter which Dashboard the setup is being run on
				if ( !empty( $trmdb->blogid ) && ( $trmdb->blogid != trs_get_root_blog_id() ) && ( !defined( 'TRS_ENABLE_MULTIBLOG' ) ) )
					switch_to_blog( trs_get_root_blog_id() );

				// Move trs-pages data from the blog options table to site options
				$existing_pages	= trs_get_option( 'trs-pages' );

				$trs_pages       = $this->setup_pages( (array)$_POST['trs_pages'] );
				$trs_pages       = array_merge( (array)$existing_pages, (array)$trs_pages );

				trs_update_option( 'trs-pages', $trs_pages );

				if ( !empty( $trmdb->blogid ) && ( $trmdb->blogid != trs_get_root_blog_id() ) && ( !defined( 'TRS_ENABLE_MULTIBLOG' ) ) )
					restore_current_blog();

				trs_core_install( $active_components );
			}

			trs_update_option( 'trs-active-components', $active_components );

			return true;
		}

		return false;
	}


	function step_components_save() {
		if ( isset( $_POST['submit'] ) && isset( $_POST['trs_components'] ) ) {
			check_admin_referer( 'trswizard_components' );

			$active_components = array();

			// Settings form submitted, now save the settings.
			foreach ( (array)$_POST['trs_components'] as $key => $value )
				$active_components[$key] = 1;

			trs_update_option( 'trs-active-components', $active_components );

			trm_cache_flush();
			trs_core_install();

			return true;
		}

		return false;
	}

	function step_pages_save() {
		global $trmdb;

		if ( isset( $_POST['submit'] ) && isset( $_POST['trs_pages'] ) ) {
			check_admin_referer( 'trswizard_pages' );

			// Make sure that the pages are created on the trs_get_root_blog_id(), no matter which Dashboard the setup is being run on
			if ( !empty( $trmdb->blogid ) && ( $trmdb->blogid != trs_get_root_blog_id() ) && ( !defined( 'TRS_ENABLE_MULTIBLOG' ) ) )
				switch_to_blog( trs_get_root_blog_id() );

			// Delete any existing pages
			$existing_pages = trs_core_update_get_page_meta( 'trs-pages' );

			foreach ( (array)$existing_pages as $page_id )
				trm_delete_post( $page_id, true );

			$blog_pages   = $this->setup_pages( (array)$_POST['trs_pages'] );
			trs_update_option( 'trs-pages', $blog_pages );

			if ( !empty( $trmdb->blogid ) && ( $trmdb->blogid != trs_get_root_blog_id() ) && ( !defined( 'TRS_ENABLE_MULTIBLOG' ) ) )
				restore_current_blog();

			return true;
		}

		return false;
	}

	function step_permalinks_save() {
		global $trm_rewrite, $current_site, $current_blog;

		// Prevent debug notices
		$iis7_permalinks = $usingpi = $writable = false;

		if ( isset( $_POST['submit'] ) ) {
			check_admin_referer( 'trswizard_permalinks' );

			$home_path       = get_home_path();
			$iis7_permalinks = iis7_supports_permalinks();

			if ( isset( $_POST['permalink_structure'] ) ) {
				$permalink_structure = $_POST['permalink_structure'];

				if ( !empty( $permalink_structure ) )
					$permalink_structure = preg_replace( '#/+#', '/', '/' . $_POST['permalink_structure'] );

				if ( ( defined( 'VHOST' ) && constant( 'VHOST' ) == 'no' ) && $permalink_structure != '' && $current_site->domain . $current_site->path == $current_blog->domain . $current_blog->path )
					$permalink_structure = '/blog' . $permalink_structure;

				$trm_rewrite->set_permalink_structure( $permalink_structure );
			}

			if ( !empty( $iis7_permalinks ) ) {
				if ( ( !file_exists( $home_path . 'web.config' ) && win_is_writable( $home_path ) ) || win_is_writable( $home_path . 'web.config' ) ) {
					$writable = true;
				}
			} else {
				if ( ( !file_exists( $home_path . '.htaccess' ) && is_writable( $home_path ) ) || is_writable( $home_path . '.htaccess' ) ) {
					$writable = true;
				}
			}

			if ( $trm_rewrite->using_index_permalinks() )
				$usingpi = true;

			$trm_rewrite->flush_rules();

			if ( !empty( $iis7_permalinks ) || ( empty( $usingpi ) && empty( $writable ) ) ) {

				function _trs_core_wizard_step_permalinks_message() {
					global $trm_rewrite; ?>

					<div id="message" class="updated fade"><p>

						<?php
							_e( 'Oops, there was a problem creating a configuration file. ', 'trendr' );

							if ( !empty( $iis7_permalinks ) ) {

								if ( !empty( $permalink_structure ) && empty( $usingpi ) && empty( $writable ) ) {

									_e( 'If your <code>web.config</code> file were <a href="http://codex.wordpress.org/Changing_File_Permissions">writable</a>, we could do this automatically, but it isn&#8217;t so this is the url rewrite rule you should have in your <code>web.config</code> file. Click in the field and press <kbd>CTRL + a</kbd> to select all. Then insert this rule inside of the <code>/&lt;configuration&gt;/&lt;system.webServer&gt;/&lt;rewrite&gt;/&lt;rules&gt;</code> element in <code>web.config</code> file.' ); ?>

									<br /><br />

									<textarea rows="9" class="large-text readonly" style="background: #fff;" name="rules" id="rules" readonly="readonly"><?php echo esc_html( $trm_rewrite->iis7_url_rewrite_rules() ); ?></textarea>

								<?php

								} else if ( !empty( $permalink_structure ) && empty( $usingpi ) && !empty( $writable ) ); {
									_e( 'Permalink structure updated. Remove write access on web.config file now!' );
								}

							} else {

								_e( 'If your <code>.htaccess</code> file were <a href="http://codex.wordpress.org/Changing_File_Permissions">writable</a>, we could do this automatically, but it isn&#8217;t so these are the mod_rewrite rules you should have in your <code>.htaccess</code> file. Click in the field and press <kbd>CTRL + a</kbd> to select all.' ); ?>

								<br /><br />

								<textarea rows="6" class="large-text readonly" style="background: #fff;" name="rules" id="rules" readonly="readonly"><?php echo esc_html( $trm_rewrite->mod_rewrite_rules() ); ?></textarea>

							<?php } ?>

						<br /><br />

						<?php
							if ( empty( $iis7_permalinks ) )
								_e( 'Paste all these rules into a new <code>.htaccess</code> file in the root of your WordPress installation and save the file. Once you\'re done, please hit the "Save and Next" button to continue.', 'trendr' );
						?>

					</p></div>

				<?php
				}

				if ( 'post' == strtolower( $_SERVER['REQUEST_METHOD'] ) && !empty( $_POST['skip-htaccess'] ) ) {
					return true;
				} else {
					add_action( 'trs_admin_notices', '_trs_core_wizard_step_permalinks_message' );
					return false;
				}
			}

			return true;
		}

		return false;
	}

	function step_theme_save() {
		if ( isset( $_POST['submit'] ) && isset( $_POST['theme'] ) ) {
			check_admin_referer( 'trswizard_theme' );

			if ( is_multisite() && trs_get_root_blog_id() != get_current_blog_id() )
				switch_to_blog( trs_get_root_blog_id() );

			switch ( $_POST['theme'] ) {

				// Activate the trs-default theme
				case 'trs_default' :
					switch_theme( 'trs-default', 'trs-default' );
					break;

				// Activate Template Pack plugin
				case 'manual_trm' :

					// Include
					require_once( ABSPATH . TRMINC . '/plugin.php' );
					$installed_plugins = get_plugins();

					foreach ( $installed_plugins as $key => $plugin ) {
						if ( 'trendr Template Pack' == $plugin['Name'] ) {
							activate_plugin( $key );
						}
					}
					break;

				// Pick a theme from the repo
				case '3rd_party' :
					if ( empty( $_POST['3rd_party_theme'] ) )
						return false;

					$theme = explode( ',', $_POST['3rd_party_theme'] );
					switch_theme( $theme[0], $theme[1] );
					break;

				// Keep existing theme
				case 'do_not_change' :
					return true;
					break;
			}

			if ( is_multisite() )
				restore_current_blog();

			return true;
		}

		return false;
	}

	function step_finish_save() {
		if ( isset( $_POST['submit'] ) ) {
			check_admin_referer( 'trswizard_finish' );

			// Update the DB version in the database
			// Stored in sitemeta. Do not use trs_update_option()
			update_site_option( 'trs-db-version', $this->new_version );
			delete_site_option( 'trs-core-db-version' );

			// Delete the setup cookie
			@setcookie( 'trs-wizard-step', '', time() - 3600, COOKIEPATH );

			// Load TRS and hook the admin menu, so that the redirect is successful
			if ( !function_exists( 'trs_core_update_message' ) )
				require( TRM_PLUGIN_DIR . '/trendr/trs-core/admin/trs-core-admin.php' );

			trs_core_add_admin_menu();

			// Redirect to the trendr dashboard
			$redirect = trs_core_update_do_network_admin() ? add_query_arg( array( 'page' => 'trs-general-settings' ), network_admin_url( 'admin.php' ) ) : add_query_arg( array( 'page' => 'trs-general-settings' ), admin_url( 'admin.php' ) );

			trm_redirect( $redirect );

			return true;
		}

		return false;
	}

	function setup_pages( $pages ) {
		foreach ( $pages as $key => $value ) {
			if ( 'page' == $value ) {
				// Check for the selected page
				if ( !empty( $_POST['trs-' . $key . '-page'] ) )
					$trs_pages[$key] = (int)$_POST['trs-' . $key . '-page'];
				else
					$trs_pages[$key] = trm_insert_post( array( 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_title' => ucwords( $key ), 'post_status' => 'publish', 'post_type' => 'page' ) );
			} else {
				// Create a new page
				$trs_pages[$key] = trm_insert_post( array( 'comment_status' => 'closed', 'ping_status' => 'closed', 'post_title' => ucwords( $value ), 'post_status' => 'publish', 'post_type' => 'page' ) );
			}
		}

		return $trs_pages;
	}

	// Database update methods based on version numbers
	function update_1_5() {
		// Delete old database version options
		delete_site_option( 'trs-activity-db-version' );
		delete_site_option( 'trs-blogs-db-version'    );
		delete_site_option( 'trs-friends-db-version'  );
		delete_site_option( 'trs-groups-db-version'   );
		delete_site_option( 'trs-messages-db-version' );
		delete_site_option( 'trs-xprofile-db-version' );
	}

	/**
	 * Reset the cookie so the install script starts over
	 */
	function reset_cookie() {
		@setcookie( 'trs-wizard-step', '', time() - 3600, COOKIEPATH );
	}
}

function trs_core_setup_wizard_init() {
	global $trs_wizard;

	$trs_wizard = new TRS_Core_Setup_Wizard;
}
add_action( trs_core_update_admin_hook(), 'trs_core_setup_wizard_init', 7 );

function trs_core_install( $active_components = false ) {
	global $trmdb;

	if ( empty( $active_components ) )
		$active_components = apply_filters( 'trs_active_components', trs_get_option( 'trs-active-components' ) );

	require( dirname( __FILE__ ) . '/trs-core-schema.php' );

	// Core DB Tables
	trs_core_install_notifications();

	// Activity Streams
	if ( !empty( $active_components['activity'] ) )
		trs_core_install_activity_streams();

	// Friend Connections
	if ( !empty( $active_components['friends'] ) )
		trs_core_install_friends();

	// Extensible Groups
	if ( !empty( $active_components['groups'] ) )
		trs_core_install_groups();

	// Private Messaging
	if ( !empty( $active_components['messages'] ) )
		trs_core_install_private_messaging();

	// Extended Profiles
	if ( !empty( $active_components['xprofile'] ) )
		trs_core_install_extended_profiles();

	// Blog tracking
	if ( !empty( $active_components['blogs'] ) )
		trs_core_install_blog_tracking();
}

function trs_core_update( $disabled ) {
	global $trmdb;

	require( dirname( __FILE__ ) . '/trs-core-schema.php' );
}

function trs_update_db_stuff() {
	$trs_prefix = trs_core_get_table_prefix();
	// Rename the old user activity cached table if needed.
	if ( $trmdb->get_var( "SHOW TABLES LIKE '%{$trs_prefix}trs_activity_user_activity_cached%'" ) )
		$trmdb->query( "RENAME TABLE {$trs_prefix}trs_activity_user_activity_cached TO {$trs->activity->table_name}" );

	// Rename fields from pre TRS 1.2
	if ( $trmdb->get_var( "SHOW TABLES LIKE '%{$trs->activity->table_name}%'" ) ) {
		if ( $trmdb->get_var( "SHOW COLUMNS FROM {$trs->activity->table_name} LIKE 'component_action'" ) )
			$trmdb->query( "ALTER TABLE {$trs->activity->table_name} CHANGE component_action type varchar(75) NOT NULL" );

		if ( $trmdb->get_var( "SHOW COLUMNS FROM {$trs->activity->table_name} LIKE 'component_name'" ) )
			$trmdb->query( "ALTER TABLE {$trs->activity->table_name} CHANGE component_name component varchar(75) NOT NULL" );
	}

	// On first installation - record all existing blogs in the system.
	if ( !(int)$trs->site_options['trs-blogs-first-install'] ) {
		trs_blogs_record_existing_blogs();
		trs_update_option( 'trs-blogs-first-install', 1 );
	}

	if ( is_multisite() )
		trs_core_add_illegal_names();

	// Update and remove the message threads table if it exists
	if ( $trmdb->get_var( "SHOW TABLES LIKE '%{$trs_prefix}trs_messages_threads%'" ) ) {
		$update = TRS_Messages_Thread::update_tables();

		if ( $update )
			$trmdb->query( "DROP TABLE {$trs_prefix}trs_messages_threads" );
	}

}

function trs_core_wizard_message() {
	if ( isset( $_GET['updated'] ) )
		$message = __( 'Installation was successful. The available options have now been updated, please continue with your selection.', 'trendr' );
	else
		return false; ?>

	<div id="message" class="updated">
		<p><?php echo esc_attr( $message ) ?></p>
	</div>

<?php
}
add_action( 'trs_admin_notices', 'trs_core_wizard_message' );

// Alter thickbox screens so the entire plugin download and install
// interface is contained within.
function trs_core_wizard_thickbox() {
	$form_action = trs_core_update_do_network_admin() ? network_admin_url( add_query_arg( array( 'page' => 'trs-wizard', 'updated' => '1' ), 'admin.php' ) ) : admin_url( add_query_arg( array( 'page' => 'trs-wizard', 'updated' => '1' ), 'admin.php' ) ); ?>

	<script type="text/javascript">
		jQuery('p.action-button a').attr( 'target', '' );

		if ( window.location != window.parent.location ) {
			jQuery('#adminmenu, #trmhead, #footer, #update-nag, #screen-meta').hide();
			jQuery('#trmbody').css( 'margin', '15px' );
			jQuery('body').css( 'min-width', '30px' );
			jQuery('#trmwrap').css( 'min-height', '30px' );
			jQuery('a').removeClass( 'thickbox thickbox-preview onclick' );
			jQuery('body.update-php div.wrap p:last').hide();
			jQuery('body.update-php div.wrap p:last').after( '<p><a class="button" target="_parent" href="<?php echo $form_action; ?>"><?php _e( 'Finish', 'trendr' ) ?></a></p>' );
		}
	</script>

<?php
}
add_action( 'admin_footer', 'trs_core_wizard_thickbox' );

/**
 * Adds the "trendr" admin submenu item to the Site Admin tab.
 *
 * @package trendr Core
 * @global object $trs Global trendr settings object
 * @global $trmdb WordPress DB access object.
 * @uses add_submenu_page() TRM function to add a submenu item
 */
function trs_core_update_add_admin_menu() {
	global $trs_wizard;

	// Only load this version of the menu if this is an upgrade or a new installation
	if ( empty( $trs_wizard->setup_type ) )
		return false;

	if ( !current_user_can( 'activate_plugins' ) )
		return false;

	if ( 'install' == $trs_wizard->setup_type )
		$status = __( 'Setup', 'trendr' );
	else
		$status = __( 'Update', 'trendr' );

	// Add the administration tab under the "Site Admin" tab for site administrators
	add_menu_page( __( 'trendr', 'trendr' ), __( 'trendr', 'trendr' ), 'manage_options', 'trs-wizard', '' );
	$hook = add_submenu_page( 'trs-wizard', $status, $status, 'manage_options', 'trs-wizard', array( $trs_wizard, 'html' ) );

	// Add a hook for css/js
	add_action( "admin_print_styles-$hook", 'trs_core_update_add_admin_menu_styles' );
}
add_action( trs_core_update_admin_hook(),  'trs_core_update_add_admin_menu', 9 );

function trs_core_update_add_admin_menu_styles() {
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		trm_enqueue_style( 'trs-admin-css', apply_filters( 'trs_core_admin_css', plugins_url( '/trendr' ) . '/trs-core/css/admin.dev.css' ), array(), '20110723' );
		trm_enqueue_script( 'trs-update-js', apply_filters( 'trs_core_update_js', plugins_url( '/trendr' ) . '/trs-core/js/update.dev.js' ), array( 'jquery' ), '20110723' );
	} else {
		trm_enqueue_style( 'trs-admin-css', apply_filters( 'trs_core_admin_css', plugins_url( '/trendr' ) . '/trs-core/css/admin.css' ), array(), '20110723' );
		trm_enqueue_script( 'trs-update-js', apply_filters( 'trs_core_update_js', plugins_url( '/trendr' ) . '/trs-core/js/update.js' ), array( 'jquery' ), '20110723' );

	}

	trm_enqueue_script( 'thickbox' );
	trm_enqueue_style( 'thickbox' ); ?>

	<style type="text/css">
		/* Wizard Icon */
		ul#adminmenu li.toplevel_page_trs-wizard .trm-menu-image a img { display: none; }
		ul#adminmenu li.toplevel_page_trs-wizard .trm-menu-image a { background-image: url( <?php echo plugins_url( 'trendr/trs-core/images/admin_menu_icon.png' ) ?> ) !important; background-position: -1px -32px; }
		ul#adminmenu li.toplevel_page_trs-wizard:hover .trm-menu-image a,
		ul#adminmenu li.toplevel_page_trs-wizard.trm-has-current-submenu .trm-menu-image a {
			background-position: -1px 0;
		}

		/* Settings Icon */
		ul#adminmenu li.toplevel_page_trs-general-settings .trm-menu-image a img { display: none; }
		ul#adminmenu li.toplevel_page_trs-general-settings .trm-menu-image a { background-image: url( <?php echo plugins_url( 'trendr/trs-core/images/admin_menu_icon.png' ) ?> ) !important; background-position: -1px -32px; }
		ul#adminmenu li.toplevel_page_trs-general-settings:hover .trm-menu-image a,
		ul#adminmenu li.toplevel_page_trs-general-settings.trm-has-current-submenu .trm-menu-image a {
			background-position: -1px 0;
		}
	</style>

<?php
}
add_action( 'admin_head', 'trs_core_update_add_admin_menu_styles' );

/**
 * Fetches TRS pages from the meta table
 *
 * @package trendr Core
 * @since 1.5
 *
 * @return array $page_ids
 */
function trs_core_update_get_page_meta() {
	if ( !$page_ids = trs_get_option( 'trs-pages' ) )
		$page_ids = array();

	return apply_filters( 'trs_core_update_get_page_meta', $page_ids );
}

function trs_core_update_do_network_admin() {
	$do_network_admin = false;

	if ( is_multisite() && ( !defined( 'TRS_ENABLE_MULTIBLOG' ) || !TRS_ENABLE_MULTIBLOG ) )
		$do_network_admin = true;

	return apply_filters( 'trs_core_do_network_admin', $do_network_admin );
}

function trs_core_update_admin_hook() {
	$hook = trs_core_update_do_network_admin() ? 'network_admin_menu' : 'admin_menu';

	return apply_filters( 'trs_core_admin_hook', $hook );
}

/**
 * Adds an admin nag about running the TRS upgrade/install wizard
 *
 * @package trendr Core
 * @since 1.5
 * @global $pagenow The current admin page
 */
function trs_core_update_nag() {
	global $trs_wizard, $pagenow;

	if ( empty( $trs_wizard->setup_type ) )
		return;

	if ( !is_super_admin() )
		return;

	if ( 'admin.php' == $pagenow && ( empty( $_GET['page'] ) || 'trs-wizard' == $_GET['page'] ) )
		return;

	$url = trs_core_update_do_network_admin() ? network_admin_url( 'admin.php?page=trs-wizard' ) : admin_url( 'admin.php?page=trs-wizard' );

	switch( $trs_wizard->setup_type ) {
		case 'update':
			$msg = sprintf( __( 'trendr has been updated! Please run the <a href="%s">update wizard</a>.', 'trendr' ), $url );
			break;

		default:
		case 'install':
			$msg = sprintf( __( 'trendr was successfully installed! Please run the <a href="%s">installation wizard</a>.', 'trendr' ), $url );
			break;
	}

	echo '<div class="update-nag">' . $msg . '</div>';
}
add_action( 'admin_notices', 'trs_core_update_nag', 5 );
add_action( 'network_admin_notices', 'trs_core_update_nag', 5 );

?>