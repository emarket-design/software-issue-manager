<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EMD_MB_Checkbox_Field' ) )
{
	class EMD_MB_Checkbox_Field extends EMD_MB_Field
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
				'<input type="checkbox" class="emd-mb-checkbox" name="%s" id="%s" value="1" %s %s/>',
				$field['field_name'],
				$field['id'],
				checked( !empty( $meta ), 1, false ),
				isset($field['data-cell']) ? "data-cell='{$field['data-cell']}'" : ''
			);
		}

		/**
		 * Set the value of checkbox to 1 or 0 instead of 'checked' and empty string
		 * This prevents using default value once the checkbox has been unchecked
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return int
		 */
		static function value( $new, $old, $post_id, $field )
		{
			return empty( $new ) ? 0 : 1;
		}
	}
}
