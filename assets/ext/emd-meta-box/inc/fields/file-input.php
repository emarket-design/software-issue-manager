<?php
if ( !class_exists( 'EMD_MB_File_Input_Field' ) )
{
	class EMD_MB_File_Input_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			// Make sure scripts for new media uploader in WordPress 3.5 is enqueued
			wp_enqueue_media();
			wp_enqueue_script( 'emd-mb-file-input', EMD_MB_JS_URL . 'file-input.js', array( 'jquery' ), EMD_MB_VER, true );
			wp_localize_script( 'emd-mb-file-input', 'emdmbFileInput', array(
				'frameTitle' => __( 'Select File', 'emd-plugins' ),
			) );
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
			return sprintf(
				'<input type="text" class="emd-mb-file-input" name="%s" id="%s" value="%s" placeholder="%s" size="%s">
				<a href="#" class="emd-mb-file-input-select button-primary">%s</a>
				<a href="#" class="emd-mb-file-input-remove button %s">%s</a>',
				$field['field_name'],
				$field['id'],
				$meta,
				$field['placeholder'],
				$field['size'],
				__( 'Select', 'emd-plugins' ),
				$meta ? '' : 'hidden',
				__( 'Remove', 'emd-plugins' )
			);
		}

		/**
		 * Normalize parameters for field
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'size'        => 30,
				'placeholder' => '',
			) );
			return $field;
		}
	}
}
