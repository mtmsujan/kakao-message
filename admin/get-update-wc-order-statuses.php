<?php

function qmessage_get_update_wc_order_statuses() {
    if ( function_exists( 'wc_get_order_statuses' ) ) {
        // Get WooCommerce order statuses
        $order_statuses = wc_get_order_statuses();

        // Update order statuses to option api
        update_option( '_wc_order_statuses', json_encode( $order_statuses ) );
    }
}
add_action( 'init', 'qmessage_get_update_wc_order_statuses' );