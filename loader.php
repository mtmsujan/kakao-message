<?php

/**
 * Load the plugin files and classes
 * 
 * @package Qata Message
 * @since 1.0.0
 */

// Include post type and metabox file
include_once QATA_MESSAGE_PLUGIN_PATH . '/admin/class-qata-message-post-type.php';
include_once QATA_MESSAGE_PLUGIN_PATH . '/admin/qsms-metabox.php';
include_once QATA_MESSAGE_PLUGIN_PATH . '/admin/get-update-wc-order-statuses.php';
include_once QATA_MESSAGE_PLUGIN_PATH . '/admin/qsms-settings-page.php';

//  Require api call file
require_once QATA_MESSAGE_PLUGIN_PATH . '/admin/call-api-status-change.php';