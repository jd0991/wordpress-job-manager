<?php

if( ! defined( 'JOBMAN_URL' ) )
	define( 'JOBMAN_URL', '/wp-content/themes/vip/' . JOBMAN_VIP_SITE . '/plugins/job-manager' );

	
function jobman_change_loggedin_html( $html ) {
	return NULL;
}

function jobman_change_login_html( $html ) {
	$loginurl = 'http://automattic.wordpress.com/wp-login.php?redirect_to=';
	$registerurl = 'http://en.wordpress.com/signup/?redirect_to=';
	
	$data = get_posts( 'post_type=jobman_app_form&numberposts=1' );
	if( count( $data ) > 0 ) {
		$applypage = $data[0];
	
		$applypageurl = get_page_link( $applypage->ID );
		$loginurl .= $applypageurl;
		$registerurl .= $applypageurl;
	}
	
	$html = "<p>Do you have a WordPress.com account? If so, please <a href='$loginurl'>login here</a>. If not, please <a href='$registerurl'>register one now</a>!</p>";
	
	return $html;
}

function jobman_change_register_html( $html ) {
	return NULL;
}

function jobman_change_pleaseregister_html( $html ) {
	return NULL;
}

add_filter( 'jobman_loggedin_html', 'jobman_change_loggedin_html' );
add_filter( 'jobman_login_html', 'jobman_change_login_html' );
add_filter( 'jobman_register_html', 'jobman_change_register_html' );
add_filter( 'jobman_pleaseregister_html', 'jobman_change_pleaseregister_html' );
?>