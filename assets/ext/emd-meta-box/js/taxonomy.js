jQuery( document ).ready( function( $ )
{
	$( '.emd-mb-taxonomy-tree input:checkbox' ).change( function()
	{
		var $this = $( this ),
			$childList = $this.parent().siblings( '.emd-mb-taxonomy-tree' );
		if ( $this.is( ':checked' ) )
		{
			$childList.removeClass( 'hidden' );
		}
		else
		{
			$childList.find( 'input' ).removeAttr( 'checked' );
			$childList.addClass( 'hidden' );
		}
	} );

	$( '.emd-mb-taxonomy-tree select' ).change( function()
	{
		var $this = $( this ),
			$childList = $this.siblings( '.emd-mb-taxonomy-tree' ),
			$value = $this.val();
		$childList.removeClass( 'active' ).addClass( 'disabled' ).find( 'select' ).each( function()
		{
			$( this ).val( $( 'options:first', this ).val() ).attr( 'disabled', 'disabled' );
		} );
		$childList.filter( '.emd-mb-taxonomy-' + $value ).removeClass( 'disabled' ).addClass( 'active' ).children( 'select' ).removeAttr( 'disabled' );
	} );
} );
