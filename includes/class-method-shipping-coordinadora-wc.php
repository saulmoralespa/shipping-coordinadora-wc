    <?php
/**
 * @package ShippingCoordinadora
 *
 * Shipping Method for Coordinadora
 */
class WC_Shipping_Method_Shipping_Coordinadora_WC extends WC_Shipping_Method
{
    /**
     * Initializes the class variables
     *
     * @param integer $instance_id Instance ID of the class
     */
    public function __construct( $instance_id = 0 )
    {

        parent::__construct( $instance_id );

        $this->id                 = 'shipping_coordinadora_wc';
        $this->instance_id        = absint( $instance_id );
        $this->method_title       = __( 'Coordinadora' );  // Title shown in admin.
        $this->method_description = __( 'Coordinadora empresa transportadora de Colombia' ); // Description shown in admin.
        $this->title              = __( 'Coordinadora' );

        $this->init();

        $this->debug = $this->get_option( 'debug' );
        $this->isTest = (bool)$this->get_option( 'environment' );

        if ($this->isTest){
            $this->apikey = $this->get_option( 'sandbox_api_key' );
            $this->password_tracings = $this->get_option( 'sandbox_password_tracings' );
            $this->nit = $this->get_option( 'sandbox_nit' );

            $this->id_client = $this->get_option( 'sandbox_id_client' );
            $this->user = $this->get_option( 'sandbox_user' );
            $this->password_guides = $this->get_option('sandbox_password_guides');
        }else{
            $this->apikey = $this->get_option( 'api_key' );
            $this->password_tracings = $this->get_option( 'password_tracings' );
            $this->nit = $this->get_option( 'nit' );

            $this->id_client = $this->get_option( 'id_client' );
            $this->user = $this->get_option( 'user' );
            $this->password_guides = $this->get_option('password_guides');
        }

        $this->city_sender = $this->get_option('city_sender');

    }

    public function is_available($package)
    {
        return parent::is_available($package) &&
            !empty($this->apikey) &&
            !empty($this->nit) &&
            !empty($this->password_tracings);
    }

    /**
     * Init the class settings
     */
    public function init()
    {
        // Load the settings API.
        $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings.
        $this->init_settings(); // This is part of the settings API. Loads settings you previously init.
        // Save settings in admin if you have any defined.
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Init the form fields for this shipping method
     */
    public function init_form_fields(){
        $this->form_fields = include( dirname( __FILE__ ) . '/admin/settings.php' );
    }

    public function admin_options()
    {
        ?>
        <h3><?php echo $this->title; ?></h3>
        <p><?php echo $this->method_description; ?></p>
        <table class="form-table">
            <?php
            if (!empty($this->apikey) && !empty($this->nit) && !empty($this->password_tracings))
                Shipping_Coordinadora_WC::test_connection_tracing();
            if (!empty($this->id_client) && !empty($this->user) && !empty($this->password_guides))
                Shipping_Coordinadora_WC::test_connection_guides();
            $this->generate_settings_html();
            ?>
        </table>
        <?php
    }

    /**
     * Calculate the rates for this shipping method.
     *
     * @access public
     * @param mixed $package Array containing the cart packages. To see more about these packages see the 'calculate_shipping' method in this file: woocommerce/includes/class-wc-cart.php.
     */
    public function calculate_shipping( $package = array() ) {
        global $wpdb;
        global $woocommerce;
        $table_name        = $wpdb->prefix . 'shipping_coordinadora_cities';
        $state_destination = $package['destination']['state'];
        $city_destination  = $package['destination']['city'];
        $items             = $woocommerce->cart->get_cart();

        $cart_prods = array();

        foreach ( $items as $item => $values ) {
            $_product = wc_get_product( $values['data']->get_id() );

            if ( $_product->get_weight() && $_product->get_length()
                && $_product->get_width() && $_product->get_height() ) {
                $cart_prods[] = array(
                    'ubl'      => '0',
                    'alto'     => $_product->get_height(),
                    'ancho'    => $_product->get_width(),
                    'largo'    => $_product->get_length(),
                    'peso'     => $_product->get_weight(),
                    'unidades' => $values['quantity'],
                );
            } else {
                if ($this->debug === 'yes')
                shipping_coordinadora_wc_cswc()->log('All products have to have a weight, a width, a lenght, and a height, otherwise this shipping method can not generate a valid rate');
                break;
            }
        }

        $apply_cost = false;

        if ( ! empty( $cart_prods ) && 'CO' === $package['destination']['country']
            && $state_destination && $city_destination ) {

            $countries_obj        = new WC_Countries();
            $country_states_array = $countries_obj->get_states();
            $state_name           = $country_states_array['CO'][ $state_destination ];
            $state_name           = $this->short_name_location($state_name);


            $query = "SELECT codigo FROM $table_name WHERE nombre_departamento='$state_name' AND nombre='$city_destination'";

            $result_destination = $wpdb->get_row( $query );

            if ( ! empty( $result_destination ) ) {


                $params = array(
                    'div'            => '01',
                    'cuenta'         => '2',
                    'producto'       => '0',
                    'origen'         => $this->city_sender,
                    'destino'        => $result_destination->codigo,
                    'valoracion'     => WC()->cart->subtotal,
                    'nivel_servicio' => array( 0 ),
                    'detalle'        => array(
                        'item' => $cart_prods,
                    )
                );

                shipping_coordinadora_wc_cswc()->log($params);


                $data = Shipping_Coordinadora_WC::cotizar($params);

                if (isset($data)){
                    $apply_cost = true;
                    $rate       = array(
                        'id'      => $this->id,
                        'label'   => $this->title,
                        'cost'    => $data->flete_total,
                        'package' => $package,
                    );
                }
            }
        }

        if ( $apply_cost ) {
            $this->add_rate( $rate );
        } else {
            apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', false, $package, $this );
        }
    }

    public function short_name_location($name_location)
    {
        if ( 'Valle del Cauca' === $name_location )
            $name_location =  'Valle';
        return $name_location;
    }

}