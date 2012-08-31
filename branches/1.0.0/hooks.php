<?php namespace jobman;

// Admin stuff.
add_action( 'admin_menu', '\\jobman\\Admin_Page::setup_menus' );
add_action( 'admin_init', '\\jobman\\Admin_Page::admin_init' );

// Initialization stuff
add_action( 'init', '\\jobman\\init_page_taxonomy' );

// Stuff that happens after each page request.
add_action( 'shutdown', '\\jobman\\Options::save_if_needed' );

?>
