<?php
/**
 * Current Year Shortcode
 *
 * Outputs the current year. Useful for copyright notices in footers
 * that should update automatically without manual edits.
 *
 * Usage:
 *   [tbk_year]                       → 2026
 *   [tbk_year format="y"]            → 26
 *   [tbk_year start="2020"]          → 2020 - 2026
 *   [tbk_year start="2020" sep=" — "] → 2020 — 2026
 *
 * Common use case (footer):
 *   © [cc_year start="2018"] Tebiko. All rights reserved.
 *   → © 2018 - 2026 Tebiko. All rights reserved.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('tbk_year', function ($atts) {
    $atts = shortcode_atts([
        'format' => 'Y',    // 'Y' for 4-digit year (2026), 'y' for 2-digit (26)
        'start'  => '',     // optional start year for a range (e.g. "2018")
        'sep'    => ' - ',  // separator between start and current year
    ], $atts, 'tbk_year');

    $current = date($atts['format'] === 'y' ? 'y' : 'Y');

    // If a start year is provided and it's different from current, show range
    if (!empty($atts['start']) && $atts['start'] !== $current) {
        return esc_html($atts['start'] . $atts['sep'] . $current);
    }

    return esc_html($current);
});
