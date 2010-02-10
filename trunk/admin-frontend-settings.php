<?php
function jobman_display_conf() {
	if( array_key_exists( 'jobmansortsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-sort-updatedb' );
		jobman_sort_updatedb();
	}
?>
	<div class="wrap">
		<h2><?php _e( 'Job Manager: Display Settings', 'jobman' ) ?></h2>
<?php
	if( ! get_option( 'pento_consulting' ) ) {
		$widths = array( '78%', '20%' );
		$functions = array(
						array( 'jobman_print_sort_box' ),
						array( 'jobman_print_donate_box', 'jobman_print_about_box' )
					);
		$titles = array(
					array( __( 'Job List Sorting', 'jobman' ) ),
					array( __( 'Donate', 'jobman' ), __( 'About This Plugin', 'jobman' ))
				);
	}
	else {
		$widths = array( '49%', '49%' );
		$functions = array(
						array( 'jobman_print_sort_box' ),
						array()
					);
		$titles = array(
					array( __( 'Job List Sorting', 'jobman' ) ),
					array()
				);
	}
	jobman_create_dashboard( $widths, $functions, $titles );
}

function jobman_print_sort_box() {
	$options = get_option( 'jobman_options' );
	?>
		<form action="" method="post">
		<input type="hidden" name="jobmansortsubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-sort-updatedb' ); 
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Sort By:', 'jobman' ) ?></th>
				<td><select name="sort-by">
					<option value=""<?php echo ( '' == $options['sort_by'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Default', 'jobman' ) ?></option>
					<option value="title"<?php echo ( 'title' == $options['sort_by'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Job Title', 'jobman' ) ?></option>
					<option value="dateposted"<?php echo ( 'dateposted' == $options['sort_by'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Date Posted', 'jobman' ) ?></option>
					<option value="closingdate"<?php echo ( 'closingdate' == $options['sort_by'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Closing Date', 'jobman' ) ?></option>
				</select></td>
				<td><span class="description"><?php _e( "Select the criteria you'd like to have job lists sorted by.", 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Sort Order', 'jobman' ) ?></th>
				<td><select name="sort-order">
					<option value=""<?php echo ( '' == $options['sort_order'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Default', 'jobman' ) ?></option>
					<option value="asc"<?php echo ( 'asc' == $options['sort_order'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Ascending', 'jobman' ) ?></option>
					<option value="desc"<?php echo ( 'desc' == $options['sort_order'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Descending', 'jobman' ) ?></option>
				</select></td>
				<td><span class="description">
					<?php _e( "Ascending: Lowest value to highest value, alphabetical or chronological order", 'jobman' ) ?><br/>
					<?php _e( "Descending: Highest value to lowest value, reverse alphabetical or chronological order", 'jobman' ) ?>
				</span></td>
			</tr>
		</table>
		
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Sort Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_sort_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$options['sort_by'] = $_REQUEST['sort-by'];
	$options['sort_order'] = $_REQUEST['sort-order'];

	update_option( 'jobman_options', $options );
}

?>