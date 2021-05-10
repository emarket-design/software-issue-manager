<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'EMD_MB_Map_Field' ) )
{
	class EMD_MB_Map_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			$map_key = 'AIzaSyBgJpne2GpL2w317poi8QuNxSosXj_YzdY';
			$misc_settings = get_option(EMD_MB_APP . '_misc_settings',Array());
			if(!empty($misc_settings) && !empty($misc_settings['google_mapkey'])){
				$map_key = $misc_settings['google_mapkey'];
			}	
			wp_register_script( 'googlemap', 'https://maps.googleapis.com/maps/api/js?key=' . $map_key, array(), EMD_MB_VER, true );
			wp_enqueue_style( 'emd-mb-map', EMD_MB_CSS_URL . 'map.css' );
			wp_enqueue_script( 'emd-mb-map', EMD_MB_JS_URL . 'map.js', array( 'jquery', 'jquery-ui-autocomplete', 'googlemap' ), EMD_MB_VER, true );
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
			$address = isset( $field['address_field'] ) ? $field['address_field'] : false;

			$html = '<div class="emd-mb-map-field">';

			$html .= sprintf(
				'<div class="emd-mb-map-canvas" data-default-loc="%s"></div>
				<input type="hidden" name="%s" class="emd-mb-map-coordinate" value="%s">',
				esc_attr( $field['std'] ),
				esc_attr( $field['field_name'] ),
				esc_attr( $meta )
			);

			if ( $address )
			{
				$html .= sprintf(
					'<button class="button emd-mb-map-goto-address-button" value="%s">%s</button>',
					is_array( $address ) ? implode( ',', $address ) : $address,
					__( 'Find Address', 'emd-plugins' )
				);
			}

			$html .= '</div>';

			return $html;
		}
		/**
		 * Get the field value
		 * The difference between this function and 'meta' function is 'meta' function always returns the escaped value
		 * of the field saved in the database, while this function returns more meaningful value of the field
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Not used for this field
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return mixed Array(latitude, longitude, zoom)
		 */
		static function get_value( $field, $args = array(), $post_id = null )
		{
			$value = parent::get_value( $field, $args, $post_id );
			list( $latitude, $longitude, $zoom ) = explode( ',', $value . ',,' );
			return compact( 'latitude', 'longitude', 'zoom' );
		}
		/**
		 * Output the field value
		 * Display Google maps
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Additional arguments. Not used for these fields.
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return mixed Field value
		 */
		static function the_value( $field, $args = array(), $post_id = null )
		{
			$value = self::get_value( $field, $args, $post_id );
			//var_dump($value);
			if ( ! $value['latitude'] || ! $value['longitude'] )
			{
				return '';
			}
			if ( ! $value['zoom'] )
			{
				$value['zoom'] = 14;
			}
			/**
			 * Enqueue scripts
			 * Note: We still can enqueue script which outputs in the footer
			 */
			$map_key = 'AIzaSyBgJpne2GpL2w317poi8QuNxSosXj_YzdY';
			$misc_settings = get_option($args['app'] . '_misc_settings',Array());
			if(!empty($misc_settings) && !empty($misc_settings['google_mapkey'])){
				$map_key = $misc_settings['google_mapkey'];
			}	
			wp_register_script( 'google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $map_key, array(), EMD_MB_VER, true );
			wp_enqueue_script( 'emd-mb-map-frontend', EMD_MB_JS_URL . 'map-frontend.js', array( 'jquery','google-maps' ), EMD_MB_VER, true );
			// Map parameters
			$args = wp_parse_args( $args, array(
				'latitude'     => $value['latitude'],
				'longitude'    => $value['longitude'],
				'width'        => '100%',
				'height'       => '480px',
				'marker'       => true, // Display marker?
				'marker_title' => '', // Marker title, when hover
				'info_window'  => '', // Content of info window (when click on marker). HTML allowed
				'js_options'   => array(),
			) );
			/**
			 * Google Maps options
			 * Option name is the same as specified in Google Maps documentation
			 * This array will be convert to Javascript Object and pass as map options
			 * @link https://developers.google.com/maps/documentation/javascript/reference
			 */
			$args['js_options'] = wp_parse_args( $args['js_options'], array(
				// Default to 'zoom' level set in admin, but can be overwritten
				'zoom'      => $args['zoom'],
				// Map type, see https://developers.google.com/maps/documentation/javascript/reference#MapTypeId
				'mapTypeId' => $args['mapTypeId'],
			) );

			$output = sprintf(
				'<style type="text/css" media="screen">
				/*<![CDATA[*/
				.gm-style img{ 
				max-width:none !important; 
				/*]]>*/} 
				</style>
				<div class="emd-mb-map-canvas" data-map_options="%s" style="width:%s;height:%s"></div>',
				esc_attr( wp_json_encode( $args ) ),
				esc_attr( $args['width'] ),
				esc_attr( $args['height'] )
			);
			return $output;
		}
		
	}
}
