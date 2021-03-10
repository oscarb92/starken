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

if (!class_exists('SwastarkenclStarter')) {
    class SwastarkenclStarter
    {
        const CHANGE_AGENCY_JS_ACTION = 'swastarkencl_change_agency';
        const FETCH_AGENCIES_JS_ACTION = 'fetch_agencies';
        const FETCH_COST_CENTERS_JS_ACTION = 'fetch_cost_centers';
        const COMMUNE_AGENCIES_FROM_API_JS_ACTION = 'get_commune_agencies_from_api';
        const PACKAGE_TYPE_OF_ORDER = 'BULTO';
        const ENVELOPE_TYPE_OF_ORDER = 'SOBRE';
        const CACHE_TIME_IN_MINUTES = 15;

        public static function init()
        {
            self::register_activation();
            self::register_deactivation();
            self::add_zone();
            self::get_country_locale();
            self::new_fields_in_form('billing');
            self::new_fields_in_form('shipping');
            self::shipping_calculator_enable('country');
            self::shipping_calculator_enable('city');
            self::shipping_calculator_enable('state');
            self::shipping_calculator_enable('postcode');
            self::set_states();

            if (is_admin()) {
                self::load_admin_script();
                self::agencies_ajax_request();
                self::center_costs_ajax_request();
            }

            self::load_front_script();
            self::change_current_user_agency();
            self::fetch_commune_agencies();
            self::shipping_init();
            self::admin_action_generate_issuance();
            self::admin_display_issuance_details();
            self::remove_shipping_price_with_payment_on_arrival();
            self::add_starken_menu_items();
        }

        public static function register_activation()
        {
            register_activation_hook(SWASTARKENCL_PLUGIN_FILE, function() {
                SwastarkenclDatabaseSchemas::get_instance()->create_all_tables();
                SwastarkenclStarter::add_communes();
            });
        }

        public static function add_communes()
        {
            if (get_option('SWASTARKENCL_STATED_ADDED')) {
                return;
            }

            $plugin_settings = SwastarkenclSetting::get_instance();

            if (!empty($plugin_settings->get_user_token()) && !empty($plugin_settings->get_api_url())) {
                $curl = new Curl\Curl();
                $curl->setHeader('Content-Type', 'application/json');
                $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
                $curl->get($plugin_settings->get_api_url() . '/agency/comuna');

                if ($curl->error) {
                    self::get_logger_instance()->warning(
                        $plugin_settings->get_api_url() . '/agency/comuna :: ' . json_encode($curl->response)
                    );
                } else {
                    foreach ($curl->response as $commune) {
                        if (SwastarkenclState::commune_exists($commune->id) <= 0) {
                            $state = new SwastarkenclState();
                            $state->starken_id = $commune->id;
                            $state->code_dls = $commune->code_dls;
                            $state->name = $commune->name;
                            $state->starken_city_id = $commune->city->id;
                            $state->city = $commune->city->name;
                            $state->city_code_dls = $commune->city->code_dls;
                            $state->commune_code_dls = $commune->code_dls;
                            $state->add();
                        }
                    }
                    add_option('SWASTARKENCL_STATED_ADDED', true);
                }
            }
        }

        public static function register_deactivation()
        {
            register_deactivation_hook(SWASTARKENCL_PLUGIN_FILE, function() {
                // code
            });
        }

        public static function add_zone()
        {
            add_action('wp_loaded', function() {
                $available_zones = WC_Shipping_Zones::get_zones();
                $available_zones_names = array();
                foreach ($available_zones as $zone ) {
                    if( !in_array( $zone['zone_name'], $available_zones_names ) ) {
                        $available_zones_names[] = $zone['zone_name'];
                    }
                }

                // SwastarkenclStarter::pr($available_zones_names);
                if (!in_array('South America', $available_zones_names)) {
                    $new_zone_sa = new WC_Shipping_Zone();
                    // TODO: valdiate region's name
                    $new_zone_sa->set_zone_name('South America');
                    $new_zone_sa->add_location('SA', 'continent');
                    $new_zone_sa->save();
                    $new_zone_sa->add_shipping_method('swastarkencl');
                }
            });
        }

        public static function get_country_locale()
        {
            add_filter('woocommerce_get_country_locale', function($address_fields) {
                $address_fields['CL']['first_name']['priority'] = 1;
                $address_fields['CL']['last_name']['priority'] = 2;
                // $address_fields['CL']['rut']['priority'] = 3;
                $address_fields['CL']['address_1']['priority'] = 4;
                $address_fields['CL']['address_2']['priority'] = 5;
                // $address_fields['CL']['complement']['priority'] = 6;
                $address_fields['CL']['company']['priority'] = 7;
                $address_fields['CL']['country']['priority'] = 8;
                $address_fields['CL']['state']['priority'] = 9;
                $address_fields['CL']['city']['priority'] = 10;
                $address_fields['CL']['phone']['priority'] = 11;
                $address_fields['CL']['postcode']['priority'] = 12;

                $address_fields['CL']['country']['label'] = __('Country', 'swastarkencl');
                $address_fields['CL']['state']['label'] = __('Commune', 'swastarkencl');
                $address_fields['CL']['address_1']['label'] = __('Street', 'swastarkencl');
                $address_fields['CL']['address_2']['label'] = __('Number', 'swastarkencl');
                $address_fields['CL']['address_2']['required'] = true;

                $address_fields['CL']['address_1']['placeholder'] = __('Street\'s name', 'swastarkencl');
                $address_fields['CL']['address_2']['placeholder'] = __('Number', 'swastarkencl');
                
                // SwastarkenclStarter::pr($address_fields['CL']);
                
                return $address_fields;
            });
        }

        /**
         * @see hook woocommerce_checkout_fields
         */
        public static function new_fields_in_form($form)
        {
            add_filter('woocommerce_' . $form . '_fields', function($address_fields) use ($form) {
                
                // SwastarkenclStarter::pr($address_fields);

                $address_fields[$form . '_rut'] = [
                    'label' => __('RUT', 'swastarkencl'),
                    'placeholder' => __('RUT', 'swastarkencl'),
                    'class' => ['form-row-wide'],
                    'required' => true,
                    'priority' => 3,
                ];

                $address_fields[$form . '_complement'] = [
                    'label' => __('Complement', 'swastarkencl'),
                    'placeholder' => __('Department, Local, Office etc...', 'swastarkencl'),
                    'required' => true,
                    'class' => ['form-row-wide'],
                    'priority' => 6,
                ];

                if ($form == 'shipping') {
                    $address_fields[$form . '_phone'] = [
                        'label' => __('Phone', 'swastarkencl'),
                        'placeholder' => __('Phone', 'swastarkencl'),
                        'required' => true,
                        'type' => 'tel',
                        'class' => ['form-row-wide'],
                        'validate' => ['phone'],
                    ];
                }

                return $address_fields;
            });
        }

        public static function shipping_calculator_enable($field = 'state')
        {
            add_filter('woocommerce_shipping_calculator_enable_'.$field, function() {
                return true;
            });
        }

        public static function set_states()
        {
            add_filter('woocommerce_states', function($states) {
                $states['CL'] = SwastarkenclState::list();
                return $states;
            });
        }

        public static function load_admin_script()
        {
            add_action('admin_enqueue_scripts', function($hook) {
                if ('woocommerce_page_wc-settings' == $hook
                    && (isset($_GET['page']) && $_GET['page'] == 'wc-settings')
                    && (isset($_GET['tab']) && $_GET['tab'] == 'shipping')
                    && (isset($_GET['section']) && $_GET['section'] == 'swastarkencl')
                ) {
                    wp_enqueue_script(
                        "admin-swastarkencl-script",
                        plugins_url('/assets/js/', SWASTARKENCL_PLUGIN_FILE) . 'admin-swastarkencl.js',
                        ['jquery']
                    );

                    wp_localize_script(
                        'admin-swastarkencl-script',
                        'swastarkencl',
                        [
                            'url' => admin_url('admin-ajax.php'),
                            'change_origin_commune_action' => SwastarkenclStarter::FETCH_AGENCIES_JS_ACTION,
                            'change_checking_account_action' => SwastarkenclStarter::FETCH_COST_CENTERS_JS_ACTION,
                            'located_at_label' => __('Located at'),
                            'no_agencies_message' => __(
                                'There is no agencies in this commune, please, select another one'
                            ), 
                        ]
                    );
                }
            });
        }

        /**
         * @see ajax request in admin-swastarkencl.js file
         */
        public static function agencies_ajax_request()
        {
            add_action("wp_ajax_" . SwastarkenclStarter::FETCH_AGENCIES_JS_ACTION, function() {
                $plugin_settings = SwastarkenclSetting::get_instance();

                if (empty($plugin_settings->get_user_token()) || empty($plugin_settings->get_api_url()) ) {
                    echo json_encode([]);
                    exit;
                }

                if(!isset($_POST['commune_id'])){
                    echo json_encode([]);
                    exit;
                }

                $curl = new Curl\Curl();
                $curl->setHeader('Content-Type', 'application/json');
                $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
                $curl->get($plugin_settings->get_api_url() . '/agency/comuna/' . $_POST['commune_id']);

                if (!SwastarkenclStarter::validate_api_response($curl->response)) {
                    echo json_encode([]);
                    exit;
                }

                echo json_encode($curl->response);
                exit;
            });

            add_action("wp_ajax_nopriv_" . SwastarkenclStarter::FETCH_AGENCIES_JS_ACTION, function() {
                check_ajax_referer('ajax-login-nonce','security');
                exit;
            });
        }

        /**
         * @todo move this into a logger class
         */
        public static function validate_api_response($response)
        {
            $errorStatuses = [500, 400, 404];
            $plugin_settings = SwastarkenclSetting::get_instance();
            if (
                (isset($response->error) && $response->error)
                || (isset($response->status) && in_array($response->status, $errorStatuses))
            ) {
                if ($plugin_settings->get_enable_log()) {
                    self::get_logger_instance()->warning(json_encode($response));
                }
                return false;
            }
            return true;
        }

        /**
         * @see ajax request in admin-swastarkencl.js file
         */
        public static function center_costs_ajax_request()
        {
            add_action("wp_ajax_" . SwastarkenclStarter::FETCH_COST_CENTERS_JS_ACTION, function() {
                $plugin_settings = SwastarkenclSetting::get_instance();

                if (empty($plugin_settings->get_user_token()) || empty($plugin_settings->get_api_url())) {
                    echo json_encode([]);
                    exit;
                }

                if (!isset($_POST['ctacte_code'])) {
                    echo json_encode([]);
                    exit;
                }

                $curl = new Curl\Curl();
                $curl->setHeader('Content-Type', 'application/json');
                $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
                $curl->get($plugin_settings->get_api_url() . '/emision/credito-cliente/cc/' . $_POST['ctacte_code']);

                echo json_encode($curl->response);

                exit;
            });

            add_action("wp_ajax_nopriv_" . SwastarkenclStarter::FETCH_COST_CENTERS_JS_ACTION, function() {
                check_ajax_referer('ajax-login-nonce','security');
                exit;
            });
        }

        public static function load_front_script()
        {
            add_action('wp_enqueue_scripts', function () {
                wp_enqueue_script(
                    "front-swastarkencl-script",
                    plugins_url('/assets/js/', SWASTARKENCL_PLUGIN_FILE) . 'front-swastarkencl.js',
                    ['jquery']
                );

                
                wp_localize_script(
                    'front-swastarkencl-script',
                    'swastarkencl',
                    [
                        'url' => admin_url('admin-ajax.php'),
                        'change_agency_action' => SwastarkenclStarter::CHANGE_AGENCY_JS_ACTION,
                        'commune_agencies_from_api_action' => SwastarkenclStarter::COMMUNE_AGENCIES_FROM_API_JS_ACTION,
                        'nonce' => wp_create_nonce('ajax-nonce'),
                    ]
                );
            });
        }

        /**
         * @see ajax request in front-swastarkencl.js file
         */
        public static function change_current_user_agency()
        {
            add_action("wp_ajax_" . SwastarkenclStarter::CHANGE_AGENCY_JS_ACTION, function() {
                echo SwastarkenclStarter::save_changed_agency();
                exit;
            });

            add_action("wp_ajax_nopriv_" . SwastarkenclStarter::CHANGE_AGENCY_JS_ACTION, function() {
                echo SwastarkenclStarter::save_changed_agency();
                exit;
            });
        }

        public static function save_changed_agency()
        {
            if(isset($_POST['none'])){
                if (!wp_verify_nonce($_POST['none'], 'ajax-nonce')) {
                    // TODO: validate
                }
            }

            $customer_id = (int) WC()->customer->get_id();
            
            if ($customer_id <= 0 && empty($_POST['customer_rut'])) {
                return false;
            }

            if (!empty($_POST['customer_prev_rut'])) {
                SwastarkenclCustomerAgency::delete_by_customer_rut(
                    $_POST['customer_prev_rut']
                );
            }
            
            SwastarkenclCustomerAgency::delete_by_customer_rut(
                $_POST['customer_rut']
            );

            SwastarkenclCustomerAgency::delete_by_customer_id($customer_id);

            $swastarkenclcustomeragency = new SwastarkenclCustomerAgency();
            $swastarkenclcustomeragency->customer_id = $customer_id;
            // TODO: validate post fields, find wordpress function zanitation

            $customer_rut = isset($_POST['customer_rut'])?sanitize_text_field($_POST['customer_rut']):'';
            $swastarkenclcustomeragency->customer_rut = $customer_rut;

            $agencyId = isset($_POST['agency_id'])?(int)$_POST['agency_id']:'';
            $swastarkenclcustomeragency->agency_dls = $agencyId;

            $state_id = isset($_POST['state_id'])?(int)$_POST['state_id']:'';
            $swastarkenclcustomeragency->state_id = $state_id;
            

            return $swastarkenclcustomeragency->add();
        }

        /**
         * @see ajax request in front-swastarkencl.js file
         */
        public static function fetch_commune_agencies()
        {
            add_action("wp_ajax_" . SwastarkenclStarter::COMMUNE_AGENCIES_FROM_API_JS_ACTION, function() {
                echo SwastarkenclStarter::get_commune_agencies_from_api();
                exit;
            });

            add_action("wp_ajax_nopriv_" . SwastarkenclStarter::COMMUNE_AGENCIES_FROM_API_JS_ACTION, function() {
                echo SwastarkenclStarter::get_commune_agencies_from_api();
                exit;
            });
        }

        public static function get_commune_agencies_from_api()
        {
            if(isset($_POST['none'])){
                if (!wp_verify_nonce($_POST['none'], 'ajax-nonce')) {
                // TODO: validate
                }
            }

            $commune_id = (int) WC()->customer->get_shipping_state();

            if (isset($_POST['state_id']) && !empty($_POST['state_id'])) {
                $commune_id = $_POST['state_id'];
            }

            $plugin_settings = SwastarkenclSetting::get_instance();
            $agencies = [];
            if ($commune_id > 0) {
                $curl = new Curl\Curl();
                $curl->setHeader('Content-Type', 'application/json');
                $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
                $curl->get($plugin_settings->get_api_url() . '/agency/comuna/' . $commune_id);

                if(isset($curl->response->agencies)) {
                    $agencies = $curl->response->agencies;
                }
            }

            return json_encode($agencies);
        }

        public static function shipping_init()
        {
            add_action('woocommerce_shipping_init', function() {
                include(dirname(SWASTARKENCL_PLUGIN_FILE) . '/includes/Swastarkencl.php');
                SwastarkenclStarter::load_translations();
                SwastarkenclStarter::set_shipping_methods();
                SwastarkenclStarter::show_agencies_selectors();
            });
        }

        public static function admin_action_generate_issuance()
        {
            add_action(
                'woocommerce_order_status_changed',
                function($order_id, $from_status, $to_status, $order) {
                    if (SwastarkenclIssuance::does_order_have_an_issuance($order_id)) {
                        return;
                    }

                    $plugin_settings = SwastarkenclSetting::get_instance();
                    if (str_replace('wc-', '', $plugin_settings->get_order_state()) != $to_status) {
                        return;
                    }

                    if (empty($order->get_shipping_method())) {
                        $order->update_status(
                            $from_status,
                            __('The order does not have a shipping method', 'swastarkencl')
                        );
                        return;
                    }

                    $order_meta_data = SwastarkenclOrderMetaData::get_instance($order_id);
                    $carrier = SwastarkenclCarrier::get_carrier_by_name($order->get_shipping_method());
                    $data = [];
                    $delivery_dls = null;
                    $service_dsl = null;
                    $order_items = $order->get_items();
                    $weight = 0.0;
                    $height = 0.0;
                    $width = 0.0;
                    $length = 0.0;
                    $volume = 0.0;
                    $dimensions = [];
                    $declared_price = 0.0;
                    $products_names_as_description = [];
                    $only_one_product = true;
                    $tipo_encargo = SwastarkenclStarter::PACKAGE_TYPE_OF_ORDER;

                    foreach ($order_items as $item) {
                        $product = $item->get_product();
                        if ($item->get_quantity() > 1) {
                            $only_one_product = false;
                        }
                        $products_names_as_description[] = $product->get_name().' ('.$item->get_quantity().')';
                        $declared_price += $product->get_price() * $item->get_quantity();
                        $dimensions[] = $product->get_width();
                        $dimensions[] = $product->get_height();
                        $dimensions[] = $product->get_length();
                        $volume += (
                            $product->get_width() * $product->get_height() * $product->get_length()
                        ) * $item->get_quantity();
                        $weight += $product->get_weight() * $item->get_quantity();
                    }

                    if (count($order_items) > 1) {
                        $only_one_product = false;
                    }

                    if (!$only_one_product) {
                        $width = max($dimensions);
                        $height = sqrt(($volume/$width)*2/3);
                        $length = $volume / $width / $height;
                    } else {
                        $width = $dimensions[0];
                        $height = $dimensions[1];
                        $length = $dimensions[2];
                    }

                    if ($volume <= 2250 && $weight <= 0.3) {
                        $tipo_encargo = SwastarkenclStarter::ENVELOPE_TYPE_OF_ORDER;
                    }

                    // get delivery type
                    $curl = new Curl\Curl();
                    $curl->setHeader('Content-Type', 'application/json');
                    $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
                    $curl->get($plugin_settings->get_api_url() . '/emision/tipo-entrega/');

                    // TODO: validate response

                    foreach ($curl->response as $delivery_type) {
                        if (strtolower($delivery_type->nombre) == 'sucursal') {
                            $delivery_type->nombre = 'agencia';
                        }

                        if (strtolower($delivery_type->nombre) == strtolower($carrier['delivery_type'])) {
                            $delivery_dls = $delivery_type->codigo_dls;
                        }
                    }
                    // get delivery type

                    // get service type
                    $curl = new Curl\Curl();
                    $curl->setHeader('Content-Type', 'application/json');
                    $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
                    $curl->get($plugin_settings->get_api_url() . '/emision/tipo-servicio/');

                    // TODO: validate response

                    foreach ($curl->response as $service_type) {
                        if (strtolower($service_type->nombre) == 'expreso') {
                            $service_type->nombre = 'express';
                        }

                        if (strtolower($service_type->nombre) == strtolower($carrier['service_type'])) {
                            $service_dsl = $service_type->codigo_dls;
                        }
                    }
                    // get service type

                    $data = [
                        "codigo_agencia_origen" => $plugin_settings->get_origin_agency(),
                        // TODO: validate codigo_agencia_destino if it applies
                        // if not exists, change back order status
                        "codigo_agencia_destino" => SwastarkenclCustomerAgency::get_agency_dls_by_customer_rut(
                            $order_meta_data->_shipping_rut
                        ),
                        "destinatario_rut" => $order_meta_data->_shipping_rut,
                        "destinatario_nombres" => $order_meta_data->_shipping_first_name,
                        "destinatario_paterno" => $order_meta_data->_shipping_last_name,
                        "destinatario_telefono" => $order_meta_data->_shipping_phone,
                        "destinatario_email" => $order_meta_data->_shipping_email,
                        "destinatario_contacto" => $order_meta_data->_shipping_full_name,
                        "destinatario_direccion" => $order_meta_data->_shipping_address_1,
                        "destinatario_numeracion" => $order_meta_data->_shipping_address_2,
                        "destinatario_departamento" => $order_meta_data->_shipping_complement,
                        "destinatario_codigo_comuna" => SwastarkenclState::get_commune_dls_code_by_starken_commune_id(
                            $order_meta_data->_shipping_state
                        ),
                        "contenido" => '#'.$order_id,
                        // TODO: test this, test with multiple products and compare with PS module
                        "valor_declarado" => ((int) round($order->get_subtotal())),
                        "tipo_entrega" => [
                            "codigo_dls" => $delivery_dls,
                        ],
                        "tipo_pago" => [
                            "codigo_dls" => $carrier['payment_type'], 
                        ],
                        "tipo_servicio" => [
                            "codigo_dls" => $service_dsl,
                        ],
                        "encargos" => [
                            [
                                "descripcion" => implode(', ', $products_names_as_description),
                                "tipo_encargo" =>$tipo_encargo,
                                "kilos" => (float) number_format($weight, 2),
                                "alto" => (float) number_format($height, 2),
                                "ancho" => (float) number_format($width, 2),
                                "largo" => (float) number_format($length, 2)
                            ]
                        ]
                    ];

                    if(
                        !empty($plugin_settings->get_checking_account())
                        && $carrier['payment_type'] != SwastarkenclCarrier::ON_ARRIVAL_PAYMENT_TYPE
                    ) {
                        $data['cuenta_corriente'] = $plugin_settings->get_checking_account();
                    }

                    if(
                        !empty($plugin_settings->get_cost_center())
                        && $carrier['payment_type'] != SwastarkenclCarrier::ON_ARRIVAL_PAYMENT_TYPE
                    ) {
                        $data['centro_costo'] = $plugin_settings->get_cost_center();
                    }

                    if ($carrier['delivery_type'] == 'DOMICILIO') {
                        unset($data['codigo_agencia_destino']);
                    } else if(empty($data['codigo_agencia_destino'])) {
                        $order->update_status(
                            $from_status,
                            __('Destination agency code is not valid.', 'swastarkencl')
                        );
                        return;
                    }

                    $curl = new Curl\Curl();
                    $curl->setHeader('Content-Type', 'application/json');
                    $curl->setHeader('Cache-Control', 'no-cache');
                    $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
                    $curl->post($plugin_settings->get_api_url() . '/emision/emision', $data);
                    
                    if ($plugin_settings->get_enable_log()) {
                        self::get_logger_instance()->info(
                            $plugin_settings->get_api_url() . ' PARAMS :: /emision/emision :: ' . json_encode($data)
                        );
                        self::get_logger_instance()->info(
                            $plugin_settings->get_api_url() . ' RESPONSE :: /emision/emision :: ' . json_encode($curl->response)
                        );
                    }

                    if (isset($curl->response->status) && in_array($curl->response->status, [400])) {
                        $order->update_status($from_status, $curl->response->error);
                    }

                    $issuance = new SwastarkenclIssuance();
                    $issuance->order_id = $order_id;
                    $issuance->issuance_id = $curl->response->id;
                    $issuance->delivery_type = json_encode($curl->response->tipo_entrega);
                    $issuance->payment_type = json_encode($curl->response->tipo_pago);
                    $issuance->service_type = json_encode($curl->response->tipo_servicio);
                    $issuance->checking_account = $curl->response->cuenta_corriente;
                    $issuance->cost_center = (string)$curl->response->centro_costo;
                    $issuance->value = $curl->response->valor;
                    $issuance->origin_agency_code = $curl->response->codigo_agencia_origen;
                    $issuance->destination_agency_code = $curl->response->codigo_agencia_destino;
                    $issuance->receiver_rut = $curl->response->destinatario_rut;
                    $issuance->receiver_names = $curl->response->destinatario_nombres;
                    $issuance->receiver_paternal = $curl->response->destinatario_paterno;
                    $issuance->receiver_maternal = $curl->response->destinatario_materno;
                    $issuance->receiver_social_reason = $curl->response->destinatario_razon_social;
                    $issuance->receiver_address = $curl->response->destinatario_direccion;
                    $issuance->receiver_number = $curl->response->destinatario_numeracion;
                    $issuance->receiver_department = $curl->response->destinatario_departamento;
                    $issuance->receiver_commune_code = $curl->response->destinatario_codigo_comuna;
                    $issuance->receiver_phone = $curl->response->destinatario_telefono;
                    $issuance->receiver_email = $curl->response->destinatario_email;
                    $issuance->receiver_contact = $curl->response->destinatario_contacto;
                    $issuance->content = $curl->response->contenido;
                    $issuance->total_kg = $curl->response->kilos_total;
                    $issuance->declared_value = $curl->response->valor_declarado;
                    $issuance->freight_order = $curl->response->orden_flete;
                    $issuance->freight_order_status = $curl->response->estado;
                    $issuance->impressions = $curl->response->impresiones;
                    $issuance->orders = json_encode($curl->response->encargos);
                    $issuance->user = json_encode($curl->response->user);
                    $issuance->master = $curl->response->master ? $curl->response->master : '';
                    $issuance->master_id = $curl->response->master_id;
                    $issuance->user_id = $curl->response->user_id;
                    $issuance->tag = $curl->response->etiqueta;
                    $issuance->status = $curl->response->status;
                    $issuance->created_at = $curl->response->created_at;
                    $issuance->normalized_address = $curl->response->direccion_normalizada;
                    $issuance->latitude = $curl->response->latitud;
                    $issuance->longitude = $curl->response->longitud;
                    $issuance->associated_withdrawal = $curl->response->retiro_asociado;
                    $issuance->queue_id = $curl->response->queue_id;
                    $issuance->observation = $curl->response->observacion;
                    $issuance->retry = $curl->response->retry;
                    $issuance->updated_at = $curl->response->updated_at;
                    $issuance->add();
                },
                10,
                4
            );
        }

        // public static function get_commune_dls_code($commune, $plugin_settings)
        // {
        //     $curl = new Curl\Curl();
        //     $curl->setHeader('Content-Type', 'application/json');
        //     $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
        //     $curl->get($plugin_settings->get_api_url() . '/agency/comuna/' . $commune);
        //     SwastarkenclStarter::pr($curl->response->code_dls);
        // }

        public static function admin_display_issuance_details()
        {
            add_action('add_meta_boxes', function() {
                global $post;

                if (!SwastarkenclIssuance::does_order_have_an_issuance($post->ID)) {
                    return;
                }

                add_meta_box(
                    'swastarkencl_issuance_details',
                    __('Starken issuance'),
                    function () use ($post) {

                        $plugin_settings = SwastarkenclSetting::get_instance();
                        $issuance = SwastarkenclIssuance::get_issuance_by_order_id($post->ID);
                        $order = new WC_Order($post->ID);
                        $swastarkencl_tracking = null;

                        if ((int) $issuance->freight_order <= 0) {
                            $curl = new Curl\Curl();
                            $curl->setHeader('Content-Type', 'application/json');
                            $curl->setHeader('Cache-Control', 'no-cache');
                            $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
                            $curl->get($plugin_settings->get_api_url() . '/emision/consulta/' . $issuance->issuance_id);

                            // TODO: validate response

                            $issuance->cost_center = $curl->response->centro_costo;
                            $issuance->value = $curl->response->valor;
                            $issuance->normalized_address = $curl->response->direccion_normalizada;
                            $issuance->latitude = $curl->response->latitud;
                            $issuance->longitude = $curl->response->longitud;
                            $issuance->freight_order = $curl->response->orden_flete;
                            $issuance->associated_withdrawal = $curl->response->retiro_asociado;
                            $issuance->impressions = $curl->response->impresiones;
                            $issuance->master_id = $curl->response->master_id;
                            $issuance->status = $curl->response->status;
                            $issuance->retry = $curl->response->retry;
                            $issuance->queue_id = $curl->response->queue_id;
                            $issuance->freight_order_status = $curl->response->estado;
                            $issuance->tag = $curl->response->etiqueta;
                            $issuance->orders = $curl->response->observacion;
                            $issuance->created_at = $curl->response->created_at;
                            $issuance->updated_at = $curl->response->updated_at;
                            $issuance->encargos = json_encode($curl->response->encargos);
                            $issuance->user = json_encode(isset($curl->response->user) ? $curl->response->user : []);
                            SwastarkenclIssuance::update($issuance);
                        } else {
                            $curl = new Curl\Curl();
                            $curl->setHeader('Content-Type', 'application/json');
                            $curl->setHeader('Cache-Control', 'no-cache');
                            $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
                            $curl->get(
                                $plugin_settings->get_api_url() . '/tracking/orden-flete/of/' . $issuance->freight_order
                            );

                            // TODO: validate response

                            $swastarkencl_tracking = $curl->response;
                        }

                        $issuance->delivery_type = json_decode($issuance->delivery_type, true);
                        $issuance->payment_type = json_decode($issuance->payment_type, true);
                        $issuance->service_type = json_decode($issuance->service_type, true);

                        $issuance->coordinate = '';
                        if (!empty($issuance->latitude) && !empty($issuance->longitude)) {
                            $issuance->coordinate = $issuance->latitude . ', '.$issuance->longitude;
                        }

                        SwastarkenclStarter::change_agency_dls_code_by_its_name($issuance, $plugin_settings);

                        require_once(dirname(SWASTARKENCL_PLUGIN_FILE). '/templates/hooks/issuance.php');
                    },
                    'shop_order',
                    'normal',
                    'core'
                );
            });
        }

        public static function change_agency_dls_code_by_its_name(&$issuance, $plugin_settings)
        {
            $curl = new Curl\Curl();
            $curl->setHeader('Content-Type', 'application/json');
            $curl->setHeader('Cache-Control', 'no-cache');
            $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
            $curl->get($plugin_settings->get_api_url() . '/agency/agency');

            if (SwastarkenclStarter::validate_api_response($curl->response) && is_array($curl->response)) {
                foreach ($curl->response as $agency) {
                    if ($issuance->origin_agency_code == $agency->code_dls) {
                        $issuance->origin_agency_code = $agency->name;
                        $issuance->origin_agency_address = $agency->address;
                        break;
                    }
                }

                if ($issuance->destination_agency_code != 0) {
                    foreach ($curl->response as $agency) {
                        if ($issuance->destination_agency_code == $agency->code_dls) {
                            $issuance->destination_agency_code = $agency->name;
                            $issuance->destination_agency_address = $agency->address;
                            break;
                        }
                    }
                }
            }
        }

        public static function remove_shipping_price_with_payment_on_arrival()
        {
            /**
             * woocommerce_admin_order_item_headers hook is used to remove shipping price from
             * totals in the front cart details. This hook is called in the file 
             * /woocommerce/includes/class-wc-cart.php and was used 
             * because current cart is passed as a parameter allowing the use of 
             * WC_Cart::set_total()
             **/
            add_action('woocommerce_after_calculate_totals', function($cart) {
                $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');
                if (!isset($chosen_shipping_methods[0])) {
                    return;
                }
                
                $cart_totals = WC()->session->get('cart_totals');
                $shipping_types = explode('-', $chosen_shipping_methods[0]);

                if (isset($shipping_types[2]) && $shipping_types[2] == SwastarkenclCarrier::ON_ARRIVAL_PAYMENT_TYPE) {
                    $cart->set_total($cart->get_total(null) - $cart->get_shipping_total());
                }
            }, 20, 1);

            /**
             * woocommerce_admin_order_item_headers hook is used to remove shipping price from
             * totals in admin order details. This hook is called in the file 
             * /woocommerce/includes/admin/meta-boxes/views/html-order-items.php and was used 
             * because current order is passed as a parameter allowing the use of 
             * WC_Order::set_total()
             **/
            add_action('woocommerce_admin_order_item_headers', function($order) {
                $carrier = SwastarkenclCarrier::get_carrier_by_name($order->get_shipping_method());
                if ($carrier['payment_type'] == SwastarkenclCarrier::ON_ARRIVAL_PAYMENT_TYPE) {
                    if ((float)$order->get_total(null) > (float)$order->get_shipping_total(null)) {
                        $order->set_total($order->get_total(null)-$order->get_shipping_total(null));
                    }
                }
            });
        }

        public static function add_starken_menu_items()
        {
            add_action('admin_menu', function() {
                add_submenu_page(
                    'woocommerce',
                    __('Starken Carrier', 'swastarkencl'),
                    __('Starken Carrier', 'swastarkencl'),
                    'manage_woocommerce',
                    admin_url('admin.php?page=wc-settings&tab=shipping&section=swastarkencl')
                );
                add_submenu_page(
                    'woocommerce',
                    __('Logs', 'swastarkencl'),
                    __('Logs', 'swastarkencl'),
                    'manage_woocommerce',
                    admin_url('admin.php?page=wc-status&tab=logs')
                );
            }, 100);
        }

        public static function load_translations()
        {
            if ( function_exists( 'determine_locale' ) ) {
                $locale = determine_locale();
            } else {
                $locale = is_admin() ? get_user_locale() : get_locale();
            }

            $locale = apply_filters('plugin_locale', $locale, 'swastarkencl');

            unload_textdomain('swastarkencl');
            load_textdomain('swastarkencl', WP_LANG_DIR . '/swastarkencl/swastarkencl-' . $locale . '.mo');
            load_plugin_textdomain(
                'swastarkencl',
                false,
                plugin_basename(dirname(SWASTARKENCL_PLUGIN_FILE )) . '/i18n/languages'
            );
        }

        public static function set_shipping_methods()
        {
            add_filter('woocommerce_shipping_methods', function($methods) {
                $methods['swastarkencl'] = 'Swastarkencl';
                return $methods;
            });
        }

        public static function show_agencies_selectors()
        {
            // Could be woocommerce_review_order_before_payment too
            add_action('woocommerce_checkout_before_order_review_heading', function() {
                SwastarkenclStarter::agencies_selector_template('agencies-selector');
            });

            add_action('woocommerce_before_shipping_calculator', function() {
                SwastarkenclStarter::agencies_selector_template('simple-agencies-selector');
            });
        }

        public static function agencies_selector_template($template)
        {
            if ((int) WC()->customer->get_id() == 0 && $template == 'simple-agencies-selector') {
                return;
            }

            $commune_id = (int) WC()->customer->get_shipping_state();
            if ($commune_id >= 0) {
                $agencies = SwastarkenclStarter::get_agencies_from_api_by_commune_id($commune_id);
                $selected_agency = SwastarkenclCustomerAgency::get_agency_dls_by_customer_id((int) WC()->customer->get_id());
                require_once(dirname(SWASTARKENCL_PLUGIN_FILE). '/templates/hooks/' . $template . '.php');
            }
        }

        public static function get_agencies_from_api_by_commune_id($commune_id)
        {
            $plugin_settings = SwastarkenclSetting::get_instance();
            $agencies = [];
            if ($commune_id > 0) {
                $curl = new Curl\Curl();
                $curl->setHeader('Content-Type', 'application/json');
                $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
                $curl->get($plugin_settings->get_api_url() . '/agency/comuna/' . $commune_id);

                $httpCode = $curl->getHttpStatusCode();

                if ($httpCode == 404 || $httpCode == 204 || $curl->error) {
                    return;
                }

                if (SwastarkenclStarter::validate_api_response($curl->response)) {
                    $agencies = $curl->response->agencies;
                }
            }

            return $agencies;
        }

        public static function get_logger_instance()
        {
            return new WC_Logger();
        }

        public static function pr($data, $use_dump = false, $stop_execution = true)
        {
            echo '<pre>';
            if (!$use_dump) {
                print_r($data);
            } else {
                var_dump($data);
            }
            echo '</pre>';
            if ($stop_execution) {
                exit;
            }
        }
    }
}
