<?php //encoding: utf-8

// Hooks for initial setup
register_activation_hook(WP_PLUGIN_DIR.'/'.JOBMAN_FOLDER.'/job-manager.php', 'jobman_activate');

if(is_admin()) {
	// Admin menu
	add_action('admin_menu', 'jobman_admin_setup');
}

// Translation hook
add_action('init', 'jobman_load_translation_file');

//
// Display Hooks
//
// URL magic
add_filter('query_vars', 'jobman_queryvars');
add_action('generate_rewrite_rules', 'jobman_add_rewrite_rules');
add_action('init', 'jobman_flush_rewrite_rules');
add_filter('the_posts', 'jobman_display_jobs', 1);
// Add our init stuff
add_action('init', 'jobman_display_init');
// Set the template we want to use
add_action('template_redirect', 'jobman_display_template');
// Set the <title> value
add_filter('wp_title', 'jobman_display_title', 10, 3);
// Add our own <head> information
add_action('wp_head', 'jobman_display_head');

// For the slugs (treat jobman pages as WP pages)
add_filter('hierarchical_post_types', 'jobman_page_hierarchical_setup');
// For the page links
add_filter('post_link', 'jobman_page_link', 10, 2);

// Our custom page/taxonomy setup
add_action('init', 'jobman_page_taxonomy_setup');

//
// Plugins
//
// Google XML Sitemap
add_action('sm_buildmap', 'jobman_gxs_buildmap');

// Uninstall function
if (function_exists('register_uninstall_hook')) {
	register_uninstall_hook(WP_PLUGIN_DIR.'/'.JOBMAN_FOLDER.'/job-manager.php', 'jobman_uninstall');
}

?>