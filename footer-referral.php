<?php
 /*
  * BKJ Footer referral
  */
function bkj_get_site_slug() : string {
    $urlparts = wp_parse_url(home_url());
    $domain = $urlparts['host'];
	$domain = sanitize_title($domain);
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
    //print_r($atts);
    extract(shortcode_atts(
        array (
            'management'    => false,
            'design'        => false,
            'and'           => false
        ),
        normalize_empty_atts($atts)
    ));

/*    echo "management: " . ($management ? 'YES ' : 'NO ');
    echo "design: " . ($design ? 'YES ' : 'NO ');
    echo "and: " . ($and ? 'YES ' : 'NO ');
*/
    $type = "WordPress Design and Management";
    
    if ($design) {
        $type = 'Website Design';
    }
    if ($management) {
        $type = 'WordPress Management';
    }
    if ($design && $management) {
        $type = 'Design and Management';
    }

    $link = "https://www.bkjproductions.com/?f=$customer";
    $text = "$type: BKJ Productions";
    $title = "Greater Boston $type by BKJ Productions, LLC";
    $html = '<span class="bkj-referral">';
    $html .= "<a target='_blank' ref='noopener' title='$title' href='$link'>";
    $html .=  $text . '</a></span>';
    return $html;
}


// Register the shortcode
function bkj_register_referral_shortcode() {
    add_shortcode('Website', 'bkj_referral_shortcode');
}

// Hook into WordPress
add_action('init', 'bkj_register_referral_shortcode');



