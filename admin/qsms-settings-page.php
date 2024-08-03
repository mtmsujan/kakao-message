<?php

class Settings_Page {
    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
    }

    public function register_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'Kakao Settings',
            'Kakao Settings',
            'manage_options',
            'kakao-settings',
            [ $this, 'kakao_settings_page_html' ]
        );
    }

    public function kakao_settings_page_html() {
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
    }
}

new Settings_Page();