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
				<p><b>YAY</b> You've created a job. GOOD FOR YOU!</p>
			<?php
		}
	
		?>
			Herpy derpy list of jerbs goes hurr.
		<?php	
	}
	
};

?>