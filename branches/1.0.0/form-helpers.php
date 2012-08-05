<?php namespace jobman;

// ************ Form-building helpers ************

// Echoes the beginning of a <form> tag, and throws in some hidden <input>s to help recognize submission
function form_open( $action, $nonce ) {
	?>
		<form action="<?php echo $action ?>" enctype="multipart/form-data" method="post">
			<input type="hidden" name="jobmansubmit" value="1" />
	<?php
	wp_nonce_field( $nonce ); 
}

// Echoes the opening table row line appropriate for an admin form	
function field_open( $label, $div_class = '' ) {
	?>
		<tr>
			<th scope="row"><?php echo $label ?></th>
			<td><div class="<?php echo $div_class ?>">
	<?php
}

// Echoes the closing table row line appropriate for an admin form
function field_close( $description = '' ) {
	?>
			</div></td>
			<td class="description"><?php echo $description ?></td>
		</tr>
	<?php
}	

// Render a set of radio buttons, with a preselected entry
function render_radio_list( $name, $selected_option, $options ) {
	foreach ( $options as $option ) {
		$checked = ( $selected_option == $option[0] ) ? 'checked="checked"' : '';
		?>
			<label>
				<input type="radio" name="<?php echo $name ?>" value="<?php echo $option[0] ?>" <?php echo $checked ?> />
				<?php echo $option[1] ?>
			</label>
		<?php
	}
}


?>