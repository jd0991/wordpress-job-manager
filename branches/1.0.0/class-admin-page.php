<?php namespace jobman;


class Admin_Page {

	protected $details;
	private static $pages;

	// Called during admin initialization -- check for POSTs, to have a chance to redirect early. 
	static function admin_init() {
		self::create_pages();
		
		if ( array_key_exists( 'jobmansubmit', $_REQUEST ) ) {
			self::$pages[$_REQUEST['page']]->handle_submit();
		}
	}

	// Hook callback to setup all admin menus
	static function setup_menus() {
		self::create_pages();

		// Setup the root admin menu page
		add_menu_page( __( 'Job Manager', 'jobman' ), __( 'Job Manager', 'jobman' ), 'publish_posts', 'jobman-conf', 'jobman_conf' );
	
		// Setup the sub-page menus
		foreach ( self::$pages as $key => $page ) {
			$details = $page->get_details();
			$page->details = $details;
			$page_name = add_submenu_page( 'jobman-conf', __( 'Job Manager', 'jobman' ), $details['menu_title'], $details['capability'], $details['menu_slug'], array( $page, 'request_page' ) );
			
			// Set hooks to load JS and CSS for the page.
			add_action( "admin_print_styles-$page_name", array( $page, 'enqueue_styles' ) );
			add_action( "admin_print_scripts-$page_name", array( $page, 'enqueue_scripts' ) );
			add_action( "admin_head-$page_name", array( $page, 'print_header' ) );
		}
	}
	
	// Called every time this Admin page is hit; work out whether submitting a form or rendering a page, and call appropriate subclass methods
	function request_page() {
		$this->render();
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

	// ************ Protected members ************
	
	// Default behaviour for register_menu; actually does nothing.
	protected function register_menu() {}
	
	// Default implementation of get_submenu_options; returns null for no submenu
	protected function get_details() {}
	
	private static function create_pages() {
		if ( is_null( self::$pages ) ) {
			self::$pages = array(
				'jobman-edit-job' => new Admin_Page_Edit_Job(),
				'jobman-jobs' => new Admin_Page_Jobs()
			);
		}
	}
	
};

?>