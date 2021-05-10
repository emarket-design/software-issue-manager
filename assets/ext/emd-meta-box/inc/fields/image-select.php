<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EMD_MB_Image_Select_Field' ) )
{
	class EMD_MB_Image_Select_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'emd-mb-image-select', EMD_MB_CSS_URL . 'image-select.css', array(), EMD_MB_VER );
			wp_enqueue_script( 'emd-mb-image-select', EMD_MB_JS_URL . 'image-select.js', array( 'jquery' ), EMD_MB_VER, true );
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
			$html = array();
			$tpl = '<label class="emd-mb-image-select"><img src="%s"><input type="%s" class="hidden" name="%s" value="%s"%s></label>';

			$meta = (array) $meta;
			foreach ( $field['options'] as $value => $image )
			{
				$html[] = sprintf(
					$tpl,
					$image,
					$field['multiple'] ? 'checkbox' : 'radio',
					$field['field_name'],
					$value,
					checked( in_array( $value, $meta ), true, false )
				);
			}

			return implode( ' ', $html );
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
			$field['field_name'] .= $field['multiple'] ? '[]' : '';
			return $field;
		}
	}
}
