<?php

function shipping_coordinadora_wc_init() {
    if ( ! class_exists( 'Shipping_Coordinadora_WC' ) ) {
        class Shipping_Coordinadora_WC extends WC_Shipping_Method
        {
            /**
             * Constructor for your shipping class
             *
             * @access public
             * @return void
             */
            public function __construct($instance_id = 0)
            {

                parent::__construct($instance_id);

                $this->id                 = 'shipping_coordinadora_wc';
                $this->instance_id				= absint( $instance_id );
                $this->method_title       = __( 'Coordinadora' );  // Title shown in admin
                $this->method_description = __( 'Coordinadora empresa transportadora de Colombia' ); // Description shown in admin
                $this->title              = __( 'Coordinadora');
                $this->init();

                $this->logger = new WC_Logger();
            }
            /**
             * Init your settings
             *
             * @access public
             * @return void
             */
            function init() {
                // Load the settings API
                $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
                $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
                // Save settings in admin if you have any defined
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }
            /**
             * calculate_shipping function.
             *
             * @access public
             * @param mixed $package
             * @return void
             */
            public function calculate_shipping( $package = array() )
            {
                global $wpdb;
                global $woocommerce;
                $table_name = $wpdb->prefix . 'shipping_coordinadora_cities';
                $state_destination = $package['destination']['state'];
                $city_destination = $package['destination']['city'];
                $items = $woocommerce->cart->get_cart();

                $cart_prods = array();

                foreach($items as $item => $values) {
                    $_product =  wc_get_product( $values['data']->get_id());

                    if ($_product->get_weight() && $_product->get_length()
                        && $_product->get_width() && $_product->get_height()){
                        $cart_prods[] = array('ubl' => '0',
                            'alto' => $_product->get_height(),
                            'ancho' => $_product->get_width(),
                            'largo' => $_product->get_length(),
                            'peso' => $_product->get_weight(),
                            'unidades' => $values['quantity']);
                    }else{
                        break;
                    }
                }

                $applyCost = false;

                if (!empty($cart_prods) && $package['destination']['country'] === 'CO'
                    && $state_destination && $city_destination){

                    $countries_obj = new WC_Countries();
                    $country_states_array = $countries_obj->get_states();
                    $state_name = $country_states_array['CO'][$state_destination];

                    $state_base = $countries_obj->get_base_state();
                    $city_local = $countries_obj->get_base_city();
                        $state_base_name = $country_states_array['CO'][$state_base];


                    if($state_name === 'Valle del Cauca')
                        $state_name = 'Valle';

                    if($state_base_name === 'Valle del Cauca')
                        $state_base_name = 'Valle';


                    $query = "SELECT codigo FROM $table_name WHERE nombre_departamento='$state_name' AND nombre='$city_destination'";
                    $result_destination = $wpdb->get_row($query);
                    $query = "SELECT codigo FROM $table_name WHERE nombre_departamento='$state_base_name' AND nombre='$city_local'";
                    $result_local = $wpdb->get_row($query);

                    if (!empty($result_destination) && !empty($result_local)){

                        $client = New SoapClient(shipping_coordinadora_wc_cswc()->tracing_url_coordinadora);


                        $this->logger->add('shipping-coordinadora', 'origen ' . $result_local->codigo);
                        $this->logger->add('shipping-coordinadora', 'destino ' . $result_destination->codigo);

                        $body = array(
                            'p' => array(
                                'nit' => "802001232",
                                'div' => "01",
                                'cuenta' => "2",
                                'producto' => "0",
                                'origen' => $result_local->codigo,
                                'destino' => $result_destination->codigo,
                                'valoracion' => WC()->cart->subtotal,
                                'nivel_servicio' => array(0),
                                'detalle' => array(
                                    'item' => $cart_prods
                                ),
                                'apikey' => '048e77c8-8171-11e8-adc0-fa7ae01bbebc',
                                'clave' => '502IPLF6p9QMDe'
                            )
                        );

                        try{
                            $data = $client->__call('Cotizador_cotizar', array($body));
                            $res = $data->Cotizador_cotizarResult;
                            $applyCost = true;
                            $rate = array(
                                'id' => $this->id,
                                'label' => $this->title,
                                'cost' => $res->flete_total,
                                'package' => $package
                            );
                        }catch (\Exception $ex){
                            $this->logger->add('shipping-coordinadora', $ex->getMessage(), true);
                        }

                    }
                }

                if ($applyCost){
                    $this->add_rate( $rate );
                }else{
                    apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );
                }
            }
        }
    }
}