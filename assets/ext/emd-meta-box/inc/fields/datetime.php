<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'EMD_MB_Datetime_Field' ) )
{
	class EMD_MB_Datetime_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			$url_css = EMD_MB_CSS_URL . 'jqueryui';
			wp_register_script( 'jquery-ui-timepicker', EMD_MB_JS_URL . 'jqueryui/jquery-ui-timepicker-addon.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '0.9.7', true );
			wp_enqueue_style( 'jquery-ui-timepicker-css', "{$url_css}/jquery-ui-timepicker-addon.css");
			$deps = array( 'jquery-ui-datepicker', 'jquery-ui-timepicker' );

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

			$time_vars['timeOnlyTitle'] = __('Choose Time','emd-plugins');
			$time_vars['timeText'] = __('Time','emd-plugins');
			$time_vars['hourText'] = __('Hour','emd-plugins');
			$time_vars['minuteText'] = __('Minute','emd-plugins');
			$time_vars['secondText'] = __('Second','emd-plugins');
			$time_vars['millisecText'] = __('Millisecond','emd-plugins');
			$time_vars['timezoneText'] = __('Time Zone','emd-plugins');
			$time_vars['currentText'] = __('Now','emd-plugins');
			$time_vars['closeText'] = __('Done','emd-plugins');

                        $vars['date'] = $date_vars;
                        $vars['time'] = $time_vars;
                        $vars['locale'] = $locale;

			wp_enqueue_script( 'emd-mb-datetime', EMD_MB_JS_URL . 'datetime.js', $deps, EMD_MB_VER, true );
                        wp_localize_script( 'emd-mb-datetime', 'dtvars', $vars);
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
                                        $getformat = 'Y-m-d H:i';
                                }
                                else
                                {
                                        $getformat = 'Y-m-d H:i:s';
                                }
				if(DateTime::createFromFormat($getformat,$meta)){
                                	$meta = DateTime::createFromFormat($getformat,$meta)->format(self::translate_format($field));
				}
                        }
                        return sprintf(
                                '<input type="text" class="emd-mb-datetime" name="%s" value="%s" id="%s" size="%s" data-options="%s" readonly/>',
                                $field['field_name'],
                                $meta,
                                isset( $field['clone'] ) && $field['clone'] ? '' : $field['id'],
                                $field['size'],
                                esc_attr( json_encode( $field['js_options'] ) )
                        );
		}

		/**
		 * Calculates the timestamp from the datetime string and returns it
		 * if $field['timestamp'] is set or the datetime string if not
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return string|int
		 */
		/*static function value( $new, $old, $post_id, $field )
		{
			if ( !$field['timestamp'] )
				return $new;

			$d = DateTime::createFromFormat( self::translate_format( $field ), $new );
			return $d ? $d->getTimestamp() : 0;
		}*/

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
				'timestamp'  => false,
			) );

			// Deprecate 'format', but keep it for backward compatible
			// Use 'js_options' instead
			$field['js_options'] = wp_parse_args( $field['js_options'], array(
				'dateFormat'      => empty( $field['format'] ) ? 'yy-mm-dd' : $field['format'],
				'timeFormat'      => 'hh:mm:ss',
				'showButtonPanel' => true,
				'separator'       => ' ',
				'changeMonth' => true,
				'changeYear' => true,
				'yearRange' => '-100:+10',
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
			return strtr( $field['js_options']['dateFormat'], self::$date_format_translation )
				. $field['js_options']['separator']
				. strtr( $field['js_options']['timeFormat'], self::$time_format_translation );
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
                                $getformat = 'Y-m-d H:i';
                        }
                        else
                        {
                                $getformat = 'Y-m-d H:i:s';
                        }
			if(DateTime::createFromFormat(self::translate_format($field), $new)){
                        	$new = DateTime::createFromFormat(self::translate_format($field), $new)->format($getformat);
                        	update_post_meta( $post_id, $name, $new );
			}
                }
	}
}
