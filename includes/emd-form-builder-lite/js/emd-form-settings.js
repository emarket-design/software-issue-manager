jQuery(document).ready(function($){
	$("[data-depend]").hide();
	$('#settings_result_templ').change(function(e){
		$("[data-depend]").hide();
		$("div[data-depend='" + $(this).attr('id')+"_"+$(this).val() + "']").show();
	});
	$("div[data-depend='settings_result_templ_" + $('#settings_result_templ').val() + "']").show();
	if($('#settings_result_templ').val() == 'adv_table'){
		if($('#settings_adv_show_all').prop('checked')){
			$("div[data-depend='settings_adv_show_all_0']").hide();
		}
		else {
			$("div[data-depend='settings_adv_show_all_0']").show();
		}
	}
	else if($('#settings_result_templ').val() == 'cust_table'){
		$("div[data-depend='settings_adv_show_all_0']").hide();
		$('#settings_result_fields').parent().hide();
	}
	else {
		$("div[data-depend='settings_adv_show_all_0']").hide();
	}
	$('#settings_adv_show_all').change(function(e){
		chk_val = 0;
		if(this.checked) {
			chk_val = 1;
		}
		if(chk_val == 0){
			$("div[data-depend='" + $(this).attr('id')+"_0']").show();
		}
		else {
			$("div[data-depend='" + $(this).attr('id')+"_0']").hide();
		}
	});

	$('#settings_confirm_method').change(function(e){
		$("[data-depend]").hide();
		$("div[data-depend='" + $(this).attr('id')+"_"+$(this).val() + "']").show();
	});
	$("div[data-depend='settings_confirm_method_" + $('#settings_confirm_method').val() + "']").show();
	$('#settings_schedule_start').datetimepicker({dateFormat:'yy-mm-dd',timeFormat:'hh:mm'})
	$('#settings_schedule_end').datetimepicker({dateFormat:'yy-mm-dd',timeFormat:'hh:mm'})
	$('#settings_disable_submit').change(function(e){
		chk_val = 0;
		if(this.checked) {
			chk_val = 1;
		}
		$("[data-depend]").show();
		$("div[data-depend='" + $(this).attr('id')+"_"+chk_val + "']").hide();
	});
	dis_submit_check_val = 1;
	if($('#settings_disable_submit').prop('checked')){
		dis_submit_check_val = 0;
	}
	$("div[data-depend='settings_disable_submit_" + dis_submit_check_val + "']").show();

	$('#settings_captcha').change(function(e){
		$("[data-depend]").hide();
		$("div[data-depend='" + $(this).attr('id')+"_"+$(this).val() + "']").show();
		$("div[data-depend2='" + $(this).attr('id')+"_"+$(this).val() + "']").show();
	});
	$("div[data-depend='settings_captcha_" + $('#settings_captcha').val() + "']").show();
	$("div[data-depend2='settings_captcha_" + $('#settings_captcha').val() + "']").show();

	$('#settings_wizard_save_step').change(function(e){
		chk_val = 0;
		if(this.checked) {
			chk_val = 1;
		}
		$("[data-depend]").hide();
		$("div[data-depend='" + $(this).attr('id')+"_"+chk_val + "']").show();
	});
	wizard_step_val = 0;
	if($('#settings_wizard_save_step').prop('checked')){
		wizard_step_val = 1;
	}
	$("div[data-depend='settings_wizard_save_step_" + wizard_step_val + "']").show();
	
	
	$('#settings_result_fields').select2();
	/*$("ul.select2-selection__rendered").sortable({
  		containment: 'parent',
	});*/
	$('#settings_result_fields').on('select2:select', function(e){
	      var id = e.params.data.id;
	      var option = $(e.target).children('[value='+id+']');
	      option.detach();
	      $(e.target).append(option).change();
	    });
	if(window.location.href.match(/tab=display|tab=submit|tab=schedule|tab=page|tab=code/)){	
		$('#submit').prop('disabled', true);
		$('#submit').removeClass('button-primary').addClass('button-secondary');
	}
});
