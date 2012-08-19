<?php namespace jobman;


class Admin_Page_Jobs extends Admin_Page {

	function get_details() {
		return array(
			'menu_title' => __( 'Jobs', 'jobman' ), 
			'capability' => 'publish_posts',
			'menu_slug' => 'jobman-list-jobs'
		);
	}	
	
	function render() {
		if ( array_key_exists( 'created', $_REQUEST ) ) {
			?>
				<p class="notification"><b>YAY</b> You've created a job. GOOD FOR YOU!</p>
			<?php
		}
		
		$jobs = Job::get_all();
		$field_set = Job::get_field_set();
		$field_count = 0;
			
		?>
			<h2>Job Manager: Jobs List</h2>
			
			<table class="widefat page fixed" cellspacing="0">
				<thead>
					<tr>
						<th scope="col" class="column-cb check-column"><input type="checkbox"></th>
						<th scope="col"><?php _e( 'Title', 'jobman' ) ?></th>
						<th scope="col"><?php _e( 'Categories', 'jobman' ) ?></th>
						<?php
							foreach ( $field_set->get_fields() as $field ) {
								if ( $field->list_display() ) {
									$field_count++;
									?>
										<th scope="col"><?php echo $field->label ?></th>
									<?php
								}
							}
						?>
						<th scope="col"><?php _e( 'Display Dates', 'jobman' ) ?></th>
						<th scope="col"><?php _e( 'Applications', 'jobman' ) ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ( $jobs as $job ) {
							?>
								<tr>
									<td><input type="checkbox"></td>
									<td><?php echo $job->get_title() ?></td>
								</tr>
							<?php
						}
					?>
				</tbody>
			</table>

		<?php	
	}
	
};

?>