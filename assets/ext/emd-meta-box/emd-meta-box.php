<?php
/* Fork from Meta-Box plugin
*/

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Script version, used to add version for scripts and styles
define( 'EMD_MB_VER', '4.3.8' );

// Define plugin URLs, for fast enqueuing scripts and styles
if ( ! defined( 'EMD_MB_URL' ) )
	define( 'EMD_MB_URL', plugin_dir_url( __FILE__ ) );
define( 'EMD_MB_JS_URL', trailingslashit( EMD_MB_URL . 'js' ) );
define( 'EMD_MB_CSS_URL', trailingslashit( EMD_MB_URL . 'css' ) );

// Plugin paths, for including files
if ( ! defined( 'EMD_MB_DIR' ) )
	define( 'EMD_MB_DIR', plugin_dir_path( __FILE__ ) );
define( 'EMD_MB_INC_DIR', trailingslashit( EMD_MB_DIR . 'inc' ) );
define( 'EMD_MB_FIELDS_DIR', trailingslashit( EMD_MB_INC_DIR . 'fields' ) );

// Optimize code for loading plugin files ONLY on admin side
// @see http://www.deluxeblogtips.com/?p=345

// Helper function to retrieve meta value
//require_once EMD_MB_INC_DIR . 'helpers.php';

//if ( is_admin() )
//{
	require_once EMD_MB_INC_DIR . 'field.php';

	// Field classes
	foreach ( glob( EMD_MB_FIELDS_DIR . '*.php' ) as $file )
	{
		require_once $file;
	}

	// Main file
	require_once EMD_MB_INC_DIR . 'meta-box.php';
	require_once EMD_MB_INC_DIR . 'init.php';
//}

// Helper function to retrieve meta value
require_once EMD_MB_INC_DIR . 'helpers.php';

if (!function_exists('emd_hidden_begin_html')){
	add_filter( 'emd_mb_hidden_begin_html', 'emd_hidden_begin_html' );
	function emd_hidden_begin_html()
	{
		return '<div class="emd-mb-label"></div><div class="emd-mb-input">';
	}
}
if (!function_exists('emd_hidden_end_html')){
	add_filter( 'emd_mb_hidden_end_html', 'emd_hidden_end_html' );
	function emd_hidden_end_html( )
	{
		return '</div>';
	}
}
