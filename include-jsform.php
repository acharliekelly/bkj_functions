<?php
/* If we have a form on the page then we do some stuff to fill it out */
function enqueue_bkjf_jsform($hook) {

	$url = plugins_url('js/jsform.js', __FILE__);
	$time = time();
	$args = array( 
	    'in_footer' => true,
	    'strategy'  => 'defer',
	);

	wp_enqueue_script( 'bkjf-jsform', $url, array(), $time, $args );	
}
add_action( 'wp_enqueue_scripts', 'enqueue_bkjf_jsform', 100 );

