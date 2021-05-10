<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "text" field is loaded
require_once EMD_MB_FIELDS_DIR . 'text.php';

if ( ! class_exists( 'EMD_MB_Password_Field' ) )
{
	class EMD_MB_Password_Field extends EMD_MB_Text_Field
	{
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
				'<input type="password" class="emd-mb-password" name="%s" id="%s" value="%s" size="%s" />',
				$field['field_name'],
				$field['id'],
				$meta,
				$field['size']
			);
		}
	}
}
