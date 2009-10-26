<?php //encoding: utf-8
function jobman_queryvars($qvars) {
	$qvars[] = 'jobman';
	return $qvars;
}

function jobman_add_rewrite_rules($wp_rewrite) {
	$url = get_option('jobman_page_name');
	if(!$url) {
		return;
	}
	$new_rules = array( 
						"$url/?$" => "index.php?jobman=all",
						"$url/(.+)" => 'index.php?jobman=' .
						$wp_rewrite->preg_index(1) );

	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

function jobman_flush_rewrite_rules() {
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}


function jobman_display_jobs($posts) {
	global $wp_query;

	if(!isset($wp_query->query_vars['jobman'])) {
		return $posts;
	}
	
	$url = $wp_query->query_vars['jobman'];
	$matches = array();
	
	if($url == 'all') {
		return jobman_display_jobs_list('all');
	}

	if(preg_match('/^view\/(\d+)/', $url, $matches)) {
		return jobman_display_job($matches[1]);
	}
	
	if(preg_match('/^apply\/(\d+)/', $url, $matches)) {
		return jobman_display_apply($matches[1]);
	}
	if(preg_match('/^apply\/([^\/]+)/', $url, $matches)) {
		return jobman_display_apply(-1, $matches[1]);
	}
	if(preg_match('/^apply$/', $url, $matches)) {
		return jobman_display_apply(-1);
	}
	
	if(preg_match('/^([^\/]+)/', $url, $matches)) {
		return jobman_display_jobs_list($matches[1]);
	}
	
	return NULL;
}

function jobman_display_init() {
	wp_enqueue_script('jquery-ui-datepicker', JOBMAN_URL.'/js/jquery-ui-datepicker.js', array('jquery-ui-core'), JOBMAN_VERSION);
	wp_enqueue_style('jobman-display', JOBMAN_URL.'/css/display.css', false, JOBMAN_VERSION);
}

function jobman_display_template() {
	global $wp_query;
	
	if(isset($wp_query->query_vars['jobman'])) {
		include(TEMPLATEPATH . '/page.php');
		exit;
	}
}

function jobman_display_title($title, $sep, $seploc) {
	global $wpdb, $wp_query;
	
	if(!isset($wp_query->query_vars['jobman'])) {
		return $title;
	}

	$url = $wp_query->query_vars['jobman'];
	$matches = array();
	
	if(preg_match('/^view\/(\d+)-(.*)/', $url, $matches)) {
		$title = $wpdb->get_var($wpdb->prepare('SELECT title FROM ' . $wpdb->prefix . 'jobman_jobs WHERE id=%d AND (displaystartdate <= NOW() OR displaystartdate = NULL) AND (displayenddate >= NOW() OR displayenddate = NULL);', $matches[1]));
		if($title != '') {
			$newtitle = __('Job', 'jobman') . ': ' . $title;
		}
		else {
			$newtitle = __('This job doesn\'t exist', 'jobman');
			add_action('wp_head', 'jobman_display_robots_noindex');
		}
	}
	else if(preg_match('/^apply/', $url, $matches)) {
		$newtitle = __('Job Application', 'jobman');
	}
	else if($url == 'all') {
		$newtitle = __('Jobs Listing', 'jobman');
	}
	else if(preg_match('/^([^\/]+)/', $url, $matches)) {
		$category = $wpdb->get_var($wpdb->prepare('SELECT title FROM ' . $wpdb->prefix . 'jobman_categories WHERE slug=%s', $matches[1]));
		$newtitle = __('Jobs Listing', 'jobman');
		if($category != '') {
			$newtitle .= ': ' . $category;
		}
	}
	else {
		$newtitle = __('Jobs Listing', 'jobman');
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
	if(is_feed()) {
		return;
	}
?>
<script type="text/javascript"> 
//<![CDATA[
jQuery(document).ready(function() {
	jQuery(".datepicker").datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true, gotoCurrent: true});
});
//]]>
</script> 
<?php
}

function jobman_display_edit_post_link($link) {
	global $wp_query;
	
	if(!isset($wp_query->query_vars['jobman'])) {
		return $link;
	}

	$url = $wp_query->query_vars['jobman'];
	$matches = array();
	
	if(preg_match('/^view\/(\d+)/', $url, $matches)) {
		return admin_url('admin.php?page=jobman-jobs-list&amp;jobid=' . $matches[1]);
	}

	return admin_url('admin.php?page=jobman-jobs-list');
}

function jobman_display_jobs_list($cat) {
	global $wpdb;
	
	$page = new stdClass;
	$content = '';
	
	$url = get_option('jobman_page_name');
	
	$page->post_title = __('Jobs Listing', 'jobman');
	
	$sql = 'SELECT id, title, salary, startdate, startdate <= NOW() AS asap, location FROM ' . $wpdb->prefix . 'jobman_jobs WHERE (displaystartdate <= NOW() OR displaystartdate = NULL) AND (displayenddate >= NOW() OR displayenddate = NULL) ORDER BY startdate ASC, enddate ASC';
	if($cat != 'all') {
		$category = $wpdb->get_var($wpdb->prepare('SELECT title FROM ' . $wpdb->prefix . 'jobman_categories WHERE slug=%s', $cat));
		if($category != '') {
			$page->post_title .= ': ' . $category;
			$sql = '';
		}
		else {
			$cat = 'all';
		}
	}
	
	$jobs = $wpdb->get_results($sql, ARRAY_A);

	if(count($jobs) > 0) {
		$content .= '<table class="jobs-table">';
		$content .= '<tr><th>' . __('Title', 'jobman') . '</th><th>' . __('Salary', 'jobman') . '</th><th>' . __('Start Date', 'jobman') . '</th><th>' . __('Location', 'jobman') . '</th></tr>';
		foreach($jobs as $job) {
			$content .= '<tr><td><a href="'. get_option('home') . "/$url/view/" . $job['id'] . '-' . strtolower(str_replace(' ', '-', $job['title'])) . '/">' . $job['title'] . '</a></td>';
			$content .= '<td>' . $job['salary'] . '</td>';
			$content .= '<td>' . (($job['asap'])?(__('ASAP', 'jobman')):($job['startdate'])) . '</td>';
			$content .= '<td>' . $job['location'] . '</td>';
			$content .= '<td class="jobs-moreinfo"><a href="'. get_option('home') . "/$url/view/" . $job['id'] . '-' . strtolower(str_replace(' ', '-', $job['title'])) . '/">' . __('More Info', 'jobman') . '</a></td></tr>';
		}
		$content .= '</table>';
	}
	else {
		$content .= '<p>';
		if($cat == 'all') {
			$content .= sprintf(__('We currently don\'t have any jobs available. Please check back regularly, as we frequently post new jobs. In the mean time, you can also <a href="%s">send through your résumé</a>, which we\'ll keep on file.', 'jobman'), get_option('home') . "/$url/apply/");
		}
		else {
			$content .= sprintf(__('We currently don\'t have any jobs available in this area. Please check back regularly, as we frequently post new jobs. In the mean time, you can also <a href="%s">send through your résumé</a>, which we\'ll keep on file, and you can check out the <a href="%s">jobs we have available in other areas</a>.', 'jobman'), get_option('home') . "/$url/apply/", get_option('home') . "/$url/");
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
	
	$sql = $wpdb->prepare('SELECT id, title, salary, startdate, startdate <= NOW() AS asap, enddate, location, abstract FROM ' . $wpdb->prefix . 'jobman_jobs WHERE id=%d AND (displaystartdate <= NOW() OR displaystartdate = NULL) AND (displayenddate >= NOW() OR displayenddate = NULL);', $jobid);
	$data = $wpdb->get_results($sql, ARRAY_A);
	
	if(count($data) <= 0) {
		$page->post_title = __('This job doesn\'t exist', 'jobman');

		$content = sprintf(__('Perhaps you followed an out-of-date link? Please check out the <a href="%s">jobs we have currently available</a>.', 'jobman'), get_option('home') . "/$url/");;
		
		$page->post_content = $content;
			
		return array($page);
	}
	
	$job = $data[0];
	
	$page->post_title = __('Job', 'jobman') . ': ' . $job['title'];
	
	$content .= '<table class="job-table">';
	$content .= '<tr><th scope="row">' . __('Title', 'jobman') . '</th><td>' . $job['title'] . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('Salary', 'jobman') . '</th><td>' . $job['salary'] . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('Start Date', 'jobman') . '</th><td>' . (($job['asap'])?(__('ASAP', 'jobman')):($job['startdate'])) . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('End Date', 'jobman') . '</th><td>' . (($job['enddate'] == '')?(__('Ongoing', 'jobman')):($job['enddate'])) . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('Location', 'jobman') . '</th><td>' . $job['location'] . '</td></tr>';
	$content .= '<tr><th scope="row">' . __('Information', 'jobman') . '</th><td>' . jobman_format_abstract($job['abstract']) . '</td></tr>';
	$content .= '<tr><td></td><td class="jobs-applynow"><a href="'. get_option('home') . "/$url/apply/" . $job['id'] . '/">' . __('Apply Now!', 'jobman') . '</td></tr>';
	$content .= '</table>';
	
	$page->post_content = $content;
		
	return array($page);
}

function jobman_display_apply($jobid, $cat = NULL) {
	global $wpdb;

	$url = get_option('jobman_page_name');
	
	$page = new stdClass;
	$content = '';
	
	$sql = $wpdb->prepare('SELECT id, title FROM ' . $wpdb->prefix . 'jobman_jobs WHERE id=%d AND (displaystartdate <= NOW() OR displaystartdate = NULL) AND (displayenddate >= NOW() OR displayenddate = NULL);', $jobid);
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
				$categoryid .= $catid;
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
		$content .= __('Title', 'jobman') . ': <a href="'. get_option('home') . "/$url/view/" . $job['id'] . '-' . strtolower(str_replace(' ', '-', $job['title'])) . '/">' . $job['title'] . '</a>';
	}

	if($jobid != -1) {
		$sql = 'SELECT af.id AS id, af.label AS label, af.type AS type, af.data AS data FROM ' . $wpdb->prefix . 'jobman_application_fields AS af';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_application_field_categories AS afc ON afc.afid=af.id';
		$sql .= ' LEFT JOIN ' . $wpdb->prefix . 'jobman_jobs AS j ON j.id=' . $jobid;
		$sql .= ' LEFT JOIN wp_jobman_job_category AS jc ON jc.jobid=j.id AND jc.categoryid=afc.categoryid';
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
					$content .= '<td><textarea name="jobman-field-' . $field['id'] . '"> ' . $field['data'] . '</textarea></td></tr>';
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

?>