<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EMD_MB_Color_Field' ) )
{
	class EMD_MB_Color_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'emd-mb-color', EMD_MB_CSS_URL . 'color.css', array( 'farbtastic',  'wp-color-picker' ), EMD_MB_VER );
			wp_enqueue_script( 'emd-mb-color', EMD_MB_JS_URL . 'color.js', array( 'farbtastic',  'wp-color-picker' ), EMD_MB_VER, true );
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
				'<input class="emd-mb-color" type="text" name="%s" id="%s" value="%s" size="%s" />
				<div class="emd-mb-color-picker"></div>',
				$field['field_name'],
				empty( $field['clone'] ) ? $field['id'] : '',
				$meta,
				$field['size']
			);
		}

		/**
		 * Don't save '#' when no color is chosen
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
			return '#' === $new ? '' : $new;
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
				'size' => 7,
			) );

			return $field;
		}
	}
}
