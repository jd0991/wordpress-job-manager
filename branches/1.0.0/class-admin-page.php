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
	
	// Callback to specify which JS scripts need to be rendered in the page's headers
	function enqueue_scripts() {
		wp_deregister_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui' );
		wp_enqueue_script( 'jquery-ui-datepicker', JOBMAN_URL . '/js/jquery-ui-datepicker.js', false, JOBMAN_VERSION );
		wp_enqueue_script( 'jobman-forms', JOBMAN_URL . '/js/forms.js', false, JOBMAN_VERSION );
	}

	// Callback to specify which CSS files need to be rendered in the page's headers
	function enqueue_styles() {
		global $wp_styles;
		
		wp_enqueue_style( 'jobman-admin', JOBMAN_URL . '/css/admin.css', false, JOBMAN_VERSION, 'all' );
		wp_enqueue_style( 'jobman-admin-ie7', JOBMAN_URL . '/css/admin-ie7.css', false, JOBMAN_VERSION, 'all' );
		wp_enqueue_style( 'jobman-admin-print', JOBMAN_URL . '/css/admin-print.css', false, JOBMAN_VERSION, 'print' );
		wp_enqueue_style( 'jobman-admin-print-ie7', JOBMAN_URL . '/css/admin-print-ie7.css', false, JOBMAN_VERSION, 'print' );
		wp_enqueue_style( 'dashboard' );
		
		$wp_styles->add_data( 'jobman-admin-print-ie7', 'conditional', 'lte IE 7' );
		$wp_styles->add_data( 'jobman-admin-ie7', 'conditional', 'lte IE 7' );
	}

	// Callback to add some extra sauce to the admin headers
	function print_header() {
		//	Throw in some information for the JavaScript to consume.
		?>
			<script type="text/javascript">
				var jobman_config = {
					url: '<?php echo JOBMAN_URL ?>'
				};
			</script>
		<?php
	}

	// ************ Private members ************
	
	// Constructor adds the page to the admin menu, if applicable
	private function __construct() {
		$submenu = $this->get_submenu_options();
		if ( null != $submenu ) {
			$menu_slug = $submenu['menu_slug'];
			$page = add_submenu_page( 'jobman-conf', __( 'Job Manager', 'jobman' ), $submenu['menu_title'], $submenu['capability'], $submenu['menu_slug'], array( $this, 'request_page' ) );
			
			// Set hooks to load JS and CSS for the page.
			add_action( "admin_print_styles-$page", array( $this, 'enqueue_styles' ) );
			add_action( "admin_print_scripts-$page", array( $this, 'enqueue_scripts' ) );
			add_action( "admin_head-$page", array( $this, 'print_header' ) );
		}
	}

};

?>