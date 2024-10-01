<?php
/*
Name: Elementor Probe
Description: Scans site for Elementor Custom Code Snippets
Version: 0.0.1
Version History:
0.0.1 Initial Commit
*/

function bkjf_get_elementor_custom_code() {
	// we need: _elementor_location and _elementor_code
	$elementor_location = false;
	$args = array(  
		'post_type' => 'elementor_snippet',
		'post_status' => 'publish',
		'posts_per_page' => -1, 
		'orderby' => 'date', 
		'order' => 'ASC',
		);
	// we don't care if there are multiple, do we? Just take the "last" one.
	$loop = new WP_Query( $args ); 
	while ( $loop->have_posts() ) : $loop->the_post(); 
		$my_meta = get_post_meta($loop->post->ID);
		$elementor_code = $my_meta['_elementor_code'][0];
		$elementor_location = $my_meta['_elementor_location'][0];
	endwhile;

	wp_reset_postdata();
	if (!$elementor_location) {return;}
	return "It appears that there is Elementor Custom Code in the $elementor_location";
}
