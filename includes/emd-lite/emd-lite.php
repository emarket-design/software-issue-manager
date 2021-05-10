<?php
/**
 * Lite Functions
 *
 */
if (!defined('ABSPATH')) exit;

add_action('emd_ext_admin_enq', 'emd_lite_admin_enq', 10, 2);

function emd_lite_admin_enq($app,$hook){
	$shc_list = get_option($app . '_shc_list',Array());
	if((preg_match('/page_' . $app . '_settings$/',$hook) || preg_match('/page_' . $app . '_notify$/',$hook) || preg_match('/page_' . $app . '_forms$/',$hook) || preg_match('/page_' . $app . '_shortcodes$/',$hook)) && !empty($shc_list['has_lite'])){
		emd_lite_admin_enq_files($app,$hook);
	}
}
function emd_lite_admin_enq_files($app,$hook){
	$dir_url = constant(strtoupper($app) . "_PLUGIN_URL");
	$lite_vars = Array();
	wp_enqueue_style('jqconf-css', $dir_url . 'includes/emd-lite/css/jquery-confirm.min.css');
	wp_enqueue_style('emd-lite-css', $dir_url . 'includes/emd-lite/css/emd-lite.css');
	wp_enqueue_script('jqconf-js', $dir_url . 'includes/emd-lite/js/jquery-confirm.min.js');
	wp_enqueue_script('emd-lite-js', $dir_url . 'includes/emd-lite/js/emd-lite.js');
	$lite_vars = apply_filters('emd_lite_modal',$lite_vars,$app,$hook);
	wp_localize_script("emd-lite-js", 'lite_vars', $lite_vars);
}
add_filter('emd_lite_modal','emd_lite_modal',10,3);
function emd_lite_modal($strings,$app,$hook){
	$strings['upgrade_title']   = apply_filters('emd_lite_upgrade_title',esc_html__('Premium Feature', 'emd-plugins'),$hook);
	$strings['upgrade_message'] = '<p>' . apply_filters('emd_lite_upgrade_message',esc_html__('Unfortunately, this feature is not available. Please upgrade to a premium edition to unlock all awesome features.', 'emd-plugins'),$hook) . '</p>';
	$strings['upgrade_button']  = apply_filters('emd_lite_upgrade_button',esc_html__('Upgrade NOW', 'emd-plugins'),$hook);
	$strings['upgrade_url']     = apply_filters('emd_lite_upgrade_url',esc_url("https://emdplugins.com/plugin-pricing/software-issue-manager-wordpress-plugin-pricing/?pk_campaign=upgradelink"),$hook);
	$strings['upgrade_modal']   = apply_filters('emd_lite_upgrade_modal',emd_lite_upgrade_modal_text($app),$hook);
	return $strings;
}
function emd_lite_upgrade_modal_text($app){
	return '<p>' .
		wp_kses(
			__('<strong>After purchasing the premium edition, please remove the free plugin</strong>. Don\'t worry, all your records will be preserved.', 'emd-plugins'),
			array(
				'strong' => array(),
			)
		) . 
		'</p>' .
		'<p>' .
		sprintf(
			wp_kses(
				__('Check out <a href="%s" target="_blank" rel="noopener noreferrer">our documentation</a> for step-by-step instructions.', 'emd-plugins'),
				array(
					'a' => array(
						'href'   => array(),
						'target' => array(),
						'rel'    => array(),
					),
				)
			),
			'https://emdplugins.com/questions/how-do-i-upgrade-my-plugin/?pk_campaign=' . $app . '&pk_kwd=upgradelink'
		) .
		'</p>';
}
function emd_lite_get_operations($type,$plural_label,$textdomain){
	$app = str_replace('-','_',$textdomain);
	$plugin_name = constant(strtoupper($app) . "_NAME");
	if($type == 'opr'){
		$img = plugin_dir_url(__FILE__) . '../../assets/img/operations.png';
	}
	elseif($type == 'yt_api'){
		$img = plugin_dir_url(__FILE__) . '../../assets/img/youtubeapi.png';
	}
	elseif($type == 'cust_fields'){
		$img = plugin_dir_url(__FILE__) . '../../assets/img/cust_fields.png';
	}
	
	echo '<style>';
	if($type == 'opr'){
		echo '
		.emd-oper-img {
			background-image: url("' . $img . '");
			background-color: #CCCCCC;
			height: 613px;
			max-width: 900px;
			background-position: center;
			background-repeat: no-repeat;
			background-size: cover;
		}';
	}
	elseif($type == 'yt_api'){
		echo '
		.ytapi-img {
			background-image: url("' . $img . '");
			background-color: #CCCCCC;
			height: 1613px;
			max-width: 990px;
			background-position: center;
			background-repeat: no-repeat;
			background-size: cover;
			position: relative;
		}';
	}
	elseif($type == 'cust_fields'){
		echo '
		.cust-fields-img {
			background-image: url("' . $img . '");
			background-color: #CCCCCC;
			height: 690px;
			max-width: 1195px;
			background-position: top center;
			background-repeat: no-repeat;
			background-size: cover;
			position: relative;
		}';
	}
	echo '
		.emd-flex {
			display: -webkit-box;
			display: -ms-flexbox;
			display: flex;
			-webkit-box-orient: vertical;
			-webkit-box-direction: normal;
			-ms-flex-direction: column;
			flex-direction: column;
		}
		.emdflexrow {
			-webkit-box-orient: horizontal;
			-webkit-box-direction: normal;
			-ms-flex-direction: row;
			flex-direction: row;
		}	
		.emd-oper-modal {
			text-align: center;
			max-width: 730px;
			margin-top:75px;
			box-shadow: 0 0 60px 30px rgba(0, 0, 0, 0.15);
			border-radius: 3px;
			border-top: solid 7px #3498db;
		}
		.emdflexcenter{
		justify-content: center;
		align-items: center;
		}
		.emdflextop{
                justify-content:top;
                align-items: center;
                }

		.emd-oper-modal *,
		.emd-oper-modal *::before,
		.emd-oper-modal *::after {
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
		}

		.emd-oper-modal h2 {
			font-size: 20px;
			margin: 0 0 16px 0;
			padding: 0;
		}

		.emd-oper-modal p {
			font-size: 16px;
			color: #666;
			margin: 0 0 30px 0;
			padding: 0;
		}

		.emd-oper-modal-content {
			background-color: #fff;
			border-radius: 3px 3px 0 0;
			padding: 20px 15px 20px;
		}

		.emd-oper-modal ul {
			width: 50%;
			margin: 0;
			padding: 0 0 0 30px;
			text-align: left;
		}

		.emd-oper-modal li {
			color:#76889b;
			font-size:0.8rem;
			padding: 6px 0;
		}

		.emd-oper-modal li .fa {
			color: #2a9b39;
			margin: 0 8px 0 0;
		}

		.emd-oper-modal-button {
			border-radius: 0 0 3px 3px;
			padding: 30px;
			background: #f5f5f5;
			text-align: center;
		}
		.emdmodalbtn {
			border: 0;
			border-top-color: currentcolor;
			border-right-color: currentcolor;
			border-bottom-color: currentcolor;
			border-left-color: currentcolor;
			border-radius: 3px;
			cursor: pointer;
			display: inline-block;
			margin: 0;
			text-decoration: none;
			text-align: center;
			vertical-align: middle;
			white-space: nowrap;
			box-shadow: none;
			font-size: 16px;
			font-weight: 600;
			padding: 16px 28px;
			background-color: #E27730;
			border-color: #E27730;
			color: #FFF;
		}
		
		.emdmodalbtn:hover {
			background-color: #b85a1b;
			border-color: #b85a1b;
			color: #fff;
		}
		.emd-oper-content-wrap {
			margin-top: 30px;
			margin-right: 10px;
		}
		.emdiconpad{
			margin:-2px 3px 0 0;
		}
		.emdiconlock{
			display: block;
			margin: 0 auto;
			color: #C4C4C4;
			transform: none;
			font-size: 45px;
			width:45px;
			height:45px;
		}
	</style>';	
	if($type == 'opr'){
		echo '<div class="emd-oper-content-wrap emd-flex emdflexcenter emd-oper-img">';
	}
	elseif($type == 'yt_api'){
		echo '<div class="emd-oper-content-wrap emd-flex emdflextop ytapi-img">';
	}
	elseif($type == 'cust_fields'){
		echo '<div class="emd-oper-content-wrap emd-flex emdflextop cust-fields-img">';
	}
	echo '<div class="emd-oper-modal emd-flex">
		<div class="emd-oper-modal-content"><span class="emdiconlock dashicons dashicons-lock"></span>';
	if($type == 'opr'){
		echo '<h2>' . sprintf(__('Import, Export and Update %s From/To CSV', 'emd-plugins'),$plural_label) . '</h2>
		<div style="max-width:450px;margin: auto;"><strong>' . sprintf(__('Do you have hundreds of %s in a spreadsheet and think about how to get them in WordPress? No problem!', 'emd-plugins'),$plural_label) . '</strong></div>
		<div style="max-width:450px;margin: auto;padding-bottom:15px;">' . 
	sprintf(__('Sometimes, adding or updating %s one by one is NOT the smartest thing to do. Bulk import, update and take snapshots at will - more time to play!', 'emd-plugins'),$plural_label) .
		'</div>
		<div class="emd-flex emdflexrow">
		<ul class="left">
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . sprintf(__('Get your %s in %s fast', 'emd-plugins'),strtolower($plural_label),$plugin_name) . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . sprintf(__('Bulk update %s with a single click', 'emd-plugins'),strtolower($plural_label)) . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Take periodic backups to protect yourself against data loss', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Reset and start from scratch anytime', 'emd-plugins') . '</li>
		</ul>
		<ul class="right">
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Create custom fields with ease', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . sprintf(__('Migrate %s from one site to another with ease', 'emd-plugins'),strtolower($plural_label))  . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . sprintf('See what changed with powerful reports', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . sprintf('Use Google Spreadsheets or Microsoft Excel to create CSV', 'emd-plugins') . '</li>
		</ul>
		</div>
		</div>
		<div class="emd-oper-modal-button">
			<a href="' . esc_url("https://emdplugins.com/plugin-pricing/software-issue-manager-wordpress-plugin-pricing/?pk_campaign=upgradelink&pk_kwd=operations") . '" class="emdmodalbtn" target="_blank" rel="noopener noreferrer">';
	}
	elseif($type == 'yt_api'){
		echo '<h2>' . sprintf(__('Get your YouTube videos and update stats with a few clicks', 'emd-plugins'),$plural_label) . '</h2>
		<div style="max-width:450px;margin: auto;"><strong>' . sprintf(__('You can get your YouTube videos in WordPress and update their stats on demand or schedule. Bonus: Video updates are done automatically. ', 'emd-plugins'),$plural_label) . '</strong></div>
		<div style="max-width:450px;margin: auto;padding-bottom:15px;">' . 
	sprintf(__('You can add multiple playlists and/or usernames and decide how often you want to get new videos or make updates to existing ones. Easy!', 'emd-plugins'),$plural_label) .
		'</div>
		<div class="emd-flex emdflexrow">
		<ul class="left">
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . sprintf(__('Import from unlimited playlists', 'emd-plugins'),strtolower($plural_label),$plugin_name) . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . sprintf(__('Import from unlimited usernames', 'emd-plugins'),strtolower($plural_label)) . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('On-demand video imports and updates', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('On-schedule imports and updates', 'emd-plugins') . '</li>
		</ul>
		<ul class="right">
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Set your batch size for each run', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . sprintf(__('On-demand video stat updates', 'emd-plugins'),strtolower($plural_label))  . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . sprintf('On-schedule video stat updates', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . sprintf('See what changed with powerful reports', 'emd-plugins') . '</li>
		</ul>
		</div>
		</div>
		<div class="emd-oper-modal-button">
			<a href="' . esc_url("https://emdplugins.com/plugin-pricing/software-issue-manager-wordpress-plugin-pricing/?pk_campaign=upgradelink&pk_kwd=youtube-api") . '" class="emdmodalbtn" target="_blank" rel="noopener noreferrer">';

	}
	elseif($type == 'cust_fields'){
		echo '<h2>' . __('Gather, display and search information with ease') . '</h2>
		<div style="max-width:470px;margin: auto;margin-bottom:15px;"><strong>' . __('EMD Custom Field Builder is an easy to use yet powerful tool to customize your plugin by adding custom fields and taxonomies. ', 'emd-plugins') . '</strong>
		<span><a href="https://emdplugins.com/questions/how-to-use-emd-custom-field-builder/?pk_campaign=' . $app . '-cust-fields&pk_kwd=learnmore" target="_blank">' . 
		__('Learn more', 'emd-plugins') . '</a>' . 
		'</span><div style="font-size:85%">* <a href="https://emdplugins.com/best-form-builder-for-wordpress/?pk_campaign=' . $app . '-cust-fields&pk_kwd=formbuilder" target="_blank">EMD Form Builder</a> is required</div></div>
		<div class="emd-flex emdflexrow">
		<ul class="left">
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Create unlimited number of custom fields', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Provide additional level of information', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Include custom fields in submit forms to gather information *', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Include custom fields in search forms to allow finding information *', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Create 55+ different types of custom fields', 'emd-plugins') . '</li>
		</ul>
		<ul class="right">
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Create 4 types of custom taxonomies to group and categorize information', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Make custom fields as "Required" not to allow empty values', 'emd-plugins')  . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Set a default value to offer initial option', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Enable or disable custom fields with a single click', 'emd-plugins') . '</li>
		<li><span class="emdiconpad dashicons dashicons-thumbs-up" aria-hidden="true"></span>' . __('Limit access to information by displaying custom fields to selected user roles only', 'emd-plugins') . '</li>
		</ul>
		</div>
		</div>
		<div class="emd-oper-modal-button">
			<a href="' . esc_url("https://emdplugins.com/plugin-pricing/software-issue-manager-wordpress-plugin-pricing/?pk_campaign=" . $app . "-cust-fields&pk_kwd=upgradelink") . '" class="emdmodalbtn" target="_blank" rel="noopener noreferrer">';

	}
	echo esc_html('Upgrade Now', 'emd-plugins') .
		'</a>
		<br>
		<p style="margin: 10px 0 0;font-style:italic;font-size: 13px;color:red"><span class="emdiconpad dashicons dashicons-heart"></span>You will love it!</p>
		</div>
		</div>
	<div class="emd-wrapper"></div></div>';
}
function emd_lite_get_filters($textdomain){
	echo '<style>
	#wpbody-content .metabox-holder {
		padding: 35px 20px 0 0;
	}
	#emd-afc-filters.wrap #filters-wrap {
		margin:36px 0 0;
	}
	#filters-wrap h3 {
		cursor:pointer;
		background-color:#F0FFF0;
	}
	#filters-wrap .handlediv:after{
		width: 36px;
		height: 36px;
		right: 0;
		content: "\f142";
		font: 400 25px/1 dashicons;
		speak: none;
		display: inline-block;
		padding: 5px;
		bottom: 2px;
		position: relative;
		vertical-align: bottom;
		-webkit-font-smoothing: antialiased;
		-moz-osx-font-smoothing: grayscale;
		text-decoration: none!important;
		color: #72777c;
	}
	#filters-wrap.closed .handlediv:after{
		content: "\f140";
	}

	</style>';
	echo '<div id="emd-afc-filters" class="metabox-holder meta-box-sortables">
		<div id="filters-wrap" class="postbox">
		<div class="handlediv upgrade-pro" title="' . __('Update to a Premium Edition to unlock Filters & Columns', $textdomain) . '"></div>
		<h3 class="upgrade-pro" title="' . __('Update to a Premium Edition to unlock Filters & Columns', $textdomain) . '">' . __('Filters &amp; Columns', $textdomain ) . '</h3>
	</div></div>';
}
