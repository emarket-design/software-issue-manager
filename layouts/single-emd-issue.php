<?php $real_post = $post;
$ent_attrs = get_option('software_issue_manager_attr_list');
?>
<div id="single-emd-issue-<?php echo get_the_ID(); ?>" class="emd-container emd-issue-wrap single-wrap">
<?php $is_editable = 0; ?>
<div class="notfronteditable">
    <div style="padding-bottom:10px;clear:both;text-align:right;" id="modified-info-block" class=" modified-info-block">
        <div class="textSmall text-muted modified" style="font-size:75%"><span class="last-modified-text"><?php _e('Last modified', 'software-issue-manager'); ?> </span><span class="last-modified-author"><?php _e('by', 'software-issue-manager'); ?> <?php echo get_the_modified_author(); ?> - </span><span class="last-modified-datetime"><?php echo human_time_diff(strtotime(get_the_modified_date() . " " . get_the_modified_time()) , current_time('timestamp')); ?> </span><span class="last-modified-dttext"><?php _e('ago', 'software-issue-manager'); ?></span></div>
    </div>
    <div class="panel panel-primary" >
        <div class="panel-heading" style="position:relative; ">
            <div class="panel-title">
                <div class='single-header header'>
                    <h1 class='single-entry-title entry-title' style='color:inherit;padding:0;margin-bottom: 15px;border:0 none;word-break:break-word;font-size:24px;'>
                        <?php if (emd_is_item_visible('title', 'software_issue_manager', 'attribute', 0)) { ?><span class="single-content title"><?php echo get_the_title(); ?></span>
<?php echo apply_filters('emd_get_parent_title', '', $post->ID, 'single'); ?><?php
} ?>
                    </h1>
                </div>
            </div>
        </div>
        <div class="panel-body" style="clear:both">
            <div class="single-well well emd-issue">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="slcontent emdbox">
                            <?php if (emd_is_item_visible('ent_iss_id', 'software_issue_manager', 'attribute', 0)) { ?>
                            <div class="segment-block ent-iss-id">
                                <div style="font-size:95%" class="segtitle"><?php _e('Issue #', 'software-issue-manager'); ?></div>
                                <div class="segvalue"><span><?php echo esc_html(emd_mb_meta('emd_iss_id')); ?>
</span></div>
                            </div>
                            <?php
} ?><?php if (emd_is_item_visible('tax_issue_cat', 'software_issue_manager', 'taxonomy', 0)) { ?>
                            <div class="segment-block tax-issue-cat">
                                <div style="font-size:95%" class="segtitle"><?php _e('Category', 'software-issue-manager'); ?></div>
                                <div class="segvalue"><span data-tax-issue-cat="<?php echo emd_get_tax_slugs(get_the_ID() , 'issue_cat') ?>"><?php echo emd_get_tax_vals(get_the_ID() , 'issue_cat'); ?></span></div>
                            </div>
                            <?php
} ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="srcontent emdbox">
                            <?php if (emd_is_item_visible('tax_issue_priority', 'software_issue_manager', 'taxonomy', 0)) { ?>
                            <div class="segment-block tax-issue-priority">
                                <div style="font-size:95%" class="segtitle"><?php _e('Priority', 'software-issue-manager'); ?></div>
                                <div class="segvalue"><span data-tax-issue-priority="<?php echo emd_get_tax_slugs(get_the_ID() , 'issue_priority') ?>"><?php echo emd_get_tax_vals(get_the_ID() , 'issue_priority'); ?></span></div>
                            </div>
                            <?php
} ?><?php if (emd_is_item_visible('tax_issue_status', 'software_issue_manager', 'taxonomy', 0)) { ?>
                            <div class="segment-block tax-issue-status">
                                <div style="font-size:95%" class="segtitle"><?php _e('Status', 'software-issue-manager'); ?></div>
                                <div class="segvalue"><span data-tax-issue-status="<?php echo emd_get_tax_slugs(get_the_ID() , 'issue_status') ?>"><?php echo emd_get_tax_vals(get_the_ID() , 'issue_status'); ?></span></div>
                            </div>
                            <?php
} ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="emd-body-content">
                <div class="tab-container emd-issue-tabcontainer" style="padding:0 5px 60px;">
                    <ul class="nav nav-tabs" role="tablist" style="margin: 20px 0px 10px;visibility: visible;padding-bottom:0px;">
                        <li class=" active "><a id="description-tablink" href="#description" role="tab" data-toggle="tab"><?php _e('Description', 'software-issue-manager'); ?></a></li>
                        <li><a id="details-tablink" href="#details" role="tab" data-toggle="tab"><?php _e('Details', 'software-issue-manager'); ?></a></li>
                    </ul>
                    <div class="tab-content emd-issue-tabcontent">
                        <div class="tab-pane fade in active" id="description">
                            <?php if (emd_is_item_visible('content', 'software_issue_manager', 'attribute', 0)) { ?>
                            <div class="single-content segment-block content"><?php echo $post->post_content; ?></div>
                            <?php
} ?>
                        </div>
                        <div class="tab-pane fade in " id="details">
                            <?php if (emd_is_item_visible('ent_iss_due_date', 'software_issue_manager', 'attribute', 0)) { ?>
                            <div class="segment-block ent-iss-due-date">
                                <div data-has-attrib="false" class="row">
                                    <div class="col-sm-6 ">
                                        <div class="segtitle"><?php _e('Due Date', 'software-issue-manager'); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="segvalue"><?php if (!empty(emd_mb_meta('emd_iss_due_date'))) {
		echo date_i18n(get_option('date_format') , strtotime(emd_mb_meta('emd_iss_due_date')));
	} ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php
} ?> <?php if (emd_is_item_visible('ent_iss_resolution_summary', 'software_issue_manager', 'attribute', 0)) { ?>
                            <div class="segment-block ent-iss-resolution-summary">
                                <div data-has-attrib="false" class="row">
                                    <div class="col-sm-6 ">
                                        <div class="segtitle"><?php _e('Resolution Summary', 'software-issue-manager'); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="segvalue"><?php echo emd_mb_meta('emd_iss_resolution_summary'); ?>
</div>
                                    </div>
                                </div>
                            </div>
                            <?php
} ?> <?php if (emd_is_item_visible('ent_iss_document', 'software_issue_manager', 'attribute', 0)) { ?>
                            <div class="segment-block ent-iss-document">
                                <div data-has-attrib="false" class="row">
                                    <div class="col-sm-6 ">
                                        <div class="segtitle"><?php _e('Documents', 'software-issue-manager'); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="segvalue"><?php
	add_thickbox();
	$emd_mb_file = emd_mb_meta('emd_iss_document', 'type=file');
	if (!empty($emd_mb_file)) {
		echo '<div class="clearfix">';
		foreach ($emd_mb_file as $info) {
			emd_get_attachment_layout($info);
		}
		echo '</div>';
	}
?>
</div>
                                    </div>
                                </div>
                            </div>
                            <?php
} ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-group" id="accordion"> <?php if (shortcode_exists('wpas_edd_product_edd_issue')) {
	echo do_shortcode("[wpas_edd_product_edd_issue con_name='edd_issue' app_name='software_issue_manager' type='layout' post= " . get_the_ID() . "]");
} ?>

<?php if (shortcode_exists('wpas_woo_product_woo_issue')) {
	echo do_shortcode("[wpas_woo_product_woo_issue con_name='woo_issue' app_name='software_issue_manager' type='layout' post= " . get_the_ID() . "]");
} ?>
 <?php if (emd_is_item_visible('entrelcon_project_issues', 'software_issue_manager', 'relation')) { ?>
<?php $post = get_post();
	$rel_filter = "";
	$res = emd_get_p2p_connections('connected', 'project_issues', 'std', $post, 1, 0, '', 'software_issue_manager', $rel_filter);
	$rel_list = get_option('software_issue_manager_rel_list');
?>
<div style="overflow-x:auto;" class="single-relpanel emd-project project-issues"><?php if (emd_check_rel_count('entrelcon_project_issues', 'software_issue_manager', $rel_filter)) { ?>
<div class="panel panel-default relseg">
 <div class="panel-heading">
  <div class="panel-title">
   <a class="btn-block accor-title-link collapsed" data-toggle="collapse" data-parent="#accordion" href="#rel-1081">
   <div class="accor-title"><?php _e('Affected Projects', 'software-issue-manager'); ?></div>
   </a>
  </div>
 </div>
 <div id="rel-1081" class="panel-collapse collapse in">
  <div data-has-attrib="false" class="panel-body emd-table-container">
<table id="table-project-issues-con" class="table emd-table table-bordered table-hover" style="background-color:#ffffff">
<thead>
<th><?php _e('Project', 'software-issue-manager'); ?></th>
<?php if (emd_is_item_visible('tax_project_priority', 'software_issue_manager', 'taxonomy', 1)) { ?>
<th data-sortable="true"  data-align="center"><?php _e('Priority', 'software-issue-manager'); ?></th>
<?php
		} ?><?php if (emd_is_item_visible('tax_project_status', 'software_issue_manager', 'taxonomy', 1)) { ?>
<th data-sortable="true"  data-align="center"><?php _e('Status', 'software-issue-manager'); ?></th>
<?php
		} ?><?php if (emd_is_item_visible('ent_prj_start_date', 'software_issue_manager', 'attribute', 1)) { ?>
<th data-sortable="true"  data-align="center"><?php _e('Start Date', 'software-issue-manager'); ?></th>
<?php
		} ?><?php if (emd_is_item_visible('ent_prj_target_end_date', 'software_issue_manager', 'attribute', 1)) { ?>
<th data-sortable="true"  data-align="center"><?php _e('Target End Date', 'software-issue-manager'); ?></th>
<?php
		} ?>
</thead>
<tbody><?php
		echo $res['before_list'];
		$real_post = $post;
		$rel_count_id = 1;
		$rel_eds = Array();
		foreach ($res['rels'] as $myrel) {
			$post = $myrel;
			echo $res['before_item']; ?>
<tr>
<td><a href="<?php echo get_permalink($post->ID); ?>" title="<?php echo get_the_title(); ?>"><?php echo get_the_title(); ?></a></td>
<?php if (emd_is_item_visible('tax_project_priority', 'software_issue_manager', 'taxonomy', 1)) { ?>
<td><span data-tax-project-priority="<?php echo emd_get_tax_slugs($myrel->ID, 'project_priority'); ?>" class="taxlabel taxvalue" style="overflow-wrap:break-word"><?php echo emd_get_tax_vals($myrel->ID, 'project_priority'); ?></span></td>
<?php
			} ?><?php if (emd_is_item_visible('tax_project_status', 'software_issue_manager', 'taxonomy', 1)) { ?>
<td><span data-tax-project-status="<?php echo emd_get_tax_slugs($myrel->ID, 'project_status'); ?>" class="taxlabel taxvalue" style="overflow-wrap:break-word"><?php echo emd_get_tax_vals($myrel->ID, 'project_status'); ?></span></td>
<?php
			} ?><?php if (emd_is_item_visible('ent_prj_start_date', 'software_issue_manager', 'attribute', 1)) { ?>
<td   data-align="center"><?php if (!empty(emd_mb_meta('emd_prj_start_date'))) {
					echo date_i18n(get_option('date_format') , strtotime(emd_mb_meta('emd_prj_start_date')));
				} ?></td>
<?php
			} ?><?php if (emd_is_item_visible('ent_prj_target_end_date', 'software_issue_manager', 'attribute', 1)) { ?>
<td   data-align="center"><?php if (!empty(emd_mb_meta('emd_prj_target_end_date'))) {
					echo date_i18n(get_option('date_format') , strtotime(emd_mb_meta('emd_prj_target_end_date')));
				} ?></td>
<?php
			} ?>
</tr><?php
			echo $res['after_item'];
			$rel_count_id++;
		}
		$post = $real_post;
		echo $res['after_list']; ?>
</tbody>
</table>  </div>
 </div>
</div>
<?php
	} ?></div><?php
} ?> </div>
        </div>
        <div class="panel-footer">
            <?php if (emd_is_item_visible('tax_browser', 'software_issue_manager', 'taxonomy', 0)) { ?>
            <div class="footer-segment-block"><span style="margin-right:2px" class="footer-object-title label label-info"><?php _e('Browser', 'software-issue-manager'); ?></span><span class="footer-object-value"><?php echo emd_get_tax_vals(get_the_ID() , 'browser'); ?></span></div>
            <?php
} ?><?php if (emd_is_item_visible('tax_issue_tag', 'software_issue_manager', 'taxonomy', 0)) { ?>
            <div class="footer-segment-block"><span style="margin-right:2px" class="footer-object-title label label-info"><?php _e('Tag', 'software-issue-manager'); ?></span><span class="footer-object-value"><?php echo emd_get_tax_vals(get_the_ID() , 'issue_tag'); ?></span></div>
            <?php
} ?><?php if (emd_is_item_visible('tax_operating_system', 'software_issue_manager', 'taxonomy', 0)) { ?>
            <div class="footer-segment-block"><span style="margin-right:2px" class="footer-object-title label label-info"><?php _e('Operating System', 'software-issue-manager'); ?></span><span class="footer-object-value"><?php echo emd_get_tax_vals(get_the_ID() , 'operating_system'); ?></span></div>
            <?php
} ?> 
        </div>
    </div>
</div>
</div><!--container-end-->