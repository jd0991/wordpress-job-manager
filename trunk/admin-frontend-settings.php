<?php
function jobman_display_conf() {
	if( array_key_exists( 'jobmandisplaysubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-display-updatedb' );
		jobman_display_updatedb();
	}
	else if( array_key_exists( 'jobmansortsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-sort-updatedb' );
		jobman_sort_updatedb();
	}
	else if( array_key_exists( 'jobmanwraptextsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-wraptext-updatedb' );
		jobman_wrap_text_updatedb();
	}
?>
	<div class="wrap">
		<h2><?php _e( 'Job Manager: Display Settings', 'jobman' ) ?></h2>
<?php
	if( ! get_option( 'pento_consulting' ) ) {
		$widths = array( '78%', '20%' );
		$functions = array(
						array( 'jobman_print_display_settings_box', 'jobman_print_sort_box', 'jobman_print_wrap_text_box' ),
						array( 'jobman_print_donate_box', 'jobman_print_about_box' )
					);
		$titles = array(
					array( __( 'Display Settings', 'jobman' ), __( 'Job List Sorting', 'jobman' ), __( 'Page Text', 'jobman' ) ),
					array( __( 'Donate', 'jobman' ), __( 'About This Plugin', 'jobman' ))
				);
	}
	else {
		$widths = array( '49%', '49%' );
		$functions = array(
						array( 'jobman_print_display_settings_box', 'jobman_print_wrap_text_box' ),
						array( 'jobman_print_sort_box' )
					);
		$titles = array(
					array( __( 'Display Settings', 'jobman' ), __( 'Page Text', 'jobman' ) ),
					array( __( 'Job List Sorting', 'jobman' ) )
				);
	}
	jobman_create_dashboard( $widths, $functions, $titles );
}

function jobman_print_display_settings_box() {
	$options = get_option( 'jobman_options' );
	?>
		<form action="" method="post">
		<input type="hidden" name="jobmandisplaysubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-display-updatedb' ); 
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Job Manager Page Template', 'jobman' ) ?></th>
				<td colspan="2"><?php printf( __( 'You can edit the page template used by Job Manager, by editing the Template Attribute of <a href="%s">this page</a>.', 'jobman' ), admin_url( 'page.php?action=edit&post=' . $options['main_page'] ) ) ?></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Show summary or full jobs list?', 'jobman' ) ?></th>
				<td><select name="list-type">
					<option value="summary"<?php echo ( 'summary' == $options['list_type'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Summary', 'jobman' ) ?></option>
					<option value="full"<?php echo ( 'full' == $options['list_type'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Full', 'jobman' ) ?></option>
				</select></td>
				<td><span class="description">
					<?php _e( 'Summary: displays many jobs concisely.', 'jobman' ) ?><br/>
					<?php _e( 'Full: allows quicker access to the application form.', 'jobman' ) ?>
				</span></td>
			</tr>
<?php
	if( ! get_option( 'pento_consulting' ) ) {
?>
			<tr>
				<th scope="row"><?php _e( 'Hide "Powered By" link?', 'jobman' ) ?></th>
				<td><input type="checkbox" value="1" name="promo-link" <?php echo ( $options['promo_link'] )?( 'checked="checked" ' ):( '' ) ?>/></td>
				<td><span class="description"><?php _e( "If you're unable to donate, I would appreciate it if you left this unchecked.", 'jobman' ) ?></span></td>
			</tr>
<?php
	}
?>
		</table>
		
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Display Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
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
			<tr>
				<th scope="row"><?php _e( 'Highlighted jobs behaviour', 'jobman' ) ?></th>
				<td><select name="highlighted-behaviour">
					<option value="sticky"<?php echo ( 'sticky' == $options['highlighted_behaviour'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Sticky', 'jobman' ) ?></option>
					<option value="inline"<?php echo ( 'inline' == $options['highlighted_behaviour'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Inline', 'jobman' ) ?></option>
				</select></td>
				<td><span class="description">
					<?php _e( 'Sticky: Put highlighted jobs at the top of the jobs list.', 'jobman' ) ?><br/>
					<?php _e( 'Inline: Leave highlighted jobs in their normal place in the jobs list.', 'jobman' ) ?>
				</span></td>
			</tr>
		</table>
		
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Sort Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_print_wrap_text_box() {
	$options = get_option( 'jobman_options' );
?>
		<p><?php _e( 'This text will be displayed before or after the lists/job/forms on the respective pages. You can enter HTML in these boxes.', 'jobman' ) ?></p>
		<form action="" method="post">
		<input type="hidden" name="jobmanwraptextsubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-wraptext-updatedb' ); 
?>
		<table class="form-table">
<?php
	$fields = array(
				'main' => array( 'before' => __( 'Before the Main Jobs List', 'jobman' ), 'after' => __( 'After the Main Jobs List', 'jobman' ) ),
				'category' => array( 'before' => __( 'Before any Category Jobs Lists', 'jobman' ), 'after' => __( 'After any Category Jobs Lists', 'jobman' ) ),
				'job' => array( 'before' => __( 'Before a Job', 'jobman' ), 'after' => __( 'After a Job', 'jobman' ) ),
				'apply' => array( 'before' => __( 'Before the Application Form', 'jobman' ), 'after' => __( 'After the Application Form', 'jobman' ) )
			);
	$positions = array( 'before', 'after' );
	foreach( $fields as $key => $field ) {
		foreach( $positions as $pos ) {
			$label = $field[$pos];
			$name = "{$key}-{$pos}";
			$value = $options['text']["{$key}_{$pos}"];
?>
			<tr>
				<th scope="row"><?php echo $label ?></th>
				<td><textarea name="<?php echo $name ?>" class="large-text code" rows="7"><?php esc_attr_e( $value ) ?></textarea></td>
			</tr>
<?php
		}
	}
?>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Text Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_display_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$options['list_type'] = $_REQUEST['list-type'];

	if( array_key_exists( 'promo-link', $_REQUEST ) && $_REQUEST['promo-link'] )
		$options['promo_link'] = 1;
	else
		$options['promo_link'] = 0;

	update_option( 'jobman_options', $options );
	
	if( $options['plugins']['gxs'] )
		do_action( 'sm_rebuild' );
}

function jobman_sort_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$options['sort_by'] = $_REQUEST['sort-by'];
	$options['sort_order'] = $_REQUEST['sort-order'];
	$options['highlighted_behaviour'] = $_REQUEST['highlighted-behaviour'];

	update_option( 'jobman_options', $options );
}

function jobman_wrap_text_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$pages = array( 'main', 'category', 'job', 'apply' );
	
	foreach( $pages as $page ) {
		$options['text']["{$page}_before"] = $_REQUEST["{$page}-before"];
		$options['text']["{$page}_after"] = $_REQUEST["{$page}-after"];
	}

	update_option( 'jobman_options', $options );
}

?>