<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EMD_MB_Hidden_Field' ) )
{
	class EMD_MB_Hidden_Field extends EMD_MB_Field
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
			global $post;
                        if(!empty($field['hidden_func']))
                        {
				if(!empty($field['no_update']) && $field['no_update'] == 1 && !empty($meta))
				{ 
					//don't do anything
                                	$val = $meta;
				}
				else
				{	
					if(empty($meta)){
						$cstring = '';
                                                if(!empty($field['concat_string'])){
                                                        $cstring = $field['concat_string'];
                                                	$val = emd_get_hidden_func($field['hidden_func'],$field['app'],$cstring,$post->ID);
                                                }
						else {
							$val = emd_get_hidden_func($field['hidden_func'],$field['app']);
						}
						switch ($val) {
							case 'emd_uid':
								$val = uniqid($post->ID,false);
								break;
							case  'emd_autoinc':
								$val = get_option($field['id'] . "_autoinc",$field['autoinc_start']);
								if($val < $field['autoinc_start']){
									$val = $field['autoinc_start'];
								}
								else {
									$val = $val + $field['autoinc_incr'];
								}
								break;
						}
					}
					else {
						if(!empty($field['concat_string'])){
                                                        $val = emd_get_hidden_func($field['hidden_func'],$field['app'],$field['concat_string'],$post->ID);
                                                }
                                                else {
                                                        $val = $meta;
                                                }
					}
				}
                        }
			elseif(!$meta && isset($field['std']))
			{
				$val = $field['std'];
			}
			else
			{
				$val = $meta;
			}

                        return sprintf(
                                '<input type="hidden" class="emd-mb-hidden" name="%s" id="%s" value="%s" />',
                                $field['field_name'],
                                $field['id'],
                                $val
                        );
		}
		static function save( $new, $old, $post_id, $field)
		{
			$name = $field['id'];
			update_post_meta($post_id, $name, $new);
			if(isset($field['hidden_func']) && $field['hidden_func'] == 'autoinc'){
				$val = get_option($field['id'] . "_autoinc",$field['autoinc_start']);
				if($new > $val){
					update_option($name . "_autoinc", $new);
				}
			}
		}
	}
}
