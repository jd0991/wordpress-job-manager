<?php namespace jobman;


class Admin_Page_Settings extends Admin_Page {

	function get_details() {
		return array(
			'menu_title' => __( 'Settings', 'jobman' ), 
			'capability' => 'manage_options',
			'custom_render' => true,
			'menu_slug' => 'jobman-settings'
		);
	}	
	
	function render() {
		?>
			<div class="wrap">
				<h3>I AM A BANANA.</h3>
			</div>
		<?php	
	}
	
};

?>