/**
 * Update select2
 * Used for static & dynamic added elements (when clone)
 */
jQuery( document ).ready( function ( $ )
{	
	$( ':input.emd-mb-select-advanced' ).each( emd_mb_update_select_advanced );
	$( '.emd-mb-input' ).on( 'clone', ':input.emd-mb-select-advanced', emd_mb_update_select_advanced );
	
	function emd_mb_update_select_advanced()
	{
		var $this = $( this ),
			options = $this.data( 'options' );
		$this.siblings('.select2-container').remove();
		$this.select2( options );	
	}
} );
