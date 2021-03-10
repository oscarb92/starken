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

defined( 'ABSPATH' ) || exit;

if (!class_exists('SwastarkenclOrderMetaData')) {
    class SwastarkenclOrderMetaData
    {
        private static $instance = null;
        public function __construct($order_id)
        {
            foreach (get_post_meta($order_id) as $key => $value) {
                // Billing == shipping address
                $this->{str_replace('_billing_', '_shipping_', $key)} = $value[0];
            }

            $this->_shipping_full_name = $this->_shipping_first_name . ' ' . $this->_shipping_last_name;
        }

        public static function get_instance($order_id)
        {
            if (self::$instance == null) {
                self::$instance = new self($order_id);
                return self::$instance;
            }
            return self::$instance;
        }
    }
}
