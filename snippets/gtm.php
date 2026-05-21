<?php
/**
 * Google Tag Manager
 *
 * To activate:
 *   1. Replace CC_GTM_ID with your real container ID
 *   2. Set CC_GTM_ENABLED to true
 */

if (!defined('ABSPATH')) {
    exit;
}

// Master switch — set to false to disable GTM completely
define('CC_GTM_ENABLED', true);
define('CC_GTM_ID', 'GTM-XXXXXXX');

if (!CC_GTM_ENABLED) {
    return; // Exits this file, nothing else loads
}

add_action('wp_head', function () {
    ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo esc_js(CC_GTM_ID); ?>');</script>
    <!-- End Google Tag Manager -->
    <?php
}, 1);

add_action('wp_body_open', function () {
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr(CC_GTM_ID); ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
});