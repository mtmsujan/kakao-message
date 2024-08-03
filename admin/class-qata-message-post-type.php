<?php

class Qata_SMS_Post_Type {
    public function __construct() {
        $this->setup_hooks();
    }

    public function setup_hooks() {
        add_action( 'init', [ $this, 'qmessage_callback_function_name' ] );
    }

    public function qmessage_callback_function_name() {
        $labels = array(
            'name'                  => _x( 'Kakao SMS', 'Post Type General Name', 'qata-message' ),
            'singular_name'         => _x( 'Kakao SMS', 'Post Type Singular Name', 'qata-message' ),
            'menu_name'             => __( 'Kakao SMS', 'qata-message' ),
            'name_admin_bar'        => __( 'Kakao SMS', 'qata-message' ),
            'archives'              => __( 'SMS Archives', 'qata-message' ),
            'attributes'            => __( 'SMS Attributes', 'qata-message' ),
            'parent_item_colon'     => __( 'Parent Message', 'qata-message' ),
            'all_items'             => __( 'Kakao SMS', 'qata-message' ),
            'add_new_item'          => __( 'Add New Message', 'qata-message' ),
            'add_new'               => __( 'Add New', 'qata-message' ),
            'new_item'              => __( 'New Message', 'qata-message' ),
            'edit_item'             => __( 'Edit Message', 'qata-message' ),
            'update_item'           => __( 'Update Message', 'qata-message' ),
            'view_item'             => __( 'View Message', 'qata-message' ),
            'view_items'            => __( 'View Messages', 'qata-message' ),
            'search_items'          => __( 'Search Message', 'qata-message' ),
            'not_found'             => __( 'Not found', 'qata-message' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'qata-message' ),
            'featured_image'        => __( 'Featured Image', 'qata-message' ),
            'set_featured_image'    => __( 'Set featured image', 'qata-message' ),
            'remove_featured_image' => __( 'Remove featured image', 'qata-message' ),
            'use_featured_image'    => __( 'Use as featured image', 'qata-message' ),
            'insert_into_item'      => __( 'Insert into Message', 'qata-message' ),
            'uploaded_to_this_item' => __( 'Uploaded to this Message', 'qata-message' ),
            'items_list'            => __( 'Messages list', 'qata-message' ),
            'items_list_navigation' => __( 'Message list navigation', 'qata-message' ),
            'filter_items_list'     => __( 'Filter Messages list', 'qata-message' ),
        );
        $args   = array(
            'label'               => __( 'Kakao SMS', 'qata-message' ),
            'description'         => __( 'Kakao SMSs', 'qata-message' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            // 'show_in_menu'        => 'woocommerce',
            'menu_position'       => 50,
            'menu_icon'           => 'dashicons-email',
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );
        register_post_type( 'qata_message', $args );
    }
}

new Qata_SMS_Post_Type();
