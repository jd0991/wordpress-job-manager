<?php namespace jobman;


class Admin_Page_Edit_Job extends Admin_Page {

	function get_details() {
		return array(
			'menu_title' => __( 'Add Job', 'jobman' ), 
			'capability' => 'publish_posts',
			'menu_slug' => 'jobman-edit-job'
		);
	}	
	
	function render() {
		// Work out what we're editing
		$jobid = array_key_exists( 'jobid', $_REQUEST ) ? $_REQUEST['jobid'] : 'new';
		if ( 'new' == $jobid ) {
			$page_title = __( 'Job Manager : Add Job', 'jobman' );
			$submit = __( 'Create Job', 'jobman' );
			$job = array();
			$display_jobid = __( 'New', 'jobman' );
		} else {
			$page_title = __( 'Job Manager : Edit Job', 'jobman' );
			$submit = __( 'Update Job', 'jobman' );
			$display_jobid = $jobid;
			// TODO get job details			
		}

		// See if there's a failed job submit to report
		$errors = Job::get_errors();
		if ( ! is_null( $errors ) )
			$job = $_REQUEST;	// re-render the request parameters

		if ( array_key_exists( 'message', $_REQUEST ) ) {
			echo $_REQUEST['message'];
		}

		// Render the page
		?>
			<div class="wrap">
			<?php form_open( admin_url( 'admin.php?page=jobman-edit-job' ), "jobman-edit-job-$jobid" ); ?>
				<input type="hidden" name="jobid" value="<?php echo $jobid ?>" />
				<h2><?php echo $page_title ?></h2>
				<table id="jobman-job-edit" class="form-table">
					<?php 				
						// Job ID
						field_open( __( 'Job ID', 'jobman' ) ); 
						echo $display_jobid;
						field_close();
	
						// Categories
						field_open( __( 'Categories', 'jobman' ), 'jobman-categories-list' );
						echo 'TODO: Implement.';
						field_close( __( 'Categories that this job belongs to. It will be displayed in the job list for each category selected.', 'jobman' ) );

						// Icon
						field_open( __( 'Icon', 'jobman' ), 'jobman-icons-list' ); 
						render_radio_list( 'jobman-icon', $job['jobman-icon'], array(
							array( '', __( 'No icon', 'jobman' ) ),
							// More icons get inserted in this array later.
						) );
						field_close( __( 'Icon to display for this job in the Job List', 'jobman' ) );
						
						// Title
						field_open( __( 'Title', 'jobman' ) );
						render_text_field( 'jobman-title', $job['jobman-title'] );
						field_close( '', $errors['jobman-title'] );
						
						// Custom Fields
						$field_set = Job::get_field_set();
						$field_set->render( $job, $errors );
					?>					
				</table>				
				
				<input type="submit" value="<?php echo $submit ?>" />
			</form>
		<?php
	}
	
	function handle_submit() {
		$jobid = $_REQUEST['jobid'];
		check_admin_referer( "jobman-edit-job-$jobid" );		
		
		if ( 'new' == $jobid ) {
			// Create a new job
			if ( Job::create( $_REQUEST ) ) {
				// On successful creation, redirect to main jobs list with a created message.
				wp_redirect( admin_url( 'admin.php?page=jobman-list-jobs&created=1' ) );
				exit;
			}	// Fall through to default rendering behaviour on failure
		} else {
			//	TODO: Code to update exiating jobs goes here.
		}
	}

};

?>