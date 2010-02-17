<?php
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

		/*if( 'summary' == $list_type ) {
			$content .= '<table class="jobs-table">';
			$content .= '<tr class="heading"><th>' . __( 'Title', 'jobman' ) . '</th><th>' . __( 'Salary', 'jobman' ) . '</th><th>' . __( 'Start Date', 'jobman' ) . '</th><th>' . __( 'Location', 'jobman' ) . '</th></tr>';
			$rowcount = 1;
			foreach( $jobs as $job ) {
				$jobmeta = get_post_custom( $job->ID );
				$jobdata = array();
				foreach( $jobmeta as $key => $value ) {
					if( is_array( $value ) )
						$jobdata[$key] = $value[0];
					else
						$jobdata[$key] = $value;
				}
				
				$highlighted = '';
				if( array_key_exists( 'highlighted', $jobdata ) && $jobdata['highlighted'] )
					$highlighted = 'highlighted';
				
				$content .= "<tr class='row$rowcount job$job->ID $highlighted ";
				$content .= ( $rowcount % 2 )?( 'odd' ):( 'even' );
				$content .= "'><td><a href='" . get_page_link( $job->ID ) . "'>";
				
				if( $jobdata['iconid'] && array_key_exists( $jobdata['iconid'], $options['icons'] ) )
					$content .= '<img src="' . JOBMAN_UPLOAD_URL . '/icons/' . $jobdata['iconid'] . '.' . $options['icons'][$jobdata['iconid']]['extension'] . '" title="' . $options['icons'][$jobdata['iconid']]['title'] . '" /><br/>';

				$content .= $job->post_title . '</a></td>';
				$content .= '<td>' . $jobdata['salary'] . '</td>';
				if( '' == $jobdata['startdate'] || strtotime( $jobdata['startdate'] ) < time() )
					$asap = __( 'ASAP', 'jobman' );
				else
					$asap = $jobdata['startdate'];

				$content .= '<td>' . $asap . '</td>';
				$content .= '<td>' . $jobdata['location'] . '</td>';
				$content .= '<td class="jobs-moreinfo"><a href="' . get_page_link( $job->ID ) . '">' . __( 'More Info', 'jobman' ) . '</a></td></tr>';
				
				$rowcount++;
			}
			$content .= '</table>';
		}
		else {
		    $rowcount = 1;
			foreach( $jobs as $job ) {
				$job_html = jobman_display_job( $job );
				if( NULL != $job_html ) {
					$content .= "<div class='row$rowcount job$job->ID";
					$content .= ( $rowcount % 2 )?( 'odd' ):( 'even' );
					$content .= "'>" . $job_html[0]->post_content . '</div><br/><br/>';
					$rowcount++;
				}
			}
		}*/
		
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
			$content .= sprintf( __( "We currently don't have any jobs available in this area. Please check back regularly, as we frequently post new jobs. In the mean time, you can also <a href='%s'>send through your résumé</a>, which we'll keep on file, and you can check out the <a href='%s'>jobs we have available in other areas</a>.", 'jobman' ), $url, get_page_link( $options['main_page'] ) );
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
	if( array_key_exists( 'displayenddate', $jobdata ) && '' != $jobdata['displayenddate'] && strtotime($jobdata['displayenddate']) <= time() ) {
		if( 'summary' == $options['list_type'] )
			$job = NULL;
		else
			return NULL;
	}
	
	if( NULL == $job ) {
		$page = get_post( $options['main_page'] );
		$page->post_type = 'jobman_job';
		$page->post_title = __( 'This job doesn\'t exist', 'jobman' );

		$content .= '<p>' . sprintf( __( 'Perhaps you followed an out-of-date link? Please check out the <a href="%s">jobs we have currently available</a>.', 'jobman' ), get_page_link( $options['main_page'] ) ) . '</p>';
		
		$page->post_content = $content;
			
		return array( $page );
	}

	/*$categories = wp_get_object_terms( $job->ID, 'jobman_category' );
	
	$highlighted = '';
	if( array_key_exists( 'highlighted', $jobdata ) && $jobdata['highlighted'] )
		$highlighted = 'highlighted';
	
	$content .= "<table class='job-table $highlighted'>";
	$content .= '<tr><th scope="row">' . __( 'Title', 'jobman' ) . '</th><td>' . $job->post_title . '</td></tr>';
	if( count( $categories ) > 0 ) {
		$content .= '<tr><th scope="row">' . __( 'Categories', 'jobman' ) . '</th><td>';
		$cats = array();
		$ii = 1;
		
		foreach( $categories as $cat ) {
			$cats[] = '<a href="'. get_term_link( $cat->slug, 'jobman_category' ) . '" title="' . sprintf( __( 'Jobs for %s', 'jobman' ), $cat->name ) . '">' . $cat->name . '</a>';
		}
		
		if(count($cats) > 0)
			$content .= implode(', ', $cats);
	}
	$content .= '<tr><th scope="row">' . __( 'Salary', 'jobman' ) . '</th><td>' . $jobdata['salary'] . '</td></tr>';

	if( '' == $jobdata['startdate'] || strtotime( $jobdata['startdate'] ) < time() )
		$asap = __( 'ASAP', 'jobman' );
	else
		$asap = $jobdata['startdate'];

	$content .= '<tr><th scope="row">' . __( 'Start Date', 'jobman' ) . '</th><td>' . $asap . '</td></tr>';
	$content .= '<tr><th scope="row">' . __( 'End Date', 'jobman' ) . '</th><td>' . ( ( '' == $jobdata['enddate'] )?( __( 'Ongoing', 'jobman' ) ):( $jobdata['enddate'] ) ) . '</td></tr>';
	$content .= '<tr><th scope="row">' . __( 'Location', 'jobman' ) . '</th><td>' . $jobdata['location'] . '</td></tr>';
	$content .= '<tr><th scope="row">' . __( 'Information', 'jobman' ) . '</th><td>' . jobman_format_abstract( $job->post_content ) . '</td></tr>';

	$data = get_posts( 'post_type=jobman_app_form&numberposts=-1' );
	if( count( $data ) > 0 ) {
		$applypage = $data[0];
	
		$url = get_page_link( $applypage->ID );
		
		$structure = get_option( 'permalink_structure' );
		
		if( '' == $structure ) {
			$url .= '&amp;j=' . $job->ID;
		}
		else {
			if( substr( $url, -1 ) == '/' )
				$url .= $job->ID . '/';
			else
				$url .= '/' . $job->ID;
		}

		$content .= '<tr><td></td><td class="jobs-applynow"><a href="'. $url . '">' . __( 'Apply Now!', 'jobman' ) . '</td></tr>';
	}
	
	$content .= '</table>';*/

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