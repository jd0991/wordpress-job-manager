<?php //encoding: utf-8

function jobman_activate() {
	$version = get_option('jobman_version');
	$dbversion = get_option('jobman_db_version');
	
	if($dbversion == "" || 1) {
		// Never been run, create the database.
		jobman_create_db();
		jobman_create_default_settings();
	}
	elseif($dbversion != JOBMAN_DB_VERSION) {
		// New version, upgrade
		jobman_upgrade_db($dbversion);
	}

	update_option('jobman_version', JOBMAN_VERSION);
	update_option('jobman_db_version', JOBMAN_DB_VERSION);
}

function jobman_create_default_settings() {
	update_option('jobman_page_name', 'jobs');
	update_option('jobman_default_email', get_option('admin_email'));

	update_option('jobman_application_email_from', 4);
	update_option('jobman_application_email_subject_text', 'Job Application:');
	update_option('jobman_application_email_subject_fields', '2,3');
}

?>