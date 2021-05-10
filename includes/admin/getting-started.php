<?php
/**
 * Getting Started
 *
 * @package SOFTWARE_ISSUE_MANAGER
 * @since WPAS 5.3
 */
if (!defined('ABSPATH')) exit;
add_action('software_issue_manager_getting_started', 'software_issue_manager_getting_started');
/**
 * Display getting started information
 * @since WPAS 5.3
 *
 * @return html
 */
function software_issue_manager_getting_started() {
	global $title;
	list($display_version) = explode('-', SOFTWARE_ISSUE_MANAGER_VERSION);
?>
<style>
.about-wrap img{
max-height: 200px;
}
div.comp-feature {
    font-weight: 400;
    font-size:20px;
}
.edition-com {
    display: none;
}
.green{
color: #008000;
font-size: 30px;
}
#nav-compare:before{
    content: "\f179";
}
#emd-about .nav-tab-wrapper a:before{
    position: relative;
    box-sizing: content-box;
padding: 0px 3px;
color: #4682b4;
    width: 20px;
    height: 20px;
    overflow: hidden;
    white-space: nowrap;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
font-family: dashicons;
}
#nav-getting-started:before{
content: "\f102";
}
#nav-release-notes:before{
content: "\f348";
}
#nav-resources:before{
content: "\f118";
}
#nav-features:before{
content: "\f339";
}
#emd-about .embed-container { 
	position: relative; 
	padding-bottom: 56.25%;
	height: 0;
	overflow: hidden;
	max-width: 100%;
	height: auto;
	} 

#emd-about .embed-container iframe,
#emd-about .embed-container object,
#emd-about .embed-container embed { 
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	}
#emd-about ul li:before{
    content: "\f522";
    font-family: dashicons;
    font-size:25px;
 }
#gallery {
display: -webkit-box;
display: -ms-flexbox;
display: flex;
-ms-flex-wrap: wrap;
    flex-wrap: wrap;
}
#gallery .gallery-item {
	margin-top: 10px;
	margin-right: 10px;
	text-align: center;
        cursor:pointer;
}
#gallery img {
	border: 2px solid #cfcfcf; 
height: 405px; 
width: auto; 
}
#gallery .gallery-caption {
	margin-left: 0;
}
#emd-about .top{
text-decoration:none;
}
#emd-about .toc{
    background-color: #fff;
    padding: 25px;
    border: 1px solid #add8e6;
    border-radius: 8px;
}
#emd-about h3,
#emd-about h2{
    margin-top: 0px;
    margin-right: 0px;
    margin-bottom: 0.6em;
    margin-left: 0px;
}
#emd-about p,
#emd-about .emd-section li{
font-size:18px
}
#emd-about a.top:after{
content: "\f342";
    font-family: dashicons;
    font-size:25px;
text-decoration:none;
}
#emd-about .toc a,
#emd-about a.top{
vertical-align: top;
}
#emd-about li{
list-style-type: none;
line-height: normal;
}
#emd-about ol li {
    list-style-type: decimal;
}
#emd-about .quote{
    background: #fff;
    border-left: 4px solid #088cf9;
    -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
    margin-top: 25px;
    padding: 1px 12px;
}
#emd-about .tooltip{
    display: inline;
    position: relative;
}
#emd-about .tooltip:hover:after{
    background: #333;
    background: rgba(0,0,0,.8);
    border-radius: 5px;
    bottom: 26px;
    color: #fff;
    content: 'Click to enlarge';
    left: 20%;
    padding: 5px 15px;
    position: absolute;
    z-index: 98;
    width: 220px;
}
</style>

<?php add_thickbox(); ?>
<div id="emd-about" class="wrap about-wrap">
<div id="emd-header" style="padding:10px 0" class="wp-clearfix">
<div style="float:right"><img src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/sim_logo.png"; ?>"></div>
<div style="margin: .2em 200px 0 0;padding: 0;color: #32373c;line-height: 1.2em;font-size: 2.8em;font-weight: 400;">
<?php printf(__('Welcome to Software Issue Manager Community %s', 'software-issue-manager') , $display_version); ?>
</div>

<p class="about-text">
<?php printf(__("For effective and efficient issue management", 'software-issue-manager') , $display_version); ?>
</p>
<div style="display: inline-block;"><a style="height: 50px; background:#ff8484;padding:10px 12px;color:#ffffff;text-align: center;font-weight: bold;line-height: 50px; font-family: Arial;border-radius: 6px; text-decoration: none;" href="https://emdplugins.com/plugin-pricing/software-issue-manager-wordpress-plugin-pricing/?pk_campaign=software-issue-manager-upgradebtn&amp;pk_kwd=software-issue-manager-resources"><?php printf(__('Upgrade Now', 'software-issue-manager') , $display_version); ?></a></div>
<div style="display: inline-block;margin-bottom: 20px;"><a style="height: 50px; background:#f0ad4e;padding:10px 12px;color:#ffffff;text-align: center;font-weight: bold;line-height: 50px; font-family: Arial;border-radius: 6px; text-decoration: none;" href="https://simpro.emdplugins.com//?pk_campaign=software-issue-manager-buybtn&amp;pk_kwd=software-issue-manager-resources"><?php printf(__('Visit Pro Demo Site', 'software-issue-manager') , $display_version); ?></a></div>
<?php
	$tabs['getting-started'] = __('Getting Started', 'software-issue-manager');
	$tabs['release-notes'] = __('Release Notes', 'software-issue-manager');
	$tabs['resources'] = __('Resources', 'software-issue-manager');
	$tabs['features'] = __('Features', 'software-issue-manager');
	$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'getting-started';
	echo '<h2 class="nav-tab-wrapper wp-clearfix">';
	foreach ($tabs as $ktab => $mytab) {
		$tab_url[$ktab] = esc_url(add_query_arg(array(
			'tab' => $ktab
		)));
		$active = "";
		if ($active_tab == $ktab) {
			$active = "nav-tab-active";
		}
		echo '<a href="' . esc_url($tab_url[$ktab]) . '" class="nav-tab ' . $active . '" id="nav-' . $ktab . '">' . $mytab . '</a>';
	}
	echo '</h2>';
?>
<?php echo '<div class="tab-content" id="tab-getting-started"';
	if ("getting-started" != $active_tab) {
		echo 'style="display:none;"';
	}
	echo '>';
?>
<div style="height:25px" id="rtop"></div><div class="toc"><h3 style="color:#0073AA;text-align:left;">Quickstart</h3><ul><li><a href="#gs-sec-181">Live Demo Site</a></li>
<li><a href="#gs-sec-262">Need Help?</a></li>
<li><a href="#gs-sec-263">Learn More</a></li>
<li><a href="#gs-sec-261">Installation, Configuration & Customization Service</a></li>
<li><a href="#gs-sec-15">Issue Manager Workflow a.k.a How to use this tool to make things happen</a></li>
<li><a href="#gs-sec-10">Using Setup assistant</a></li>
<li><a href="#gs-sec-12">How to create your first project</a></li>
<li><a href="#gs-sec-9">How to create your first issue</a></li>
<li><a href="#gs-sec-17">How to customize it to better match your needs</a></li>
<li><a href="#gs-sec-18">How to limit access to issue entry and search forms by logged-in users only</a></li>
<li><a href="#gs-sec-19">How to resolve theme related issues</a></li>
</ul></div><div class="quote">
<p class="about-description">The secret of getting ahead is getting started - Mark Twain</p>
</div>
<div id="gs-sec-181"></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">Live Demo Site</div><div class="changelog emd-section getting-started-181" style="margin:0;background-color:white;padding:10px"><div id="gallery"></div><div class="sec-desc"><p>Feel free to check out our <a target="_blank" href="https://simcom.emdplugins.com//?pk_campaign=software-issue-manager-gettingstarted&pk_kwd=software-issue-manager-livedemo">live demo site</a> to learn how to use Software Issue Manager Community starter edition. The demo site will always have the latest version installed.</p>
<p>You can also use the demo site to identify possible issues. If the same issue exists in the demo site, open a support ticket and we will fix it. If a Software Issue Manager Community feature is not functioning or displayed correctly in your site but looks and works properly in the demo site, it means the theme or a third party plugin or one or more configuration parameters of your site is causing the issue.</p>
<p>If you'd like us to identify and fix the issues specific to your site, purchase a work order to get started.</p>
<p><a target="_blank" style="
    padding: 16px;
    background: coral;
    border: 1px solid lightgray;
    border-radius: 12px;
    text-decoration: none;
    color: white;
    margin: 10px 0;
    display: inline-block;" href="https://emdplugins.com/expert-service-pricing/?pk_campaign=software-issue-manager-gettingstarted&pk_kwd=software-issue-manager-livedemo">Purchase Work Order</a></p></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div id="gs-sec-262"></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">Need Help?</div><div class="changelog emd-section getting-started-262" style="margin:0;background-color:white;padding:10px"><div id="gallery"></div><div class="sec-desc"><p>There are many resources available in case you need help:</p>
<ul>
<li>Search our <a target="_blank" href="https://emdplugins.com/support">knowledge base</a></li>
<li><a href="https://emdplugins.com/kb_tags/software-issue-manager" target="_blank">Browse our Software Issue Manager Community articles</a></li>
<li><a href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation" target="_blank">Check out Software Issue Manager Community documentation for step by step instructions.</a></li>
<li><a href="https://emdplugins.com/emdplugins-support-introduction/" target="_blank">Open a support ticket if you still could not find the answer to your question</a></li>
</ul>
<p>Please read <a href="https://emdplugins.com/questions/what-to-write-on-a-support-ticket-related-to-a-technical-issue/" target="_blank">"What to write to report a technical issue"</a> before submitting a support ticket.</p></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div id="gs-sec-263"></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">Learn More</div><div class="changelog emd-section getting-started-263" style="margin:0;background-color:white;padding:10px"><div id="gallery"></div><div class="sec-desc"><p>The following articles provide step by step instructions on various concepts covered in Software Issue Manager Community.</p>
<ul><li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article140">Concepts</a>
</li>
<li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article446">Quick Start</a>
</li>
<li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article143">Working with Projects</a>
</li>
<li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article142">Working with Issues</a>
</li>
<li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article144">Widgets</a>
</li>
<li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article145">Forms</a>
</li>
<li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article230">Roles and Capabilities</a>
</li>
<li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article146">Administration</a>
</li>
<li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article148">Screen Options</a>
</li>
<li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article147">Localization(l10n)</a>
</li>
<li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article443">Customizations</a>
</li>
<li>
<a target="_blank" href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#article149">Glossary</a>
</li></ul>
</div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div id="gs-sec-261"></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">Installation, Configuration & Customization Service</div><div class="changelog emd-section getting-started-261" style="margin:0;background-color:white;padding:10px"><div id="gallery"></div><div class="sec-desc"><p>Get the peace of mind that comes from having Software Issue Manager Community properly installed, configured or customized by eMarket Design.</p>
<p>Being the developer of Software Issue Manager Community, we understand how to deliver the best value, mitigate risks and get the software ready for you to use quickly.</p>
<p>Our service includes:</p>
<ul>
<li>Professional installation by eMarket Design experts.</li>
<li>Configuration to meet your specific needs</li>
<li>Installation completed quickly and according to best practice</li>
<li>Knowledge of Software Issue Manager Community best practices transferred to your team</li>
</ul>
<p>Pricing of the service is based on the complexity of level of effort, required skills or expertise. To determine the estimated price and duration of this service, and for more information about related services, purchase a work order.  
<p><a target="_blank" style="
    padding: 16px;
    background: coral;
    border: 1px solid lightgray;
    border-radius: 12px;
    text-decoration: none;
    color: white;
    margin: 10px 0;
    display: inline-block;" href="https://emdplugins.com/expert-service-pricing/?pk_campaign=software-issue-manager-gettingstarted&pk_kwd=software-issue-manager-livedemo">Purchase Work Order</a></p></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div id="gs-sec-15"></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">Issue Manager Workflow a.k.a How to use this tool to make things happen</div><div class="changelog emd-section getting-started-15" style="margin:0;background-color:white;padding:10px"><div id="gallery"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-15" href="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-requirement.jpg"; ?>"><img src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-requirement.jpg"; ?>"></a></div></div><div class="sec-desc"><p>Most projects fail due to lack of understanding of requirement management and analysis process. Requirement management and analysis is not some type of red tape dragging you down but a necessary component of successful system development process. <a href="https://speakerdeck.com/emarketdesign/effective-requirement-collection">Check out a presentation by Dara Duman on "Effective Requirement Collection".</a></p>
<ol>
  <li>Create a project</li>
  <li>Create issues and assign them to that project</li>
  <li>Alternatively, assign issues to Products using Easy Digital Download or WooCommerce extensions</li>
  <li>Update issue and project information as your project moves along in the timeline</li>
  <li>If issues are resolved; bugs fixed, feature released or task completed, write down a resolution summary</li>
  <li>Ask project members to collaborate on issue resolutions</li>
<li>Repeat this process till you get things done and move on to the next one.</li>
</ol></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div id="gs-sec-10"></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">Using Setup assistant</div><div class="changelog emd-section getting-started-10" style="margin:0;background-color:white;padding:10px"><div id="gallery"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-10" href="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-setupassistant.png"; ?>"><img src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-setupassistant.png"; ?>"></a></div></div><div class="sec-desc"><p>Setup assistant creates the issue search and entry form pages automatically.</p></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div id="gs-sec-12"></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">How to create your first project</div><div class="changelog emd-section getting-started-12" style="margin:0;background-color:white;padding:10px"><div id="gallery"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-12" href="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-project_edit.png"; ?>"><img src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-project_edit.png"; ?>"></a></div></div><div class="sec-desc"><p>Project is a collection of related issues. A project is identified by its name and version.</p>
<ol>
  <li>Click the 'Projects' tab.</li>
  <li>Click the 'Add New' sub-tab or the “Add New” button in the project list page.</li>
  <li>Start filling in your project fields. You must fill all required fields. All required fields have red star after their labels.</li>
  <li>As needed, set project taxonomies and relationships. All required relationships or taxonomies must be set.</li>
  <li>When you are ready, click Publish. If you do not have publish privileges, the "Submit for Review" button is displayed.</li>
  <li>After the submission is completed, the project status changes to "Published" or "Pending Review".</li>
<li>Click on the permalink to see the project page on the frontend</li>
</ol></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div id="gs-sec-9"></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">How to create your first issue</div><div class="changelog emd-section getting-started-9" style="margin:0;background-color:white;padding:10px"><div id="gallery"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-9" href="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-issue_edit.png"; ?>"><img src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-issue_edit.png"; ?>"></a></div></div><div class="sec-desc"><p>Issues can be any type of defects, feature requests, improvements etc. Issues can be shared by many projects.</p>
<ol>
  <li>Click the 'All Issues' link.</li>
  <li>Click “Add New” button in the issue list page.</li>
  <li>Start filling in your issue fields. You must fill all required fields. All required fields have red star after their labels.</li>
  <li>As needed, set issue taxonomies. Link issue to one or more projects by linking it under "Affected projects" connection box</li>
  <li>When you are ready, click Publish. If you do not have publish privileges, the "Submit for Review" button is displayed.</li>
  <li>After the submission is completed, the issue status changes to "Published" or "Pending Review".</li>
<li>Click on the permalink to see the issue page on the frontend</li>
</ol></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div id="gs-sec-17"></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">How to customize it to better match your needs</div><div class="changelog emd-section getting-started-17" style="margin:0;background-color:white;padding:10px"><div id="gallery"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-17" href="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-customization.png"; ?>"><img src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-customization.png"; ?>"></a></div></div><div class="sec-desc"><p>Software Issue Manager can be customized from plugin setting without modifying code or theme templates(most cases)</p>
<ul>
<li>Enable or disable all fields, taxonomies and relationships from backend and/or frontend</li>
<li>Create custom fields in the edit area and optionally display them in issue search and entry forms and frontend pages</li>
<li>Set slug of any entity and/or archive base slug</li><li>Set the page template of any entity, taxonomy and/or archive page to sidebar on left, sidebar on right or no sidebar (full width)</li>
<li>Hide the previous and next post links on the frontend for single posts</li>
<li>Hide the page navigation links on the frontend for archive posts</li>
<li>Display any side bar widget on plugin pages using EMD Widget Area</li>
<li>Set custom CSS rules for all plugin pages including plugin shortcodes</li>
</ul></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div id="gs-sec-18"></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">How to limit access to issue entry and search forms by logged-in users only</div><div class="changelog emd-section getting-started-18" style="margin:0;background-color:white;padding:10px"><div id="gallery"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-18" href="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-loginregform.png"; ?>"><img src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-loginregform.png"; ?>"></a></div></div><div class="sec-desc"><ol>
<li>Go to SIM COM menu > Settings page > Forms tab</li>
<li>Click on the form you want access to be limited by logged in users only</li>
<li>Locate Show Register / Login Form field and select from the dropdown which forms needs to show when non-logged-in users access to the form page</li>
<li>Click save changes and done</li>
</ol>

</div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px"><div id="gs-sec-19"></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">How to resolve theme related issues</div><div class="changelog emd-section getting-started-19" style="margin:0;background-color:white;padding:10px"><div id="gallery"></div><div class="sec-desc"><p>If your theme is not coded based on WordPress theme coding standards and does have an unorthodox markup, you might see some unussual things on your site such as sidebars not getting displayed where they are supposed to or random text getting displayed on headers etc. The good news is you may fix all of theme related conflicts following the steps in the documentation.</p>
<p>Please note that if you’re unfamiliar with code/templates and resolving potential conflicts, we strongly suggest to <a href="https://emdplugins.com/open-a-support-ticket/?pk_campaign=simcom-hireme">hire us</a> or a developer to complete the project for you.</p>
<p>
<a href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/#section1470">Software Issue Manager Community Edition Documentation - Resolving theme related conflicts</a>
</p></div></div><div style="margin-top:15px"><a href="#rtop" class="top">Go to top</a></div><hr style="margin-top:40px">

<?php echo '</div>'; ?>
<?php echo '<div class="tab-content" id="tab-release-notes"';
	if ("release-notes" != $active_tab) {
		echo 'style="display:none;"';
	}
	echo '>';
?>
<p class="about-description">This page lists the release notes from every production version of Software Issue Manager Community.</p>


<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.8.2 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-1287" style="margin:0">
<h3 style="font-size:18px;" class="fix"><div  style="font-size:110%;color:#c71585"><span class="dashicons dashicons-admin-tools"></span> FIX</div>
date fields on issue and project pages</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-1286" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
tested with WP 5.7</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.8.1 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-1220" style="margin:0">
<h3 style="font-size:18px;" class="fix"><div  style="font-size:110%;color:#c71585"><span class="dashicons dashicons-admin-tools"></span> FIX</div>
Required field validation is not working.</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.8.0 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-1216" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
updates and improvements to libraries</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-1215" style="margin:0">
<h3 style="font-size:18px;" class="fix"><div  style="font-size:110%;color:#c71585"><span class="dashicons dashicons-admin-tools"></span> FIX</div>
multi-select form component missing scroll bars when the content overflows its fixed height.</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.7.0 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-1099" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
updates and improvements to libraries</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-1098" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Added previous and next buttons for the edit screens of issues and projects</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.6.0 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-1032" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
Emd templates</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-1031" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
updates and improvements to form library</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-1030" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Added support for Emd Custom Field Builder when upgraded to premium editions</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.5.0 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-970" style="margin:0">
<h3 style="font-size:18px;" class="fix"><div  style="font-size:110%;color:#c71585"><span class="dashicons dashicons-admin-tools"></span> FIX</div>
Session cleanup workflow by creating a custom table to process records.</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-969" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Added Emd form builder support.</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-968" style="margin:0">
<h3 style="font-size:18px;" class="fix"><div  style="font-size:110%;color:#c71585"><span class="dashicons dashicons-admin-tools"></span> FIX</div>
XSS related issues.</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-967" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
Cleaned up unnecessary code and optimized the library file content.</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.4.1 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-900" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
code updates for better stability and compatibility</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.4.0 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-850" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
Emd templating system to match modern web standards</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-849" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Created a new shortcode page which displays all available shortcodes. You can access this page under the plugin settings.</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.3.2 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-756" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
code updates for better stability and compatibility</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.3.1 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-741" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
library updates for better stability and compatibility</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.3.0 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-603" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
library updates</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-602" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Ability to limit max size, max number of files and file types of issue and project documents</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.2.0 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-408" style="margin:0">
<h3 style="font-size:18px;" class="tweak"><div  style="font-size:110%;color:#33b5e5"><span class="dashicons dashicons-admin-settings"></span> TWEAK</div>
Updated codemirror libraries for custom CSS and JS options in plugin settings page</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-407" style="margin:0">
<h3 style="font-size:18px;" class="fix"><div  style="font-size:110%;color:#c71585"><span class="dashicons dashicons-admin-tools"></span> FIX</div>
PHP 7 compatibility</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-406" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Added container type field in the plugin settings</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-405" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Added custom JavaScript option in plugin settings under Tools tab</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.1.0 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-223" style="margin:0">
<h3 style="font-size:18px;" class="fix"><div  style="font-size:110%;color:#c71585"><span class="dashicons dashicons-admin-tools"></span> FIX</div>
WP Sessions security vulnerability</h3>
<div ></a></div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-222" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Added Email field to Issues</h3>
<div ></a></div></div></div><hr style="margin:30px 0">
<h3 style="font-size: 18px;font-weight:700;color: white;background: #708090;padding:5px 10px;width:155px;border: 2px solid #fff;border-radius:4px;text-align:center">4.0.0 changes</h3>
<div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-14" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Admin tools to recreate plugin pages</h3>
<div ></a>* Added ability to recreate installation pages from plugin settings</div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-13" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Admin tools to permanently delete plugin data</h3>
<div ></a>* Added ability to permanently delete plugin related data from plugin settings</div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-12" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Custom frontend login and registration forms</h3>
<div ></a>* Ability to limit Issue search and entry forms to logged-in users only from plugin settings</div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-11" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Custom Css area in settings</h3>
<div ></a>* Easily add site specific CSS rules in setting without getting affected by plugin updates</div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-10" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Easy customization system</h3>
<div ></a>* Ability enable/disable any field, taxonomy and relationship from backend and/or frontend</div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-9" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
EMD Widget Area for all sidebar widgets</h3>
<div ></a>* EMD Widget area to display sidebar widgets in plugin pages</div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-8" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
New templating system</h3>
<div ></a>* Ability to set page templates for issue and project single pages. Options are sidebar on left, sidebar on right or full width</div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-7" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Interface consolidation</h3>
<div ></a>* Consolidated issues and projects under projects menu</div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-6" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
WooCommerce Software Issue Manager extension</h3>
<div ></a>* Added configuration for WooCommerce Software Issue Manager extension</div></div></div><hr style="margin:30px 0"><div class="wp-clearfix"><div class="changelog emd-section whats-new whats-new-5" style="margin:0">
<h3 style="font-size:18px;" class="new"><div style="font-size:110%;color:#00C851"><span class="dashicons dashicons-megaphone"></span> NEW</div>
Easy Digital Downloads Software Issue Manager extension</h3>
<div ></a>* Added configuration for Easy Digital Downloads Software Issue Manager extension</div></div></div><hr style="margin:30px 0">
<?php echo '</div>'; ?>
<?php echo '<div class="tab-content" id="tab-resources"';
	if ("resources" != $active_tab) {
		echo 'style="display:none;"';
	}
	echo '>';
?>
<div style="height:25px" id="ptop"></div><div class="toc"><h3 style="color:#0073AA;text-align:left;">Upgrade your game for better results</h3><ul><li><a href="#gs-sec-11">Extensive documentation is available</a></li>
<li><a href="#gs-sec-16">EMD CSV Import Export Extension</a></li>
<li><a href="#gs-sec-13">Software Issue Manager Pro - for small development teams</a></li>
<li><a href="#gs-sec-14">Software Issue Manager Enterprise - for large multi-role development teams</a></li>
<li><a href="#gs-sec-138">EMD Advanced Filters and Columns Extension for finding what's important faster</a></li>
<li><a href="#gs-sec-150">EMD Active Directory/LDAP Extension helps bulk import and update Project Members data from LDAP</a></li>
<li><a href="#gs-sec-139">Incoming Email WordPress Plugin - Create issues from emails</a></li>
<li><a href="#gs-sec-35">SIM WooCommerce and SIM Easy Digital Downloads extensions - tracking issues to products</a></li>
</ul></div><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">Extensive documentation is available</div><div class="emd-section changelog resources resources-11" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-11"></div><div id="gallery" class="wp-clearfix"></div><div class="sec-desc"><a href="https://docs.emdplugins.com/docs/software-issue-manager-community-documentation/">Software Issue Manager Community Edition Documentation</a></div></div><div style="margin-top:15px"><a href="#ptop" class="top">Go to top</a></div><hr style="margin-top:40px"><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">EMD CSV Import Export Extension</div><div class="emd-section changelog resources resources-16" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-16"></div><div id="gallery" class="wp-clearfix"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-16" href="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-operations_large.png"; ?>"><img src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-operations_540.png"; ?>"></a></div></div><div class="sec-desc"><p>EMD CSV Import Export Extension allows bulk import/export/sync of issues, projects and their relationship information (including edd or woo products if corresponding extensions are purchased) from/to external systems using CSV files.</p>
<p><a href="https://emdplugins.com/plugin-features/software-issue-manager-importexport-addon/?pk_campaign=siment-buybtn&pk_kwd=simcom-resources"><img style="width: 154px;" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/button_buy-now.png"; ?>"></a></p></div></div><div style="margin-top:15px"><a href="#ptop" class="top">Go to top</a></div><hr style="margin-top:40px"><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">Software Issue Manager Pro - for small development teams</div><div class="emd-section changelog resources resources-13" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-13"></div><div id="gallery" class="wp-clearfix"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-13" href="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-simprointro.png"; ?>"><img src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-simprointro.png"; ?>"></a></div></div><div class="sec-desc"><p>Software Issue Manager Professional provides project based issue management solution with built-in reports, dashboards, and advanced collaboration methods helping organizations move faster to issue resolutions.</p>
<p><a href="https://emdplugins.com/pricing/software-issue-manager-wordpress-plugin-pricing/?pk_campaign=simpro-buybtn&pk_kwd=simcom-resources"><img style="width: 154px;" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/button_buy-now.png"; ?>"></a></p></div></div><div style="margin-top:15px"><a href="#ptop" class="top">Go to top</a></div><hr style="margin-top:40px"><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">Software Issue Manager Enterprise - for large multi-role development teams</div><div class="emd-section changelog resources resources-14" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-14"></div><div id="gallery" class="wp-clearfix"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-14" href="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-simentintro.png"; ?>"><img src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/simcom-simentintro.png"; ?>"></a></div></div><div class="sec-desc"><p>Software Issue Manager Enterprise provides project based 360-degree issue management with ability to create custom reports, built-in system and staff dashboards, built-in multi-role data access, time tracking and more.</p>
<p><a href="https://emdplugins.com/pricing/software-issue-manager-wordpress-plugin-pricing/?pk_campaign=siment-buybtn&pk_kwd=simcom-resources"><img style="width: 154px;" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/button_buy-now.png"; ?>"></a></p></div></div><div style="margin-top:15px"><a href="#ptop" class="top">Go to top</a></div><hr style="margin-top:40px"><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">EMD Advanced Filters and Columns Extension for finding what's important faster</div><div class="emd-section changelog resources resources-138" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-138"></div><div class="emd-yt" data-youtube-id="JDIHIibWyR0" data-ratio="16:9">loading...</div><div class="sec-desc"><p>EMD Advanced Filters and Columns Extension for Software Issue Manager Community edition helps you:</p><ul><li>Filter entries quickly to find what you're looking for</li><li>Save your frequently used filters so you do not need to create them again</li><li>Sort entry columns to see what's important faster</li><li>Change the display order of columns </li>
<li>Enable or disable columns for better and cleaner look </li><li>Export search results to PDF or CSV for custom reporting</li></ul><div style="margin:25px"><a href="https://emdplugins.com/plugin-features/software-issue-manager-smart-search-and-columns-addon/?pk_campaign=emd-afc-buybtn&pk_kwd=software-issue-manager-resources"><img style="width: 154px;" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/button_buy-now.png"; ?>"></a></div></div></div><div style="margin-top:15px"><a href="#ptop" class="top">Go to top</a></div><hr style="margin-top:40px"><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">EMD Active Directory/LDAP Extension helps bulk import and update Project Members data from LDAP</div><div class="emd-section changelog resources resources-150" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-150"></div><div class="emd-yt" data-youtube-id="onWfeZHLGzo" data-ratio="16:9">loading...</div><div class="sec-desc"><p>EMD Active Directory/LDAP Extension helps bulk importing and updating member data by visually mapping LDAP fields. The imports/updates can scheduled on desired intervals using WP Cron.</p>
<p><a href="https://emdplugins.com/plugin-features/software-issue-manager-microsoft-active-directoryldap-addon/?pk_campaign=emdldap-buybtn&pk_kwd=software-issue-manager-resources"><img style="width: 154px;" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/button_buy-now.png"; ?>"></a></p></div></div><div style="margin-top:15px"><a href="#ptop" class="top">Go to top</a></div><hr style="margin-top:40px"><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">Incoming Email WordPress Plugin - Create issues from emails</div><div class="emd-section changelog resources resources-139" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-139"></div><div class="emd-yt" data-youtube-id="WQW_bvJBAzA" data-ratio="16:9">loading...</div><div class="sec-desc"><p>Create issues from emails easily with Incoming Email WordPress Plugin.</p>
<p><a href="https://emdplugins.com/plugin-features/software-issue-manager-incoming-email-addon/?pk_campaign=incemail-buybtn&pk_kwd=simresources"><img style="width: 154px;" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/button_buy-now.png"; ?>"></a></p></div></div><div style="margin-top:15px"><a href="#ptop" class="top">Go to top</a></div><hr style="margin-top:40px"><div style="color:white;background:#0000003b;padding:5px 10px;font-size: 1.4em;font-weight: 600;">SIM WooCommerce and SIM Easy Digital Downloads extensions - tracking issues to products</div><div class="emd-section changelog resources resources-35" style="margin:0;background-color:white;padding:10px"><div style="height:40px" id="gs-sec-35"></div><div id="gallery" class="wp-clearfix"><div class="sec-img gallery-item"><a class="thickbox tooltip" rel="gallery-35" href="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/montage_sim_com_edd_woo_large.png"; ?>"><img src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/montage_sim_com_edd_woo_540.png"; ?>"></a></div></div><div class="sec-desc"><p>Software Issue Manager EDD and WooCommerce Extension enables development teams to track product related issues providing insight on the real cost of developing and supporting products.</p>


<div style="display: table">
<div style="display: table-row">
<div style="display: table-cell;padding-bottom: 22px;padding-right:40px;">
<a href="https://emdplugins.com/plugin-features/software-issue-manager-easy-digital-downloads-addon/?pk_campaign=simcom-buybtn&pk_kwd=simcom-resources">
<p>SIM Easy Digital Downloads extension</p>
<div><img style="width: 154px;" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/button_buy-now.png"; ?>"></div>
</a>
</div>

<div style="display: table-cell;padding-bottom: 22px;">
<a href="https://emdplugins.com/plugin-features/software-issue-manager-woocommerce-addon/?pk_campaign=simcom-buybtn&pk_kwd=simcom-resources">
<p>SIM WooCommerce extension</p>
<div><img style="width: 154px;" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/button_buy-now.png"; ?>">
</div></a>
</div>

</div>
</div></div></div><div style="margin-top:15px"><a href="#ptop" class="top">Go to top</a></div><hr style="margin-top:40px">
<?php echo '</div>'; ?>
<?php echo '<div class="tab-content" id="tab-features"';
	if ("features" != $active_tab) {
		echo 'style="display:none;"';
	}
	echo '>';
?>
<h3>Get more work done on time</h3>
<p>Explore the full list of features available in the the latest version of Software Issue Manager. Click on a feature title to learn more.</p>
<table class="widefat features striped form-table" style="width:auto;font-size:16px">
<tr><td><a href="https://emdplugins.com/?p=10487&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/responsive.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10487&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Allow access to your data from any device, any time.</a></td><td></td></tr>
<tr><td><a href="https://emdplugins.com/?p=10634&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/settings.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10634&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Powerful, easy to use customization from the plugin settings.</a></td><td> - Premium feature included in Starter edition. Pro and Enterprise have more powerful features)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10631&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/comments.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10631&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Powerful project, issue and member commenting system with file attachment support.</a></td><td> - Premium feature included in Starter edition but Pro and Enterprise have more powerful features. Enterprise is the best.)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10636&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/custom-report.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10636&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Create and display custom reports on your projects, issues and member groups.</a></td><td> - Premium feature (included in both Pro and Enterprise)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10500&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/key.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10500&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Control access to issues, projects and member profiles.</a></td><td> - Premium feature (Included in both Pro and Enterprise. Enterprise has more powerful features.)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10499&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/megaphone.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10499&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Customizable, powerful notification system to keep your project team posted.</a></td><td> - Premium feature (Included in both Pro and Enterprise. Enterprise has more powerful features.)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10635&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/clipboard.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10635&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Summary views to find patterns and track them.</a></td><td> - Premium feature (included in both Pro and Enterprise)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=12958&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/automation.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=12958&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Workflow: Automate your project tasks on a schedule basis.</a></td><td> - Premium feature (Included in Enterprise only)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=12957&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/triggers.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=12957&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Workflow: Automate your business rules when an issue, project or member record is created or updated.</a></td><td> - Premium feature (Included in Enterprise only)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=12960&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/attribute-access.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=12960&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Control who accesses what on a field by field basis.</a></td><td> - Premium feature (Included in Enterprise only)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=12959&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/frontend_edit.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=12959&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Allow all project, issue and member fields to be updated from the frontend.</a></td><td> - Premium feature (Included in Enterprise only)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10976&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/empower-users.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10976&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Give more power to project team members</a></td><td> - Premium feature (included in both Pro and Enterprise)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10630&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/shop.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10630&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Categorize projects, issues and members to form groups of interest.</a></td><td> - Premium feature included in Starter edition but Pro and Enterprise have more powerful features. Enterprise is the best.)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10501&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/speedometer.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10501&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Project and team member specific dashboards to measure performance and contributions.</a></td><td> - Premium feature (Included in Enterprise only)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10502&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/analytics.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10502&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Powerful, realtime, custom metrics to measure performance and track patterns.</a></td><td> - Premium feature (Included in Enterprise only)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10629&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/search-tickets.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10629&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Find what is important and relevant faster on your projects and issues.</a></td><td> - Premium feature included in Starter edition but Pro and Enterprise have more powerful features. Enterprise is the best.)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10633&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/customize.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10633&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Streamlined frontend forms to make it easy create and document issues, projects and members.</a></td><td> - Premium feature included in Starter edition. Pro and Enterprise have more powerful features)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10637&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/group.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10637&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Get insight on project member activities, interactions and performance.</a></td><td> - Premium feature (Included in both Pro and Enterprise. Enterprise has more powerful features.)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10628&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/widgets.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10628&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Easy to use sidebar widgets to showcase progress.</a></td><td> - Premium feature included in Starter edition but Pro and Enterprise have more powerful features. Enterprise is the best.)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10626&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/project.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10626&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Implement projects with ease.</a></td><td> - Premium feature included in Starter edition but Pro and Enterprise have more powerful features. Enterprise is the best.)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10627&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/visual-search.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10627&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Powerful project issue tracking with intuitive interface.</a></td><td> - Premium feature included in Starter edition but Pro and Enterprise have more powerful features. Enterprise is the best.)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10632&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/brush-pencil.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10632&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Create custom fields to expand your project, issue and member fields.</a></td><td> - Premium feature included in Starter edition. Pro and Enterprise have more powerful features)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10498&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/dashboard.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10498&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">System wide dashboard to keep an eye on big picture.</a></td><td> - Premium feature (Included in both Pro and Enterprise. Enterprise has more powerful features.)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10505&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/email.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10505&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Create and update project issues including comments from incoming email.</a></td><td> - Add-on (Included in Enterprise only)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10508&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/active-directory.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10508&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Get your project, issue and member information in sync with Microsoft Active Directory/LDAP services.</a></td><td> - Add-on</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10504&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/zoomin.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10504&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Find and track information on your issues, projects and project members faster with smart search.</a></td><td> - Add-on (included both Pro and Enterprise)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10507&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/woocom.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10507&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Connect and track your WooCommerce products and services with the SIM issues, projects and project members.</a></td><td> - Add-on</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10503&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/csv-impexp.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10503&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Import, export and update project, issue and member information in bulk from or to CSV.</a></td><td> - Add-on (included both Pro and Enterprise)</td></tr>
<tr><td><a href="https://emdplugins.com/?p=10506&pk_campaign=software-issue-manager-com&pk_kwd=getting-started"><img style="width:128px;height:auto" src="<?php echo SOFTWARE_ISSUE_MANAGER_PLUGIN_URL . "assets/img/eddcom.png"; ?>"></a></td><td><a href="https://emdplugins.com/?p=10506&pk_campaign=software-issue-manager-com&pk_kwd=getting-started">Connect and track your Easy Digital Downloads products and services with the SIM issues, projects and project members.</a></td><td> - Add-on</td></tr>
</table>
<?php echo '</div>'; ?>
<?php echo '</div>';
}