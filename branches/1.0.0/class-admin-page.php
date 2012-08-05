<?php namespace jobman;


class Admin_Page {

	protected $menu_slug;

	// Hook callback to setup all admin menus
	static function setup_menus() {
		// Setup the root admin menu page
		add_menu_page( __( 'Job Manager', 'jobman' ), __( 'Job Manager', 'jobman' ), 'publish_posts', 'jobman-conf', 'jobman_conf' );
	
		// Setup the sub-pages
		new Admin_Page_Edit_Job();
	}
	
	// Default implementation of get_submenu_options; returns null for no submenu
	function get_submenu_options() {
		return null;
	}

	// Called every time this Admin page is hit; work out whether submitting a form or rendering a page, and call appropriate subclass methods
	function request_page() {
		if ( array_key_exists( 'jobmansubmit', $_REQUEST ) ) {
			$this->handle_submit();
		} else {
			$this->render();
		}
	}

	// ************ Private members ************
	
	// Constructor adds the page to the admin menu, if applicable
	private function __construct() {
		$submenu = $this->get_submenu_options();
		if ( null != $submenu ) {
			$menu_slug = $submenu['menu_slug'];
			$page = add_submenu_page( 'jobman-conf', __( 'Job Manager', 'jobman' ), $submenu['menu_title'], $submenu['capability'], $submenu['menu_slug'], array( $this, 'request_page' ) );
		}
	}

};

?>