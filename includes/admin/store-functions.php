<?php
/**
 * Add-On Page Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.2
 */
if (!defined('ABSPATH')) exit;
/**
 * Show emdplugins plugins and extensions
 *
 * @param string $textdomain
 * @since WPAS 4.2
 *
 * @return html page content
 */
if (!function_exists('emd_display_store')) {
	function emd_display_store($textdomain) {
		global $title;
		wp_enqueue_script('jquery');
		ob_start(); ?>
	<div class="wrap">
	<h2><?php echo $title;?> &nbsp;&mdash;&nbsp;<a href="https://emdplugins.com/plugins?pk_source=plugin-addons-page&pk_medium=plugin&pk_campaign=<?php echo $textdomain;?>-addonspage&pk_content=browseall" class="button-primary" title="<?php _e( 'Browse All', 'emd-plugins' ); ?>" target="_blank"><?php _e( 'Browse All', 'emd-plugins' ); ?></a>
	</h2>
	<p><?php _e('The following plugins extend and expand the functionality of your app.','emd-plugins'); ?></p>
			<?php echo emd_add_ons('tabs',$textdomain); ?>
		</div>
		<?php
		echo ob_get_clean();
	}
}
/**
 * Get plugin and extension list from emdplugins site and save it in a transient
 *
 * @since WPAS 4.2
 *
 * @return $cache html content
 */
if (!function_exists('emd_add_ons')) {
	function emd_add_ons($type,$textdomain) {
		if($type == 'tabs'){
			require_once(constant(strtoupper(str_replace("-", "_", $textdomain)) . "_PLUGIN_DIR") . '/includes/admin/tabs.php');
		}
		elseif($type == 'plugin-support'){
			require_once(constant(strtoupper(str_replace("-", "_", $textdomain)) . "_PLUGIN_DIR") . '/includes/admin/plugin-support.php');
		}	
	}
}
/**
 * Show support info
 *
 * @param string $textdomain
 * @since WPAS 4.3
 *
 * @return html page content
 */
if (!function_exists('emd_display_support')) {
	function emd_display_support($textdomain,$show_review,$rev=''){
		global $title;
		ob_start(); ?>
		<div class="wrap">
		<h2><?php echo $title;?></h2>
		<div id="support-header"><?php printf(__('Thanks for installing %s.','emd-plugins'),constant(strtoupper(str_replace("-", "_", $textdomain)) . '_NAME'));?> &nbsp; <?php  printf(__('All support requests are accepted through <a href="%s" target="_blank">our support site.</a>','emd-plugins'),'https://emdplugins.com/support/?pk_source=support-page&pk_medium=plugin&pk_campaign=plugin-support&pk_content=supportlink'); ?>
	<?php 
		switch($show_review){
			case '1':
			//if prodev or freedev generation
			emd_display_review('wp-app-studio');
			break;
			case '2':
			//eMarketDesign free plugin
			emd_display_review($rev);
			break;
			default:
			echo "<br></br>";
			break;
		}
		echo '</div>';
		echo emd_add_ons('plugin-support',$textdomain); 
		echo '</div>';
		echo ob_get_clean();
	}
}
if (!function_exists('emd_display_review')) {
	function emd_display_review($plugin){
	?>
	<div id="plugin-review">
	<div class="plugin-review-text"><a href="https://wordpress.org/support/view/plugin-reviews/<?php echo $plugin; ?>" target="_blank"><?php _e('Like our plugin? Leave us a review','emd-plugins'); ?></a>
	</div><div class="plugin-review-star"><span class="dashicons dashicons-star-filled"></span>
	<span class="dashicons dashicons-star-filled"></span>
	<span class="dashicons dashicons-star-filled"></span>
	<span class="dashicons dashicons-star-filled"></span>
	<span class="dashicons dashicons-star-filled"></span>
	</div>
	</div>
	<?php
	}
}
