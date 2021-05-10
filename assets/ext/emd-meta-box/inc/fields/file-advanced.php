<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

require_once EMD_MB_FIELDS_DIR . 'file.php';
if ( ! class_exists( 'EMD_MB_File_Advanced_Field' ) )
{
	class EMD_MB_File_Advanced_Field extends EMD_MB_File_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			parent::admin_enqueue_scripts();

			// Make sure scripts for new media uploader in WordPress 3.5 is enqueued
			wp_enqueue_media();
			wp_enqueue_script( 'emd-mb-file-advanced', EMD_MB_JS_URL . 'file-advanced.js', array( 'jquery', 'underscore' ), EMD_MB_VER, true );
			wp_localize_script( 'emd-mb-file-advanced', 'emdmbFileAdvanced', array(
				'frameTitle' => __( 'Select Files', 'emd-plugins' ),
			) );
		}

		/**
		 * Add actions
		 *
		 * @return void
		 */
		static function add_actions()
		{
			parent::add_actions();

			// Attach images via Ajax
			add_action( 'wp_ajax_emd_mb_attach_file', array( __CLASS__, 'wp_ajax_attach_file' ) );
			add_action( 'print_media_templates', array( __CLASS__, 'print_templates' ) );
		}

		static function wp_ajax_attach_file()
		{
			$post_id = is_numeric( $_REQUEST['post_id'] ) ? (int) $_REQUEST['post_id'] : 0;
			$field_id = isset( $_POST['field_id'] ) ? (string) $_POST['field_id'] : 0;
			$attachment_ids = isset( $_POST['attachment_ids'] ) ? array_map( 'intval', $_POST['attachment_ids']) : array();

			check_ajax_referer( "emd-mb-attach-file_{$field_id}" );
			foreach( $attachment_ids as $attachment_id )
				add_post_meta( $post_id, $field_id, $attachment_id, false );

			wp_send_json_success();
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			$i18n_title  = apply_filters( 'emd_mb_file_advanced_select_string', _x( 'Select or Upload Files', 'file upload', 'emd-plugins' ), $field );
			$attach_nonce = wp_create_nonce( "emd-mb-attach-file_{$field['id']}" );

			// Uploaded files
			$html = self::get_uploaded_files( $meta, $field );

			// Show form upload
			$classes = array( 'button', 'emd-mb-file-advanced-upload', 'hide-if-no-js', 'new-files' );
			if ( ! empty( $field['max_file_uploads'] ) && count( $meta ) >= (int) $field['max_file_uploads'] )
				$classes[] = 'hidden';

			$classes = implode( ' ', $classes );
			$html .= "<a href='#' class='{$classes}' data-attach_file_nonce={$attach_nonce}>{$i18n_title}</a>";

			return $html;
		}

		/**
		 * Get field value
		 * It's the combination of new (uploaded) images and saved images
		 *
		 * @param array $new
		 * @param array $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return array|mixed
		 */
		static function value( $new, $old, $post_id, $field )
		{
			$new = (array) $new;
			return array_unique( array_merge( $old, $new ) );
		}

		static function print_templates()
		{
			$i18n_delete = apply_filters( 'emd_mb_file_delete_string', _x( 'Delete', 'file upload', 'emd-plugins' ) );
			$i18n_edit   = apply_filters( 'emd_mb_file_edit_string', _x( 'Edit', 'file upload', 'emd-plugins' ) );
			?>
            <script id="tmpl-emd-mb-file-advanced" type="text/html">
				<# _.each( attachments, function( attachment ) { #>
				<li>
					<div class="emd-mb-icon"><img src="<# if ( attachment.type == 'image' ){ #>{{{ attachment.sizes.thumbnail.url }}}<# } else { #>{{{ attachment.icon }}}<# } #>"></div>
					<div class="emd-mb-info">
						<a href="{{{ attachment.url }}}" target="_blank">{{{ attachment.title }}}</a>
						<p>{{{ attachment.mime }}}</p>
						<a title="<?php echo $i18n_edit; ?>" href="{{{ attachment.editLink }}}" target="_blank"><?php echo $i18n_edit; ?></a> |
						<a title="<?php echo $i18n_delete; ?>" class="emd-mb-delete-file" href="#" data-attachment_id="{{{ attachment.id }}}"><?php echo $i18n_delete; ?></a>
					</div>
				</li>
				<# } ); #>
			</script>
            <?php
		}
	}
}
