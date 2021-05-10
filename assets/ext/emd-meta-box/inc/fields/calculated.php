<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EMD_MB_Calculated_Field' ) )
{
	class EMD_MB_Calculated_Field extends EMD_MB_Field
	{
		/**
                 * Enqueue scripts and styles
                 *
                 * @return void
                 */
                static function admin_enqueue_scripts()
                {
                        wp_enqueue_script( 'wpas-calculate', EMD_MB_URL . '../calculate/wpas-calculate.min.js');
                        wp_enqueue_script( 'calculate', EMD_MB_JS_URL . 'calculate.js');
			wp_localize_script('calculate','app_name',EMD_MB_APP);
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
				'<input type="text" class="emd-mb-text" name="%s" id="%s" value="%s" placeholder="%s" size="%s" %s data-formula="%s" data-cell="%s" readonly>%s',
				$field['field_name'],
				$field['id'],
				$meta,
				$field['placeholder'],
				$field['size'],
				!$field['datalist'] ?  '' : "list='{$field['datalist']['id']}'",
				isset($field['data-formula']) ? str_replace("\"","'",$field['data-formula']) : '',
				isset($field['data-cell']) ? $field['data-cell'] : '',
				self::datalist_html($field)
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
				'datalist'    => false,
				'placeholder' => '',
			) );
			return $field;
		}

		/**
		 * Create datalist, if any
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function datalist_html( $field )
		{
			if( !$field['datalist'] )
				return '';
			$datalist = $field['datalist'];
			$html = sprintf(
				'<datalist id="%s">',
				$datalist['id']
			);

			foreach( $datalist['options'] as $option ) {
				$html.= sprintf('<option value="%s"></option>', $option);
			}

			$html .= '</datalist>';

			return $html;
		}
	}
}
