<?php

class Create_Update_Order {
    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        add_action( 'woocommerce_thankyou', [ $this, 'create_order' ] );
        add_action( 'woocommerce_order_status_changed', [ $this, 'changed_order' ], 10, 4 );
    }

    public function create_order( $order_id ) {
        // Create Order
        $create_order = $this->call_api( 'Create Order ' . $order_id );
        // Put the api response to log file.
        $this->put_api_response_data( 'Create Order ' . $create_order );
    }

    public function changed_order( $order_id, $old_status, $new_status, $order ) {

        // Get all data from custom post type
        $posts = $this->get_posts()->posts;

        // define selected post
        $selected_post = null;

        // Loop through the posts and return this post which status = $new_status
        foreach ( $posts as $post ) {
            if ( strtolower( $post->post_title ) === $new_status ) {
                $selected_post = $post;
                break;
            }
        }

        if ( $selected_post ) {
            // Call API
            $call_api = $this->call_api( $order_id, $selected_post );
            // Put the api response to log file.
            $this->put_api_response_data( 'Call API ' . $call_api );
        }

    }

    /**
     * Get all posts
     */
    public function get_posts() {
        $args = array(
            'post_type'   => 'qata_message',
            'numberposts' => -1,
        );

        $posts = new \WP_Query( $args );
        return $posts;
    }

    public function call_api( $order_id, $message ) {
        // Get the order
        $order = wc_get_order( $order_id );

        // Get billing phone number for recipient no
        $recipient_no = $order->get_billing_phone();
        // Retrieve order data
        $order_data   = $this->get_order_data( $order );

        // Prepare template parameters
        $template_parameters = [];

        // Get post type data
        $metabox_values = get_post_meta( $message->ID, '_qata_message', true );
        // Get repeater field data
        $qsms_params    = $metabox_values['qsms_params'];

        // Loop for generate template parameters
        foreach ( $qsms_params as $param ) {
            $param_key                       = $param['qsms_param_key'];
            $param_value                     = $order_data[$param['qsms_param_value']] ?? '';
            $template_parameters[$param_key] = $param_value;
        }

        $this->put_api_response_data( 'Template Parameters ' . json_encode( $template_parameters ) );

        $payload = json_encode( [
            'senderKey'     => '10454ae1766dd86366d113b1eb2f6234b65df2ab',
            'templateCode'  => get_post_meta( $message->ID, 'qsms_template_code', true ),
            'recipientList' => [
                [
                    'recipientNo'       => $recipient_no,
                    'templateParameter' => $template_parameters,
                ],
            ],
        ] );

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL            => 'https://api-alimtalk.cloud.toast.com.bd/alimtalk/v2.3/appkeys/XEqo1OsqojDOR94y/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING       => '',
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json;charset=UTF-8',
                    'X-Secret-Key: pbEwaOWl',
                ],
            ]
        );

        $response = curl_exec( $curl );
        if ( curl_errno( $curl ) ) {
            $error_msg = curl_error( $curl );
            curl_close( $curl );
            return "cURL Error: " . $error_msg;
        }

        curl_close( $curl );
        return $response;
    }

    public function get_order_data( $order ) {
        return [
            'order_number'        => $order->get_order_number(),
            'order_total'         => $order->get_total(),
            'billing_first_name'  => $order->get_billing_first_name(),
            'billing_last_name'   => $order->get_billing_last_name(),
            'billing_address_1'   => $order->get_billing_address_1(),
            'billing_address_2'   => $order->get_billing_address_2(),
            'billing_city'        => $order->get_billing_city(),
            'billing_state'       => $order->get_billing_state(),
            'billing_postcode'    => $order->get_billing_postcode(),
            'billing_country'     => $order->get_billing_country(),
            'billing_email'       => $order->get_billing_email(),
            'billing_phone'       => $order->get_billing_phone(),
            'shipping_first_name' => $order->get_shipping_first_name(),
            'shipping_last_name'  => $order->get_shipping_last_name(),
            'shipping_address_1'  => $order->get_shipping_address_1(),
            'shipping_address_2'  => $order->get_shipping_address_2(),
            'shipping_city'       => $order->get_shipping_city(),
            'shipping_state'      => $order->get_shipping_state(),
            'shipping_postcode'   => $order->get_shipping_postcode(),
            'shipping_country'    => $order->get_shipping_country(),
            'customer_note'       => $order->get_customer_note(),
            'payment_method'      => $order->get_payment_method(),
            'transaction_id'      => $order->get_transaction_id(),
            'order_date'          => $order->get_date_created()->date( 'Y-m-d H:i:s' ),
            'order_status'        => $order->get_status(),
            'shipping_method'     => $order->get_shipping_method(),
            'shipping_total'      => $order->get_shipping_total(),
            'shipping_tax'        => $order->get_shipping_tax(),
            'discount_total'      => $order->get_discount_total(),
            'discount_tax'        => $order->get_discount_tax(),
            'cart_tax'            => $order->get_cart_tax(),
            'total_tax'           => $order->get_total_tax(),
            'order_key'           => $order->get_order_key(),
            'customer_id'         => $order->get_customer_id(),
            'order_currency'      => $order->get_currency(),
            'prices_include_tax'  => $order->get_prices_include_tax(),
            'customer_ip_address' => $order->get_customer_ip_address(),
            'customer_user_agent' => $order->get_customer_user_agent(),
        ];
    }

    public function put_api_response_data( $data ) {
        // Ensure directory exists to store response data
        $directory = QATA_MESSAGE_PLUGIN_PATH . '/api_response/';
        if ( !file_exists( $directory ) ) {
            mkdir( $directory, 0777, true );
        }

        // Construct file path for response data
        $fileName = $directory . 'response.log';

        // Get the current date and time
        $current_datetime = date( 'Y-m-d H:i:s' );

        // Append current date and time to the response data
        $data = $data . ' - ' . $current_datetime;

        // Append new response data to the existing file
        if ( file_put_contents( $fileName, $data . "\n\n", FILE_APPEND | LOCK_EX ) !== false ) {
            return "Data appended to file successfully.";
        } else {
            return "Failed to append data to file.";
        }
    }
}

new Create_Update_Order();