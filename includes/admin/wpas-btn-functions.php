<?php
/**
 * WPAS Media Button Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_filter('media_buttons', 'emd_shc_button', 11);
add_action('admin_footer', 'emd_shc_insert_button');
/**
 * Return select options with all wpas components
 *
 * @since WPAS 4.0
 *
 * @return array
 */
if (!function_exists('emd_get_comp_select')) {
	function emd_get_comp_select() {
		$emd_activated_plugins = get_option('emd_activated_plugins');
		$std_analytics = ''; //empty is none , std only, analytics
		$comp_select = "<option disabled='disabled' value='' selected='selected'>" . __('Please Select','emd-plugins') . "</option>";
		foreach ($emd_activated_plugins as $active_plugin) {
			$app_name = str_replace("-", "_", $active_plugin);
			$shc_list = get_option($app_name . '_shc_list');
			$shc_list = apply_filters('emd_ext_chart_list',$shc_list,$app_name);
			$calendar_list = get_option($app_name . '_has_calendar');
			$autocomplete_list = Array();
			if (isset($shc_list['app'])) {
				$comp_select.= "<optgroup style='color:red;' label='- " . $shc_list['app'] . "'>";
			}
			if (isset($shc_list['shcs']) && !empty($shc_list['shcs'])) {
				$std_analytics = 'std';
				$comp_select.= "<optgroup label='-- " . __('Standards', 'emd-plugins') . "'>";
				foreach ($shc_list['shcs'] as $keyshc => $myshc) {
					if($myshc['type'] != 'autocomplete'){
						if ($keyshc == 'analytics') {
							$std_analytics = 'analytics';
						}
						$comp_select.= "<option value='" . $keyshc . "' app='" . $app_name . "'";
						$comp_select.= " ent='" . $myshc['class_name'] . "'";
						$comp_select.= ">" . $keyshc . "</option>";
					}
					else {
						$autocomplete_list[$keyshc] = $myshc;
					}
				}
				$comp_select .= "</optgroup>";
			}
			if(!empty($autocomplete_list)){
				$comp_select.= "<optgroup label='-- " . __('AutoCompletes', 'emd-plugins') . "'>";
				foreach ($autocomplete_list as $keycomp => $mycomp) {
					$comp_select.= "<option value='" . $keycomp . "' app='" . $app_name . "'";
					$comp_select.= ">" . $keycomp . "</option>";
				}
				$comp_select .= "</optgroup>";
			}
			if (isset($shc_list['integrations']) && !empty($shc_list['integrations'])) {
				$comp_select.= "<optgroup label='-- " . __('Integrations', 'emd-plugins') . "'>";
				foreach ($shc_list['integrations'] as $keyint => $myint) {
					$comp_select.= "<option value='" . $keyint . "' app='" . $app_name . "'";
					if(!empty($myint['shc_entities'])){
						$comp_select.= " ent='" . $myint['shc_entities'] . "'";
					}
					$comp_select.= ">" . $keyint . "</option>";
				}
				$comp_select .= "</optgroup>";
			}
			if (isset($shc_list['charts']) && !empty($shc_list['charts'])) {
				$comp_select.= "<optgroup label='-- " . __('Charts', 'emd-plugins') . "'>";
				foreach ($shc_list['charts'] as $keych => $mych) {
					$comp_select.= "<option value='" . $keych . "' app='" . $app_name . "'";
					if($mych['chart_type'] == 'org' || $mych['chart_type'] == 'jorg'){
						$comp_select.= " ent='" . $mych['class_name'] . "'";
						$comp_select.= " org-chart='1'";
					}
					else{
						$comp_select.= " ent='" . $mych['class_name'] . "'";
						$comp_select.= " chart='1'";
					}
					$comp_select.= ">" . $keych . "</option>";
				}
				$comp_select .= "</optgroup>";
			}
			if (isset($shc_list['datagrids']) && !empty($shc_list['datagrids'])) {
				$comp_select.= "<optgroup label='-- " . __('Datagrids', 'emd-plugins') . "'>";
				foreach ($shc_list['datagrids'] as $keydg => $mydg) {
					$comp_select.= "<option value='" . $keydg . "' app='" . $app_name . "'";
					$comp_select.= ">" . $keydg . "</option>";
				}
				$comp_select .= "</optgroup>";
			}
			if(!empty($calendar_list)){
				$comp_select.= "<optgroup label='-- " . __('Calendars', 'emd-plugins') . "'>";
				foreach ($calendar_list as $keyc => $myc) {
					$comp_select.= "<option value='" . $keyc . "' calendar=1 app='" . $app_name . "'";
					$comp_select.= " ent='" . $myc['entity'] . "'";
					$comp_select.= ">" . $myc['label'] . "</option>";
				}
				$comp_select .= "</optgroup>";
			}
			if (isset($shc_list['app'])) {
				$comp_select .= "</optgroup>";
			}
		}
		$ret[0] = $comp_select;
		$ret[1] = $std_analytics;
		return $ret;
	}
}
/**
 * Add html&js needed for wpas button to admin footer
 *
 * @since WPAS 4.0
 *
 */
if (!function_exists('emd_shc_insert_button')) {
	function emd_shc_insert_button() {
		global $pagenow, $typenow;
		if ((!empty($_GET['page']) && preg_match('/_shortcodes$/',$_GET['page'])) || (in_array($pagenow, array(
			'page.php',
			'post.php',
			'post-new.php',
			'post-edit.php'
		)) && $typenow == 'page')) {
			list($comp_select, $std_analytics) = emd_get_comp_select();
	?>
				<script type='text/javascript'>
				/*<![CDATA[*/
				jQuery(document).ready(function($) { 
						<?php do_action('emd_' . $std_analytics . '_media_js'); ?>
						$(document).on('change','#wpas-components',function(){
							if($(this).val() == ''){
							$('input[type="submit"]').hide();
							<?php do_action('emd_' . $std_analytics . '_hide_div'); ?>
							return;
							}
							var ent_val = $('#wpas-components option:selected').attr('ent');
							if(ent_val === null || ent_val === undefined) {
							<?php do_action('emd_' . $std_analytics . '_hide_div'); ?>
							}
							<?php do_action('emd_' . $std_analytics . '_call_js'); ?>
							$('input[type="submit"]').show();
							});
						$( '#add-wpas-component' ).submit(function(e){
							e.preventDefault();
							if($('#wpas-components').val() != ''){
							var shc_filters = '';
							var shc_hiddens = '';
							var shc_set = '';
							var shc_hiddens_set = '';
							if($('#wpas-components option:selected').attr('calendar') != undefined){
								var shc = '[emd_calendar app="' + $('#wpas-components option:selected').attr('app') + '"';
								shc += ' cname="' + $('#wpas-components').val() + '"';
							}
							else {	
								var shc = '[' + $('#wpas-components').val();
							}
							var ent = $('#wpas-ent-list option:selected').val();
							<?php do_action('emd_add_comp_' . $std_analytics . '_js'); ?>
							shc +=  ']';
							if($(this).data('from') && $(this).data('from') == 'admin'){
								<?php do_action('emd_create_shc_with_filters'); ?>
							}
							else {
								window.send_to_editor(shc);
							}
							window.tb_remove();
							}
							});
				});
			/* ]]> */
			</script>
				<div id="wpas-component" style="display: none;">
				<form id="add-wpas-component" class="media-upload-form type-form validate" data-from="<?php echo (!empty($_GET['page']) && preg_match('/_shortcodes$/',$_GET['page'])) ? 'admin': 'pages'; ?>" data-app="<?php echo (!empty($_GET['page']) && preg_match('/_shortcodes$/',$_GET['page'])) ? preg_replace('/_shortcodes$/','',$_GET['page']) : ''; ?>">
				<h3 class="media-title"><?php _e('Create Shortcodes', 'emd-plugins'); ?></h3>
				<p><?php _e('Select a component below to insert into any Post or Page.', 'emd-plugins'); ?></p>
				<select id="wpas-components" name="wpas-components">
				<?php echo $comp_select; ?>
				</select>
				<?php do_action('emd_' . $std_analytics . '_div'); ?>
				<input type="submit" class="button-primary" value="<?php _e('Insert Shortcode', 'emd-plugins'); ?>" />
				</form>
				</div>
				<?php
		}
	}
}
/**
 * Add thickbox for wpas button
 *
 * @since WPAS 4.0
 *
 */
if (!function_exists('emd_shc_button')) {
	function emd_shc_button() {
		global $pagenow, $typenow;
		if (in_array($pagenow, array(
			'page.php',
			'post.php',
			'post-new.php',
			'post-edit.php'
		)) && $typenow == 'page') {
			$content = '<a href="#TB_inline?width=640&height=750&inlineId=wpas-component" class="thickbox button" title="' . __('Create shortcodes using Visual Shortcode Builder', 'emd-plugins') . '">
				<img alt="' . __('Create shortcodes using Visual Shortcode Builder', 'emd-plugins') . '" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAABmElEQVQ4ja2VvWoUURiGj/EavIM0kluwtNpuhQGLQNBgYbtk3a3MLEmdDcvMfs+LoIUWCRsQhHgRNmn0ElJst5BUiTop9oycPZyZjegH08x5ec473984F8VkMnkUv2uJB62nRVFsAXNJ++tIZvZG0g1geZ5vtMEqSRUwaoIBL2qd175fgcawQHgQwyTtm9krSd8i/dQ5t8xZAHsn6ToSHgbORsAv4DaGAlcrtwJjoIxd+icDRpJuJO0APxLQ13FeOv5gIWkY3PxJ0mENk3RmZl1J32uomT1vSvYA2JZ0mYIBMzPrAqfe3W9gr7kXltBem7MA9lNSvxVWh/+klLOTv4Z5l6M1sKGk7L/BgI9xSyWjLMvHfpy+3APW2PzOOefyPN+Q9BXY9dDZOljrmE6n06decBFAz+sC+EJVki6BQQw1szzO3YdAcGFmL30u+4Gm5/t04XXXgcv5n9WXZdlD4Di69XNZlpuJog08oPSzXwHzoii2QtEzoJB0BJikJ65leQIdYJyEOeecb5FOclE2Q98mYf8Sqd/FHeQKP9hMDNHMAAAAAElFTkSuQmCC" />VSB</a>';
			echo $content;
		}
	}
}
