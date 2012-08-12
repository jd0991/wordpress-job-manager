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
	function render( $value, $error ) {
		$name = 'jobman-field-' . $this->definition['id'];
		$type = $this->definition['type'];
		$description = $this->definition['description'];
		$col_span = ( 'textarea' == $type && '' == $description ) ? 2 : 1;
	
		field_open( $this->definition['label'], '', $col_span );
		switch ( $type ) {
			case 'text': 
				render_text_field( $name, $value );
				break;
				
			case 'date':
				render_date_picker( $name, $value );
				break;
				
			case 'textarea':
				render_text_area( $name, $value );
				break;
			
			default:
				echo 'OH NOES! Unknown: ' . $type;
				break;
		}
		field_close( $description, $error );
	}
	
	// Validate a single value against this field type
	function validate( $value ) {
		switch( $type ) {
			case 'date':
				if ( '' != $value && ! preg_match( '/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $value ) )
					return 'Invalid date!';
				break;
		}	
	
		return null;
	}
	
}

/*

uasort( $fields, 'jobman_sort_fields' );
		foreach( $fields as $id => $field ) {
			if( 'new' == $jobid )
				$data = $field['data'];
			else if( array_key_exists( "data$id", $jobdata ) )
				$data = $jobdata["data$id"];
			else
				$data = '';

			if( 'heading' != $field['type'] )
				echo '<tr>';
				
			if( ! array_key_exists( 'description', $field ) )
				$field['description'] = '';
			
			switch( $field['type'] ) {
				case 'text':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th>";
					else
						echo '<td class="th"></td>';
					
					echo "<td><input type='text' name='jobman-field-$id' value='$data' /></td>";
					echo "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'radio':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th><td>";
					else
						echo '<td class="th"></td><td>';
					
					$values = split( "\n", strip_tags( $field['data'] ) );
					$display_values = split( "\n", $field['data'] );
					
					foreach( $values as $key => $value ) {
						$checked = '';
						if( $value == $data )
							$checked = ' checked="checked"';
						echo "<input type='radio' name='jobman-field-$id' value='" . trim( $value ) . "'$checked /> {$display_values[$key]}<br/>";
					}
					echo '</td>';
					echo "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'checkbox':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th><td>";
					else
						echo '<td class="th"></td><td>';

					$values = split( "\n", strip_tags( $field['data'] ) );
					$display_values = split( "\n", $field['data'] );
					
					if( 'new' == $jobid )
						$data = array();
					else
						$data = split( "\n", strip_tags( $data ) );
					
					foreach( $values as $key => $value ) {
						$value = trim( $value );
						$checked = '';
						if( in_array( $value, $data ) )
							$checked = ' checked="checked"';
						echo "<input type='checkbox' name='jobman-field-{$id}[]' value='$value'$checked /> {$display_values[$key]}<br/>";
					}
					echo '</td>';
					echo "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'textarea':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th>";
					else
						echo '<td class="th"></td>';

					if( '' == $field['description'] )
						echo "<td colspan='2'>";
					else
						echo '<td>';

					if( user_can_richedit() && version_compare( $wp_version, '3.3-aortic-dissection', '<' )) {
						echo "<p id='field-toolbar-$id' class='jobman-editor-toolbar'><a class='toggleHTML'>" . __( 'HTML', 'jobman' ) . '</a><a class="active toggleVisual">' . __( 'Visual', 'jobman' ) . '</a></p>';
						echo "<textarea class='large-text code jobman-editor jobman-field-$id' name='jobman-field-$id' id='jobman-field-$id' rows='7'>$data</textarea></td>";
					}
					else {
						$settings = array(
							'editor_class' => "large-text code jobman-editor jobman-field-$id"
						);
						wp_editor( $data, "jobman-field-$id", $settings );
					}

					if( '' == $field['description'] )
						echo '</tr>';
					else
						echo "<td><span class='description'>{$field['description']}</span></td></tr>";

					break;
				case 'date':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th>";
					else
						echo '<td class="th"></td>';

					echo "<td><input type='text' class='datepicker' name='jobman-field-$id' value='$data' /></td>";
					echo "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'file':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th>";
					else
						echo '<td class="th"></td>';

					echo '<td>';
					echo "<input type='file' name='jobman-field-$id' />";

					if( ! empty( $data ) ) {
						echo '<br/><a href="' . wp_get_attachment_url( $data ) . '">' . wp_get_attachment_url( $data ) . '</a>';
						echo "<input type='hidden' name='jobman-field-current-$id' value='$data' />";
						echo "<br/><input type='checkbox' name='jobman-field-delete-$id' value='1' />" . __( 'Delete File?', 'jobman' );
					}

					echo "</td>";
					echo "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'heading':
					echo '</table>';
					echo "<h3>{$field['label']}</h3>";
					echo "<table>";
					$tablecount++;
					$totalrowcount--;
					$rowcount = 0;
					break;
				case 'html':
					echo "<td colspan='3'>$data</td></tr>";
					break;
				case 'blank':
					echo '<td colspan="3">&nbsp;</td></tr>';
					break;
			}
			
			$previd = "jobman-field-$id";

*/

?>