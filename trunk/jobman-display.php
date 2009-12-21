<?php //encoding: utf-8
function jobman_queryvars($qvars) {
	$options = get_option('jobman_options');

	$url = $options['page_name'];
	if(!$url) {
		return;
	}
	$qvars[] = $url;
	$qvars[] = 'data';
	return $qvars;
}

function jobman_add_rewrite_rules($wp_rewrite) {
	$options = get_option('jobman_options');
	
	$url = $options['page_name'];
	if(!$url) {
		return;
	}
	$new_rules = array( 
						"$url/?$" => "index.php?$url=all",
						"$url/([^/]+)/?([^/]+)?/?" => "index.php?$url=" .
						$wp_rewrite->preg_index(1) .
						"&data=" . $wp_rewrite->preg_index(2));

	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

function jobman_flush_rewrite_rules() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules(false);
}


function jobman_display_jobs($posts) {
	global $wp_query;
	$options = get_option('jobman_options');

	$url = $options['page_name'];
	
	if(!isset($wp_query->query_vars[$url])) {
		return $posts;
	}
	
	$func = $wp_query->query_vars[$url];
	$data = '';
	if(array_key_exists('data', $wp_query->query_vars)) {
		$data = $wp_query->query_vars['data'];
	}
	$matches = array();

	switch($func) {
		case 'all':
			$posts = jobman_display_jobs_list('all');
			break;
		case 'view':
			$posts = jobman_display_job($data);
			break;
		case 'apply':
			$jobid = (int) $data;
			if($data == '') {
				$posts = jobman_display_apply(-1);
			}
			else if($jobid > 0) {
				$posts = jobman_display_apply($jobid);
			}
			else {
				$posts = jobman_display_apply(-1, $data);
			}
			break;
		default:
			$posts = jobman_display_jobs_list($func);
	}

	$hidepromo = $options['promo_link'];
	
	if(get_option('pento_consulting')) {
		$hidepromo = true;
	}
	
	if(!$hidepromo) {
		$posts[0]->post_content .= '<p class="jobmanpromo">' . sprintf(__('This job listing was created using <a href="%s" title="%s">Job Manager</a> for WordPress, by <a href="%s">Gary Pendergast</a>.', 'resman'), 'http://pento.net/projects/wordpress-job-manager/', __('WordPress Job Manager', 'resman'), 'http://pento.net') . '</p>';
	}
	

	return $posts;
}

function jobman_display_init() {
	wp_enqueue_script('jquery-ui-datepicker', JOBMAN_URL.'/js/jquery-ui-datepicker.js', array('jquery-ui-core'), JOBMAN_VERSION);
	wp_enqueue_style('jobman-display', JOBMAN_URL.'/css/display.css', false, JOBMAN_VERSION);
}

function jobman_display_template() {
	global $wp_query;
	$options = get_option('jobman_options');

	$url = $options['page_name'];
	
	if(isset($wp_query->query_vars[$url])) {
		include(TEMPLATEPATH . '/page.php');
		exit;
	}
}

function jobman_display_title($title, $sep, $seploc) {
	$wp_query;
	$options = get_option('jobman_options');

	$url = $options['page_name'];
	
	if(!isset($wp_query->query_vars[$url])) {
		return $title;
	}

	$func = $wp_query->query_vars[$url];
	$data = $wp_query->query_vars['data'];
	$matches = array();

	switch($func) {
		case 'view':
			if(preg_match('/^(\d+)-?(.*)?/', $data, $matches)) {
				$title = '';
				$job = get_post($matches[1]);
				$jobmeta = get_post_meta($job->ID);
				
				if(is_array($jobmeta['displayenddate'])) {
					$displayenddate = $jobmeta['displayenddate'][0];
				}
				else {
					$displayenddate = $jobmeta['displayenddate'];
				}
				
				if(strtotime($job->post_date) < time() || $displayenddate == '' || strtotime($displayenddate) > time()) {
					$title = $job->post_title;
				}
				if($title != '') {
					$newtitle = __('Job', 'jobman') . ': ' . $title;
				}
				else {
					$newtitle = __('This job doesn\'t exist', 'jobman');
					add_action('wp_head', 'jobman_display_robots_noindex');
				}
			}
			break;
		case 'apply':
			$newtitle = __('Job Application', 'jobman');
			break;
		case 'all':
			$newtitle = __('Jobs Listing', 'jobman');
			break;
		default:
			$category = get_term_by('slug', $data, 'jobman_category')->name;
			$newtitle = __('Jobs Listing', 'jobman');
			if($category != '') {
				$newtitle .= ': ' . $category;
			}
	}

	if($seploc == 'right') {
		$title = "$newtitle $sep ";
	}
	else {
		$title = " $sep $newtitle";
	}
	
	return $title;
}

function jobman_display_head() {
	global $wp_query;
	$options = get_option('jobman_options');

	$url = $options['page_name'];
	
	if(!isset($wp_query->query_vars[$url])) {
		return;
	}
	
	if(is_feed()) {
		return;
	}
?>
<script type="text/javascript"> 
//<![CDATA[
jQuery(document).ready(function() {
	jQuery(".datepicker").datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true, gotoCurrent: true});
	jQuery("#ui-datepicker-div").css('display', 'none');
});
//]]>
</script> 
<?php
}

function jobman_display_edit_post_link($link) {
	global $wp_query;
	$options = get_option('jobman_options');

	$url = $options['page_name'];
	
	if(!isset($wp_query->query_vars[$url])) {
		return $link;
	}

	$func = $wp_query->query_vars[$url];
	$data = $wp_query->query_vars['data'];
	$matches = array();
	
	if($func == 'view' && is_int($data)) {
		return admin_url('admin.php?page=jobman-jobs-list&amp;jobid=' . $data);
	}

	return admin_url('admin.php?page=jobman-jobs-list');
}

function jobman_display_jobs_list($cat) {
	$options = get_option('jobman_options');

	$page = new stdClass;
	$content = '';
	
	$url = $options['page_name'];
	$list_type = $options['list_type'];
	
	$page->post_title = __('Jobs Listing', 'jobman');
	$page->post_author = 1;
	$page->post_date = time();
	$page->post_type = 'page';
	$page->post_status = 'published';
	
	if($cat != 'all') {
		$category = get_term_by('slug', $cat, 'jobman_category');
		if($category == NULL) {
			$cat = 'all';
		}
	}
	
	if($cat == 'all') {
		$jobs = get_posts('post_type=jobman_job');
	}
	else {
		$jobs = get_posts('post_type=jobman_job&jobman_category='.$cat);
	}

	if(count($jobs) > 0) {
		if($list_type == 'summary') {
			$content .= '<table class="jobs-table">';
			$content .= '<tr><th>' . __('Title', 'jobman') . '</th><th>' . __('Salary', 'jobman') . '</th><th>' . __('Start Date', 'jobman') . '</th><th>' . __('Location', 'jobman') . '</th></tr>';
			foreach($jobs as $job) {
				$jobmeta = get_post_custom($job->ID);
				$jobdata = array();
				foreach($jobmeta as $key => $value) {
					if(is_array($value)) {
						$jobdata[$key] = $value[0];
					}
					else {
						$jobdata[$key] = $value;
					}
				}
				$content .= '<tr><td><a href="' . jobman_url('view', $job->post_name) . '">';
				if($job['iconid']) {
					$content .= '<img src="' . JOBMAN_URL . '/icons/' . $jobdata['iconid'] . '.' . $options['icons'][$jobdata['iconid']]['ext'] . '" title="' . $options['icons'][$jobdata['iconid']]['title'] . '" /><br/>';
				}
				$content .= $job->post_title . '</a></td>';
				$content .= '<td>' . $jobdata['salary'] . '</td>';
				if($jobdata['startdate'] == '' || strtotime($jobdata['startdate']) < time()) {
					$asap = __('ASAP', 'jobman');
				}
				else {
					$asap = $jobdata['startdate'];
				}
				$content .= '<td>' . $asap . '</td>';
				$content .= '<td>' . $jobdata['location'] . '</td>';
				$content .= '<td class="jobs-moreinfo"><a href="' . jobman_url('view', $job->post_name) . '">' . __('More Info', 'jobman') . '</a></td></tr>';
			}
			$content .= '</table>';
		}
		else {
			foreach($jobs as $job) {
				$job_html = jobman_display_job($job);
				$content .= $job_html[0]->post_content . '<br/><br/>';
			}
		}
	}
	else {
		$content .= '<p>';
		if($cat == 'all') {
			$content .= sprintf(__('We currently don\'t have any jobs available. Please check back regularly, as we frequently post new jobs. In the mean time, you can also <a href="%s">send through your résumé</a>, which we\'ll keep on file.', 'jobman'), jobman_url('apply'));
		}
		else {
			$content .= sprintf(__('We currently don\'t have any jobs available in this area. Please check back regularly, as we frequently post new jobs. In the mean time, you can also <a href="%s">send through your résumé</a>, which we\'ll keep on file, and you can check out the <a href="%s">jobs we have available in other areas</a>.', 'jobman'), jobman_url('apply', get_term($cat, 'jobman_category')->slug), jobman_url());
		}
	}
	
	$page->post_content = $content;
		
	return array($page);
}

function jobman_display_job($job) {
	$options = get_option('jobman_options');

	$url = $options['page_name'];
	
	$page = new stdClass;
	$content = '';
	
	/*$sql = $wpdb->prepare('SELECT id, title, salary, startdate, startdate <= NOW() AS asap, enddate, location, abstract FROM ' . $wpdb->prefix . 'jobman_jobs WHERE id=%d AND (displaystartdate <= NOW() OR displaystartdate = "") AND (displayenddate >= NOW() OR displayenddate = "");', $jobid);
	$data = $wpdb->get_results($sql, ARRAY_A);*/
	if(is_string($job)) {
		$data = get_posts('post_type=jobman_job&name='.$job);
		if(count($data) > 0) {
			$job = $data[0];
		}
		else {
			$job = NULL;
		}
	}
	
	if($job == NULL) {
		$page->post_title = __('This job doesn\'t exist', 'jobman');
		$page->post_author = 1;
		$page->post_date = time();
		$page->post_type = 'page';
		$page->post_status = 'published';

		$content = sprintf(__('Perhaps you followed an out-of-date link? Please check out the <a href="%s">jobs we have currently available</a>.', 'jobman'), jobman_url());;
		
		$page->post_content = $content;
			
		return array($page);
	}

	$jobmeta = get_post_custom($job->ID);
	$jobdata = array();
	foreach($jobmeta as $key => $value) {
		if(is_array($value)) {
			$jobdata[$key] = $value[0];
		}
		else {
			$jobdata[$key] = $value;
		}
	}

	$page->post_title = __('Job', 'jobman') . ': ' . $job->post_title;
	
	$page->post_author = $job->post_author;
	$page->post_date = $job->post_date;
	$page->post_type = 'page';
	$page->post_status = 'published';

	$categories = wp_get_object_terms($job->ID, 'jobman_category');
	
	$content .= '<table class="job-table">';
	$content .= '<tr><th scope="row">' . __('Title', 'jobman') . '</th><td>' . $job->post_title . '</td></tr>';
	if(count($categories) > 0) {
		$content .= '<tr><th scope="row">' . __('Categories', 'jobman') . '</th><td>';
		$cats = array();
		$ii = 1;
		foreach($categories as $cat) {
			$content .= '<a href="'. jobman_url($cat->slug) . '" title="' . sprintf(__('Jobs for %s', 'jobman'), $cat->name) . '">' . $cat->name . '</a>';
			if($ii < count($categories)) {
				$content .= ', ';
			}
		}
	}
	$content .= '<tr><th scope="row">' . __('Salary', 'jobman') . '</th><td>' . $jobdata['salary'] . '</td></tr>';
	if($jobdata['startdate'] == '' || strtotime($jobdata['startdate']) < time()) {
		$asap = __('ASAP', 'jobman');
	}
	else {
		$asap = $jobdata['startdate'];
	}
	$content .= '<tr><th scope="row">' . __('Start Date', 'jobman') . '</th><td>' . $asap . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('End Date', 'jobman') . '</th><td>' . (($jobdata['enddate'] == '')?(__('Ongoing', 'jobman')):($jobdata['enddate'])) . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('Location', 'jobman') . '</th><td>' . $jobdata['location'] . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('Information', 'jobman') . '</th><td>' . jobman_format_abstract($job->post_content) . '</td></tr>';
	$content .= '<tr><td></td><td class="jobs-applynow"><a href="'. jobman_url('apply', $job->ID) . '">' . __('Apply Now!', 'jobman') . '</td></tr>';
	$content .= '</table>';
	
	$page->post_content = $content;
		
	return array($page);
}

function jobman_display_apply($jobid, $cat = NULL) {
	$options = get_option('jobman_options');

	$url = $options['page_name'];
	
	$page = new stdClass;
	$content = '';
	
	$page->post_author = 1;
	$page->post_date = time();
	$page->post_type = 'page';
	$page->post_status = 'published';

	if(isset($_REQUEST['jobman-apply'])) {
		$err = jobman_store_application($jobid, $cat);
		switch($err) {
			case -1:
				// No error, stored properly
				$msg = __('Thank you for your application! We\'ll check it out, and get back to you soon!', 'jobman');
				break;
			default:
				// Failed filter rules
				$msg = $options['fields'][$err]['error'];
				if($msg == NULL || $msg == '') {
					$msg = __('Thank you for your application. While your application doesn\'t fit our current requirements, please contact us directly to see if we have other positions available.', 'jobman');
				}
				break;
		}
		
		$page->post_title = __('Job Application', 'jobman');
		$page->post_content .= '<div class="jobman-message">' . $msg . '</div>';
		
		return array($page);
	}
	
	$job = get_post($jobid);
	
	$cat_arr = array();

	if($job != NULL) {
		$page->post_title = __('Job Application', 'jobman') . ': ' . $job->post_title;
		$foundjob = true;
		$jobid = $job->ID;
		
		$categories = wp_get_object_terms($job->ID, 'jobman_category');
		if(count($categories) > 0) {
			foreach($categories as $cat) {
				$cat_arr[] = $cat->term_id;
			}
		}
	}
	else {
		$page->post_title = __('Job Application', 'jobman');
		$foundjob = false;
		$jobid = -1;
		if($cat != NULL) {
			$cat_arr[] = get_term_by('slug', $cat, 'jobman_category')->term_id;
		}
	}
	
	$content .= '<form action="" enctype="multipart/form-data" method="post">';
	$content .= '<input type="hidden" name="jobman-apply" value="1">';
	$content .= '<input type="hidden" name="jobman-jobid" value="' . $jobid . '">';
	$content .= '<input type="hidden" name="jobman-categoryid" value="' . implode(',', $cat_arr) . '">';
	
	if($foundjob) {
		$content .= '<p>' . __('Title', 'jobman') . ': <a href="'. jobman_url('view', $job->post_name) . '">' . $job->post_title . '</a></p>';
	}

	$fields = $options['fields'];

	$start = true;
	
	if(count($fields) > 0 ) {
		foreach($fields as $id => $field) {
			if(count($field['categories']) > 0) {
				// If there are cats defined for this field, check that either the job has one of those categories, or we're submitting to that category
				if(count(array_intersect($field['categories'], $cat_arr)) <= 0) {
					continue;
				}
			}
			
			if($start && $field['type'] != 'heading') {
				$content .= '<table class="job-apply-table">';
			}
			switch($field['type']) {
				case 'text':
					if($field['label'] != '') {
						$content .= '<tr><th scope="row">' . $field['label'] . '</th>';
					}
					else {
						$content .= '<tr><td class="th"></td>';
					}
					$content .= '<td><input type="text" name="jobman-field-' . $id . '" value="' . $field['data'] . '" /></td></tr>';
					break;
				case 'radio':
					if($field['label'] != '') {
						$content .= '<tr><th scope="row">' . $field['label'] . '</th><td>';
					}
					else {
						$content .= '<tr><td class="th"></td><td>';
					}
					$values = split("\n", $field['data']);
					foreach($values as $value) {
						$content .= '<input type="radio" name="jobman-field-' . $id . '" value="' . trim($value) . '" /> ' . $value;
					}
					$content .= '</td></tr>';
					break;
				case 'checkbox':
					if($field['label'] != '') {
						$content .= '<tr><th scope="row">' . $field['label'] . '</th><td>';
					}
					else {
						$content .= '<tr><td class="th"></td><td>';
					}
					$values = split("\n", $field['data']);
					foreach($values as $value) {
						$content .= '<input type="checkbox" name="jobman-field-' . $id . '[]" value="' . trim($value) . '" /> ' . $value;
					}
					$content .= '</td></tr>';
					break;
				case 'textarea':
					if($field['label'] != '') {
						$content .= '<tr><th scope="row">' . $field['label'] . '</th>';
					}
					else {
						$content .= '<tr><td class="th"></td>';
					}
					$content .= '<td><textarea name="jobman-field-' . $id . '">' . $field['data'] . '</textarea></td></tr>';
					break;
				case 'date':
					if($field['label'] != '') {
						$content .= '<tr><th scope="row">' . $field['label'] . '</th>';
					}
					else {
						$content .= '<tr><td class="th"></td>';
					}
					$content .= '<td><input type="text" class="datepicker" name="jobman-field-' . $id . '" value="' . $field['data'] . '" /></td></tr>';
					break;
				case 'file':
					if($field['label'] != '') {
						$content .= '<tr><th scope="row">' . $field['label'] . '</th>';
					}
					else {
						$content .= '<tr><td class="th"></td>';
					}
					$content .= '<td><input type="file" name="jobman-field-' . $id . '" /></td></tr>';
					break;
				case 'heading':
					if(!$start) {
						$content .= '</table>';
					}
					$content .= '<h3>' . $field['label'] . '</h3>';
					$content .= '<table class="job-apply-table">';
					break;
				case 'blank':
					$content .= '<tr><td colspan="2">&nbsp;</td></tr>';
					break;
			}
			$start = false;
		}
	}
	
	$content .= '<tr><td colspan="2">&nbsp;</td></tr>';
	$content .= '<tr><td colspan="2" class="submit"><input type="submit" name="submit"  class="button-primary" value="' . __('Submit Your Application', 'jobman') . '" /></td></tr>';
	$content .= '</table>';

	$page->post_content = $content;
		
	return array($page);
}

function jobman_display_robots_noindex() {
	if(is_feed()) {
		return;
	}
?>
	<!-- Generated by Job Manager plugin -->
	<meta name="robots" content="noindex" />
<?php
}

function jobman_format_abstract($text) {
	$textsplit = preg_split("[\n]", $text);
	
	$listlevel = 0;
	$starsmatch = array();
	foreach($textsplit as $key => $line) {
		preg_match('/^[*]*/', $line, $starsmatch);
		$stars = strlen($starsmatch[0]);
		
		$line = preg_replace('/^[*]*/', '', $line);
		
		$listhtml_start = '';
		$listhtml_end = '';
		while($stars > $listlevel) {
			$listhtml_start .= '<ul>';
			$listlevel++;
		}
		while($stars < $listlevel) {
			$listhtml_start .= '</ul>';
			$listlevel--;
		}
		if($listlevel > 0) {
			$listhtml_start .= '<li>';
			$listhtml_end = '</li>';
		}
		
		$textsplit[$key] = $listhtml_start . $line . $listhtml_end;
	}

	$text = implode("\n", $textsplit);

	while($listlevel > 0) {
		$text .= '</ul>';
		$listlevel--;
	}
	
	// Bold
	$text = preg_replace("/'''(.*?)'''/", '<strong>$1</strong>', $text);
	
	// Italic
	$text = preg_replace("/''(.*?)''/", '<em>$1</em>', $text);

	$text = '<p>' . $text . '</p>';
	return $text;
}

function jobman_store_application($jobid, $cat) {
	$filter_err = jobman_check_filters($jobid, $cat);
	if($filter_err != -1) {
		// Failed filter rules
		return $filter_err;
	}
	$options = get_option('jobman_options');
	
	$parent = $options['main_page'];
	
	$job = NULL;
	if($jobid != -1) {
		$job = get_post($jobid);
		if($job != NULL) {
			$parent = $job->ID;
		}
	}
	
	if($job != NULL && $cat != NULL) {
		$cat = get_term_by('slug', $cat, 'jobman_category');
		if($cat != NULL) {
			$data = get_posts('post_type=jobman_joblist&meta_key=_cat&meta_value='.$cat->term_id);
			if(count($data) > 0) {
				$parent = $data[0]->ID;
			}
		}
	}

	$fields = $options['fields'];
	
	$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'jobman_app',
				'post_content' => '',
				'post_title' => __('Application', 'jobman'),
				'post_parent' => $parent);

	$appid = wp_insert_post($page);

	// Add the categories to the page
	if($cat != NULL) {
		if(is_term($cat->term_id, 'jobman_category')) {
			wp_set_object_terms($appid, $cat->term_id, 'jobman_category', true);
		}
	}
	if($job != NULL) {
		// Get parent (job) categories, and apply them to application
		$parentcats = wp_get_object_terms($job->ID, 'jobman_category');
		foreach($parentcats as $pcat) {
			if(is_term($pcat->term_id, 'jobman_category')) {
				wp_set_object_terms($appid, $pcat->term_id, 'jobman_category', true);
			}
		}
	}
	
	if(count($fields) > 0) {
		foreach($fields as $id => $field) {
			if($field['type'] != 'file' && (!isset($_REQUEST['jobman-field-'.$id]) || $_REQUEST['jobman-field-'.$id] == '')) {
				continue;
			}
			
			if($field['type'] == 'file' && !isset($_FILES['jobman-field-'.$id])) {
				continue;
			}
			
			switch($field['type']) {
				case 'file':
					$matches = array();
					preg_match('/.*\.(.+)$/', $_FILES['jobman-field-'.$id]['name'], $matches);
					$ext = $matches[1];
					if(is_uploaded_file($_FILES['jobman-field-'.$id]['tmp_name'])) {
						move_uploaded_file($_FILES['jobman-field-'.$id]['tmp_name'], WP_PLUGIN_DIR . '/' . JOBMAN_FOLDER . '/uploads/' . $appid . '-' . $id . '.' . $ext);
						$data = $appid . '-' . $id . '.' . $ext;
					}
					else {
						$data = '';
					}
					break;
				case 'checkbox':
					$data = implode(', ', $_REQUEST['jobman-field-'.$id]);
					break;
				default:
					$data = $_REQUEST['jobman-field-'.$id];
			}
			
			add_post_meta($appid, 'data' . $id, $data, true);
		}
	}
	
	jobman_email_application($appid);

	// No error
	return -1;
}

function jobman_check_filters($jobid, $cat) {
	$options = get_option('jobman_options');
	
	$fields = $options['fields'];
	
	$matches = array();
	if(count($fields) > 0) {
		foreach($fields as $id => $field) {
			if($field['filter'] == '') {
				// No filter for this field
				continue;
			}
			
			$used_eq = false;
			$eqflag = false;
			
			$data = $_REQUEST['jobman-field-'.$id];
			if($field['type'] != 'checkbox') {
				$data = trim($data);
			}
			else if(!is_array($data)) {
				$data = array();
			}
			$filters = split("\n", $field['filter']);
			
			foreach($filters as $filter) {
				$filter = trim($filter);
				
				// Date
				if($field['type'] == 'date') {
					$data = strtotime($data);

					// [<>][+-]P(\d+Y)?(\d+M)?(\d+D)?
					if(preg_match('/^([<>])([+-])P(\d+Y)?(\d+M)?(\d+D)?$/', $filter, $matches)) {
						$intervalstr = $matches[2];
						for($ii = 3; $ii < count($matches); $ii++) {
							$interval = array();
							preg_match('/(\d+)([YMD])/', $matches[$ii], $interval);
							switch($interval[2]) {
								case 'Y':
									$intervalstr .= $interval[1] . ' years ';
									break;
								case 'M':
									$intervalstr .= $interval[1] . ' months ';
									break;
								case 'D':
									$intervalstr .= $interval[1] . ' days ';
									break;
							}
						}
						
						$cmp = strtotime($intervalstr);

						switch($matches[1]) {
							case '<':
								if($cmp > $data) {
									return $field['id'];
								}
								break;
							case '>':
								if($cmp < $data) {
									return $field['id'];
								}
								break;
						}
						
						break;
					}
				}

				preg_match('/^([<>]=?|[!]|)(.+)/', $filter, $matches);
				if($field['type'] == 'date') {
					$fdata = strtotime($matches[2]);
				}
				else {
					$fdata = $matches[2];
				}
				
				if($field['type'] != 'checkbox') {
					switch($matches[1]) {
						case '<=':
							if($data > $fdata) {
								return $field['id'];
							}
							break;
						case '>=':
							if($data > $fdata) {
								return $field['id'];
							}
							break;
						case '<':
							if($data >= $fdata) {
								return $field['id'];
							}
							break;
						case '>':
							if($data <= $fdata) {
								return $field['id'];
							}
							break;
						case '!':
							if($data == $fdata) {
								return $field['id'];
							}
							break;
						default:
							$used_eq = true;
							if($data == $fdata) {
								$eqflag = true;
								break 2;
							}
							break;
					}
				}
				else {
					switch($matches[1]) {
						case '!':
							if(in_array($fdata, $data)) {
								return $field['id'];
							}
							break;
						default:
							$used_eq = true;
							if(in_array($fdata, $data)) {
								$eqflag = true;
								break 2;
							}
							break;
					}
				}
			}
			
			if($used_eq && !$eqflag) {
				return $field['id'];
			}
			$used_eq = false;
			$eqflag = false;
		}
	}

	return -1;
}

function jobman_email_application($appid) {
	$options = get_option('jobman_options');

	$app = get_post($appid);
	if($app == NULL) {
		return;
	}
	
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

	$categories = wp_get_object_terms($appid, 'jobman_category');
	
	$to = '';
	if(count($categories) > 0) {
		$ii = 1;
		foreach($categories as $cat) {
			$to .= $cat->description;
			if($ii < count($categories)) {
				$to .= ', ';
			}
		}
	} else {
		$to = $options['default_email'];
	}
	
	if($to == '') {
		return;
	}
	
	$fromid = $options['application_email_from'];
	if($fromid == '') {
		$from = $options['default_email'];
	}
	else {
		$from = $appdata['data'.$fromid];
	}
	
	if($from == '') {
		$from = get_option('admin_email');
	}
	
	$subject = $options['application_email_subject_text'];
	if($subject != '') {
		$subject .= ' ';
	}

	$fids = $options['application_email_subject_fields'];

	if(count($fids) > 0) {
		foreach($fids as $fid) {
			if($appdata['data'.$fid] != '') {
				$subject .= $appdata['data'.$fid] . ' ';
			}
		}
	}
	
	$msg = '';
	
	$url = $options['page_name'];
	
	$msg .= __('Application Link', 'jobman') . ': ' . admin_url('admin.php?page=jobman-list-applications&amp;appid=' . $appid) . PHP_EOL;

	$parent = get_post($app->post_parent);
	if($parent != NULL && $parent->post_type == 'jobman_job') {
		$msg .= __('Job', 'jobman') . ': ' . $parent->ID . ' - ' . $parent->post_title . PHP_EOL;
		$msg .= jobman_url('view', $parent->post_name) . PHP_EOL;
	}
	
	$msg .= __('Timestamp', 'jobman') . ': ' . $app->post_date . PHP_EOL . PHP_EOL;
	
	$fields = $options['fields'];
	
	if(count($fields) > 0) {
		foreach($fields as $id => $field) {
			if(!array_key_exists('data'.$id, $appdata) || $appdata['data'.$id] == '') {
				continue;
			}
			switch($field['type']) {
				case 'text':
				case 'radio':
				case 'checkbox':
				case 'date':
					$msg .= $field['label'] . ': ' . $appdata['data'.$id] . PHP_EOL;
					break;
				case 'textarea':
					$msg .= $field['label'] . ':' . PHP_EOL . $appdata['data'.$id] . PHP_EOL;
					break;
				case 'file':
					$msg .= $field['label'] . ': ' . JOBMAN_URL . '/uploads/' . $appdata['data'.$id] . PHP_EOL;
					break;
			}
		}
	}

	$header = "From: $from" . PHP_EOL;
	$header .= "Reply-To: $from" . PHP_EOL;
	$header .= "Return-Path: $from" . PHP_EOL;
	$header .= 'Content-type: text/plain; charset='. get_option('blog_charset') . PHP_EOL;
	
	wp_mail($to, $subject, $msg, $header);
}

?>