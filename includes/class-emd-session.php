<?php
/**
 * EMD Session
 *
 * This is a wrapper class for WP_Session / PHP $_SESSION and handles the storage of login, file uplaod sessions, etc
 *
 * @package     EMD
 * @copyright   Copyright (c) 2016,  Emarket Design
 * @since       5.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Emd_Session Class
 *
 * @since WPAS 5.3
 */
class Emd_Session {

	/**
	 * Holds our session data
	 *
	 * @var array
	 * @access private
	 */
	private $session;

	/**
	 * Session index prefix
	 *
	 * @var string
	 * @access private
	 */
	private $prefix = '';
	/**
	 * Session for app_name
	 *
	 * @var string
	 * @access private
	 */
	private $app_name = '';

	/**
	 * Get things started
	 *
	 * Defines our WP_Session constants, includes the necessary libraries and
	 * retrieves the WP Session instance
	 *
	 */
	public function __construct($myapp) {
		if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
			define( 'WP_SESSION_COOKIE', $myapp . '_wp_session' );
		}
		if(!function_exists('create_emd_sessions_table')){
			require_once constant(strtoupper($myapp) . "_PLUGIN_DIR") . 'assets/ext/wp-session/wp-session-manager.php';
		}
		add_filter( 'wp_session_cookie_secure',     array( $this, 'emd_set_cookie_secure_flag' ), 10, 1 ); // Set the SECURE flag on the cookie
		add_filter( 'wp_session_cookie_httponly',   array( $this, 'emd_set_http_only_flag' ), 10, 1 ); // Set the SECURE flag on the cookie
		add_filter( 'wp_session_delete_batch_size', array( $this, 'emd_set_session_delete_batch_Size' ), 10, 1 ); // Set the number of expired session objects to delete on every clean-up pass

		$this->init();
	}

	/**
	 * Setup the WP_Session instance
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		$open_session = apply_filters('emd_initiate_session_flag', true);

		if(true === $open_session){
			$this->session = WP_Session::get_instance();
		}	
	}

	/**
	 * Add new session variable
	 *
	 * @since 3.2
	 *
	 * @param string $key   Name of the session to add
	 * @param mixed  $value Session value
	 * @param bool   $add   Whether to add the new value to the previous one or just update
	 *
	 * @return void
	 */
	public function set( $key, $value, $add = false ) {
		$key   = sanitize_text_field( $key );
		$value = $this->sanitize( $value );
		if ( true === $this->session->offsetExists( $key ) && true === $add ) {
			$old = $this->get( $key );
			if ( ! is_array( $old ) ) {
				$old = (array) $old;
			}
			$new                   = array_push( $old, $value );
			$this->session[ $key ] = serialize( $new );

		} else {
			$this->session[ $key ] = $value;
		}
	}

	/**
	 * Get session value
	 *
	 * @since 3.2
	 *
	 * @param string $key     Session key to retrieve the value for
	 * @param mixed  $default Value to return if the key doesn't exist
	 *
	 * @return mixed
	 */
	public function get( $key, $default = false ) {
		$value = $default;
		$key   = sanitize_text_field( $key );

		if ( true === $this->session->offsetExists( $key ) ) {
			$value = $this->session[ $key ];
		}

		return maybe_unserialize( $value );
	}

	/**
	 * Get current session superglobal
	 *
	 * @since 3.2
	 * @return array
	 */
	public function get_session() {
		return $this->session;
	}

	/**
	 * Clean a session
	 *
	 * @since 3.2
	 *
	 * @param string $key Name of the session to clean
	 *
	 * @return bool True if the session was cleaned, false otherwise
	 */
	public function clean( $key ) {
		$key     = sanitize_text_field( $key );
		$cleaned = false;
		if ( true === $this->session->offsetExists( $key ) ) {
			unset( $this->session[ $key ] );
			$cleaned = true;
		}
		return $cleaned;
	}

	/**
	 * Reset the entire session
	 *
	 * @since 3.2
	 * @return void
	 */
	public function reset() {
		$this->session = array();
	}

	/**
	 * Sanitize session value
	 *
	 * @since 3.2
	 *
	 * @param mixed $value Value to sanitize
	 *
	 * @return string Sanitized value
	 */
	public function sanitize( $value ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			$value = serialize( $value );
		}
		return $value;
	}


	/**
	 * Set the secure flag on the cookie
	 *
	 * Filter: wp_session_cookie_secure
	 *
	 * @param boolean $secure_flag
	 *
	 * @since 4.0.4
	 *
	 * @return boolean flag - true or false, default false
	 */
	public function emd_set_cookie_secure_flag ( $secure_flag ) {
		$secure_flag = boolval( get_option( 'secure_cookies', false) );
		return $secure_flag;
	}

	/**
	 * Set the httponly flag on the cookie
	 *
	 * Filter: wp_session_cookie_httponly
	 *
	 * @param boolean $http_only_flag
	 *
	 * @since 4.0.4
	 *
	 * @return boolean flag - true or false, default false
	 */
	public function emd_set_http_only_flag ( $http_only_flag ) {
		$http_only_flag = boolval( get_option( 'cookie_http_only', false) );
		return $http_only_flag;
	}

	/**
	 * Set the amount of expired sessions to delete in one pass
	 *
	 * Filter: wp_session_delete_batch_size
	 *
	 * @param boolean $batch_size
	 *
	 * @since 4.2.0
	 *
	 * @return number - number of expired sessions to delete in every call
	 */
	public function emd_set_session_delete_batch_Size ( $batch_size ) {
		$batch_size = intval( get_option( 'session_delete_batch_size', 1000 ) ) ;
		return $batch_size;
	}
}
