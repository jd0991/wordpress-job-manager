<?php
//
// Google XML Sitemap
// http://wordpress.org/extend/plugins/google-sitemap-generator/
//

// Intercept the sitemap build and add all our URLs to it.
function jobman_gxs_buildmap() {
	global $wpdb;
	$options = get_option('jobman_options');

	if(!$options['plugins']['gxs']) {
		return;
	}

	$generatorObject = &GoogleSitemapGenerator::GetInstance();
	if($generatorObject == NULL) {
		// GXS doesn't seem to be here. Abort.
		return;
	}
	
	/*// Add the main list.
	$generatorObject->AddUrl(jobman_url(), time(), "daily", 0.5);

	// Add category list.
	$sql = 'SELECT slug FROM ' . $wpdb->prefix . 'jobman_categories ORDER BY id;';
	$categories = $wpdb->get_results($sql, ARRAY_A);
	
	if(count($categories) > 0 ) {
		foreach($categories as $cat) {
			$generatorObject->AddUrl(jobman_url($cat['slug']), time(), "daily", 0.5);
		}
	}
	
	// Add each job.
	$sql = 'SELECT j.id AS id, j.title AS title FROM ' . $wpdb->prefix . 'jobman_jobs AS j WHERE (j.displaystartdate <= NOW() OR j.displaystartdate = "") AND (j.displayenddate >= NOW() OR j.displayenddate = "") ORDER BY j.startdate ASC, j.enddate ASC';
	$jobs = $wpdb->get_results($sql, ARRAY_A);

	if(count($jobs) > 0) {
		foreach($jobs as $job) {
			$generatorObject->AddUrl(jobman_url('view', $job['id'] . '-' . strtolower(str_replace(' ', '-', $job['title']))), time(), "daily", 0.5);
		}
	}*/
}
?>