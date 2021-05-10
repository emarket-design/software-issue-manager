/**
 * Update datetime picker element
 * Used for static & dynamic added elements (when clone)
 */
jQuery( document ).ready( function( $ )
{
	$( ':input.emd-mb-datetime' ).each( emd_mb_update_datetime_picker );
	$( '.emd-mb-input' ).on( 'clone', ':input.emd-mb-datetime', emd_mb_update_datetime_picker );
	
	function emd_mb_update_datetime_picker()
	{
		var $this = $( this ),
			options = $this.data( 'options' );
	
		$this.siblings( '.ui-datepicker-append' ).remove();         // Remove appended text
		$.datepicker.regional[dtvars.locale] = dtvars.date;
		$.timepicker.regional[dtvars.locale] = dtvars.time;	
		$.datepicker.setDefaults($.datepicker.regional[dtvars.locale]);
		$.timepicker.setDefaults($.timepicker.regional[dtvars.locale]);
		$this.removeClass( 'hasDatepicker' ).attr( 'id', '' ).datetimepicker( options );
	}
} );
