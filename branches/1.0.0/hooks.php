<?php namespace jobman;

add_action( 'admin_menu', '\\jobman\\Admin_Page::setup_menus' );
add_action( 'admin_init', '\\jobman\\Admin_Page::admin_init' );
add_action( 'shutdown', '\\jobman\\Options::save_if_needed' );

?>
