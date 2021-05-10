jQuery( function( $ )
{
	$( '.emd-mb-oembed-wrapper .spinner' ).hide();

	$( 'body' ).on( 'click', '.emd-mb-oembed-wrapper .show-embed', function() {
		var $this = $( this ),
			$spinner = $this.siblings( '.spinner' ),
			data = {
				action: 'emd_mb_get_embed',
				url: $this.siblings( 'input' ).val()
			};

		$spinner.show();
		$.post( ajaxurl, data, function( r )
		{
			$spinner.hide();
			$this.siblings( '.embed-code' ).html( r.data );
		}, 'json' );

		return false;
	} );
} );
