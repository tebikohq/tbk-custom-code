<?php 
/**
 * Custom shortcode to display ACF field values.
 * Works around ACF's built-in restrictions in block themes (e.g. Spectra/FSE).
 *
 * Usage examples:
 * [tbk_field field="titulo"]
 * [tbk_field field="imagen" format="url"]
 * [tbk_field field="descripcion" format="html"]
 * [tbk_field field="titulo" post_id="123"]
 */
add_shortcode( 'tbk_field', 'tbk_acf_field_shortcode' );
function tbk_acf_field_shortcode( $atts ) {

    $atts = shortcode_atts(
        array(
            'field'   => '',
            'post_id' => get_the_ID(),
            'format'  => 'text', // text | html | url | raw
        ),
        $atts,
        'tbk_field'
    );

    if ( empty( $atts['field'] ) || ! function_exists( 'get_field' ) ) {
        return '';
    }

    $value = get_field( $atts['field'], $atts['post_id'] );

    if ( empty( $value ) && $value !== '0' ) {
        return '';
    }

    // Image field returned as array
    if ( is_array( $value ) && isset( $value['url'] ) ) {
        if ( $atts['format'] === 'url' ) {
            return esc_url( $value['url'] );
        }
        return '<img src="' . esc_url( $value['url'] ) . '" alt="' . esc_attr( $value['alt'] ?? '' ) . '">';
    }

    // Link field returned as array
    if ( is_array( $value ) && isset( $value['url'], $value['title'] ) ) {
        if ( $atts['format'] === 'url' ) {
            return esc_url( $value['url'] ); // just the URL, for use inside href=""
        }
        return '<a href="' . esc_url( $value['url'] ) . '">' . esc_html( $value['title'] ) . '</a>';
    }



    // Generic array (checkbox, repeater subfields, etc.)
    if ( is_array( $value ) ) {
        return esc_html( implode( ', ', array_filter( $value, 'is_scalar' ) ) );
    }

    // Plain string value
    if ( $atts['format'] === 'html' ) {
        return wp_kses_post( $value ); // allow safe HTML (e.g. WYSIWYG fields)
    }

    if ( $atts['format'] === 'raw' ) {
        return $value; // no escaping, use only if you trust the source
    }

    return esc_html( $value );
}