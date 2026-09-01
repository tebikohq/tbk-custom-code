<?php
/**
 * Tebiko — WhatsApp Link Resolver
 *
 * Centralizes the WhatsApp phone number and base message in one place.
 *
 * Works with both patterns:
 *
 *   Pattern A — direct anchor (e.g. an inline link in content):
 *     <a class="tbk_wa">Chat on WhatsApp</a>
 *
 *   Pattern B — wrapper around an anchor (e.g. a WP block button):
 *     <div class="wp-block-button tbk_wa">
 *       <a class="wp-block-button__link">Chat</a>
 *     </div>
 *
 * In both cases, the script finds the actual <a> tag and updates its href.
 *
 * If the phone number ever changes, update TBK_WA_PHONE below.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SINGLE SOURCE OF TRUTH.
 * Phone in international format, digits only (no +, no spaces, no dashes).
 * Example: '528112345678' for +52 81 1234 5678.
 */
if ( ! defined( 'TBK_WA_PHONE' ) ) {
    define( 'TBK_WA_PHONE', '528135707030' ); // <-- CHANGE THIS
}

if ( ! defined( 'TBK_WA_MESSAGE_EN' ) ) {
    define( 'TBK_WA_MESSAGE_EN', "Hi, I'd like more information on " );
}

if ( ! defined( 'TBK_WA_MESSAGE_ES' ) ) {
    define( 'TBK_WA_MESSAGE_ES', "Hola, me gustaría más información de" );
}

add_action( 'wp_footer', 'tbk_whatsapp_resolver_print', 95 );
function tbk_whatsapp_resolver_print() {
    if ( is_admin() || is_feed() ) {
        return;
    }
    ?>
    <script type="text/javascript">
    (function () {
        var PHONE      = '<?php echo esc_js( TBK_WA_PHONE ); ?>';
        var MESSAGE_EN = '<?php echo esc_js( TBK_WA_MESSAGE_EN ); ?>';
        var MESSAGE_ES = '<?php echo esc_js( TBK_WA_MESSAGE_ES ); ?>';
        var CLASS      = 'tbk_wa';

        function getMessage() {
            var lang = (document.documentElement.getAttribute('lang') || 'es').toLowerCase();
            return lang.indexOf('en') === 0 ? MESSAGE_EN : MESSAGE_ES;
        }

        /**
         * For a given element with the trigger class, find the actual <a> tag
         * that should receive the href.
         *   - If the element itself is an <a>, use it.
         *   - Otherwise, use the first <a> descendant inside it.
         */
        function resolveAnchor(el) {
            if (el.tagName && el.tagName.toLowerCase() === 'a') return el;
            return el.querySelector('a');
        }

        function resolveLinks() {
            var href = 'https://wa.me/' + PHONE + '?text=' + encodeURIComponent(getMessage());
            var elements = document.getElementsByClassName(CLASS);

            for (var i = 0; i < elements.length; i++) {
                var anchor = resolveAnchor(elements[i]);
                if (!anchor) continue;
                anchor.setAttribute('href', href);
                anchor.setAttribute('target', '_blank');
                anchor.setAttribute('rel', 'noopener');
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', resolveLinks);
        } else {
            resolveLinks();
        }
    })();
    </script>
    <?php
}