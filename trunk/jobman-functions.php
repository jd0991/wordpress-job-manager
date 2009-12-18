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

function jobman_url($func = 'all', $data = '') {
	$options = get_option('jobman_options');
	$structure = get_option('permalink_structure');
	$url = $options['page_name'];
	
	if($structure == '') {
		$return = get_option('home') . '?' . $url . '=' . $func;
		if($data != '') {
			$return .= '&amp;data=' . $data;
		}
	}
	else {
		$return = get_option('home') . '/' . $url . '/';
		if($func != 'all' && $func != '') {
			$return .=  $func . '/';
		}
		if($data != '') {
			$return .= $data . '/';
		}
	}

	return $return;
}

function jobman_load_translation_file() {
	load_plugin_textdomain('jobman', '', JOBMAN_FOLDER . '/translations');
}

function jobman_page_taxonomy_setup() {
	// Create our new page types
	register_post_type('jobman_job', array('exclude_from_search' => false));
	register_post_type('jobman_joblist', array('exclude_from_search' => false));
	register_post_type('jobman_app_form', array('exclude_from_search' => false));
	register_post_type('jobman_app', array('exclude_from_search' => false));

	// Create our new taxonomy thing
		register_taxonomy('jobman_category', 'jobman_job', array('hierarchical' => false, 'label' => __('Category', 'series')));
}


?>