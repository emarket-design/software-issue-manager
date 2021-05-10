<?php
/**
 * Install and Deactivate Plugin Functions
 * @package SOFTWARE_ISSUE_MANAGER
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
if (!class_exists('Software_Issue_Manager_Install_Deactivate')):
	/**
	 * Software_Issue_Manager_Install_Deactivate Class
	 * @since WPAS 4.0
	 */
	class Software_Issue_Manager_Install_Deactivate {
		private $option_name;
		/**
		 * Hooks for install and deactivation and create options
		 * @since WPAS 4.0
		 */
		public function __construct() {
			$this->option_name = 'software_issue_manager';
			add_action('admin_init', array(
				$this,
				'check_update'
			));
			register_activation_hook(SOFTWARE_ISSUE_MANAGER_PLUGIN_FILE, array(
				$this,
				'install'
			));
			register_deactivation_hook(SOFTWARE_ISSUE_MANAGER_PLUGIN_FILE, array(
				$this,
				'deactivate'
			));
			add_action('wp_head', array(
				$this,
				'version_in_header'
			));
			add_action('admin_init', array(
				$this,
				'setup_pages'
			));
			add_action('admin_notices', array(
				$this,
				'install_notice'
			));
			add_action('admin_init', array(
				$this,
				'register_settings'
			) , 0);
			add_action('before_delete_post', array(
				$this,
				'delete_post_file_att'
			));
			add_action('wp_ajax_emd_load_file', 'emd_load_file');
			add_action('wp_ajax_nopriv_emd_load_file', 'emd_load_file');
			add_action('wp_ajax_emd_delete_file', 'emd_delete_file');
			add_action('wp_ajax_nopriv_emd_delete_file', 'emd_delete_file');
			add_action('init', array(
				$this,
				'init_extensions'
			) , 99);
			do_action('emd_ext_actions', $this->option_name);
			add_filter('tiny_mce_before_init', array(
				$this,
				'tinymce_fix'
			));
		}
		public function check_update() {
			$curr_version = get_option($this->option_name . '_version', 1);
			$new_version = constant(strtoupper($this->option_name) . '_VERSION');
			if (version_compare($curr_version, $new_version, '<')) {
				P2P_Storage::install();
				$this->set_options();
				$this->set_roles_caps();
				if (!get_option($this->option_name . '_activation_date')) {
					$triggerdate = mktime(0, 0, 0, date('m') , date('d') + 7, date('Y'));
					add_option($this->option_name . '_activation_date', $triggerdate);
				}
				set_transient($this->option_name . '_activate_redirect', true, 30);
				do_action($this->option_name . '_upgrade', $new_version);
				update_option($this->option_name . '_version', $new_version);
			}
		}
		public function version_in_header() {
			$version = constant(strtoupper($this->option_name) . '_VERSION');
			$name = constant(strtoupper($this->option_name) . '_NAME');
			echo '<meta name="generator" content="' . $name . ' v' . $version . ' - https://emdplugins.com" />' . "\n";
		}
		public function init_extensions() {
			do_action('emd_ext_init', $this->option_name);
		}
		/**
		 * Runs on plugin install to setup custom post types and taxonomies
		 * flushing rewrite rules, populates settings and options
		 * creates roles and assign capabilities
		 * @since WPAS 4.0
		 *
		 */
		public function install() {
			$this->set_options();
			P2P_Storage::install();
			Emd_Project::register();
			Emd_Issue::register();
			flush_rewrite_rules();
			$this->set_roles_caps();
			set_transient($this->option_name . '_activate_redirect', true, 30);
			do_action('emd_ext_install_hook', $this->option_name);
		}
		/**
		 * Runs on plugin deactivate to remove options, caps and roles
		 * flushing rewrite rules
		 * @since WPAS 4.0
		 *
		 */
		public function deactivate() {
			flush_rewrite_rules();
			$this->remove_caps_roles();
			$this->reset_options();
			do_action('emd_ext_deactivate', $this->option_name);
		}
		/**
		 * Register notification and/or license settings
		 * @since WPAS 4.0
		 *
		 */
		public function register_settings() {
			do_action('emd_ext_register', $this->option_name);
			if (!get_transient($this->option_name . '_activate_redirect')) {
				return;
			}
			// Delete the redirect transient.
			delete_transient($this->option_name . '_activate_redirect');
			$query_args = array(
				'page' => $this->option_name
			);
			wp_safe_redirect(add_query_arg($query_args, admin_url('admin.php')));
		}
		/**
		 * Sets caps and roles
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function set_roles_caps() {
			global $wp_roles;
			$cust_roles = Array();
			update_option($this->option_name . '_cust_roles', $cust_roles);
			$add_caps = Array(
				'delete_others_emd_projects' => Array(
					'administrator',
					'editor'
				) ,
				'export' => Array(
					'administrator'
				) ,
				'manage_project_priority' => Array(
					'administrator'
				) ,
				'edit_emd_issues' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'delete_private_emd_issues' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'delete_operating_system' => Array(
					'administrator'
				) ,
				'assign_issue_tag' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'edit_issue_cat' => Array(
					'administrator'
				) ,
				'limitby_author_backend_emd_projects' => Array(
					'editor',
					'author',
					'contributor'
				) ,
				'assign_issue_priority' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'set_author_emd_issues' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'assign_operating_system' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'delete_published_emd_projects' => Array(
					'administrator',
					'editor'
				) ,
				'assign_project_priority' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'manage_issue_status' => Array(
					'administrator'
				) ,
				'manage_browser' => Array(
					'administrator'
				) ,
				'edit_private_emd_projects' => Array(
					'administrator',
					'editor'
				) ,
				'manage_operating_system' => Array(
					'administrator'
				) ,
				'delete_issue_priority' => Array(
					'administrator'
				) ,
				'delete_published_emd_issues' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'delete_issue_cat' => Array(
					'administrator'
				) ,
				'edit_emd_projects' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'read_private_emd_projects' => Array(
					'administrator',
					'editor'
				) ,
				'publish_emd_issues' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'manage_issue_priority' => Array(
					'administrator'
				) ,
				'edit_published_emd_issues' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'publish_emd_projects' => Array(
					'administrator',
					'editor'
				) ,
				'delete_emd_projects' => Array(
					'administrator',
					'editor'
				) ,
				'limitby_author_backend_emd_issues' => Array(
					'editor',
					'author',
					'contributor'
				) ,
				'manage_operations_emd_projects' => Array(
					'administrator'
				) ,
				'delete_private_emd_projects' => Array(
					'administrator',
					'editor'
				) ,
				'edit_published_emd_projects' => Array(
					'administrator',
					'editor'
				) ,
				'read_private_emd_issues' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'delete_project_status' => Array(
					'administrator'
				) ,
				'delete_others_emd_issues' => Array(
					'administrator',
					'editor'
				) ,
				'assign_browser' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'manage_project_status' => Array(
					'administrator'
				) ,
				'edit_project_status' => Array(
					'administrator'
				) ,
				'edit_browser' => Array(
					'administrator'
				) ,
				'limitby_author_frontend_emd_issues' => Array(
					'editor',
					'author',
					'contributor'
				) ,
				'limitby_author_frontend_emd_projects' => Array(
					'editor',
					'author',
					'contributor'
				) ,
				'assign_issue_status' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'delete_browser' => Array(
					'administrator'
				) ,
				'manage_issue_tag' => Array(
					'administrator'
				) ,
				'set_author_emd_projects' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'edit_issue_priority' => Array(
					'administrator'
				) ,
				'delete_issue_tag' => Array(
					'administrator'
				) ,
				'assign_project_status' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'edit_operating_system' => Array(
					'administrator'
				) ,
				'delete_project_priority' => Array(
					'administrator'
				) ,
				'edit_issue_status' => Array(
					'administrator'
				) ,
				'delete_emd_issues' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'edit_private_emd_issues' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'manage_operations_emd_issues' => Array(
					'administrator'
				) ,
				'manage_issue_cat' => Array(
					'administrator'
				) ,
				'edit_issue_tag' => Array(
					'administrator'
				) ,
				'assign_issue_cat' => Array(
					'administrator',
					'editor',
					'author',
					'contributor'
				) ,
				'edit_others_emd_issues' => Array(
					'administrator',
					'editor',
					'author'
				) ,
				'edit_others_emd_projects' => Array(
					'administrator',
					'editor'
				) ,
				'edit_project_priority' => Array(
					'administrator'
				) ,
				'delete_issue_status' => Array(
					'administrator'
				) ,
			);
			update_option($this->option_name . '_add_caps', $add_caps);
			if (class_exists('WP_Roles')) {
				if (!isset($wp_roles)) {
					$wp_roles = new WP_Roles();
				}
			}
			if (is_object($wp_roles)) {
				if (!empty($cust_roles)) {
					foreach ($cust_roles as $krole => $vrole) {
						$myrole = get_role($krole);
						if (empty($myrole)) {
							$myrole = add_role($krole, $vrole);
						}
					}
				}
				$this->set_reset_caps($wp_roles, 'add');
			}
		}
		/**
		 * Removes caps and roles
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function remove_caps_roles() {
			global $wp_roles;
			if (class_exists('WP_Roles')) {
				if (!isset($wp_roles)) {
					$wp_roles = new WP_Roles();
				}
			}
			if (is_object($wp_roles)) {
				$this->set_reset_caps($wp_roles, 'remove');
			}
		}
		/**
		 * Set  capabilities
		 *
		 * @since WPAS 4.0
		 * @param object $wp_roles
		 * @param string $type
		 *
		 */
		public function set_reset_caps($wp_roles, $type) {
			$caps['enable'] = get_option($this->option_name . '_add_caps', Array());
			$caps['enable'] = apply_filters('emd_ext_get_caps', $caps['enable'], $this->option_name);
			foreach ($caps as $stat => $role_caps) {
				foreach ($role_caps as $mycap => $roles) {
					foreach ($roles as $myrole) {
						if (($type == 'add' && $stat == 'enable') || ($stat == 'disable' && $type == 'remove')) {
							$wp_roles->add_cap($myrole, $mycap);
						} else if (($type == 'remove' && $stat == 'enable') || ($type == 'add' && $stat == 'disable')) {
							$wp_roles->remove_cap($myrole, $mycap);
						}
					}
				}
			}
		}
		/**
		 * Set app specific options
		 *
		 * @since WPAS 4.0
		 *
		 */
		private function set_options() {
			$access_views = Array();
			if (get_option($this->option_name . '_setup_pages', 0) == 0) {
				update_option($this->option_name . '_setup_pages', 1);
			}
			$limitby_auth_caps = Array(
				'emd_issue' => Array(
					'limitby_author_backend_emd_issues',
					'limitby_author_frontend_emd_issues'
				) ,
				'emd_project' => Array(
					'limitby_author_backend_emd_projects',
					'limitby_author_frontend_emd_projects'
				)
			);
			if (!empty($limitby_caps)) {
				update_option($this->option_name . '_limitby_caps', $limitby_caps);
			}
			if (!empty($limitby_auth_caps)) {
				update_option($this->option_name . '_limitby_auth_caps', $limitby_auth_caps);
			}
			update_option($this->option_name . '_access_views', $access_views);
			$ent_list = Array(
				'emd_project' => Array(
					'label' => __('Projects', 'software-issue-manager') ,
					'rewrite' => 'projects',
					'archive_view' => 0,
					'rest_api' => 0,
					'sortable' => 0,
					'searchable' => 1,
					'class_title' => Array(
						'emd_prj_name',
						'emd_prj_version'
					) ,
					'unique_keys' => Array(
						'emd_prj_name',
						'emd_prj_version'
					) ,
					'blt_list' => Array(
						'blt_content' => __('Content', 'software-issue-manager') ,
					) ,
				) ,
				'emd_issue' => Array(
					'label' => __('Issues', 'software-issue-manager') ,
					'rewrite' => 'issues',
					'archive_view' => 0,
					'rest_api' => 0,
					'sortable' => 0,
					'searchable' => 1,
					'class_title' => Array(
						'emd_iss_id'
					) ,
					'unique_keys' => Array(
						'emd_iss_id'
					) ,
					'blt_list' => Array(
						'blt_content' => __('Content', 'software-issue-manager') ,
					) ,
					'req_blt' => Array(
						'blt_title' => Array(
							'msg' => __('Title', 'software-issue-manager')
						) ,
					) ,
				) ,
			);
			update_option($this->option_name . '_ent_list', $ent_list);
			$shc_list['app'] = 'Software Issue Manager';
			$shc_list['has_gmap'] = 0;
			$shc_list['has_form_lite'] = 1;
			$shc_list['has_lite'] = 1;
			$shc_list['has_bs'] = 1;
			$shc_list['has_autocomplete'] = 0;
			$shc_list['remove_vis'] = 0;
			$shc_list['forms']['issue_entry'] = Array(
				'name' => 'issue_entry',
				'type' => 'submit',
				'ent' => 'emd_issue',
				'targeted_device' => 'desktops',
				'label_position' => 'top',
				'element_size' => 'medium',
				'display_inline' => '0',
				'noaccess_msg' => 'You are not allowed to access to this area. Please contact the site administrator.',
				'disable_submit' => '0',
				'submit_status' => 'publish',
				'visitor_submit_status' => 'publish',
				'submit_button_type' => 'btn-primary',
				'submit_button_label' => 'Create Issue',
				'submit_button_size' => 'btn-large',
				'submit_button_block' => '0',
				'submit_button_fa' => '',
				'submit_button_fa_size' => '',
				'submit_button_fa_pos' => 'left',
				'show_captcha' => 'show-to-visitors',
				'disable_after' => '0',
				'confirm_method' => 'text',
				'confirm_url' => '',
				'confirm_success_txt' => 'Thanks for your submission.',
				'confirm_error_txt' => 'There has been an error when submitting your entry. Please contact the site administrator.',
				'enable_ajax' => '0',
				'after_submit' => 'show',
				'schedule_start' => '',
				'schedule_end' => '',
				'enable_operators' => '0',
				'ajax_search' => '0',
				'result_templ' => '',
				'result_fields' => '',
				'noresult_msg' => 'Your search returned no results.',
				'view_name' => '',
				'honeypot' => '1',
				'login_reg' => 'none',
				'page_title' => __('Issue Entry', 'software-issue-manager')
			);
			$shc_list['forms']['issue_search'] = Array(
				'name' => 'issue_search',
				'type' => 'search',
				'ent' => 'emd_issue',
				'targeted_device' => 'desktops',
				'label_position' => 'top',
				'element_size' => 'medium',
				'display_inline' => '0',
				'noaccess_msg' => '<p>You are not allowed to access to this area. Please contact the site administrator.</p>',
				'disable_submit' => '0',
				'submit_status' => 'publish',
				'visitor_submit_status' => 'draft',
				'submit_button_type' => 'btn-info',
				'submit_button_label' => 'Search Issues',
				'submit_button_size' => 'btn-large',
				'submit_button_block' => '0',
				'submit_button_fa' => '',
				'submit_button_fa_size' => '',
				'submit_button_fa_pos' => 'left',
				'show_captcha' => 'show-to-visitors',
				'disable_after' => '0',
				'confirm_method' => 'text',
				'confirm_url' => '',
				'confirm_success_txt' => 'Thanks for your submission.',
				'confirm_error_txt' => 'There has been an error when submitting your entry. Please contact the site administrator.',
				'enable_ajax' => '0',
				'after_submit' => 'show',
				'schedule_start' => '',
				'schedule_end' => '',
				'enable_operators' => '0',
				'ajax_search' => '0',
				'result_templ' => 'cust_table',
				'result_fields' => '',
				'noresult_msg' => 'Your search returned no results.',
				'view_name' => 'sc_issues',
				'honeypot' => '1',
				'login_reg' => 'none',
				'page_title' => __('Search Issues', 'software-issue-manager')
			);
			if (!empty($shc_list)) {
				update_option($this->option_name . '_shc_list', $shc_list);
			}
			$attr_list['emd_project']['emd_prj_name'] = Array(
				'label' => __('Name', 'software-issue-manager') ,
				'display_type' => 'text',
				'required' => 1,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'project_info_emd_project_0',
				'desc' => __('Sets the name of a project.', 'software-issue-manager') ,
				'type' => 'char',
				'minlength' => 3,
				'uniqueAttr' => true,
			);
			$attr_list['emd_project']['emd_prj_version'] = Array(
				'label' => __('Version', 'software-issue-manager') ,
				'display_type' => 'text',
				'required' => 1,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'project_info_emd_project_0',
				'desc' => __('Sets the version number of a project.', 'software-issue-manager') ,
				'type' => 'char',
				'std' => 'V1.0.0',
				'uniqueAttr' => true,
			);
			$attr_list['emd_project']['emd_prj_start_date'] = Array(
				'label' => __('Start Date', 'software-issue-manager') ,
				'display_type' => 'date',
				'required' => 1,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'project_info_emd_project_0',
				'desc' => __('Sets the start date of a project.', 'software-issue-manager') ,
				'type' => 'date',
				'dformat' => array(
					'dateFormat' => 'mm-dd-yy'
				) ,
				'date_format' => 'm-d-Y',
			);
			$attr_list['emd_project']['emd_prj_target_end_date'] = Array(
				'label' => __('Target End Date', 'software-issue-manager') ,
				'display_type' => 'date',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'project_info_emd_project_0',
				'desc' => __('Sets the targeted end date of a project.', 'software-issue-manager') ,
				'type' => 'date',
				'dformat' => array(
					'dateFormat' => 'mm-dd-yy'
				) ,
				'date_format' => 'm-d-Y',
			);
			$attr_list['emd_project']['emd_prj_actual_end_date'] = Array(
				'label' => __('Actual End Date', 'software-issue-manager') ,
				'display_type' => 'date',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'project_info_emd_project_0',
				'desc' => __('Sets the actual end date of a project.', 'software-issue-manager') ,
				'type' => 'date',
				'dformat' => array(
					'dateFormat' => 'mm-dd-yy'
				) ,
				'date_format' => 'm-d-Y',
			);
			$attr_list['emd_project']['emd_prj_file'] = Array(
				'label' => __('Documents', 'software-issue-manager') ,
				'display_type' => 'file',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'project_info_emd_project_0',
				'desc' => __('Allows to upload project related files.', 'software-issue-manager') ,
				'type' => 'char',
			);
			$attr_list['emd_issue']['emd_iss_id'] = Array(
				'label' => __('ID', 'software-issue-manager') ,
				'display_type' => 'hidden',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'issue_info_emd_issue_0',
				'desc' => __('Sets a unique identifier for an issue.', 'software-issue-manager') ,
				'type' => 'char',
				'hidden_func' => 'unique_id',
				'uniqueAttr' => true,
			);
			$attr_list['emd_issue']['emd_iss_due_date'] = Array(
				'label' => __('Due Date', 'software-issue-manager') ,
				'display_type' => 'date',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'issue_info_emd_issue_0',
				'desc' => __('Sets the targeted resolution date for an issue.', 'software-issue-manager') ,
				'type' => 'date',
				'dformat' => array(
					'dateFormat' => 'mm-dd-yy'
				) ,
				'date_format' => 'm-d-Y',
			);
			$attr_list['emd_issue']['emd_iss_resolution_summary'] = Array(
				'label' => __('Resolution Summary', 'software-issue-manager') ,
				'display_type' => 'wysiwyg',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'mid' => 'issue_info_emd_issue_0',
				'desc' => __('Sets a brief summary of the resolution of an issue.', 'software-issue-manager') ,
				'type' => 'char',
				'options' => array(
					'media_buttons' => false
				) ,
			);
			$attr_list['emd_issue']['emd_iss_document'] = Array(
				'label' => __('Documents', 'software-issue-manager') ,
				'display_type' => 'file',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'issue_info_emd_issue_0',
				'desc' => __('Allows to upload files related to an issue.', 'software-issue-manager') ,
				'type' => 'char',
			);
			$attr_list['emd_issue']['emd_iss_email'] = Array(
				'label' => __('Email', 'software-issue-manager') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'issue_info_emd_issue_0',
				'type' => 'char',
				'email' => true,
			);
			$attr_list['emd_issue']['wpas_form_name'] = Array(
				'label' => __('Form Name', 'software-issue-manager') ,
				'display_type' => 'hidden',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'mid' => 'issue_info_emd_issue_0',
				'type' => 'char',
				'options' => array() ,
				'no_update' => 1,
				'std' => 'admin',
			);
			$attr_list['emd_issue']['wpas_form_submitted_by'] = Array(
				'label' => __('Form Submitted By', 'software-issue-manager') ,
				'display_type' => 'hidden',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'mid' => 'issue_info_emd_issue_0',
				'type' => 'char',
				'options' => array() ,
				'hidden_func' => 'user_login',
				'no_update' => 1,
			);
			$attr_list['emd_issue']['wpas_form_submitted_ip'] = Array(
				'label' => __('Form Submitted IP', 'software-issue-manager') ,
				'display_type' => 'hidden',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 1,
				'list_visible' => 0,
				'mid' => 'issue_info_emd_issue_0',
				'type' => 'char',
				'options' => array() ,
				'hidden_func' => 'user_ip',
				'no_update' => 1,
			);
			$attr_list = apply_filters('emd_ext_attr_list', $attr_list, $this->option_name);
			if (!empty($attr_list)) {
				update_option($this->option_name . '_attr_list', $attr_list);
			}
			update_option($this->option_name . '_glob_init_list', Array());
			$glob_forms_list['issue_entry']['captcha'] = 'show-to-visitors';
			$glob_forms_list['issue_entry']['noaccess_msg'] = 'You are not allowed to access to this area. Please contact the site administrator.';
			$glob_forms_list['issue_entry']['error_msg'] = 'There has been an error when submitting your entry. Please contact the site administrator.';
			$glob_forms_list['issue_entry']['success_msg'] = 'Thanks for your submission.';
			$glob_forms_list['issue_entry']['login_reg'] = 'none';
			$glob_forms_list['issue_entry']['csrf'] = 1;
			$glob_forms_list['issue_entry']['blt_title'] = Array(
				'show' => 1,
				'row' => 1,
				'req' => 1,
				'size' => 12,
				'label' => __('Title', 'software-issue-manager')
			);
			$glob_forms_list['issue_entry']['blt_content'] = Array(
				'show' => 1,
				'row' => 2,
				'req' => 0,
				'size' => 12,
				'label' => __('Content', 'software-issue-manager')
			);
			$glob_forms_list['issue_entry']['emd_iss_email'] = Array(
				'show' => 1,
				'row' => 3,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_entry']['emd_iss_due_date'] = Array(
				'show' => 1,
				'row' => 4,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_entry']['issue_priority'] = Array(
				'show' => 1,
				'row' => 5,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_entry']['issue_cat'] = Array(
				'show' => 1,
				'row' => 6,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_entry']['issue_status'] = Array(
				'show' => 1,
				'row' => 7,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_entry']['issue_tag'] = Array(
				'show' => 1,
				'row' => 8,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_entry']['browser'] = Array(
				'show' => 1,
				'row' => 9,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_entry']['operating_system'] = Array(
				'show' => 1,
				'row' => 10,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_entry']['emd_iss_document'] = Array(
				'show' => 1,
				'row' => 11,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_entry']['rel_project_issues'] = Array(
				'show' => 1,
				'row' => 13,
				'req' => 1,
				'size' => 12,
			);
			$glob_forms_list['issue_entry']['emd_iss_id'] = Array(
				'show' => 1,
				'row' => 14,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_search']['captcha'] = 'show-to-visitors';
			$glob_forms_list['issue_search']['noaccess_msg'] = '<p>You are not allowed to access to this area. Please contact the site administrator.</p>';
			$glob_forms_list['issue_search']['login_reg'] = 'none';
			$glob_forms_list['issue_search']['noresult_msg'] = 'Your search returned no results.';
			$glob_forms_list['issue_search']['csrf'] = 0;
			$glob_forms_list['issue_search']['emd_iss_id'] = Array(
				'show' => 1,
				'row' => 1,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_search']['emd_iss_email'] = Array(
				'show' => 1,
				'row' => 2,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_search']['emd_iss_due_date'] = Array(
				'show' => 1,
				'row' => 3,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_search']['issue_cat'] = Array(
				'show' => 1,
				'row' => 4,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_search']['issue_priority'] = Array(
				'show' => 1,
				'row' => 5,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_search']['issue_status'] = Array(
				'show' => 1,
				'row' => 6,
				'req' => 0,
				'size' => 12,
			);
			$glob_forms_list['issue_search']['rel_project_issues'] = Array(
				'show' => 1,
				'row' => 7,
				'req' => 0,
				'size' => 12,
			);
			if (!empty($glob_forms_list)) {
				update_option($this->option_name . '_glob_forms_init_list', $glob_forms_list);
				if (get_option($this->option_name . '_glob_forms_list') === false) {
					update_option($this->option_name . '_glob_forms_list', $glob_forms_list);
				}
			}
			$tax_list['emd_issue']['issue_priority'] = Array(
				'archive_view' => 0,
				'label' => __('Priorities', 'software-issue-manager') ,
				'single_label' => __('Priority', 'software-issue-manager') ,
				'default' => Array(
					__('Normal', 'software-issue-manager')
				) ,
				'type' => 'single',
				'hier' => 0,
				'sortable' => 0,
				'list_visible' => 1,
				'required' => 0,
				'srequired' => 0,
				'rewrite' => 'issue_priority',
				'init_values' => Array(
					Array(
						'name' => __('Critical', 'software-issue-manager') ,
						'slug' => sanitize_title('Critical') ,
						'desc' => __('Critical bugs either render a system unusable (not being able to create content or upgrade between versions, blocks not displaying, and the like), cause loss of data, or expose security vulnerabilities. These bugs are to be fixed immediately.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Major', 'software-issue-manager') ,
						'slug' => sanitize_title('Major') ,
						'desc' => __('Issues which have significant repercussions but do not render the whole system unusable are marked major. An example would be a PHP error which is only triggered under rare circumstances or which affects only a small percentage of all users. These issues are prioritized in the current development release and backported to stable releases where applicable. Major issues do not block point releases.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Normal', 'software-issue-manager') ,
						'slug' => sanitize_title('Normal') ,
						'desc' => __('Bugs that affect one piece of functionality are normal priority. An example would be the category filter not working on the database log screen. This is a self-contained bug and does not impact the overall functionality of the software.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Minor', 'software-issue-manager') ,
						'slug' => sanitize_title('Minor') ,
						'desc' => __('Minor priority is most often used for cosmetic issues that don\'t inhibit the functionality or main purpose of the project, such as correction of typos in code comments or whitespace issues.', 'software-issue-manager')
					)
				)
			);
			$tax_list['emd_issue']['issue_status'] = Array(
				'archive_view' => 0,
				'label' => __('Statuses', 'software-issue-manager') ,
				'single_label' => __('Status', 'software-issue-manager') ,
				'default' => Array(
					__('Open', 'software-issue-manager')
				) ,
				'type' => 'single',
				'hier' => 0,
				'sortable' => 0,
				'list_visible' => 1,
				'required' => 0,
				'srequired' => 0,
				'rewrite' => 'issue_status',
				'init_values' => Array(
					Array(
						'name' => __('Open', 'software-issue-manager') ,
						'slug' => sanitize_title('Open') ,
						'desc' => __('This issue is in the initial state, ready for the assignee to start work on it.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('In Progress', 'software-issue-manager') ,
						'slug' => sanitize_title('In Progress') ,
						'desc' => __('This issue is being actively worked on at the moment.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Reopened', 'software-issue-manager') ,
						'slug' => sanitize_title('Reopened') ,
						'desc' => __('This issue was once \'Resolved\' or \'Closed\', but is now being re-visited, e.g. an issue with a Resolution of \'Cannot Reproduce\' is Reopened when more information becomes available and the issue becomes reproducible. The next issue states are either marked In Progress, Resolved or Closed.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Closed', 'software-issue-manager') ,
						'slug' => sanitize_title('Closed') ,
						'desc' => __('This issue is complete.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Resolved - Fixed', 'software-issue-manager') ,
						'slug' => sanitize_title('Resolved - Fixed') ,
						'desc' => __('A fix for this issue has been implemented.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Resolved - Won\'t Fix', 'software-issue-manager') ,
						'slug' => sanitize_title('Resolved - Won\'t Fix') ,
						'desc' => __('This issue will not be fixed, e.g. it may no longer be relevant.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Resolved - Duplicate', 'software-issue-manager') ,
						'slug' => sanitize_title('Resolved - Duplicate') ,
						'desc' => __('This issue is a duplicate of an existing issue. It is recommended you create a link to the duplicated issue by creating a related issue connection.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Resolved - Incomplete', 'software-issue-manager') ,
						'slug' => sanitize_title('Resolved - Incomplete') ,
						'desc' => __('There is not enough information to work on this issue.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Resolved - CNR', 'software-issue-manager') ,
						'slug' => sanitize_title('Resolved - CNR') ,
						'desc' => __('This issue could not be reproduced at this time, or not enough information was available to reproduce the issue. If more information becomes available, reopen the issue.', 'software-issue-manager')
					)
				)
			);
			$tax_list['emd_issue']['issue_cat'] = Array(
				'archive_view' => 0,
				'label' => __('Categories', 'software-issue-manager') ,
				'single_label' => __('Category', 'software-issue-manager') ,
				'default' => Array(
					__('Bug', 'software-issue-manager')
				) ,
				'type' => 'single',
				'hier' => 0,
				'sortable' => 0,
				'list_visible' => 1,
				'required' => 0,
				'srequired' => 0,
				'rewrite' => 'issue_cat',
				'init_values' => Array(
					Array(
						'name' => __('Bug', 'software-issue-manager') ,
						'slug' => sanitize_title('Bug') ,
						'desc' => __('Bugs are software problems or defects in the system that need to be resolved.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Feature Request', 'software-issue-manager') ,
						'slug' => sanitize_title('Feature Request') ,
						'desc' => __('Feature requests are functional enhancements submitted by clients.', 'software-issue-manager')
					) ,
					Array(
						'name' => __('Task', 'software-issue-manager') ,
						'slug' => sanitize_title('Task') ,
						'desc' => __('Tasks are activities that need to be accomplished within a defined period of time or by a deadline to resolve issues.', 'software-issue-manager')
					)
				)
			);
			$tax_list['emd_issue']['issue_tag'] = Array(
				'archive_view' => 0,
				'label' => __('Tags', 'software-issue-manager') ,
				'single_label' => __('Tag', 'software-issue-manager') ,
				'default' => '',
				'type' => 'multi',
				'hier' => 0,
				'sortable' => 0,
				'list_visible' => 0,
				'required' => 0,
				'srequired' => 0,
				'rewrite' => 'issue_tag'
			);
			$tax_list['emd_project']['project_status'] = Array(
				'archive_view' => 0,
				'label' => __('Statuses', 'software-issue-manager') ,
				'single_label' => __('Status', 'software-issue-manager') ,
				'default' => Array(
					__('Draft', 'software-issue-manager')
				) ,
				'type' => 'single',
				'hier' => 0,
				'sortable' => 0,
				'list_visible' => 1,
				'required' => 1,
				'srequired' => 0,
				'rewrite' => 'project_status',
				'init_values' => Array(
					Array(
						'name' => __('Draft', 'software-issue-manager') ,
						'slug' => sanitize_title('Draft')
					) ,
					Array(
						'name' => __('In Review', 'software-issue-manager') ,
						'slug' => sanitize_title('In Review')
					) ,
					Array(
						'name' => __('Published', 'software-issue-manager') ,
						'slug' => sanitize_title('Published')
					) ,
					Array(
						'name' => __('In Process', 'software-issue-manager') ,
						'slug' => sanitize_title('In Process')
					)
				)
			);
			$tax_list['emd_project']['project_priority'] = Array(
				'archive_view' => 0,
				'label' => __('Priorities', 'software-issue-manager') ,
				'single_label' => __('Priority', 'software-issue-manager') ,
				'default' => Array(
					__('Medium', 'software-issue-manager')
				) ,
				'type' => 'single',
				'hier' => 0,
				'sortable' => 0,
				'list_visible' => 1,
				'required' => 1,
				'srequired' => 0,
				'rewrite' => 'project_priority',
				'init_values' => Array(
					Array(
						'name' => __('Low', 'software-issue-manager') ,
						'slug' => sanitize_title('Low')
					) ,
					Array(
						'name' => __('Medium', 'software-issue-manager') ,
						'slug' => sanitize_title('Medium')
					) ,
					Array(
						'name' => __('High', 'software-issue-manager') ,
						'slug' => sanitize_title('High')
					)
				)
			);
			$tax_list['emd_issue']['browser'] = Array(
				'archive_view' => 0,
				'label' => __('Browsers', 'software-issue-manager') ,
				'single_label' => __('Browser', 'software-issue-manager') ,
				'default' => '',
				'type' => 'multi',
				'hier' => 0,
				'sortable' => 0,
				'list_visible' => 0,
				'required' => 0,
				'srequired' => 0,
				'rewrite' => 'browser',
				'init_values' => Array(
					Array(
						'name' => __('Chrome 33', 'software-issue-manager') ,
						'slug' => sanitize_title('Chrome 33')
					) ,
					Array(
						'name' => __('Internet Explorer 11', 'software-issue-manager') ,
						'slug' => sanitize_title('Internet Explorer 11')
					) ,
					Array(
						'name' => __('Safari 7.0', 'software-issue-manager') ,
						'slug' => sanitize_title('Safari 7.0')
					) ,
					Array(
						'name' => __('Opera 20', 'software-issue-manager') ,
						'slug' => sanitize_title('Opera 20')
					) ,
					Array(
						'name' => __('Firefox 29', 'software-issue-manager') ,
						'slug' => sanitize_title('Firefox 29')
					)
				)
			);
			$tax_list['emd_issue']['operating_system'] = Array(
				'archive_view' => 0,
				'label' => __('Operating Systems', 'software-issue-manager') ,
				'single_label' => __('Operating System', 'software-issue-manager') ,
				'default' => '',
				'type' => 'multi',
				'hier' => 0,
				'sortable' => 0,
				'list_visible' => 0,
				'required' => 0,
				'srequired' => 0,
				'rewrite' => 'operating_system',
				'init_values' => Array(
					Array(
						'name' => __('Windows 8 (32-bit and 64-bit)', 'software-issue-manager') ,
						'slug' => sanitize_title('Windows 8 (32-bit and 64-bit)')
					) ,
					Array(
						'name' => __('Windows 7 (32-bit and 64-bit)', 'software-issue-manager') ,
						'slug' => sanitize_title('Windows 7 (32-bit and 64-bit)')
					) ,
					Array(
						'name' => __('Windows Vista (32-bit and 64-bit)', 'software-issue-manager') ,
						'slug' => sanitize_title('Windows Vista (32-bit and 64-bit)')
					) ,
					Array(
						'name' => __('Windows XP (32-bit and 64-bit)', 'software-issue-manager') ,
						'slug' => sanitize_title('Windows XP (32-bit and 64-bit)')
					) ,
					Array(
						'name' => __('Windows Server 2008 R2 (64-bit)', 'software-issue-manager') ,
						'slug' => sanitize_title('Windows Server 2008 R2 (64-bit)')
					) ,
					Array(
						'name' => __('Windows Server 2008 (32-bit and 64-bit)', 'software-issue-manager') ,
						'slug' => sanitize_title('Windows Server 2008 (32-bit and 64-bit)')
					) ,
					Array(
						'name' => __('Windows Server 2003 (32-bit and 64-bit)', 'software-issue-manager') ,
						'slug' => sanitize_title('Windows Server 2003 (32-bit and 64-bit)')
					) ,
					Array(
						'name' => __('Windows 2000 SP4', 'software-issue-manager') ,
						'slug' => sanitize_title('Windows 2000 SP4')
					) ,
					Array(
						'name' => __('Mac OS X 10.8 Mountain Lion (32-bit and 64-bit)', 'software-issue-manager') ,
						'slug' => sanitize_title('Mac OS X 10.8 Mountain Lion (32-bit and 64-bit)')
					) ,
					Array(
						'name' => __('Mac OS X 10.7 Lion (32-bit and 64-bit)', 'software-issue-manager') ,
						'slug' => sanitize_title('Mac OS X 10.7 Lion (32-bit and 64-bit)')
					) ,
					Array(
						'name' => __('Mac OS X 10.6 Snow Leopard (32-bit)', 'software-issue-manager') ,
						'slug' => sanitize_title('Mac OS X 10.6 Snow Leopard (32-bit)')
					) ,
					Array(
						'name' => __('Mac OS X 10.5 Leopard', 'software-issue-manager') ,
						'slug' => sanitize_title('Mac OS X 10.5 Leopard')
					) ,
					Array(
						'name' => __('Mac OS X 10.4 Tiger', 'software-issue-manager') ,
						'slug' => sanitize_title('Mac OS X 10.4 Tiger')
					) ,
					Array(
						'name' => __('Linux (32-bit and 64-bit versions, kernel 2.6 or compatible)', 'software-issue-manager') ,
						'slug' => sanitize_title('Linux (32-bit and 64-bit versions, kernel 2.6 or compatible)')
					)
				)
			);
			$tax_list = apply_filters('emd_ext_tax_list', $tax_list, $this->option_name);
			if (!empty($tax_list)) {
				update_option($this->option_name . '_tax_list', $tax_list);
			}
			$rel_list['rel_project_issues'] = Array(
				'from' => 'emd_project',
				'to' => 'emd_issue',
				'type' => 'many-to-many',
				'from_title' => __('Project Issues', 'software-issue-manager') ,
				'to_title' => __('Affected Projects', 'software-issue-manager') ,
				'required' => 1,
				'srequired' => 0,
				'show' => 'to',
				'filter' => ''
			);
			if (!empty($rel_list)) {
				update_option($this->option_name . '_rel_list', $rel_list);
			}
			$emd_activated_plugins = get_option('emd_activated_plugins');
			if (!$emd_activated_plugins) {
				update_option('emd_activated_plugins', Array(
					'software-issue-manager'
				));
			} elseif (!in_array('software-issue-manager', $emd_activated_plugins)) {
				array_push($emd_activated_plugins, 'software-issue-manager');
				update_option('emd_activated_plugins', $emd_activated_plugins);
			}
			//conf parameters for incoming email
			$has_incoming_email = Array(
				'emd_issue' => Array(
					'label' => 'Issues',
					'status' => 'publish',
					'vis_submit' => 0,
					'tax' => 'issue_tag',
					'subject' => 'blt_title',
					'date' => Array(
						'post_date'
					) ,
					'body' => 'emd_blt_content',
					'att' => 'emd_iss_document',
					'email' => '',
					'name' => ''
				)
			);
			update_option($this->option_name . '_has_incoming_email', $has_incoming_email);
			$emd_inc_email_apps = get_option('emd_inc_email_apps');
			$emd_inc_email_apps[$this->option_name] = $this->option_name . '_inc_email_conf';
			update_option('emd_inc_email_apps', $emd_inc_email_apps);
			//conf parameters for inline entity
			//conf parameters for calendar
			//conf parameters for woocommerce
			$has_woocommerce = Array(
				'woo_issue' => Array(
					'label' => 'Woo Issues',
					'entity' => 'emd_issue',
					'txn' => '',
					'order_rel' => 0,
					'product_rel' => 1,
					'myaccount_before' => '',
					'myaccount_after' => '',
					'smanager_caps' => Array() ,
					'customer_caps' => Array() ,
					'product_term' => '',
					'product_type' => 'many-to-many',
					'product_from' => 'Issues',
					'product_to' => 'Products',
					'product_box' => 'any',
					'product_layout' => '        <tr>
          <td>!#woo_product_image_thumb#</td>
          <td>
            <a href="!#woo_product_link#" title="!#woo_product_title#">!#woo_product_id#</a>
          </td>
          <td>!#woo_product_title#</td>
          <td>!#woo_product_sku#</td>
          <td>!#woo_product_price#</td>
        </tr>',
					'product_header' => '<div class="panel panel-default" style="overflow:visible">
  <div class="panel-heading">
    <div class="panel-title"><a class="accor-title-link collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapse-woo-products">!#trans[Related Products]#</a></div>
  </div>
  <div class="panel-collapse out collapse" id="collapse-woo-products">
    <div class="panel-body" data-has-attrib="false">
<table id="table-products" class="table emd-table table-bordered table-hover" data-toggle="table" data-search="true" data-click-to-select="true" data-show-columns="true" data-show-export="true" data-pagination="true">
<thead>
        <tr>
          <th>!#trans[Image]#</th>
          <th data-sortable="true">!#trans[ID]#</th>
          <th data-sortable="true">!#trans[Title]#</th>
          <th data-sortable="true">!#trans[Sku]#</th>
          <th data-sortable="true">!#trans[Price]#</th>
        </tr>
</thead>
<tbody>',
					'product_footer' => '   </tbody>  
   </table>
    </div>
  </div>
</div>'
				)
			);
			update_option($this->option_name . '_has_woocommerce', $has_woocommerce);
			$woo_forms_list['issue_entry']['rel_emd_issue_woo_product'] = Array(
				'show' => 1,
				'row' => 16,
				'req' => 0,
				'size' => 12,
			);
			$woo_forms_list['issue_search']['rel_emd_issue_woo_product'] = Array(
				'show' => 1,
				'row' => 9,
				'req' => 0,
				'size' => 12,
			);
			update_option($this->option_name . '_has_woocommerce_forms_list', $woo_forms_list);
			//conf parameters for woocommerce
			$has_edd = Array(
				'edd_issue' => Array(
					'label' => 'Easy Digital Downloads',
					'entity' => 'emd_issue',
					'txn' => '',
					'order_rel' => 0,
					'product_rel' => 1,
					'myaccount_before' => '',
					'myaccount_after' => '',
					'smanager_caps' => Array(
						'edit_emd_projects',
						'delete_emd_projects',
						'edit_others_emd_projects',
						'publish_emd_projects',
						'read_private_emd_projects',
						'delete_private_emd_projects',
						'delete_published_emd_projects',
						'delete_others_emd_projects',
						'edit_private_emd_projects',
						'edit_published_emd_projects',
						'edit_emd_issues',
						'delete_emd_issues',
						'edit_others_emd_issues',
						'publish_emd_issues',
						'read_private_emd_issues',
						'delete_private_emd_issues',
						'delete_published_emd_issues',
						'delete_others_emd_issues',
						'edit_private_emd_issues',
						'edit_published_emd_issues',
						'assign_issue_priority',
						'assign_issue_status',
						'assign_issue_cat',
						'assign_issue_tag',
						'assign_project_status',
						'assign_project_priority',
						'assign_browser',
						'assign_operating_system'
					) ,
					'sacc_caps' => Array() ,
					'svendor_caps' => Array() ,
					'sworker_caps' => Array(
						'edit_emd_projects',
						'delete_emd_projects',
						'publish_emd_projects',
						'delete_published_emd_projects',
						'edit_published_emd_projects',
						'edit_emd_issues',
						'delete_emd_issues',
						'publish_emd_issues',
						'delete_published_emd_issues',
						'edit_published_emd_issues',
						'assign_issue_priority',
						'assign_issue_status',
						'assign_issue_cat',
						'assign_issue_tag',
						'assign_project_status',
						'assign_project_priority',
						'assign_browser',
						'assign_operating_system'
					) ,
					'product_term' => '',
					'product_type' => 'many-to-many',
					'product_from' => 'Issues',
					'product_to' => 'Products',
					'product_box' => 'any',
					'product_layout' => '        <tr>
          <td>!#edd_download_image_thumb#</td>
          <td>
            <a href="!#edd_download_link#" title="!#edd_download_title#">!#edd_download_id#</a>
          </td>
          <td>!#edd_download_title#</td>
          <td>!#edd_download_sku#</td>
          <td>!#edd_download_price#</td>
        </tr>',
					'product_header' => '<div class="panel panel-default" style="overflow:visible">
  <div class="panel-heading">
    <div class="panel-title"><a class="accor-title-link collapsed" data-parent="#accordion" data-toggle="collapse" href="#collapse-edd-products">!#trans[Related Products]#</a></div>
  </div>
  <div class="panel-collapse out collapse" id="collapse-edd-products">
    <div class="panel-body" data-has-attrib="false">
<table id="table-products" class="table emd-table table-bordered table-hover" data-toggle="table" data-search="true" data-click-to-select="true" data-show-columns="true" data-show-export="true" data-pagination="true">
<thead>
        <tr>
          <th>!#trans[Image]#</th>
          <th data-sortable="true">!#trans[ID]#</th>
          <th data-sortable="true">!#trans[Title]#</th>
          <th data-sortable="true">!#trans[Sku]#</th>
          <th data-sortable="true">!#trans[Price]#</th>
        </tr>
</thead>
<tbody>',
					'product_footer' => '  </tbody>  
   </table>
    </div>
  </div>
</div>'
				)
			);
			update_option($this->option_name . '_has_edd', $has_edd);
			$edd_forms_list['issue_entry']['rel_emd_issue_edd_product'] = Array(
				'show' => 1,
				'row' => 15,
				'req' => 0,
				'size' => 12,
			);
			$edd_forms_list['issue_search']['rel_emd_issue_edd_product'] = Array(
				'show' => 1,
				'row' => 8,
				'req' => 0,
				'size' => 12,
			);
			update_option($this->option_name . '_has_edd_forms_list', $edd_forms_list);
			//action to configure different extension conf parameters for this plugin
			do_action('emd_ext_set_conf', 'software-issue-manager');
		}
		/**
		 * Reset app specific options
		 *
		 * @since WPAS 4.0
		 *
		 */
		private function reset_options() {
			delete_option($this->option_name . '_shc_list');
			$incemail_settings = get_option('emd_inc_email_apps', Array());
			unset($incemail_settings[$this->option_name]);
			update_option('emd_inc_email_apps', $incemail_settings);
			delete_option($this->option_name . '_has_incoming_email');
			delete_option($this->option_name . '_has_edd');
			do_action('emd_ext_reset_conf', 'software-issue-manager');
		}
		/**
		 * Show admin notices
		 *
		 * @since WPAS 4.0
		 *
		 * @return html
		 */
		public function install_notice() {
			if (isset($_GET[$this->option_name . '_adm_notice1'])) {
				update_option($this->option_name . '_adm_notice1', true);
			}
			if (current_user_can('manage_options') && get_option($this->option_name . '_adm_notice1') != 1) {
?>
<div class="updated">
<?php
				printf('<p><a href="%1s" target="_blank"> %2$s </a>%3$s<a style="float:right;" href="%4$s"><span class="dashicons dashicons-dismiss" style="font-size:15px;"></span>%5$s</a></p>', 'https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/?pk_campaign=simcom&pk_source=plugin&pk_medium=link&pk_content=notice', __('New To Software Issue Manager? Review the documentation!', 'wpas') , __('&#187;', 'wpas') , esc_url(add_query_arg($this->option_name . '_adm_notice1', true)) , __('Dismiss', 'wpas'));
?>
</div>
<?php
			}
			if (isset($_GET[$this->option_name . '_adm_notice2'])) {
				update_option($this->option_name . '_adm_notice2', true);
			}
			if (current_user_can('manage_options') && get_option($this->option_name . '_adm_notice2') != 1) {
?>
<div class="updated">
<?php
				printf('<p><a href="%1s" target="_blank"> %2$s </a>%3$s<a style="float:right;" href="%4$s"><span class="dashicons dashicons-dismiss" style="font-size:15px;"></span>%5$s</a></p>', 'https://emdplugins.com/plugins/software-issue-manager-wordpress-plugin/?pk_campaign=simcom&pk_source=plugin&pk_medium=link&pk_content=notice', __('Get More Features You Need To Deliver The Best Software!', 'wpas') , __('&#187;', 'wpas') , esc_url(add_query_arg($this->option_name . '_adm_notice2', true)) , __('Dismiss', 'wpas'));
?>
</div>
<?php
			}
			if (current_user_can('manage_options') && get_option($this->option_name . '_setup_pages') == 1) {
				echo "<div id=\"message\" class=\"updated\"><p><strong>" . __('Welcome to Software Issue Manager', 'software-issue-manager') . "</strong></p>
           <p class=\"submit\"><a href=\"" . add_query_arg('setup_software_issue_manager_pages', 'true', admin_url('index.php')) . "\" class=\"button-primary\">" . __('Setup Software Issue Manager Pages', 'software-issue-manager') . "</a> <a class=\"skip button-primary\" href=\"" . add_query_arg('skip_setup_software_issue_manager_pages', 'true', admin_url('index.php')) . "\">" . __('Skip setup', 'software-issue-manager') . "</a></p>
         </div>";
			}
		}
		/**
		 * Setup pages for components and redirect to dashboard
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function setup_pages() {
			if (!is_admin()) {
				return;
			}
			if (!empty($_GET['setup_' . $this->option_name . '_pages'])) {
				$shc_list = get_option($this->option_name . '_shc_list');
				emd_create_install_pages($this->option_name, $shc_list);
				update_option($this->option_name . '_setup_pages', 2);
				wp_redirect(admin_url('admin.php?page=' . $this->option_name . '_settings&software-issue-manager-installed=true'));
				exit;
			}
			if (!empty($_GET['skip_setup_' . $this->option_name . '_pages'])) {
				update_option($this->option_name . '_setup_pages', 2);
				wp_redirect(admin_url('admin.php?page=' . $this->option_name . '_settings'));
				exit;
			}
		}
		/**
		 * Delete file attachments when a post is deleted
		 *
		 * @since WPAS 4.0
		 * @param $pid
		 *
		 * @return bool
		 */
		public function delete_post_file_att($pid) {
			$entity_fields = get_option($this->option_name . '_attr_list');
			$post_type = get_post_type($pid);
			if (!empty($entity_fields[$post_type])) {
				//Delete fields
				foreach (array_keys($entity_fields[$post_type]) as $myfield) {
					if (in_array($entity_fields[$post_type][$myfield]['display_type'], Array(
						'file',
						'image',
						'plupload_image',
						'thickbox_image'
					))) {
						$pmeta = get_post_meta($pid, $myfield);
						if (!empty($pmeta)) {
							foreach ($pmeta as $file_id) {
								//check if this file is used for another post
								$fargs = array(
									'meta_query' => array(
										array(
											'key' => $myfield,
											'value' => $file_id,
											'compare' => '=',
										)
									) ,
									'fields' => 'ids',
									'post_type' => $post_type,
									'posts_per_page' => - 1,
								);
								$fquery = new WP_Query($fargs);
								if (empty($fquery->posts)) {
									wp_delete_attachment($file_id);
								}
							}
						}
					}
				}
			}
			return true;
		}
		public function tinymce_fix($init) {
			global $post;
			$ent_list = get_option($this->option_name . '_ent_list', Array());
			if (!empty($post) && in_array($post->post_type, array_keys($ent_list))) {
				$init['wpautop'] = false;
				$init['indent'] = true;
			}
			return $init;
		}
	}
endif;
return new Software_Issue_Manager_Install_Deactivate();