<?php
/**
 * Login Settings Tab
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

add_action('emd_ext_register','emd_login_register_settings');
add_filter('emd_add_settings_tab','emd_login_settings_tab',10,2);
add_action('emd_show_settings_tab','emd_show_login_settings_tab',10,2);

function emd_login_settings_tab($tabs,$app){
	$has_login_shc = 0;	
	$ent_list = get_option($app . '_ent_list');
	foreach($ent_list as $kent => $myent){
		if(!empty($myent['user_key'])){
			$has_login_shc = 1;	
		}
	}
	if($has_login_shc == 1){
		$tabs['login'] = __('Login', 'emd_plugins');
		echo '<p>' . settings_errors($app . '_login_settings') . '</p>';
	}
	return $tabs;
}
function emd_show_login_settings_tab($app,$active_tab){
	$login_settings = get_option($app . '_login_settings');
	emd_login_tab($app,$active_tab,$login_settings);
}
function emd_login_register_settings($app){
	register_setting($app . '_login_settings', $app . '_login_settings', 'emd_login_sanitize');
}
function emd_login_sanitize($input){
	if(empty($input['app'])){
		return $input;
	}
	$login_settings = get_option($input['app'] . '_login_settings');
	$keys = Array('login_page','pass_reset_subj','pass_reset_msg','redirect_login','redirect_logout');
	foreach($keys as $mkey){	
		if(isset($input[$mkey])){
			$login_settings[$mkey] = $input[$mkey];
		}
	}	
	return $login_settings;
}
function emd_login_tab($app,$active_tab,$login_settings){
?>
	<div class='tab-content' id='tab-login' <?php if ( 'login' != $active_tab ) { echo 'style="display:none;"'; } ?>>
	<?php	echo '<form method="post" action="options.php">';
		settings_fields($app .'_login_settings');
		echo '<input type="hidden" name="' . esc_attr($app) . '_login_settings[app]" id="' . esc_attr($app) . '_login_settings_app" value="' . $app . '">';
		echo "<h4>" . __('Login/Register:','emd-plugins') . ' ' . __('Use the options below to customize Login page.','emd-plugins');
		echo '</h4>';
		echo '<div id="login-settings" class="accordion-container"><ul class="outer-border">';
		echo '<table class="form-table"><tbody>';
		echo "<tr><th scope='row'><label for='login_settings_login_page'>";
		echo __('Login Page','emd-plugins');
		echo '</label></th><td>';
		echo '<select id="' . esc_attr($app) . '_login_settings_login_page" name="' . esc_attr($app) . '_login_settings[login_page]">';
		$login_page = '';
		if(!empty($login_settings['login_page'])){
			$login_page = $login_settings['login_page'];
		}
		$lpages = get_pages();
		$lpages_options = Array();
		if(!empty($lpages)){
			foreach($lpages as $page) {
				$lpages_options[$page->ID] = $page->post_title;
			}
		}
		foreach($lpages_options as $klp => $vlp){
			echo '<option value="' . $klp . '"';
			if($klp == $login_page){
				echo ' selected';
			}
			echo '>' . $vlp . '</option>';
		}
		echo '</select>';
		echo "<p class='description'>" . sprintf(__('Use [emd_login app="%s"] shortcode in your page. Attributes: ent="emd_customer,emd_vendor" show_reg=1 , reg_label="",reg_link="",show_lost=1  ','emd-plugins'),$app) . "</p></td></tr>";
		echo "<tr><th scope='row'><label for='login_settings_pass_reset_subj'>";
		echo __('Password Reset Message Subject','emd-plugins');
		echo '</label></th><td>';
		echo '<input class="regular-text" type="text" id="' . esc_attr($app) . '_login_settings_pass_reset_subj" name="' . esc_attr($app) . '_login_settings[pass_reset_subj]"';
		if(!empty($login_settings['pass_reset_subj'])){
			echo 'value="' . $login_settings['pass_reset_subj'] . '"';
		}
		echo '>';
		echo '</td></tr>';
		echo "<tr><th scope='row'><label for='login_settings_pass_reset_msg'>";
		echo __('Password Reset Message','emd-plugins');
		echo '</label></th><td>';
		ob_start();
		wp_editor($login_settings['pass_reset_msg'], esc_attr($app) . '_login_settings_pass_reset_msg', array(
			'tinymce' => false,
			'textarea_rows' => 10,
			'media_buttons' => true,
			'textarea_name' => esc_attr($app) . '_login_settings[pass_reset_msg]',
			'quicktags' => Array(
				'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,spell'
			)
		));
		$html = ob_get_clean();
		echo $html;
		echo '</td></tr>';
		echo "<tr><th scope='row'><label for='login_settings_pass_reset_msg'>";
		echo '</label></th><td>';
		echo __('Use these template tags to customize your email: {username}, {password_reset_link}, {sitename}','emd-plugins'); 
		echo '</td></tr>';
		$cust_roles = get_option($app . '_cust_roles',Array());
		if(!empty($cust_roles)){
			echo "<tr style='border-top:2px solid #e0e0e0;border-bottom:2px solid #e0e0e0;'><th scope='row' style='padding:5px 5px;' colspan=2><h3 style='display:inline;color:#5f9ea0;'>";
			echo __('User Role Redirects','emd-plugins');
			echo '</h3>';
			echo "<span class='description' style='padding-left:10px;color:#777;'>- " . __('Set a redirect url which users belonging to a role will be redirected to after a successful login or logout.','emd-plugins') . "</span></th></tr>";
			$ent_list = get_option($app . '_ent_list');
			foreach($cust_roles as $krole => $vrole){
				foreach($ent_list as $kent => $myent){
					if(in_array($krole,$myent['limit_user_roles'])){
						$ent_roles[$krole] = $kent;
					}
				}
				echo "<tr><th scope='row'><label for='login_settings_redirect_login'>";
				echo sprintf(__('%s Login','emd-plugins'),$vrole);
				echo '</label></th><td>';
				echo '<input class="regular-text" type="text" id="' . esc_attr($app) . '_login_settings_redirect_login_' . $krole . '" name="' . esc_attr($app) . '_login_settings[redirect_login][' . $krole . ']"';
				if(!empty($login_settings['redirect_login'][$krole])){
					echo 'value="' . $login_settings['redirect_login'][$krole] . '"';
				}
				echo '>';
				if(!empty($ent_roles[$krole])){
					echo '<p class="description">' . __('If left empty, user will be directed to the corresponding user entity page','emd-plugins') . '</p>';
				}
				else {
					echo '<p class="description">' . __('If left empty, user will be directed to the site\'s homepage','emd-plugins') . '</p>';
				}
				echo '</td></tr>';
				echo "<tr><th scope='row'><label for='login_settings_redirect_logout'>";
				echo sprintf(__('%s Logout','emd-plugins'),$vrole);
				echo '</label></th><td>';
				echo '<input class="regular-text" type="text" id="' . esc_attr($app) . '_login_settings_redirect_logout_' . $krole . '" name="' . esc_attr($app) . '_login_settings[redirect_logout][' . $krole . ']"';
				if(!empty($login_settings['redirect_logout'][$krole])){
					echo 'value="' . $login_settings['redirect_logout'][$krole] . '"';
				}
				echo '>';
				echo '<p class="description">' . __('If left empty user, will be directed to the login page','emd-plugins') . '</p>';
				echo '</td></tr>';
			}
		}
		echo '</tbody></table>';
		echo '</ul></div>';
		submit_button(); 
		echo '</form></div>';
}
