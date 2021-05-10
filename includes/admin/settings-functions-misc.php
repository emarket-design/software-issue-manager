<?php
/**
 * Settings Functions Misc
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 5.3
 */
if (!defined('ABSPATH')) exit;

add_action('emd_ext_register','emd_misc_register_settings');
add_filter('emd_add_settings_tab','emd_misc_settings_tab',10,2);
add_action('emd_show_settings_tab','emd_show_misc_settings_tab',10,2);

if (!function_exists('emd_misc_settings_tab')) {
	function emd_misc_settings_tab($tabs,$app){
		$shc_list = get_option($app . '_shc_list');
		$show_tab = 0;
		if(!empty($shc_list)){
			if(isset($shc_list['has_gmap']) && $shc_list['has_gmap'] == 1){
				$show_tab = 1;
			}
			elseif(isset($shc_list['has_bs']) && $shc_list['has_bs'] == 1){
				$show_tab = 1;
			}
			elseif(isset($shc_list['has_autocomplete']) && $shc_list['has_autocomplete'] == 1){
				$show_tab = 1;
			}
		}
		if(function_exists('emd_show_login_register_options') 
			&& emd_show_login_register_options($app)){
			$show_tab = 1;
		}
                if($show_tab == 1){
			$tabs['misc'] = __('Misc', 'emd_plugins');
			echo '<p>' . settings_errors($app . '_misc_settings') . '</p>';
		}
		return $tabs;
	}
}
if (!function_exists('emd_show_misc_settings_tab')) {
	function emd_show_misc_settings_tab($app,$active_tab){
		$misc_settings = get_option($app . '_misc_settings');
		emd_misc_tab($app,$active_tab,$misc_settings);
	}
}
if (!function_exists('emd_misc_register_settings')) {
	function emd_misc_register_settings($app){
		register_setting($app . '_misc_settings', $app . '_misc_settings', 'emd_misc_sanitize');
	}
}
if (!function_exists('emd_misc_sanitize')) {
	function emd_misc_sanitize($input){
		if(empty($input['app'])){
                        return $input;
                }
		$misc_settings = get_option($input['app'] . '_misc_settings');
		$keys = Array('login_reg','no_access_msg','disable_bs_css','disable_bs_js','google_mapkey','autocomp_min_input','autocomp_num_result');
		foreach($keys as $mkey){	
			if(isset($input[$mkey])){
				$misc_settings[$mkey] = $input[$mkey];
			}
			else {
				$misc_settings[$mkey] = 0;
			}
		}	
		return $misc_settings;
	}
}
if (!function_exists('emd_misc_tab')) {
	function emd_misc_tab($app,$active_tab,$misc_settings){
	?>
	<div class='tab-content' id='tab-misc' <?php if ( 'misc' != $active_tab ) { echo 'style="display:none;"'; } ?>>
		<?php	echo '<form method="post" action="options.php">';
			settings_fields($app .'_misc_settings');
			echo '<input type="hidden" name="' . esc_attr($app) . '_misc_settings[app]" id="' . esc_attr($app) . '_misc_settings_app" value="' . $app . '">';
			echo '<div id="misc-settings" class="accordion-container"><ul class="outer-border">';
			echo '<table class="form-table"><tbody>';
			$shc_list = get_option($app . '_shc_list');
                        if(!empty($shc_list) && isset($shc_list['has_gmap']) && $shc_list['has_gmap'] == 1){
				echo "<tr><th scope='row'><label for='misc_settings_google_mapkey'>";
				echo __('Google Maps API Key','emd-plugins');
				echo '</label></th><td>';
				$google_mapkey = '';
				if(!empty($misc_settings['google_mapkey'])){
					$google_mapkey = $misc_settings['google_mapkey'];
				}
				echo "<input id='" . esc_attr($app) . "_misc_settings_google_mapkey' name='" . esc_attr($app) . "_misc_settings[google_mapkey]' type='text' value='" . $google_mapkey . "'";
				echo "></input><p class='description'>" . __('Google requires api key to access google map service. By default we provide a complimentary key. Get your own google maps api key to avoid service interruptions','emd-plugins') . ": <a href='https://developers.google.com/maps/documentation/javascript/get-api-key' target='_blank'>Documentation</a></p></td></tr>";
                        }
			if(!empty($shc_list) && isset($shc_list['has_bs']) && $shc_list['has_bs'] == 1){
				echo "<tr><th scope='row'><label for='misc_settings_disable_bs_css'>";
				echo __('Disable Bootstrap CSS','emd-plugins');
				echo '</label></th><td>';
				$disable_bs_css =0;
				if(isset($misc_settings['disable_bs_css']) && $misc_settings['disable_bs_css'] == 1){
					$disable_bs_css =1;
				}
				echo "<input id='" . esc_attr($app) . "_misc_settings_disable_bs_css' name='" . esc_attr($app) . "_misc_settings[disable_bs_css]' type='checkbox' value='1'";
				if($disable_bs_css == 1){
					echo " checked";
				}
				echo "></input><p class='description'>" . __('Disables loading of Bootstrap stylesheet in plugin related pages when checked. You may need to disable it if your theme already uses Bootstrap based stylesheet.','emd-plugins') . "</p></td></tr>";
				echo "<tr><th scope='row'><label for='misc_settings_disable_bs_js'>";
				echo __('Disable Bootstrap JS','emd-plugins');
				echo '</label></th><td>';
				$disable_bs_js =0;
				if(isset($misc_settings['disable_bs_js']) && $misc_settings['disable_bs_js'] == 1){
					$disable_bs_js =1;
				}
				echo "<input id='" . esc_attr($app) . "_misc_settings_disable_bs_js' name='" . esc_attr($app) . "_misc_settings[disable_bs_js]' type='checkbox' value='1'";
				if($disable_bs_js == 1){
					echo " checked";
				}
				echo "></input><p class='description'>" . __('Disables loading of Bootstrap JavaScript in plugin related pages when checked. You may need to disable it if your theme already uses Bootstrap based JavaScript.','emd-plugins') . "</p></td></tr>";
			}
			if(!empty($shc_list) && isset($shc_list['has_autocomplete']) && $shc_list['has_autocomplete'] == 1){
				echo "<tr><th scope='row'><label for='misc_settings_min_input'>";
				echo __('Autocomplete Minimum Input Length','emd-plugins');
				echo '</label></th><td>';
				$min_input = 3;
				if(!empty($misc_settings['autocomp_min_input'])){
					$min_input = $misc_settings['autocomp_min_input'];
				}
				echo "<input id='" . esc_attr($app) . "_misc_settings_autocomp_min_input' name='" . esc_attr($app) . "_misc_settings[autocomp_min_input]' type='text' value='" . $min_input . "'";
				echo "></input></td></tr>";
				echo "<tr><th scope='row'><label for='misc_settings_autocomp_num_result'>";
				echo __('Autocomplete Number of Results Returned','emd-plugins');
				echo '</label></th><td>';
				$num_result = 5;
				if(!empty($misc_settings['autocomp_num_result'])){
					$num_result = $misc_settings['autocomp_num_result'];
				}
				echo "<input id='" . esc_attr($app) . "_misc_settings_autocomp_num_result' name='" . esc_attr($app) . "_misc_settings[autocomp_num_result]' type='text' value='" . $num_result . "'";
				echo "></input></td></tr>";

			}
			if(function_exists('emd_show_login_register_options') && emd_show_login_register_options($app)){
				echo '<tr><th scope="row"><label for="misc_no_access_msg">' . __('No Access Message','emd-plugins') . '</label></th>';
				$no_access_msg = (isset($misc_settings['no_access_msg'])) ? $misc_settings['no_access_msg'] : __('You do not have sufficient permissions to access this page.','emd-plugins');
				echo '<td colspan=5><textarea class="large-text code" cols=50 rows=5 id="' . esc_attr($app) . '_misc_settings_no_access_msg" name="' . esc_attr($app) . '_misc_settings[no_access_msg]">' . $no_access_msg . '</textarea>';
				echo '</td></tr>';
				echo '<tr><th scope="row"><label for="misc_login_reg">' . __('Show Register / Login Form','emd-plugins') . '</label></th>';
				echo '<td colspan=5><select id="' . esc_attr($app) . '_misc_settings_login_reg" name="' . esc_attr($app) . '_misc_settings[login_reg]">';
				$login_reg_options = Array('none' => __('Only Message','emd-plugins'),
							'both' => __('Registration and Login Forms','emd-plugins'),
							'login' => __('Only Login Form','emd-plugins'));
				foreach($login_reg_options as $kopt => $vopt){
					echo '<option value="' . $kopt . '"';
					if(!empty($misc_settings['login_reg']) && $misc_settings['login_reg'] == $kopt){
						echo ' selected';
					}
					echo '>' . $vopt . '</option>';
				}
				echo '</select>';
				echo '<p class="description">' . __('Display the registration and login forms on the pages non-logged-in users don\'t have access to.','emd-plugins') . '</p></td></tr>';
			}
			echo '</tbody></table>';
			echo '</ul></div>';
			submit_button(); 
			echo '</form></div>';
	}
}
