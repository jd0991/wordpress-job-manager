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
	update_option('jobman_list_type', 'full');

	update_option('jobman_application_email_from', 4);
	update_option('jobman_application_email_subject_text', 'Job Application:');
	update_option('jobman_application_email_subject_fields', '2,3');
}

function jobman_upgrade_settings($oldversion) {
	if($oldversion <= 1) {
		update_option('jobman_list_type', 'full');
	}
}

function jobman_uninstall() {
	delete_option('jobman_version');
	delete_option('jobman_db_version');
	delete_option('jobman_page_name');
	delete_option('jobman_default_email');
	delete_option('jobman_list_type');
	delete_option('jobman_application_email_from');
	delete_option('jobman_application_email_subject_text');
	delete_option('jobman_application_email_subject_fields');
	
	jobman_drop_db();
}

?>