<?php
/**
 * Entity Class
 *
 * @package SOFTWARE_ISSUE_MANAGER
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_Project Class
 * @since WPAS 4.0
 */
class Emd_Project extends Emd_Entity {
	protected $post_type = 'emd_project';
	protected $textdomain = 'software-issue-manager';
	protected $sing_label;
	protected $plural_label;
	protected $menu_entity;
	protected $id;
	/**
	 * Initialize entity class
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function __construct() {
		add_action('init', array(
			$this,
			'set_filters'
		) , 1);
		add_action('admin_init', array(
			$this,
			'set_metabox'
		));
		add_filter('wp_dropdown_users', array(
			$this,
			'author_override'
		));
		add_action('save_post', array(
			$this,
			'update_form_submitted_by'
		) , 11, 3);
		add_filter('wp_insert_post_data', array(
			$this,
			'update_author_data'
		) , 10, 2);
		add_action('save_post', array(
			$this,
			'change_title'
		) , 99, 2);
		add_filter('post_updated_messages', array(
			$this,
			'updated_messages'
		));
		add_action('admin_menu', array(
			$this,
			'add_menu_link'
		));
		add_action('admin_head-edit.php', array(
			$this,
			'add_opt_button'
		));
		$is_adv_filt_ext = apply_filters('emd_adv_filter_on', 0);
		if ($is_adv_filt_ext === 0) {
			add_action('manage_emd_project_posts_custom_column', array(
				$this,
				'custom_columns'
			) , 10, 2);
			add_filter('manage_emd_project_posts_columns', array(
				$this,
				'column_headers'
			));
		}
		add_action('admin_init', array(
			$this,
			'set_single_taxs'
		));
		add_filter('post_row_actions', array(
			$this,
			'duplicate_link'
		) , 10, 2);
		add_action('admin_action_emd_duplicate_entity', array(
			$this,
			'duplicate_entity'
		));
	}
	public function set_single_taxs() {
		global $pagenow;
		if ('post-new.php' === $pagenow || 'post.php' === $pagenow) {
			if ((isset($_REQUEST['post_type']) && $this->post_type === $_REQUEST['post_type']) || (isset($_REQUEST['post']) && get_post_type($_REQUEST['post']) === $this->post_type)) {
				$this->stax = new Emd_Single_Taxonomy('software-issue-manager');
			}
		}
	}
	public function change_title_disable_emd_temp($title, $id) {
		$post = get_post($id);
		if ($this->post_type == $post->post_type && (!empty($this->id) && $this->id == $id)) {
			return '';
		}
		return $title;
	}
	public function update_author_data($data, $postarr) {
		if (isset($_REQUEST['post_author_override']) && $_REQUEST['post_author_override'] == 0) {
			$data['post_author'] = 0;
		}
		return $data;
	}
	public function update_form_submitted_by($post_id, $post, $update) {
		if ($update && $post->post_type == 'emd_project') {
			$ulogin = "";
			if (isset($_REQUEST['post_author_override']) && $_REQUEST['post_author_override'] == 0) {
				$ulogin = 'Visitor';
			} elseif (!empty($post->post_author)) {
				$user = get_user_by('id', $post->post_author);
				$ulogin = $user->user_login;
			}
			if (!empty($_REQUEST['wpas_form_submitted_by']) && $ulogin != $_REQUEST['wpas_form_submitted_by']) {
				update_post_meta($post_id, 'wpas_form_submitted_by', $ulogin);
			}
		}
	}
	public function author_override($output) {
		global $pagenow, $post, $user_ID;
		if ('post-new.php' === $pagenow || 'post.php' === $pagenow) {
			if ((isset($_GET['post_type']) && $this->post_type === $_GET['post_type']) || (isset($_GET['post']) && get_post_type($_GET['post']) === $this->post_type)) {
				// return if this isn't the theme author override dropdown
				if (!preg_match('/post_author_override/', $output)) return $output;
				// return if we've already replaced the list (end recursion)
				if (preg_match('/post_author_override_replaced/', $output)) return $output;
				//get dropdown values all users who have edit cap for this entity
				// Get valid roles
				global $wp_roles;
				$roles = $wp_roles->role_objects;
				$valid_roles = array();
				$user_ids = array();
				if (!current_user_can('set_author_' . $this->post_type . 's')) {
					//current user
					$user_ids[] = get_current_user_id();
				} else {
					foreach ($roles as $role) {
						if (isset($role->capabilities['edit_' . $this->post_type . 's'])) {
							$valid_roles[] = $role->name;
						}
					}
					if (empty($valid_roles)) return $output;
					// Get user IDs
					foreach ($valid_roles as $role) {
						$users = get_users(array(
							'role' => $role
						));
						if (!empty($users)) {
							foreach ($users as $user) {
								$user_ids[] = $user->ID;
							}
						}
					}
				}
				if (empty($user_ids)) return $output;
				// replacement call to wp_dropdown_users
				$output = wp_dropdown_users(array(
					'echo' => 0,
					'show_option_none' => 'Visitor',
					'option_none_value' => '0',
					'name' => 'post_author_override_replaced',
					'selected' => empty($post->ID) ? $user_ID : $post->post_author,
					'include' => implode(',', $user_ids) ,
					'include_selected' => true
				));
				// put the original name back
				$output = preg_replace('/post_author_override_replaced/', 'post_author_override', $output);
			}
		}
		return $output;
	}
	/**
	 * Get column header list in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param array $columns
	 *
	 * @return array $columns
	 */
	public function column_headers($columns) {
		$ent_list = get_option(str_replace("-", "_", $this->textdomain) . '_ent_list');
		if (!empty($ent_list[$this->post_type]['featured_img'])) {
			$columns['featured_img'] = __('Featured Image', $this->textdomain);
		}
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if (!in_array($fkey, Array(
					'wpas_form_name',
					'wpas_form_submitted_by',
					'wpas_form_submitted_ip'
				)) && !in_array($mybox_field['type'], Array(
					'textarea',
					'wysiwyg'
				)) && $mybox_field['list_visible'] == 1) {
					$columns[$fkey] = $mybox_field['name'];
				}
			}
		}
		$taxonomies = get_object_taxonomies($this->post_type, 'objects');
		if (!empty($taxonomies)) {
			$tax_list = get_option(str_replace("-", "_", $this->textdomain) . '_tax_list');
			foreach ($taxonomies as $taxonomy) {
				if (!empty($tax_list[$this->post_type][$taxonomy->name]) && $tax_list[$this->post_type][$taxonomy->name]['list_visible'] == 1) {
					$columns[$taxonomy->name] = $taxonomy->label;
				}
			}
		}
		$rel_list = get_option(str_replace("-", "_", $this->textdomain) . '_rel_list');
		if (!empty($rel_list)) {
			foreach ($rel_list as $krel => $rel) {
				if ($rel['from'] == $this->post_type && in_array($rel['show'], Array(
					'any',
					'from'
				))) {
					$columns[$krel] = $rel['from_title'];
				} elseif ($rel['to'] == $this->post_type && in_array($rel['show'], Array(
					'any',
					'to'
				))) {
					$columns[$krel] = $rel['to_title'];
				}
			}
		}
		return $columns;
	}
	/**
	 * Get custom column values in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param int $column_id
	 * @param int $post_id
	 *
	 * @return string $value
	 */
	public function custom_columns($column_id, $post_id) {
		if (taxonomy_exists($column_id) == true) {
			$terms = get_the_terms($post_id, $column_id);
			$ret = array();
			if (!empty($terms)) {
				foreach ($terms as $term) {
					$url = add_query_arg(array(
						'post_type' => $this->post_type,
						'term' => $term->slug,
						'taxonomy' => $column_id
					) , admin_url('edit.php'));
					$a_class = preg_replace('/^emd_/', '', $this->post_type);
					$ret[] = sprintf('<a href="%s"  class="' . $a_class . '-tax ' . $term->slug . '">%s</a>', $url, $term->name);
				}
			}
			echo implode(', ', $ret);
			return;
		}
		$rel_list = get_option(str_replace("-", "_", $this->textdomain) . '_rel_list');
		if (!empty($rel_list) && !empty($rel_list[$column_id])) {
			$rel_arr = $rel_list[$column_id];
			if ($rel_arr['from'] == $this->post_type) {
				$other_ptype = $rel_arr['to'];
			} elseif ($rel_arr['to'] == $this->post_type) {
				$other_ptype = $rel_arr['from'];
			}
			$column_id = str_replace('rel_', '', $column_id);
			if (function_exists('p2p_type') && p2p_type($column_id)) {
				$rel_args = apply_filters('emd_ext_p2p_add_query_vars', array(
					'posts_per_page' => - 1
				) , Array(
					$other_ptype
				));
				$connected = p2p_type($column_id)->get_connected($post_id, $rel_args);
				$ptype_obj = get_post_type_object($this->post_type);
				$edit_cap = $ptype_obj->cap->edit_posts;
				$ret = array();
				if (empty($connected->posts)) return '&ndash;';
				foreach ($connected->posts as $myrelpost) {
					$rel_title = get_the_title($myrelpost->ID);
					$rel_title = apply_filters('emd_ext_p2p_connect_title', $rel_title, $myrelpost, '');
					$url = get_permalink($myrelpost->ID);
					$url = apply_filters('emd_ext_connected_ptype_url', $url, $myrelpost, $edit_cap);
					$ret[] = sprintf('<a href="%s" title="%s" target="_blank">%s</a>', $url, $rel_title, $rel_title);
				}
				echo implode(', ', $ret);
				return;
			}
		}
		$value = get_post_meta($post_id, $column_id, true);
		$type = "";
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if ($fkey == $column_id) {
					$type = $mybox_field['type'];
					break;
				}
			}
		}
		if ($column_id == 'featured_img') {
			$type = 'featured_img';
		}
		switch ($type) {
			case 'featured_img':
				$thumb_url = wp_get_attachment_image_src(get_post_thumbnail_id($post_id) , 'thumbnail');
				if (!empty($thumb_url)) {
					$value = "<img style='max-width:100%;height:auto;' src='" . $thumb_url[0] . "' >";
				}
			break;
			case 'plupload_image':
			case 'image':
			case 'thickbox_image':
				$image_list = emd_mb_meta($column_id, 'type=image');
				$value = "";
				if (!empty($image_list)) {
					$myimage = current($image_list);
					$value = "<img style='max-width:100%;height:auto;' src='" . $myimage['url'] . "' >";
				}
			break;
			case 'user':
			case 'user-adv':
				$user_id = emd_mb_meta($column_id);
				if (!empty($user_id)) {
					$user_info = get_userdata($user_id);
					$value = $user_info->display_name;
				}
			break;
			case 'file':
				$file_list = emd_mb_meta($column_id, 'type=file');
				if (!empty($file_list)) {
					$value = "";
					foreach ($file_list as $myfile) {
						$fsrc = wp_mime_type_icon($myfile['ID']);
						$value.= "<a style='margin:5px;' href='" . $myfile['url'] . "' target='_blank'><img src='" . $fsrc . "' title='" . $myfile['name'] . "' width='20' /></a>";
					}
				}
			break;
			case 'radio':
			case 'checkbox_list':
			case 'select':
			case 'select_advanced':
				$value = emd_get_attr_val(str_replace("-", "_", $this->textdomain) , $post_id, $this->post_type, $column_id);
			break;
			case 'checkbox':
				if ($value == 1) {
					$value = '<span class="dashicons dashicons-yes"></span>';
				} elseif ($value == 0) {
					$value = '<span class="dashicons dashicons-no-alt"></span>';
				}
			break;
			case 'rating':
				$value = apply_filters('emd_get_rating_value', $value, Array(
					'meta' => $column_id
				) , $post_id);
			break;
		}
		if (is_array($value)) {
			$value = "<div class='clonelink'>" . implode("</div><div class='clonelink'>", $value) . "</div>";
		}
		echo $value;
	}
	/**
	 * Register post type and taxonomies and set initial values for taxs
	 *
	 * @since WPAS 4.0
	 *
	 */
	public static function register() {
		$labels = array(
			'name' => __('Projects', 'software-issue-manager') ,
			'singular_name' => __('Project', 'software-issue-manager') ,
			'add_new' => __('Add New', 'software-issue-manager') ,
			'add_new_item' => __('Add New Project', 'software-issue-manager') ,
			'edit_item' => __('Edit Project', 'software-issue-manager') ,
			'new_item' => __('New Project', 'software-issue-manager') ,
			'all_items' => __('All Projects', 'software-issue-manager') ,
			'view_item' => __('View Project', 'software-issue-manager') ,
			'search_items' => __('Search Projects', 'software-issue-manager') ,
			'not_found' => __('No Projects Found', 'software-issue-manager') ,
			'not_found_in_trash' => __('No Projects Found In Trash', 'software-issue-manager') ,
			'menu_name' => __('Projects', 'software-issue-manager') ,
		);
		$ent_map_list = get_option('software_issue_manager_ent_map_list', Array());
		$myrole = emd_get_curr_usr_role('software_issue_manager');
		if (!empty($ent_map_list['emd_project']['rewrite'])) {
			$rewrite = $ent_map_list['emd_project']['rewrite'];
		} else {
			$rewrite = 'projects';
		}
		$supports = Array(
			'author',
		);
		if (empty($ent_map_list['emd_project']['attrs']['blt_content']) || $ent_map_list['emd_project']['attrs']['blt_content'] != 'hide') {
			if (empty($ent_map_list['emd_project']['edit_attrs'])) {
				$supports[] = 'editor';
			} elseif ($myrole == 'administrator') {
				$supports[] = 'editor';
			} elseif ($myrole != 'administrator' && !empty($ent_map_list['emd_project']['edit_attrs'][$myrole]['blt_content']) && $ent_map_list['emd_project']['edit_attrs'][$myrole]['blt_content'] == 'edit') {
				$supports[] = 'editor';
			}
		}
		register_post_type('emd_project', array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'description' => __('A project is a collection of related issues. Projects have a unique version number, specific start and end dates.', 'software-issue-manager') ,
			'show_in_menu' => true,
			'menu_position' => 6,
			'has_archive' => true,
			'exclude_from_search' => false,
			'rewrite' => array(
				'slug' => $rewrite
			) ,
			'can_export' => true,
			'show_in_rest' => false,
			'hierarchical' => false,
			'menu_icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0ZWQgYnkgSWNvTW9vbi5pbyAtLT4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDIwIDIwIj4KPGc+CjwvZz4KCTxwYXRoIGQ9Ik0xMS4yNTUgNS42NWw0Ljc0LTEuNTkgMy45NzYgMTEuODUxLTQuNzQgMS41OXpNMCAxNy41aDV2LTEzLjc1aC01djEzLjc1ek0xLjI1IDYuMjVoMi41djEuMjVoLTIuNXYtMS4yNXpNNi4yNSAxNy41aDV2LTEzLjc1aC01djEzLjc1ek03LjUgNi4yNWgyLjV2MS4yNWgtMi41di0xLjI1eiIgZmlsbD0iIzM2MzYzNiI+PC9wYXRoPgo8L3N2Zz4K',
			'map_meta_cap' => 'true',
			'taxonomies' => array() ,
			'capability_type' => 'emd_project',
			'supports' => $supports,
		));
		$tax_settings = get_option('software_issue_manager_tax_settings', Array());
		$myrole = emd_get_curr_usr_role('software_issue_manager');
		$project_priority_nohr_labels = array(
			'name' => __('Priorities', 'software-issue-manager') ,
			'singular_name' => __('Priority', 'software-issue-manager') ,
			'search_items' => __('Search Priorities', 'software-issue-manager') ,
			'popular_items' => __('Popular Priorities', 'software-issue-manager') ,
			'all_items' => __('All', 'software-issue-manager') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Priority', 'software-issue-manager') ,
			'update_item' => __('Update Priority', 'software-issue-manager') ,
			'add_new_item' => __('Add New Priority', 'software-issue-manager') ,
			'new_item_name' => __('Add New Priority Name', 'software-issue-manager') ,
			'separate_items_with_commas' => __('Seperate Priorities with commas', 'software-issue-manager') ,
			'add_or_remove_items' => __('Add or Remove Priorities', 'software-issue-manager') ,
			'choose_from_most_used' => __('Choose from the most used Priorities', 'software-issue-manager') ,
			'menu_name' => __('Priorities', 'software-issue-manager') ,
		);
		if (empty($tax_settings['project_priority']['hide']) || (!empty($tax_settings['project_priority']['hide']) && $tax_settings['project_priority']['hide'] != 'hide')) {
			if (!empty($tax_settings['project_priority']['rewrite'])) {
				$rewrite = $tax_settings['project_priority']['rewrite'];
			} else {
				$rewrite = 'project_priority';
			}
			$targs = array(
				'hierarchical' => false,
				'labels' => $project_priority_nohr_labels,
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_tagcloud' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array(
					'slug' => $rewrite,
				) ,
				'show_in_rest' => false,
				'capabilities' => array(
					'manage_terms' => 'manage_project_priority',
					'edit_terms' => 'edit_project_priority',
					'delete_terms' => 'delete_project_priority',
					'assign_terms' => 'assign_project_priority'
				) ,
			);
			if ($myrole != 'administrator' && !empty($tax_settings['project_priority']['edit'][$myrole]) && $tax_settings['project_priority']['edit'][$myrole] != 'edit') {
				$targs['meta_box_cb'] = false;
			}
			register_taxonomy('project_priority', array(
				'emd_project'
			) , $targs);
		}
		$project_status_nohr_labels = array(
			'name' => __('Statuses', 'software-issue-manager') ,
			'singular_name' => __('Status', 'software-issue-manager') ,
			'search_items' => __('Search Statuses', 'software-issue-manager') ,
			'popular_items' => __('Popular Statuses', 'software-issue-manager') ,
			'all_items' => __('All', 'software-issue-manager') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Status', 'software-issue-manager') ,
			'update_item' => __('Update Status', 'software-issue-manager') ,
			'add_new_item' => __('Add New Status', 'software-issue-manager') ,
			'new_item_name' => __('Add New Status Name', 'software-issue-manager') ,
			'separate_items_with_commas' => __('Seperate Statuses with commas', 'software-issue-manager') ,
			'add_or_remove_items' => __('Add or Remove Statuses', 'software-issue-manager') ,
			'choose_from_most_used' => __('Choose from the most used Statuses', 'software-issue-manager') ,
			'menu_name' => __('Statuses', 'software-issue-manager') ,
		);
		if (empty($tax_settings['project_status']['hide']) || (!empty($tax_settings['project_status']['hide']) && $tax_settings['project_status']['hide'] != 'hide')) {
			if (!empty($tax_settings['project_status']['rewrite'])) {
				$rewrite = $tax_settings['project_status']['rewrite'];
			} else {
				$rewrite = 'project_status';
			}
			$targs = array(
				'hierarchical' => false,
				'labels' => $project_status_nohr_labels,
				'public' => true,
				'show_ui' => true,
				'show_in_nav_menus' => true,
				'show_in_menu' => true,
				'show_tagcloud' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var' => true,
				'rewrite' => array(
					'slug' => $rewrite,
				) ,
				'show_in_rest' => false,
				'capabilities' => array(
					'manage_terms' => 'manage_project_status',
					'edit_terms' => 'edit_project_status',
					'delete_terms' => 'delete_project_status',
					'assign_terms' => 'assign_project_status'
				) ,
			);
			if ($myrole != 'administrator' && !empty($tax_settings['project_status']['edit'][$myrole]) && $tax_settings['project_status']['edit'][$myrole] != 'edit') {
				$targs['meta_box_cb'] = false;
			}
			register_taxonomy('project_status', array(
				'emd_project'
			) , $targs);
		}
		$tax_list = get_option('software_issue_manager_tax_list');
		$init_tax = get_option('software_issue_manager_init_tax', Array());
		if (!empty($tax_list['emd_project'])) {
			foreach ($tax_list['emd_project'] as $keytax => $mytax) {
				if (!empty($mytax['init_values']) && (empty($init_tax['emd_project']) || (!empty($init_tax['emd_project']) && !in_array($keytax, $init_tax['emd_project'])))) {
					$set_tax_terms = Array();
					foreach ($mytax['init_values'] as $myinit) {
						$set_tax_terms[] = $myinit;
					}
					self::set_taxonomy_init($set_tax_terms, $keytax);
					$init_tax['emd_project'][] = $keytax;
				}
			}
			update_option('software_issue_manager_init_tax', $init_tax);
		}
	}
	/**
	 * Set metabox fields,labels,filters, comments, relationships if exists
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function set_filters() {
		do_action('emd_ext_class_init', $this);
		$search_args = Array();
		$filter_args = Array();
		$this->sing_label = __('Project', 'software-issue-manager');
		$this->plural_label = __('Projects', 'software-issue-manager');
		$this->menu_entity = 'emd_project';
		$this->boxes['project_info_emd_project_0'] = array(
			'id' => 'project_info_emd_project_0',
			'title' => __('Project Info', 'software-issue-manager') ,
			'app_name' => 'software_issue_manager',
			'pages' => array(
				'emd_project'
			) ,
			'context' => 'normal',
		);
		$this->boxes['emd_cust_field_meta_box'] = array(
			'id' => 'emd_cust_field_meta_box',
			'title' => __('Custom Fields', 'software-issue-manager') ,
			'app_name' => 'software_issue_manager',
			'pages' => array(
				'emd_project'
			) ,
			'context' => 'normal',
			'priority' => 'low'
		);
		list($search_args, $filter_args) = $this->set_args_boxes();
		if (empty($this->boxes['emd_cust_field_meta_box']['fields'])) {
			unset($this->boxes['emd_cust_field_meta_box']);
		}
		if (!post_type_exists($this->post_type) || in_array($this->post_type, Array(
			'post',
			'page'
		))) {
			self::register();
		}
		do_action('emd_set_adv_filtering', $this->post_type, $search_args, $this->boxes, $filter_args, $this->textdomain, $this->plural_label);
		add_action('admin_notices', array(
			$this,
			'show_lite_filters'
		));
		$ent_map_list = get_option(str_replace('-', '_', $this->textdomain) . '_ent_map_list');
		if (!function_exists('p2p_register_connection_type')) {
			return;
		}
		$rel_list = get_option(str_replace('-', '_', $this->textdomain) . '_rel_list');
		$myrole = emd_get_curr_usr_role('software_issue_manager');
		if (empty($ent_map_list['emd_project']['hide_rels']['rel_project_issues']) || $ent_map_list['emd_project']['hide_rels']['rel_project_issues'] != 'hide') {
			if ($myrole != 'administrator' && !empty($ent_map_list['emd_project']['edit_rels'][$myrole]['rel_project_issues']) && $ent_map_list['emd_project']['edit_rels'][$myrole]['rel_project_issues'] != 'edit') {
				$admin_box = 'none';
			} else {
				$admin_box = array(
					'show' => 'to',
					'context' => 'advanced'
				);
			}
			$rel_fields = Array();
			p2p_register_connection_type(array(
				'name' => 'project_issues',
				'from' => 'emd_project',
				'to' => 'emd_issue',
				'sortable' => 'any',
				'reciprocal' => false,
				'cardinality' => 'many-to-many',
				'title' => array(
					'from' => __('Project Issues', 'software-issue-manager') ,
					'to' => __('Affected Projects', 'software-issue-manager')
				) ,
				'from_labels' => array(
					'singular_name' => __('Project', 'software-issue-manager') ,
					'search_items' => __('Search Projects', 'software-issue-manager') ,
					'not_found' => __('No Projects found.', 'software-issue-manager') ,
				) ,
				'to_labels' => array(
					'singular_name' => __('Issue', 'software-issue-manager') ,
					'search_items' => __('Search Issues', 'software-issue-manager') ,
					'not_found' => __('No Issues found.', 'software-issue-manager') ,
				) ,
				'fields' => $rel_fields,
				'admin_box' => $admin_box,
			));
		}
	}
	/**
	 * Initialize metaboxes
	 * @since WPAS 4.5
	 *
	 */
	public function set_metabox() {
		if (class_exists('EMD_Meta_Box') && is_array($this->boxes)) {
			foreach ($this->boxes as $meta_box) {
				new EMD_Meta_Box($meta_box);
			}
		}
	}
	/**
	 * Change content for created frontend views
	 * @since WPAS 4.0
	 * @param string $content
	 *
	 * @return string $content
	 */
	public function change_content($content) {
		global $post;
		$layout = "";
		$this->id = $post->ID;
		$tools = get_option('software_issue_manager_tools');
		if (!empty($tools['disable_emd_templates'])) {
			add_filter('the_title', array(
				$this,
				'change_title_disable_emd_temp'
			) , 10, 2);
		}
		if (get_post_type() == $this->post_type && is_single()) {
			ob_start();
			do_action('emd_single_before_content', $this->textdomain, $this->post_type);
			emd_get_template_part($this->textdomain, 'single', 'emd-project');
			do_action('emd_single_after_content', $this->textdomain, $this->post_type);
			$layout = ob_get_clean();
		}
		if ($layout != "") {
			$content = $layout;
		}
		if (!empty($tools['disable_emd_templates'])) {
			remove_filter('the_title', array(
				$this,
				'change_title_disable_emd_temp'
			) , 10, 2);
		}
		return $content;
	}
	/**
	 * Add operations and add new submenu hook
	 * @since WPAS 4.4
	 */
	public function add_menu_link() {
		add_submenu_page(null, __('CSV Import/Export', 'software-issue-manager') , __('CSV Import/Export', 'software-issue-manager') , 'manage_operations_emd_projects', 'operations_emd_project', array(
			$this,
			'get_operations'
		));
	}
	/**
	 * Display operations page
	 * @since WPAS 4.0
	 */
	public function get_operations() {
		if (current_user_can('manage_operations_emd_projects')) {
			$myapp = str_replace("-", "_", $this->textdomain);
			if (!function_exists('emd_operations_entity')) {
				emd_lite_get_operations('opr', $this->plural_label, $this->textdomain);
			} else {
				do_action('emd_operations_entity', $this->post_type, $this->plural_label, $this->sing_label, $myapp, $this->menu_entity);
			}
		}
	}
	public function show_lite_filters() {
		if (class_exists('EMD_AFC')) {
			return;
		}
		global $pagenow;
		if (get_post_type() == $this->post_type && $pagenow == 'edit.php') {
			emd_lite_get_filters($this->textdomain);
		}
	}
}
new Emd_Project;