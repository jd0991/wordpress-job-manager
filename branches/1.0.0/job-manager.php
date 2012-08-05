<?php //encoding: utf-8
/*
Plugin Name: Job Manager
Plugin URI: http://pento.net/projects/wordpress-job-manager-plugin/
Description: A job listing and job application management plugin for WordPress.
Version: 1.0
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
define( 'JOBMAN_VERSION', '1.0' );
define( 'JOBMAN_DB_VERSION', 19 );

// Define the URL to the plugin folder
define( 'JOBMAN_FOLDER', 'job-manager' );
if( ! defined( 'JOBMAN_URL' ) )
	define( 'JOBMAN_URL', WP_PLUGIN_URL . '/' . JOBMAN_FOLDER );

// Define the basename
define( 'JOBMAN_BASENAME', plugin_basename( __FILE__ ) );

// Define the complete directory path
define( 'JOBMAN_DIR', dirname( __FILE__ ) );

// Load core Job Manager libraries
require dirname( __FILE__ ) . '/form-helpers.php';
require dirname( __FILE__ ) . '/class-custom-field.php';
require dirname( __FILE__ ) . '/class-custom-field-set.php';
require dirname( __FILE__ ) . '/class-job.php';
require dirname( __FILE__ ) . '/class-options.php';
require dirname( __FILE__ ) . '/class-admin-page.php';
require dirname( __FILE__ ) . '/class-admin-page-edit-job.php';
require dirname( __FILE__ ) . '/setup.php';
require dirname( __FILE__ ) . '/hooks.php';

function setup() {
	\jobman\setup();
}

?>