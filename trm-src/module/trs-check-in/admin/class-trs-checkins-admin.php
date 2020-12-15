<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    Trs_Checkins
 * @sutrsackage Trs_Checkins/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Trs_Checkins
 * @sutrsackage Trs_Checkins/admin
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Trs_Checkins_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->trschk_save_general_settings();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if ( ( strpos( filter_input( INPUT_SERVER, 'REQUEST_URI' ), 'trs-checkins' ) !== false ) ) {
			trm_enqueue_style( $this->plugin_name . '-font-awesome', TRSCHK_PLUGIN_URL . 'public/css/font-awesome.min.css' );
			trm_enqueue_style( $this->plugin_name . '-selectize-css', plugin_dir_url( __FILE__ ) . 'css/selectize.css' );
			trm_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/trs-checkins-admin.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( strpos( filter_input( INPUT_SERVER, 'REQUEST_URI' ), 'trs-checkins' ) !== false ) {
			trm_enqueue_script( $this->plugin_name . '-selectize-js', plugin_dir_url( __FILE__ ) . 'js/selectize.min.js', array( 'jquery' ) );
			trm_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/trs-checkins-admin.js', array( 'jquery' ), $this->version, false );

			trm_localize_script(
				$this->plugin_name,
				'trschk_admin_js_obj',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
			);
		}

	}

	/**
	 * Register a menu page to handle checkins settings
	 *
	 * @since    1.0.0
	 */
	public function trschk_add_menu_page() {
		add_menu_page( __( 'trendr Checkins Settings', 'trs-checkins' ), __( 'Check-ins', 'trs-checkins' ), 'manage_options', $this->plugin_name, array( $this, 'trschk_admin_settings_page' ), 'dashicons-location', 59 );
	}

	/**
	 * Actions performed to create a submenu page content
	 */
	public function trschk_admin_settings_page() {
		global $allowedposttags;
		$tab = filter_input( INPUT_GET, 'tab' ) ? filter_input( INPUT_GET, 'tab' ) : $this->plugin_name;
		?>
		<div class="wrap">
			<div class="trschk-header">
				<div class="trschk-extra-actions">
					<button type="button" class="button button-secondary" onclick="window.open('https://wbcomdesigns.com/contact/', '_blank');"><i class="fa fa-envelope" aria-hidden="true"></i> <?php esc_html_e( 'Email Support', 'trs-checkins' ); ?></button>
					<button type="button" class="button button-secondary" onclick="window.open('https://wbcomdesigns.com/helpdesk/article-categories/trendr-checkins/', '_blank');"><i class="fa fa-file" aria-hidden="true"></i> <?php esc_html_e( 'User Manual', 'trs-checkins' ); ?></button>
					<button type="button" class="button button-secondary" onclick="window.open('https://trendr.org/support/plugin/trs-check-in/reviews/', '_blank');"><i class="fa fa-star" aria-hidden="true"></i> <?php esc_html_e( 'Rate Us on trendr.org', 'trs-checkins' ); ?></button>
				</div>
				<h2 class="trschk-plugin-heading"><?php esc_html_e( 'trendr Check-ins', 'trs-checkins' ); ?></h2>
			</div>
			<form method="POST" action="">

				<?php
				settings_errors();
				if ( filter_input( INPUT_POST, 'trschk-submit-general-settings' ) !== null ) {
					$success_msg  = "<div class='notice updated is-dismissible' id='message'>";
					$success_msg .= '<p>' . __( '<strong>Settings Saved.</strong>', 'trs-checkins' ) . '</p>';
					$success_msg .= '</div>';
					echo trm_kses( $success_msg, $allowedposttags );
				}
				$this->trschk_plugin_settings_tabs();
				settings_fields( $tab );
				?>
				<?php do_settings_sections( $tab ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Actions performed to create tabs on the sub menu page
	 */
	public function trschk_plugin_settings_tabs() {
		$current_tab = filter_input( INPUT_GET, 'tab' ) ? filter_input( INPUT_GET, 'tab' ) : $this->plugin_name;
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab === $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . esc_attr( $active ) . '" id="' . esc_attr( $tab_key ) . '-tab" href="?page=' . esc_attr( $this->plugin_name ) . '&tab=' . esc_attr( $tab_key ) . '">' . esc_attr( $tab_caption ) . '</a>';
		}
		echo '</h2>';
	}

	/**
	 * General Tab.
	 */
	public function trschk_plugin_settings() {
		// General settings tab.
		$this->plugin_settings_tabs['trs-checkins'] = __( 'General', 'trs-checkins' );
		register_setting( 'trs-checkins', 'trs-checkins' );
		add_settings_section( 'trs-checkins-section', ' ', array( &$this, 'trschk_general_settings_content' ), 'trs-checkins' );

		// Support tab.
		$this->plugin_settings_tabs['trschk-support'] = __( 'Support', 'trs-checkins' );
		register_setting( 'trschk-support', 'trschk-support' );
		add_settings_section( 'trschk-support-section', ' ', array( &$this, 'trschk_support_settings_content' ), 'trschk-support' );
	}

	/**
	 * General Tab Content
	 */
	public function trschk_general_settings_content() {
		if ( file_exists( dirname( __FILE__ ) . '/includes/trs-checkins-general-settings.php' ) ) {
			require_once dirname( __FILE__ ) . '/includes/trs-checkins-general-settings.php';
		}
	}

	/**
	 * Support Tab Content
	 */
	public function trschk_support_settings_content() {
		if ( file_exists( dirname( __FILE__ ) . '/includes/trs-checkins-support-settings.php' ) ) {
			require_once dirname( __FILE__ ) . '/includes/trs-checkins-support-settings.php';
		}
	}

	/**
	 * Save Plugin General Settings
	 */
	public function trschk_save_general_settings() {
		if ( filter_input( INPUT_POST, 'trschk-submit-general-settings' ) !== null ) {
			$checkin_by = '';
			if ( filter_input( INPUT_POST, 'trschk-checkin-by' ) !== null ) {
				$checkin_by = filter_input( INPUT_POST, 'trschk-checkin-by', FILTER_SANITIZE_STRING );
			}

			$admin_settings = array(
				'apikey'     => filter_input( INPUT_POST, 'trschk-api-key', FILTER_SANITIZE_STRING ),
				'checkin_by' => $checkin_by,
				'range'      => filter_input( INPUT_POST, 'trschk-google-places-range', FILTER_SANITIZE_STRING ),
				'placetypes' => ( ! empty( $_POST['trschk-google-place-types'] ) ) ? trm_unslash( $_POST['trschk-google-place-types'] ) : array(),
			);

			trs_update_option( 'trschk_general_settings', $admin_settings );

		}
	}

	/**
	 * Ajax served to delete the group type
	 */
	public function trschk_verify_apikey() {
		if ( filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) && filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING ) === 'trschk_verify_apikey' ) {
			$apikey    = filter_input( INPUT_POST, 'apikey', FILTER_SANITIZE_STRING );
			$latitude  = filter_input( INPUT_POST, 'latitude', FILTER_SANITIZE_STRING );
			$longitude = filter_input( INPUT_POST, 'longitude', FILTER_SANITIZE_STRING );
			$radius    = 10000;

			$response = Trs_Checkins::trschk_fetch_google_places( $apikey, $latitude, $longitude, $radius );
			$code     = trm_remote_retrieve_response_code( $response );
			$message  = 'verified';
			trs_update_option( 'trschk_apikey_verified', 'yes' );
			if ( 200 !== $code ) {
				$message = 'not-verified';
				trs_update_option( 'trschk_apikey_verified', 'no' );
			}

			$response = array( 'message' => $message );
			trm_send_json_success( $response );
			die;
		}
	}

	/**
	 * This function will list the checkin link in the dropdown list.
	 *
	 * @param    array $trm_admin_nav    trendr Check-ins nav array.
	 */
	public function trschk_setup_admin_bar_links( $trm_admin_nav = array() ) {
		global $trm_admin_bar;
		$profile_menu_slug  = 'checkin';
		$profile_menu_title = __( 'Check-ins', 'trs-checkins' );

		$base_url = trs_loggedin_user_domain() . $profile_menu_slug;
		if ( is_user_logged_in() ) {
			$trm_admin_bar->add_menu(
				array(
					'parent' => 'my-account-trendr',
					'id'     => 'my-account-' . $profile_menu_slug,
					'title'  => $profile_menu_title,
					'href'   => trailingslashit( $base_url ),
				)
			);
		}
	}

}
