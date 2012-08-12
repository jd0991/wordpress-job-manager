<?php namespace jobman;


class Admin_Page_Settings extends Admin_Page {

	function get_details() {
		return array(
			'menu_title' => __( 'Settings', 'jobman' ), 
			'capability' => 'manage_options',
			'menu_slug' => 'jobman-conf'
		);
	}	
	
	function render() {
		?>
			<h3>I AM A BANANA.</h3>
		<?php	
	}
	
};

?>