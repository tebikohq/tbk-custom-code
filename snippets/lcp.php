<?php
/**
 * Tebiko — LCP fetchpriority by CSS class
 *
 * Add the class `tbk_lcp` to any block (Cover, Image, Gallery, Group, or a
 * custom HTML block) in the editor under "Advanced → Additional CSS class(es)".
 * On render, the first <img> inside that block gets:
 *   - fetchpriority="high"
 *   - loading="eager"   (removes any loading="lazy")
 *   - decoding="async"  (added if missing)
 *
 * Why filter at render_block instead of wp_get_attachment_image_attributes:
 * the class is set on the BLOCK wrapper, not on the image's attachment meta,
 * so it is only visible once the block HTML is assembled.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The CSS class that marks a block as containing the LCP image.
 * Change here if you ever want to rename it project-wide.
 */
if ( ! defined( 'TBK_LCP_CLASS' ) ) {
    define( 'TBK_LCP_CLASS', 'tbk_lcp' );
}

add_filter( 'render_block', 'tbk_apply_lcp_attrs_to_block', 10, 2 );
function tbk_apply_lcp_attrs_to_block( $block_content, $block ) {
    if ( empty( $block_content ) || is_admin() ) {
        return $block_content;
    }

    // Quick bail-out: if the marker class is nowhere in this block's HTML, skip.
    if ( strpos( $block_content, TBK_LCP_CLASS ) === false ) {
        return $block_content;
    }

    // Only touch the FIRST <img> in the block. LCP is a single element.
    $count = 0;
    $block_content = preg_replace_callback(
        '/<img\b[^>]*>/i',
        function ( $matches ) use ( &$count ) {
            if ( $count > 0 ) {
                return $matches[0]; // leave subsequent images alone
            }
            $count++;
            return tbk_inject_lcp_attrs( $matches[0] );
        },
        $block_content,
        1 // limit to first match for safety, even though the callback also guards
    );

    return $block_content;
}

/**
 * Inject / overwrite attributes on a single <img> tag string.
 * Idempotent: running it twice produces the same result.
 */
function tbk_inject_lcp_attrs( $img_tag ) {
    // 1) Remove loading="lazy" or any loading="..." (we'll force eager).
    $img_tag = preg_replace( '/\s+loading\s*=\s*(["\'])[^"\']*\1/i', '', $img_tag );

    // 2) Remove any existing fetchpriority="..." (we'll force high).
    $img_tag = preg_replace( '/\s+fetchpriority\s*=\s*(["\'])[^"\']*\1/i', '', $img_tag );

    // 3) Remove any existing decoding="..." so we can normalize to async.
    $img_tag = preg_replace( '/\s+decoding\s*=\s*(["\'])[^"\']*\1/i', '', $img_tag );

    // 4) Inject the new attributes right before the closing '>' (or '/>').
    $injected = ' fetchpriority="high" loading="eager" decoding="async"';
    $img_tag  = preg_replace( '/\s*\/?>$/', $injected . '$0', $img_tag, 1 );

    return $img_tag;
}