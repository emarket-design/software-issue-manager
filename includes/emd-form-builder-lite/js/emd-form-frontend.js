jQuery(document).ready(function($){
	$.fn.showLocalStor = function (){
		$.each($('.emd-form-control,.emd-form-check-input'), function() {
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
	$.fn.verifyRegistration = function (el,event){
		$.ajax({
			type: 'POST',
			url:emd_form_vars.ajax_url ,
			data: {action:'emd_lite_verify_registration',reg_username:el.val(),nonce:emd_form_vars.nonce},
			cache: false,
			async: false,
			success: function(resp) {
				if(!resp.success){
					el.addClass('text-danger');
					if(!el.closest('.emd-form-field').find('label.text-danger').length > 0){
						$('<label class="text-danger">'+resp.data.msg+'</label>').insertAfter(el.parent());
					}
					else if(!el.closest('.emd-form-field').find('label.text-danger').html()){
						el.closest('.emd-form-field').find('label.text-danger').html(resp.data.msg);
						el.closest('.emd-form-field').find('label.text-danger').show();
					}
					if(event){
						event.preventDefault();
						return false;
					}
				}
				else {
					el.removeClass('text-danger');
					el.closest('.emd-form-field').find('label.text-danger').remove();
					if(event){
						$.fn.checkPassword($('#login_box_reg_confirm_password'),event);
					}
				}
			}
		});
	}
	$.fn.checkPassword = function (el,event){
		if(el.val() != $('#login_box_reg_password').val()){
			el.addClass('text-danger');
			if(!el.closest('.emd-form-field').find('label.text-danger').length > 0){
				$('<label class="text-danger">'+emd_form_vars.validate_msg.passw+'</label>').insertAfter(el.parent());
			}
			else if(!el.closest('.emd-form-field').find('label.text-danger').html()){
				el.closest('.emd-form-field').find('label.text-danger').html(emd_form_vars.validate_msg.passw);
				el.closest('.emd-form-field').find('label.text-danger').show();
			}
			if(event){
				event.preventDefault();
				return false;
			}
		}
		else {
			el.removeClass('text-danger');
			el.closest('.emd-form-field').find('label.text-danger').remove();
			if(event){
				$.fn.submitEmdForm(emd_form_vars.form_steps);
			}
		}
	}
	$.fn.submitEmdForm = function (form_steps){
		last_step = 1;	
		$.each(form_steps, function (ind, val){
			if(val.end > last_step){
				last_step = val.end;
			}
		});
		submitted_form = $('.emd-form-container:last #form_name_'+last_step).val();
		//submit data
		nonce = $('.emd-form-container:last #'+submitted_form+'_nonce').val();
		form_data = [];
		for (i = form_steps[submitted_form]['beg']; i <= form_steps[submitted_form]['end']; i++) { 
			form_data = $.merge(form_data,$('#step-'+i+' :input').serializeArray());
		}
		form_data.push({name:'save_end',value:1});
		form_data.push({name:'end_form',value:1});
		$.ajax({
			type: 'POST',
			url:emd_form_vars.ajax_url ,
			data: {action:'emd_formb_lite_submit_ajax_form',form_data:form_data,nonce:emd_form_vars.nonce},
			cache: false,
			async: false,
			success: function(resp) {
				if(resp.success){
					$('.emd-form-success-error').html(resp.data.msg);
					$('.emd-form-success-error').show();
					new_pos = $('.emd-form-success-error').offset();
					window.scrollTo(new_pos.left,new_pos.top);
					$('.emd-form-container:last').closest('#emd-wizard').hide();
					localStorage.clear();
				}
				else {
					$('.emd-form-success-error').html(resp.data.msg);
					$('.emd-form-success-error').show();
					new_pos = $('.emd-form-success-error').offset();
					window.scrollTo(new_pos.left,new_pos.top);
				}
			}
		});
	}
	//fields
	if($('.emd-file').length > 0){
		$.each($('.emd-file'), function( ind, val ) {
			$(val).filepicker(emd_form_vars[$(val).attr('id')]);
		});
	}
	if(emd_form_vars.incl_select2 && $('.emd-select').length > 0){
		$.each($('.emd-select'), function( ind, val ) {
			$(val).select2({placeholder: $(val).attr('placeholder')});
			$(val).parent().find('.select2-selection').addClass(emd_form_vars.element_size);
		});
	}
	if($('.emd-datetime').length > 0){
		$.each($('.emd-datetime'), function( ind, val ) {
			if(emd_form_vars.locale == 'en_US'){
				$(val).flatpickr({
					enableTime: true,
					'dateFormat': $(val).data('format'),
				});
			}
			else if(emd_form_vars.locale.match(/^en_/)){
				$(val).flatpickr({
					enableTime: true,
					'dateFormat': $(val).data('format'),
					'time_24hr': true,
				});
			}
			else {
				$(val).flatpickr({
					enableTime: true,
					'dateFormat': $(val).data('format'),
					'locale' : emd_form_vars.locale,
					'time_24hr': true,
				});
			}
		});
	}
	if($('.emd-date').length > 0){
		$.each($('.emd-date'), function( ind, val ) {
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


	//hide if any rel is set with shortcode
	$('.emd-hide-form-rel').closest('.emd-row').hide();


	/*$.validator.setDefaults({
	    //ignore: [],
	});*/
	$.extend($.validator.messages,emd_form_vars.validate_msg);

	$.validator.addMethod('uniqueAttr',function(val,element){
	  var unique = true;
	  if(val){
		  var ptype = $(element).closest(".emd-form-container").find('input[name="emd_ent"]').val();
		  var myapp = $(element).closest(".emd-form-container").find('input[name="emd_app"]').val();
		  var data_input = {};
		  data_input[$(element).attr('name')] = $(element).val();
		  var data_input = $(element).closest(".emd-form-container").serialize();
		  $.ajax({
		    type: 'GET',
		    url: emd_form_vars.ajax_url,
		    cache: false,
		    async: false,
		    data: {action:'emd_check_unique',data_input:data_input, ptype:ptype,myapp:myapp,nonce:emd_form_vars.nonce},
		    success: function(response)
		    {
		      unique = response;
		    },
		  });
	  }
	  return unique;
	}, emd_form_vars.unique_msg);

	$.validator.addMethod('userEmail',function(val,element){
	  var ptype = $(element).closest(".emd-form-container").find('input[name="emd_ent"]').val();
	  var myapp = $(element).closest(".emd-form-container").find('input[name="emd_app"]').val();
	  var user_email = true;
	  var email_key = $(element).attr('name');
          var email_val = $(element).val();
	  var data_input = $(element).closest(".emd-form-container").serialize();
	  $.ajax({
	    type: 'GET',
	    url: emd_form_vars.ajax_url,
	    cache: false,
	    async: false,
	    data: {action:'emd_check_userEmail',email_key:email_key,email_val:email_val,ptype:ptype,myapp:myapp,nonce:emd_form_vars.nonce},
	    success: function(response)
	    {
	      user_email = response;
	    },
	  });
	  return user_email;
	}, emd_form_vars.user_email_msg);


	/*$.validator.addClassRules('required', {
		required: true 
	});*/
	$.validator.addClassRules('uniqueattr', {
		uniqueAttr: true 
	});
	$.validator.addClassRules('user_email_key', {
		userEmail: true 
	});
	/*$.validator.addClassRules('postalCodeCA', {
		postalCodeCA: true 
	});*/
	
	validation_fields = ['postalCodeCA','mobileUK','ipv6','ipv4','vinUS','integer','postcodeUK','zipcodeUS','nowhitespace','lettersonly','alphanumeric','letterswithbasicpunc','phoneUK','phoneUS','creditcard','digits','number','url','email'];
	$.each(validation_fields, function(index,value) {
		$.validator.addClassRules("'"+value+"'", {
			value: true 
		});
	});
	if($('.minlength').length > 0){
		$.each($('.minlength'), function( ind, val ) {
			$.validator.addClassRules('minlength', {
				minlength: {
					param: $(val).data('minlength')
				}
			});
		});
	}
	if($('.maxlength').length > 0){
		$.each($('.maxlength'), function( ind, val ) {
			$.validator.addClassRules('maxlength', {
				maxlength: {
					param: $(val).data('maxlength')
				}
			});
		});
	}
	if($('.min').length > 0){
		$.each($('.min'), function( ind, val ) {
			$.validator.addClassRules('min', {
				min: {
					param: $(val).data('min')
				}
			});
		});
	}
	if($('.max').length > 0){
		$.each($('.max'), function( ind, val ) {
			$.validator.addClassRules('max', {
				max: {
					param: $(val).data('max')
				}
			});
		});
	}
	if($('.minWords').length > 0){
		$.each($('.minWords'), function( ind, val ) {
			$.validator.addClassRules('minWords', {
				minWords: {
					param: $(val).data('minWords')
				}
			});
		});
	}
	if($('.maxWords').length > 0){
		$.each($('.maxWords'), function( ind, val ) {
			$.validator.addClassRules('maxWords', {
				maxWords: {
					param: $(val).data('maxWords')
				}
			});
		});
	}
	
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
		label.parent().find('.emd-form-group').removeClass('required');
		label.remove();
	},
	errorPlacement: function(error, element) {
	$('.form-alerts').hide();
	if(element.closest('.emd-form-group').is(":hidden")){
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
	else if(element.attr("class").search("selectpicker") != -1 && element.parent().parent().attr("class").search("emd-form-group") == -1){
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
	else if(element.attr("class").search("emd-form-check-input") != -1){
		element.parent().parent().addClass('required');
		error.insertAfter(element.closest('.emd-form-group'));
		error.addClass('check-radio');
	}
	else {
		error.insertAfter(element.parent());
	}
	},
	});
	}); //end of each emd-form-container
	$.fn.showLocalStor();
	if(emd_form_vars.conditional_rules[1] && emd_form_vars.conditional_rules[1].length != 0){
		$.fn.conditionalCheck(emd_form_vars.conditional_rules[1]);
	}

	//var fname = $('.emd-form-container').find('input[name="form_name"]').val();
	if(emd_form_vars.wizard_save_step ||  emd_form_vars.form_steps[emd_form_vars.fname].end > 1){
		$(document).on('click','.emd-form-submit',function(event){
			var valid = $('.emd-form-container:last').valid();
			if(!valid) {
				event.preventDefault();
				return false;
			}
			//see if registration fields
			if($('#login_box_reg_username').val()){
				$.fn.verifyRegistration($('#login_box_reg_username'),event);
			}
			else {
				$.fn.submitEmdForm(emd_form_vars.form_steps);
			}
		});
	}
	else if(emd_form_vars.enable_ajax){
		$(document).on('click','.emd-form-submit',function(event){
			notvalid = 0;
			var valid = $(this).closest('.emd-form-container').valid();
			$.each(emd_form_vars.req[1], function (ind, val){
				 if(!$('input[name="'+val+'"],#'+ val+',input[name="'+val+'[]"]').closest('.emd-form-group').is(":hidden")){
				     $('input[name="'+val+'"],#'+ val+',input[name="'+val+'[]"]').rules("add","required");
				     if($('#'+val).hasClass('emd-sumnote') && $('#'+val).val().length == 0){
					$('#'+val).parent().find('.note-toolbar').addClass('text-danger');
					$('<label class="text-danger">'+emd_form_vars.validate_msg.required+'</label>').insertAfter($('#'+val).parent());
					notvalid = 1;
				     }
				     else {
					$('#'+val).parent().find('.note-toolbar').removeClass('text-danger');
				     }
				 }
				 else {
				     $('input[name="'+val+'"],#'+ val+',input[name="'+val+'[]"]').rules("remove","required");
				}
			});
			if(!valid || notvalid == 1){
				event.preventDefault();
				return false;
			}
			if(emd_form_vars.disable_submit){
				event.preventDefault();
				return false;
			}
			sform =  $(this).closest('.emd-form-container');	
			form_div = $(this).closest('.emd-form');
			$(this).prop('disabled', true);
			event.preventDefault();
			form_data = sform.find(':input').serializeArray();
			$.ajax({
				type: 'POST',
				url:emd_form_vars.ajax_url ,
				data: {action:'emd_formb_lite_submit_ajax_form',form_data:form_data,nonce:emd_form_vars.nonce},
				success: function(resp) {
					if(resp.data.status == 'redirect'){
						window.location.href = resp.data.link;
					}
					else if(resp.success){
						form_div.find('.emd-form-success-error').html(resp.data.msg);
						form_div.find('.emd-form-success-error').show();
						if(sform.closest('.modal').length > 0){
							mymodal = sform.closest('.modal');
							setTimeout(function() {
								mymodal.modal('hide');
							}, 2000);
							mymodal.parent().find('button').hide();
						}
						new_pos = form_div.find('.emd-form-success-error').parent().parent().offset();
						window.scrollTo(new_pos.left,new_pos.top);
						if(emd_form_vars.after_submit == 'hide'){
							sform.hide();
						}
						localStorage.clear();
					}
					else {
						form_div.find('.emd-form-success-error').html(resp.data.msg);
						form_div.find('.emd-form-success-error').show();
						new_pos = form_div.find('.emd-form-success-error').parent().parent().offset();
						window.scrollTo(new_pos.left,new_pos.top);
					}
				}
			});
		});
	}
	else {
		$(document).on('click','.emd-form-submit',function(event){
			notvalid = 0;
			var valid = $('.emd-form-container:last').valid();
			$.each(emd_form_vars.req[1], function (ind, val){
				 if(!$('input[name="'+val+'"],#'+ val+',input[name="'+val+'[]"]').closest('.emd-form-group').is(":hidden")){
				     $('input[name="'+val+'"],#'+ val+',input[name="'+val+'[]"]').rules("add","required");
				     if($('#'+val).hasClass('emd-sumnote') && $('#'+val).val().length == 0){
					$('#'+val).parent().find('.note-toolbar').addClass('text-danger');
					$('<label class="text-danger">'+emd_form_vars.validate_msg.required+'</label>').insertAfter($('#'+val).parent());
					notvalid = 1;
				     }
				     else {
					$('#'+val).parent().find('.note-toolbar').removeClass('text-danger');
				     }
				 }
				 else {
				     $('input[name="'+val+'"],#'+ val+',input[name="'+val+'[]"]').rules("remove","required");
				}
			});
			if(!valid || notvalid == 1) {
				event.preventDefault();
				return false;
			}
			if(emd_form_vars.disable_submit){
				event.preventDefault();
				return false;
			}
			//$(this).prop('disabled', true);
			$('.emd-form-container:last').submit();
			//localStorage.clear();
		});
	}
	if(emd_form_vars.disable_submit){
		$(document).on('click','.emd-form-submit',function(event){
			var valid = $('.emd-form-container').valid();
			if(!valid) {
				event.preventDefault();
				return false;
			}
			event.preventDefault();
		});
	}
	if(emd_form_vars.has_paging){
		var stepnum = 1;
		var went_back = 0;
		theme = 'default';
		if(emd_form_vars.wizard){
			theme = emd_form_vars.wizard;
		}
		toolbar = 'bottom';
		if(emd_form_vars.wizard_toolbar){
			toolbar = emd_form_vars.wizard_toolbar;
		}
		$('#emd-wizard').emdWizard({theme: theme,
			toolbarSettings: {toolbarPosition: toolbar},
			transitionEffect: emd_form_vars.wizard_effect,
			transitionSpeed: emd_form_vars.wizard_speed,
			keyNavigation: false
		});
		if(emd_form_vars.button_size){
			$('.emd-btn-prev').addClass(emd_form_vars.button_size);
			$('.emd-btn-next').addClass(emd_form_vars.button_size);
			$('.emd-wizard-cancel').addClass(emd_form_vars.button_size);
		}

		$(document).on('click','.emd-wizard-cancel',function(event){
			if(emd_form_vars.wizard_cancel){
				window.location.href = emd_form_vars.wizard_cancel;
			}
			else {
				$('#emd-wizard').emdWizard("reset");
				$('input[type=radio]').prop('checked',false);
				went_back = 1;
			}
		});

		$("#emd-wizard").on("leaveStep", function(e, anchorObject, stepNumber, stepDirection) {
			if(stepDirection === 'forward' && (stepnum != stepNumber || went_back == 1)){
				went_back = 0;
				pagenum = stepNumber + 1;
				notvalid = 0;
				submitted_form = $('#step-'+pagenum+' #form_name_'+pagenum).val();
				$.each(emd_form_vars.req[pagenum], function (ind, val){
					 if(!$('input[name="'+val+'"],#'+ val+',input[name="'+val+'[]"]').closest('.emd-form-group').is(":hidden")){
					     $('input[name="'+val+'"],#'+ val+',input[name="'+val+'[]"]').rules("add","required");
					     if($('#'+val).hasClass('emd-sumnote') && $('#'+val).val().length == 0){
						$('#'+val).parent().find('.note-toolbar').addClass('text-danger');
						$('<label class="text-danger">'+emd_form_vars.validate_msg.required+'</label>').insertAfter($('#'+val).parent());
						notvalid = 1;
					     }
					 }
					 else {
					     $('input[name="'+val+'"],#'+ val+',input[name="'+val+'[]"]').rules("remove","required");
					}
				});
				if(!$("#"+submitted_form+"_"+pagenum).valid()){
					notvalid = 1;
				}
				if(notvalid == 1){
					e.preventDefault();
					return false;
				}
				stepnum = stepNumber;
				if(emd_form_vars.form_steps[submitted_form]['end'] == pagenum || emd_form_vars.wizard_save_step){
					save_end = 0;
					//submit data
					nonce = $('#step-'+pagenum+' #'+$('#form_name_'+pagenum).val()+'_nonce').val();
					if(emd_form_vars.form_steps[submitted_form]['end'] == pagenum){
						form_data = [];
						for (i = emd_form_vars.form_steps[submitted_form]['beg']; i <= emd_form_vars.form_steps[submitted_form]['end']; i++) { 
							form_data = $.merge(form_data,$('#step-'+i+' :input').serializeArray());
						}
						form_data.push({name:'save_end',value:1});
						save_end = 1;
					}
					else if(emd_form_vars.wizard_save_step){	
						form_data = [];
						form_data = $.merge(form_data,$('#step-'+pagenum+' :input').serializeArray());
						form_data.push({name:'save_step',value:1});
					}
					e.preventDefault();
					$.ajax({
						type: 'POST',
						url:emd_form_vars.ajax_url ,
						data: {action:'emd_formb_lite_submit_ajax_form',form_data:form_data,nonce:emd_form_vars.nonce},
						success: function(resp) {
							if(resp.data.status == 'success'){
								if(save_end == 1 && resp.data.rel_id && resp.data.rel_val){
									$('#'+resp.data.rel_id).val(resp.data.rel_val);
								}
								else if(save_end == 1 && resp.data.rel_val && $('#emd_hidden_rel_val')){
									$('#emd_hidden_rel_val').val(resp.data.rel_val);
								}
								else if(save_end == 0 && resp.data.uniq_keys){
									$.each(resp.data.uniq_keys, function (ind, val){
										$("input[name="+ind+"]").val(val);
									});
								}
								$('#emd-wizard').emdWizard("next");
								//don't do anything
							}
							else if(resp.data.status == 'error'){
								$('#emd-wizard').emdWizard("reset");
								$('.emd-form-success-error').html(resp.data.msg);
								$('.emd-form-success-error').show();
								new_pos = $('.emd-form-success-error').offset();
								window.scrollTo(new_pos.left,new_pos.top);
							}	
							else if(resp.data.status == 'redirect'){
								window.location.href = resp.data.link;
							}
						}
					});
				}
			}
			else if(stepDirection === 'backward'){
				pagenum = stepNumber + 1;
				$('input[type=radio]').prop('checked',false);
				went_back = 1;
			}
		});
		$("#emd-wizard").on("showStep", function(e, anchorObject, stepNumber, stepDirection) {
			$.fn.showLocalStor();
			if(stepNumber == 0){
				$('.emd-btn-prev').hide();
			}
			else{
				$('.emd-btn-prev').show();
			}
			if(stepNumber == emd_form_vars.laststep){		
				$('.emd-btn-finish').addClass(emd_form_vars.finish_class);
				$('.emd-btn-finish').text(emd_form_vars.finish_label);
				$('.emd-btn-finish').attr('name',emd_form_vars.finish_name);
				finish_fa_class = 'fa fa-fw fas ';
				if(emd_form_vars.finish_fa_class){
					finish_fa_class = finish_fa_class + emd_form_vars.finish_fa_class;
				}
				if(emd_form_vars.finish_fa_size){
					finish_fa_class = finish_fa_class + ' ' + emd_form_vars.finish_fa_size;
				}
				if(emd_form_vars.finish_fa_class){	
					btnFinishIcon = $('<i></i>').addClass(finish_fa_class).attr('aria-hidden','true');
					if(emd_form_vars.finish_fa_pos == 'left'){
						$('.emd-btn-finish').prepend(btnFinishIcon);
					}
					else {
						$('.emd-btn-finish').append(btnFinishIcon);
					}
				}
				$('.emd-btn-finish').show();
				$('.emd-btn-next').hide();
			}else{
				$('.emd-btn-finish').hide();
				$('.emd-btn-next').show();
			}
			pagenum = stepNumber + 1;
			//if(stepDirection != 'backward'){
				if(emd_form_vars.conditional_rules[pagenum] && emd_form_vars.conditional_rules[pagenum].length != 0){
					$.fn.conditionalCheck(emd_form_vars.conditional_rules[pagenum]);
				}
			//}
		});
	}
	else {
		$.validator.addClassRules('required', {
			required: true 
		});
	}
	/*$.each($('.form-control,.form-check-input'), function() {
		input_name = $(this).attr('name');
		if (localStorage[input_name]) {
			if($(this).hasClass('emd-select')){
				$(this).val(localStorage[input_name]).trigger('change');
			}
			else if($(this).hasClass('radio')){
				$("input[name="+input_name+"][value=" + localStorage[input_name] + "]").attr('checked', 'checked');
			}
			else {
				$(this).val(localStorage[input_name]);
			}
		}
	});*/
	$('.emd-form-control,.emd-form-check-input').focus(function () {
                $(this).closest('.emd-form-row').find('label.text-danger').hide();
                $(this).removeClass('text-danger');
                $(this).closest('.emd-form-group').removeClass('required');
        });
	$('.emd-form-container :input').change(function () {
		if(!$(this).hasClass('emd-radio') && $(this).val() && $(this).attr('name') != 'login_box_password' && $(this).attr('name') != 'login_box_username'){
			localStorage[$(this).attr('name')] = $(this).val();
		}
	});
		
	$("#login_box_reg_username").on('change', function() {
		if($(this).val()){
			$.fn.verifyRegistration($(this));
		}
	});
	$("#login_box_reg_confirm_password").on('change', function() {
		$.fn.checkPassword($(this));
	});

	// Show the login form 
	$(document).on('click','.emd-login-box',function(event){
		event.preventDefault();
		$('.emd-form-row').hide();
		$('.emd-btn-toolbar').hide();
		$(this).closest('.emd-form-row').find('.emd-form-field.emd-login').show();	
		$(this).closest('.emd-form-row').show();	
		$(this).closest('.emd-form-row').addClass('loginbox');	
		$(this).closest('.emd-form-row').css('display', 'inline-block');	
		$('.emd-login-label').hide();
		$('.emd-login-button').show();
		$(this).closest('.emd-form-row').find('.emd-reg-label').show();
        });
	$(document).on('click','.emd-register-login',function(event){
		event.preventDefault();
		$('.emd-login-label').show();
		$('.emd-form-row').show();
		$('.emd-btn-toolbar').show();
		$(this).closest('.emd-form-row').removeClass('loginbox');	
		$(this).closest('.emd-form-row').find('.emd-form-field.emd-login').hide();	
		$(this).closest('.emd-form-row').find('.emd-form-field.emd-reg').show();	
		$('.emd-reg-label').hide();
		$('.emd-login-button').hide();
		$('.emd-reg-error').hide();
        });
	$(document).on('click','.emd-login-submit',function(event){
		event.preventDefault();
		redirect = '';
		if($('#emd_login_redirect').val()){
			redirect = $('#emd_login_redirect').val();
		}
		$.ajax({
			type: 'POST',
			url:emd_form_vars.ajax_url ,
			data: {action:'emd_lite_process_login',
				nonce: $('#emd_login_nonce').val(),
				emd_user_pass:$('#login_box_password').val(),
				emd_user_login:$('#login_box_username').val(),
				emd_login_entity:$('#emd_login_entity').val(),
				emd_login_user_attr:$('#emd_login_user_attr').val(),
				emd_hidden_rel:$('#emd_hidden_rel').val(),
				emd_hidden_rel_val:$('#emd_hidden_rel_val').val(),
				emd_login_redirect:redirect,
			},
			success: function(msg) {
				if(msg.success && msg.data.redirect){
					window.location.href = msg.data.redirect;
				}
				else if(!msg.success){
					$('.emd-reg-error').html(msg.data.error);
					$('.emd-reg-error').show();
				}
			}
		});
	});
});
