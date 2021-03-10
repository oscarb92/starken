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

if (!class_exists('SwastarkenclCustomerAgency')) {
    class SwastarkenclCustomerAgency
    {
        private $db;
        public $customer_id;
        public $state_id;
        public $agency_dls;
        public $customer_rut;

        public function __construct()
        {
            global $wpdb;
            $this->db = $wpdb;
        }

        public function add()
        {
            return (bool) $this->db->insert(
                $this->db->prefix . "swastarkencl_customers_agency",
                [
                    'customer_id' => $this->customer_id,
                    'agency_dls' => $this->agency_dls,
                    'state_id' => $this->state_id,
                    'customer_rut' => $this->customer_rut,
                ]
            );
        }

        public static function delete_by_customer_rut($rut)
        {
            global $wpdb;
            $wpdb->delete(
                $wpdb->prefix . "swastarkencl_customers_agency",
                [
                    'customer_rut' => $rut,
                ]
            );
        }

        public static function delete_by_customer_id($id)
        {
            global $wpdb;
            $wpdb->delete(
                $wpdb->prefix . "swastarkencl_customers_agency",
                [
                    'customer_id' => $id,
                ]
            );
        }

        public static function get_agency_dls_by_customer_rut($rut)
        {
            global $wpdb;

            $prepare = $wpdb->prepare(
                "
                    SELECT agency_dls 
                    FROM " . $wpdb->prefix . "swastarkencl_customers_agency 
                    WHERE customer_rut = %s
                    ORDER BY id DESC
                    LIMIT 1
                ",
                [
                    $rut,
                ]
            );
            $result = $wpdb->get_results($prepare);
            if (count($result) <= 0) {
                return null;
            }
            return $result[0]->agency_dls;
        }

        public static function get_agency_dls_by_customer_id($id)
        {
            global $wpdb;

            $prepare = $wpdb->prepare(
                "
                    SELECT agency_dls 
                    FROM " . $wpdb->prefix . "swastarkencl_customers_agency 
                    WHERE customer_id = %d
                    ORDER BY id DESC
                    LIMIT 1
                ",
                [
                    $id,
                ]
            );
            $result = $wpdb->get_results($prepare);
            if (count($result) <= 0) {
                return null;
            }
            return $result[0]->agency_dls;
        }
    }
}