<?php
/**
 * Entity Widget Classes
 *
 * @package SOFTWARE_ISSUE_MANAGER
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Entity widget class extends Emd_Widget class
 *
 * @since WPAS 4.0
 */
class software_issue_manager_recent_issues_sidebar_widget extends Emd_Widget {
	public $title;
	public $text_domain = 'software-issue-manager';
	public $class_label;
	public $class = 'emd_issue';
	public $type = 'entity';
	public $has_pages = false;
	public $css_label = 'recent-issues';
	public $id = 'software_issue_manager_recent_issues_sidebar_widget';
	public $query_args = array(
		'post_type' => 'emd_issue',
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'DESC',
		'context' => 'software_issue_manager_recent_issues_sidebar_widget',
	);
	public $filter = '';
	public $header = '';
	public $footer = '';
	/**
	 * Instantiate entity widget class with params
	 *
	 * @since WPAS 4.0
	 */
	public function __construct() {
		parent::__construct($this->id, __('Recent Issues', 'software-issue-manager') , __('Issues', 'software-issue-manager') , __('The most recent issues', 'software-issue-manager'));
	}
	/**
	 * Get header and footer for layout
	 *
	 * @since WPAS 4.6
	 */
	protected function get_header_footer() {
	}
	/**
	 * Enqueue css and js for widget
	 *
	 * @since WPAS 4.5
	 */
	protected function enqueue_scripts() {
		software_issue_manager_enq_custom_css_js();
	}
	/**
	 * Returns widget layout
	 *
	 * @since WPAS 4.0
	 */
	public static function layout() {
		ob_start();
		emd_get_template_part('software_issue_manager', 'widget', 'recent-issues-sidebar-content');
		$layout = ob_get_clean();
		return $layout;
	}
}
$access_views = get_option('software_issue_manager_access_views', Array());
if (empty($access_views['widgets']) || (!empty($access_views['widgets']) && in_array('recent_issues_sidebar', $access_views['widgets']) && current_user_can('view_recent_issues_sidebar'))) {
	register_widget('software_issue_manager_recent_issues_sidebar_widget');
}