<?php $real_post = $post;
$ent_attrs = get_option('software_issue_manager_attr_list');
?>
<div id="single-emd-project-<?php echo get_the_ID(); ?>" class="emd-container emd-project-wrap single-wrap">
<?php $is_editable = 0; ?>
<div class="notfronteditable">
    <div style="padding-bottom:10px;clear:both;text-align:right;" id="modified-info-block" class=" modified-info-block">
        <div class="textSmall text-muted modified" style="font-size:75%"><span class="last-modified-text"><?php _e('Last modified', 'software-issue-manager'); ?> </span><span class="last-modified-author"><?php _e('by', 'software-issue-manager'); ?> <?php echo get_the_modified_author(); ?> - </span><span class="last-modified-datetime"><?php echo human_time_diff(strtotime(get_the_modified_date() . " " . get_the_modified_time()) , current_time('timestamp')); ?> </span><span class="last-modified-dttext"><?php _e('ago', 'software-issue-manager'); ?></span></div>
    </div>
    <div class="panel panel-info" >
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
            <div class="single-well well emd-project">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="slcontent emdbox">
                            <?php if (emd_is_item_visible('tax_project_priority', 'software_issue_manager', 'taxonomy', 0)) { ?>
                            <div class="segment-block tax-project-priority">
                                <div style="font-size:95%" class="segtitle"><?php _e('Priority', 'software-issue-manager'); ?></div>
                                <div class="segvalue"><span data-tax-project-priority="<?php echo emd_get_tax_slugs(get_the_ID() , 'project_priority') ?>"><?php echo emd_get_tax_vals(get_the_ID() , 'project_priority'); ?></span></div>
                            </div>
                            <?php
} ?><?php if (emd_is_item_visible('tax_project_status', 'software_issue_manager', 'taxonomy', 0)) { ?>
                            <div class="segment-block tax-project-status">
                                <div style="font-size:95%" class="segtitle"><?php _e('Status', 'software-issue-manager'); ?></div>
                                <div class="segvalue"><span data-tax-project-status="<?php echo emd_get_tax_slugs(get_the_ID() , 'project_status') ?>"><?php echo emd_get_tax_vals(get_the_ID() , 'project_status'); ?></span></div>
                            </div>
                            <?php
} ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="srcontent emdbox">
                            <?php if (emd_is_item_visible('ent_prj_start_date', 'software_issue_manager', 'attribute', 0)) { ?>
                            <div class="segment-block ent-prj-start-date">
                                <div style="font-size:95%" class="segtitle"><?php _e('Start Date', 'software-issue-manager'); ?></div>
                                <div class="segvalue"><span><?php if (!empty(emd_mb_meta('emd_prj_start_date'))) {
		echo date_i18n(get_option('date_format') , strtotime(emd_mb_meta('emd_prj_start_date')));
	} ?></span></div>
                            </div>
                            <?php
} ?><?php if (emd_is_item_visible('ent_prj_target_end_date', 'software_issue_manager', 'attribute', 0)) { ?>
                            <div class="segment-block ent-prj-target-end-date">
                                <div style="font-size:95%" class="segtitle"><?php _e('Target End Date', 'software-issue-manager'); ?></div>
                                <div class="segvalue"><span><?php if (!empty(emd_mb_meta('emd_prj_target_end_date'))) {
		echo date_i18n(get_option('date_format') , strtotime(emd_mb_meta('emd_prj_target_end_date')));
	} ?></span></div>
                            </div>
                            <?php
} ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="emd-body-content">
                <div class="tab-container emd-project-tabcontainer" style="padding:0 5px 60px;">
                    <ul class="nav nav-tabs" role="tablist" style="margin: 20px 0px 10px;visibility: visible;padding-bottom:0px;">
                        <li class=" active "><a id="description-tablink" href="#description" role="tab" data-toggle="tab"><?php _e('Description', 'software-issue-manager'); ?></a></li>
                        <li><a id="details-tablink" href="#details" role="tab" data-toggle="tab"><?php _e('Details', 'software-issue-manager'); ?></a></li>
                    </ul>
                    <div class="tab-content emd-project-tabcontent">
                        <div class="tab-pane fade in active" id="description">
                            <?php if (emd_is_item_visible('content', 'software_issue_manager', 'attribute', 0)) { ?>
                            <div class="single-content segment-block content"><?php echo $post->post_content; ?></div>
                            <?php
} ?>
                        </div>
                        <div class="tab-pane fade in " id="details">
                            <?php if (emd_is_item_visible('ent_prj_actual_end_date', 'software_issue_manager', 'attribute', 0)) { ?>
                            <div class="segment-block ent-prj-actual-end-date">
                                <div data-has-attrib="false" class="row">
                                    <div class="col-sm-6 ">
                                        <div class="segtitle"><?php _e('Actual End Date', 'software-issue-manager'); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="segvalue"><?php if (!empty(emd_mb_meta('emd_prj_actual_end_date'))) {
		echo date_i18n(get_option('date_format') , strtotime(emd_mb_meta('emd_prj_actual_end_date')));
	} ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php
} ?> <?php if (emd_is_item_visible('ent_prj_file', 'software_issue_manager', 'attribute', 0)) { ?>
                            <div class="segment-block ent-prj-file">
                                <div data-has-attrib="false" class="row">
                                    <div class="col-sm-6 ">
                                        <div class="segtitle"><?php _e('Documents', 'software-issue-manager'); ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="segvalue"><?php
	add_thickbox();
	$emd_mb_file = emd_mb_meta('emd_prj_file', 'type=file');
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
            <div class="panel-group" id="accordion"> <?php if (emd_is_item_visible('entrelcon_project_issues', 'software_issue_manager', 'relation')) { ?>
<?php $post = get_post();
	$rel_filter = "";
	$res = emd_get_p2p_connections('connected', 'project_issues', 'std', $post, 1, 0, '', 'software_issue_manager', $rel_filter);
	$rel_list = get_option('software_issue_manager_rel_list');
?>
<div style="overflow-x:auto;" class="single-relpanel emd-issue project-issues"><?php if (emd_check_rel_count('entrelcon_project_issues', 'software_issue_manager', $rel_filter)) { ?>
<div class="panel panel-default relseg">
 <div class="panel-heading">
  <div class="panel-title">
   <a class="btn-block accor-title-link collapsed" data-toggle="collapse" data-parent="#accordion" href="#rel-1088">
   <div class="accor-title"><?php _e('Project Issues', 'software-issue-manager'); ?></div>
   </a>
  </div>
 </div>
 <div id="rel-1088" class="panel-collapse collapse in">
  <div data-has-attrib="false" class="panel-body emd-table-container">
<table id="table-project-issues-con" class="table emd-table table-bordered table-hover" style="background-color:#ffffff">
<thead>
<th><?php _e('Issue', 'software-issue-manager'); ?></th>
<?php if (emd_is_item_visible('ent_iss_id', 'software_issue_manager', 'attribute', 1)) { ?>
<th data-sortable="true"  data-align="center"><?php _e('Issue #', 'software-issue-manager'); ?></th>
<?php
		} ?><?php if (emd_is_item_visible('tax_issue_priority', 'software_issue_manager', 'taxonomy', 1)) { ?>
<th data-sortable="true"  data-align="center"><?php _e('Priority', 'software-issue-manager'); ?></th>
<?php
		} ?><?php if (emd_is_item_visible('tax_issue_cat', 'software_issue_manager', 'taxonomy', 1)) { ?>
<th data-sortable="true"  data-align="center"><?php _e('Category', 'software-issue-manager'); ?></th>
<?php
		} ?><?php if (emd_is_item_visible('tax_issue_status', 'software_issue_manager', 'taxonomy', 1)) { ?>
<th data-sortable="true"  data-align="center"><?php _e('Status', 'software-issue-manager'); ?></th>
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
<?php if (emd_is_item_visible('ent_iss_id', 'software_issue_manager', 'attribute', 1)) { ?>
<td   data-align="center"><?php echo esc_html(emd_mb_meta('emd_iss_id', '', $myrel->ID)); ?></td>
<?php
			} ?><?php if (emd_is_item_visible('tax_issue_priority', 'software_issue_manager', 'taxonomy', 1)) { ?>
<td><span data-tax-issue-priority="<?php echo emd_get_tax_slugs($myrel->ID, 'issue_priority'); ?>" class="taxlabel taxvalue" style="overflow-wrap:break-word"><?php echo emd_get_tax_vals($myrel->ID, 'issue_priority'); ?></span></td>
<?php
			} ?><?php if (emd_is_item_visible('tax_issue_cat', 'software_issue_manager', 'taxonomy', 1)) { ?>
<td><span data-tax-issue-cat="<?php echo emd_get_tax_slugs($myrel->ID, 'issue_cat'); ?>" class="taxlabel taxvalue" style="overflow-wrap:break-word"><?php echo emd_get_tax_vals($myrel->ID, 'issue_cat'); ?></span></td>
<?php
			} ?><?php if (emd_is_item_visible('tax_issue_status', 'software_issue_manager', 'taxonomy', 1)) { ?>
<td><span data-tax-issue-status="<?php echo emd_get_tax_slugs($myrel->ID, 'issue_status'); ?>" class="taxlabel taxvalue" style="overflow-wrap:break-word"><?php echo emd_get_tax_vals($myrel->ID, 'issue_status'); ?></span></td>
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
        <div class="panel-footer"></div>
    </div>
</div>
</div><!--container-end-->