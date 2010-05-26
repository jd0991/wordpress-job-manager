<?php //encoding: utf-8
/*
Plugin Name: Job Manager
Plugin URI: http://pento.net/projects/wordpress-job-manager-plugin/
Description: A job listing and job application management plugin for WordPress.
Version: 0.6.6
Author: Gary Pendergast
Author URI: http://pento.net/
Text Domain: jobman
Tags: job, jobs, manager, list, listing, employment, employer, career
*/

/*
    Copyright 2009, 2010 Gary Pendergast (http://pento.net/)
	Copyright 2010 Automattic (http://automattic.com/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Version
define( 'JOBMAN_VERSION', '0.6.6' );
define( 'JOBMAN_DB_VERSION', 13 );

// Define the URL to the plugin folder
define( 'JOBMAN_FOLDER', 'job-manager' );
define( 'JOBMAN_URL', WP_PLUGIN_URL . '/' . JOBMAN_FOLDER );

// Define the basename
define( 'JOBMAN_BASENAME', plugin_basename(__FILE__) );

// Some Global vars

global $jobman_shortcodes;
$jobman_shortcodes = array( 'job_loop', 'job_row_number', 'job_id', 'job_highlighted', 'job_odd_even', 'job_link', 'job_icon', 'job_title', 'job_field', 'job_field_label', 'job_categories', 'job_category_links', 'job_field_loop', 'job_apply_link' );

$jobman_options = get_option( 'jobman_options' );
global $jobman_field_shortcodes;
$jobman_field_shortcodes = array();
if( is_array( $jobman_options ) && array_key_exists( 'job_fields', $jobman_options ) )
	foreach( $jobman_options['job_fields'] as $fid => $field )
		$jobman_field_shortcodes[] = "job_field$fid";

//
// Load Jobman
//

// Jobman global functions
require_once( dirname( __FILE__ ) . '/functions.php' );

// Jobman setup (for installation/upgrades)
require_once( dirname( __FILE__ ) . '/setup.php' );

// Jobman database
require_once( dirname( __FILE__ ) . '/db.php' );

// Jobman admin
require_once( dirname( __FILE__ ) . '/admin.php' );

// Support for other plugins
require_once( dirname( __FILE__ ) . '/plugins.php' );

// Jobman frontend
require_once( dirname( __FILE__ ) . '/frontend.php' );

// Widgets
require_once( dirname( __FILE__ ) . '/widgets.php' );

// Add hooks at the end
require_once( dirname( __FILE__ ) . '/hooks.php' );

// If the user is after a CSV export, give it to them
if( array_key_exists( 'jobman-mass-edit', $_REQUEST ) && 'export-csv' == $_REQUEST['jobman-mass-edit'] )
	jobman_get_application_csv();
?>