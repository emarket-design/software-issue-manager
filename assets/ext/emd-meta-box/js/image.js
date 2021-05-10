jQuery( function( $ )
{
	// Reorder images
	$( '.emd-mb-images' ).each( function()
	{
		var $this = $( this ),
			data = {
				action  	: 'emd_mb_reorder_images',
				_ajax_nonce	: $this.data( 'reorder_nonce' ),
				post_id 	: $( '#post_ID' ).val(),
				field_id	: $this.data( 'field_id' )
			};
		$this.sortable( {
			placeholder: 'ui-state-highlight',
			items      : 'li',
			update     : function()
			{
				data.order = $this.sortable( 'serialize' );
				$.post( ajaxurl, data );
			}
		} );
	} );
} );
