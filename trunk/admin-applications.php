<?php
function jobman_list_applications() {
	global $wpdb;
	$options = get_option( 'jobman_options' );

	$deleted = false;
	$emailed = false;
	if(array_key_exists( 'jobman-mass-edit', $_REQUEST ) && 'delete' == $_REQUEST['jobman-mass-edit'] ) {
		if( array_key_exists( 'jobman-delete-confirmed', $_REQUEST ) ) {
			check_admin_referer( 'jobman-mass-delete-applications' );
			jobman_application_delete();
			$deleted = true;
		}
		else {
			check_admin_referer( 'jobman-mass-edit-applications' );
			jobman_application_delete_confirm();
			return;
		}
	}
	else if( array_key_exists( 'jobman-mass-edit', $_REQUEST ) && 'email' == $_REQUEST['jobman-mass-edit'] ) {
		check_admin_referer( 'jobman-mass-edit-applications' );
		jobman_application_mailout();
		return;
	}
	else if(array_key_exists( 'appid', $_REQUEST ) ) {
		jobman_application_display_details( $_REQUEST['appid'] );
		return;
	}
	else if( array_key_exists( 'jobman-mailout-send', $_REQUEST ) ) {
		check_admin_referer( 'jobman-mailout-send' );
		jobman_application_mailout_send();
		$emailed = true;
	}
?>
	<div class="wrap">
		<h2><?php _e( 'Job Manager: Applications', 'jobman' ) ?></h2>
<?php
	if( $deleted )
		echo '<p class="error">' . __( 'Selected applications have been deleted.', 'jobman' ) . '</p>';
	if( $emailed )
		echo '<p class="error">' . __( 'The mailout has been sent.', 'jobman' ) . '</p>';

	$fields = $options['fields'];

	$categories = get_terms( 'jobman_category', 'hide_empty=0' );
?>
		<div id="jobman-filter">
		<form action="" method="post">
			<div class="jobman-filter-normal">
				<h4><?php _e( 'Standard Filters', 'jobman' ) ?></h4>
				<table>
					<tr>
						<th scope="row"><?php _e( 'Job ID', 'jobman' ) ?>:</th>
						<td><input type="text" name="jobman-jobid" value="<?php echo ( array_key_exists( 'jobman-jobid', $_REQUEST ) )?( $_REQUEST['jobman-jobid'] ):( '' ) ?>" /></td>
					</tr>
<?php
	if( $options['user_registration'] ) {
?>
					<tr>
						<th scope="row"><?php _e( 'Registered Applicant', 'jobman' ) ?>:</th>
						<td><select name="jobman-applicant">
							<option value=""><?php _e( 'All Applicants', 'jobman' ) ?></option>
<?php
		$users = $wpdb->get_results( "SELECT ID, display_name FROM $wpdb->users ORDER BY display_name ASC" );
		
		if(count( $users ) > 0) {
			foreach( $users as $user ) {
				$checked = '';
				if( array_key_exists( 'jobman-applicant', $_REQUEST ) && $_REQUEST['jobman-applicant'] == $user->ID )
					$checked = ' checked="checked"';
?>
							<option value="<?php echo $user->ID ?>"<?php echo $checked ?>><?php echo $user->display_name ?></option>
<?php
			}
		}
?>
						</select></td>
					</tr>
<?php
	}
?>
					<tr>
						<th scope="row"><?php _e( 'Categories', 'jobman' ) ?>:</th>
						<td>
<?php
	if( count( $categories ) > 0 ) {
		$ii = 0;
		foreach( $categories as $cat ) {
			$checked = '';
			if( array_key_exists( 'jobman-categories', $_REQUEST ) && is_array( $_REQUEST['jobman-categories'] ) && in_array( $cat->term_id, $_REQUEST['jobman-categories'] ) )
				$checked = ' checked="checked"';
?>
							<input type="checkbox" name="jobman-categories[]" value="<?php echo $cat->term_id ?>"<?php echo $checked ?> /> <?php echo $cat->name ?><br/>
<?php
		}
	}
?>
						</td>
					</tr>
<?php
	$rating = 0;
	if( array_key_exists( 'jobman-rating', $_REQUEST ) )
	    $rating = $_REQUEST['jobman-rating'];
?>
					<tr>
					    <th scope="row"><?php _e( 'Minimum Rating', 'jobman' ) ?>:</th>
					    <td>
<?php
	jobman_print_rating_stars( 'filter', $rating );
?>
						</td>
					</tr>
				</table>
			</div>
			<div class="jobman-filter-custom">
				<h4><?php _e( 'Custom Filters', 'jobman' ) ?></h4>
<?php
	if( count( $fields ) > 0 ) {
		uasort( $fields, 'jobman_sort_fields' );
?>
				<table class="widefat page fixed" cellspacing="0">
					<thead>
					<tr>
<?php
		$fieldcount = 0;
		foreach( $fields as $id => $field ) {
			if( $field['listdisplay'] ) {
				$fieldcount++;
?>
						<th scope="col"><?php echo $field['label'] ?></th>
<?php
			}
		}
?>
					</tr>
					</thead>
<?php
		echo '<tr>';
		foreach( $fields as $id => $field ) {
			if( ! $field['listdisplay'] )
				continue;

			$req_value = '';
			if( array_key_exists( "jobman-field-$id", $_REQUEST ) )
				$req_value = $_REQUEST["jobman-field-$id"];

			switch( $field['type'] ) {
				case 'text':
				case 'textarea':
					
						echo "<td><input type='text' name='jobman-field-$id' value='$req_value' /></td>";
					break;
				case 'date':
					echo "<td><input type='text' class='datepicker' name='jobman-field-$id' value='$req_value' /></td>";
					break;
				case 'radio':
				case 'checkbox':
					echo '<td>';
					$values = split( "\n", $field['data'] );
					foreach( $values as $value ) {
						$checked = '';
						if( is_array( $req_value ) && in_array( trim( $value ), $req_value ) )
							$checked = ' checked="checked"';

						echo "<input type='checkbox' name='jobman-field-{$id}[]' value='" . trim($value) . "'$checked /> $value<br/>";
					}
					echo '</td>';
					break;
				default:
					'<td>' . __( 'This field cannot be filtered.', 'jobman' ) . '</td>';
			}
		}
		echo '</tr>';
?>
				</table>
<?php
	}
?>
				</div>
			<div style="clear: both; text-align: right;"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Filter Applications', 'jobman' ) ?>" /></div>
			
		</form>
		</div>
		<div id="jobman-filter-link-show"><a href="#" onclick="jQuery('#jobman-filter').show('slow'); jQuery('#jobman-filter-link-show').hide(); jQuery('#jobman-filter-link-hide').show(); return false;"><?php _e( 'Show Filter Options', 'jobman' ) ?></a></div>
		<div id="jobman-filter-link-hide" class="hidden"><a href="#" onclick="jQuery('#jobman-filter').hide('slow'); jQuery('#jobman-filter-link-hide').hide(); jQuery('#jobman-filter-link-show').show(); return false;"><?php _e( 'Hide Filter Options', 'jobman' ) ?></a></div>
		
		<form action="" method="post">
<?php 
	wp_nonce_field( 'jobman-mass-edit-applications' ); 
?>
		<table id="jobman-applications-list" class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" id="cb" class="column-cb check-column"><input type="checkbox"></th>
				<th scope="col"><?php _e( 'Job', 'jobman' ) ?></th>
<?php
	if( $options['user_registration'] ) {
?>
				<th scope="col"><?php _e( 'User', 'jobman' ) ?></th>
<?php
	}
?>
				<th scope="col"><?php _e( 'Categories', 'jobman' ) ?></th>
<?php
	if( count( $fields ) > 0 ) {
		foreach( $fields as $field ) {
			if( $field['listdisplay'] ) {
?>
				<th scope="col"><?php echo $field['label'] ?></th>
<?php
			}
		}
	}
?>
				<th scope="col"><?php _e( 'View Details', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Emails', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Rating', 'jobman' ) ?></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<th scope="col" class="column-cb check-column"><input type="checkbox"></th>
				<th scope="col"><?php _e( 'Job', 'jobman' ) ?></th>
<?php
	if( $options['user_registration'] ) {
?>
				<th scope="col"><?php _e( 'User', 'jobman' ) ?></th>
<?php
	}
?>
				<th scope="col"><?php _e( 'Categories', 'jobman' ) ?></th>
<?php
	if( count( $fields ) > 0 ) {
		foreach( $fields as $field ) {
			if( $field['listdisplay'] ) {
?>
				<th scope="col"><?php echo $field['label'] ?></th>
<?php
			}
		}
	}
?>
				<th scope="col"><?php _e( 'View Details', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Emails', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Rating', 'jobman' ) ?></th>
			</tr>
			</tfoot>
<?php
	$args = array();
	$args['post_type'] = 'jobman_app';
	$args['offset'] = 0;
	$args['numberposts'] = -1;
	
	$filtered = false;
	
	// Add job filter
	if( array_key_exists( 'jobman-jobid', $_REQUEST ) ) {
		$filtered = true;
		$args['post_parent'] = $_REQUEST['jobman-jobid'];
	}
	
	// Add applicant filter
	if( array_key_exists( 'jobman-applicant', $_REQUEST ) )
		$args['author'] = $_REQUEST['jobman-applicant'];
	
	// Add category filter
	// Removed this until WP_Query supports *__in for custom taxonomy.
	/*if( array_key_exists( 'jobman-categories', $_REQUEST ) && is_array( $_REQUEST['jobman-categories'] ) ) {
		$filtered = true;
		$args['jcat__in'] = array();
		foreach( $_REQUEST['jobman-categories'] as $cat ) {
			$args['jcat__in'][] = $cat;
		}
	}*/
	
	// Add star rating filter
	if( array_key_exists( 'jobman-rating', $_REQUEST ) && is_int( $_REQUEST['jobman-rating'] ) ) {
	    $args['meta_key'] = 'rating';
	    $args['meta_value'] = $_REQUEST['jobman-rating'];
	    $args['meta_compare'] = '>=';
	}

	$applications = get_posts( $args );
	
	$app_displayed = false;
	if( count( $applications ) > 0 ) {
		foreach( $applications as $app ) {
			$appmeta = get_post_custom( $app->ID );

			$appdata = array();
			foreach( $appmeta as $key => $value ) {
				if( is_array( $value ) )
					$appdata[$key] = $value[0];
				else
					$appdata[$key] = $value;
			}
			
			// Workaround for WP_Query not supporting *__in for custom taxonomy.
			if( array_key_exists( 'jobman-categories', $_REQUEST ) && is_array( $_REQUEST['jobman-categories'] ) ) {
				$cats = wp_get_object_terms( $app->ID, 'jobman_category' );
				if( count( $cats ) > 0 ) {
					$found = false;
					foreach( $cats as $cat ) {
						echo "$cat->term_id ";
						if( in_array( $cat->term_id, $_REQUEST['jobman-categories'] ) ) {
							// $app is in the list of selected categories. Let it through.
							$found = true;
							break;
						}
					}
					
					// $app wasn't in the categories. Skip it.
					if( ! $found ) {
						$filtered = true;
						continue;
					}
				}
				else {
					// $app has no categories. Skip it.
					$filtered = true;
					continue;
				}
			}
			
			// Check against field filters
			if( count( $fields ) > 0 ) {
				foreach( $fields as $id => $field ) {
					if( ! array_key_exists( "jobman-field-$id", $_REQUEST ) || '' == $_REQUEST["jobman-field-$id"] )
						continue;
					if( ! array_key_exists( "data$id", $appdata ) ) {
						// No data for this key application, so it can't match. Go to next $app.
						$filtered = true;
						continue 2;
					}
					switch( $field['type'] ) {
						case 'text':
						case 'textarea':
						case 'date':
							if( $appdata["data$id"] != $_REQUEST["jobman-field-$id"] ) {
								// App doesn't match. Go to the next item in the $applications loop.
								$filtered = true;
								continue 3;
							}
							break;
						case 'radio':
						case 'checkbox':
							if( is_array( $_REQUEST["jobman-field-$id"] ) ) {
								$data = split( ',', $appdata["data$id"] );
								foreach( $_REQUEST["jobman-field-$id"] as $selected ) {
									if( in_array( trim( $selected ), $data ) )
										// We have a match. Go to the next item in the $fields loop.
										continue 3;
								}
								// There was no match. Go to next in $applications loop.
								$filtered = true;
								continue 3;
							}
							break;
					}
				}
			}
			$app_displayed = true;
?>
			<tr>
				<th scope="row" class="check-column"><input type="checkbox" name="application[]" value="<?php echo $app->ID ?>" /></th>
<?php
			$parent = get_post( $app->post_parent );
			if( NULL != $parent && 'jobman_job' == $parent->post_type ) {
?>
				<td><strong><a href="?page=jobman-list-jobs&amp;jobman-jobid=<?php echo $parent->ID ?>"><?php echo $parent->post_title ?></a></strong></td>
<?php
			}
			else {
?>
				<td><?php _e( 'No job', 'jobman' ) ?></td>
<?php
			}

			if( $options['user_registration'] ) {
				$name = '';
				if( 0 == $app->post_author ) {
					$name = __( 'Unregistered Applicant', 'jobman' );
				}
				else {
					$author = get_userdata( $app->post_author );
					$name = $author->display_name;
				}
?>
				<td><?php echo $name ?></td>
<?php
			}
			
			$cats = wp_get_object_terms( $app->ID, 'jobman_category' );
			$cats_arr = array();
			if( count( $cats ) > 0 ) {
				foreach( $cats as $cat ) {
					$cats_arr[] = $cat->name;
				}
			}
?>
				<td><?php echo implode( ', ', $cats_arr ) ?></td>
<?php
			if( count( $fields ) ) {
				foreach( $fields as $id => $field ) {
					if( $field['listdisplay'] ) {
						$data = '';
						if( array_key_exists("data$id", $appdata ) ) {
							switch( $field['type'] ) {
								case 'text':
								case 'radio':
								case 'checkbox':
								case 'date':
								case 'textarea':
									$data = $appdata["data$id"];
									break;
								case 'file':
									$data = '<a href="' . admin_url('admin.php?page=jobman-list-applications&amp;appid=' . $app->ID . '&amp;getfile=' . $appdata["data$id"]) . '">' . $appdata["data$id"] . '</a>';
									break;
							}
						}
?>
				<td><?php echo $data ?></td>
<?php
					}
				}
			}
?>
				<td><a href="?page=jobman-list-applications&amp;appid=<?php echo $app->ID ?>"><?php _e( 'View Details', 'jobman' ) ?></a></td>
				<td>
<?php
			$emailids = get_post_meta( $app->ID, 'contactmail', false );
			if( count( $emailids ) > 0 )
			    echo "<a href='?page=jobman-list-emails&amp;appid=$app->ID'>" . count( $emailids ) . '</a>';
			else
			    echo '0';
?>
				</td>
				<td>
<?php
	$rating = 0;
	if( array_key_exists( 'rating', $appdata ) )
    	$rating = $appdata['rating'];

	jobman_print_rating_stars( $app->ID, $rating );
?>
					</div>
				</td>
			</tr>
<?php
		}
	}
	if( ! $app_displayed ) {
		if( $filtered )
			$msg = __( 'There were no applications that matched your search.', 'jobman' );
		else
			$msg = __( 'There are currently no applications in the system.', 'jobman' );
			
		if( $options['user_registration'] )
			$fieldcount++;
?>
			<tr>
				<td colspan="<?php echo 3 + $fieldcount ?>"><?php echo $msg ?></td>
			</tr>
<?php
	}
?>
		</table>
		<div class="alignleft actions">
			<select name="jobman-mass-edit">
				<option value=""><?php _e( 'Bulk Actions', 'jobman' ) ?></option>
				<option value="email"><?php _e( 'Email', 'jobman' ) ?></option>
				<option value="delete"><?php _e( 'Delete', 'jobman' ) ?></option>
				<option value="export-csv"><?php _e( 'Export as CSV file', 'jobman' ) ?></option>
			</select>
			<input type="submit" value="<?php _e( 'Apply', 'jobman' ) ?>" name="submit" class="button-secondary action" />
		</div>
		</form>
	</div>
<?php
}

function jobman_print_rating_stars( $id, $rating ) {
?>
			        <div class="star-holder">
						<a href="#" onclick="jobman_reset_rating('<?php echo $id ?>'); return false;"><?php _e( 'No rating', 'jobman' ) ?></a>
						<div id="jobman-star-rating-<?php echo $id ?>" class="star-rating" style="width: <?php echo $rating * 19 ?>px"></div>
						<input type="hidden" id="jobman-rating-<?php echo $id ?>" name="jobman-rating" value="<?php echo $rating ?>" />
						<input type="hidden" name="callbackid" value="<?php echo $id ?>" />
<?php
	for( $ii = 1; $ii <= 5; $ii++) {
?>
						<div class="star star<?php echo $ii ?>"><img src="<?php echo JOBMAN_URL ?>/images/star.gif" alt="<?php echo $ii ?>" /></div>
<?php
	}
}

function jobman_rate_application() {
	$rating = get_post_meta( $_REQUEST['appid'], 'rating', true );
	if( '' == $rating )
		add_post_meta( $_REQUEST['appid'], 'rating', $_REQUEST['rating'], true );
	else
	    update_post_meta( $_REQUEST['appid'], 'rating', $_REQUEST['rating'] );

	die();
}

function jobman_application_display_details( $appid ) {
	$options = get_option( 'jobman_options' );
	$fromid = $options['application_email_from'];
	
	if( array_key_exists( 'jobman-email', $_REQUEST ) ) {
		check_admin_referer( 'jobman-reemail-application' );
	    jobman_email_application( $appid, $_REQUEST['jobman-email'] );
 }
?>
	<div id="jobman-application" class="wrap">
		<h2><?php _e( 'Job Manager: Application Details', 'jobman' ) ?></h2>
		<div class="printicon"><a href="javascript:window.print()"><img src="<?php echo JOBMAN_URL ?>/images/print-icon.png" /></a></div>
		<a href="?page=jobman-list-applications" class="backlink">&lt;--<?php _e( 'Back to Application List', 'jobman' ) ?></a>
<?php
	$app = get_post( $appid );
	$appmeta = get_post_custom( $appid );

	$appdata = array();
	foreach( $appmeta as $key => $value ) {
		if( is_array( $value ) )
			$appdata[$key] = $value[0];
		else
			$appdata[$key] = $value;
	}
	
	if( NULL != $app ) {
		echo '<table class="form-table">';
		
		$parent = get_post( $app->post_parent );
		if( NULL != $parent && 'jobman_job' == $parent->post_type ) {
			echo '<tr><th scope="row"><strong>' . __( 'Job', 'jobman' ) . "</strong></th><td><strong><a href='" . get_page_link( $parent->ID ) . "'>$parent->ID - $parent->post_title</a></strong></td></tr>";
		}
		echo '<tr><th scope="row"><strong>' . __( 'Timestamp', 'jobman' ) . "</strong></th><td>$app->post_date</td></tr>";
		
		echo '<tr><th scope="row"><strong>' . __( 'Rating', 'jobman' ) . '</strong></th>';
		echo '<td>';

		$rating = 0;
		if( array_key_exists( 'rating', $appdata ) )
	    	$rating = $appdata['rating'];

		jobman_print_rating_stars( $app->ID, $rating );
		
		echo '</div></td><tr><td colspan="2">&nbsp;</td></tr>';

		$fields = $options['fields'];
		foreach( $appdata as $key => $item ) {
			$matches = array();
			if( ! preg_match( '/^data(\d+)$/', $key, $matches ) )
				// Not a data key
				continue;
			$fid = $matches[1];
			
			echo '<tr><th scope="row" style="white-space: nowrap;"><strong>' . $fields[$fid]['label'] . '</strong></th><td>';
			if( $fid == $fromid ) {
				echo "<a href='mailto:$item'>";
			}
			switch( $fields[$fid]['type'] ) {
				case 'text':
				case 'radio':
				case 'checkbox':
				case 'date':
				case 'textarea':
					echo $item;
					break;
				case 'file':
					echo "<a href='" . admin_url("admin.php?page=jobman-list-applications&amp;appid=$app->ID&amp;getfile=$item") . "'>$item</a>";
					break;
			}
			if( $fid == $fromid ) {
				echo '</a>';
			}
			echo '</td></tr>';
		}
?>
		</table>

		<div class="emailapplication">
			<h3><?php _e( 'Email Application', 'jobman' ) ?></h3>
			<p><?php _e( 'Use this form to email the application to a new email address.', 'jobman' ) ?></p>
			<form action="" method="post">
<?php
	wp_nonce_field( 'jobman-reemail-application' );
?>
			<input type="text" name="jobman-email" />
			<input type="submit" name="submit" value="<?php _e( 'Email', 'jobman' ) ?>!" />
			</form>
		</div>
		<a href="?page=jobman-list-applications" class="backlink">&lt;--<?php _e( 'Back to Application List', 'jobman' ) ?></a>
<?php
	}
	else {
		echo '<p class="error">' . __( 'No such application.', 'jobman' ) . '</p>';
	}
	echo '</div>';
}

function jobman_application_delete_confirm() {
?>
	<div class="wrap">
	<form action="" method="post">
	<input type="hidden" name="jobman-delete-confirmed" value="1" />
	<input type="hidden" name="jobman-mass-edit" value="delete" />
	<input type="hidden" name="jobman-app-ids" value="<?php echo implode( ',', $_REQUEST['application'] ) ?>" />
<?php
	wp_nonce_field( 'jobman-mass-delete-applications' );
?>
		<h2><?php _e( 'Job Manager: Applications', 'jobman' ) ?></h2>
		<p class="error"><?php _e( 'This will permanently delete all of the selected applications. Please confirm that you want to continue.', 'jobman' ) ?></p>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Delete Applications', 'jobman' ) ?>" /></p>
	</form>
	</div>
<?php
}

function jobman_application_delete() {
	$options = get_option( 'jobman_options' );
	
	$apps = explode( ',', $_REQUEST['jobman-app-ids'] );
	
	// Get the file fields
	$file_fields = array();
	foreach( $options['fields'] as $id => $field ) {
		if( 'file' == $field['type'] )
			$file_fields[] = $id;
	}
	
	foreach( $apps as $app ) {
		$appmeta = get_post_custom( $app );
		$appdata = array();
		foreach( $appmeta as $key => $value ) {
			if( is_array( $value ) )
				$appdata[$key] = $value[0];
			else
				$appdata[$key] = $value;
		}

		// Delete any files uploaded
		foreach( $file_fields as $fid ) {
			if( array_key_exists( "data$fid", $appdata )  && '' != $appdata["data$fid"] ) {
				$filename = JOBMAN_UPLOAD_DIR . '/uploads/' . $appdata["data$fid"];
				if( file_exists( $filename ) )
					unlink( $filename );
			}
		}
		// Delete the application
		wp_delete_post( $app );
	}
}

function jobman_get_application_csv() {
	require_once( ABSPATH . WPINC . '/pluggable.php' );
	
	$options = get_option( 'jobman_options' );

	header( 'Cache-Control: no-cache' );
	header( 'Expires: -1' );

	if( ! current_user_can( 'read_private_pages' ) ) {
		header( $_SERVER["SERVER_PROTOCOL"] . ' 403 Forbidden' );
		header( 'Refresh: 0; url=' . admin_url() );
		echo '<html><head><title>403 Forbidden</title></head><body><p>Access is forbidden.</p></body></html>';
		exit;
	}

	header( 'Content-Type: application/force-download' );
	header( 'Content-type: text/csv' );
	header( 'Content-Type: application/download' );
	header( "Content-Disposition: attachment; filename=applications.csv	" );

	$fields = $options['fields'];
	$out = fopen( 'php://output', 'w' );
	
	if( count( $fields ) > 0 ) {
		uasort( $fields, 'jobman_sort_fields' );
		
		$labels = array();
		foreach( $fields as $field ) {
			$labels[] = $field['label'];
		}
		fputcsv( $out, $labels );
		
		$posts = array();
		if( array_key_exists( 'application', $_REQUEST ) && is_array( $_REQUEST['application'] ) )
			$posts = $_REQUEST['application'];
		$apps = get_posts( array( 'post_type' => 'jobman_app', 'post__in' => $posts, 'numberposts' => -1 ) );

		if( count( $apps ) > 0 ) {
			foreach( $apps as $app ) {
				$data = array();

				$appmeta = get_post_custom( $app->ID );

				$appdata = array();
				foreach( $appmeta as $key => $value ) {
					if( is_array( $value ) )
						$appdata[$key] = $value[0];
					else
						$appdata[$key] = $value;
				}

				foreach( $fields as $id => $field ) {
					if( array_key_exists( "data$id", $appdata ) ) {
						$item = $appdata["data$id"];
						switch( $field['type'] ) {
							case 'text':
							case 'radio':
							case 'checkbox':
							case 'date':
							case 'textarea':
								$data[] = $item;
								break;
							case 'file':
								$data[] =  admin_url("admin.php?page=jobman-list-applications&amp;appid=$app->ID&amp;getfile=$item");
								break;
							default:
								$data[] = '';
						}
					}
					else {
						$data[] = '';
					}
				}
				
				fputcsv( $out, $data );
			}
		}
	}
	
	fclose( $out );

	exit;
}

?>