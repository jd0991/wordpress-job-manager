<?php namespace jobman;


class Job {

	private static $custom_fields;
	private static $errors;

	static function get_errors() {
		return self::$errors;
	}

	static function get_field_set() {
		if ( is_null( self::$custom_fields ) )
			self::$custom_fields = new Custom_Field_Set( 'job_fields' );
		return self::$custom_fields;
	}
	
	static function create( $args ) {
		// TODO: MOAR VALIDATION
		self::$errors = array();
		
		// Validate job title.
		$title = array_key_exists( 'jobman-title', $args ) ? $args['jobman-title'] : '';
		if ( '' == $title )
			self::$errors['jobman-title'] = 'Must not be blank!';
			
		// Validate custom fields		
		self::$errors = array_merge( self::$errors, self::get_field_set()->validate( $args ) );

		// Return false if any validation errors
		if ( count( self::$errors ) )
			return false;
		
		// TODO: Actually create the job.
		
		return true;
	}
	


/*
	static function create( $title, $start_date, $end_date, $icon, $email, $highlight = 'no-highlight' ) {
		$page = array(
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_status' => 'publish',
			'post_content' => '',
			'post_name' => strtolower( str_replace( ' ', '-', $title ) ),
			'post_title' => stripslashes( html_entity_decode( $title ) ),
			'post_type' => 'jobman_job',
			'post_date' => $start_date,
			'post_date_gmt' => $start_date,
			'post_parent' => Options::get( 'main_page' )
		);
	}
*/	

}

?>