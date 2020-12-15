<?php
/**
 * trendr Check-ins general setting tab file.
 *
 * @package Trs_Checkins
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $trs_checkins;
$saved_range = $trs_checkins->google_places_range;

$verify_btn_style = 'display: none;';
if ( ! empty( $trs_checkins->apikey ) ) {
	$verify_btn_style = '';
}

$placetype_settings_style = 'display: none;';
if ( 'placetype' === $trs_checkins->checkin_by ) {
	$placetype_settings_style = '';
}
$place_types = array(
	'Accounting',
	'Airport',
	'Amusement Park',
	'Aquarium',
	'Art Gallery',
	'ATM',
	'Bakery',
	'Bank',
	'Bar',
	'Beauty Salon',
	'Bicycle Store',
	'Book Store',
	'Bowling Alley',
	'Bus Station',
	'Cafe',
	'Campground',
	'Car Dealer',
	'Car Rental',
	'Car Repair',
	'Car Wash',
	'Casino',
	'Cemetery',
	'Church',
	'City Hall',
	'Clothing Store',
	'Convenience Store',
	'Courthouse',
	'Dentist',
	'Department Store',
	'Doctor',
	'Electrician',
	'Electronics Store',
	'Embassy',
	'Fire Station',
	'Florist',
	'Funeral Home',
	'Furniture Home',
	'Gas Station',
	'Gym',
	'Hair Care',
	'Hardware Store',
	'Hindu Temple',
	'Home Goods Store',
	'Hospital',
	'Insurance Agency',
	'Jewelery Store',
	'Laundry',
	'Lawyer',
	'Library',
	'Liquor Store',
	'Local Government Office',
	'Locksmith',
	'Lodging',
	'Meal Delivery',
	'Meal Takeaway',
	'Mosque',
	'Movie Rental',
	'Movie Theatre',
	'Moving Company',
	'Museum',
	'Night Club',
	'Painter',
	'Park',
	'Parking',
	'Pet Store',
	'Pharmacy',
	'Physiotherapist',
	'Plumber',
	'Police',
	'Post Office',
	'Real Estate Agency',
	'Restaurant',
	'Roofing Contractor',
	'RV Park',
	'School',
	'Shoe Store',
	'Shopping Mall',
	'SPA',
	'Stadium',
	'Storage',
	'Store',
	'Subway Station',
	'Synagogue',
	'Taxi Stand',
	'Train Station',
	'Transit Station',
	'Travel Agency',
	'University',
	'Veterinary Care',
	'Zoo',
);
?>

<table class="form-table trschk-admin-page-table">
	<tbody>
		<!-- API Key -->
		<tr>
			<th scope="row"><label for="api-key"><?php esc_html_e( 'API Key', 'trs-checkins' ); ?></label></th>
			<td>
				<input class="regular-text" type="text" value="<?php echo esc_attr( $trs_checkins->apikey ); ?>" name="trschk-api-key" id="trschk-api-key" placeholder="<?php esc_html_e( 'API Key', 'trs-checkins' ); ?>" required>
				<button type="button" class="button button-secondary" style="<?php echo esc_attr( $verify_btn_style ); ?>" id="trschk-verify-apikey"><?php esc_html_e( 'Verify', 'trs-checkins' ); ?></button>
				<p class="description"><?php esc_html_e( "Due to changes in Google Maps API it's required to use an API key for the trendr Check-ins plugin to work properly. You can get the API key", 'trs-checkins' ); ?>&nbsp;<a target="blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key"><?php esc_html_e( 'here.', 'trs-checkins' ); ?></a>&nbsp;
					<a href="javascript:void(0);" onClick="window.open('https://wbcomdesigns.com/helpdesk/knowledge-base/get-google-api-key/','pagename','resizable,height=600,width=700'); return false;">
							<?php esc_html_e( '( How to Get Google API Key? )', 'trs-checkins' ); ?>
					</a>
				</p>
			</td>
		</tr>

		<?php if ( $trs_checkins->apikey ) { ?>
			<!-- Checkin By - autocomplete or placetype -->
			<tr>
				<th scope="row"><label for="checkin-by"><?php esc_html_e( 'Check-in by', 'trs-checkins' ); ?></label></th>
				<td>
					<p>
						<input <?php echo ( 'autocomplete' === $trs_checkins->checkin_by ) ? 'checked' : ''; ?> required type="radio" value="autocomplete" name="trschk-checkin-by" class="trschk-checkin-by" id="trschk-checkin-by-autocomplete" checked="checked">
						<label for="trschk-checkin-by-autocomplete"><?php esc_html_e( 'Location Autocomplete', 'trs-checkins' ); ?></label>
					</p>
					<p>
						<input <?php echo ( 'placetype' === $trs_checkins->checkin_by ) ? 'checked' : ''; ?> required type="radio" value="placetype" name="trschk-checkin-by" class="trschk-checkin-by" id="trschk-checkin-by-placetype">
						<label for="trschk-checkin-by-placetype"><?php esc_html_e( 'Place Types', 'trs-checkins' ); ?></label>
					</p>
					<p class="description"><?php esc_html_e( 'This setting will help the users check-in by autocomplete or place type google features.', 'trs-checkins' ); ?></p>
				</td>
			</tr>
		<?php } ?>

		<!-- Settings for place types -->
		<tr style="<?php echo esc_attr( $placetype_settings_style ); ?>" class="trschk-placetype-settings-row">
			<th scope="row"><?php esc_html_e( 'Range', 'trs-checkins' ); ?></th>
			<td>
				<input type="hidden" value="<?php echo esc_attr( $saved_range ); ?>" id="hidden_range" />
				<input value="<?php echo esc_attr( $saved_range ); ?>" id="trschk-google-places-range" type="range" name="trschk-google-places-range" min="1" max="10">
				<span id="range_disp">
				<?php
				if ( $saved_range ) {
					echo esc_attr( $saved_range . ' kms.' );}
?>
</span>
				<p class="description"><?php esc_html_e( 'This will set the range for fetching the places while check-in.', 'trs-checkins' ); ?></p>
			</td>
		</tr>

		<!-- Settings for place types -->
		<tr style="<?php echo esc_attr( $placetype_settings_style ); ?>" class="trschk-placetype-settings-row">
			<th scope="row"><?php esc_html_e( 'Place Types', 'trs-checkins' ); ?></th>
			<td>
				<p class="trschk-selection-tags">
					<a href="javascript:void(0);" id="trschk-select-all-place-types"><?php esc_html_e( 'Select All', 'trs-checkins' ); ?></a> /
					<a href="javascript:void(0);" id="trschk-unselect-all-place-types"><?php esc_html_e( 'Unselect All', 'trs-checkins' ); ?></a>
				</p>
				<select name="trschk-google-place-types[]" id="trschk-pre-place-types" multiple>
					<?php foreach ( $place_types as $place_type ) { ?>
						<?php $placetype_slug = str_replace( ' ', '_', strtolower( $place_type ) ); ?>
						<option value="<?php echo esc_attr( $placetype_slug ); ?>" <?php echo ( ! empty( $trs_checkins->place_types ) && in_array( $placetype_slug, $trs_checkins->place_types, true ) ) ? 'selected' : ''; ?>><?php echo esc_attr( $place_type ); ?></option>
					<?php } ?>
				</select>
				<p class="description"><?php esc_html_e( 'This will help in fetching the place types, that will be selected here.', 'trs-checkins' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>
<p class="submit">
	<?php submit_button( 'Save Changes', 'primary', 'trschk-submit-general-settings' ); ?>
</p>
