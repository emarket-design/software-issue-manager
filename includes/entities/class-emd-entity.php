<?php
/**
 * Emd Entity
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_Entity Class
 *
 * Base class for entities
 *
 * @since WPAS 4.0
 */
class Emd_Entity {
	protected $post_type;
	protected $textdomain;
	protected $boxes = Array();
	/**
	 * Check to show tabs/accordions in admin entity add/edit pages
	 * @since WPAS 4.0
	 *
	 * @return bool
	 *
	 */
	private function maybe_show_tabs() {
		$desired_screen = 'edit-' . $this->post_type;
		// Exit early on autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return false;
		}
		// Inline save?
		if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['screen']) && $desired_screen === $_POST['screen']) {
			return true;
		}
		if (!$screen = get_current_screen()) {
			global $pagenow;
			if ('post-new.php' === $pagenow || 'post.php' === $pagenow) {
				if (isset($_GET['post_type']) && $this->post_type === $_GET['post_type']) {
					return true;
				} elseif (isset($_GET['post']) && get_post_type($_GET['post']) === $this->post_type) {
					return true;
				} else if ('post' === $this->post_type) {
					return true;
				}
				return false;
			}
		}
		if (is_object($screen) && isset($screen->id)) {
			return $desired_screen === $screen->id;
		} else {
			return false;
		}
	}
	/**
	 * Include file for tabs/accordions in admin entity add/edit pages
	 * @since WPAS 4.0
	 *
	 * @return include file
	 *
	 */
	public function include_tabs_acc() {
		if (defined('DOING_AJAX') && DOING_AJAX) return;
		if ($this->maybe_show_tabs()) {
			$fname = str_replace("_", "-", $this->post_type);
			$inc_file = constant(strtoupper(str_replace("-", "_", $this->textdomain)) . "_PLUGIN_DIR") . '/includes/entities/' . $fname . '-tabs.php';
			if (file_exists($inc_file)) {
				require_once $inc_file;
			}
		}
	}
	/**
	 * Change title to post id or concat of unique keys
	 * @since WPAS 4.0
	 *
	 * @param int $post_id
	 * @param object $post
	 *
	 */
	public function change_title($post_id, $post) {
		if ($post->post_type == $this->post_type) {
			if (in_array($post->post_title, Array(
				'',
				'Auto Draft'
			))) {
				remove_action('save_post', array(
					$this,
					'change_title'
				) , 99, 2);
				wp_update_post(array(
					'ID' => $post_id,
					'post_title' => $post_id
				));
				add_action('save_post', array(
					$this,
					'change_title'
				) , 99, 2);
			} elseif (empty($_POST['form_name'])) {
				$new_title = $post->post_title;
				$app = str_replace("-", "_", $this->textdomain);
				$ent_list = get_option($app . '_ent_list');
				$class_delimiter = apply_filters('emd_get_title_delimiter', " ", $app, $this->post_type);
				if (!empty($ent_list[$this->post_type]['class_title'])) {
					$class_title = $ent_list[$this->post_type]['class_title'];
					if(count($class_title) == 1 && isset($ent_list[$this->post_type]['user_key']) && $ent_list[$this->post_type]['user_key'] == $class_title[0])
					{
						$tpart = emd_mb_meta($ent_list[$this->post_type]['user_key'], Array() , $post_id);
						$user_info = get_userdata($tpart);
						$new_title = $user_info->display_name;
					}
					else {
						$new_title = '';
						foreach ($class_title as $mykey) {
							$tpart = emd_mb_meta($mykey, Array() , $post_id);
							if(!empty($tpart)){
								$new_title.= $tpart . $class_delimiter;
							}
						}
						$new_title = rtrim($new_title, $class_delimiter);
					}
				}
				if ($post->post_title == $post_id ||  ($post->post_title != $new_title && $new_title != '')) {
					remove_action('save_post', array(
						$this,
						'change_title'
					) , 99, 2);
					wp_update_post(array(
						'ID' => $post_id,
						'post_title' => $new_title,
						'post_name' => sanitize_title($new_title)
					));
					add_action('save_post', array(
						$this,
						'change_title'
					) , 99, 2);
				}
			}
		}
	}
	/**
	 * Update admin messages for specific entity
	 * @since WPAS 4.0
	 *
	 * @param array $messages
	 *
	 * @return array $messages
	 */
	public function updated_messages($messages) {
		global $post, $post_ID;
		$messages[$this->post_type] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf(__('%s updated. <a href="%s">View %s</a>', 'emd-plugins') , $this->sing_label, esc_url(get_permalink($post_ID)) , $this->sing_label) ,
			2 => __('Custom field updated.', 'emd-plugins') ,
			3 => __('Custom field deleted.', 'emd-plugins') ,
			4 => sprintf(__('%s updated.', 'emd-plugins') , $this->sing_label) ,
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf(__('%s restored to revision from %s', 'emd-plugins') , $this->sing_label, wp_post_revision_title((int)$_GET['revision'], false)) : false,
			6 => sprintf(__('%s published. <a href="%s">View %s</a>', 'emd-plugins') , $this->sing_label, esc_url(get_permalink($post_ID)) , $this->sing_label) ,
			7 => sprintf(__('%s saved.', 'emd-plugins') , $this->sing_label) ,
			8 => sprintf(__('%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'emd-plugins') , $this->sing_label, esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) , $this->sing_label) ,
			9 => sprintf(__('%s scheduled for: <strong>%s</strong>. <a target="_blank" href="%s">Preview %s</a>', 'emd-plugins') , $this->sing_label, date_i18n(__('M j, Y @ G:i','emd-plugins') , strtotime($post->post_date)) , esc_url(get_permalink($post_ID)) , $this->sing_label) ,
			10 => sprintf(__('%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'emd-plugins') , $this->sing_label, esc_url(add_query_arg('preview', 'true', get_permalink($post_ID))) , $this->sing_label) ,
		);
		return $messages;
	}
	/**
	 * Add operations button
	 * @since WPAS 4.0
	 *
	 *
	 */
	public function add_opt_button() {
		global $post_type;
		if ($post_type != $this->post_type) {
			return;
		}
		if (current_user_can('manage_operations_' . $this->post_type . "s")) {
?>
			<script type="text/javascript">
			jQuery(document).ready(function($){
					$('a.page-title-action').after('<a id="opt-<?php echo str_replace("_", "-", $this->post_type); ?>" class="add-new-h2" href="<?php echo admin_url('edit.php?post_type=' . $this->menu_entity . '&page=operations_' . $this->post_type); ?>" ><?php _e('CSV Import/Export', 'emd-plugins'); ?></a>');
					$('li.opt_<?php echo $this->post_type; ?>').html('');
					});     
		</script>
		<?php
		}
	}
	/**
	 * Set initial taxonomy terms related to this entity
	 * @since WPAS 4.0
	 *
	 * @param array $set_tax_terms
	 * @param string $tax_name
	 *
	 */
	protected static function set_taxonomy_init($set_tax_terms, $tax_name) {
		foreach ($set_tax_terms as $my_tax_term) {
			$term_id_arr = term_exists($my_tax_term['slug'], $tax_name);
			$args = Array();
			if (!empty($my_tax_term['desc'])) {
				$args['description'] = $my_tax_term['desc'];
			}
			$args['slug'] = $my_tax_term['slug'];
			if (empty($term_id_arr)) {
				wp_insert_term($my_tax_term['name'], $tax_name, $args);
			} else {
				$args['name'] = $my_tax_term['name'];
				wp_update_term($term_id_arr['term_id'], $tax_name, $args);
			}
		}
		foreach ($set_tax_terms as $my_tax_term) {
			$args = Array();
			if (!empty($my_tax_term['parent'])) {
				$parent_term = term_exists($my_tax_term['parent'], $tax_name);
				if ($parent_term !== 0 && $parent_term !== null) {
					$args['parent'] = $parent_term['term_id'];
					$myterm = term_exists($my_tax_term['slug'], $tax_name);
					$term_id = $myterm['term_id'];
					wp_update_term($term_id, $tax_name, $args);
				}
			}
		}
		delete_option($tax_name . '_children');
	}
	/**
	 * Sets attributes and filter and columns 
	 * @since WPAS 4.4
	 *
	 * @return array $search_args
	 * @return array $filter_args
	 *
	 */
	protected function set_args_boxes(){
		$search_args = Array();
		$filter_args = Array();	
		foreach($this->boxes as $kbox => $vbox){
			$this->boxes[$kbox]['validation'] = array(
				'onfocusout' => false,
				'onkeyup' => false,
				'onclick' => false
			);
		}
		$myapp = str_replace("-", "_", $this->textdomain);
		$attr_list = get_option($myapp . '_attr_list');

		if (!empty($attr_list[$this->post_type])) {
			$ent_map_list = get_option($myapp . '_ent_map_list');
			foreach ($attr_list[$this->post_type] as $kattr => $vattr) {
				if (empty($ent_map_list[$this->post_type]['attrs'][$kattr]) || (!empty($ent_map_list[$this->post_type]['attrs'][$kattr]) && $ent_map_list[$this->post_type]['attrs'][$kattr] != 'hide')) {
					$search_args[$kattr]['name'] = $vattr['label'];
					$search_args[$kattr]['meta'] = $kattr;
					$search_args[$kattr]['type'] = $vattr['display_type'];
					$search_args[$kattr]['cast'] = strtoupper($vattr['type']);
					if (!empty($vattr['options'])) {
						$search_args[$kattr]['options'] = $vattr['options'];
					}
					if(!empty($vattr['select_list']) && in_array($vattr['select_list'],Array('country','state'))){
						if($vattr['select_list'] == 'country'){
							$search_args[$kattr]['options'] = emd_get_country_list();
						}
						if($vattr['select_list'] == 'state'){
							$search_args[$kattr]['dependent_country'] = $vattr['dependent_country'];
							$search_args[$kattr]['options'] = emd_get_country_states();
						}
					}

					if (!empty($vattr['date_format'])) {
						$search_args[$kattr]['date_format'] = $vattr['date_format'];
					}
					if (!empty($vattr['desc'])) {
						$search_args[$kattr]['desc'] = $vattr['desc'];
					}
					if (!empty($vattr['max'])) {
						$search_args[$kattr]['max'] = $vattr['max'];
					}
					if (!empty($vattr['display_meta'])) {
						$search_args[$kattr]['display_meta'] = $vattr['display_meta'];
					}
					if(!empty($vattr['custom']) && !empty($this->boxes['emd_cust_field_meta_box']['title'])){
						$vattr['mid'] = 'emd_cust_field_meta_box';
						$this->boxes['emd_cust_field_meta_box']['fields'][$kattr]['custom'] = $vattr['custom'];
					}
					$myrole = emd_get_curr_usr_role($myapp);
					if(!empty($this->boxes[$vattr['mid']]['title'])){
						$this->boxes[$vattr['mid']]['fields'][$kattr]['name'] = $vattr['label'];
						$this->boxes[$vattr['mid']]['fields'][$kattr]['list_visible'] = $vattr['list_visible'];
						$this->boxes[$vattr['mid']]['fields'][$kattr]['id'] = $kattr;
						//check editable        
						if($myrole != 'administrator' && !empty($ent_map_list[$this->post_type]['edit_attrs'][$myrole][$kattr]) && $ent_map_list[$this->post_type]['edit_attrs'][$myrole][$kattr] != 'edit'){
							$this->boxes[$vattr['mid']]['fields'][$kattr]['visible'] = 0;
						}
						else {
							$this->boxes[$vattr['mid']]['fields'][$kattr]['visible'] = 1;
						}
					}
					if($myrole != 'administrator' && !empty($ent_map_list[$this->post_type]['edit_attrs'][$myrole][$kattr]) && $ent_map_list[$this->post_type]['edit_attrs'][$myrole][$kattr] == 'not_show'){
						$search_args[$kattr]['disable'] = 'columns';
					}
					if(!empty($this->boxes[$vattr['mid']]['title'])){
						if ($vattr['display_type'] == 'user-adv') {
							$this->boxes[$vattr['mid']]['fields'][$kattr]['type'] = 'user';
						} else {
							$this->boxes[$vattr['mid']]['fields'][$kattr]['type'] = $vattr['display_type'];
						}
						if (isset($vattr['roles'])) {
							$this->boxes[$vattr['mid']]['fields'][$kattr]['query_args']['role'] = $vattr['roles'];
						}
						if (isset($vattr['dformat'])) {
							$this->boxes[$vattr['mid']]['fields'][$kattr]['js_options'] = $vattr['dformat'];
						}
						if(!empty($ent_map_list[$this->post_type]['max_files'][$kattr])){
							$this->boxes[$vattr['mid']]['fields'][$kattr]['max_file_uploads'] = $ent_map_list[$this->post_type]['max_files'][$kattr];
						}
						elseif(isset($vattr['max_file_uploads'])){
							$this->boxes[$vattr['mid']]['fields'][$kattr]['max_file_uploads'] = $vattr['max_file_uploads'];
						}
						if(!empty($ent_map_list[$this->post_type]['file_exts'][$kattr])){
							$this->boxes[$vattr['mid']]['fields'][$kattr]['mime_type'] = $ent_map_list[$this->post_type]['file_exts'][$kattr];
						}
						elseif(isset($vattr['file_ext'])){
							$this->boxes[$vattr['mid']]['fields'][$kattr]['mime_type'] = $vattr['file_ext'];
						}
						if(!empty($ent_map_list[$this->post_type]['max_file_size'][$kattr])){
							$this->boxes[$vattr['mid']]['fields'][$kattr]['max_file_size'] = $ent_map_list[$this->post_type]['max_file_size'][$kattr];
						}
						elseif(!empty($vattr['max_file_size'])){
							$this->boxes[$vattr['mid']]['fields'][$kattr]['max_file_size'] = $vattr['max_file_size'];
						}
						else {
							$server_size = ini_get('upload_max_filesize');
							if(preg_match('/M$/',$server_size)){
								$server_size = preg_replace('/M$/','',$server_size);
								$server_size = $server_size * 1000;
							}
							$this->boxes[$vattr['mid']]['fields'][$kattr]['max_file_size'] = $server_size;
						}
						$attr_fields = Array(
							'hidden_func',
							'no_update',
							'autoinc_start',
							'autoinc_incr',
							'multiple',
							'desc',
							'std',
							'options',
							'placeholder',
							'field_type',
							'address_field',
							'data-formula',
							'data-cell',
							'clone',
							'sort_clone',
							'max_clone',
							'max',
							'display_meta',
							'concat_string',
						);
						foreach ($attr_fields as $attr_field) {
							if (isset($vattr[$attr_field])) {
								$this->boxes[$vattr['mid']]['fields'][$kattr][$attr_field] = $vattr[$attr_field];
							}
						}
						if(!empty($vattr['select_list']) && in_array($vattr['select_list'],Array('country','state'))){
							$this->boxes[$vattr['mid']]['fields'][$kattr]['select_list'] = $vattr['select_list'];
							$def_country = 'US';	
							$def_state = '';	
							if($vattr['select_list'] == 'country'){
								if(!empty($ent_map_list[$this->post_type]['default_country'][$kattr])){
									$def_country = $ent_map_list[$this->post_type]['default_country'][$kattr];
								}
								$this->boxes[$vattr['mid']]['fields'][$kattr]['options'] = emd_get_country_list();
								$this->boxes[$vattr['mid']]['fields'][$kattr]['std'] = $def_country;
								$this->boxes[$vattr['mid']]['fields'][$kattr]['state'] = $vattr['dependent_state'];
								$this->boxes[$vattr['mid']]['fields'][$kattr]['class'] = 'emd-country ' . $kattr;
							}
							if($vattr['select_list'] == 'state'){
								if(!empty($ent_map_list[$this->post_type]['default_country'][$vattr['dependent_country']])){
									$def_country = $ent_map_list[$this->post_type]['default_country'][$vattr['dependent_country']];
								}
								if(!empty($ent_map_list[$this->post_type]['default_state'][$kattr])){
									$def_state = $ent_map_list[$this->post_type]['default_state'][$kattr];
								}
								$this->boxes[$vattr['mid']]['fields'][$kattr]['options'] = emd_get_country_states($def_country);
								$this->boxes[$vattr['mid']]['fields'][$kattr]['std'] = $def_state;
								$this->boxes[$vattr['mid']]['fields'][$kattr]['class'] = 'emd-state ' . $kattr;
								$this->boxes[$vattr['mid']]['fields'][$kattr]['dependent_country'] = $vattr['dependent_country'];
							}
						}
						else {
							$this->boxes[$vattr['mid']]['fields'][$kattr]['class'] = $kattr;
						}
						$this->boxes[$vattr['mid']]['fields'][$kattr]['app'] = $myapp;
						//validation
						if ($vattr['required'] == 1) {
							$this->boxes[$vattr['mid']]['validation']['rules'][$kattr]['required'] = true;
						} else {
							$this->boxes[$vattr['mid']]['validation']['rules'][$kattr]['required'] = false;
						}

						$valid_rules = Array(
							'email',
							'url',
							'number',
							'minlength',
							'maxlength',
							'digits',
							'creditcard',
							'phoneUS',
							'phoneUK',
							'letterswithbasicpunc',
							'alphanumeric',
							'lettersonly',
							'nowhitespace',
							'zipcodeUS',
							'postcodeUK',
							'integer',
							'vinUS',
							'ipv4',
							'ipv6',
							'maxWords',
							'minWords',
							'patern',
							'max',
							'min',
							'mobileUK',
							'uniqueAttr'
						);
						foreach ($valid_rules as $vrule) {
							if (isset($vattr[$vrule])) {
								$this->boxes[$vattr['mid']]['validation']['rules'][$kattr][$vrule] = $vattr[$vrule];
							}
						}
						if(!empty($vattr['conditional'])){
							$this->boxes[$vattr['mid']]['conditional'][$kattr] = $vattr['conditional'];
							$this->boxes[$vattr['mid']]['conditional'][$kattr]['type'] = $vattr['display_type'];
						}
					}
					if ($vattr['filterable'] == 1) {
						$filter_args[$kattr]['name'] = $vattr['label'];
						$filter_args[$kattr]['meta'] = $kattr;
						$filter_args[$kattr]['type'] = $vattr['display_type'];
						$filter_args[$kattr]['cast'] = strtoupper($vattr['type']);
						if (!empty($vattr['desc'])) {
							$filter_args[$kattr]['desc'] = $vattr['desc'];
						}
						if (!empty($vattr['options'])) {
							$filter_args[$kattr]['options'] = $vattr['options'];
						}
						if(!empty($vattr['select_list']) && in_array($vattr['select_list'],Array('country','state'))){
							if($vattr['select_list'] == 'country'){
								$filter_args[$kattr]['options'] = emd_get_country_list();
							}
							if($vattr['select_list'] == 'state'){
								$filter_args[$kattr]['options'] = emd_get_all_states();
							}
						}
						if (!empty($vattr['user_roles'])) {
							$filter_args[$kattr]['user_roles'] = $vattr['user_roles'];
						}
						if (!empty($vattr['dformat'])) {
							if (isset($vattr['dformat']['dateFormat'])) {
								$filter_args[$kattr]['date_format'] = $vattr['dformat']['dateFormat'];
							}
							if (isset($vattr['dformat']['timeFormat'])) {
								$filter_args[$kattr]['time_format'] = $vattr['dformat']['timeFormat'];
							}
						}
					}
				}
				else {
					if(!empty($this->boxes[$vattr['mid']]['title'])){
						$this->boxes[$vattr['mid']]['fields'][$kattr]['id'] = $kattr;
						$this->boxes[$vattr['mid']]['fields'][$kattr]['visible'] = 0;
						if ($vattr['display_type'] == 'user-adv') {
							$this->boxes[$vattr['mid']]['fields'][$kattr]['type'] = 'user';
						} else {
							$this->boxes[$vattr['mid']]['fields'][$kattr]['type'] = $vattr['display_type'];
						}
					}
				}
				if(!empty($this->boxes[$vattr['mid']]['title'])){
					$this->boxes[$vattr['mid']]['fields'] = apply_filters('emd_set_args_boxes',$this->boxes[$vattr['mid']]['fields'],$kattr,$this->post_type);
				}
			}
		}
		$tax_list = get_option($myapp . '_tax_list');
		if (!empty($tax_list[$this->post_type])) {
			foreach ($tax_list[$this->post_type] as $ktax => $vtax) {
				if(!empty($vtax['conditional']['attr_rules']) || !empty($vtax['conditional']['tax_rules'])){
					$this->boxes[$vattr['mid']]['tax_conditional'][$ktax] = $vtax['conditional'];
					$this->boxes[$vattr['mid']]['tax_conditional'][$ktax]['type'] = $vtax['cond_type'];
				}
			}
		}
		$ext_list = get_option($myapp . '_ext_field_list');
		if(!empty($ext_list[$this->post_type])) {
			foreach($ext_list[$this->post_type] as $ext_key => $ext_val){
				$search_args[$ext_key]['name'] = $ext_val['label'];
				$search_args[$ext_key]['meta'] = $ext_key;
				$search_args[$ext_key]['type'] = $ext_val['display_type'];
				$search_args[$ext_key]['cast'] = strtoupper($ext_val['type']);
				if (!empty($ext_val['options'])) {
					$search_args[$ext_key]['options'] = $ext_val['options'];
				}
				if ($ext_val['filterable'] == 1) {
					$filter_args[$ext_key]['name'] = $ext_val['label'];
					$filter_args[$ext_key]['meta'] = $ext_key;
					$filter_args[$ext_key]['type'] = $ext_val['display_type'];
					$filter_args[$ext_key]['cast'] = strtoupper($ext_val['type']);
					if (!empty($ext_val['desc'])) {
						$filter_args[$ext_key]['desc'] = $ext_val['desc'];
					}
					if (!empty($ext_val['options'])) {
						$filter_args[$ext_key]['options'] = $ext_val['options'];
					}
				}
			}
		}
		return Array($search_args,$filter_args);
	}
	public function duplicate_entity(){
		if(!empty($_GET['entity_id']) && wp_verify_nonce($_REQUEST['_wpnonce'],'duplicate_'.$_GET['entity_id'])){
			if(!empty($_GET['post_type']) && $this->post_type == $_GET['post_type']){
				$post = get_post((int) $_GET['entity_id']);
				$mypost['post_type'] = $post->post_type;
				$mypost['post_author'] = $post->post_author;
				$mypost['post_content'] = $post->post_content;
				$mypost['post_excerpt'] = $post->post_excerpt;
				$mypost['post_status'] = 'draft';
				$last_pdate = gmdate('Y-m-d H:i:s');
				$mypost['post_date'] = get_date_from_gmt($last_pdate);
				$mypost['post_date_gmt'] =  $last_pdate;
				$mypost['post_title'] = $post->post_title;
				if ($id = wp_insert_post($mypost)){
					$ent_list = get_option(str_replace('-', '_', $this->textdomain) . '_ent_list');
					$attr_list = get_option(str_replace('-', '_', $this->textdomain) . '_attr_list');
					$tax_list = get_option(str_replace('-', '_', $this->textdomain) . '_tax_list');
					$rel_list = get_option(str_replace('-', '_', $this->textdomain) . '_rel_list');
					if(!empty($attr_list[$post->post_type])){
						foreach($attr_list[$post->post_type] as $kattr => $myattr){
							if(in_array($kattr,$ent_list[$post->post_type]['unique_keys'])){
								if(!empty($myattr['hidden_func']) && $myattr['hidden_func'] == 'autoinc'){
									$autoinc_start = $myattr['autoinc_start'];
									$autoinc_incr = $myattr['autoinc_incr'];
									$attr_val = get_option($kattr . "_autoinc",$autoinc_start);
                                                			$attr_val = $attr_val + $autoinc_incr;
									update_post_meta($id,$kattr,$attr_val);
									update_option($kattr . "_autoinc", $attr_val);
								}
								elseif(!empty($myattr['hidden_func']) && $myattr['hidden_func'] == 'unique_id'){
									$attr_val = uniqid($id, false);
									update_post_meta($id,$kattr,$attr_val);
								}
								elseif($meta_value == 'concat'){
									$attr_val = emd_get_hidden_func('concat',str_replace('-', '_', $this->textdomain),$myattr['concat_string'],$id);
									update_post_meta($id,$kattr,$attr_val);
								}
							}
							else {
								$attr_val = get_post_meta($post->ID,$kattr,true);
								if(isset($attr_val)){
									update_post_meta($id,$kattr,$attr_val);
								}
							}
						}
					}
					if(!empty($tax_list[$post->post_type])){
						foreach($tax_list[$post->post_type] as $ktax => $mytax){
							$tax_val = wp_get_object_terms($post->ID,$ktax,Array('fields'=>'ids')); 
							wp_set_object_terms($id,$tax_val,$ktax); 
						}
					}
					if(!empty($rel_list)){
						foreach ($rel_list as $krel => $vrel) {
							$rel_type = str_replace("rel_", "", $krel);
							if ($vrel['from'] == $post->post_type || $vrel['to'] == $post->post_type) {
								if(p2p_type($rel_type)){
									$connected = p2p_type($rel_type)->get_connected($post->ID,Array('posts_per_page' => - 1));
									if(!empty($connected->posts)){
										foreach($connected->posts as $mycon_post){
											if($post->ID == $mycon_post->p2p_from){
												$c = p2p_type($rel_type)->connect($id,$mycon_post->p2p_to);
											}					
											elseif($post->ID == $mycon_post->p2p_to){
												$b= p2p_type($rel_type)->connect($id,$mycon_post->p2p_from);
											}		
										}
									}			
								}
							}
						}
					}
					wp_redirect(admin_url('post.php?post=' . $id . '&action=edit'));
					exit();
				}
				else {
					wp_redirect(wp_get_referer());
					exit;
				}
			}
		}
	}
	public function duplicate_link($actions, $post){
		if($post->post_type == $this->post_type && in_array($post->post_status, Array('publish','future','private')) && current_user_can('edit_' . $post->post_type . 's')){
			$duplicate_url = add_query_arg(array('action'=>'emd_duplicate_entity', 'entity_id'=>$post->ID, '_wpnonce'=> wp_create_nonce('duplicate_'.$post->ID)));
			$actions['duplicate'] = '<a href="'. esc_url($duplicate_url) .'" title="'. __('Duplicate','emd-plugins') .'">'.__('Duplicate','emd-plugins').'</a>';
	    	}
		return $actions;
	}
}
