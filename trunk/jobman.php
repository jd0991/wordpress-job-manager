<?php //encoding: utf-8
/*
Plugin Name: Job Manager
Plugin URI: http://code.google.com/p/wordpress-job-mananger/
Description: A job management plugin for Wordpress.
Version: 0.1
Author: Gary Pendergast
Author URI: http://pento.net/
Tags: job, jobs, manager, jobs, list, listing, employment, employer
*/

// Version
define('JOBMAN_VERSION', '0.1');
define('JOBMAN_DB_VERSION', 1);

// Define the URL to the plugin folder
define('JOBMAN_FOLDER', dirname(plugin_basename(__FILE__)));
define('JOBMAN_URL', get_option('siteurl').'/wp-content/plugins/' . JOBMAN_FOLDER);

//
// Load Jobman
//

// Jobman global functions
require_once(WP_PLUGIN_DIR.'/'.JOBMAN_FOLDER.'/jobman-functions.php');

// Jobman setup (for installation/upgrades)
require_once(WP_PLUGIN_DIR.'/'.JOBMAN_FOLDER.'/jobman-setup.php');

// Jobman database
require_once(WP_PLUGIN_DIR.'/'.JOBMAN_FOLDER.'/jobman-db.php');

// Jobman admin
require_once(WP_PLUGIN_DIR.'/'.JOBMAN_FOLDER.'/jobman-conf.php');

// Jobman frontend
require_once(WP_PLUGIN_DIR.'/'.JOBMAN_FOLDER.'/jobman-display.php');

// Add hooks at the end
require_once(WP_PLUGIN_DIR.'/'.JOBMAN_FOLDER.'/jobman-hooks.php');

?>