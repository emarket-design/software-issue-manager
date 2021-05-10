<?php
add_action('wp_ajax_single_tax_add_taxterm', 'emd_singletax_ajax_add_term');

/**
 * Add new term from metabox
 * @since WPAS 4.0
 *
 *
 * @return json data
 */
function emd_singletax_ajax_add_term() {
	$taxonomy = !empty($_POST['taxonomy']) ? $_POST['taxonomy'] : '';
	$term = !empty($_POST['term']) ? $_POST['term'] : '';
	check_ajax_referer('add-' . $taxonomy, 'wpnonce_single-add-term');
	//term already exists
	if ($tag = term_exists($term, $taxonomy)) {
		echo json_encode(array(
			'hasterm' => $tag['term_id'],
			'term' => $term,
			'taxonomy' => $taxonomy
		));
		exit();
	}
	//ok at this point we can add the new term
	$tag = wp_insert_term($term, $taxonomy);
	//in theory, by now we shouldn't have any errors, but just in case
	if (!$tag || is_wp_error($tag) || (!$tag = get_term($tag['term_id'], $taxonomy))) {
		echo json_encode(array(
			'error' => $tag->get_error_message()
		));
		exit();
	}
	//if all is successful, build the new radio button to send back
	$id = $taxonomy . '-' . $tag->term_id;
	$name = 'radio_tax_input[' . $taxonomy . ']';
	$html = '<li id="' . $id . '"><label class="selectit"><input type="radio" id="in-' . $id . '" name="' . $name . '" value="' . $tag->slug . '" checked="checked"/>&nbsp;' . $tag->name . '</label></li>';
	echo json_encode(array(
		'term' => $tag->term_id,
		'html' => $html
	));
	exit();
}
