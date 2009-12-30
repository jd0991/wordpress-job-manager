<?php
function jobman_create_dashboard($widths, $functions, $titles) {
?>
<div id="dashboard-widgets-wrap">
	<div id='dashboard-widgets' class='metabox-holder'>
<?php
	$ii = 0;
	foreach($widths as $width) {
?>
		<div class='postbox-container' style='width:<?php echo $width ?>'>
			<div id='normal-sortables' class='meta-box-sortables'>
<?php
		$jj = 0;
		foreach($functions[$ii] as $function) {
			jobman_create_widget($function, $titles[$ii][$jj]);
			$jj++;
		}
?>
			</div>
		</div>
<?php
		$ii++;
	}
?>
	</div>
	<div class="clear"></div>
</div>
<?php
}

function jobman_create_widget($function, $title) {
?>
				<div id="jobman-<?php echo $function ?>" class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle') ?>"><br /></div>
					<h3 class='hndle'><span><?php echo $title ?></span></h3>
					<div class="inside">
<?php
	call_user_func($function);
?>
						<div class="clear"></div>
					</div>
				</div>
<?php
}

function jobman_check_upload_dirs() {
	if(!is_writeable(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR)) {
		return false;
	}
	if(!is_writeable(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR)) {
		return false;
	}
	return true;
}

function jobman_load_translation_file() {
	load_plugin_textdomain('jobman', '', JOBMAN_FOLDER . '/translations');
}

function jobman_page_taxonomy_setup() {
	// Create our new page types
	register_post_type('jobman_job', array('exclude_from_search' => true));
	register_post_type('jobman_joblist', array('exclude_from_search' => true));
	register_post_type('jobman_app_form', array('exclude_from_search' => true));
	register_post_type('jobman_app', array('exclude_from_search' => true));

	// Create our new taxonomy thing
	register_taxonomy('jobman_category', array('jobman_job', 'jobman_app'), array('hierarchical' => false, 'label' => __('Category', 'series')));
}


?>