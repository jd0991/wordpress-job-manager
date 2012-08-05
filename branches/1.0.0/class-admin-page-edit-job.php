<?php namespace jobman;


class Admin_Page_Edit_Job extends Admin_Page {

	function get_submenu_options() {
		return array(
			'menu_title' => __( 'Add Job', 'jobman' ), 
			'capability' => 'publish_posts',
			'menu_slug' => 'jobman-add-job'
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

		// Render the page
		?>
			<div class="wrap">
			<?php form_open( $this->menu_slug, "jobman-edit-job-$jobid" ); ?>
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
						render_radio_list( 'jobman-icon', '', array(
							array( '', __( 'No icon', 'jobman' ) ),
							// More icons get inserted in this array later.
						) );
						field_close( __( 'Icon to display for this job in the Job List', 'jobman' ) );
						
						// Custom Fields
						$field_set = Job::get_field_set();
						$field_set->render();
					?>					
				</table>				
				
				<input type="submit" value="<?php echo $submit ?>" />
			</form>
		<?php
	}
	
	function handle_submit() {
		?>
			BAR!
		<?php
	}

};

?>