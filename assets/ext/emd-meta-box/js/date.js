/**
 * Update date picker element
 * Used for static & dynamic added elements (when clone)
 */
jQuery( document ).ready( function( $ )
{
	$( ':input.emd-mb-date' ).each( emd_mb_update_date_picker );
	$( '.emd-mb-input' ).on( 'clone', ':input.emd-mb-date', emd_mb_update_date_picker );
	$(':input.emd-mb-date').change(function(){
                if($(this).attr('data-cell') != undefined){
                        var $form = $('#post');
                        $form.calx('setValue',$(this).attr('data-cell'),$(this).val());
                        $form.calx('calculate');
                }
        });
	
	function emd_mb_update_date_picker()
	{
		var $this = $( this ),
			options = $this.data( 'options' );
	
		$this.siblings( '.ui-datepicker-append' ).remove();         // Remove appended text
		$.datepicker.regional[vars.locale] = vars.date;
        	$.datepicker.setDefaults($.datepicker.regional[vars.locale]);
		$this.removeClass( 'hasDatepicker' ).attr( 'id', '' ).datepicker( options );
	}
} );
