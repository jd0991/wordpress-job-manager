<?php namespace jobman;


class Custom_Field_Set {

	private $option_key;  // Key within jobman_options this field set is stored in.
	private $definitions; // Array of raw field definition arrays, as stored in options.
	private $fields;      // Array of nice Custom_Field objects
	
	function __construct( $option_key ) {
		$this->option_key = $option_key;
	}
	
	// Returns an associative array of Custom_Field objects that apply to this set.
	function get_fields() {
		$this->load_fields();
		return $this->fields;
	}
	
	// Adds a new field to this set, assigning it a new id automagically.
	function add_field( $field ) {
		$this->load_fields();
		
		$next_id = $this->get_next_id();
		$field->id = $next_id;
		
		// Insert this field into both the raw config, and the nice Custom_Field list
		$this->fields[ $next_id ] = $field;
		$this->definitions[ $next_id ] = $field->get_definition();
		
		// My definition array is a child of the options; tell the options that they need to be saved.		
		Options::save_later();
	}
	
	// ************ Private members ************
	
	// Lazy-load custom fields the first time they're needed.
	private function load_fields() {
		if ( ! is_null( $this->fields ) )
			return;
			
		//	Load the raw configuration data
		$this->definitions = &Options::get( $this->option_key, array() );
		
		//	Generate a nice array of Custom_Fields to play with later
		$this->fields = array();
		foreach ($this->definitions as $id => $definition) {
			$field = new Custom_Field( $definition );
			$field->id = $id;
			$this->fields[ $id ] = $field;
		}
	}
	
	//	Returns the next unused id for a new field
	private function get_next_id() {
		return empty( $this->fields ) ? 0 : max( array_keys( $this->fields ) ) + 1;
	}
}

?>