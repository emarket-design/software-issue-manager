<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EMD_MB_Time_Field' ) )
{
	class EMD_MB_Time_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return	void
		 */
		static function admin_enqueue_scripts( )
		{
			$url = EMD_MB_JS_URL . 'jqueryui';
			$url_css = EMD_MB_CSS_URL . 'jqueryui';
			wp_register_script( 'jquery-ui-timepicker', "{$url}/jquery-ui-timepicker-addon.js", array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '0.9.7', true );
			wp_enqueue_style( 'jquery-ui-timepicker-css', "{$url_css}/jquery-ui-timepicker-addon.css");

			wp_enqueue_script( 'emd-mb-time', EMD_MB_JS_URL.'time.js', array( 'jquery-ui-timepicker' ), EMD_MB_VER, true );
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
			if($meta != '')
                        {
                                if($field['js_options']['timeFormat'] == 'hh:mm')
                                {
                                        $getformat = 'H:i';
                                }
                                else
                                {
                                        $getformat = 'H:i:s';
                                }
				if(DateTime::createFromFormat($getformat,$meta)){
                                	$meta = DateTime::createFromFormat($getformat,$meta)->format(self::translate_format($field));
				}
                        }
                        return sprintf(
                                '<input type="text" class="emd-mb-time" name="%s" value="%s" id="%s" size="%s" data-options="%s" readonly/>',
                                $field['field_name'],
                                $meta,
                                isset( $field['clone'] ) && $field['clone'] ? '' : $field['id'],
                                $field['size'],
                                esc_attr( json_encode( $field['js_options'] ) )
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
				'size'       => 30,
				'js_options' => array(),
			) );

			// Deprecate 'format', but keep it for backward compatible
			// Use 'js_options' instead
			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'showButtonPanel' => true,
				'timeFormat'      => empty( $field['format'] ) ? 'hh:mm:ss' : $field['format'],
			) );

			return $field;
		}
		// Missing: 't' => '', T' => '', 'm' => '', 's' => ''
                static $time_format_translation = array(
                        'H' => 'H', 'HH' => 'H', 'h' => 'H', 'hh' => 'H',
                        'mm' => 'i', 'ss' => 's', 'l' => 'u', 'tt' => 'a', 'TT' => 'A'
                );

                static function translate_format( $field )
                {
                       return  strtr( $field['js_options']['timeFormat'], self::$time_format_translation );
                }
                static function save( $new, $old, $post_id, $field )
                {
                        $name = $field['id'];
                        if ( '' === $new)
                        {
                                delete_post_meta( $post_id, $name );
                                return;
                        }
                        if($field['js_options']['timeFormat'] == 'hh:mm')
                        {
                                $getformat = 'H:i';
                        }
                        else
                        {
                                $getformat = 'H:i:s';
                        }
			if(DateTime::createFromFormat(self::translate_format($field), $new)){
				$new = DateTime::createFromFormat(self::translate_format($field), $new)->format($getformat);
                        	update_post_meta( $post_id, $name, $new );
			}
                }
	}
}
