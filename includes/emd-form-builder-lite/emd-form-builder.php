<?php
/**
 * Form Settings Functions
 *
 */
if (!defined('ABSPATH')) exit;

require_once 'emd-form-frontend.php';
require_once 'emd-form-settings.php';
require_once 'emd-form-functions.php';

add_action('emd_ext_set_conf','emd_form_builder_lite_install');

function emd_form_builder_lite_install($app){
	$app = str_replace('-','_',$app);
	$shc_list = get_option($app . '_shc_list',Array());
	if(!empty($shc_list['has_form_lite'])){
		emd_form_builder_lite_save_forms_data($app);
	}
}

add_action('emd_ext_init','emd_form_lite_old_forms');

function emd_form_lite_old_forms($app){
	$shc_list = get_option($app . '_shc_list',Array());
	if(!empty($shc_list['forms']) && !empty($shc_list['has_form_lite'])){
		foreach($shc_list['forms'] as $fkey => $fval){
			add_shortcode($fkey,'emd_old_forms_lite_shc');
		}
	}
}

function emd_old_forms_lite_shc($atts,$content,$tag){
	if(!empty($tag)){
		$fid = get_option('emd_form_id_' . $tag,0);
		if(!empty($fid)){
			if(!empty($atts['set'])){
				return do_shortcode("[emd_form id='" . $fid . "' set=\"" . $atts['set'] . "\"]");
			}
			else {
				return do_shortcode("[emd_form id='" . $fid . "']");
			}
		}
	}
}

if (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
	add_filter('posts_where', 'emd_form_lite_builtin_posts_where', 10, 2);
}
add_action('emd_ext_admin_enq', 'emd_form_builder_lite_admin_enq', 10, 2);

function emd_form_builder_lite_admin_enq($app,$hook){
	if(preg_match('/page_' . $app . '_forms$/',$hook)){
		$shc_list = get_option($app . '_shc_list',Array());
		if(empty($shc_list['has_form_lite'])){
			return;
		}
		$dir_url = constant(strtoupper($app) . "_PLUGIN_URL");
		$builder_vars = Array();
		if(!empty($_GET['edit']) && $_GET['edit'] == 'layout'){
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-draggable');
			wp_enqueue_style('form-builder-css', $dir_url . 'includes/emd-form-builder-lite/css/emd-form-builder.min.css');
			$builder_vars['ajax_url'] = admin_url('admin-ajax.php');
			$builder_vars['exit_url'] = admin_url('admin.php?page=' . sanitize_text_field($_GET['page']));
			$builder_vars['nonce'] = wp_create_nonce('emd_form');
			wp_enqueue_script('form-builder-js', $dir_url . 'includes/emd-form-builder-lite/js/emd-form-builder.js');
			wp_localize_script("form-builder-js", 'builder_vars', $builder_vars);
		}
		elseif(!empty($_GET['edit']) && $_GET['edit'] == 'settings'){
			wp_enqueue_style('form-builder-css', $dir_url . 'includes/emd-form-builder-lite/css/emd-form-builder.min.css');
			wp_enqueue_style('jq-css', $dir_url . 'assets/css/smoothness-jquery-ui.css');
			wp_enqueue_script('jquery-ui-timepicker', $dir_url . 'assets/ext/emd-meta-box/js/jqueryui/jquery-ui-timepicker-addon.js', array(
						'jquery-ui-datepicker',
						'jquery-ui-slider'
						) , constant(strtoupper($app) . '_VERSION'), true);
			wp_enqueue_style('jquery-ui-timepicker', $dir_url . 'assets/ext/emd-meta-box/css/jqueryui/jquery-ui-timepicker-addon.css');
			wp_enqueue_style('wpas-select2', $dir_url . 'assets/ext/bselect24/select2.min.css');
			wp_enqueue_script('wpas-select2-js', $dir_url . 'assets/ext/bselect24/select2.full.min.js');
			wp_enqueue_script('form-settings-js', $dir_url . 'includes/emd-form-builder-lite/js/emd-form-settings.js');
			return;
		}
		else {
			wp_enqueue_script('emd-copy-js', $dir_url . 'assets/js/emd-copy.js', array('clipboard') , '');
		}
	}
}


//change class install to save all forms in wp_posts with emd_form ptype
function emd_form_builder_lite_save_forms_data($app){
	$shc_list = get_option($app . '_shc_list');
	if(!empty($shc_list['forms'])){
		$post_forms = get_posts(Array('post_type'=>'emd_form','post_status'=>'publish','posts_per_page' => '-1'));
		$saved_forms = Array();
		if(!empty($post_forms)){
			foreach($post_forms as $myp_form){
				$fcontent = json_decode($myp_form->post_content,true);
				if($app == $fcontent['app']){
					$saved_forms[$fcontent['name']] = $fcontent;
				}
			}
		}
		$rel_list = get_option($app . '_rel_list', Array());
		$rel_list = apply_filters('emd_ext_form_rels',$rel_list,$app);
		$attr_list = get_option($app . '_attr_list', Array());
		$ent_list = get_option($app . '_ent_list', Array());
		$txn_list = get_option($app . '_tax_list', Array());
		$glob_list = get_option($app . '_glob_init_list', Array());
		$glob_forms_list = get_option($app . '_glob_forms_list',Array());
		$glob_forms_list = apply_filters('emd_ext_form_var_init',$glob_forms_list,$app, '');
		$glob_forms_init_list = get_option($app . '_glob_forms_init_list',Array());
		$glob_forms_init_list = apply_filters('emd_ext_form_var_init', $glob_forms_init_list, $app, '');
		$non_field_confs = Array('captcha','noaccess_msg','error_msg','success_msg','login_reg','csrf','btn', 'noresult_msg');
		foreach($shc_list['forms'] as $kform => $vform){
			if(!in_array($kform,array_keys($saved_forms))){
				$ftitle = '';
				$myform = Array();
				$myform['name'] = $kform;
				$myform['type'] = $vform['type'];
				$myform['entity'] = $vform['ent'];	
				$myform['app'] = $app;	
				$myform['source'] = 'plugin';	
				$myform['settings']['captcha'] = $vform['show_captcha'];
				$myform['settings']['noaccess_msg'] = $vform['noaccess_msg'];
				$myform['settings']['success_msg'] = $vform['confirm_success_txt'];
				$myform['settings']['error_msg'] = $vform['confirm_error_txt'];
				$myform['settings']['login_reg'] = $vform['login_reg'];
				$rel_labels = Array();
				foreach($rel_list as $krel => $vrel){
					if($myform['entity'] == $vrel['from']){
						$rel_labels[$krel]['label'] = $vrel['from_title'];
					}
					elseif($myform['entity'] == $vrel['to']){
						$rel_labels[$krel]['label'] = $vrel['to_title'];
					}
					if(!empty($vrel['desc'])){
						$rel_labels[$krel]['desc'] = $vrel['desc'];
					}
					elseif(!empty($rel_labels[$krel]['label'])){
						$rel_labels[$krel]['desc'] = $rel_labels[$krel]['label'];
					}
				}
				if(!empty($vform['page_title'])){
					$myform['page_title'] = $vform['page_title'];
				}
				if(empty($glob_forms_list[$kform])){
					$fsettings = $glob_forms_init_list[$kform];
				}
				else {
					$fsettings = $glob_forms_list[$kform];
				}	
				foreach($fsettings as $skey => $sval){
					if($skey != 'btn'){
						$myfield = Array();
						if(in_array($skey,$non_field_confs) && !is_array($sval)){
							$myform['settings'][$skey] = ltrim($sval);
						}
						else {
							if(in_array($skey,Array('blt_title','blt_content','blt_excerpt'))){
								$myfield[$skey] = Array("show"=>$sval['show'],"req"=>$sval['req'],"size"=>$sval['size']);
								if(!empty($ent_list[$myform['entity']]['req_blt'][$skey])){
									$myfield[$skey]['label'] = $ent_list[$myform['entity']]['req_blt'][$skey]['msg'];
								}
								elseif(!empty($ent_list[$myform['entity']]['blt_list'][$skey])){
									$myfield[$skey]['label'] = $ent_list[$myform['entity']]['blt_list'][$skey];
								}	
								$myfield[$skey]['desc'] = $myfield[$skey]['label'];
							}
							elseif(!empty($rel_labels[$skey])){
								$myfield[$skey] = Array("show"=>$sval['show'],"req"=>$sval['req'],"size"=>$sval['size']);
								$myfield[$skey]['label'] = $rel_labels[$skey]['label'];
								$myfield[$skey]['desc'] = $rel_labels[$skey]['desc'];
							}
							elseif(!empty($attr_list[$myform['entity']][$skey])){
								if($myform['type'] == 'search' || (empty($attr_list[$myform['entity']][$skey]['uniqueAttr']) && $myform['type'] == 'submit')){
									$myfield[$skey] = Array("show"=>$sval['show'],"req"=>$sval['req'],"size"=>$sval['size']);
									if(!empty($attr_list[$myform['entity']][$skey]['desc'])){
										$myfield[$skey]['desc'] = $attr_list[$myform['entity']][$skey]['desc'];
									}	
									if(!empty($attr_list[$myform['entity']][$skey]['label'])){
										$myfield[$skey]['label'] = $attr_list[$myform['entity']][$skey]['label'];
									}
								}
							}
							elseif(!empty($txn_list[$myform['entity']][$skey])){
								$myfield[$skey] = Array("show"=>$sval['show'],"req"=>$sval['req'],"size"=>$sval['size']);
								$myfield[$skey]['label'] = $txn_list[$myform['entity']][$skey]['single_label'];
								if(!empty($txn_list[$myform['entity']][$skey]['desc'])){
									$myfield[$skey]['desc'] = $txn_list[$myform['entity']][$skey]['desc'];
								}
							}
							elseif(!empty($glob_list[$skey])){
								$myfield[$skey] = Array("show"=>$sval['show'],"req"=>$sval['req'],"size"=>$sval['size']);
								$myfield[$skey]['label'] = $glob_list[$skey]['label'];
								$myfield[$skey]['display_type'] = 'global';
								if(!empty($glob_list[$skey]['desc'])){
									$myfield[$skey]['desc'] = $glob_list[$skey]['desc'];
								}
							}
							if(!empty($myfield[$skey]['label'])){
								$myfield[$skey]['placeholder'] = $myfield[$skey]['label'];
							}
							if(!empty($myfield)){
								$myform['layout'][1]['rows'][$sval['row']][] = $myfield;
							}
						}
					}
				}
				ksort($myform['layout'][1]['rows']);
				if(empty($myform['page_title'])){
					$ftitle = ucwords(str_replace("_"," ",$myform['name']));
				}
				else {
					$ftitle = $myform['page_title'];
				}
				$myform['settings']['title'] = $ftitle;
				$myform['settings']['targeted_device'] = $vform['targeted_device'];
				$myform['settings']['label_position'] = $vform['label_position'];
				$myform['settings']['element_size'] = $vform['element_size'];
				$myform['settings']['display_inline'] = $vform['display_inline'];
				$myform['settings']['disable_submit'] = $vform['disable_submit'];
				$myform['settings']['submit_status'] = $vform['submit_status'];
				$myform['settings']['visitor_submit_status'] = $vform['visitor_submit_status'];
				$myform['settings']['submit_button_type'] = $vform['submit_button_type'];
				$myform['settings']['submit_button_label'] = $vform['submit_button_label'];
				$myform['settings']['submit_button_size'] = $vform['submit_button_size'];
				$myform['settings']['submit_button_block'] = $vform['submit_button_block'];
				$myform['settings']['submit_button_fa'] = $vform['submit_button_fa'];
				$myform['settings']['submit_button_fa_size'] = $vform['submit_button_fa_size'];
				$myform['settings']['submit_button_fa_pos'] = $vform['submit_button_fa_pos'];
				$myform['settings']['disable_after'] = $vform['disable_after'];
				$myform['settings']['confirm_method'] = $vform['confirm_method'];
				$myform['settings']['confirm_url'] = $vform['confirm_url'];
				$myform['settings']['enable_ajax'] = $vform['enable_ajax'];
				$myform['settings']['after_submit'] = $vform['after_submit'];
				$myform['settings']['schedule_start'] = $vform['schedule_start'];
				$myform['settings']['schedule_end'] = $vform['schedule_end'];
				$myform['settings']['enable_operators'] = $vform['enable_operators'];
				$myform['settings']['ajax_search'] = $vform['ajax_search'];
				$myform['settings']['result_templ'] = $vform['result_templ'];
				$myform['settings']['result_fields'] = $vform['result_fields'];
				if(!empty($vform['incl_select2'])){
					$myform['settings']['incl_select2'] = 1;
				}	
				$myform['settings']['noresult_msg'] = $vform['noresult_msg'];
				$myform['settings']['honeypot'] = $vform['honeypot'];
				$myform['settings']['view_name'] = $vform['view_name'];

				$form_data = array(
						'post_status' => 'publish',
						'post_type' => 'emd_form',
						'post_title' => $ftitle,
						'post_content' => wp_slash(json_encode($myform,true)),
						'comment_status' => 'closed'
						);
				$id = wp_insert_post($form_data);
				if(!empty($id)){
					update_option('emd_form_id_' . $myform['name'],$id);
				}
			}
			else {
				$saved_forms[$kform]['settings']['result_templ'] = $vform['result_templ'];
				$saved_forms[$kform]['settings']['result_fields'] = $vform['result_fields'];
				if(!empty($vform['incl_select2'])){
					$saved_forms[$kform]['settings']['incl_select2'] = 1;
				}	
				foreach($post_forms as $myp_form){
					$fcontent = json_decode($myp_form->post_content,true);
					if($app == $fcontent['app'] && $kform == $fcontent['name']){
						$form_id = $myp_form->ID;
						break;
					}
				}
				wp_update_post(Array('ID' => $form_id,'post_content'=> wp_slash(json_encode($saved_forms[$kform],true))));
			}
		}
	}
}


add_action('emd_show_forms_lite_page','emd_show_forms_lite_page',1);
/**
 * Show forms list page
 *
 * @param string $app
 * @since WPAS 4.4
 *
 * @return html page content
 */
function emd_show_forms_lite_page($app){
	global $title;
	echo '<div class="wrap">';
	echo '<h2><span style="padding-right:10px;">' .  $title . "</span>"; 
	$create_url = '#'; 
	echo '<a href="' . $create_url . '" class="button btn-primary button-primary upgrade-pro">' . esc_html('Create New', 'emd-plugins') . '</a>';
	echo '<a href="#" class="add-new-h2 upgrade-pro" style="padding:6px 10px;">' . esc_html('Import', 'emd-plugins') . '</a>';
	echo '<a href="#" class="add-new-h2 upgrade-pro" style="padding:6px 10px;">' . esc_html('Export', 'emd-plugins') . '</a>';
	echo '</h2>';
	echo '<p>' . __('Emd Form Builder makes it easy to create simple or advanced forms with a few clicks.','emd-plugins') . ' <a href="https://emdplugins.com/best-form-builder-for-wordpress/?pk_campaign=' . $app . '&pk_kwd=emdformbuilderpagelink" target="_blank">' . __('Click here to learn more.','emd-plugins') . '</a></p>';
	echo '<style>.tablenav.top{display:none;}</style>';
	if(!empty($_POST['submit']) && !empty($_POST['submit_settings'])){
		emd_form_builder_lite_save_settings($app);
	}
	elseif(!empty($_GET['edit']) && $_GET['edit'] == 'layout' && !empty($_GET['form_id'])){
		emd_form_builder_lite_layout($app,(int) $_GET['form_id']);
	}
	elseif(!empty($_GET['edit']) && $_GET['edit'] == 'settings' && !empty($_GET['form_id'])){
		emd_form_builder_lite_settings('edit',$app, (int) $_GET['form_id']);
	}
	else {
		$list_table = new Emd_List_Table($app,'form',0);
		$list_table->prepare_items();
		?>
			<div class="emd-form-list-admin-content">
			<form id="emd-form-list-table" method="get" action="<?php echo admin_url( 'admin.php?page=' . $app . '_forms'); ?>">
			<input type="hidden" name="page" value="<?php echo $app . '_forms';?>"/>
			<?php $list_table->views(); ?>
			<?php $list_table->display(); ?>
			</form>
			</div>
			<?php
	}
}
function emd_form_builder_lite_layout($app,$form_id,$from='admin'){
	$myform = get_post($form_id);
	$fcontent = json_decode($myform->post_content,true);
	$fentity = $fcontent['entity'];
	$pcount = count($fcontent['layout']);
	$htmlcount = 1;
	//var_dump($fcontent['layout']);
	foreach($fcontent['layout'] as $kpage => $cpage){
		if(!empty($cpage['rows'])){
			foreach($cpage['rows'] as $krow => $crow){
				foreach($crow as $fcount => $field){
					foreach($field as $kfield => $cfield){
						if(preg_match('/^html/',$kfield)){
							$htmlcount ++;
						} 
					}
				}
			}
		}
	}
	echo '<div class="updated is-dismissible notice emd-form-save-success" style="display:none;"><p><strong>' . __('Saved successfully.','emd-plugins') . '</strong></p></div>';
	echo '<div class="emd-form-builder';
	if($from == 'front'){
		echo ' emd-frontend';
	}
	echo '">';
	echo '<form name="emd-form-builder" id="emd-form-builder-form" method="post" data-id="' . $form_id . '">';
	echo '<input type="hidden" name="id" value="' . $form_id . '">';
	echo '<div class="emd-form-builder-top"><div class="emd-form-builder-center">' . __('Editing Form:','emd-plugins') . ' ' . $myform->post_title . '</div>
		<div class="emd-form-builder-right"><a href="#" id="emd-form-save">' . __('Save','emd-plugins') . '</a>
		<a href="#" id="emd-form-exit"><span class="field-icons times"></span></a>
		</div></div>';
	echo '<div class="emd-form-builder-sidebar-content">';
	echo '<div class="emd-form-builder-sidebar">
		<div class="emd-form-builder-add-row-wrap">
		<button class="emd-form-builder-add-row" data-app="' . $app . '"' . ' data-entity="' . $fentity . '" title="' . __('Click here to add row','emd-plugins') . '">' . __('Add Row','emd-plugins') . '</button>
		</div>
		<div class="emd-form-builder-html-fields">
		<button class="emdform-hr-button" data-app="' . $app . '"' . ' data-entity="' . $fentity . '" title="' . __('Click here to add','emd-plugins') . '">' . __('Divider','emd-plugins') . '</button>
		<button class="emdform-html-button upgrade-pro" data-app="' . $app . '"' . ' data-entity="' . $fentity . '" data-field="html' . $htmlcount . '" title="' . __('Click here to add','emd-plugins') . '">' . __('HTML','emd-plugins') . '</button>
		</div>
		<div class="emd-form-builder-add-page-wrap">';
	if($fcontent['type'] == 'submit'){	
		echo '<button class="emd-form-builder-add-page upgrade-pro" data-app="' . $app . '"' . ' data-entity="' . $fentity . '" title="' . __('Click here to add a form wizard page','emd-plugins') . '">' . __('Add Page','emd-plugins') . '</button>';
	}
	echo '<div class="emd-form-builder-pages">
		<div class="emd-form-builder-page active" id="emd-form-builder-page-1" title="' . __('Click to go to this page','emd-plugins') . '">' . 
		__('Page 1','emd-plugins') . '</div>';
	if($pcount > 1){
		for($i=2;$i<=$pcount;$i++){
			echo '<div class="emd-form-builder-page" id="emd-form-builder-page-' . $i . '" title="' . __('Click to go to this page','emd-plugins') . '">' .
				'<a href="#" class="emd-form-builder-page-delete" title="' . __('Delete','emd-plugins') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>' .
				sprintf(__('Page %s','emd-plugins'),$i) . '</div>';
		}
	}	
	echo '</div>
		</div>';
	echo '<div class="emd-form-builder-tabs">
		<a href="#" class="emd-form-builder-tab active" id="fields">' . __('Fields','emd-plugins') . '</a>
		<a href="#" class="emd-form-builder-tab" id="settings">' . __('Settings','emd-plugins') . '</a>
		</div>
		<div class="emd-form-builder-fields">';
	emd_form_builder_lite_fields($app,$fentity,$fcontent);
	echo '</div>
		<div class="emd-form-builder-fields-settings" style="display:none;">';
	emd_form_builder_lite_get_form_field_settings($app,$fentity,$fcontent);
	echo '</div>';
	echo '</div>';
	echo '<div class="emd-form-builder-content-wrap">
		<div class="emd-form-builder-content">';
	emd_form_builder_lite_get_form_layout($app,$fentity,$myform->post_title,$fcontent);
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '</form>';
	echo '</div>';


}
function emd_form_builder_lite_fields($app,$fentity,$fcontent){
	$attr_list = get_option($app . '_attr_list');
	$txn_list = get_option($app . '_tax_list', Array());
	$rel_list = get_option($app . '_rel_list', Array());
	$ent_list = get_option($app . '_ent_list',Array());
	$glob_init_list = get_option($app . '_glob_init_list',Array());
	$glob_forms_init_list = get_option($app . '_glob_forms_init_list',Array());
	$glob_list = Array();
	if(!empty($glob_forms_init_list[$fcontent['name']])){
		foreach($glob_forms_init_list[$fcontent['name']] as $kglob => $vglob){
			$non_field_confs = Array('captcha','noaccess_msg','error_msg','success_msg','login_reg','csrf','btn', 'noresult_msg');
			if(!in_array($kglob,$non_field_confs)){
				$glob_list[] = $kglob;
			}
		}
	}
	$ext_list = apply_filters('emd_ext_form_rels',Array(),$app);
	$cust_fields = Array();
	$flist = Array();
	foreach($fcontent['layout'] as $pid => $pcont){
		if(!empty($pcont['rows'])){
			foreach($pcont['rows'] as $rid => $rcont){
				foreach($rcont as $r => $row){
					foreach($row as $f => $fval){
						if(!empty($fval['show'])){
							$flist[] = $f;
						}
					}  
				}
			}
		}
	}
	$blt_fields = Array('blt_title','blt_content','blt_excerpt');
	$blts = Array();
	foreach($blt_fields as $myblt){
		if(!empty($ent_list[$fentity]['req_blt'][$myblt])){
			$blts[$myblt] = $ent_list[$fentity]['req_blt'][$myblt]['msg'];
		}
		elseif(!empty($ent_list[$fentity]['blt_list'][$myblt])){
			$blts[$myblt] = $ent_list[$fentity]['blt_list'][$myblt];
		}	
	}
	if(!empty($glob_init_list)){
		foreach($glob_init_list as $kglob => $vglob){
			if(!empty($vglob['in_form'])){
				$glbs[$kglob] = $vglob;
			}
		}
	}
	if(post_type_supports($fentity, 'custom-fields') == 1){
		$cust_fields = apply_filters('emd_get_cust_fields', $cust_fields, $fentity);
	}
	$has_user = 0;
	$titl = __('Drag this field to a row.','emd-plugins');
	if(!empty($attr_list[$fentity])){
		echo '<div class="emd-form-builder-fields-group"><a class="emd-form-builder-fields-heading attr"><span>Attributes</span><span class="emd-formbuilder-icons angle-down"></span></a>';
		echo '<div class="emd-form-builder-fields">';
		if(!empty($blts)){
			foreach($blts as $kblt => $vblt){
				echo '<button class="emdform-field-button emd-attr';
				if(in_array($kblt,$flist)){
					echo ' disabled';
				}
				elseif(!in_array($kblt,$glob_list)){
					echo ' upgrade-pro';
					$titl = __('Drag this field to a row - available in premium edition.','emd-plugins');
				}
				echo '" id="' . $kblt . '-btn" data-field="' . $kblt . '" title="' . $titl . '">' . $vblt . '</button>';
			}
		}
		foreach($attr_list[$fentity] as $kattr => $vattr){
			if($vattr['display_type'] == 'user'){
				$has_user = 1;
			}
			if(!preg_match('/^wpas_/',$kattr) && !(!empty($vattr['uniqueAttr']) && $vattr['display_type'] == 'hidden' && $fcontent['type'] == 'submit') && !($fcontent['type'] == 'search' && in_array($vattr['display_type'], Array('file','image','plupload_image','thickbox_image')))){
				echo '<button class="emdform-field-button ';
				if(in_array($kattr,$flist)){
					echo ' disabled';
				}
				elseif(!in_array($kattr,$glob_list)){
					echo ' upgrade-pro';
					$titl = __('Drag this field to a row - available in premium edition.','emd-plugins');
				}
				echo ' emd-attr" id="' . $kattr . '-btn" data-field="' . $kattr . '" title="' . $titl . '">' . $vattr['label'] . '</button>';
			}
		}
		if(!empty($glbs)){
			foreach($glbs as $kglb => $vglb){
				echo '<button class="emdform-field-button ';
				if(in_array($kglb,$flist)){
					echo ' disabled';
				}
				echo ' emd-attr" id="' . $kglb . '-btn" data-field="' . $kglb . '" title="' . __('Drag this field to a row','emd-plugins') . '">' . $vglb['label'] . '</button>';
			}
		}
		echo '</div>';
		echo '</div>';
	}	
	if(!empty($txn_list[$fentity])){
		echo '<div class="emd-form-builder-fields-group"><a class="emd-form-builder-fields-heading tax"><span>Taxonomies</span><span class="emd-formbuilder-icons angle-down"></span></a>';
		echo '<div class="emd-form-builder-fields">';
		foreach($txn_list[$fentity] as $ktxn => $vtxn){
			echo '<button class="emdform-field-button ';
			if(in_array($ktxn,$flist)){
				echo ' disabled';
			}
			elseif(!in_array($ktxn,$glob_list)){
				echo ' upgrade-pro';
				$titl = __('Drag this field to a row - available in premium edition.','emd-plugins');
			}
			echo ' emd-attr" id="' . $ktxn . '-btn" data-field="' . $ktxn . '" title="' . $titl . '">' . $vtxn['single_label'] . '</button>';
		}
		echo '</div>';
		echo '</div>';
	}	
	if(!empty($rel_list)){
		$rels = Array();
		foreach($rel_list as $krel => $vrel){
			if($fentity == $vrel['from']){
				$rels[] = Array('key' => $krel, 'label' => $vrel['from_title']);
			}
			elseif($fentity == $vrel['to']){
				$rels[] = Array('key' => $krel, 'label' => $vrel['to_title']);
			}
		}
		if(!empty($rels)){
			echo '<div class="emd-form-builder-fields-group"><a class="emd-form-builder-fields-heading relate"><span>Relationships</span><span class="emd-formbuilder-icons angle-down"></span></a>';
			echo '<div class="emd-form-builder-fields">';
			foreach($rels as $myrel){
				echo '<button class="emdform-field-button ';
				if(in_array($myrel['key'],$flist)){
					echo ' disabled';
				}
				elseif(!in_array($myrel['key'],$glob_list) && empty($ext_list[$myrel['key']])){
					echo ' upgrade-pro';
					$titl = __('Drag this field to a row - available in premium edition.','emd-plugins');
				}
				echo ' emd-attr" id="' . $myrel['key'] . '-btn" data-field="' . $myrel['key'] . '" title="' . $titl . '">' . $myrel['label'] . '</button>';
			}
			echo '</div>';
			echo '</div>';
		}
	}
	if(!empty($has_user)){
		echo '<div class="emd-form-builder-fields-group"><a class="emd-form-builder-fields-heading comp"><span>Components</span><span class="emd-formbuilder-icons angle-down"></span></a>';
		echo '<div class="emd-form-builder-fields">';
		echo '<button class="emdform-field-button ';
		if(in_array('login_box_username',$flist)){
			echo ' disabled';
		}
		else {
			echo ' upgrade-pro';
			$titl = __('Drag this field to a row - available in premium edition.','emd-plugins');
		}
		echo ' emd-attr" id="login_box-btn" data-field="login_box_username" title="' . $titl . '">' . __('Login Box','emd-plugins') . '</button>';
		echo '</div>';
		echo '</div>';
	}	
}	
function emd_form_builder_lite_get_form_layout($app,$fentity,$ftitle,$fcontent){
	$attr_list = get_option($app . '_attr_list',Array());
	$ent_list = get_option($app . '_ent_list',Array());
	$txn_list = get_option($app . '_tax_list', Array());
	$rel_list = get_option($app . '_rel_list', Array());
	$glob_list = get_option($app . '_glob_init_list', Array());
	//var_dump($fcontent);
	//layout/page#/row#
	$count_pages = count($fcontent['layout']);
	echo '<div class="emd-form-page-list">';
	if(!empty($fcontent['layout'])){
		foreach($fcontent['layout'] as $kpage => $cpage){
			if(!empty($cpage['rows'])){
				echo '<div class="emd-form-page-wrap" id="emd-form-page-' . $kpage . '" data-page="' . $kpage . '" data-app="' . $app . '" data-entity="' . $fentity . '"';
				if($count_pages > 1){
					echo ' style="display:none;"';
				}
				echo '>';
				echo '<input type="hidden" class="emd-form-page-hidden" name="layout[]" value="page">';
				foreach($cpage['rows'] as $krow => $crow){
					echo '<div class="emd-form-row emd-row">';
					echo '<a href="#" class="emd-form-row-delete" title="' . __('Delete','emd-plugins') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>';
					echo '<span class="emd-form-row-info">' . __('Drag to reorder','emd-plugins') . '</span>';
					echo '<div class="emd-form-row-holder" data-app="' . $app . '" data-entity="' . $fentity . '" data-row="' . $krow . '">';
					echo '<input type="hidden" name="layout[]" value="row">';
					foreach($crow as $fcount => $field){
						foreach($field as $kfield => $cfield){
							if(!empty($cfield['show']) && !in_array($kfield, Array('login_box_password','login_box_reg_password','login_box_reg_confirm_password','login_box_reg_username'))){ 
								//if this field is an html field
								if(!empty($cfield['value'])){
									$cfield['size'] = 12;
								}
								if(empty($cfield['size'])){
									$cfield['size'] = 12;
								}
								echo '<div class="emd-form-field emd-col emd-md-' . $cfield['size'] . '" data-size="'.  $cfield['size'] . '" data-field="' . $kfield . '">';
								echo '<a href="#" class="emd-form-field-delete" title="' . __('Delete','emd-plugins') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>';
								echo '<span class="emd-form-field-info" style="cursor:pointer;">' . __('Drag to reorder / Click for settings','emd-plugins') . '</span>';
								if(!empty($attr_list[$fentity]) && in_array($kfield,array_keys($attr_list[$fentity]))){
									$cfield['display_type'] = $attr_list[$fentity][$kfield]['display_type'];
								}
								if(!empty($attr_list[$fentity][$kfield]['uniqueAttr'])){
									$cfield['uniqueAttr'] = $attr_list[$fentity][$kfield]['uniqueAttr'];
								}	
								if($kfield == 'hr'){
									emd_form_builder_lite_layout_hr($kfield);
								}
								elseif(preg_match('/^html/',$kfield)){
									emd_form_builder_lite_layout_html($kfield,$cfield);
								}
								else {
									if(!empty($fcontent['type'])){
										$cfield['form_type'] = $fcontent['type'];
									}
									if(in_array($kfield,array_keys($attr_list[$fentity]))){
										$cfield['display_type'] =  $attr_list[$fentity][$kfield]['display_type'];
									}
									emd_form_builder_lite_layout_field_top_bottom($kfield,$cfield,'top');
									if(in_array($kfield,Array('blt_title','blt_content','blt_excerpt'))){
										echo emd_form_builder_lite_blt_fields($kfield,$cfield);
									}
									elseif(preg_match('/^login_box_/',$kfield)){
										echo emd_form_builder_lite_login_box($kfield,$cfield);
									}
									elseif(!empty($attr_list[$fentity]) && in_array($kfield,array_keys($attr_list[$fentity]))){
										$cfield['display_type'] = $attr_list[$fentity][$kfield]['display_type'];
										if(!empty($attr_list[$fentity][$kfield]['options'])){
											$cfield['options'] = $attr_list[$fentity][$kfield]['options'];
										}
										echo emd_form_builder_lite_attr_fields($kfield,$cfield);
									}
									elseif(!empty($txn_list[$fentity]) && in_array($kfield,array_keys($txn_list[$fentity]))){
										echo emd_form_builder_lite_txn_fields($kfield,$cfield);
									}
									elseif(!empty($rel_list) && array_key_exists($kfield,$rel_list)){
										echo emd_form_builder_lite_rel_fields($kfield,$cfield);
									}
									elseif(!empty($glob_list) && array_key_exists($kfield,$glob_list)){
										$cfield['display_type'] = 'global';
										echo emd_form_builder_lite_attr_fields($kfield,$cfield);
									}
									if(!empty($attr_list[$fentity][$kfield]['display_type']) && in_array($attr_list[$fentity][$kfield]['display_type'], Array('checkbox'))){
										emd_form_builder_lite_layout_field_top_bottom($kfield,$cfield,'bottom');
										echo '</div>';
									}
								}
								echo '</div>';
							}
						}
					}
					echo '</div>';
					echo '</div>';
				}
				echo '</div>';
			}
		}
	}
	else {
		echo '<div class="emd-form-page-wrap" id="emd-form-page-1" data-page="1" data-app="' . $app . '" data-entity="' . $fentity . '">';
		echo '<input type="hidden" class="emd-form-page-hidden" name="layout[]" value="page">';
		echo '<div class="emd-form-row init">
			<a href="#" class="emd-form-row-delete" title="' . __('Delete','emd-plugins') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-row-info">' . __('Drag to reorder','emd-plugins') . '</span>
			<div class="emd-form-row-holder" data-app="' . $app . '" data-entity="' . $fentity . '" data-row="0">
			<input type="hidden" name="layout[]" value="row">
			<div class="emd-form-insert-row">' . __('Drag fields here','emd-plugins') . 
			'</div>
			</div>
			</div>';

	}
	echo '</div>';
}
function emd_form_builder_lite_layout_html($kfield,$cfield){
	echo '<div class="emd-form-group">
		<input type="hidden" name="layout[]" value="' . $kfield . '">
		<input type="text" name="' . $kfield . '" id="' . $kfield . '" class="text emd-input-md emd-form-control html-code" placeholder="' . __('HTML','emd-plugins') . '" disabled/>
		</div>';
}
function emd_form_builder_lite_layout_hr($kfield){
	echo '<hr class="emd-form-row-hr">
		<input type="hidden" name="layout[]" value="hr">';
}
function emd_form_builder_lite_layout_field_top_bottom($kfield,$cfield,$loc){
	if(!empty($cfield['display_type']) && in_array($cfield['display_type'], Array('checkbox')) && $loc == 'top'){
		echo '<div class="emd-form-group">';
		echo '<input type="hidden" name="layout[]" value="' . $kfield . '">';
	}
	else {
		if($loc == 'top'){
			echo '<div class="emd-form-group">';
			if($kfield == 'login_box_username'){
				echo '<input type="hidden" name="layout[]" value="login_box_username">';
				echo '<input type="hidden" name="layout[]" value="login_box_password">';
				echo '<input type="hidden" name="layout[]" value="login_box_reg_username">';
				echo '<input type="hidden" name="layout[]" value="login_box_reg_password">';
				echo '<input type="hidden" name="layout[]" value="login_box_reg_confirm_password">';
			}
			else {
				echo '<input type="hidden" name="layout[]" value="' . $kfield . '">';
			}
		}
		echo '<label class="';
		if(!empty($cfield['display_type']) && in_array($cfield['display_type'],Array('checkbox'))){
			echo 'emd-form-check-label" for="' . $kfield . '">';
		}
		else {
			echo 'emd-control-label" for="' . $kfield . '">';
		}
		echo '<span id="label_' . $kfield . '">';
		if(!empty($cfield['label']) && $kfield != 'login_box_username'){
			echo $cfield['label'];
		}
		else {
			$cfield['label'] = '';
		}
		echo '</span>';	
		echo '<span style="display: inline-flex;right: 0px; position: relative; top:-6px;">';
		echo '<a data-html="true" href="#" data-toggle="tooltip"';
		if(empty($cfield['desc'])){
			echo ' style="display:none;"';
		}
		else {
			echo ' title="' . $cfield['desc'] . '"';
		}
		echo ' id="info_' . $kfield . '" class="helptip"';
		echo '><span class="field-icons info"></span></a>';
		echo '<a href="#" data-html="true" data-toggle="tooltip" title="' . $cfield['label'] . ' field is required" id="req_' . $kfield . '" class="helptip"';
		if (empty($cfield['req'])) { 
			echo ' style="display:none;"';
		}
		echo '>';
		echo '<span class="field-icons required"></span>
			</a>';
		echo '</span>';
		echo '</label>';
	}
}
function emd_form_builder_lite_blt_fields($kfield,$cfield){
	if($kfield == 'blt_title'){
		$blt_lay = '<input type="text" name="' . $kfield . '" id="' . $kfield . '" class="text emd-input-md emd-form-control" placeholder="' . $cfield['placeholder'] . '" disabled/>';
	}	
	else {
		$blt_lay = '<textarea name="' . $kfield . '" id="' . $kfield . '" class="control wyrb" placeholder="' . $cfield['placeholder'] . '" disabled></textarea>';
	}
	$blt_lay .= '</div>';
	return $blt_lay;
}
function emd_form_builder_lite_attr_fields($kfield,$cfield){
	$attr_lay = '';
	switch($cfield['display_type']){
		case 'select':
		case 'select_advanced':
			$attr_lay .= '<div class="dropdown">';
			$attr_lay .= '<input type="text" name="' . $kfield . '" id="' . $kfield . '" class="text emd-input-md emd-form-control" placeholder="' . $cfield['placeholder'] . '" disabled>';
			$attr_lay .= '<div class="emd-arrow"></div>';
			$attr_lay .= '</div>';
			break;
		case 'wysiwyg':
			$attr_lay .= '<textarea name="' . $kfield . '" id="' . $kfield . '" class="control wyrb" placeholder="' . $cfield['placeholder'] . '" disabled></textarea>';
			break;
		case 'checkbox':
			$attr_lay .= '<input type="checkbox" name="' . $kfield . '" id="' . $kfield . '" class="emd-checkbox emd-input-md emd-form-control" disabled>';
			break;
		case 'radio':
			if(!empty($cfield['options'])){
				foreach($cfield['options'] as $kopt => $vopt){
					$attr_lay .= '<div class="emd-form-check">';
					$attr_lay .= '<input type="radio" name="' . $kfield . '" id="' . $kfield . '_' . $kopt . '" class="emd-radio emd-input-md emd-form-control" disabled>';
					$attr_lay .= '<label class="emd-form-check-label" for="' . $kfield . '_' . $kopt . '">' . $vopt  . '</label>';
					$attr_lay .= '</div>';
				}
			}
			break;
		case 'checkbox_list':
			if(!empty($cfield['options'])){
				foreach($cfield['options'] as $kopt => $vopt){
					$attr_lay .= '<div class="emd-form-check">';
					$attr_lay .= '<input type="checkbox" name="' . $kfield . '" id="' . $kfield . '_' . $kopt . '" class="emd-checkbox emd-input-md emd-form-control" disabled>';
					$attr_lay .= '<label class="emd-form-check-label" for="' . $kfield . '_' . $kopt . '">' . $vopt  . '</label>';
					$attr_lay .= '</div>';
				}
			}
			break;
		case 'hidden':
			if($cfield['form_type'] == 'search'){
				$attr_lay .= '<input type="text" name="' . $kfield . '" id="' . $kfield . '" class="text emd-input-md emd-form-control" placeholder="' . $cfield['placeholder'] . '" disabled/>';
			}
			elseif(empty($cfield['uniqueAttr'])){
				$attr_lay .= '<div>' . __('Hidden field','emd-plugins') . '</div>';
			}
			break;
		case 'global':
			$attr_lay .= '<div>' . __('Global field','emd-plugins') . '</div>';
			break;
		case 'text':
		default:
			$attr_lay .= '<input type="text" name="' . $kfield . '" id="' . $kfield . '" class="text emd-input-md emd-form-control" placeholder="' . $cfield['placeholder'] . '" disabled/>';
			break;
	}
	if(!in_array($cfield['display_type'], Array('checkbox'))){
		$attr_lay .= '</div>';
	}
	return $attr_lay;
}
function emd_form_builder_lite_txn_fields($kfield,$cfield){
	$txn_lay = '<div class="dropdown">';
	$txn_lay .= '<input type="text" name="' . $kfield . '" id="' . $kfield . '" class="text emd-input-md emd-form-control" placeholder="' . $cfield['placeholder'] . '" disabled>';
	$txn_lay .= '<div class="emd-arrow"></div>';
	$txn_lay .= '</div>';
	$txn_lay .= '</div>';
	return $txn_lay;
}
function emd_form_builder_lite_rel_fields($kfield,$cfield){
	$rel_lay = '<div class="dropdown">';
	$rel_lay .= '<input type="text" name="' . $kfield . '" id="' . $kfield . '" class="text emd-input-md emd-form-control" placeholder="' . $cfield['placeholder'] . '" disabled>';
	$rel_lay .= '<div class="emd-arrow"></div>';
	$rel_lay .= '</div>';
	$rel_lay .= '</div>';
	return $rel_lay;
}
add_action('wp_ajax_emd_form_builder_lite_get_field', 'emd_form_builder_lite_get_field');
function emd_form_builder_lite_get_field(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	if(!empty($_POST['app']) && !empty($_POST['entity']) && !empty($_POST['field'])){	
		$app = sanitize_text_field($_POST['app']);
		$fentity = sanitize_text_field($_POST['entity']);
		$kfield = sanitize_text_field($_POST['field']);
		$form_id = (int) $_POST['form_id'];
		$attr_list = get_option($app . '_attr_list',Array());
		$ent_list = get_option($app . '_ent_list',Array());
		$txn_list = get_option($app . '_tax_list', Array());
		$rel_list = get_option($app . '_rel_list', Array());
		$glob_list = get_option($app . '_glob_init_list', Array());
		if(!empty($glob_list[$kfield])){
			$cfield['label'] = $glob_list[$kfield]['label'];
			$cfield['display_type'] = 'global';
			$cfield['size'] = 12;
		}
		$cfield = emd_form_builder_lite_get_cfield($fentity,$kfield,$attr_list,$ent_list,$txn_list,$rel_list);
		ob_start();
		echo '<div class="emd-form-field emd-col emd-md-' . $cfield['size'] . '" data-size="'.  $cfield['size'] . '" ui-sortable-handle ui-draggable ui-draggable-handle" data-field="' . $kfield . '">';
		echo '<a href="#" class="emd-form-field-delete" title="' . __('Delete','emd-plugins') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>';
		echo '<span class="emd-form-field-info" style="cursor:pointer;">' . __('Drag to reorder / Click for settings','emd-plugins') . '</span>';
		if(in_array($kfield,array_keys($attr_list[$fentity]))){
			$cfield['display_type'] =  $attr_list[$fentity][$kfield]['display_type'];
		}
		emd_form_builder_lite_layout_field_top_bottom($kfield,$cfield,'top');
		if(in_array($kfield,Array('blt_title','blt_content','blt_excerpt'))){
			echo emd_form_builder_lite_blt_fields($kfield,$cfield);
		}
		elseif(preg_match('/^login_box_/',$kfield)){
			echo emd_form_builder_lite_login_box($kfield,$cfield);
		}
		elseif(!empty($attr_list[$fentity]) && in_array($kfield,array_keys($attr_list[$fentity]))){
			$cfield['display_type'] = $attr_list[$fentity][$kfield]['display_type'];
			if(!empty($attr_list[$fentity][$kfield]['options'])){
				$cfield['options'] = $attr_list[$fentity][$kfield]['options'];
			}
			if(!empty($attr_list[$fentity][$kfield]['uniqueAttr'])){
				$cfield['uniqueAttr'] = $attr_list[$fentity][$kfield]['uniqueAttr'];
			}
			$myform = get_post($form_id);
			$fcontent = json_decode($myform->post_content,true);
			if(!empty($fcontent['type'])){
				$cfield['form_type'] = $fcontent['type'];
			}
			echo emd_form_builder_lite_attr_fields($kfield,$cfield);
		}
		elseif(!empty($txn_list[$fentity]) && in_array($kfield,array_keys($txn_list[$fentity]))){
			echo emd_form_builder_lite_txn_fields($kfield,$cfield);
		}
		elseif(!empty($rel_list) && array_key_exists($kfield,$rel_list)){
			echo emd_form_builder_lite_rel_fields($kfield,$cfield);
		}
		elseif(!empty($glob_list) && array_key_exists($kfield,$glob_list)){
			$cfield['display_type'] = 'global';
			echo emd_form_builder_lite_attr_fields($kfield,$cfield);
		}
		if(!empty($attr_list[$fentity][$kfield]['display_type']) && in_array($attr_list[$fentity][$kfield]['display_type'],Array('checkbox'))){
			emd_form_builder_lite_layout_field_top_bottom($kfield,$cfield,'bottom');
		}
		echo '</div>';
		$field = ob_get_clean();
		wp_send_json_success(array('field' => $field));
	}
	else {
		die();
	}
}
function emd_form_builder_lite_get_form_field_settings($app,$fentity,$fcontent){
	$attr_list = get_option($app . '_attr_list');
	$txn_list = get_option($app . '_tax_list', Array());
	$rel_list = get_option($app . '_rel_list', Array());
	$ent_list = get_option($app . '_ent_list',Array());
	$glob_list = get_option($app . '_glob_init_list',Array());
	$all_fields = Array();
	$html_fields = Array();
	$page_fields = Array();
	$login_fields = Array();
	$fields = Array('req','label','size','desc','display_type','placeholder','css_class','login_label','reg_label','username_label','password_label','redirect_link','enable_registration','reg_username_label','reg_password_label','reg_confirm_password_label');
	foreach($fcontent['layout'] as $pid => $pcont){
		if(!empty($pcont['rows'])){
			foreach($pcont['rows'] as $rid => $rcont){
				foreach($rcont as $r => $row){
					foreach($row as $f => $fval){
						if(preg_match('/^html/',$f)){
							$html_fields[$f] = $fval;
						}
						foreach($fields as $myfield){
							if(!empty($fval[$myfield]) && preg_match('/^login_box/',$f)){
								$login_fields[$f][$myfield] = $fval[$myfield];
							}
							elseif(!empty($fval[$myfield])){
								$all_fields[$f][$myfield] = $fval[$myfield];
							}
						}
					}  
				}
			}
		}
		$page_fields[$pid]['step_title'] = '';
		$page_fields[$pid]['step_desc'] = '';
		if(!empty($pcont['step_title'])){
			$page_fields[$pid]['step_title'] = $pcont['step_title'];
		}
		if(!empty($pcont['step_desc'])){
			$page_fields[$pid]['step_desc'] = $pcont['step_desc'];
		}
	}
	$blt_fields = Array('blt_title','blt_content','blt_excerpt');
	foreach($blt_fields as $myblt){
		if(!empty($ent_list[$fentity]['req_blt'][$myblt]) && empty($all_fields[$myblt]['label'])){
			$all_fields[$myblt]['label'] = $ent_list[$fentity]['req_blt'][$myblt]['msg'];
			$all_fields[$myblt]['desc'] = $all_fields[$myblt]['label'];
		}
		elseif(!empty($ent_list[$fentity]['blt_list'][$myblt]) && empty($all_fields[$myblt]['label'])){
			$all_fields[$myblt]['label'] = $ent_list[$fentity]['blt_list'][$myblt];
			$all_fields[$myblt]['desc'] = $all_fields[$myblt]['label'];
		}	
	}
	$has_user = 0;
	if(!empty($attr_list[$fentity])){
		foreach($attr_list[$fentity] as $kattr => $vattr){
			if($vattr['display_type'] == 'user'){
				$has_user = 1;
			}
			if(!preg_match('/^wpas_/',$kattr)){
				if(empty($all_fields[$kattr]['label'])){
					$all_fields[$kattr]['label'] = $vattr['label'];
				}
				if(empty($all_fields[$kattr]['desc']) && !empty($vattr['desc'])){
					$all_fields[$kattr]['desc'] = $vattr['desc'];
				}
				if($vattr['display_type'] == 'hidden'){
					$all_fields[$kattr]['display_type'] = 'hidden';
				}
			}
		}
	}
	if(!empty($txn_list[$fentity])){
		foreach($txn_list[$fentity] as $ktxn => $vtxn){
			if(empty($all_fields[$ktxn]['label'])){
				$all_fields[$ktxn]['label'] = $vtxn['single_label'];
			}
			if(empty($all_fields[$ktxn]['desc']) && !empty($vtxn['desc'])){
				$all_fields[$ktxn]['desc'] = $vtxn['desc'];
			}
		}
	}
	if(!empty($glob_list)){
		foreach($glob_list as $kglob => $vglob){
			if(!empty($vglob['in_form'])){
				$all_fields[$kglob]['label'] = $vglob['label'];
				$all_fields[$kglob]['display_type'] = 'global';
			}
		}
	}
	if(!empty($rel_list)){
		$rels = Array();
		foreach($rel_list as $krel => $vrel){
			if(empty($all_fields[$krel]['label'])){
				if($fentity == $vrel['from']){
					$all_fields[$krel]['label'] = $vrel['from_title'];
				}
				elseif($fentity == $vrel['to']){
					$all_fields[$krel]['label'] = $vrel['to_title'];
				}
			}
			if(empty($all_fields[$krel]['desc']) && !empty($vrel['desc'])){
				$all_fields[$krel]['desc'] = $vrel['desc'];
			}
		}
	}
	foreach($all_fields as $kfield => $vfield){
		if(empty($vfield['placeholder'])){
			$vfield['placeholder'] = $vfield['label'];
		}
		if(empty($vfield['size'])){
			$vfield['size'] = 12;
		}
		echo '<div class="emd-form-builder-field-settings-wrap emd-field-' . $kfield . '" style="display:none;">';
		if(!empty($vfield['display_type']) && $vfield['display_type'] == 'hidden' && $fcontent['type'] == 'submit'){
			echo '<input type="hidden" name="fields[' . $kfield . '][label]" class="emd-form-builder-field-label" id="emd-fbl-' . $kfield . '" value="' . $vfield['label'] . '"/>';
			echo '<div>' . __('There are no settings options for hidden fields.','emd-plugins') . '</div>';
		}
		elseif(!empty($vfield['display_type']) && $vfield['display_type'] == 'global' && $fcontent['type'] == 'submit'){
			echo '<input type="hidden" name="fields[' . $kfield . '][label]" class="emd-form-builder-field-label" id="emd-fbl-' . $kfield . '" value="' . $vfield['label'] . '"/>';
			echo '<div>' . __('There are no settings options for global fields.','emd-plugins') . '</div>';
		}
		else {
			echo '<div class="emd-form-builder-field-setting label">';
			echo '<label for="' . $kfield . '-label">' . __('Label','emd-plugins') . '</label>';
			echo '<input type="text" name="fields[' . $kfield . '][label]" class="emd-form-builder-field-label" id="emd-fbl-' . $kfield . '" value="' . $vfield['label'] . '"/>'; 
			echo '</div>';
			echo '<div class="emd-form-builder-field-setting req">';
			echo '<input type="checkbox" name="fields[' . $kfield . '][req]" class="inline emd-form-builder-field-req" id="emd-fbr-' . $kfield . '" value=1';
			if(!empty($vfield['req'])){
				echo ' checked';
			}
			echo '>'; 
			echo '<label class="inline" for="' . $kfield . '-req">' . __('Required','emd-plugins') . '</label>';
			echo '</div>';
			echo '<div class="emd-form-builder-field-setting size">';
			echo '<label for="' . $kfield . '-size">' . __('Size','emd-plugins') . '</label>';
			echo '<select name="fields[' . $kfield . '][size]" class="emd-form-builder-field-size" id="emd-fbs-' . $kfield . '">';
			for($i=1;$i<=12;$i++){
				echo '<option value=' . $i;
				if(!empty($vfield['size']) && $vfield['size'] == $i){
					echo ' selected';
				}
				echo '>' . $i . '</option>';
			}
			echo '</select>';
			echo '<p class="desc">' . __('Size column refers to the form elements length relative to the other elements in the same row. Total element size in each row can not exceed 12 units.','emd-plugins') . '</p>';
			echo '</div>';
			echo '<div class="emd-form-builder-field-setting desc">';
			echo '<label for="' . $kfield . '-desc">' . __('Description','emd-plugins') . '</label>';
			echo '<textarea name="fields[' . $kfield . '][desc]" id="emd-fbd-' . $kfield . '" class="emd-form-builder-field-desc" rows=3>'; 
			if(!empty($vfield['desc'])){
				echo $vfield['desc'];
			}
			echo '</textarea>';
			echo '</div>';
			echo '<div class="emd-form-builder-field-setting placeholder">';
			echo '<label for="' . $kfield . '-placeholder">' . __('Placeholder','emd-plugins') . '</label>';
			echo '<input type="text" name="fields[' . $kfield . '][placeholder]" class="emd-form-builder-field-placeholder" id="emd-fbp-' . $kfield . '" value="' . $vfield['placeholder'] . '"/>'; 
			echo '</div>';
			echo '<div class="emd-form-builder-field-setting css">';
			echo '<label for="' . $kfield . '-css-class">' . __('Css Class','emd-plugins') . '</label>';
			echo '<input type="text" name="fields[' . $kfield . '][css_class]" class="emd-form-builder-field-css" id="emd-fbc-' . $kfield . '"';
			if(!empty($vfield['css_class'])){
				echo ' value="' . $vfield['css_class'] . '"';
			}
			echo '/>'; 
			echo '</div>';
		}
		echo '</div>';
	}	
	foreach($html_fields as $hfield => $hvfield){
		echo '<div class="emd-form-builder-field-settings-wrap emd-field-' . $hfield . '" style="display:none;">
			<div class="emd-form-builder-field-setting html">
			<label for="emd-field-html">' . __('HTML','emd-plugins') . '</label>
			<textarea name="fields[' . $hfield .'][value]" id="emd-fbhtml-' . str_replace('html','',$hfield) . '" class="emd-form-builder-field-html" rows=3>' . $hvfield['value'] . '</textarea>
			</div></div>';
	}
	foreach($page_fields as $pfield => $pvfield){
		echo '<div class="emd-form-builder-field-settings-wrap emd-field-page' . $pfield . '" style="display:none;">
			<div class="emd-form-builder-field-setting step-title">
			<label for="emd-field-step-title">' . __('Step Title','emd-plugins') . '</label>
			<input type="text" name="fields[step_title_' . $pfield . '][value]" id="emd-fbform-' . $pfield . '" class="emd-form-builder-field-step-title"';
		if(!empty($pvfield['step_title'])){
			echo ' value="' . $pvfield['step_title'] . '"';
		}
		echo '>
			</div>
			<div class="emd-form-builder-field-setting step-desc">
			<label for="emd-field-step-desc">' . __('Step Description','emd-plugins') . '</label>
			<textarea name="fields[step_desc_' . $pfield . '][value]" id="emd-fbform-' . $pfield . '" class="emd-form-builder-field-step-desc" rows=3>';
		if(!empty($pvfield['step_desc'])){
			echo $pvfield['step_desc'];
		}
		echo '</textarea>
			</div></div>';
	}
	if(!empty($has_user)){
		echo '<div class="emd-form-builder-field-settings-wrap emd-field-login_box_username" style="display:none;">
			<div class="emd-form-builder-field-setting label">
			<label for="login_box-login-label">' . __('Login Label','emd-plugins') . '</label>
			<input type="text" name="fields[login_box_username][login_label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_login_label" value="';
		if(!empty($login_fields['login_box_username']['login_label'])){
			echo  $login_fields['login_box_username']['login_label'];
		}
		else {
			echo __('Already have an account? Login.','emd-plugins');
		}
		echo  '"></div>
			<div class="emd-form-builder-field-setting label">
			<label for="login_box-register-label">' . __('Register Label','emd-plugins') . '</label>
			<input type="text" name="fields[login_box_username][reg_label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_reg_label" value="';
		if(!empty($login_fields['login_box_username']['reg_label'])){
			echo  $login_fields['login_box_username']['reg_label'];
		}
		else {
			echo __('Need to create an account? Register.','emd-plugins');
		}
		echo  '"></div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-username-label">' . __('Login Username Label','emd-plugins') . '</label>
		<input type="text" name="fields[login_box_username][label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_username_label" value="';
		if(!empty($login_fields['login_box_username']['label'])){
			echo  $login_fields['login_box_username']['label'];
		}
		else {
			echo __('Username','emd-plugins');
		}
		echo  '"></div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-password-label">' . __('Login Password Label','emd-plugins') . '</label>
		<input type="text" name="fields[login_box_password][label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_password_label" value="';
		if(!empty($login_fields['login_box_password']['label'])){
			echo  $login_fields['login_box_password']['label'];
		}
		else {
			echo __('Password','emd-plugins');
		}
		echo  '"></div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-redirect-link">' . __('Redirect Link','emd-plugins') . '</label>
		<input type="text" name="fields[login_box_username][redirect_link]" class="emd-form-builder-field-label" id="emd-fbl-login_box_redirect_link" value="';
		if(!empty($login_fields['login_box_username']['redirect_link'])){
			echo  $login_fields['login_box_username']['redirect_link'];
		}
		echo  '">';
		echo '<p class="desc">' . __('If left empty after login user will be redirected to single entity page.','emd-plugins') . '</p>
		</div>
		<div class="emd-form-builder-field-setting enable-register">';
		echo '<input type="checkbox" name="fields[login_box_reg_username][enable_registration]" class="inline emd-form-builder-field-label" id="emd-fbr-login_box_enable_registation" value=1';
		if(!empty($login_fields['login_box_reg_username']['enable_registration'])){
			echo ' checked';
		}
		echo '>'; 
		echo '<label class="inline" for="login_box-enable_registration">' . __('Enable Registration','emd-plugins') . '</label>
		</div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-reg-username-label">' . __('Registration Username Label','emd-plugins') . '</label>
		<input type="text" name="fields[login_box_reg_username][label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_reg_username_label" value="';
		if(!empty($login_fields['login_box_reg_username']['label'])){
			echo  $login_fields['login_box_reg_username']['label'];
		}
		else {
			echo __('Username','emd-plugins');
		}
		echo  '"></div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-reg-password-label">' . __('Registration Password Label','emd-plugins') . '</label>
		<input type="text" name="fields[login_box_reg_password][label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_reg_password_label" value="';
		if(!empty($login_fields['login_box_reg_password']['label'])){
			echo  $login_fields['login_box_reg_password']['label'];
		}
		else {
			echo __('Password','emd-plugins');
		}
		echo  '"></div>
		<div class="emd-form-builder-field-setting label">
		<label for="login_box-reg-confirm-password-label">' . __('Registration Confirm Password Label','emd-plugins') . '</label>
		<input type="text" name="fields[login_box_reg_confirm_password][label]" class="emd-form-builder-field-label" id="emd-fbl-login_box_reg_confirm_password_label" value="';
		if(!empty($login_fields['login_box_reg_confirm_password']['label'])){
			echo  $login_fields['login_box_reg_confirm_password']['label'];
		}
		else {
			echo __('Confirm Password','emd-plugins');
		}
		echo  '"></div>
		</div>';
	}
}
add_action('wp_ajax_emd_form_builder_lite_get_page', 'emd_form_builder_lite_get_page');
function emd_form_builder_lite_get_page(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	if(!empty($_GET['page_id']) && !empty($_GET['app']) && !empty($_GET['entity'])){
		$npid = (int) $_GET['page_id'] + 1;
		$page = '<div class="emd-form-builder-page" id="emd-form-builder-page-' . $npid . '" title="' . __('Click to go to this page','emd-plugins') . '">
			<a href="#" class="emd-form-builder-page-delete" title="' . __('Delete','emd-plugins') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>' .
			__('Page','emd-plugins') . ' ' . $npid . '</div>';
		$playout = '<div class="emd-form-page-wrap" id="emd-form-page-' . $npid . '" data-page="' . $npid . '" style="display:none;">
			<input type="hidden" class="emd-form-page-hidden" name="layout[]" value="page">
			<div class="emd-form-row init">
			<a href="#" class="emd-form-row-delete" title="' . __('Delete','emd-plugins') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-row-info">' . __('Drag to reorder','emd-plugins') . '</span>
			<div class="emd-form-row-holder" data-app="' . esc_attr($_GET['app']) . '" data-entity="' . esc_attr($_GET['entity']) . '" data-row="0">
			<input type="hidden" name="layout[]" value="row">
			<div class="emd-form-insert-row">' . __('Drag fields here','emd-plugins') . 
			'</div>
			</div>
			</div>
			</div>';
		$psetting = '';
		if($_GET['page_id'] == 1){
			$psetting .= '<div class="emd-form-builder-field-settings-wrap emd-field-page' . $_GET['page_id'] . '" style="display:none;">
				<div class="emd-form-builder-field-setting step-title">
				<label for="emd-field-step-title">' . __('Step Title','emd-plugins') . '</label>
				<input type="text" name="fields[step_title_' . $_GET['page_id'] . '][value]" id="emd-fbform-' . $_GET['page_id'] . '" class="emd-form-builder-field-step-title">
				</div>
				<div class="emd-form-builder-field-setting step-desc">
				<label for="emd-field-step-desc">' . __('Step Description','emd-plugins') . '</label>
				<textarea name="fields[step_desc_' . $_GET['page_id'] . '][value]" id="emd-fbform-' . $_GET['page_id'] . '" class="emd-form-builder-field-step-desc" rows=3></textarea>
				</div></div>';
		}
		$psetting .= '<div class="emd-form-builder-field-settings-wrap emd-field-page' . $npid . '" style="display:none;">
			<div class="emd-form-builder-field-setting step-title">
			<label for="emd-field-step-title">' . __('Step Title','emd-plugins') . '</label>
			<input type="text" name="fields[step_title_' . $npid . '][value]" id="emd-fbform-' . $npid . '" class="emd-form-builder-field-step-title">
			</div>
			<div class="emd-form-builder-field-setting step-desc">
			<label for="emd-field-step-desc">' . __('Step Description','emd-plugins') . '</label>
			<textarea name="fields[step_desc_' . $npid . '][value]" id="emd-fbform-' . $npid . '" class="emd-form-builder-field-step-desc" rows=3></textarea>
			</div></div>';
		wp_send_json_success(array('page' => $page, 'playout' => $playout, 'setting' => $psetting));
	}
}
add_action('wp_ajax_emd_form_builder_lite_get_row','emd_form_builder_lite_get_row');
function emd_form_builder_lite_get_row(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	if(!empty($_GET['app']) && !empty($_GET['entity'])){
		$playout = '<div class="emd-form-row init">
			<a href="#" class="emd-form-row-delete" title="' . __('Delete','emd-plugins') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-row-info">' . __('Drag to reorder','emd-plugins') . '</span>
			<div class="emd-form-row-holder" data-app="' . esc_attr($_GET['app']) . '" data-entity="' . esc_attr($_GET['entity']) . '" data-row="0">
			<input type="hidden" name="layout[]" value="row">
			<div class="emd-form-insert-row">' . __('Drag fields here','emd-plugins') . 
			'</div>
			</div>
			</div>';
		wp_send_json_success(array('row' => $playout));
	}
}
add_action('wp_ajax_emd_form_builder_lite_save_form','emd_form_builder_lite_save_form');

function emd_form_builder_lite_save_form(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	$layout = Array();
	$fields = Array();
	if(!empty($_POST['data'])){
		$data = json_decode(stripslashes($_POST['data']),true);
		$pcount = 0;
		$rcount = 0;
		foreach($data as $mydata){
			if($mydata['name'] == 'id'){
				$form_id = $mydata['value'];
			}
			elseif(preg_match('/^fields(\[([^\[\]]+)\]\[([^\[\]]+)\])/',$mydata['name'],$matches)){
				$fields[$matches[2]][$matches[3]] = $mydata['value'];
				if($matches[2] == 'login_box_username'){
					$fields['login_box_password'][$matches[3]] = $mydata['value'];
					$fields['login_box_password']['req'] = 1;
					$fields[$matches[2]]['req'] = 1;
				}
			}
		}
		foreach($data as $mydata){
			if($mydata['name'] == 'layout[]' && $mydata['value'] == 'page'){
				$pcount ++;
				$rcount = 0;
				$layout[$pcount]['step_title'] = $fields['step_title_' . $pcount]['value'];
				$layout[$pcount]['step_desc'] = $fields['step_desc_' . $pcount]['value'];
			}
			elseif($mydata['name'] == 'layout[]' && $mydata['value'] == 'row'){
				$rcount ++;
			}
			elseif($mydata['name'] == 'layout[]' && $mydata['value'] == 'hr'){
				$layout[$pcount]['rows'][$rcount][][$mydata['value']] = Array('show' => 1);
			}
			elseif($mydata['name'] == 'layout[]' && !empty($fields[$mydata['value']])){
				$fields[$mydata['value']]['show'] = 1;
				$layout[$pcount]['rows'][$rcount][][$mydata['value']] = $fields[$mydata['value']];
			}
		}
	}
	if(!empty($form_id)){		
		foreach($layout as $kpage => $cpage){
			if(empty($cpage['rows'])){
				unset($layout[$kpage]);
			}
		}
		$form = get_post($form_id);
		$myform = json_decode($form->post_content,true);
		$myform['layout'] = $layout;
		$form_data = array(
				'ID' => $form_id,
				'post_content' => wp_slash(json_encode($myform,true)),
				);
		$res = wp_update_post($form_data);
		if(!is_wp_error($res)){
			wp_send_json_success();
		}
	}
	die();
}
function emd_form_builder_lite_get_cfield($fentity,$kfield,$attr_list,$ent_list,$txn_list,$rel_list){
	$rel_labels = Array();
	$cfield = Array('req' => 0);
	foreach($rel_list as $krel => $vrel){
		if($fentity == $vrel['from']){
			$rel_labels[$krel]['label'] = $vrel['from_title'];
		}
		elseif($fentity == $vrel['to']){
			$rel_labels[$krel]['label'] = $vrel['to_title'];
		}
		if(!empty($vrel['desc'])){
			$rel_labels[$krel]['desc'] = $vrel['desc'];
		}
		elseif(!empty($rel_labels[$krel]['label'])){
			$rel_labels[$krel]['desc'] = $rel_labels[$krel]['label'];
		}
	}
	if(in_array($kfield,Array('blt_title','blt_content','blt_excerpt'))){
		if(!empty($ent_list[$fentity]['req_blt'][$kfield])){
			$cfield['label'] = $ent_list[$fentity]['req_blt'][$kfield]['msg'];
		}
		elseif(!empty($ent_list[$fentity]['blt_list'][$kfield])){
			$cfield['label'] = $ent_list[$fentity]['blt_list'][$kfield];
		}	
		$cfield['desc'] = $cfield['label'];
	}
	elseif(!empty($rel_labels[$kfield])){
		$cfield['label'] = $rel_labels[$kfield]['label'];
		$cfield['desc'] = $rel_labels[$kfield]['desc'];
	}
	elseif(!empty($attr_list[$fentity][$kfield])){
		if(!empty($attr_list[$fentity][$kfield]['desc'])){
			$cfield['desc'] = $attr_list[$fentity][$kfield]['desc'];
		}	
		if(!empty($attr_list[$fentity][$kfield]['label'])){
			$cfield['label'] = $attr_list[$fentity][$kfield]['label'];
		}
	}
	elseif(!empty($txn_list[$fentity][$kfield])){
		$cfield['label'] = $txn_list[$fentity][$kfield]['single_label'];
		if(!empty($txn_list[$fentity][$kfield]['desc'])){
			$cfield['desc'] = $txn_list[$fentity][$kfield]['desc'];
		}
	}
	if(!empty($cfield['label'])){
		$cfield['placeholder'] = $cfield['label'];
	}
	$cfield['size'] = 12;
	return $cfield;
}
add_action('wp_ajax_emd_form_builder_lite_get_hr', 'emd_form_builder_lite_get_hr');
function emd_form_builder_lite_get_hr(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	if(!empty($_GET['app']) && !empty($_GET['entity'])){
		$playout = '<div class="emd-form-row">
			<a href="#" class="emd-form-row-delete" title="' . __('Delete','emd-plugins') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-row-info">' . __('Drag to reorder','emd-plugins') . '</span>
			<input type="hidden" name="layout[]" value="row">
			<hr class="emd-form-row-hr">
			<input type="hidden" name="layout[]" value="hr">
			</div>';
		wp_send_json_success(array('row' => $playout));
	}
}
add_action('wp_ajax_emd_form_builder_lite_get_html', 'emd_form_builder_lite_get_html');
function emd_form_builder_lite_get_html(){
	check_ajax_referer('emd_form', 'nonce');
	$fcap = 'manage_options';
	$fcap = apply_filters('emd_settings_pages_cap', $fcap, sanitize_text_field($_POST['app']));
	if(!current_user_can($fcap)){
		echo false;
		die();
	}
	if(!empty($_GET['htmlcount']) && !empty($_GET['app']) && !empty($_GET['entity'])){
		$playout = '<div class="emd-form-row init html">
			<a href="#" class="emd-form-row-delete" title="' . __('Delete','emd-plugins') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-row-info">' . __('Drag to reorder','emd-plugins') . '</span>
			<div class="emd-form-row-holder" data-app="' . esc_attr($_GET['app']) . '" data-entity="' . esc_attr($_GET['entity']) . '" data-row="0">
			<input type="hidden" name="layout[]" value="row">
			<div class="emd-form-field emd-form-html emd-col" data-field="html' . esc_attr($_GET['htmlcount']) . '">
			<a href="#" class="emd-form-field-delete" title="' . __('Delete','emd-plugins') . '"><span class="field-icons times-circle" aria-hidden="true"></span></a>
			<span class="emd-form-field-info" style="cursor:pointer;">' . __('Drag to reorder / Click for settings','emd-plugins') . '</span>
			<div class="emd-form-group">
			<input type="hidden" name="layout[]" value="html' . esc_attr($_GET['htmlcount']) . '">
			<div id="html' . esc_attr($_GET['htmlcount']) . '" class="emd-form-html-div"><span class="emd-html-click">' . __('Click to go to settings','emd-plugins') . '</span>
			<input type="text" class="text emd-input-md emd-form-control html-code" style="display:none;" placeholder="' . __('HTML','emd-plugins') . '" disabled/>
			</div>
			</div>
			</div>
			</div>
			</div>';
		$psetting = '<div class="emd-form-builder-field-settings-wrap emd-field-html' . esc_attr($_GET['htmlcount']) . '" style="display:none;">
			<div class="emd-form-builder-field-setting html">
			<label for="emd-field-html">' . __('HTML','emd-plugins') . '</label>
			<textarea name="fields[html' . esc_attr($_GET['htmlcount']) . '][value]" id="emd-fbhtml-' . esc_attr($_GET['htmlcount']) . '" class="emd-form-builder-field-html" rows=3></textarea>
			</div>';
		wp_send_json_success(array('row' => $playout,'setting' => $psetting));
	}
}
function emd_form_builder_lite_login_box($kfield,$cfield){
	if($kfield == 'login_box_username'){
		$lay = '<div class"login_register">
			<input type="text" name="' . $kfield . '" id="' . $kfield . '" class="text emd-input-md emd-form-control" placeholder="' . __('Login / Register Box','emd-plugins') . '" disabled/>';
		$lay .= '</div></div>';
	}
	else {
		$lay = '';
	}
	return $lay;
}
