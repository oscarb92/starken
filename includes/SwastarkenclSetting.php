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

if (!class_exists('SwastarkenclSetting')) {
    class SwastarkenclSetting
    {
        private $api_url = 'https://api-desarrollo.starken-cloud.com/integracion';
        private $user_token = '5b2fb88e-bffb-4fd1-a53b-093cf0cd43c6';
        private $origin_commune;
        private $origin_agency;
        private $disable_checking_accounts_usage;
        private $checking_account;
        private $rut;
        private $cost_center;
        private $order_state;
        private $hide_shipping_with_no_cost;
        private $enable_log;
        private static $instance = null;

        public function __construct()
        {
            $settings = get_option('woocommerce_swastarkencl_settings');
            $this->api_url = !empty($settings['api_url']) ? $settings['api_url'] : $this->api_url;
            $this->user_token = !empty($settings['user_token']) ? $settings['user_token'] : $this->user_token;
            $this->origin_commune = $settings['origin_commune'];
            $this->origin_agency = $settings['origin_agency'];
            $this->disable_checking_accounts_usage = $settings['disable_checking_accounts_usage'];
            $this->checking_account = $settings['checking_account'];
            $this->rut = $settings['rut'];
            $this->cost_center = $settings['cost_center'];
            $this->order_state = $settings['order_state'];
            $this->hide_shipping_with_no_cost = $settings['hide_shipping_with_0_cost'];
            $this->enable_log = $settings['enable_log'];
        }

        public static function get_instance()
        {
            if (self::$instance == null) {
                self::$instance = new self();
                return self::$instance;
            }
            return self::$instance;
        }

        public function get_api_url()
        {
            return $this->api_url;
        }

        public function get_user_token()
        {
            return $this->user_token;
        }

        public function get_origin_commune()
        {
            return $this->origin_commune;
        }

        public function get_origin_agency()
        {
            return $this->origin_agency;
        }

        public function get_disable_checking_accounts_usage()
        {
            return $this->disable_checking_accounts_usage;
        }

        public function get_checking_account()
        {
            return $this->checking_account;
        }

        public function get_rut()
        {
            return $this->rut;
        }

        public function get_cost_center()
        {
            return $this->cost_center;
        }

        public function get_order_state()
        {
            return $this->order_state;
        }

        public function get_hide_shipping_with_no_cost()
        {
            return $this->hide_shipping_with_no_cost;
        }

        public function get_enable_log()
        {
            return $this->enable_log;
        }
    }
}
