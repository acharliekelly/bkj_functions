<?php

/*
Name: IQ Block Country Probe
Description: Scans site for IQ Block Country plugin
Version: 0.0.1
Version History:
0.0.1 Initial Commit
*/

if (!defined('ABSPATH')) die();

class IQBlockCountryProbe {
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    // Check if the IQ Block Country plugin is active
    public function isIQBlockCountryPluginActive(): bool {
        $plugin_path = 'iq-block-country/iq-block-country.php';
        $active_plugins = get_option('active_plugins', array());

        if (is_multisite()) {
            return in_array($plugin_path, $active_plugins);
        } else {
            return is_plugin_active($plugin_path);
        }
    }
}
