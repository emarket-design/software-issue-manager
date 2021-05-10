<?php global $sc_issues_count;
$ent_attrs = get_option('software_issue_manager_attr_list'); ?>
<tr>
<?php if (emd_is_item_visible('ent_iss_id', 'software_issue_manager', 'attribute', 1)) { ?>
<td class="results-cell"><a href="<?php echo get_permalink(); ?>"><?php echo esc_html(emd_mb_meta('emd_iss_id')); ?>
</a></td>
<?php
} ?>
<td class="results-cell"><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></td>
<?php if (emd_is_item_visible('ent_iss_email', 'software_issue_manager', 'attribute', 1)) { ?>
<td class="results-cell"><?php _e('Email', 'software-issue-manager'); ?></td>
<?php
} ?>
<?php if (emd_is_item_visible('tax_issue_cat', 'software_issue_manager', 'taxonomy', 1)) { ?>
<td class="results-cell"><?php echo emd_get_tax_vals(get_the_ID() , 'issue_cat'); ?></td>
<?php
} ?>
<?php if (emd_is_item_visible('tax_issue_status', 'software_issue_manager', 'taxonomy', 1)) { ?>
<td class="results-cell"><?php echo emd_get_tax_vals(get_the_ID() , 'issue_status'); ?></td>
<?php
} ?>
<?php if (emd_is_item_visible('tax_issue_priority', 'software_issue_manager', 'taxonomy', 1)) { ?>
<td class="results-cell"><?php echo emd_get_tax_vals(get_the_ID() , 'issue_priority'); ?></td>
<?php
} ?>
<?php if (shortcode_exists('wpas_woo_product_woo_issue')) { ?>
 <td class="search-results-row"><?php echo do_shortcode("[wpas_woo_product_woo_issue con_name='woo_issue' app_name='software_issue_manager' type='list_ol' post= " . get_the_ID() . "]"); ?>
</td>
<?php
} ?>
<?php if (shortcode_exists('wpas_edd_product_edd_issue')) { ?>
 <td class="search-results-row"><?php echo do_shortcode("[wpas_edd_product_edd_issue con_name='edd_issue' app_name='software_issue_manager' type='list_ol' post= " . get_the_ID() . "]"); ?>
</td>
<?php
} ?>
</tr>