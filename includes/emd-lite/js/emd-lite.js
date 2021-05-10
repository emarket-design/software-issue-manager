jQuery(document).ready(function($){
	$('.upgrade-pro').click(function(e){
		feature = $(this).text();
		if(!feature){
			feature = $(this).data('upgrade');
		}
		var message    = lite_vars.upgrade_message; 
		upgradeURL = encodeURI(lite_vars.upgrade_url + '&pk_kwd=' + feature.trim());
		e.preventDefault();
		url = $(this).attr('href');
		show_url = 0;
		if(url != undefined && url.match(/tab=/)){
			show_url = 1;
		}
		jconfirm.defaults = {
			closeIcon: true,
			backgroundDismiss: true,
			escapeKey: true,
			animationBounce: 1,
			useBootstrap: false,
			theme: 'modern',
			boxWidth: '315px',
			animateFromElement: false
		};
		$.alert({
			title   : lite_vars.upgrade_title,
			icon    : 'dashicons dashicons-lock',
			content: message,
			type: 'blue',
			boxWidth: '315px',
			onOpenBefore: function() {
				this.$body.find( '.jconfirm-content' ).addClass( 'lite-upgrade' );
			},
			onClose: function () {
				if(show_url == 1 && url != undefined){
					window.location.href = url;
				}
			},
			buttons: {
				confirm: {
					text    : lite_vars.upgrade_button,
					btnClass: 'btn-confirm',
					keys: [ 'enter' ],
					action: function () {
						show_url = 0;
						window.open( upgradeURL, '_blank' );
						$.alert({
							title   : false,
							content : lite_vars.upgrade_modal,
							icon    : 'dashicons dashicons-info',
							type    : 'blue',
							boxWidth: '315px',
							onClose: function () {
								if(url != undefined){
									window.location.href = url;
								}
							},
							buttons : {
								confirm: {
									text    : "OK",
									btnClass: 'btn-confirm',
									keys    : [ 'enter' ]
								}
							}
						});
					}
				}
			}
		});

	});
});
