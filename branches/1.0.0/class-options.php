<?php namespace jobman;

class Options {

	private static $opts = null;
	private static $instance;
	private static $changed = false;
	
	function __destruct() {
		if (self::$changed)
			Options::save();
	}
	
	private static function load() {
		if ( self::$opts != null )
			return;

		// Instance is used to auto-save on destruct.
		self::$instance = new Options();
		self::$opts = get_option( 'jobman_options', array() );
	}
	
	private static function save() {
		update_option( 'jobman_options', self::$opts );
	}
	
	//	Tell the options that they need to save themselves at the end of the current HTTP request	
	static function save_later() {
		self::$changed = true;
	}
	
	static function &get( $key, $default = null ) {
		self::load();

		if ( ! array_key_exists( $key, self::$opts ) )
			self::$opts[ $key ] = $default;
		
		return self::$opts[ $key ];
	}
	
	static function &get_all() {
		self::load();
		return self::$opts;
	}
	
	static function set( $key, $value ) {
		self::$opts[ $key ] = $value;
		self::$changed = true;
	}
	
	static function set_multi( $args ) {
		self::$opts = array_merge( self::$opts, $args );
		self::$changed = true;
	}

}

?>