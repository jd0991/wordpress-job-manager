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
		jobman_conf_updatedb();
	}
	else if(isset($_REQUEST['jobmancatsubmit'])) {
		jobman_categories_updatedb();
	}
	else if(isset($_REQUEST['jobmaniconsubmit'])) {
		jobman_icons_updatedb();
	}
	else if(isset($_REQUEST['jobmanappemailsubmit'])) {
		jobman_application_email_updatedb();
	}
?>
	<div class="wrap">
		<h2><?php _e('Job Manager: Settings', 'jobman') ?></h2>
<?php
	if(!get_option('pento_consulting')) {
		$widths = array('60%', '39%');
		$functions = array(
						array('jobman_print_settings_box', 'jobman_print_categories_box', 'jobman_print_icons_box', 'jobman_print_application_email_box'),
						array('jobman_print_donate_box', 'jobman_print_about_box')
					);
		$titles = array(
					array(__('Settings', 'jobman'), __('Categories', 'jobman'), __('Icons', 'jobman'), __('Application Email Settings', 'jobman')),
					array(__('Donate', 'jobman'), __('About This Plugin', 'jobman'))
				);
	}
	else {
		$widths = array('49%', '49%');
		$functions = array(
						array('jobman_print_settings_box', 'jobman_print_categories_box'),
						array('jobman_print_icons_box', 'jobman_print_application_email_box')
					);
		$titles = array(
					array(__('Settings', 'jobman'), __('Categories', 'jobman')),
					array(__('Icons', 'jobman'), __('Application Email Settings', 'jobman'))
				);
	}
	jobman_create_dashboard($widths, $functions, $titles);
}

function jobman_print_settings_box() {
	$structure = get_option('permalink_structure');
	if($structure == '') {
		$url_before = get_option('home') . '/?' . $url;
		$url_after = '=all';
	}
	else {
		$url_before = get_option('home') . '/';
		$url_after = '/';
	}

?>
		<form action="" method="post">
		<input type="hidden" name="jobmanconfsubmit" value="1" />
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('URL path', 'jobman') ?></th>
				<td colspan="2"><?php echo $url_before ?><input class="small-text code" type="text" name="page-name" value="<?php echo get_option('jobman_page_name') ?>" /><?php echo $url_after ?></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Default email', 'jobman') ?></th>
				<td colspan="2"><input class="regular-text code" type="text" name="default-email" value="<?php echo get_option('jobman_default_email') ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Show summary or full jobs list?', 'jobman') ?></th>
				<td><select name="list-type">
					<option value="summary"<?php echo (get_option('jobman_list_type') == 'summary')?(' selected="selected"'):('') ?>><?php _e('Summary', 'jobman') ?></option>
					<option value="full"<?php echo (get_option('jobman_list_type') == 'full')?(' selected="selected"'):('') ?>><?php _e('Full', 'jobman') ?></option>
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
				<td><input type="checkbox" value="1" name="promo-link" <?php echo (get_option('jobman_promo_link'))?('checked="checked" '):('') ?>/></td>
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
	global $wpdb;
?>
		<p>
			<strong><?php _e('Title', 'jobman') ?></strong> - <?php _e('The display name of the category', 'jobman') ?><br/>
			<strong><?php _e('Slug', 'jobman') ?></strong> - <?php _e('The URL of the category', 'jobman') ?><br/>
			<strong><?php _e('Email', 'jobman') ?></strong> - <?php _e('The address to notify when new applications are submitted in this category', 'jobman') ?>
		</p>
		<form action="" method="post">
		<input type="hidden" name="jobmancatsubmit" value="1" />
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
	$sql = 'SELECT * FROM ' . $wpdb->prefix . 'jobman_categories ORDER BY id;';
	$categories = $wpdb->get_results($sql, ARRAY_A);
	
	if(count($categories) > 0 ) {
		foreach($categories as $cat) {
?>
			<tr>
				<td>
					<input type="hidden" name="id[]" value="<?php echo $cat['id'] ?>" />
					<input class="regular-text code" type="text" name="title[]" value="<?php echo $cat['title'] ?>" />
				</td>
				<td><input class="regular-text code" type="text" name="slug[]" value="<?php echo $cat['slug'] ?>" /></td>
				<td><input class="regular-text code" type="text" name="email[]" value="<?php echo $cat['email'] ?>" /></td>
				<td><a href="#" onclick="jobman_delete(this, 'id', 'jobman-delete-list'); return false;"><?php _e('Delete', 'jobman') ?></a></td>
			</tr>
<?php
		}
	}
	
	$template = '<tr><td><input type="hidden" name="id[]" value="-1" />';
	$template .= '<input class="regular-text code" type="text" name="title[]" /></td>';
	$template .= '<td><input class="regular-text code" type="text" name="slug[]" /></td>';
	$template .= '<td><input class="regular-text code" type="text" name="email[]" /></td>';
	$template .= '<td><a href="#" onclick="jobman_delete(this, \\\'id\\\', \\\'jobman-delete-list\\\'); return false;">' . __('Delete', 'jobman') . '</a></td>';
	
	echo $template;
?>
		<tr id="jobman-catnew">
				<td colspan="4" style="text-align: right;">
					<input type="hidden" name="jobman-delete-list" id="jobman-delete-list" value="" />
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
	global $wpdb;
?>
		<p>
			<strong><?php _e('Icon', 'jobman') ?></strong> - <?php _e('The current icon', 'jobman') ?><br/>
			<strong><?php _e('Title', 'jobman') ?></strong> - <?php _e('The display name of the icon', 'jobman') ?><br/>
			<strong><?php _e('File', 'jobman') ?></strong> - <?php _e('The icon file', 'jobman') ?><br/>
		</p>
		<form action="" enctype="multipart/form-data" method="post">
		<input type="hidden" name="jobmaniconsubmit" value="1" />
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
	$sql = 'SELECT * FROM ' . $wpdb->prefix . 'jobman_icons ORDER BY id;';
	$icons = $wpdb->get_results($sql, ARRAY_A);
	
	if(count($icons) > 0 ) {
		foreach($icons as $icon) {
?>
			<tr>
				<td>
					<input type="hidden" name="id[]" value="<?php echo $icon['id'] ?>" />
					<img src="<?php echo JOBMAN_URL . '/icons/' . $icon['id'] . '.' . $icon['extension'] ?>" />
				</td>
				<td><input class="regular-text code" type="text" name="title[]" value="<?php echo $icon['title'] ?>" /></td>
				<td><input class="regular-text code" type="file" name="icon[]" /></td>
				<td><a href="#" onclick="jobman_delete(this, 'id', 'jobman-delete-list'); return false;"><?php _e('Delete', 'jobman') ?></a></td>
			</tr>
<?php
		}
	}
	
	$template = '<tr><td><input type="hidden" name="id[]" value="-1" /></td>';
	$template .= '<td><input class="regular-text code" type="text" name="title[]" /></td>';
	$template .= '<td><input class="regular-text code" type="file" name="icon[]" /></td>';
	$template .= '<td><a href="#" onclick="jobman_delete(this, \\\'id\\\', \\\'jobman-delete-list\\\'); return false;">' . __('Delete', 'jobman') . '</a></td>';
	
	echo $template;
?>
		<tr id="jobman-iconnew">
				<td colspan="4" style="text-align: right;">
					<input type="hidden" name="jobman-delete-list" id="jobman-delete-list" value="" />
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
	global $wpdb;
	
	$sql = 'SELECT id, label, type FROM ' . $wpdb->prefix . 'jobman_application_fields ORDER BY sortorder ASC;';
	$fields = $wpdb->get_results($sql, ARRAY_A);
?>
		<form action="" method="post">
		<input type="hidden" name="jobmanappemailsubmit" value="1" />
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('Email Address', 'jobman') ?></th>
				<td><select name="jobman-from">
					<option value=""><?php _e('None', 'jobman') ?></option>
<?php
	$fid = get_option('jobman_application_email_from');
	if(count($fields) > 0) {
		foreach($fields as $field) {
			if($field['type'] == 'text' || $field['type'] == 'textarea') {
				$selected = '';
				if($field['id'] == $fid) {
					$selected = ' selected="selected"';
				}
?>
					<option value="<?php echo $field['id'] ?>"<?php echo $selected ?>><?php echo $field['label'] ?></option>
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
					<input class="regular-text code" type="text" name="jobman-subject-text" value="<?php echo get_option('jobman_application_email_subject_text') ?>" /><br/>
					<select name="jobman-subject-fields[]" multiple="multiple" size="5" class="multiselect">
					<option value="" style="font-weight: bold; border-bottom: 1px solid black;"><?php _e('None', 'jobman') ?></option>
<?php
	$fid_text = get_option('jobman_application_email_subject_fields');
	$fids = split(',', $fid_text);
	if(count($fields) > 0) {
		foreach($fields as $field) {
			if($field['type'] == 'text' || $field['type'] == 'textarea') {
				$selected = '';
				if(in_array($field['id'], $fids)) {
					$selected = ' selected="selected"';
				}
?>
					<option value="<?php echo $field['id'] ?>"<?php echo $selected ?>><?php echo $field['label'] ?></option>
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

function jobman_list_jobs() {
	global $wpdb;
	
	$displayed = 1;
	if(isset($_REQUEST['jobman-jobid'])) {
		$displayed = jobman_edit_job($_REQUEST['jobman-jobid']);
		if($displayed == 1) {
			return;
		}
	}
?>
	<form action="" method="post">
	<input type="hidden" name="jobman-jobid" value="new" />
	<div class="wrap">
		<h2><?php _e('Job Manager: Jobs List', 'jobman') ?></h2>
		<p class="submit"><input type="submit" name="submit" class="button-primary" value="<?php _e('New Job', 'jobman') ?>" /></p>
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
	
	$sql = 'SELECT id, title, displaystartdate, displayenddate, displayenddate > NOW() AS display FROM ' . $wpdb->prefix . 'jobman_jobs ORDER BY displayenddate DESC, displaystartdate DESC';
	$jobs = $wpdb->get_results($sql, ARRAY_A);
?>
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col"><?php _e('Title', 'jobman') ?></th>
				<th scope="col"><?php _e('Categories', 'jobman') ?></th>
				<th scope="col"><?php _e('Display Dates', 'jobman') ?></th>
			</tr>
			</thead>
<?php
	if(count($jobs) > 0) {
		foreach($jobs as $job) {
			$sql = $wpdb->prepare('SELECT c.title AS title FROM ' . $wpdb->prefix . 'jobman_categories AS c LEFT JOIN ' . $wpdb->prefix . 'jobman_job_category AS jc ON c.id=jc.categoryid WHERE jc.jobid=%d;', $job['id']);
			$data = $wpdb->get_results($sql, ARRAY_A);
			$cats = array();
			$catstring = '';
			if(count($data) > 0) {
				foreach($data as $cat) {
					$cats[] = $cat['title'];
				}
			}
			$catstring = implode(', ', $cats);
?>
			<tr>
				<td class="post-title page-title column-title"><strong><a href="?page=jobman-list-jobs&amp;jobman-jobid=<?php echo $job['id'] ?>"><?php echo $job['title']?></a></strong>
				<div class="row-actions"><a href="?page=jobman-list-jobs&amp;jobman-jobid=<?php echo $job['id'] ?>">Edit</a> | <a href="<?php echo jobman_url('view', $job['id'] . '-' . strtolower(str_replace(' ', '-', $job['title']))) ?>">View</a></div></td>
				<td><?php echo $catstring ?></td>
				<td><?php echo ($job['displaystartdate'] == '')?(__('Now', 'jobman')):($job['displaystartdate']) ?> - <?php echo ($job['displayenddate'] == '')?(__('End of Time', 'jobman')):($job['displayenddate']) ?><br/>
				<?php echo ($job['display'])?(__('Live', 'jobman')):(__('Expired', 'jobman')) ?></td>
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
	</div>
	</form>
<?php
}

function jobman_edit_job($jobid) {
	global $wpdb;
	if(isset($_REQUEST['jobmansubmit'])) {
		// Job form has been submitted. Update the database.
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
		
		$sql = $wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . 'jobman_jobs WHERE id=%d;', $jobid);
		$data = $wpdb->get_results($sql, ARRAY_A);
		
		if(count($data) == 0) {
			// No job associated with that id.
			return 0;
		}
		$job = $data[0];
	}
	
	if(isset($job['id'])) {
		$jobid = $job['id'];
	}
	
	$sql = 'SELECT * FROM ' . $wpdb->prefix . 'jobman_icons ORDER BY id';
	$icons = $wpdb->get_results($sql, ARRAY_A);
?>
	<form action="" method="post">
	<input type="hidden" name="jobmansubmit" value="1" />
	<input type="hidden" name="jobman-jobid" value="<?php echo $jobid ?>" />
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
	if($jobid == 'new') {
		$sql = 'SELECT id, title, 0 AS checked FROM ' . $wpdb->prefix . 'jobman_categories;';
	}
	else {
		$sql = $wpdb->prepare('SELECT c.id AS id, c.title AS title, (jc.jobid IS NOT NULL) AS checked FROM ' . $wpdb->prefix . 'jobman_categories AS c LEFT JOIN ' . $wpdb->prefix . 'jobman_job_category AS jc ON jc.categoryid=c.id AND jc.jobid=%d;', $jobid);
	}
	$categories = $wpdb->get_results($sql, ARRAY_A);
	if(count($categories) > 0) {
		foreach($categories as $cat) {
			$checked = '';
			if($cat['checked']) {
				$checked = ' checked="checked"';
			}
?>
					<input type="checkbox" name="jobman-categories[]" value="<?php echo $cat['id'] ?>"<?php echo $checked ?> /> <?php echo $cat['title'] ?><br/>
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
		foreach($icons as $icon) {
			if(isset($job['iconid']) && $icon['id'] == $job['iconid']) {
				$checked = ' checked="checked"';
			}
			else {
				$checked = '';
			}
?>
					<input type="radio" name="jobman-icon" value="<?php echo $icon['id']?>"<?php echo $checked ?> /> <img src="<?php echo JOBMAN_URL . '/icons/' . $icon['id'] . '.' . $icon['extension'] ?>"> <?php echo $icon['title'] ?><br/>
<?php
		}
	}

	if(!isset($job['iconid']) || $job['iconid'] == 0) {
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
				<td><input class="regular-text code" type="text" name="jobman-title" value="<?php echo $job['title'] ?>" /></td>
				<td></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Salary', 'jobman') ?></th>
				<td><input class="regular-text code" type="text" name="jobman-salary" value="<?php echo $job['salary'] ?>" /></td>
				<td></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Start Date', 'jobman') ?></th>
				<td><input class="regular-text code datepicker" type="text" name="jobman-startdate" value="<?php echo $job['startdate'] ?>" /></td>
				<td><span class="description"><?php _e('The date that the job starts. For positions available immediately, leave blank.', 'jobman') ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('End Date', 'jobman') ?></th>
				<td><input class="regular-text code datepicker" type="text" name="jobman-enddate" value="<?php echo $job['enddate'] ?>" /></td>
				<td><span class="description"><?php _e('The date that the job finishes. For ongoing positions, leave blank.', 'jobman') ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Location', 'jobman') ?></th>
				<td><input class="regular-text code" type="text" name="jobman-location" value="<?php echo $job['location'] ?>" /></td>
				<td></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Display Start Date', 'jobman') ?></th>
				<td><input class="regular-text code datepicker" type="text" name="jobman-displaystartdate" value="<?php echo $job['displaystartdate'] ?>" /></td>
				<td><span class="description"><?php _e('The date this job should start being displayed on the site. To start displaying immediately, leave blank.', 'jobman') ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Display End Date', 'jobman') ?></th>
				<td><input class="regular-text code datepicker" type="text" name="jobman-displayenddate" value="<?php echo $job['displayenddate'] ?>" /></td>
				<td><span class="description"><?php _e('The date this job should start being displayed on the site. To display indefinitely, leave blank.', 'jobman') ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Job Information', 'jobman') ?></th>
				<td><textarea class="large-text code" name="jobman-abstract" rows="6"><?php echo $job['abstract'] ?></textarea></td>
				<td></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php echo $submit ?>" /></p>
	</div>
	</form>
<?php
	return 1;
}

function jobman_application_setup() {
	global $wpdb;
	
	if(isset($_REQUEST['jobmansubmit'])) {
		jobman_application_setup_updatedb();
	}
	
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
				
	$sql = 'SELECT id, title FROM ' . $wpdb->prefix . 'jobman_categories ORDER BY title ASC;';
	$categories = $wpdb->get_results($sql, ARRAY_A);
?>
	<form action="" method="post">
	<input type="hidden" name="jobmansubmit" value="1" />
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
	$sql = 'SELECT af.*, (SELECT COUNT(*) FROM wp_jobman_application_field_categories AS afc WHERE afc.afid=af.id) AS categories FROM ' . $wpdb->prefix . 'jobman_application_fields AS af ORDER BY af.sortorder ASC;';
	$fields = $wpdb->get_results($sql, ARRAY_A);

	if(count($fields) > 0 ) {
		foreach($fields as $field) {
?>
			<tr class="form-table">
				<td>
					<input type="hidden" name="jobman-fieldid[]" value="<?php echo $field['id'] ?>" />
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
					<input type="checkbox" name="jobman-listdisplay[<?php echo $field['id'] ?>]" value="1"<?php echo $checked ?> /> <?php _e('Show this field in the Application List?', 'jobman') ?>
				</td>
				<td>
<?php
			$field_categories = array();
			if($field['categories'] > 0) {
				$sql = 'SELECT categoryid FROM ' . $wpdb->prefix . 'jobman_application_field_categories WHERE afid=' . $field['id'] . ';';
				$field_categories = $wpdb->get_results($sql, ARRAY_A);
			}
			if(count($categories) > 0 ) {
				foreach($categories as $cat) {
					$checked = '';
					foreach($field_categories as $fc) {
						if(in_array($cat['id'], $fc)) {
							$checked = ' checked="checked"';
							break;
						}
					}
?>
					<input type="checkbox" name="jobman-categories[<?php echo $field['id'] ?>][]" value="<?php echo $cat['id'] ?>"<?php echo $checked ?> /> <?php echo $cat['title'] ?><br/>
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
			$template .= '<input type="checkbox" name="jobman-categories" class="jobman-categories" value="' . $cat['id'] . '" />' . $cat['title'] . '<br/>';
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
	global $wpdb;

	$deleted = false;
	$emailed = false;
	if($_REQUEST['jobman-mass-edit'] == 'delete') {
		if(isset($_REQUEST['jobman-delete-confirmed'])) {
			jobman_application_delete();
			$deleted = true;
		}
		else {
			jobman_application_delete_confirm();
			return;
		}
	}
	else if($_REQUEST['jobman-mass-edit'] == 'email') {
		jobman_application_mailout();
		return;
	}
	else if(isset($_REQUEST['appid'])) {
		jobman_application_display_details($_REQUEST['appid']);
		return;
	}
	else if(isset($_REQUEST['jobman-mailout-send'])) {
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
	$sql = 'SELECT id, label, type, data FROM ' . $wpdb->prefix . 'jobman_application_fields WHERE listdisplay=1 ORDER BY sortorder ASC';
	$fields = $wpdb->get_results($sql, ARRAY_A);

	$sql = 'SELECT id, title FROM ' . $wpdb->prefix . 'jobman_categories;';
	$categories = $wpdb->get_results($sql, ARRAY_A);
?>
		<div id="jobman-filter">
		<form action="" method="post">
			<div class="jobman-filter-normal">
				<h4><?php _e('Standard Filters', 'jobman') ?></h4>
				<table>
					<tr>
						<th scope="row"><?php _e('Job ID', 'jobman') ?>:</th>
						<td><input type="text" name="jobman-jobid" value="<?php $_REQUEST['jobman-jobid'] ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Categories', 'jobman') ?>:</th>
						<td>
<?php
	if(count($categories) > 0) {
		$ii = 0;
		foreach($categories as $cat) {
			$checked = '';
			if(is_array($_REQUEST['jobman-categories']) && in_array($cat['id'], $_REQUEST['jobman-categories'])) {
				$checked = ' checked="checked"';
			}
?>
							<input type="checkbox" name="jobman-categories[]" value="<?php echo $cat['id'] ?>"<?php echo $checked ?> /> <?php echo $cat['title'] ?><br/>
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
?>
				<table class="widefat page fixed" cellspacing="0">
					<thead>
					<tr>
<?php
		foreach($fields as $field) {
?>
						<th scope="col"><?php echo $field['label'] ?></th>
<?php
		}
?>
					</tr>
					</thead>
<?php
		echo '<tr>';
		foreach($fields as $field) {
			switch($field['type']) {
				case 'text':
				case 'textarea':
					echo '<td><input type="text" name="jobman-field-' . $field['id'] . '" value="' . $_REQUEST['jobman-field-' . $field['id']] . '" /></td>';
					break;
				case 'date':
					echo '<td><input type="text" class="datepicker" name="jobman-field-' . $field['id'] . '" value="' . $_REQUEST['jobman-field-' . $field['id']] . '" /></td>';
					break;
				case 'radio':
				case 'checkbox':
					echo '<td>';
					$values = split("\n", $field['data']);
					foreach($values as $value) {
						$checked = '';
						if(is_array($_REQUEST['jobman-field-' . $field['id']]) && in_array(trim($value), $_REQUEST['jobman-field-' . $field['id']])) {
							$checked = ' checked="checked"';
						}
						echo '<input type="checkbox" name="jobman-field-' . $field['id'] . '[]" value="' . trim($value) . '"' . $checked . ' /> ' . $value . '<br/>';
					}
					echo '</td>';
					break;
				default:
					_e('This field cannot be filtered.', 'jobman');
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
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" id="cb" class="column-cb check-column"><input type="checkbox"></th>
				<th scope="col"><?php _e('Job', 'jobman') ?></th>
				<th scope="col"><?php _e('Categories', 'jobman') ?></th>
<?php
	if(count($fields) > 0) {
		foreach($fields as $field) {
?>
				<th scope="col"><?php echo $field['label'] ?></th>
<?php
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
?>
				<th scope="col"><?php echo $field['label'] ?></th>
<?php
		}
	}
?>
				<th scope="col"><?php _e('View Details', 'jobman') ?></th>
			</tr>
			</tfoot>
<?php
	$sql = 'SELECT a.id AS id, a.jobid AS jobid, j.title AS jobname';
	$join = '';
	$filter = '';
	if(count($fields > 0)) {
		foreach($fields as $field) {
			$sql .= ', d' . $field['id'] . '.data AS data' . $field['id'];
			$join .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_application_data as d' . $field['id'] . ' ON d' . $field['id'] . '.applicationid=a.id AND d' . $field['id'] . '.fieldid=' . $field['id'];
			switch($field['type']) {
				case 'text':
				case 'textarea':
				case 'date':
					if(isset($_REQUEST['jobman-field-' . $field['id']]) && $_REQUEST['jobman-field-' . $field['id']] != '') {
						$filter .= $wpdb->prepare(' AND d' . $field['id'] . '.data=%s', $_REQUEST['jobman-field-' . $field['id']]);
					}
					break;
				case 'radio':
				case 'checkbox':
					if(is_array($_REQUEST['jobman-field-' . $field['id']])) {
						$filter .= ' AND (1=0';
						$values = split("\n", $field['data']);
						foreach($values as $value) {
							if(in_array(trim($value), $_REQUEST['jobman-field-' . $field['id']])) {
								$filter .= $wpdb->prepare(' OR d' . $field['id'] . '.data=%s', trim($value));
							}
						}
						$filter .= ')';
					}
					break;
			}
		}
	}
	$sql .= ' FROM ' . $wpdb->prefix . 'jobman_applications AS a';
	$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_jobs AS j ON j.id=a.jobid';
	$sql .= $join;
	
	// Add filters in
	$sql .= ' WHERE 1=1';
	if(isset($_REQUEST['jobman-jobid']) && $_REQUEST['jobman-jobid'] != '') {
		$sql .= $wpdb->prepare(' AND a.jobid=%d', $_REQUEST['jobman-jobid']);
	}
	if(is_array($_REQUEST['jobman-categories'])) {
		$sql .= ' AND (1=0';
		foreach($_REQUEST['jobman-categories'] as $cat) {
			$sql .= $wpdb->prepare(' OR %d IN (SELECT ac.categoryid FROM ' . $wpdb->prefix . 'jobman_application_categories AS ac WHERE ac.applicationid=a.id)', $cat);
		}
		$sql .= ')';
	}
	$sql .= $filter;
	
	$sql .= ' ORDER BY a.id;';

	$applications = $wpdb->get_results($sql, ARRAY_A);

	if(count($applications) > 0) {
		foreach($applications as $app) {
?>
			<tr>
				<th scope="row" class="check-column"><input type="checkbox" name="application[]" value="<?php echo $app['id'] ?>" /></th>
<?php
			if($app['jobid'] > 0) {
?>
				<td><strong><a href="?page=jobman-list-jobs&amp;jobman-jobid=<?php echo $app['jobid'] ?>"><?php echo $app['jobname']?></a></strong></td>
<?php
			}
			else {
?>
				<td><?php _e('No job', 'jobman') ?></td>
<?php
			}
			
			$sql = $wpdb->prepare('SELECT c.title AS title FROM ' . $wpdb->prefix . 'jobman_categories AS c LEFT JOIN ' . $wpdb->prefix . 'jobman_application_categories AS ac ON c.id=ac.categoryid WHERE ac.applicationid=%d;', $app['id']);
			$data = $wpdb->get_results($sql, ARRAY_A);
			$cats = array();
			$catstring = '';
			if(count($data) > 0) {
				foreach($data as $cat) {
					$cats[] = $cat['title'];
				}
			}
			$catstring = implode(', ', $cats);
?>
				<td><?php echo $catstring ?></td>
<?php
			if(count($fields)) {
				foreach($fields as $field) {
?>
				<td><?php echo $app['data'.$field['id']] ?></td>
<?php
				}
			}
?>
				<td><a href="?page=jobman-list-applications&amp;appid=<?php echo $app['id'] ?>"><?php _e('View Details', 'jobman') ?></a></td>
			</tr>
<?php
		}
	}
	else {
?>
			<tr>
				<td colspan="<?php echo 3 + count($fields) ?>"><?php _e('There are currently no applications in the system.', 'jobman') ?></td>
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
	global $wpdb;
	$url = get_option('jobman_page_name');
	$fromid = get_option('jobman_application_email_from');
?>
	<div class="wrap">
		<h2><?php _e('Job Manager: Application Details', 'jobman') ?></h2>
		<a href="?page=jobman-list-applications">&lt;--<?php _e('Back to Application List', 'jobman') ?></a>
		<table class="form-table">
<?php
	
	$sql = $wpdb->prepare('SELECT a.jobid AS jobid, j.title AS jobtitle, a.submitted AS submitted FROM ' . $wpdb->prefix . 'jobman_applications AS a LEFT JOIN ' . $wpdb->prefix . 'jobman_jobs AS j ON j.id=a.jobid WHERE a.id=%d;', $appid);
	$data = $wpdb->get_results($sql, ARRAY_A);
	if(count($data) > 0) {
		if($data[0]['jobid'] != 0) {
			echo '<tr><th scope="row"><strong>' . __('Job', 'jobman') . '</strong></th><td><strong><a href="' . jobman_url('view', $data[0]['jobid'] . '-' . strtolower(str_replace(' ', '-', $data[0]['jobtitle']))) . '">' . $data[0]['jobid'] . ' - ' . $data[0]['jobtitle'] . '</a></strong></td></tr>';
		}
		echo '<tr><th scope="row"><strong>' . __('Timestamp', 'jobman') . '</strong></th><td>' . $data[0]['submitted'] . '</td></tr><tr><td colspan="2">&nbsp;</td></tr>';
	}
	
	$sql = $wpdb->prepare('SELECT af.id AS id, af.label as label, af.type AS type, ad.data AS data FROM ' . $wpdb->prefix . 'jobman_application_data AS ad LEFT JOIN ' . $wpdb->prefix . 'jobman_application_fields AS af ON af.id=ad.fieldid WHERE ad.applicationid=%d ORDER BY af.sortorder ASC;', $appid);
	$data = $wpdb->get_results($sql, ARRAY_A);
	
	if(count($data) > 0) {
		foreach($data as $item) {
			echo '<tr><th scope="row" style="white-space: nowrap;"><strong>' . $item['label'] . '</strong></th><td>';
			if($item['id'] == $fromid) {
				echo '<a href="mailto:' . $item['data'] . '">';
			}
			switch($item['type']) {
				case 'text':
				case 'radio':
				case 'checkbox':
				case 'date':
				case 'textarea':
					echo  $item['data'];
					break;
				case 'file':
					echo '<a href="' . JOBMAN_URL . '/uploads/' . $item['data'] . '">' . $item['data'] . '</a>';
					break;
			}
			if($item['id'] == $fromid) {
				echo '</a>';
			}
			echo '</td></tr>';
		}
	}
?>
		</table>
		<a href="?page=jobman-list-applications">&lt;--<?php _e('Back to Application List', 'jobman') ?></a>
	</div>
<?php
}

function jobman_application_delete_confirm() {
?>
	<div class="wrap">
	<form action="" method="post">
	<input type="hidden" name="jobman-delete-confirmed" value="1" />
	<input type="hidden" name="jobman-mass-edit" value="delete" />
	<input type="hidden" name="jobman-app-ids" value="<?php echo implode(',', $_REQUEST['application']) ?>" />
		<h2><?php _e('Job Manager: Applications', 'jobman') ?></h2>
		<p class="error"><?php _e('This will permanently delete all of the selected applications. Please confirm that you want to continue.', 'jobman') ?></p>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e('Delete Applications', 'jobman') ?>" /></p>
	</form>
	</div>
<?php
}

function jobman_application_delete() {
	global $wpdb;
	
	$apps = explode(',', $_REQUEST['jobman-app-ids']);
	
	foreach($apps as $app) {
		// Delete the application record
		$sql = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'jobman_applications WHERE id=%d;', $app);
		$wpdb->query($sql);

		// Delete associated categories
		$sql = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'jobman_application_categories WHERE applicationid=%d;', $app);
		$wpdb->query($sql);

		// Delete any files uploaded
		$sql = $wpdb->prepare('SELECT ac.data AS name FROM ' . $wpdb->prefix . 'jobman_application_fields AS af LEFT JOIN ' . $wpdb->prefix . 'jobman_application_data AS ad ON ad.fieldid=af.id WHERE ad.applicationid=%d AND af.type="file";', $app);
		$files = $wpdb->get_results($sql, ARRAY_A);
		if(count($files) > 0) {
			foreach($files as $file) {
				$filename = WP_PLUGIN_DIR . '/' . JOBMAN_FOLDER . '/uploads/' . $file['name'];
				if(file_exists($filename)) {
					unlink($filename);
				}
			}
		}
		
		// Delete the application data
		$sql = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'jobman_application_categories WHERE jobman_application_data=%d;', $app);
		$wpdb->query($sql);
	}
}

function jobman_application_mailout() {
	global $wpdb, $current_user;
	get_currentuserinfo();
	
	$apps = implode(',', $_REQUEST['application']);
	$fromid = get_option('jobman_application_email_from');
	
	$sql = $wpdb->prepare('SELECT data FROM ' . $wpdb->prefix . 'jobman_application_data WHERE applicationid IN (' . $apps . ') AND fieldid=%d;', $fromid);
	$data = $wpdb->get_results($sql, ARRAY_A);
	
	$emails = array();
	foreach($data as $email) {
		$emails[] = $email['data'];
	}
	$email_str = implode(', ', $emails);
?>
	<div class="wrap">
		<h2><?php _e('Job Manager: Application Email', 'jobman') ?></h2>

		<form action="" method="post">
		<input type="hidden" name="jobman-mailout-send" value="1" />
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
	update_option('jobman_page_name', $_REQUEST['page-name']);
	update_option('jobman_default_email', $_REQUEST['default-email']);
	update_option('jobman_list_type', $_REQUEST['list-type']);

	if($_REQUEST['promo-link']) {
		update_option('jobman_promo_link', 1);
	}
	else {
		update_option('jobman_promo_link', 0);
	}
}

function jobman_updatedb() {
	global $wpdb;

	if($_REQUEST['jobman-jobid'] == 'new') {
		$sql = $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . 'jobman_jobs(iconid, title, salary, startdate, enddate, location, displaystartdate, displayenddate, abstract) VALUES(%d, %s, %s, %s, %s, %s, %s, %s, %s)',
								$_REQUEST['jobman-icon'], stripslashes($_REQUEST['jobman-title']), stripslashes($_REQUEST['jobman-salary']), stripslashes($_REQUEST['jobman-startdate']), stripslashes($_REQUEST['jobman-enddate']), 
								stripslashes($_REQUEST['jobman-location']), stripslashes($_REQUEST['jobman-displaystartdate']), stripslashes($_REQUEST['jobman-displayenddate']), stripslashes($_REQUEST['jobman-abstract']));
	}
	else {
		$sql = $wpdb->prepare('UPDATE ' . $wpdb->prefix . 'jobman_jobs SET iconid=%d, title=%s, salary=%s, startdate=%s, enddate=%s, location=%s, displaystartdate=%s, displayenddate=%s, abstract=%s WHERE id=%d;',
								$_REQUEST['jobman-icon'], stripslashes($_REQUEST['jobman-title']), stripslashes($_REQUEST['jobman-salary']), stripslashes($_REQUEST['jobman-startdate']), stripslashes($_REQUEST['jobman-enddate']), 
								stripslashes($_REQUEST['jobman-location']), stripslashes($_REQUEST['jobman-displaystartdate']), stripslashes($_REQUEST['jobman-displayenddate']), stripslashes($_REQUEST['jobman-abstract']), $_REQUEST['jobman-jobid']);

		// Delete all the existing category records, to prepare for any updates
		$delsql = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'jobman_job_category WHERE jobid=%d;', $_REQUEST['jobman-jobid']);
		$wpdb->query($delsql);
	}

	$wpdb->query($sql);

	if($_REQUEST['jobman-jobid'] == 'new') {
		$jobid = $wpdb->insert_id;
	}
	else {
		$jobid = $_REQUEST['jobman-jobid'];
	}
	$categories = $_REQUEST['jobman-categories'];
	if(count($categories) > 0) {
		$sql = 'INSERT INTO ' . $wpdb->prefix . 'jobman_job_category(jobid, categoryid) VALUES';
		$jj = 1;
		foreach($categories as $categoryid) {
			$sql .= $wpdb->prepare('(%d, %d)', $jobid, $categoryid);
			if($jj < count($categories)) {
				$sql .= ', ';
			}
		}
		$sql .= ';';
		$wpdb->query($sql);
	}
}

function jobman_categories_updatedb() {
	global $wpdb;
	
	$ii = 0;
	$newcount = -1;
	foreach($_REQUEST['id'] as $id) {
		if($id == -1) {
			$newcount++;
			// INSERT new field
			if($_REQUEST['title'][$ii] != '' || $_REQUEST['slug'][$ii] != '') {
				$sql = $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . 'jobman_categories(title, slug, email) VALUES(%s, %s, %s);',
								$_REQUEST['title'][$ii], $_REQUEST['slug'][$ii], $_REQUEST['email'][$ii]);
			}
			else {
				// No input. Don't insert into the DB.
				$ii++;
				continue;
			}
		}
		else {
			// UPDATE existing field
			$sql = $wpdb->prepare('UPDATE ' . $wpdb->prefix . 'jobman_categories SET title=%s, slug=%s, email=%s WHERE id=%d;',
							$_REQUEST['title'][$ii], $_REQUEST['slug'][$ii], $_REQUEST['email'][$ii], $id);
		}
		
		$wpdb->query($sql);
		$ii++;
	}

	$deletes = explode(',', $_REQUEST['jobman-delete-list']);
	foreach($deletes as $delete) {
		$sql = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'jobman_categories WHERE id=%d', $delete);
		$wpdb->query($sql);
	}
}

function jobman_icons_updatedb() {
	global $wpdb;
	
	$ii = 0;
	$newcount = -1;
	
	foreach($_REQUEST['id'] as $id) {
		if($id == -1) {
			$newcount++;
			// INSERT new field
			if($_REQUEST['title'][$ii] != '' || $_FILES['icon']['name'][$ii] != '') {
				preg_match('/.*\.(.+)$/', $_FILES['icon']['name'][$ii], $matches);
				$ext = $matches[1];

				$sql = $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . 'jobman_icons(title, extension) VALUES(%s, %s);',
								$_REQUEST['title'][$ii], $ext);
			}
			else {
				// No input. Don't insert into the DB.
				$ii++;
				continue;
			}
		}
		else {
			// UPDATE existing field
			if($_FILES['icon']['name'][$ii] != '') {
				preg_match('/.*\.(.+)$/', $_FILES['icon']['name'][$ii], $matches);
				$ext = $matches[1];
			
				$sql = $wpdb->prepare('UPDATE ' . $wpdb->prefix . 'jobman_icons SET title=%s, extension=%s WHERE id=%d;',
								$_REQUEST['title'][$ii], $ext, $id);
			}
			else {
				$sql = $wpdb->prepare('UPDATE ' . $wpdb->prefix . 'jobman_icons SET title=%s WHERE id=%d;',
								$_REQUEST['title'][$ii], $id);
			}
		}
		
		$wpdb->query($sql);

		if($_FILES['icon']['name'][$ii] != '') {
			if(is_uploaded_file($_FILES['icon']['tmp_name'][$ii])) {
				if($id == -1) {
					$id = $wpdb->insert_id;
				}
				move_uploaded_file($_FILES['icon']['tmp_name'][$ii], WP_PLUGIN_DIR . '/' . JOBMAN_FOLDER . '/icons/' . $id . '.' . $ext);
			}
		}


		$ii++;
	}

	$deletes = explode(',', $_REQUEST['jobman-delete-list']);
	foreach($deletes as $delete) {
		$sql = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'jobman_icons WHERE id=%d', $delete);
		$wpdb->query($sql);
	}
}

function jobman_application_email_updatedb() {
	update_option('jobman_application_email_from', $_REQUEST['jobman-from']);
	update_option('jobman_application_email_subject_text', $_REQUEST['jobman-subject-text']);
	if(is_array($_REQUEST['jobman-subject-fields'])) {
		update_option('jobman_application_email_subject_fields', implode(',', $_REQUEST['jobman-subject-fields']));
	}
	else {
		update_option('jobman_application_email_subject_fields', '');
	}
}

function jobman_application_setup_updatedb() {
	global $wpdb;
	
	// Delete all the existing category records, to prepare for any updates
	$sql = 'DELETE FROM ' . $wpdb->prefix . 'jobman_application_field_categories WHERE 1';
	$wpdb->query($sql);
	
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
				$sql = $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . 'jobman_application_fields(label, type, listdisplay, data, filter, error, sortorder) VALUES(%s, %s, %s, %s, %s, %s, %d);',
					$_REQUEST['jobman-label'][$ii], $_REQUEST['jobman-type'][$ii], $listdisplay, stripslashes($_REQUEST['jobman-data'][$ii]), stripslashes($_REQUEST['jobman-filter'][$ii]), stripslashes($_REQUEST['jobman-error'][$ii]), $ii);
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
			$sql = $wpdb->prepare('UPDATE ' . $wpdb->prefix . 'jobman_application_fields SET label=%s, type=%s, listdisplay=%d, data=%s, filter=%s, error=%s, sortorder=%d WHERE id=%d',
					$_REQUEST['jobman-label'][$ii], $_REQUEST['jobman-type'][$ii], $listdisplay, stripslashes($_REQUEST['jobman-data'][$ii]), stripslashes($_REQUEST['jobman-filter'][$ii]), stripslashes($_REQUEST['jobman-error'][$ii]), $ii, $id);
		}

		$wpdb->query($sql);
		
		if($id == -1) {
			$categories = $_REQUEST['jobman-categories']['new'][$newcount];
		}
		else {
			$categories = $_REQUEST['jobman-categories'][$id];
		}
		if(count($categories) > 0) {
			$sql = 'INSERT INTO ' . $wpdb->prefix . 'jobman_application_field_categories(afid, categoryid) VALUES';
			$jj = 1;
			foreach($categories as $categoryid) {
				$sql .= $wpdb->prepare('(%d, %d)', $id, $categoryid);
				if($jj < count($categories)) {
					$sql .= ', ';
				}
			}
			$sql .= ';';
			$wpdb->query($sql);
		}
		
		$ii++;
	}
	
	$deletes = explode(',', $_REQUEST['jobman-delete-list']);
	foreach($deletes as $delete) {
		$sql = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'jobman_application_fields WHERE id=%d', $delete);
		$wpdb->query($sql);
	}
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