<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Meta Box Class
if ( ! class_exists( 'EMD_Meta_Box' ) )
{
	/**
	 * A class to rapid develop meta boxes for custom & built in content types
	 *
	 */
	class EMD_Meta_Box
	{
		/**
		 * @var array Meta box information
		 */
		public $meta_box;

		/**
		 * @var array Fields information
		 */
		public $fields;

		/**
		 * @var array Contains all field types of current meta box
		 */
		public $types;

		/**
		 * @var array Validation information
		 */
		public $validation;

		/**
		 * @var array conditional information
		 */
		public $conditional;

		public $saved = false;

		/**
		 * Create meta box based on given data
		 *
		 * @see demo/demo.php file for details
		 *
		 * @param array $meta_box Meta box definition
		 *
		 * @return EMD_Meta_Box
		 */
		function __construct( $meta_box )
		{
			// Run script only in admin area
			if ( ! is_admin() )
				return;

			// Assign meta box values to local variables and add it's missed values
			$this->meta_box   = self::normalize( $meta_box );
			$this->fields     = &$this->meta_box['fields'];
			$this->validation = &$this->meta_box['validation'];
			$this->conditional = &$this->meta_box['conditional'];
			if(!defined('EMD_MB_APP') && !empty($this->meta_box['app_name'])){
				define('EMD_MB_APP', $this->meta_box['app_name']);
			}
			if(!empty($this->meta_box['tax_conditional'])){
				if(!empty($this->conditional)){
					$this->conditional =array_merge($this->conditional,$this->meta_box['tax_conditional']);
				}
				else {	
					$this->conditional =$this->meta_box['tax_conditional'];
				}
			}


			// Allow users to show/hide meta box
			// 1st action applies to all meta boxes
			// 2nd action applies to only current meta box
			$show = true;
			$show = apply_filters( 'emd_mb_show', $show, $this->meta_box );
			$show = apply_filters( "emd_mb_show_{$this->meta_box['id']}", $show, $this->meta_box );
			if ( !$show )
				return;

			// Enqueue common styles and scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			// Add additional actions for fields
			$fields = self::get_fields( $this->fields );
			foreach ( $fields as $field )
			{
				if($field['type'] == 'thickbox_image'){
                                        call_user_func(array(self::get_class_name($field), 'set_field'), $field);
                                }
				call_user_func( array( self::get_class_name( $field ), 'add_actions' ) );
			}

			// Add meta box
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

			// Hide meta box if it's set 'default_hidden'
			add_filter( 'default_hidden_meta_boxes', array( $this, 'hide' ), 10, 2 );

			// Save post meta
			add_action( 'save_post', array( $this, 'save_post' ) );

			// Attachment uses other hooks
			// @see wp_update_post(), wp_insert_attachment()
			add_action( 'edit_attachment', array( $this, 'save_post' ) );
			add_action( 'add_attachment', array( $this, 'save_post' ) );
		}

		/**
		 * Enqueue common styles
		 *
		 * @return void
		 */
		function admin_enqueue_scripts()
		{
			$jqvalid_msg['required'] = __('This field is required.','emd-plugins');
			$jqvalid_msg['remote'] = __('Please fix this field.','emd-plugins');
			$jqvalid_msg['email'] = __('Please enter a valid email address.','emd-plugins');
			$jqvalid_msg['url'] = __('Please enter a valid URL.','emd-plugins');
			$jqvalid_msg['date'] = __('Please enter a valid date.','emd-plugins');
			$jqvalid_msg['dateISO'] = __('Please enter a valid date ( ISO )','emd-plugins');
			$jqvalid_msg['number'] = __('Please enter a valid number.','emd-plugins');
			$jqvalid_msg['digits']= __('Please enter only digits.','emd-plugins');
			$jqvalid_msg['creditcard']= __('Please enter a valid credit card number.','emd-plugins');
			$jqvalid_msg['equalTo']= __('Please enter the same value again.','emd-plugins');
			$jqvalid_msg['maxlength']= __('Please enter no more than {0} characters.','emd-plugins');
			$jqvalid_msg['minlength']= __('Please enter at least {0} characters.','emd-plugins');
			$jqvalid_msg['rangelength']= __('Please enter a value between {0} and {1} characters long.','emd-plugins');
			$jqvalid_msg['range']= __('Please enter a value between {0} and {1}.','emd-plugins');
			$jqvalid_msg['max']= __('Please enter a value less than or equal to {0}.','emd-plugins');
			$jqvalid_msg['min']= __('Please enter a value greater than or equal to {0}.','emd-plugins');


			$screen = get_current_screen();

			// Enqueue scripts and styles for registered pages (post types) only
			if ( 'post' != $screen->base || ! in_array( $screen->post_type, $this->meta_box['pages'] ) )
				return;

			wp_enqueue_style( 'emd-mb', EMD_MB_CSS_URL . 'style.css', array(), EMD_MB_VER );

			// Load clone script conditionally
			$has_clone = false;
			$fields = self::get_fields( $this->fields );

			foreach ( $fields as $field )
			{
				if ( $field['clone'] )
					$has_clone = true;

				// Enqueue scripts and styles for fields
				call_user_func( array( self::get_class_name( $field ), 'admin_enqueue_scripts' ) );
			}

			if ( $has_clone )
				wp_enqueue_script( 'emd-mb-clone', EMD_MB_JS_URL . 'clone.js', array( 'jquery' ), EMD_MB_VER, true );

			if ($this->validation || ( $this->validation  && $this->conditional))
			{
				wp_enqueue_script( 'jquery-validate', EMD_MB_URL . '../jvalidate/wpas.validate.min.js', array( 'jquery' ), EMD_MB_VER, true );
				wp_enqueue_script( 'emd-mb-validate-cond', EMD_MB_JS_URL . 'validate-cond.js', array( 'jquery-validate' ), EMD_MB_VER, true );
				wp_localize_script('emd-mb-validate-cond','validate_msg',$jqvalid_msg);
			}
			else if($this->conditional)
			{
				wp_enqueue_script( 'emd-mb-validate-cond', EMD_MB_JS_URL . 'validate-cond.js', array(),EMD_MB_VER, true );
			}

			// Auto save
			if ( $this->meta_box['autosave'] )
				wp_enqueue_script( 'emd-mb-autosave', EMD_MB_JS_URL . 'autosave.js', array( 'jquery' ), EMD_MB_VER, true );
		}

		/**
		 * Get all fields of a meta box, recursively
		 *
		 * @param array $fields
		 *
		 * @return array
		 */
		static function get_fields( $fields )
		{
			$all_fields = array();
			foreach ( $fields as $field )
			{
				$all_fields[] = $field;
				if ( isset( $field['fields'] ) )
					$all_fields = array_merge( $all_fields, self::get_fields( $field['fields'] ) );
			}

			return $all_fields;
		}

		/**************************************************
		 SHOW META BOX
		 **************************************************/

		/**
		 * Add meta box for multiple post types
		 *
		 * @return void
		 */
		function add_meta_boxes()
		{
			$screen = get_current_screen();
			if(!empty($screen) && in_array($screen->post_type, $this->meta_box['pages'])){
				foreach ( $this->meta_box['pages'] as $page )
				{
					add_meta_box(
						$this->meta_box['id'],
						$this->meta_box['title'],
						array( $this, 'show' ),
						$page,
						$this->meta_box['context'],
						$this->meta_box['priority']
					);
				}
			}
		}

		/**
		 * Hide meta box if it's set 'default_hidden'
		 *
		 * @param array  $hidden Array of default hidden meta boxes
		 * @param object $screen Current screen information
		 *
		 * @return array
		 */
		function hide( $hidden, $screen )
		{
			if (
				'post' === $screen->base
				&& in_array( $screen->post_type, $this->meta_box['pages'] )
				&& $this->meta_box['default_hidden']
			)
			{
				$hidden[] = $this->meta_box['id'];
			}
			return $hidden;
		}

		/**
		 * Callback function to show fields in meta box
		 *
		 * @return void
		 */
		function show()
		{
			global $post;

			$saved = self::has_been_saved( $post->ID, $this->fields );

			// Container
			printf(
				'<div class="emd-mb-meta-box" data-autosave="%s">',
				$this->meta_box['autosave']  ? 'true' : 'false'
			);

			wp_nonce_field( "emd-mb-save-{$this->meta_box['id']}", "nonce_{$this->meta_box['id']}" );

			// Allow users to add custom code before meta box content
			// 1st action applies to all meta boxes
			// 2nd action applies to only current meta box
			do_action( 'emd_mb_before' );
			do_action( "emd_mb_before_{$this->meta_box['id']}" );

			foreach ( $this->fields as $field )
			{
				if(empty($field['custom']) || (!empty($field['custom']) && $this->meta_box['id'] == 'emd_cust_field_meta_box')){
					call_user_func( array( self::get_class_name( $field ), 'show' ), $field, $saved );
				}
			}

			// Allow users to add custom code after meta box content
			// 1st action applies to all meta boxes
			// 2nd action applies to only current meta box
			do_action( 'emd_mb_after' );
			do_action( "emd_mb_after_{$this->meta_box['id']}" );

			// End container
			echo '</div>';
			
			// Include validation settings for this meta-box

			if((isset( $this->validation ) && $this->validation)  || (isset($this->conditional) && $this->conditional))
			{
				echo '
				<script>
				if ( typeof emd_mb == "undefined" ) {
					var emd_mb = {	';
			}
			if(isset( $this->validation ) && $this->validation){
				echo '
					validationOptions : jQuery.parseJSON( \'' . json_encode( $this->validation ) . '\' ),
					summaryMessage : "' . __( 'Please correct the errors highlighted below and try again.', 'emd-plugins' ) . '",';
			}
			if(isset($this->conditional) && $this->conditional){
				echo 'conditional: jQuery.parseJSON( \'' . json_encode($this->conditional) . '\' ),';
			}
			if((isset( $this->validation ) && $this->validation)  || (isset($this->conditional) && $this->conditional))
			{
				echo '	
					};
				}
				else
				{ ';
			}
			if(isset( $this->validation ) && $this->validation){
				echo '	
					var tempOptions = jQuery.parseJSON( \'' . json_encode( $this->validation ) . '\' );
					jQuery.extend( true, emd_mb.validationOptions, tempOptions ); ';
			}
			if(isset($this->conditional) && $this->conditional){
				echo '
					var tempConditionals = jQuery.parseJSON( \'' . json_encode( $this->conditional ) . '\' );					     jQuery.extend( true, emd_mb.conditional, tempConditionals ); ';	
			}
			if((isset( $this->validation ) && $this->validation)  || (isset($this->conditional) && $this->conditional))
			{	
				echo '	
				};
				</script>
				';
			}

		}

		/**************************************************
		 SAVE META BOX
		 **************************************************/

		/**
		 * Save data from meta box
		 *
		 * @param int $post_id Post ID
		 *
		 * @return void
		 */
		function save_post( $post_id )
		{
			// Check if this function is called to prevent duplicated calls like revisions, manual hook to wp_insert_post, etc.
			if ( $this->saved === true )
				return;
			$this->saved = true;

			// Check whether form is submitted properly
			$id = $this->meta_box['id'];
			if ( empty( $_POST["nonce_{$id}"] ) || !wp_verify_nonce( $_POST["nonce_{$id}"], "emd-mb-save-{$id}" ) )
				return;

			// Autosave
			if ( defined( 'DOING_AUTOSAVE' ) && !$this->meta_box['autosave'] )
				return;

			// Make sure meta is added to the post, not a revision
			if ( $the_post = wp_is_post_revision( $post_id ) )
				$post_id = $the_post;

			// Before save action
			do_action( 'emd_mb_before_save_post', $post_id );
			do_action( "emd_mb_{$this->meta_box['id']}_before_save_post", $post_id );

			foreach ( $this->fields as $field )
			{
				$name = $field['id'];
				$single = $field['clone'] || ! $field['multiple'];
				$old  = get_post_meta( $post_id, $name, $single );
				$new  = isset( $_POST[$name] ) ? $_POST[$name] : ( $single ? '' : array() );

				// Allow field class change the value
				$new = call_user_func( array( self::get_class_name( $field ), 'value' ), $new, $old, $post_id, $field );

				// Use filter to change field value
				// 1st filter applies to all fields with the same type
				// 2nd filter applies to current field only
				$new = apply_filters( "emd_mb_{$field['type']}_value", $new, $field, $old );
				$new = apply_filters( "emd_mb_{$name}_value", $new, $field, $old );

				// Call defined method to save meta value, if there's no methods, call common one
				call_user_func( array( self::get_class_name( $field ), 'save' ), $new, $old, $post_id, $field );
			}

			// After save action
			do_action( 'emd_mb_after_save_post', $post_id );
			do_action( "emd_mb_{$this->meta_box['id']}_after_save_post", $post_id );

			// Done saving post meta
			$called = false;
		}

		/**************************************************
		 HELPER FUNCTIONS
		 **************************************************/

		/**
		 * Normalize parameters for meta box
		 *
		 * @param array $meta_box Meta box definition
		 *
		 * @return array $meta_box Normalized meta box
		 */
		static function normalize( $meta_box )
		{
			// Set default values for meta box
			$meta_box = wp_parse_args( $meta_box, array(
				'id'             => sanitize_title( $meta_box['title'] ),
				'context'        => 'normal',
				'priority'       => 'high',
				'pages'          => array( 'post' ),
				'autosave'       => false,
				'default_hidden' => false,
			) );

			// Set default values for fields
			$meta_box['fields'] = self::normalize_fields( $meta_box['fields'] );

			return $meta_box;
		}

		/**
		 * Normalize an array of fields
		 *
		 * @param array $fields Array of fields
		 *
		 * @return array $fields Normalized fields
		 */
		static function normalize_fields( $fields )
		{
			foreach ( $fields as &$field )
			{
				$field = wp_parse_args( $field, array(
					'multiple'      => false,
					'clone'         => false,
					'max_clone'     => 0,
					'sort_clone'    => false,
					'std'           => '',
					'desc'          => '',
					'format'        => '',
					'before'        => '',
					'after'         => '',
					'field_name'    => isset( $field['id'] ) ? $field['id'] : '',
					'required'      => false,
					'placeholder'   => ''
				) );

				// Allow field class add/change default field values
				$field = call_user_func( array( self::get_class_name( $field ), 'normalize_field' ), $field );

				if ( isset( $field['fields'] ) )
					$field['fields'] = self::normalize_fields( $field['fields'] );
			}

			return $fields;
		}

		/**
		 * Get field class name
		 *
		 * @param array $field Field array
		 *
		 * @return bool|string Field class name OR false on failure
		 */
		static function get_class_name( $field )
		{
			// Convert underscores to whitespace so ucwords works as expected. Otherwise: plupload_image -> Plupload_image instead of Plupload_Image
			$type = str_replace( '_', ' ', $field['type'] );

			// Uppercase first words
			$class = 'EMD_MB_' . ucwords( $type ) . '_Field';

			// Relace whitespace with underscores
			$class = str_replace( ' ', '_', $class );
			return class_exists( $class ) ? $class : false;
		}

		/**
		 * Check if meta box has been saved
		 * This helps saving empty value in meta fields (for text box, check box, etc.)
		 *
		 * @param int   $post_id
		 * @param array $fields
		 *
		 * @return bool
		 */
		static function has_been_saved( $post_id, $fields )
		{
			foreach ( $fields as $field )
			{
				$value = get_post_meta( $post_id, $field['id'], !$field['multiple'] );
				if (
					( !$field['multiple'] && '' !== $value )
					|| ( $field['multiple'] && array() !== $value )
				)
				{
					return true;
				}
			}
			return false;
		}
	}
}
