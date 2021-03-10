<?php
/**
 * Swastarkencl is private software: you CANNOT redistribute it, sell it and/or 
 * modify it under or in any form without written authorization of its owner / developer.
 *
 * Swastarkencl is distributed / sell in the hope that it will be useful to you.
 *
 * You should have received a copy of its License along with Swastarkencl. 
 * If not, see https://www.softwareagil.com.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "swastarkencl_states");
$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "swastarkencl_customers_agency");
$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "swastarkencl_issuance");
delete_option('SWASTARKENCL_TABLES_CREATED');
delete_option('SWASTARKENCL_STATED_ADDED');