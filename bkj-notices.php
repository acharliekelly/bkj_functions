<?php
// TODO: Update to modern codes; see codex or
// https://digwp.com/2016/05/wordpress-admin-notices/
// this file handles a series of useful notices when the user does something

/*
echo "<PRE style='color: black;'>";
print_r($bkjfunctions_option);
echo "</PRE>";
*/

function bkjnotices_trash_post_attachment_reminder_admin_notice() {
	$what_was_deleted = 'item';
	if (isset($_GET['post_type']) ) { $what_was_deleted = $_GET['post_type'];}
	// are there in fact any attachments?
	$found_attachments = '';
	// what's deleted could in fact be 'ids' (multiple) or 'post' (single)
	$whichid = false;
	if ( isset($_GET['ids']) ) {
		$whichid = $_GET['ids'];
		if (strpos($whichid,',')) {
			$whichid = explode(',',$whichid);
			$whichid = $whichid[0];
		}
	}
	if ( isset($_GET['post'] ) ) {$whichid = $_GET['post'];}
	if ($whichid) {
		$attachments = get_children( array( 'post_parent' => $whichid) );
		$count = count( $attachments );
		$s = 's';
		if ($count == 1) {$s = '';}
		$at_least = '';
		if ($count >0) {$at_least = ' at least ';}
		$found_attachments = "<br />Found $at_least$count attachment$s.";
		if ($count >0) {$found_attachments .= " (So you may have some orphans on your hands when the $what_was_deleted is truly deleted.) "; }
		if (strpos(@$_GET['ids'],',') > 0) {$found_attachments = "<br />Multiple posts deleted, so there could be multiple attachments to delete, too.";}
	}
	
	?>
	<div class="notice notice-warning is-dismissible">
	<p>If the <strong><?php echo $what_was_deleted; ?></strong> you deleted or trashed has any attachments, remember to delete them also, 
	<strong>if and only if</strong> you are <strong style="color: red;">not</strong> using them on <em>other</em> Posts/Pages! <br />
	<a href="upload.php?detached=1">Look for "orphaned" attachments in the Media Library</a>
	<?php echo $found_attachments; ?>
	</p>
	</div>
	<?php
}
/// looking for this: edit.php?post_type=post&trashed=1&ids=149
if ( isset($_GET['deleted']) ||  isset($_GET['trashed'])  ) {	add_action( 'admin_notices', 'bkjnotices_trash_post_attachment_reminder_admin_notice' ); }
// additional idea: jazz up the top of the page
if ( is_admin() ) {
	$whattype = false;
	if (basename($_SERVER['SCRIPT_NAME']) == 'edit.php') {
		if (isset($_GET['post_type']) ) {$whattype = $_GET['post_type'];}
		if ($whattype == 'page') { add_action( 'admin_notices', 'bkjnotices__page' ); }
		if ($whattype == 'misc') { add_action( 'admin_notices', 'bkjnotices__misc' ); }
		if ($whattype == '') { add_action( 'admin_notices', 'bkjnotices__post' ); }
		// our own internal "Notes" content type, if misc/notes plugin is installed
		if ($whattype == 'notes') { add_action( 'admin_notices', 'bkjnotices__articles' ); }
		// KB uses content type of 'client_reference' so cover that too
		if ($whattype == 'client_reference') { add_action( 'admin_notices', 'bkjnotices__articles' ); }
		//if ($whatttype == 'media') { add_action( 'admin_notices', 'bkjnotices__media' ); }
	}
	
	if (basename($_SERVER['SCRIPT_NAME']) == 'media-new.php') { add_action( 'admin_notices', 'bkjnotices__media' ); }
}

function bkjnotices__page () {
	draw_notice('updated',
		'<strong>TIP:</strong>	Content in this area is not likely to change as frequently as the "Posts" type of content.<br />
		Some Pages have special rules that are not immediately obvious (such as the "News" page or the Home page.)<br />
		<br />
		If you see any [shortcodes like this in brackets] please leave them alone.
		');
}
function bkjnotices__post () {
	draw_notice('updated',
		'<strong>TIP:</strong>
		<strong>Posts</strong> are the items in your website that have dates and categories/tags associated with them.<br />
		Typically, you are going to give a Post a category so it will show up in a certain area on the site.
		The order of the post is determined by its date, which you can change.
	 
		<br />
		If you see any [shortcodes like this in brackets] please leave them alone.
		');
}

function bkjnotices__misc () {
	draw_notice('updated bkj-notice ',
		'<strong>TIP:</strong> 
		This "Miscellaneous" content doesn\'t necessarily show up automatically.<br />
		This is useful when you want to create something that is neither a Page (out of the normal hierarchy) nor a Post.
		<br />
		If you see any [shortcodes like this in brackets] please leave them alone.
		');
}

// we use class "error" to show red bar so it may be more noticeable; we typically don't want people to upload files here
function bkjnotices__media () {
	draw_notice('notice-warning',
		'<strong>TIP:</strong>
		Typically, you do NOT want to upload media here! You want to create a Post or Page and then click the "Upload Media" button over there, so that your images are "attached" to the Post. (It just makes life simpler that way, trust us!)
		<br /><br />
		<strong>Media</strong> refers to any type of image, jpg, gif, png, or pdf. <br />
		Typically, you want to make sure that a media item is attached to a Post or Page.<br />
		It is a good idea to periodically remove any orphaned or unattached images, if you are sure they are not being used somewhere in the website.
		');
}
function bkjnotices__articles () {
	draw_notice('notice-info',
		'<strong>Articles:</strong> 
		These are Knowledgebase articles intended to help you manage your website better. Make sure you read through the <a href="admin.php?page=wpclientref_articles">Knowledgebase!</a>
		You can even create your own Article if you want to!
		');
}

function draw_notice($level = 'notice-info', $message = 'Type of message') {
	if (!bkj_notes_display()) {return;}
	echo "<div class='$level bkj-notice notice is-dismissible'><p>$message</p></div>";
}
/* handy, semi-elegant debugger: 
$bkjf_debug = print_r(error_get_last(),true);
if ($bkjf_debug) {
	draw_notice('error', "<p>Hmm, there seems to be some sort of error:</p><pre>" . $bkjf_debug . "</pre>");
}
*/