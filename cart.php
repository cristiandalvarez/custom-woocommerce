<?php
/**
 * 
 * This file is part of Custom WooCommerce.
 *
 * by Cristian Álvarez
 * 
 */

// Limit to Single Product
add_filter( 'woocommerce_add_cart_item_data', 'cda_one_item_cart', 10, 1 );

function cda_one_item_cart( $cartItemData ) {
    wc_empty_cart();
    return $cartItemData;
}