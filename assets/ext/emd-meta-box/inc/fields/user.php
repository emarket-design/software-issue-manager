<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "select" field is loaded
require_once EMD_MB_FIELDS_DIR . 'select-advanced.php';

if ( !class_exists( 'EMD_MB_User_Field' ) )
{
	class EMD_MB_User_Field extends EMD_MB_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			EMD_MB_Select_Advanced_Field::admin_enqueue_scripts();
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
			$field['options'] = self::get_options( $field );
			switch ( $field['field_type'] )
			{
				case 'select':
					return EMD_MB_Select_Field::html( $meta, $field );
					break;
				case 'select_advanced':
				default:
					return EMD_MB_Select_Advanced_Field::html( $meta, $field );
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
			
			$default_post_type = __( 'User', 'emd-plugins' );
			
			$field = wp_parse_args( $field, array(
				'field_type' => 'select_advanced',
				'parent'     => false,
				'query_args' => array()
			) );

			$field['std'] = empty( $field['std'] ) ? sprintf( __( 'Select a %s', 'emd-plugins' ), $default_post_type ) : $field['std'];

			$field['query_args'] = wp_parse_args( $field['query_args'], array(
				'orderby'       => 'display_name',
				'order'         => 'asc',
				'role'          => '',
				'fields'        => 'all'
			) );

			switch ( $field['field_type'] )
			{
				case 'select':
					return EMD_MB_Select_Field::normalize_field( $field );
					break;
				case 'select_advanced':
				default:
					return EMD_MB_Select_Advanced_Field::normalize_field( $field );
			}
		}

		/**
		 * Get meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries (for backward compatibility)
		 *
		 * @see "save" method for better understanding
		 *
		 * @param $post_id
		 * @param $saved
		 * @param $field
		 *
		 * @return array
		 */
		static function meta( $post_id, $saved, $field )
		{
			if ( isset( $field['parent'] ) && $field['parent'] )
			{
				$post = get_post( $post_id );
				return $post->post_parent;
			}
			return EMD_MB_Select_Field::meta( $post_id, $saved, $field );
		}

		/**
		 * Save meta value
		 * If field is cloneable, value is saved as a single entry in DB
		 * Otherwise value is saved as multiple entries (for backward compatibility)
		 *
		 * TODO: A good way to ALWAYS save values in single entry in DB, while maintaining backward compatibility
		 *
		 * @param $new
		 * @param $old
		 * @param $post_id
		 * @param $field
		 */
		static function save( $new, $old, $post_id, $field )
		{
			return EMD_MB_Select_Field::save( $new, $old, $post_id, $field );
		}

		/**
		 * Get users
		 *
		 * @param array $field
		 *
		 * @return array
		 */
		static function get_options( $field )
		{
			if(!empty($field['query_args']['role']) && is_array($field['query_args']['role']))
                        {
                                $froles = $field['query_args']['role'];
                                $field['query_args']['role'] = $froles[0];
                                $results = get_users( $field['query_args'] );
                                for($i=1;$i<count($froles);$i++)
                                {
                                        $field['query_args']['role'] = $froles[$i];
                                        $results = array_merge($results,get_users( $field['query_args'] ));
                                }
                        }
                        else
                        {
                                $results = get_users( $field['query_args'] );
                        }
			$options = array();
			$role_labels = get_option($field['app'] . '_cust_roles',Array());
			foreach ( $results as $result )
			{
				$options[$result->ID] = $result->display_name;
				foreach($result->roles as $rrole){
					if(in_array($rrole,$froles) && !empty($role_labels) && !empty($role_labels[$rrole])){
						$options[$result->ID] .= ' - (' . $role_labels[$rrole] . ')';
						break;
					}
				}
			}
			return $options;
		}
	}
}
