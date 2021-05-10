<?php
/**
 * Entity Class
 *
 * @package SOFTWARE_ISSUE_MANAGER
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_Issue Class
 * @since WPAS 4.0
 */
class Emd_Issue extends Emd_Entity {
	protected $post_type = 'emd_issue';
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
		add_action('admin_menu', array(
			$this,
			'add_top_menu_link'
		) , 1);
		add_filter('parent_file', array(
			$this,
			'tax_submenus'
		));
		$is_adv_filt_ext = apply_filters('emd_adv_filter_on', 0);
		if ($is_adv_filt_ext === 0) {
			add_action('manage_emd_issue_posts_custom_column', array(
				$this,
				'custom_columns'
			) , 10, 2);
			add_filter('manage_emd_issue_posts_columns', array(
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
		if ($update && $post->post_type == 'emd_issue') {
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
			'name' => __('Issues', 'software-issue-manager') ,
			'singular_name' => __('Issue', 'software-issue-manager') ,
			'add_new' => __('Add New', 'software-issue-manager') ,
			'add_new_item' => __('Add New Issue', 'software-issue-manager') ,
			'edit_item' => __('Edit Issue', 'software-issue-manager') ,
			'new_item' => __('New Issue', 'software-issue-manager') ,
			'all_items' => __('All Issues', 'software-issue-manager') ,
			'view_item' => __('View Issue', 'software-issue-manager') ,
			'search_items' => __('Search Issues', 'software-issue-manager') ,
			'not_found' => __('No Issues Found', 'software-issue-manager') ,
			'not_found_in_trash' => __('No Issues Found In Trash', 'software-issue-manager') ,
			'menu_name' => __('Issues', 'software-issue-manager') ,
		);
		$ent_map_list = get_option('software_issue_manager_ent_map_list', Array());
		$myrole = emd_get_curr_usr_role('software_issue_manager');
		if (!empty($ent_map_list['emd_issue']['rewrite'])) {
			$rewrite = $ent_map_list['emd_issue']['rewrite'];
		} else {
			$rewrite = 'issues';
		}
		$supports = Array(
			'author',
			'revisions',
			'comments'
		);
		if (empty($ent_map_list['emd_issue']['attrs']['blt_title']) || $ent_map_list['emd_issue']['attrs']['blt_title'] != 'hide') {
			if (empty($ent_map_list['emd_issue']['edit_attrs'])) {
				$supports[] = 'title';
			} elseif ($myrole == 'administrator') {
				$supports[] = 'title';
			} elseif ($myrole != 'administrator' && !empty($ent_map_list['emd_issue']['edit_attrs'][$myrole]['blt_title']) && $ent_map_list['emd_issue']['edit_attrs'][$myrole]['blt_title'] == 'edit') {
				$supports[] = 'title';
			}
		}
		if (empty($ent_map_list['emd_issue']['attrs']['blt_content']) || $ent_map_list['emd_issue']['attrs']['blt_content'] != 'hide') {
			if (empty($ent_map_list['emd_issue']['edit_attrs'])) {
				$supports[] = 'editor';
			} elseif ($myrole == 'administrator') {
				$supports[] = 'editor';
			} elseif ($myrole != 'administrator' && !empty($ent_map_list['emd_issue']['edit_attrs'][$myrole]['blt_content']) && $ent_map_list['emd_issue']['edit_attrs'][$myrole]['blt_content'] == 'edit') {
				$supports[] = 'editor';
			}
		}
		register_post_type('emd_issue', array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'description' => __('An issue is anything that might affect the project meeting its goals such as bugs, tasks, and feature requests that occur during a project\'s life cycle.', 'software-issue-manager') ,
			'show_in_menu' => '',
			'menu_position' => null,
			'has_archive' => true,
			'exclude_from_search' => false,
			'rewrite' => array(
				'slug' => $rewrite
			) ,
			'can_export' => true,
			'show_in_rest' => false,
			'hierarchical' => false,
			'map_meta_cap' => 'true',
			'taxonomies' => array() ,
			'capability_type' => 'emd_issue',
			'supports' => $supports,
		));
		$tax_settings = get_option('software_issue_manager_tax_settings', Array());
		$myrole = emd_get_curr_usr_role('software_issue_manager');
		$issue_priority_nohr_labels = array(
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
		if (empty($tax_settings['issue_priority']['hide']) || (!empty($tax_settings['issue_priority']['hide']) && $tax_settings['issue_priority']['hide'] != 'hide')) {
			if (!empty($tax_settings['issue_priority']['rewrite'])) {
				$rewrite = $tax_settings['issue_priority']['rewrite'];
			} else {
				$rewrite = 'issue_priority';
			}
			$targs = array(
				'hierarchical' => false,
				'labels' => $issue_priority_nohr_labels,
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
					'manage_terms' => 'manage_issue_priority',
					'edit_terms' => 'edit_issue_priority',
					'delete_terms' => 'delete_issue_priority',
					'assign_terms' => 'assign_issue_priority'
				) ,
			);
			if ($myrole != 'administrator' && !empty($tax_settings['issue_priority']['edit'][$myrole]) && $tax_settings['issue_priority']['edit'][$myrole] != 'edit') {
				$targs['meta_box_cb'] = false;
			}
			register_taxonomy('issue_priority', array(
				'emd_issue'
			) , $targs);
		}
		$operating_system_nohr_labels = array(
			'name' => __('Operating Systems', 'software-issue-manager') ,
			'singular_name' => __('Operating System', 'software-issue-manager') ,
			'search_items' => __('Search Operating Systems', 'software-issue-manager') ,
			'popular_items' => __('Popular Operating Systems', 'software-issue-manager') ,
			'all_items' => __('All', 'software-issue-manager') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Operating System', 'software-issue-manager') ,
			'update_item' => __('Update Operating System', 'software-issue-manager') ,
			'add_new_item' => __('Add New Operating System', 'software-issue-manager') ,
			'new_item_name' => __('Add New Operating System Name', 'software-issue-manager') ,
			'separate_items_with_commas' => __('Seperate Operating Systems with commas', 'software-issue-manager') ,
			'add_or_remove_items' => __('Add or Remove Operating Systems', 'software-issue-manager') ,
			'choose_from_most_used' => __('Choose from the most used Operating Systems', 'software-issue-manager') ,
			'menu_name' => __('Operating Systems', 'software-issue-manager') ,
		);
		if (empty($tax_settings['operating_system']['hide']) || (!empty($tax_settings['operating_system']['hide']) && $tax_settings['operating_system']['hide'] != 'hide')) {
			if (!empty($tax_settings['operating_system']['rewrite'])) {
				$rewrite = $tax_settings['operating_system']['rewrite'];
			} else {
				$rewrite = 'operating_system';
			}
			$targs = array(
				'hierarchical' => false,
				'labels' => $operating_system_nohr_labels,
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
					'manage_terms' => 'manage_operating_system',
					'edit_terms' => 'edit_operating_system',
					'delete_terms' => 'delete_operating_system',
					'assign_terms' => 'assign_operating_system'
				) ,
			);
			if ($myrole != 'administrator' && !empty($tax_settings['operating_system']['edit'][$myrole]) && $tax_settings['operating_system']['edit'][$myrole] != 'edit') {
				$targs['meta_box_cb'] = false;
			}
			register_taxonomy('operating_system', array(
				'emd_issue'
			) , $targs);
		}
		$issue_cat_nohr_labels = array(
			'name' => __('Categories', 'software-issue-manager') ,
			'singular_name' => __('Category', 'software-issue-manager') ,
			'search_items' => __('Search Categories', 'software-issue-manager') ,
			'popular_items' => __('Popular Categories', 'software-issue-manager') ,
			'all_items' => __('All', 'software-issue-manager') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Category', 'software-issue-manager') ,
			'update_item' => __('Update Category', 'software-issue-manager') ,
			'add_new_item' => __('Add New Category', 'software-issue-manager') ,
			'new_item_name' => __('Add New Category Name', 'software-issue-manager') ,
			'separate_items_with_commas' => __('Seperate Categories with commas', 'software-issue-manager') ,
			'add_or_remove_items' => __('Add or Remove Categories', 'software-issue-manager') ,
			'choose_from_most_used' => __('Choose from the most used Categories', 'software-issue-manager') ,
			'menu_name' => __('Categories', 'software-issue-manager') ,
		);
		if (empty($tax_settings['issue_cat']['hide']) || (!empty($tax_settings['issue_cat']['hide']) && $tax_settings['issue_cat']['hide'] != 'hide')) {
			if (!empty($tax_settings['issue_cat']['rewrite'])) {
				$rewrite = $tax_settings['issue_cat']['rewrite'];
			} else {
				$rewrite = 'issue_cat';
			}
			$targs = array(
				'hierarchical' => false,
				'labels' => $issue_cat_nohr_labels,
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
					'manage_terms' => 'manage_issue_cat',
					'edit_terms' => 'edit_issue_cat',
					'delete_terms' => 'delete_issue_cat',
					'assign_terms' => 'assign_issue_cat'
				) ,
			);
			if ($myrole != 'administrator' && !empty($tax_settings['issue_cat']['edit'][$myrole]) && $tax_settings['issue_cat']['edit'][$myrole] != 'edit') {
				$targs['meta_box_cb'] = false;
			}
			register_taxonomy('issue_cat', array(
				'emd_issue'
			) , $targs);
		}
		$issue_tag_nohr_labels = array(
			'name' => __('Tags', 'software-issue-manager') ,
			'singular_name' => __('Tag', 'software-issue-manager') ,
			'search_items' => __('Search Tags', 'software-issue-manager') ,
			'popular_items' => __('Popular Tags', 'software-issue-manager') ,
			'all_items' => __('All', 'software-issue-manager') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Tag', 'software-issue-manager') ,
			'update_item' => __('Update Tag', 'software-issue-manager') ,
			'add_new_item' => __('Add New Tag', 'software-issue-manager') ,
			'new_item_name' => __('Add New Tag Name', 'software-issue-manager') ,
			'separate_items_with_commas' => __('Seperate Tags with commas', 'software-issue-manager') ,
			'add_or_remove_items' => __('Add or Remove Tags', 'software-issue-manager') ,
			'choose_from_most_used' => __('Choose from the most used Tags', 'software-issue-manager') ,
			'menu_name' => __('Tags', 'software-issue-manager') ,
		);
		if (empty($tax_settings['issue_tag']['hide']) || (!empty($tax_settings['issue_tag']['hide']) && $tax_settings['issue_tag']['hide'] != 'hide')) {
			if (!empty($tax_settings['issue_tag']['rewrite'])) {
				$rewrite = $tax_settings['issue_tag']['rewrite'];
			} else {
				$rewrite = 'issue_tag';
			}
			$targs = array(
				'hierarchical' => false,
				'labels' => $issue_tag_nohr_labels,
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
					'manage_terms' => 'manage_issue_tag',
					'edit_terms' => 'edit_issue_tag',
					'delete_terms' => 'delete_issue_tag',
					'assign_terms' => 'assign_issue_tag'
				) ,
			);
			if ($myrole != 'administrator' && !empty($tax_settings['issue_tag']['edit'][$myrole]) && $tax_settings['issue_tag']['edit'][$myrole] != 'edit') {
				$targs['meta_box_cb'] = false;
			}
			register_taxonomy('issue_tag', array(
				'emd_issue'
			) , $targs);
		}
		$browser_nohr_labels = array(
			'name' => __('Browsers', 'software-issue-manager') ,
			'singular_name' => __('Browser', 'software-issue-manager') ,
			'search_items' => __('Search Browsers', 'software-issue-manager') ,
			'popular_items' => __('Popular Browsers', 'software-issue-manager') ,
			'all_items' => __('All', 'software-issue-manager') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Browser', 'software-issue-manager') ,
			'update_item' => __('Update Browser', 'software-issue-manager') ,
			'add_new_item' => __('Add New Browser', 'software-issue-manager') ,
			'new_item_name' => __('Add New Browser Name', 'software-issue-manager') ,
			'separate_items_with_commas' => __('Seperate Browsers with commas', 'software-issue-manager') ,
			'add_or_remove_items' => __('Add or Remove Browsers', 'software-issue-manager') ,
			'choose_from_most_used' => __('Choose from the most used Browsers', 'software-issue-manager') ,
			'menu_name' => __('Browsers', 'software-issue-manager') ,
		);
		if (empty($tax_settings['browser']['hide']) || (!empty($tax_settings['browser']['hide']) && $tax_settings['browser']['hide'] != 'hide')) {
			if (!empty($tax_settings['browser']['rewrite'])) {
				$rewrite = $tax_settings['browser']['rewrite'];
			} else {
				$rewrite = 'browser';
			}
			$targs = array(
				'hierarchical' => false,
				'labels' => $browser_nohr_labels,
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
					'manage_terms' => 'manage_browser',
					'edit_terms' => 'edit_browser',
					'delete_terms' => 'delete_browser',
					'assign_terms' => 'assign_browser'
				) ,
			);
			if ($myrole != 'administrator' && !empty($tax_settings['browser']['edit'][$myrole]) && $tax_settings['browser']['edit'][$myrole] != 'edit') {
				$targs['meta_box_cb'] = false;
			}
			register_taxonomy('browser', array(
				'emd_issue'
			) , $targs);
		}
		$issue_status_nohr_labels = array(
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
		if (empty($tax_settings['issue_status']['hide']) || (!empty($tax_settings['issue_status']['hide']) && $tax_settings['issue_status']['hide'] != 'hide')) {
			if (!empty($tax_settings['issue_status']['rewrite'])) {
				$rewrite = $tax_settings['issue_status']['rewrite'];
			} else {
				$rewrite = 'issue_status';
			}
			$targs = array(
				'hierarchical' => false,
				'labels' => $issue_status_nohr_labels,
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
					'manage_terms' => 'manage_issue_status',
					'edit_terms' => 'edit_issue_status',
					'delete_terms' => 'delete_issue_status',
					'assign_terms' => 'assign_issue_status'
				) ,
			);
			if ($myrole != 'administrator' && !empty($tax_settings['issue_status']['edit'][$myrole]) && $tax_settings['issue_status']['edit'][$myrole] != 'edit') {
				$targs['meta_box_cb'] = false;
			}
			register_taxonomy('issue_status', array(
				'emd_issue'
			) , $targs);
		}
		$tax_list = get_option('software_issue_manager_tax_list');
		$init_tax = get_option('software_issue_manager_init_tax', Array());
		if (!empty($tax_list['emd_issue'])) {
			foreach ($tax_list['emd_issue'] as $keytax => $mytax) {
				if (!empty($mytax['init_values']) && (empty($init_tax['emd_issue']) || (!empty($init_tax['emd_issue']) && !in_array($keytax, $init_tax['emd_issue'])))) {
					$set_tax_terms = Array();
					foreach ($mytax['init_values'] as $myinit) {
						$set_tax_terms[] = $myinit;
					}
					self::set_taxonomy_init($set_tax_terms, $keytax);
					$init_tax['emd_issue'][] = $keytax;
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
		$this->sing_label = __('Issue', 'software-issue-manager');
		$this->plural_label = __('Issues', 'software-issue-manager');
		$this->menu_entity = 'emd_project';
		$this->boxes['issue_info_emd_issue_0'] = array(
			'id' => 'issue_info_emd_issue_0',
			'title' => __('Issue Info', 'software-issue-manager') ,
			'app_name' => 'software_issue_manager',
			'pages' => array(
				'emd_issue'
			) ,
			'context' => 'normal',
		);
		$this->boxes['emd_cust_field_meta_box'] = array(
			'id' => 'emd_cust_field_meta_box',
			'title' => __('Custom Fields', 'software-issue-manager') ,
			'app_name' => 'software_issue_manager',
			'pages' => array(
				'emd_issue'
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
			emd_get_template_part($this->textdomain, 'single', 'emd-issue');
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
		add_submenu_page(null, __('CSV Import/Export', 'software-issue-manager') , __('CSV Import/Export', 'software-issue-manager') , 'manage_operations_emd_issues', 'operations_emd_issue', array(
			$this,
			'get_operations'
		));
	}
	/**
	 * Display operations page
	 * @since WPAS 4.0
	 */
	public function get_operations() {
		if (current_user_can('manage_operations_emd_issues')) {
			$myapp = str_replace("-", "_", $this->textdomain);
			if (!function_exists('emd_operations_entity')) {
				emd_lite_get_operations('opr', $this->plural_label, $this->textdomain);
			} else {
				do_action('emd_operations_entity', $this->post_type, $this->plural_label, $this->sing_label, $myapp, $this->menu_entity);
			}
		}
	}
	/**
	 * Add new submenu hook
	 * @since WPAS 4.4
	 */
	public function add_top_menu_link() {
		add_submenu_page('edit.php?post_type=emd_project', __('Issues', 'software-issue-manager') , __('All Issues', 'software-issue-manager') , 'edit_emd_issues', 'edit.php?post_type=emd_issue', false);
		add_submenu_page('edit.php?post_type=emd_project', __('Issues', 'software-issue-manager') , __('Add New Issue', 'software-issue-manager') , 'edit_emd_issues', 'post-new.php?post_type=emd_issue', false);
		add_submenu_page('edit.php?post_type=emd_project', __('Browsers', 'software-issue-manager') , __('Browsers', 'software-issue-manager') , 'manage_browser', 'edit-tags.php?taxonomy=browser&amp;post_type=emd_issue', false);
		add_submenu_page('edit.php?post_type=emd_project', __('Categories', 'software-issue-manager') , __('Categories', 'software-issue-manager') , 'manage_issue_cat', 'edit-tags.php?taxonomy=issue_cat&amp;post_type=emd_issue', false);
		add_submenu_page('edit.php?post_type=emd_project', __('Operating Systems', 'software-issue-manager') , __('Operating Systems', 'software-issue-manager') , 'manage_operating_system', 'edit-tags.php?taxonomy=operating_system&amp;post_type=emd_issue', false);
		add_submenu_page('edit.php?post_type=emd_project', __('Priorities', 'software-issue-manager') , __('Priorities', 'software-issue-manager') , 'manage_issue_priority', 'edit-tags.php?taxonomy=issue_priority&amp;post_type=emd_issue', false);
		add_submenu_page('edit.php?post_type=emd_project', __('Statuses', 'software-issue-manager') , __('Statuses', 'software-issue-manager') , 'manage_issue_status', 'edit-tags.php?taxonomy=issue_status&amp;post_type=emd_issue', false);
		add_submenu_page('edit.php?post_type=emd_project', __('Tags', 'software-issue-manager') , __('Tags', 'software-issue-manager') , 'manage_issue_tag', 'edit-tags.php?taxonomy=issue_tag&amp;post_type=emd_issue', false);
		$myapp = str_replace("-", "_", $this->textdomain);
		do_action('emd_add_submenu_pages', $this->post_type, $myapp, $this->menu_entity);
	}
	/**
	 * Parent file for tax submenus with top level
	 * @since WPAS 5.3
	 */
	function tax_submenus($parent_file) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;
		if (in_array($taxonomy, Array(
			'browser',
			'issue_cat',
			'issue_priority',
			'issue_status',
			'issue_tag',
			'operating_system'
		))) {
			$parent_file = 'edit.php?post_type=emd_project';
		}
		return $parent_file;
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
new Emd_Issue;