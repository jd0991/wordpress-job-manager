<?php //encoding: utf-8

function jobman_admin_setup() {
	// Setup the admin menu item
	$file = WP_PLUGIN_DIR.'/'.JOBMAN_FOLDER.'/job-manager.php';
	$pages = array();
	add_menu_page(__('Job Manager', 'jobman'), __('Job Manager', 'jobman'), 'manage_options', $file, 'jobman_conf');
	$pages[] = add_submenu_page($file, __('Job Manager', 'jobman'), __('Settings', 'jobman'), 'manage_options', $file, 'jobman_conf');
	$pages[] = add_submenu_page($file, __('Job Manager', 'jobman'), __('App. Form Settings', 'jobman'), 'manage_options', 'jobman-application-setup', 'jobman_application_setup');
	$pages[] = add_submenu_page($file, __('Job Manager', 'jobman'), __('List Jobs', 'jobman'), 'manage_options', 'jobman-list-jobs', 'jobman_list_jobs');
	$pages[] = add_submenu_page($file, __('Job Manager', 'jobman'), __('List Applications', 'jobman'), 'manage_options', 'jobman-list-applications', 'jobman_list_applications');

	// Load our header info
	foreach($pages as $page) {
		add_action('admin_head-'.$page, 'jobman_admin_header');
	}

	wp_enqueue_script('jobman-admin', JOBMAN_URL.'/js/admin.js', false, JOBMAN_VERSION);
	wp_enqueue_script('jquery-ui-datepicker', JOBMAN_URL.'/js/jquery-ui-datepicker.js', array('jquery-ui-core'), JOBMAN_VERSION);
	wp_enqueue_style('jobman-admin', JOBMAN_URL.'/css/admin.css', false, JOBMAN_VERSION);

	wp_enqueue_style('dashboard');
	wp_enqueue_script('dashboard');
}

function jobman_admin_header() {
?>
<script type="text/javascript"> 
//<![CDATA[
addLoadEvent(function() {
	jQuery(".datepicker").datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true, gotoCurrent: true});
	jQuery(".column-cb > *").click(function() { jQuery(".check-column > *").attr('checked', jQuery(this).is(':checked')) } );
});
//]]>
</script> 
<?php
}

function jobman_conf() {
	global $jobman_formats;
	if(isset($_REQUEST['jobmanconfsubmit'])) {
		// Configuration form as been submitted. Updated the database.
		check_admin_referer('jobman-conf-updatedb');
		jobman_conf_updatedb();
	}
	else if(isset($_REQUEST['jobmancatsubmit'])) {
		check_admin_referer('jobman-categories-updatedb');
		jobman_categories_updatedb();
	}
	else if(isset($_REQUEST['jobmaniconsubmit'])) {
		check_admin_referer('jobman-icons-updatedb');
		jobman_icons_updatedb();
	}
	else if(isset($_REQUEST['jobmanappemailsubmit'])) {
		check_admin_referer('jobman-application-email-updatedb');
		jobman_application_email_updatedb();
	}
	else if(isset($_REQUEST['jobmanotherpluginssubmit'])) {
		check_admin_referer('jobman-other-plugins-updatedb');
		jobman_other_plugins_updatedb();
	}
?>
	<div class="wrap">
		<h2><?php _e('Job Manager: Settings', 'jobman') ?></h2>
<?php
	$writeable = jobman_check_upload_dirs();
	if(!$writeable) {
		echo '<div class="error">';
		echo '<p>' . __('It seems the Job Manager data directories are not writeable. In order to allow applicants to upload resumes, and for you to upload icons, please make the following directories writeable.', 'jobman') . '</p>';
		echo '<pre>' . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . "\n";
		echo dirname(__FILE__) . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . '</pre>';
		echo '<p>' . sprintf(__('For help with changing directory permissions, please see <a href="%1s">this page</a> in the WordPress documentation.', 'jobman'), 'http://codex.wordpress.org/Changing_File_Permissions') . '</p>';
		echo '</div>';
	}

	if(!get_option('pento_consulting')) {
		$widths = array('60%', '39%');
		$functions = array(
						array('jobman_print_settings_box', 'jobman_print_categories_box', 'jobman_print_icons_box', 'jobman_print_application_email_box', 'jobman_print_other_plugins_box'),
						array('jobman_print_donate_box', 'jobman_print_about_box')
					);
		$titles = array(
					array(__('Settings', 'jobman'), __('Categories', 'jobman'), __('Icons', 'jobman'), __('Application Email Settings', 'jobman'), __('Other Plugins', 'jobman')),
					array(__('Donate', 'jobman'), __('About This Plugin', 'jobman'))
				);
	}
	else {
		$widths = array('49%', '49%');
		$functions = array(
						array('jobman_print_settings_box', 'jobman_print_categories_box', 'jobman_print_other_plugins_box'),
						array('jobman_print_icons_box', 'jobman_print_application_email_box')
					);
		$titles = array(
					array(__('Settings', 'jobman'), __('Categories', 'jobman'), __('Other Plugins', 'jobman')),
					array(__('Icons', 'jobman'), __('Application Email Settings', 'jobman'))
				);
	}
	jobman_create_dashboard($widths, $functions, $titles);
}

function jobman_print_settings_box() {
	$options = get_option('jobman_options');
	$structure = get_option('permalink_structure');
	
	$root = get_page($options['main_page']);
	if($structure == '') {
		$url_before = get_option('home') . '/?p=' . $root->ID;
		$url_after = '';
	}
	else {
		$url_before = get_option('home') . '/';
		$url_after = '/';
	}

?>
		<form action="" method="post">
		<input type="hidden" name="jobmanconfsubmit" value="1" />
<?php 
	wp_nonce_field('jobman-conf-updatedb'); 
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('URL path', 'jobman') ?></th>
				<td colspan="2">
<?php 
	echo $url_before;
	if($structure == '') {
		echo '<input type="hidden" name="page-name" value="' . $root->post_name . '" />';
	}
	else {
		echo '<input class="small-text code" type="text" name="page-name" value="' . $root->post_name . '" />';
	}
	echo $url_after; 
?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Job Manager Page Template', 'jobman') ?></th>
				<td colspan="2"><?php printf(__('You can edit the page template used by Job Manager, by editing the Template Attribute of <a href="%s">this page</a>.', 'jobman'), admin_url('page.php?action=edit&post=' . $options['main_page'])) ?></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Default email', 'jobman') ?></th>
				<td colspan="2"><input class="regular-text code" type="text" name="default-email" value="<?php echo $options['default_email'] ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Show summary or full jobs list?', 'jobman') ?></th>
				<td><select name="list-type">
					<option value="summary"<?php echo ($options['list_type'] == 'summary')?(' selected="selected"'):('') ?>><?php _e('Summary', 'jobman') ?></option>
					<option value="full"<?php echo ($options['list_type'] == 'full')?(' selected="selected"'):('') ?>><?php _e('Full', 'jobman') ?></option>
				</select></td>
				<td><span class="description">
					<?php _e('Summary: displays many jobs concisely.', 'jobman') ?><br/>
					<?php _e('Full: allows quicker access to the application form.', 'jobman') ?>
				</span></td>
			</tr>
<?php
	if(!get_option('pento_consulting')) {
?>
			<tr>
				<th scope="row"><?php _e('Hide "Powered By" link?', 'jobman') ?></th>
				<td><input type="checkbox" value="1" name="promo-link" <?php echo ($options['promo_link'])?('checked="checked" '):('') ?>/></td>
				<td><span class="description"><?php _e('If you\'re unable to donate, I would appreciate it if you left this unchecked.', 'jobman') ?></span></td>
			</tr>
<?php
	}
?>
		</table>
		
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e('Update Settings', 'jobman') ?>" /></p>
		</form>
<?php
}

function jobman_print_categories_box() {
?>
		<p>
			<strong><?php _e('Title', 'jobman') ?></strong> - <?php _e('The display name of the category', 'jobman') ?><br/>
			<strong><?php _e('Slug', 'jobman') ?></strong> - <?php _e('The URL of the category', 'jobman') ?><br/>
			<strong><?php _e('Email', 'jobman') ?></strong> - <?php _e('The address to notify when new applications are submitted in this category', 'jobman') ?>
		</p>
		<form action="" method="post">
		<input type="hidden" name="jobmancatsubmit" value="1" />
<?php 
	wp_nonce_field('jobman-categories-updatedb'); 
?>
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col"><?php _e('Title', 'jobman') ?></th>
				<th scope="col"><?php _e('Slug', 'jobman') ?></th>
				<th scope="col"><?php _e('Email', 'jobman') ?></th>
				<th scope="col" class="jobman-fielddelete"><?php _e('Delete', 'jobman') ?></th>
			</tr>
			</thead>
<?php
	$categories = get_terms('jobman_category', 'hide_empty=0');
	
	if(count($categories) > 0 ) {
		foreach($categories as $cat) {
?>
			<tr>
				<td>
					<input type="hidden" name="id[]" value="<?php echo $cat->term_id ?>" />
					<input class="regular-text code" type="text" name="title[]" value="<?php echo $cat->name ?>" />
				</td>
				<td><input class="regular-text code" type="text" name="slug[]" value="<?php echo $cat->slug ?>" /></td>
				<td><input class="regular-text code" type="text" name="email[]" value="<?php echo $cat->description ?>" /></td>
				<td><a href="#" onclick="jobman_delete(this, 'id', 'jobman-delete-category-list'); return false;"><?php _e('Delete', 'jobman') ?></a></td>
			</tr>
<?php
		}
	}
	
	$template = '<tr><td><input type="hidden" name="id[]" value="-1" />';
	$template .= '<input class="regular-text code" type="text" name="title[]" /></td>';
	$template .= '<td><input class="regular-text code" type="text" name="slug[]" /></td>';
	$template .= '<td><input class="regular-text code" type="text" name="email[]" /></td>';
	$template .= '<td><a href="#" onclick="jobman_delete(this, \\\'id\\\', \\\'jobman-delete-category-list\\\'); return false;">' . __('Delete', 'jobman') . '</a></td>';
	
	echo $template;
?>
		<tr id="jobman-catnew">
				<td colspan="4" style="text-align: right;">
					<input type="hidden" name="jobman-delete-list" id="jobman-delete-category-list" value="" />
					<a href="#" onclick="jobman_new('jobman-catnew', 'category'); return false;"><?php _e('Add New Category', 'jobman') ?></a>
				</td>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e('Update Categories', 'jobman') ?>" /></p>
<script type="text/javascript"> 
//<![CDATA[
	jobman_templates['category'] = '<?php echo $template ?>';
//]]>
</script> 
		</form>
<?php
}

function jobman_print_icons_box() {
	$options = get_option('jobman_options');
?>
		<p>
			<strong><?php _e('Icon', 'jobman') ?></strong> - <?php _e('The current icon', 'jobman') ?><br/>
			<strong><?php _e('Title', 'jobman') ?></strong> - <?php _e('The display name of the icon', 'jobman') ?><br/>
			<strong><?php _e('File', 'jobman') ?></strong> - <?php _e('The icon file', 'jobman') ?><br/>
		</p>
		<form action="" enctype="multipart/form-data" method="post">
		<input type="hidden" name="jobmaniconsubmit" value="1" />
<?php 
	wp_nonce_field('jobman-icons-updatedb'); 
?>
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" class="jobman-icon"><?php _e('Icon', 'jobman') ?></th>
				<th scope="col"><?php _e('Title', 'jobman') ?></th>
				<th scope="col"><?php _e('File', 'jobman') ?></th>
				<th scope="col" class="jobman-fielddelete"><?php _e('Delete', 'jobman') ?></th>
			</tr>
			</thead>
<?php
	$icons = $options['icons'];
	
	if(count($icons) > 0 ) {
		foreach($icons as $id => $icon) {
?>
			<tr>
				<td>
					<input type="hidden" name="id[]" value="<?php echo $id ?>" />
					<img src="<?php echo JOBMAN_URL . '/icons/' . $id . '.' . $icon['extension'] ?>" />
				</td>
				<td><input class="regular-text code" type="text" name="title[]" value="<?php echo $icon['title'] ?>" /></td>
				<td><input class="regular-text code" type="file" name="icon[]" /></td>
				<td><a href="#" onclick="jobman_delete(this, 'id', 'jobman-delete-icon-list'); return false;"><?php _e('Delete', 'jobman') ?></a></td>
			</tr>
<?php
		}
	}
	
	$template = '<tr><td><input type="hidden" name="id[]" value="-1" /></td>';
	$template .= '<td><input class="regular-text code" type="text" name="title[]" /></td>';
	$template .= '<td><input class="regular-text code" type="file" name="icon[]" /></td>';
	$template .= '<td><a href="#" onclick="jobman_delete(this, \\\'id\\\', \\\'jobman-delete-icon-list\\\'); return false;">' . __('Delete', 'jobman') . '</a></td>';
	
	echo $template;
?>
		<tr id="jobman-iconnew">
				<td colspan="4" style="text-align: right;">
					<input type="hidden" name="jobman-delete-list" id="jobman-delete-icon-list" value="" />
					<a href="#" onclick="jobman_new('jobman-iconnew', 'icon'); return false;"><?php _e('Add New Icon', 'jobman') ?></a>
				</td>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e('Update Icons', 'jobman') ?>" /></p>
<script type="text/javascript"> 
//<![CDATA[
	jobman_templates['icon'] = '<?php echo $template ?>';
//]]>
</script> 
		</form>
<?php
}

function jobman_print_application_email_box() {
	$options = get_option('jobman_options');
	
	$fields = $options['fields'];
?>
		<form action="" method="post">
		<input type="hidden" name="jobmanappemailsubmit" value="1" />
<?php 
	wp_nonce_field('jobman-application-email-updatedb'); 
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('Email Address', 'jobman') ?></th>
				<td><select name="jobman-from">
					<option value=""><?php _e('None', 'jobman') ?></option>
<?php
	$fid = $options['application_email_from'];
	if(count($fields) > 0) {
		foreach($fields as $id => $field) {
			if($field['type'] == 'text' || $field['type'] == 'textarea') {
				$selected = '';
				if($id == $fid) {
					$selected = ' selected="selected"';
				}
?>
					<option value="<?php echo $id ?>"<?php echo $selected ?>><?php echo $field['label'] ?></option>
<?php
			}
		}
	}
?>
				</select></td>
				<td><span class="description"><?php _e('The application field to use as the email address. This will be the "From" address in the initial application, and the field used for emailing applicants.', 'jobman') ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Subject', 'jobman') ?></th>
				<td>
					<input class="regular-text code" type="text" name="jobman-subject-text" value="<?php echo $options['application_email_subject_text'] ?>" /><br/>
					<select name="jobman-subject-fields[]" multiple="multiple" size="5" class="multiselect">
					<option value="" style="font-weight: bold; border-bottom: 1px solid black;"><?php _e('None', 'jobman') ?></option>
<?php
	$fids = $options['application_email_subject_fields'];
	if(count($fields) > 0) {
		foreach($fields as $id => $field) {
			if($field['type'] == 'text' || $field['type'] == 'textarea') {
				$selected = '';
				if(in_array($id, $fids)) {
					$selected = ' selected="selected"';
				}
?>
					<option value="<?php echo $id ?>"<?php echo $selected ?>><?php echo $field['label'] ?></option>
<?php
			}
		}
	}
?>
					</select>
				</td>
				<td><span class="description"><?php _e('The email subject, and any fields to include in the subject.', 'jobman') ?></span></td>
			</tr>
		</table>
		
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e('Update Email Settings', 'jobman') ?>" /></p>
		</form>
<?php
}

function jobman_print_other_plugins_box() {
?>
	<p><?php _e('Job Manager provides extra functionality through the use of other plugins available for WordPress. These plugins are not required for Job Manager to function, but do provide enhancements.', 'jobman') ?></p>
	<form action="" method="post">
	<input type="hidden" name="jobmanotherpluginssubmit" value="1" />
<?php
	wp_nonce_field('jobman-other-plugins-updatedb');

	if(class_exists('GoogleSitemapGeneratorLoader')) {
		$gxs = true;
		$gxs_status = __('Installed', 'jobman');
		$gxs_version = GoogleSitemapGeneratorLoader::GetVersion();
	}
	else {
		$gxs = false;
		$gxs_status = __('Not Installed', 'jobman');
	}
?>
		<h4><?php _e('Google XML Sitemaps', 'jobman') ?></h4>
		<p><?php _e('Allows you to automatically add all your job listing and job detail pages to your sitemap.', 'jobman') ?></p>
		<p>
			<a href="http://wordpress.org/extend/plugins/google-sitemap-generator/"><?php _e('Download', 'jobman') ?></a><br/>
			<?php _e('Status', 'jobman') ?>: <span class="<?php echo ($gxs)?('pluginokay'):('pluginwarning') ?>"><?php echo $gxs_status ?></span><br/>
			<?php echo ($gxs)?(__('Version', 'jobman') . ': ' . $gxs_version):('') ?>
			<?php echo (!$gxs || version_compare($gxs_version, '3.2', '<'))?(' <span class="pluginwarning">' . __('Job Manager requires Google XML Sitemaps version 3.2 or later.', 'jobman') . '</span>'):('') ?>
		</p>
<?php
	if($gxs && version_compare($gxs_version, '3.2', '>=')) {
?>
		<strong><?php _e('Options', 'jobman') ?></strong>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('Add Job pages to your Sitemap?', 'jobman') ?></th>
				<td><input type="checkbox" value="1" name="plugin-gxs"<?php echo ($options['plugins']['gxs'])?(' checked="checked"'):('') ?> /></td>
			</tr>
		</table>
<?php
	}
?>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e('Update Plugin Settings', 'jobman') ?>" /></p>
	</form>
<?php
}

function jobman_list_jobs() {
	$displayed = 1;

	if(array_key_exists('jobman-mass-edit-jobs', $_REQUEST) && $_REQUEST['jobman-mass-edit-jobs'] == 'delete') {
		if(array_key_exists('jobman-delete-confirmed', $_REQUEST)) {
			check_admin_referer('jobman-mass-delete-jobs');
			jobman_job_delete();
			$deleted = true;
		}
		else {
			check_admin_referer('jobman-mass-edit-jobs');
			jobman_job_delete_confirm();
			return;
		}
	}
	else if(isset($_REQUEST['jobman-jobid'])) {
		$displayed = jobman_edit_job($_REQUEST['jobman-jobid']);
		if($displayed == 1) {
			return;
		}
	}


?>
	<div class="wrap">
		<h2><?php _e('Job Manager: Jobs List', 'jobman') ?></h2>
		<form action="" method="post">
		<input type="hidden" name="jobman-jobid" value="new" />
		<p class="submit"><input type="submit" name="submit" class="button-primary" value="<?php _e('New Job', 'jobman') ?>" /></p>
		</form>
<?php
	switch($displayed) {
		case 0:
			echo '<div class="error">' . __('There is no job associated with that Job ID', 'jobman') . '</div>';
			break;
		case 2:
			echo '<div class="error">' . __('New job created', 'jobman') . '</div>';
			break;
		case 3:
			echo '<div class="error">' . __('Job updated', 'jobman') . '</div>';
			break;
	}
	
	$jobs = get_posts('post_type=jobman_job');
?>
		<form action="" method="post">
<?php 
	wp_nonce_field('jobman-mass-edit-jobs'); 
?>
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" id="cb" class="column-cb check-column"><input type="checkbox"></th>
				<th scope="col"><?php _e('Title', 'jobman') ?></th>
				<th scope="col"><?php _e('Categories', 'jobman') ?></th>
				<th scope="col"><?php _e('Display Dates', 'jobman') ?></th>
			</tr>
			</thead>
<?php
	if(count($jobs) > 0) {
		foreach($jobs as $job) {
			$jobmeta = get_post_custom($job->ID);
			$cats = wp_get_object_terms($job->ID, 'jobman_category');
			$cats_arr = array();
			if(count($cats) > 0) {
				foreach($cats as $cat) {
					$cats_arr[] = $cat->name;
				}
			}
			$catstring = implode(', ', $cats_arr);
			
			if(is_array($jobmeta['displayenddate'])) {
				$displayenddate = $jobmeta['displayenddate'][0];
			}
			else {
				$displayenddate = $jobmeta['displayenddate'];
			}
			
			$display = false;
			if($displayenddate == '' || strtotime($displayenddate) > time()) {
				$display = true;
			}
?>
			<tr>
				<th scope="row" class="check-column"><input type="checkbox" name="job[]" value="<?php echo $job->ID ?>" /></th>
				<td class="post-title page-title column-title"><strong><a href="?page=jobman-list-jobs&amp;jobman-jobid=<?php echo $job->ID ?>"><?php echo $job->post_title ?></a></strong>
				<div class="row-actions"><a href="?page=jobman-list-jobs&amp;jobman-jobid=<?php echo $job->ID ?>">Edit</a> | <a href="<?php echo get_page_link($job->ID) ?>">View</a></div></td>
				<td><?php echo $catstring ?></td>
				<td><?php echo date('Y-m-d', strtotime($job->post_date)) ?> - <?php echo ($displayenddate == '')?(__('End of Time', 'jobman')):($displayenddate) ?><br/>
				<?php echo ($display)?(__('Live/Upcoming', 'jobman')):(__('Expired', 'jobman')) ?></td>
			</tr>
<?php
		}
	}
	else {
?>
			<tr>
				<td colspan="1"><?php _e('There are currently no jobs in the system.', 'jobman') ?></td>
			</tr>
<?php
	}
?>
		</table>
		<div class="alignleft actions">
			<select name="jobman-mass-edit-jobs">
				<option value=""><?php _e('Bulk Actions', 'jobman') ?></option>
				<option value="delete"><?php _e('Delete', 'jobman') ?></option>
			</select>
			<input type="submit" value="<?php _e('Apply', 'jobman') ?>" name="submit" class="button-secondary action" />
		</div>
		</form>
	</div>
<?php
}

function jobman_edit_job($jobid) {
	$options = get_option('jobman_options');
	
	if(isset($_REQUEST['jobmansubmit'])) {
		// Job form has been submitted. Update the database.
		check_admin_referer('jobman-edit-job-'.$jobid);
		jobman_updatedb();
		if($jobid == 'new') {
			return 2;
		} 
		else {
			return 3;
		}
	}
	
	if($jobid == 'new') {
		$title = __('Job Manager: New Job', 'jobman');
		$submit = __('Create Job', 'jobman');
		$job = array();
	}
	else {
		$title = __('Job Manager: Edit Job', 'jobman');
		$submit = __('Update Job', 'jobman');
		
		$job = get_post($jobid);
		if($job == NULL) {
			// No job associated with that id.
			return 0;
		}
	}
	
	if(isset($job->ID)) {
		$jobid = $job->ID;
		$jobmeta = get_post_custom($job->ID);
		$jobcats = wp_get_object_terms($job->ID, 'jobman_category');
	}
	else {
		$jobmeta = array();
		$jobcats = array();
	}
	
	$icons = $options['icons'];
	
	$jobdata = array();
	foreach($jobmeta as $key => $value) {
		if(is_array($value)) {
			$jobdata[$key] = $value[0];
		}
		else {
			$jobdata[$key] = $value;
		}
	}
?>
	<form action="" method="post">
	<input type="hidden" name="jobmansubmit" value="1" />
	<input type="hidden" name="jobman-jobid" value="<?php echo $jobid ?>" />
<?php 
	wp_nonce_field('jobman-edit-job-'.$jobid); 
?>
	<div class="wrap">
		<h2><?php echo $title ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('Job ID', 'jobman') ?></th>
				<td><?php echo $jobid ?></td>
				<td></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Categories', 'jobman') ?></th>
				<td>
<?php
	$categories = get_terms('jobman_category', 'hide_empty=0');
	if(count($categories) > 0) {
		foreach($categories as $cat) {
			$checked = '';
			if($jobid != 'new') {
				foreach($jobcats as $jobcat) {
					if($cat->term_id == $jobcat->term_id) {
						$checked = ' checked="checked"';
						break;
					}
				}
			}
?>
					<input type="checkbox" name="jobman-categories[]" value="<?php echo $cat->slug ?>"<?php echo $checked ?> /> <?php echo $cat->name ?><br/>
<?php
		}
	}
?>
				</td>
				<td><span class="description"><?php _e('Categories that this job belongs to. It will be displayed in the job list for each category selected.', 'jobman') ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Icon', 'jobman') ?></th>
				<td>
<?php
	if(count($icons) > 0 ) {
		foreach($icons as $id => $icon) {
			if(isset($jobdata['iconid']) && $id == $jobdata['iconid']) {
				$checked = ' checked="checked"';
			}
			else {
				$checked = '';
			}
?>
					<input type="radio" name="jobman-icon" value="<?php echo $id ?>"<?php echo $checked ?> /> <img src="<?php echo JOBMAN_URL . '/icons/' . $id . '.' . $icon['extension'] ?>"> <?php echo $icon['title'] ?><br/>
<?php
		}
	}

	if(!isset($jobdata['iconid']) || $jobdata['iconid'] == 0) {
		$checked = ' checked="checked"';
	}
	else {
		$checked = '';
	}
?>
					<input type="radio" name="jobman-icon"<?php echo $checked ?> /> <?php _e('No Icon', 'jobman') ?><br/>
				</td>
				<td><span class="description"><?php _e('Icon to display for this job in the Job List', 'jobman') ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Title', 'jobman') ?></th>
				<td><input class="regular-text code" type="text" name="jobman-title" value="<?php echo $job->post_title ?>" /></td>
				<td></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Salary', 'jobman') ?></th>
				<td><input class="regular-text code" type="text" name="jobman-salary" value="<?php echo $jobdata['salary'] ?>" /></td>
				<td></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Start Date', 'jobman') ?></th>
				<td><input class="regular-text code datepicker" type="text" name="jobman-startdate" value="<?php echo $jobdata['startdate'] ?>" /></td>
				<td><span class="description"><?php _e('The date that the job starts. For positions available immediately, leave blank.', 'jobman') ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('End Date', 'jobman') ?></th>
				<td><input class="regular-text code datepicker" type="text" name="jobman-enddate" value="<?php echo $jobdata['enddate'] ?>" /></td>
				<td><span class="description"><?php _e('The date that the job finishes. For ongoing positions, leave blank.', 'jobman') ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Location', 'jobman') ?></th>
				<td><input class="regular-text code" type="text" name="jobman-location" value="<?php echo $jobdata['location'] ?>" /></td>
				<td></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Display Start Date', 'jobman') ?></th>
				<td><input class="regular-text code datepicker" type="text" name="jobman-displaystartdate" value="<?php echo date('Y-m-d', strtotime($job->post_date)) ?>" /></td>
				<td><span class="description"><?php _e('The date this job should start being displayed on the site. To start displaying immediately, leave blank.', 'jobman') ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Display End Date', 'jobman') ?></th>
				<td><input class="regular-text code datepicker" type="text" name="jobman-displayenddate" value="<?php echo $jobdata['displayenddate'] ?>" /></td>
				<td><span class="description"><?php _e('The date this job should start being displayed on the site. To display indefinitely, leave blank.', 'jobman') ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Job Information', 'jobman') ?></th>
				<td><textarea class="large-text code" name="jobman-abstract" rows="6"><?php echo $job->post_content ?></textarea></td>
				<td></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php echo $submit ?>" /></p>
	</div>
	</form>
<?php
	return 1;
}

function jobman_job_delete_confirm() {
?>
	<div class="wrap">
	<form action="" method="post">
	<input type="hidden" name="jobman-delete-confirmed" value="1" />
	<input type="hidden" name="jobman-mass-edit-jobs" value="delete" />
	<input type="hidden" name="jobman-job-ids" value="<?php echo implode(',', $_REQUEST['job']) ?>" />
<?php
	wp_nonce_field('jobman-mass-delete-jobs');
?>
		<h2><?php _e('Job Manager: Jobs', 'jobman') ?></h2>
		<p class="error"><?php _e('This will permanently delete all of the selected jobs. Please confirm that you want to continue.', 'jobman') ?></p>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e('Delete Jobs', 'jobman') ?>" /></p>
	</form>
	</div>
<?php
}

function jobman_job_delete() {
	$options = get_option('jobman_options');
	
	$jobs = explode(',', $_REQUEST['jobman-job-ids']);
	
	foreach($jobs as $job) {
		// Delete the job
		wp_delete_post($job);
	}
}

function jobman_application_setup() {
	if(isset($_REQUEST['jobmansubmit'])) {
		check_admin_referer('jobman-application-setup');
		jobman_application_setup_updatedb();
	}
	
	$options = get_option('jobman_options');
	
	$fieldtypes = array(
						'text' => __('Text Input', 'jobman'),
						'radio' => __('Radio Buttons', 'jobman'),
						'checkbox' => __('Checkboxes', 'jobman'),
						'textarea' => __('Large Text Input (textarea)', 'jobman'),
						'date' => __('Date Selector', 'jobman'),
						'file' => __('File Upload', 'jobman'),
						'heading' => __('Heading', 'jobman'),
						'blank' => __('Blank Space', 'jobman')
				);
				
	$categories = get_terms('jobman_category', 'hide_empty=0');
?>
	<form action="" method="post">
	<input type="hidden" name="jobmansubmit" value="1" />
<?php 
	wp_nonce_field('jobman-application-setup'); 
?>
	<div class="wrap">
		<h2><?php _e('Job Manager: Application Setup', 'jobman') ?></h2>
		<table class="widefat page fixed">
			<thead>
			<tr>
				<th scope="col"><?php _e('Field Label/Type', 'jobman') ?></th>
				<th scope="col"><?php _e('Categories', 'jobman') ?></th>
				<th scope="col"><?php _e('Data', 'jobman') ?></th>
				<th scope="col"><?php _e('Submit Filter/Filter Error Message', 'jobman') ?></th>
				<th scope="col" class="jobman-fieldsortorder"><?php _e('Sort Order', 'jobman') ?></th>
				<th scope="col" class="jobman-fielddelete"><?php _e('Delete', 'jobman') ?></th>
			</tr>
			</thead>
<?php
	$fields = $options['fields'];

	if(count($fields) > 0 ) {
		uasort($fields, 'jobman_sort_fields');
		foreach($fields as $id => $field) {
?>
			<tr class="form-table">
				<td>
					<input type="hidden" name="jobman-fieldid[]" value="<?php echo $id ?>" />
					<input class="regular-text code" type="text" name="jobman-label[]" value="<?php echo $field['label'] ?>" /><br/>
					<select name="jobman-type[]">
<?php
			foreach($fieldtypes as $type => $label) {
				if($field['type'] == $type) {
					$selected = ' selected="selected"';
				}
				else {
					$selected = '';
				}
?>
						<option value="<?php echo $type ?>"<?php echo $selected ?>><?php echo $label ?></option>
<?php
			}
?>
					</select><br/>
<?php
			if($field['listdisplay'] == 1) {
				$checked = ' checked="checked"';
			}
			else {
				$checked = '';
			}
?>
					<input type="checkbox" name="jobman-listdisplay[<?php echo $id ?>]" value="1"<?php echo $checked ?> /> <?php _e('Show this field in the Application List?', 'jobman') ?>
				</td>
				<td>
<?php
			if(count($categories) > 0 ) {
				foreach($categories as $cat) {
					$checked = '';
					if(in_array($cat->term_id, $field['categories'])) {
						$checked = ' checked="checked"';
					}
?>
					<input type="checkbox" name="jobman-categories[<?php echo $id ?>][]" value="<?php echo $cat->term_id ?>"<?php echo $checked ?> /> <?php echo $cat->name ?><br/>
<?php
				}
			}
?>
				</td>
				<td><textarea class="large-text code" name="jobman-data[]"><?php echo $field['data'] ?></textarea></td>
				<td>
					<textarea class="large-text code" name="jobman-filter[]"><?php echo $field['filter'] ?></textarea><br/>
					<input class="regular-text code" type="text" name="jobman-error[]" value="<?php echo $field['error'] ?>" />
				</td>
				<td><a href="#" onclick="jobman_sort_field_up(this); return false;"><?php _e('Up', 'jobman') ?></a> <a href="#" onclick="jobman_sort_field_down(this); return false;"><?php _e('Down', 'jobman') ?></a></td>
				<td><a href="#" onclick="jobman_delete(this, 'jobman-fieldid', 'jobman-delete-list'); return false;"><?php _e('Delete', 'jobman') ?></a></td>
			</tr>
<?php
		}
	}

	$template = '<tr class="form-table">';
	$template .= '<td><input type="hidden" name="jobman-fieldid[]" value="-1" /><input class="regular-text code" type="text" name="jobman-label[]" /><br/>';
	$template .= '<select name="jobman-type[]">';

	foreach($fieldtypes as $type => $label) {
		$template .= '<option value="' . $type . '">' . $label . '</option>';
	}
	$template .= '</select>';
	$template .= '<input type="checkbox" name="jobman-listdisplay" value="1" />' . __('Show this field in the Application List?', 'jobman') . '</td>';
	$template .= '<td>';
	if(count($categories) > 0 ) {
		foreach($categories as $cat) {
			$template .= '<input type="checkbox" name="jobman-categories" class="jobman-categories" value="' . $cat->term_id . '" />' . $cat->name . '<br/>';
		}
	}
	$template .= '</td>';
	$template .= '<td><textarea class="large-text code" name="jobman-data[]"></textarea></td>';
	$template .= '<td><textarea class="large-text code" name="jobman-filter[]"></textarea><br/>';
	$template .= '<input class="regular-text code" type="text" name="jobman-error[]" /></td>';
	$template .= '<td><a href="#" onclick="jobman_sort_field_up(this); return false;">' . __('Up', 'jobman') . '</a> <a href="#" onclick="jobman_sort_field_down(this); return false;">' . __('Down', 'jobman') . '</a></td>';
	$template .= '<td><a href="#" onclick="jobman_delete(this, \\\'jobman-fieldid\\\', \\\'jobman-delete-list\\\'); return false;">' . __('Delete', 'jobman') . '</a></td></tr>';
	
	$display_template = str_replace('jobman-categories', 'jobman-categories[new][0][]', $template);
	$display_template = str_replace('jobman-listdisplay', 'jobman-listdisplay[new][0][]', $display_template);
	
	echo $display_template;
?>
		<tr id="jobman-fieldnew">
				<td colspan="6" style="text-align: right;">
					<input type="hidden" name="jobman-delete-list" id="jobman-delete-list" value="" />
					<a href="#" onclick="jobman_new('jobman-fieldnew', 'field'); return false;"><?php _e('Add New Field', 'jobman') ?></a>
				</td>
		</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e('Update Application Form', 'jobman') ?>" /></p>
<script type="text/javascript"> 
//<![CDATA[
	jobman_templates['field'] = '<?php echo $template ?>';
//]]>
</script> 
	</div>
	</form>
<?php
}

function jobman_list_applications() {
	$options = get_option('jobman_options');

	$deleted = false;
	$emailed = false;
	if(array_key_exists('jobman-mass-edit', $_REQUEST) && $_REQUEST['jobman-mass-edit'] == 'delete') {
		if(array_key_exists('jobman-delete-confirmed', $_REQUEST)) {
			check_admin_referer('jobman-mass-delete-applications');
			jobman_application_delete();
			$deleted = true;
		}
		else {
			check_admin_referer('jobman-mass-edit-applications');
			jobman_application_delete_confirm();
			return;
		}
	}
	else if(array_key_exists('jobman-mass-edit', $_REQUEST) && $_REQUEST['jobman-mass-edit'] == 'email') {
		check_admin_referer('jobman-mass-edit-applications');
		jobman_application_mailout();
		return;
	}
	else if(isset($_REQUEST['appid'])) {
		jobman_application_display_details($_REQUEST['appid']);
		return;
	}
	else if(isset($_REQUEST['jobman-mailout-send'])) {
		check_admin_referer('jobman-mailout-send');
		jobman_application_mailout_send();
		$emailed = true;
	}
?>
	<div class="wrap">
		<h2><?php _e('Job Manager: Applications', 'jobman') ?></h2>
<?php
	if($deleted) {
		echo '<p class="error">' . __('Selected applications have been deleted.', 'jobman') . '</p>';
	}
	if($emailed) {
		echo '<p class="error">' . __('The mailout has been sent.', 'jobman') . '</p>';
	}
	$fields = $options['fields'];

	$categories = get_terms('jobman_category', 'hide_empty=0');
?>
		<div id="jobman-filter">
		<form action="" method="post">
			<div class="jobman-filter-normal">
				<h4><?php _e('Standard Filters', 'jobman') ?></h4>
				<table>
					<tr>
						<th scope="row"><?php _e('Job ID', 'jobman') ?>:</th>
						<td><input type="text" name="jobman-jobid" value="<?php echo (array_key_exists('jobman-jobid', $_REQUEST))?($_REQUEST['jobman-jobid']):('') ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Categories', 'jobman') ?>:</th>
						<td>
<?php
	if(count($categories) > 0) {
		$ii = 0;
		foreach($categories as $cat) {
			$checked = '';
			if(array_key_exists('jobman-categories', $_REQUEST) && is_array($_REQUEST['jobman-categories']) && in_array($cat->term_id, $_REQUEST['jobman-categories'])) {
				$checked = ' checked="checked"';
			}
?>
							<input type="checkbox" name="jobman-categories[]" value="<?php echo $cat->term_id ?>"<?php echo $checked ?> /> <?php echo $cat->name ?><br/>
<?php
		}
	}
?>
						</td>
				</table>
			</div>
			<div class="jobman-filter-custom">
				<h4><?php _e('Custom Filters', 'jobman') ?></h4>
<?php
	if(count($fields) > 0) {
		uasort($fields, 'jobman_sort_fields');
?>
				<table class="widefat page fixed" cellspacing="0">
					<thead>
					<tr>
<?php
		$fieldcount = 0;
		foreach($fields as $id => $field) {
			if($field['listdisplay']) {
				$fieldcount++;
?>
						<th scope="col"><?php echo $field['label'] ?></th>
<?php
			}
		}
?>
					</tr>
					</thead>
<?php
		echo '<tr>';
		foreach($fields as $id => $field) {
			if(!$field['listdisplay']) {
				continue;
			}
			$req_value = '';
			if(array_key_exists('jobman-field-' . $id, $_REQUEST)) {
				$req_value = $_REQUEST['jobman-field-' . $id];
			}
			switch($field['type']) {
				case 'text':
				case 'textarea':
					
						echo '<td><input type="text" name="jobman-field-' . $id . '" value="' . $req_value . '" /></td>';
					break;
				case 'date':
					echo '<td><input type="text" class="datepicker" name="jobman-field-' . $id . '" value="' . $req_value . '" /></td>';
					break;
				case 'radio':
				case 'checkbox':
					echo '<td>';
					$values = split("\n", $field['data']);
					foreach($values as $value) {
						$checked = '';
						if(is_array($req_value) && in_array(trim($value), $req_value)) {
							$checked = ' checked="checked"';
						}
						echo '<input type="checkbox" name="jobman-field-' . $id . '[]" value="' . trim($value) . '"' . $checked . ' /> ' . $value . '<br/>';
					}
					echo '</td>';
					break;
				default:
					'<td>' . __('This field cannot be filtered.', 'jobman') . '</td>';
			}
		}
		echo '</tr>';
?>
				</table>
<?php
	}
?>
				</div>
			<div style="clear: both; text-align: right;"><input type="submit" name="submit"  class="button-primary" value="<?php _e('Filter Applications', 'jobman') ?>" /></div>
			
		</form>
		</div>
		<div id="jobman-filter-link-show"><a href="#" onclick="jQuery('#jobman-filter').show('slow'); jQuery('#jobman-filter-link-show').hide(); jQuery('#jobman-filter-link-hide').show(); return false;"><?php _e('Show Filter Options') ?></a></div>
		<div id="jobman-filter-link-hide" class="hidden"><a href="#" onclick="jQuery('#jobman-filter').hide('slow'); jQuery('#jobman-filter-link-hide').hide(); jQuery('#jobman-filter-link-show').show(); return false;"><?php _e('Hide Filter Options') ?></a></div>
		
		<form action="" method="post">
<?php 
	wp_nonce_field('jobman-mass-edit-applications'); 
?>
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" id="cb" class="column-cb check-column"><input type="checkbox"></th>
				<th scope="col"><?php _e('Job', 'jobman') ?></th>
				<th scope="col"><?php _e('Categories', 'jobman') ?></th>
<?php
	if(count($fields) > 0) {
		foreach($fields as $field) {
			if($field['listdisplay']) {
?>
				<th scope="col"><?php echo $field['label'] ?></th>
<?php
			}
		}
	}
?>
				<th scope="col"><?php _e('View Details', 'jobman') ?></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<th scope="col" class="column-cb check-column"><input type="checkbox"></th>
				<th scope="col"><?php _e('Job', 'jobman') ?></th>
				<th scope="col"><?php _e('Categories', 'jobman') ?></th>
<?php
	if(count($fields) > 0) {
		foreach($fields as $field) {
			if($field['listdisplay']) {
?>
				<th scope="col"><?php echo $field['label'] ?></th>
<?php
			}
		}
	}
?>
				<th scope="col"><?php _e('View Details', 'jobman') ?></th>
			</tr>
			</tfoot>
<?php
	$args = array();
	$args['post_type'] = 'jobman_app';
	$args['offset'] = 0;
	
	// Add job filter
	if(array_key_exists('jobman-jobid', $_REQUEST)) {
		$args['post_parent'] = $_REQUEST['jobman-jobid'];
	}
	
	// Add category filter
	if(array_key_exists('jobman-categories', $_REQUEST) && is_array($_REQUEST['jobman-categories'])) {
		$args['jobman_category'] = array();
		foreach($_REQUEST['jobman-categories'] as $cat) {
			$args['jobman_category__in'][] = $cat;
		}
	}
	
	$applications = get_posts($args);
	
	$app_displayed = false;
	while(count($applications) > 0) {
		foreach($applications as $app) {
			$appmeta = get_post_custom($app->ID);

			$appdata = array();
			foreach($appmeta as $key => $value) {
				if(is_array($value)) {
					$appdata[$key] = $value[0];
				}
				else {
					$appdata[$key] = $value;
				}
			}
			
			// Check against field filters
			if(count($fields) > 0 ) {
				foreach($fields as $id => $field) {
					if(!array_key_exists('jobman-field-'.$id, $_REQUEST) || $_REQUEST['jobman-field-'.$id] == '') {
						continue;
					}
					if(!array_key_exists('data'.$id, $appdata)) {
						// No data for this key application, so it can't match. Go to next $app.
						continue 2;
					}
					switch($field['type']) {
						case 'text':
						case 'textarea':
						case 'date':
							if($appdata['data'.$id] != $_REQUEST['jobman-field-'.$id]) {
								// App doesn't match. Go to the next item in the $applications loop.
								continue 3;
							}
							break;
						case 'radio':
						case 'checkbox':
							if(is_array($_REQUEST['jobman-field-' . $id])) {
								$data = split(',', $appdata['data'.$id]);
								foreach($_REQUEST['jobman-field-' . $id] as $selected) {
									if(in_array(trim($selected), $data)) {
										// We have a match. Go to the next item in the $fields loop.
										continue 3;
									}
								}
								// There was no match. Go to next in $applications loop.
								continue 3;
							}
							break;
					}
				}
			}
			$app_displayed = true;
?>
			<tr>
				<th scope="row" class="check-column"><input type="checkbox" name="application[]" value="<?php echo $app->ID ?>" /></th>
<?php
			$parent = get_post($app->post_parent);
			if($parent != NULL && $parent->post_type == 'jobman_job') {
?>
				<td><strong><a href="?page=jobman-list-jobs&amp;jobman-jobid=<?php echo $parent->ID ?>"><?php echo $parent->post_title ?></a></strong></td>
<?php
			}
			else {
?>
				<td><?php _e('No job', 'jobman') ?></td>
<?php
			}
			
			$cats = wp_get_object_terms($app->ID, 'jobman_category');
			$cats_arr = array();
			if(count($cats) > 0) {
				foreach($cats as $cat) {
					$cats_arr[] = $cat->name;
				}
			}
?>
				<td><?php echo implode(', ', $cats_arr) ?></td>
<?php
			if(count($fields)) {
				foreach($fields as $id => $field) {
					if($field['listdisplay']) {
						$data = '';
						if(array_key_exists('data' . $id, $appdata)) {
							switch($field['type']) {
								case 'text':
								case 'radio':
								case 'checkbox':
								case 'date':
								case 'textarea':
									$data = $appdata['data'.$id];
									break;
								case 'file':
									$data = '<a href="' . admin_url('admin.php?page=jobman-list-applications&amp;appid=' . $app->ID . '&amp;getfile=' . $appdata['data'.$id]) . '">' . $appdata['data'.$id] . '</a>';
									break;
							}
						}
?>
				<td><?php echo $data ?></td>
<?php
					}
				}
			}
?>
				<td><a href="?page=jobman-list-applications&amp;appid=<?php echo $app->ID ?>"><?php _e('View Details', 'jobman') ?></a></td>
			</tr>
<?php
		}
		
		$args['offset'] += count($applications);
		
		$applications = get_posts($args);
	}
	if(!$app_displayed) {
?>
			<tr>
				<td colspan="<?php echo 3 + $fieldcount ?>"><?php _e('There are currently no applications in the system.', 'jobman') ?></td>
			</tr>
<?php
	}
?>
		</table>
		<div class="alignleft actions">
			<select name="jobman-mass-edit">
				<option value=""><?php _e('Bulk Actions', 'jobman') ?></option>
				<option value="email"><?php _e('Email', 'jobman') ?></option>
				<option value="delete"><?php _e('Delete', 'jobman') ?></option>
			</select>
			<input type="submit" value="<?php _e('Apply', 'jobman') ?>" name="submit" class="button-secondary action" />
		</div>
		</form>
	</div>
<?php
}

function jobman_application_display_details($appid) {
	$options = get_option('jobman_options');
	$url = $options['page_name'];
	$fromid = $options['application_email_from'];
?>
	<div class="wrap">
		<h2><?php _e('Job Manager: Application Details', 'jobman') ?></h2>
		<a href="?page=jobman-list-applications">&lt;--<?php _e('Back to Application List', 'jobman') ?></a>
<?php
	$app = get_post($appid);
	$appmeta = get_post_custom($appid);

	$appdata = array();
	foreach($appmeta as $key => $value) {
		if(is_array($value)) {
			$appdata[$key] = $value[0];
		}
		else {
			$appdata[$key] = $value;
		}
	}
	
	if($app != NULL) {
		echo '<table class="form-table">';
		
		$parent = get_post($app->post_parent);
		if($parent != NULL && $parent->post_type == 'jobman_job') {
			echo '<tr><th scope="row"><strong>' . __('Job', 'jobman') . '</strong></th><td><strong><a href="' . get_page_link($parent->ID) . '">' . $parent->ID . ' - ' . $parent->post_title . '</a></strong></td></tr>';
		}
		echo '<tr><th scope="row"><strong>' . __('Timestamp', 'jobman') . '</strong></th><td>' . $app->post_date . '</td></tr><tr><td colspan="2">&nbsp;</td></tr>';
		
		$fields = $options['fields'];
		foreach($appdata as $key => $item) {
			$matches = array();
			if(!preg_match('/^data(\d+)$/', $key, $matches)) {
				// Not a data key
				continue;
			}
			$fid = $matches[1];
			
			echo '<tr><th scope="row" style="white-space: nowrap;"><strong>' . $fields[$fid]['label'] . '</strong></th><td>';
			if($fid == $fromid) {
				echo '<a href="mailto:' . $item . '">';
			}
			switch($fields[$fid]['type']) {
				case 'text':
				case 'radio':
				case 'checkbox':
				case 'date':
				case 'textarea':
					echo $item;
					break;
				case 'file':
					echo '<a href="' . admin_url('admin.php?page=jobman-list-applications&amp;appid=' . $app->ID . '&amp;getfile=' . $item) . '">' . $item . '</a>';
					break;
			}
			if($fid == $fromid) {
				echo '</a>';
			}
			echo '</td></tr>';
		}
?>
		</table>
		<a href="?page=jobman-list-applications">&lt;--<?php _e('Back to Application List', 'jobman') ?></a>
<?php
	}
	else {
		echo '<p class="error">' . __('No such application.', 'jobman') . '</p>';
	}
	echo '</div>';
}

function jobman_application_delete_confirm() {
?>
	<div class="wrap">
	<form action="" method="post">
	<input type="hidden" name="jobman-delete-confirmed" value="1" />
	<input type="hidden" name="jobman-mass-edit" value="delete" />
	<input type="hidden" name="jobman-app-ids" value="<?php echo implode(',', $_REQUEST['application']) ?>" />
<?php
	wp_nonce_field('jobman-mass-delete-applications');
?>
		<h2><?php _e('Job Manager: Applications', 'jobman') ?></h2>
		<p class="error"><?php _e('This will permanently delete all of the selected applications. Please confirm that you want to continue.', 'jobman') ?></p>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e('Delete Applications', 'jobman') ?>" /></p>
	</form>
	</div>
<?php
}

function jobman_application_delete() {
	$options = get_option('jobman_options');
	
	$apps = explode(',', $_REQUEST['jobman-app-ids']);
	
	// Get the file fields
	$file_fields = array();
	foreach($options['fields'] as $id => $field) {
		if($field['type'] == 'file') {
			$file_fields[] = $id;
		}
	}
	
	foreach($apps as $app) {
		$appmeta = get_post_custom($app);
		$appdata = array();
		foreach($appmeta as $key => $value) {
			if(is_array($value)) {
				$appdata[$key] = $value[0];
			}
			else {
				$appdata[$key] = $value;
			}
		}

		// Delete any files uploaded
		foreach($file_fields as $fid) {
			if(array_key_exists('data'.$fid, $appdata)) {
				$filename = WP_PLUGIN_DIR . '/' . JOBMAN_FOLDER . '/uploads/' . $appdata['data'.$fid];
				if(file_exists($filename)) {
					unlink($filename);
				}
			}
		}
		// Delete the application
		wp_delete_post($app);
	}
}

function jobman_application_mailout() {
	global $wpdb, $current_user;
	$options = get_option('jobman_options');
	get_currentuserinfo();
	
	$fromid = $options['application_email_from'];
	
	$apps = get_posts(array('post_type' => 'jobman_app', 'post__in' => $_REQUEST['application']));
	
	$emails = array();
	foreach($apps as $app) {
		$appmeta = get_post_custom($app->ID);
		if(!array_key_exists('data'.$fromid, $appmeta)) {
			// No email for this application
			continue;
		}
		if(is_array($appmeta['data'.$fromid])) {
			$emails[] = $appmeta['data'.$fromid][0];
		}
		else {
			$emails[] = $appmeta['data'.$fromid];
		}
	}
	$email_str = implode(', ', $emails);
?>
	<div class="wrap">
		<h2><?php _e('Job Manager: Application Email', 'jobman') ?></h2>

		<form action="" method="post">
		<input type="hidden" name="jobman-mailout-send" value="1" />
<?php
	wp_nonce_field('jobman-mailout-send');
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('From', 'jobman') ?></th>
				<td><input class="regular-text code" type="text" name="jobman-from" value="<?php echo '&quot;' . $current_user->display_name . '&quot; <' . $current_user->user_email . '>' ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('To', 'jobman') ?></th>
				<td><input class="regular-text code" type="text" name="jobman-to" value="<?php echo $email_str ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Subject', 'jobman') ?></th>
				<td><input class="regular-text code" type="text" name="jobman-subject" /></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Message', 'jobman') ?></th>
				<td><textarea class="large-text code" name="jobman-message" rows="15"></textarea></td>
			</tr>
		</table>
		
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e('Send Email', 'jobman') ?>" /></p>
		</form>
	</div>
<?php
}

function jobman_application_mailout_send() {
	$from = $_REQUEST['jobman-from'];
	$to = $_REQUEST['jobman-to'];
	$subject = $_REQUEST['jobman-subject'];
	$message = $_REQUEST['jobman-message'];
	
	$header = "From: $from" . PHP_EOL;
	$header .= "Reply-To: $from" . PHP_EOL;
	$header .= "Return-Path: $from" . PHP_EOL;
	$header .= 'Content-type: text/plain; charset='. get_option('blog_charset') . PHP_EOL;

	wp_mail($to, $subject, $message, $header);
}

function jobman_conf_updatedb() {
	$options = get_option('jobman_options');
	
	$root = get_page($options['main_page']);
	$root->post_name = $_REQUEST['page-name'];
	wp_update_post($root);
	
	$options['default_email'] = $_REQUEST['default-email'];
	$options['list_type'] = $_REQUEST['list-type'];

	if(array_key_exists('promo-link', $_REQUEST) && $_REQUEST['promo-link']) {
		$options['promo_link'] = 1;
	}
	else {
		$options['promo_link'] = 0;
	}

	update_option('jobman_options', $options);
	
	if($options['plugins']['gxs']) {
		do_action('sm_rebuild');
	}
}

function jobman_updatedb() {
	global $wpdb;
	$options = get_option('jobman_options');

	$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'post_content' => stripslashes($_REQUEST['jobman-abstract']),
				'post_name' => strtolower(str_replace(' ', '-', $_REQUEST['jobman-title'])),
				'post_title' => stripslashes($_REQUEST['jobman-title']),
				'post_type' => 'jobman_job',
				'post_date' => stripslashes($_REQUEST['jobman-displaystartdate']),
				'post_parent' => $options['main_page']);
	
	if($_REQUEST['jobman-jobid'] == 'new') {
		$id = wp_insert_post($page);
		
		add_post_meta($id, 'salary', stripslashes($_REQUEST['jobman-salary']), true);
		add_post_meta($id, 'startdate', stripslashes($_REQUEST['jobman-startdate']), true);
		add_post_meta($id, 'enddate', stripslashes($_REQUEST['jobman-enddate']), true);
		add_post_meta($id, 'location', stripslashes($_REQUEST['jobman-location']), true);
		add_post_meta($id, 'displayenddate', stripslashes($_REQUEST['jobman-displayenddate']), true);
		add_post_meta($id, 'iconid', $_REQUEST['jobman-icon'], true);
	}
	else {
		$page['ID'] = $_REQUEST['jobman-jobid'];
		$id = wp_update_post($page);
		
		update_post_meta($id, 'salary', stripslashes($_REQUEST['jobman-salary']));
		update_post_meta($id, 'startdate', stripslashes($_REQUEST['jobman-startdate']));
		update_post_meta($id, 'enddate', stripslashes($_REQUEST['jobman-enddate']));
		update_post_meta($id, 'location', stripslashes($_REQUEST['jobman-location']));
		update_post_meta($id, 'displayenddate', stripslashes($_REQUEST['jobman-displayenddate']));
		update_post_meta($id, 'iconid', $_REQUEST['jobman-icon']);
	}

	wp_set_object_terms($id, $_REQUEST['jobman-categories'], 'jobman_category', false);

	if($options['plugins']['gxs']) {
		do_action('sm_rebuild');
	}
}

function jobman_categories_updatedb() {
	$options = get_option('jobman_options');
	
	$ii = 0;
	$newcount = -1;
	foreach($_REQUEST['id'] as $id) {
		if($id == -1) {
			$newcount++;
			// INSERT new field
			if($_REQUEST['title'][$ii] != '') {
				$catid = wp_insert_term($_REQUEST['title'][$ii], 'jobman_category', array('slug' => $_REQUEST['slug'][$ii], 'description' => $_REQUEST['email'][$ii]));

				$page = array(
							'comment_status' => 'closed',
							'ping_status' => 'closed',
							'post_status' => 'publish',
							'post_author' => 1,
							'post_content' => '',
							'post_name' => $_REQUEST['slug'][$ii],
							'post_title' => $_REQUEST['title'][$ii],
							'post_type' => 'jobman_joblist',
							'post_parent' => $options['main_page']);
				$id = wp_insert_post($page);
				add_post_meta($id, '_catpage', 1, true);
				add_post_meta($id, '_cat', $catid, true);
			}
			else {
				// No input. Don't insert into the DB.
				$ii++;
				continue;
			}
		}
		else {
			// UPDATE existing field
			$data = get_posts('post_type=jobman_joblist&meta_key=_cat&meta_value='.$id);
			if(count($data) > 0) {
				$page = get_post($data[0]->ID, ARRAY_A);
				$page['post_title'] = $_REQUEST['title'][$ii];
				$page['post_name'] = $_REQUEST['slug'][$ii];
				wp_update_post($page);
			}	

			if($_REQUEST['slug'][$ii] != '') {
				wp_update_term($id, 'jobman_category', array('slug' => $_REQUEST['slug'][$ii], 'description' => $_REQUEST['email'][$ii]));
			}
			else {
				wp_update_term($id, 'jobman_category', array('description' => $_REQUEST['email'][$ii]));
			}
		}
		$ii++;
	}

	$deletes = explode(',', $_REQUEST['jobman-delete-list']);
	foreach($deletes as $delete) {
		$data = get_posts('post_type=jobman_joblist&meta_key=_cat&meta_value='.$id);
		if(count($data) > 0) {
			wp_delete_post($data[0]->ID);
		}
		wp_delete_term($delete, 'jobman_category');
		
		// Delete the category from any fields
		foreach($options['fields'] as $fid => $field) {
			$loc = array_search($delete, $field['categories']);
			if($loc !== false) {
				unset($options['fields'][$fid]['categories'][$loc]);
				$options['fields'][$fid]['categories'] = array_values($options['fields'][$fid]['categories']);
			}
		}
	}

	if(get_option('jobman_plugin_gxs')) {
		do_action('sm_rebuild');
	}
}

function jobman_icons_updatedb() {
	$options = get_option('jobman_options');
	
	$ii = 0;
	$newcount = -1;
	
	foreach($_REQUEST['id'] as $id) {
		if($id == -1) {
			$newcount++;
			// INSERT new field
			if($_REQUEST['title'][$ii] != '' || $_FILES['icon']['name'][$ii] != '') {
				preg_match('/.*\.(.+)$/', $_FILES['icon']['name'][$ii], $matches);
				$ext = $matches[1];

				$options['icons'][] = array(
											'title' => $_REQUEST['title'][$ii],
											'extension' => $ext
									);
			}
			else {
				// No input. Don't insert into the DB.
				$ii++;
				continue;
			}
		}
		else {
			// UPDATE existing field
			$options['icons'][$id]['title'] = $_REQUEST['title'][$ii];

			if($_FILES['icon']['name'][$ii] != '') {
				preg_match('/.*\.(.+)$/', $_FILES['icon']['name'][$ii], $matches);
				$ext = $matches[1];
			
				$options['icons'][$id]['extension'] = $ext;
			}
		}
		
		if($_FILES['icon']['name'][$ii] != '') {
			if(is_uploaded_file($_FILES['icon']['tmp_name'][$ii])) {
				if($id == -1) {
					$keys = array_keys($options['icons']);
					$id = end($keys);
				}
				move_uploaded_file($_FILES['icon']['tmp_name'][$ii], WP_PLUGIN_DIR . '/' . JOBMAN_FOLDER . '/icons/' . $id . '.' . $ext);
			}
		}


		$ii++;
	}

	$deletes = explode(',', $_REQUEST['jobman-delete-list']);
	foreach($deletes as $delete) {
		unset($options['icons'][$delete]);
		
		// Remove the icon from any jobs that have it
		$jobs = get_posts('post_type=jobman_job&meta_key=iconid&meta_value='.$delete);
		foreach($jobs as $job) {
			update_post_meta($job->ID, 'iconid', '');
		}
	}
	
	update_option('jobman_options', $options);
}

function jobman_application_email_updatedb() {
	$options = get_option('jobman_options');
	
	$options['application_email_from'] = $_REQUEST['jobman-from'];
	$options['application_email_subject_text'] = $_REQUEST['jobman-subject-text'];
	if(is_array($_REQUEST['jobman-subject-fields'])) {
		$options['application_email_subject_fields'] = $_REQUEST['jobman-subject-fields'];
	}
	else {
		$options['application_email_subject_fields'] = array();
	}
	
	update_option('jobman_options', $options);
}

function jobman_other_plugins_updatedb() {
	$options = get_option('jobman_options');

	if($_REQUEST['plugin-gxs']) {
		$option['plugins']['gxs'] = 1;
	}
	else {
		$option['plugins']['gxs'] = 1;
	}
	
	update_option('jobman_options', $options);
}

function jobman_application_setup_updatedb() {
	$options = get_option('jobman_options');
	
	$ii = 0;
	$newcount = -1;

	foreach($_REQUEST['jobman-fieldid'] as $id) {
		if($id == -1) {
			$newcount++;
			$listdisplay = 0;
			if(isset($_REQUEST['jobman-listdisplay']['new'][$newcount])) {
				$listdisplay = 1;
			}
			// INSERT new field
			if($_REQUEST['jobman-label'][$ii] != '' || $_REQUEST['jobman-data'][$ii] != '' || $_REQUEST['jobman-type'][$ii] == 'blank') {
					$options['fields'][] = array(
												'label' => $_REQUEST['jobman-label'][$ii],
												'type' => $_REQUEST['jobman-type'][$ii],
												'listdisplay' => $listdisplay,
												'data' => stripslashes($_REQUEST['jobman-data'][$ii]),
												'filter' => stripslashes($_REQUEST['jobman-filter'][$ii]),
												'error' => stripslashes($_REQUEST['jobman-error'][$ii]),
												'sortorder' => $ii
											);
			}
			else {
				// No input, not a 'blank' field. Don't insert into the DB.
				$ii++;
				continue;
			}
		}
		else {
			$listdisplay = 0;
			if(isset($_REQUEST['jobman-listdisplay'][$id])) {
				$listdisplay = 1;
			}
			// UPDATE existing field
			if(array_key_exists($id, $options['fields'])) {
				$options['fields'][$id]['label'] = $_REQUEST['jobman-label'][$ii];
				$options['fields'][$id]['type'] = $_REQUEST['jobman-type'][$ii];
				$options['fields'][$id]['listdisplay'] = $listdisplay;
				$options['fields'][$id]['data'] = stripslashes($_REQUEST['jobman-data'][$ii]);
				$options['fields'][$id]['filter'] = stripslashes($_REQUEST['jobman-filter'][$ii]);
				$options['fields'][$id]['error'] = stripslashes($_REQUEST['jobman-error'][$ii]);
				$options['fields'][$id]['sortorder'] = $ii;
			}
		}

		$categories = array();
		if(array_key_exists('jobman-categories', $_REQUEST)) {
			if($id == -1) {
				if(array_key_exists('new', $_REQUEST['jobman-categories']) && array_key_exists($newcount, $_REQUEST['jobman-categories']['new'])) {
					$categories = $_REQUEST['jobman-categories']['new'][$newcount];
					$keys = array_keys($options['fields']);
					$id = end($keys);
				}
			}
			else if(array_key_exists($id, $_REQUEST['jobman-categories'])) {
				$categories = $_REQUEST['jobman-categories'][$id];
			}
		}
		if(count($categories) > 0) {
			if(array_key_exists($id, $options['fields'])) {
				$options['fields'][$id]['categories'] = array();
				foreach($categories as $categoryid) {
					$options['fields'][$id]['categories'][] = $categoryid;
				}
			}
		}
		else if(array_key_exists($id, $options['fields'])) {
			$options['fields'][$id]['categories'] = array();
		}
		
		$ii++;
	}
	
	$deletes = explode(',', $_REQUEST['jobman-delete-list']);
	foreach($deletes as $delete) {
		unset($options['fields'][$delete]);
	}

	update_option('jobman_options', $options);
}

function jobman_get_uploaded_file($filename) {
	require_once(ABSPATH . WPINC . '/pluggable.php');

	header("Cache-Control: no-cache");
	header("Expires: -1");

	if(!current_user_can('manage_options')) {
		header($_SERVER["SERVER_PROTOCOL"] . ' 403 Forbidden');
		header('Refresh: 0; url=' . admin_url());
		echo '<html><head><title>403 Forbidden</title></head><body><p>Access is forbidden.</p></body></html>';
		exit;
	}
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	
	switch($ext) {
		case 'doc':
			$type = 'application/msword';
			break;
		case 'docx':
			$type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
			break;
		case 'odt':
			$type = 'application/vnd.oasis.opendocument.text';
			break;
		case 'pdf':
			$type = 'application/pdf';
			break;
		case 'rtf':
			$type = 'application/rtf';
			break;
		default:
			$type = 'application/octet-stream';
	}
	header("Content-Type: application/force-download");
	header('Content-type: ' . $type);
	header("Content-Type: application/download");
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Content-Transfer-Encoding: binary');	

	readfile(WP_PLUGIN_DIR.'/'.JOBMAN_FOLDER.'/uploads/'.$filename);
	
	exit;
}

function jobman_print_donate_box() {
?>
		<p><?php _e('If this plugin helps you find that perfect new employee, I\'d appreciate it if you shared the love, by way of my Donate or Amazon Wish List links below.', 'jobman') ?></p>
		<ul>
			<li><a href="http://www.amazon.com/wishlist/1ORKI9ZG875BL"><?php _e('My Amazon Wish List', 'jobman') ?></a></li>
			<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=gary%40pento%2enet&item_name=WordPress%20Plugin%20(Job%20Manager)&item_number=Support%20Open%20Source&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8"><?php _e('Donate with PayPal', 'jobman') ?></a></li>
		</ul>
<?php
}

function jobman_print_about_box() {
?>
		<ul>
			<li><a href="http://pento.net/"><?php _e('Gary Pendergast\'s Blog', 'jobman') ?></a></li>
			<li><a href="http://twitter.com/garypendergast"><?php _e('Follow me on Twitter!', 'jobman') ?></a></li>
			<li><a href="http://pento.net/projects/wordpress-job-manager-plugin/"><?php _e('Plugin Homepage', 'jobman') ?></a></li>
			<li><a href="http://code.google.com/p/wordpress-job-manager/issues/list"><?php _e('Submit a Bug/Feature Request', 'jobman') ?></a></li>
		</ul>
<?php
}
?>