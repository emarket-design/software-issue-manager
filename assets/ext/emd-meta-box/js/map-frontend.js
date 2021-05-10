/* global google, jQuery */

jQuery( function ( $ )
{
	'use strict';

	/**
	 * Callback function for Google Maps Lazy Load library to display map
	 *
	 * @return void
	 */
	function displayMap()
	{
		var $container = $( this ),
			options = $container.data( 'map_options' );

		var mapOptions = options.js_options,
			center = new google.maps.LatLng( options.latitude, options.longitude ),
			map;

		switch ( mapOptions.mapTypeId )
		{
			case 'ROADMAP':
				mapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
				break;
			case 'SATELLITE':
				mapOptions.mapTypeId = google.maps.MapTypeId.SATELLITE;
				break;
			case 'HYBRID':
				mapOptions.mapTypeId = google.maps.MapTypeId.HYBRID;
				break;
			case 'TERRAIN':
				mapOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
				break;
		}
		mapOptions.center = center;
		map = new google.maps.Map( this, mapOptions );

		// Set marker
		if ( options.marker )
		{
			var marker = new google.maps.Marker( {
				position: center,
				map     : map
			} );

			// Set marker title
			if ( options.marker_title )
			{
				marker.setTitle( mapOptions.marker_title );
			}
		}

		// Set info window
		if ( options.info_window )
		{
			var infoWindow = new google.maps.InfoWindow( {
				content : options.info_window,
				minWidth: 200
			} );

			if( options.load_info) {
				infoWindow.open( map, marker );
			}

			google.maps.event.addListener( marker, 'click', function ()
			{
				infoWindow.open( map, marker );
			} );
		}
	}

	// Loop through all map instances and display them
	$( '.emd-mb-map-canvas' ).each( displayMap );
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                        $( '.emd-mb-map-canvas' ).each(displayMap);
        });
	$('.emd-bs-modal').on('shown.bs.modal', function () {
                $( '.emd-mb-map-canvas' ).each(displayMap);
	});
	$('.emd-modal').click(function(event){
                $( '.emd-mb-map-canvas' ).each(displayMap);
        });
} );
