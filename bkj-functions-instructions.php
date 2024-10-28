<?php
$bkjf_debug = print_r(error_get_last(),true);
if ($bkjf_debug) {
	draw_notice('error', "<p>Hmm, there seems to be some sort of error:</p><pre>" . $bkjf_debug . "</pre><p>This is probably <strong>NOT</strong> related to the BKJ Functions plugin. But you should probably check it out!");
}
// This file is designed to make it relatively easy to set up a basic information panel about the plugin. It's mostly just HTML.
global $bkjfunctions_version;
?>
<div class="wrap">
<div class="icon32" id="icon-plugins"></div>
<h2><span class="dashicons dashicons-admin-tools"></span> BKJ Functions <?php echo $bkjfunctions_version; ?> </h2>
<h3>Notes</h3>
<p>This plugin adds a few helpful messages for the novice, at the top of the &quot;List&quot; type of displays of Pages and Posts. Also when you delete something, a reminder to delete attachments shows up.</p>
<form id="bkjfunctions" name="bkjfunctions">
<table class="widefat">
<tbody><tr><td colspan="4">
<?php include('its-report-instructions.php'); ?>
</td></tr>
</tbody></table>
</form>
	
<?php
	bkjf_reveal_functions();
?>
	
<p>Plugin by 
<a href="https://www.bkjproductions.com" target="_blank">BKJproductions.com</a>
</p>
</div>
<script language="javascript" type="text/javascript">
jQuery(function ($) {
});
</script>
<?php 
function bkjf_reveal_functions() {
	$found = array();
	$mylist = file(__DIR__.'/bkj-functions.php');
	$k = -1;
	foreach ($mylist as $line) {
		if (strpos($line, 'function ') >-1) {
			// does previous line have a comment?
			if (strpos($mylist[$k], '// ') >-1) {
				$line = str_replace('{','',$line); 
				$found[] = '<th>' . str_replace('function ', '', $line) .
					'<td>' . str_replace('// ', '', $mylist[$k]) ;
				}
			}
			$k++;
	}
	$found = implode('<tr>',$found);
	if ($found) {
		echo '<table class="widefat">
		<thead><tr><th valign="top" colspan="2">Functions</th></thead>
		<tbody>';
		echo $found;
		echo '</tbody></table><br />';
	}
	else {
		//hmm we can't show functions
		echo "<p>(Cannot seem to display functions in this file.)</p>";
	}
	
}