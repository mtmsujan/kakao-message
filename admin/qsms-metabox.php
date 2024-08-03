<?php

if ( !defined( 'ABSPATH' ) ) {
    die;
} // Cannot access directly.

if ( class_exists( 'CSF' ) ) {

    // Get WooCommerce order statuses
    $order_statuses = get_option( '_wc_order_statuses' ) ?? array();
    // Decode to array
    $order_statuses = json_decode( $order_statuses );

    // Prefix
    $prefix = '_qata_message';

    // Create metabox
    CSF::createMetabox( $prefix, array(
        'title'        => 'Message',
        'post_type'    => 'qata_message',
        'show_restore' => true,
    ) );

    // Convert order statuses to a format suitable for dropdown
    $status_options = array();
    foreach ( $order_statuses as $status_key => $status_label ) {
        $status_key                  = str_replace( 'wc-', '', $status_key ); // Remove 'wc-' prefix
        $status_label                = translate( $status_label );
        $status_options[$status_key] = $status_label;
    }

    CSF::createSection( $prefix, array(
        'title'  => 'Message',
        'icon'   => '',
        'fields' => array(

            // Status field
            array(
                'id'          => 'qsms_order_status',
                'type'        => 'select',
                'title'       => 'Status',
                'placeholder' => 'Select a Status',
                'options'     => $status_options,
            ),

            // Template code field
            array(
                'id'          => 'qsms_template_code',
                'type'        => 'text',
                'title'       => 'Template Code',
                'placeholder' => 'Template Code',
            ),

            // Repeater field
            array(
                'id'     => 'qsms_params',
                'type'   => 'repeater',
                'title'  => 'Parameters',
                'fields' => array(
                    array(
                        'id'          => 'qsms_param_key',
                        'type'        => 'text',
                        'title'       => 'Parameter Key',
                        'placeholder' => 'Parameter Key',
                    ),
                    array(
                        'id'          => 'qsms_param_value',
                        'type'        => 'select',
                        'title'       => 'Parameter Value',
                        'placeholder' => 'Select a Value',
                        'options'     => $status_options, // Replace with an order infos like price, phone, addresses etc etc.
                    ),
                ),
            ),

        ),
    ) );

}
