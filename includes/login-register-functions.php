<?php
/**
 * Login and Register form functions/actions
 * @since WPAS 5.3
 */
if (!defined('ABSPATH')) exit;

add_filter('emd_get_login_register_option_for_views','emd_get_login_register_option_for_views',10,2);

function emd_get_login_register_option_for_views($show,$app){
	if(is_user_logged_in()){
		return 'none';
	}
	$misc_settings = get_option($app . '_misc_settings');
	if(!empty($misc_settings['login_reg'])){
		return $misc_settings['login_reg'];
	}
	return 'none';
}


add_action('emd_show_login_register_forms','emd_show_login_register_forms',10,3);

function emd_show_login_register_forms($app,$fcontent,$show){
	if($show != 'none'){
		$sess_name = strtoupper($app);
		$session_class = $sess_name();
		if(!empty($_GET['emd_error'])){
			switch($_GET['emd_error']){
				case 'invalid_login':
					$error = __('Invalid username, email or password.','emd-plugins');
					break;
			}
		}
		else {
			$error = $session_class->session->get('login_reg_errors');
		}
		if (!empty($error) && !empty($_POST['emd_action']) && $_POST['emd_action'] == $app . '_user_register') {
			$show = "register";
		}	
	}
	$dir_url = constant(strtoupper($app) . "_PLUGIN_URL");
	$version = constant(strtoupper($app) . "_VERSION");
	//check to show login and registration forms
	wp_enqueue_style('form-frontend-css', $dir_url . '/includes/emd-form-builder/css/emd-form-frontend.min.css','',$version);
	wp_enqueue_style('emd-login-register', $dir_url . 'assets/css/emd-login-register.min.css','',$version);
	wp_enqueue_script('wpas-jvalidate', $dir_url . 'assets/ext/jvalidate/wpas.validate.min.js', array('jquery'),$version,true);
	wp_enqueue_script('emd-login-register', $dir_url . 'assets/js/emd-login-register.js',Array('jquery'),$version);
	$log_reg_vars['show'] = $show;
	$log_reg_vars['nonce'] = wp_create_nonce('emd_form');
	$log_reg_vars['ajax_url'] = admin_url('admin-ajax.php');
	$log_reg_vars['user_email_msg'] = __('This email has been already registered.', 'emd-plugins');
	$log_reg_vars['verify_msg'] = __('Invalid username','emd-plugins');
	$log_reg_vars['verify_pass'] = __('Please enter at least 5 characters.','emd-plugins');
	$log_reg_vars['verify_pass2'] = __('Please enter same password.','emd-plugins');
	$log_reg_vars['verify_email'] = __('Email address already taken.','emd-plugins');
	wp_localize_script("emd-login-register", 'log_reg_vars', $log_reg_vars);
	if($show != 'none'){
		ob_start();
		echo "<div class='emd-container'>";
		if (!empty($error)) {
			echo "<div class='emd-alert-container'>";
			echo "<div class='emd-alert-error emd-alert'>" . $error . "</div>";
			echo "</div>";
		}
		elseif(!empty($fcontent)) {
			$noaccess_msg = $fcontent['settings']['noaccess_msg'];	
			if(!empty($noaccess_msg)){
				echo "<div class='emd-alert-container'>";
				echo "<div class='emd-alert-error emd-alert'>" . $noaccess_msg . "</div>";
				echo "</div>";
			}
		}
		emd_get_template_part(str_replace("_","-",$app), 'emd-login');
		if ($show == 'both' || (!empty($error) && $_POST['emd_action'] == $app . '_user_register')) {
			emd_get_template_part(str_replace("_","-",$app), 'emd-register');
		}
		echo "</div>";
		$layout = ob_get_clean();
		$session_class->session->set('login_reg_errors', null);
		echo $layout;
	}
	else {
		echo "<div class='noaccess-container'><div class='emd-ncc-msg'>";
		$misc_settings = get_option($app . '_misc_settings');
		if(!empty($misc_settings['no_access_msg'])){
			echo $misc_settings['no_access_msg'];
		}
		else {
			_e('You do not have sufficient permissions to access this page.', 'emd-plugins');
		}
		echo '</div></div>';
	}
}
function emd_show_login_register_options($app){
	$access_views = get_option($app . "_access_views");
	if(!empty($access_views['single']) || !empty($access_views['tax']) || !empty($access_views['archive'])){
		return true;
	}
	$front_ents = emd_find_limitby('frontend', $app);
	if(!empty($front_ents)){
		return true;
	}
	return false;
}
add_action('wp_ajax_nopriv_emd_verify_email', 'emd_login_register_verify_email');
add_action('wp_ajax_emd_verify_email', 'emd_ilogin_register_verify_email');

function emd_login_register_verify_email(){
        check_ajax_referer('emd_form', 'nonce');
        if(!empty($_POST['email'])){
		if(email_exists( $_POST['email'])) {
                        wp_send_json_error(array('msg' => __('Email aready exists','emd-plugins')));
                }
                else {
                        wp_send_json_success(array('status' => 'success'));
                }
        }
        die();
}
