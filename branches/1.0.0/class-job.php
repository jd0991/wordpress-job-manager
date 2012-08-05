<?php namespace jobman;


class Job {

	private static $custom_fields;

	static function get_field_set() {
		if ( is_null( self::$custom_fields ) )
			self::$custom_fields = new Custom_Field_Set( 'job_fields' );
		return self::$custom_fields;
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