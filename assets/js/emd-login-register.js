jQuery(document).ready(function($){
	if(log_reg_vars['show'] == 'register'){
		$("#emd-register-container").show();
		$("#emd-login-container").hide();
	}
	else if(log_reg_vars['show'] == 'both'){
		$('p.emd-register-link').show();
	}
	else {
		$('p.emd-register-link').hide();
	}
	$("p.emd-register-link a").click(function(e){
		e.preventDefault();
		$("#emd-register-container").fadeIn(1000);
		$("#emd-login-container").fadeOut(1000);
	});
	$("p.emd-login-link a").click(function(e){
		e.preventDefault();
		$("#emd-register-container").fadeOut(1000);
		$("#emd-login-container").fadeIn(1000);
	});
	$.validator.addClassRules('verify_reg', {
                verifyReg: true
        });
	$.validator.addClassRules('check_email', {
                verifyEmail: true
        });
	$.validator.addClassRules('check_passw2', {
                verifyPass2: true
        });
	$.validator.addClassRules('check_passw', {
                verifyPass: true
        });
	$.validator.addMethod('verifyReg',function(val,element){
		var ret_val = true;
                $.ajax({
                        type: 'POST',
                        url:log_reg_vars.ajax_url,
                        data: {action:'emd_verify_registration',reg_username:val,nonce:log_reg_vars.nonce},
                        cache: false,
                        async: false,
                        success: function(resp) {
                                if(!resp.success){
					ret_val = false;
                                }
                        }
                });
		return ret_val;
        }, log_reg_vars.verify_msg);
	$.validator.addMethod('verifyEmail',function(val,element){
		var ret_val = true;
                $.ajax({
                        type: 'POST',
                        url:log_reg_vars.ajax_url,
                        data: {action:'emd_verify_email',email:val,nonce:log_reg_vars.nonce},
                        cache: false,
                        async: false,
                        success: function(resp) {
                                if(!resp.success){
					ret_val = false;
                                }
                        }
                });
		return ret_val;
        }, log_reg_vars.verify_email);
	$.validator.addMethod('verifyPass2',function(val,element){
		var ret_val = true;
		el = $(element);
		fpass = el.closest('.emd-register-form').find('#emd-user-pass').val();
		if(val != fpass){
			ret_val = false;
		}
		return ret_val;
        }, log_reg_vars.verify_pass2);
	$.validator.addMethod('verifyPass',function(val,element){
		var ret_val = true;
		if(val.length < 5){
			ret_val = false;
		}
		return ret_val;
        }, log_reg_vars.verify_pass);
	$('.emdloginreg-container').each(function() {
		$(this).validate({
			onfocusout: false,
			onkeyup: false,
			onclick: false,
			errorClass: 'text-danger',
			success: function(label) {
				label.parent().parent().removeClass('required');
				label.parent().find('.emd-form-group').removeClass('required');
				label.remove();
			},
			errorPlacement: function(error, element) {
				$('.form-alerts').hide();
				if(element.closest('.emd-form-group').is(":hidden")){
					return;
				}
				else {
					error.insertAfter(element.parent());
				}
			},
		});
        }); //end of each emd-form-container
});
