<?php
/**
 *  Uninstall Software Issue Manager
 *
 * Uninstalling deletes notifications and terms initializations
 *
 * @package SOFTWARE_ISSUE_MANAGER
 * @since WPAS 4.0
 */
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
if (!current_user_can('activate_plugins')) return;
$tools = get_option('software_issue_manager_tools');
if (!empty($tools['remove_data'])) {
	global $wpdb;
	//delete all relationships
	$rel_list = get_option('software_issue_manager_rel_list');
	if (!empty($rel_list)) {
		foreach ($rel_list as $krel => $vrel) {
			$rel_type = str_replace("rel_", "", $krel);
			$wpdb->query("DELETE FROM " . $wpdb->prefix . "p2p WHERE p2p_type =  '" . $rel_type . "';");
			if (!empty($vrel['attrs'])) {
				foreach (array_keys($vrel['attrs']) as $rel_attr) {
					$wpdb->query("DELETE FROM " . $wpdb->prefix . "p2pmeta WHERE meta_key =  '" . $rel_attr . "';");
				}
			}
		}
	}
	$app_post_types = Array(
		'emd_project',
		'emd_issue'
	);
	foreach ($app_post_types as $post_type) {
		//delete all taxonomies
		$tax_list = get_option('software_issue_manager_tax_list');
		if (!empty($tax_list[$post_type])) {
			foreach (array_keys($tax_list[$post_type]) as $tkey) {
				if ($tax_list[$post_type][$tkey]['type'] != 'builtin') {
					$wpdb->delete($wpdb->term_taxonomy, array(
						'taxonomy' => $tkey
					));
				}
			}
		}
		//delete posts and attrs
		$postslist = get_posts(array(
			'post_type' => $post_type,
			'numberposts' => - 1,
			'post_status' => 'any'
		));
		if (!empty($postslist)) {
			$entity_fields = get_option('software_issue_manager_attr_list');
			foreach ($postslist as $mypost) {
				if (!empty($entity_fields[$post_type])) {
					//Delete fields
					foreach (array_keys($entity_fields[$post_type]) as $myfield) {
						if (in_array($entity_fields[$post_type][$myfield]['display_type'], Array(
							'file',
							'image',
							'plupload_image',
							'thickbox_image'
						))) {
							$pmeta = get_post_meta($mypost->ID, $myfield);
							if (!empty($pmeta)) {
								foreach ($pmeta as $file_id) {
									wp_delete_attachment($file_id);
								}
							}
						}
						delete_post_meta($mypost->ID, $myfield);
					}
					//delete post
					wp_delete_post($mypost->ID);
				}
			}
		}
	}
	// Delete orphan relationships
	$wpdb->query("DELETE tr FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} posts ON posts.ID = tr.object_id WHERE posts.ID IS NULL;");
	// Delete orphan terms
	$wpdb->query("DELETE t FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.term_id IS NULL;");
	// Delete orphan term meta
	if (!empty($wpdb->termmeta)) {
		$wpdb->query("DELETE tm FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id WHERE tt.term_id IS NULL;");
	}
	$pages_list = get_option('software_issue_manager_setup_pages_list', Array());
	if (!empty($pages_list)) {
		foreach ($pages_list as $page_id) {
			wp_trash_post($page_id);
		}
	}
	// Clear any cached data that has been removed
	wp_cache_flush();
}
if (!empty($tools['remove_settings'])) {
	global $wpdb;
	$pages_list = get_option('software_issue_manager_setup_pages_list', Array());
	if (!empty($pages_list)) {
		foreach ($pages_list as $page_id) {
			wp_trash_post($page_id);
		}
	}
	//delete all settings
	$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'software_issue_manager\_%';");
}
$emd_activated_plugins = get_option('emd_activated_plugins');
if (!empty($emd_activated_plugins)) {
	$emd_activated_plugins = array_diff($emd_activated_plugins, Array(
		'software-issue-manager'
	));
	update_option('emd_activated_plugins', $emd_activated_plugins);
}