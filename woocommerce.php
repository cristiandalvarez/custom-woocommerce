<?php
/**
 *
 * by Cristian Álvarez
 * 
 */

// Deactivate Zoom, Gallery or Images
add_action( 'after_setup_theme', 'cda_setup' );

function cda_setup() {
add_theme_support( 'wc-product-gallery-zoom' ); // Deactivate Zoom
add_theme_support( 'wc-product-gallery-lightbox' ); // Deactivate Lightbox
add_theme_support( 'wc-product-gallery-slider' ); // Desactivate Slider
}


// Product Image with External Link
add_filter( 'woocommerce_single_product_image_thumbnail_html', 'cda_imagen_url_externa', 100, 2 );

function cda_imagen_url_externa( $html, $post_thumbnail_id ) {
   global $product;
   if ( ! $product->is_type( 'external' ) ) return $html;
   $url = $product->add_to_cart_url();
   $pattern = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
   $html = preg_replace( $pattern, $url, $html );  
   return $html;
}