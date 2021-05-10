jQuery(document).ready(function($){
	pluginModalHtml =
		    '<div class="emd-plugin-modal emd-plugin-modal-deactivation-feedback">'
		    + ' <div class="emd-plugin-modal-dialog">'
		    + '         <div class="emd-plugin-modal-header">'
		    + '         <h4>' + plugin_feedback_vars.header + ' </h4>'
		    + '         </div>'
		    + '         <div class="emd-plugin-modal-body">'
		    + '                 <div class="emd-plugin-modal-panel active" data-panel-id="reasons">' + '<ul id="reasons-list">' + plugin_feedback_vars.reasons + '</ul></div>'
		    + '         </div>'
		    + '         <div class="emd-plugin-modal-footer"><input type="hidden" id="emd-plugin" value="">'
		    + '			<div style="font-size:80%;text-align:left;padding-bottom:10px">' + plugin_feedback_vars.disclaimer + '</div>'
		    + '                 <div>'
		    + '			<a href="#" class="button button-secondary button-deactivate">'+ plugin_feedback_vars.skip + '</a>'
		    + '                 <a href="#" class="button button-primary button-close">'+ plugin_feedback_vars.cancel +'</a>'
		    + '         	</div>'
		    + '         </div>'
		    + ' </div>'
		    + '</div>',
	$pluginModal = $(pluginModalHtml);
	$.fn.setFeedback = function (plugin){
		$deactivatePluginLink = $('i.'+plugin+'-deactivate-slug').parent().find('a');
		$pluginModal.appendTo($('body'));
		
		$deactivatePluginLink.click(function (e) {
			e.preventDefault();
			resetPluginModal();
			$pluginModal.addClass('active');
			$('body').addClass('has-emd-plugin-modal');
			$('#emd-plugin').val(plugin);
		});
		$pluginModal.on('click', '.emd-plugin-modal-footer .button', function (e) {
			e.preventDefault();
			if($(this).hasClass('disabled')){
				return;
			}
			var href_link = $('i.'+$('#emd-plugin').val()+'-deactivate-slug').parent().find('a').attr('href');
			var _parent = $(this).parents('.emd-plugin-modal:first');
			if($(this).hasClass('allow-deactivate')) {
				var $radio = $('input[type="radio"]:checked');

				if (0 === $radio.length) {
					// If no selected reason, just deactivate the plugin.
					window.location.href = href_link;
					return;
				}

				var $selected_reason = $radio.parents('li:first'),
				    $input = $selected_reason.find('textarea, input[type="text"]'),
				    userReason = ( 0 !== $input.length ) ? $input.val().trim() : '';
				var $uemail = $selected_reason.find('input[type="email"]'),
                                    userEmail = ( 0 !== $uemail.length ) ? $uemail.val().trim() : '';

				if (isPluginOtherReasonSelected() && ( '' === userReason )) {
					return;
				}
				$.ajax({
					url       : ajaxurl,
					method    : 'POST',
					data      : {
						'action'     : plugin+'_send_deactivate_reason',
						'reason_id'  : $radio.val(),
						'utype'  : plugin_feedback_vars.utype,
						'plugin_name'  : plugin,
						'deactivate_nonce': plugin_feedback_vars.nonce,
						'reason_info': userReason,
						'email' : userEmail,
					},
					beforeSend: function () {
						_parent.find('.emd-plugin-modal-footer .button').addClass('disabled');
						_parent.find('.emd-plugin-modal-footer .button-secondary').text('Processing...');
					},
					complete  : function () {
						// Do not show the dialog box, deactivate the plugin.
						window.location.href = href_link;
					}
				});
			}
			else if ($(this).hasClass('button-deactivate')) {
				_parent.find('.button-deactivate').addClass('allow-deactivate');
				showPanel('reasons');
			}
		});
		//If clicked outside modal, cancel it.
		$pluginModal.on('click', function (e) {
			var $target = $(e.target);
			// If the user has clicked anywhere in the modal dialog, just return.
			if ($target.hasClass('emd-plugin-modal-body') || $target.hasClass('emd-plugin-modal-footer')) {
				return;
			}
			// If the user has not clicked the close button and the clicked element is inside the modal dialog, just return.
			if (!$target.hasClass('button-close') && ( $target.parents('.emd-plugin-modal-body').length > 0 || $target.parents('.emd-plugin-modal-footer').length > 0 )) {
				return;
			}
			$pluginModal.removeClass('active');
			$('body').removeClass('has-emd-plugin-modal');
		});
		$pluginModal.on('input propertychange', '.reason-input input, .reason-input textarea', function () {
			if (!isPluginOtherReasonSelected()) {
				return;
			}
			var reason = $(this).val().trim();
			if (reason.length > 0) {
				$('.message').removeClass('error-message');
				$pluginModal.find('.button-deactivate').removeClass('disabled');
			}
		});
		$pluginModal.on('blur', '.reason-input input', function () {
			var $userReason = $(this);
			setTimeout(function () {
				if (!isPluginOtherReasonSelected()) {
					return;
				}
				if (0 === $userReason.val().trim().length) {
					$('.message').addClass('error-message');
					$pluginModal.find('.button-deactivate').addClass('disabled');
				}
			}, 150);
		});
		$pluginModal.on('click', 'input[type="radio"]', function () {
			var $selectedReasonOption = $(this);

			// If the selection has not changed, do not proceed.
			if (false === $selectedReasonOption.val())
				return;

			selectedReasonID = $selectedReasonOption.val();

			var _parent = $(this).parents('li:first');

			$pluginModal.find('.reason-input').remove();
			$pluginModal.find('.reason-support').remove();
			$pluginModal.find('.button-deactivate').text(plugin_feedback_vars.submit);

			$pluginModal.find('.button-deactivate').removeClass('disabled');
			if (_parent.hasClass('has-input')) {
				reasonSupport = '';
				if(selectedReasonID == 9 || selectedReasonID == 5 || selectedReasonID == 7){
					reasonSupport = '<div class="reason-support" style="padding: 10px 25px 0;"><input type="checkbox" value="1" class="reason-support-ckb" /><label style="">' + plugin_feedback_vars.ticket + '</label><p><input type="email" class="reason-support-email" style="display:none;" placeholder="' + plugin_feedback_vars.emplach + '"/></div>';
				}
				var inputType = _parent.data('input-type'),
				    inputPlaceholder = _parent.data('input-placeholder'),
				    reasonInputHtml = reasonSupport + '<div class="reason-input"><span class="message"></span>' + ( ( 'textfield' === inputType ) ? '<input type="text" />' : '<textarea rows="5"></textarea>' ) + '</div>';
				_parent.append($(reasonInputHtml));
				_parent.find('input:text, textarea').attr('placeholder', inputPlaceholder).focus();

				if (isPluginOtherReasonSelected()) {
					$pluginModal.find('.message').text(plugin_feedback_vars.ask_reason).show();
					$pluginModal.find('.button-deactivate').addClass('disabled');
				}
			}
		});
		$pluginModal.on('click','.reason-support-ckb', function (){
			$('.reason-support-email').toggle();
		});
	}
	function showPluginPanel(panelType) {
		$pluginModal.find('.emd-plugin-modal-panel').removeClass('active ');
		$pluginModal.find('[data-panel-id="' + panelType + '"]').addClass('active');
		$pluginModal.find('.button-deactivate').text(plugin_feedback_vars.skip);
	}
	function isPluginOtherReasonSelected() {
		var $selectedReasonOption = $pluginModal.find('input[type="radio"]:checked'),
		selectedReason = $selectedReasonOption.parent().next().text().trim();
		return ( 'Other' === selectedReason );
	}
	function resetPluginModal() {
		selectedReasonID = false;
		$pluginModal.find('.button-deactivate').removeClass('disabled');
		// Uncheck all radio buttons.
		$pluginModal.find('input[type="radio"]').prop('checked', false);
		// Remove all input fields ( textfield, textarea ).
		$pluginModal.find('.reason-input').remove();
		$pluginModal.find('.message').hide();
		var $deactivateButton = $pluginModal.find('.button-deactivate');
		$deactivateButton.addClass('allow-deactivate');
		showPluginPanel('reasons');
	}
});
