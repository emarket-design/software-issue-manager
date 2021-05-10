<?php
/**
 * Emd List Table
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd List Table class
 * Creates a list page for forms, shortcodes in admin
 *
 * @since WPAS 4.0
 */
class Emd_List_Table extends WP_List_Table {

	public $per_page;
	public $type;
	public $app;
	public $has_bulk;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct($app,$type,$has_bulk) {

		// Utilize the parent constructor to build the main class properties.
		parent::__construct(
			array(
				'singular' => $type,
				'plural'   => $type . 's',
				'ajax'     => false,
			)
		);

		$this->app = $app;
		$this->type = $type;
		$this->has_bulk = $has_bulk;
		// Default number to show per page
		$this->per_page = 20;
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 1.0.0
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		if(!empty($this->has_bulk)){
			if($this->type == 'cfield'){
				$columns = array(
					'cb'        => '<input type="checkbox" />',
					'name' => esc_html__( 'Name', 'emd-plugins' ),
					'label' => esc_html__( 'Label', 'emd-plugins' ),
					'entity' => esc_html__( 'Entity', 'emd-plugins' ),
					'ctype' => esc_html__( 'Type', 'emd-plugins' ),
					'dtype' => esc_html__( 'Display Type', 'emd-plugins' ),
					'created'   => esc_html__( 'Updated', 'emd-plugins' ),
				);
			}
			else {
				$columns = array(
					'cb'        => '<input type="checkbox" />',
					'name' => esc_html__( 'Name', 'emd-plugins' ),
					'type' => esc_html__( 'Type', 'emd-plugins' ),
					'shortcode' => esc_html__( 'Shortcode', 'emd-plugins' ),
					'created'   => esc_html__( 'Created', 'emd-plugins' ),
				);
			}
		}
		else {
			$columns = array(
				'name' => esc_html__( 'Name', 'emd-plugins' ),
				'type' => esc_html__( 'Type', 'emd-plugins' ),
				'shortcode' => esc_html__( 'Shortcode', 'emd-plugins' ),
			);
		}
		return $columns; 
	}

	/**
	 * Render the checkbox column.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $rec
	 *
	 * @return string
	 */
	public function column_cb( $rec ) {
		if(empty($rec['default']) && !empty($this->has_bulk)){
			if($this->type == 'shortcode'){
				return '<input type="checkbox" name="rec_id[]" value="' . absint( $rec['ush_id'] ) . '" />';
			}
			elseif($this->type == 'form'){
				return '<input type="checkbox" name="rec_id[]" value="' . absint( $rec['id'] ) . '" />';
			}
			elseif($this->type == 'cfield'){
				return '<input type="checkbox" name="rec_id[]" value="' . $rec['id'] . '" />';
			}
		}
	}

	/**
	 * Renders the columns.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $rec
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $rec, $column_name ) {
		switch ( $column_name ) {
			case 'name':
				$value = $rec['name'];
				break;
			case 'type':
				$value = $rec['type'];
				break;
			case 'label':
				$value = $rec['label'];
				break;
			case 'entity':
				$value = $rec['entity'];
				break;
			case 'ctype':
				if($rec['ctype'] == 'attr'){
					$value = __('Attribute','emd-plugins');
				}
				elseif($rec['ctype'] == 'tax'){
					$value = __('Taxonomy','emd-plugins');
				}
				break;
			case 'dtype':
				$value = $rec['dtype'];
				break;
			case 'shortcode':
				$value = '<p id="shortcode_' . $rec['id'] . '">' . stripslashes($rec['shortcode']) . '</p>
					<a class="emd-copy-clipb button button-primary" style="padding:1px 6px;" data-clipboard-target="#shortcode_' . $rec['id'] . '">' . __('Copy','emd-plugins') . '</a>';
				break;
			case 'created':
				$value = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $rec['created']);
				break;
			case 'modified':
				$value = date_i18n(get_option('date_format'). ' ' . get_option('time_format'), $rec['modified']);
				break;
			case 'author':
				$author = get_userdata($rec->post_author);
				$value  = $author->display_name;
				break;
			default:
				$value = '';
		}
		return $value;
	}

	/**
	 * Define bulk actions available for our table listing.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = Array();
		if(!empty($this->has_bulk)){
			if($this->type == 'cfield'){
				$actions = array(
					'delete' => esc_html__( 'Delete Field', 'emd-plugins' ),
					'delete_data' => esc_html__( 'Delete Field & Data', 'emd-plugins' ),
				);
			}
			else {
				$actions = array(
					'delete' => esc_html__( 'Delete', 'emd-plugins' ),
				);
			}
		}
		return $actions;
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_actions() {
		$ids = isset( $_GET['rec_id'] ) ? $_GET['rec_id'] : array();

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}
		if($this->type  != 'cfield'){
			$ids    = array_map( 'absint', $ids );
		}
		$action = ! empty( $_REQUEST['action'] ) ? sanitize_text_field($_REQUEST['action']) : false;

		if ( empty( $ids ) || empty( $action ) ) {
			return;
		}
		// Delete one or multiple recs - both delete links and bulk actions.
		if ( 'delete' === $this->current_action() ) {
			if (wp_verify_nonce( $_GET['_wpnonce'], 'bulk-' . $this->type . 's')){
				if($this->type  == 'shortcode'){
					$user_shortcodes = get_option($this->app . '_user_shortcodes',Array());
					foreach ( $ids as $id ) {
						unset($user_shortcodes[$id]);
					}
					update_option($this->app . '_user_shortcodes',$user_shortcodes);
				}
				elseif($this->type  == 'form'){
					foreach ( $ids as $id ) {
						wp_delete_post($id);
					}
				}
				elseif($this->type  == 'cfield'){
					$cfields = get_option($this->app . '_custom_attr_tax_list',Array());
					$attr_list = get_option($this->app . '_attr_list',Array());
					$tax_list = get_option($this->app . '_tax_list',Array());
					foreach ( $ids as $id ) {
						if(!empty($cfields[$id]) && $cfields[$id]['ctype'] == 'attr'){
							if(!empty($cfields[$id]['entity']) && !empty($attr_list[$cfields[$id]['entity']][$cfields[$id]['name']])){
								unset($attr_list[$cfields[$id]['entity']][$cfields[$id]['name']]);
							}
							unset($cfields[$id]);
						}
						elseif(!empty($cfields[$id]) && $cfields[$id]['ctype'] == 'tax'){
							foreach($cfields[$id]['entity'] as $tent){
								if(!empty($tax_list[$tent][$cfields[$id]['name']])){
									unset($tax_list[$tent][$cfields[$id]['name']]);
								}
							}
							unset($cfields[$id]);
						}
					}
					update_option($this->app . '_tax_list',$tax_list);
					update_option($this->app . '_attr_list',$attr_list);
					update_option($this->app . '_custom_attr_tax_list',$cfields);
				}
				?>
				<div class="notice updated">
					<p>
						<?php
						if ( count( $ids ) === 1 ) {
							esc_html_e( 'Record was successfully deleted.', 'emd-plugins' );
						} else {
							esc_html_e( 'Records were successfully deleted.', 'emd-plugins' );
						}
						?>
					</p>
				</div>
				<?php
			} else {
				?>
				<div class="notice updated">
					<p>
						<?php esc_html_e( 'Security check failed. Please try again.', 'emd-plugins' ); ?>
					</p>
				</div>
				<?php
			}
		}
		if ( 'delete_data' === $this->current_action() && $this->type  == 'cfield') {
			if (wp_verify_nonce( $_GET['_wpnonce'], 'bulk-' . $this->type . 's')){
				$cfields = get_option($this->app . '_custom_attr_tax_list',Array());
				$attr_list = get_option($this->app . '_attr_list',Array());
				$tax_list = get_option($this->app . '_tax_list',Array());
				foreach ( $ids as $id ) {
					if(!empty($cfields[$id]) && $cfields[$id]['ctype'] == 'attr'){
						if(!empty($cfields[$id]['entity']) && !empty($attr_list[$cfields[$id]['entity']][$cfields[$id]['name']])){
							unset($attr_list[$cfields[$id]['entity']][$cfields[$id]['name']]);
						}
						$args = Array('posts_per_page' => -1, 'post_type' => $cfields[$id]['entity'],
								'meta_key' => $cfields[$id]['name'], 'fields'=>'ids');
						$posts = get_posts($args);
						if(!empty($posts)){
							foreach($posts as $mypid){
								delete_post_meta($mypid, $cfields[$id]['name']);
							}
						}
						unset($cfields[$id]);
					}
					elseif(!empty($cfields[$id]) && $cfields[$id]['ctype'] == 'tax'){
						foreach($cfields[$id]['entity'] as $tent){
							if(!empty($tax_list[$tent][$cfields[$id]['name']])){
								unset($tax_list[$tent][$cfields[$id]['name']]);
							}
							$args = Array('posts_per_page' => -1, 'post_type' => $tent,
									'tax_query' => Array(Array('taxonomy' => $cfields[$id]['name'], 'operator' => 'EXISTS')), 'fields'=>'ids');
							$posts = get_posts($args);
							if(!empty($posts)){
								foreach($posts as $mypid){
									 wp_delete_object_term_relationships($mypid, $cfields[$id]['name']);
								}
							}
							$terms = get_terms(array('taxonomy' => $cfields[$id]['name'],'hide_empty'=>false));
							foreach($terms as $myterm){
								wp_delete_term($myterm->term_id, $cfields[$id]['name']);
							}
						}
						unset($cfields[$id]);
					}
				}
				update_option($this->app . '_tax_list',$tax_list);
				update_option($this->app . '_attr_list',$attr_list);
				update_option($this->app . '_custom_attr_tax_list',$cfields);
			}
		}
	}

	/**
	 * Fetch and setup the final data for the table.
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {

		// Process bulk actions if found.
		$this->process_bulk_actions();

		// Setup the columns.
		$columns = $this->get_columns();

		// Hidden columns (none).
		$hidden = array();

		// Define which columns can be sorted - rec name, date.
		$sortable = array(
			'name' => array( 'name', false ),
			'label' => array( 'label', false ),
			'entity' => array( 'entity', false ),
			'ctype' => array( 'ctype', false ),
			'dtype' => array( 'dtype', false ),
			'created'   => array( 'created', false ),
		);

		// Set column headers.
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Get records
		$data = Array();
		if($this->type == 'shortcode'){
			$shc_list = get_option($this->app . '_shc_list');
			//$autocomplete_list = Array();
			$count = 1;
			//moved this to forms page
			/*if (isset($shc_list['forms']) && !empty($shc_list['forms'])) {
				foreach ($shc_list['forms'] as $keyform => $myform) {
					$forms['id'] = $count;
					$forms['name'] = $keyform;
					$forms['type'] = 'Form - ' . ucfirst($myform['type']);
					$forms['shortcode'] = '[' . $keyform . ']';
					$forms['default'] = 1;
					$forms['created'] = get_option($this->app . '_activation_date');
					$count++;
					$data[] = $forms;
				}
			}*/
			if (isset($shc_list['shcs']) && !empty($shc_list['shcs'])) {
				foreach ($shc_list['shcs'] as $keyshc => $myshc) {
					if ($keyshc == 'analytics') {
						$std_analytics = 'analytics';
					}
					$views['id'] = $count;
					$views['name'] = $keyshc;
					if($myshc['type'] == 'chart'){
						$views['type'] = 'View - ' . ucfirst($myshc['type']);
					}
					else {
						$views['type'] = 'View';
					}
					$views['shortcode'] = '[' . $keyshc . ']';
					$views['default'] = 1;
					$views['created'] = get_option($this->app . '_activation_date');
					$count++;
					$data[] = $views;
				}
			}
			if (isset($shc_list['integrations']) && !empty($shc_list['integrations'])) {
				foreach ($shc_list['integrations'] as $keyshc => $myshc) {
					$views['id'] = $count;
					$views['name'] = $keyshc;
					$views['type'] = 'View';
					$views['shortcode'] = '[' . $keyshc . ']';
					$views['default'] = 1;
					$views['created'] = get_option($this->app . '_activation_date');
					$count++;
					$data[] = $views;
				}
			}
			if (isset($shc_list['charts']) && !empty($shc_list['charts'])) {
				foreach ($shc_list['charts'] as $keyshc => $myshc) {
					$views['id'] = $count;
					$views['name'] = $keyshc;
					$views['type'] = 'Chart';
					$views['shortcode'] = '[' . $keyshc . ']';
					$views['default'] = 1;
					$views['created'] = get_option($this->app . '_activation_date');
					$count++;
					$data[] = $views;
				}
			}
			$user_shortcodes = get_option($this->app . '_user_shortcodes',Array());
			if(!empty($user_shortcodes)){
				foreach($user_shortcodes as $kshc => $ushc){
					$uviews['id'] = $count;
					$uviews['ush_id'] = $kshc;
					$uviews['name'] = $ushc['name'];
					$uviews['type'] = $ushc['type'];
					$uviews['shortcode'] = $ushc['shortcode'];
					$uviews['created'] = $ushc['created'];
					$count++;
					$data[] = $uviews;
				}
			}
		}
		elseif($this->type == 'form'){
			$forms = get_posts(Array('post_type'=>'emd_form','posts_per_page'=>-1,'post_status'=>'publish'));
			if(!empty($forms)){
				foreach($forms as $myform){
					$dform = Array();
					$fcontent = json_decode($myform->post_content,true);
					if($fcontent['app'] == $this->app){
						$dform['id'] = $myform->ID;
						$dform['name'] = $myform->post_title;
						$dform['type'] = $fcontent['type'];
						$dform['shortcode'] = '[emd_form id=\"' . $myform->ID . '\"]';
						if(!empty($fcontent['source']) && $fcontent['source'] == 'plugin'){
							$dform['default'] = 1;
						}
						$dform['created'] = strtotime($myform->post_date);
						$data[] = $dform;
					}
				}
			}
		}
		elseif($this->type == 'cfield'){
			$cfields = get_option($this->app . '_custom_attr_tax_list',Array());
			$ent_list = get_option($this->app . '_ent_list',Array());
			if(!empty($cfields)){
				foreach($cfields as $kfield => $cfield){
					$sort_data[$kfield] = $cfield['created'];
					if(!empty($_GET['orderby'])){
						if($_GET['orderby'] == 'dtype' && empty($cfield[$_GET['orderby']])){
							$cfield[$_GET['orderby']] = $cfield['type'];
						}
						$sort_data[$kfield] = $cfield[$_GET['orderby']];
					}
				}
				if(!empty($_GET['order']) && $_GET['order'] == 'desc'){
					arsort($sort_data);	
				}	
				else {
					asort($sort_data);
				}
				foreach($sort_data as $mysortk => $mysortv){
					$cf['id'] = $mysortk;
					$cf['name'] = $mysortk;
					$cf['label'] = $cfields[$mysortk]['label'];
					$ent_labels = Array();
					if(is_array($cfields[$mysortk]['entity'])){
						foreach($cfields[$mysortk]['entity'] as $myent){
							$ent_labels[$myent] = $ent_list[$myent]['label'];
						}
						$cf['entity'] = implode(",",$ent_labels);
					}
					else {
						$cf['entity'] = $ent_list[$cfields[$mysortk]['entity']]['label'];
					}
					$cf['ctype'] = $cfields[$mysortk]['ctype'];
					if(empty($cfields[$mysortk]['dtype'])){
						$cfields[$mysortk]['dtype'] = $cfields[$mysortk]['type'];
					}
					$cf['dtype'] = apply_filters('emd_cust_dtype_labels',$cfields[$mysortk]['dtype'],$cfield['ctype']);
					$cf['created'] = $cfields[$mysortk]['created'];
					$data[] = $cf;
				}
			}
		}
		$total    = count($data);
		$page     = $this->get_pagenum();
		$per_page = $this->per_page;
		$first = 0;
		if($page > 1){
			$first = (($page -1) * $per_page);
		}
		$this->items = array_slice($data, $first, $per_page);
		// Finalize pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total / $per_page ),
			)
		);
	}
	/**
         * Render the rec name column with action links.
         *
         * @since 1.0.0
         *
         * @param $rec
         *
         * @return string
         */
        public function column_name( $rec ) {

                $name = $rec['name'];
		if($this->type == 'form'){
			$name = sprintf(
				'<a class="row-title" href="%s" title="%s"><strong>%s</strong></a>',
				add_query_arg(
					array(
						'edit'    => 'layout',
						'form_id' => $rec['id'],
					),
					admin_url('admin.php?page=' . $this->app . '_forms')
				),
				esc_html__('Edit Form', 'emd-plugins'),
				$name
			);
			// Build all of the row action links.
			$row_actions = array();
			// Edit.
			$row_actions['edit_settings'] = sprintf(
				'<a href="%s" title="%s">%s</a>',
				add_query_arg(
					array(
						'edit'    => 'settings',
						'form_id' => $rec['id'],
					),
					admin_url('admin.php?page=' . $this->app . '_forms')
				),
				esc_html__('Edit Settings', 'emd-plugins'),
				esc_html__('Edit Settings', 'emd-plugins')
			);
			$row_actions['edit'] = sprintf(
				'<a href="%s" title="%s">%s</a>',
				add_query_arg(
					array(
						'edit'    => 'layout',
						'form_id' => $rec['id'],
					),
					admin_url('admin.php?page=' . $this->app . '_forms')
				),
				esc_html__('Edit Layout', 'emd-plugins'),
				esc_html__('Edit Layout', 'emd-plugins')
			);
			// Build the row action links and return the value.
			$name  = $name . $this->row_actions($row_actions);
		}
		if($this->type == 'cfield'){
			$name = sprintf(
				'<a class="row-title" href="%s" title="%s"><strong>%s</strong></a>',
				add_query_arg(
					array(
						'action'    => 'edit',
						'cfield' => $rec['id'],
					),
					admin_url('admin.php?page=' . $this->app . '_cust_fields')
				),
				esc_html__('Edit', 'emd-plugins'),
				$name
			);
		}
		return $name;
	}
}
