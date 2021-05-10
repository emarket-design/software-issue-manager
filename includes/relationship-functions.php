<?php
/**
 * Relationship Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Gets relationship attributes
 *
 * @since WPAS 4.0
 * @param array $fields
 * @param string $rel
 * @param string $type
 * @return array $newrels_fields array of field value htmls
 */
function emd_show_p2p_fields($fields, $rel, $type) {
	$newrels_fields = Array();
	foreach ($fields as $field_name => $myfield) {
		$ftitle = $myfield['title'];
		$id_name = "rel-" . $type . "-" . $field_name;
		$field_val = "<div id=\"" . esc_attr($id_name) . "\" class=\"rel-attrib-block\">";
		$field_val.= "<div id=\"" . esc_attr($id_name) . "-label\" class=\"rel-attrib-label\">" . $ftitle . "</div>";
		$field_val.= "<div id=\"" . esc_attr($id_name) . "-value\" class=\"rel-attrib-value\">";
		$field_val.= p2p_get_meta($rel->p2p_id, $field_name, true) . "</div>";
		$field_val.= "</div>";
		$newrels_fields[] = $field_val;
	}
	return $newrels_fields;
}
/**
 * Gets relationships for an entity
 *
 * @since WPAS 4.0
 * @param string $conn_type
 * @param string $type
 * @param string $mode
 * @param string $post
 * @param bool $show_attributes
 * @param bool $user_rel
 * @return array $res array of related entities for frontend display
 */
function emd_get_p2p_connections($conn_type, $type, $mode, $post, $show_attributes = 0, $user_rel = 0, $direction='',$app='',$filter='') {
	$show = "";
	$newrels = Array();
	$fields = Array();
	$rels = Array();
	if ($show_attributes == 1 && p2p_type($type)) {
		$fields = p2p_type($type)->fields;
	}

	$args = array('posts_per_page' => - 1);
	if(!empty($filter) && !empty($app) && p2p_type($type)){
		$rel_class = p2p_type($type)->side['from']->query_vars['post_type'][0];
                if($post->post_type == $rel_class){
                        $rel_class = p2p_type($type)->side['to']->query_vars['post_type'][0];
                }
                $emd_query = new Emd_Query($rel_class, $app);
                $emd_query->args_filter($filter);
        	$args = array_merge($args,$emd_query->args);
	}

	if(p2p_type($type)){
		if ($conn_type == 'related') {
			$related = p2p_type($type)->get_related($post, $args); 
		} elseif ($conn_type == 'connected') {
			if($direction == 'to'){
				$related = p2p_type($type)->set_direction('to')->get_connected($post, $args);
			}
			else {
				$related = p2p_type($type)->get_connected($post, $args); 
			}
		}
		if ($user_rel == 1) {
			$rels = $related->results;
		} else {
			$rels = $related->posts;
		}
	}
	if (!empty($rels)) {
		foreach ($rels as $myrel) {
			$newrels[$myrel->ID] = $myrel;
			if ($user_rel == 1) {
				$newrels[$reluser->ID]->ptitle = $reluser->data->display_name;
				$newrels[$reluser->ID]->permalink = "/author/" . $reluser->data->user_nicename;
			} else {
				$newrels[$myrel->ID]->ptitle = get_the_title($myrel->ID);
				$newrels[$myrel->ID]->permalink = get_permalink($myrel->ID);
			}
			$p2p_ids[$myrel->ID][] = $myrel->p2p_id;
			$newrels[$myrel->ID]->p2p_ids = $p2p_ids[$myrel->ID];
		}
		$rels = $newrels;
		foreach($newrels as $krel => $myrel){
			if (!empty($fields) && $conn_type == 'connected') {
				$rels[$krel]->fields = emd_show_p2p_fields($fields,$myrel, $type);
			}
		}
	}
	$before_item = "<li>";
	$after_item = "</li>";
	if ($mode == 'ul') {
		$before_list = "<ul id=\"rel-" . esc_attr($type) . "-list\">";
		$after_list = "</ul>";
	} elseif ($mode == 'ol') {
		$before_list = "<ol id=\"rel-" . esc_attr($type) . "-list\">";
		$after_list = "</ol>";
	} elseif ($mode == 'inline') {
		$before_item = "";
		$after_item = ",";
		$before_list = "";
		$after_list = "";
	} elseif ($mode == 'std') {
		$before_list = "";
		$after_list = "";
		$before_item = "";
		$after_item = "";
	}
	$res['fields'] = $fields;
	$res['before_list'] = $before_list;
	$res['after_list'] = $after_list;
	$res['rels'] = $rels;
	$res['before_item'] = $before_item;
	$res['after_item'] = $after_item;
	return $res;
}
function emd_check_rel_count($rel,$app,$rel_filter=''){
	global $post;
	$direction = '';
	if(preg_match('/^entrelcon_to_/',$rel)){
		$conn_type = 'connected';
		$direction = 'from';
	}
	elseif(preg_match('/^entrelcon_from_/',$rel)){
		$conn_type = 'connected';
		$direction = 'to';
	}
	elseif(preg_match('/^entrelcon_/',$rel)){
		$conn_type = 'connected';
	}
	elseif(preg_match('/^entrelrltd_to_/',$rel)){
		$conn_type = 'related';
		$direction = 'from';
	}
	elseif(preg_match('/^entrelrltd_from_/',$rel)){
		$conn_type = 'related';
		$direction = 'to';
	}
	elseif(preg_match('/^entrelrltd_/',$rel)){
		$conn_type = 'related';
	}
	$rel = preg_replace('/^entrelcon_to_/','rel_',$rel);
	$rel = preg_replace('/^entrelrltd_to_/','rel_',$rel);
	$rel = preg_replace('/^entrelcon_from_/','rel_',$rel);
	$rel = preg_replace('/^entrelrltd_from_/','rel_',$rel);
	$rel = preg_replace('/^entrelcon_/','rel_',$rel);
	$rel = preg_replace('/^entrelrltd_/','rel_',$rel);
	if(empty($direction)){
		$rel_list = get_option($app . "_rel_list");
		if(!empty($rel_list[$rel])){
			if($rel_list[$rel]['from'] == $post->post_type){
				$direction = "from";
				$other = $rel_list[$rel]['to'];
			}
			else {
				$direction = "to";
				$other = $rel_list[$rel]['from'];
			}
		}
	}
	$new_rel = preg_replace('/^rel_/','',$rel);
	if(!p2p_type($new_rel)){
		return 0;
	}
	$ent_map_list = get_option($app . '_ent_map_list');
	$myrole = emd_get_curr_usr_role($app);
	if ($myrole != 'administrator' && (!empty($ent_map_list[$post->post_type]['edit_rels'][$myrole][$rel])
			&& $ent_map_list[$post->post_type]['edit_rels'][$myrole][$rel] == 'not_show')) {
		return 0;
	}
	$args = array('posts_per_page' => 1, 'fields' => 'ids');
	if(!empty($rel_filter)){
		$emd_query = new Emd_Query($other, $app);
		$emd_query->args_filter($rel_filter);
		$args = array_merge($args,$emd_query->args);
	}
	if ($conn_type == 'related') {
		$related = p2p_type($new_rel)->get_related($post, $args); 
	} elseif ($conn_type == 'connected') {
		if($direction == 'to'){
			$related = p2p_type($new_rel)->set_direction('to')->get_connected($post, $args);
		}
		else {
			$related = p2p_type($new_rel)->get_connected($post, $args); 
		}
	}
	if (!empty($related->posts)) {
		return 1;
	}
	return 0;
}
function emd_get_rels_csv($rel,$app){
	global $post;
	$ret = Array();
	$ret_csv = '';
	$rel_list = get_option($app . "_rel_list");
	if(!empty($rel_list['rel_' . $rel])){
		if($rel_list['rel_' . $rel]['from'] == $post->post_type){
			$direction = "from";
		}
		else {
			$direction = "to";
		}
		$args = array('posts_per_page' => -1, 'fields' => 'ids');
		if($direction == 'to'){
			$connected = p2p_type($rel)->set_direction('to')->get_connected($post, $args);
		}
		else {
			$connected = p2p_type($rel)->get_connected($post, $args); 
		}
		if (!empty($connected->posts)) {
			foreach($connected->posts as $mrpost){
				$ret[] = get_the_title($mrpost);
			}
		}
	}
	if(!empty($ret)){
		$ret_csv = implode(", ",$ret);
	}
	return $ret_csv;
}
