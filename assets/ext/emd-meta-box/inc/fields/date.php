<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'EMD_MB_Date_Field' ) )
{
	class EMD_MB_Date_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			$deps = array( 'jquery-ui-datepicker' );
			$locale = get_locale();
			$date_vars['closeText'] = __('Done','emd-plugins');
			$date_vars['prevText'] = __('Prev','emd-plugins');
			$date_vars['nextText'] = __('Next','emd-plugins');
			$date_vars['currentText'] = __('Today','emd-plugins');
			$date_vars['monthNames'] = Array(__('January','emd-plugins'),__('February','emd-plugins'),__('March','emd-plugins'),__('April','emd-plugins'),__('May','emd-plugins'),__('June','emd-plugins'),__('July','emd-plugins'),__('August','emd-plugins'),__('September','emd-plugins'),__('October','emd-plugins'),__('November','emd-plugins'),__('December','emd-plugins'));
			$date_vars['monthNamesShort'] = Array(__('Jan','emd-plugins'),__('Feb','emd-plugins'),__('Mar','emd-plugins'),__('Apr','emd-plugins'),__('May','emd-plugins'),__('Jun','emd-plugins'),__('Jul','emd-plugins'),__('Aug','emd-plugins'),__('Sep','emd-plugins'),__('Oct','emd-plugins'),__('Nov','emd-plugins'),__('Dec','emd-plugins'));
			$date_vars['dayNames'] = Array(__('Sunday','emd-plugins'),__('Monday','emd-plugins'),__('Tuesday','emd-plugins'),__('Wednesday','emd-plugins'),__('Thursday','emd-plugins'),__('Friday','emd-plugins'),__('Saturday','emd-plugins'));
			$date_vars['dayNamesShort'] = Array(__('Sun','emd-plugins'),__('Mon','emd-plugins'),__('Tue','emd-plugins'),__('Wed','emd-plugins'),__('Thu','emd-plugins'),__('Fri','emd-plugins'),__('Sat','emd-plugins'));	
			$date_vars['dayNamesMin'] = Array(__('Su','emd-plugins'),__('Mo','emd-plugins'),__('Tu','emd-plugins'),__('We','emd-plugins'),__('Th','emd-plugins'),__('Fr','emd-plugins'),__('Sa','emd-plugins'));	
			$date_vars['weekHeader'] = __('Wk','emd-plugins');
		
			$vars['date'] = $date_vars;
			$vars['locale'] = $locale;	
			wp_enqueue_script( 'emd-mb-date', EMD_MB_JS_URL . 'date.js', $deps, EMD_MB_VER, true );
			wp_localize_script( 'emd-mb-date', 'vars', $vars);
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
				if(DateTime::createFromFormat('Y-m-d',$meta)){
                                	$meta = DateTime::createFromFormat('Y-m-d',$meta)->format(self::translate_format($field));
				}
                        }
			return sprintf(
				'<input type="text" class="emd-mb-date" name="%s" value="%s" id="%s" size="%s" data-options="%s" %s readonly/>',
				$field['field_name'],
				$meta,
				isset( $field['clone'] ) && $field['clone'] ? '' : $field['id'],
				$field['size'],
				esc_attr( json_encode( $field['js_options'] ) ),
				isset($field['data-cell']) ? "data-cell='{$field['data-cell']}'" : ''
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
				'dateFormat'      => empty( $field['format'] ) ? 'yy-mm-dd' : $field['format'],
				'showButtonPanel' => true,
				'changeMonth' => true,
				'changeYear' => true,
				'yearRange' => '-100:+10'
			) );

			return $field;
		}
	
                /**
                 * Returns a date() compatible format string from the JavaScript format
                 *
                 * @see http://www.php.net/manual/en/function.date.php
                 *
                 * @param array $field
                 *
                 * @return string
                 */
                static function translate_format( $field )
                {
                        return strtr( $field['js_options']['dateFormat'], self::$date_format_translation );
                }

                static function save( $new, $old, $post_id, $field )
                {
                        $name = $field['id'];
                        if ( '' === $new)
                        {
                                delete_post_meta( $post_id, $name );
                                return;
                        }
			if(DateTime::createFromFormat(self::translate_format($field), $new)){
                        	$new = DateTime::createFromFormat(self::translate_format($field), $new)->format('Y-m-d');
                        	update_post_meta( $post_id, $name, $new );
			}
                }
	}
}
