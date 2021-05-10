<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'EMD_MB_Rating_Field' ) )
{
	class EMD_MB_Rating_Field extends EMD_MB_Field
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
			global $post_id;
			$html = apply_filters('emd_get_rating_value',$meta,Array('meta' => $field['id']), $post_id);
			return $html;
		}
		/**
		 * Standard meta retrieval
		 *
		 * @param int   $post_id
		 * @param bool  $saved
		 * @param array $field
		 *
		 * @return array
		 */
		static function meta( $post_id, $saved, $field )
		{
			$value = get_post_meta($post_id,'emd_ratings_average_'. $field['id'],true);
			return $value;
		}
	}
}
