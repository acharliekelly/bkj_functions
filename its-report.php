<?php
/*
Ithemes Report
Version: 0.1.1
Version History:
0.1.1	Updated to show GA status, fix error with transient update plugin upgrade check
0.1.0	Removed "usually" to be less vague, more reassuring
0.0.9	2022-04-05 Misc updates including number of plugins to update
0.0.8 2020-02-25 Fixed issue with js its-ip-workaround 
0.0.7 various bug fixes, removal of the date_range stuff. TODO: Remove all date range, no need.
0.0.6 tiny bug in undeclared variable line 566ish 
0.0.5 added version string to report, removed bkj, malden, melrose from suspcious places list, also better identification of what site this actually is.
0.0.4 Improved date reporting and other things
0.0.3 compatability error for numberformatter in older PHP versions, sigh.
0.0.2 checking out curl
0.0.1 Initial testing of parameters
*/

add_action( 'init', 'its_report' );
//error_log('this message from its-report.php');
function its_report() {
	global $mysql_time;
	if ( !( isset($_GET['its_email']) ||
		isset($_GET['its_view']) ||
		isset($_GET['its_spreadsheet']) ||
		isset($_GET['its_store'])
		) ) { return;}
/*	error_reporting(E_ALL);
	if (!function_exists('curl_exec')) {
		die("Curl not supported!");
	}
*/
	// TODO: Make sure the iThemes Security plugin is active!
	if( !class_exists( 'ITSEC_Core' ) ) {
		die('To use this feature you have to have iThemes Security plugin activated.'); 
	}

	global $wp_version;
	global $bkjfunctions_version;
	$php_version = phpversion();
	$active_plugins = count(get_option('active_plugins'));
	$php_version_note = ' (Great! Your site is using a modern version of PHP!)';
	if ($php_version < 8) { $php_version_note = ' (Note: We will need to update PHP at some point.)';}

	$extra_version_info = "<p><strong>Geeky Tech-stuff: </strong>";
	$extra_version_info .= bkjf_plugin_update_count() . ' WordPress version: ' . 
		"$wp_version. PHP: $php_version$php_version_note</p>" .
		"<hr noshade style='border: none; border-top: 1px solid grey;'>" .
		"<p>Active Plugins: $active_plugins</p>";
	
	if ( !function_exists( 'SimpleLogger' )) {
		$extra_version_info .= '<a href="https://wordpress.org/plugins/simple-history/" target="_blank"><strong>Simple History </strong>plugin</a> is NOT running. Please consider adding it.';
	}
	
	$extra_version_info .= '<p>' . bkjf_ga_stuff() . '</p>';
	if ($temp = bkjf_elementor_custom_code()) {
		$extra_version_info .= '<p>Elementor: ' . $temp . '</p>';
	}
	$extra_version_info .= "<p>(BKJ Functions Version: $bkjfunctions_version)</p>";
	

	global $table_prefix;
	$mysql_time = "Y-m-d H:i:s";
	$narrative = '';
	// Parameters you could use:
	//	email  to send the report to an email address
	//	view to simply view on screen
	//	store to create a Post, type=article | note (adding a KB article)
	//	daterange could be "this month" or "last month" or "all" etc.
	//	dump would re-set the ITS log, assuming there was no issue.
	//	Other parameters TBA: max404 and maxlogin to specify a maximum number of logins, etc.
	//	Set up defaults for these.
	global $its_debug;
	$its_email = (@$_GET['its_email'] ? $_GET['its_email'] : false );
	// if request is just "1" then send to the admin
	if ($its_email == 1) {$its_email = get_bloginfo('admin_email');}
	
	// test email:
	if ($its_email && !filter_var($its_email, FILTER_VALIDATE_EMAIL) ) {
		die( "<p><strong>$its_email</strong> is not a valid email address!</p>\n");
	}
	
	$its_spreadsheet = (@$_GET['its_spreadsheet'] ? $_GET['its_spreadsheet'] : false); // return AJAX JSON
	$its_ajax = (@$_GET['its_ajax'] ? $_GET['its_ajax'] : false); // return AJAX JSON
	$its_view = (@$_GET['its_view'] ? $_GET['its_view'] : false); 
	$its_store = (@$_GET['its_store'] ? $_GET['its_store'] : false); 
	$its_type = (@$_GET['its_type'] ? $_GET['its_type'] : 'client_reference'); 
	$its_daterange_start = (@$_GET['its_daterange_start'] ? $_GET['its_daterange_start'] : false); 
	$its_daterange_stop = (@$_GET['its_daterange_stop'] ? $_GET['its_daterange_stop'] : false); 
	
	$its_max404 = (@$_GET['its_max404'] ? $_GET['its_max404'] : 3); 
	$its_maxlogin = (@$_GET['its_maxlogin'] ? $_GET['its_maxlogin'] : 5); 
	$its_maxip = (@$_GET['its_maxip'] ? $_GET['its_maxip'] : 3); 
	// we must have at least TWO of them:
	if ($its_max404 == 1) {$its_max404 = 2;}
	if ($its_maxlogin == 1) {$its_maxlogin = 2;}
	if ($its_maxip == 1) {$its_maxip = 2;}
	
	$its_delete = (@$_GET['its_delete'] ? $_GET['its_delete'] : false); 
	$its_debug = (@$_GET['its_debug'] ? $_GET['its_debug'] : false); 
	// for right now we are setting diagnostic to true:
	//$its_debug = true;
	
	if ($its_delete && ! ($its_email || $its_store || $its_view)) {
		die('Sorry, you cannot delete data without viewing, storing, or emailing the data! (If you delete the data, it is going to be gone forever!)');
	}
	// OK, if we're dumping or sending email or storing, we're going to view as well (unless we turn this into something that would do ajax)
	if ($its_delete || $its_email || $its_store ) {
		$its_view = 1;
	}
	// deal with the dates, make sure they're all legit!
	$dates = deal_with_dates($its_daterange_start, $its_daterange_stop);
	if ($its_debug) {
		echo "<PRE>";
		echo "mysql_time: $mysql_time<BR>";
		print_r($dates);
	}
	$its_daterange_start_sql = $dates['its_daterange_start_sql'];
	$its_daterange_stop_sql = $dates['its_daterange_stop_sql'];
	$its_daterange_start_display = $dates['its_daterange_start_display'];
	$its_daterange_stop_display = $dates['its_daterange_stop_display'];
	$its_daterange_start = $dates['its_daterange_start'];
	$its_daterange_stop = $dates['its_daterange_stop'];
	
	if ($its_debug) { echo "here";}
	
	$four_oh_four = get_its_data('four_oh_four',$its_daterange_start_sql, $its_daterange_stop_sql);
	if ($its_debug) { echo "here";}
		$brute_force = get_its_data('brute_force',$its_daterange_start_sql, $its_daterange_stop_sql);
	$ip_list = get_its_data(false,$its_daterange_start_sql, $its_daterange_stop_sql);
		if ($its_debug) { echo "here";}
	
	
	// list all variables during development to better document them:
/*	$x = get_defined_vars();
	echo "<PRE>";
	print_r($x);
	die();
	*/
	$rawdata = get_its_data('raw',$its_daterange_start_sql, $its_daterange_stop_sql);
	$date_from_data = bkjf_process_date_range_from_data($rawdata);
	$narrative .= bkjf_process_ips($ip_list, $its_maxip);
	$narrative .= bkjf_process_404s($four_oh_four, $its_max404);
	$narrative .= bkjf_process_brute_force($brute_force, $its_maxlogin);
	$narrative .= bkjf_process_cf7dbplugin_submits();
	$narrative = onetwothree($narrative);
	$narrative = "<p>$narrative</p>";
	$mindate = $date_from_data['mindate'];
	$maxdate = $date_from_data['maxdate'];
	$site_name = get_bloginfo('name' );
	$narrative .= log_simplehistory();
	
	$css = "<style>* {font-family: sans-serif;}body {max-width: 760px; width: 90%; margin: 10px auto;}</style>";
	$narrative = "$css<h1>$site_name</h1><p><strong>Security Notes: $mindate  to $maxdate</strong></p>" . $narrative;
	
	if ($its_spreadsheet) {
		print_spreadsheet($rawdata);
	}
	
	$post_narrative = '';
	$post_narrative .= $extra_version_info;
	if ($its_email || $its_store || $its_delete) {
		$post_narrative = "<p>Options were included in this transaction:</p><ul>";
		if ($its_email) {$post_narrative .= "<li>Emailing to $its_email</li>"; }
		if ($its_store) {$post_narrative .= "<li>Stored in WordPress as post type $its_type</li>"; }
		if ($its_delete) {$post_narrative .= "<li>Dumping data (completely deleting it). </li>"; }
		$post_narrative .= "</ul>";
		$post_narrative .= $extra_version_info;
	}
	// now, we can do something with the narrative:	
	if ($its_email) {
		$headers = array();
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'From: WordPress <' . get_bloginfo('admin_email') . '>';
		// TODO: use wp_mail rather than mail(), but that function is not yet available at the time this PHP is processed
		$headers = implode(";\r\n",$headers);
		$subject = get_bloginfo('url') . ' iThemes quick report on intrusions';
		$x = mail($its_email, $subject,$narrative . $post_narrative, $headers);
		if (!$x &&  $its_store) {
			echo "<p>Data not saved.</p>";
		}
		if (!$x &&  $its_delete) {
			echo "<p><strong>Delete canceled.</strong></p>";
		}
		$its_delete = false;
		if (!$x) {die("<p>There was a problem sending email to $its_email.</p>\n$x");}
	}
	$check_if_store = '';
	if ($its_store) {
		$date = substr($its_daterange_stop_sql, 0, 10);
		$check_if_store = create_post($narrative . $post_narrative,$its_type, $date );
	}
	if ($its_view) {
		echo $narrative;
		if ($its_email) {
			echo "<hr />";
			echo "<p><strong>Mail sent: </strong> $its_email</p>";
		}
		if ($check_if_store) {
			echo "<hr />";
			echo "<p>$check_if_store</p>";
		} else { 
			// do not dump!
			/* 
			TODO: Decide if this logic is OK; allow someone to dump without storing to database?
			if ( $its_delete) {
				echo "<p><strong>Dump cancelled.</strong></p>";
			}
			$its_delete = false;
			//END TODO
			*/
		}
		
		if ($its_delete) {
			diagnose( "delete_data");
			$x = delete_data( $its_daterange_start_sql, $its_daterange_stop_sql); 
			if ($x) {
				echo "<p>Successfully deleted data $its_daterange_start_sql to $its_daterange_stop_sql.</p>";
				}
			else {
				echo "<p><strong>FAILED TO DELETE!</strong> Date range: $its_daterange_start_sql to $its_daterange_stop_sql.</p>";
			}
		}
		echo $post_narrative;
	}
	
	die("<p>Complete.</p>");
}
// end of main function
function 	print_spreadsheet($data) {
	$maxdata = 100;
	$nl = "\r\n";
	$k = 0;
	$output = '';
	$header = '';
	foreach ($data as $v) {
		// first line is header
		
		if ($k==0) {
			foreach ($v as $k2=>$v2) {
				$header .= $k2 . "\t";
			}
			$output .= $header . $nl;
		}
		// [data] element can be horrifically long
		$datalen = strlen($v['data']);
		if ($datalen > $maxdata) { 
			$v['data'] = substr($v['data'],0, $maxdata) . "... ($datalen characters)";
		}
		$output .= implode("\t",$v) . $nl;
		$k++;
	}
	// make a nice filename:
	$get_home_url = get_home_url();
	$get_home_url = str_replace('https://','',$get_home_url);
	$get_home_url = str_replace('http://','',$get_home_url);
	$get_home_url = str_replace('www.','',$get_home_url);
	$date = date('Y-m-d');
	$filename = preg_replace('/[^a-z0-9.]/i', '_', $get_home_url) . " ITSR $date.xls";
	
	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
	header("Content-Disposition: attachment;filename=\"$filename\"");
	header("Cache-Control: max-age=0");
	echo $output;
	die();
}

// helper functions:
function deal_with_dates($its_daterange_start, $its_daterange_stop) {
	global $mysql_time;
	if ($its_daterange_start && !$its_daterange_stop) {
		$its_daterange_stop = 'next month';
	}
	// see if a natural language "next month" or "this month" has been requested:
	if ($its_daterange_start || $its_daterange_stop) {
		if ($its_daterange_start == 'this month') {
		
			$its_daterange_start = date($mysql_time,strtotime($its_daterange_start));
			$its_daterange_start = substr($its_daterange_start,0,7) . '-01 00:00:01';
			/*$its_daterange_stop = 'next month';
			$its_daterange_stop = date($mysql_time,strtotime($its_daterange_stop));
			$its_daterange_stop = substr($its_daterange_stop,0,7). '-01 00:00:00';
			// now go 1 second back;
			$its_daterange_stop = date($mysql_time,strtotime($its_daterange_stop)-1);
			*/
		}
		
		if ($its_daterange_start == 'last month') {
			$date = date("Y-m-01");
			$newdate = strtotime ( '-1 month' , strtotime ( $date ) ) ;
			
			//$its_daterange_stop = date($mysql_time,strtotime($its_daterange_stop));
			$its_daterange_start = date($mysql_time,$newdate);
			//$its_daterange_start = date($mysql_time,strtotime($its_daterange_start));
			//$its_daterange_start = substr($its_daterange_start,0,7) . '-01 00:00:01';
			/*$its_daterange_stop = 'this month';
			$its_daterange_stop = date($mysql_time,strtotime($its_daterange_stop));
			$its_daterange_stop = substr($its_daterange_stop,0,7). '-01 00:00:00';
			// now go 1 second back;
			$its_daterange_stop = date($mysql_time,strtotime($its_daterange_stop)-1);
			*/
	
		}
		if ($its_daterange_stop == 'this month') {
			$its_daterange_stop = date($mysql_time,strtotime($its_daterange_stop));
			$its_daterange_stop = substr($its_daterange_start,0,7) . '-01 00:00:01';
		}
		if ($its_daterange_stop == 'last month') {
			$its_daterange_stop = date($mysql_time,strtotime($its_daterange_stop));
			$its_daterange_stop = substr($its_daterange_start,0,7) . '-01 00:00:01';
		}
		
		if ($its_daterange_stop == 'next month') {
			$date = date("Y-m-01");
			$newdate = strtotime ( '+1 month' , strtotime ( $date ) ) ;
			
			//$its_daterange_stop = date($mysql_time,strtotime($its_daterange_stop));
			$its_daterange_stop = date($mysql_time,$newdate -1);
			//$its_daterange_stop = substr($its_daterange_start,0,7) . '-01 00:00:01';
		}
	}
		// OK now process that thing to something we can use
	$its_daterange_start = strtotime($its_daterange_start);
	// did they not set an end date? make one up, make it to the end of this month
	if ($its_daterange_stop) { $its_daterange_stop = strtotime($its_daterange_stop); } else {
			$its_daterange_stop = 'next month';
			$its_daterange_stop = date($mysql_time,strtotime($its_daterange_stop));
			$its_daterange_stop = substr($its_daterange_stop,0,7). '-01 00:00:00';
			// now go 1 second back;
			$its_daterange_stop = date($mysql_time,strtotime($its_daterange_stop)-1);
			$its_daterange_stop = strtotime($its_daterange_stop);
	}
	
	// now we have time for start and stop, convert it to proper style for mysql
	$its_daterange_start_sql = date ($mysql_time, $its_daterange_start);
	$its_daterange_stop_sql = date ($mysql_time, $its_daterange_stop);
	
	$its_daterange_start_display = substr($its_daterange_start_sql,0,10);
	$its_daterange_stop_display = substr($its_daterange_stop_sql,0,10);
	
	//diagnose("its_daterange_start_sql: $its_daterange_start_sql");
	//diagnose("its_daterange_stop_sql: $its_daterange_stop_sql");
	$output = array(
		'its_daterange_start_sql' => $its_daterange_start_sql,
		'its_daterange_stop_sql' => $its_daterange_stop_sql,
		'its_daterange_start_display' => $its_daterange_start_display,
		'its_daterange_stop_display' => $its_daterange_stop_display,
		'its_daterange_start' => $its_daterange_start,
		'its_daterange_stop' => $its_daterange_stop,
	);
	return $output;
}
function create_post($narrative,$post_type, $date) {
	$page_title = "Security notes to $date";	
	$args = array( 'post_type' => $post_type	);
	$find_posts = new WP_Query( $args );
	if( !$find_posts->have_posts() ) {
		die("Sorry, cannot find post type <strong>$post_type</strong>. You need to have at least one pre-existing $post_type.");
	}
	
	if ( get_page_by_title( $page_title, OBJECT, $post_type ) ) {
		die("Sorry, that post, <strong>$page_title</strong> has already been created. (Hint: Look in Trash if you don't see it?)");
	}
	// OK to create a post:
	$postarr = array(
		'post_content' => $narrative,
		'post_title' => $page_title,
		'post_status' => 'publish',
		'post_type' => $post_type,
		);
	$check_create = wp_insert_post( $postarr); 
	if (!$check_create) {die( "Unable to create $page_title of type $post_type. "); }
	$check_create = get_bloginfo('wpurl') . '/wp-admin/post.php?post=' . $check_create;
	$edit_link = "<a href='$check_create&action=edit'>Edit Now</a>";
	return "Created <strong>$page_title</strong> of type $post_type. $edit_link";
}

function bkjf_process_date_range_from_data($array) {
	// using spaceship operator:
	// https://lindevs.com/sort-comparison-functions-that-return-boolean-value-is-deprecated-in-php-8-0/
	usort($array, function($a, $b) {
		return ($a['timestamp'] <=> $b['timestamp']);
	});
	//print_r($array);
	$mindate = $array[0]['timestamp'];
	$maxdate = $array[count($array)-1]['timestamp'];
	// trim out extra part of date: HH:MM:SS
	$mindate = substr($mindate,0,10);
	$maxdate = substr($maxdate,0,10);
	return array(
		'mindate' => $mindate,
		'maxdate' => $maxdate,
	);
}
function bkjf_process_brute_force($array, $max) {
	diagnose('<hr>');
	diagnose('bkjf_process_brute_force');
	$quote = '"';
	$out_array = array();
	$i = 0;
	$howmany_in_array = count($array);
	if ($howmany_in_array < $max) {$max = $howmany_in_array- 1;}
	$and = 'and ';
	foreach ($array as $k=>$v) {
		$i++;
		// first item is howmany
		if ($i==1) { 
			$howmany = $v; 
			continue;
			}
		// only do so many (max)
		if ( $i>$max+1 ) {continue;}
		// wrap in quotes
		$k = "$quote$k$quote";
		$plurals = '';
		if ($v > 1) {$plurals = 's';}
		// put 'and' before last item:
		if ($i >$max) {
			$k = "$and$k ";
		}
		if ($k != '"0"') {
			$out_array[] = "$k &mdash; $v time$plurals";	
		}
	}
	if ($howmany == 0) {return 'No brute force username/password attempts detected. ';}
	$output = "There were $howmany brute force attempts to log in, with such user names as ";
	$output .= implode(', ',$out_array) . ". ";
	$output = str_replace('as and','as ',$output);
	// qualifier:
	$output .= qualify_risk($howmany, 'brute_force');
	$output .= '<br /><br />';
	return $output;
}
function bkjf_process_404s($array, $max) {
	diagnose('<hr>');
	diagnose('bkjf_process_404s');
	$out_array = array();
	$i = 0;
	$howmany_in_array = count($array);
	if ($howmany_in_array < $max) {$max = $howmany_in_array- 1;}
	function has_false_positives($k) {
		$falsepositives = explode(',', '404testpage,404javascript,bkj-');
		foreach ($falsepositives as $compare) {
			if (strpos($k,$compare)) {return true;}
		}
		return false;
	}
	
	foreach ($array as $k=>$v) {
		if (has_false_positives($k) ) {continue;}
		$i++;
		// first item is howmany
		if ($i==1) { 
			$howmany = $v; 
			continue;
			}
		// only do so many (max)
		if ( $i>$max+1 ) {continue;}
		// skip bkj ones:
		// strip out the path
		$k = strip_path($k);
		$k = "<em>$k</em>";
		$plurals = '';
		if ($v > 1) {$plurals = 's';}
		// put 'and' before last item:
		if ($i >$max) {
			$k = "and $k ";
		}
		$out_array[] = "$k &mdash; $v time$plurals";	
	}
	if ($howmany == 0) {return '';}
	if (class_exists('NumberFormatter')) {
		$f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
		$max = $f->format($max);
	} else
	{$max = $f;}
	
	$output = "There were $howmany pages or items not found. Here are the top $max: ";
	$output .= implode(', ',$out_array) . ". \n";
	$output .= qualify_risk($howmany, 'four_oh_four');
	$output .= '<br /><br />';
	return $output;
}
function bkjf_process_ips($array, $max) {
	diagnose('<hr>');
	diagnose('bkjf_process_ips');
//print_r($array);
	// first get locations
	$array = lookup_ips($array, $max*2);
	$out_array = array();
	$i = 0;
	$howmany_in_array = count($array);
	if ($howmany_in_array < $max) {$max = $howmany_in_array- 1;}
	foreach ($array as $k=>$v) {
		// ignore our location: Malden, medford, stoneham, melrose
		if (strpos($k,'Malden')>-1) {continue;}
		if (strpos($k,'Medford')>-1) {continue;}
		if (strpos($k,'Stoneham')>-1) {continue;}
		if (strpos($k,'Melrose')>-1) {continue;}
		$i++;
		// first item is howmany
		if ($i==1) { 
			$howmany = $v; 
			continue;
			}
		// only do so many (max)
		if ( $i>$max +1) {continue;}
		$plurals = '';
		if ($v > 1) {$plurals = 's';}
		// put 'and' before last item:
		
//		if ($i >$max) {
		if (count($out_array)> 3) {
			if ($i >2) {$k = "and $k ";
					 }
		}
		$out_array[] = "$k &mdash; $v time$plurals";	
	}
	if ($howmany == 0) {return 'No suspicious IP addresses were found. ';}
	$intrusions = pick_random('intrusions,infractions,examples of meddling by hackers,trespasses by possible hackers,possible hack attempts,likely attacks,annoying requests,suspicious requests,hacker-like activities,hacker-ish things,intrusions');
	$locations = pick_random('diverse locations,places,exotic locales,dens of iniquity,locations,nefarious places,dubious locations,suspect locales,faraway places,alarming areas,treacherous locales,risky spots,nefarious neighborhoods,risky regions');
	$noticed = pick_random('noticed,recorded,encountered,found,observed,discovered,reveals');
	$output = "iThemes Security $noticed $howmany $intrusions overall, from such $locations as \n";
	$output .= implode(', ',$out_array) . ". \n";
	$output .= '<br /><br />';
	return $output;
}

/* from ip-api.com you'll get a json string
	"status": "success",
	"country": "United States",
	"countryCode": "US",
	"region": "CA",
	"regionName": "California",
	"city": "San Francisco",
	"zip": "94105",
	"lat": "37.7898",
	"lon": "-122.3942",
	"timezone": "America\/Los_Angeles",
	"isp": "Wikimedia Foundation",
	"org": "Wikimedia Foundation",
	"as": "AS14907 Wikimedia US network",
	"query": "208.80.152.201"
*/
function lookup_ips($array, $max) {
	diagnose('<hr>');
	diagnose('lookup_ips');
	$maxlookups = 5; // could be 150
	// $service must be http for free version, and max 45 requests per minute
	$service = 'http://ip-api.com/json/';
	$out = array();
	$i = 0;
	foreach ($array as $k=>$v) {
		// first element we just skip over
		$i++;
		if ($i == 1) {
			$out[$k] = $v;
			continue;
		} 
		diagnose("I: $i");
		diagnose("K: $k V: $v");
		if ($i>$maxlookups) {continue;}
		$json = curlit("$service$k");
		
		$json_data = json_decode($json, true);
//		print_r($json_data);
		$place = '';
		if ($json_data['status'] == 'success') {
			$place = "<strong>{$json_data['city']}</strong>, {$json_data['regionName']}";
			if ($json_data['countryCode'] != "US") $place .= " ({$json_data['country']})";
			$out[$place] = $v;
		}
	}
	return $out;
}
function delete_data( $datestart = false, $datestop = false) {
	diagnose('<hr>');
	diagnose('delete_data');
	// TODO: MAKE THIS WORK and be more secure; maybe we prompt and set a nonce
	global $table_prefix, $wpdb;
	if (!$datestart) {die("Cannot delete; start date invalid. <strong>$datestart</strong>");}
	if (!$datestop) {die("Cannot delete; stop date invalid. <strong>$datestop</strong>");}
	
	
	$condition = " timestamp>='$datestart' AND timestamp<='$datestop' ";
	
	// First see how many are going to be deleted:
	$found = $wpdb->get_var($wpdb->prepare( 
		"SELECT COUNT(*) FROM `{$table_prefix}itsec_logs` WHERE timestamp>=%s AND timestamp<=%s",
		$datestart,
		$datestop));
	diagnose("Found: $found");
	$sql = "DELETE FROM `{$table_prefix}itsec_logs` WHERE $condition";
	//echo "DUMPING: $sql";
	//return false;
	// TODO: Is there any way to confirm this?
	diagnose($sql);
	$mydata = $wpdb->get_results( $sql, ARRAY_A);
	// now see how many are left:
	$found = $wpdb->get_var($wpdb->prepare( 
		"SELECT COUNT(*) FROM `{$table_prefix}itsec_logs` WHERE timestamp>=%s AND timestamp<=%s",
		$datestart,
		$datestop));
	diagnose("Remaining: $found");
	if ($found == 0) {return true;}
	return $found;
}

function get_its_data($type = false, $datestart = false, $datestop = false) {
	global $its_debug;
	$and = '';
	$condition = 1;
	//if ($type) {$condition = "`log_type` LIKE '%$type%' ";}
	if ($type) {$condition = "`module` LIKE '%$type%' ";}
	if ($type == 'raw') {$condition = '1';}
	if ($datestart && $datestop) {
		$and = " AND timestamp>='$datestart' AND timestamp<='$datestop' ";
	}
	global $table_prefix, $wpdb;
	$limit = '';
	$what = '*';
	$what = 'module, code, data, type, timestamp, url, remote_ip, user_id';
	if ( isset($_GET['its_limit'])  ) {$limit = ' LIMIT ' . intval( $_GET['its_limit'] ); }
	$sql = "SELECT $what FROM `{$table_prefix}itsec_logs` WHERE $condition $and $limit "; //ORDER BY `wp_itsec_logs`.`log_host` ASC";
	diagnose($sql);
	$mydata = $wpdb->get_results( $sql, ARRAY_A);
	diagnose($mydata);
	// how shall we sort the data?
	$sort = 'remote_ip';
	$sort_secondary = 'timestamp';
	if ($type == 'brute_force') {$sort = 'user_id';}
	if ($type == 'four_oh_four') {$sort = 'url';}
	
	diagnose($mydata);
	// we loop through and clean up data a tiny bit:
	$mydata_out = array();
	foreach ( $mydata as $row ) 	{
		diagnose( "{$row['user_id']} \t| {$row['url']} \t| {$row['timestamp']} \n");
		// in the case of invalid login we have to dig into data-> details
	//	print_r($row);
		if ( strpos('x'. $row['code'], 'invalid-login') >0 ) { 
			$invalid_data = unserialize ($row['data']);
			$username = @$invalid_data['username'];
			if ($username) {$row['user_id'] = $username;}
		}
		$mydata_out[] = $row;
	}
	
	
	// count unique things
	if ($type == 'raw') {return $mydata_out;}
	return find_things($mydata_out, $sort, $sort_secondary);
}
function find_things($mydata, $sort = 'remote_ip', $sort_secondary = 'user_id') {
	global $its_debug;
	diagnose( "find_things list:");
	if (!$its_debug) {
		for ($i = count($mydata)-1; $i>=0; $i--) {
			$row = $mydata[$i];
			diagnose( "$i {$row['user_id']} \t| {$row['url']} \t| {$row['timestamp']} \n");
		}
	}
	diagnose( "<hr>");
	// sneaky way to count duplicates in an array list:
	$out = array();
	foreach ($mydata as $row) {
		@$out[$row[$sort]] ++;
	}
	diagnose($out);
	// now reverse sort this by the count (element 2 of row)
	uasort($out, "cmp_rev");
	// add a header, element 0
	$header = array( $sort => count($mydata) );
	$out = $header + $out;
	// array_unshift($out, $header);
	diagnose($out);
	diagnose("<HR>");
	return $out;
}
function qualify_risk($howmany, $type_of_attack = 'brute_force') {
	$output = '';
	$addon = '';
	if ($type_of_attack == 'four_oh_four') {
		$addon = 'Attacks like this mean that a hacker was trying to access a file that may have a known vulnerability. ';
	}
	
	if ($type_of_attack == 'brute_force') {
		$addon = 'Someone is trying to guess a username and password. The security plugin will shut down the IP address after the first few tries, so not to worry! ';
	}
	if (( $howmany > 1) && ($howmany <= 10) ) {
		$output .= '(Not too bad, not much to worry about.) ';
	}
	
	if (( $howmany > 10) && ($howmany <= 100) ) {
		$output .= 'This is fairly typical. ';
	}
	
	if (( $howmany > 100) && ($howmany <= 500) ) {
		$output .= "Whoa, $howmany? This is pretty extreme! " . $addon;
	}
	
	if (( $howmany > 500) && ($howmany < 1500) ) {
		$output .= "Wait, $howmany??? This is getting pretty serious! Please give us a call to discuss some options, if you want to do something about this. ";
	}
	return $output;
}

function onetwothree($text) {
	$text = str_replace(' 1 time', ' once', $text);
	$text = str_replace(' 2 times', ' twice', $text);
	$text = str_replace(' 3 times', ' thrice', $text);
	return $text;
}
//	When the request is made, first look into a table that is created by ITS, and look for the 404 errors and illegal requests for logging in. Get the count of 404 errors, and perhaps come up with some way to quantify them--- if there are over a dozen of the same thing, then highlight that in some way. When looking at the Host, do a sort by that column, and take the top five or so, or perhaps that is a parameter.
//	
//	The SQL select is something like this:
//	SELECT * FROM `wp_itsec_logs` WHERE `log_type` LIKE '%four%' ORDER BY `wp_itsec_logs`.`log_host` ASC
//	The fields here are:
//	log_id, log_type
//	log_function, log_priority, timestamp, timestamp_gmt,
//	log_host, user_id, log_user, url, log_referrer, log_data
//	
//	with log_type values brute_force or four_oh_four
//	
//	To make the text more interesting, have a little list of adjectives to vary the content, such as: "There were 400 login attempts by some [creepy | crazy | ridiculous] user named admin, coming from locations as [diverse | far away | distant] as Indiana." This can further be augmented by varying some statements like "This is a lot of activity" if there are more than 100 intrusions, etc.
//	
//	To look up a country's IP, use the API at
//	
//	http://ip-api.com/docs/ probably use the JSON format: http://ip-api.com/docs/api:json
//	
//	Make sure not to do more than 150 requests in a minute.
//	
//	Some nuances: WordPress has a different table prefix, defined in a constant in wp-config.php. There are preferred methods of making database queries rather than the direct one shown; perhaps the ITS plugin itself has a function or API.
//	
//	Security: Because this plugin can be called willy-nilly via the parameter, we should set some guidelines for use. Maybe we don't allow more than 10 requests a day. When creating a KB article, make sure that one doesn't exist already and that we only allow that to happen once, and an email is sent to the admin (or perhaps required in order to create a KB article, as a level of security). You can create an option setting to hold the count for the day, perhaps.
//	
//	Possible problems: 
//	
//	What if there is no data?
//	
//	What if there is too much data?
//	
//	Validate email address follows standard pattern
//	
//	Date range not found
//	
//	Dates not valid
//	
//	How to handle error reporting? If a new Post is to be created then it would be bad if it was just blank, or too big, so have to send an email to the Admin about this.
//	
//	Log usage of this, perhaps by adding a row to the it_sec_log.

// some useful functions:
function diagnose($x) {
	global $its_debug;
	if (!$its_debug) {return;}
		echo '<pre>';
	if (is_array($x)) {
		print_r($x);
	}
	else {
		echo $x;
	}
	echo '</pre>';
}

function bkjf_array_msort($array, $cols) {
	$colarr = array();
	foreach ($cols as $col => $order) {
		$colarr[$col] = array();
		foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
	}
	$eval = 'array_multisort(';
	foreach ($cols as $col => $order) {
		$eval .= '$colarr[\''.$col.'\'],'.$order.',';
	}
	$eval = substr($eval,0,-1).');';
	eval($eval);
	$ret = array();
	foreach ($colarr as $col => $arr) {
		foreach ($arr as $k => $v) {
			$k = substr($k,1);
			if (!isset($ret[$k])) $ret[$k] = $array[$k];
			$ret[$k][$col] = $array[$k][$col];
		}
	}
	return $ret;
}
function pick_random($list) {
	$list = explode(',',$list);
	$rand = rand(1,count($list) );
	return $list[$rand-1];
}
function strip_path($x) {
	$removeme = home_url();
	$x = str_replace(home_url(), '', $x);
	return $x;
	
}

function cmp($a, $b) {
	if ($a == $b) {
		return 0;
	}
	return ($a < $b) ? -1 : 1;
}
function cmp_rev($a, $b) {
	if ($a == $b) {
		return 0;
	}
	return ($a > $b) ? -1 : 1;
}

// we can't use file reading functions, have to use CURL. If destination server is slow, what to do?
function curlit($url) {
	// skip blank requests
	if (strpos(basename($url),'.')<1) {return;}
	$response = wp_remote_get($url );
	if ( is_array( $response ) ) {
		//echo "wp_remote_get<BR>";
		$header = $response['headers']; // array of http header lines
		$body = $response['body']; // use the content
		return $body;
	}
	
}
/* REMAINDER OF CURLIT FUNCTION THAT DIDN'T WORK:
	echo "Try to allow_url_fopen: $url";
	// first see if we can use file reading:
	if ( ini_get('allow_url_fopen') ) {
		$arrContextOptions=array(
			"ssl"=>array(
				"verify_peer"=>false,
				"verify_peer_name"=>false,
				),
			);  
		echo "allow_url_fopen: is on";
		
		$file = file_get_contents($url, false, stream_context_create($arrContextOptions));
		return $file;
	}
	else {die('no allow_url_fopen');}
	
	echo "Try curling: $url<BR>";
	
	$ch = curl_init();
	// suggestions CURLOPT_USERAGENT and CURLOPT_REFERER found at
	// https://stackoverflow.com/questions/2453207/curl-http-post-keep-getting-500-error-has-no-idea
	
	// set URL and other appropriate options
	$options = array(
		CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
		CURLOPT_REFERER => get_bloginfo('url'),
		CURLOPT_URL => $url,
		CURLOPT_HEADER => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CONNECTTIMEOUT => 1,		 // timeout on connect 
		CURLOPT_TIMEOUT		=> 1,		 // timeout on response 
		CURLOPT_MAXREDIRS => 10,		  // stop after 10 redirects 
		);
	curl_setopt_array($ch, $options);

	$result = curl_exec($ch);
	
	if ($result === FALSE) {
		printf("cUrl error (#%d): %s<br>\n", curl_errno($ch),
		htmlspecialchars(curl_error($ch)));
	}
	
	//rewind($verbose);
	//$verboseLog = stream_get_contents($verbose);
	
	//echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
	curl_close($ch);
	
	return $result;
}
*/

function bkjf_process_cf7dbplugin_submits() {
	if (!function_exists('CF7DBPlugin_i18n_init')) {return "";}
	global $wpdb;
	$table_name = 'cf7dbplugin_submits';
	$table_name = $wpdb->prefix . $table_name;
	$count_query = "select count(*) from $table_name";
	$count_query = "select distinct submit_time from $table_name";
	$num = $wpdb->get_var($count_query);
	$results = $wpdb->get_results($count_query);
	$howmany = count($results);
	if ($howmany) {
		$plural = '';
		if ($howmany > 1) {$plural = 's';}
    		return  "You should have received $howmany contact form$plural (since the last time your contact form database was dumped).";
		}
	else {return "No contact form database detected.";}
}

function bkjf_plugin_update_count() {
	global $wpdb;
	$site_transient = get_site_transient( 'update_plugins' );
	if (!$site_transient) { return 'Looks like there are no plugin needing an update.';}
	
	$plugin_update_count = count(get_site_transient( 'update_plugins' )->response);

	if ($plugin_update_count==0) {return;}
	$plural = 's';
	if ($plugin_update_count == 1) {$plural = '';}
	return "$plugin_update_count plugin$plural needed updating.";
}

function log_simplehistory( $return='print') {	
	if (!function_exists( 'SimpleLogger' )) return;

	SimpleLogger()->info( 'BKJ Functions ITSR' );
	$args = array( 'action'=>'simple_history_api',
			   'context_message_key' => 'plugin_bulk_updated'
			   );
	$logQuery = new SimpleHistoryLogQuery();
	$logQuery = $logQuery->query( $args );
//	print_r($logQuery['log_rows']);
	$logQuery = $logQuery['log_rows'];
	$date = false;
	$updatelist = array();
	foreach ($logQuery as $k) {
		if ( $k->context_message_key == 'plugin_bulk_updated') {
		$updatelist[] = $k->context['plugin_name'];
		//print_r($k);
		}
	$date = $k->date;
	}
	$howmany = count($updatelist);

	$date = explode(' ', $date);
	$date = $date[0];
	if ($howmany == 0) {return;}
	if ($return == 'print') {return "Updated $howmany plugins since $date.";}
	if ($return == 'echo') {echo "Updated $howmany plugins since $date.";}
	return array('updated'=> $updatelist, 'date'=>$date);
	// https://docs.simple-history.com/hooks#simple_history/rss_feed_show
	//	$mything = SimpleLogger()->rss_feed_show();//
}

function bkjf_ga_stuff( ) {

	include('ga-analytics-probe.php');

	$googleAnalyticsProb = new GoogleAnalyticsProbe();
	if (!$googleAnalyticsProb->isGoogleAnalyticsPluginActive() ){ return "Google Analytics Plugin is NOT installed.";}
	//if ($googleAnalyticsProb->isGoogleAnalyticsPluginActive() ){ return "Google Analytics Plugin IS installed.";}

	$googleAnalyticsProb->getGoogleAnalyticsOptions();
	
	//$site_url = get_option('siteurl');
	$ga_id = $googleAnalyticsProb->getGoogleAnalyticsID(); // The Analytics ID
	$ga_type = $googleAnalyticsProb->getGoogleAnalyticsType(); // The Analytics Type
	
	$output = "Google Analytics Status: $ga_type $ga_id";
	
	return $output;
	
}
	
function bkjf_elementor_custom_code() {
	include('elementor-probe.php');
	return bkjf_get_elementor_custom_code();
}

