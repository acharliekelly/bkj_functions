<?php
// TODO: Can the class here be used to store the data easily enough?
class MySettingsPage {
	/**
	* Holds the values to be used in the fields callbacks
	*/
	private $options;
	/**
	* Start up
	*/
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}
	/**
	* Add options page
	*/
	public function add_plugin_page()
	{
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin', 
			'BKJ Functions Settings', 
			'manage_options', 
			'bkjfunctions-setting-admin', 
			array( $this, 'create_admin_page' )
		);
	}
	/**
	* Options page callback
	*/
	public function create_admin_page()
	{
		// Set class property
		$this->options = get_option( 'bkjfunctions_option_name' );
		?>
<style>
.settings_page_bkjfunctions-setting-admin fieldset {
	border: 2px inset white;
	padding: .5em 1em;
	margin: 1em 0;
	border-radius: 10px;
}
</style>
<div class="wrap">
	<h1>BKJ Functions Settings</h1>
	<p>(Not ALL of these currently active in this version.)</p>
	<form method="post" action="options.php">
	<?php
	bkjf_proposed_settings();
	echo "TODO: Check this to see how options are stored...<br>";
	// This prints out all hidden setting fields
	settings_fields( 'bkjfunctions_option_group' );
	do_settings_sections( 'bkjfunctions-setting-admin' );
	submit_button();
	?>
	</form>
</div>
<?php
	}
	/**
	* Register and add settings
	*/
	public function page_init()	{		
		register_setting(
			'bkjfunctions_option_group', // Option group
			'bkjfunctions_option_name', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);
		add_settings_section(
			'setting_section_active', // ID
			'Settings', // Title
			array( $this, 'print_section_info' ), // Callback
			'bkjfunctions-setting-admin' // Page
		); 
	/*
	add_settings_section(
			'setting_section_inactive', // ID
			'INACTIVE Settings', // Title
			array( $this, 'print_section_info' ), // Callback
			'bkjfunctions-setting-admin' // Page
		);
*/
		// now some extra settings
		add_settings_field(
			'notes_display', // ID
			'Display Helpful Notes', // Title 
			array( $this, 'bkjf_notes_display_callback' ), // Callback
			'bkjfunctions-setting-admin', // Page
			'setting_section_active' // Section		 
		); 

		
		// now some extra settings
		add_settings_field(
			'allow_block_editor', // ID
			'Allow "Block Editor"', // Title 
			array( $this, 'bkjf_allow_block_editor' ), // Callback
			'bkjfunctions-setting-admin', // Page
			'setting_section_active' // Section		 
		); 

		// now some extra settings
		add_settings_field(
			'allow_staging_colors', // ID
			'Allow "Staging" color interface (Sunrise/red color scheme)', // Title 
			array( $this, 'bkjf_allow_staging_colors' ), // Callback
			'bkjfunctions-setting-admin', // Page
			'setting_section_active' // Section		 
		); 
		// now some extra settings
		add_settings_field(
			'force_staging_colors', // ID
			'Force "Staging" color interface (Sunrise/red color scheme)', // Title 
			array( $this, 'bkjf_force_staging_colors' ), // Callback
			'bkjfunctions-setting-admin', // Page
			'setting_section_active' // Section		 
		);  		

		// now some extra settings
		add_settings_field(
			'bkjf_no_comments', // ID
			'Do Not Allow Comments/Discussion', // Title 
			array( $this, 'bkjf_no_comments' ), // Callback
			'bkjfunctions-setting-admin', // Page
			'setting_section_active' // Section		 
		); 

	/*	add_settings_field(
			'bkjf_theme_color', 
			'Mobile Theme Color', 
			array( $this, 'bkjf_theme_color_callback' ), 
			'bkjfunctions-setting-admin', 
			'setting_section_active'
		);
	*/
		
		add_settings_field(
			'bkjf_simple_history', 
			'Simple History Duration', 
			array( $this, 'bkjf_simple_history_callback' ), 
			'bkjfunctions-setting-admin', 
			'setting_section_active'
		);

		
		/*
		add_settings_field(
			'bkjf_plugin_update_count', 
			count(get_plugin_updates()) .
			' Plugins Needing Update', 
			array( $this, 'bkjf_plugin_update_count_callback' ), 
			'bkjfunctions-setting-admin', 
			'setting_section_active'
		);
*/
		
	/*
	add_settings_field(
			'id_number', // ID
			'ID Number', // Title 
			array( $this, 'id_number_callback' ), // Callback
			'bkjfunctions-setting-admin', // Page
			'setting_section_inactive' // Section		 
		); 
		add_settings_field(
			'title', 
			'Title', 
			array( $this, 'title_callback' ), 
			'bkjfunctions-setting-admin', 
			'setting_section_inactive'
		); 
		add_settings_field(
			'url', 
			'URL', 
			array( $this, 'url_callback' ), 
			'bkjfunctions-setting-admin', 
			'setting_section_inactive'
		); 
		
		*/
		
	//	bkjf_add_settings_field_notices();
	}
	/**
	* Sanitize each setting field as needed
	*
	* @param array $input Contains all settings fields as array keys
	*/
	public function sanitize( $input )
	{
		//$new_input = array();
		if( isset( $input['id_number'] ) )
			$input['id_number'] = absint( $input['id_number'] );
		if( isset( $input['title'] ) )
			$input['title'] = sanitize_text_field( $input['title'] );
		if( isset( $input['url'] ) )
			$input['url'] = sanitize_text_field( $input['url'] );
		return $input; //$new_input;
	}
	/** 
	* Print the Section text
	*/
	public function print_section_info() {
		print 'Enter your settings below:';
	}
	/** 
	* Get the settings option array and print one of its values
	*/
	
	public function bkjf_notes_display_callback() {
	$temp = 0;
		if (isset($this->options['notes_display'] ) ) { $temp = $this->options['notes_display'];}
		//echo $this->options['notes_display'];
		$html = '<input type="checkbox" id="notes_display" name="bkjfunctions_option_name[notes_display]" value="1"' . checked( 1, $temp, false ) . ' />';
		$html = "<label>$html Display helpful tips about Post Types, etc. at top of the Page/Post/Media lists</label>";
		echo $html;
	}
	
	
	public function bkjf_plugin_update_count_callback() {
		$temp = 0;
		$html = '';
		if (isset($this->options['plugin_update_count'] ) ) { $temp = $this->options['plugin_update_count'];}
		//echo $this->options['notes_display'];
		if ($temp ==0) {
			$temp = count(get_plugin_updates());
			}
		$html .= '<input type="text" id="plugin_update_count" name="bkjfunctions_option_name[plugin_update_count]" value="' . $temp. '" />';
		$html = "<label>$html</label>";
		echo $html;
	}
		
	
		
		
	public function bkjf_allow_block_editor() {
		$temp = 0;
		if (isset($this->options['allow_block_editor'] ) ) { $temp = $this->options['allow_block_editor'];}
		    
		$html = '<input type="checkbox" id="allow_block_editor" name="bkjfunctions_option_name[allow_block_editor]" value="1"' . checked( 1, $temp, false ) . ' />';
		$html = "<label>$html Allow the Block Editor CSS to load</label>";
		echo $html;
	}
	
	public function bkjf_allow_staging_colors() {
		$temp = 0;
		if (isset($this->options['allow_staging_colors'] ) ) { $temp = $this->options['allow_staging_colors'];}
		
		$html = '<input type="checkbox" id="allow_staging_colors" name="bkjfunctions_option_name[allow_staging_colors]" value="1"' . checked( 1, $temp, false ) . ' />';
		$html = "<label>$html Allow Staging Color Scheme</label>";
		echo $html;
	}
	
	public function bkjf_force_staging_colors() {
		$temp = 0;
		if (isset($this->options['force_staging_colors'] ) ) { $temp = $this->options['force_staging_colors'];}
		
		$html = '<input type="checkbox" id="force_staging_colors" name="bkjfunctions_option_name[force_staging_colors]" value="1"' . checked( 1, $temp, false ) . ' />';
		$html = "<label>$html Force Staging Color Scheme</label>";
		echo $html;
	}
	
	public function bkjf_no_comments() {
		$temp = 0;
		if (isset($this->options['no_comments'] ) ) { $temp = $this->options['no_comments'];}
		    
		$html = '<input type="checkbox" id="allow_block_editor" name="bkjfunctions_option_name[no_comments]" value="1"' . checked( 1, $temp, false ) . ' />';
		$html = "<label>$html Do NOT Allow Comments/Discussion</label><br> You MAY need to toggle this on and off if you have existing comments or want to change anything related to comments!";
		$status = get_default_comment_status('post');
		$msg = '<BR>Current Comment status is "'. $status . '".';
		$html .= $msg;
		echo $html;
	}
	
	
	public function id_number_callback() {
		printf(
			'<input type="text" id="id_number" name="bkjfunctions_option_name[id_number]" value="%s" />',
			isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
		);
	}
	// Get the settings option array and print one of its values
	public function title_callback() {
		printf('<input type="text" id="title" name="bkjfunctions_option_name[title]" value="%s" />',
		isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : '');
		}
	
	public function url_callback() {
		printf('<input type="url" id="url" name="bkjfunctions_option_name[url]" maxlength="200" size="60" value="%s" />',
		isset( $this->options['url'] ) ? esc_attr( $this->options['url']) : '');
	}

	public function bkjf_theme_color_callback() {
		$temp = '';
		if (isset($this->options['bkjf_theme_color'] ) ) { $temp = $this->options['bkjf_theme_color'];}
		    
		$html = '<input type="text" id="bkjf_theme_color" name="bkjfunctions_option_name[bkjf_theme_color]"  maxlength="20" size="10" placeholder="#000000" value="' . $temp . '" />';
	
		$html = "<label>$html";
		
		$msg = '<span style="background-color: '. $temp . ';display: inline-block;margin-left:  2em;border: 1px solid black;padding: 8px; padding-left: 38px;border-top-left-radius: 1em;border-top-right-radius: 1em;"><span style="border-radius: 100px;padding: 3px 21px;padding-right: 50px;color: white;font-size: 90%;background-color: rgba(255,255,255,.3);">www.example.com</span><span style="margin-left: 2em;color: white;" class="dashicons dashicons-menu"></span> </span>';
		$html .= $msg;
		echo $html;

	}
	
	
	public function bkjf_simple_history_callback() {
		$temp = '366';
		if (isset($this->options['bkjf_simple_history'] ) ) { $temp = $this->options['bkjf_simple_history'];}
		if  ($temp < 1) {$temp = 366;}   
		$html = '<input type="number" style="width: 6em;" id="bkjf_simple_history" name="bkjfunctions_option_name[bkjf_simple_history]"  maxlength="4" size="4" placeholder="366" value="' . $temp . '" />';
		$html .= ' The number of days to log with <a href="index.php?page=simple_history_page">Simple History</a>, if it is active.';
		$html = "<label>$html";
        // Not sure if there should be a message here, but this was throwing an error because it was undefined so I add an empty string.
        $msg = '';

		$html .= $msg;
		echo $html;

	}

}
if( is_admin() )
	$bkjfunctions_settings_page = new MySettingsPage();

function bkjf_proposed_settings() {
	echo "These are proposed settings. ";
	echo "In future, you will be able to change the text as appropriate for your needs. ";
	
}

function bkjf_add_settings_field_notices() {
	// temporarily, we load the defaults:
	$bkjf_notices_defaults = bkjf_notices_defaults();
	foreach ($bkjf_notices_defaults as $notice) {
		bkjf_interface_output(
			$notice['bkjf_setting_notice_level'],
			$notice['bkjf_setting_notice_name'],
			$notice['bkjf_setting_notice_message'],
			$notice['bkjf_setting_notice_slug'],
			$notice['bkjf_setting_notice_status']
		);
	}	
}

function bkjf_option_wrap ($x) {
	return 'bkjfunctions_option_name[' . $x . ']';
}
// TODO: Make this into a function that works with add_settings_field()
function bkjf_interface_output($level = 'update-nag', $name = 'Type of message', $message='<strong>Title</strong> and text', $slug= 'slug', $status = true, $echo = true) {
	$output = '';
	
	$label = 'bkjnotices-' . sanitize_title_with_dashes($name);
	$label = str_replace('-', '_', $label);
	$option_level_label = $label . '_level';
	$option_message_label = $label . '_message';
	$option_name_label = $label . '_name';
	$option_slug_label = $label . '_slug';
	$option_status_label = $label . '_status';
	
	
	$output.= "<!--\n";
	$output.= "option_level_label: $option_level_label<BR>\n";
	$output.= "option_message_label: $option_message_label<BR>\n";
	$output.= "option_name_label: $option_name_label<BR>\n";
	$output.= "option_slug_label: $option_slug_label<BR>\n";
	$output.= "option_status_label: $option_status_label<BR>\n";
	$output.= "-->\n";
	
	
	// now get those out of the options:
	$option_level = get_option($option_level_label, $level);
	$option_message = get_option($option_message_label, $message);
	$option_name = get_option($option_name_label, $name);
	$option_slug = get_option($option_slug_label, $slug);
	$option_status = get_option($option_status_label, $status);
	
	// wrap these items with bkj_functions_option_name[]
	$option_level_label_name = bkjf_option_wrap($option_level_label);
	$option_message_label_name = bkjf_option_wrap($option_message_label);
	$option_name_label_name = bkjf_option_wrap($option_name_label);
	$option_slug_label_name = bkjf_option_wrap($option_slug_label);
	$option_status_label_name = bkjf_option_wrap($option_status_label);
	
	// now build some HTML elements
	$select = bkjf_options_select($level, $option_level_label_name);
	if ($option_status) {$checked = ' checked';} else {$checked = '';}
	$checkbox = "<label><input type='checkbox' $checked id='$option_status_label'  name='$option_status_label_name'> Active</label>\n";
	$textarea = "<p><textarea id='$option_message_label' class='notice $level' cols='80' name='$option_message_label_name' style='width: 80%'>$option_message</textarea></p>\n";
	$output.= "\n<p>$select $checkbox</p>\n$textarea\n";
	$output.= "<!--\n";

	$output.= "option_level: $option_level<BR>\n";
	$output.= "option_message: $option_message<BR>\n";
	$output.= "option_slug: $option_slug<BR>\n";
	$output.= "option_status: $option_status<BR>\n";
	$output.= "-->\n";
	/*
	$data       = new Settings_Data(  $slug ); // option name
	$textarea   = new Textarea( $data, 'textarea_' . $slug ); // data plus field id
	$checkbox   = new Checkbox( $data, 'checkbox_' . $slug );
	$text_field = new Text_field( $data, $level );
	*/
	$args = array( 'level' => $level,
		'name' => $name,
		'message' => $message,
		'slug' => $slug,
		'status' => $status
		);
	
	add_settings_field(
		$label,
		$name,
		'bkjf_options_echo_callback',
		'bkjfunctions-setting-admin', 
		'setting_section_inactive',
		$output
	);
	
	
	
	//if ($echo) {echo $output;}
	//return $output;
}
function bkjf_options_select($level, $label) {
	$opts = 'notice-error - error message displayed with a red border,
		notice-warning - warning message displayed with a yellow border,
		notice-success - success message displayed with a green border,
		notice-info - info message displayed with a blue border';
	$opts = array_map('trim', explode(',', $opts));
	$select = "\n<select id='$label' name='$label'>\n";
	foreach ($opts as $opt) {
		$opt = array_map('trim', explode('-',$opt));
		$opt[0] .= '-' . $opt[1];
		$selected = '';
		if ($level == $opt[0]) {$selected = ' selected';}
		$select .= "<option value='{$opt[0]}' $selected>$opt[2]</option>\n";
	}
	$select .= "</select>\n";
	return $select;
}
function bkjf_options_echo_callback($val) {
	echo $val;
}
// setting up some defaults in sort of a complex, object-based array
function bkjf_notices_defaults() {
	$return = array();

	$return['articles'] = array( 'bkjf_setting_notice_name' => 'Articles note',
		'bkjf_setting_notice_slug' => 'articles-note',
		'bkjf_setting_notice_status' => true,
		'bkjf_setting_notice_level' => 'notice-info',
		'bkjf_setting_notice_message' => 
		'<strong>Articles:</strong> 
		These are Knowledgebase articles intended to help you manage your website better. Make sure you read through the <a href="admin.php?page=wpclientref_articles">Knowledgebase!</a>
		You can even create your own Article if you want to!
		');
	//bkjnotices__page 
	$return['pages'] = array( 'bkjf_setting_notice_name' => 'Pages note',
		'bkjf_setting_notice_slug' => 'pages-note',
		'bkjf_setting_notice_status' => true,
		'bkjf_setting_notice_level' => 'notice-success',
		'bkjf_setting_notice_message' => 
		'<strong>TIP:</strong> 
		Content in this area is not likely to change as frequently as the "Posts" type of content.<br />
		Some Pages have special rules that are not immediately obvious (such as the "News" page or the Home page.)<br />
		<br />
		If you see any [shortcodes like this in brackets] please leave them alone.
		');
	

	//bkjnotices__post
	$return['posts'] = array( 'bkjf_setting_notice_name' => 'Posts note',
		'bkjf_setting_notice_slug' => 'posts-note',
		'bkjf_setting_notice_status' => true,
		'bkjf_setting_notice_level' => 'notice-success',
		'bkjf_setting_notice_message' => 
		'<strong>TIP:</strong>
		<strong>Posts</strong> are the items in your website that have dates and categories/tags associated with them.<br />
		Typically, you are going to give a Post a category so it will show up in a certain area on the site.
		The order of the post is determined by its date, which you can change.
	 
		<br />
		If you see any [shortcodes like this in brackets] please leave them alone.
		');

	//bkjnotices__misc () {
	$return['misc'] = array( 'bkjf_setting_notice_name' => 'Misc note',
		'bkjf_setting_notice_slug' => 'miscs-note',
		'bkjf_setting_notice_status' => true,
		'bkjf_setting_notice_level' => 'notice-success',
		'bkjf_setting_notice_message' => 
		'<strong>TIP:</strong>
		This "Miscellaneous" content doesn\'t necessarily show up automatically.<br />
		This is useful when you want to create something that is neither a Page (out of the normal hierarchy) nor a Post.
		<br />
		If you see any [shortcodes like this in brackets] please leave them alone.
		');

	//bkjnotices__media () {
	$return['media'] = array( 'bkjf_setting_notice_name' => 'Media note',
		'bkjf_setting_notice_slug' => 'media-note',
		'bkjf_setting_notice_status' => true,
		'bkjf_setting_notice_level' => 'notice-error',
		'bkjf_setting_notice_message' => 
		'<strong>TIP:</strong>
		Typically, you do NOT want to upload media here! You want to create a Post or Page and then click the "Upload Media" button over there, so that your images are "attached" to the Post. (It just makes life simpler that way, trust us!)
		<br /><br />
		<strong>Media</strong> refers to any type of image, jpg, gif, png, or pdf. <br />
		Typically, you want to make sure that a media item is attached to a Post or Page.<br />
		It is a good idea to periodically remove any orphaned or unattached images, if you are sure they are not being used somewhere in the website.
		');
	return $return;
}
