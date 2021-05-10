<?php
/**
 * Filter Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
add_filter('emd_limit_by', 'emd_limit_by', 10, 5);
/**
 * Limits shown entities by limitby capabilities of users
 *
 * @since WPAS 4.0
 * @param string $app
 * @param string $check_ptype post_type
 * @param array $pids post ids
 * @param string $type frontend or admin
 *
 * @return array $pids accessible post ids
 */
function emd_limit_by($pids = Array() , $app, $check_ptype, $where = 'frontend', $prev_next = 0) {
	global $filter;
	$ent_list = get_option($app . '_ent_list');
	if(!array_key_exists($check_ptype,$ent_list)){
                return $pids;
        }
	$limitby_caps = get_option($app . '_limitby_caps', Array());
	$limitby_auth_caps = get_option($app . '_limitby_auth_caps', Array());
	$pobject = get_post_type_object($check_ptype);
	$edit_cap = $pobject->cap->edit_posts;
	$ent_map_list = get_option($app . "_ent_map_list",Array());
	$make_all_visible = 0;
	if(!empty($ent_map_list[$check_ptype]['make_visitor_visible']) && $ent_map_list[$check_ptype]['make_visitor_visible'] == 1) {
		$make_all_visible = 1;
	}

	if (!empty($limitby_caps[$check_ptype])) {
		$pids = emd_get_pids($pids, $limitby_caps[$check_ptype], $prev_next);
	}
	if (!empty($limitby_auth_caps[$check_ptype])) {
		$pids = emd_get_author_pids($pids, $limitby_auth_caps[$check_ptype], $check_ptype, $where, $prev_next, $make_all_visible);
	}
	elseif(!current_user_can($edit_cap) || $prev_next == 1){
		$pids = emd_get_author_pids_nolimit($pids, $check_ptype, $prev_next);
	}
	if (empty($pids) && $filter == 1) {
		$pids = Array(
			'0'
		);
	}
	$filter = 0;
	return $pids;
}
/**
 * Gets pid list for entities with no limit roles and no edit capabilities
 *
 * @since WPAS 4.8
 * @param array $pids post ids
 * @param string $ptype  posttype
 *
 * @return array $pids accessible post ids
 */
function emd_get_author_pids_nolimit($pids, $ptype, $prev_next) {
	global $user_ID, $wpdb, $filter;
	if ($prev_next == 1 || (!(is_multisite() && is_super_admin())) || $user_ID == 0) {
		$filter = 1;
		if($user_ID == 0){
			$author_posts = $wpdb->get_results("SELECT ID FROM $wpdb->posts wp, $wpdb->postmeta wpm WHERE wp.ID= wpm.post_id AND ((wpm.meta_key='wpas_form_submitted_by' AND wpm.meta_value='Visitor') OR wp.post_author=0) AND wp.post_type='" . $ptype . "' AND (wp.post_status='publish' OR wp.post_status='pending')");
		}
		elseif(!current_user_can('edit_' . $ptype . 's')){
			$author_posts = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_author='" . $user_ID . "' AND post_type='" . $ptype . "' AND (post_status='publish' OR post_status='pending')");
			$visitor_posts = $wpdb->get_results("SELECT ID FROM $wpdb->posts wp, $wpdb->postmeta wpm WHERE wp.ID= wpm.post_id AND ((wpm.meta_key='wpas_form_submitted_by' AND wpm.meta_value='Visitor') OR wp.post_author=0) AND wp.post_type='" . $ptype . "' AND (wp.post_status='publish' OR wp.post_status='pending')");
			$author_posts = array_merge($author_posts,$visitor_posts);
		}
		else {
			$author_posts = get_posts(array('post_type'=>$ptype,'posts_per_page'=>-1));
		}
		if (!empty($author_posts)) {
			foreach ($author_posts as $apost) {
				$pids[$apost->ID] = $apost->ID;
			}
		}
	}
	return $pids;
}
/**
 * Gets pid list by limitby_author capabilities
 *
 * @since WPAS 4.0
 * @param array $pids post ids
 * @param string $rel_name
 * @param string $ptype  posttype
 *
 * @return array $pids accessible post ids
 */
function emd_get_author_pids($pids, $rel_arr, $ptype, $where, $prev_next, $make_all_visible) {
	global $user_ID, $wpdb, $filter;
	$rel_name = "limitby_author_" . $where . "_" . $ptype . "s";
	if(in_array($rel_name,$rel_arr)){
		if ($prev_next == 1 || (!(is_multisite() && is_super_admin()) && (current_user_can($rel_name) || !current_user_can('edit_' . $ptype . 's'))) || $user_ID == 0) {
			$filter = 1;
			if($make_all_visible == 1){
				$author_posts = get_posts(array('post_type'=>$ptype,'posts_per_page'=>-1));
			}
			elseif($user_ID == 0){
				$author_posts = $wpdb->get_results("SELECT ID FROM $wpdb->posts wp, $wpdb->postmeta wpm WHERE wp.ID= wpm.post_id AND ((wpm.meta_key='wpas_form_submitted_by' AND wpm.meta_value='Visitor') OR wp.post_author=0) AND wp.post_type='" . $ptype . "' AND (wp.post_status='publish' OR wp.post_status='pending')");
			}
			elseif(current_user_can($rel_name)){
			//CHECK this with wp ticket SAFIYE 15-July-2019
			//elseif(current_user_can($rel_name) || !current_user_can('edit_' . $ptype . 's')){
				//authors can edit their posts
				//$author_posts = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_author='" . $user_ID . "' AND post_type='" . $ptype . "' AND (post_status='publish' OR post_status='pending')");
				$author_posts = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_author='" . $user_ID . "' AND post_type='" . $ptype . "'");
				$visitor_posts = $wpdb->get_results("SELECT ID FROM $wpdb->posts wp, $wpdb->postmeta wpm WHERE wp.ID= wpm.post_id AND ((wpm.meta_key='wpas_form_submitted_by' AND wpm.meta_value='Visitor') OR wp.post_author=0) AND wp.post_type='" . $ptype . "' AND (wp.post_status='publish' OR wp.post_status='pending')");
				$author_posts = array_merge($author_posts,$visitor_posts);
			}
			else {
				$author_posts = get_posts(array('post_type'=>$ptype,'posts_per_page'=>-1));
			}
			if (!empty($author_posts)) {
				foreach ($author_posts as $apost) {
					$pids[$apost->ID] = $apost->ID;
				}
			}
		}
	}
	return $pids;
}
/**
 * Gets pid list by limitby relationships to entity with a userid attribute
 *
 * @since WPAS 4.0
 * @param array $pids post ids
 * @param string $limitby relname
 *
 * @return array $pids accessible post ids
 */
function emd_get_pids($pids, $limitby, $prev_next) {
	global $user_ID, $wpdb, $filter;
	foreach ($limitby as $rel_name => $meta_key) {
		if ($prev_next == 1 || (!(is_multisite() && is_super_admin()) && current_user_can('limitby_' . $rel_name))) {
			if($meta_key == 'user'){
				$conn = p2p_type($rel_name)->get_connected($user_ID, array(
                                'posts_per_page' => - 1
                        	));
			}
			else {
				$res_postid = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta 
						WHERE meta_value='$user_ID' AND meta_key='$meta_key'", ARRAY_A);
				if (!empty($res_postid) && $res_postid[0]['post_id']) {
					$filter = 1;
					$upost_id = $res_postid[0]['post_id'];
					$conn = p2p_type($rel_name)->get_connected($upost_id, array(
						'posts_per_page' => - 1
					));
				}
			}
			if (!empty($conn)) {
				foreach ($conn->posts as $cpost) {
					$pids[$cpost->ID] = $cpost->ID;
				}
			}
		}
	}
	return $pids;
}
/**
 * Limits form dropdown entities by limitby capabilities of users
 * if empty and emtity has user_key only add current user
 *
 * @since WPAS 4.0
 * @param string $app
 * @param string $class
 * @param array $from
 *
 * @return array $pids accessible post ids
 */
function emd_get_form_pids($app, $class) {
	global $wpdb;
	$pids = apply_filters('emd_limit_by', Array() , $app, $class, 'frontend');
	if (empty($pids) && !current_user_can('edit_' . $class . 's')) {
		$ent_list = get_option($app . '_ent_list');
		if (!empty($ent_list[$class]['user_key'])) {
			$curr_id = get_current_user_id();
			$meta_key = $ent_list[$class]['user_key'];
			//find post id of entity which has user id as field
			$res_postid = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_value='$curr_id' AND meta_key='$meta_key'", ARRAY_A);
			$pids = $res_postid[0];
		}
	}
	return $pids;
}
/**
 * Limits prev and next entity by limitby capabilities of users
 *
 * @since WPAS 4.0
 * @param string $app
 * @param string $link
 * @param bool $previous
 *
 * @return string $out
 */
function emd_limit_prev_next_link($app, $link, $previous) {
	global $wp_query, $post;
	$pids = apply_filters('emd_limit_by', Array() , $app, $wp_query->query_vars['post_type'], 'frontend', 1);
	$pids = array_values($pids);
	$key = array_search($post->ID, $pids);
	if ($previous) $key--;
	else $key++;
	if (!isset($pids[$key])) return '';
	$adjpost = get_post($pids[$key]);
	$rel = $previous ? 'prev' : 'next';
	$permalink = get_permalink($adjpost);
	if ($previous) {
		$out = '<div class="nav-previous">';
	} else {
		$out = '<div class="nav-next">';
	}
	$out.= '<a href="' . $permalink . '" rel="' . $rel . '">' . $adjpost->post_title . '</a></div>';
	return $out;
}
/**
 * Limits shown entities by limitby capabilities of users in single, taxonomy and archive pages
 *
 * @since WPAS 4.0
 * @param string $app
 * @param object $query
 *
 * @return object $query
 */
function emd_limit_tax_single_archive($app, $query) {
	$ent_list = get_option($app . '_ent_list');
	if ($query->is_tax && $query->is_archive) {
		$qtax = get_queried_object();
		$qtax_obj = get_taxonomy($qtax->taxonomy);
		if(!empty($qtax_obj->object_type[0]) && array_key_exists($qtax_obj->object_type[0],$ent_list)){
			$pids = apply_filters('emd_limit_by', Array() , $app, $qtax_obj->object_type[0], 'frontend');
			$query->query_vars['post__in'] = $pids;
		}
	} elseif ($query->is_single) {
		if(!empty($query->query_vars['post_type']) && array_key_exists($query->query_vars['post_type'],$ent_list)){
			add_filter('previous_post_link', $app . '_limit_previous_post_link');
			add_filter('next_post_link', $app . '_limit_next_post_link');
		}
	} elseif ($query->is_post_type_archive && !empty($query->query_vars['post_type']) && array_key_exists($query->query_vars['post_type'],$ent_list)) {
		$pids = apply_filters('emd_limit_by', Array() , $app, $query->query_vars['post_type'], 'frontend');
		$query->query_vars['post__in'] = $pids;
	}
	return $query;
}
/**
 * Limits shown entities for relationships
 *
 * @since WPAS 4.0
 * @param string $app
 * @param array $args
 * @param object $ctype
 *
 * @return array $args
 */
function emd_limit_by_filters($app, $args, $ctype) {
	global $typenow;
	$direction = $ctype->get_direction();
	switch ($direction){
		case 'from':
			if(isset($ctype->side['to']->query_vars['post_type'])){
				$check_ptype = $ctype->side['to']->query_vars['post_type'][0];
			}
			else {
				$check_ptype = $ctype->side['to']->query_vars['role'];
			}
			break;
		case 'to':
			if(isset($ctype->side['from']->query_vars['post_type'])){
				$check_ptype = $ctype->side['from']->query_vars['post_type'][0];
			}
			else {
				$check_ptype = $ctype->side['from']->query_vars['role'];
			}
			break;
		case 'any':
		default:
			if(isset($ctype->side['to']->query_vars['post_type']) && $typenow == $ctype->side['to']->query_vars['post_type'][0]){
				if(isset($ctype->side['from']->query_vars['post_type'])){
					$check_ptype = $ctype->side['from']->query_vars['post_type'][0];
				}
				else {
					$check_ptype = $ctype->side['from']->query_vars['role'];
				}
			}
			else {
				if(isset($ctype->side['to']->query_vars['post_type'])){
					$check_ptype = $ctype->side['to']->query_vars['post_type'][0];
				}
				else {
					$check_ptype = $ctype->side['to']->query_vars['role'];
				}
			}
			break;
	}
	$pids = apply_filters('emd_limit_by', Array() , $app, $check_ptype, 'backend');
	if(!empty($args['p2p:exclude']) && empty($args['p2p:include'])){
		$include_arr = array_diff($pids,$args['p2p:exclude']);
		if(empty($include_arr) && !empty($pids)){
			$args['p2p:include'] = Array(0);
		}
		else {
			$args['p2p:include'] = $include_arr;
		}
	}
	else {
		if(!empty($args['p2p:include']) && !empty($pids)){
			$include_arr = array_intersect($args['p2p:include'],$pids);
			$args['p2p:include'] = $include_arr;
		}
		elseif(empty($args['p2p:include']) && !empty($pids)) {
			$args['p2p:include'] = $pids;
		}
	}
	return $args;
}
/**
 * Limits shown entities by limitby capabilities of users in admin entity archive pages
 *
 * @since WPAS 4.0
 * @param string $app
 * @param object $query
 *
 * @return object $query
 */
function emd_afc_filter($app, $query) {
	global $relposts, $filter;
	if(is_array($query->query_vars['post_type'])){
		$qpost_type = $query->query_vars['post_type'][0];
	}
	else {
		$qpost_type = $query->query_vars['post_type'];
	}	
	$pids = apply_filters('emd_limit_by', Array() , $app, $qpost_type, 'backend');
	if(!empty($query->query_vars['post__not_in'])){
		$pids = array_diff($pids,$query->query_vars['post__not_in']);
	}
	if (!empty($query->query_vars['post__in']) && !empty($pids)) {
		$query->query_vars['post__in'] = array_intersect($query->query_vars['post__in'], $pids);
		if (empty($query->query_vars['post__in'])) {
			$query->query_vars['post__in'] = Array(
				'0'
			);
		}
	} elseif (empty($query->query_vars['post__in'])) {
		$query->query_vars['post__in'] = $pids;
	}
	if (!isset($query->query_vars['emd_afc_custom']) && ($filter == 1 || !empty($query->query_vars['post__in']))) {
		foreach ($query->query_vars['post__in'] as $pid) {
			$relposts[$qpost_type][$pid] = get_post($pid);
		}
		add_filter('views_edit-' . $qpost_type, 'emd_filter_views');
	}
	if (!empty($_GET['author']) && !isset($query->query_vars['emd_afc_filter']) && !isset($query->query_vars['emd_afc_custom'])) {
		$query->query_vars['author'] = $_GET['author'];
	}
	return $query;
}
/**
 * Update view of entity archive admin page
 *
 * @since WPAS 4.0
 * @param array $view
 *
 * @return array $status_links
 */
function emd_filter_views($view) {
	global $locked_post_status, $avail_post_stati, $post_type_object, $wpdb;
	global $relposts, $wp_query;
	if (empty($relposts) && !$wp_query->query_vars['emd_afc_filter']) {
		return $view;
	}
	if (isset($wp_query->query_vars['emd_afc_filter']) && $wp_query->query_vars['emd_afc_filter'] == 1) {
		return array();
	}
	$post_type = $post_type_object->name;
	$post_type_object = get_post_type_object($post_type);
	if (!empty($locked_post_status)) return array();
	$status_links = array();
	foreach (get_post_stati() as $state) {
		$stats[$state] = 0;
	}
	$user_posts_count = 0;
	$current_user_id = get_current_user_id();
	if (!empty($relposts) && !isset($wp_query->query_vars['emd_afc_filter'])) {
		foreach ($relposts[$post_type] as $cpost) {
			if(!empty($cpost)){
				if ($cpost->post_author == $current_user_id) {
					$user_posts_count++;
				}
				$stats[$cpost->post_status]+= 1;
			}
		}
	}
	$num_posts = (object)$stats;
	$class = '';
	$allposts = '';
	if ($user_posts_count) {
		if (isset($_GET['author']) && ($_GET['author'] == $current_user_id)) $class = ' class="current"';
		$status_links['mine'] = "<a href='edit.php?post_type=$post_type&author=$current_user_id'$class>" . sprintf(_nx('Mine <span class="count">(%s)</span>', 'Mine <span class="count">(%s)</span>', $user_posts_count, 'posts') , number_format_i18n($user_posts_count)) . '</a>';
		$allposts = '&all_posts=1';
	}
	$total_posts = array_sum((array)$num_posts);
	// Subtract post types that are not included in the admin all list.
	foreach (get_post_stati(array(
		'show_in_admin_all_list' => false
	)) as $state) $total_posts-= $num_posts->$state;
	$class = empty($class) && empty($_REQUEST['post_status']) && empty($_REQUEST['show_sticky']) ? ' class="current"' : '';
	$status_links['all'] = "<a href='edit.php?post_type=$post_type{$allposts}'$class>" . sprintf(_nx('All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', $total_posts, 'posts') , number_format_i18n($total_posts)) . '</a>';
	foreach (get_post_stati(array(
		'show_in_admin_status_list' => true
	) , 'objects') as $status) {
		$class = '';
		$status_name = $status->name;
		if (!in_array($status_name, $avail_post_stati)) continue;
		if (empty($num_posts->$status_name)) continue;
		if (isset($_REQUEST['post_status']) && $status_name == $_REQUEST['post_status']) $class = ' class="current"';
		$status_links[$status_name] = "<a href='edit.php?post_status=$status_name&amp;post_type=$post_type'$class>" . sprintf(translate_nooped_plural($status->label_count, $num_posts->$status_name) , number_format_i18n($num_posts->$status_name)) . '</a>';
	}
	return $status_links;
}
