<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'EMD_MB_Number_Field' ) )
{
	class EMD_MB_Number_Field extends EMD_MB_Field
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
				'<input type="number" class="emd-mb-number" name="%s" id="%s" value="%s" step="%s" min="%s" placeholder="%s"/>',
				$field['field_name'],
				empty( $field['clone'] ) ? $field['id'] : '',
				$meta,
				$field['step'],
				$field['min'],
				$field['placeholder']
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
				'step' => 1,
				'min'  => 0,
			) );
			return $field;
		}
	}
}
