jQuery( function( $ )
{
	$( ':input.emd-mb-slider-value' ).each( emd_mb_update_slider );
	$( '.emd-mb-input' ).on( 'clone', ':input.emd-mb-slider-value', emd_mb_update_slider );

	function emd_mb_update_slider()
	{
		var $input = $( this ),
			$slider = $input.siblings( '.emd-mb-slider' ),
			$valueLabel = $slider.siblings( '.emd-mb-slider-value-label' ).find( 'span' ),
			value = $input.val(),
			options = $slider.data( 'options' );

		$slider.html( '' );

		if ( !value )
		{
			value = 0;
			$input.val( 0 );
			$valueLabel.text( '0' );
		}
		else
		{
			$valueLabel.text( value );
		}

		// Assign field value and callback function when slide
		options.value = value;
		options.slide = function( event, ui )
		{
			$input.val( ui.value );
			$valueLabel.text( ui.value );
		};

		$slider.slider( options );
	}
} );
