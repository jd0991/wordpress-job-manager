<?php namespace jobman;

function init_page_taxonomy() {
	register_post_type( 'jobman_job', array( 
		'description' => __( 'Job Manager Job: Describes an open position at your company.', 'jobman' ),
		'labels' => array(
			'name' => __( 'Jobs', 'jobman' ),
			'singular_name' => __( 'Job', 'jobman' ),
			'add_new' => __( 'Add New', 'jobman' ),
			'all_items' => __( 'All Jobs', 'jobman' ),
			'add_new_item' => __( 'Add New Job', 'jobman' ),
			'edit_item' => __( 'Edit Job', 'jobman' ),
		),
		
		'exclude_from_search' => false, 
		'public' => true, 
		'show_ui' => true, 
		'show_in_menu' => 'jobman-settings',
//		'supports' => array( 'title' ),
		'taxonomies' => array( 'TODO', 'Gotta', 'Add', 'Category', 'Customization' ),
	) );
	register_post_type( 'jobman_joblist', array( 'exclude_from_search' => true ) );
}

?>