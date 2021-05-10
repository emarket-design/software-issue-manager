<?php
/**
 * Query Filter Functions
 *
 * @package SOFTWARE_ISSUE_MANAGER
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_filter('posts_request', 'software_issue_manager_posts_request', 99, 2);
add_filter('post_limits', 'software_issue_manager_post_limits', 99, 2);
add_filter('posts_orderby', 'software_issue_manager_posts_orderby', 99, 2);
/**
 * Change limit for author archive before wp_query is processed
 *
 * @since WPAS 4.8
 * @param string $input
 *
 * @return string $input
 */
function software_issue_manager_post_limits($input, $query) {
	if (!is_admin() && $query->is_main_query() && (is_author() || is_search())) {
		global $software_issue_manager_limit;
		$software_issue_manager_limit = $input;
	}
	return $input;
}
/**
 * Change orderby for author archive before wp_query is processed
 *
 * @since WPAS 4.8
 * @param string $input
 *
 * @return string $input
 */
function software_issue_manager_posts_orderby($input, $query) {
	$set_types = emd_find_limitby('frontend', 'software_issue_manager');
	if (!is_admin() && $query->is_main_query() && (is_author() || (is_search() && !empty($set_types)))) {
		global $wpdb;
		global $software_issue_manager_orderby;
		$input = str_replace($wpdb->posts . ".", "", $input);
		$software_issue_manager_orderby = $input;
		return '';
	}
	return $input;
}
/**
 * Change request for author archive before wp_query is processed
 *
 * @since WPAS 4.8
 * @param string $input
 *
 * @return string $input
 */
function software_issue_manager_posts_request($input, $query) {
	global $wpdb;
	if (!is_admin() && $query->is_main_query() && is_search()) {
		$input = emd_author_search_results('software_issue_manager', $input, $query, 'search');
	} elseif (!is_admin() && $query->is_main_query() && is_author()) {
		$input = emd_author_search_results('software_issue_manager', $input, $query, 'author');
	}
	return $input;
}
/**
 * Change query parameters before wp_query is processed
 *
 * @since WPAS 4.0
 * @param object $query
 *
 * @return object $query
 */
function software_issue_manager_query_filters($query) {
	if (!is_admin() && $query->is_main_query()) {
		$front_ents = emd_find_limitby('frontend', 'software_issue_manager');
		if ($query->is_author) {
			return $query;
		} elseif ($query->is_search && empty($front_ents)) {
			return $query;
		} elseif ($query->is_search && !empty($front_ents)) {
			$cap_post_types = get_post_types();
			foreach ($cap_post_types as $ptype) {
				if (!is_post_type_viewable($ptype)) {
					unset($cap_post_types[$ptype]);
				}
			}
			$query->set('post_type', array_diff($cap_post_types, $front_ents));
			return $query;
		} elseif (!empty($front_ents) && !empty($query->query['post_type']) && in_array($query->query['post_type'], $front_ents)) {
			$query = emd_limit_tax_single_archive('software_issue_manager', $query);
		}
	} elseif ($query->is_admin && $query->is_post_type_archive()) {
		$back_ents = emd_find_limitby('backend', 'software_issue_manager');
		if (empty($back_ents)) return $query;
		if (defined('DOING_AJAX') && DOING_AJAX) return $query;
		if (!empty($_GET['page']) && !empty($_GET['post_type']) && preg_match('/operations_' . $_GET['post_type'] . '/', $_GET['page'])) return $query;
		$query = emd_afc_filter('software_issue_manager', $query);
	}
	return $query;
}
add_action('pre_get_posts', 'software_issue_manager_query_filters');
/**
 * Get previous link for post type limited to seen by user
 * @since WPAS 4.0
 * @param string $link
 */
function software_issue_manager_limit_previous_post_link($link) {
	return emd_limit_prev_next_link('software_issue_manager', $link, true);
}
/**
 * Get next link for post type limited to seen by user
 * @since WPAS 4.0
 * @param string $link
 */
function software_issue_manager_limit_next_post_link($link) {
	return emd_limit_prev_next_link('software_issue_manager', $link, false);
}
add_filter('p2p_connectable_args', 'software_issue_manager_limit_by_filters', 10, 3);
/**
 * Limitby relationships seen by user
 * @since WPAS 4.0
 * @param array $args
 * @param string $ctype
 * @param object $lpost
 *
 * @return array $args
 */
function software_issue_manager_limit_by_filters($args, $ctype, $lpost) {
	return emd_limit_by_filters('software_issue_manager', $args, $ctype);
}