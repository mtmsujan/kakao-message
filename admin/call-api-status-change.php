<?php

class Create_Update_Order {
    public function __construct() {
        $this->setup_hooks();
    }

    // Setup hooks for WooCommerce actions
    public function setup_hooks() {
        add_action( 'woocommerce_thankyou', [ $this, 'create_order' ] );
        add_action( 'woocommerce_order_status_changed', [ $this, 'changed_order' ], 10, 4 );
    }

    // Handle WooCommerce thank you action to create an order
    public function create_order( $order_id ) {
        // Get all custom post type data
        $posts = $this->get_posts()->posts;

        $this->put_api_response_data( 'Posts ' . json_encode( $posts ) );

        // Define selected post based on new order status
        $selected_post = null;

        // Loop through posts to find the one matching the new status
        foreach ( $posts as $post ) {
            if ( strtolower( $post->post_title ) === 'processing' ) {
                $selected_post = $post;
                break;
            }
        }

        // $this->put_api_response_data( 'Selected Post ' . json_encode( $selected_post ) );

        // Create Order via API
        // $create_order = $this->call_api( $order_id, null );
        // Log API response
        // $this->put_api_response_data( 'Create Order ' . $create_order );
    }

    // Handle WooCommerce order status change
    public function changed_order( $order_id, $old_status, $new_status, $order ) {
        // Get all custom post type data
        $posts = $this->get_posts()->posts;

        // Define selected post based on new order status
        $selected_post = null;

        // Loop through posts to find the one matching the new status
        foreach ( $posts as $post ) {
            // Retrieve metabox status value
            $metabox_values = get_post_meta( $post->ID, '_qata_message', true );
            // Get post type data
            $message_status = $metabox_values['qsms_order_status'] ?? '';

            // Compare the selected post status with new status
            if ( $message_status === $new_status ) {
                $selected_post = $post;
                break;
            }
        }

        // $this->put_api_response_data( 'Selected Post ' . json_encode( $selected_post ) );

        // Get order content from selected post
        $order_content = $selected_post->post_content ?? '';

        // Save order content to WordPress options table
        update_option( '_order_content_' . $order_id, $order_content );

        if ( $selected_post ) {
            // Call API with selected post data
            $call_api = $this->call_api( $order_id, $selected_post );
            // Log API response
            $this->put_api_response_data( 'Call API ' . $call_api );
        }
    }

    // Retrieve all custom post type data
    public function get_posts() {
        $args = array(
            'post_type'   => 'qata_message',
            'numberposts' => -1,
        );

        $posts = new \WP_Query( $args );
        return $posts;
    }

    // Call external API with order and message data
    public function call_api( $order_id, $message ) {
        // Get WooCommerce order object
        $order = wc_get_order( $order_id );

        // Get billing phone number for recipient number
        $recipient_no = $order->get_billing_phone();

        // Retrieve order data
        $order_data = $this->get_order_data( $order );

        // Prepare template parameters from metabox values
        $template_parameters = [];

        // Get post type data
        $metabox_values = get_post_meta( $message->ID, '_qata_message', true );
        // Get repeater field data
        $qsms_params = $metabox_values['qsms_params'];

        // Get template code
        $template_code = $metabox_values['qsms_template_code'];

        // Loop through params to generate template parameters
        foreach ( $qsms_params as $param ) {
            $param_key                       = $param['qsms_param_key'];
            $param_value                     = $order_data[$param['qsms_param_value']] ?? '';
            $template_parameters[$param_key] = $param_value;
        }

        // Get api credentials
        $api_key    = get_option( 'kakao_api_key' ) ?? '';
        $sender_key = get_option( 'kakao_sender_key' ) ?? '';

        // Prepare payload for API request
        $payload = json_encode( [
            'senderKey'     => $sender_key,
            'templateCode'  => $template_code,
            'recipientList' => [
                [
                    'recipientNo'       => $recipient_no,
                    'templateParameter' => $template_parameters,
                ],
            ],
        ] );

        // Initialize cURL
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL            => 'https://api-alimtalk.cloud.toast.com/alimtalk/v2.3/appkeys/' . urlencode( $api_key ) . '/messages',
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

        // Execute cURL and handle errors
        $response = curl_exec( $curl );
        if ( curl_errno( $curl ) ) {
            $error_msg = curl_error( $curl );
            curl_close( $curl );
            return "cURL Error: " . $error_msg;
        }

        curl_close( $curl );
        return $response;
    }

    // Get order data from WooCommerce order object
    public function get_order_data( $order ) {
        // Get order ID
        $order_id = $order->get_id();
        // Get order content from WordPress options table
        $order_content = get_option( '_order_content_' . $order_id ) ?? '';
        return [
            'order_number'        => $order->get_order_number(),
            'order_total'         => $order->get_total(),
            'order_content'       => $order_content,
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

    // Log API response data to a file
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

// Instantiate the class to set up hooks
new Create_Update_Order();
