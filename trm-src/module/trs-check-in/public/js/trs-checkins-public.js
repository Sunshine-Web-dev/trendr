jQuery( document ).ready(
	function($){
		'use strict';

		var autocomplete1;
		var autocomplete2;
		function initialize() {
			if (navigator.geolocation) {

				var options = {
					enableHighAccuracy: true,
					timeout: 5000,
					maximumAge: 0
				};

				navigator.geolocation.getCurrentPosition( success, error,options );
			} else {
				x.innerHTML = "Geolocation is not supported by this browser."; }

			var loc_xprof = document.getElementById( trschk_public_js_obj.trschk_loc_xprof );
			if (loc_xprof) {
				var autocomplete3 = new google.maps.places.Autocomplete( loc_xprof );
				google.maps.event.addListener(
					autocomplete3, 'place_changed', function () {
						var place3     = autocomplete3.getPlace();
						var latitude3  = place3.geometry.location.lat();
						var longitude3 = place3.geometry.location.lng();
						trschk_loc_xprof_ajax_save( latitude3,longitude3 );
					}
				);
			}
		}
		function error(e) {

			console.log( "error code:" + e.code + 'message: ' + e.message );

		}
		function success(position) {
			var  lat = position.coords.latitude;
			var  lng = position.coords.longitude;

			var  myLocation = new google.maps.LatLng( lat, lng );

			var mapOptions = {
				center: new google.maps.LatLng( myLocation.lat(),myLocation.lng() ),
				zoom: 13,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			/*start google map api code*/
			if (document.getElementById( 'checkin-by-autocomplete-map' )) {
				var map = new google.maps.Map(
					document.getElementById( 'checkin-by-autocomplete-map' ),
					mapOptions
				);

				var marker = new google.maps.Marker(
					{
						position: myLocation,
						map: map,
						title:"you are here"
					}
				);

				// Create the search box and link it to the UI element.
				var input     = document.getElementById( 'trschk-autocomplete-place' );
				var searchBox = new google.maps.places.SearchBox( input );
				// map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
				// Bias the SearchBox results towards current map's vietrmort.
				map.addListener(
					'bounds_changed', function() {
						searchBox.setBounds( map.getBounds() );
					}
				);

				var markers = [];
				// Listen for the event fired when the user selects a prediction and retrieve
				// more details for that place.
				searchBox.addListener(
					'places_changed', function() {
						var places = searchBox.getPlaces();

						if (places.length == 0) {
							return;
						}

						// Clear out the old markers.
						markers.forEach(
							function(marker) {
								marker.setMap( null );
							}
						);
						markers = [];

						// For each place, get the icon, name and location.
						var bounds = new google.maps.LatLngBounds();
						places.forEach(
							function(place) {
								if ( ! place.geometry) {
									console.log( "Returned place contains no geometry" );
									return;
								}
								var icon = {
									url: place.icon,
									size: new google.maps.Size( 71, 71 ),
									origin: new google.maps.Point( 0, 0 ),
									anchor: new google.maps.Point( 17, 34 ),
									scaledSize: new google.maps.Size( 25, 25 )
								};

								// Create a marker for each place.
								markers.push(
									new google.maps.Marker(
										{
											map: map,
											icon: icon,
											title: place.name,
											position: place.geometry.location
										}
									)
								);

								if (place.geometry.vietrmort) {
									// Only geocodes have vietrmort.
									bounds.union( place.geometry.vietrmort );
								} else {
									bounds.extend( place.geometry.location );
								}

								var latitude1  = place.geometry.location.lat();
								var longitude1 = place.geometry.location.lng();
								$( '#trschk-checkin-place-lat' ).val( latitude1 ).trigger( 'change' );
								$( '#trschk-checkin-place-lng' ).val( longitude1 ).trigger( 'change' );
							}
						);
						map.fitBounds( bounds );
					}
				);
				/*end google map api code*/
			}
		}
		google.maps.event.addDomListener( window, 'load', initialize );

		function trschk_loc_xprof_ajax_save(latitude3,longitude3){

			var place = $( '#' + trschk_public_js_obj.trschk_loc_xprof ).val();

			var data = {
				'action'			: 'trschk_save_xprofile_location',
				'place'				: place,
				'latitude'			: latitude3,
				'longitude'			: longitude3
			}

			$.ajax(
				{
					dataType: "JSON",
					url: trschk_public_js_obj.ajaxurl,
					type: 'POST',
					data: data,
					success: function( response ) {

					},
				}
			);
		}

		// Open the tabs - my places
		

		// Send AJAX to save the temp location just as location changed during checkin by autocomplete
		$( document ).on(
			'change', '#trschk-checkin-place-lng', function() {
				$( '.trschk-place-loader' ).show();
				var latitude        = $( '#trschk-checkin-place-lat' ).val();
				var longitude       = $( '#trschk-checkin-place-lng' ).val();
				var place           = $( '#trschk-autocomplete-place' ).val();
				var add_as_my_place = 'no';
				if ( $( '#trschk-add-as-place' ).is( ':checked' ) ) {
					add_as_my_place = 'yes';
				}

				// $('#trschk-autocomplete-place').addClass('trschk-autocomplete-place');
				var data = {
					'action'			: 'trschk_save_temp_location',
					'place'				: place,
					'latitude'			: latitude,
					'longitude'			: longitude,
					'add_as_my_place'	: add_as_my_place
				}
				// console.log(data);
				$.ajax(
					{
						dataType: "JSON",
						url: trschk_public_js_obj.ajaxurl,
						type: 'POST',
						data: data,
						success: function( response ) {
							if ( response['data']['message'] == 'temp-locaition-saved' ) {

								$( '#trschk-add-as-place' ).attr( 'disabled', true );
								$( '.trschk-place-loader' ).hide();
								$( '#trschk-autocomplete-place' ).removeClass( 'trschk-autocomplete-place' );
							}
						},
					}
				);
			}
		);

		// Open the checkin panel when clicked
		$( document ).on(
			'click', '.trschk-allow-checkin', function(){
				$( '.trs-checkin-panel' ).slideToggle( 500 );
				window.onload = initialize();
			}
		);

		// Send an AJAX to fetch the places when checkin is to be done by placetype
		if ( trschk_public_js_obj.checkin_by == 'placetype' ) {
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
				var data  = {
					'action'			: 'trschk_fetch_places',
					'latitude'			: latitude,
					'longitude'			: longitude
				}
				$.post(
					trschk_public_js_obj.ajaxurl,data,function(response){

						var obj = JSON.parse( response );
						$( '.checkin-by-placetype' ).html( obj.html );
					}
				);
			}
		}

		$( document ).on(
			'click', '.trschk-select-place-to-checkin', function(){
				var clicked_event   = $( this );
				var place_reference = $( this ).data( 'place_reference' );
				var place_id        = $( this ).data( 'place_id' );
				var add_as_my_place = 'no';
				if ( $( '#trschk-add-as-place' ).is( ':checked' ) ) {
					add_as_my_place = 'yes';
				}

				clicked_event.html( '<span class="trschk-place-select-loader">Selecting location..<i class="fa fa-refresh fa-spin"></i></span>' );

				// $('.trschk-select-place-to-checkin').each(function(){
				// $(this).html('Select this location');
				// });
				// $('.trschk-select-place-to-checkin').not(this).each(function(){
				// $(this).html('Select this location');
				// });
				var data = {
					'action'			: 'trschk_select_place_to_checkin',
					'place_reference'	: place_reference,
					'place_id'			: place_id,
					'add_as_my_place'	: add_as_my_place
				}
				$.ajax(
					{
						dataType: "JSON",
						url: trschk_public_js_obj.ajaxurl,
						type: 'POST',
						data: data,
						success: function( response ) {
							$( '.trschk-select-place-to-checkin' ).html( 'Select' );
							clicked_event.html( 'Selected' );
							console.log( response['data']['message'] );
							$( '.trschk-single-location-added' ).html( response['data']['html'] );
							$( '.trschk-places-fetched, #trschk-add-as-place, #trschk-add-my-place-label' ).hide();
						},
					}
				);
			}
		);

		// Show the places panel
		$( document ).on(
			'click', '#trschk-show-places-panel', function(){
				$( '.trschk-places-fetched' ).slideToggle( 500 );
				 $('.trschk-places-fetched, #trschk-add-as-place, #trschk-add-my-place-label').show();
			}
		);

		 //Hide the places panel
		 $(document).on('click', '#trschk-hide-places-panel', function(){
		 $('.trschk-places-fetched').slideToggle(500);
		 $('.trschk-places-fetched, #trschk-add-as-place, #trschk-add-my-place-label').hide();
		 });
		// Cancel checkin  - the temporary location
		
	

		$( "#aw-whats-new-submit" ).click(
			function(){
				$( '.trschk-select-place-to-checkin' ).each(
					function(){
						$( this ).html( 'Select this location' );
					}
				);
				$( '.trschk-checkin-temp-location' ).remove();
				if ($( '.trs-checkin-panel' ).is( ':visible' )) {
					$( '.trs-checkin-panel' ).slideToggle( 500 );
				}
				if ($( '.trschk-places-fetched' ).is( ':visible' )) {
					$( '.trschk-places-fetched' ).slideToggle( 500 );
				}

			}
		);
	}
);
