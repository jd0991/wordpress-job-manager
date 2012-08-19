<?php namespace jobman;


class Job {

	private static $custom_fields;
	private static $errors;
	
	private $post_id;
	private $properties;

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

		// Actually create the post...
		$job = new Job( $args );
		$job->post_id = wp_insert_post( $job->get_post_array() );
		$job->save_meta_data();
				
		return true;
	}
	
	static function get_all() {
		$posts = get_posts( 'post_type=jobman_job&numberposts=-1&post_status=publish,draft,future' );
		$jobs = array();
		foreach ($posts as $post) {
			array_push( $jobs, Job::from_post( $post ) );
		}
		return $jobs;
	}
	
	function is_highlighted() {
		return array_key_exists( 'highlighted', $this->properties ) ? $this->properties['highlighted'] : false;
	}
	
	function get_title() {
		return $this->properties['jobman-title'];
	}	
	
	// ************ Private members ************
	
	// Construct a Job object from a hash describing one. To get a job externally, use a static method from above.
	private function __construct( $properties ) {
		$this->properties = $properties;
	}
	
	// Construct a Job object from the provided post hash.
	private static function from_post( $post ) {
		$job = new Job( array(
			'jobman-title' => $post->post_title,
			'jobman-displaystartdate' => $post->post_date
		) );
		$job->post_id = $post->ID;
		return $job;
	}
	
	// Get an array describing the basic post properties for this Job.
	private function get_post_array() {
		return array(
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'post_status' => 'publish',
			'post_content' => '',
			'post_name' => strtolower( str_replace( ' ', '-', $this->properties['jobman-title'] ) ),
			'post_title' => stripslashes( html_entity_decode( $this->properties['jobman-title'] ) ),
			'post_type' => 'jobman_job',
			'post_date' => $this->propeties['jobman-displaystartdate'],
			'post_date_gmt' => $this->propeties['jobman-displaystartdate'],
			'post_parent' => Options::get( 'main_page' )
		);
	}
	
	// Save the metadata with this job's post. Includes custom fields and a fistful of properties.
	private function save_meta_data() {
		$this->upsert_meta( 'displayenddate', stripslashes( $this->properties['jobman-displayenddate'] ) );
		$this->upsert_meta( 'iconid', $this->properties['icon'] );
		$this->upsert_meta( 'email', $this->properties['jobman-email'] );
		$this->upsert_meta( 'highlighted', $this->is_highlighted() );

		//	Custom data
		foreach ( $this->properties as $key => $value ) {
			if ( preg_match( '/^data[0-9]+$/', $key ) ) {
				$this->upsert_meta( $key, $data );
			}
		}
	}
	
	private function upsert_meta( $field, $data ) {
		add_post_meta( $this->post_id, $field, $data, true ) or update_post_meta( $this->post_id, $field, $data );
	}

}

?>