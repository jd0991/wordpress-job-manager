<?php

global $jobman_shortcode_jobs, $jobman_shortcode_job, $jobman_shortcode_categories;

function jobman_add_shortcodes( $array ) {
	foreach ( (array) $array as $shortcode ) {
		$conditional = 'if_' . $shortcode;
		add_shortcode( $shortcode, 'jobman_shortcode' );
		add_shortcode( $conditional, 'jobman_shortcode_conditional' );
	}
}

function jobman_remove_shortcodes( $array ) {
	foreach ( (array) $array as $shortcode ) {
		$conditional = 'if_' . $shortcode;
		remove_shortcode( $shortcode );
		remove_shortcode( $conditional );
	}
}

function jobman_add_field_shortcodes( $array ) {
	foreach ( (array) $array as $shortcode ) {
		$conditional = 'if_' . $shortcode;
		
		$label = $shortcode . '_label';
		$cond_label = 'if_' . $shortcode . '_label';
		
		add_shortcode( $shortcode, 'jobman_field_shortcode' );
		add_shortcode( $conditional, 'jobman_field_shortcode_conditional' );
		
		add_shortcode( $label, 'jobman_field_shortcode' );
		add_shortcode( $cond_label, 'jobman_field_shortcode_conditional' );
	}
}

function jobman_add_app_shortcodes( $array ) {
	foreach ( (array) $array as $shortcode ) {
		$conditional = 'if_' . $shortcode;
		add_shortcode( $shortcode, 'jobman_app_shortcode' );
		add_shortcode( $conditional, 'jobman_app_shortcode_conditional' );
	}
}

function jobman_add_app_field_shortcodes( $array ) {
	foreach ( (array) $array as $shortcode ) {
		$conditional = 'if_' . $shortcode;
		
		$label = $shortcode . '_label';
		$cond_label = 'if_' . $shortcode . '_label';
		
		$mandatory = $shortcode . '_mandatory';
		$cond_mandatory = 'if_' . $shortcode . '_mandatory';
		
		add_shortcode( $shortcode, 'jobman_app_field_shortcode' );
		add_shortcode( $conditional, 'jobman_app_field_shortcode_conditional' );
		
		add_shortcode( $label, 'jobman_app_field_shortcode' );
		add_shortcode( $cond_label, 'jobman_app_field_shortcode_conditional' );

		add_shortcode( $mandatory, 'jobman_app_field_shortcode' );
		add_shortcode( $cond_mandatory, 'jobman_app_field_shortcode_conditional' );
	}
}

global $jobman_shortcode_row_number, $jobman_shortcode_field_id, $jobman_shortcode_field;
							
function jobman_shortcode( $atts, $content, $tag ) {
	global $jobman_shortcode_jobs, $jobman_shortcode_job, $jobman_shortcode_row_number, $jobman_shortcode_field_id, $jobman_shortcode_field;
	$options = get_option( 'jobman_options' );

	$return = '';
	switch ( $tag ) {
		case 'job_loop':
			if( NULL == $jobman_shortcode_jobs || ! count( $jobman_shortcode_jobs ) )
				return do_shortcode( $content );
			
			$jobman_shortcode_row_number = 1;
			foreach( $jobman_shortcode_jobs as $job ) {
				$jobman_shortcode_job = $job;
				$return .= do_shortcode( $content );
				$jobman_shortcode_row_number++;
			}
			
			$jobman_shortcode_job = NULL;
			
			return $return;
		case 'job_row_number':
			return $jobman_shortcode_row_number;
		case 'job_id':
			return $jobman_shortcode_job->ID;
		case 'job_highlighted':
			$highlighted = get_post_meta( $jobman_shortcode_job->ID, 'highlighted', true );
			if( $highlighted )
				return 'highlighted';
			else
				return NULL;
		case 'job_odd_even':
			return ( $jobman_shortcode_row_number % 2 )?( 'odd' ):( 'even' );
		case 'job_link':
			return '<a href="' . get_page_link( $jobman_shortcode_job->ID ) . '">' . do_shortcode( $content ) . '</a>';
		case 'job_title':
			return $jobman_shortcode_job->post_title;
		case 'job_icon':
			$icon = get_post_meta( $jobman_shortcode_job->ID , 'iconid', true );
			if( ! $icon )
				return NULL;
			
			$post = get_post( $icon );
			$url = wp_get_attachment_url( $icon );
			return "<img src='$url' title='$post->post_title' />";
		case 'job_categories':
			$categories = wp_get_object_terms( $jobman_shortcode_job->ID, 'jobman_category' );
			if( ! count( $categories ) )
				return NULL;
			
			$cats = array();
			foreach( $categories as $cat )
				$cats[] = $cat->name;
			
			return implode( ', ', $cats );
		case 'job_category_links':
			$categories = wp_get_object_terms( $jobman_shortcode_job->ID, 'jobman_category' );
			if( ! count( $categories ) )
				return NULL;
			
			$cats = array();
			foreach( $categories as $cat )
				$cats[] = '<a href="'. get_term_link( $cat->slug, 'jobman_category' ) . '" title="' . sprintf( __( 'Jobs for %s', 'jobman' ), $cat->name ) . '">' . $cat->name . '</a>';;
			
			return implode( ', ', $cats );
		case 'job_field_loop':
			foreach( $options['job_fields'] as $fid => $field ) {
				$jobman_shortcode_field_id = $fid;
				$jobman_shortcode_field = $field;
				$return .= do_shortcode( $content );
			}
			
			$jobman_shortcode_field_id = NULL;
			$jobman_shortcode_field = NULL;
			return $return;
		case 'job_field':
			$data = get_post_meta( $jobman_shortcode_job->ID, 'data' . $jobman_shortcode_field_id, true );
			
			if( empty( $data ) )
				return NULL;
			
			switch( $jobman_shortcode_field['type'] ) {
				case 'textarea':
					return wpautop( $data );
				case 'file':
					return '<a href="' . wp_get_attachment_url( $data ) . '">' . __( 'Download', 'jobman' ) . '</a>';
				default:
					return $data;
			}
		case 'job_field_label':
			return $jobman_shortcode_field['label'];
		case 'job_apply_link':
			$data = get_posts( 'post_type=jobman_app_form&numberposts=-1' );
			if( count( $data ) > 0 ) {
				$applypage = $data[0];
			
				$url = get_page_link( $applypage->ID );
				
				if( ! $jobman_shortcode_job )
					return '<a href="'. $url . '">' . do_shortcode( $content ) . '</a>';
				
				$structure = get_option( 'permalink_structure' );
				
				if( '' == $structure ) {
					$url .= '&amp;j=' . $jobman_shortcode_job->ID;
				}
				else {
					if( substr( $url, -1 ) == '/' )
						$url .= $jobman_shortcode_job->ID . '/';
					else
						$url .= '/' . $jobman_shortcode_job->ID;
				}

				return '<a href="'. $url . '">' . do_shortcode( $content ) . '</a>';
			}
			return NULL;
		case 'job_checkbox':
			if( $options['multi_applications'] ) {
				return "<input type='checkbox' name='jobman-joblist[]' value='$jobman_shortcode_job->ID' />";
			}
			return NULL;
		case 'job_apply_multi':
			if( $options['multi_applications'] ) {
				return '<input type="submit" name="submit" value="' . do_shortcode( $content ) . '" />';
			}
			return NULL;
	}
	
	return do_shortcode( $content );
}

function jobman_shortcode_conditional( $atts, $content, $tag ) {
	$test_tag = preg_replace( '#^if_#', '', $tag );
	$test_output = jobman_shortcode( NULL, NULL, $test_tag );
	if ( ! empty( $test_output ) )
		return do_shortcode( $content );
}

function jobman_field_shortcode( $atts, $content, $tag ) {
	global $jobman_shortcode_job;
	$options = get_option( 'jobman_options' );
	
	$matches = array();
	preg_match( '#^job_field(\d+)(_label)?#', $tag, $matches );
	
	if( array_key_exists( 2, $matches ) )
		return $options['job_fields'][$matches[1]]['label'];
	
	$data = get_post_meta( $jobman_shortcode_job->ID, 'data' . $matches[1], true );

	if( empty( $data ) )
		return NULL;
	
	switch( $options['job_fields'][$matches[1]]['type'] ) {
		case 'textarea':
			return wpautop( $data );
		case 'file':
			return '<a href="' . wp_get_attachment_url( $data ) . '">' . __( 'Download', 'jobman' ) . '</a>';
		default:
			return $data;
	}
}

function jobman_field_shortcode_conditional( $atts, $content, $tag ) {
	$test_tag = preg_replace( '#^if_#', '', $tag );
	$test_output = jobman_field_shortcode( NULL, NULL, $test_tag );
	if ( !empty( $test_output ) )
		return do_shortcode( $content );
}

function jobman_app_shortcode( $atts, $content, $tag ) {
	global $jobman_shortcode_job, $jobman_shortcode_categories;
	
	$options = get_option( 'jobman_options' );
	
	switch( $tag ) {
		case 'job_links':
			$jobs = array();
			if( NULL != $jobman_shortcode_job )
				$jobs[] = $jobman_shortcode_job->ID;
				
			if( array_key_exists( 'jobman-joblist', $_REQUEST ) )
				$jobs = array_merge( $jobs, $_REQUEST['jobman-joblist'] );
				
			if( empty( $jobs ) )
				return NULL;
				
			$jobstr = array();
			foreach( $jobs as $job ) {
				$data = get_post( $job );
				if( empty( $data ) )
					continue;
					
				$jobstr[] = "<a href='" . get_page_link( $data->ID ) . "'>$data->post_title</a>";
			}
			
			return implode( ', ', $jobstr );
		case 'job_app_submit':
			return '<input type="submit" name="submit"  class="button-primary" value="' . do_shortcode( $content ) . '" />';
		case 'job_list':
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
			
			if( empty( $jobman_shortcode_categories ) ) {
				$jobs = get_posts( "post_type=jobman_job&numberposts=-1$sortby$sortorder" );
			}
			else {
				$cat = $jobman_shortcode_categories[0];
				$jobs = get_posts( "post_type=jobman_job&jcat=$cat&numberposts=-1$sortby$sortorder" );
			}
			
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
			}
			
			if( 'sticky' == $options['highlighted_behaviour'] )
				// Sort the sticky jobs to the top
				uasort( $jobs, 'jobman_sort_highlighted_jobs' );
				
			$content = '<span id="jobman-jobselect">';

			$atts = shortcode_atts( array( 'type' => 'select' ), $atts );
			
			$inputtype = 'radio';
			$inputarray = '';
			$selectsize = 1;
			$selectmultiple = '';
			if( $options['multi_applications'] ) {
				$inputtype = 'checkbox';
				$inputarray = '[]';
				$selectsize = 5;
				$selectmultiple = ' multiple="multiple"';
			}
			
			$style = '';
			$class = '';
			$closebutton = '';
			if( 'popout' == $atts['type'] ) {
				$style = 'display: none;';
				$class = 'jobselect-popout';
				$content .= '<span id="jobman-jobselect-echo"></span>';
				$closebutton = '<span id="jobman-jobselect-close"><a href="#">[x]</a></span>';
			}
			
			switch( $atts['type'] ) {
				case 'popout':
				case 'individual':
					$content .= "<span style='$style' class='$class'>";
					$content .= $closebutton;
					foreach( $jobs as $job ) {
						$checked = '';
						if( array_key_exists( 'jobman-joblist', $_REQUEST ) && in_array( $job->ID, $_REQUEST['jobman-joblist'] ) )
							$checked = ' checked="checked"';
						$content .= "<span><input type='$inputtype' name='jobman-jobselect$inputarray' title='$job->post_title' value='$job->ID'$checked /> $job->post_title</span>";
					}
					$content .= '</span>';
					break;
				case 'select':
				default:
					$content .= "<select name='jobman-jobselect$inputarray'$selectmultiple>";
					$content .= '<option value="">' . _e( 'None', 'jobman' ) . '</option>';
					foreach( $jobs as $job ) {
						$selected = '';
						if( array_key_exists( 'jobman-joblist', $_REQUEST ) && in_array( $job->ID, $_REQUEST['jobman-joblist'] ) )
							$selected = ' selected="selected"';
						$content .= "<option value='$job->ID'$selected>$job->post_title</option>";
					}
					$content .= '</select>';
			}
			
			$content .= '</span>';
			
			return $content;
	}
}

function jobman_app_field_shortcode( $atts, $content, $tag ) {
	global $jobman_shortcode_app_field, $current_user;
	$options = get_option( 'jobman_options' );
	
	$matches = array();
	preg_match( '#^job_app_field(\d+)(_label)?#', $tag, $matches );
	
	if( array_key_exists( 2, $matches ) )
		return $options['fields'][$matches[1]]['label'];
	
	preg_match( '#^job_app_field(\d+)(_mandatory)?#', $tag, $matches );
	if( array_key_exists( 2, $matches ) )
		if( $options['fields'][$matches[1]]['mandatory'] )
			return 'mandatory';
		else
			return NULL;

	preg_match( '#^job_app_field(\d+)#', $tag, $matches );
	if( ! array_key_exists( 1, $matches ) )
		return NULL;

	$id = $matches[1];
	$field = $options['fields'][$id];
	$data = strip_tags( $field['data'] );

	// Auto-populate logged in user email address
	if( $id == $options['application_email_from'] && '' == $data && is_user_logged_in() ) {
		$data = $current_user->user_email;
	}
	
	return jobman_app_field_input_html( $id, $field, $data );
}

function jobman_app_field_shortcode_conditional( $atts, $content, $tag ) {
	$test_tag = preg_replace( '#^if_#', '', $tag );
	$test_output = jobman_app_field_shortcode( NULL, NULL, $test_tag );
	if ( !empty( $test_output ) )
		return do_shortcode( $content );
}

?>