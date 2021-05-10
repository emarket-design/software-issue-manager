<?php
/**
 * Emd Single Taxonomy
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd Single Taxonomy class
 * Creates a single select radio taxonomy in admin
 * Taken from radio-buttons-for-taxonomies
 *
 * @since WPAS 4.0
 */
class Emd_Single_Taxonomy {
	public $app;
	public $textdomain;
	public $single_tax_list = Array();
	/**
	 * Instantiate class
	 * Set single taxonomy list and add actions and filters
	 * @since WPAS 4.0
	 *
	 * @param string $textdomain
	 *
	 */
	public function __construct($textdomain = '') {
		$this->app = str_replace("-", "_", $textdomain);
		$this->textdomain = $textdomain;
		$this->get_single_tax_list();
		add_action('add_meta_boxes', array(
			$this,
			'change_metabox'
		));
		add_filter('wp_terms_checklist_args', array(
			$this,
			'filter_terms'
		));
		add_action('save_post', array(
			$this,
			'save_taxonomy_terms'
		));
	}
	/**
	 * Set single taxonomy list from tax list options
	 * @since WPAS 4.0
	 *
	 */
	private function get_single_tax_list() {
		$tax_list = get_option($this->app . '_tax_list', Array());
		$tax_settings = get_option($this->app . '_tax_settings', Array());
		$cust_settings = get_option($this->app . '_custom_attr_tax_list', Array());
		$myrole = emd_get_curr_usr_role($this->app);
		foreach ($tax_list as $ptype => $taxs) {
			foreach ($taxs as $stax => $stax_val) {
				if ($stax_val['type'] == 'single' && empty($stax_val['custom']) && (empty($tax_settings[$stax]['hide']) ||
					(!empty($tax_settings[$stax]['hide']) && $tax_settings[$stax]['hide'] != 'hide'))
				) {
					if($myrole == 'administrator' || ($myrole != 'administrator' && 
							(empty($tax_settings[$stax]['edit'][$myrole]) || (!empty($tax_settings[$stax]['edit'][$myrole]) && $tax_settings[$stax]['edit'][$myrole] == 'edit')))){
						$this->single_tax_list[$stax]['default'] = '';
						if (!empty($stax_val['default']) && is_array($stax_val['default'])) {
							$this->single_tax_list[$stax]['default'] = $stax_val['default'][0];
						}
						$this->single_tax_list[$stax]['label'] = $stax_val['label'];
						$this->single_tax_list[$stax]['ptype'][] = $ptype;
					}
				}
				elseif ($stax_val['type'] == 'single' && !empty($stax_val['custom']) && !empty($cust_settings[$stax]['visibility']) && $cust_settings[$stax]['visibility'] != 'hide'){
					if ($myrole == 'administrator' || ($myrole != 'administrator' && !empty($cust_settings[$stax][$myrole]) && $cust_settings[$stax][$myrole] == 'edit')) {
						$this->single_tax_list[$stax]['default'] = '';
						if (!empty($stax_val['default']) && is_array($stax_val['default'])) {
							$this->single_tax_list[$stax]['default'] = $stax_val['default'][0];
						}
						$this->single_tax_list[$stax]['label'] = $stax_val['label'];
						$this->single_tax_list[$stax]['ptype'][] = $ptype;
					}
				}	
			}
		}
	}
	/**
	 * Tell checklist function to use our new Walker
	 * @since WPAS 4.0
	 *
	 * @param array $args
	 *
	 * @return array $args
	 */
	public function filter_terms($args) {
		if (isset($args['taxonomy']) && in_array($args['taxonomy'], array_keys($this->single_tax_list))) {
			$args['walker'] = new Emd_Walker_Radio();
			$args['checked_ontop'] = false;
		}
		return $args;
	}
	/**
	 * Remove meta box and add new metabox
	 * @since WPAS 4.0
	 *
	 */
	public function change_metabox() {
		foreach ($this->single_tax_list as $stax => $sval) {
			if (!is_taxonomy_hierarchical($stax)) {
				$id = 'tagsdiv-' . $stax;
				$add_id = 'radio-tagsdiv-' . $stax;
			} else {
				$id = $stax . 'div';
				$add_id = 'radio-' . $stax . 'div';
			}
			foreach($sval['ptype'] as $sptype){
				remove_meta_box($id, $sptype, 'side');
				add_meta_box($add_id, $sval['label'], array(
					$this,
					'stax_metabox'
				) , $sptype, 'side', 'core', array(
					'taxonomy' => $stax
				));
			}
		}
	}
	/**
	 * Callback to setup metabox
	 * @since WPAS 4.0
	 *
	 * @param object $post
	 * @param array $args
	 *
	 * @return html
	 */
	public function stax_metabox($post, $box) {
		$defaults = array(
			'taxonomy' => 'category'
		);
		if (!isset($box['args']) || !is_array($box['args'])) $args = array();
		else $args = $box['args'];
		extract(wp_parse_args($args, $defaults) , EXTR_SKIP);
		$tax = get_taxonomy($taxonomy);
		if(!empty($tax)){
			//get current terms
			$checked_terms = $post->ID ? get_the_terms($post->ID, $taxonomy) : array();
			//get first term object
			$current_term = !empty($checked_terms) && !is_wp_error($checked_terms) ? array_pop($checked_terms) : false;
			$current_id = ($current_term) ? $current_term->term_id : '';
			$def_term_id = "";
			if (empty($current_id)) {
				$def_term = get_term_by('name', $this->single_tax_list[$taxonomy]['default'], $taxonomy);
				$def_term_id = ($def_term) ? $def_term->term_id : '';
			}
	?>
			<div id="taxonomy-<?php echo $taxonomy; ?>" class="radio-buttons-for-taxonomies">
			<ul id="<?php echo $taxonomy; ?>-tabs" class="category-tabs">
			<li class="tabs"><a href="#<?php echo $taxonomy; ?>-all" tabindex="3"><?php echo $tax->labels->all_items; ?></a></li>
			<li class="hide-if-no-js"><a href="#<?php echo $taxonomy; ?>-pop" tabindex="3"><?php _e('Most Used', 'emd-plugins'); ?></a></li>
			</ul>
			<style>
			.radio-buttons-for-taxonomies ul.categorychecklist, .radio-buttons-for-taxonomies ul.tagchecklist { margin: 0; }
			.radio-buttons-for-taxonomies ul.categorychecklist li, .radio-buttons-for-taxonomies ul.tagchecklist li { float:none; }
			.radio-buttons-for-taxonomies ul.children { margin-left: 18px; }
			</style>
			<?php wp_nonce_field('single_nonce-' . $taxonomy, '_single_nonce-' . $taxonomy); ?>

			<div id="<?php echo $taxonomy; ?>-pop" class="wp-tab-panel tabs-panel" style="display: none;">
			<ul id="<?php echo $taxonomy; ?>checklist-pop" class="<?php if (is_taxonomy_hierarchical($taxonomy)) {
				echo 'categorychecklist';
			} else {
				echo 'tagchecklist';
			} ?> form-no-clear" >
		<?php $popular = get_terms($taxonomy, array(
				'orderby' => 'count',
				'order' => 'DESC',
				'number' => 10,
				'hierarchical' => false
			));
			if (!current_user_can($tax->cap->assign_terms)) $disabled = 'disabled="disabled"';
			else $disabled = '';
			$popular_ids = array(); ?>

			<?php foreach ($popular as $term) {
				$popular_ids[] = $term->term_id;
				$value = is_taxonomy_hierarchical($taxonomy) ? $term->term_id : $term->slug;
				$id = 'popular-' . $taxonomy . '-' . $term->term_id;
				echo "<li id='$id'><label class='selectit'>";
				echo "<input type='radio' id='in-{$id}'" . checked($current_id, $term->term_id, false) . " value='{$value}' {$disabled} />&nbsp;{$term->name}<br />";
				echo "</label></li>";
			} ?>
		</ul>
			</div>
			<div id="<?php echo $taxonomy; ?>-all" class="wp-tab-panel tabs-panel">
			<?php
			$name = 'radio_tax_input[' . $taxonomy . ']';
			echo "<input type='hidden' name='{$name}[]' value='-1' />"; // Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks. -1 is used instead of 0
			
	?>
			<ul id="<?php echo $taxonomy; ?>checklist" data-wp-lists="list:<?php echo $taxonomy ?>" class="<?php if (is_taxonomy_hierarchical($taxonomy)) {
				echo 'categorychecklist';
			} else {
				echo 'tagchecklist';
			} ?> form-no-clear">
		<?php
			$checklist = array(
				'taxonomy' => $taxonomy,
				'popular_cats' => $popular_ids
			);
			if ($def_term_id) {
				$checklist['selected_cats'] = Array(
					$def_term_id
				);
			}
			wp_terms_checklist($post->ID, $checklist) ?>
			</ul>
			</div>
			<?php if (current_user_can($tax->cap->edit_terms)): ?>
			<div id="<?php echo $taxonomy; ?>-adder" class="wp-hidden-children">
			<h4>
			<a id="<?php echo $taxonomy; ?>-add-toggle" href="#<?php echo $taxonomy; ?>-add" class="hide-if-no-js" tabindex="3">
			<?php
				/* translators: %s: add new taxonomy label */
				printf(__('+ %s','emd-plugins') , $tax->labels->add_new_item);
	?>
			</a>
			</h4>
			<p id="<?php echo $taxonomy; ?>-add" class="category-add wp-hidden-child">
			<label class="screen-reader-text" for="new<?php echo $taxonomy; ?>"><?php echo $tax->labels->add_new_item; ?></label>
			<input type="text" name="new<?php echo $taxonomy; ?>" id="new<?php echo $taxonomy; ?>" class="form-required form-input-tip" value="<?php echo esc_attr($tax->labels->new_item_name); ?>" aria-required="true"/>
			<label class="screen-reader-text" for="new<?php echo $taxonomy; ?>_parent">
			<?php echo $tax->labels->parent_item_colon; ?>
			</label>
			<?php if (is_taxonomy_hierarchical($taxonomy)) {
					wp_dropdown_categories(array(
						'taxonomy' => $taxonomy,
						'hide_empty' => 0,
						'name' => 'new' . $taxonomy . '_parent',
						'orderby' => 'name',
						'hierarchical' => 1,
						'show_option_none' => '&mdash; ' . $tax->labels->parent_item . ' &mdash;',
						'tab_index' => 3
					));
				} ?>
		<input type="button" id="<?php echo $taxonomy; ?>-add-submit" data-wp-lists="add:<?php echo $taxonomy ?>checklist:<?php echo $taxonomy ?>-add" class="button <?php if (is_taxonomy_hierarchical($taxonomy)) {
					echo 'category-add-submit';
				} else {
					echo 'radio-add-submit';
				} ?>" value="<?php echo esc_attr($tax->labels->add_new_item); ?>" tabindex="3" />
		<?php wp_nonce_field('add-' . $taxonomy, '_ajax_nonce-add-' . $taxonomy); ?>
			<span id="<?php echo $taxonomy; ?>-ajax-response"></span>
			</p>
			</div>
			<?php
			endif; ?>
			</div>
			<?php
		}
	}
	/**
	 * Save taxonomy term
	 * @since WPAS 4.0
	 *
	 * @param int $post_id
	 *
	 * @return int $post_id
	 */
	public function save_taxonomy_terms($post_id) {
		if (!empty($this->single_tax_list)) {
			foreach ($this->single_tax_list as $stax => $sval) {
				if (isset($_POST['post_type']) && in_array($_POST['post_type'],$sval['ptype'])) {
					// verify this came from our plugin - one of our nonces must be set
					if (!isset($_POST["_single_nonce-{$stax}"]) && !isset($_POST["_ajax_nonce-add-{$stax}"])) return;
					// verify the nonce if this is an ajax "add term" action
					if (isset($_POST["_ajax_nonce-add-{$stax}"]) && !wp_verify_nonce($_POST["_ajax_nonce-add-{$stax}"], "add-{$stax}")) return;
					// verify the nonce if we're just saving the post normally
					if (isset($_POST["_single_nonce-{$stax}"]) && !wp_verify_nonce($_POST["_single_nonce-{$stax}"], "single_nonce-{$stax}")) return;
					// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
					if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
					// Check permissions
					if ('page' == $_POST['post_type']) {
						if (!current_user_can('edit_page', $post_id)) return;
					} else {
						if (!current_user_can('edit_post', $post_id)) return;
					}
					$terms = null;
					// OK, we're authenticated: we need to find and save the data
					if (isset($_POST["radio_tax_input"]["{$stax}"])) $terms = $_POST["radio_tax_input"]["{$stax}"];
					// should always be an array because WP saves a hidden 0 term
					if (is_array($terms)) {
						$new_terms = Array();
						foreach ($terms as $mterm) {
							if ($mterm != - 1) {
								$new_terms[] = $mterm;
							}
						}
						// magically removes "0" terms
						//$terms = array_filter( $terms );
						// make sure we're only saving 1 term
						$terms = ( array )array_shift($new_terms);
						// if hierarchical we need to ensure integers!
						if (is_taxonomy_hierarchical($stax)) {
							$terms = array_map('intval', $terms);
						}
					} else {
						// if somehow user is saving string of tags, split string and grab first
						$terms = explode(',', $terms);
						/*$terms = array_map(array(
							$this,
							'array_map'
						) , $terms);*/
						$terms = $terms[0];
					}
					// if category and not saving any terms, set to default
					if ('category' == $stax && empty($terms)) {
						$terms = intval(get_option('default_category'));
					}
					// set the single term
					wp_set_object_terms($post_id, $terms, $stax);
				}
			}
		}
		return $post_id;
	}
}
