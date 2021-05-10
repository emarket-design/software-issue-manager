jQuery(document).ready(function($){
	$.fn.emdFormSortDrag = function() {	
		$('.emd-form-page-wrap').sortable({
			items  : '> .emd-form-row',
			axis   : 'y',
			delay  : 100,
			opacity: 0.75,
		});
		$('.emd-form-row-holder').sortable({
			items  : '> .emd-form-field',
			delay  : 100,
			axis   : 'y',
			opacity: 0.75,
			over: function(e, ui) {
				$(this).parent('.emd-form-row').addClass("dragging");
			},
			out: function(e, ui) {
				$(this).parent('.emd-form-row').removeClass("dragging");
			},
			receive: function(e, ui) {
				$el  = ui.helper,
				field = $el.attr('data-field');
				app = $(this).data('app');
				entity = $(this).data('entity');
				form_id = $(this).closest('form').data('id');
				form_wrap = $(this);
				page = $(this).parent('.emd-form-page-wrap').data('page');
				$(this).find('.emd-form-insert-row').remove();
				$(this).parent('.emd-form-row').removeClass('init');
				//call ajax to get correct div
				if(ui.item.hasClass('emdform-field-button')){
					$el.addClass('emd-form-field-drag-over emd-form-field-drag-pending').removeClass('emd-field-drag-out').css('width', '100%');
					$.ajax({
						type: 'POST',
						url: builder_vars.ajax_url,
						cache: false,
						async: false,
						data: {action:'emd_form_builder_lite_get_field',app:app,field:field,entity:entity,page:page,form_id:form_id,nonce:builder_vars.nonce},
						success: function(response){
							if(response.success){
								newfield = response.data.field;
								$el.replaceWith(newfield);
								ui.item.addClass('disabled').removeClass('ui-draggable ui-draggable-handle');
								ui.item.draggable({disabled: true});
								$.fn.emdFormDel();
								//jvscript to change the sizes in boxes
								/*new_field = ui.item.data('field');
								field_count = $(form_wrap).children('.emd-form-field').length;
								find_div = 0;
								find_total = 0;
								$(form_wrap).children('.emd-form-field').each(function(i,v) {
									if($(v).data('field') != new_field){	
										find_total = +find_total + +$(v).data('size');
									}
								});
								if(find_total >= 12){
									new_size = 12 / field_count;
								}
								$(form_wrap).children('.emd-form-field').each(function(i,v) {
									$(v).data('size',new_size);
									old_class = $(v).attr('class');
									new_class = old_class.replace(/emd-md-(\d+)/g,'emd-md-'+new_size);
									$(v).attr('class',new_class);
								});*/
							}
						}
					});
				}
				//jvscript to change the sizes in boxes
				/*else {
					new_field = ui.item.data('field');
					field_count = $(this).children('.emd-form-field').length;
					find_div = 0;
					find_total = 0;
					$(this).children('.emd-form-field').each(function(i,v) {
						if($(v).data('field') != new_field){	
							find_total = +find_total + +$(v).data('size');
						}
					});
					if(find_total >= 12){
						new_size = 12 / field_count;
					}
					$(this).children('.emd-form-field').each(function(i,v) {
						$(v).data('size',new_size);
						old_class = $(v).attr('class');
						new_class = old_class.replace(/emd-md-(\d+)/g,'emd-md-'+new_size);
						$(v).attr('class',new_class);
					});
				}*/
			},
		});
		$('.emd-form-field').draggable({
			connectToSortable: '.emd-form-row-holder',
			delay: 200,
			revert: 'invalid',
			cancel: false,
			scroll: false,
			opacity: 0.75,
			containment: 'document',
			start: function (event, ui) {
				$(ui.helper).addClass("ayy");
			},
		});	
		$('.emdform-field-button').not('.disabled').not('.upgrade-pro').draggable({
			connectToSortable: '.emd-form-row-holder',
			delay: 200,
			helper: function(event) {
				var $this = $(this),
				width = $this.outerWidth(),
				text  = $this.html(),
				field  = $this.data('field'),
				$el   = $('<div class="emd-form-field-drag-out emd-form-field-drag">');
				return $el.html(text).css('width',width).attr('data-original-width',width).attr('data-field',field);
			},
			revert: 'invalid',
			cancel: false,
			scroll: false,
			opacity: 0.75,
			containment: 'document'
		});
		$('.emdform-field-button,.emdform-html-button').click(function(e){
			e.preventDefault();
		});
	}
	$.fn.emdFormSortDrag();
	$('.emd-form-builder-fields-heading').click(function(e){
		e.preventDefault();
		if($(this).find('.emd-formbuilder-icons').hasClass('angle-down')){
			$(this).find('.emd-formbuilder-icons').removeClass('angle-down').addClass('angle-up');
		}
		else {
			$(this).find('.emd-formbuilder-icons').removeClass('angle-up').addClass('angle-down');
		}
		$(this).parent('.emd-form-builder-fields-group').find('.emd-form-builder-fields').toggle();
	});
	$('.emd-form-builder-tab').click(function(e){
		e.preventDefault();
		$('.emd-form-builder-tab').removeClass('active');
		$('.emd-form-field').removeClass('active');
		$(this).addClass('active');
		if($(this).attr('id') == 'fields'){
			$('.emd-form-builder-fields').show();
			$('.emd-form-builder-fields-settings').hide();
		}
		else {
			$('.emd-form-builder-fields-settings').show();
			$('.emd-form-builder-field-settings-wrap').hide();
			$('.emd-form-builder-fields').hide();
			id= $('.emd-form-builder-page.active').attr('id');
			page_id = id.replace('emd-form-builder-page-','');
			$('#emd-form-page-'+page_id+' .emd-form-field').first().addClass('active');
			field_id = $('#emd-form-page-'+page_id+' .emd-form-field').first().data('field');	
			$('.emd-form-builder-field-settings-wrap.emd-field-'+field_id).show();
		}
	});	
	$('.emd-form-builder-page').click(function(e){
		e.preventDefault();
		pid = $(this).attr('id').replace('emd-form-builder-page-','');
		$('.emd-form-builder-page').removeClass('active');
		$(this).addClass('active');
		$('.emd-form-page-wrap').hide();
		$('#emd-form-page-'+pid).show();
		$('.emd-form-builder-field-settings-wrap').hide();
		$('.emd-form-builder-field-settings-wrap.emd-field-page'+pid).show();
		$('.emd-form-builder-fields-settings').show();
		$('.emd-form-builder-fields').hide();
		$('#fields').removeClass('active');
		$('#settings').addClass('active');
	});
	$('.emd-form-builder-page-delete').click(function(e){
		e.preventDefault();
		id = $(this).parent('.emd-form-builder-page').attr('id');
		page_id = id.replace('emd-form-builder-page-','');
		//get all fields 
		form_fields = $('#emd-form-page-'+page_id).find('.emd-form-field');
		$.each(form_fields,function(key, val){	
			$('#'+$(val).data('field')+'-btn').removeClass('disabled ui-draggable-disabled');
			$('#'+$(val).data('field')+'-btn').draggable({
				connectToSortable: '.emd-form-row-holder',
				delay: 200,
				helper: function(event) {
					var $this = $(this),
					width = $this.outerWidth(),
					text  = $this.html(),
					field  = $this.data('field'),
					$el   = $('<div class="emd-form-field-drag-out emd-form-field-drag">');
					return $el.html(text).css('width',width).attr('data-original-width',width).attr('data-field',field);
				},
				revert: 'invalid',
				cancel: false,
				scroll: false,
				opacity: 0.75,
				containment: 'document'
			});
			$('#'+$(val).data('field')+'-btn').draggable('enable');
		});
		$(this).parent('.emd-form-builder-page').remove();
		$('#emd-form-page-'+page_id).remove();
		$('#emd-form-builder-page-1').addClass('active');
		$('#emd-form-page-1').show();
	});

	$('#emd-form-builder-page-1').addClass('active');
	$('#emd-form-page-1').show();


	$('.emd-form-builder-add-row').click(function(e){
		e.preventDefault();
		app = $(this).data('app');
		entity = $(this).data('entity');
		id= $('.emd-form-builder-page.active').attr('id');
		page_id = id.replace('emd-form-builder-page-','');
		$.ajax({
			type: 'GET',
			url: builder_vars.ajax_url,
			cache: false,
			async: false,
			data: {action:'emd_form_builder_lite_get_row',app:app,entity:entity,nonce:builder_vars.nonce},
			success: function(response){
				if(response.success){
					$(response.data.row).insertAfter($('#emd-form-page-'+page_id+' .emd-form-page-hidden'));
					$.fn.emdFormSortDrag();
					$.fn.emdFormDel();
				}
			}
		});
	});
	$('.emdform-hr-button').click(function(e){
		e.preventDefault();
		app = $(this).data('app');
		entity = $(this).data('entity');
		id= $('.emd-form-builder-page.active').attr('id');
		page_id = id.replace('emd-form-builder-page-','');
		$.ajax({
			type: 'GET',
			url: builder_vars.ajax_url,
			cache: false,
			async: false,
			data: {action:'emd_form_builder_lite_get_hr',app:app,entity:entity,nonce:builder_vars.nonce},
			success: function(response){
				if(response.success){
					$('#emd-form-page-'+page_id).prepend(response.data.row);
					$.fn.emdFormSortDrag();
					$.fn.emdFormDel();
				}
			}
		});
	});
	$.fn.emdFormDel = function() {	
		$('.emd-form-field-delete').click(function(e){
			e.preventDefault();
			$('#'+$(this).parent('.emd-form-field').data('field')+'-btn').removeClass('disabled ui-draggable-disabled');
			$('#'+$(this).parent('.emd-form-field').data('field')+'-btn').draggable({
				connectToSortable: '.emd-form-row-holder',
				delay: 200,
				helper: function(event) {
					var $this = $(this),
					width = $this.outerWidth(),
					text  = $this.html(),
					field  = $this.data('field'),
					$el   = $('<div class="emd-form-field-drag-out emd-form-field-drag">');
					return $el.html(text).css('width',width).attr('data-original-width',width).attr('data-field',field);
				},
				revert: 'invalid',
				cancel: false,
				scroll: false,
				opacity: 0.75,
				containment: 'document'
			});
			$('#'+$(this).parent('.emd-form-field').data('field')+'-btn').draggable('enable');
			$(this).parent('.emd-form-field').remove();
		});
		$('.emd-form-row-delete').click(function(e){
			e.preventDefault();
			form_fields = $(this).parent('.emd-form-row').find('.emd-form-field');
			$.each(form_fields,function(key, val){	
				$('#'+$(val).data('field')+'-btn').removeClass('disabled ui-draggable-disabled');
				$('#'+$(val).data('field')+'-btn').draggable({
					connectToSortable: '.emd-form-row-holder',
					delay: 200,
					helper: function(event) {
						var $this = $(this),
						width = $this.outerWidth(),
						text  = $this.html(),
						field  = $this.data('field'),
						$el   = $('<div class="emd-form-field-drag-out emd-form-field-drag">');
						return $el.html(text).css('width',width).attr('data-original-width',width).attr('data-field',field);
					},
					revert: 'invalid',
					cancel: false,
					scroll: false,
					opacity: 0.75,
					containment: 'document'
				});
				$('#'+$(val).data('field')+'-btn').draggable('enable');
			});
			$(this).parent('.emd-form-row').remove();
		});
		$('.emd-form-field').click(function(e){
			e.preventDefault();
			$('.emd-form-field').removeClass('active');
			$(this).addClass('active');
			$('.emd-form-builder-field-settings-wrap').hide();
			$('.emd-form-builder-field-settings-wrap.emd-field-'+$(this).data('field')).show();
			$('.emd-form-builder-fields-settings').show();
			$('.emd-form-builder-fields').hide();
			$('#fields').removeClass('active');
			$('#settings').addClass('active');
		});
	}
	$.fn.emdFormDel();
	$('#emd-form-exit').click(function(e){
		e.preventDefault();
		window.location.href = builder_vars.exit_url;
	});	
	$('#emd-form-save').click(function(e){
		e.preventDefault();
		$.ajax({
			type: 'POST',
			url: builder_vars.ajax_url,
			cache: false,
			async: false,
			data: {action:'emd_form_builder_lite_save_form',data:JSON.stringify($('#emd-form-builder-form').serializeArray()),nonce:builder_vars.nonce},
			success: function(response){
				if(response.success){
					$('.emd-form-save-success').show();
					//took out redirecting , after save show message and stay in builder
					 //window.location.href = builder_vars.exit_url;
				}
			}
		});
	});	
	$('.emd-form-builder-field-label').change(function(e){
		new_label = $(this).val();
		label_id = $(this).attr('id').replace('emd-fbl-','');
		$('#label_'+label_id).text(new_label);
	});	
	$('.emd-form-builder-field-desc').change(function(e){
		new_desc = $(this).val();
		desc_id = $(this).attr('id').replace('emd-fbd-','');
		if(!new_desc){
			$('#info_'+desc_id).hide();
		}
		else {	
			$('#info_'+desc_id).show();
		}
		$('#info_'+desc_id).attr('title',new_desc);
	});	
	$('.emd-form-builder-field-req').change(function(e){
		new_req = $(this).val();
		req_id = $(this).attr('id').replace('emd-fbr-','');
		if($(this).prop('checked')){
			$('#req_'+req_id).show();
		}
		else {
			$('#req_'+req_id).hide();
		}
	});	
	$('.emd-form-builder-field-placeholder').change(function(e){
		new_pl = $(this).val();
		pl_id = $(this).attr('id').replace('emd-fbp-','');
		$('#'+pl_id).attr('placeholder',new_pl);
	});	
	$('.emd-form-builder-field-html').change(function(e){
		html_id = $(this).attr('id').replace('emd-fbhtml-','html');
		$('#'+html_id).closest('.emd-form-row').removeClass('init');
		$('#'+html_id).removeClass('emd-form-html-div');
	});	
	$('.emd-form-builder-field-size').change(function(e){
		new_size = $(this).val();
		size_id = $(this).attr('id').replace('emd-fbs-','');
		old_class = $('#'+size_id).closest('.emd-form-field').attr('class');
		new_class = old_class.replace(/emd-md-(\d+)/g,'emd-md-'+new_size);
		$('#'+size_id).closest('.emd-form-field').attr('class',new_class);
		$('#'+size_id).closest('.emd-form-field').data('size',new_size);
	});	
});
