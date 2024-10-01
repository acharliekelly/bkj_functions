<?php
grab_kb();

// This should move to a high level function

function bkj_write_log($log):void {
    if (true === WP_DEBUG) {
        if (is_array($log) || is_object($log)) {
            error_log(print_r($log, true));
        } else {
            error_log($log);
        }
    }
}


function grab_kb() {
	// KB articles are viewed inside the dashboard this way:
	// https://www.whatever.com/site/wp-admin/admin.php?page=wpclientref_articles&article_id=3012
	$kbpath = admin_url() . 'admin.php?page=wpclientref_articles&article_id=';
	$kb_list = @$_GET['kb_list'];
	$json = @$_GET['kb_json'];
	$view = @$_GET['kb_view'];
	$kb_content = @$_GET['kb_content'];
	$out = array();
	$output = array();
	// look for posts of type client_reference	
	$args = array(
		'numberposts' => -1,
		'orderby' => 'name',
		'post_type' => 'client_reference',
		'order' => 'ASC',
		'post_status' => 'any'
		);
		
	$myposts = get_posts( $args);
	// see if there are any "notes" then?
	if (!$myposts) {
		$args['post_type'] = 'notes';
		$myposts = get_posts( $args);
	}
	
	if ( !$myposts ) {
		dealwith_results( array(array('link'=>'#','title'=>'No  data found; KB may be using a different post type, looking for "client_reference" ')), $json, $view);
		die();
	}
	foreach ( $myposts as $post ) :
		setup_postdata( $post ); 
		$out['title'] = $post->post_title;
		$out['link'] = $kbpath . $post->ID;
		if ($kb_content) { $out['content'] = $post->post_content;}
		$output[] = $out;

	endforeach; 
	wp_reset_postdata();
	// now display results
	dealwith_results($output, $json, $view);
	die();
	return $output;
}
function dealwith_results($output, $json = '', $view = '') {
	if ($json) {
		$output = json_encode ($output);
		echo $output;
	}
	if ($view) {

        //write_log($output);
		echo '<ul class="kb_links">';
		foreach ($output as $out) {
			echo "<li><a href='{$out['link']}' target='_blank'>{$out['title']}</a>";
            if (in_array("content",$output)) {echo '<br />' . $out['content']; }
			echo '</li>';
		}
		echo '</ul>';
	}
}
