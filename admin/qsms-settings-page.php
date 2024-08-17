<?php

class Settings_Page {
    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'wp_ajax_save_kakao_settings', [ $this, 'save_kakao_settings' ] );
    }

    public function register_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=qata_message',
            'Kakao Settings',
            'Kakao Settings',
            'manage_options',
            'kakao-settings',
            [ $this, 'kakao_settings_page_html' ]
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( 'kakao-settings-js', plugin_dir_url( __FILE__ ) . 'js/qata-message-admin.js', array( 'jquery' ), null, true );
        wp_localize_script(
            'kakao-settings-js',
            'kakaoSettings',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'kakao_settings_nonce' ),
            )
        );
    }

    public function kakao_settings_page_html() {
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>

        <style>
            .input-fields {
                margin-top: 50px;
                width: 25%;
            }

            .api_key,
            .sender_key,
            .secret_key {
                display: block;
                margin-bottom: 20px;
                gap: 30px;
            }

            .kakao-input {
                margin-top: 5px;
            }
        </style>

        <div class="kakao-wrapper">
            <h1><?php esc_html_e( 'Kakao Settings', 'qata-message' ) ?></h1>

            <form id="kakao-settings-form">

                <div class="input-fields">
                    <div class="api_key">
                        <label for="api_key"><?php esc_html_e( 'API Key:', 'qata-message' ) ?></label>
                        <input type="password" class="widefat kakao-input" placeholder="API Key" name="api_key" id="api_key"
                            value="<?php echo esc_attr( get_option( 'kakao_api_key' ) ); ?>">
                    </div>

                    <div class="sender_key">
                        <label for="sender_key"><?php esc_html_e( 'Sender Key:', 'qata-message' ) ?></label>
                        <input type="password" class="widefat kakao-input" placeholder="Sender Key" name="sender_key"
                            id="sender_key" value="<?php echo esc_attr( get_option( 'kakao_sender_key' ) ); ?>">
                    </div>

                    <div class="secret_key">
                        <label for="secret_key"><?php esc_html_e( 'Secret Key:', 'qata-message' ) ?></label>
                        <input type="password" class="widefat kakao-input" placeholder="Secret Key" name="secret_key"
                            id="secret_key" value="<?php echo esc_attr( get_option( 'kakao_secret_key' ) ); ?>">
                    </div>
                </div>

                <input type="button" class="button button-primary" id="save" value="Save">
            </form>
        </div>

        <?php
    }

    public function save_kakao_settings() {
        check_ajax_referer( 'kakao_settings_nonce', 'nonce' );

        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized user' );
        }

        $api_key    = sanitize_text_field( $_POST['api_key'] );
        $sender_key = sanitize_text_field( $_POST['sender_key'] );
        $secret_key = sanitize_text_field( $_POST['secret_key'] );

        update_option( 'kakao_api_key', $api_key );
        update_option( 'kakao_sender_key', $sender_key );
        update_option( 'kakao_secret_key', $secret_key );

        wp_send_json_success( 'Settings saved' );
    }
}

new Settings_Page();
