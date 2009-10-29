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
			  slug VARCHAR(255),
			  title VARCHAR(255),
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
			  extension VARCHAR(3),
			  title VARCHAR(255),
			  PRIMARY KEY (id));';
	$wpdb->query($sql);
	
	$tablename = $wpdb->prefix . 'jobman_application_fields';
	$sql = 'CREATE TABLE ' . $tablename . ' (
			  id INT NOT NULL AUTO_INCREMENT,
			  label VARCHAR(255),
			  type VARCHAR(255),
			  data TEXT,
			  filter TEXT,
			  error TEXT,
			  sortorder INT,
			  PRIMARY KEY (id),
			  KEY sortorder (sortorder));';
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
			  label VARCHAR(255),
			  type VARCHAR(255),
			  data TEXT,			  
			  PRIMARY KEY (id));';
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