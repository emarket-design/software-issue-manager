jQuery(document).ready(function($){
	$.validator.addMethod('uniqueAttr',function(val,element){
		var data_input = {};
		$.each(unique_vars.keys,function(i,val){
			if(val == 'blt_title'){
				data_input[val] = $('#title').val();
			}
			else if($('#'+val).length){
				data_input[val] = $('#'+val).val();
			}	
			else {
				data_input[val] = $('[name='+val+']').val();
			}
			});
		data_input['post_ID'] = $('#post_ID').val();
		var unique = true;
		$.ajax({
			type: 'GET',
			url: ajaxurl,
			cache: false,
			async: false,
			data: {action:'emd_check_unique',data_input:data_input, ptype:pagenow,myapp:unique_vars.app_name,nonce:unique_vars.nonce},
			success: function(response){
			unique = response;
			},
		});
		return unique;
	}, unique_vars.msg);
	$('.emd-country select').change(function(){
		var state_id = $(this).attr('data-state');
		if(state_id){
			$.ajax({
				type: 'GET',
				url: ajaxurl,
				cache: false,
				async: false,
				data: {action:'emd_get_ajax_states',country:$(this).val()},
				success: function(response)
				{   
					if(response.length > 0){ 
						$('#'+state_id).val("").trigger("change");
						$('#'+state_id).html(response);
						$('#'+state_id).closest('.emd-mb-input').show();
						$("label[for='"+state_id+"']").closest('.emd-mb-label').show(); 
					}   
					else {
						$('#'+state_id).closest('.emd-mb-input').hide();
						$("label[for='"+state_id+"']").closest('.emd-mb-label').hide(); 
						$('#'+state_id).val("").trigger("change");
					}   
				},  
			}); 
		}
	});

	$('#publish').click(function(){
		var msg = [];
		if(unique_vars.req_blt_tax != undefined){
			$.each(unique_vars.req_blt_tax,function(i,val){
				switch(i) {
					case 'blt_title':
						var title = $('[id^="titlediv"]').find('#title');
						if(title.val().length < 1) {
							$('#title').addClass('error');
							msg.push(val.msg);
						}
						else {
							$('#title').removeClass('error');
						}
						break;
					case 'blt_content':
						if (typeof tinyMCE != "undefined" && tinyMCE.editors.content != null) {
							var content = tinyMCE.editors.content.getContent();
						}
						else {
							var content = $('[id^="wp-content-editor-container"]').find('#content').val();
						}
						if(content.length < 1){
							$('#wp-content-wrap').addClass('error');
							msg.push(val.msg);
						}
						else {
							$('#wp-content-wrap').removeClass('error');
						}
						break;
					case 'blt_excerpt':
						var excerpt = $('[id^="postexcerpt"]').find('#excerpt');
						if(excerpt.val().length < 1){
							$('#excerpt').addClass('error');
							msg.push(val.msg);
						}
						else {
							$('#excerpt').removeClass('error');
						}
						break;
					default:
						if(val.type == 'rel'){
							if($("#"+i+" input[name='p2p_connections[]']").length < 1){
								$('#'+i).css({'border-left':'4px solid #DD3D36'});
								msg.push(val.label);
							} else if($('#'+i).is(':hidden') != true){
								$('#'+i).attr('style','');
							}
						}
						else {
							//check if there is any conditional which hides this tax then don't do any required check
							if(val.hier == 0 && val.type == 'multi'){
								var tcount = $("#"+i+" ul.tagchecklist li").length;
								var txn_div = 'tagsdiv-'+i;
							}
							else if(val.type == 'single'){
								var tcount = $("input[name='radio_tax_input["+ i + "][]']:checked").length;
								if(val.hier == 0){
									var txn_div = 'radio-tagsdiv-'+i;
								}
								else {
									var txn_div = 'radio-'+i+'div';
								}
							}
							else {
								var tcount = $("input[name='tax_input[" + i + "][]']:checked").length;
								var txn_div = i +'div';
							}
							if(tcount < 1 && $('#'+txn_div).is(':hidden') != true){
								$('#'+txn_div).css({'border-left':'4px solid #DD3D36'});
								msg.push(val.label);
							}else if($('#'+txn_div).is(':hidden') != true){
								$('#'+txn_div).attr('style','');
							}
						}
						break;
				}
			});
		}
		if(msg.length > 0){
			$('#publish').removeClass('button-primary-disabled');
			$('#ajax-loading').attr( 'style','');
			$('#post').siblings('#message').remove();
			$('#post').before('<div id="message" class="error"><p>'+msg.join(', ')+  ' ' + unique_vars.reqtxt + '</p></div>');
			return false;
		}
		else {
			var data_input = {};
			check_uniq =0;
			$.each(unique_vars.keys,function(i,val){
				if(val == 'blt_title'){
					check_uniq = 1;
					data_input[val] = $('#title').val();
				}
			});
			if(unique_vars.keys.length == 1 && check_uniq == 1){
				data_input['post_ID'] = $('#post_ID').val();
				unique = 1;
				$.ajax({
					type: 'GET',
					url: ajaxurl,
					cache: false,
					async: false,
					data: {action:'emd_check_unique',data_input:data_input, ptype:pagenow,myapp:unique_vars.app_name,nonce:unique_vars.nonce},
					success: function(response){
						unique = response;
					},
				});
				if(unique != '1'){
					$('#publish').removeClass('button-primary-disabled');
					$('#ajax-loading').attr( 'style','');
					$('#post').siblings('#message').remove();
					$('#title').addClass('error');
					$('#post').before('<div id="message" class="error"><p>'+ unique_vars.msg + '</p></div>');
					return false;
				}
			}
		}
	});
});
