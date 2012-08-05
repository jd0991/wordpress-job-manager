<?php namespace jobman;


class Custom_Field {

	private $field_set;
	private $definition;
	private static $defaults = array(
		'type' => 'text',
		'data' => '',
		'mandatory' => 0,
	);
	
	// Create a new custom field, automatically adding it to the specified field set
	static function create( $field_set, $definition ) {
		$field = new Custom_Field();
		$field->field_set = $field_set;
		$field->definition = array_merge( self::$defaults, $definition );

		$field_set->add_field($field);
		
		return $field;
	}
	
	// Used by fieldsets during initialization, this wraps an already configured field
	static function wrap_existing( $field_set, &$definition ) {
		$field = new Custom_Field();
		$field->field_set = $field_set;
		$field->definition = &$definition;
		return $field;
	}
	
	// Get elements from the field's definition
	function __get( $key ) {
		return array_key_exists( $key, $this->definition ) ? $this->definition[$key] : null;
	}
	
	// Set elements form the field's definition (automatically saving to options)	
	function __set( $key, $value ) {
		$this->definition[$key] = $value;

		// My definition array is a child of the options; tell the options that they need to be saved.		
		Options::save_later();
	}
	
	// Gets the full definition hash
	function &get_definition() {
		return $this->definition;
	}
	
	// Renders this field
	function render() {
		field_open( $this->definition['label'] );
		echo "<input type='text'>";
		field_close( $this->definition['description'] );
	}
	
}

?>