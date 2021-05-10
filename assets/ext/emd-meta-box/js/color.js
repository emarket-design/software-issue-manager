/**
 * Update color picker element
 * Used for static & dynamic added elements (when clone)
 */
jQuery( document ).ready( function( $ )
{	
	$( ':input.emd-mb-color' ).each( emd_mb_update_color_picker );
	$( '.emd-mb-input' ).on( 'clone', ':input.emd-mb-color', emd_mb_update_color_picker )
	.on( 'focus', '.emd-mb-color', function()
	{
		$( this ).siblings( '.emd-mb-color-picker' ).show();
		return false;
	} ).on( 'blur',  '.emd-mb-color', function()
	{
		$( this ).siblings( '.emd-mb-color-picker' ).hide();
		return false;
	} );
	
	function emd_mb_update_color_picker()
	{
		var $this = $( this ),
			$clone_container = $this.closest('.emd-mb-clone'),
			$color_picker = $this.siblings( '.emd-mb-color-picker' );
		
		// Make sure the value is displayed
		if ( !$this.val() )
			$this.val( '#' );
			
		if( typeof jQuery.wp === 'object' && typeof jQuery.wp.wpColorPicker === 'function' ){
			if( $clone_container.length > 0 )
			{
				$this.appendTo( $clone_container ).siblings( 'div.wp-picker-container' ).remove();
			}
        	$this.wpColorPicker();
		}
		else {
			//We use farbtastic if the WordPress color picker widget doesn't exist
			$color_picker.farbtastic( $this );			
		}			
	}

} );
