<?php namespace jobman;

function setup_database() {

	// Ensure the main jobs page exists
	$main_page_id = Options::get( 'main_page' );
	if ( is_null( $main_page_id ) || is_null( get_page( $main_page_id ) ) ) {
		$main_page_id = wp_insert_post( array(
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_status' => 'publish',
			'post_content' => '',
			'post_type' => 'page',
			'post_name' => 'jobs',
			'post_title' => __( 'Jobs Listing', 'jobman' ),
			'post_content' => __( 'Hi! This page is used by your Job Manager plugin as a base. Feel free to change settings here, but please do not delete this page. Also note that any content you enter here will not show up when this page is displayed on your site.', 'jobman' ),
		) );
		Options::set( 'main_page', $main_page_id );
	}
	
}

function create_default_fields() {

	$job_fields = Job::get_field_set();
	
	Custom_Field::create( $job_fields, array(
		'label' => 'Start Date',
		'type' => 'date',
		'sortorder' => 0,
		'description' => __( 'The date that the job starts. For positions available immediately, leave blank.', 'jobman' ),
	) );	
	
	Custom_Field::create( $job_fields, array(
		'label' => __( 'Salary', 'jobman' ),
		'type' => 'text',
		'sortorder' => 0,
		'description' => ''
	) );

	Custom_Field::create( $job_fields, array(
		'label' => __( 'Start Date', 'jobman' ),
		'type' => 'date',
		'sortorder' => 1,
		'description' => __( 'The date that the job starts. For positions available immediately, leave blank.', 'jobman' )
	) );

	Custom_Field::create( $job_fields, array(
		'label' => __( 'End Date', 'jobman' ),
		'type' => 'date',
		'sortorder' => 2,
		'description' =>  __( 'The date that the job finishes. For ongoing positions, leave blank.', 'jobman' )
	) );

	Custom_Field::create( $job_fields, array(
		'label' => __( 'Location', 'jobman' ),
		'type' => 'text',
		'sortorder' => 3,
		'description' => ''
	) );

	Custom_Field::create( $job_fields, array(
		'label' => __( 'Job Information', 'jobman' ),
		'type' => 'textarea',
		'sortorder' => 4,
		'description' => ''
	) );
}

?>