<?php
/**
 * Settings Glossary Functions
 *
 * @package SOFTWARE_ISSUE_MANAGER
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_action('software_issue_manager_settings_glossary', 'software_issue_manager_settings_glossary');
/**
 * Display glossary information
 * @since WPAS 4.0
 *
 * @return html
 */
function software_issue_manager_settings_glossary() {
	global $title;
?>
<div class="wrap">
<h2><?php echo $title; ?></h2>
<p><?php _e('Software Issue Manager allows to track the resolution of every project issue in a productive and efficient way.', 'software-issue-manager'); ?></p>
<p><?php _e('The below are the definitions of entities, attributes, and terms included in Software Issue Manager.', 'software-issue-manager'); ?></p>
<div id="glossary" class="accordion-container">
<ul class="outer-border">
<li id="emd_issue" class="control-section accordion-section">
<h3 class="accordion-section-title hndle" tabindex="2"><?php _e('Issues', 'software-issue-manager'); ?></h3>
<div class="accordion-section-content">
<div class="inside">
<table class="form-table"><p class"lead"><?php _e('An issue is anything that might affect the project meeting its goals such as bugs, tasks, and feature requests that occur during a project\'s life cycle.', 'software-issue-manager'); ?></p><tr><th style='font-size: 1.1em;color:cadetblue;border-bottom: 1px dashed;padding-bottom: 10px;' colspan=2><div><?php _e('Attributes', 'software-issue-manager'); ?></div></th></tr>
<tr>
<th><?php _e('Title', 'software-issue-manager'); ?></th>
<td><?php _e(' Title is a required field. Title does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Content', 'software-issue-manager'); ?></th>
<td><?php _e(' Content does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('ID', 'software-issue-manager'); ?></th>
<td><?php _e('Sets a unique identifier for an issue. Being a unique identifier, it uniquely distinguishes each instance of Issue entity. ID does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Due Date', 'software-issue-manager'); ?></th>
<td><?php _e('Sets the targeted resolution date for an issue. Due Date does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Resolution Summary', 'software-issue-manager'); ?></th>
<td><?php _e('Sets a brief summary of the resolution of an issue. Resolution Summary does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Documents', 'software-issue-manager'); ?></th>
<td><?php _e('Allows to upload files related to an issue. Documents does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Email', 'software-issue-manager'); ?></th>
<td><?php _e(' Email does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Form Name', 'software-issue-manager'); ?></th>
<td><?php _e(' Form Name is filterable in the admin area. Form Name has a default value of <b>admin</b>.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Form Submitted By', 'software-issue-manager'); ?></th>
<td><?php _e(' Form Submitted By is filterable in the admin area. Form Submitted By does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Form Submitted IP', 'software-issue-manager'); ?></th>
<td><?php _e(' Form Submitted IP is filterable in the admin area. Form Submitted IP does not have a default value. ', 'software-issue-manager'); ?></td>
</tr><tr><th style='font-size:1.1em;color:cadetblue;border-bottom: 1px dashed;padding-bottom: 10px;' colspan=2><div><?php _e('Taxonomies', 'software-issue-manager'); ?></div></th></tr>
<tr>
<th><?php _e('Browser', 'software-issue-manager'); ?></th>

<td><?php _e('Sets the browser version that an issue may be reproduced in. Browser accepts multiple values like tags', 'software-issue-manager'); ?>. <?php _e('Browser does not have a default value', 'software-issue-manager'); ?>.<div class="taxdef-block"><p><?php _e('The following are the preset values for <b>Browser:</b>', 'software-issue-manager'); ?></p><p class="taxdef-values"><?php _e('Chrome 33', 'software-issue-manager'); ?>, <?php _e('Internet Explorer 11', 'software-issue-manager'); ?>, <?php _e('Safari 7.0', 'software-issue-manager'); ?>, <?php _e('Opera 20', 'software-issue-manager'); ?>, <?php _e('Firefox 29', 'software-issue-manager'); ?></p></div></td>
</tr>

<tr>
<th><?php _e('Category', 'software-issue-manager'); ?></th>

<td><?php _e('Sets the category that an issue belongs to. Category accepts multiple values like tags', 'software-issue-manager'); ?>. <?php _e('Category has a default value of:', 'software-issue-manager'); ?> <?php _e(' bug', 'software-issue-manager'); ?>. <div class="taxdef-block"><p><?php _e('The following are the preset values and value descriptions for <b>Category:</b>', 'software-issue-manager'); ?></p>
<table class="table tax-table form-table"><tr><td><?php _e('Bug', 'software-issue-manager'); ?></td>
<td><?php _e('Bugs are software problems or defects in the system that need to be resolved.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Feature Request', 'software-issue-manager'); ?></td>
<td><?php _e('Feature requests are functional enhancements submitted by clients.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Task', 'software-issue-manager'); ?></td>
<td><?php _e('Tasks are activities that need to be accomplished within a defined period of time or by a deadline to resolve issues.', 'software-issue-manager'); ?></td>
</tr>
</table>
</div></td>
</tr>

<tr>
<th><?php _e('Priority', 'software-issue-manager'); ?></th>

<td><?php _e('Sets the priority level assigned to an issue. Priority accepts multiple values like tags', 'software-issue-manager'); ?>. <?php _e('Priority has a default value of:', 'software-issue-manager'); ?> <?php _e(' normal', 'software-issue-manager'); ?>. <div class="taxdef-block"><p><?php _e('The following are the preset values and value descriptions for <b>Priority:</b>', 'software-issue-manager'); ?></p>
<table class="table tax-table form-table"><tr><td><?php _e('Critical', 'software-issue-manager'); ?></td>
<td><?php _e('Critical bugs either render a system unusable (not being able to create content or upgrade between versions, blocks not displaying, and the like), cause loss of data, or expose security vulnerabilities. These bugs are to be fixed immediately.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Major', 'software-issue-manager'); ?></td>
<td><?php _e('Issues which have significant repercussions but do not render the whole system unusable are marked major. An example would be a PHP error which is only triggered under rare circumstances or which affects only a small percentage of all users. These issues are prioritized in the current development release and backported to stable releases where applicable. Major issues do not block point releases.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Normal', 'software-issue-manager'); ?></td>
<td><?php _e('Bugs that affect one piece of functionality are normal priority. An example would be the category filter not working on the database log screen. This is a self-contained bug and does not impact the overall functionality of the software.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Minor', 'software-issue-manager'); ?></td>
<td><?php _e('Minor priority is most often used for cosmetic issues that don\'t inhibit the functionality or main purpose of the project, such as correction of typos in code comments or whitespace issues.', 'software-issue-manager'); ?></td>
</tr>
</table>
</div></td>
</tr>

<tr>
<th><?php _e('Status', 'software-issue-manager'); ?></th>

<td><?php _e('Sets the current status of an issue. Status accepts multiple values like tags', 'software-issue-manager'); ?>. <?php _e('Status has a default value of:', 'software-issue-manager'); ?> <?php _e(' open', 'software-issue-manager'); ?>. <div class="taxdef-block"><p><?php _e('The following are the preset values and value descriptions for <b>Status:</b>', 'software-issue-manager'); ?></p>
<table class="table tax-table form-table"><tr><td><?php _e('Open', 'software-issue-manager'); ?></td>
<td><?php _e('This issue is in the initial state, ready for the assignee to start work on it.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('In Progress', 'software-issue-manager'); ?></td>
<td><?php _e('This issue is being actively worked on at the moment.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Reopened', 'software-issue-manager'); ?></td>
<td><?php _e('This issue was once \'Resolved\' or \'Closed\', but is now being re-visited, e.g. an issue with a Resolution of \'Cannot Reproduce\' is Reopened when more information becomes available and the issue becomes reproducible. The next issue states are either marked In Progress, Resolved or Closed.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Closed', 'software-issue-manager'); ?></td>
<td><?php _e('This issue is complete.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Resolved - Fixed', 'software-issue-manager'); ?></td>
<td><?php _e('A fix for this issue has been implemented.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Resolved - Won\'t Fix', 'software-issue-manager'); ?></td>
<td><?php _e('This issue will not be fixed, e.g. it may no longer be relevant.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Resolved - Duplicate', 'software-issue-manager'); ?></td>
<td><?php _e('This issue is a duplicate of an existing issue. It is recommended you create a link to the duplicated issue by creating a related issue connection.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Resolved - Incomplete', 'software-issue-manager'); ?></td>
<td><?php _e('There is not enough information to work on this issue.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<td><?php _e('Resolved - CNR', 'software-issue-manager'); ?></td>
<td><?php _e('This issue could not be reproduced at this time, or not enough information was available to reproduce the issue. If more information becomes available, reopen the issue.', 'software-issue-manager'); ?></td>
</tr>
</table>
</div></td>
</tr>

<tr>
<th><?php _e('Tag', 'software-issue-manager'); ?></th>

<td><?php _e('Allows to tag issues to further classify or group related issues. Tag accepts multiple values like tags', 'software-issue-manager'); ?>. <?php _e('Tag does not have a default value', 'software-issue-manager'); ?>.<div class="taxdef-block"><p><?php _e('There are no preset values for <b>Tag.</b>', 'software-issue-manager'); ?></p></div></td>
</tr>

<tr>
<th><?php _e('Operating System', 'software-issue-manager'); ?></th>

<td><?php _e('Sets the operating system(s) that an issue may be reproduced in. Operating System accepts multiple values like tags', 'software-issue-manager'); ?>. <?php _e('Operating System does not have a default value', 'software-issue-manager'); ?>.<div class="taxdef-block"><p><?php _e('The following are the preset values for <b>Operating System:</b>', 'software-issue-manager'); ?></p><p class="taxdef-values"><?php _e('Windows 8 (32-bit and 64-bit)', 'software-issue-manager'); ?>, <?php _e('Windows 7 (32-bit and 64-bit)', 'software-issue-manager'); ?>, <?php _e('Windows Vista (32-bit and 64-bit)', 'software-issue-manager'); ?>, <?php _e('Windows XP (32-bit and 64-bit)', 'software-issue-manager'); ?>, <?php _e('Windows Server 2008 R2 (64-bit)', 'software-issue-manager'); ?>, <?php _e('Windows Server 2008 (32-bit and 64-bit)', 'software-issue-manager'); ?>, <?php _e('Windows Server 2003 (32-bit and 64-bit)', 'software-issue-manager'); ?>, <?php _e('Windows 2000 SP4', 'software-issue-manager'); ?>, <?php _e('Mac OS X 10.8 Mountain Lion (32-bit and 64-bit)', 'software-issue-manager'); ?>, <?php _e('Mac OS X 10.7 Lion (32-bit and 64-bit)', 'software-issue-manager'); ?>, <?php _e('Mac OS X 10.6 Snow Leopard (32-bit)', 'software-issue-manager'); ?>, <?php _e(' Mac OS X 10.5 Leopard', 'software-issue-manager'); ?>, <?php _e('Mac OS X 10.4 Tiger', 'software-issue-manager'); ?>, <?php _e('Linux (32-bit and 64-bit versions, kernel 2.6 or compatible)', 'software-issue-manager'); ?></p></div></td>
</tr>
<tr><th style='font-size: 1.1em;color:cadetblue;border-bottom: 1px dashed;padding-bottom: 10px;' colspan=2><div><?php _e('Relationships', 'software-issue-manager'); ?></div></th></tr>
<tr>
<th><?php _e('Affected Projects', 'software-issue-manager'); ?></th>
<td><?php _e('Allows to display and create connections with Projects', 'software-issue-manager'); ?>. <?php _e('One instance of Issues can associated with many instances of Projects, and vice versa', 'software-issue-manager'); ?>.  <?php _e('The relationship can be set up in the edit area of Issues using Affected Projects relationship box. ', 'software-issue-manager'); ?> <?php _e('This relationship is required when publishing new Issues', 'software-issue-manager'); ?>. </td>
</tr></table>
</div>
</div>
</li><li id="emd_project" class="control-section accordion-section">
<h3 class="accordion-section-title hndle" tabindex="1"><?php _e('Projects', 'software-issue-manager'); ?></h3>
<div class="accordion-section-content">
<div class="inside">
<table class="form-table"><p class"lead"><?php _e('A project is a collection of related issues. Projects have a unique version number, specific start and end dates.', 'software-issue-manager'); ?></p><tr><th style='font-size: 1.1em;color:cadetblue;border-bottom: 1px dashed;padding-bottom: 10px;' colspan=2><div><?php _e('Attributes', 'software-issue-manager'); ?></div></th></tr>
<tr>
<th><?php _e('Content', 'software-issue-manager'); ?></th>
<td><?php _e(' Content does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Name', 'software-issue-manager'); ?></th>
<td><?php _e('Sets the name of a project. Name is a required field. Being a unique identifier, it uniquely distinguishes each instance of Project entity. Name does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Version', 'software-issue-manager'); ?></th>
<td><?php _e('Sets the version number of a project. Version is a required field. Being a unique identifier, it uniquely distinguishes each instance of Project entity. Version has a default value of <b>V1.0.0</b>.', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Start Date', 'software-issue-manager'); ?></th>
<td><?php _e('Sets the start date of a project. Start Date is a required field. Start Date does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Target End Date', 'software-issue-manager'); ?></th>
<td><?php _e('Sets the targeted end date of a project. Target End Date does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Actual End Date', 'software-issue-manager'); ?></th>
<td><?php _e('Sets the actual end date of a project. Actual End Date does not have a default value. ', 'software-issue-manager'); ?></td>
</tr>
<tr>
<th><?php _e('Documents', 'software-issue-manager'); ?></th>
<td><?php _e('Allows to upload project related files. Documents does not have a default value. ', 'software-issue-manager'); ?></td>
</tr><tr><th style='font-size:1.1em;color:cadetblue;border-bottom: 1px dashed;padding-bottom: 10px;' colspan=2><div><?php _e('Taxonomies', 'software-issue-manager'); ?></div></th></tr>
<tr>
<th><?php _e('Priority', 'software-issue-manager'); ?></th>

<td><?php _e('Sets the current priority of a project. Priority accepts multiple values like tags', 'software-issue-manager'); ?>. <?php _e('Priority has a default value of:', 'software-issue-manager'); ?> <?php _e(' medium', 'software-issue-manager'); ?>. <?php _e('Priority is a required field therefore must be assigned to a value', 'software-issue-manager'); ?>.<div class="taxdef-block"><p><?php _e('The following are the preset values for <b>Priority:</b>', 'software-issue-manager'); ?></p><p class="taxdef-values"><?php _e('Low', 'software-issue-manager'); ?>, <?php _e('Medium', 'software-issue-manager'); ?>, <?php _e('High', 'software-issue-manager'); ?></p></div></td>
</tr>

<tr>
<th><?php _e('Status', 'software-issue-manager'); ?></th>

<td><?php _e('Sets the current status of a project. Status accepts multiple values like tags', 'software-issue-manager'); ?>. <?php _e('Status has a default value of:', 'software-issue-manager'); ?> <?php _e(' draft', 'software-issue-manager'); ?>. <?php _e('Status is a required field therefore must be assigned to a value', 'software-issue-manager'); ?>.<div class="taxdef-block"><p><?php _e('The following are the preset values for <b>Status:</b>', 'software-issue-manager'); ?></p><p class="taxdef-values"><?php _e('Draft', 'software-issue-manager'); ?>, <?php _e('In Review', 'software-issue-manager'); ?>, <?php _e('Published', 'software-issue-manager'); ?>, <?php _e('In Process', 'software-issue-manager'); ?></p></div></td>
</tr>
<tr><th style='font-size: 1.1em;color:cadetblue;border-bottom: 1px dashed;padding-bottom: 10px;' colspan=2><div><?php _e('Relationships', 'software-issue-manager'); ?></div></th></tr>
<tr>
<th><?php _e('Project Issues', 'software-issue-manager'); ?></th>
<td><?php _e('Allows to display and create connections with Issues', 'software-issue-manager'); ?>. <?php _e('One instance of Projects can associated with many instances of Issues, and vice versa', 'software-issue-manager'); ?>.  <?php _e('The relationship can be set up in the edit area of Issues using Affected Projects relationship box', 'software-issue-manager'); ?>. <?php _e('This relationship is required when publishing new Projects', 'software-issue-manager'); ?>. </td>
</tr></table>
</div>
</div>
</li>
</ul>
</div>
</div>
<?php
}