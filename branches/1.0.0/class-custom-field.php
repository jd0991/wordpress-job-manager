<?php namespace jobman;


class Custom_Field {

	private $field_set;
	private $definition;
	private static $defaults = array(
		'type' => 'text',
		'data' => '',
		'mandatory' => 0,
	);
	
	static function create( $field_set, $definition ) {
		$field = new Custom_Field();
		$field->field_set = $field_set;
		$field->definition = array_merge( self::$defaults, $definition );

		$field_set->add_field($field);
		
		return $field;
	}
	
	static function wrap_existing( $field_set, &$definition ) {
		$field = new Custom_Field();
		$field->field_set = $field_set;
		$field->definition = &$definition;
		return $field;
	}
	
	function __get( $key ) {
		return array_key_exists( $key, $this->definition ) ? $this->definition[$key] : null;
	}
	
	function __set( $key, $value ) {
		$this->definition[$key] = $value;

		//	My definition array is a child of the options; tell the options that they need to be saved.		
		Options::save_later();
	}
	
	function &get_definition() {
		return $this->definition;
	}
	
}

?>