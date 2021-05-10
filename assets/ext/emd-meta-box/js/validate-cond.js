jQuery( document ).ready( function( $ )
{
	var $form = $( '#post' );

	// Required field styling
	$.each( emd_mb.validationOptions.rules, function( k, v )
	{
		if ( v['required'] )
		{
			//$( '#' + k ).parent().siblings( '.mb-label' ).addClass( 'required' ).append( '<span>*</span>' );
			$('input[name='+k+'],select[name='+k+'],textarea[name='+k+']').parents().find('label[for='+k+']').parent().addClass( 'required' ).append( '<span>*</span>' );
		}
	} );

	emd_mb.validationOptions.invalidHandler = function( form, validator )
	{
		// Re-enable the submit ( publish/update ) button and hide the ajax indicator
		$( '#publish' ).removeClass( 'button-primary-disabled' );
		$( '#ajax-loading' ).attr( 'style', '' );
		$form.siblings( '#message' ).remove();
		$form.before( '<div id="message" class="error"><p>' + emd_mb.summaryMessage + '</p></div>' );
	};
	
	//added for validation to work on select2 (required and selects)
	emd_mb.validationOptions.ignore= null;
	$.extend($.validator.messages,validate_msg);

	$form.validate(emd_mb.validationOptions );

} );
