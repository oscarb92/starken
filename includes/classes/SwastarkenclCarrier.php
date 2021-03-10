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

if (!class_exists('SwastarkenclCarrier')) {
    class SwastarkenclCarrier
    {
        const CHECKING_ACCOUNT_PAYMENT_TYPE = 2;
        const ON_ARRIVAL_PAYMENT_TYPE = 3;
        public static function get_carriers()
        {
            return [
                [
                    'name' => __('Starken Normal to agency service', 'swastarkencl'), 
                    'service_type' => 'NORMAL',
                    'delivery_type' => 'AGENCIA',
                    'payment_type' => SwastarkenclCarrier::CHECKING_ACCOUNT_PAYMENT_TYPE,
                ],
                [
                    'name' => __('Starken Normal to residence service', 'swastarkencl'), 
                    'service_type' => 'NORMAL',
                    'delivery_type' => 'DOMICILIO',
                    'payment_type' => SwastarkenclCarrier::CHECKING_ACCOUNT_PAYMENT_TYPE,
                ],
                [
                    'name' => __('Starken Express to agency service', 'swastarkencl'), 
                    'service_type' => 'EXPRESS',
                    'delivery_type' => 'AGENCIA',
                    'payment_type' => SwastarkenclCarrier::CHECKING_ACCOUNT_PAYMENT_TYPE,
                ],
                [
                    'name' => __('Starken Express to residence service', 'swastarkencl'), 
                    'service_type' => 'EXPRESS',
                    'delivery_type' => 'DOMICILIO',
                    'payment_type' => SwastarkenclCarrier::CHECKING_ACCOUNT_PAYMENT_TYPE,
                ],
                [
                    'name' => __('Starken Normal to agency service - Pay on arrival', 'swastarkencl'), 
                    'service_type' => 'NORMAL',
                    'delivery_type' => 'AGENCIA',
                    'payment_type' => SwastarkenclCarrier::ON_ARRIVAL_PAYMENT_TYPE,
                ],
                [
                    'name' => __('Starken Normal to residence service - Pay on arrival', 'swastarkencl'), 
                    'service_type' => 'NORMAL',
                    'delivery_type' => 'DOMICILIO',
                    'payment_type' => SwastarkenclCarrier::ON_ARRIVAL_PAYMENT_TYPE,
                ],
                [
                    'name' => __('Starken Express to agency service - Pay on arrival', 'swastarkencl'), 
                    'service_type' => 'EXPRESS',
                    'delivery_type' => 'AGENCIA',
                    'payment_type' => SwastarkenclCarrier::ON_ARRIVAL_PAYMENT_TYPE,
                ],
                [
                    'name' => __('Starken Express to residence service - Pay on arrival', 'swastarkencl'), 
                    'service_type' => 'EXPRESS',
                    'delivery_type' => 'DOMICILIO',
                    'payment_type' => SwastarkenclCarrier::ON_ARRIVAL_PAYMENT_TYPE,
                ],
            ];
        }

        public static function get_carrier_name_by_types($service, $delivery, $payment)
        {
            $carrier_name = '';
            foreach (self::get_carriers() as $carrier) {
                if (
                    $carrier['service_type'] == $service
                    && $carrier['delivery_type'] == $delivery
                    && $carrier['payment_type'] == $payment
                ) {
                    $carrier_name = $carrier['name'];
                    break;
                }
            }
            return $carrier_name;
        }

        public static function get_carrier_by_name($name)
        {
            $matched_carrier = null;
            foreach (self::get_carriers() as $carrier) {
                if ($carrier['name'] == $name) {
                    $matched_carrier = $carrier;
                    break;
                }
            }
            return $matched_carrier;
        }
    }
}