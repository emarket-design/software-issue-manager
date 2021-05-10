jQuery( function( $ )
{
	$( 'body' ).on( 'change', '.emd-mb-image-select input', function()
	{
		var $this = $( this ),
			type = $this.attr( 'type' ),
			selected = $this.is( ':checked' ),
			$parent = $this.parent(),
			$others = $parent.siblings();
		if ( selected )
		{
			$parent.addClass( 'emd-mb-active' );
			type == 'radio' && $others.removeClass( 'emd-mb-active' );
		}
		else
		{
			$parent.removeClass( 'emd-mb-active' );
		}
	} );
	$( '.emd-mb-image-select input' ).trigger( 'change' );
} );
