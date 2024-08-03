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
        $create_order = $this->call_api( $order_id, 'Create Order' );
        // Put the api response to log file.
        $this->put_api_response_data( 'Create Order ' . $create_order );
    }

    public function changed_order( $order_id, $old_status, $new_status, $order ) {

        /**
         * Calls the API based on the current status.
         * For example: If the status is 'hold', retrieves the 'on-hold' custom post type message and calls the API.
         * Similar logic applies to other status values.
         */

        // Change order status
        $change_order_status = $this->call_api( $order_id, 'Change Order Status' );
        // Put the api response to log file.
        $this->put_api_response_data( 'Change Order Status ' . $change_order_status );
    }

    public function call_api( $order_id, $action ) {

        // Get the order
        $order = wc_get_order( $order_id );

        $recipient_no  = $order->get_billing_phone();
        $order_number  = $order->get_order_number();
        $order_content = 'Your order details here';
        $order_total   = $order->get_total();

        $payload = json_encode( [
            'senderKey'     => '10454ae1766dd86366d113b1eb2f6234b65df2ab',
            'templateCode'  => '1002', // Replace with your own template code
            'recipientList' => [
                [
                    'recipientNo'       => $recipient_no,
                    'templateParameter' => [
                        'ORDER_NUMBER'  => $order_number,
                        'ORDER_CONTENT' => $order_content,
                        'ORDER_TOTAL'   => $order_total,
                    ],
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