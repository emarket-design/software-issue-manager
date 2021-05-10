<?php
/**
 * Layout Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Retrieves a template part
 * @since WPAS 4.0
 *
 * Taken from bbPress,eaysdigitaldownloads
 *
 * @param string $app
 * @param string $slug
 * @param string $name Optional. Default null
 * @param bool   $load
 *
 * @return string
 *
 * @uses emd_locate_template()
 */
if (!function_exists('emd_get_template_part')) {
	function emd_get_template_part($app, $slug, $name = null, $load = true) {
		// Setup possible parts
		$templates = array();
		if (isset($name)) $templates[] = $slug . '-' . $name . '.php';
		$templates[] = $slug . '.php';
		// Allow template parts to be filtered
		$templates = apply_filters('emd_get_template_part', $templates, $slug, $name);
		// Return the part that is found
		return emd_locate_template($app, $templates, $load, false);
	}
}
/**
 * Retrieves a template part
 * @since WPAS 4.0
 *
 * Taken from bbPress,eaysdigitaldownloads
 *
 * @param string $app
 * @param array $template_names
 * @param bool   $load
 * @param bool   $require_once
 *
 * @return string
 *
 * @uses load_template()
 */
if (!function_exists('emd_locate_template')) {
	function emd_locate_template($app, $template_names, $load = false, $require_once = true) {
		// No file found yet
		$located = false;
		// Try to find a template file
		foreach ((array)$template_names as $template_name) {
			// Continue if template is empty
			if (empty($template_name)) continue;
			// Trim off any slashes from the template name
			$template_name = ltrim($template_name, '/');
			// try locating this template file by looping through the template paths
			foreach(emd_get_theme_template_paths($app) as $template_path) {
				if (file_exists($template_path . $template_name)) {
					$located = $template_path . $template_name;
					break;
				}
			}
			if($located) {
				break;
			}
		}
		if ((true == $load) && !empty($located)) {
			load_template($located, $require_once);
		}
		return $located;
	}
}
if (!function_exists('emd_get_theme_template_paths')) {
	function emd_get_theme_template_paths($app) {
		$template_dir = emd_get_theme_template_dir_name();
		$file_paths = array(
			1 => trailingslashit(get_stylesheet_directory()) . $template_dir,
			10 => trailingslashit(get_template_directory()) . $template_dir,
			100 => emd_get_templates_dir($app)
		);

		$file_paths = apply_filters('emd_template_paths', $file_paths);
		// sort the file paths based on priority
		ksort($file_paths, SORT_NUMERIC);
		return array_map('trailingslashit', $file_paths);
	}
}
if (!function_exists('emd_get_theme_template_dir_name')) {
	function emd_get_theme_template_dir_name() {
		return trailingslashit(apply_filters('emd_templates_dir', 'emd_templates'));
	}
}
if (!function_exists('emd_get_templates_dir')) {
	function emd_get_templates_dir($app) {
		return constant(strtoupper(str_replace("-", "_", $app)) . '_PLUGIN_DIR') . 'layouts/';
	}
}
/**
 * Checks template settings and access to find template
 *
 * @since WPAS 5.3
 * @param string $app
 * @param string $fpath
 * @param string $template
 *
 * @return string $template
 */
if (!function_exists('emd_show_template')) {
	function emd_show_template($app, $fpath, $template) {
		$file = '';
		$tools = get_option($app . '_tools');
		if (is_single()){
			global $post;
			$post_type = $post->post_type;
			$ent_list = get_option($app . "_ent_list");
			if(!in_array($post_type,array_keys($ent_list))){
				return $template;
			}	
			$file = emd_get_single_template($app,$post_type);
		} elseif (is_tax()) {
			$tax_list = get_option($app . "_tax_list");
			//gets taxs with archive_view
			$tax_keys = emd_get_tax_keys_with_views(apply_filters('emd_get_conn_tax', $tax_list,$app));
			if(empty($tax_keys) || !is_tax($tax_keys)){
				return $template;
			}
			$file = emd_get_taxonomy_template($app);
		} elseif (is_post_type_archive()){
			$current = get_queried_object();
			$post_type = $current->name;
			$ent_list = get_option($app . "_ent_list");
			$temp_ent_list = Array();
			foreach($ent_list as $kent => $vent){
				if(!empty($vent['archive_view'])){
					$temp_ent_list[] = $kent;
				}
			}
			if(!in_array($post_type,$temp_ent_list)){
				return $template;
			}	
			$file = emd_get_archive_template($app);
		}
		if($file){
			if(!empty($tools['disable_emd_templates']) && $file != 'emd-no-access.php') return $template;
			$template = locate_template(str_replace('_','-',$app) . '.php');
			if(!$template){
				$template = locate_template('emd_templates/'. $file);
				if(!$template){
					if($file != 'emd-no-access.php'){
						wp_enqueue_style('emd-template-css', constant(strtoupper(str_replace("-", "_", $app)) . '_PLUGIN_URL') . 'assets/css/emd-template.css','',constant(strtoupper(str_replace("-", "_", $app)) . '_VERSION'));
					}
					$template = $fpath . 'layouts/' . $file;
				}
			}
		}
		return $template;
	}
}
if (!function_exists('emd_show_temp_sidebar')) {
	add_filter('emd_show_temp_sidebar','emd_show_temp_sidebar',10,3);

	function emd_show_temp_sidebar($has_sidebar,$app,$type){
		if($type == 'single'){
			global $post;
			$post_type = $post->post_type;
			$ent_conf = get_option($app . "_ent_map_list");
			if(!empty($ent_conf[$post_type]['single_temp'])){
				$has_sidebar = $ent_conf[$post_type]['single_temp'];
			}
		}
		elseif($type == 'archive'){
			$current = get_queried_object();
			$post_type = $current->name;
			$ent_conf = get_option($app . "_ent_map_list");
			if(!empty($ent_conf[$post_type]['archive_temp'])){
				$has_sidebar = $ent_conf[$post_type]['archive_temp'];
			}	
		}	
		elseif($type == 'taxonomy'){
			$current_term = get_queried_object();
			$tax_conf = get_option($app . "_tax_settings");
			if(!empty($tax_conf[$current_term->taxonomy]['temp'])){
				$has_sidebar = $tax_conf[$current_term->taxonomy]['temp'];
			}	
		}
		return $has_sidebar;
	}
}
if (!function_exists('emd_get_sidebar')) {
	add_action('emd_sidebar','emd_get_sidebar');
	function emd_get_sidebar($app) {
		emd_get_template_part($app, 'emd-sidebar' );
	}
}
if (!function_exists('emd_widgets_init')) {
	function emd_widgets_init(){
		register_sidebar( array(
				'name' => esc_html__( 'EMD Widget Area', 'emd-plugins' ),
				'id' => 'sidebar-emd',
				'description' => esc_html__( 'Appears only in the sidebar on single, archive and taxonomy posts of emdplugins.com plugins. The location of this widget area can be adjusted in the corresponding plugin\'s settings.', 'emd-plugins' ),
				'before_widget' => '<aside id="%1$s" style="display:block;" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>'
			) );
	}
	add_action( 'widgets_init', 'emd_widgets_init' );
}
if (!function_exists('emd_get_single_template')) {
	function emd_get_single_template($app,$post_type){
		$file = '';
		global $post;
		$access_views = get_option($app . "_access_views");
		if (!empty($access_views['single'])) {
			foreach ($access_views['single'] as $vval) {
				if ($post_type == $vval['obj'] && !current_user_can('view_' . $vval['name'])) {
					$file = 'emd-no-access.php';
					break;	
				}
			}
		}
		if($file != 'emd-no-access.php'){
			$file = 'emd-single.php';
			$front_ents = emd_find_limitby('frontend', $app);
			if(!empty($front_ents) && in_array($post_type,$front_ents)){
				$pids = apply_filters('emd_limit_by', Array() , $app, $post_type,'frontend');
				if (!empty($pids) && !in_array($post->ID, $pids)) {
					$file = 'emd-no-access.php';
				}
			}
		}
		$file = apply_filters('emd_change_template_access',$file,$app,$post_type,'single');
		return $file;
	}
}
if (!function_exists('emd_get_taxonomy_template')) {
	function emd_get_taxonomy_template($app){
		$file = 'emd-taxonomy.php';
		$access_views = get_option($app . "_access_views");
		if (!empty($access_views['tax'])) {
			foreach ($access_views['tax'] as $vval) {
				if (is_tax($vval['obj']) && !current_user_can('view_' . $vval['name'])) {
					$file = 'emd-no-access.php';
					$file = apply_filters('emd_change_template_access',$file,$app,$vval['name'],'tax');
					break;	
				}
			}
		}
		return $file;
	}
}
if (!function_exists('emd_get_archive_template')) {
	function emd_get_archive_template($app){
		$file = 'emd-archive.php';
		$access_views = get_option($app . "_access_views");
		if(!empty($access_views['archive'])) {
			foreach ($access_views['archive'] as $vval) {
				if (is_post_type_archive($vval['obj']) && !current_user_can('view_' . $vval['name'])) {
					$file = 'emd-no-access.php';
					$file = apply_filters('emd_change_template_access',$file,$app,$vval['name'],'archive');
					break;	
				}
			}
		}
		return $file;
	}
}
if(!function_exists('emd_get_tax_keys_with_views')) {
	function emd_get_tax_keys_with_views($tax_list){
		$tax_keys = Array();
		if(!empty($tax_list)){
			foreach($tax_list as $ent_tax => $tax){
				foreach($tax as $ktax => $vtax){
					if(!empty($vtax['archive_view']) && $vtax['archive_view'] == 1){
						$tax_keys[] = $ktax;
					}
				}
			}
		}
		return $tax_keys;
	}
}
if (!function_exists('emd_show_temp_navigation')) {
	add_filter('emd_show_temp_navigation','emd_show_temp_navigation',10,3);

	function emd_show_temp_navigation($has_navigation,$app,$type){
		if($type == 'single'){
			global $post;
			$post_type = $post->post_type;
			$ent_conf = get_option($app . "_ent_map_list");
			if(!empty($ent_conf[$post_type]['hide_prev_next'])){
				$has_navigation = false;
			}
		}
		elseif($type == 'archive'){
			$current = get_queried_object();
			$post_type = $current->name;
			$ent_conf = get_option($app . "_ent_map_list");
			if(!empty($ent_conf[$post_type]['hide_archive_page_nav'])){
				$has_navigation = false;
			}
		}
		elseif($type == 'taxonomy'){
			$current_term = get_queried_object();
			$tax_conf = get_option($app . "_tax_settings");
			if(!empty($tax_conf[$current_term->taxonomy]['hide_page_nav'])){
				$has_navigation = false;
			}
		}
		return $has_navigation;
	}
}
if (!function_exists('emd_show_single_edit_link')) {
	add_filter('emd_show_single_edit_link','emd_show_single_edit_link',10,2);
	function emd_show_single_edit_link($show,$app){
		global $post;
		$post_type = $post->post_type;
		$ent_conf = get_option($app . "_ent_map_list");
		if(!empty($ent_conf[$post_type]['hide_edit_link'])){
			$show = false;
		}
		return $show;
	}
}
if (!function_exists('emd_change_container')) {
	add_filter('emd_change_container','emd_change_container',10,3);
	function emd_change_container($container,$app,$type){
		$container = 'container';
		if($type == 'single'){
			global $post;
			$post_type = $post->post_type;
			$ent_conf = get_option($app . "_ent_map_list");
			if(!empty($ent_conf[$post_type]['single_container'])){
				$container = $ent_conf[$post_type]['single_container'];
			}
		}
		elseif($type == 'archive'){
			$current = get_queried_object();
			$post_type = $current->name;
			$ent_conf = get_option($app . "_ent_map_list");
			if(!empty($ent_conf[$post_type]['archive_container'])){
				$container = $ent_conf[$post_type]['archive_container'];
			}	
		}	
		elseif($type == 'taxonomy'){
			$current_term = get_queried_object();
			$tax_conf = get_option($app . "_tax_settings");
			if(!empty($tax_conf[$current_term->taxonomy]['container'])){
				$container = $tax_conf[$current_term->taxonomy]['container'];
			}	
		}
		if($container == 'container'){
			$container = 'emdcontainer';
		}
		elseif($container == 'container-fluid'){
			$container = 'emdfcontainer';
		}
		return $container;
	}
}
				
