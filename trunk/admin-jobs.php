<?php 
function jobman_list_jobs() {
	$displayed = 1;

	if( array_key_exists( 'jobman-mass-edit-jobs', $_REQUEST ) && 'delete' == $_REQUEST['jobman-mass-edit-jobs'] ) {
		if( array_key_exists( 'jobman-delete-confirmed', $_REQUEST ) ) {
			check_admin_referer( 'jobman-mass-delete-jobs' );
			jobman_job_delete();
			$deleted = true;
		}
		else {
			check_admin_referer( 'jobman-mass-edit-jobs' );
			jobman_job_delete_confirm();
			return;
		}
	}
	else if( isset( $_REQUEST['jobman-jobid'] ) ) {
		$displayed = jobman_edit_job( $_REQUEST['jobman-jobid'] );
		if( 1 == $displayed )
			return;
	}


?>
	<div class="wrap">
		<h2><?php _e( 'Job Manager: Jobs List', 'jobman' ) ?></h2>
		<form action="" method="post">
		<input type="hidden" name="jobman-jobid" value="new" />
		<p class="submit"><input type="submit" name="submit" class="button-primary" value="<?php _e( 'New Job', 'jobman' ) ?>" /></p>
		</form>
<?php
	switch($displayed) {
		case 0:
			echo '<div class="error">' . __( 'There is no job associated with that Job ID', 'jobman' ) . '</div>';
			break;
		case 2:
			echo '<div class="error">' . __( 'New job created', 'jobman' ) . '</div>';
			break;
		case 3:
			echo '<div class="error">' . __( 'Job updated', 'jobman' ) . '</div>';
			break;
	}
	
	$jobs = get_posts( 'post_type=jobman_job&numberposts=-1' );
?>
		<form action="" method="post">
<?php 
	wp_nonce_field( 'jobman-mass-edit-jobs' ); 
?>
		<table id="jobman-jobs-list" class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" id="cb" class="column-cb check-column"><input type="checkbox"></th>
				<th scope="col"><?php _e( 'Title', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Categories', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Display Dates', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Applications', 'jobman' ) ?></th>
			</tr>
			</thead>
<?php
	if( count( $jobs ) > 0 ) {
		$expired = jobman_list_jobs_data( $jobs, false );
		jobman_list_jobs_data( $expired, true );
	}
	else {
?>
			<tr>
				<td colspan="5"><?php _e( 'There are currently no jobs in the system.', 'jobman' ) ?></td>
			</tr>
<?php
	}
?>
		</table>
		<div class="alignleft actions">
			<select name="jobman-mass-edit-jobs">
				<option value=""><?php _e( 'Bulk Actions', 'jobman' ) ?></option>
				<option value="delete"><?php _e( 'Delete', 'jobman' ) ?></option>
			</select>
			<input type="submit" value="<?php _e( 'Apply', 'jobman' ) ?>" name="submit" class="button-secondary action" />
		</div>
		</form>
	</div>
<?php
}

function jobman_list_jobs_data( $jobs, $showexpired = false ) {
		if( ! is_array( $jobs ) || count( $jobs ) <= 0 )
			return;

		$expiredjobs = array();
		foreach( $jobs as $job ) {
			$cats = wp_get_object_terms( $job->ID, 'jobman_category' );
			$cats_arr = array();
			if( count( $cats ) > 0 ) {
				foreach( $cats as $cat ) {
					$cats_arr[] = $cat->name;
				}
			}
			$catstring = implode( ', ', $cats_arr );
			
			$displayenddate = get_post_meta( $job->ID, 'displayenddate', true );
			
			$display = false;
			if( '' == $displayenddate || strtotime( $displayenddate ) > time() )
				$display = true;
				
			if( ! ( $display || $showexpired ) ) {
				$expiredjobs[] = $job;
				continue;
			}
			
			$children = get_posts( "post_type=jobman_app&post_parent=$job->ID" );
			if( count( $children ) > 0 )
				$applications = '<a href="' . admin_url("admin.php?page=jobman-list-applications&amp;jobman-jobid=$job->ID") . '">' . count( $children ) . '</a>';
			else
				$applications = count( $children );
?>
			<tr>
				<th scope="row" class="check-column"><input type="checkbox" name="job[]" value="<?php echo $job->ID ?>" /></th>
				<td class="post-title page-title column-title"><strong><a href="?page=jobman-list-jobs&amp;jobman-jobid=<?php echo $job->ID ?>"><?php echo $job->post_title ?></a></strong>
				<div class="row-actions"><a href="?page=jobman-list-jobs&amp;jobman-jobid=<?php echo $job->ID ?>"><?php _e( 'Edit', 'jobman' ) ?></a> | <a href="<?php echo get_page_link( $job->ID ) ?>"><?php _e( 'View', 'jobman' ) ?></a></div></td>
				<td><?php echo $catstring ?></td>
				<td><?php echo date( 'Y-m-d', strtotime( $job->post_date ) ) ?> - <?php echo ( '' == $displayenddate )?( __( 'End of Time', 'jobman' ) ):( $displayenddate ) ?><br/>
				<?php echo ( $display )?( __( 'Live/Upcoming', 'jobman' ) ):( __( 'Expired', 'jobman' ) ) ?></td>
				<td><?php echo $applications ?></td>
			</tr>
<?php
		}
		return $expiredjobs;
}

function jobman_add_job() {
	jobman_edit_job( 'new' );
}

function jobman_edit_job( $jobid ) {
	$options = get_option( 'jobman_options' );
	
	if( array_key_exists( 'jobmansubmit', $_REQUEST ) ) {
		// Job form has been submitted. Update the database.
		check_admin_referer( "jobman-edit-job-$jobid" );
		jobman_updatedb();
		if( 'new' == $jobid )
			return 2;
		else
			return 3;
	}
	
	if( 'new' == $jobid ) {
		$title = __( 'Job Manager: New Job', 'jobman' );
		$submit = __( 'Create Job', 'jobman' );
		$job = array();
	}
	else {
		$title = __( 'Job Manager: Edit Job', 'jobman' );
		$submit = __( 'Update Job', 'jobman' );
		
		$job = get_post( $jobid );
		if( NULL == $job )
			// No job associated with that id.
			return 0;
	}
	
	if( isset( $job->ID ) ) {
		$jobid = $job->ID;
		$jobmeta = get_post_custom( $job->ID );
		$jobcats = wp_get_object_terms( $job->ID, 'jobman_category' );
	}
	else {
		$jobmeta = array();
		$jobcats = array();
	}
	
	$icons = $options['icons'];
	
	$jobdata = array();
	foreach( $jobmeta as $key => $value ) {
		if( is_array( $value ) )
			$jobdata[$key] = $value[0];
		else
			$jobdata[$key] = $value;
	}
?>
	<form action="<?php echo admin_url('admin.php?page=jobman-list-jobs') ?>" method="post">
	<input type="hidden" name="jobmansubmit" value="1" />
	<input type="hidden" name="jobman-jobid" value="<?php echo $jobid ?>" />
<?php 
	wp_nonce_field( "jobman-edit-job-$jobid"); 
?>
	<div class="wrap">
		<h2><?php echo $title ?></h2>
		<table id="jobman-job-edit" class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Job ID', 'jobman' ) ?></th>
				<td><?php echo $jobid ?></td>
				<td></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Categories', 'jobman' ) ?></th>
				<td>
<?php
	$categories = get_terms( 'jobman_category', 'hide_empty=0' );
	if( count( $categories ) > 0 ) {
		foreach( $categories as $cat ) {
			$checked = '';
			if( 'new' != $jobid ) {
				foreach( $jobcats as $jobcat ) {
					if( $cat->term_id == $jobcat->term_id ) {
						$checked = ' checked="checked"';
						break;
					}
				}
			}
?>
					<input type="checkbox" name="jobman-categories[]" value="<?php echo $cat->slug ?>"<?php echo $checked ?> /> <?php echo $cat->name ?><br/>
<?php
		}
	}
?>
				</td>
				<td><span class="description"><?php _e( 'Categories that this job belongs to. It will be displayed in the job list for each category selected.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Icon', 'jobman' ) ?></th>
				<td>
<?php
	if( count( $icons ) > 0 ) {
		foreach( $icons as $icon ) {
			if( isset( $jobdata['iconid'] ) && $icon == $jobdata['iconid'] )
				$checked = ' checked="checked"';
			else
				$checked = '';
				
			$post = get_post( $icon );
?>
					<input type="radio" name="jobman-icon" value="<?php echo $icon ?>"<?php echo $checked ?> /> <img src="<?php echo wp_get_attachment_url( $icon ) ?>" /> <?php echo $post->post_title ?><br/>
<?php
		}
	}

	if( ! isset( $jobdata['iconid'] ) || 0 == $jobdata['iconid'] )
		$checked = ' checked="checked"';
	else
		$checked = '';
?>
					<input type="radio" name="jobman-icon"<?php echo $checked ?> value="" /> <?php _e( 'No Icon', 'jobman' ) ?><br/>
				</td>
				<td><span class="description"><?php _e( 'Icon to display for this job in the Job List', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Title', 'jobman' ) ?></th>
				<td><input class="regular-text code" type="text" name="jobman-title" value="<?php echo ( isset( $job->post_title ) )?( $job->post_title ):( '' ) ?>" /></td>
				<td></td>
			</tr>
<?php
	$fields = $options['job_fields'];
	$content = '';
	if( count( $fields ) > 0 ) {
		uasort( $fields, 'jobman_sort_fields' );
		foreach( $fields as $id => $field ) {
			if( 'new' == $jobid )
				$data = $field['data'];
			else if( array_key_exists( "data$id", $jobdata ) )
				$data = $jobdata["data$id"];
			else
				$data = '';

			if( 'heading' != $field['type'] )
				$content .= '<tr>';
			
			switch( $field['type'] ) {
				case 'text':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}</th>";
					else
						$content .= '<td class="th"></td>';
					
					$content .= "<td><input type='text' name='jobman-field-$id' value='$data' /></td>";
					$content .= "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'radio':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}</th><td>";
					else
						$content .= '<td class="th"></td><td>';
					
					$values = split( "\n", strip_tags( $field['data'] ) );
					$display_values = split( "\n", $field['data'] );
					
					foreach( $values as $key => $value ) {
						$checked = '';
						if( $value == $data )
							$checked = ' checked="checked"';
						$content .= "<input type='radio' name='jobman-field-$id' value='" . trim( $value ) . "'$checked /> {$display_values[$key]}<br/>";
					}
					$content .= '</td>';
					$content .= "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'checkbox':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}</th><td>";
					else
						$content .= '<td class="th"></td><td>';

					$values = split( "\n", strip_tags( $field['data'] ) );
					$display_values = split( "\n", $field['data'] );
					
					foreach( $values as $key => $value ) {
						$checked = '';
						if( $value == $data )
							$checked = ' checked="checked"';
						$content .= "<input type='checkbox' name='jobman-field-{$id}[]' value='" . trim( $value ) . "'$checked /> {$display_values[$key]}<br/>";
					}
					$content .= '</td>';
					$content .= "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'textarea':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}</th>";
					else
						$content .= '<td class="th"></td>';

					if( '' == $field['description'] ) {
						$content .= "<td colspan='2'><textarea class='large-text code' name='jobman-field-$id' rows='7'>$data</textarea></td></tr>";
					}
					else {
						$content .= "<td><textarea class='large-text code' name='jobman-field-$id' rows='7'>$data</textarea></td>";
						$content .= "<td><span class='description'>{$field['description']}</span></td></tr>";
					}
					break;
				case 'date':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}</th>";
					else
						$content .= '<td class="th"></td>';

					$content .= "<td><input type='text' class='datepicker' name='jobman-field-$id' value='$data' /></td>";
					$content .= "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'file':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}</th>";
					else
						$content .= '<td class="th"></td>';

					$content .= '<td>';
					$content .= "<input type='file' name='jobman-field-$id' />";
					if( 'new' != $jobid ) {
						$content .= "<br/><input type='checkbox' name='jobman-field-delete-$id' value='1' />" . __( 'Delete File?', 'jobman' );
						$content .= "<input type='hidden' name='jobman-field-delete-$id' value='$data' />";
					}
					$content .= "</td>";
					$content .= "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'heading':
					$content .= '</table>';
					$content .= "<h3>{$field['label']}</h3>";
					$content .= "<table>";
					$tablecount++;
					$totalrowcount--;
					$rowcount = 0;
					break;
				case 'html':
					$content .= "<td colspan='3'>$data</td></tr>";
					break;
				case 'blank':
					$content .= '<td colspan="3">&nbsp;</td></tr>';
					break;
			}
		}
	}
	
	echo $content;
?>
			<tr>
				<th scope="row"><?php _e( 'Display Start Date', 'jobman' ) ?></th>
				<td><input class="regular-text code datepicker" type="text" name="jobman-displaystartdate" value="<?php echo ( 'new' != $jobid )?( date( 'Y-m-d', strtotime( $job->post_date ) ) ):( '' ) ?>" /></td>
				<td><span class="description"><?php _e( 'The date this job should start being displayed on the site. To start displaying immediately, leave blank.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Display End Date', 'jobman' ) ?></th>
				<td><input class="regular-text code datepicker" type="text" name="jobman-displayenddate" value="<?php echo ( array_key_exists( 'displayenddate', $jobdata ) )?( $jobdata['displayenddate'] ):( '' ) ?>" /></td>
				<td><span class="description"><?php _e( 'The date this job should stop being displayed on the site. To display indefinitely, leave blank.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Application Email', 'jobman' ) ?></th>
				<td><input class="regular-text code" type="text" name="jobman-email" value="<?php echo ( array_key_exists( 'email', $jobdata ) )?( $jobdata['email'] ):( '' ) ?>" /></td>
				<td><span class="description"><?php _e( 'The email address to notify when an application is submitted for this job. For default behaviour (category email or global email), leave blank.', 'jobman' ) ?></span></td>
			</tr>
<?php
	$checked = '';
	if( array_key_exists( 'highlighted', $jobdata ) && $jobdata['highlighted'] )
		$checked = ' checked="checked"';
?>
			<tr>
				<th scope="row"><?php _e( 'Highlighted?', 'jobman' ) ?></th>
				<td><input type="checkbox" name="jobman-highlighted" value="1" <?php echo $checked ?>/></td>
				<td><span class="description"><?php _e( 'Mark this job as highlighted? For the behaviour of highlighted jobs, see the Display Settings admin page.', 'jobman' ) ?></span></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php echo $submit ?>" /></p>
	</div>
	</form>
<?php
	return 1;
}

function jobman_updatedb() {
	global $wpdb;
	$options = get_option( 'jobman_options' );
	
	$displaystartdate = stripslashes( $_REQUEST['jobman-displaystartdate'] );
	if( empty( $displaystartdate ) )
		$displaystartdate = date( 'Y-m-d', strtotime( '-1 day' ) );

	$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'post_content' => '',
				'post_name' => strtolower( str_replace( ' ', '-', $_REQUEST['jobman-title'] ) ),
				'post_title' => stripslashes( $_REQUEST['jobman-title'] ),
				'post_type' => 'jobman_job',
				'post_date' => $displaystartdate,
				'post_parent' => $options['main_page']);
	
	if( 'new' == $_REQUEST['jobman-jobid'] ) {
		$id = wp_insert_post( $page );
		
		$fields = $options['job_fields'];
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
								$attachment = array(
												'post_title' => '',
												'post_content' => '',
												'post_status' => 'publish',
												'post_mime_type' => mime_content_type( $upload['file'] )
											);
								$data = wp_insert_attachment( $attachment, $upload['file'], $id );
								$attach_data = wp_generate_attachment_metadata( $data, $upload['file'] );
								wp_update_attachment_metadata( $data, $attach_data );
							}
						}
						break;
					case 'checkbox':
						$data = implode( ', ', $_REQUEST["jobman-field-$fid"] );
						break;
					default:
						$data = $_REQUEST["jobman-field-$fid"];
				}
				
				add_post_meta( $id, "data$fid", $data, true );
			}
		}

		add_post_meta( $id, 'displayenddate', stripslashes( $_REQUEST['jobman-displayenddate'] ), true );
		add_post_meta( $id, 'iconid', $_REQUEST['jobman-icon'], true );
		add_post_meta( $id, 'email', $_REQUEST['jobman-email'], true );
		
		if( array_key_exists( 'jobman-highlighted', $_REQUEST ) && $_REQUEST['jobman-highlighted'] )
			add_post_meta( $id, 'highlighted', 1, true );
		else
			add_post_meta( $id, 'highlighted', 0, true );
	}
	else {
		$page['ID'] = $_REQUEST['jobman-jobid'];
		$id = wp_update_post( $page );
		
		$fields = $options['job_fields'];
		if( count( $fields ) > 0 ) {
			foreach( $fields as $fid => $field ) {
				if($field['type'] != 'file' && ( ! array_key_exists( "jobman-field-$fid", $_REQUEST ) || '' == $_REQUEST["jobman-field-$fid"] ) )
					continue;
				
				if( 'file' == $field['type'] && ! array_key_exists( "jobman-field-$fid", $_FILES ) && ! array_key_exists( "jobman-field-delete-$fid", $_REQUEST ) )
					continue;
				
				$data = '';
				switch( $field['type'] ) {
					case 'file':
						if( array_key_exists( "jobman-field-delete-$fid", $_REQUEST ) )
							wp_delete_attachment( $_REQUEST["jobman-field-current-$fid"] );

						if( is_uploaded_file( $_FILES["jobman-field-$fid"]['tmp_name'] ) ) {
							$upload = wp_upload_bits( $_FILES["jobman-field-$fid"]['name'], NULL, file_get_contents( $_FILES["jobman-field-$fid"]['tmp_name'] ) );
							if( ! $upload['error'] ) {
								// Delete the old attachment
								wp_delete_attachment( $_REQUEST["jobman-field-current-$fid"] );
								
								$attachment = array(
												'post_title' => '',
												'post_content' => '',
												'post_status' => 'publish',
												'post_mime_type' => mime_content_type( $upload['file'] )
											);
								$data = wp_insert_attachment( $attachment, $upload['file'], $id );
								$attach_data = wp_generate_attachment_metadata( $data, $upload['file'] );
								wp_update_attachment_metadata( $data, $attach_data );
							}
						}
						break;
					case 'checkbox':
						$data = implode( ', ', $_REQUEST["jobman-field-$fid"] );
						break;
					default:
						$data = $_REQUEST["jobman-field-$fid"];
				}
				
				update_post_meta( $id, "data$fid", $data );
			}
		}

		
		update_post_meta( $id, 'displayenddate', stripslashes( $_REQUEST['jobman-displayenddate'] ) );
		update_post_meta( $id, 'iconid', $_REQUEST['jobman-icon'] );
		update_post_meta( $id, 'email', $_REQUEST['jobman-email'] );
		
		if( array_key_exists( 'jobman-highlighted', $_REQUEST ) && $_REQUEST['jobman-highlighted'] )
			update_post_meta( $id, 'highlighted', 1 );
		else
			update_post_meta( $id, 'highlighted', 0 );
	}

	if( array_key_exists( 'jobman-categories', $_REQUEST ) )
		wp_set_object_terms( $id, $_REQUEST['jobman-categories'], 'jobman_category', false );

	if( $options['plugins']['gxs'] )
		do_action( 'sm_rebuild' );
}

function jobman_job_delete_confirm() {
?>
	<div class="wrap">
	<form action="" method="post">
	<input type="hidden" name="jobman-delete-confirmed" value="1" />
	<input type="hidden" name="jobman-mass-edit-jobs" value="delete" />
	<input type="hidden" name="jobman-job-ids" value="<?php echo implode( ',', $_REQUEST['job'] ) ?>" />
<?php
	wp_nonce_field( 'jobman-mass-delete-jobs' );
?>
		<h2><?php _e( 'Job Manager: Jobs', 'jobman' ) ?></h2>
		<p class="error"><?php _e( 'This will permanently delete all of the selected jobs. Please confirm that you want to continue.', 'jobman' ) ?></p>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Delete Jobs', 'jobman' ) ?>" /></p>
	</form>
	</div>
<?php
}

function jobman_job_delete() {
	$options = get_option( 'jobman_options' );
	
	$jobs = explode( ',', $_REQUEST['jobman-job-ids'] );
	
	foreach( $jobs as $job ) {
		// Delete the job
		wp_delete_post( $job );
	}
}

?>