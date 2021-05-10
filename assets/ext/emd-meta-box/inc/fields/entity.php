<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EMD_MB_Entity_Field' ) )
{
	class EMD_MB_Entity_Field extends EMD_MB_Field
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
			$ent_id_meta = $field['display_meta'];
			$ent_id = get_post_meta(get_the_ID(),$ent_id_meta,true);
			return sprintf(
				'<span class="emd-mb-entity" name="%s" id="%s">%s</span>',
				$field['field_name'],
				$field['id'],
				get_the_title($ent_id)
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
	}
}
