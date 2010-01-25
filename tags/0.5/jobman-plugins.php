<?php
//
// Google XML Sitemap
// http://wordpress.org/extend/plugins/google-sitemap-generator/
//

// Intercept the sitemap build and add all our URLs to it.
function jobman_gxs_buildmap() {
	global $wpdb;
	$options = get_option( 'jobman_options' );

	if( ! $options['plugins']['gxs'] )
		return;

	$generatorObject = &GoogleSitemapGenerator::GetInstance();
	if( NULL == $generatorObject )
		// GXS doesn't seem to be here. Abort.
		return;
	
	// Add each job if individual jobs are displayed
	if( 'summary' == $options['list_type'] ) {
		$jobs = get_posts( 'post_type=jobman_job' );

		if( count( $jobs ) > 0 ) {
			foreach( $jobs as $job ) {
				$generatorObject->AddUrl( get_page_link($job->ID), time(), "daily", 0.5 );
			}
		}
	}
}
?>