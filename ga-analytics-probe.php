<?php

/*
Name: Google Analytics Probe
Description: Scans site for Google Analytics
Version: 0.0.1
Version History:
0.0.1 Initial Commit
*/


if (!defined('ABSPATH')) die();
//include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
/*
if (is_plugin_active_for_network('ga-google-analytics/ga-google-analytics.php')){
    error_log("Plugin is active");
}
*/
class GoogleAnalyticsProbe {
	private $wpdb; // This classes instance of the global WordPress DB Object
	private $options; // Object to hold plugin options

	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		//$this->getGoogleAnalyticsOptions();
	}

	public function isGoogleAnalyticsPluginActive(): bool
	{

		$plugin_path = 'ga-google-analytics/ga-google-analytics.php';
		$active_plugins = get_option('active_plugins', array());

		if (is_multisite()){
			return (in_array($plugin_path, $active_plugins));
		}else {
			return is_plugin_active($plugin_path);
		}
	}


    public function getGoogleAnalyticsOptions() {
        $option_name = 'gap_options';  // Google Analytics Options
        $table_name = $this->wpdb->prefix . 'options'; // The options table

        // Get the value from the database
        $option_value = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT option_value FROM $table_name WHERE option_name = %s",
                $option_name
            )
        );

        // Access the option value
        if ($option_value !== null) {
            // Option value exists
            $gap_object = unserialize($option_value);
            $this->options = $gap_object;


            // Numeric to string mapping for Analytics Type
            $option_mapping = [
                1 => 'UA (deprecated!)',
                2 => 'Google Tag',
                3 => 'Legacy (deprecated!)'
            ];

            $this->options['gap_enable'] = $option_mapping[$this->options['gap_enable']];


            //return $gap_object;
        } else {
		// Option value does not exist
		// error_log("Option value not found.");
		return null;
        }
    }
	public function getGoogleAnalyticsID(){
		$myoption = $this->options['gap_id'];
		if (!$myoption) {return "No ID set";}
		return $this->options['gap_id'];
	}
	public function getGoogleAnalyticsType()
	{
		return $this->options['gap_enable'];
	}
	public function getGAcustomCode() {
		return $this->options['gap_custom'];
	}
}
