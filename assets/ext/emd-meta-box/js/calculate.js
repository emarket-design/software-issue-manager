jQuery( document ).ready( function ( $ )
{
	var $form = $( '#post' );
	$form.calx({'ajaxUrl': ajaxurl + '?action='+app_name+'_emd_calc_formula'});
});
