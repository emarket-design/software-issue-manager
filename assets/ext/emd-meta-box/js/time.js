/**
 * Update datetime picker element
 * Used for static & dynamic added elements (when clone)
 */
jQuery( document ).ready( function( $ )
{
	$( ':input.emd-mb-time' ).each( emd_mb_update_time_picker );
	$( '.emd-mb-input' ).on( 'clone', ':input.emd-mb-time', emd_mb_update_time_picker );
	
	function emd_mb_update_time_picker()
	{
		var $this = $( this ),
			options = $this.data( 'options' );
	
		$this.siblings( '.ui-datepicker-append' ).remove();         // Remove appended text
		$this.removeClass( 'hasDatepicker' ).attr( 'id', '' ).timepicker( options );
	
	}
} );
