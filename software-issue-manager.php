<?php
/** 
 * Plugin Name: Software Issue Manager
 * Plugin URI: https://emdplugins.com
 * Description: Software Issue Manager allows to track the resolution of every project issue in a productive and efficient way.
 * Version: 4.8.2
 * Author: eMarket Design
 * Author URI: https://emarketdesign.com
 * Text Domain: software-issue-manager
 * Domain Path: /lang
 * @package SOFTWARE_ISSUE_MANAGER
 * @since WPAS 4.0
 */
/*
 * LICENSE:
 * Software Issue Manager is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Software Issue Manager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * Please see <http://www.gnu.org/licenses/> for details.
*/
if (!defined('ABSPATH')) exit;
if (!class_exists('Software_Issue_Manager')):
	/**
	 * Main class for Software Issue Manager
	 *
	 * @class Software_Issue_Manager
	 */
	final class Software_Issue_Manager {
		/**
		 * @var Software_Issue_Manager single instance of the class
		 */
		private static $_instance;
		public $textdomain = 'software-issue-manager';
		public $app_name = 'software_issue_manager';
		public $session;
		/**
		 * Main Software_Issue_Manager Instance
		 *
		 * Ensures only one instance of Software_Issue_Manager is loaded or can be loaded.
		 *
		 * @static
		 * @see SOFTWARE_ISSUE_MANAGER()
		 * @return Software_Issue_Manager - Main instance
		 */
		public static function instance() {
			if (!isset(self::$_instance)) {
				self::$_instance = new self();
				self::$_instance->define_constants();
				self::$_instance->includes();
				self::$_instance->load_plugin_textdomain();
				self::$_instance->session = new Emd_Session('software_issue_manager');
				add_filter('the_content', array(
					self::$_instance,
					'change_content'
				));
				add_action('admin_menu', array(
					self::$_instance,
					'display_settings'
				));
				add_filter('template_include', array(
					self::$_instance,
					'show_template'
				));
				add_action('widgets_init', array(
					self::$_instance,
					'include_widgets'
				));
			}
			return self::$_instance;
		}
		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', $this->textdomain) , '1.0');
		}
		/**
		 * Define Software_Issue_Manager Constants
		 *
		 * @access private
		 * @return void
		 */
		private function define_constants() {
			define('SOFTWARE_ISSUE_MANAGER_VERSION', '4.8.2');
			define('SOFTWARE_ISSUE_MANAGER_AUTHOR', 'eMarket Design');
			define('SOFTWARE_ISSUE_MANAGER_NAME', 'Software Issue Manager');
			define('SOFTWARE_ISSUE_MANAGER_PLUGIN_FILE', __FILE__);
			define('SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
			define('SOFTWARE_ISSUE_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));
			define('EMD_ADMIN_DIR', ABSPATH . 'wp-admin');
		}
		/**
		 * Include required files
		 *
		 * @access private
		 * @return void
		 */
		private function includes() {
			//these files are in all apps
			if (!function_exists('emd_mb_meta')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'assets/ext/emd-meta-box/emd-meta-box.php';
			}
			if (!function_exists('emd_translate_date_format')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/date-functions.php';
			}
			if (!function_exists('emd_get_hidden_func')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/common-functions.php';
			}
			if (!class_exists('Emd_Entity')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/entities/class-emd-entity.php';
			}
			if (!function_exists('emd_get_template_part')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/layout-functions.php';
			}
			//the rest
			if (!class_exists('Emd_Query')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/class-emd-query.php';
			}
			if (!function_exists('emd_get_p2p_connections')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/relationship-functions.php';
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'assets/ext/posts-to-posts/posts-to-posts.php';
			}
			if (!function_exists('emd_shc_get_layout_list')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/shortcode-functions.php';
			}
			if (!class_exists('Emd_Widget')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/class-emd-widget.php';
			}
			if (!class_exists('Emd_Session')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/class-emd-session.php';
			}
			if (!function_exists('emd_show_login_register_forms')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/login-register-functions.php';
			}
			do_action('emd_ext_include_files');
			//app specific files
			if (!function_exists('emd_show_settings_page')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/admin/settings-functions.php';
			}
			if (!function_exists('emd_misc_register_settings')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/admin/settings-functions-misc.php';
			}
			if (is_admin()) {
				if (!class_exists('WP_List_Table', false)) {
					require_once EMD_ADMIN_DIR . '/includes/class-wp-list-table.php';
				}
				if (!class_exists('Emd_List_Table')) {
					require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/admin/class-emd-list-table.php';
				}
				//these files are in all apps
				if (!function_exists('emd_display_store')) {
					require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/admin/store-functions.php';
				}
				//the rest
				if (!function_exists('emd_shc_button')) {
					require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/admin/wpas-btn-functions.php';
				}
				if (!class_exists('Emd_Single_Taxonomy')) {
					require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/admin/singletax/class-emd-single-taxonomy.php';
					require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/admin/singletax/emd-singletax-functions.php';
					require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/admin/singletax/class-emd-walker-radio.php';
				}
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/admin/glossary.php';
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/admin/getting-started.php';
			}
			require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/class-install-deactivate.php';
			require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/entities/class-emd-issue.php';
			require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/entities/class-emd-project.php';
			require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/entities/emd-issue-shortcodes.php';
			if (!function_exists('emd_show_forms_lite_page')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/emd-form-builder-lite/emd-form-builder.php';
			}
			if (!function_exists('emd_lite_modal')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/emd-lite/emd-lite.php';
			}
			require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/scripts.php';
			if (!function_exists('emd_limit_by')) {
				require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/filter-functions.php';
			}
			require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/query-filters.php';
			require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/plugin-feedback-functions.php';
			require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/content-functions.php';
		}
		/**
		 * Loads plugin language files
		 *
		 * @access public
		 * @return void
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters('plugin_locale', get_locale() , 'software-issue-manager');
			$mofile = sprintf('%1$s-%2$s.mo', 'software-issue-manager', $locale);
			$mofile_shared = sprintf('%1$s-emd-plugins-%2$s.mo', 'software-issue-manager', $locale);
			$lang_file_list = Array(
				'emd-plugins' => $mofile_shared,
				'software-issue-manager' => $mofile
			);
			foreach ($lang_file_list as $lang_key => $lang_file) {
				$localmo = SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . '/lang/' . $lang_file;
				$globalmo = WP_LANG_DIR . '/software-issue-manager/' . $lang_file;
				if (file_exists($globalmo)) {
					load_textdomain($lang_key, $globalmo);
				} elseif (file_exists($localmo)) {
					load_textdomain($lang_key, $localmo);
				} else {
					load_plugin_textdomain($lang_key, false, SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . '/lang/');
				}
			}
		}
		/**
		 * Changes content on frontend views
		 *
		 * @access public
		 * @param string $content
		 *
		 * @return string $content
		 */
		public function change_content($content) {
			if (!is_admin()) {
				if (post_password_required()) {
					$content = get_the_password_form();
				} else {
					$mypost_type = get_post_type();
					if ($mypost_type == 'post' || $mypost_type == 'page') {
						$mypost_type = "emd_" . $mypost_type;
					}
					$ent_list = get_option($this->app_name . '_ent_list');
					if (in_array($mypost_type, array_keys($ent_list)) && class_exists($mypost_type)) {
						$func = "change_content";
						$obj = new $mypost_type;
						$content = $obj->$func($content);
					}
				}
			}
			return $content;
		}
		/**
		 * Creates plugin page in menu with submenus
		 *
		 * @access public
		 * @return void
		 */
		public function display_settings() {
			$settings_pages_cap = 'manage_options';
			$settings_pages_cap = apply_filters('emd_settings_pages_cap', $settings_pages_cap, $this->app_name);
			add_menu_page(__('SIM Com', $this->textdomain) , __('SIM Com', $this->textdomain) , $settings_pages_cap, $this->app_name, array(
				$this,
				'display_getting_started_page'
			));
			add_submenu_page($this->app_name, __('Getting Started', $this->textdomain) , __('Getting Started', $this->textdomain) , $settings_pages_cap, $this->app_name);
			add_submenu_page($this->app_name, __('Glossary', $this->textdomain) , __('Glossary', $this->textdomain) , $settings_pages_cap, $this->app_name . '_glossary', array(
				$this,
				'display_glossary_page'
			));
			add_submenu_page($this->app_name, __('Settings', $this->textdomain) , __('Settings', $this->textdomain) , $settings_pages_cap, $this->app_name . '_settings', array(
				$this,
				'display_settings_page'
			));
			add_submenu_page($this->app_name, __('Forms', $this->textdomain) , __('Forms', $this->textdomain) , $settings_pages_cap, $this->app_name . '_forms', array(
				$this,
				'display_forms_page'
			));
			add_submenu_page($this->app_name, __('Custom Fields', $this->textdomain) , __('Custom Fields', $this->textdomain) , $settings_pages_cap, $this->app_name . '_cust_fields', array(
				$this,
				'display_cust_fields_page'
			));
			add_submenu_page($this->app_name, __('Plugins', $this->textdomain) , __('Plugins', $this->textdomain) , $settings_pages_cap, $this->app_name . '_store', array(
				$this,
				'display_store_page'
			));
			add_submenu_page($this->app_name, __('Support', $this->textdomain) , __('Support', $this->textdomain) , $settings_pages_cap, $this->app_name . '_support', array(
				$this,
				'display_support_page'
			));
			//add submenu page under app settings page
			do_action('emd_ext_add_menu_pages', $this->app_name);
			$emd_lic_settings = get_option('emd_license_settings', Array());
			$show_lic_page = 0;
			if (!empty($emd_lic_settings)) {
				foreach ($emd_lic_settings as $key => $val) {
					if ($key == $this->app_name) {
						$show_lic_page = 1;
						break;
					} else if ($val['type'] == 'ext') {
						$show_lic_page = 1;
						break;
					}
				}
				if ($show_lic_page == 1 && function_exists('emd_show_license_page')) {
					add_submenu_page($this->app_name, __('Licenses', $this->textdomain) , __('Licenses', $this->textdomain) , 'manage_options', $this->app_name . '_licenses', array(
						$this,
						'display_licenses_page'
					));
				}
			}
		}
		/**
		 * Calls settings function to display glossary page
		 *
		 * @access public
		 * @return void
		 */
		public function display_glossary_page() {
			do_action($this->app_name . '_settings_glossary');
		}
		public function display_getting_started_page() {
			do_action($this->app_name . '_getting_started');
		}
		public function display_store_page() {
			emd_display_store($this->textdomain);
		}
		public function display_support_page() {
			emd_display_support($this->textdomain, 2, 'software-issue-manager');
		}
		public function display_licenses_page() {
			do_action('emd_show_license_page', $this->app_name);
		}
		public function display_settings_page() {
			do_action('emd_show_settings_page', $this->app_name);
		}
		public function display_forms_page() {
			do_action('emd_show_forms_lite_page', $this->app_name);
		}
		public function display_cust_fields_page() {
			emd_lite_get_operations('cust_fields', __('Custom Fields', $this->textdomain) , $this->textdomain);
		}
		/**
		 * Displays single, archive, tax and no-access frontend views
		 *
		 * @access public
		 * @return string, $template:emd template or template
		 */
		public function show_template($template) {
			return emd_show_template($this->app_name, SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR, $template);
		}
		/**
		 * Loads sidebar widgets
		 *
		 * @access public
		 * @return void
		 */
		public function include_widgets() {
			require_once SOFTWARE_ISSUE_MANAGER_PLUGIN_DIR . 'includes/entities/class-emd-issue-widgets.php';
		}
	}
endif;
/**
 * Returns the main instance of Software_Issue_Manager
 *
 * @return Software_Issue_Manager
 */
function SOFTWARE_ISSUE_MANAGER() {
	return Software_Issue_Manager::instance();
}
// Get the Software_Issue_Manager instance
SOFTWARE_ISSUE_MANAGER();