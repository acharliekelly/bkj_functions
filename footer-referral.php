<?php
/*
 * BKJ Referral Link
 */


 /**
  * Return contents of footer. If BKJ Functions is active, 
  * insert BKJ ref link 
  */
function bkj_footer_referral() : string {
    if (is_plugin_active('bkj-functions/bkj-functions.php')) {
        $footer_content = get_footer();
        return bkj_replace_footer_referral($footer_content);
    } else {
        return get_footer();
    }
}

function bkj_get_site_slug() : string {
    $urlparts = wp_parse_url(home_url());
    $domain = $urlparts['host'];
    return $domain;
}

function bkj_replace_footer_referral($content) : string { 

    $customer = bkj_get_site_slug();
    // TODO: use options?
    $referral_title = "WordPress Management by BKJ Productions, LLC";
    $referral_link = "https://www.bkjproductions.com/?f=$customer";
    $visible_text = "Greater Boston Website Management by BKJ Productions, LLC";

    $referral_html = '<span class="referral">';
    $referral_html .= '<a href="' . $referral_link . '" target="_blank" title="' . $referral_title . '">';
    $referral_html .= $visible_text;
    $referral_html .= '</a></span>';

    $content = str_replace('<referral/>', $referral_html, $content);
    // test
    echo "FISH";
    return $content;
}

add_action('wp_footer', 'bkj_footer_referral');
