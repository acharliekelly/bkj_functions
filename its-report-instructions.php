<style>
.settings_page_bkjfunctions-setting-admin fieldset {
	 border: 2px inset white;
    padding: 1em;
    margin: 1em 0;
    border-radius: 10px;
}
#its_options label {
	width: 120px;
	display: inline-block;
	text-align: right;
	margin-right: 1em;
}
#its_options input { width: 150px; }
#its_options li.active {
	font-weight: bold;
	color: green;
}
#its_options li .description,
#its_options li.active .description {
	font-weight: normal;
	color: black;
	width: 25%;
	margin-left: 1em;
	font-style: normal
}
a#its_sample {background-color: #0073aa;
padding: .25em 1em; border-radius: 100px; color: white!important;}
</style>
<h1>iThemes Security Report</h1>
<p>To automatically generate a report, you can use this handy form to build a url, with the following options:</p>
<p id="its_sample_div">
	<a href="#" id='its_sample' target="blank"><?php echo home_url();?></a>
</p>
<ul id='its_options'>
<?php
	
			$plugin_updates = count(get_plugin_updates());
//echo($plugin_updates);
	
$current_user = wp_get_current_user();
$user_login = $current_user->user_login;
$user_email = $current_user->user_email;
$display_name = $current_user->display_name;
	
$vals = array(
	'its_view' => '1|View results: 1',
	'its_spreadsheet' => '1|Download a spreadsheet of the data (does <em>not</em> email, download only)',
	//'its_daterange_start' => 'last month|Enter <em>this month, last month, yesterday</em>or <em>yyyy-mm-dd</em>',
	//'its_daterange_stop' => 'this month|Enter <em>this month, last month, today </em>or <em>yyyy-mm-dd</em>',
	'its_email' => $user_email . '|Enter 1 for default Admin to send the report to, or else an email address',
	'its_store' => '0|Store results in internal KB of site',
	'its_delete' => '0|Delete data for the date range: <em style="color: red;font-weight: bold;">USE WITH CAUTION!!!!</em>',

	'its_type' => 'client_reference|Post type for KB',
	'its_ajax' => '0|<em>NOT ACTIVE YET, intended to return Ajax/json result</em>',
	'its_max404' => '3|Maximum number of 404 Not Found results to display',
	'its_maxlogin' => '5|Maximum number of failed login names to display',
	'its_maxip' => '3|Maximum number of IP addresses to look up',
	'its_debug' => '0|Turn on debug: 1',
	'its_limit' => '|Limit number of records return, in case you have a huge number of records in the database and get a blank page. Try 100 to start, then double to 200, etc.',
	);
	
foreach ($vals as $k=>$v) {
	$v = explode('|',$v);
	$v2 = $v[1];
	$v = $v[0];
	echo "<li><label for='$k'>$k</label><input id='$k' name='$k' placeholder='$v' /><span class='description'>$v2</span></li>";
}
?>
</ul>
<script language="javascript" type="text/javascript">
jQuery(function ($) {
	update_its_options();
	$('#its_options li').on('click',function(){
		var theid = $(this).children('input').attr('id');
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			$(this).children('input').val('');
		}
		else {
			var thecontent = $(this).children('input').attr('placeholder');
			$(this).children('input').val(thecontent);
		}
		update_its_options();
	});

	$('#its_options input').on('change',function(){
		update_its_options();
	});
	function update_its_options() {
		var output = window.location.protocol + '//' + window.location.hostname +'/?';
		$('#its_options li input').each(function(index,element) {
			var myval = $(this).val();
			var myid = $(this).attr('id');
			if (myval) { 
				output +=  myid + '=' + myval + '&';
				$(this).parent('li').addClass('active');
			}
			else {$(this).parent('li').removeClass('active');}
		});
		$('#its_sample').text(output);
		$('#its_sample').attr('href',output);
	}
});
</script> 
