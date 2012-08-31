<?php namespace jobman;


class Admin_Page_Debug extends Admin_Page {

	function get_details() {
		return array(
			'menu_title' => __( 'Debug Options', 'jobman' ), 
			'capability' => 'manage_options',
			'custom_render' => true,
			'menu_slug' => 'jobman-debug'
		);
	}	
	
	function render() {
		$action = $_REQUEST['action'];
		if ( 'reset_config' == $action ) {
			delete_option( 'jobman_options' );
			Setup::database();
			Setup::custom_fields();
			?>
				<p class="notification">Config reset, bitches.</p>
			<?php
		}	
	
		?>
			<div class="wrap">
				<h2>JobManager Debug Stuffs</h2>
				
				<ul>
					<li><a href="admin.php?page=jobman-debug&action=reset_config">Reset config</a></li>
				</ul>
			</div>
		<?php	
	}
	
};

?>