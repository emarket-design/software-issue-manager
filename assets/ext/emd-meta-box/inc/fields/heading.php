<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EMD_MB_Heading_Field' ) )
{
	class EMD_MB_Heading_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			wp_enqueue_style( 'emd-mb-heading', EMD_MB_CSS_URL . 'heading.css', array(), EMD_MB_VER );
		}

		/**
		 * Show begin HTML markup for fields
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function begin_html( $meta, $field )
		{
			return sprintf(
				'<h4>%s</h4>',
				$field['name']
			);
		}

		/**
		 * Show end HTML markup for fields
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function end_html( $meta, $field )
		{
			return '';
		}
	}
}
