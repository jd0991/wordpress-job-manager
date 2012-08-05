<?php namespace jobman;

class Options {

	private static $opts = null;
	private static $instance;
	private static $changed = false;
	
	// Tell the options that they need to save themselves at the end of the current HTTP request	
	static function save_later() {
		self::$changed = true;
	}
	
	// Get a JobManager option by key, returning a reference if you're inclined to make changes
	static function &get( $key, $default = null ) {
		self::load();

		if ( ! array_key_exists( $key, self::$opts ) )
			self::$opts[ $key ] = $default;
		
		return self::$opts[ $key ];
	}
	
	// Returns a reference to the whole options hash
	static function &get_all() {
		self::load();
		return self::$opts;
	}
	
	// Sets a single value in the options hash
	static function set( $key, $value ) {
		self::$opts[ $key ] = $value;
		self::$changed = true;
	}
	
	// Set multiple values in the options hash
	static function set_multi( $args ) {
		self::$opts = array_merge( self::$opts, $args );
		self::$changed = true;
	}
	
	// Returns true if the given option name is set
	static function is_set( $key ) {
		self::load();
		return array_key_exists( $key, self::$opts );
	}
	
	// ************ Private members ************

	// Make sure any unsaved changes to options are saved during singleton destruction (end of HTTP request)	
	function __destruct() {
		if (self::$changed)
			Options::save();
	}
	
	// Lazy-load options hash on first use
	private static function load() {
		if ( self::$opts != null )
			return;

		// Instance is used to auto-save on destruct.
		self::$instance = new Options();
		self::$opts = get_option( 'jobman_options', array() );
	}
	
	// Actually save the options hash. Generally called during the destructor.
	private static function save() {
		update_option( 'jobman_options', self::$opts );
	}

}

?>