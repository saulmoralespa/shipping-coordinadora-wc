<?php

class Coordinadora_Shipping_WC extends WC_Shipping_Method
{
    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->id = 'coordinadora_shipping_wc';
        $this->instance_id = absint( $instance_id );
        $this->method_title = __( 'Coordinadora');
        $this->method_description = __( 'Coordinadora método de envio');

        $this->supports = array(
            'settings',
            'shipping-zones',
            'instance-settings'
        );

        $this->init();
    }

    function init(){
        $this->form_fields = $this->define_instance_form_fields();

        $this->init_form_fields();
        $this->init_settings();

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function define_instance_form_fields()
    {
        return include 'settings.php';
    }
}