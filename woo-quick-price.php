<?php
/**
 * Plugin Name:       WooCommerce Quick Updater
 * Description:       High-performance update product's price in WooCommerce
 * Version:           1.0
 * Author:            Mostafa Kalantar
 */
if (!defined('ABSPATH')) exit;

define('WQP_PATH', plugin_dir_path(__FILE__));
define('WQP_URL', plugin_dir_url(__FILE__));

require_once WQP_PATH . 'includes/AdminPage.php';
require_once WQP_PATH . 'includes/Ajax.php';

add_action('plugins_loaded', function () {
    new WQP_AdminPage();
    new WQP_Ajax();
});