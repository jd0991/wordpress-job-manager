<?php
function jobman_display_apply( $jobid, $cat = NULL ) {
	global $current_user, $si_image_captcha;
	get_currentuserinfo();

	$options = get_option( 'jobman_options' );

	$content = '';
	
	$data = get_posts( 'post_type=jobman_app_form&numberposts=-1' );
	if( count( $data ) > 0) {
		$page = $data[0];
	}
	else {
		$page = new stdClass;
		$page->post_author = 1;
		$page->post_date = time();
		$page->post_type = 'page';
		$page->post_status = 'published';
	}

	if( array_key_exists( 'jobman-apply', $_REQUEST ) ) {
		if( isset( $si_image_captcha ) && $options['plugins']['sicaptcha'] ) {
			$fake_comment = array( 'comment_type' => 'comment' );
			// No need to check return - will wp_die() if CAPTCHA failed
			$si_image_captcha->si_captcha_comment_post( $fake_comment );
		}
		$err = jobman_store_application( $jobid, $cat );
		switch( $err ) {
			case -1:
				// No error, stored properly
				$msg = $options['text']['application_acceptance'];
				break;
			default:
				// Failed filter rules
				$msg = $options['fields'][$err]['error'];
				
				if( NULL == $msg || '' == $msg )
					$msg = __( "Thank you for your application. While your application doesn't fit our current requirements, please contact us directly to see if we have other positions available.", 'jobman' );
				
				break;
		}
		
		$page->post_title = __( 'Job Application', 'jobman' );
		$page->post_content .= "<div class='jobman-message'>$msg</div>";
		
		return array( $page );
	}

	if( $options['user_registration'] && ( $options['loginform_apply'] || $options['user_registration_required'] ) )
		$content .= jobman_display_login();
		
	if( $options['user_registration'] && $options['user_registration_required'] && ! is_user_logged_in() ) {
		// Skip the application form if the user hasn't registered yet, and we're enforcing registration. 
		
		$content .= '<p>' . __( 'Before completing your application, please login using the form above, or register using the form below.', 'jobman' ) . '</p>';
		
		$reg = jobman_display_register();
		$content .= $reg[0]->post_content;
		
		$page->post_content = $content;
			
		return array( $page );
	}

	if( $jobid > 0 )
		$job = get_post( $jobid );
	else
		$job = NULL;
	
	$cat_arr = array();

	if( NULL != $job ) {
		$page->post_title = __( 'Job Application', 'jobman' ) . ': ' . $job->post_title;
		$foundjob = true;
		$jobid = $job->ID;
		
		$categories = wp_get_object_terms( $job->ID, 'jobman_category' );
		if( count( $categories ) > 0 ) {
			foreach( $categories as $cat ) {
				$cat_arr[] = $cat->term_id;
			}
		}
	}
	else {
		$page->post_title = __( 'Job Application', 'jobman' );
		$foundjob = false;
		$jobid = -1;
		if( NULL != $cat ) {
			$data = get_term_by( 'slug', $cat, 'jobman_category' );
			if( isset( $data->term_id ) )
				$cat_arr[] = $data->term_id;
		}
	}
	
	$content .= '<form action="" enctype="multipart/form-data" onsubmit="return jobman_apply_filter()" method="post">';
	$content .= '<input type="hidden" name="jobman-apply" value="1">';
	$content .= '<input type="hidden" name="jobman-jobid" value="' . $jobid . '">';
	$content .= '<input type="hidden" name="jobman-categoryid" value="' . implode( ',', $cat_arr ) . '">';
	
	if( $foundjob )
		$content .= '<p>' . __( 'Title', 'jobman' ) . ': <a href="'. get_page_link( $job->ID ) . '">' . $job->post_title . '</a></p>';

	$fields = $options['fields'];

	$start = true;
	
	$content .= '<p>' . __( 'Fields marked with an asterisk (*) must be filled out before submitting.', 'jobman' ) . '</p>';
	
	if( count( $fields ) > 0 ) {
		uasort( $fields, 'jobman_sort_fields' );
		$rowcount = 1;
		$totalrowcount = 1;
		$tablecount = 1;
		foreach( $fields as $id => $field ) {
			if( array_key_exists( 'categories', $field ) && count( $field['categories'] ) > 0 ) {
				// If there are cats defined for this field, check that either the job has one of those categories, or we're submitting to that category
				if( count( array_intersect( $field['categories'], $cat_arr ) ) <= 0)
					continue;
			}
			
			if( $start && 'heading' != $field['type'] ) {
				$content .= "<table class='job-apply-table table$tablecount'>";
				$tablecount++;
				$rowcount = 1;
			}

			$data = strip_tags( $field['data'] );

			// Auto-populate logged in user email address
			if( $id == $options['application_email_from'] && '' == $data && is_user_logged_in() ) {
			    $data = $current_user->user_email;
			}
			
			if( 'heading' != $field['type'] ) {
				$content .= "<tr class='row$rowcount totalrow$totalrowcount field$id ";
				$content .= ( $rowcount % 2 )?( 'odd' ):( 'even' );
				$content .= "'>";
			}
			
			$mandatory = '';
			if( $field['mandatory'] )
				$mandatory = ' *';
			
			switch( $field['type'] ) {
				case 'text':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}$mandatory</th>";
					else
						$content .= '<td class="th"></td>';
					
					$content .= "<td><input type='text' name='jobman-field-$id' value='$data' /></td></tr>";
					break;
				case 'radio':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}$mandatory</th><td>";
					else
						$content .= '<td class="th"></td><td>';
					
					$values = split( "\n", $data );
					$display_values = split( "\n", $field['data'] );
					
					foreach( $values as $key => $value ) {
						$content .= "<input type='radio' name='jobman-field-$id' value='" . trim( $value ) . "' /> {$display_values[$key]}<br/>";
					}
					$content .= '</td></tr>';
					break;
				case 'checkbox':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}$mandatory</th><td>";
					else
						$content .= '<td class="th"></td><td>';

					$values = split( "\n", $data );
					$display_values = split( "\n", $field['data'] );
					
					foreach( $values as $key => $value ) {
						$content .= "<input type='checkbox' name='jobman-field-{$id}[]' value='" . trim( $value ) . "' /> {$display_values[$key]}<br/>";
					}
					$content .= '</td></tr>';
					break;
				case 'textarea':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}$mandatory</th>";
					else
						$content .= '<td class="th"></td>';

					$content .= "<td><textarea name='jobman-field-$id'>{$field['data']}</textarea></td></tr>";
					break;
				case 'date':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}$mandatory</th>";
					else
						$content .= '<td class="th"></td>';

					$content .= "<td><input type='text' class='datepicker' name='jobman-field-$id' value='$data' /></td></tr>";
					break;
				case 'file':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}$mandatory</th>";
					else
						$content .= '<td class="th"></td>';

					$content .= "<td><input type='file' name='jobman-field-$id' /></td></tr>";
					break;
				case 'heading':
					if( ! $start )
						$content .= '</table>';

					$content .= "<h3>{$field['label']}</h3>";
					$content .= "<table class='job-apply-table table$tablecount'>";
					$tablecount++;
					$totalrowcount--;
					$rowcount = 0;
					break;
				case 'html':
					$content .= "<td colspan='2'>{$field['data']}</td></tr>";
					break;
				case 'blank':
					$content .= '<td colspan="2">&nbsp;</td></tr>';
					break;
			}
			$start = false;
			$rowcount++;
			$totalrowcount++;
		}
	}
	
	$content .= '<tr><td colspan="2">&nbsp;</td></tr>';
	if( isset( $si_image_captcha ) && $options['plugins']['sicaptcha'] ) {
		// SI CAPTCHA echos directly to screen. We need to redirect that to our $content buffer.
		ob_start();
		$si_image_captcha->si_captcha_comment_form();
		$content .= '<tr><td colspan="2">' . ob_get_contents() . '</td></tr>';
		ob_end_clean();
	}
	$content .= '<tr><td colspan="2" class="submit"><input type="submit" name="submit"  class="button-primary" value="' . __( 'Submit Your Application', 'jobman' ) . '" /></td></tr>';
	$content .= '</table>';

	$page->post_content = $content;
		
	return array( $page );
}

function jobman_store_application( $jobid, $cat ) {
	$filter_err = jobman_check_filters( $jobid, $cat );
	if($filter_err != -1) {
		// Failed filter rules
		return $filter_err;
	}

	$dir = dirname( $_SERVER['SCRIPT_FILENAME'] );
	require_once( "$dir/wp-admin/includes/image.php" );

	$options = get_option( 'jobman_options' );
	
	$parent = $options['main_page'];
	
	$job = NULL;
	if( -1 != $jobid ) {
		$job = get_post( $jobid );
		if( NULL != $job )
			$parent = $job->ID;
	}
	
	$fields = $options['fields'];
	
	// Workaround for WP to Twitter plugin tweeting about new application
	$_POST['jd_tweet_this'] = 'no';
	
	$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'private',
				'post_type' => 'jobman_app',
				'post_content' => '',
				'post_title' => __( 'Application', 'jobman' ),
				'post_parent' => $parent
			);

	$appid = wp_insert_post( $page );

	// Add the categories to the page
	$append = false;
	if( NULL != $cat && is_term( $cat->slug, 'jobman_category' ) ) {
		wp_set_object_terms( $appid, $cat->slug, 'jobman_category', false );
		$append = true;
	}

	if( NULL != $job ) {
		// Get parent (job) categories, and apply them to application
		$parentcats = wp_get_object_terms( $job->ID, 'jobman_category' );
		foreach( $parentcats as $pcat ) {
			if( is_term( $pcat->slug, 'jobman_category' ) ) {
				wp_set_object_terms( $appid, $pcat->slug, 'jobman_category', $append );
				$append = true;
			}
		}
	}
	
	if( count( $fields ) > 0 ) {
		foreach( $fields as $fid => $field ) {
			if($field['type'] != 'file' && ( ! array_key_exists( "jobman-field-$fid", $_REQUEST ) || '' == $_REQUEST["jobman-field-$fid"] ) )
				continue;
			
			if( 'file' == $field['type'] && ! array_key_exists( "jobman-field-$fid", $_FILES ) )
				continue;
			
			$data = '';
			switch( $field['type'] ) {
				case 'file':
					if( is_uploaded_file( $_FILES["jobman-field-$fid"]['tmp_name'] ) ) {
							$upload = wp_upload_bits( $_FILES["jobman-field-$fid"]['name'], NULL, file_get_contents( $_FILES["jobman-field-$fid"]['tmp_name'] ) );
							if( ! $upload['error'] ) {
								$filetype = wp_check_filetype( $upload['file'] );
								$attachment = array(
												'post_title' => '',
												'post_content' => '',
												'post_status' => 'private',
												'post_mime_type' => $filetype['type']
											);
								$data = wp_insert_attachment( $attachment, $upload['file'], $appid );
								$attach_data = wp_generate_attachment_metadata( $data, $upload['file'] );
								wp_update_attachment_metadata( $data, $attach_data );

								add_post_meta( $data, '_jobman_attachment', 1, true );
								add_post_meta( $data, '_jobman_attachment_upload', 1, true );
							}
					}
					break;
				case 'checkbox':
					$data = implode( ', ', $_REQUEST["jobman-field-$fid"] );
					break;
				default:
					$data = $_REQUEST["jobman-field-$fid"];
			}
			
			add_post_meta( $appid, "data$fid", $data, true );
		}
	}
	
	jobman_email_application( $appid );

	// No error
	return -1;
}

function jobman_check_filters( $jobid, $cat ) {
	$options = get_option( 'jobman_options' );
	
	$fields = $options['fields'];
	
	$matches = array();
	if( count( $fields ) > 0 ) {
		foreach( $fields as $id => $field ) {
			if( '' == $field['filter'] && ! $field['mandatory'] )
				// No filter for this field, not mandatory
				continue;
			
			$used_eq = false;
			$eqflag = false;
			
			$data = '';
			if( array_key_exists( "jobman-field-$id", $_REQUEST ) )
				$data = $_REQUEST["jobman-field-$id"];

			if( 'checkbox' != $field['type'] )
				$data = trim( $data );
			else if( ! is_array( $data ) )
				$data = array();

			// If the field is mandatory, check that there is data submitted
			if( $field['mandatory'] ) {
				if( 'file' == $field['type'] ) {
					if ( ! array_key_exists( "jobman-field-$id", $_FILES ) )
						return $id;
				}
				else if( '' == $data || ( is_array( $data ) && count( $data ) == 0 ) )
					return $id;
			}
			
			if( '' == $field['filter'] )
				// No filter for this field, and mandatory check has passed
				continue;
				
			$filters = split( "\n", $field['filter'] );
			
			foreach($filters as $filter) {
				$filter = trim( $filter );
				
				// Date
				if( 'date' == $field['type'] ) {
					$data = strtotime($data);

					// [<>][+-]P(\d+Y)?(\d+M)?(\d+D)?
					if( preg_match( '/^([<>])([+-])P(\d+Y)?(\d+M)?(\d+D)?$/', $filter, $matches ) ) {
						$intervalstr = $matches[2];
						for( $ii = 3; $ii < count($matches); $ii++ ) {
							$interval = array();
							preg_match( '/(\d+)([YMD])/', $matches[$ii], $interval );
							switch( $interval[2] ) {
								case 'Y':
									$intervalstr .= $interval[1] . ' years ';
									break;
								case 'M':
									$intervalstr .= $interval[1] . ' months ';
									break;
								case 'D':
									$intervalstr .= $interval[1] . ' days ';
									break;
							}
						}
						
						$cmp = strtotime( $intervalstr );

						switch( $matches[1] ) {
							case '<':
								if( $cmp > $data )
									return $id;
								break;
							case '>':
								if( $cmp < $data )
									return $id;
								break;
						}
						
						break;
					}
				}

				preg_match( '/^([<>]=?|[!]|)(.+)/', $filter, $matches );
				if( 'date' == $field['type'] )
					$fdata = strtotime($matches[2]);
				else
					$fdata = $matches[2];
				
				if( 'checkbox' != $field['type'] ) {
					switch( $matches[1] ) {
						case '<=':
							if( $data > $fdata )
								return $id;
							break;
						case '>=':
							if( $data > $fdata )
								return $id;
							break;
						case '<':
							if( $data >= $fdata )
								return $id;
							break;
						case '>':
							if( $data <= $fdata )
								return $id;
							break;
						case '!':
							if( $data == $fdata )
								return $id;
							break;
						default:
							$used_eq = true;
							if( $data == $fdata ) {
								$eqflag = true;
								break 2;
							}
							break;
					}
				}
				else {
					switch( $matches[1] ) {
						case '!':
							if( in_array( $fdata, $data ) )
								return $id;
							break;
						default:
							$used_eq = true;
							if( in_array( $fdata, $data ) ) {
								$eqflag = true;
								break 2;
							}
							break;
					}
				}
			}
			
			if( $used_eq && ! $eqflag )
				return $id;

			$used_eq = false;
			$eqflag = false;
		}
	}

	return -1;
}

function jobman_email_application( $appid, $sendto = '' ) {
	$options = get_option( 'jobman_options' );

	$app = get_post( $appid );
	if( NULL == $app )
		return;
	
	$parent = get_post( $app->ancestors[0] );
	$job_email = '';
	if( 'jobman_job' == $parent->post_type )
		$job_email = get_post_meta( $parent->ID, 'email', true );

	$appmeta = get_post_custom( $appid );

	$appdata = array();
	foreach( $appmeta as $key => $value ) {
		if( is_array( $value ) )
			$appdata[$key] = $value[0];
		else
			$appdata[$key] = $value;
	}

	$categories = wp_get_object_terms( $appid, 'jobman_category' );
	
	$to = '';
	if( '' != $sendto ) {
	    $to = $sendto;
	}
	else if( '' != $job_email ) {
	    $to = $job_email;
	}
	else if( count( $categories ) > 0 ) {
		$ii = 1;
		foreach( $categories as $cat ) {
			$to .= $cat->description;
			if( $ii < count( $categories ) )
				$to .= ', ';
		}
	} else {
		$to = $options['default_email'];
	}
	
	if( '' == $to )
		return;
	
	$fromid = $options['application_email_from'];
	$from = '';
	
	if('' == $fromid )
		$from = $options['default_email'];
	else if( array_key_exists( "data$fromid", $appdata ) )
		$from = $appdata["data$fromid"];
	
	if( '' == $from )
		$from = get_option( 'admin_email' );
	
	$fids = $options['application_email_from_fields'];

	$fromname = '';
	if( count( $fids ) > 0 ) {
		foreach( $fids as $fid ) {
			if( array_key_exists( "data$fid", $appdata ) && '' != $appdata["data$fid"] )
				$fromname .= $appdata["data$fid"] . ' ';
		}
	}
	$fromname = trim( $fromname );
	
	$from = "\"$fromname\" <$from>";
	
	$subject = $options['application_email_subject_text'];
	if( '' != $subject )
		$subject .= ' ';

	$fids = $options['application_email_subject_fields'];

	if( count( $fids ) > 0 ) {
		foreach( $fids as $fid ) {
			if( array_key_exists( "data$fid", $appdata ) && '' != $appdata["data$fid"] )
				$subject .= $appdata["data$fid"] . ' ';
		}
	}
	
	$msg = '';
	
	$msg .= __( 'Application Link', 'jobman' ) . ': ' . admin_url( 'admin.php?page=jobman-list-applications&amp;appid=' . $app->ID ) . PHP_EOL;

	$parent = get_post( $app->post_parent );
	if( NULL != $parent && 'jobman_job' == $parent->post_type ) {
		$msg .= __( 'Job', 'jobman' ) . ': ' . $parent->ID . ' - ' . $parent->post_title . PHP_EOL;
		$msg .= get_page_link( $parent->ID ) . PHP_EOL;
	}
	
	$msg .= __( 'Timestamp', 'jobman' ) . ': ' . $app->post_date . PHP_EOL . PHP_EOL;
	
	$fields = $options['fields'];
	
	if( count( $fields ) > 0 ) {
		uasort( $fields, 'jobman_sort_fields' );
		foreach( $fields as $id => $field ) {
			if( ! array_key_exists("data$id", $appdata ) || '' == $appdata["data$id"] )
				continue;

			switch( $field['type'] ) {
				case 'text':
				case 'radio':
				case 'checkbox':
				case 'date':
					$msg .= $field['label'] . ': ' . $appdata['data'.$id] . PHP_EOL;
					break;
				case 'textarea':
					$msg .= $field['label'] . ':' . PHP_EOL . $appdata['data'.$id] . PHP_EOL;
					break;
				case 'file':
					$msg .= $field['label'] . ': ' . wp_get_attachment_url( $appdata["data$id"] ) . PHP_EOL;
					break;
			}
		}
	}

	$header = "From: $from" . PHP_EOL;
	$header .= "Reply-To: $from" . PHP_EOL;
	$header .= "Return-Path: $from" . PHP_EOL;
	$header .= 'Content-type: text/plain; charset='. get_option( 'blog_charset' ) . PHP_EOL;
	
	wp_mail( $to, $subject, $msg, $header );
}

?>