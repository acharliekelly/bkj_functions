<?php
/*
Plugin Name: BKJ Functions
Plugin URI: http://bkjproductions.com/wordpress/
Description: Includes JS Form-filling script. Disables autosave, adds Page Category/Tag/Excerpt, displays image sizes, removes Emoji, Nags you when changing themes, adds a "Class" taxonomy suitable for giving a page a class, makes Julienne Fries. Also runs an iThemes Security Report, via url. Connects with BKJ Process URLs plugin to deliver information about a client's KB. Adds a few helpful messages for the novice, at the top of the "List" type of displays of Pages and Posts. When you delete something, a reminder to delete attachments shows up. Rewrites URL for iThemes security lookups. Allows VCF (vcard) to be uploaded. Adds Log setting for Simple History.
Author: Various
Version: 1.6.6
Author URI: http://www.bkjproductions.com/

Version History: REMEMBER TO UPDATE $bkjfunctions_version IMMEDIATELY when you edit this file!
1.6.6.	Fix issues on referral shortcode
1.6.5	Referral shortcode
1.6.4	Footer referral function
1.6.3	Update CSS
1.6.2	Fixed message when deleting media files to ONLY appear is MEDIA TRASH is defined as true in wp config
1.6.1	Added some GREEN or RED text to indicate whether or not simple history is active inside of BKJ Settings + Added a note in our auto form fill alert about getting flagged by recaptcha
1.6.0	Added a counter for our flagged ITSR files also added a check for GeoLite2-Country.mmdb with a display message and to check if plugin is installed / active.
1.5.9	Added some CSS to hide annoying elementor alerts and displays. Also fixed CSS for list items in KB
1.5.8 	Added some Elementor-specific CSS
1.5.7	Changed jsf delay to 3 seconds rather than 1 second. Removed duplicate code in jsf. Added new mad-libs on ITSR.
1.5.6	Added error/deg log probe to display on ITS Reports
1.5.5jsf	Javascript Form
1.5.5	Review GA Probe
1.5.4   Add google-analytics-probe.php  (see line =~ 777)
1.5.3	Updated CSS file to hide license renewal notification
1.5.2 	Fixed duplicate call for write_log() (previously declared... somewhere else)
1.5.1	Update admin css / Address nuisance warnings
1.5.0	Added SimpleHistory logging of inline custom field edits
1.4.9.1	Add admin-level CSS, some emoji removal
1.4.8 	simplehistory 366 days
1.4.7emoji: Added script from https://geek.hellyer.kiwi/plugins/disable-emojis/
1.4.7	Fixed == in the plugin updates counter $howmany
1.4.6	Tweaks on plugin updates
1.4.5	Removed "Usually" in ITSR report and added ability to review plugin updates
1.4.4	Count of plugins needing updates
1.4.3	Restored logic from 1.4.2 removal. Fixed BIG problem with "opposite" problem, allow block editor
1.4.2	Some logic fixed for PHP8ish
1.4.1L	Remove annoying stuff from WP Dashboard initial panel
1.4.1 fix issue with count() line 495ish. Removed dubious function-media.php
1.4.0 	Fixed little bug with counting "and"s
1.3.9.4	Disabled error message reporting
1.3.9.3	Fixed limit on number of custom fields in popup (why is this not fixed in core?)
1.3.9.2 	Fixed issue with  {{ in jQuery node?
1.3.9.a 	Removed debug error
1.3.9 	fixed is_admin() bug, added dev,wpengine to staging colors, preliminary work on head-color-for-mobile
1.3.8	Added VCF as allowed upload content type
1.3.7	Added FORCE STAGING COLOR
1.3.6	Fixed small script error with undefined element in our settings array, added comment options
1.3.5	Fixed jQuery(function ($) {
1.3.4 	Added Staging check and preferences
1.3.3	Update warning when deleting things, cleaned up some @$_GET issues 
1.3.2	Finally found way to store some settings, but not all.
1.3.1 	Fixed some annoying bugs that disabled sites, and added error reporting of hidden erros
1.3.0	Added code to remove Gutenberg css
1.2.91	Fixed js error in its-ip-workaround.js
1.2.9	Added Custom Taxonomy, Class, fixed issue with Ithemes Security IP Lookup (see Console)
1.2.8	Added SVG support, Fixed little bug in bkj-notices $id wasn't defined.
1.2.7	Addied its_limit, Fix link problem for settings/notes in Plugin list (was broken, now fixed)
1.2.6	fixed problem with array_msort previously declared in chamber plugin, caused by added functionality to bkj functions
1.2.5	added referrer field for contact form 7 message
1.2.4	added function that changess ip look up address for its
1.2.3	added its_spreadsheet
		Changed category_and_tag_archives function; review this and rollback if you have to.
		Clarified that its_delete is an option that deletes
1.2.2	Fixed numerous little issues around updates etc
1.2.1	Tiny bug undeclared variable 566
1.2.0	Added beginnings of settings, improved ITSR reporting
1.1.9 fixed the create_function error 
1.1.8	Fix version string! Doh! Add is_admin check
1.1.7 remove WP nag update
1.1.6 Fixed problem with some PHP7 compatability around line 113/114
1.1.5 Removed problem with array_msort previously declared in chamber plugin, added helpful notices

1.1.4 Removed bkj/malden/melrose from suspicious places
1.1.3 Handles Notes vs. client_reference content types
1.1.2 Fix bug in bkjf_process_date_range_from_data() function
1.1.1 Add WP Updater class
1.0.9a Add way to display KB from website
1.0.8 Numberformatter issue with older PHP versions. TODO: think more about security!
1.0.7a added instructions at wp49.bkjdev.com test site 
1.0.7 changed method of accessing curl to look up IP
1.0.6 added iThemes Security Report (its-...)
1.0.5a tested defined('DISALLOW_FILE_EDIT')
1.0.4 Added information page to list functions
1.0.3 removed error notice on Plugins page
1.0.2a added bkj notice to allow "notes" custom fields to be visible
1.0.2 disable srcset 
1.0.1 cleaned out a few tests we don't need
1.0 Disable Autosave, Page Category/Tag/Excerpt, Remove Emoji

TODO: Make SimpleHistory extension a setting.
*/
$bkjfunctions_version = '1.6.6';

if ( function_exists('is_admin') && is_admin() ) {
	//require_once('wp-updates-plugin.php');
	//new WPUpdatesPluginUpdater_1831( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));
}

function bkjf_formatFileSize($sizeInBytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $unitIndex = 0;

    while ($sizeInBytes >= 1024 && $unitIndex < count($units) - 1) {
        $sizeInBytes /= 1024;
        $unitIndex++;
    }
    return round($sizeInBytes, 2) . ' ' . $units[$unitIndex];
}

// show media sizes:
/*
function setup_image_sizes() {
	//add_image_size( 'custom-image', 576, 320, true );
	
	function my_image_sizes($sizes){
		global $_wp_additional_image_sizes;
		$custom_sizes = array();
		//print_r( $_wp_additional_image_sizes);
		foreach ($_wp_additional_image_sizes as $k=>$v) {
			$custom_sizes[] = array( $k, $k . ': ' . $v['width'] . 'x'. $v['height']);
		}

		//$custom_sizes = array('custom-image' => 'Custom Image');
		return array_merge( $sizes, $custom_sizes );
	}

	add_filter('image_size_names_choose', 'my_image_sizes');
}

add_action( 'after_setup_theme', 'setup_image_sizes' );
*/
// courtesy: http://gregrickaby.com/remove-wordpress-emoji/
// Remove emoji support.
function grd_remove_emoji() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	// Remove from TinyMCE
	add_filter( 'tiny_mce_plugins', 'grd_remove_tinymce_emoji' );
}
add_action( 'init', 'grd_remove_emoji' );

// example of how to include another plugin, if desired:
// include('extra-functions/plugin-last-updated.php');

/**
 * Filter out the tinymce emoji plugin.
 */
// remove emoji
function grd_remove_tinymce_emoji( $plugins ) {
	if ( ! is_array( $plugins ) ) {
		return array();
	}
	return array_diff( $plugins, array( 'wpemoji' ) );
}
//courtesy http://www.chaosm.net/blog/2013/06/21/how-to-detect-mobile-phones-not-tablets-in-wordpress/
// tell us if this is a mobile phone:
if (!function_exists('isMobilePhone')) {
	function isMobilePhone() {
		$useragent=$_SERVER['HTTP_USER_AGENT'];
		if ( preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent,0,4) ) ) {
		return true;}
		return false;
	}
}

//see also http://wptheming.com/2011/08/admin-notices-in-wordpress/
// http://www.presscoders.com/2011/10/better-theme-activation-handling/
add_action('admin_init', 'bkj_newtheme_nag');
// remind to activate theme when incrementing 
function bkj_newtheme_nag() {
	global $pagenow;
	if ( is_admin() && isset($_GET['activated']) && $pagenow == "themes.php" ) {
		bkj_newtheme_nag_message();
	}
}
function bkj_newtheme_nag_message() {
	$msg = '<div class="updated"><p>'; 
	$msg .= 'You <em>may</em> need to update the <a href="nav-menus.php?action=locations">Menu Location settings</a> after changing the Theme.';
	$msg .= "</p></div>";
	// was add_action( 'admin_notices', create_function( '', 'echo "' . addcslashes( $msg, '"' ) . '";' ) );
	add_action( 'admin_notices', function() use ($msg) { echo $msg; });
}


/*
Add Categories to Pages. http://spoontalk.com
Easily add Categories and Tags to Pages. Simply Activate and visit the Page Edit SCreen.
*/
// Allow Pages to have Categories and Tags 
function add_taxonomies_to_pages() {
	register_taxonomy_for_object_type( 'post_tag', 'page' );
	register_taxonomy_for_object_type( 'category', 'page' );
} 
add_action( 'init', 'add_taxonomies_to_pages' );
if ( ! is_admin() ) {
	add_action( 'pre_get_posts', 'category_and_tag_archives' );
}

// Add Page as a post_type in the archive.php and tag.php 
function category_and_tag_archives( $wp_query ) {
	//$my_post_array = array('post','page');
	$args = array(
		'public'	=> true,
		/// '_builtin' => false
		);
	$my_post_array = get_post_types($args);

	if ( $wp_query->get( 'category_name' ) || $wp_query->get( 'cat' ) )
	$wp_query->set( 'post_type', $my_post_array );
	
	if ( $wp_query->get( 'tag' ) )
		$wp_query->set( 'post_type', $my_post_array );
}

/*
Disable autosave
Disables autosaving on the write/edit page/post panel
http://samm.dreamhosters.com/wordpress/plugins/
*/
add_action('admin_print_scripts', 'plugin_deregister_autosave');
// disable Autosave
function plugin_deregister_autosave() {
	wp_deregister_script('autosave');
	//echo '<!-- '.basename(__FILE__).' '.__FUNCTION__.'() -->';
}
/* Page Excerpt
from http://masseltech.com/plugins/page-excerpt/
Adds support for page excerpts - uses WordPress code
Author: Jeremy Massel
http://www.masseltech.com/
*/

add_action( 'edit_page_form', 'pe_add_box');
add_action('init', 'pe_init');

function pe_init() {
	add_post_type_support( 'page', 'excerpt' );
}
// add Page Excerpt
function pe_page_excerpt_meta_box($post) {
	?>
	<label class="hidden" for="excerpt"><?php _e('Excerpt') ?></label><textarea rows="1" cols="40" name="excerpt" tabindex="6" id="excerpt"><?php echo $post->post_excerpt ?></textarea>
	<p><?php _e('Excerpts are optional hand-crafted summaries of your content. You can <a href="https://codex.wordpress.org/Template_Tags/the_excerpt" target="_blank">use them in your template</a>'); ?></p>
	<?php
}

function pe_add_box() {
	add_meta_box('postexcerpt', __('Page Excerpt'), 'pe_page_excerpt_meta_box', 'page', 'advanced', 'core');
}
function disable_mytheme_action() {
	if (! defined('DISALLOW_FILE_EDIT')) {define('DISALLOW_FILE_EDIT', TRUE);}
}
add_action('init','disable_mytheme_action');

// https://gist.github.com/mattclements/eab5ef656b2f946c4bfb
// remove comments

if (bkjf_no_comments() == true) {
	// Disable support for comments and trackbacks in post types
	function df_disable_comments_post_types_support() {
		$post_types = get_post_types();
		foreach ($post_types as $post_type) {
			if(post_type_supports($post_type, 'comments')) {
				remove_post_type_support($post_type, 'comments');
				remove_post_type_support($post_type, 'trackbacks');
			}
		}
	}
	add_action('admin_init', 'df_disable_comments_post_types_support');

	// Close comments on the front-end
	function df_disable_comments_status() {
		return false;
	}
	add_filter('comments_open', 'df_disable_comments_status', 20, 2);
	add_filter('pings_open', 'df_disable_comments_status', 20, 2);

	// Hide existing comments
	function df_disable_comments_hide_existing_comments($comments) {
		$comments = array();
		return $comments;
	}
	add_filter('comments_array', 'df_disable_comments_hide_existing_comments', 10, 2);

	// Remove comments page in menu
	function df_disable_comments_admin_menu() {
		remove_menu_page('edit-comments.php');
	}
	add_action('admin_menu', 'df_disable_comments_admin_menu');

	// Redirect any user trying to access comments page
	function df_disable_comments_admin_menu_redirect() {
		global $pagenow;
		if ($pagenow === 'edit-comments.php') {
			wp_redirect(admin_url()); exit;
		}
	}
	add_action('admin_init', 'df_disable_comments_admin_menu_redirect');

	// Remove comments metabox from dashboard
	function df_disable_comments_dashboard() {
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	}
	add_action('admin_init', 'df_disable_comments_dashboard');

	// Remove comments links from admin bar
	function df_disable_comments_admin_bar() {
		if (is_admin_bar_showing()) {
			remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
		}
	}
	add_action('init', 'df_disable_comments_admin_bar');

} // end comments section

// from http://dinevthemes.com/how-to-disable-some-new-features-of-wordpress-4-4/
/*
function disable_srcset( $sources ) {
	return false;
}
add_filter( 'wp_calculate_image_srcset', 'disable_srcset' );
*/
// from https://www.zigpress.com/2015/12/11/how-to-disable-responsive-images-in-wordpress-4-4/
// disable SrcSet so images are just normal <IMG> tag
add_filter('wp_get_attachment_image_attributes', function($attr) {
	if (isset($attr['sizes'])) unset($attr['sizes']);
	if (isset($attr['srcset'])) unset($attr['srcset']);
	return $attr;
}, PHP_INT_MAX);
add_filter('wp_calculate_image_sizes', '__return_false', PHP_INT_MAX);
add_filter('wp_calculate_image_srcset', '__return_false', PHP_INT_MAX);
remove_filter('the_content', 'wp_make_content_images_responsive');
		

/*********************
* re-order left admin menu
*********************

function reorder_admin_menu( $__return_true ) {
	return array(
		'index.php', // Dashboard
		'edit.php?post_type=product', // Pages 
		'edit.php?post_type=page', // Pages 
		'edit.php', // Posts
		'upload.php', // Media
		'edit.php?post_type=misc', // 
		'edit.php?post_type=link', // 
		'edit.php?post_type=faq', // 
		'separator1', // --Space--
		'themes.php', // Appearance
		'separator2', // --Space--
		'edit-comments.php', // Comments 
		'users.php', // Users
		'separator3', // --Space--
		'plugins.php', // Plugins
		'tools.php', // Tools
		'options-general.php', // Settings
		'separator3', // --Space--
	);
}
add_filter( 'custom_menu_order', 'reorder_admin_menu' );
add_filter( 'menu_order', 'reorder_admin_menu' );

*/
// look for custom field "note" or "notes" to show at top of Edit Post...
function bkj_notes_notice() {
	global $current_screen, $post, $pagenow;
	if (!$post) {return;}
	if ($pagenow != 'post.php') {return;}
	$thenote = get_post_meta( $post->ID, 'notes', true );
	if (!$thenote) {$thenote = get_post_meta( $post->ID, 'note', true );}
	if ( ($current_screen->parent_base == 'edit' ) && $thenote) {
		echo '
		<div class="bkj-notice notice-info notice is-dismissable"><p>';
		echo $thenote;
		echo '</p></div>';
	}
}
add_action( 'admin_notices', 'bkj_notes_notice' );

include('its-report.php');


if (is_admin()) {include('bkj-notices.php');}
if (is_admin()) {include('bkj-notices-options.php');}
// see if we can add the KB report:
add_action('init', 'bkj_kb_report');
function bkj_kb_report() {
	if ( isset($_GET['kb_list'] ) ) {include('kb-report.php');}
}

add_action('init', 'bkj_jsform');
function bkj_jsform() {
	if ( isset($_GET['jsf'] ) ) {include('include-jsform.php');}
}


// some actions to make this plugin work:
// Add settings link on plugin page

function bkjfunctions_plugin_settings_link($links) { 
	$settings_link = '<a href="options-general.php?page=BKJ Functions Notes">Notes</a> | '; 
	$settings_link .= '<a href="options-general.php?page=bkjfunctions-setting-admin">Settings (TBD)</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'bkjfunctions_plugin_settings_link' );
add_action('admin_menu', 'bkjfunctions_plugin_menu');

function bkjfunctions_plugin_menu() {
	add_options_page(
	'BKJ Functions note', 
	'BKJ Functions Notes', 
	'manage_options', 
	'BKJ Functions Notes', 
	'bkjfunctions_plugin_options');
}

function bkjfunctions_plugin_options() {
	global $bkjfunctions_version;
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	// now the rest of this is pretty much just the instructions
	include('bkj-functions-instructions.php');
	?>
	<?php } 
// END of instructions panel 
add_action('after_setup_theme','bkj_remove_core_updates');
// Remove WP Core update nag
function bkj_remove_core_updates()	{
	if(! current_user_can('update_core')){return;}
	
	function remove_wp_version_check(){
		remove_action( 'init', 'wp_version_check' );
	}
	add_action('init', 'remove_wp_version_check',2);
		
	function remove_core_updates () {
		global $wp_version;
		return(object) array(
			'last_checked'=> time(),
			'version_checked'=> $wp_version,
			'updates' => array()
		);
	}
	add_filter('pre_site_transient_update_core','remove_core_updates');
	// add_filter('pre_site_transient_update_plugins','remove_core_updates');
	add_filter('pre_site_transient_update_themes','remove_core_updates');
}

/*ITS WORKAROUND*/
function inc_its_ip_script(){
	wp_register_script( 'bkjf_admin_custom_js', plugin_dir_url( __FILE__ ) . '/js/its-ip-workasround.js', array ( 'jquery' ), 3.141591, true);
	wp_enqueue_script('bkjf_admin_custom_js');
}

add_action('admin_init', 'its_ip_search_workaround');
add_action('admin_init', 'bkjf_admin_css');


//add_action( 'elementor/frontend/after_enqueue_styles', 'bkjf_admin_css' );
add_action( 'elementor/editor/after_enqueue_styles', 'bkjf_elementor_admin_css');

//add_action( 'elementor/frontend/before_enqueue_styles', 'bkjf_elementor_admin_css' );

/* alternate method
add_action( 'wp_enqueue_scripts', 'enqueue_elementor_custom_css' );

function enqueue_elementor_custom_css() {
    if ( defined( 'ELEMENTOR_VERSION' ) ) {
        // Replace 'your-custom-style' with your handle and 'path/to/your/custom-style.css' with your CSS file path.
        wp_enqueue_style( 'your-custom-style', plugin_dir_url( __FILE__ ) .'css/bkjf-admin.css?s=randomseed', array(), '1.0.0', 'all' );
    }
}
*/
function bkjf_elementor_admin_css() {
	$randomseed = time();
	wp_register_style( 'bkjf-admin', plugin_dir_url( __FILE__ ) ."css/bkjf-admin.css?s=$randomseed",array(), '1.0.0', 'all');
	wp_enqueue_style( 'bkjf-admin' );
}

function bkjf_admin_css() {
	wp_enqueue_style( 'bkjf-admin', plugin_dir_url( __FILE__ ) .'css/bkjf-admin.css?s=randomseed',array(), '1.0.0', 'all');
}

function its_ip_search_workaround(){
	if( is_admin() && isset($_GET['page']) == 'itsec-logs'){
		add_action( 'admin_enqueue_scripts', 'inc_its_ip_script' );
		
	}
}
/*
Instructions:
-add [hidden referer-page default:get] to form under "Form" tag
-add Referer Page: [referer-page] to message body under "Mail"

*/
function getRefererPage( $form_tag )
{
	if (isset($_SERVER['HTTP_REFERER']) && $form_tag['name'] == 'referer-page' ) {
		$form_tag['values'][] = htmlspecialchars($_SERVER['HTTP_REFERER']);
	}
	return $form_tag;
}

if ( !is_admin() ) {
	add_filter( 'wpcf7_form_tag', 'getRefererPage' );
}

function bkjf_svg_mime_types($mimes) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'bkjf_svg_mime_types');

function cptui_register_bkjf_tax_class() {
	/**
	 * Taxonomy: Classes.
	 */
	$labels = [
		"name" => __( "Classes", "custom-post-type-ui" ),
		"singular_name" => __( "Class", "custom-post-type-ui" ),
		"menu_name" => __( "Class", "custom-post-type-ui" ),
		"all_items" => __( "All Class", "custom-post-type-ui" ),
		"edit_item" => __( "Edit Class", "custom-post-type-ui" ),
		"view_item" => __( "View Class", "custom-post-type-ui" ),
		"update_item" => __( "Update Class name", "custom-post-type-ui" ),
		"add_new_item" => __( "Add New Class", "custom-post-type-ui" ),
		"new_item_name" => __( "New Class name", "custom-post-type-ui" ),
		"parent_item" => __( "Parent Class", "custom-post-type-ui" ),
		"parent_item_colon" => __( "Parent Class:", "custom-post-type-ui" ),
		"search_items" => __( "Search Class", "custom-post-type-ui" ),
		"popular_items" => __( "Popular Class", "custom-post-type-ui" ),
		"separate_items_with_commas" => __( "Separate Class with commas", "custom-post-type-ui" ),
		"add_or_remove_items" => __( "Add or remove Class", "custom-post-type-ui" ),
		"choose_from_most_used" => __( "Choose from the most used Class", "custom-post-type-ui" ),
		"not_found" => __( "No Class found", "custom-post-type-ui" ),
		"no_terms" => __( "No Class", "custom-post-type-ui" ),
		"items_list_navigation" => __( "Class list navigation", "custom-post-type-ui" ),
		"items_list" => __( "Class list", "custom-post-type-ui" ),
		"back_to_items" => __( "Back to Class", "custom-post-type-ui" ),
		"name_field_description" => __( "The name is how it appears on your site.", "custom-post-type-ui" ),
		"parent_field_description" => __( "Assign a parent Class to create a hierarchy. The class Insect, for example, would be the parent of Bee and Butterfly.", "custom-post-type-ui" ),
		"slug_field_description" => __( "The slug is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.", "custom-post-type-ui" ),
		"desc_field_description" => __( "The description is not prominent by default; however, some themes may show it.", "custom-post-type-ui" ),

	];
	$args = [
		"label" => "Classes",
		"labels" => $labels,
		"description" => "Use this as a page or body class to target CSS or set up rules for display in Elementor",	
		"public" => true,
		"publicly_queryable" => false,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'class', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => false,
		"rest_base" => "class",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => false,
		];
	register_taxonomy( "class", [ "post", "page", "attachment" ], $args );
}
add_action( 'init', 'cptui_register_bkjf_tax_class' );

function bkj_notes_display() {
	$thevalue = '';
	$bkjfunctions_option = get_option( 'bkjfunctions_option_name' );
	if (isset($bkjfunctions_option['notes_display']) ) {
		$thevalue = $bkjfunctions_option['notes_display'];
	}
	return $thevalue;
}
function bkjf_allow_block_editor() {
	$thevalue = '';
	$bkjfunctions_option = get_option( 'bkjfunctions_option_name' );
	if (isset($bkjfunctions_option['allow_block_editor']) ) {
		$thevalue = $bkjfunctions_option['allow_block_editor'];
	}
	return $thevalue;
}

function bkjf_allow_staging_colors() {
	$thevalue = '';
	$bkjfunctions_option = get_option( 'bkjfunctions_option_name' );
	if (isset($bkjfunctions_option['allow_staging_colors']) ) {
		$thevalue = $bkjfunctions_option['allow_staging_colors'];
	}
	// force will override this "allow" option
	if (isset($bkjfunctions_option['force_staging_colors']) ) {
		$thevalue = $bkjfunctions_option['force_staging_colors'];
	}
	return $thevalue;
}
function bkjf_force_staging_colors() {
	$thevalue = '';
	$bkjfunctions_option = get_option( 'bkjfunctions_option_name' );
	if (isset($bkjfunctions_option['force_staging_colors']) ) {
		$thevalue = $bkjfunctions_option['force_staging_colors'];
	}
	return $thevalue;
}

function bkjf_no_comments() {
	$thevalue = '';
	$bkjfunctions_option = get_option( 'bkjfunctions_option_name' );
	if (isset($bkjfunctions_option['no_comments']) ) {
		$thevalue = $bkjfunctions_option['no_comments'];
	}
	return $thevalue;
}

function bkjf_theme_color_wp_head() {
	if (bkjf_theme_color() >'') {
	include('include_header.php');
	}
}
	add_action('wp_head', 'bkjf_theme_color_wp_head');

function bkjf_theme_color() {
	$thevalue = '';
	$bkjfunctions_option = get_option( 'bkjfunctions_option_name' );
	if (isset($bkjfunctions_option['bkjf_theme_color']) ) {
		$thevalue = $bkjfunctions_option['bkjf_theme_color'];
	}
	return $thevalue;
}

// thanks to https://mycyberuniverse.com/loading-scripts-on-wordpress-admin-pages.html
//	add script only on this page in Admin: 
//	wp-admin/admin.php?page=itsec-logs
function enqueue_bkjf_scripts($hook) {
	wp_enqueue_script('wp-deactivation-message', plugins_url('js/message-deactivate.js', __FILE__), array());
	if ( 'security_page_itsec-logs' != $hook ) {
		return;
	}
	inc_its_ip_script();
	// ENQUEUE SCRIPTS…

}
add_action( 'admin_enqueue_scripts', 'enqueue_bkjf_scripts' );

if ( !bkjf_allow_block_editor()) {
	// Alternative from https://smartwp.com/remove-gutenberg-css/
	// Fully Disable Gutenberg editor.
	add_filter('use_block_editor_for_post_type', '__return_false', 10);
	// Don't load Gutenberg-related stylesheets.
	add_action( 'wp_enqueue_scripts', 'bkjf_remove_block_css', 100 );

	function bkjf_remove_block_css() {
		wp_dequeue_style( 'wp-block-library' ); // WordPress core
		wp_dequeue_style( 'wp-block-library-theme' ); // WordPress core
		wp_dequeue_style( 'wc-block-style' ); // WooCommerce
		wp_dequeue_style( 'storefront-gutenberg-blocks' ); // Storefront theme
		/// from https://wordpress.org/support/topic/disabling-gutenberg-duotone-filter/
		wp_dequeue_style( 'global-styles' );


		// Remove unwanted SVG filter injection WP
		remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
	
	}

}
/*
//https://wordpress.stackexchange.com/questions/313419/different-color-admin-bars-for-dev-staging-and-production
//https://wordpress.stackexchange.com/questions/126697/wp-3-8-default-admin-colour-for-all-users
Default color schemes:
fresh
light
blue
coffee
ectoplasm
midnight
ocean
sunrise
	*/
	
add_filter( 'get_user_option_admin_color', 'bkjf_get_user_option_admin_color', 5 );

function bkjf_get_user_option_admin_color( $color_scheme ) {
	$default = 'sunrise';
	if (!bkjf_allow_staging_colors() ) {return $color_scheme;}

	if (bkjf_force_staging_colors() ) {return $default;}

	$site_title = get_bloginfo( 'name' );
	$site_url = get_bloginfo( 'url' );
	$possibilities = 'development,staging,stage,devserver,test,dev,wpengine';
	$possibilities = explode(',', $possibilities);
	foreach ($possibilities as $possibility) {
		if ( strpos($site_title,$possibility)>0 ) {
			$color_scheme = $default;
		}
		if ( strpos($site_url,$possibility)>0 ) {
			$color_scheme = $default;
		}
	}
	return $color_scheme;
}

function bkjf_admin_head_css() {
	echo '<style>#wpadminbar {background-color: #cf4944;};</style>';
}

function bkjf_admin_head_custom_theme_setup() {
	if (!bkjf_allow_staging_colors() ) {return;}

	add_theme_support( 'admin-bar', array( 'callback' => 'bkjf_admin_head_css') );
}
add_action( 'after_setup_theme', 'bkjf_admin_head_custom_theme_setup' );
//https://wordpress.org/support/topic/allow-upload-vcf-in-wordpress-5-0-3/
function bkjf_enable_vcard_upload( $mime_types=array() ){
  	$mime_types['vcf'] = 'text/vcard';
	$mime_types['vcard'] = 'text/vcard';
  	return $mime_types;
}
add_filter('upload_mimes', 'bkjf_enable_vcard_upload' );

add_filter( 'postmeta_form_limit', 'bkjf_meta_limit_increase' );
function bkjf_meta_limit_increase( $limit ) {
    return 99999;
}

function bkjf_dweandw_remove() {
    remove_meta_box( 'dashboard_primary', get_current_screen(), 'side' );
}
add_action( 'wp_network_dashboard_setup', 'bkjf_dweandw_remove', 20 );
add_action( 'wp_user_dashboard_setup',    'bkjf_dweandw_remove', 20 );
add_action( 'wp_dashboard_setup',         'bkjf_dweandw_remove', 20 );
function bkjf_disable_annoying_dashboard_widgets_widget() {
	remove_meta_box( 'e-dashboard-overview', 'dashboard', 'normal');
	remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
	remove_meta_box('itsec-dashboard-widget', 'dashboard', 'normal');
	remove_action( 'welcome_panel', 'wp_welcome_panel' );
}
add_action('wp_dashboard_setup', 'bkjf_disable_annoying_dashboard_widgets_widget', 40);


//a test for Simple History
add_filter( "simple_history/db_purge_days_interval", function( $days ) {
	$bkjfunctions_option = get_option( 'bkjfunctions_option_name' );
	$howmanydays = $bkjfunctions_option['bkjf_simple_history'];
	if ($howmanydays <1 ) {$howmanydays = 366;}
     $days = 366;
     return $days;
 } );
 
 
/**
 * https://geek.hellyer.kiwi/plugins/disable-emojis/
 * Disable the emojis.
 */
function bkjf_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );	
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'bkjf_disable_emojis_tinymce' );
	add_filter( 'wp_resource_hints', 'bkjf_disable_emojis_remove_dns_prefetch', 10, 2 );
}
add_action( 'init', 'bkjf_disable_emojis' );

/**
 * Filter function used to remove the tinymce emoji plugin.
 * 
 * @param    array  $plugins  
 * @return   array             Difference betwen the two arrays
 */
function bkjf_disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	}

	return array();
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param  array  $urls          URLs to print for resource hints.
 * @param  string $relation_type The relation type the URLs are printed for.
 * @return array                 Difference betwen the two arrays.
 */
function bkjf_disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {

	if ( 'dns-prefetch' == $relation_type ) {

		// Strip out any URLs referencing the WordPress.org emoji location
		$emoji_svg_url_bit = 'https://s.w.org/images/core/emoji/';
		foreach ( $urls as $key => $url ) {
			if ( strpos( $url, $emoji_svg_url_bit ) !== false ) {
				unset( $urls[$key] );
			}
		}

	}

	return $urls;
}


include('footer-referral.php');



include('simplehistory.php');



// Google Analytics Prob

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}
