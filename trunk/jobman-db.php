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
			(16, '', 'blank', 0, '', '', '', 15),
			(17, '', 'checkbox', 0, 'I have read and understood the privacy policy.', 'I have read and understood the privacy policy.', 'You need to read and agree to our privacy policy before we can accept your application. Please click the ''Back'' button in your browser, read our privacy policy, and confirm that you accept.', 16);";
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
}

?>