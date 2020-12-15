jQuery( document ).ready(
	function($){
		'use strict';

		var trschk_post_types = $( '#trschk-pre-place-types' ).selectize(
			{
				placeholder		: "Select Place Types",
				plugins			: ['remove_button'],
			}
		);
		if (trschk_post_types[0]) {
			var plc_types_selectize = trschk_post_types[0].selectize;
		}
		// console.log(plc_types_selectize);
		// Select-Unselect all place types
		$( document ).on(
			'click', '#trschk-select-all-place-types', function(){
				var pt_names   = [], i;
				var pt_options = plc_types_selectize.options;
				for ( i in pt_options ) {
					pt_names.push( pt_options[i]['value'] );
				}
				plc_types_selectize.setValue( pt_names );
			}
		);
		$( document ).on(
			'click', '#trschk-unselect-all-place-types', function(){
				plc_types_selectize.setValue( [] );
			}
		);

		// Support tab
		var acc = document.getElementsByClassName( "trschk-accordion" );
		var i;
		for ( i = 0; i < acc.length; i++ ) {
			acc[i].onclick = function() {
				this.classList.toggle( "active" );
				var panel = this.nextElementSibling;
				if (panel.style.maxHeight) {
					panel.style.maxHeight = null;
				} else {
					panel.style.maxHeight = panel.scrollHeight + "px";
				}
			}
		}
		$( document ).on(
			'click', '.trschk-accordion', function(){
				return false;
			}
		);

		var latitude  = '';
		var longitude = '';
		trschk_get_current_geolocation();
		function trschk_get_current_geolocation() {
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition( showPosition );
			} else {
				console.log( 'Geolocation is not supported by your browser.' );
			}
		}
		function showPosition(position) {
			latitude  = position.coords.latitude;
			longitude = position.coords.longitude;
		}

		$( document ).on(
			'keyup', '#trschk-api-key', function(){
				var apikey = $( this ).val();
				if ( apikey != '' ) {
					$( '#trschk-verify-apikey' ).show();
				} else {
					$( '#trschk-verify-apikey' ).hide();
				}
			}
		);

		// Verify the api key
		$( document ).on(
			'click', '#trschk-verify-apikey', function(){
				var btn_val = $( this ).html();
				var apikey  = $( '#trschk-api-key' ).val();
				$( this ).html( btn_val + ' <i class="fa fa-refresh fa-spin"></i>' );

				var data = {
					'action'	: 'trschk_verify_apikey',
					'apikey'	: apikey,
					'latitude'	: latitude,
					'longitude'	: longitude
				}
				$.ajax(
					{
						dataType: "JSON",
						url: trschk_admin_js_obj.ajaxurl,
						type: 'POST',
						data: data,
						success: function( response ) {
							console.log( response['data']['message'] );
							if ( response['data']['message'] == 'not-verified' ) {
								$( '#trschk-verify-apikey' ).html( btn_val + ' <i class="fa fa-times"></i>' );
							} else {
								$( '#trschk-verify-apikey' ).html( btn_val + ' <i class="fa fa-check"></i>' );
							}
						},
					}
				);
			}
		);

		// Open the settings panel once checkinby placetype is selected
		$( document ).on(
			'click', '.trschk-checkin-by', function(){
				var checkin_by = $( this ).val();
				if ( checkin_by == 'placetype' ) {
					$( '.trschk-placetype-settings-row' ).show();
				} else {
					$( '.trschk-placetype-settings-row' ).hide();
				}
			}
		);

		// Update the range value when the slider changes
		$( document ).on(
			'change', '#trschk-google-places-range', function(){
				var val = $( this ).val();
				$( '#range_disp' ).html( val + ' kms.' );
				$( '#hidden_range' ).val( val );
			}
		);

		// Select all place types
		$( document ).on(
			'change', '#trschk-select-all-place-types', function(){
				if ( $( this ).is( ':checked' ) ) {
					$( '.google-place-types' ).prop( 'checked', true );
				} else {
					$( '.google-place-types' ).prop( 'checked', false );
				}
			}
		);
	}
);
