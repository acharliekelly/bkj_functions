<?php
 /*
  * BKJ Footer referral
  */
  function bkj_get_site_slug() : string {
    $urlparts = wp_parse_url(home_url());
    $domain = $urlparts['host'];
    return $domain;
}

function bkj_footer_referral() : void {
    $customer = bkj_get_site_slug();
    $referral_data = array(
        'title' => "WordPress Management by BKJ Productions, LLC",
        'link' => "https://www.bkjproductions.com/?f=$customer",
        'text' => "Greater Boston Website Management by BKJ Productions, LLC",
        'display' => is_plugin_active('bkj-functions/bkj-functions.php')
    );

    get_footer(null, $referral_data);
}

add_action('wp_footer', 'bkj_footer_referral');
