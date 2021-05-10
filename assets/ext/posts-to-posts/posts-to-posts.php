<?php
if ( ! defined( 'EMD_P2P_PLUGIN_VERSION' ) ){
	define( 'EMD_P2P_PLUGIN_VERSION', '1.4.3' );
}
if ( ! defined( 'EMD_P2P_TEXTDOMAIN' ) ){
	define( 'EMD_P2P_TEXTDOMAIN', 'emd-plugins' );
}
require dirname( __FILE__ ) . '/scb/load.php';

if (!function_exists('_p2p_load_files')){
function _p2p_load_files( $dir, $files ) {
        foreach ( $files as $file )
                require_once "$dir/$file.php";
}
}

if (!function_exists('_p2p_load')){
function _p2p_load() {
	if ( function_exists( 'p2p_register_connection_type' ) ) return;
	$base = dirname( __FILE__ );


	_p2p_load_files( "$base/core", array(
		'storage', 'query', 'query-post', 'query-user', 'url-query',
		'util', 'item', 'list', 'side',
		'type-factory', 'type', 'directed-type', 'indeterminate-type',
		'api', 'extra'
	) );

	P2P_Widget::init();
	P2P_Shortcodes::init();

	if ( is_admin() ) {
		_p2p_load_files( "$base/admin", array(
			'mustache', 'factory',
			'box-factory', 'box', 'fields',
			'column-factory', 'column',
		) );
	}

	register_uninstall_hook( __FILE__, array( 'P2P_Storage', 'uninstall' ) );
}
}
scb_init( '_p2p_load' );

if (!function_exists('_p2p_init')){
function _p2p_init() {
	// Safe hook for calling p2p_register_connection_type()
	do_action( 'p2p_init' );
}
}
add_action( 'wp_loaded', '_p2p_init' );

