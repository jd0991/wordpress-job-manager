<?php //encoding: utf-8

// Admin Settings
require_once( dirname( __FILE__ ) . '/admin-settings.php' );
// Frontend Display Settings
require_once( dirname( __FILE__ ) . '/admin-frontend-settings.php' );
// Job Form Setup
require_once( dirname( __FILE__ ) . '/admin-jobs-settings.php' );
// Job management
require_once( dirname( __FILE__ ) . '/admin-jobs.php' );
// Application form setup
require_once( dirname( __FILE__ ) . '/admin-application-form.php' );
// Applications
require_once( dirname( __FILE__ ) . '/admin-applications.php' );
// Emails
require_once( dirname( __FILE__ ) . '/admin-emails.php' );


function jobman_admin_setup() {
	// Setup the admin menu item
	$file = WP_PLUGIN_DIR . '/' . JOBMAN_FOLDER . '/job-manager.php';
	$pages = array();
	add_menu_page( __( 'Job Manager', 'jobman' ), __( 'Job Manager', 'jobman' ), 'publish_posts', $file, 'jobman_conf' );
	$pages[] = add_submenu_page( $file, __( 'Job Manager', 'jobman' ), __( 'Admin Settings', 'jobman' ), 'manage_options', $file, 'jobman_conf' );
	$pages[] = add_submenu_page( $file, __( 'Job Manager', 'jobman' ), __( 'Display Settings', 'jobman' ), 'manage_options', 'jobman-display-conf', 'jobman_display_conf' );
	$pages[] = add_submenu_page( $file, __( 'Job Manager', 'jobman' ), __( 'App. Form Settings', 'jobman' ), 'manage_options', 'jobman-application-setup', 'jobman_application_setup' );
	$pages[] = add_submenu_page( $file, __( 'Job Manager', 'jobman' ), __( 'Job Form Settings', 'jobman' ), 'manage_options', 'jobman-job-setup', 'jobman_job_setup' );
	$pages[] = add_submenu_page( $file, __( 'Job Manager', 'jobman' ), __( 'Add Job', 'jobman' ), 'publish_posts', 'jobman-add-job', 'jobman_add_job' );
	$pages[] = add_submenu_page( $file, __( 'Job Manager', 'jobman' ), __( 'List Jobs', 'jobman' ), 'publish_posts', 'jobman-list-jobs', 'jobman_list_jobs' );
	$pages[] = add_submenu_page( $file, __( 'Job Manager', 'jobman' ), __( 'List Applications', 'jobman' ), 'read_private_pages', 'jobman-list-applications', 'jobman_list_applications' );
	$pages[] = add_submenu_page( $file, __( 'Job Manager', 'jobman' ), __( 'List Emails', 'jobman' ), 'read_private_pages', 'jobman-list-emails', 'jobman_list_emails' );

	// Load our header info
	foreach( $pages as $page ) {
		add_action( "admin_print_styles-$page", 'jobman_admin_print_styles' );
		add_action( "admin_print_scripts-$page", 'jobman_admin_print_scripts' );
		add_action( "admin_head-$page", 'jobman_admin_header' );
	}
}

function jobman_plugin_row_meta( $links, $file ) {
	if( JOBMAN_BASENAME == $file && ! get_option( 'pento_consulting' ) ) {
		$links[] = '<a href="http://www.amazon.com/wishlist/1ORKI9ZG875BL">' . __( 'My Amazon Wish List', 'jobman' ) . '</a>';
		$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=gary%40pento%2enet&item_name=WordPress%20Plugin%20(Job%20Manager)&item_number=Support%20Open%20Source&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8">' . __( 'Donate with PayPal', 'jobman' ) . '</a>';
	}
	
	return $links;
}

function jobman_admin_print_styles() {
	wp_enqueue_style( 'jobman-admin', JOBMAN_URL . '/css/admin.css', false, JOBMAN_VERSION, 'all' );
	wp_enqueue_style( 'jobman-admin-print', JOBMAN_URL . '/css/admin-print.css', false, JOBMAN_VERSION, 'print' );
	wp_enqueue_style( 'dashboard' );
}

function jobman_admin_print_scripts() {
	wp_enqueue_script( 'jobman-admin', JOBMAN_URL . '/js/admin.js', false, JOBMAN_VERSION );
	wp_enqueue_script( 'jquery-ui-datepicker', JOBMAN_URL . '/js/jquery-ui-datepicker.js', array( 'jquery-ui-core' ), JOBMAN_VERSION );
	wp_enqueue_script( 'dashboard' );
}

function jobman_admin_header() {
?>
<script type="text/javascript"> 
//<![CDATA[
addLoadEvent(function() {
	jQuery(".datepicker").datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true, gotoCurrent: true});
	jQuery(".column-cb > *").click(function() { jQuery(".check-column > *").attr('checked', jQuery(this).is(':checked')) } );
	
	jQuery("div.star-holder img").click(function() {
	    var class = jQuery(this).parent().attr("class");
		var count = class.replace("star star", "");
		jQuery(this).parent().parent().find("input[name=jobman-rating]").attr("value", count);
		jQuery(this).parent().parent().find("div.star-rating").css("width", (count * 19) + "px");
		
        var data = jQuery(this).parent().parent().find("input[name=callbackid]");
        var callback;
        if( data.length > 0 ) {
			callback = {
			        action: 'jobman_rate_application',
			        appid: data[0].value,
			        rating: count
			};
			
			jQuery.post( ajaxurl, callback );
		}
	});
	
	jQuery("div.star-holder img").mouseenter(function() {
	    var class = jQuery(this).parent().attr("class");
		var count = class.replace("star star", "");
		jQuery(this).parent().parent().find("div.star-rating").css("width", (count * 19) + "px");
	});

	jQuery("div.star-holder img").mouseleave(function() {
		var count = jQuery(this).parent().parent().find("input[name=jobman-rating]").attr("value");
		jQuery(this).parent().parent().find("div.star-rating").css("width", (count * 19) + "px");
	});
});

function jobman_reset_rating( application ) {
	jQuery( "#jobman-rating-" + application ).attr("value", 0);
	jQuery( "#jobman-star-rating-" + application ).css("width", "0px");
	
	if( "filter" != application ) {
		callback = {
				action: 'jobman_rate_application',
				appid: application,
				rating: 0
		};
		
		jQuery.post( ajaxurl, callback );
	}
}
//]]>
</script> 
<?php
}

function jobman_print_donate_box() {
?>
		<p><?php _e( "If this plugin helps you find that perfect new employee, I'd appreciate it if you shared the love, by way of my Donate or Amazon Wish List links below.", 'jobman' ) ?></p>
		<ul>
			<li><a href="http://www.amazon.com/wishlist/1ORKI9ZG875BL"><?php _e( 'My Amazon Wish List', 'jobman' ) ?></a></li>
			<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=gary%40pento%2enet&item_name=WordPress%20Plugin%20(Job%20Manager)&item_number=Support%20Open%20Source&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8"><?php _e( 'Donate with PayPal', 'jobman' ) ?></a></li>
		</ul>
<?php
}

function jobman_print_about_box() {
?>
		<ul>
			<li><a href="http://pento.net/"><?php _e( "Gary Pendergast's Blog", 'jobman' ) ?></a></li>
			<li><a href="http://twitter.com/garypendergast"><?php _e( 'Follow me on Twitter!', 'jobman' ) ?></a></li>
			<li><a href="http://pento.net/projects/wordpress-job-manager-plugin/"><?php _e( 'Plugin Homepage', 'jobman' ) ?></a></li>
			<li><a href="http://code.google.com/p/wordpress-job-manager/issues/list"><?php _e( 'Submit a Bug/Feature Request', 'jobman' ) ?></a></li>
		</ul>
<?php
}
?>