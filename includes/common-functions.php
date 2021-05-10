<?php
/**
 * Common Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
global $wp_version;
/**
 * Generates hidden func value
 *
 * @since WPAS 4.0
 * @param string $hidden_func
 *
 * @return string $val
 */
if (!function_exists('emd_get_hidden_func')) {
	function emd_get_hidden_func($hidden_func,$app='',$cstring='',$pid=0) {
		$current_user = wp_get_current_user();
		$val = "";
		switch ($hidden_func) {
			case 'user_login':
				$val = $current_user->user_login;
			break;
			case 'user_email':
				$val = $current_user->user_email;
			break;
			case 'user_firstname':
				$val = $current_user->user_firstname;
			break;
			case 'user_lastname':
				$val = $current_user->user_lastname;
			break;
			case 'user_displayname':
				$val = $current_user->display_name;
			break;
			case 'user_id':
				$val = $current_user->ID;
			break;
			case 'date_mm_dd_yyyy':
				$val = date("Y-m-d");
			break;
			case 'date_dd_mm_yyyy':
				$val = date("Y-m-d");
			break;
			case 'current_year':
				$val = date("Y");
			break;
			case 'current_month':
				$val = date("F");
			break;
			case 'current_month_num':
				$val = date("m");
			break;
			case 'current_day':
				$val = date("d");
			break;
			case 'now':
				$val = date("Y-m-d H:i:s");
			break;
			case 'current_time':
				$val = date("H:i:s");
			break;
			case 'user_ip':
				$val = $_SERVER['REMOTE_ADDR'];
			break;
			case 'unique_id':
				$val = 'emd_uid';
			break;
			case 'autoinc':
				$val = 'emd_autoinc';
			break;
			case 'concat':
				if($pid != 0){
					$attr_list = get_option($app . '_attr_list');
					$mypost = get_post($pid);
					$builtins = Array(
						'entity_id' => $mypost->ID,
						'title' => $mypost->post_title,
						'permalink' => get_permalink($mypost->ID),
						'edit_link' => get_edit_post_link($mypost->ID),
						'delete_link' => esc_url(add_query_arg('frontend', 'true', get_delete_post_link($mypost->ID))),
						'excerpt' => $mypost->post_excerpt,
						'content' => $mypost->post_content,
						'author_dispname' => get_the_author_meta('display_name',$mypost->post_author),
						'author_nickname' => get_the_author_meta('nickname',$mypost->post_author),
						'author_fname' => get_the_author_meta('first_name',$mypost->post_author),
						'author_lname' => get_the_author_meta('last_name',$mypost->post_author),
						'author_login' => get_the_author_meta('user_login',$mypost->post_author),
						'modified_datetime' => $mypost->post_modified,
						'created_datetime' => $mypost->post_date,
					);
					if (preg_match_all('/\!#([^}#]*)\#/', $cstring, $matches)) {
						foreach ($matches[1] as $match_tag) {
							if (preg_match('/^ent_/', $match_tag)) {
								$rmatch_tag = preg_replace('/^ent_/','emd_',$match_tag);
								$new = emd_mb_meta($rmatch_tag, array() , $mypost->ID);
								//if match tag is emd_autoinc
								if(empty($new) && !empty($attr_list[$mypost->post_type][$rmatch_tag]['hidden_func']) && $attr_list[$mypost->post_type][$rmatch_tag]['hidden_func'] == 'autoinc'){
									$new = get_option($rmatch_tag . "_autoinc",$attr_list[$mypost->post_type][$rmatch_tag]['autoinc_start']);
									if($new < $attr_list[$mypost->post_type][$rmatch_tag]['autoinc_start']){
										$new = $attr_list[$mypost->post_type][$rmatch_tag]['autoinc_start'];
									}
							        	else {
										$new = $new + $attr_list[$mypost->post_type][$rmatch_tag]['autoinc_incr'];
									}
								}
								$cstring = str_replace('!#' . $match_tag . '#', $new, $cstring);
							}
							elseif (in_array($match_tag, array_keys($builtins))) {
								$cstring = str_replace('!#'.$match_tag.  '#',$builtins[$match_tag],$cstring);
							}
						}
					}
					$val = esc_attr($cstring);
				}
				elseif(!empty($cstring)){
					$val = esc_attr($cstring);
				}
				else {
					$val = 'emd_concat';
				}
			break;
		}
		if ($current_user->ID == 0 && $val == '') {
			$val = "Visitor";
		}
		return $val;
	}
}
/**
 * Sets wp query request for author and search on frontend
 * @since WPAS 4.8
 * @param string $app
 * @param string $input
 * @param object $query
 * @param string $type
 *
 * @return string $input
 */
if (!function_exists('emd_author_search_results')) {
	function emd_author_search_results($app,$input,$query,$type){
		global $wpdb;
		$glob_limit = $app . '_limit';
		$glob_orderby = $app . '_orderby';
		global ${$glob_limit},${$glob_orderby};
		if($type == 'author'){
			$ent_list = get_option($app ."_ent_list");
			if(!empty($ent_list)){
				$set_types =  array_keys($ent_list);
			}
		}
		else {
			$set_types = emd_find_limitby('frontend',$app);
		}
		if(!empty($set_types)){
			$pids = Array();
			$diff_pids = Array();
			$input_add = "";
			$auth_id = $query->query_vars['author'];
			$search = $query->query_vars['s'];
			foreach (array_values($set_types) as $ptype) {
				$pids = apply_filters('emd_limit_by', $pids, $app, $ptype, 'frontend');
				$diff_pids = array_diff($pids,Array('0'));	
				if(empty($pids)){
					$input_add .= " UNION (SELECT * FROM " . $wpdb->posts . " WHERE " . $wpdb->posts . ".post_type ='" . $ptype . "' AND " . $wpdb->posts . ".post_status = 'publish' AND ";
					if($type == 'author'){
						$input_add .=  $wpdb->posts . ".post_author=" . $auth_id . ")";
					}
					elseif($type == 'search'){
						$input_add .=  "(" . $wpdb->posts . ".post_title LIKE '%" . $search . "%' OR " . $wpdb->posts . ".post_content LIKE '%" . $search . "%'))";
					}
				}
				elseif(!empty($diff_pids)) {
					$pids_arr = "(" . implode(",",$pids) . ")";
					$input_add .= " UNION (SELECT * FROM " . $wpdb->posts . " WHERE " . $wpdb->posts . ".ID IN " . $pids_arr . " AND ";
					if($type == 'author'){
						$input_add .= $wpdb->posts . ".post_author=" . $auth_id . ")";
					}
					elseif($type == 'search'){
						$input_add .=  "(" . $wpdb->posts . ".post_title LIKE '%" . $search . "%') OR (" . $wpdb->posts . ".post_content LIKE '%" . $search . "%'))";
					}
				}
			}
			if(!empty($input_add)){
				$input = str_replace(${$glob_limit},"",$input);
				$input = $input . $input_add .  " ORDER BY " . ${$glob_orderby} . " " . ${$glob_limit};
			}
		}
		return $input;
	}
}

/**
 * Sets query parameters for author and search on frontend
 * Not used after WPAS 4.7 , Feb-01-2016, kept for backward comp.
 * @since WPAS 4.0
 * @param string $app
 * @param object $query
 * @param bool $has_limit_by
 *
 * @return object $query
 */
if (!function_exists('emd_limit_author_search')) {
	function emd_limit_author_search($app, $query, $has_limitby = 0) {
		$current_ptype = isset($query->query_vars['post_type']) ? $query->query_vars['post_type'] : Array();
		$cap_post_types = get_post_types();
		foreach ($cap_post_types as $ptype) {
			if (!in_array($ptype, Array(
				'attachment',
				'revision',
				'nav_menu_item'
			)) && current_user_can('edit_' . $ptype . 's')) {
				$set_types[] = $ptype;
			}
		}
		if (!empty($set_types)) {
			$latest_ptypes = array_unique(array_merge($current_ptype, $set_types));
			$query->set('post_type', array_values($latest_ptypes));
			if ($has_limitby == 1) {
				$pids = Array();
				foreach (array_values($latest_ptypes) as $ptype) {
					$pids = apply_filters('emd_limit_by', $pids, $app, $ptype, 'frontend');
				}
				$query->set('post__in', $pids);
			}
		}
		return $query;
	}
}
/**
 * Has_shortcode func if wp version is < 3.6
 *
 * @since WPAS 4.0
 * @param string $content
 * @param string $shc
 *
 * @return bool
 */
if (version_compare($wp_version, "3.6", "<") && !function_exists('has_shortcode')) {
	function has_shortcode($content, $shc) {
		global $shortcode_tags;
		if (array_key_exists($shc, $shortcode_tags)) {
			preg_match_all('/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER);
			if (empty($matches)) return false;
			foreach ($matches as $shortcode) {
				if ($shc === $shortcode[2]) return true;
			}
		}
		return false;
	}
}
/**
 * Parse and replace template tags with values in email subject and messages
 *
 * @since WPAS 4.3
 * moved from notify-actions file
 *
 * @param string $app
 * @param string $message
 * @param string $pid
 *
 * @param string $message
 */
if (!function_exists('emd_parse_template_tags')) {
	function emd_parse_template_tags($app, $message, $pid, $last=1,$type='') {
		global $wpdb;
		$rel_list = get_option($app . "_rel_list");
		$mypost = get_post($pid);
		$permlink = get_permalink($pid);
		$access_views = get_option($app . "_access_views");
		$ent_list = get_option($app . "_ent_list");
		if(!empty($ent_list[$mypost->post_type]['user_key'])){
			$user_key = $ent_list[$mypost->post_type]['user_key'];
			$user_id = get_post_meta($pid,$user_key,true);
			if(!empty($user_id)){
				$user = get_userdata($user_id);	
				if(preg_match('/{' . $mypost->post_type . '_login_username}/',$message)){
					$message = str_replace('{' . $mypost->post_type . '_login_username}', $user->user_login, $message);
				}
				if(preg_match('/{' . $mypost->post_type . '_login_password_reset_link}/',$message) || preg_match('/{' . $mypost->post_type . '_passw_reset_link}/',$message)){
					$emd_rp_key = get_password_reset_key($user);
					$user_login = $user->user_login;
					//get login page link saved in settings
					$login_settings = get_option($app . '_login_settings');
					if(!empty($login_settings['login_page'])){
						$login_url = get_permalink($login_settings['login_page']);
						$emd_rp_link = add_query_arg(array('emd_action' => 'rp', 'emd_key' => $emd_rp_key, 'emd_login' => rawurlencode($user_login)),untrailingslashit($login_url));
						$message = str_replace('{' . $mypost->post_type . '_login_password_reset_link}', $emd_rp_link, $message);
					}
				}
			}
		}
		if (in_array($mypost->post_status, Array(
			'pending',
			'draft'
		))) {
			$preview_link = add_query_arg('preview', 'true', get_permalink($pid));
		}
		if($type == 'rel'){
			$builtins = Array(
				$mypost->post_type . '_title' => $mypost->post_title,
				$mypost->post_type . '_permalink' => $permlink,
				$mypost->post_type . '_edit_link' => get_edit_post_link($pid) ,
				$mypost->post_type . '_delete_link' => esc_url(add_query_arg('frontend', 'true', get_delete_post_link($pid))) ,
				$mypost->post_type . '_excerpt' => $mypost->post_excerpt,
				$mypost->post_type . '_content' => $mypost->post_content,
				$mypost->post_type . '_author_dispname' => get_the_author_meta('display_name',$mypost->post_author),
				$mypost->post_type . '_author_nickname' => get_the_author_meta('nickname',$mypost->post_author),
				$mypost->post_type . '_author_fname' => get_the_author_meta('first_name',$mypost->post_author),
				$mypost->post_type . '_author_lname' => get_the_author_meta('last_name',$mypost->post_author),
				$mypost->post_type . '_author_login' => get_the_author_meta('user_login',$mypost->post_author),
				$mypost->post_type . '_author_bio' => get_the_author_meta('description',$mypost->post_author),
				$mypost->post_type . '_author_googleplus' => get_the_author_meta('googleplus',$mypost->post_author),
				$mypost->post_type . '_author_twitter' => get_the_author_meta('twitter',$mypost->post_author),
			);
		}
		else {
			$builtins = Array(
				'title' => $mypost->post_title,
				'permalink' => $permlink,
				'edit_link' => get_edit_post_link($pid) ,
				'delete_link' => esc_url(add_query_arg('frontend', 'true', get_delete_post_link($pid))) ,
				'excerpt' => $mypost->post_excerpt,
				'content' => $mypost->post_content,
				'author_dispname' => get_the_author_meta('display_name',$mypost->post_author),
				'author_nickname' => get_the_author_meta('nickname',$mypost->post_author),
				'author_fname' => get_the_author_meta('first_name',$mypost->post_author),
				'author_lname' => get_the_author_meta('last_name',$mypost->post_author),
				'author_login' => get_the_author_meta('user_login',$mypost->post_author),
				'author_bio' => get_the_author_meta('description',$mypost->post_author),
				'author_googleplus' => get_the_author_meta('googleplus',$mypost->post_author),
				'author_twitter' => get_the_author_meta('twitter',$mypost->post_author),
				'created_date' => date_i18n(get_option('date_format'),strtotime($mypost->post_date)),
			);
		}

		$glob_list = get_option($app . "_glob_list");
		if(!empty($glob_list)){
			foreach($glob_list as $kglob => $vglob){
				$globs[$kglob] = emd_global_val($app,$kglob);
			}
		}
		
		$message = apply_filters('emd_ext_parse_tags',$message,$pid,$app,$type);
		//first get each template tag
		if (preg_match_all('/\{([^}]*)\}/', $message, $matches)) {
			foreach ($matches[1] as $match_tag) {
				if(in_array($match_tag,Array('site_name','site_tagline','site_link'))){
					if(is_multisite()){
						$site_name = get_network()->site_name;
					} else {
						$site_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
					}
					$message = str_replace('{site_name}',$site_name,$message);
					$message = str_replace('{site_tagline}',get_option('blogdescription'),$message);
					$message = str_replace('{site_link}',site_url(),$message);
				} elseif (in_array($match_tag, array_keys($builtins))) {
					//replace if builtin
					$message = str_replace('{' . $match_tag . '}', $builtins[$match_tag], $message);
				} elseif (!empty($globs) && in_array($match_tag, array_keys($globs))) {
					$message = str_replace('{' . $match_tag . '}', $globs[$match_tag], $message);
				} elseif (preg_match('/^wpas_/', $match_tag)) {
					$message = str_replace('{' . $match_tag . '}', emd_mb_meta($match_tag, array() , $pid) , $message);
				} elseif (preg_match('/^emd_/', $match_tag)) {
					$new = emd_get_attr_val($app, $pid, $mypost->post_type, $match_tag,'notify',$type);
					if($last == 1 || !empty($new)){
						$message = str_replace('{' . $match_tag . '}', $new, $message);
					}
					elseif($new === '0'){
						$message = str_replace('{' . $match_tag . '}', $new, $message);
					}
				} elseif (preg_match('/^com_/', $match_tag)) {
					$new_match_tag = preg_replace('/^com_/', '', $match_tag);
					$mycomments = get_comments(array(
						'post_id' => $pid,
						'type' => $new_match_tag,
						'status'=> 'approve',
					));
					$new = '<ul class="commentlist">';
					foreach($mycomments as $mycomm){
						$new .= "<li><div>" . __("Author:","emd-plugins") . $mycomm->comment_author . "&nbsp;&nbsp;";
						$new .= get_comment_date(sprintf(__('%s \a\t %s', 'emd-plugins'),get_option('date_format'),get_option('time_format')),$mycomm->comment_ID) . "</div>";
						$com_content = str_replace("\n", "<br>", $mycomm->comment_content);	
						$new .= "<p>" . $com_content . "</p>";
						$new .= "</li>";
					}
					$new .= '</ul>';
					$message = str_replace('{' . $match_tag . '}', $new, $message);
				} elseif (preg_match('/^rel_/', $match_tag)) {
					$new_rel = "";
					$has_no_link = 0;
					$has_redirect = 0;
					$new_match_tag = preg_replace('/^rel_/', '', $match_tag);
					//_rl for redirect link
					if(preg_match('/_rl$/', $match_tag)){
						$new_match_tag = preg_replace('/_rl$/', '', $new_match_tag);
						$has_redirect = 1;
					}
					elseif(preg_match('/_nl$/', $match_tag)){
						$new_match_tag = preg_replace('/_nl$/', '', $new_match_tag);
						$has_no_link = 1;
					}
					$myrel = $rel_list['rel_' . $new_match_tag];
					$from_to = "from";
					$other = "to";
					if ($myrel['from'] == $mypost->post_type) {
						$from_to = "to";
						$other = "from";
					}
					$conns = $wpdb->get_results("SELECT p2p_" . $from_to . " as pid FROM {$wpdb->p2p} WHERE p2p_type='" . $new_match_tag . "' AND p2p_" . $other . "='" . $pid . "'", ARRAY_A);	
					if(!empty($conns)){
						foreach ($conns as $mycon) {
							$rpost = get_post($mycon['pid']);
							if($has_no_link == 1){
								$new_rel.= $rpost->post_title . ", ";
							}
							elseif($has_redirect == 1){
								//{site_login}?redirect_to={emd_order_permalink}
								$new_rel.= "<a href='{site_login}?redirect_to=" . get_permalink($mycon['pid']) . "'>" . $rpost->post_title . "</a>,";
							}
							else {
								$new_rel.= "<a href='" . get_permalink($mycon['pid']) . "'>" . $rpost->post_title . "</a>,";
							}
						}
						$new_rel = rtrim($new_rel, ", ");
					}
					$message = str_replace('{' . $match_tag . '}', $new_rel, $message);
				} else {
					if (preg_match('/_nl$/', $match_tag)) {
						$new_match_tag = preg_replace('/_nl$/', '', $match_tag);
						$new = emd_get_tax_vals($pid,$new_match_tag,1);
					} else {
						$new = emd_get_tax_vals($pid,$match_tag);
					}
					if (!is_wp_error($new)) {
						$message = str_replace('{' . $match_tag . '}', $new, $message);
					}
				}
			}
		}
		return $message;
	}
}
/**
 * Get attribute value by attribute type
 *
 * @since WPAS 4.3
 * moved from notify-actions file
 *
 * @param string $app
 * @param string $pid
 * @param string $ptype
 * @param string $attr_id
 *
 * @return string $val
 *
 */
if (!function_exists('emd_get_attr_val')) {
	function emd_get_attr_val($app, $pid, $ptype, $attr_id,$where='',$type='') {
		$attr_list = get_option($app . "_attr_list");
		$val = "";
		if(!empty($attr_list[$ptype][$attr_id])){
			$attr = $attr_list[$ptype][$attr_id];
			$dtype = $attr['display_type'];
			if ($dtype == 'file') {
				$emd_mb_file = emd_mb_meta($attr_id, 'type=file', $pid);
				if (!empty($emd_mb_file)) {
					foreach ($emd_mb_file as $info) {
						$val.= "<a href='" . $info['url'] . "' title='" . $info['title'] . "'>" . $info['name'] . "</a><br/>";
					}
				}
			} elseif (in_array($dtype, Array(
				'image',
				'plupload_image',
				'thickbox_image'
			))) {
				$images = emd_mb_meta($attr_id, 'type=plupload_image', $pid);
				if (!empty($images)) {
					foreach ($images as $image) {
						if(!empty($image)){
							$val .= "<img src='" . $image['url'] . "' width='" . $image['width'] . "' height='" . $image['height'] . "' alt='" . $image['alt'] . "'/>";
							if(!empty($where) && $where == 'notify'){
								break;
							}
						}
						else {
							$val = emd_featured_img('','',$app);
						}	
					}
				}
				elseif(file_exists(constant(strtoupper($app) . '_PLUGIN_DIR') . 'assets/img/' . $ptype . '_default.svg')){
					$val = constant(strtoupper($app) . '_PLUGIN_URL') . 'assets/img/' . $ptype . '_default.svg';
				}
			} elseif (in_array($dtype, Array(
				'date',
				'datetime',
				'time'
			))) {
				//$val = emd_translate_date_format($attr, emd_mb_meta($attr_id, array() , $pid) , 1);
				$dt_val = emd_mb_meta($attr_id, array(), $pid);
				if(!empty($dt_val)){
					if($dtype == 'date'){
						if(!empty($type) && $type == 'sms' && get_locale != 'en_US'){
							$val = date_i18n('d.m.Y',strtotime($dt_val));
						}
						elseif(!empty($type) && $type == 'sms' && get_locale == 'en_US'){
							$val = date_i18n('m.d.Y',strtotime($dt_val));
						}
						else {
							$val = date_i18n(get_option('date_format'),strtotime($dt_val));
						}
					}
					elseif($dtype == 'datetime'){
						if(!empty($type) && $type == 'sms' && get_locale != 'en_US'){
							$val = date_i18n('d.m.Y' . ' ' . get_option('time_format'),strtotime($dt_val));
						}
						elseif(!empty($type) && $type == 'sms' && get_locale == 'en_US'){
							$val = date_i18n('m.d.Y' . ' ' . get_option('time_format'),strtotime($dt_val));
						}
						else {
							$val = date_i18n(get_option('date_format') . ' ' . get_option('time_format'),strtotime($dt_val));
						}
					}
					elseif($dtype == 'time'){
						$val = date_i18n(get_option('time_format'),strtotime($dt_val));
					}
				}
				else {
					$val = ' - ';
				}
			} elseif(in_array($dtype, Array('radio','select','select_advanced','checkbox_list'))) {
				if(!empty($attr['multiple'])){
					$val = emd_mb_meta($attr_id, Array('type'=> $dtype,'multiple' =>1), $pid);
				}
				else {
					$val = emd_mb_meta($attr_id, 'type=' . $dtype, $pid);
				}
				if(!empty($where) && $where == 'key'){
					if(is_array($val)){
						$val = __(strtolower(implode(',', $val)),$app);
					}
				}
				else {
					if(!empty($val) && is_array($val)){
						foreach($val as $kval => $mval){
							if(!empty($attr_list[$ptype][$attr_id]['options'][strtolower($mval)])){
								$val[$kval] = __($attr_list[$ptype][$attr_id]['options'][strtolower($mval)],$app);
							}
						}
						$val = implode(', ', $val);
					}
					elseif(!empty($val) && !empty($attr_list[$ptype][$attr_id]['options'][strtolower($val)])){
						$val = __($attr_list[$ptype][$attr_id]['options'][strtolower($val)],$app);
					}
					elseif(!empty($val) && !empty($attr_list[$ptype][$attr_id]['options'][$val])){
						$val = __($attr_list[$ptype][$attr_id]['options'][$val],$app);
					}
					elseif(empty($val) && is_array($val)){
						$val = '';
					}
				}
			} else {
                                $val = emd_mb_meta($attr_id, array() , $pid);
                        }
		}
		return $val;
	}
}
/**
 * Operator list for search moved from form-functions
 *
 * @since WPAS 4.3 
 * @param string $opr
 *
 * @return string $operator
 */
if (!function_exists('emd_get_meta_operator')) {
	function emd_get_meta_operator($opr) {
		$operators['is'] = '=';
		$operators['is_not'] = '!=';
		$operators['like'] = 'LIKE';
		$operators['not_like'] = 'NOT LIKE';
		$operators['less_than'] = '<';
		$operators['greater_than'] = '>';
		$operators['less_than_eq'] = '<=';
		$operators['greater_than_eq'] = '>=';
		$operators['begins'] = 'REGEXP';
		$operators['ends'] = 'REGEXP';
		$operators['word'] = 'REGEXP';
		return $operators[$opr];
	}
}
if (!function_exists('emd_check_userEmail')) {
	add_action('wp_ajax_emd_check_userEmail','emd_check_userEmail');
	/**
	 * Check user email
	 *
	 * @since WPAS 4.0
	 *
	 * @return bool $response
	 */
	function emd_check_userEmail() {
		check_ajax_referer('emd_form', 'nonce');
		$response = true;
		$post_id = '';
		$post_email = isset($_GET['email_val']) ? sanitize_email($_GET['email_val']) : '';
		$email_key = isset($_GET['email_key']) ? sanitize_text_field($_GET['email_key']) : '';
		$post_type = isset($_GET['ptype']) ? (string) $_GET['ptype'] : '';
		$myapp = isset($_GET['myapp']) ? (string) $_GET['myapp'] : '';
		$ent_list = get_option($myapp . "_ent_list");
		if(!empty($ent_list[$post_type]['user_email_key']) && $email_key == $ent_list[$post_type]['user_email_key']){
			$user_data = get_user_by('email', $post_email);
			if(!empty($user_data)){
				$response = false;
			}
		}
		echo $response;
		die();
	}
}

if (!function_exists('emd_check_unique')) {
	add_action('wp_ajax_emd_check_unique','emd_check_unique');
	/**
	 * Check unique keys
	 *
	 * @since WPAS 4.0
	 *
	 * @return bool $response
	 */
	function emd_check_unique() {
		check_ajax_referer('emd_form', 'nonce');
		$response = false;
		$post_id = '';
		$form_arr = isset($_GET['data_input']) ? array_map('sanitize_text_field',$_GET['data_input']) : '';
		$post_type = isset($_GET['ptype']) ? (string) $_GET['ptype'] : '';
		$myapp = isset($_GET['myapp']) ? (string) $_GET['myapp'] : '';
		$ent_list = get_option($myapp . "_ent_list");
		$ent_attrs = get_option($myapp . "_attr_list");
		$uniq_fields = $ent_list[$post_type]['unique_keys'];
		$title_set = 0;
		foreach ($form_arr as $fkey => $myform_field) {
			if($fkey == 'blt_title' && in_array('blt_title',$uniq_fields)){
				$title_set = 1;
				$data['blt_title'] = $myform_field;
			}	
			elseif($fkey == 'post_ID'){
				$post_id = $myform_field;
			}
			elseif(in_array($fkey, $uniq_fields)) {
				$val = emd_translate_date_format($ent_attrs[$post_type][$fkey], $myform_field, 0);
				if(!empty($val) && $val != 'emd_uid'){	
					$data[$fkey] = $val;
				}
			}
		}
		if (!empty($data) && !empty($post_type)) {
			$response = emd_check_uniq_from_wpdb($data, $post_id, $post_type, $title_set);
		}
		echo $response;
		die();
	}
}
/**
 * Sql query to check unique keys
 *
 * @since WPAS 4.0
 *
 * @return bool $response
 */
if (!function_exists('emd_check_uniq_from_wpdb')) {
	function emd_check_uniq_from_wpdb($data, $post_id, $post_type, $title_set= 0) {
		global $wpdb;
		$where = "";
		$where_last = "";
		$join = "";
		$count = 1;
		foreach ($data as $key => $val) {
			if($key == 'blt_title'){
				$where_last = " AND p.post_title='" . $val . "'";
			}
			else {
				$join.= " LEFT JOIN " . $wpdb->postmeta . " pm" . $count . " ON p.ID = pm" . $count . ".post_id";
				$where.= " pm" . $count . ".meta_key='" . $key . "' AND pm" . $count . ".meta_value='" . $val . "' AND ";
				$count++;
			}
		}
		$where = rtrim($where, "AND");
		if(empty($join)){
			$where_last .= " AND p.ID != '" . $post_id . "'";
		}
		$result_arr = $wpdb->get_results("SELECT p.ID FROM " . $wpdb->posts . " p " . $join . " WHERE " . $where . " p.post_type = '" . $post_type . "'" . $where_last, ARRAY_A);
		if (empty($result_arr)) {
			return true;
		} elseif (!empty($post_id) && $result_arr[0]['ID'] == $post_id) {
			return true;
		}
		return false;
	}
}
/**
 * Show insert into post button for media uploads
 *
 * @since WPAS 4.5
 * @param array $args
 * @return array $args
 *
 */
if (!function_exists('emd_media_item_args')) {
	function emd_media_item_args($args){
		$args['send'] = true;
		return $args;
	}
}
/**
 * Parse set filter for form shortcodes
 *
 * @since WPAS 4.10
 * @param string $set
 * @return array $ret_sets
 *
 */
if (!function_exists('emd_parse_set_filter')) {
	function emd_parse_set_filter($set){
		$set_list = explode(";",$set);
		$set_rels = Array();
		$set_attrs = Array();
		$set_taxs = Array();

		foreach($set_list as $aset){
			$aset_arr = explode("::",$aset);
			if($aset_arr[0] == 'rel'){
				$set_rels[$aset_arr[1]] = $aset_arr[3];
			}
			if($aset_arr[0] == 'attr'){
				$set_attrs[$aset_arr[1]] = $aset_arr[3];
			}
			if($aset_arr[0] == 'tax'){
				$set_taxs[$aset_arr[1]] = $aset_arr[3];
			}
			if($aset_arr[0] == 'csrf'){
				$ret_sets['csrf'] = $aset_arr[1];
			}
		}
		$ret_sets['rel'] = $set_rels;
		$ret_sets['attr'] = $set_attrs;
		$ret_sets['tax'] = $set_taxs;
		return $ret_sets;
	}
}
if (!function_exists('emd_get_tax_vals')) {
	function emd_get_tax_vals($pid,$txn_name,$nolink = 0,$seperator=' '){
		$term_list = get_the_term_list($pid,$txn_name,'',$seperator,'');
		if(empty($term_list) || is_wp_error($term_list)){
			return '';
		}	
		if($nolink == 1){
			return strip_tags($term_list);
		}	
		return $term_list;		
	}
}
if (!function_exists('emd_get_tax_slugs')) {
	function emd_get_tax_slugs($pid,$txn_name,$seperator=''){
		$slugs = '';
		$term_list = get_the_terms($pid,$txn_name);
		if(!empty($term_list) && !is_wp_error($term_list)){
			foreach($term_list as $term){
				$slugs .= esc_attr($term->slug) . $seperator;
			}
		}
		if(!empty($seperator)){
			$slugs = rtrim($slugs,$seperator);
		}
		return $slugs;
	}
}
if (!function_exists('emd_post_exists')) {
	function emd_post_exists($title, $type) {
		global $wpdb;

		$post_title = wp_unslash(sanitize_post_field('post_title', $title, 0, 'db'));
		$post_type = wp_unslash(sanitize_post_field('post_type', $type, 0, 'db'));

		$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
		$args = array();
		
		if(!empty($title)) {
			$query .= ' AND post_title = %s';
			$args[] = $post_title;
		}
	 
		if(!empty($type)) {
		     $query .= ' AND post_type = %s ';
		     $args[] = $post_type;
		}

		if(!empty($args))
			return (int) $wpdb->get_var($wpdb->prepare($query, $args));

		return 0;
	}
}
/**
 * Check if any author limitby set for frontend or backend
 *
 * @since WPAS 5.3
 * @param string $type
 *
 * @return array $limitby_ents
 */
if (!function_exists('emd_find_limitby')) {
	function emd_find_limitby($type,$app) {
		$limitby_ents = Array();
		$has_limitby_auth_type = get_option($app ."_limitby_auth_caps");
		if (!empty($has_limitby_auth_type)) {
			$ent_map_list = get_option($app . "_ent_map_list");
			foreach ($has_limitby_auth_type as $lent => $auth_limits) {
				foreach ($auth_limits as $limit) {
					if (empty($ent_map_list[$lent]['make_visitor_visible']) && preg_match('/limitby_author_' . $type . '/', $limit)) {
						$limitby_ents[] = $lent;
					}
				}
			}
		}
		return $limitby_ents;
	}
}
/**
 * Check if attr, taxonomy and relationship visible, enabled 
 *
 * @since WPAS 5.3
 * @param string $item
 * @param string $type
 *
 * @return bool $visible
 */
if (!function_exists('emd_is_visible')) {
	function emd_is_item_visible($item,$app,$type,$check_full=0) {
		if($type == 'attribute' && in_array($item,Array('created_date','created_datetime','created_time','current_time','modified_date','modified_datetime','modified_time'))){
			return 1;
		}
		$item = trim($item);
		$shc_list = get_option($app . "_shc_list");
		if(!empty($shc_list['remove_vis']) && $shc_list['remove_vis'] == 1){
			if(in_array($type,Array('attribute','taxonomy')) && $check_full == 1){
				return 1;
			}
			if(in_array($type,Array('relation','rel-attribute'))){
				return 1;
			}
		}
		$visible = 0;
		//lets get role of this user
		$user = wp_get_current_user();
		$app_custom_roles = get_option($app . '_cust_roles');
		$myrole = '';
		if($user->ID == 0){
			$myrole = 'visitor';
		}
		elseif(in_array('administrator',$user->roles)){
			$myrole = 'administrator';
		}
		elseif(!empty($app_custom_roles)){
			foreach($app_custom_roles as $krole => $vrole){
				if(in_array($krole,$user->roles)){
					$myrole = $krole;
					break;  
				}
			}
		}
		if($type == 'attribute'){
			if(in_array($item,Array('content','excerpt','title'))){
				$item = 'blt_' . $item;
			}
			else {
				$item = preg_replace('/^ent_/','emd_',$item);
			}
			$ent_map_list = get_option($app . "_ent_map_list");
			$attr_list = get_option($app . "_attr_list");
			global $post;
			if(empty($attr_list[$post->post_type])){
				if(!empty($attr_list)){
					foreach($attr_list as $kent => $vent){
						if(in_array($item,array_keys($vent))){
							$post_type = $kent;
							break;
						}
					}
				}
			}
			else {
				$post_type = $post->post_type;
			}
			$item_is_clone = '';
			if(!empty($ent_map_list)){
				if(!empty($ent_map_list[$post_type]['attrs'][$item]) && $ent_map_list[$post_type]['attrs'][$item] == 'show'){
					//check access 
					if($myrole == 'administrator'){
						$visible = 1;
					}
					elseif(empty($ent_map_list[$post_type]['edit_attrs'][$myrole][$item]) || (!empty($ent_map_list[$post_type]['edit_attrs'][$myrole][$item]) && $ent_map_list[$post_type]['edit_attrs'][$myrole][$item] != 'not_show')){
						$visible = 1;
					}
					if(!empty($attr_list[$post_type][$item]['clone'])){
						$item_is_clone = $attr_list[$post_type][$item]['clone'];
					}
				}
				elseif(empty($ent_map_list[$post_type]['attrs'][$item])){
					$visible = 1;
				}
			}
			else {
				$visible = 1;
			}
			if($visible == 1 && $check_full == 0){
				if($item == 'featured_img'){
					//$val = get_the_post_thumbnail(null, 'large');
					$val = emd_featured_img('','large',$app);
				} elseif($item == 'blt_content'){
                                        $val = $post->post_content;
                                } elseif($item == 'blt_excerpt'){
                                        $val = $post->post_excerpt;
                                } elseif($item == 'blt_title'){
                                        $val = $post->post_title;
                                } else {
					$val = emd_get_attr_val($app, $post->ID, $post->post_type, $item,$where='');
					if(!empty($item_is_clone) && is_array($val) && empty($val[0])){
						$visible = 0;
					}
				}
				if(!is_array($val) && empty($val) && $val != "0"){
					$visible = 0;
				}	
			}
		}
		elseif($type == 'taxonomy'){	
			$item = preg_replace('/^tax_/','',$item);
			global $post;
			$tax_settings = get_option($app . "_tax_settings");
			if(!empty($tax_settings[$item]['hide']) && $tax_settings[$item]['hide'] == 'show'){
				
				$visible = 1;
			}
			elseif(empty($tax_settings[$item]['hide'])) {
				$visible = 1;
			}
			if($visible == 1 && $check_full == 0){
				$val = emd_get_tax_vals($post->ID,$item, 0);
				if(empty($val)){
					$visible = 0;
				}
				elseif(!empty($tax_settings[$item]['edit'][$myrole]) && $tax_settings[$item]['edit'][$myrole] == 'not_show'){
					$visible = 0;
				}
			}
		}
		elseif($type == 'relation'){
			$item = preg_replace('/^entrelcon_to_/','rel_',$item);
                        $item = preg_replace('/^entrelrltd_to_/','rel_',$item);
                        $item = preg_replace('/^entrelcon_from_/','rel_',$item);
                        $item = preg_replace('/^entrelrltd_from_/','rel_',$item);
			$item = preg_replace('/^entrelcon_/','rel_',$item);
			$item = preg_replace('/^entrelrltd_/','rel_',$item);
			$ent_map_list = get_option($app . "_ent_map_list");
			global $post;
			$rel_list = get_option($app . "_rel_list");
			$check_hide_rel = '';
			if(!empty($rel_list[$item])){
				if($rel_list[$item]['from'] == $post->post_type){
					$other_ptype = $rel_list[$item]['to'];
				}
				elseif($rel_list[$item]['to'] == $post->post_type){
					$other_ptype = $rel_list[$item]['from'];
				}
			}
			if(empty($other_type)){
				$ptype = $rel_list[$item]['from'];
				$other_ptype = $rel_list[$item]['to'];
			}
			else {
				$ptype = $post->post_type;
			}
			
			if(!empty($ent_map_list[$ptype])){
				if((empty($ent_map_list[$ptype]['hide_rels'])) || (!empty($ent_map_list[$ptype]['hide_rels'][$item]) && $ent_map_list[$ptype]['hide_rels'][$item] == 'show')){
					$visible = 1;
				}
			}
			elseif(!empty($ent_map_list[$other_ptype])){
				if((empty($ent_map_list[$other_ptype]['hide_rels'])) || (!empty($ent_map_list[$other_ptype]['hide_rels'][$item]) && $ent_map_list[$other_ptype]['hide_rels'][$item] == 'show')){
					$visible = 1;
				}
			}

			if(empty($ent_map_list[$ptype]['hide_rels']) && empty($ent_map_list[$other_ptype]['hide_rels'])) {
				$visible = 1;
			}
			if($visible == 1 && (!empty($ent_map_list[$ptype]['edit_rels'][$myrole][$item]) && $ent_map_list[$ptype]['edit_rels'][$myrole][$item] == 'not_show')){
				$visible = 0;
			}
			elseif($visible == 1 && (!empty($ent_map_list[$other_ptype]['edit_rels'][$myrole][$item]) && $ent_map_list[$other_ptype]['edit_rels'][$myrole][$item] == 'not_show')){
				$visible = 0;
			}
		}
		elseif($type == 'rel-attribute'){
			$visible = 1;
                        $item = preg_replace('/^rel/','emd',$item);
			$ent_map_list = get_option($app . "_ent_map_list");
			if(!empty($ent_map_list)){
				foreach($ent_map_list as $kent => $val){
					if(!empty($val['hide_rel_attrs'][$item]) && $val['hide_rel_attrs'][$item] != 'show'){
						$visible = 0;
						break;
					}
					elseif(!empty($val['edit_rels'][$myrole][$item]) && $val['edit_rels'][$myrole][$item] == 'not_show'){
						$visible = 0;
						break;
					}	
				}
			}
			else {
				$visible = 1;
			}
                }
		return $visible;
	}
}
if (!function_exists('emd_global_val')) {
	function emd_global_val($app,$key,$set_list=Array()){
		if(!empty($set_list) && !empty($set_list[$key])){
			return $set_list[$key];
		}
		$variables = get_option(str_replace("-","_",$app) . '_glob_list');
		$variables_init = get_option(str_replace("-","_",$app) . '_glob_init_list');
		$variables_init = apply_filters('emd_ext_glob_var_init', $variables_init);
		if(!empty($variables[$key])){
			$check_var_key = $variables[$key];
		}
		else{
			$check_var_key = $variables_init[$key];
		}
			
		if($check_var_key['type'] == 'checkbox_list' || $check_var_key['type'] == 'multi_select'){
			if(!empty($check_var_key['val'])){
				return implode(',',$check_var_key['val']);
			}
			else {
				return implode(',',$check_var_key['dflt']);
			}
		}	
		elseif(isset($check_var_key['val'])) {
			return $check_var_key['val'];
		}
		else {
			return $check_var_key['dflt'];
		}	
		return '';
	}
}
/**
 * Ajax load file
 *
 * @since WPAS 4.0
 *
 */
if (!function_exists('emd_load_file')) {
	function emd_load_file() {
		$ret = check_ajax_referer('emd_load_file', 'nonce', false);
		if ($ret === false) {
			echo '<div class="text-danger"><a href="' . wp_get_referer() . '">' . __('Please refresh the page and try again.', 'emd-plugins') . '</a></div>';
			die();
		}
		$path = sanitize_text_field($_POST['path']);
		$myapp = strtolower(preg_replace('/_PLUGIN_DIR$/','',$path));
		require_once constant($path) . 'assets/ext/filepicker/upload.php';
		$upload_handler = new UploadHandler(true, sanitize_text_field($_POST['field']), sanitize_text_field($_POST['extensions']),$myapp);
		die();
	}
}
if (!function_exists('emd_delete_file')) {
	function emd_delete_file() {
		$ret = check_ajax_referer('emd_delete_file', 'nonce', false);
		if ($ret === false) {
			echo '<div class="text-danger"><a href="' . wp_get_referer() . '">' . __('Please refresh the page and try again.', 'emd-plugins') . '</a></div>';
			die();
		}
		$path = sanitize_text_field($_POST['path']);
		$myapp = strtolower(preg_replace('/_PLUGIN_DIR$/','',$path));
		$sess_name = strtoupper($myapp);
		$session_class = $sess_name();
		$sess_files = $session_class->session->get('uploads');
		$field = sanitize_text_field($_POST['field']);
		if(!empty($sess_files[$field])){
			foreach($sess_files[$field] as $kattch => $myattch){
				if($myattch['name'] == sanitize_text_field($_POST['del_file'])){
					unset($sess_files[$field][$kattch]);
				}
			}
			$session_class->session->set('uploads',$sess_files);
		}
		echo 1;
		die();
	}
}
if(!function_exists('emd_get_attachment_layout')){
	function emd_get_attachment_layout($info){
		$ext = preg_replace('/^.+?\.([^.]+)$/', '$1', $info['name']);
		$type = wp_ext2type($ext);
		if($type == 'image'){
			$img_alt = get_post_meta($info['ID'], '_wp_attachment_image_alt', true);
			$att_src = wp_get_attachment_image_src($info['ID'],'thumbnail');
			$fsrc = "<a class='att-link thickbox' rel='page' href='" . esc_url($info['url']) . "' title='" . esc_attr($info['title']) . "'><img class='img-thumbnail' style='width:75px;height:75px;' alt='" . $img_alt . "' src='" . $att_src[0] . "'/></a>";
		}
		else {
			if(in_array($type, Array('video','text','audio','archive','code','document','spreadsheet','interactive'))){
				$dashicon = 'dashicons-media-' . $type;
			}
			else {
				$dashicon = 'dashicons-media-default';
			}
			$fsrc = "<a class='att-link img-thumbnail' style='padding:0;text-decoration:none;' href='" . esc_url($info['url']) . "' target='_blank' title='" . esc_attr($info['title']) . "'><span style='vertical-align:middle;font-size:75px;width:75px;height:75px;' class='dashicons " . $dashicon . "'></span></a>";
		}
		echo "<div class='att-file' style='padding:5px;float:left;'>" . $fsrc . "</div>";
	}
}
if(!function_exists('emd_get_curr_usr_role')){
	function emd_get_curr_usr_role($app){
		$myrole = '';	
		if(function_exists('wp_get_current_user')) {
			//get role of this user
			$user = wp_get_current_user();
			$app_custom_roles = get_option($app . '_cust_roles');
			$myrole = '';
			if($user->ID == 0){
				$myrole = 'visitor';
			}
			if(in_array('administrator',$user->roles)){
				$myrole = 'administrator';
			}
			elseif(!empty($app_custom_roles)){
				foreach($app_custom_roles as $krole => $vrole){
					if(in_array($krole,$user->roles)){
						$myrole = $krole;
						break;	
					}
				}
			}
		}
		return $myrole;
	}
}
if(!function_exists('emd_featured_img')){
	function emd_featured_img($type,$size,$app,$pid=''){
		global $post;
		if(!empty($pid)){
			if(is_int($pid)){
				$post = get_post($pid);
			}
			else {
				$post->post_type = $pid;
			}
		}
		if($type == 'url'){
			if(get_post_thumbnail_id()){
				return wp_get_attachment_url(get_post_thumbnail_id());
			}
			elseif(file_exists(constant(strtoupper($app) . '_PLUGIN_DIR') . 'assets/img/' . $post->post_type . '_default.svg')){
				return constant(strtoupper($app) . '_PLUGIN_URL') . 'assets/img/' . $post->post_type . '_default.svg';
			}
			else {
				return '';
			}
		}
		else{
			if(get_the_post_thumbnail(null, $size)){
				return get_the_post_thumbnail(null, $size);
			}
			elseif(file_exists(constant(strtoupper($app) . '_PLUGIN_DIR') . 'assets/img/' . $post->post_type . '_default.svg')){
				$img_src = constant(strtoupper($app) . '_PLUGIN_URL') . 'assets/img/' . $post->post_type . '_default.svg';
				return '<img src="' . $img_src . '" class="emd-default-img" alt="' . get_the_title($post->ID) . '">';
			}
			else {
				return '';
			}
		}
				
	}
}
if(!function_exists('emd_attr_img')){
	function emd_attr_img($app,$attr,$type,$size){
		global $post;
		if($type == 'orig1'){
			if (get_post_meta($post->ID, $attr)) {
				$sval = get_post_meta($post->ID, $attr);
				$size_arr = wp_get_attachment_image_src($sval[0], $size);
				return '<img class="emd-img ' . $size . '" src="' . $size_arr[0] . '" width="' . $size_arr[1] . '" height="' . $size_arr[2] . '" alt="' . get_post_meta($sval[0], '_wp_attachment_image_alt', true) . '" />';
			}
			elseif(file_exists(constant(strtoupper($app) . '_PLUGIN_DIR') . 'assets/img/' . $post->post_type . '_default.svg')){
				$src = constant(strtoupper($app) . '_PLUGIN_URL') . 'assets/img/' . $post->post_type . '_default.svg';
				return '<img src="' . $src . '" class="emd-default-img" alt="' . get_the_title($post->ID) . '">';
			}
			else {
				return '';
			}
		}
	}
}
