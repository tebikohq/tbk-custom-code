<?php
/**
 * Google Preferred Sources Shortcode
 *
 * Add this code to your child theme's functions.php,
 * or better, wrap it in a small custom plugin so it
 * survives theme changes.
 *
 * Usage inside Gutenberg / Site Editor:
 * Add a "Shortcode" block and type:
 *   [preferred_source]
 *   [preferred_source theme="dark"]
 *   [preferred_source align="right"]
 *   [preferred_source theme="dark" align="left"]
 *
 * You can also drop the shortcode directly into a
 * template part (header, footer, sidebar) using the
 * "Shortcode" block inside the Site Editor.
 */

// Keeps track of whether the script/styles were already enqueued,
// so they only load once even if the shortcode is used more than once
function site_preferred_source_shortcode( $atts ) {

    $atts = shortcode_atts(
        array(
            'theme' => 'light',
            'align' => 'center',
        ),
        $atts,
        'preferred_source'
    );

    $theme     = ( 'dark' === $atts['theme'] ) ? 'dark' : 'light';
    $alignment = in_array( $atts['align'], array( 'left', 'center', 'right' ), true )
        ? $atts['align']
        : 'center';

    // Enqueue Google's script and our tracking code only when the shortcode is actually used
    site_preferred_source_enqueue_assets();

    $output = '<div class="preferred-sources-wrap preferred-sources-align-' . esc_attr( $alignment ) . '">';
    $output .= '<div google-add-preferred-source-btn';

    if ( 'dark' === $theme ) {
        $output .= ' data-theme="dark"';
    }

    $output .= '></div>';
    $output .= '</div>';

    return $output;
}
add_shortcode( 'tbk_preferred_source', 'site_preferred_source_shortcode' );

/**
 * Loads Google's publisher script, the layout CSS, and the GA4
 * click-tracking script. Runs only once per page even if the
 * shortcode appears multiple times.
 */
function site_preferred_source_enqueue_assets() {

    static $already_loaded = false;

    if ( $already_loaded ) {
        return;
    }
    $already_loaded = true;

    // Load Google's official embed script, async, in the footer
    wp_enqueue_script(
        'google-preferred-sources',
        'https://news.google.com/swg/js/v1/publisher.js',
        array(),
        null,
        array(
            'strategy'  => 'async',
            'in_footer' => true,
        )
    );

    // Minimal layout styles for the alignment options
    $inline_css = <<<CSS
/*
==========================================================================
PREFERRED SOURCES SHORTCODE LAYOUT
==========================================================================
*/
.preferred-sources-wrap {
    display: flex;
    width: 100%;
}
.preferred-sources-align-left {
    justify-content: flex-start;
}
.preferred-sources-align-center {
    justify-content: center;
}
.preferred-sources-align-right {
    justify-content: flex-end;
}
CSS;

    wp_register_style( 'site-preferred-sources-style', false );
    wp_enqueue_style( 'site-preferred-sources-style' );
    wp_add_inline_style( 'site-preferred-sources-style', $inline_css );

    // GA4 click tracking, delegated because Google renders the badge after page load
    $tracking_script = <<<'JS'
(function () {
    if ( window.__preferredSourceButtonTrackingBound ) {
        return;
    }
    window.__preferredSourceButtonTrackingBound = true;

    // Check if the click happened inside the preferred-source button
    function isPreferredSourceClick( event ) {
        var path = typeof event.composedPath === 'function'
            ? event.composedPath()
            : [];

        for ( var i = 0; i < path.length; i += 1 ) {
            var node = path[i];
            if (
                node
                && node.nodeType === 1
                && typeof node.hasAttribute === 'function'
                && node.hasAttribute( 'google-add-preferred-source-btn' )
            ) {
                return true;
            }
        }

        return Boolean(
            event.target
            && typeof event.target.closest === 'function'
            && event.target.closest( '[google-add-preferred-source-btn]' )
        );
    }

    // Detect if Google Tag Manager is present on the page
    function hasGoogleTagManager() {
        if ( ! window.google_tag_manager ) {
            return false;
        }

        return Object.keys( window.google_tag_manager ).some( function ( key ) {
            return key.indexOf( 'GTM-' ) === 0;
        } );
    }

    document.addEventListener( 'click', function ( event ) {
        if ( ! isPreferredSourceClick( event ) ) {
            return;
        }

        // Prefer GTM's dataLayer if available
        if ( hasGoogleTagManager() && Array.isArray( window.dataLayer ) ) {
            window.dataLayer.push( { event: 'preferred_source_button_click' } );
            return;
        }

        // Fall back to gtag.js if GTM is not present
        if ( typeof window.gtag === 'function' ) {
            window.gtag( 'event', 'preferred_source_button_click' );
        }
    }, true );
}());
JS;

    wp_add_inline_script(
        'google-preferred-sources',
        $tracking_script,
        'after'
    );
}