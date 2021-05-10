jQuery( function( $ )
{
	$( 'body' ).on( 'click', '.emd-mb-thickbox-upload', function()
	{
		var $this = $( this ),
			$holder = $this.siblings( '.emd-mb-images' ),
			post_id = $( '#post_ID' ).val(),
			field_id = $this.data( 'field_id' ),
			backup = window.send_to_editor;

		window.send_to_editor = function( html )
		{
			var $img = $( '<div />' ).append( html ).find( 'img' ),
				url = $img.attr( 'src' ),
				img_class = $img.attr( 'class' ),
				id = parseInt( img_class.replace( /\D/g, '' ), 10 );

			html = '<li id="item_' + id + '">';
			html += '<img src="' + url + '">';
			html += '<div class="emd-mb-image-bar">';
			html += '<a class="emd-mb-delete-file" href="#" data-attachment_id="' + id + '">×</a>';
			html += '</div>';
			html += '<input type="hidden" name="' + field_id + '[]" value="' + id + '">';
			html += '</li>';

			$holder.append( $( html ) ).removeClass( 'hidden' );

			tb_remove();
			if($('.emd-mb-images').data('max_file_uploads') == 1){
				$('a.emd-mb-thickbox-upload').hide();
			}
			window.send_to_editor = backup;
		}
		tb_show( '', 'media-upload.php?post_id=' + post_id + '&TB_iframe=true' );

		return false;
	} );
	$( '.emd-mb-images' ).on( 'click', '.emd-mb-delete-file', function(){
		if($('.emd-mb-images').data('max_file_uploads') == 1){
			$('a.emd-mb-thickbox-upload').show();
		}
	});
} );
