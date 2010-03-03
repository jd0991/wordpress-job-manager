<?php // encoding: UTF-8
function jobman_display_jobs_list( $cat ) {
	global $jobman_shortcode_jobs, $jobman_shortcodes, $jobman_field_shortcodes;
	$options = get_option( 'jobman_options' );

	$content = '';
	
	$list_type = $options['list_type'];
	
	if( 'all' == $cat ) {
		$page = get_post( $options['main_page'] );
	}
	else {
		$page = get_post( $options['main_page'] );
		
		$page->post_type = 'jobman_joblist';
		$page->post_title = __( 'Jobs Listing', 'jobman' );
	}
	
	if( 'all' != $cat ) {
		$category = get_term( $cat, 'jobman_category' );
		if( NULL == $category )
			$cat = 'all';
		else
			$page->post_title = $category->name;
	}
	
	$sortby = '';
	switch( $options['sort_by'] ) {
		case 'title':
			$sortby = '&orderby=title';
			break;
		case 'dateposted':
			$sortby = '&orderby=date';
			break;
		case 'closingdate':
			$sortby = '&orderby=meta_value&meta_key=displayenddate';
			break;
	}
	
	$sortorder = '';
	if( in_array( $options['sort_order'], array( 'ASC', 'DESC' ) ) )
		$sortorder = '&order=' . $options['sort_order'];
	
	if( 'all' == $cat )
		$jobs = get_posts( "post_type=jobman_job&numberposts=-1$sortby$sortorder" );
	else
		$jobs = get_posts( "post_type=jobman_job&jcat=$category->slug&numberposts=-1$sortby$sortorder" );
		
	if( $options['user_registration'] ) {
		if( 'all' == $cat && $options['loginform_main'] )
			$content .= jobman_display_login();
		else if( 'all' != $cat && $options['loginform_category'] )
			$content .= jobman_display_login();
	}

	$related_cats = array();
	foreach( $jobs as $id => $job ) {
		// Remove expired jobs
		$displayenddate = get_post_meta( $job->ID, 'displayenddate', true );
		if( '' != $displayenddate && strtotime( $displayenddate ) <= time() ) {
			unset( $jobs[$id] );
			continue;
		}
			
		// Remove future jobs
		$displaystartdate = $job->post_date;
		if( '' != $displaystartdate && strtotime( $displaystartdate ) > time() ) {
			unset( $jobs[$id] );
			continue;
		}
			
		// Get related categories
		if( $options['related_categories'] ) {
			$categories = wp_get_object_terms( $job->ID, 'jobman_category' );
			if( count( $categories ) > 0 ) {
				foreach( $categories as $cat ) {
					$related_cats[] = $cat->slug;
				}
			}
		}
	}
	$related_cats = array_unique( $related_cats );
	
	if( $options['related_categories'] && count( $related_cats ) > 0 ) {
		$links = array();
		foreach( $related_cats as $rc ) {
			$cat = get_term_by( 'slug', $rc, 'jobman_category' );
			$links[] = '<a href="'. get_term_link( $cat->slug, 'jobman_category' ) . '" title="' . sprintf( __( 'Jobs for %s', 'jobman' ), $cat->name ) . '">' . $cat->name . '</a>';
		}
		
		$content .= '<h3>' . __( 'Related Categories', 'jobman' ) . '</h3>';
		$content .= implode(', ', $links) . '<br>';
	}
	
	if( count( $jobs ) > 0 ) {
		if( 'sticky' == $options['highlighted_behaviour'] )
			// Sort the sticky jobs to the top
			uasort( $jobs, 'jobman_sort_highlighted_jobs' );

		$template = $options['templates']['job_list'];
		
		jobman_add_shortcodes( $jobman_shortcodes );
		jobman_add_field_shortcodes( $jobman_field_shortcodes );
		
		$jobman_shortcode_jobs = $jobs;
		$content .= do_shortcode( $template );
		
		jobman_remove_shortcodes( array_merge( $jobman_shortcodes, $jobman_field_shortcodes ) );

	}
	else {
		$data = get_posts( 'post_type=jobman_app_form&numberposts=-1' );
		
		if( count( $data > 0 ) )
			$applypage = $data[0];
		
		$content .= '<p>';
		if( 'all' == $cat ||  ! isset( $category->term_id ) ) {
			$content .= sprintf( __( "We currently don't have any jobs available. Please check back regularly, as we frequently post new jobs. In the meantime, you can also <a href='%s'>send through your résumé</a>, which we'll keep on file.", 'jobman' ), get_page_link( $applypage->ID ) );
		}
		else {
			$url = get_page_link( $applypage->ID );
			$structure = get_option( 'permalink_structure' );
			if( '' == $structure ) {
				$url .= '&amp;c=' . $category->term_id;
			}
			else {
				if( substr( $url, -1 ) == '/' )
					$url .= $category->slug . '/';
				else
					$url .= '/' . $category->slug;
			}
			$content .= sprintf( __( "We currently don't have any jobs available in this area. Please check back regularly, as we frequently post new jobs. In the mean time, you can also <a href='%s'>send through your résuméa>, which we'll keep on file, and you can check out the <a href='%s'>jobs we have available in other areas</a>.", 'jobman' ), $url, get_page_link( $options['main_page'] ) );
		}
	}

	$page->post_content = $content;
	
	return array( $page );
}

function jobman_sort_highlighted_jobs( $a, $b ) {
	$ahighlighted = get_post_meta( $a->ID, 'highlighted', true );
	$bhighlighted = get_post_meta( $b->ID, 'highlighted', true );
	
	if( $ahighlighted == $bhighlighted )
		return 0;
	
	if( 1 == $ahighlighted )
		return -1;
		
	return 1;
}

function jobman_display_job( $job ) {
	global $jobman_shortcode_job, $jobman_shortcodes, $jobman_field_shortcodes;
	$options = get_option( 'jobman_options' );

	$content = '';
	
	if( is_string( $job ) || is_int( $job ) )
		$job = get_post( $job );
	
	if( $options['user_registration'] && $options['loginform_job'] && 'summary' == $options['list_type'] )
		$content .= jobman_display_login();

	if( NULL != $job ) {
		$jobmeta = get_post_custom( $job->ID );
		$jobdata = array();
		foreach( $jobmeta as $key => $value ) {
			if( is_array( $value ) )
				$jobdata[$key] = $value[0];
			else
				$jobdata[$key] = $value;
		}
	}
	
	// Check that the job hasn't expired
	if( array_key_exists( 'displayenddate', $jobdata ) && '' != $jobdata['displayenddate'] && strtotime($jobdata['displayenddate']) <= time() )
		$job = NULL;
		
	// Check that the job isn't in the future
	if( strtotime( $job->post_date ) > time() )
		$job = NULL;
	
	if( NULL == $job ) {
		$page = get_post( $options['main_page'] );
		$page->post_type = 'jobman_job';
		$page->post_title = __( 'This job doesn\'t exist', 'jobman' );

		$content .= '<p>' . sprintf( __( 'Perhaps you followed an out-of-date link? Please check out the <a href="%s">jobs we have currently available</a>.', 'jobman' ), get_page_link( $options['main_page'] ) ) . '</p>';
		
		$page->post_content = $content;
			
		return array( $page );
	}

	$template = $options['templates']['job'];
	
	jobman_add_shortcodes( $jobman_shortcodes );
	jobman_add_field_shortcodes( $jobman_field_shortcodes );
	
	$jobman_shortcode_job = $job;
	$content .= do_shortcode( $template );
	
	jobman_remove_shortcodes( array_merge( $jobman_shortcodes, $jobman_field_shortcodes ) );

	$page = $job;

	$page->post_title = $options['text']['job_title_prefix'] . $job->post_title;
	
	$page->post_content = $content;
		
	return array( $page );
}
?>