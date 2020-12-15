<?php
/**
 * trendr Check-ins support tab file.
 *
 * @package Trs_Checkins
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="trschk-adming-setting">
	<div class="trschk-tab-header"><h3><?php esc_html_e( 'FAQ(s)', 'trs-checkins' ); ?></h3></div>

	<div class="trschk-admin-settings-block">
		<div id="trschk-settings-tbl">
			<div class="trschk-admin-row">
				<div>
					<button class="trschk-accordion"><?php esc_html_e( 'Does this plugin require trendr?', 'trs-checkins' ); ?></button>
					<div class="panel">
						<p><?php esc_html_e( 'Yes, It needs you to have trendr installed and activated.', 'trs-checkins' ); ?></p>
						<p><?php esc_html_e( 'You\'ll also get an admin notice and the plugin will become ineffective if the required plugin will not be there.', 'trs-checkins' ); ?></p>
					</div>
				</div>
			</div>
			<div class="trschk-admin-row">
				<div>
					<button class="trschk-accordion"><?php esc_html_e( 'What is the use of API Key option provided in general settings section?', 'trs-checkins' ); ?></button>
					<div class="panel">
						<p><?php esc_html_e( 'With the help of Google Places Api Key, user can check-in with places autocomplete while updating post in trendr and list checked in location in google map.', 'trs-checkins' ); ?></p>
					</div>
				</div>
			</div>
			<div class="trschk-admin-row">
				<div>
					<button class="trschk-accordion"><?php esc_html_e( 'Does this plugin require current location service?', 'trs-checkins' ); ?></button>
					<div class="panel">
						<p><?php esc_html_e( 'Yes, this plugin require location service and you can allow it from browser settings.', 'trs-checkins' ); ?></p>
					</div>
				</div>
			</div>
			<div class="trschk-admin-row">
				<div>
					<button class="trschk-accordion"><?php esc_html_e( 'How to check-in using Location Autocomplete option provided in general settings section?', 'trs-checkins' ); ?></button>
					<div class="panel">
						<p><?php esc_html_e( 'Location Autocomplete setting provide an interface at activity page to post an update using google places autocomplete.', 'trs-checkins' ); ?></p>
						<p><?php esc_html_e( 'There is an option [Add as my location] to set current check-in location as a favourite location.', 'trs-checkins' ); ?></p>
					</div>
				</div>
			</div>
			<div class="trschk-admin-row">
				<div>
					<button class="trschk-accordion"><?php esc_html_e( 'How to check-in using Place Types option provided in general settings section?', 'trs-checkins' ); ?></button>
					<div class="panel">
						<p><?php esc_html_e( 'Place Types setting provide an interface at activity page to post an update using selected google places types. Range option will set the range in kilometers for fetching the places during check-in.', 'trs-checkins' ); ?></p>
					</div>
				</div>
			</div>
			<div class="trschk-admin-row">
				<div>
					<button class="trschk-accordion"><?php esc_html_e( 'Where can I see all check-ins activity?', 'trs-checkins' ); ?></button>
					<div class="panel">
						<p><?php esc_html_e( 'Check-ins filter option provides trendr filter drop-down to list all check-ins activity.', 'trs-checkins' ); ?></p>
					</div>
				</div>
			</div>
			<div class="trschk-admin-row">
				<div>
					<button class="trschk-accordion"><?php esc_html_e( 'Where can I see favourite locations?', 'trs-checkins' ); ?></button>
					<div class="panel">
						<p><?php esc_html_e( 'We are listing all favorite locations under Check-ins tab at trendr profile page.', 'trs-checkins' ); ?></p>
					</div>
				</div>
			</div>
			<div class="trschk-admin-row">
				<div>
					<button class="trschk-accordion"><?php esc_html_e( 'How to set location at profile page?', 'trs-checkins' ); ?></button>
					<div class="panel">
						<p><?php esc_html_e( 'The plugin provides xprofile location field to set location at trendr edit profile page.', 'trs-checkins' ); ?></p>
					</div>
				</div>
			</div>
			<div class="trschk-admin-row">
				<div>
					<button class="trschk-accordion"><?php esc_html_e( 'If I need to customize plugin, to whom I should contact?', 'trs-checkins' ); ?></button>
					<div class="panel">
						<p><?php esc_html_e( 'If you need additional help you can contact us at <a href="https://wbcomdesigns.com/contact/" target="_blank" title="Wbcom Designs">Wbcom Designs</a>.', 'trs-checkins' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
