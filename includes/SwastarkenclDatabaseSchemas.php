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

if (!class_exists('SwastarkenclDatabaseSchemas')) {
    class SwastarkenclDatabaseSchemas
    {
        private $db = null;
        private static $instance = null;

        public function __construct()
        {
            global $wpdb;
            $this->db = $wpdb; 
        }

        public static function get_instance()
        {
            if (self::$instance == null) {
                self::$instance = new self();
                return self::$instance;
            }
            return self::$instance;
        }

        public function create_all_tables()
        {
            if (!get_option('SWASTARKENCL_TABLES_CREATED')) {
                $this->create_states_table();
                $this->create_customers_agency_table();
                $this->create_issuance_table();
                add_option('SWASTARKENCL_TABLES_CREATED', true);
            }
        }

        public function create_states_table()
        {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS " . $this->db->prefix . "swastarkencl_states(
                    id INT NOT NULL AUTO_INCREMENT,
                    starken_id INT NOT NULL,
                    code_dls INT NOT NULL,
                    name VARCHAR(50),
                    starken_city_id INT NOT NULL,
                    city VARCHAR(50),
                    city_code_dls INT NOT NULL,
                    commune_code_dls INT NOT NULL,

                    PRIMARY KEY(id)
                );
            ");
        }

        public function create_customers_agency_table()
        {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . $this->db->prefix . "swastarkencl_customers_agency` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `customer_id` INT NOT NULL,
                    `customer_rut` VARCHAR(50),
                    `state_id` INT NOT NULL,
                    `agency_dls` INT NOT NULL,

                    PRIMARY KEY (`id`)
                );
            ");
        }

        public function create_issuance_table()
        {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `" . $this->db->prefix . "swastarkencl_issuance` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `order_id` INT NOT NULL,
                    `issuance_id` INT NOT NULL,
                    `delivery_type` TEXT,
                    `payment_type` TEXT,
                    `service_type` TEXT,
                    `checking_account` VARCHAR(50),
                    `cost_center` VARCHAR(50),
                    `value` FLOAT,
                    `origin_agency_code` INT NOT NULL,
                    `destination_agency_code` INT,
                    `receiver_rut` VARCHAR(50) NOT NULL,
                    `receiver_names` VARCHAR(50),
                    `receiver_paternal` VARCHAR(50),
                    `receiver_maternal` VARCHAR(50),
                    `receiver_social_reason` VARCHAR(50),
                    `receiver_address` VARCHAR(50),
                    `receiver_number` VARCHAR(50),
                    `receiver_department` VARCHAR(50),
                    `receiver_commune_code` INT,
                    `receiver_phone` VARCHAR(50) NOT NULL,
                    `receiver_email` VARCHAR(50) NOT NULL,
                    `receiver_contact` VARCHAR(50) NOT NULL,
                    `content` VARCHAR(50),
                    `total_kg` FLOAT DEFAULT 0.0,
                    `declared_value` FLOAT DEFAULT 0.0,
                    `freight_order` INT,
                    `freight_order_status` VARCHAR(50),
                    `impressions` INT,
                    `orders` TEXT,
                    `user` TEXT,
                    `master` TEXT,
                    `master_id` INT,
                    `user_id` INT,
                    `tag` TEXT,
                    `status` VARCHAR(50), 
                    `created_at` VARCHAR(50), 
                    `normalized_address` TEXT,
                    `latitude` VARCHAR(50),
                    `longitude` VARCHAR(50),
                    `associated_withdrawal` VARCHAR(50),
                    `queue_id` VARCHAR(50),
                    `observation` VARCHAR(50),
                    `retry` VARCHAR(50),
                    `updated_at` VARCHAR(50),

                    PRIMARY KEY (`id`)
                );
            ");
        }
    }
}
