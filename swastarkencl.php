<?php
/**
 * Plugin Name: Starken
 * Plugin URI: https://www.softwareagil.com
 * Description: Your shipments anywhere in Chile
 * Version: 0.1.0
 * Requires at least: 4.2
 * Requires PHP: 5.6
 * Author: Software Agíl
 * Author URI: https://www.softwareagil.com
 * License: Private License
 * License URI: https://www.softwareagil.com
 * Text Domain: swastarkencl
 * Domain Path: /i18n/languages
 * Developer: Software Agíl
 * Developer URI: https://www.softwareagil.com
 */

defined( 'ABSPATH' ) || exit;

if (
    in_array(
        'woocommerce/woocommerce.php',
        apply_filters('active_plugins', get_option('active_plugins'))
    )
) {
    require_once(__DIR__ . '/vendor/autoload.php');

    if (!defined('SWASTARKENCL_PLUGIN_FILE')) {
        define('SWASTARKENCL_PLUGIN_FILE', __FILE__);
    }

    SwastarkenclStarter::init();
}

