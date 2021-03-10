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

if (!class_exists('SwastarkenclIssuance')) {
    class SwastarkenclIssuance
    {
        private $db;
        public $order_id;
        public $issuance_id;
        public $delivery_type;
        public $payment_type;
        public $service_type;
        public $checking_account;
        public $cost_center;
        public $value;
        public $origin_agency_code;
        public $destination_agency_code;
        public $receiver_rut;
        public $receiver_names;
        public $receiver_paternal;
        public $receiver_maternal;
        public $receiver_social_reason;
        public $receiver_address;
        public $receiver_number;
        public $receiver_department;
        public $receiver_commune_code;
        public $receiver_phone;
        public $receiver_email;
        public $receiver_contact;
        public $content;
        public $total_kg;
        public $declared_value;
        public $freight_order;
        public $freight_order_status;
        public $impressions;
        public $orders;
        public $user;
        public $master;
        public $master_id;
        public $user_id;
        public $tag;
        public $status;
        public $created_at;
        public $normalized_address;
        public $latitude;
        public $longitude;
        public $associated_withdrawal;
        public $queue_id;
        public $observation;
        public $retry;
        public $updated_at;

        public function __construct()
        {
            global $wpdb;
            $this->db = $wpdb;
        }

        public function add()
        {
            return (bool) $this->db->insert(
                $this->db->prefix . "swastarkencl_issuance",
                [
                    'order_id' => $this->order_id,
                    'issuance_id' => $this->issuance_id,
                    'delivery_type' => $this->delivery_type,
                    'payment_type' => $this->payment_type,
                    'service_type' => $this->service_type,
                    'checking_account' => $this->checking_account,
                    'cost_center' => $this->cost_center,
                    'value' => $this->value,
                    'origin_agency_code' => $this->origin_agency_code,
                    'destination_agency_code' => $this->destination_agency_code,
                    'receiver_rut' => $this->receiver_rut,
                    'receiver_names' => $this->receiver_names,
                    'receiver_paternal' => $this->receiver_paternal,
                    'receiver_maternal' => $this->receiver_maternal,
                    'receiver_social_reason' => $this->receiver_social_reason,
                    'receiver_address' => $this->receiver_address,
                    'receiver_number' => $this->receiver_number,
                    'receiver_department' => $this->receiver_department,
                    'receiver_commune_code' => $this->receiver_commune_code,
                    'receiver_phone' => $this->receiver_phone,
                    'receiver_email' => $this->receiver_email,
                    'receiver_contact' => $this->receiver_contact,
                    'content' => $this->content,
                    'total_kg' => $this->total_kg,
                    'declared_value' => $this->declared_value,
                    'freight_order' => $this->freight_order,
                    'freight_order_status' => $this->freight_order_status,
                    'impressions' => $this->impressions,
                    'orders' => $this->orders,
                    'user' => $this->user,
                    'master' => $this->master,
                    'master_id' => $this->master_id,
                    'user_id' => $this->user_id,
                    'tag' => $this->tag,
                    'status' => $this->status,
                    'created_at' => $this->created_at,
                    'normalized_address' => $this->normalized_address,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'associated_withdrawal' => $this->associated_withdrawal,
                    'queue_id' => $this->queue_id,
                    'observation' => $this->observation,
                    'retry' => $this->retry,
                    'updated_at' => $this->updated_at,
                ]
            );
        }

        public static function update($instance)
        {
            global $wpdb;
            return (bool) $wpdb->update(
                $wpdb->prefix . "swastarkencl_issuance",
                [
                    'delivery_type' => $instance->delivery_type,
                    'payment_type' => $instance->payment_type,
                    'service_type' => $instance->service_type,
                    'checking_account' => $instance->checking_account,
                    'cost_center' => $instance->cost_center,
                    'value' => $instance->value,
                    'origin_agency_code' => $instance->origin_agency_code,
                    'destination_agency_code' => $instance->destination_agency_code,
                    'receiver_rut' => $instance->receiver_rut,
                    'receiver_names' => $instance->receiver_names,
                    'receiver_paternal' => $instance->receiver_paternal,
                    'receiver_maternal' => $instance->receiver_maternal,
                    'receiver_social_reason' => $instance->receiver_social_reason,
                    'receiver_address' => $instance->receiver_address,
                    'receiver_number' => $instance->receiver_number,
                    'receiver_department' => $instance->receiver_department,
                    'receiver_commune_code' => $instance->receiver_commune_code,
                    'receiver_phone' => $instance->receiver_phone,
                    'receiver_email' => $instance->receiver_email,
                    'receiver_contact' => $instance->receiver_contact,
                    'content' => $instance->content,
                    'total_kg' => $instance->total_kg,
                    'declared_value' => $instance->declared_value,
                    'freight_order' => $instance->freight_order,
                    'freight_order_status' => $instance->freight_order_status,
                    'impressions' => $instance->impressions,
                    'orders' => $instance->orders,
                    'user' => $instance->user,
                    'master' => $instance->master,
                    'master_id' => $instance->master_id,
                    'user_id' => $instance->user_id,
                    'tag' => $instance->tag,
                    'status' => $instance->status,
                    'created_at' => $instance->created_at,
                    'normalized_address' => $instance->normalized_address,
                    'latitude' => $instance->latitude,
                    'longitude' => $instance->longitude,
                    'associated_withdrawal' => $instance->associated_withdrawal,
                    'queue_id' => $instance->queue_id,
                    'observation' => $instance->observation,
                    'retry' => $instance->retry,
                    'updated_at' => $instance->updated_at,
                ],
                ['order_id' => $instance->order_id]
            );
        }

        public static function get_issuance_by_order_id($id)
        {
            global $wpdb;
            $issuance = null;

            if ((int) $id > 0) {
                $prepare = $wpdb->prepare(
                    "
                        SELECT * 
                        FROM " . $wpdb->prefix . "swastarkencl_issuance 
                        WHERE order_id = %d 
                        ORDER BY id DESC 
                        LIMIT 1 
                    ",
                    [
                        (int) $id,
                    ]
                );
                $results = $wpdb->get_results($prepare);
                if (count($results) > 0) {
                    $issuance = $results[0];
                }
            }
            
            return $issuance;
        }

        public static function does_order_have_an_issuance($id)
        {
            global $wpdb;
            $does_order_have_an_issuance = false;

            if ((int) $id > 0) {
                $prepare = $wpdb->prepare(
                    "
                        SELECT id 
                        FROM " . $wpdb->prefix . "swastarkencl_issuance 
                        WHERE order_id = %d 
                        ORDER BY id DESC 
                        LIMIT 1 
                    ",
                    [
                        (int) $id,
                    ]
                );
                $results = $wpdb->get_results($prepare);
                if (count($results) > 0) {
                    $does_order_have_an_issuance = (bool) $results[0]->id;
                }
            }
            
            return $does_order_have_an_issuance;
        }
    }
}