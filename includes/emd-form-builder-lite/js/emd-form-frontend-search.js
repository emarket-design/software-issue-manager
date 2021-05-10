jQuery(document).ready(function($){
	//$('.emd-form-search').prop('disabled', true);
	form_disabled = 1;
	//fields
	if(emd_form_vars.incl_select2 && $('.emd-select').length > 0){
		$.each($('.emd-select'), function( ind, val ) {
			$(val).select2({placeholder: $(val).attr('placeholder')});
			$(val).parent().find('.select2-selection').addClass(emd_form_vars.element_size);
		});
	}
	if($('.emd-datetime').length > 0){
		$.each($('.emd-datetime'), function( ind, val ) {
			if(emd_form_vars.locale != 'en_US'){
				$(val).flatpickr({
					enableTime: true,
					'dateFormat': $(val).data('format'),
					'locale' : emd_form_vars.locale,
				});
			}
			else {
				$(val).flatpickr({
					enableTime: true,
					'dateFormat': $(val).data('format'),
				});
			}
		});
	}
	if($('.emd-date').length > 0){
		$.each($('.emd-datetime'), function( ind, val ) {
			if(emd_form_vars.locale != 'en_US'){
				$(val).flatpickr({
					'dateFormat': $(val).data('format'),
					'locale' : emd_form_vars.locale,
				});
			}
			else {
				$(val).flatpickr({
					'dateFormat': $(val).data('format'),
				});
			}
		});
	}
	$.fn.Paging = function (form_name,beg){
		var page =1;
		if(beg != 1){
			$('form[id="'+form_name+'"]').fadeOut(1000);
		}
		$('#'+form_name+'_show_link a').click(function(){
			$('form[id="'+form_name+'"]').fadeIn(1000);
			$('#'+form_name+'_hide_link').show();
			$('#'+form_name+'_show_link').hide();
		});
		$('#'+form_name+'_hide_link a').click(function(){
			$('form[id="'+form_name+'"]').fadeOut(1000);
			$('#'+form_name+'_show_link').show();
			$('#'+form_name+'_hide_link').hide();
		});
		$('.pagination-bar a').click(function(){
			if($(this).hasClass('prev')){
				page --;
			}  
			else if($(this).hasClass('next')){
				page ++;
			}  
			else{  
				page = $(this).text();
			}
			var div_id = $(this).closest('.emd-view-results').attr('id');
			var entity = $('#emd_entity').val();
			var view = $('#emd_view').val();
			var app = $('#emd_app').val();
			load_posts(div_id,entity,view,form_name,app);
			return false;
		}); 
		var load_posts = function(div_id,entity,view,form,app){
			$.ajax({
				type: 'GET',
				url: emd_form_vars.ajax_url,
				cache: false,
				async: false,
				data: {action:'emd_form_builder_lite_pagenum',pageno: page,entity:entity,view:view,form:form,app:app,nonce:emd_form_vars.nonce},
				success: function(response)
				{
					$('#'+ div_id).html(response);
					if(emd_form_vars.result_templ == 'adv_table'){
						$.fn.showAdvTable();
					}
					$('.pagination-bar a').click(function(){
						if($(this).hasClass('prev')){
							page --;
						}
						else if($(this).hasClass('next')){
							page ++;
						}
						else{
							page = $(this).text();
						}
						var div_id = $(this).closest('.emd-view-results').attr('id');
						var entity = $('#emd_entity').val();
						var view = $('#emd_view').val();
						var app = $('#emd_app').val();
						load_posts(div_id,entity,view,form,app);
						return false;
					});
				},
			});
		}
	}

	$.fn.showLocalStor = function (){
		$.each($('.form-control,.form-check-input'), function() {
			input_name = $(this).attr('name');
			if (input_name && localStorage[input_name]) {
				if($(this).hasClass('emd-select')){
					$(this).val(localStorage[input_name]).trigger('change');
				}
				else if($(this).hasClass('emd-radio')){
					//do nothing 
					//$("input[name="+input_name+"][value=" + localStorage[input_name] + "]").attr('checked', 'checked');
				}
				else {
					$(this).val(localStorage[input_name]);
				}
			}
		});
	}
	$.fn.showAdvTable = function (){
		if($('.emd-table') != 'undefined'){
			$('.emd-table').bootstrapTable();
		}
		$('.emd-table-toolbar').find('li').click(function (e) {
			e.preventDefault();
			$(this).find('i').addClass('fa-check');
			$(this).siblings().find('i').removeClass('fa-check');
			$(this).closest('.emd-table-toolbar').find('.emd-table-export').text($(this).find('a').text());
			var strSelector = $( this ).data('type') == 'selected' ? 'tr.selected' : 'tr';
			$(this).closest('.emd-table-container').find('.emd-table').bootstrapTable( 'refreshOptions', {
				exportDataType: $(this).data('type'),
				exportOptions: {
					tbodySelector: strSelector
				}
			});
		});
	}
	if(emd_form_vars.display_records){
		form_div = $('.emd-form');
		var form_name = form_div.attr('id').replace('-search','');
		$('#'+form_name+'_show_link').hide();
		$('#'+form_name+'_hide_link').show();
		$('#'+form_name+'_show_link a').click(function(){
			$('form[id="'+form_name+'"]').fadeIn(1000);
			$('#'+form_name+'_hide_link').show();
			$('#'+form_name+'_show_link').hide();
		});
		$('#'+form_name+'_hide_link a').click(function(){
			$('form[id="'+form_name+'"]').fadeOut(1000);
			$('#'+form_name+'_show_link').show();
			$('#'+form_name+'_hide_link').hide();
		});
		if(emd_form_vars.enable_ajax){
			$.fn.Paging(form_name,1);
		}
	}
	$('.emd-country').change(function(){
		dep_state = $(this).data('dep-state');
		  $.ajax({
		    type: 'GET',
		    url: emd_form_vars.ajax_url,
		    cache: false,
		    async: false,
		    data: {action:'emd_get_ajax_states',country:$(this).val(),nonce:emd_form_vars.nonce},
		    success: function(response)
		    {
			    if(response.length > 0){
				$('#'+dep_state).val("").trigger("change");
				$('#'+dep_state).html(response);
				$('#'+dep_state).closest('.emd-row').show();
			    }
			    else{
				$('#'+dep_state).val("").trigger("change");
				$('#'+dep_state).closest('.emd-row').hide();
			    }
		    },
		  });
	});
	$('.emd-form-container :input').change(function () {
		if($(this).val()){
			$('.emd-form-search').prop('disabled', false);
		}
		localStorage[$(this).attr('name')] = $(this).val();
	});


	$.fn.showLocalStor();
	
	$('.emd-form-container').each(function() {
	$(this).validate({
	onfocusout: false,
	onkeyup: false,
	onclick: false,
	errorClass: 'text-danger',
	success: function(label) {
		label.parent().find('.select2-selection').removeClass('text-danger');
		label.parent().find('.note-toolbar').removeClass('text-danger');
		label.parent().parent().removeClass('required');
		label.parent().find('.form-group').removeClass('required');
		label.remove();
	},
	errorPlacement: function(error, element) {
	$('.form-alerts').hide();
	if(element.closest('.form-group').is(":hidden")){
		return;
	}
	if (typeof(element.parent().attr("class")) != "undefined" && element.parent().attr("class").search(/date|time/) != -1) {
		error.insertAfter(element.parent().parent());
	}
	else if(element.attr("class").search("emd-radio") != -1){
		error.insertAfter(element.parent().parent());
		element.parent().parent().addClass('required');
		error.addClass('check-radio');
	}
	else if(element.attr("class").search("select2-offscreen") != -1){
		error.insertAfter(element.parent().parent());
	}
	else if(element.attr("class").search("selectpicker") != -1 && element.parent().parent().attr("class").search("form-group") == -1){
		error.insertAfter(element.parent().find('.bootstrap-select').parent());
	} 
	else if(element.parent().parent().attr("class").search("pure-g") != -1){
		error.insertAfter(element);
	}
	else if(element.attr("class").search("emd-select") != -1){
		element.parent().find('.select2-selection').addClass('text-danger');
		error.insertAfter(element.parent().find('.select2-container'));
	}
	else if(element.attr("class").search("emd-sumnote") != -1){
		element.parent().find('.note-toolbar').addClass('text-danger');
		error.insertAfter(element.parent());
	}
	else if(element.attr("class").search("form-check-input") != -1){
		element.parent().parent().addClass('required');
		error.insertAfter(element.closest('.form-group'));
		error.addClass('check-radio');
	}
	else {
		error.insertAfter(element.parent());
	}
	},
	});
	}); //end of each emd-form-container



	if(emd_form_vars.enable_ajax){
		$(document).on('click','.emd-form-search',function(event){
			var valid = $('.emd-form-container:last').valid();
			if(!valid) {
				event.preventDefault();
				return false;
			}
			sform =  $(this).closest('.emd-form-container');	
			form_data = sform.find(':input').serializeArray();
			form_div = $(this).closest('.emd-form');
			event.preventDefault();
			$.ajax({
				type: 'POST',
				url:emd_form_vars.ajax_url ,
				data: {action:'emd_formb_lite_submit_ajax_form',form_data:form_data,nonce:emd_form_vars.nonce},
				success: function(resp) {
					if(resp.success){
						form_div.find('.emd-form-search-results').html(resp.data.msg);
						form_div.find('.emd-form-search-results').show();
						var form = form_div.attr('id').replace('-search','');
						if(emd_form_vars.display_records){
							$.fn.Paging(form,1);
						}
						else {
							$.fn.Paging(form,0);
						}
						if(emd_form_vars.after_submit == 'hide'){
							sform.hide();
						}
						if(emd_form_vars.result_templ == 'adv_table'){
							$.fn.showAdvTable();
						}
					}
				}
			});
		});
	}
	/*else {
		$(document).on('click','.emd-form-search',function(event){
				return false;
			}
			var valid = $('.emd-form-container:last').valid();
			if(!valid) {
				event.preventDefault();
				return false;
			}
			$('.emd-form-container:last').submit();
			//localStorage.clear();
		});
	}*/
});
