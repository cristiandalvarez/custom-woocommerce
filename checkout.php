<?php
/**
 * 
 * This file is part of Custom WooCommerce.
 *
 * by Cristian Álvarez
 * 
 */

// Automatic Checkout Redirect
add_filter ('woocommerce_add_to_cart_redirect', 'cda_to_checkout');

function cda_to_checkout() {
    global $woocommerce;
    $checkout_url = wc_get_checkout_url();
    return $checkout_url;
}


// Delete Additional WooCommerce Fields in Checkout
add_filter( 'woocommerce_checkout_fields' , 'cda_checkout_fields' );

function cda_checkout_fields( $fields ) {
    // Unset indicates that the field will be removed.
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_state']);
    return $fields;
}

add_filter('woocommerce_enable_order_notes_field', '__return_false');


// Customize Payment Methods
add_filter ('woocommerce_gateway_icon', function ($contenido, $id) {
    // 'paypal' refers to the name of the payment method.
    if ('paypal' == $id)
        // The image URL can be from an external site.
        // For example: <img src="https://www.paypalobjects.com/webstatic/i/logo/rebrand/ppcom-white.png" width="51"/>
        return '<img src="'.plugins_url('assets/img/paypal.png', cda_PLUGIN_FILE ).'" width="51"/>';
    return $contenido;
}, 10, 2);


// Customize WooCommerce Texts
add_filter( 'gettext', 'cda_woocommerce_text', 20, 3 );

function cda_woocommerce_text( $translated_text, $text, $domain ) {
    switch ( $translated_text ) {
        // They can be as many cases as necessary.
        case 'Name' :
        $translated_text = __( 'Nombre', 'woocommerce' );
        break;
        case 'Email' :
        $translated_text = __( 'Correo electrónico', 'woocommerce' );
        break;
    }
    return $translated_text;
}