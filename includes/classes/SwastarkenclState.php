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

if (!class_exists('SwastarkenclState')) {
    class SwastarkenclState
    {
        private $db;
        public $starken_id;
        public $code_dls;
        public $name;
        public $starken_city_id;
        public $city;
        public $city_code_dls;
        public $commune_code_dls;

        public function __construct()
        {
            global $wpdb;
            $this->db = $wpdb;
        }

        public function add()
        {
            $prepare = $this->db->prepare(
                "SELECT id FROM " . $this->db->prefix . "swastarkencl_states WHERE starken_id = %d AND code_dls = %d",
                [
                    (int) $this->starken_id,
                    (int) $this->code_dls,
                ]
            );

            if (count($this->db->get_results($prepare)) == 0) {
                return (bool) $this->db->insert(
                    $this->db->prefix . "swastarkencl_states",
                    [
                        'starken_id' => $this->starken_id,
                        'code_dls' => $this->code_dls,
                        'name' => $this->name,
                        'starken_city_id' => $this->starken_city_id,
                        'city' => $this->city,
                        'city_code_dls' => $this->city_code_dls,
                        'commune_code_dls' => $this->commune_code_dls,
                    ]
                );
            }

            return false;
        }

        public static function all()
        {
            global $wpdb;
            return $wpdb->get_results("SELECT starken_id AS id, name FROM " . $wpdb->prefix . "swastarkencl_states", ARRAY_N);
        }

        public static function list() {
            $states = SwastarkenclState::all();
            $list = [];
            foreach ($states as $state) {
                $list[$state[0]] = $state[1];
            }

            return $list;
        }

        public static function get_city_dls_code_by_starken_commune_id($starken_id)
        {
            global $wpdb;
            $city_code_dls = null;

            if ((int) $starken_id > 0) {
                $prepare = $wpdb->prepare(
                    "SELECT city_code_dls FROM " . $wpdb->prefix . "swastarkencl_states WHERE starken_id = %d",
                    [
                        (int) $starken_id,
                    ]
                );
                $city_code_dls = $wpdb->get_results($prepare)[0]->city_code_dls;
            }
            
            return $city_code_dls;
        }

        public static function get_commune_dls_code_by_starken_commune_id($starken_id)
        {
            global $wpdb;
            $commune_code_dls = null;

            if ((int) $starken_id > 0) {
                $prepare = $wpdb->prepare(
                    "SELECT commune_code_dls FROM " . $wpdb->prefix . "swastarkencl_states WHERE starken_id = %d",
                    [
                        (int) $starken_id,
                    ]
                );
                $commune_code_dls = $wpdb->get_results($prepare)[0]->commune_code_dls;
            }
            
            return $commune_code_dls;
        }

        public static function commune_exists($starken_id)
        {
            global $wpdb;

            $prepare = $wpdb->prepare(
                "SELECT id FROM " . $wpdb->prefix . "swastarkencl_states WHERE starken_id = %d",
                [
                    (int) $starken_id,
                ]
            );
            
            return count($wpdb->get_results($prepare));
        }
    }
}