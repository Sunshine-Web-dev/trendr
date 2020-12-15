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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $trs_checkins;
$apikey = $trs_checkins->apikey;

$trschk_fav_places = get_user_meta( trs_displayed_user_id(), 'trschk_fav_places', true );

if ( ! empty( $trschk_fav_places ) ) {
	$trschk_fav_places = array_reverse( $trschk_fav_places );
} else {
	$trschk_fav_places = $trschk_fav_places;
}

if ( $trschk_fav_places ) {
	?>
	<div id="accordion" class="trschk-fav-loc-map-container">
	<?php
	foreach ( $trschk_fav_places as $key => $fav_places ) {
		$map_url = 'https://www.google.com/maps/embed/v1/place?key=' . $apikey . '&q=' . $fav_places['formatted_address'];
?>

	<h3><?php echo esc_attr( $fav_places['place'] ); ?></h3>
	<div>
		<div class="trschk-fav-loc-map">
			<iframe frameborder="0" style="border:0" src="<?php echo esc_url( $map_url ); ?>" allowfullscreen></iframe>
		</div>
		<div class="trschk-fav-loc-map-details">
			<?php if ( $fav_places['formatted_address'] ) { ?>

				<div class="trschk-fav-loc-row">
					<label><?php esc_html_e( 'Address', 'trs-checkins' ); ?></label>
					<span><?php echo esc_attr( $fav_places['formatted_address'] ); ?></span>
				</div>

			<?php } ?>
			<?php if ( $fav_places['street'] ) { ?>

				<div class="trschk-fav-loc-row">
					<label><?php esc_html_e( 'Street', 'trs-checkins' ); ?></label>
					<span><?php echo esc_attr( $fav_places['street'] ); ?></span>
				</div>

			<?php } ?>
			<?php if ( $fav_places['postal_code'] ) { ?>

				<div class="trschk-fav-loc-row">
					<label><?php esc_html_e( 'Postal Code', 'trs-checkins' ); ?></label>
					<span><?php echo esc_attr( $fav_places['postal_code'] ); ?></span>
				</div>

			<?php } ?>
			<?php if ( $fav_places['city'] ) { ?>

				<div class="trschk-fav-loc-row">
					<label><?php esc_html_e( 'City', 'trs-checkins' ); ?></label>
					<span><?php echo esc_attr( $fav_places['city'] ); ?></span>
				</div>

			<?php } ?>
			<?php if ( $fav_places['visit_date'] ) { ?>

				<div class="trschk-fav-loc-row">
					<label><?php esc_html_e( 'Visited Date', 'trs-checkins' ); ?></label>
					<span><?php echo esc_attr( $fav_places['visit_date'] ); ?></span>
				</div>

			<?php } ?>
		</div>
		<div class="clear"></div>
	</div>
<?php
	}
	?>
	</div>
	<?php
}
