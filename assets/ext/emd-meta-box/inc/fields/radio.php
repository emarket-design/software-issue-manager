<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EMD_MB_Radio_Field' ) )
{
	class EMD_MB_Radio_Field extends EMD_MB_Field
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
			$html = array();
			$tpl = '<label><input type="radio" class="emd-mb-radio" name="%s" value="%s"%s %s> %s</label>';

			foreach ( $field['options'] as $value => $label )
			{
				$html[] = sprintf(
					$tpl,
					$field['field_name'],
					$value,
					checked( $value, $meta, false ),
					isset($field['data-cell'][$value]) ? "data-cell='{$field['data-cell'][$value]}'" : '',
					$label
				);
			}

			return implode( ' ', $html );
		}
	}
}
