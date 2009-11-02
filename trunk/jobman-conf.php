<?php //encoding: utf-8

function jobman_admin_setup() {
	// Setup the admin menu item
	$file = WP_PLUGIN_DIR.'/'.JOBMAN_FOLDER.'/jobman.php';
	add_menu_page(__('Job Manager', 'jobman'), __('Job Manager', 'jobman'), 'manage_options', $file, 'jobman_conf');
	add_submenu_page($file, __('Job Manager', 'jobman'), __('Settings', 'jobman'), 'manage_options', $file, 'jobman_conf');
	add_submenu_page($file, __('Job Manager', 'jobman'), __('App. Form Settings', 'jobman'), 'manage_options', 'jobman-application-setup', 'jobman_application_setup');
	$pageref = add_submenu_page($file, __('Job Manager', 'jobman'), __('List Jobs', 'jobman'), 'manage_options', 'jobman-list-jobs', 'jobman_list_jobs');
	add_submenu_page($file, __('Job Manager', 'jobman'), __('List Applications', 'jobman'), 'manage_options', 'jobman-list-applications', 'jobman_list_applications');

	// Load our header info
	add_action('admin_head-'.$pageref, 'jobman_admin_header');
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
?>
	<div class="wrap">
		<h2><?php _e('Job Manager: Settings', 'jobman') ?></h2>
<?php
	if(!get_option('pento_consulting')) {
		$widths = array('60%', '39%');
		$functions = array(
						array('jobman_print_settings_box', 'jobman_print_categories_box', 'jobman_print_icons_box'),
						array('jobman_print_donate_box', 'jobman_print_about_box')
					);
		$titles = array(
					array(__('Settings', 'jobman'), __('Categories', 'jobman'), __('Icons', 'jobman')),
					array(__('Donate', 'jobman'), __('About This Plugin', 'jobman'))
				);
	}
	else {
		$widths = array('49%', '49%');
		$functions = array(
						array('jobman_print_settings_box', 'jobman_print_categories_box'),
						array('jobman_print_icons_box')
					);
		$titles = array(
					array(__('Settings', 'jobman'), __('Categories', 'jobman')),
					array(__('Icons', 'jobman'))
				);
	}
	jobman_create_dashboard($widths, $functions, $titles);
}

function jobman_print_settings_box() {
?>
		<form action="" method="post">
		<input type="hidden" name="jobmanconfsubmit" value="1" />
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('URL path', 'jobman') ?></th>
				<td><input class="regular-text code" type="text" name="page-name" value="<?php echo get_option('jobman_page_name') ?>" /></td>
				<td><span class="description"><?php _e('Enter the URL you want the Job Manager to use for displaying the jobs listing.', 'jobman') ?></span></td>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Default email', 'jobman') ?></th>
				<td><input class="regular-text code" type="text" name="default-email" value="<?php echo get_option('jobman_default_email') ?>" /></td>
				<td><span class="description"><?php _e('The email address to notify when a new application is submitted, and there is no email address in the corresponding categories.', 'jobman') ?></span></td>
				</td>
			</tr>
<?php
	if(!get_option('pento_consulting')) {
?>
			<tr>
				<th scope="row"><?php _e('Hide "Powered By" link?', 'resman') ?></th>
				<td><input type="checkbox" value="1" name="promo-link" <?php echo (get_option('jobman_promo_link'))?('checked="checked" '):('') ?>/></td>
				<td><span class="description"><?php _e('If you\'re unable to donate, I would appreciate it if you left this unchecked.', 'resman') ?></span></td>
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
?>
			<tr>
				<td class="post-title page-title column-title"><strong><a href="?page=jobman-jobs-list&amp;jobman-jobid=<?php echo $job['id'] ?>"><?php echo $job['title']?></a></strong>
				<div class="row-actions"><a href="?page=jobman-jobs-list&amp;jobman-jobid=<?php echo $job['id'] ?>">Edit</a> | <a href="#">View</a></div></td>
				<td></td>
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
					<input type="radio" name="jobman-icon" value="<?php echo $icon['id']?>"<?php echo $checked ?> /><img src="<?php echo JOBMAN_URL . '/' . $icon['id'] . '.' . $icon['extension'] ?>"> <?php echo $icon['title'] ?><br/>
<?php
		}
	}

	if(!isset($job['iconid']) || $job['iconid'] == '') {
		$checked = ' checked="checked"';
	}
	else {
		$checked = '';
	}
?>
					<input type="radio" name="jobman-icon"<?php echo $checked ?> /><?php _e('No Icon', 'jobman') ?><br/>
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
?>
	<form action="" method="post">
	<input type="hidden" name="jobman-jobid" value="new" />
	<div class="wrap">
		<h2><?php _e('Job Manager: Applications', 'jobman') ?></h2>
<?php
	$sql = 'SELECT id, label, type FROM ' . $wpdb->prefix . 'jobman_application_fields WHERE listdisplay=1 ORDER BY sortorder ASC';
	$fields = $wpdb->get_results($sql, ARRAY_A);
?>
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
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
<?php
	$sql = 'SELECT a.id AS id, a.jobid AS jobid';
	$join = '';
	if(count($fields > 0)) {
		foreach($fields as $field) {
			$sql .= ', d' . $field['id'] . '.data AS data' . $field['id'];
			$join .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_application_data as d' . $field['id'] . ' ON d' . $field['id'] . '.applicationid=a.id AND d' . $field['id'] . '.fieldid=' . $field['id'];
		}
	}
	$sql .= ' FROM ' . $wpdb->prefix . 'jobman_applications AS a';
	$sql .= $join;
	$sql .= ' ORDER BY a.id;';
	$applications = $wpdb->get_results($sql, ARRAY_A);

	if(count($applications) > 0) {
		foreach($applications as $app) {
?>
			<tr>
<?php
			if($app['jobid'] > 0) {
?>
				<td><strong><a href="?page=jobman-jobs-list&amp;jobman-jobid=<?php echo $app['jobid'] ?>"><?php echo $app['jobid']?></a></strong></td>
<?php
			}
			else {
?>
				<td><?php _e('No job', 'jobman') ?></td>
<?php
			}
?>
				<td></td>
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
	</div>
	</form>
<?php
}

function jobman_conf_updatedb() {
	update_option('jobman_page_name', $_REQUEST['page-name']);
	update_option('jobman_default_email', $_REQUEST['default-email']);

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
								$_REQUEST['jobman-iconid'], stripslashes($_REQUEST['jobman-title']), stripslashes($_REQUEST['jobman-salary']), stripslashes($_REQUEST['jobman-startdate']), stripslashes($_REQUEST['jobman-enddate']), 
								stripslashes($_REQUEST['jobman-location']), stripslashes($_REQUEST['jobman-displaystartdate']), stripslashes($_REQUEST['jobman-displayenddate']), stripslashes($_REQUEST['jobman-abstract']));
	}
	else {
		$sql = $wpdb->prepare('UPDATE ' . $wpdb->prefix . 'jobman_jobs SET iconid=%d, title=%s, salary=%s, startdate=%s, enddate=%s, location=%s, displaystartdate=%s, displayenddate=%s, abstract=%s WHERE id=%d;',
								$_REQUEST['jobman-iconid'], stripslashes($_REQUEST['jobman-title']), stripslashes($_REQUEST['jobman-salary']), stripslashes($_REQUEST['jobman-startdate']), stripslashes($_REQUEST['jobman-enddate']), 
								stripslashes($_REQUEST['jobman-location']), stripslashes($_REQUEST['jobman-displaystartdate']), stripslashes($_REQUEST['jobman-displayenddate']), stripslashes($_REQUEST['jobman-abstract']), $_REQUEST['jobman-jobid']);
	}

	$wpdb->query($sql);
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
				$sql = $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . 'jobman_application_fields(label, type, listdisplay, data, filter, error, sortorder) VALUES(%s, %s, %s, %s, %s, %d);',
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
		<p><?php _e('If this plugin helps you find that perfect new employee, I\'d appreciate it if you shared the love, by way of my Donate or Amazon Wish List links below.', 'resman') ?></p>
		<ul>
			<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=gary%40pento%2enet&item_name=WordPress%20Plugin%20(Job%20Manager)&item_number=Support%20Open%20Source&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8"><?php _e('Donate with PayPal', 'resman') ?></a></li>
			<li><a href="http://www.amazon.com/wishlist/1ORKI9ZG875BL"><?php _e('My Amazon Wish List', 'resman') ?></a></li>
		</ul>
<?php
}

function jobman_print_about_box() {
?>
		<ul>
			<li><a href="http://pento.net/"><?php _e('Gary Pendergast\'s Blog', 'resman') ?></a></li>
			<li><a href="http://twitter.com/garypendergast"><?php _e('Follow me on Twitter!', 'resman') ?></a></li>
			<li><a href="http://pento.net/projects/wordpress-job-manager/"><?php _e('Plugin Homepage', 'resman') ?></a></li>
			<li><a href="http://code.google.com/p/wordpress-job-manager/issues/list"><?php _e('Submit a Bug/Feature Request', 'resman') ?></a></li>
		</ul>
<?php
}
?>