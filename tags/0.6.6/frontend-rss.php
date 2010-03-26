<?php
function jobman_rss_feed( $forcomments ) {
	global $post;
	$dir = dirname( $_SERVER['SCRIPT_FILENAME'] );
	
	query_posts( 'post_type=jobman_job' );
	
	require_once( "$dir/wp-includes/feed-rss2.php" );
	exit;
}

function jobman_rss_page_link( $link ) {
	global $post;
	if( NULL == $post || 'jobman_job' != $post->post_type )
		return $link;
		
	return get_page_link( $post->ID );
}

?>