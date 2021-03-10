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

if (!class_exists('Swastarkencl') && class_exists('WC_Shipping_Method')) {
    class Swastarkencl extends WC_Shipping_Method
    {
        private $transients = [
            'starkencl-dimensions',
            'starkencl-rate-response-alternatives',
            'starkencl-rate-parameters',
        ];

        public function __construct()
        {
            $this->id = 'swastarkencl';
            $this->method_title = __('Starken Carrier', 'swastarkencl');
            $this->title = 'Starken';
            $this->method_description = __('Your shipments anywhere in Chile', 'swastarkencl');
            $this->enabled = "yes";
            $this->module_configuration();
        }

        public function module_configuration()
        {
            $countries = new WC_Countries();

            // foreach ($this->transients as $value) {
            //     delete_transient($value);
            // }

            $this->form_fields = [
                'api_url' => [
                    'title' => __('API URL', 'swastarkencl'),
                    'type' => 'url',
                    'description' => __('Enter the API URL to connect to Starken services', 'swastarkencl'),
                    'placeholder' => __('API URL', 'swastarkencl'),
                    'default' => 'https://api-desarrollo.starken-cloud.com/integracion',
                ],
                'user_token' => [
                    'title' => __('User token', 'swastarkencl'),
                    'type' => 'text',
                    'description' => __(
                        'Enter the user token to authenticate in the Starken services',
                        'swastarkencl'
                    ),
                    'placeholder' => __('User token', 'swastarkencl'),
                    'default' => '5b2fb88e-bffb-4fd1-a53b-093cf0cd43c6',
                ],
                'origin_commune' => [
                    'title' => __('Origin commune', 'swastarkencl'),
                    'description' => __('Set the origin commune to rate and generate issues', 'swastarkencl'),
                    'type' => 'select',
                    'options' => $countries->get_states('CL'),
                ],
                'origin_agency' => [
                    'title' => __('Origin agency', 'swastarkencl'),
                    'description' => __('Set the origin agency to generate issues', 'swastarkencl'),
                    'type' => 'select',
                    'options' => [],
                    'custom_attributes' => ['data-swastarkencl-origin-agency' => $this->get_option('origin_agency')],
                ],
                'disable_checking_accounts_usage' => [
                    'title' => __('Disable checking accounts', 'swastarkencl'),
                    'label' => __('Disable checking accounts', 'swastarkencl'),
                    'type' => 'checkbox',
                    'description' => __(
                        'Disable checking accounts and avoid to fetch ralated rates',
                        'swastarkencl'
                    ),
                ],
                'checking_account' => [
                    'title' => __('Select a checking account', 'swastarkencl'),
                    'description' => __('Select a checking account', 'swastarkencl'),
                    'type' => 'select',
                    'options' => $this->get_checking_accounts(),
                ],
                'rut' => [
                    'title' => __('RUT', 'swastarkencl'),
                    'type' => 'text',
                    'description' => __(
                        'Enter the RUT. It will be use to rate and generate issues',
                        'swastarkencl'
                    ),
                    'placeholder' => __('Enter a RUT: 00000000-k', 'swastarkencl'),
                ],
                'cost_center' => [
                    'title' => __('Cost Center', 'swastarkencl'),
                    'description' => __('Select the cost center', 'swastarkencl'),
                    'type' => 'select',
                    'options' => []
                ],
                'order_state' => [
                    'title' => __('Order state to generate an issue', 'swastarkencl'),
                    'description' => __('Set the order state from which the issue will be generated', 'swastarkencl'),
                    'type' => 'select',
                    'defaults' => 'wc-processing',
                    'options' => wc_get_order_statuses(),
                ],
                'hide_shipping_with_0_cost' => [
                    'title' => __('Hide shipping options without cost (0.00)', 'swastarkencl'),
                    'label' => __('Hide shipping options without cost', 'swastarkencl'),
                    'type' => 'checkbox',
                    'description' => __(
                        'Hide shipping options without cost (0.00) from API',
                        'swastarkencl'
                    ),
                ],
                'enable_log' => [
                    'title' => __('Logs', 'swastarkencl'),
                    'label' => __('Enable log', 'swastarkencl'),
                    'type' => 'checkbox',
                    'description' => __(
                        'This option is useful to fix API response problems',
                        'swastarkencl'
                    ),
                ],
            ];

            $this->init_settings();

            add_action('woocommerce_update_options_shipping_' . $this->id, [&$this, 'process_admin_options']);
        }

        /**
         * Generate Select HTML.
         *
         * @see override the WC_Settings_API::generate_select_html($key, $data)
         */
        public function generate_select_html( $key, $data ) {
            if (!in_array($key, ['checking_account'])) {
                return parent::generate_select_html($key, $data);
            }

            $field_key = $this->get_field_key($key);
            $defaults  = [
                'title'             => '',
                'disabled'          => false,
                'class'             => '',
                'css'               => '',
                'placeholder'       => '',
                'type'              => 'text',
                'desc_tip'          => false,
                'description'       => '',
                'custom_attributes' => [],
                'options'           => [],
            ];

            $data = wp_parse_args( $data, $defaults );
            
            $html = '<tr valign="top">
                <th scope="row" class="titledesc">
                    <label
                        for="' . esc_attr($field_key) . '">
                        ' . wp_kses_post($data['title']) . '
                        ' . $this->get_tooltip_html($data) . '
                    </label>
                </th>
                <td class="forminp">
                    <fieldset>
                        <legend class="screen-reader-text">
                            <span>' . wp_kses_post($data['title']) . '</span>
                        </legend>
                        ' . $this->generate_select_and_options_tag($key, $data) . ' 
                        ' . $this->get_description_html($data) . '
                    </fieldset>
                </td>
            </tr>';

            return $html;
        }

        private function generate_select_and_options_tag($key, $data)
        {
            $field_key = $this->get_field_key( $key );
            $selected = '';

            $html = '
            <select
                class="select ' . esc_attr($data['class']) . '"
                name="' . esc_attr($field_key) . '"
                id="' . esc_attr($field_key) . '"
                style="' . esc_attr($data['css']) . '"
                ' . disabled($data['disabled'], true) . '
                ' . $this->get_custom_attribute_html($data) . '>';
            foreach ((array) $data['options'] as $option_key => $option_value):
                if ($option_key == $this->get_option($key)) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }

                $html .= '
                <option
                    ' . $option_value['custom_attributes'] . '
                    value="' . esc_attr($option_key) . '" 
                    ' . $selected . '>

                    ' . esc_html($option_value['name']) . '
                </option>';
            endforeach;

            $html .= '</select>';

            return $html;
        }

        private function get_checking_accounts()
        {
            $checkingAccounts = [];

            foreach ($this->get_checking_accounts_from_api() as $checkingAccount) {
                $checkingAccounts[(string)$checkingAccount->codigo] = [
                    'name' => $checkingAccount->codigo,
                    'custom_attributes' => 'data-rut="'. $checkingAccount->rut . '-' . $checkingAccount->dv .'"'
                ];
            }

            return $checkingAccounts;
        }

        private function get_checking_accounts_from_api()
        {
            $plugin_settings = SwastarkenclSetting::get_instance();

            if ( empty($plugin_settings->get_user_token()) || empty($plugin_settings->get_api_url())) {
                return [];
            }

            $curl = new Curl\Curl();
            $curl->setHeader('Content-Type', 'application/json');
            $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
            $curl->get($plugin_settings->get_api_url() . '/emision/credito-cliente/ctacte');

            if (!SwastarkenclStarter::validate_api_response($curl->response)) {
                return [];
            }

            if (is_array($curl->response)) {
                return $curl->response;
            }

            $responseAsArrayFromJson = json_decode($curl->response, true);

            if (is_array($responseAsArrayFromJson)) {
                return $responseAsArrayFromJson;
            }

            return [];
        }

        public function calculate_shipping($package = [])
        {
            $weight = 0.0;
            $height = 0.0;
            $width = 0.0;
            $depth = 0.0;
            $price = 0.0;
            $cartProductsPrice = 0.0;
            $volume = 0.0;
            $dimensions = [];
            $onlyOneProduct = true;
            $productIds = [];
            $package = SwastarkenclStarter::PACKAGE_TYPE_OF_ORDER;
            $plugin_settings = SwastarkenclSetting::get_instance();

            foreach (WC()->cart->get_cart() as $cartItem) {
                if ($cartItem['quantity'] > 1) {
                    $onlyOneProduct = false;
                }
                $dimensions[] = (float) $cartItem['data']->get_width();
                $dimensions[] = (float) $cartItem['data']->get_height();
                $dimensions[] = (float) $cartItem['data']->get_length();
                $volume += (
                    (float) $cartItem['data']->get_width() * (float) $cartItem['data']->get_height() * (float) $cartItem['data']->get_length()
                ) * $cartItem['quantity'];
                $weight += $cartItem['data']->get_weight() * $cartItem['quantity'];

                $productIds[] = $cartItem['product_id'];
            }

            if ($volume <= 2250 && $weight <= 0.3) {
                $package = SwastarkenclStarter::ENVELOPE_TYPE_OF_ORDER;
            }

            if (count($productIds) > 1) {
                $onlyOneProduct = false;
            }

            $width = max($dimensions);
            $height = sqrt(($volume/$width)*2/3);
            $depth = $volume / $width / $height;

            if (!$onlyOneProduct) {
                $width = max($dimensions);
                $height = sqrt(($volume/$width)*2/3);
                $depth = $volume / $width / $height;
            } else {
                $width = $dimensions[0];
                $height = $dimensions[1];
                $depth = $dimensions[2];
            }

            if ($width <= 0 || $height <= 0 || $depth <= 0 || $weight <= 0) {
                if ($plugin_settings->get_enable_log() && get_transient($this->transients[0]) === false) {
                    SwastarkenclStarter::get_logger_instance()->warning(
                        sprintf(
                            __('Invalid dimensions: width %f, height %f, depth %f, weight %f', 'swastarkencl'),
                            $width,
                            $height,
                            $depth,
                            $weight
                        )
                    );
                    set_transient(
                        $this->transients[0],
                        json_encode(['width' => $width, 'height' => $height, 'weight' => $weight]),
                        MINUTE_IN_SECONDS * SwastarkenclStarter::CACHE_TIME_IN_MINUTES
                    );
                }
                return;
            }

            $rut = explode('-', $plugin_settings->get_rut());
            $data = [
                'origen' => SwastarkenclState::get_city_dls_code_by_starken_commune_id(
                    $plugin_settings->get_origin_commune()
                ),
                'destino' => SwastarkenclState::get_city_dls_code_by_starken_commune_id(
                    WC()->customer->get_shipping_state()
                ),
                // 'run' => $rut[0],
                'bulto' => $package,
                'alto' => (float) number_format($height, 2),
                'ancho' => (float) number_format($width, 2),
                'largo' => (float) number_format($depth, 2),
                'kilos' => (float) number_format($weight, 2),
                // 'precio' => (float) number_format($cartProductsPrice, 2),
                'todas_alternativas' => true,
            ];

            if ($plugin_settings->get_disable_checking_accounts_usage() == 'no' && isset($rut[1])) {
                $data['ctacte'] = $plugin_settings->get_checking_account();
                $data['ctacte_dv'] = $rut[1];
            }

            $shipping_options = $this->get_shipping_options_from_api($data, $plugin_settings);
            $this->show_shipping_options($shipping_options, $plugin_settings);
            
        }

        private function get_shipping_options_from_api($data, $plugin_settings)
        {
            $rate_response_alternatives = get_transient($this->transients[1]);
            $rate_parameters = get_transient($this->transients[2]);

            if ($rate_response_alternatives !== false && json_encode($data) == $rate_parameters) {
                return json_decode($rate_response_alternatives);
            }

            $curl = new Curl\Curl();
            $curl->setHeader('Content-Type', 'application/json');
            $curl->setHeader('Cache-Control', 'no-cache');
            $curl->setHeader('Authorization', 'Bearer ' . $plugin_settings->get_user_token());
            $curl->post($plugin_settings->get_api_url() . '/quote/cotizador-multiple', json_encode($data));

            $httpCode = $curl->getHttpStatusCode();

            if ($plugin_settings->get_enable_log()) {
                SwastarkenclStarter::get_logger_instance()->info(
                    $plugin_settings->get_api_url() . ' RESPONSE :: /quote/cotizador-multiple :: ' . json_encode($curl->response)
                );
            }

            if ($httpCode == 404 || $httpCode == 204 || $curl->error) {
                return null;
            }

            if (SwastarkenclStarter::validate_api_response($curl->response)) {
                if (get_transient($this->transients[1]) === false) {
                    set_transient(
                        $this->transients[1],
                        json_encode($curl->response->alternativas),
                        MINUTE_IN_SECONDS * SwastarkenclStarter::CACHE_TIME_IN_MINUTES
                    );
                    set_transient(
                        $this->transients[2],
                        json_encode($data),
                        MINUTE_IN_SECONDS * SwastarkenclStarter::CACHE_TIME_IN_MINUTES
                    );
                }
                return $curl->response->alternativas;
            }
            
            return null;
        }

        private function show_shipping_options($shipping_options, $plugin_settings)
        {
            if ($shipping_options != null) {
                foreach ($shipping_options as $shipping_option) {
                    if ($plugin_settings->get_hide_shipping_with_no_cost() == 'yes' && (float) $shipping_option->precio <= 0 ) {
                        continue;
                    }
                    $this->add_rate([
                        'id' => $shipping_option->servicio . '-' . $shipping_option->entrega . '-' . $shipping_option->codigo_tipo_pago,
                        'label' => SwastarkenclCarrier::get_carrier_name_by_types(
                            $shipping_option->servicio == 'EXPRESO' ? 'EXPRESS' : $shipping_option->servicio,
                            $shipping_option->entrega == 'SUCURSAL' ? 'AGENCIA' : $shipping_option->entrega,
                            $shipping_option->codigo_tipo_pago
                        ),
                        'cost' => $shipping_option->precio,
                        'calc_tax' => 'per_item'
                    ]);
                }
            }
        }
    }
}