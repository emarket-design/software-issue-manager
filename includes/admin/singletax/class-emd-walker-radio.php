<?php
/**
 * Emd Walker Radio
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd Walker Radio class
 * Walker to output an unordered list of category radio <input> elements.
 * Taken from radio-buttons-for-taxonomies
 *
 * @since WPAS 4.0
 */
class Emd_Walker_Radio extends Walker {
	var $tree_type = 'category';
	var $db_fields = array(
		'parent' => 'parent',
		'id' => 'term_id'
	);
	/**
	 * Starts the list before the elements are added.
	 * @see Walker:start_lvl()
	 * @since WPAS 4.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of category. Used for tab indentation.
	 * @param array $args An array of arguments. @see wp_terms_checklist()
	 */
	public function start_lvl(&$output, $depth = 0, $args = array()) {
		$indent = str_repeat("\t", $depth);
		$output.= "$indent<ul class='children'>\n";
	}
	/**
	 * Ends the list of after the elements are added.
	 * @see Walker:end_lvl()
	 * @since WPAS 4.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of category. Used for tab indentation.
	 * @param array $args An array of arguments. @see wp_terms_checklist()
	 */
	public function end_lvl(&$output, $depth = 0, $args = array()) {
		$indent = str_repeat("\t", $depth);
		$output.= "$indent</ul>\n";
	}
	/**
	 * Start the element output.
	 * @see Walker:start_el()
	 * @since WPAS 4.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int $depth Depth of the term in reference to parents. Default 0.
	 * @param array $args An array of arguments. @see wp_terms_checklist()
	 * @param int $id ID of the current term.
	 */
	public function start_el(&$output, $term, $depth = 0, $args = array() , $id = 0) {
		extract($args);
		if (empty($taxonomy)) $taxonomy = 'category';
		$name = 'radio_tax_input[' . $taxonomy . ']';
		//get first term object
		$current_term = !empty($selected_cats) && !is_wp_error($selected_cats) ? array_pop($selected_cats) : false;
		// if no term, match the 0 "no term" option
		$current_id = ($current_term) ? $current_term : 0;
		//small tweak so that it works for both hierarchical and non-hierarchical tax
		$value = is_taxonomy_hierarchical($taxonomy) ? $term->term_id : $term->slug;
		$class = in_array($term->term_id, $popular_cats) ? ' class="popular-category"' : '';
		$output.= sprintf("\n" . '<li id="%1$s-%2$s" %3$s><label class="selectit"><input id="%4$s" type="radio" name="%5$s" value="%6$s" %7$s %8$s/> %9$s</label>', $taxonomy, //1
		$value, //2
		$class, //3
		"in-{$taxonomy}-{$term->term_id}", //4
		$name . '[]', //5
		esc_attr(trim($value)) , //6
		checked($current_id, $term->term_id, false) , //7
		disabled(empty($args['disabled']) , false, false) , //8
		esc_html(apply_filters('the_category', $term->name)) //9
		);
	}
	/**
	 * Ends the element output, if neeeded.
	 * @see Walker:end_el()
	 * @since WPAS 4.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $category The current term object.
	 * @param int $depth Depth of the term in reference to parents. Default 0.
	 * @param array $args An array of arguments. @see wp_terms_checklist()
	 */
	public function end_el(&$output, $term, $depth = 0, $args = array()) {
		$output.= "</li>\n";
	}
}
