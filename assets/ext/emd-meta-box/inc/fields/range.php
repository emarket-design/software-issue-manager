<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'EMD_MB_Range_Field' ) )
{
	class EMD_MB_Range_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'emd-mb-range', EMD_MB_CSS_URL . 'range.css', array(), EMD_MB_VER );
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
				'<input type="range" class="emd-mb-range" name="%s" id="%s" value="%s" min="%s" max="%s" step="%s" />',
				$field['field_name'],
				$field['id'],
				$meta,
				$field['min'],
				$field['max'],
				$field['step']
			);
		}

		/**
		 * Normalize parameters for field.
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function normalize_field( $field )
		{
			$field = wp_parse_args( $field, array(
				'min'  => 0,
				'max'  => 10,
				'step' => 1
			) );
			return $field;
		}

		/**
		 * Ensure number in range.
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
			$new = intval($new);
			$min = intval($field['min']);
			$max = intval($field['max']);

			if ($new < $min) {
				return $min;
			}
			else if ($new > $max) {
				return $max;
			}

			return $new;
		}
	}
}
