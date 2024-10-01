jQuery(function ($) {
	console.log("Script to replace ithemes IP lookup added, courtesy BKJ Functions");
	$( 'td.column-remote_ip a' ).each( function( item ) {	
		var anchor_org = $(this).attr('href');
		var anchor_new = anchor_org.replace("http://www.iptrackeronline.com/ithemes.php?ip_address=", "https://ipapi.co/" );
		$(this).attr( 'href', anchor_new );
		console.log('updated iThemes IP lookup');
	});
	
});