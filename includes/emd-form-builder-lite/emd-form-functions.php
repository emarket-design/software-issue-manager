<?php
/**
 * Form Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
/**
 * Process search form and return layout
 *
 * @since WPAS 4.0
 * @param string $myapp
 * @param string $myentity
 * @param string $myform
 * @param string $myview
 * @param string $noresult_msg
 * @param string $path
 *
 * @return string $res_layout html
 */
if (!function_exists('emd_form_builder_lite_search_form')) {
	function emd_form_builder_lite_search_form($myapp, $fcontent) { 
		$layout = '';
		$myentity = $fcontent['entity'];
		$myform = $fcontent['name'];
		$args = Array();
		$search_fields = Array();
		$attrs = Array();
		$txns = Array();
		$rels = Array();
		$oprs = Array();
		$blts = Array();
		$rel_count = 0;
		$myattr_list = Array();
		$mytxn_list = Array();
		$myrel_list = Array();
		$attr_list = get_option($myapp . '_attr_list', Array());
		$txn_list = get_option($myapp . '_tax_list', Array());
		$rel_list = get_option($myapp . '_rel_list', Array());
		$cust_fields = Array();
		if(post_type_supports($myentity, 'custom-fields') == 1){
			$cust_fields = apply_filters('emd_get_cust_fields', $cust_fields, $myentity);
		}
		if (isset($attr_list[$myentity])) {
			$myattr_list = array_keys($attr_list[$myentity]);
		}
		if (isset($txn_list[$myentity])) {
			$mytxn_list = array_keys($txn_list[$myentity]);
		}
		if (isset($rel_list)) {
			$myrel_list = array_keys($rel_list);
		}
		if(!empty($_POST)){
			foreach ($_POST as $postkey => $postval) {
				if (!empty($postkey) && !is_array($postval)) {
					$postval = sanitize_text_field(urldecode($postval));
					$postval = html_entity_decode($postval);
				}
				if (!empty($postval)) {
					if (in_array($postkey, $mytxn_list)) {
						$txns[$postkey] = $postval;
					} elseif (in_array($postkey, $myrel_list)) {
						$rel_key = preg_replace("/rel_/", "", $postkey, 1);
						$rels[$rel_count] = Array(
							'key' => $rel_key,
							'val' => $postval
						);
						$rel_count++;
					} elseif (in_array($postkey, $myattr_list)) {
						$attrs[$postkey] = $postval;
					} elseif (preg_match('/^opr__/', $postkey)) {
						$opr_key = preg_replace("/opr__/", "", $postkey, 1);
						$oprs[$opr_key] = $postval;
					} elseif (in_array($postkey, Array(
						'blt_title',
						'blt_content',
						'blt_excerpt'
					))) {
						$blts[$postkey] = $postval;
					} elseif (!empty($cust_fields) && in_array($postkey, array_keys($cust_fields))) {
						$attrs[$cust_fields[$postkey]] = $postval;
					}
				}
			}
		}
		if (!empty($blts)) {
			foreach ($blts as $bltkey => $bltval) {
				$args[$bltkey] = $bltval;
				if (!empty($oprs) && isset($oprs[$myform . '_' . $bltkey])) {
					$args['opr__' . $bltkey] = emd_get_meta_operator($oprs[$myform . '_' . $bltkey]);
					$blts[$bltkey] = $oprs[$myform . '_' . $bltkey];			
				} else {
					//Change default to like
					//$args['opr__' . $bltkey] = "=";
					$args['opr__' . $bltkey] = "LIKE";
				}
			}
			$args['emd_blts'] = $blts;
		}
		$filter = "";
		if (!empty($attrs)) {
			foreach ($attrs as $key => $myattr) {
				if(!empty($myattr)){
					if(is_array($myattr)){
						foreach($myattr as $karr => $varr){
							if($varr == ''){
								unset($myattr[$karr]);
							}
						}
					}
					if(!empty($myattr)){
						if(!empty($cust_fields) && in_array($key,$cust_fields)){
							$filter.= "cattr::" . $key . "::";
							$opr_key = array_search($key,$cust_fields);
							if (!empty($oprs) && isset($oprs[$myform . '_' . $opr_key])) {
								$filter.= $oprs[$myform . '_'  . $opr_key];
							} else {
								$filter.= "is";
							}
						}
						else {	
							$filter.= "attr::" . $key . "::";
							if (!empty($oprs) && isset($oprs[$myform . '_' . $key])) {
								$filter.= $oprs[$myform . '_'  . $key];
							} else {
								$filter.= "is";
							}
						}
						if (is_array($myattr) && !empty($myattr)) {
							$filter.= "::" . implode(',', $myattr) . ";";
						} else {
							$filter.= "::" . $myattr . ";";
						}
					}
				}
			}
		}
		if (!empty($txns)) {
			foreach ($txns as $keytxn => $mytxn) {
				if (is_array($mytxn) && !empty($mytxn)) {
					$filter.= "tax::" . $keytxn . "::is::" . implode(",", $mytxn) . ";";
				} elseif (!empty($mytxn)) {
					$filter.= "tax::" . $keytxn . "::is::" . $mytxn . ";";
				}
			}
		}
		if (!empty($rels)) {
			foreach ($rels as $vrel) {
				if (is_array($vrel['val']) && !empty($vrel['val'])) {
					$filter.= "rel::" . $vrel['key'] . "::is::" . implode(',', $vrel['val']) . ";";
				} elseif (!empty($vrel['val'])) {
					$filter.= "rel::" . $vrel['key'] . "::is::" . $vrel['val'] . ";";
				}
			}
		}
		if(!empty($_POST) && empty($filter) && empty($args['emd_blts'])){
			if(!empty($fcontent['settings']['ajax_search'])){
				$layout .= '<div id="' . $myform . '_show_link" style="padding-top:10px;padding-bottom:20px;"><a href="#">
                        <span id="' . $myform . '_show_link_span" style="color:#fff;background-color:#5bc0de;border-color:#5bc0de;padding:1px 5px;font-size:12px;line-height:1.5;border-radius:3px;display:inline-block;margin-bottom:0;font-weight:normal;text-align:center;vertical-align:middle;touch-action:manipulation;cursor:pointer;background-image:none;border:1px solid rgba(0,0,0,0);white-space:nowrap;">'. __('Show Form','emd-plugins') . '</span></a></div>
                        <div id="' . $myform . '_hide_link" style="display:none;padding-top:10px;padding-bottom:20px;"><a href="#">
                        <span id="' . $myform . '_hide_link_span" style="color:#fff;background-color:#d9534f;border-color:#d43f3a;padding:1px 5px;font-size:12px;line-height:1.5;border-radius:3px;display:inline-block;margin-bottom:0;font-weight:normal;text-align:center;vertical-align:middle;touch-action:manipulation;cursor:pointer;background-image:none;border:1px solid rgba(0,0,0,0);white-space:nowrap;">' . __('Hide Form','emd-plugins') . '</span></a></div>';
			}
			return $layout;
		}
		$emd_query = new Emd_Query($myentity, $myapp);
		$emd_query->args_filter($filter);
		$args = array_merge($args,$emd_query->args);
		$args['post_type'] = $myentity;
		$myview = '';
		$paged = (get_query_var('pageno')) ? get_query_var('pageno') : 0;
		if($paged == '0'){
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 0;
		}
		if($paged != 0){
			$sess_name = strtoupper($myapp);
			$session_class = $sess_name();
			$sess_form_args = $session_class->session->get($myform . '_args');
			$args = $sess_form_args;
		}
		else{
			$paged = 1;	
		}	
		if (!empty($fcontent['settings']['display_records']) || !empty($attrs) || !empty($txns) || !empty($rels) || !empty($blts) || ($paged != 0 && !empty($sess_form_args))) {
			$fields['app'] = $myapp;
			$fields['form_name'] = $myform;
			if(!empty($fcontent['settings']['result_fields'])){
				$fields['form_res_fields'] = $fcontent['settings']['result_fields'];
			}
			$fields['res_templ'] = $fcontent['settings']['result_templ'];
			if($fields['res_templ'] == 'adv_table'){
				$adv_fields = Array('adv_search','adv_click','adv_show_col','adv_show_export','adv_show_toggle','adv_show_all','adv_page_size','adv_page_list','adv_maintain');
				foreach($adv_fields as $myadv){
					if(!empty($fcontent['settings'][$myadv])){
						$fields['adv_table'][$myadv] = $fcontent['settings'][$myadv];
					}
					else {
						$fields['adv_table'][$myadv] = false;
					}
				}
				if(!$fields['adv_table']['adv_show_all'] && empty($fields['adv_table']['adv_page_size'])){
					$fields['adv_table']['adv_page_size'] = 10;
				}
				if(!$fields['adv_table']['adv_show_all'] && empty($fields['adv_table']['adv_page_list'])){
					$fields['adv_table']['adv_page_list'] = '10, 20, 50, all';
				}
			}
			else if($fields['res_templ'] == 'cust_table'){
				$fields['view_name'] = $fcontent['settings']['view_name'];
			}
			$fields['has_pages'] = true;
			$fields['posts_per_page'] = 10;
			if(!empty($fcontent['settings']['ajax_search']) && (empty($fcontent['settings']['display_records']) || (!empty($fcontent['settings']['display_records']) && empty($_POST)))){
				$layout .= '<div id="' . $myform . '_show_link" style="padding-top:10px;padding-bottom:20px;"><a href="#">
                        <span id="' . $myform . '_show_link_span" style="color:#fff;background-color:#5bc0de;border-color:#5bc0de;padding:1px 5px;font-size:12px;line-height:1.5;border-radius:3px;display:inline-block;margin-bottom:0;font-weight:normal;text-align:center;vertical-align:middle;touch-action:manipulation;cursor:pointer;background-image:none;border:1px solid rgba(0,0,0,0);white-space:nowrap;">'. __('Show Form','emd-plugins') . '</span></a></div>
                        <div id="' . $myform . '_hide_link" style="display:none;padding-top:10px;padding-bottom:20px;"><a href="#">
                        <span id="' . $myform . '_hide_link_span" style="color:#fff;background-color:#d9534f;border-color:#d43f3a;padding:1px 5px;font-size:12px;line-height:1.5;border-radius:3px;display:inline-block;margin-bottom:0;font-weight:normal;text-align:center;vertical-align:middle;touch-action:manipulation;cursor:pointer;background-image:none;border:1px solid rgba(0,0,0,0);white-space:nowrap;">' . __('Hide Form','emd-plugins') . '</span></a></div>';
			}
			if(!empty($fcontent['settings']['display_records'])){
				$layout .= '<div class="emd-form-search-results">'; 
			}
			if($fcontent['settings']['result_templ'] == 'adv_table'){
				$fields['posts_per_page'] = -1;
				$fields['has_pages'] = false;
			}
			if($fcontent['settings']['result_templ'] == 'cust_table'){
				$cust_func_name = $myapp . '_' . $fields['view_name'] . '_set_shc';
				$res_layout = $cust_func_name('',$args,$fields['form_name'],$paged);
			}
			else {
				$res_layout =  emd_form_builder_lite_set_shc('', $args, $fields, $paged);
			}
			$layout .= "<div id='myview" . $myentity . "-results'>";
			if(empty($res_layout)){
				$layout .= "<div class='well text-danger' style='margin:10px 0;'>";
				$layout .= '<div class="text-danger">' . $fcontent['settings']['noresult_msg'] . '</div>';
				$layout .= "</div>";
			}
			else {
				$layout .= $res_layout;
			}
			if(!empty($fcontent['settings']['display_records'])){
				$layout .= '</div>';
			}
		}
		$emd_query->remove_filters();
		return $layout;
	}
}
/**
 * Process Submit form, insert ent, attribute and tax and rel values
 *
 * @since WPAS 4.0
 * @param string $app
 * @param string $fcontent
 *
 * @return array $ret
 */
if (!function_exists('emd_form_builder_lite_submit_form')) {
	function emd_form_builder_lite_submit_form($app, $fcontent) {
		$user_conf = Array();
		$entity_post = Array();
		$entity_fields = Array();
		$txn_fields = Array();
		$rel_fields = Array();
		$user_email_val = "";
		$myattr_list = Array();
		$mytxn_list = Array();
		$myrel_list = Array();
		$default_list = Array();
		$default_txns = Array();
		$myentity = $fcontent['entity'];
		$blts = Array(
			'blt_title' => '',
			'blt_content' => '',
			'blt_excerpt' => ''
		);
		if(!empty($fcontent['settings']['honeypot']) && !empty($_POST)){	
			$honey_pre = $fcontent['name'] . (int) $_POST['emd_step'] . '_';
			$honeys = Array($honey_pre . 'web_site',$honey_pre . 'url',$honey_pre . 'email',$honey_pre . 'company',$honey_pre . 'name',$honey_pre . 'phone', $honey_pre . 'twitter');
			foreach ($_POST as $postkey => $mypost) {
				if (in_array($postkey,$honeys) && !empty($mypost)) {
					return false;
				}
			}
		}
		$attr_list = get_option($app . '_attr_list', Array());
		$ent_list = get_option($app . '_ent_list', Array());
		$txn_list = get_option($app . '_tax_list', Array());
		$rel_list = get_option($app . '_rel_list', Array());
		$ret_attrs = emd_form_builder_lite_check_attr($fcontent['layout'],$fcontent['entity'],$ent_list,$attr_list,$txn_list,$rel_list);
		if (!empty($ret_attrs['file_attrs'])) {
			$sess_name = strtoupper($app);
			$session_class = $sess_name();
			$sess_uploads = $session_class->session->get('uploads');
			emd_form_builder_lite_set_file_upload($sess_uploads);
		}
		$cust_fields = Array();
		if(post_type_supports($myentity, 'custom-fields') == 1){
			$cust_fields = apply_filters('emd_get_cust_fields', $cust_fields, $myentity);
		}
		if(!empty($txn_list[$myentity])){
			foreach ($txn_list[$myentity] as $mykey => $mytxn) {
				if (!empty($mytxn['default'])) {
					$default_txns[$mykey] = $mytxn['default'];
				}
			}
		}
		if(!empty($attr_list[$myentity])){
			$current_user = wp_get_current_user();
			foreach ($attr_list[$myentity] as $mykey => $myattr) {
				if (isset($myattr['std'])) {
					$default_list[$mykey] = $myattr['std'];
				}
				if(!empty($current_user) && !empty($myattr['user_map'])){
					$umap = $myattr['user_map'];
					$default_list[$mykey] = (string)$current_user->$umap;	
				}
				if(!empty($current_user) && !empty($myattr['email']) && !empty($current_user->user_email)){
					$default_list[$mykey] =  (string)$current_user->user_email;
				}	
			}
		}
		if (isset($attr_list[$myentity])) {
			$myattr_list = array_keys($attr_list[$myentity]);
		}
		if (isset($txn_list[$myentity])) {
			$mytxn_list = array_keys($txn_list[$myentity]);
		}
		if (isset($rel_list)) {
			$myrel_list = array_keys($rel_list);
		}
		if(!empty($_POST)){
			foreach ($_POST as $postkey => $mypost) {
				if (isset($mypost) && $mypost != '') {
					if(is_array($mypost)){
						$mypost = array_map('wp_kses_post',$mypost);
						$mypost = array_map('html_entity_decode',$mypost);
					}
					else {
						$mypost = wp_kses_post($mypost);
						$mypost = html_entity_decode($mypost);
					}
				}
				if (isset($mypost) && $mypost != '') {
					if(!empty($cust_fields) && in_array($postkey,array_keys($cust_fields))){
						$entity_fields[$cust_fields[$postkey]] = $mypost;
					} elseif (in_array($postkey, $mytxn_list)) {
						if(is_array($mypost)){
							$txn_fields[$postkey] = array_map('sanitize_text_field',$mypost);
						}
						else {
							$txn_fields[$postkey] = sanitize_text_field($mypost);
						}
					} elseif (in_array($postkey, $myrel_list)) {
						$postkey = preg_replace("/rel_/", "", $postkey, 1);
						$rel_fields[$postkey] = $mypost;
						if (isset($user_conf['email_type']) && $user_conf['email_type'] == 'rel' && $user_conf['email_field'] != '') {
							$email_arr = explode("__rel__", $user_conf['email_field']);
							if ($email_arr[0] == $postkey) {
								if (is_array($mypost)) {
									foreach ($mypost as $mypostid) {
										$user_email_val[$mypostid] = get_post_meta($mypostid, $email_arr[1], true);
									}
								} else {
									$user_email_val[$mypost] = get_post_meta($mypost, $email_arr[1], true);
								}
							}
						}
					} elseif (in_array($postkey, $myattr_list)) {
						if(in_array($attr_list[$myentity][$postkey]['display_type'],Array('wysiwyg','textarea'))){
							$entity_fields[$postkey] = $mypost;
						}
						else {
							$entity_fields[$postkey] = sanitize_text_field($mypost);
						}
						if (isset($user_conf['email_type']) && $user_conf['email_type'] == 'ent' && $user_conf['email_field'] != '' && $postkey == $user_conf['email_field']) {
							$user_email_val[$postkey] = sanitize_text_field($mypost);
						}
					} elseif (in_array($postkey, Array(
						'blt_title',
					))) {
						$blts[$postkey] = sanitize_text_field($mypost);
					} elseif (in_array($postkey, Array(
						'blt_content',
						'blt_excerpt'
					))) {
						$blts[$postkey] = $mypost;
					}
				}
			}
		}
		$entity_post['post_type'] = $myentity;
		$published_cap = get_post_type_object($myentity)->cap->edit_published_posts;
		$current_user_id = get_current_user_id();

		if (current_user_can($published_cap)) {
			$entity_post['post_status'] = $fcontent['settings']['submit_status'];
			$entity_post['post_author'] = $current_user_id;
		}
		else {
			$entity_post['post_status'] = $fcontent['settings']['visitor_submit_status'];
			$entity_post['post_author'] = $current_user_id;
		}
		if (!empty($blts)) {
			foreach ($blts as $blt_key => $blt_val) {
				$key = str_replace("blt_", "post_", $blt_key);
				$entity_post[$key] = $blt_val;
			}
		}
		if (empty($entity_post['post_title'])) {
			$wpas_ent_list = get_option($app . '_ent_list');
			if (!empty($wpas_ent_list[$myentity]['class_title'])) {
				$class_title = $wpas_ent_list[$myentity]['class_title'];
				$new_title = '';
				$class_delimiter = apply_filters('emd_get_title_delimiter', " ", $app, $myentity);
				foreach ($class_title as $mykey) {
					if(isset($entity_fields[$mykey])){
						$new_title.= $entity_fields[$mykey] . $class_delimiter;
					}
				}
				$entity_post['post_title'] = rtrim($new_title,$class_delimiter);
				$blts['blt_title'] = $entity_post['post_title'];
			}
		}
		//first check if this entity is there
                $args = Array();
                $args['post_type'] = $entity_post['post_type'];
                $args['post_status'] = 'any';
                $args['fields'] = 'ids';
                foreach ($ent_list[$entity_post['post_type']]['unique_keys'] as $uniq_key) {
                        $unique_fields[] = $uniq_key;
                        if($uniq_key == 'blt_title'){
				$args['title'] = $entity_post['post_title'];
			}
                        else{
                                $args['meta_query'][] = Array('key' => $uniq_key, 'value' => $entity_fields[$uniq_key]);
                        }
                }
                if(empty($args['meta_query']) && !empty($entity_post['post_title'])){
			$id = emd_post_exists($entity_post['post_title'],$entity_post['post_type']);
                }
                else{
                        $post_exists = get_posts($args);
			if(!empty($post_exists)){
				$id = $post_exists[0];
			}
                }
		$new = 0;
		if(empty($id)){
			$new = 1;
			$id = wp_insert_post($entity_post);
			if(empty($entity_post['post_title'])){
				wp_update_post(Array('ID' => $id,'post_title'=>$id));
			}
		}
		else {
			$entity_post['ID'] = $id;
			wp_update_post($entity_post);
		}
		if (!empty($id)) {
			if (!empty($default_list)) {
				foreach ($default_list as $def_key => $def_value) {
					if (!in_array($def_key, array_keys($entity_fields))) {
						$def_value_arr = explode(",", $def_value);
						if (count($def_value_arr) > 1) {
							foreach ($def_value_arr as $dvalue) {
								$dvalue = rtrim($dvalue, "'");
								$dvalue = ltrim($dvalue, "'");
								if($new == 1){
									add_post_meta($id, $def_key, $dvalue);
								}
								else {
									update_post_meta($id, $def_key, $dvalue);
								}
							}
						} else {
							$def_value = rtrim($def_value, "'");
							$def_value = ltrim($def_value, "'");
							if($new == 1){
								add_post_meta($id, $def_key, $def_value);
							}
							else {
								update_post_meta($id, $def_key, $def_value);
							}
						}
					}
				}
			}
			$concat_arr = Array();
			foreach ($entity_fields as $meta_key => $meta_value) {
				if ($meta_value == 'emd_uid') {
					$meta_value = uniqid($id, false);
					if(preg_match('/emd_uid/',$entity_post['post_title'])){
						$new_title = preg_replace('/emd_uid/',$meta_value,$entity_post['post_title']);
						wp_update_post(Array('ID' => $id,'post_title'=>$new_title,'post_name' => $new_title));
					}
					if($new == 1){
						add_post_meta($id, $meta_key, $meta_value);
					}
					else {
						update_post_meta($id, $meta_key, $meta_value);
					}
				}
				elseif($meta_value == 'emd_autoinc'){
					$autoinc_start = $attr_list[$myentity][$meta_key]['autoinc_start'];
					$autoinc_incr = $attr_list[$myentity][$meta_key]['autoinc_incr'];
					$meta_value = get_option($meta_key . "_autoinc",$autoinc_start);
					if($meta_value < $autoinc_start){
						$meta_value = $autoinc_start;
					}
					else {
						$meta_value = $meta_value + $autoinc_incr;
					}
					if(preg_match('/emd_autoinc/',$entity_post['post_title'])){
						$entity_post['post_title'] = preg_replace('/emd_autoinc/',$meta_value,$entity_post['post_title']);
						wp_update_post(Array('ID' => $id,'post_title'=>$entity_post['post_title']));
					}
					update_option($meta_key . "_autoinc", $meta_value);
				}
				elseif($meta_value == 'emd_concat'){
					$concat_arr['key'] = $meta_key;
					$concat_arr['concat'] = $attr_list[$myentity][$meta_key]['concat_string'];
				}
				elseif(!in_array($attr_list[$myentity][$meta_key]['display_type'],Array('file','image','plupload_image','thickbox_image'))){
					if (is_array($meta_value)) {
						foreach ($meta_value as $mvalue) {
							if($new == 1){
								add_post_meta($id, $meta_key, $mvalue);
							}
							else {
								update_post_meta($id, $meta_key, $mvalue);
							}
						}
					} else {
						if(!empty($attr_list[$myentity][$meta_key])){
							$meta_value = emd_translate_date_format($attr_list[$myentity][$meta_key], $meta_value);
						}
						if($new == 1){
							add_post_meta($id, $meta_key, $meta_value);
						}
						else {
							update_post_meta($id, $meta_key, $meta_value);
						}
					}
				}
			}
			if(!empty($concat_arr)){
				$meta_value = emd_get_hidden_func('concat',$app,$concat_arr['concat'],$id);
				update_post_meta($id, $concat_arr['key'], $meta_value);	
				if($entity_post['post_title'] == 'emd_concat'){
					wp_update_post(Array('ID' => $id,'post_title'=>$meta_value,'post_name' => $meta_value));
				}
			}
			if (!empty($default_txns)) {
				foreach ($default_txns as $def_key => $def_value) {
					if (!in_array($def_key, array_keys($txn_fields))) {
						foreach ($def_value as $dvalue) {
							$def = get_term_by('name', $dvalue, $def_key);
							if (!empty($def)) {
								$new_def_value[] = $def->term_id;
							}
						}
						if(!empty($new_def_value)){
							wp_set_object_terms($id, $new_def_value, $def_key);
						}
					}
				}
			}
			if(!empty($txn_fields)){
				foreach ($txn_fields as $txn_key => $txn_value) {
					wp_set_object_terms($id, $txn_value, $txn_key);
				}
			}
			if(!empty($rel_fields)){
				foreach ($rel_fields as $rel_key => $rel_value) {
					if (!empty($rel_value)) {
						if (is_array($rel_value)) {
							foreach ($rel_value as $rvalue) {
								if(!empty($rvalue)){
									p2p_type($rel_key)->connect($rvalue, $id);
								}
							}
						} else {
							p2p_type($rel_key)->connect($rel_value, $id);
						}
					}
				}
			}
			if(!empty($_FILES)){
				foreach($_FILES as $key_upload => $uploads){
					if(in_array($key_upload,$myattr_list)){
						$emd_file_upload = wp_upload_dir();
						if (is_array($uploads)) {
							foreach ($uploads as $myfileupload) {
								if (isset($myfileupload['path']) && $myfileupload['error'] == 0) {
									if(!$myfileupload['type']){
										$filetype = wp_check_filetype(basename($myfileupload['name']));
										$pmtype = $filetype['type'];
									}
									else {
										$pmtype = $myfileupload['type'];
									}
									$guid = $emd_file_upload['url'] . '/' . $myfileupload['name'];
									$attachment = array(
										'post_mime_type' => $pmtype,
										'guid' => $guid,
										'post_title' => basename($myfileupload['name']) ,
										'post_content' => '',
										'post_status' => 'inherit',
									);
									require_once (EMD_ADMIN_DIR . '/includes/image.php');
									$insert_id = wp_insert_attachment($attachment, $myfileupload['path'], $id);
									if (!is_wp_error($insert_id)) {
										wp_update_attachment_metadata($insert_id, wp_generate_attachment_metadata($insert_id, $myfileupload['path']));
										// Save file ID in meta field
										add_post_meta($id, $key_upload, $insert_id, false);
									}
								}
							}
						}
					}
				}
			}
			if(!empty($fcontent['settings']['confirm_form'])){
				$next_form = get_post($fcontent['settings']['confirm_form']);
				$next_content = json_decode($next_form->post_content,true);
				$next_entity = $next_content['entity'];
				if(!empty($rel_list)){
					foreach($rel_list as $rkey => $rval){
						if($rval['from'] == $fcontent['entity'] && $rval['to'] == $next_entity){
							$rkey = preg_replace('/rel_/','',$rkey);
							$ret['hidden_rel'] = $rkey;
						}
						elseif($rval['to'] == $fcontent['entity'] && $rval['from'] == $next_entity){
							$rkey = preg_replace('/rel_/','',$rkey);
							$ret['hidden_rel'] = $rkey;
						}
					}
				}
			}
			$ret['id'] = $id;
			$ret['user_send_to'] = $user_email_val;
			$ret['rel_uniqs'] = $rel_fields;
			if (!empty($blts)) {
				$ret['blts'] = $blts;
			}
			$ret['blts']['post_author'] = $entity_post['post_author'];
			do_action('emd_after_form_submit',$app, $myentity, $fcontent['name'], $id);
			//do_action('emd_after_form_submit',$app, $myentity, $id);
			if (!empty($ret_attrs['file_attrs'])) {
				emd_form_builder_lite_unset_file_upload($session_class);
			}
			return $ret;
		}
		if (!empty($ret_attrs['file_attrs'])) {
			emd_form_builder_lite_unset_file_upload($session_class);
		}
		return false;
	}
}
if (!function_exists('emd_form_builder_lite_set_shc')) {
	function emd_form_builder_lite_set_shc($atts, $args = Array() , $myfields, $pageno = 1, $shc_page_count = 0){
		$form_name = $myfields['form_name'];
		$app = $myfields['app'];
		global $shc_count;
		if ($shc_page_count != 0) {
			$shc_count = $shc_page_count;
		} else {
			if (empty($shc_count)) {
				$shc_count = 1;
			} else {
				$shc_count++;
			}
		}
		$fields = Array(
			'app' => $app,
			'type' => 'search_res',
			'class' => $args['post_type'],
			'shc' => $myfields['res_templ'],
			'shc_count' => $shc_count,
			'form' => $form_name,
			'has_pages' => $myfields['has_pages'],
			'pageno' => $pageno,
			'pgn_class' => '',
			'theme' => 'bs',
			'hier' => 0,
			'hier_type' => 'ul',
			'hier_depth' => - 1,
			'hier_class' => '',
			'has_json' => 0,
			'form_res_fields' => $myfields['form_res_fields']
		);
		if(!empty($myfields['adv_table'])){
			$fields['adv_table'] = $myfields['adv_table'];
		}
		if(!empty($myfields['view_name'])){
			$fields['view_name'] = $myfields['view_name'];
		}
		$args_default = array(
			'posts_per_page' => $myfields['posts_per_page'],
			'post_status' => 'publish',
			'orderby' => 'date',
			'order' => 'DESC',
			'filter' => ''
		);
		return emd_shc_get_layout_list($atts, $args, $args_default, $fields);
	}
}
if (!function_exists('emd_form_builder_lite_cond_vars')) {
	function emd_form_builder_lite_cond_vars($layout,$fentity,$attr_list,$txn_list){
		$cond_arr= Array();
		foreach($layout as  $kpage => $mypage){
			foreach($mypage['rows'] as $myrow){
				foreach($myrow as $fcount => $field){
					foreach($field as $kfield => $cfield){
						if(!empty($attr_list[$fentity][$kfield]) && !empty($attr_list[$fentity][$kfield]['conditional'])){
							$cond_arr[$kpage][$kfield] = $attr_list[$fentity][$kfield]['conditional'];
							$cond_arr[$kpage][$kfield]['type'] = $attr_list[$fentity][$kfield]['display_type'];
						}
						elseif(!empty($txn_list[$fentity][$kfield]) && !empty($txn_list[$fentity][$kfield]['conditional'])){
							$cond_arr[$kpage][$kfield] = $txn_list[$fentity][$kfield]['conditional'];
							$cond_arr[$kpage][$kfield]['type'] = $txn_list[$fentity][$kfield]['display_type'];
						}
					}
				}
			}
		}
		return $cond_arr;
	}
}
if (!function_exists('emd_form_builder_lite_req_hide_vars')) {
	function emd_form_builder_lite_req_hide_vars($layout){
		$req_arr= Array();
		foreach($layout as  $kpage => $mypage){
			foreach($mypage['rows'] as $myrow){
				foreach($myrow as $fcount => $field){
					foreach($field as $kfield => $cfield){
						if(!empty($cfield['req'])){
							$req_arr[$kpage][] = $kfield;
						}
					}
				}
			}
		}
		$ret['req'] = $req_arr;
		return $ret;
	}
}
add_filter('emd_ext_parse_tags', 'emd_form_builder_lite_parse_tags', 10, 3 );
if (!function_exists('emd_form_builder_lite_parse_tags')) {
	function emd_form_builder_lite_parse_tags($message,$pid,$app){
		if(!empty($pid)){
			$mypost = get_post($pid);
			if(preg_match('/{' . $mypost->post_type . '_verify_link}/',$message)){
				//create user_verify_link
				 $base_url = add_query_arg(array(
						'emd_action' => 'verify',
						'id'    => $pid,
						'app' => $app,
				), untrailingslashit(home_url()));
				$hash = 'sha256';
				$secret = hash($hash, wp_salt());
				$args['secret'] = $secret;
				$url   = add_query_arg($args, $base_url);
				$parts = parse_url($url);
				$token = md5($parts['query']);
				$verify_link = add_query_arg('token', $token, $base_url);
				$new_message = preg_replace('/{' . $mypost->post_type . '_verify_link}/',$verify_link,$message);		
				return $new_message;
			}
		}
		return $message;
	}
}
add_action('init', 'emd_form_builder_lite_user_actions');

function emd_form_builder_lite_user_actions(){
	if(!empty($_GET['emd_action']) && $_GET['emd_action'] == 'verify' && !empty($_GET['id']) && !empty($_GET['app']) && !empty($_GET['token'])){
		//check if token is valid
		$parts = parse_url(add_query_arg(array()));
		wp_parse_str($parts['query'], $query_args);
		unset($query_args['token']);
		$base_url = add_query_arg($query_args, untrailingslashit(home_url()));
		$hash = 'sha256';
		$secret = hash($hash, wp_salt());
		$args['secret'] = $secret;
		$url = add_query_arg($args, $base_url);
		$parts = parse_url($url);
        	$token = md5($parts['query']);
		if($token == $_GET['token']){
			//verified, lets go to its 	
			$app = sanitize_text_field($_GET['app']);
			$ent_list = get_option($app . '_ent_list', Array());
			$id = (int) $_GET['id'];
			$entity = get_post_type($id);
			$user_key = $ent_list[$entity]['user_key'];
			wp_update_post(Array('ID'=>$id,'post_status'=>'publish'));
			$user_id = get_post_meta($id,$user_key,true);
			if(!empty($user_id)){
				update_user_meta($user_id, 'emd_status', 1);
				$new_user = get_user_by('id', $user_id);
				wp_set_auth_cookie($user_id);
				wp_set_current_user($user_id);
				do_action('wp_login', $new_user->user_login, get_userdata($user_id));
			}
			do_action('emd_after_verify_token',$app, $id);
			wp_redirect(get_permalink($id));
			exit;
		}
	}
}

function emd_lite_get_form_shc($fname){
	$forms = get_posts(Array(
		'post_type' => 'emd_form',
		's' => $fname,
		'posts_per_page' => '-1'
	));
	if(!empty($forms)){
		foreach($forms as $myform){
			$fcontent = json_decode($myform->post_content,true);
			if($fcontent['name'] == $fname){
				return $myform->ID;
			}
		}
	}
}
function emd_form_builder_lite_check_attr($layout,$fentity,$ent_list,$attr_list,$txn_list=Array(),$rel_list=Array(),$glob_list=Array()){
	$ret = Array();
	if(!empty($layout)){
		foreach($layout as $kpage => $mypage){
			foreach($mypage['rows'] as $myrow){
				foreach($myrow as $fcount => $field){
					foreach($field as $kfield => $cfield){
						if(!empty($attr_list[$fentity][$kfield]) && in_array($attr_list[$fentity][$kfield]['display_type'],Array('file','image','plupload_image','thickbox_image'))){
							$ret['file_attrs'][] = $kfield;
						}
						elseif(!empty($attr_list[$fentity][$kfield]) && in_array($attr_list[$fentity][$kfield]['display_type'],Array('date','datetime'))){
							$ret['date_attrs'][] = $kfield;
						}
						elseif(!empty($attr_list[$fentity][$kfield]) && $attr_list[$fentity][$kfield]['display_type'] == 'wysiwyg'){
							$ret['wysiwyg_attrs'][] = $kfield;
						}
						elseif(!empty($attr_list[$fentity][$kfield]) && in_array($attr_list[$fentity][$kfield]['display_type'],Array('select','select_advanced'))){
							$ret['select_attrs'][] = $kfield;
						}
						elseif(!empty($txn_list) && !empty($txn_list[$fentity][$kfield])){
							$ret['select_attrs'][] = $kfield;
						}
						elseif(!empty($rel_list) && !empty($rel_list[$kfield])){
							$ret['select_attrs'][] = $kfield;
						}
						elseif(!empty($glob_list) && !empty($glob_list[$kfield])){
							$ret['glob_attrs'][] = $kfield;
						}
						if(in_array($kfield,Array('blt_content','blt_excerpt'))){
							if(!empty($ent_list[$fentity]['req_blt']) && !empty($ent_list[$fentity]['req_blt'][$kfield])){
								$ret['wysiwyg_attrs'][] = $kfield;
							}
							if(!empty($ent_list[$fentity]['blt_list']) && !empty($ent_list[$fentity]['blt_list'][$kfield])){
								$ret['wysiwyg_attrs'][] = $kfield;
							}
						}
					}
				}
			}
		}
	}	
	return $ret;
}
function emd_form_builder_lite_set_file_upload($sess_uploads) {
	if (!empty($_FILES)) {
		foreach ($_FILES as $key_attr => $myfile) {
			if (!empty($sess_uploads) && !empty($sess_uploads[$key_attr])) {
				$_FILES[$key_attr] = $sess_uploads[$key_attr];
			} else {
				unset($_FILES[$key_attr]);
				$_FILES[$key_attr][0] = $myfile;
			}
		}
	}
	if (!empty($sess_uploads) && empty($_FILES)) {
		foreach ($sess_uploads as $key_attr => $files) {
			$_FILES[$key_attr] = $files;
		}
	}
}
function emd_form_builder_lite_unset_file_upload($session_class) {
	$sess_uploads = $session_class->session->get('uploads');
	if (!empty($sess_uploads)) {
		$session_class->session->set('uploads','');	
	}
}
function emd_form_builder_lite_search_results($fields,$type,$pid){
	$attr_list = get_option($fields['app'] . '_attr_list',Array());
	$txn_list = get_option($fields['app'] . '_tax_list', Array());
	$rel_list = get_option($fields['app'] . '_rel_list', Array());
	$ent_list = get_option($fields['app'] . '_ent_list', Array());
	if(!empty($fields['form_res_fields'])){
		foreach($fields['form_res_fields'] as $myfield){
			$new_myfield = $myfield;
			if (preg_match('/_nl$/', $myfield)) {
				$new_myfield = preg_replace('/_nl$/', '', $myfield);
			}
			if (in_array($myfield,Array('blt_title','blt_content','blt_excerpt'))) {
				if(!empty($ent_list[$fields['class']]['req_blt'][$myfield]['msg'])){
					$labels[] = $ent_list[$fields['class']]['req_blt'][$myfield]['msg'];
				}
				elseif(!empty($ent_list[$fields['class']]['blt_list'][$myfield])){
					$labels[] = $ent_list[$fields['class']]['blt_list'][$myfield];
				}
				if($type == 'content' && !empty($pid)){
					$rpost = get_post($pid);
					if($myfield == 'blt_title'){
						$results[] = '<a href="' . get_permalink($rpost) . '">' . $rpost->post_title . '</a>';
					}
					elseif($myfield == 'blt_content'){
						$results[] = $rpost->post_content;
					}
					elseif($myfield == 'blt_excerpt'){
						$results[] = $rpost->post_excerpt;
					}
				}
			}
			elseif(!empty($attr_list[$fields['class']][$myfield])){
				$labels[] = $attr_list[$fields['class']][$myfield]['label'];
				if($type == 'content' && !empty($pid)){
					if(!empty($attr_list[$fields['class']][$myfield]['uniqueAttr'])){
						$rpost = get_post($pid);
						$results[] = '<a href="' . get_permalink($rpost) . '">' . emd_mb_meta($myfield,Array(),$pid) . '</a>';
					}
					elseif(in_array($attr_list[$fields['class']][$myfield]['display_type'],Array('image','plupload_image','thickbox_image'))){
						$img_html = '<a title="' . $rpost->post_title . '" href="' . get_permalink($rpost) . '">';
						if(get_post_meta($pid, $myfield)){
							$sval = get_post_meta($pid, $myfield);
							$thumb = wp_get_attachment_image_src($sval[0], 'thumbnail');
							$img_html .= '<img class="emd-img thumb" src="' . $thumb[0] . '" width="' . $thumb[1] . '" height="' . $thumb[2] . '" alt="' . get_post_meta($sval[0], '_wp_attachment_image_alt', true) . '"/>';
						}
						$img_html .= '</a>';
						$results[] = $img_html;
					}
					else {	
						$results[] = emd_mb_meta($myfield,Array(),$pid);
					}
				}
			}
			elseif(!empty($txn_list[$fields['class']][$myfield])){
				$labels[] = $txn_list[$fields['class']][$myfield]['label'];
				if($type == 'content' && !empty($pid)){
					$results[] = emd_get_tax_vals($pid,$myfield);
				}
			}
			elseif(!empty($txn_list[$fields['class']][$new_myfield])){
				$labels[] = $txn_list[$fields['class']][$new_myfield]['label'];
				if($type == 'content' && !empty($pid)){
					$results[] = emd_get_tax_vals($pid,$new_myfield,1);
				}
			}
			elseif(!empty($rel_list[$myfield])){
				$rel = preg_replace('/rel_/','',$myfield);
				if($fields['class'] == $rel_list[$myfield]['from']){
					$labels[] = $rel_list[$myfield]['from_title'];
					if($type == 'content' && !empty($pid)){
						$connected = p2p_type($rel)->set_direction('to')->get_connected($pid, Array('posts_per_page' => -1,'fields'=>'ids'));
					}
				}
				else {
					$labels[] = $rel_list[$myfield]['to_title'];
					if($type == 'content' && !empty($pid)){
						$connected = p2p_type($rel)->get_connected($pid, Array('posts_per_page' => -1,'fields'=>'ids'));
					}
				}
				if(!empty($connected->posts)){
					$myres = '<div class="' . str_replace("_","-",$rel) . '-wrap">';
					foreach($connected->posts as $myrel){
						$myres .= '<div style="margin:2px;display:block;" class="' . str_replace("_","-",$rel) . '">';
						$myres .= '<a href="' . get_permalink($myrel) . '" target="_blank">' . get_the_title($myrel) . '</a>';
						$myres .= '</div>';
					}
					$myres .= '</div>';
					$results[] = $myres;
				}
				else {
					$results[] = '';
				}
			}
		}
	}
	if($fields['shc'] == 'simple_table'){
		if($type == 'header'){
			$ret = "<table id='" . $fields['form'] . "_results' class='emd-simple-table' data-toggle='table'><thead><tr>";
			foreach($labels as $mylabel){
				$ret .= "<th>" . $mylabel . "</th>";
			}
			$ret .= "</tr></thead><tbody>";
		}
		elseif($type == 'content'){
			$ret = "<tr>";
			foreach($results as $kres => $myres){
				$ret .= "<td data-label='" . $labels[$kres] . "'>" . $myres . "</td>";
			}
			$ret .= "</tr>";
		}
		elseif($type == 'footer'){
			$ret = '</tbody></table>';
		}
		echo $ret;
	}
	elseif($fields['shc'] == 'adv_table'){
		if($type == 'header'){
			$adv_pagination = true;
			if($fields['adv_table']['adv_show_all']){
				$adv_pagination = false;
			}
			$ret = '<div class="btn-group emd-table-toolbar" id="' . $fields['form'] . '-toolbar">';
			if($fields['adv_table']['adv_show_export']){
				$ret .= '<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fa fa-fw fa-share"></i><span class="emd-table-export" id="' . $fields['form'] . '-export"></span> <span class="caret"></span> </button>
				<ul class="dropdown-menu">
				<li data-type="">
				<a href="#"><i class="fa fa-fw fa-check"></i>' . __('Current Page', 'emd-plugins') . '</a>
				</li>
				<li data-type="all">
				<a href="#"><i class="fa fa-fw"></i>' . __('All', 'emd-plugins') . '</a>
				</li>
				<li data-type="selected">
				<a href="#"><i class="fa fa-fw"></i>' . __('Selected', 'emd-plugins') . '</a>
				</li>
				</ul>';
			}
			$ret .= '</div>
				<table id="table-' . $fields['form'] . '" class="table emd-table" data-toggle="table" data-toolbar="#' . $fields['form'] . '-toolbar" data-search="' . $fields['adv_table']['adv_search'] . '" data-click-to-select="' . $fields['adv_table']['adv_select'] . '" data-show-columns="' . $fields['adv_table']['adv_show_col'] . '" data-show-export="' . $fields['adv_table']['adv_show_export'] . '" data-show-toggle="' . $fields['adv_table']['adv_show_toggle'] . '" data-pagination="' . $adv_pagination . '" data-page-size="' . $fields['adv_table']['adv_page_size'] . '" data-page-list="' . $fields['adv_table']['adv_page_list'] . '" data-maintain-selected="' . $fields['adv_table']['adv_maintain'] . '">
				<thead>
				<tr>
				<th data-field="state" data-checkbox="true"></th>';
			foreach($labels as $mylabel){
				$ret .= '<th data-sortable="false">' . $mylabel . '</th>';
			}
			$ret .= "</tr></thead>";
		}
		elseif($type == 'content'){
			$ret = "<tr><td></td>";
			foreach($results as $kres => $myres){
				$ret .= "<td data-label='" . $labels[$kres] . "'>" . $myres . "</td>";
			}
			$ret .= "</tr>";
		}
		elseif($type == 'footer'){
			$ret = '</table>';
		}
		echo $ret;
	}
	elseif($fields['shc'] == 'cust_table'){
		if($type == 'header'){
			emd_get_template_part($fields['app'], 'shc', str_replace('_', '-', $fields['view_name']) . "-header");
		}
		elseif($type == 'content'){
			emd_get_template_part($fields['app'], 'shc', str_replace('_', '-', $fields['view_name']) . "-content");
		}
		elseif($type == 'footer'){
			emd_get_template_part($fields['app'], 'shc', str_replace('_', '-', $fields['view_name']) . "-footer");
		}
	}
}
add_action('wp_ajax_emd_form_builder_lite_pagenum', 'emd_form_builder_lite_pagenum');
add_action('wp_ajax_nopriv_emd_form_builder_lite_pagenum', 'emd_form_builder_lite_pagenum');
function emd_form_builder_lite_pagenum() {
	check_ajax_referer('emd_form', 'nonce');
	$response = false;
	$pageno = isset($_GET['pageno']) ? (int) $_GET['pageno'] : 1;
	$myentity = isset($_GET['entity']) ? sanitize_text_field($_GET['entity']) : '';
	$myview = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : '';
	$myapp = isset($_GET['app']) ? sanitize_text_field($_GET['app']) : '';
	$myform = isset($_GET['form']) ? sanitize_text_field($_GET['form']) : '';
	$sess_name = strtoupper($myapp);
	$session_class = $sess_name();
	$sess_form_args = $session_class->session->get($myform . '_args');
	if (!empty($myentity) && !empty($myform) && !empty($sess_form_args)) {
		$fields['app'] = $myapp;
		$fields['form_name'] = $myform;
		$form_posts = get_posts(Array('post_type' => 'emd_form', 's' => $myform, 'posts_per_page' => '-1'));
		if(!empty($form_posts)){
			foreach($form_posts as $myform_post){
				$fcontent = json_decode($myform_post->post_content,true);
				if($fcontent['name'] == $myform && $myapp == $fcontent['app']){
					$fields['form_res_fields'] = $fcontent['settings']['result_fields'];
					$fields['res_templ'] = $fcontent['settings']['result_templ'];
					$fields['posts_per_page'] = 10;
					if($fields['res_templ'] == 'adv_table'){
						$fields['has_pages'] = false;
						$fields['posts_per_page'] = -1;
					}
					else {
						$fields['has_pages'] = true;
					}
					$fields['view_name'] = $myview;
					if($fcontent['settings']['result_templ'] == 'cust_table'){
						$cust_func_name = $myapp . '_' . $fields['view_name'] . '_set_shc';
						$response = $cust_func_name('',$sess_form_args,$fields['form_name'],$pageno);
					}
					else {
						$response =  emd_form_builder_lite_set_shc('', $sess_form_args, $fields, $pageno);
					}
				}
			}
		}
	}
	echo $response;
	die();
}
function emd_form_lite_builtin_posts_where($where, $wp_query) {
	if(!empty($wp_query->query['emd_blts'])){
		$blts = $wp_query->query['emd_blts'];
		global $wpdb;
		foreach ($blts as $bltkey => $bltval) {
			$key = str_replace('blt_', '', $bltkey);
			$value = esc_sql($wp_query->get($bltkey));	
			$where.= ' AND ' . $wpdb->posts . '.post_' . $key . ' ' . $wp_query->get('opr__' . $bltkey) . ' \'';
			if ($wp_query->get('opr__' . $bltkey) == 'LIKE' || $wp_query->get('opr__' . $bltkey) == 'NOT LIKE') {
				$where.= '%' . $value . '%';
			}
			elseif($wp_query->get('opr__' . $bltkey) == 'REGEXP'){
				switch($bltval){
					case 'begins':
						$value = '^' . $value;
						break;
					case 'ends':
						$value = $value . '$';
						break;
					case 'word':
						$value = '[[:<:]]' . $value . '[[:>:]]';
						break;
				}	
				$where .= $value;
			}
			$where.= '\'';
		}
	}
	return $where;
}
add_filter('kses_allowed_protocols', 'emd_form_lite_allow_data_protocol_urls', 10);
function emd_form_lite_allow_data_protocol_urls($allowed_protocols) {
	return array_merge($allowed_protocols, array('data'));
}
