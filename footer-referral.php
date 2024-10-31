<?php
 /*
  * BKJ Footer referral
  */
function bkj_get_site_slug() : string {
    $urlparts = wp_parse_url(home_url());
    $domain = $urlparts['host'];
    return $domain;
}



if (!function_exists('normalize_empty_atts')) {
    function normalize_empty_atts($atts) : array {
        foreach ($atts as $attribute => $value) {
            if (is_int($attribute)) {
                $atts[strtolower($value)] = true;
                unset($atts[$attribute]);
            }
        }
        return $atts;
    }
}


function bkj_referral_shortcode($atts, $content = null) : string {
    $customer = bkj_get_site_slug();
    print_r($atts);
    extract(shortcode_atts(
        array (
            'Management'    => false,
            'Design'        => false,
            'and'           => false
        ),
        normalize_empty_atts($atts)
    ));

    echo "management: " . ($management ? 'YES ' : 'NO ');
    echo "design: " . ($design ? 'YES ' : 'NO ');
    echo "and: " . ($and ? 'YES ' : 'NO ');

    $type = "stuff";
    
    if ($design) {
        $type = 'Design';
    }
    if ($management) {
        $type = 'Management';
    }
    if ($and) {
        $type = 'Management and Design';
    }

    $link = "https://bkjproductions.com/?f=$customer";
    $title = "Website $type by BKJ Productions, LLC";
    $text = "Greater Boston Website $type by BKJ Productions, LLC";
    $html = '<span class="bkj-referral">';
    $html .= '<a target="_blank" ref="noopener" title="' . $title . '" href="' . $link . '">';
    $html .=  $text . '</a></span>';
    return $html;
}


// Register the shortcode
function bkj_register_referral_shortcode() {
    add_shortcode('Website', 'bkj_referral_shortcode');
}

// Hook into WordPress
add_action('init', 'bkj_register_referral_shortcode');



