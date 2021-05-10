<?php
if ( !class_exists( 'EMD_MB_Field ' ) )
{
	class EMD_MB_Field
	{
		/**
		 * Add actions
		 *
		 * @return void
		 */
		static function add_actions() {}

		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts() {}

		/**
		 * Show field HTML
		 *
		 * @param array $field
		 * @param bool  $saved
		 *
		 * @return string
		 */
		static function show( $field, $saved )
		{
			global $post;

			$field_class = EMD_Meta_Box::get_class_name( $field );
			$meta = call_user_func( array( $field_class, 'meta' ), $post->ID, $saved, $field );
			$begin = "";
			$end = "";
			// Apply filter to field meta value
			// 1st filter applies to all fields
			// 2nd filter applies to all fields with the same type
			// 3rd filter applies to current field only
			$meta = apply_filters( 'emd_mb_field_meta', $meta, $field, $saved );
			$meta = apply_filters( "emd_mb_{$field['type']}_meta", $meta, $field, $saved );
			$meta = apply_filters( "emd_mb_{$field['id']}_meta", $meta, $field, $saved );

			$type = $field['type'];
			$id   = $field['id'];

			if(!empty($field['visible'])){
				$begin = call_user_func( array( $field_class, 'begin_html' ), $meta, $field );
			}
			// Apply filter to field begin HTML
			// 1st filter applies to all fields
			// 2nd filter applies to all fields with the same type
			// 3rd filter applies to current field only
			$begin = apply_filters( 'emd_mb_begin_html', $begin, $field, $meta );
			$begin = apply_filters( "emd_mb_{$type}_begin_html", $begin, $field, $meta );
			$begin = apply_filters( "emd_mb_{$id}_begin_html", $begin, $field, $meta );

			// Separate code for cloneable and non-cloneable fields to make easy to maintain

			// Cloneable fields
			if ( $field['clone'] )
			{
				$field_html = '';
				$meta = (array) $meta;

				foreach ( $meta as $index => $sub_meta )
				{
					$sub_field = $field;
					$sub_field['field_name'] = $field['field_name'] . "[{$index}]";
					if ($index>0) {
						if (isset( $sub_field['address_field'] )) 
							$sub_field['address_field'] = $field['address_field'] . "_{$index}";
						$sub_field['id'] = $field['id'] . "_{$index}";
					}
					if ( $field['multiple'] )
						$sub_field['field_name'] .= '[]';

					// Wrap field HTML in a div with class="rwmb-clone" if needed
					$class = "emd-mb-clone emd-mb-{$field['type']}-clone";
					if ( $field['sort_clone'] )
					{
						$class .= ' emd-mb-sort-clone';
					}
					$input_html = "<div class='$class'>";


					// Drag clone icon
					if ( $field['sort_clone'] )
						$input_html .= "<a href='javascript:;' class='emd-mb-clone-icon'></a>";

					// Call separated methods for displaying each type of field
					$input_html .= call_user_func( array( $field_class, 'html' ), $sub_meta, $sub_field );

					// Apply filter to field HTML
					// 1st filter applies to all fields with the same type
					// 2nd filter applies to current field only
					$input_html = apply_filters( "emd_mb_{$type}_html", $input_html, $field, $sub_meta );
					$input_html = apply_filters( "emd_mb_{$id}_html", $input_html, $field, $sub_meta );

					// Remove clone button
					$input_html .= call_user_func( array( $field_class, 'remove_clone_button' ), $sub_field );

					$input_html .= '</div>';

					$field_html .= $input_html;
				}
			}
			// Non-cloneable fields
			else
			{
				if(!empty($field['visible'])){
					// Call separated methods for displaying each type of field
					$field_html = call_user_func( array( $field_class, 'html' ), $meta, $field );
				}
				else {
					$field_html = "";
				}
				// Apply filter to field HTML
				// 1st filter applies to all fields with the same type
				// 2nd filter applies to current field only
				$field_html = apply_filters( "emd_mb_{$type}_html", $field_html, $field, $meta );
				$field_html = apply_filters( "emd_mb_{$id}_html", $field_html, $field, $meta );
			}

			if(!empty($field['visible'])){
				$end = call_user_func( array( $field_class, 'end_html' ), $meta, $field );
			}
			// Apply filter to field end HTML
			// 1st filter applies to all fields
			// 2nd filter applies to all fields with the same type
			// 3rd filter applies to current field only
			$end = apply_filters( 'emd_mb_end_html', $end, $field, $meta );
			$end = apply_filters( "emd_mb_{$type}_end_html", $end, $field, $meta );
			$end = apply_filters( "emd_mb_{$id}_end_html", $end, $field, $meta );

			//wpas-change--- Move classes before html
			$classes = array( 'emd-mb-field', "emd-mb-{$type}-wrapper" );
			if ( 'hidden' === $field['type'] )
				$classes[] = 'hidden';
			if ( !empty( $field['required'] ) )
				$classes[] = 'required';
			if ( !empty( $field['class'] ) )
				$classes[] = $field['class'];
		
			$field_classes = implode( ' ', $classes );
			if(!empty($field['visible'])){
				$field_html = '<div class="' . $field_classes . '">' . $field_html . '</div>';
			}
			//wpas-change	

			// Apply filter to field wrapper
			// This allow users to change whole HTML markup of the field wrapper (i.e. table row)
			// 1st filter applies to all fields with the same type
			// 2nd filter applies to current field only
			$html = apply_filters( "emd_mb_{$type}_wrapper_html", "{$begin}{$field_html}{$end}", $field, $meta );
			$html = apply_filters( "emd_mb_{$id}_wrapper_html", $html, $field, $meta );

			printf('%s',$html);
		}

		/**
		 * Get field HTML
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			return '';
		}

		/**
		 * Show begin HTML markup for fields
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function begin_html( $meta, $field )
		{
			if ( empty( $field['name'] ) )
				return '<div class="emd-mb-input">';

			$data_max_clone = '';
			if ( is_numeric( $field['max_clone'] ) && $field['max_clone'] > 1 )
			{
				$data_max_clone .= ' data-max-clone=' . $field['max_clone'];
			}
			return sprintf(
				'<div class="emd-mb-label">
					<label for="%s">%s</label>
				</div>
				<div class="emd-mb-input"%s>',
				$field['id'],
				$field['name'],
				$data_max_clone
			);
		}

		/**
		 * Show end HTML markup for fields
		 *
		 * @param mixed $meta
		 * @param array $field
		 *
		 * @return string
		 */
		static function end_html( $meta, $field )
		{
			$id = $field['id'];

			$button = $field['clone'] ? call_user_func( array( EMD_Meta_Box::get_class_name( $field ), 'add_clone_button' ), $field ) : '';

			$desc = !empty( $field['desc'] ) ? "<p id='{$id}_description' class='description'>{$field['desc']}</p>" : '';

			// Closes the container
			$html = "{$button}{$desc}</div>";

			return $html;
		}

		/**
		 * Add clone button
		 *
		 * @param array $field Field parameter
		 *
		 * @return string $html
		 */
		static function add_clone_button( $field )
		{
			$text = apply_filters( 'emd_mb_add_clone_button_text', __( '+ Add more', 'meta-box' ), $field );
			return "<a href='#' class='emd-mb-button button-primary add-clone'>$text</a>";
		}

		/**
		 * Remove clone button
		 *
		 * @param array $field Field parameter
		 *
		 * @return string $html
		 */
		static function remove_clone_button( $field )
		{
			$icon = '<i class="dashicons dashicons-minus"></i>';
			$text = apply_filters( 'emd_mb_remove_clone_button_text', $icon, $field );
			return "<a href='#' class='emd-mb-button remove-clone'>$text</a>";
		}

		/**
		 * Get meta value
		 *
		 * @param int   $post_id
		 * @param bool  $saved
		 * @param array $field
		 *
		 * @return mixed
		 */
		static function meta( $post_id, $saved, $field )
		{
			$single = $field['clone'] || ! $field['multiple'];
			$meta = get_post_meta( $post_id, $field['id'], $single );

			// Use $field['std'] only when the meta box hasn't been saved (i.e. the first time we run)
			$meta = ( !$saved && '' === $meta || array() === $meta ) ? $field['std'] : $meta;

			// Escape attributes for non-wysiwyg fields
			if ( 'wysiwyg' !== $field['type'] )
				$meta = is_array( $meta ) ? array_map( 'esc_attr', $meta ) : esc_attr( $meta );

			$meta = apply_filters( "emd_mb_{$field['type']}_meta", $meta );
			$meta = apply_filters( "emd_mb_{$field['id']}_meta", $meta );

			return $meta;
		}

		/**
		 * Set value of meta before saving into database
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
			return $new;
		}

		/**
		 * Save meta value
		 *
		 * @param $new
		 * @param $old
		 * @param $post_id
		 * @param $field
		 */
		static function save( $new, $old, $post_id, $field )
		{
			$name = $field['id'];

			if ( '' === $new || array() === $new )
			{
				delete_post_meta( $post_id, $name );
				return;
			}

			if ( $field['multiple'] )
			{
				foreach ( $new as $new_value )
				{
					if ( !in_array( $new_value, $old ) )
						add_post_meta( $post_id, $name, $new_value, false );
				}
				foreach ( $old as $old_value )
				{
					if ( !in_array( $old_value, $new ) )
						delete_post_meta( $post_id, $name, $old_value );
				}
			}
			else
			{
				if ( $field['clone'] )
				{
					$new = (array) $new;
					foreach ( $new as $k => $v )
					{
						if ( '' === $v )
							unset( $new[$k] );
					}
				}
				update_post_meta( $post_id, $name, $new );
			}
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
			return $field;
		}
	
		// Missing: 't' => '', T' => '', 'm' => '', 's' => ''
                static $time_format_translation = array(
                        'H'  => 'H', 'HH' => 'H', 'h' => 'H', 'hh' => 'H',
                        'mm' => 'i', 'ss' => 's', 'l' => 'u', 'tt' => 'a', 'TT' => 'A'
                );

                // Missing:  'o' => '', '!' => '', 'oo' => '', '@' => '', "''" => "'"
                static $date_format_translation = array(
                        'd' => 'j', 'dd' => 'd', 'oo' => 'z', 'D' => 'D', 'DD' => 'l',
                        'm' => 'n', 'mm' => 'm', 'M' => 'M', 'MM' => 'F', 'y' => 'y', 'yy' => 'Y'
                );

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
			return $field;
                }
		/**
		 * Get the field value
		 * The difference between this function and 'meta' function is 'meta' function always returns the escaped value
		 * of the field saved in the database, while this function returns more meaningful value of the field, for ex.:
		 * for file/image: return array of file/image information instead of file/image IDs
		 *
		 * Each field can extend this function and add more data to the returned value.
		 * See specific field classes for details.
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Additional arguments. Rarely used. See specific fields for details
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return mixed Field value
		 */
		static function get_value( $field, $args = array(), $post_id = null )
		{
			if ( ! $post_id )
				$post_id = get_the_ID();
			/**
			 * Get raw meta value in the database, no escape
			 * Very similar to self::meta() function
			 */
			/**
			 * For special fields like 'divider', 'heading' which don't have ID, just return empty string
			 * to prevent notice error when display in fields
			 */
			$value = '';
			if ( ! empty( $field['id'] ) )
			{
				$single = $field['clone'] || ! $field['multiple'];
				$value  = get_post_meta( $post_id, $field['id'], $single );
				// Make sure meta value is an array for clonable and multiple fields
				if ( $field['clone'] || $field['multiple'] )
				{
					$value = is_array( $value ) && $value ? $value : array();
				}
			}
			/**
			 * Return the meta value by default.
			 * For specific fields, the returned value might be different. See each field class for details
			 */
			return $value;
		}
		/**
		 * Output the field value
		 * Depends on field value and field types, each field can extend this method to output its value in its own way
		 * See specific field classes for details.
		 *
		 * Note: we don't echo the field value directly. We return the output HTML of field, which will be used in
		 * emd_mb_the_field function later.
		 *
		 * @use self::get_value()
		 * @see emd_mb_the_field()
		 *
		 * @param  array    $field   Field parameters
		 * @param  array    $args    Additional arguments. Rarely used. See specific fields for details
		 * @param  int|null $post_id Post ID. null for current post. Optional.
		 *
		 * @return string HTML output of the field
		 */
		static function the_value( $field, $args = array(), $post_id = null )
		{
			$value  = call_user_func( array( EMD_Meta_Box::get_class_name( $field ), 'get_value' ), $field, $args, $post_id );
			$output = $value;
			if ( is_array( $value ) )
			{
				$output = '<ul>';
				foreach ( $value as $subvalue )
				{
					$output .= '<li>' . $subvalue . '</li>';
				}
				$output .= '</ul>';
			}
			return $output;
		}
	}
}
