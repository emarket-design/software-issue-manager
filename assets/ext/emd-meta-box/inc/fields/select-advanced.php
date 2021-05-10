<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "select" field is loaded
require_once EMD_MB_FIELDS_DIR . 'select.php';

if ( !class_exists( 'EMD_MB_Select_Advanced_Field' ) )
{
	class EMD_MB_Select_Advanced_Field extends EMD_MB_Select_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'emdmb-select2', EMD_MB_CSS_URL . 'select2/select2.css', array(), '3.2' );
			wp_enqueue_style( 'emdmb-select-advanced', EMD_MB_CSS_URL . 'select-advanced.css', array(), EMD_MB_VER );

			wp_register_script( 'emdmb-select2', EMD_MB_JS_URL . 'select2/select2.min.js', array(), '3.2', true );
			wp_enqueue_script( 'emd-mb-select-advanced', EMD_MB_JS_URL . 'select-advanced.js', array( 'emdmb-select2' ), EMD_MB_VER, true );

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
			$html = sprintf(
				'<select class="emd-mb-select-advanced" name="%s" id="%s" size="%s"%s %s %s data-options="%s">',
				$field['field_name'],
				$field['id'],
				$field['size'],
				$field['multiple'] ? ' multiple="multiple"' : '',
				isset($field['data-cell']) ? "data-cell='{$field['data-cell']}'" : '',
				isset($field['state']) ? "data-state='{$field['state']}'" : '',
				esc_attr( json_encode( $field['js_options'] ) )
			);

			$html .= self::options_html( $field, $meta );

			$html .= '</select>';

			return $html;
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
			$field = parent::normalize_field( $field );

			$field = wp_parse_args( $field, array(
				'js_options' => array(),
			) );

			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'allowClear'  => true,
				'width'       => 'resolve',
				'placeholder' => $field['placeholder'],
			) );

			return $field;
		}
	}
}
