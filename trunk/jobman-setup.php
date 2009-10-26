<?php //encoding: utf-8

function jobman_activate() {
	$version = get_option('jobman_version');
	$dbversion = get_option('jobman_db_version');
	
	if($dbversion == "" || 1) {
		// Never been run, create the database.
		jobman_create_db();
	}
	elseif($dbversion != JOBMAN_DB_VERSION) {
		// New version, upgrade
		jobman_upgrade_db($dbversion);
	}

	update_option('jobman_version', JOBMAN_VERSION);
	update_option('jobman_db_version', JOBMAN_DB_VERSION);
}

?>