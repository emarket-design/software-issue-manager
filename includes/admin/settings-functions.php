<?php
/**
 * Settings Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.4
 */
if (!defined('ABSPATH')) exit;

add_action('emd_ext_register','emd_glob_register_settings');
add_action('emd_show_settings_page','emd_show_settings_page',1);
/**
 * Show settings page for global variables
 *
 * @param string $app
 * @since WPAS 4.4
 *
 * @return html page content
 */
if (!function_exists('emd_show_settings_page')) {
	function emd_show_settings_page($app){
		global $title;
		?>
		<div class="wrap">
		<h2><?php echo $title; ?>
		<?php
		$shc_list = get_option($app . '_shc_list');
		if(empty($shc_list['has_lite'])){
		$export_url = admin_url('admin.php?page=' . $app . '_settings&action=export&app=' . $app . '&nonce=' . wp_create_nonce('emd_settings_export'));
		?>
		<input type="file" id="emd_settings_import_file" data-app="<?php echo $app; ?>" hidden>
		<a href="#" class="add-new-h2 emd-import" style="padding:6px 10px;"><?php echo esc_html('Import', 'emd-plugins');?></a>
		<a href="<?php echo $export_url; ?>" class="add-new-h2" style="padding:6px 10px;"><?php echo esc_html('Export', 'emd-plugins');?></a>
		<?php
		}
		else {
		?>
		<a href="#" class="add-new-h2 upgrade-pro" style="padding:6px 10px;"><?php echo esc_html('Import', 'emd-plugins');?></a>
		<a href="#" class="add-new-h2 upgrade-pro" style="padding:6px 10px;"><?php echo esc_html('Export', 'emd-plugins');?></a>
		<?php
		} ?>
		</h2>
		<?php	
		$tabs['entity'] = __('Entities', 'emd_plugins');
		$new_tax_list = Array();
		$tax_list = get_option($app . '_tax_list');
		$shc_list = get_option($app . '_shc_list');
		if(!empty($tax_list)){
			foreach($tax_list as $tax_ent => $tax){
				foreach($tax as $tax_key => $set_tax){
					if($set_tax['type'] != 'builtin'){
						$new_tax_list[$tax_ent][$tax_key] = $set_tax;			
					}
				}
			}
		}
		echo '<p>' . settings_errors($app . '_ent_map_list') . '</p>';
		if(!empty($new_tax_list)){	
			echo '<p>' . settings_errors($app . '_tax_settings') . '</p>';
			$tabs['taxonomy'] = __('Taxonomies', 'emd_plugins');
		}
		$tabs = apply_filters('emd_add_settings_tab',$tabs,$app);
		$tabs['tools'] = __('Tools', 'emd_plugins');
		$active_tab = isset($_GET['tab']) ? (string) $_GET['tab'] : 'entity';
		if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == true){
			echo '<div id="message" class="updated">' . __('Settings Saved.','emd-plugins') . '</div>';
		}
		echo '<style>
			div.tab-content label {
				text-transform: capitalize;
			}
			</style>';
		echo '<h2 class="nav-tab-wrapper">';
		foreach ($tabs as $ktab => $mytab) {
			$turl = remove_query_arg(array('action','_wpnonce'));
			$tab_url[$ktab] = esc_url(add_query_arg(array(
							'tab' => $ktab
							),$turl));
			$active = "";
			if ($active_tab == $ktab) {
				$active = "nav-tab-active";
			}
			echo '<a href="' . esc_url($tab_url[$ktab]) . '" class="nav-tab ' . $active . '" id="nav-' . $ktab . '">' . $mytab . '</a>';
		}
		echo '</h2>';
		emd_ent_map_tab($app,$active_tab,$shc_list);
		emd_tax_tab($app,$active_tab,$new_tax_list,$shc_list);
		do_action('emd_show_settings_tab',$app,$active_tab);
		emd_tools_tab($app,$active_tab,$shc_list);	
		echo '</div>';
	}
}
if (!function_exists('emd_glob_register_settings')) {
	function emd_glob_register_settings($app){
		register_setting($app . '_ent_map_list', $app . '_ent_map_list', 'emd_ent_map_sanitize');
		register_setting($app . '_tax_settings', $app . '_tax_settings', 'emd_tax_settings_sanitize');
		register_setting($app . '_tools', $app . '_tools', 'emd_tools_sanitize');
	}
}
if (!function_exists('emd_ent_map_tab')) {
	function emd_ent_map_tab($app,$active_tab,$shc_list){
		$ent_map_list = get_option($app .'_ent_map_list',Array());
		?>
			<div class='tab-content' id='tab-entity' <?php if ( 'entity' != $active_tab ) { echo 'style="display:none;"'; } ?>>
			<?php	echo '<form method="post" action="options.php">';
		settings_fields($app .'_ent_map_list');
		//show entity rewrite url
		$ent_map_variables = Array();
		$file_attrs = Array();
		$country_attrs = Array();
		$state_attrs = Array();
		$attr_list = get_option($app . '_attr_list');
		$ent_list = get_option($app . '_ent_list');
		foreach($attr_list as $ent => $attr){
			foreach($attr as $kattr => $vattr){
				if($vattr['display_type'] == 'map'){
					$ent_map_variables[$kattr] = Array('ent'=>$ent,'label'=>$vattr['label'], 'ent_label'=>$ent_list[$ent]['label']);
				}
				elseif(in_array($vattr['display_type'], Array('file','image','plupload_image','thickbox_image'))){
					
					$file_attrs[$ent][$kattr] = Array('label' => $vattr['label']);
					if(!empty($vattr['max_file_uploads'])){
						$file_attrs[$ent][$kattr]['max_file_uploads'] = $vattr['max_file_uploads'];
					}
					if(!empty($vattr['max_file_size'])){
						$file_attrs[$ent][$kattr]['max_file_size'] = $vattr['max_file_size'];
					}
					if(!empty($vattr['file_ext'])){
						$file_attrs[$ent][$kattr]['file_ext'] = $vattr['file_ext'];
					}
				}
				elseif(!empty($vattr['select_list']) && $vattr['select_list'] == 'country'){
					$country_attrs[$ent][$kattr] = Array('label' => $vattr['label'], 'state'=> $vattr['dependent_state']);
				}
				elseif(!empty($vattr['select_list']) && $vattr['select_list'] == 'state'){
					$state_attrs[$ent][$kattr] = Array('label' => $vattr['label'],'country'=>$vattr['dependent_country']);
				}
			}
		}
		$map_ents = Array();
		if(!empty($ent_map_variables)){
			foreach($ent_map_variables as $mkey => $mval){
				$map_ents[$mval['ent']]['label'] = $mval['ent_label'];
				$map_ents[$mval['ent']]['attrs'][] = $mkey;
			}
		}
		if(!empty($ent_list)){
			$inline_ent_list = get_option('emd_inline_ent_list', Array());
			foreach($ent_list as $kent => $vent){
				if(empty($vent['rating_ent']) && !in_array($kent,array_keys($inline_ent_list))){
					if(empty($map_ents[$kent])){
						$map_ents[$kent]['label'] = $vent['label'];
					}
					$map_ents[$kent]['rewrite'] = '';
					if(!empty($vent['rewrite'])){
						$map_ents[$kent]['rewrite'] = $vent['rewrite'];
					}
				}
			}
		}
		echo '<input type="hidden" name="' . esc_attr($app) . '_ent_map_list[app]" id="' . esc_attr($app) . '_ent_map_list_app" value="' . $app . '">';
		echo '<div id="map-list" class="accordion-container"><ul class="outer-border">';
		if(!empty($shc_list['frontedit'])){
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$('.emd-attr-visibility').on('change',function(){
				if($(this).find('option:selected').val() == 'hide'){
					$(this).closest('tr').find('.emd-attr-access').prop('disabled',true);
				}
				else if($(this).find('option:selected').val() == 'hide_frontend'){
					$(this).closest('tr').find('.emd-attr-access').prop('disabled',false);
					$(this).closest('tr').find('.emd-attr-visitor').prop('disabled',true);	
				} else {
					$(this).closest('tr').find('.emd-attr-access').prop('disabled',false);
				}
			});
		});
		</script>
		<?php
		}
		foreach($map_ents as $kent => $myent){
			$is_ent_public = get_post_type_object($kent)->public;
			echo '<li id="' . esc_attr($kent) . '" class="control-section accordion-section ';
			echo (count($map_ents) == 1) ? 'open' : '';
			echo '">';
			echo '<h3 class="accordion-section-title hndle" tabindex="0">' . $myent['label'] . '</h3>';
			echo '<div class="accordion-section-content"><div class="inside">';
			echo '<table class="form-table"><tbody>';
			if($is_ent_public !== false){
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_rewrite'>";
				echo __('Base slug','emd-plugins');
				echo '</label></th><td>';
				$rewrite = isset($ent_map_list[$kent]['rewrite']) ? $ent_map_list[$kent]['rewrite'] : $myent['rewrite'];
				echo "<input id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_rewrite' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][rewrite]' type='text' value='" . $rewrite ."'></input><p class='description'>" . sprintf(__('Sets the custom base slug for single and archive %s. After you update,  flush the rewrite rules by going to the Permalink Settings page. This works only if post name based permalink structure is selected.','emd-plugins'),strtolower($myent['label'])) . "</p></td></tr>";
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_single_temp'>";
				echo __('Single template','emd-plugins');
				echo '</label></th><td>';
				$single_temp = isset($ent_map_list[$kent]['single_temp']) ? $ent_map_list[$kent]['single_temp'] : 'right';
				echo "<select id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_single_temp' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][single_temp]'>";
				$temp_options = Array('right' => __('Right Sidebar','emd-plugins'),'left' => __('Left Sidebar','emd-plugins'), 'full' => __('Full Width','emd-plugins'));
				foreach($temp_options as $ktemp => $vtemp){
					echo "<option value='" . $ktemp . "'";
					if($single_temp == $ktemp){
						echo " selected";
					}
					echo ">" . $vtemp . "</option>";
				}
				echo "</select><p class='description'>" . sprintf(__('Sets the template for single %s.','emd-plugins'),strtolower($myent['label'])) . "</p></td></tr>";
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_single_container'>";
				echo __('Container type','emd-plugins');
				echo '</label></th><td>';
				$single_container = isset($ent_map_list[$kent]['single_container']) ? $ent_map_list[$kent]['single_container'] : 'container';
				echo "<select id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_single_container' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][single_container]'>";
				$container_options = Array('container' => __('Fixed','emd-plugins'),'container-fluid' => __('Full','emd-plugins'));
				foreach($container_options as $kcont => $vcont){
					echo "<option value='" . $kcont . "'";
					if($single_container == $kcont){
						echo " selected";
					}
					echo ">" . $vcont . "</option>";
				}
				echo "</select><p class='description'>" . sprintf(__('Change this if the sidebars are getting out of your page boundry. Fixed type provides a responsive fixed width container for single %s. Full type provides a full width container, spanning the entire width of the viewport for single %s.','emd-plugins'),strtolower($myent['label']),strtolower($myent['label'])) . "</p></td></tr>";
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_hide_prev_next'>";
				echo __('Hide previous next links','emd-plugins');
				echo '</label></th><td>';
				echo "<input type='checkbox' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_hide_prev_next' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][hide_prev_next]' value=1";
				if(isset($ent_map_list[$kent]['hide_prev_next'])){
					echo " checked";
				}
				echo ">";
				echo "<p class='description'>" . sprintf(__('Hides the previous and next %s links on the frontend for single %s.','emd-plugins'),strtolower($myent['label']),strtolower($myent['label'])) . "</p></td></tr>";
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_hide_edit_link'>";
				echo __('Hide edit links','emd-plugins');
				echo '</label></th><td>';
				echo "<input type='checkbox' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_hide_edit_link' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][hide_edit_link]' value=1";
				if(isset($ent_map_list[$kent]['hide_edit_link'])){
					echo " checked";
				}
				echo ">";
				echo "<p class='description'>" . sprintf(__('Hides edit %s link on the frontend for single %s.','emd-plugins'),strtolower($myent['label']),strtolower($myent['label'])) . "</p></td></tr>";
			}
			if($ent_list[$kent]['archive_view']){	
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_archive_temp'>";
				echo __('Archive template','emd-plugins');
				echo '</label></th><td>';
				$archive_temp = isset($ent_map_list[$kent]['archive_temp']) ? $ent_map_list[$kent]['archive_temp'] : 'right';
				echo "<select id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_archive_temp' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][archive_temp]'>";
				foreach($temp_options as $ktemp => $vtemp){
					echo "<option value='" . $ktemp . "'";
					if($archive_temp == $ktemp){
						echo " selected";
					}
					echo ">" . $vtemp . "</option>";
				}
				echo "</select><p class='description'>" . sprintf(__('Sets the template for archive %s.','emd-plugins'),strtolower($myent['label'])) . "</p></td></tr>";
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_archive_container'>";
				echo __('Archive container type','emd-plugins');
				echo '</label></th><td>';
				$archive_container = isset($ent_map_list[$kent]['archive_container']) ? $ent_map_list[$kent]['archive_container'] : 'container';
				echo "<select id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_archive_container' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][archive_container]'>";
				$container_options = Array('container' => __('Fixed','emd-plugins'),'container-fluid' => __('Full','emd-plugins'));
				foreach($container_options as $kcont => $vcont){
					echo "<option value='" . $kcont . "'";
					if($archive_container == $kcont){
						echo " selected";
					}
					echo ">" . $vcont . "</option>";
				}
				echo "</select><p class='description'>" . sprintf(__('Change this if the sidebars are getting out of your page boundry. Fixed type provides a responsive fixed width container for archive %s. Full type provides a full width container, spanning the entire width of the viewport for archive %s.','emd-plugins'),strtolower($myent['label']),strtolower($myent['label'])) . "</p></td></tr>";
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_hide_archive_page_nav'>";
				echo __('Hide page navigation','emd-plugins');
				echo '</label></th><td>';
				echo "<input type='checkbox' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_hide_archive_page_nav' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][hide_archive_page_nav]' value=1";
				if(isset($ent_map_list[$kent]['hide_archive_page_nav'])){
					echo " checked";
				}
				echo ">";
				echo "<p class='description'>" . sprintf(__('Hides the page navigation links on the frontend for archive %s.','emd-plugins'),strtolower($myent['label'])) . "</p></td></tr>";
			}
			if($shc_list['remove_vis'] != 1){
				//Show all attributes and ability to disable them
				$fields_table = '';
				$fields_options_pub = Array('show' => __('Enable','emd-plugins'),
						'hide' => __('Disable','emd-plugins'),
						'hide_frontend' => __('Show only in Admin','emd-plugins')
				);
				$fields_options_not_pub = Array('show' => __('Enable','emd-plugins'),
						'hide' => __('Disable','emd-plugins'),
				);
				$fields_options_no_disable = Array('show' => __('Enable','emd-plugins'),
						'hide_frontend' => __('Show only in Admin','emd-plugins')
				);
				if($is_ent_public !== false){
					$fields_options = $fields_options_pub;
				}
				else {
					$fields_options = $fields_options_not_pub;
				}
				if(!empty($shc_list['frontedit'])){
					//get roles
					$app_custom_roles = get_option($app . '_cust_roles');
					$role_caps = get_option($app . '_add_caps');
					$visitor_access = Array('show' => __('Show','emd-plugins'),'not_show'=>__('Do not show','emd-plugins'));
					$role_access = Array('edit'=>__('Allow Edit','emd-plugins'),'show' => __('Show','emd-plugins'),'not_show'=>__('Do not show','emd-plugins'));
				}
				if(!empty($ent_list[$kent]['blt_list'])){
					foreach($ent_list[$kent]['blt_list'] as $blt_attr => $blt_label){
						$fields_table .= "<tr><td style='font-weight:500;'>" . $blt_label . "</td>";
						$fields_table .= "<td style='text-align:center;'><select class='emd-attr-visibility' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_attrs' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][attrs][" . $blt_attr . "]'>";
						foreach($fields_options as $fkey => $fval){
							$fields_table .= "<option value='" . $fkey . "'";
							if(!empty($ent_map_list[$kent]['attrs'][$blt_attr]) && $ent_map_list[$kent]['attrs'][$blt_attr] == $fkey){
								$fields_table .= " selected";
							}
							$fields_table .= ">" . $fval . "</option>";
						}
						$fields_table .= "</select></td>";
						if(!empty($shc_list['frontedit'])){
							$fields_table .= "<td style='text-align:center;'><select class='emd-attr-access emd-attr-visitor' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_attrs_visitor_" . $blt_attr . "'' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_attrs][visitor][" . $blt_attr . "]'";
							if(!empty($ent_map_list[$kent]['attrs'][$blt_attr]) && in_array($ent_map_list[$kent]['attrs'][$blt_attr],Array('hide','hide_frontend'))){
								$fields_table .= " disabled";
							}
							$fields_table .= ">";
							foreach($visitor_access as $rkey => $rval){
								$fields_table .= "<option value='" . $rkey . "'";
								if(!empty($ent_map_list[$kent]['edit_attrs']['visitor'][$blt_attr]) && $ent_map_list[$kent]['edit_attrs']['visitor'][$blt_attr] == $rkey){
									$fields_table .= " selected";
								}
								$fields_table .= ">" . $rval . "</option>";
							}
							$fields_table .= "</select></td>";
							foreach($app_custom_roles as $krole => $vrole){
								//check if this role can edit this entity
								if(!empty($role_caps['edit_' . $kent . 's']) && in_array($krole,$role_caps['edit_' . $kent . 's'])){
									$access_arr = $role_access;
								}
								else {
									$access_arr = $visitor_access;
								}
								$fields_table .= "<td style='text-align:center;'><select class='emd-attr-access' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_attrs_" . $krole . "_" . $blt_attr . "'' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_attrs][" . $krole . "][" . $blt_attr . "]'";
								if(!empty($ent_map_list[$kent]['attrs'][$blt_attr]) && $ent_map_list[$kent]['attrs'][$blt_attr] == 'hide'){
									$fields_table .= " disabled";
								}
								$fields_table .= ">";
								foreach($access_arr as $rkey => $rval){
									$fields_table .= "<option value='" . $rkey . "'";
									if(!empty($ent_map_list[$kent]['edit_attrs'][$krole][$blt_attr]) && $ent_map_list[$kent]['edit_attrs'][$krole][$blt_attr] == $rkey){
										$fields_table .= " selected";
									}
									$fields_table .= ">" . $rval . "</option>";
								}
								$fields_table .= "</select></td>";
							}
						}
						$fields_table .= "</tr>";
					}
				}
				if(!empty($ent_list[$kent]['req_blt'])){
					foreach($ent_list[$kent]['req_blt'] as $blt_attr => $blt_label){
						$fields_table .= "<tr><td style='font-weight:500;'>" . $blt_label['msg'] . "</td>";
						$fields_table .= "<td style='text-align:center;'><select class='emd-attr-visibility' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_attrs' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][attrs][" . $blt_attr . "]'>";
						foreach($fields_options_no_disable as $fkey => $fval){
							$fields_table .= "<option value='" . $fkey . "'";
							if(!empty($ent_map_list[$kent]['attrs'][$blt_attr]) && $ent_map_list[$kent]['attrs'][$blt_attr] == $fkey){
								$fields_table .= " selected";
							}
							$fields_table .= ">" . $fval . "</option>";
						}
						$fields_table .= "</select></td>";
						if(!empty($shc_list['frontedit'])){
							$fields_table .= "<td style='text-align:center;'><select class='emd-attr-access emd-attr-visitor' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_attrs_visitor_" . $blt_attr . "'' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_attrs][visitor][" . $blt_attr . "]'";
							if(!empty($ent_map_list[$kent]['attrs'][$blt_attr]) && in_array($ent_map_list[$kent]['attrs'][$blt_attr],Array('hide','hide_frontend'))){
								$fields_table .= " disabled";
							}
							$fields_table .= ">";
							foreach($visitor_access as $rkey => $rval){
								$fields_table .= "<option value='" . $rkey . "'";
								if(!empty($ent_map_list[$kent]['edit_attrs']['visitor'][$blt_attr]) && $ent_map_list[$kent]['edit_attrs']['visitor'][$blt_attr] == $rkey){
									$fields_table .= " selected";
								}
								$fields_table .= ">" . $rval . "</option>";
							}
							$fields_table .= "</select></td>";
							foreach($app_custom_roles as $krole => $vrole){
								$fields_table .= "<td style='text-align:center;'><select class='emd-attr-access' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_attrs_" . $krole . "_" . $blt_attr . "'' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_attrs][" . $krole . "][" . $blt_attr . "]'";
								if(!empty($ent_map_list[$kent]['attrs'][$blt_attr]) && $ent_map_list[$kent]['attrs'][$blt_attr] == 'hide'){
									$fields_table .= " disabled";
								}
								$fields_table .= ">";
								//check if this role can edit this entity
								if(!empty($role_caps['edit_' . $kent . 's']) && in_array($krole,$role_caps['edit_' . $kent . 's'])){
									$access_arr = $role_access;
								}
								else {
									$access_arr = $visitor_access;
								}
								foreach($access_arr as $rkey => $rval){
									$fields_table .= "<option value='" . $rkey . "'";
									if(!empty($ent_map_list[$kent]['edit_attrs'][$krole][$blt_attr]) && $ent_map_list[$kent]['edit_attrs'][$krole][$blt_attr] == $rkey){
										$fields_table .= " selected";
									}
									$fields_table .= ">" . $rval . "</option>";
								}
								$fields_table .= "</select></td>";
							}
						}
						$fields_table .= "</tr>";
					}
				}
				if(!empty($ent_list[$kent]['featured_img'])){
					$fields_table .= "<tr><td style='style='font-weight:500;'>" . __('Featured Image','emd-plugins') . "</td>";
					$fields_table .= "<td style='text-align:center;'><select class='emd-attr-visibility' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_attrs' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][attrs][featured_img]'>";
					foreach($fields_options as $fkey => $fval){
						$fields_table .= "<option value='" . $fkey . "'";
						if(!empty($ent_map_list[$kent]['attrs']['featured_img']) && $ent_map_list[$kent]['attrs']['featured_img'] == $fkey){
							$fields_table .= " selected";
						}
						$fields_table .= ">" . $fval . "</option>";
					}
					$fields_table .= "</select></td>";
					if(!empty($shc_list['frontedit'])){
						$fields_table .= "<td style='text-align:center;'><select class='emd-attr-access emd-attr-visitor' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_attrs_visitor_featured_img' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_attrs][visitor][featured_img]'";
						if(!empty($ent_map_list[$kent]['attrs']['featured_img']) && in_array($ent_map_list[$kent]['attrs']['featured_img'],Array('hide','hide_frontend'))){
							$fields_table .= " disabled";
						}
						$fields_table .= ">";
						foreach($visitor_access as $rkey => $rval){
							$fields_table .= "<option value='" . $rkey . "'";
							if(!empty($ent_map_list[$kent]['edit_attrs']['visitor']['featured_img']) && $ent_map_list[$kent]['edit_attrs']['visitor']['featured_img'] == $rkey){
								$fields_table .= " selected";
							}
							$fields_table .= ">" . $rval . "</option>";
						}
						$fields_table .= "</select></td>";
						foreach($app_custom_roles as $krole => $vrole){
							if(!empty($role_caps['edit_' . $kent . 's']) && in_array($krole,$role_caps['edit_' . $kent . 's'])){
								$access_arr = $role_access;
							}
							else {
								$access_arr = $visitor_access;
							}
							$fields_table .= "<td style='text-align:center;'><select class='emd-attr-access' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_attrs_" . $krole . "_featured_img' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_attrs][" . $krole . "][featured_img]'";
							if(!empty($ent_map_list[$kent]['attrs']['featured_img']) && $ent_map_list[$kent]['attrs']['featured_img'] == 'hide'){
								$fields_table .= " disabled";
							}
							$fields_table .= ">";
							foreach($access_arr as $rkey => $rval){
								$fields_table .= "<option value='" . $rkey . "'";
								if(!empty($ent_map_list[$kent]['edit_attrs'][$krole]['featured_img']) && $ent_map_list[$kent]['edit_attrs'][$krole]['featured_img'] == $rkey){
									$fields_table .= " selected";
								}
								$fields_table .= ">" . $rval . "</option>";
							}
							$fields_table .= "</select></td>";
						}
					}
					$fields_table .= "</tr>";
				}
				if(!empty($attr_list[$kent])){
					foreach($attr_list[$kent] as $kattr => $vattr){
						if(!preg_match('/^wpas_/',$kattr)){
							$fields_table .= "<tr><td style='font-weight:500;'>" . $vattr['label'] . "</td>";
							$fields_table .= "<td style='text-align:center;'><select class='emd-attr-visibility' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_attrs' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][attrs][" . $kattr . "]'>";
							if($is_ent_public === false){
								$fields_options = $fields_options_not_pub;
							}
							else {
								if(empty($vattr['uniqueAttr']) && empty($vattr['required'])){
									$fields_options = $fields_options_pub;
								}
								else {	
									$fields_options = $fields_options_no_disable;
								}
							}
							foreach($fields_options as $fkey => $fval){
								$fields_table .= "<option value='" . $fkey . "'";
								if(!empty($ent_map_list[$kent]['attrs'][$kattr]) && $ent_map_list[$kent]['attrs'][$kattr] == $fkey){
									$fields_table .= " selected";
								}
								$fields_table .= ">" . $fval . "</option>";
							}
							$fields_table .= "</select></td>";
							if(!empty($shc_list['frontedit'])){
								$fields_table .= "<td style='text-align:center;'><select class='emd-attr-access emd-attr-visitor' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_attrs_visitor_" . $kattr . "'' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_attrs][visitor][" . $kattr . "]'";
								if(!empty($ent_map_list[$kent]['attrs'][$kattr]) && in_array($ent_map_list[$kent]['attrs'][$kattr],Array('hide','hide_frontend'))){
									$fields_table .= " disabled";
								}
								$fields_table .= ">";
								foreach($visitor_access as $rkey => $rval){
									$fields_table .= "<option value='" . $rkey . "'";
									if(!empty($ent_map_list[$kent]['edit_attrs']['visitor'][$kattr]) && $ent_map_list[$kent]['edit_attrs']['visitor'][$kattr] == $rkey){
										$fields_table .= " selected";
									}
									$fields_table .= ">" . $rval . "</option>";
								}
								foreach($app_custom_roles as $krole => $vrole){
									//check if this role can edit this entity
									if(!empty($role_caps['edit_' . $kent . 's']) && in_array($krole,$role_caps['edit_' . $kent . 's'])){
										$access_arr = $role_access;
									}
									else {
										$access_arr = $visitor_access;
									}
									if($vattr['display_type'] == 'map'){
										$fields_table .= "<td style='text-align:center;'><select class='emd-attr-access' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_attrs_" . $krole . "_" . $kattr . "' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_attrs][" . $krole . "][" . $kattr . "]'";
										if(!empty($ent_map_list[$kent]['attrs'][$kattr]) && $ent_map_list[$kent]['attrs'][$kattr] == 'hide'){
											$fields_table .= " disabled";
										}
										$fields_table .= ">";
										foreach($visitor_access as $rkey => $rval){
											$fields_table .= "<option value='" . $rkey . "'";
											if(!empty($ent_map_list[$kent]['edit_attrs'][$krole][$kattr]) && $ent_map_list[$kent]['edit_attrs'][$krole][$kattr] == $rkey){
												$fields_table .= " selected";
											}
											$fields_table .= ">" . $rval . "</option>";
										}
										$fields_table .= "</select></td>";
									}
									else {
										$fields_table .= "<td style='text-align:center;'><select class='emd-attr-access' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_attrs_" . $krole . "_" . $kattr . "' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_attrs][" . $krole . "][" . $kattr . "]'";
										if(!empty($ent_map_list[$kent]['attrs'][$kattr]) && $ent_map_list[$kent]['attrs'][$kattr] == 'hide'){
											$fields_table .= " disabled";
										}
										$fields_table .= ">";
										foreach($access_arr as $rkey => $rval){
											$fields_table .= "<option value='" . $rkey . "'";
											if(!empty($ent_map_list[$kent]['edit_attrs'][$krole][$kattr]) && $ent_map_list[$kent]['edit_attrs'][$krole][$kattr] == $rkey){
												$fields_table .= " selected";
											}
											$fields_table .= ">" . $rval . "</option>";
										}
										$fields_table .= "</select></td>";
									}
								}
							}
							$fields_table .= "</tr>";
						}
					}
				}
				//hide relationships
				$rel_list = get_option($app . '_rel_list');
				$rels = Array();
				$rel_attrs = Array();
				if(!empty($rel_list)){
					foreach($rel_list as $rkey => $rval){
						if($rval['show'] == 'from' && $rval['from'] == $kent){
							$rels[$rkey] = Array('key' => $rkey, 'title' => $rval['from_title']);
							if(!empty($rval['attrs'])){
								foreach($rval['attrs'] as $rakey => $raval){
									$rel_attrs[$rakey] = $raval;
								}
							}
						}
						else if($rval['show'] == 'to' && $rval['to'] == $kent){
							$rels[$rkey] = Array('key' => $rkey, 'title' => $rval['to_title']);
							if(!empty($rval['attrs'])){
								foreach($rval['attrs'] as $rakey => $raval){
									$rel_attrs[$rakey] = $raval;
								}
							}
						}	
						else if($rval['from'] == $kent){
							$rels[$rkey] = Array('key' => $rkey, 'title' => $rval['from_title']);
							if(!empty($rval['attrs'])){
								foreach($rval['attrs'] as $rakey => $raval){
									$rel_attrs[$rakey] = $raval;
								}
							}
						}
					}
				}
				$rel_trs = "";
				if(!empty($rels)){
					foreach($rels as $myrel){
						$rel_trs .= "<tr><td style='font-weight:500;'>" . $myrel['title'] . "</td>";
						$rel_trs .= "<td style='text-align:center;'><select class='emd-attr-visibility' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_hide_rels' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][hide_rels][" . $myrel['key'] . "]'>";
						$rel_options = Array('show' => __('Enable','emd-plugins'),
									'hide' => __('Disable','emd-plugins'),
									'hide_frontend' => __('Show only in Admin','emd-plugins')
								);
						foreach($rel_options as $fkey => $fval){
							$rel_trs .= "<option value='" . $fkey . "'";
							if(!empty($ent_map_list[$kent]['hide_rels'][$myrel['key']]) && $ent_map_list[$kent]['hide_rels'][$myrel['key']] == $fkey){
								$rel_trs .= " selected";
							}
							$rel_trs .= ">" . $fval . "</option>";
						}
						$rel_trs .= "</select></td>";
						if(!empty($shc_list['frontedit'])){
							$rel_trs .= "<td style='text-align:center;'><select class='emd-attr-access emd-attr-visitor' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_rels_visitor_" . $myrel['key'] . "' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_rels][visitor][" . $myrel['key'] . "]'";
							if(!empty($ent_map_list[$kent]['hide_rels'][$myrel['key']]) && in_array($ent_map_list[$kent]['hide_rels'][$myrel['key']],Array('hide','hide_frontend'))){
								$rel_trs .= " disabled";
							}
							$rel_trs .= ">";
							foreach($visitor_access as $rkey => $rval){
								$rel_trs .= "<option value='" . $rkey . "'";
								if(!empty($ent_map_list[$kent]['edit_rels']['visitor'][$myrel['key']]) && $ent_map_list[$kent]['edit_rels']['visitor'][$myrel['key']] == $rkey){
									$rel_trs .= " selected";
								}
								$rel_trs .= ">" . $rval . "</option>";
							}
							$rel_trs .= "</select></td>";
							foreach($app_custom_roles as $krole => $vrole){
								$rel_trs .= "<td style='text-align:center;'><select class='emd-attr-access' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_rels_" . $krole . "_" . $myrel['key'] . "' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_rels][" . $krole . "][" . $myrel['key'] . "]'";
								if(!empty($ent_map_list[$kent]['hide_rels'][$myrel['key']]) && $ent_map_list[$kent]['hide_rels'][$myrel['key']] == 'hide'){
									$rel_trs .= " disabled";
								}
								$rel_trs .= ">";
								//check if this role can edit this entity
								if(!empty($role_caps['edit_' . $kent . 's']) && in_array($krole,$role_caps['edit_' . $kent . 's'])){
									$access_arr = $role_access;
								}
								else {
									$access_arr = $visitor_access;
								}
								foreach($access_arr as $rkey => $rval){
									$rel_trs .= "<option value='" . $rkey . "'";
									if(!empty($ent_map_list[$kent]['edit_rels'][$krole][$myrel['key']]) && $ent_map_list[$kent]['edit_rels'][$krole][$myrel['key']] == $rkey){
										$rel_trs .= " selected";
									}
									$rel_trs .= ">" . $rval . "</option>";
								}
								$rel_trs .= "</select></td>";
							}
						}
						$rel_trs .= "</tr>";
						
					}
					if(!empty($rel_attrs)){
						foreach($rel_attrs as $krattr => $myrelattr){
							$rel_trs .= "<tr><td style='font-weight:500;'>" . $rel_list[$myrel]['from_title'] . ' - ' . $myrelattr['label'] . "</td>";
							$rel_trs .= "<td style='text-align:center;'><select class='emd-attr-visibility' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_hide_rel_attrs' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][hide_rel_attrs][" . $krattr . "]'>";
							$rel_options = Array('show' => __('Enable','emd-plugins'),
										'hide' => __('Disable','emd-plugins'),
										'hide_frontend' => __('Show only in Admin','emd-plugins')
									);
							foreach($rel_options as $fkey => $fval){
								$rel_trs .= "<option value='" . $fkey . "'";
								if(!empty($ent_map_list[$kent]['hide_rel_attrs'][$krattr]) && $ent_map_list[$kent]['hide_rel_attrs'][$krattr] == $fkey){
									$rel_trs .= " selected";
								}
								$rel_trs .= ">" . $fval . "</option>";
							}
							$rel_trs .= "</select></td>";
							if(!empty($shc_list['frontedit'])){
								$rel_trs .= "<td style='text-align:center;'><select class='emd-attr-access emd-attr-visitor' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_rels_visitor_" . $krattr . "'' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_rels][visitor][" . $krattr . "]'";
								if(!empty($ent_map_list[$kent]['hide_rels'][$krattr]) && in_array($ent_map_list[$kent]['hide_rels'][$krattr],Array('hide','hide_frontend'))){
									$rel_trs .= " disabled";
								}
								$rel_trs .= ">";
								foreach($visitor_access as $rkey => $rval){
									$rel_trs .= "<option value='" . $rkey . "'";
									if(!empty($ent_map_list[$kent]['edit_rels']['visitor'][$krattr]) && $ent_map_list[$kent]['edit_rels']['visitor'][$krattr] == $rkey){
										$rel_trs .= " selected";
									}
									$rel_trs .= ">" . $rval . "</option>";
								}
								$rel_trs .= "</select></td>";
								foreach($app_custom_roles as $krole => $vrole){
									//check if this role can edit this entity
									if(!empty($role_caps['edit_' . $kent . 's']) && in_array($krole,$role_caps['edit_' . $kent . 's'])){
										$access_arr = $role_access;
									}
									else {
										$access_arr = $visitor_access;
									}
									$rel_trs .= "<td style='text-align:center;'><select class='emd-attr-access' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_edit_rels_" . $krole . "_" . $krattr . "'' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][edit_rels][" . $krole . "][" . $krattr . "]'";
									if(!empty($ent_map_list[$kent]['hide_rels'][$krattr]) && $ent_map_list[$kent]['hide_rels'][$krattr] == 'hide'){
										$rel_trs .= " disabled";
									}
									$rel_trs .= ">";
									foreach($access_arr as $rkey => $rval){
										$rel_trs .= "<option value='" . $rkey . "'";
										if(!empty($ent_map_list[$kent]['edit_rels'][$krole][$krattr]) && $ent_map_list[$kent]['edit_rels'][$krole][$krattr] == $rkey){
											$rel_trs .= " selected";
										}
										$rel_trs .= ">" . $rval . "</option>";
									}
									$rel_trs .= "</select></td>";
								}
							}
							$rel_trs .= "</tr>";
						}
					}
				}
				if(!empty($fields_table)){
					echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_attrs'>";
					echo __('Attributes','emd-plugins');
					echo '</label></th><td>';
					echo "<p class='description'>" . __('Enable: Display this attribute on everywhere</br>Disable: Remove this attribute from everywhere</br>Show only in Admin: This attribute is still enabled on admin area. If you want to hide this attribute on the frontend forms go to Forms tab.','emd-plugins') . "</p></br>";
					echo "<table class='widefat striped'";
					if(empty($shc_list['frontedit'])){
						echo " style='width:320px;'";
					}
					elseif(!empty($app_custom_roles) && count($app_custom_roles) < 4){
						echo " style='width:640px;'";
					}	
					echo ">";
					echo "<tr><th style='text-align:center;'>" . __('Attribute','emd-plugins') . "</th><th style='text-align:center;'>" . __('Visibility','emd-plugins'). "</th>";
					if(!empty($shc_list['frontedit'])){
						echo "<th style='text-align:center;'>" . __('Visitor','emd-plugins') . "</th>";
						foreach($app_custom_roles as $crole){
							echo "<th style='text-align:center;'>" . $crole . "</th>";
						}
					}
					echo "</tr>";
					echo $fields_table;
					echo $rel_trs;
					echo "</table>";
					echo "</td></tr>";
				}
			}
			$is_ent_limitby = false;
			$has_limitby_auth_type = get_option($app ."_limitby_auth_caps");
			if (!empty($has_limitby_auth_type[$kent])){
				foreach ($has_limitby_auth_type[$kent] as $limit) {
					if (preg_match('/limitby_author_frontend/', $limit)) {
						$is_ent_limitby = true;
					}
				}
				if($is_ent_limitby === true && $is_ent_public !== false){
					echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_make_visitor_visible'>";
					echo __('Remove access restrictions on frontend','emd-plugins');
					echo '</label></th><td>';
					echo "<input type='checkbox' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_make_visitor_visible' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][make_visitor_visible]' value=1";
					if(isset($ent_map_list[$kent]['make_visitor_visible'])){
						echo " checked";
					}
					echo ">";
					echo "<p class='description'>" . sprintf(__('Shows all %s to logged in and non-logged in users regardless of content access permissions assigned to their user role.','emd-plugins'),strtolower($myent['label'])) . "</p></td></tr>";
				}	
			}
			if(!empty($file_attrs[$kent])){
				foreach($file_attrs[$kent] as $fattr => $fval){
					echo "<tr style='border-top:2px solid #e0e0e0;border-bottom:2px solid #e0e0e0;'><th scope='row' style='padding:5px 5px;' colspan=2><h3 style='display:inline;color:#5f9ea0;'>" . $fval['label'];
					echo '</h3>';
					echo "<span class='description' style='padding-left:10px;color:#777;'>- " . __('Use the options below to customize file uploads.','emd-plugins') . "</span></th></tr>";
					if(empty($fval['max_file_uploads']) || (!empty($fval['max_file_uploads']) &&  $fval['max_file_uploads'] != 1)){
						echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_max_files_" . $fattr . "'>";
						echo __('Maximum files','emd-plugins');
						echo '</label></th><td>';
						$max_files = "";
						if(!empty($ent_map_list[$kent]['max_files'][$fattr])){
							$max_files = $ent_map_list[$kent]['max_files'][$fattr];
						}
						elseif(!empty($fval['max_file_uploads'])){
							$max_files = $fval['max_file_uploads'];
						}
						echo '<input type="number" class="small-text" id="' . esc_attr($app) . '_ent_map_list_' . $kent . '_max_files_' . $fattr . '" name="' . esc_attr($app) . '_ent_map_list[' . $kent . '][max_files][' . $fattr . ']" value="' . $max_files . '">';
						echo "<p class='description'>" . __('Sets the maximum number of allowable file uploads.','emd-plugins') . "</p></td></tr>";
					}
					echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_max_file_size_" . $fattr . "'>";
					echo __('Maximum file size','emd-plugins');
					echo '</label></th><td>';
					$max_file_size = "";
					if(!empty($ent_map_list[$kent]['max_file_size'][$fattr])){
						$max_file_size = $ent_map_list[$kent]['max_file_size'][$fattr];
					}
					elseif(!empty($fval['max_file_size'])){
						$max_file_size = $fval['max_file_size'];
					}
					echo '<input type="number" class="small-text" id="' . esc_attr($app) . '_ent_map_list_' . $kent . '_max_file_size_' . $fattr .'" name="' . esc_attr($app) . '_ent_map_list[' . $kent . '][max_file_size][' . $fattr . ']" value="' . $max_file_size . '"> KB';
					$server_size = ini_get('upload_max_filesize');
					if(preg_match('/M$/',$server_size)){
						$server_size = preg_replace('/M$/','',$server_size);
						$server_size = $server_size * 1000;
					}
					echo "<p class='description'>" . sprintf(__('Sets the maximum uploadable file size in kilobytes. Your server allows up to %s KB. Leave it blank to use server limit.','emd-plugins'),$server_size) . "</p></td></tr>";
					echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_file_exts_" . $fattr . "'>";
					echo __('Allowed file extensions','emd-plugins');
					echo '</label></th><td>';
					$file_exts = "";
					if(!empty($ent_map_list[$kent]['file_exts'][$fattr])){
						$file_exts = $ent_map_list[$kent]['file_exts'][$fattr];
					}
					elseif(!empty($fval['file_ext'])){
						$file_exts = $fval['file_ext'];
					}
					echo '<textarea cols="40" rows="5" id="' . esc_attr($app) . '_ent_map_list_' . $kent . '_file_exts_' . $fattr . '" name="' . esc_attr($app) . '_ent_map_list[' . $kent . '][file_exts][' . $fattr . ']">' . $file_exts . '</textarea>';
					echo "<p class='description'>" . __('Sets the file extensions allowed to upload. Seperate each extension by a comma. For example: jpg,png,pdf','emd-plugins') . "</p></td></tr>";
				}
			}
			if(!empty($country_attrs[$kent])){
				foreach($country_attrs[$kent] as $cattr => $cval){
					echo "<tr style='border-top:2px solid #e0e0e0;border-bottom:2px solid #e0e0e0;'><th scope='row' style='padding:5px 5px;' colspan=2><h3 style='display:inline;color:#5f9ea0;'>" . $cval['label'];
					echo '</h3>';
					echo "<span class='description' style='padding-left:10px;color:#777;'>- " . __('Use the option below to select a default.','emd-plugins') . "</span></th></tr>";
					echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_default_country_" . $cattr . "'>";
					echo __('Default Country','emd-plugins');
					echo '</label></th><td>';
					echo '<select class="emd-country" data-state="' . esc_attr($app) . '_ent_map_list_' . $kent . '_default_state_' . $cval['state'] . '" id="' . esc_attr($app) . '_ent_map_list_' . $kent . '_default_country_' . $cattr . '" name="' . esc_attr($app) . '_ent_map_list[' . $kent . '][default_country][' . $cattr . ']">';
					$countries = emd_get_country_list();
					$country_val = 'US';
					if(!empty($ent_map_list[$kent]['default_country'][$cattr])){
						$country_val = $ent_map_list[$kent]['default_country'][$cattr];
					}
					foreach($countries as $kc => $vc){
						echo '<option value="' . $kc . '"';
						if($kc == $country_val){
							echo ' selected';
						}
						echo '>' . $vc . '</option>';
					}
					echo '</select>';
					echo "</td></tr>";
				}
			}
			if(!empty($state_attrs[$kent])){
				foreach($state_attrs[$kent] as $sattr => $sval){
					echo "<tr style='border-top:2px solid #e0e0e0;border-bottom:2px solid #e0e0e0;'><th scope='row' style='padding:5px 5px;' colspan=2><h3 style='display:inline;color:#5f9ea0;'>" . $sval['label'];
					echo '</h3>';
					echo "<span class='description' style='padding-left:10px;color:#777;'>- " . __('Use the option below to select a default.','emd-plugins') . "</span></th></tr>";
					echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_default_state_" . $sattr . "'>";
					echo __('Default State','emd-plugins');
					echo '</label></th><td>';
					echo '<select id="' . esc_attr($app) . '_ent_map_list_' . $kent . '_default_state_' . $sattr . '" name="' . esc_attr($app) . '_ent_map_list[' . $kent . '][default_state][' . $sattr . ']">';
					$country_val = 'US';
					if(!empty($ent_map_list[$kent]['default_country'][$sval['country']])){
						$country_val = $ent_map_list[$kent]['default_country'][$sval['country']];
					}
					$states = emd_get_country_states($country_val);
					$state_val = '';
					if(!empty($ent_map_list[$kent]['default_state'][$sattr])){
						$state_val = $ent_map_list[$kent]['default_state'][$sattr];
					}
					foreach($states as $ks => $vs){
						echo '<option value="' . $ks . '"';
						if($ks == $state_val){
							echo ' selected';
						}
						echo '>' . $vs . '</option>';
					}
					echo '</select>';
					echo "</td></tr>";
				}
			}
			//comments
			$comment_list = get_option($app . '_comment_list');
			//if(!empty($comment_list) && !empty($comment_list[$kent]) && !empty($comment_list[$kent]['display_type']) && $comment_list[$kent]['display_type'] != 'backend'){
			if(!empty($comment_list) && !empty($comment_list[$kent])){
				echo "<tr style='border-top:2px solid #e0e0e0;border-bottom:2px solid #e0e0e0;'><th scope='row' style='padding:5px 5px;' colspan=2><h3 style='display:inline;color:#5f9ea0;'>";
				echo $comment_list[$kent]['label'];
				echo '</h3>';
				echo "<span class='description' style='padding-left:10px;color:#777;'>- " . sprintf(__('Use the options below to customize %s %s.','emd-plugins'),strtolower($myent['label']),strtolower($comment_list[$kent]['label'])) . "</span></th></tr>";
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_comment_placeholder'>";
				echo __('Set placeholder','emd-plugins');
				echo "</label></th><td>";
				echo "<input id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_comment_placeholder' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][comment_placeholder]' type='text'";
				$comm_placeholder = __('Join the discussion...','emd-plugins');
				if(!empty($ent_map_list[$kent]['comment_placeholder'])){
					$comm_placeholder = $ent_map_list[$kent]['comment_placeholder'];
				}
				echo " value='" . $comm_placeholder . "'/>";
				echo "</td></tr>";
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_thread_depth'>";
				echo __('Set the depth of threaded (nested) comments','emd-plugins');	
				echo '</label></th><td>';
				echo "<select id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_thread_depth' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][thread_depth]'>";
				$thread_depth = '';
				if(!empty($ent_map_list[$kent]['thread_depth'])){
					$thread_depth = $ent_map_list[$kent]['thread_depth'];
				}
				echo '<option value=""';
				if($thread_depth == ''){
					echo " selected";
				}
				echo ">" . __('All','emd-plugins') . "</option>";	
				echo '<option value=1';
				if($thread_depth == 1){
					echo " selected";
				}
				echo ">" . __('None','emd-plugins') . "</option>";	
				for($i=2;$i<=10;$i++){
					echo "<option value='" . $i . "'";
					if($thread_depth == $i){
						echo " selected";
					}
					echo ">" . $i . "</option>";
				}
				echo "</select></td></tr>";
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_comments_per_page'>";
				echo __('Posts per page','emd-plugins');
				echo '</label></th><td>';
				$per_page_val = '10';
				if(!empty($ent_map_list[$kent]['comments_per_page'])){
					$per_page_val = $ent_map_list[$kent]['comments_per_page'];
				}
				echo '<input type="number" class="small-text" id="' . esc_attr($app) . '_ent_map_list_' . $kent . '_comments_per_page" name="' . esc_attr($app) . '_ent_map_list[' . $kent . '][comments_per_page]" value="' . $per_page_val . '">';
				echo "</p></td></tr>";
				$cust_roles = get_option($app . '_cust_roles');
				if(!empty($cust_roles)){
					$roles = implode(", ",$cust_roles);
					echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_comments_labels'>";
					echo __('Show labels for user roles','emd-plugins');
					echo '</label></th><td>';
					echo "<input type='checkbox' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_comments_labels' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][comments_labels]' value=1";
					if(!empty($ent_map_list[$kent]['comments_labels'])){
						echo ' checked';
					}
					echo "><p class='description'>" . sprintf(__('Displays the default plugin user roles (%s) next to the user name in a response box when a user belonging to that role leaves a reply.','emd-plugins'),$roles) . "</p></td></tr>";
				}
				echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_comments_attach'>";
				echo __('Enable file uploads','emd-plugins');
				echo '</label></th><td>';
				echo "<input type='checkbox' class='enable-comment-attch' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_comments_attach' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][comments_attach]' value=1";
				if(!empty($ent_map_list[$kent]['comments_attach'])){
					echo ' checked';
					$style_hidden = "";
				}
				else {
					$style_hidden = " style='display:none;'";
				}
				echo "></td></tr>";
				echo "<tr class='comments-attch' $style_hidden><th scope='row'><label for='ent_map_list_" . $kent . "_comments_attach_visitor'>";
				echo __('Enable file uploads for non-logged in users','emd-plugins');
				echo '</label></th><td>';
				echo "<input type='checkbox' class='enable-comment-attch' id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_comments_attach_visitor' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][comments_attach_visitor]' value=1";
				if(!empty($ent_map_list[$kent]['comments_attach_visitor'])){
					echo ' checked';
				}
				echo "></td></tr>";
				echo "<tr class='comments-attch' $style_hidden><th scope='row'><label for='ent_map_list_" . $kent . "_comments_max_files'>";
				echo __('Maximum files','emd-plugins');
				echo '</label></th><td>';
				$max_files = "";
				if(!empty($ent_map_list[$kent]['comments_max_files'])){
					$max_files = $ent_map_list[$kent]['comments_max_files'];
				}
				echo '<input type="number" class="small-text" id="' . esc_attr($app) . '_ent_map_list_' . $kent . '_comments_max_files" name="' . esc_attr($app) . '_ent_map_list[' . $kent . '][comments_max_files]" value="' . $max_files . '">';
				echo "<p class='description'>" . __('Sets the maximum number of allowable file uploads.','emd-plugins') . "</p></td></tr>";
				echo "<tr class='comments-attch' $style_hidden><th scope='row'><label for='ent_map_list_" . $kent . "_comments_max_file_size'>";
				echo __('Maximum file size','emd-plugins');
				echo '</label></th><td>';
				$max_file_size = "";
				if(!empty($ent_map_list[$kent]['comments_max_file_size'])){
					$max_file_size = $ent_map_list[$kent]['comments_max_file_size'];
				}
				echo '<input type="number" class="small-text" id="' . esc_attr($app) . '_ent_map_list_' . $kent . '_comments_max_file_size" name="' . esc_attr($app) . '_ent_map_list[' . $kent . '][comments_max_file_size]" value="' . $max_file_size . '"> KB';
				$server_size = ini_get('upload_max_filesize');
				if(preg_match('/M$/',$server_size)){
					$server_size = preg_replace('/M$/','',$server_size);
					$server_size = $server_size * 1000;
				}
				echo "<p class='description'>" . sprintf(__('Sets the maximum uploadable file size in kilobytes. Your server allows up to %s KB. Leave it blank to use server limit.','emd-plugins'),$server_size) . "</p></td></tr>";
				echo "<tr class='comments-attch' $style_hidden><th scope='row'><label for='ent_map_list_" . $kent . "_comments_file_exts'>";
				echo __('Allowed file extensions','emd-plugins');
				echo '</label></th><td>';
				$file_exts = "";
				if(!empty($ent_map_list[$kent]['comments_file_exts'])){
					$file_exts = $ent_map_list[$kent]['comments_file_exts'];
				}
				echo '<textarea cols="40" rows="5" id="' . esc_attr($app) . '_ent_map_list_' . $kent . '_comments_file_exts" name="' . esc_attr($app) . '_ent_map_list[' . $kent . '][comments_file_exts]">' . $file_exts . '</textarea>';
				echo "<p class='description'>" . __('Sets the file extensions allowed to upload. Seperate each extension by a comma. For example: jpg,png,pdf','emd-plugins') . "</p></td></tr>";
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($){
					$('.enable-comment-attch').on('click',function(){
						if($(this).is(':checked')){
							$('.comments-attch').show();
						}
						else {
							$('.comments-attch').hide();
						}
					});
				});
			</script>
			<?php
			}
			if(!empty($ent_map_list[$kent])){
				$ent_conf = $ent_map_list[$kent];
			}
			else {
				$ent_conf = Array();
			}
			do_action('emd_entity_end_settings',$app,$kent,$ent_conf);
			 
			if(!empty($myent['attrs'])){
				emd_show_map_attrs($app,$myent,$ent_map_variables,$ent_map_list);	
			}
			echo '</tbody></table>';
			echo '</div></div></li>';
		}
		echo '</ul></div>';
		submit_button(); 
		echo '</form></div>';
	}
}
if (!function_exists('emd_show_map_attrs')) {
	function emd_show_map_attrs($app,$myent,$ent_map_variables,$ent_map_list){
		foreach($myent['attrs'] as $mattr){
			$mattr_key = $mattr;
			$mattr_val = $ent_map_variables[$mattr_key];
			echo '<tr>
				<th scope="row">
				<label for="' . $mattr_key . '">';
			echo $mattr_val['label']; 
			echo '</label>
				</th>
				<td>';
			$width = isset($ent_map_list[$mattr_key]['width']) ? $ent_map_list[$mattr_key]['width'] : '';
			$height = isset($ent_map_list[$mattr_key]['height']) ? $ent_map_list[$mattr_key]['height'] : '';
			$zoom = isset($ent_map_list[$mattr_key]['zoom']) ? $ent_map_list[$mattr_key]['zoom'] : '14';
			$marker = isset($ent_map_list[$mattr_key]['marker']) ? 'checked' : '';
			$load_info = isset($ent_map_list[$mattr_key]['load_info']) ? 'checked' : '';
			$map_type = isset($ent_map_list[$mattr_key]['map_type']) ? $ent_map_list[$mattr_key]['map_type'] : '';
			echo "<tr><th scope='row'></th><td><table><th scope='row'><label>" . __('Frontend map settings','emd-plugins') . "</th><td></td></tr>
				<th scope='row'><label for='ent_map_list_" . $mattr_key . "_width'>" . __('Width','emd-plugins') . "</th><td><input id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_width' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][width]' type='text' value='" . $width . "'></input><p class='description'>" . __('Sets the map width.You can use \'%\' or \'px\'. Default is 100%.','emd-plugins') . "</p></td></tr>";
			echo "<tr><th scope='row'><label for='ent_map_list_" . $mattr_key . "_height'>" . __('Height','emd-plugins') . "</th><td><input id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_height' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][height]' type='text' value='" . $height ."'></input><p class='description'>" . __('Sets the map height. You can use \'px\'. Default is 480px.','emd-plugins') . "</p></td></tr>";
			echo "<tr><th scope='row'><label for='ent_map_list_" . $mattr_key . "_zoom'>" . __('Zoom','emd-plugins') . "</th><td><select id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_zoom' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][zoom]'>";
			for($i=20;$i >=1;$i--){
				echo "<option value='" . $i . "'";
				if($zoom == $i){
					echo " selected";
				}
				echo ">" . $i . "</option>";
			}
			echo "</select></td></tr>";
			echo "<tr><th scope='row'><label for='ent_map_list_" . $mattr_key . "_map_type'>" . __('Type','emd-plugins') . "</th><td><select id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_map_type' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][map_type]'>";
			$map_types = Array("ROADMAP","SATELLITE","HYBRID","TERRAIN");
			foreach($map_types as $mtype){
				echo "<option value='" . $mtype . "'";
				if($map_type == $mtype){
					echo " selected";
				}
				echo ">" . $mtype . "</option>";
			}
			echo "</select></td></tr>";
			echo "<tr><th scope='row'><label for='ent_map_list_" . $mattr_key . "_marker'>" . __('Marker','emd-plugins') . "</th><td><input id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_marker' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][marker]' type='checkbox' value=1 $marker></input></td></tr>";
			echo "<tr><th scope='row'><label for='ent_map_list_" . $mattr_key . "_load_info'>" . __('Display info window on page load','emd-plugins') . "</th><td><input id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_load_info' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][load_info]' type='checkbox' value=1 $load_info></input></td></tr>";
			echo "</div></td></tr></table></td></tr>";
			echo '</td>
				</tr>';
		}
	}
}
if (!function_exists('emd_ent_map_sanitize')) {
	function emd_ent_map_sanitize($input){
		if(empty($input['app'])){
			return $input;
		}
		$ent_map_list = get_option($input['app'] . '_ent_map_list');
		$attr_list = get_option($input['app'] . '_attr_list');
		$rel_list = get_option($input['app'] . '_rel_list');
		$map_keys = apply_filters('emd_entity_map_keys',Array('hide_archive_page_nav','hide_prev_next','hide_edit_link','hide_rels','edit_rels','hide_rel_attrs','attrs','edit_attrs','single_temp','archive_temp','single_container','archive_container','make_visitor_visible','rewrite','cust_fields','width','height','zoom','map_type','marker','load_info','comment_placeholder','thread_depth','max_files','max_file_size','file_exts','comments_per_page','comments_labels','comments_attach','comments_attach_visitor','comments_max_file_size','comments_max_files','comments_file_exts','default_country','default_state'),$input['app']);
		foreach($input as $ikey => $vkey){
			if($ikey != 'app'){
				foreach($map_keys as $mkey){
					if(isset($vkey[$mkey])){
						$ent_map_list[$ikey][$mkey] = $vkey[$mkey];
					}
					elseif(!empty($ent_map_list[$ikey][$mkey])){
						unset($ent_map_list[$ikey][$mkey]);    
					}
				}
			}
		}
		return $ent_map_list;
	}
}
if (!function_exists('emd_get_attr_map')) {
	function emd_get_attr_map($app,$key,$marker_title,$info_window,$post_id=''){
		$ent_map_list = get_option(str_replace("-","_",$app) . '_ent_map_list');
		$args = Array();
		$marker = (!empty($ent_map_list[$key]['marker'])) ? true : false;
		$load_info = (!empty($ent_map_list[$key]['load_info'])) ? true : false;
		$zoom = (!empty($ent_map_list[$key]['zoom'])) ? (int) $ent_map_list[$key]['zoom'] : 14;
		$map_type = (!empty($ent_map_list[$key]['map_type'])) ? $ent_map_list[$key]['map_type'] : 'ROADMAP';
		$width = (!empty($ent_map_list[$key]['width'])) ? $ent_map_list[$key]['width'] : '100%'; // Map width, default is 640px. You can use '%' or 'px'
		$height = (!empty($ent_map_list[$key]['height'])) ? $ent_map_list[$key]['height'] : '480px'; // Map height, default is 480px. You can use '%' or 'px'
		
		$args = array(
				'app'		=> str_replace("-","_",$app),
				'type'	       => 'map',
				'zoom'         => $zoom,  // Map zoom, default is the value set in admin, and if it's omitted - 14
				'width'        => $width,
				'height'       => $height,
				// Map type, see https://developers.google.com/maps/documentation/javascript/reference#MapTypeId
				'mapTypeId'    => $map_type,
				'marker'       => $marker, // Display marker? Default is 'true',
				'load_info'    => $load_info
			);
		if($marker !== false && !empty($marker_title)){
			if($marker_title == 'emd_blt_title'){
				$args['marker_title'] = get_the_title($post_id); // Marker title when hover
			}
			else {	
				$args['marker_title'] = emd_mb_meta($marker_title,'',$post_id); // Marker title when hover
			}
		}
		if($marker !== false && !empty($info_window)){
			if($info_window == 'emd_blt_title'){
				$args['info_window'] = get_the_title($post_id); // Info window content, can be anything. HTML allowed.
			}
			else {
				$args['info_window'] = emd_mb_meta($info_window,'',$post_id); // Info window content, can be anything. HTML allowed.
			}
		}
		return emd_mb_meta($key,$args,$post_id);
	}
}
if (!function_exists('emd_tax_tab')) {
	function emd_tax_tab($app,$active_tab,$tax_list,$shc_list){
		if(!empty($tax_list)){
			$tax_settings = get_option($app .'_tax_settings',Array());
	?>
	<div class='tab-content' id='tab-taxonomy' <?php if ( 'taxonomy' != $active_tab ) { echo 'style="display:none;"'; } ?>>
		<?php	echo '<form method="post" action="options.php">';
			settings_fields($app .'_tax_settings');
			//show taxonomy rewrite url
			if(!empty($tax_list)){
				$inline_ent_list = get_option('emd_inline_ent_list', Array());
				foreach($tax_list as $tent => $vtax){
					if(!in_array($tent,array_keys($inline_ent_list))){
						foreach($vtax as $ktax => $valtax){
							$tax_list_vals[$ktax]['rewrite'] = $ktax;
							if(!empty($valtax['rewrite'])){
								$tax_list_vals[$ktax]['rewrite'] = $valtax['rewrite'];
							}
							$tax_list_vals[$ktax]['label'] = $valtax['label'];
							$tax_list_vals[$ktax]['archive_view'] = $valtax['archive_view'];
						}
					}
				}
			}
			$role_caps = get_option($app . '_add_caps');
			echo '<input type="hidden" name="' . esc_attr($app) . '_tax_settings[app]" id="' . esc_attr($app) . '_tax_settings_app" value="' . $app . '">';
			echo '<div id="tax-settings" class="accordion-container"><ul class="outer-border">';
			$app_custom_roles = get_option($app . '_cust_roles');
			$visitor_access = Array('show' => __('Show','emd-plugins'),'not_show'=>__('Do not show','emd-plugins'));
			$role_access = Array('edit'=>__('Allow Edit','emd-plugins'),'show' => __('Show','emd-plugins'),'not_show'=>__('Do not show','emd-plugins'));
			foreach($tax_list_vals as $ktax => $mytax){
				$rewrite = isset($tax_settings[$ktax]['rewrite']) ? $tax_settings[$ktax]['rewrite'] : $mytax['rewrite'];
				echo '<li id="' . esc_attr($ktax) . '" class="control-section accordion-section ';
				echo (count($tax_list_vals) == 1) ? 'open' : '';
				echo '">';
				echo '<h3 class="accordion-section-title hndle" tabindex="0">' . $mytax['label'] . ' (' . $rewrite . ')' . '</h3>';
				echo '<div class="accordion-section-content"><div class="inside">';
				echo '<table class="form-table"><tbody>';
				if($shc_list['remove_vis'] != 1){
					echo "<tr><th scope='row'><label for='tax_settings_" . $ktax . "_hide'>";
					echo __('Availability','emd-plugins');
					echo '</label></th><td>';
					if(empty($shc_list['frontedit'])){
						echo "<select class='emd-attr-visibility' id='" . esc_attr($app) . "_tax_settings_" . $ktax . "_hide' name='" . esc_attr($app) . "_tax_settings[" . $ktax . "][hide]'>";
						$tax_options = Array('show' => __('Enable','emd-plugins'),
								'hide' => __('Disable','emd-plugins'),
								'hide_frontend' => __('Show only in Admin','emd-plugins')
								);
						foreach($tax_options as $tkey => $tval){
							echo "<option value='" . $tkey . "'";
							if(!empty($tax_settings[$ktax]['hide']) && $tax_settings[$ktax]['hide'] == $tkey){
								echo " selected";
							}
							echo ">" . $tval . "</option>";
						}
						echo "</select>";
						echo "<p class='description'>" . __('Enable: Display this taxonomy on everywhere</br>Disable: Remove this taxonomy from everywhere</br>Show only in Admin: This taxonomy is still enabled on admin area. If you want to hide this taxonomy on the frontend forms go to Forms tab.','emd-plugins') . "</p></br>";
					} else {
						echo "<table class='widefat striped'";
						if(!empty($app_custom_roles) && count($app_custom_roles) < 4){
							echo " style='width:640px;'";
						}	
						echo ">";
						echo "<tr><th style='text-align:center;'>" . __('Visibility','emd-plugins'). "</th>";
						if(!empty($shc_list['frontedit'])){
							echo "<th style='text-align:center;'>" . __('Visitor','emd-plugins') . "</th>";
							foreach($app_custom_roles as $crole){
								echo "<th style='text-align:center;'>" . $crole . "</th>";
							}
						}
						echo "</tr>";
						echo "<tr><td style='text-align:center;'>";
						echo "<select class='emd-attr-visibility' id='" . esc_attr($app) . "_tax_settings_" . $ktax . "_hide' name='" . esc_attr($app) . "_tax_settings[" . $ktax . "][hide]'>";
						$tax_options = Array('show' => __('Enable','emd-plugins'),
								'hide' => __('Disable','emd-plugins'),
								'hide_frontend' => __('Show only in Admin','emd-plugins')
								);
						foreach($tax_options as $tkey => $tval){
							echo "<option value='" . $tkey . "'";
							if(!empty($tax_settings[$ktax]['hide']) && $tax_settings[$ktax]['hide'] == $tkey){
								echo " selected";
							}
							echo ">" . $tval . "</option>";
						}
						echo "</select></td>";
						echo "<td style='text-align:center;'><select class='emd-attr-access emd-attr-visitor' id='" . esc_attr($app) . "_tax_settings_" . $ktax . "_edit_visitor' name='" . esc_attr($app) . "_tax_settings[" . $ktax . "][edit][visitor]'";
						if(!empty($tax_settings[$ktax]['hide']) && in_array($tax_settings[$ktax]['hide'],Array('hide','hide_frontend'))){
								echo " disabled";
						}
						echo ">";
						foreach($visitor_access as $rkey => $rval){
							echo "<option value='" . $rkey . "'";
							if(!empty($tax_settings[$ktax]['edit']['visitor']) && $tax_settings[$ktax]['edit']['visitor'] == $rkey){
								echo " selected";
							}
							echo ">" . $rval . "</option>";
						}
						echo "</select></td>";
						foreach($app_custom_roles as $krole => $vrole){
							echo "<td style='text-align:center;'><select class='emd-attr-access' id='" . esc_attr($app) . "_tax_settings_" . $ktax . "_edit_" . $krole . "' name='" . esc_attr($app) . "_tax_settings[" . $ktax . "][edit][" . $krole . "]'";
							if(!empty($tax_settings[$ktax]['hide']) && $tax_settings[$ktax]['hide'] == 'hide'){
								echo " disabled";
							}
							echo ">";
							//check if this role can edit this entity
							if(!empty($role_caps['assign_' . $ktax]) && in_array($krole,$role_caps['assign_' . $ktax])){
								$access_arr = $role_access;
							}
							else {
								$access_arr = $visitor_access;
							}
							foreach($access_arr as $rkey => $rval){
								echo "<option value='" . $rkey . "'";
								if(!empty($tax_settings[$ktax]['edit'][$krole]) && $tax_settings[$ktax]['edit'][$krole] == $rkey){
									echo " selected";
								}
								echo ">" . $rval . "</option>";
							}
							echo "</select></td>";
						}
						echo "</tr></table>";
						echo "<p class='description'>" . __('Enable: Display this taxonomy on everywhere</br>Disable: Remove this taxonomy from everywhere</br>Show only in Admin: This taxonomy is still enabled on admin area. If you want to hide this taxonomy on the frontend forms go to Forms tab.','emd-plugins') . "</p></br>";
					}
					echo "</td></tr>";
				}
				echo "<tr><th scope='row'><label for='tax_settings_" . $ktax . "_rewrite'>";
				echo __('Base slug','emd-plugins');
				echo '</label></th><td>';
				echo "<input id='" . esc_attr($app) . "_tax_settings_" . $ktax . "_rewrite' name='" . esc_attr($app) . "_tax_settings[" . $ktax . "][rewrite]' type='text' value='" . $rewrite ."'></input><p class='description'>" . __('Sets the custom base slug for this taxonomy. After you update,  flush the rewrite rules by going to the Permalink Settings page.','emd-plugins') . "</p></td></tr>";
				if(!empty($mytax['archive_view'])){	
					echo "<tr><th scope='row'><label for='tax_settings_" . $ktax . "_temp'>";
					echo __('Template','emd-plugins');
					echo '</label></th><td>';
					$tax_temp = isset($tax_settings[$ktax]['temp']) ? $tax_settings[$ktax]['temp'] : 'right';
					echo "<select id='" . esc_attr($app) . "_tax_settings_" . $ktax . "_temp' name='" . esc_attr($app) . "_tax_settings[" . $ktax . "][temp]'>";
					$temp_options = Array('right' => __('Right Sidebar','emd-plugins'),'left' => __('Left Sidebar','emd-plugins'), 'full' => __('Full Width','emd-plugins'));
					foreach($temp_options as $ktemp => $vtemp){
						echo "<option value='" . $ktemp . "'";
						if($tax_temp == $ktemp){
							echo " selected";
						}
						echo ">" . $vtemp . "</option>";
					}
					echo "</select><p class='description'>" . __('Sets the template for the posts which belong to this taxonomy.','emd-plugins') . "</p></td></tr>";
					echo "<tr><th scope='row'><label for='tax_settings_" . $ktax . "_container'>";
					echo __('Container type','emd-plugins');
					echo '</label></th><td>';
					$tax_container = isset($tax_settings[$ktax]['container']) ? $tax_settings[$ktax]['container'] : 'container';
					echo "<select id='" . esc_attr($app) . "_tax_settings_" . $ktax . "_container' name='" . esc_attr($app) . "_tax_settings[" . $ktax . "][container]'>";
					$container_options = Array('container' => __('Fixed','emd-plugins'),'container-fluid' => __('Full','emd-plugins'));
					foreach($container_options as $kcont => $vcont){
						echo "<option value='" . $kcont . "'";
						if($tax_container == $kcont){
							echo " selected";
						}
						echo ">" . $vcont . "</option>";
					}
					echo "</select><p class='description'>" . __('Change this if the sidebars are getting out of your page boundry. Fixed type provides a responsive fixed width container for this taxonomy. Full type provides a full width container, spanning the entire width of the viewport for this taxonomy.','emd-plugins') . "</p></td></tr>";
					echo "<tr><th scope='row'><label for='tax_settings_" . $ktax . "_hide_page_nav'>";
					echo __('Hide page navigation','emd-plugins');
					echo '</label></th><td>';
					echo "<input type='checkbox' id='" . esc_attr($app) . "_tax_settings_" . $ktax . "_hide_page_nav' name='" . esc_attr($app) . "_tax_settings[" . $ktax . "][hide_page_nav]' value=1";
					if(isset($tax_settings[$ktax]['hide_page_nav'])){
						echo " checked";
					}
					echo ">";
					echo "<p class='description'>" . __('Hides the page navigation links on the frontend for archive posts.','emd-plugins') . "</p></td></tr>";
				}
				echo '</tbody></table>';
				echo '</div></div></li>';
			}
			echo '</ul></div>';
			submit_button(); 
			echo '</form></div>';
		}
	}
}
if (!function_exists('emd_tax_settings_sanitize')) {
	function emd_tax_settings_sanitize($input){
		if(empty($input['app'])){
			return $input;
		}
		$tax_settings = get_option($input['app'] . '_tax_settings');
		$keys = Array('rewrite','temp','container','hide','edit','hide_page_nav');
		foreach($input as $ikey => $vkey){
			if($ikey != 'app'){
				foreach($keys as $mkey){
					if(isset($vkey[$mkey])){
						$tax_settings[$ikey][$mkey] = $vkey[$mkey];
					}
					elseif(!empty($tax_settings[$ikey][$mkey])){
						unset($tax_settings[$ikey][$mkey]);    
					}
				}
			}
		}
		return $tax_settings;
	}
}
if (!function_exists('emd_tools_tab')) {
	function emd_tools_tab($app,$active_tab,$shc_list){
		if ( ! empty( $_GET['action'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'install_pages_action' ) ) {
			emd_create_install_pages($app,$shc_list);
			echo '<div class="updated inline"><p>' . __( 'All missing pages were installed successfully.', 'emd-plugins' ) . '</p></div>';
		}
			
		$tools = get_option($app .'_tools',Array());
		?>
		<div class='tab-content' id='tab-tools' <?php if ( 'tools' != $active_tab ) { echo 'style="display:none;"'; } ?>>
		<?php	echo '<form method="post" action="options.php">';
		settings_fields($app .'_tools');
		echo '<input type="hidden" name="' . esc_attr($app) . '_tools[app]" id="' . esc_attr($app) . '_tools_app" value="' . $app . '">';
		echo '<table class="form-table"><tbody>';
		echo "<tr><th scope='row'><label for='tools_install_pages'>";
		echo __('Install pages','emd-plugins');
		echo '</label></th><td>';
		echo '<a href="' .  wp_nonce_url( admin_url('admin.php?page=' . $app . '_settings&tab=tools&action=install_pages'), 'install_pages_action' ) . '" class="button install_pages">' . __( 'Install pages', 'emd-plugins' ) . '</a>';
		echo "<p class='description'>" . sprintf(__('This tool will install all the missing %s pages. Pages already defined and set up will not be replaced.','emd-plugins'),$shc_list['app']) . "</p></td></tr>";
		echo "<tr><th scope='row'><label for='tools_disable_emd_templates'>";
		echo __('Disable EMD Templating System','emd-plugins');
		echo '</label></th><td>';
		echo "<input type='checkbox' id='" . esc_attr($app) . "_tools_disable_emd_templates' name='" . esc_attr($app) . "_tools[disable_emd_templates]' value=1";
		if(isset($tools['disable_emd_templates'])){
			echo " checked";
		}
		echo ">";
		echo "<p class='description'>" . __('Check this if you experience theme related issues. This will disable EMD Templating System and use your themes templates instead. It will also disable all the templating related options in Entities and Taxonomies tabs. If you still experience theme related issues after checking this option, read the plugin documentation\'s resolving theme related issues section.','emd-plugins') . "</p></td></tr>";
		echo "<tr><th scope='row'><label for='tools_remove_settings'>";
		echo __('Delete all settings','emd-plugins');
		echo '</label></th><td>';
		echo "<input type='checkbox' id='" . esc_attr($app) . "_tools_remove_settings' name='" . esc_attr($app) . "_tools[remove_settings]' value=1";
		if(isset($tools['remove_settings'])){
			echo " checked";
		}
		echo ">";
		echo "<p class='description'>" . __('This tool will delete all settings/options data including setup assistant created plugin pages when using the "Delete" link on the plugins screen.','emd-plugins') . "</p></td></tr>";
		echo "<tr><th scope='row'><label for='tools_remove_data'>";
		echo __('Delete all data','emd-plugins');
		echo '</label></th><td>';
		echo "<input type='checkbox' id='" . esc_attr($app) . "_tools_remove_data' name='" . esc_attr($app) . "_tools[remove_data]' value=1";
		if(isset($tools['remove_data'])){
			echo " checked";
		}
		echo ">";
		//get ent labels
		$ent_list = get_option($app . '_ent_list');
		foreach($ent_list as $myent){
			$ent_labels_arr[] = $myent['label'];
		}
		$ent_labels = implode($ent_labels_arr," , ");
		echo "<p class='description'>" . sprintf(__('This tool will delete all %s data and related taxonomies including setup assistant created plugin pages when using the "Delete" link on the plugins screen. As a best practice we recommend you to backup your database before selecting this option.','emd-plugins'),$ent_labels) . "</p></td></tr>";
		echo "<tr><th scope='row'><label for='tools_custom_css'>";
                echo __('Custom CSS','emd-plugins');
                echo "</label></th><td>";
                $custom_css = isset($tools['custom_css']) ? $tools['custom_css'] : '';
                echo "<textarea cols='70' rows='30' id='" . esc_attr($app) . "_tools_custom_css' name='" . esc_attr($app) . "_tools[custom_css]' >" .  esc_html($custom_css) . "</textarea>";
                echo "<p class='description'>" . __('Custom CSS allows you to add your own styles or override the default CSS of this plugin. The CSS code written here is only applied to this plugin\'s frontend pages.','emd-plugins') . "</p></td></tr>";
		echo "<tr><th scope='row'><label for='tools_custom_js'>";
                echo __('Custom JS','emd-plugins');
                echo "</label></th><td>";
                $custom_js = isset($tools['custom_js']) ? $tools['custom_js'] : '';
                echo "<textarea cols='70' rows='30' id='" . esc_attr($app) . "_tools_custom_js' name='" . esc_attr($app) . "_tools[custom_js]' >" .  esc_html($custom_js) . "</textarea>";
                echo "<p class='description'>" . __('Custom JS allows you to add your own JavaScript. The JavaScript code written here is only applied to this plugin\'s frontend pages.','emd-plugins') . "</p></td></tr>";
		echo '</tbody></table>';
		submit_button(); 
		echo '</form></div>';
		echo '<script language="javascript">
                        jQuery( document ).ready( function() {
                                var editor = CodeMirror.fromTextArea(document.getElementById("' . esc_attr($app) . '_tools_custom_css"), {lineNumbers: true, lineWrapping: true, mode:"css"} );
                                var editor = CodeMirror.fromTextArea(document.getElementById("' . esc_attr($app) . '_tools_custom_js"), {lineNumbers: true, lineWrapping: true, mode:"javascript"} );
                        });
                </script>';
	}
}
if (!function_exists('emd_create_install_pages')) {
	function emd_create_install_pages($app,$shc_list){
		global $wpdb;
		$shc_list = apply_filters('emd_ext_chart_list', $shc_list, $app);
		$types = Array(
			'forms',
			'charts',
			'shcs',
			'datagrids',
			'integrations'
		);
		foreach ($types as $shc_type) {
			if (!empty($shc_list[$shc_type])) {
				foreach ($shc_list[$shc_type] as $keyshc => $myshc) {
					if (isset($myshc['page_title'])) {
						$pages[$keyshc] = $myshc;
					}
				}
			}
		}
		$setup_pages_list = get_option($app . '_setup_pages_list',Array());
		foreach ($pages as $key => $page) {
			$found = "";
			$page_content = "[" . $key . "]";
			$found = $wpdb->get_var($wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type='page' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%"));
			if ($found != "") {
				$setup_pages_list[$key] = $found;
				continue;
			}
			$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_author' => get_current_user_id() ,
				'post_title' => $page['page_title'],
				'post_content' => $page_content,
				'comment_status' => 'closed'
			);
			$page_id = wp_insert_post($page_data);
			$setup_pages_list[$key] = $page_id;
		}
		if(!empty($setup_pages_list)){
			update_option($app . '_setup_pages_list',$setup_pages_list);
		}
	}
}
if (!function_exists('emd_tools_sanitize')) {
	function emd_tools_sanitize($input){
		if(empty($input['app'])){
			return $input;
		}
		$tools = get_option($input['app'] . '_tools');
		$keys = Array('disable_emd_templates','remove_settings','remove_data','custom_css','custom_js');
		foreach($keys as $mkey){
			if(isset($input[$mkey])){
				$tools[$mkey] = $input[$mkey];
			}
			elseif(!empty($tools[$mkey])){
				unset($tools[$mkey]);    
			}
		}
		return $tools;
	}
}
