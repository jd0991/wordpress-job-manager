<?php //encoding: utf-8
	
function jobman_create_db() {
	global $wpdb;
	
	$tablename = $wpdb->prefix . 'jobman_jobs';
	$sql = 'CREATE TABLE ' . $tablename . ' (
			  id INT NOT NULL AUTO_INCREMENT,
			  iconid INT,
			  title VARCHAR(255),
			  salary VARCHAR(255),
			  startdate VARCHAR(255),
			  enddate VARCHAR(255),
			  location TEXT,
			  displaystartdate VARCHAR(10),
			  displayenddate VARCHAR(10),
			  abstract TEXT,
			  PRIMARY KEY (id));';
	$wpdb->query($sql);
	
	$tablename = $wpdb->prefix . 'jobman_categories';
	$sql = 'CREATE TABLE ' . $tablename . ' (
			  id INT NOT NULL AUTO_INCREMENT,
			  title VARCHAR(255),
			  slug VARCHAR(255),
			  email VARCHAR(255),
			  PRIMARY KEY (id));';
	$wpdb->query($sql);
	
	$tablename = $wpdb->prefix . 'jobman_job_category';
	$sql = 'CREATE TABLE ' . $tablename . ' (
			  jobid INT,
			  categoryid INT,
			  KEY job (jobid),
			  KEY category (categoryid));';
	$wpdb->query($sql);
	
	$tablename = $wpdb->prefix . 'jobman_icons';
	$sql = 'CREATE TABLE ' . $tablename . ' (
			  id INT NOT NULL AUTO_INCREMENT,
			  title VARCHAR(255),
			  extension VARCHAR(3),
			  PRIMARY KEY (id));';
	$wpdb->query($sql);
	
	$tablename = $wpdb->prefix . 'jobman_application_fields';
	$sql = 'CREATE TABLE ' . $tablename . ' (
			  id INT NOT NULL AUTO_INCREMENT,
			  label VARCHAR(255),
			  type VARCHAR(255),
			  listdisplay INT,
			  data TEXT,
			  filter TEXT,
			  error TEXT,
			  sortorder INT,
			  PRIMARY KEY (id),
			  KEY sortorder (sortorder));';
	$wpdb->query($sql);
	
	$sql = "INSERT INTO $tablename (id, label, type, listdisplay, data, filter, error, sortorder) VALUES
			(1, 'Personal Details', 'heading', 0, '', '', '', 0),
			(2, 'Name', 'text', 1, '', '', '', 1),
			(3, 'Surname', 'text', 1, '', '', '', 2),
			(4, 'Email Address', 'text', 0, '', '', '', 3),
			(5, 'Contact Details', 'heading', 0, '', '', '', 4),
			(6, 'Address', 'textarea', 0, '', '', '', 5),
			(7, 'City', 'text', 0, '', '', '', 6),
			(8, 'Post code', 'text', 0, '', '', '', 7),
			(9, 'Country', 'text', 1, '', '', '', 8),
			(10, 'Telephone', 'text', 0, '', '', '', 9),
			(11, 'Cell Phone', 'text', 0, '', '', '', 10),
			(12, 'Qualifications', 'heading', 0, '', '', '', 11),
			(13, 'Do you have a degree?', 'radio', 1, 'Yes\r\nNo', '', '', 12),
			(14, 'Where did you complete your degree?', 'text', 0, '', '', '', 13),
			(15, 'Title of your degree', 'text', 1, '', '', '', 14),
			(16, 'Upload your CV', 'file', 1, '', '', '', 15),
			(17, '', 'blank', 0, '', '', '', 16),
			(18, '', 'checkbox', 0, 'I have read and understood the privacy policy.', 'I have read and understood the privacy policy.', 'You need to read and agree to our privacy policy before we can accept your application. Please click the ''Back'' button in your browser, read our privacy policy, and confirm that you accept.', 17);";
	$wpdb->query($sql);
	
	$tablename = $wpdb->prefix . 'jobman_application_field_categories';
	$sql = 'CREATE TABLE ' . $tablename . ' (
			  afid INT,
			  categoryid INT,
			  KEY af (afid),
			  KEY category (categoryid));';
	$wpdb->query($sql);
	
	$tablename = $wpdb->prefix . 'jobman_applications';
	$sql = 'CREATE TABLE ' . $tablename . ' (
			  id INT NOT NULL AUTO_INCREMENT,
			  jobid INT,
			  submitted DATETIME,
			  PRIMARY KEY (id),
			  KEY job (jobid));';
	$wpdb->query($sql);
	
	$tablename = $wpdb->prefix . 'jobman_application_categories';
	$sql = 'CREATE TABLE ' . $tablename . ' (
			  applicationid INT,
			  categoryid INT,
			  KEY application (applicationid),
			  KEY category (categoryid));';
	$wpdb->query($sql);
	
	$tablename = $wpdb->prefix . 'jobman_application_data';
	$sql = 'CREATE TABLE ' . $tablename . ' (
			  id INT NOT NULL AUTO_INCREMENT,
			  applicationid INT,
			  fieldid INT,
			  data TEXT,
			  PRIMARY KEY (id),
			  KEY appid (applicationid));';
	$wpdb->query($sql);
}

function jobman_upgrade_db($oldversion) {
	global $wpdb;
	
	if($oldversion < 4) {
		// Fix any empty slugs in the category list.
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'jobman_categories ORDER BY id;';
		$categories = $wpdb->get_results($sql, ARRAY_A);
		
		if(count($categories) > 0 ) {
			foreach($categories as $cat) {
				if($cat['slug'] == '') {
					$slug = strtolower($cat['title']);
					$slug = str_replace(' ', '-', $slug);
					
					$sql = $wpdb->prepare('UPDATE ' . $wpdb->prefix . 'jobman_categories SET slug=%s WHERE id=%d;', $slug, $id);
					$wpdb->query($sql);
				}
			}
		}
	}
	if($oldversion < 5) {
		// Re-write the database to use the existing WP tables
		
		$pages = array();
		
		// Create the root jobs page
		$page = array(
					'comment_status' => 'closed',
					'ping_status' => 'closed',
					'post_status' => 'publish',
					'post_author' => 1,
					'post_content' => '',
					'post_name' => get_option('home'),
					'post_title' => __('Jobs Listing', 'jobman'),
					'post_type' => 'page');
		$mainid = wp_insert_post($page);
		$pages[] = $mainid;
		add_post_meta($mainid, '_jobman', 1, true);

		// Move the categories to WP categories
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'jobman_categories;';
		$categories = $wpdb->get_results($sql, ARRAY_A);
		
		$oldcats = array();
		$newcats = array();
		
		if(count($categories) > 0 ) {
			foreach($categories as $cat) {
				$oldcats[] = $cat['id'];
				// Check if a category with this slug exists
				$catid = get_category_by_slug($cat['slug'])->term_id;
				if($catid) {
					// Category already exists
					$newcats[] = $catid;
				}
				else {
					$newcat = array(
									'cat_name' => $cat['title'],
									'category_nicename' => $cat['slug']);
					$catid = wp_insert_category($newcat);
					$newcats[] = $catid;
				}
			}
		}

		// Move the jobs to posts
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'jobman_jobs;';
		$jobs = $wpdb->get_results($sql, ARRAY_A);
		if(count($jobs) > 0) {
			foreach($jobs as $job) {
				// Get the old category ids
				$sql = $wpdb->prepare('SELECT c.id AS id FROM ' . $wpdb->prefix . 'jobman_categories AS c LEFT JOIN ' . $wpdb->prefix . 'jobman_job_category AS jc ON c.id=jc.categoryid WHERE jc.jobid=%d;', $job['id']);
				$data = $wpdb->get_results($sql, ARRAY_A);
				$cats = array();
				$catstring = '';
				if(count($data) > 0) {
					foreach($data as $cat) {
						// Make an array of the new category ids
						$cats[] = $newcats[array_search($cat['id'], $oldcats)];
					}
				}

				$page = array(
							'comment_status' => 'closed',
							'ping_status' => 'closed',
							'post_status' => 'publish',
							'post_author' => 1,
							'post_content' => $job['abstract'],
							'post_category' => $cats,
							'post_name' => get_option('home'),
							'post_title' => __('Job', 'jobman') . ': ' . $job['title'],
							'post_type' => 'page',
							'post_date' => $job['displaystartdate'],
							'post_parent' => $mainid);
				$id = wp_insert_post($page);
				$pages[] = $id;
				add_post_meta($id, '_jobman', 1, true);
				
				add_post_meta($id, '_jobman_salary', $job['salary'], true);
				add_post_meta($id, '_jobman_startdate', $job['startdate'], true);
				add_post_meta($id, '_jobman_enddate', $job['enddate'], true);
				add_post_meta($id, '_jobman_location', $job['location'], true);
				add_post_meta($id, '_jobman_displayenddate', $job['displayenddate'], true);
				add_post_meta($id, '_jobman_iconid', $job['iconid'], true);
			}
		}
		
		// Move the icons to jobman_options
		
		// Move the application fields to jobman_options
		// Create the apply page
		
		// Drop the old tables
		$tables = array(
					$wpdb->prefix . 'jobman_jobs',
					$wpdb->prefix . 'jobman_categories',
					$wpdb->prefix . 'jobman_job_category',
					$wpdb->prefix . 'jobman_icons',
					$wpdb->prefix . 'jobman_application_fields',
					$wpdb->prefix . 'jobman_application_field_categories',
					$wpdb->prefix . 'jobman_applications',
					$wpdb->prefix . 'jobman_application_categories',
					$wpdb->prefix . 'jobman_application_data'
				);
				
		foreach($tables as $table) {
			$sql = 'DROP TABLE IF EXISTS ' . $table;
			// $wpdb->query($sql);
		}
	}
}

function jobman_drop_db() {
}

?>