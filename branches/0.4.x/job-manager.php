<?php //encoding: utf-8
/*
Plugin Name: Job Manager
Plugin URI: http://pento.net/projects/wordpress-job-manager-plugin/
Description: A job listing and job application management plugin for WordPress.
Version: 0.4.4
Author: Gary Pendergast
Author URI: http://pento.net/
Text Domain: jobman
Tags: job, jobs, manager, list, listing, employment, employer, career
*/

// Version
define('JOBMAN_VERSION', '0.4.4');
define('JOBMAN_DB_VERSION', 6);

// Define the URL to the plugin folder
define('JOBMAN_FOLDER', 'job-manager');
define('JOBMAN_URL', get_option('siteurl').'/wp-content/plugins/' . JOBMAN_FOLDER);

//
// Load Jobman
//

// Jobman global functions
require_once(dirname(__FILE__).'/jobman-functions.php');

// Jobman setup (for installation/upgrades)
require_once(dirname(__FILE__).'/jobman-setup.php');

// Jobman database
require_once(dirname(__FILE__).'/jobman-db.php');

if(is_admin()) {
	// Jobman admin
	require_once(dirname(__FILE__).'/jobman-conf.php');
}

// Support for other plugins
require_once(dirname(__FILE__).'/jobman-plugins.php');

// Jobman frontend
require_once(dirname(__FILE__).'/jobman-display.php');

// Add hooks at the end
require_once(dirname(__FILE__).'/jobman-hooks.php');

// If the user is after an uploaded file, give it to them
if(array_key_exists('getfile', $_GET)) {
	jobman_get_uploaded_file($_GET['getfile']);
}

?>