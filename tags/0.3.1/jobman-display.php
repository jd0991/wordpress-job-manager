<?php //encoding: utf-8
function jobman_queryvars($qvars) {
	$url = get_option('jobman_page_name');
	if(!$url) {
		return;
	}
	$qvars[] = $url;
	$qvars[] = 'data';
	return $qvars;
}

function jobman_add_rewrite_rules($wp_rewrite) {
	$url = get_option('jobman_page_name');
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
	$wp_rewrite->flush_rules();
}


function jobman_display_jobs($posts) {
	global $wp_query;

	$url = get_option('jobman_page_name');
	
	if(!isset($wp_query->query_vars[$url])) {
		return $posts;
	}
	
	$func = $wp_query->query_vars[$url];
	$data = $wp_query->query_vars['data'];
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

	$hidepromo = get_option('jobman_promo_link');
	
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
	
	$url = get_option('jobman_page_name');
	
	if(isset($wp_query->query_vars[$url])) {
		include(TEMPLATEPATH . '/page.php');
		exit;
	}
}

function jobman_display_title($title, $sep, $seploc) {
	global $wpdb, $wp_query;
	
	$url = get_option('jobman_page_name');
	
	if(!isset($wp_query->query_vars[$url])) {
		return $title;
	}

	$func = $wp_query->query_vars[$url];
	$data = $wp_query->query_vars['data'];
	$matches = array();

	switch($func) {
		case 'view':
			if(preg_match('/^(\d+)-?(.*)?/', $data, $matches)) {
				$title = $wpdb->get_var($wpdb->prepare('SELECT title FROM ' . $wpdb->prefix . 'jobman_jobs WHERE id=%d AND (displaystartdate <= NOW() OR displaystartdate = NULL) AND (displayenddate >= NOW() OR displayenddate = NULL);', $matches[1]));
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
			$category = $wpdb->get_var($wpdb->prepare('SELECT title FROM ' . $wpdb->prefix . 'jobman_categories WHERE slug=%s', $data));
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

	$url = get_option('jobman_page_name');
	
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
	
	$url = get_option('jobman_page_name');
	
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
	global $wpdb;
	
	$page = new stdClass;
	$content = '';
	
	$url = get_option('jobman_page_name');
	$list_type = get_option('jobman_list_type');
	
	$page->post_title = __('Jobs Listing', 'jobman');
	
	$sql = 'SELECT j.id AS id, j.title AS title, j.iconid AS iconid, i.title AS icontitle, i.extension as iconext, j.salary AS salary, j.startdate AS startdate, j.startdate <= NOW() AS asap, location FROM ' . $wpdb->prefix . 'jobman_jobs AS j LEFT JOIN ' . $wpdb->prefix . 'jobman_icons AS i ON i.id=j.iconid WHERE (j.displaystartdate <= NOW() OR j.displaystartdate = "") AND (j.displayenddate >= NOW() OR j.displayenddate = "") ORDER BY j.startdate ASC, j.enddate ASC';
	if($cat != 'all') {
		$category = $wpdb->get_var($wpdb->prepare('SELECT title FROM ' . $wpdb->prefix . 'jobman_categories WHERE slug=%s', $cat));
		if($category != '') {
			$page->post_title .= ': ' . $category;
			$sql = 'SELECT j.id AS id, j.title AS title, j.iconid AS iconid, i.title AS icontitle, i.extension as iconext, j.salary AS salary, j.startdate AS startdate, j.startdate <= NOW() AS asap, location';
			$sql .= ' FROM ' . $wpdb->prefix . 'jobman_jobs AS j LEFT JOIN ' . $wpdb->prefix . 'jobman_icons AS i ON i.id=j.iconid';
			$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_job_category AS jc ON jc.jobid=j.id';
			$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_categories AS c ON c.id = jc.categoryid';
			$sql .= $wpdb->prepare(' WHERE (j.displaystartdate <= NOW() OR j.displaystartdate = NULL) AND (j.displayenddate >= NOW() OR j.displayenddate = NULL) AND c.slug=%s', $cat);
			$sql .= ' ORDER BY j.startdate ASC, j.enddate ASC';
		}
		else {
			$cat = 'all';
		}
	}
	
	$jobs = $wpdb->get_results($sql, ARRAY_A);

	if(count($jobs) > 0) {
		if($list_type == 'summary') {
			$content .= '<table class="jobs-table">';
			$content .= '<tr><th>' . __('Title', 'jobman') . '</th><th>' . __('Salary', 'jobman') . '</th><th>' . __('Start Date', 'jobman') . '</th><th>' . __('Location', 'jobman') . '</th></tr>';
			foreach($jobs as $job) {
				$content .= '<tr><td><a href="' . jobman_url('view', $job['id'] . '-' . strtolower(str_replace(' ', '-', $job['title']))) . '">';
				if($job['iconid']) {
					$content .= '<img src="' . JOBMAN_URL . '/icons/' . $job['iconid'] . '.' . $job['iconext'] . '" title="' . $job['icontitle'] . '" /><br/>';
				}
				$content .= $job['title'] . '</a></td>';
				$content .= '<td>' . $job['salary'] . '</td>';
				$content .= '<td>' . (($job['asap'])?(__('ASAP', 'jobman')):($job['startdate'])) . '</td>';
				$content .= '<td>' . $job['location'] . '</td>';
				$content .= '<td class="jobs-moreinfo"><a href="' . jobman_url('view', $job['id'] . '-' . strtolower(str_replace(' ', '-', $job['title']))) . '">' . __('More Info', 'jobman') . '</a></td></tr>';
			}
			$content .= '</table>';
		}
		else {
			foreach($jobs as $job) {
				$job_html = jobman_display_job($job['id']);
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
			$content .= sprintf(__('We currently don\'t have any jobs available in this area. Please check back regularly, as we frequently post new jobs. In the mean time, you can also <a href="%s">send through your résumé</a>, which we\'ll keep on file, and you can check out the <a href="%s">jobs we have available in other areas</a>.', 'jobman'), jobman_url('apply'), jobman_url());
		}
	}
	
	$page->post_content = $content;
		
	return array($page);
}

function jobman_display_job($jobid) {
	global $wpdb;
	
	$url = get_option('jobman_page_name');
	
	$page = new stdClass;
	$content = '';
	
	$sql = $wpdb->prepare('SELECT id, title, salary, startdate, startdate <= NOW() AS asap, enddate, location, abstract FROM ' . $wpdb->prefix . 'jobman_jobs WHERE id=%d AND (displaystartdate <= NOW() OR displaystartdate = "") AND (displayenddate >= NOW() OR displayenddate = "");', $jobid);
	$data = $wpdb->get_results($sql, ARRAY_A);
	
	if(count($data) <= 0) {
		$page->post_title = __('This job doesn\'t exist', 'jobman');

		$content = sprintf(__('Perhaps you followed an out-of-date link? Please check out the <a href="%s">jobs we have currently available</a>.', 'jobman'), jobman_url());;
		
		$page->post_content = $content;
			
		return array($page);
	}
	
	$job = $data[0];
	
	$page->post_title = __('Job', 'jobman') . ': ' . $job['title'];
	
	$sql = $wpdb->prepare('SELECT c.title AS title, c.slug AS slug FROM ' . $wpdb->prefix . 'jobman_categories AS c LEFT JOIN ' . $wpdb->prefix . 'jobman_job_category AS jc ON jc.categoryid=c.id WHERE jc.jobid=%d;', $job['id']);
	$categories = $wpdb->get_results($sql, ARRAY_A);
	
	$content .= '<table class="job-table">';
	$content .= '<tr><th scope="row">' . __('Title', 'jobman') . '</th><td>' . $job['title'] . '</td></tr>';
	if(count($categories) > 0) {
		$content .= '<tr><th scope="row">' . __('Categories', 'jobman') . '</th><td>';
		$cats = array();
		$ii = 1;
		foreach($categories as $cat) {
			$slug = $cat['slug'];
			$content .= '<a href="'. jobman_url($slug) . '" title="' . sprintf(__('Jobs for %s', 'jobman'), $cat['title']) . '">' . $cat['title'] . '</a>';
			if($ii < count($categories)) {
				$content .= ', ';
			}
		}
	}
	$content .= '<tr><th scope="row">' . __('Salary', 'jobman') . '</th><td>' . $job['salary'] . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('Start Date', 'jobman') . '</th><td>' . (($job['asap'])?(__('ASAP', 'jobman')):($job['startdate'])) . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('End Date', 'jobman') . '</th><td>' . (($job['enddate'] == '')?(__('Ongoing', 'jobman')):($job['enddate'])) . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('Location', 'jobman') . '</th><td>' . $job['location'] . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('Information', 'jobman') . '</th><td>' . jobman_format_abstract($job['abstract']) . '</td></tr>';
	$content .= '<tr><td></td><td class="jobs-applynow"><a href="'. jobman_url('apply', $job['id']) . '">' . __('Apply Now!', 'jobman') . '</td></tr>';
	$content .= '</table>';
	
	$page->post_content = $content;
		
	return array($page);
}

function jobman_display_apply($jobid, $cat = NULL) {
	global $wpdb;

	$url = get_option('jobman_page_name');
	
	$page = new stdClass;
	$content = '';
	
	if(isset($_REQUEST['jobman-apply'])) {
		$err = jobman_store_application($jobid, $cat);
		switch($err) {
			case -1:
				// No error, stored properly
				$msg = __('Thank you for your application! We\'ll check it out, and get back to you soon!', 'jobman');
				break;
			default:
				// Failed filter rules
				$msg = $wpdb->get_var($wpdb->prepare('SELECT error FROM ' . $wpdb->prefix . 'jobman_application_fields WHERE id=%d', $err));
				if($msg == NULL) {
					$msg = __('Thank you for your application. While your application doesn\'t fit our current requirements, please contact us directly to see if we have other positions available.', 'jobman');
				}
				break;
		}
		
		$page->post_title = __('Job Application', 'jobman');
		$page->post_content .= '<div class="jobman-message">' . $msg . '</div>';
		
		return array($page);
	}
	
	$sql = $wpdb->prepare('SELECT id, title FROM ' . $wpdb->prefix . 'jobman_jobs WHERE id=%d AND (displaystartdate <= NOW() OR displaystartdate = "") AND (displayenddate >= NOW() OR displayenddate = "");', $jobid);
	$data = $wpdb->get_results($sql, ARRAY_A);
	
	if(count($data) > 0) {
		$job = $data[0];
		$page->post_title = __('Job Application', 'jobman') . ': ' . $job['title'];
		$foundjob = true;
		$jobid = $job['id'];
		
		$sql = 'SELECT categoryid FROM ' . $wpdb->prefix . 'jobman_job_category WHERE jobid=' . $jobid;
		$catids = $wpdb->get_results($sql, ARRAY_A);
		$categoryid = '';
		$jj = 1;
		if(count($catids) > 0) {
			foreach($catids as $catid) {
				$categoryid .= $catid['categoryid'];
				if($jj < count($catids)) {
					$categoryid .= ',';
				}
			}
		}
	}
	else {
		$page->post_title = __('Job Application', 'jobman');
		$foundjob = false;
		$jobid = -1;
		$categoryid = $wpdb->get_var($wpdb->prepare('SELECT id FROM ' . $wpdb->prefix . 'jobman_categories WHERE slug=%s', $cat));
	}
	
	$content .= '<form action="" enctype="multipart/form-data" method="post">';
	$content .= '<input type="hidden" name="jobman-apply" value="1">';
	$content .= '<input type="hidden" name="jobman-jobid" value="' . $jobid . '">';
	$content .= '<input type="hidden" name="jobman-categoryid" value="' . $categoryid . '">';
	
	if($foundjob) {
		$content .= __('Title', 'jobman') . ': <a href="'. jobman_url('view', $job['id'] . '-' . strtolower(str_replace(' ', '-', $job['title']))) . '">' . $job['title'] . '</a>';
	}

	if($jobid != -1) {
		$sql = 'SELECT af.id AS id, af.label AS label, af.type AS type, af.data AS data FROM ' . $wpdb->prefix . 'jobman_application_fields AS af';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_application_field_categories AS afc ON afc.afid=af.id';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_jobs AS j ON j.id=' . $jobid;
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_job_category AS jc ON jc.jobid=j.id AND jc.categoryid=afc.categoryid';
		$sql .= ' WHERE afc.categoryid IS NULL OR jc.categoryid=afc.categoryid ORDER BY sortorder ASC';
	}
	else {
		$sql = 'SELECT af.id AS id, af.label AS label, af.type AS type, af.data AS data FROM ' . $wpdb->prefix . 'jobman_application_fields AS af';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_application_field_categories AS afc ON afc.afid=af.id';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_categories AS c ON c.slug=%s';
		$sql .= ' WHERE afc.categoryid IS NULL OR c.id=afc.categoryid ORDER BY sortorder ASC';
		$sql = $wpdb->prepare($sql, $cat);
	}
	$fields = $wpdb->get_results($sql, ARRAY_A);
	
	$start = true;
	
	if(count($fields) > 0 ) {
		foreach($fields as $field) {
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
					$content .= '<td><input type="text" name="jobman-field-' . $field['id'] . '" value="' . $field['data'] . '" /></td></tr>';
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
						$content .= '<input type="radio" name="jobman-field-' . $field['id'] . '" value="' . trim($value) . '" /> ' . $value;
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
						$content .= '<input type="checkbox" name="jobman-field-' . $field['id'] . '[]" value="' . trim($value) . '" /> ' . $value;
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
					$content .= '<td><textarea name="jobman-field-' . $field['id'] . '">' . $field['data'] . '</textarea></td></tr>';
					break;
				case 'date':
					if($field['label'] != '') {
						$content .= '<tr><th scope="row">' . $field['label'] . '</th>';
					}
					else {
						$content .= '<tr><td class="th"></td>';
					}
					$content .= '<td><input type="text" class="datepicker" name="jobman-field-' . $field['id'] . '" value="' . $field['data'] . '" /></td></tr>';
					break;
				case 'file':
					if($field['label'] != '') {
						$content .= '<tr><th scope="row">' . $field['label'] . '</th>';
					}
					else {
						$content .= '<tr><td class="th"></td>';
					}
					$content .= '<td><input type="file" name="jobman-field-' . $field['id'] . '" /></td></tr>';
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
	<!-- Generated by Jobs Manager plugin -->
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
	global $wpdb;
	$filter_err = jobman_check_filters($jobid, $cat);
	if($filter_err != -1) {
		// Failed filter rules
		return $filter_err;
	}

	if($jobid != -1) {
		$sql = 'SELECT af.id AS id, af.type AS type FROM ' . $wpdb->prefix . 'jobman_application_fields AS af';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_application_field_categories AS afc ON afc.afid=af.id';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_jobs AS j ON j.id=%d';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_job_category AS jc ON jc.jobid=j.id AND jc.categoryid=afc.categoryid';
		$sql .= ' WHERE afc.categoryid IS NULL OR jc.categoryid=afc.categoryid ORDER BY sortorder ASC';
		$sql = $wpdb->prepare($sql, $jobid);
	}
	else {
		$sql = 'SELECT af.id AS id, af.type AS type FROM ' . $wpdb->prefix . 'jobman_application_fields AS af';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_application_field_categories AS afc ON afc.afid=af.id';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_categories AS c ON c.slug=%s';
		$sql .= ' WHERE afc.categoryid IS NULL OR c.id=afc.categoryid ORDER BY sortorder ASC';
		$sql = $wpdb->prepare($sql, $cat);
	}
	$fields = $wpdb->get_results($sql, ARRAY_A);
	
	if($jobid != -1) {
		$sql = $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . 'jobman_applications(jobid, submitted) VALUES(%d, NOW())', $jobid);
	}
	else {
		$sql = 'INSERT INTO ' . $wpdb->prefix . 'jobman_applications(submitted) VALUES(NOW());';
	}
	$wpdb->query($sql);
	$appid = $wpdb->insert_id;
	
	$categories = split(',', $_REQUEST['jobman-categoryid']);
	if(count($categories) > 0 && $_REQUEST['jobman-categoryid'] != '') {
		foreach($categories as $cat) {
			$sql = $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . 'jobman_application_categories(applicationid, categoryid) VALUES(%d, %d);', $appid, $cat);
			$wpdb->query($sql);
		}
	}
	
	if(count($fields) > 0) {
		foreach($fields as $field) {
			if($field['type'] != 'file' && (!isset($_REQUEST['jobman-field-'.$field['id']]) || $_REQUEST['jobman-field-'.$field['id']] == '')) {
				continue;
			}
			
			if($field['type'] == 'file' && !isset($_FILES['jobman-field-'.$field['id']])) {
				continue;
			}
			
			switch($field['type']) {
				case 'file':
					$matches = array();
					preg_match('/.*\.(.+)$/', $_FILES['jobman-field-'.$field['id']]['name'], $matches);
					$ext = $matches[1];
					if(is_uploaded_file($_FILES['jobman-field-'.$field['id']]['tmp_name'])) {
						move_uploaded_file($_FILES['jobman-field-'.$field['id']]['tmp_name'], WP_PLUGIN_DIR . '/' . JOBMAN_FOLDER . '/uploads/' . $appid . '-' . $field['id'] . '.' . $ext);
						$data = $appid . '-' . $field['id'] . '.' . $ext;
					}
					else {
						$data = '';
					}
					break;
				case 'checkbox':
					$data = implode(', ', $_REQUEST['jobman-field-'.$field['id']]);
					break;
				default:
					$data = $_REQUEST['jobman-field-'.$field['id']];
			}
			
			$sql = $wpdb->prepare('INSERT INTO ' . $wpdb->prefix . 'jobman_application_data(applicationid, fieldid, data) VALUES(%d, %d, %s);', $appid, $field['id'], $data);
			$wpdb->query($sql);
		}
	}
	
	jobman_email_application($appid);

	// No error
	return -1;
}

function jobman_check_filters($jobid, $cat) {
	global $wpdb;
	
	if($jobid != -1) {
		$sql = 'SELECT af.id AS id, af.type AS type, af.filter AS filter FROM ' . $wpdb->prefix . 'jobman_application_fields AS af';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_application_field_categories AS afc ON afc.afid=af.id';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_jobs AS j ON j.id=%d';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_job_category AS jc ON jc.jobid=j.id AND jc.categoryid=afc.categoryid';
		$sql .= ' WHERE afc.categoryid IS NULL OR jc.categoryid=afc.categoryid ORDER BY sortorder ASC';
		$sql = $wpdb->prepare($sql, $jobid);
	}
	else {
		$sql = 'SELECT af.id AS id, af.type AS type, af.filter AS filter FROM ' . $wpdb->prefix . 'jobman_application_fields AS af';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_application_field_categories AS afc ON afc.afid=af.id';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_categories AS c ON c.slug=%s';
		$sql .= ' WHERE afc.categoryid IS NULL OR c.id=afc.categoryid ORDER BY sortorder ASC';
		$sql = $wpdb->prepare($sql, $cat);
	}
	$fields = $wpdb->get_results($sql, ARRAY_A);
	
	$matches = array();
	if(count($fields) > 0) {
		foreach($fields as $field) {
			if($field['filter'] == '') {
				// No filter for this field
				continue;
			}
			
			$used_eq = false;
			$eqflag = false;
			
			$data = $_REQUEST['jobman-field-'.$field['id']];
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
	global $wpdb;
	
	$sql = $wpdb->prepare('SELECT c.email AS email FROM ' . $wpdb->prefix . 'jobman_categories AS c LEFT JOIN ' . $wpdb->prefix . 'jobman_application_categories AS ac ON ac.categoryid=c.id WHERE ac.applicationid=%d AND c.email NOT NULL;', $appid);
	$emails = $wpdb->get_results($sql, ARRAY_A);
	
	$to = '';
	if(count($emails) > 0) {
		$ii = 1;
		foreach($emails as $email) {
			$to .= $email['email'];
			if($ii < count($emails)) {
				$to .= ', ';
			}
		}
	} else {
		$to = get_option('jobman_default_email');
	}
	
	if($to == '') {
		return;
	}
	
	$fromid = get_option('jobman_application_email_from');
	if($fromid == '') {
		$from = get_option('jobman_default_email');
	}
	else {
		$sql = $wpdb->prepare('SELECT data FROM ' . $wpdb->prefix . 'jobman_application_data WHERE applicationid=%d AND fieldid=%d;', $appid, $fromid);
		$from = $wpdb->get_var($sql);
	}
	
	if($from == '') {
		$from = 'NO-REPLY <NO-REPLY@fakedomain.com>';
	}
	
	$subject = get_option('jobman_application_email_subject_text');
	if($subject != '') {
		$subject .= ' ';
	}

	$fid_text = get_option('jobman_application_email_subject_fields');
	$fids = split(',', $fid_text);

	if(count($fids) > 0) {
		foreach($fids as $fid) {
			$sql = $wpdb->prepare('SELECT data FROM ' . $wpdb->prefix . 'jobman_application_data WHERE applicationid=%d AND fieldid=%d;', $appid, $fid);
			$data = $wpdb->get_var($sql);
			
			if($data != '') {
				$subject .= $data . ' ';
			}
		}
	}
	
	$msg = '';
	
	$url = get_option('jobman_page_name');
	
	$sql = $wpdb->prepare('SELECT a.jobid AS jobid, j.title AS jobtitle, a.submitted AS submitted FROM ' . $wpdb->prefix . 'jobman_applications AS a LEFT JOIN ' . $wpdb->prefix . 'jobman_jobs AS j ON j.id=a.jobid WHERE a.id=%d;', $appid);
	$data = $wpdb->get_results($sql, ARRAY_A);
	if(count($data) > 0) {
		$msg .= __('Application Link', 'jobman') . ': ' . admin_url('admin.php?page=jobman-list-applications&amp;appid=' . $appid) . PHP_EOL;
		if($data[0]['jobid'] != '') {
			$msg .= __('Job', 'jobman') . ': ' . $data[0]['jobid'] . ' - ' . $data[0]['jobtitle'] . PHP_EOL;
			$msg .= get_option('home') . "/$url/view/" . $data[0]['jobid'] . '-' . strtolower(str_replace(' ', '-', $data[0]['jobtitle'])) . '/' . PHP_EOL;
		}
		$msg .= __('Timestamp', 'jobman') . ': ' . $data[0]['submitted'] . PHP_EOL . PHP_EOL;
	}
	
	$sql = $wpdb->prepare('SELECT af.label as label, af.type AS type, ad.data AS data FROM ' . $wpdb->prefix . 'jobman_application_data AS ad LEFT JOIN ' . $wpdb->prefix . 'jobman_application_fields AS af ON af.id=ad.fieldid WHERE ad.applicationid=%d ORDER BY af.sortorder ASC;', $appid);
	$data = $wpdb->get_results($sql, ARRAY_A);
	
	if(count($data) > 0) {
		foreach($data as $item) {
			switch($item['type']) {
				case 'text':
				case 'radio':
				case 'checkbox':
				case 'date':
					$msg .= $item['label'] . ': ' . $item['data'] . PHP_EOL;
					break;
				case 'textarea':
					$msg .= $item['label'] . ':' . PHP_EOL . $item['data'] . PHP_EOL;
					break;
				case 'file':
					$msg .= $item['label'] . ': ' . JOBMAN_URL . '/uploads/' . $item['data'] . PHP_EOL;
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