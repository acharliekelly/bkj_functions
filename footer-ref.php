<?php

function bkj_get_site_slug() {
    $urlparts = wp_parse_url(home_url());
    $domain = $urlparts['host'];
    return $domain;
}

function bkj_replace_footer_referral($content) { 

    $website = bkj_get_site_slug();
    $bkjf_referral_title = "WordPress Management by BKJ Productions, LLC";
    $bkjf_referral_link = "https://www.bkjproductions.com/?f=$website";
    $bkjf_referral_visible_text = "Greater Boston Website Management by BKJ Productions, LLC";

    $referral_html = '<span class="referral">';
    $referral_html .= '<a href="' . $bkjf_referral_link . '" target="_blank" title="' . $bkjf_referral_title . '">';
    $referral_html .= $bkjf_referral_visible_text;
    $referral_html .= '</a></span>';

    $content = str_replace('<referral>', $referral_html, $content);

}

function bkj_apply_footer_replacement() {
    // capture and replace content in footer
    ob_start('bkj_replace_footer_referral');
}

function bkj_flush_footer_replacement() {
    ob_end_flush();
}

// Hook into wp_footer to start the output buffering
add_action('wp_footer', 'bkj_apply_footer_replacement', 1);
add_action('shutdown', 'bkj_flush_footer_replacement', 100);