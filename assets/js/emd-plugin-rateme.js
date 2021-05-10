jQuery(document).ready(function($){
	$('.emd-permissions .emd-perm-trigger').on('click', function () {
                $('.emd-permissions-list').toggle();
        });
	var container = $('.emd-show-rateme');
	if (container.length) {
		container.find('a').click(function() {
			var rateAction = $(this).data('rate-action');
			var ratePlugin = $(this).data('plugin');
			if(rateAction != 'twitter'){	
				container.remove();
				$.ajax({
					url       : ajaxurl,
					method    : 'POST',
					dataType  : 'json',
					data      : {
						'action'     : ratePlugin+'_show_rateme',
						'rate_action': rateAction,
						'rateme_nonce': container.find('ul:first').attr('data-nonce'),
					},
					success: function(response){
						if ('do-rate' !== rateAction && 'upgrade-now' !== rateAction) {
							return false;
						}
						else if(response.redirect) {
							window.location.href = response.redirect;
						}
					}
				});
			}
		});
	}
});

