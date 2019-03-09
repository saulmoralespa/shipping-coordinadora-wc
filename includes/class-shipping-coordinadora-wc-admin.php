<?php

class Shipping_Coordinadora_WC_Admin
{
    public function __construct()
    {
        add_action( 'admin_menu', array($this, 'shipping_coordinadora_wc_cswc_menu'));
        add_action( 'wp_ajax_shipping_coordinadora_wc_cswc',array($this,'shipping_coordinadora_wc_cswc_ajax'));
    }

    public function shipping_coordinadora_wc_cswc_menu()
    {
        add_submenu_page(
            null,
            '',
            '',
            'manage_options',
            'coordinadora-install-setp',
            array($this, 'coordinadora_install_step')
        );

        add_action( 'admin_footer', array( $this, 'enqueue_scripts_admin' ) );
    }

    public function coordinadora_install_step()
    {
        ?>
        <div class="wrap about-wrap">
            <h3><?php _e( 'Actualicemos y estaremos listos para iniciar :)' ); ?></h3>
            <button class="button-primary shipping_coordinadora_update_cities" type="button">Actualizar</button>
        </div>
        <?php
    }

    public function shipping_coordinadora_wc_cswc_ajax()
    {
        do_action('shipping_coordinadora_wc_cswc_schedule');
        die();
    }

    public function enqueue_scripts_admin()
    {
        wp_enqueue_script('sweetalert', 'https://cdn.jsdelivr.net/npm/sweetalert2@7.29.0/dist/sweetalert2.all.min.js', array('jquery'), false, true );
        wp_enqueue_script( 'shipping_coordinadora_wc_cswc', shipping_coordinadora_wc_cswc()->plugin_url . 'assets/js/config.js', array( 'jquery' ), shipping_coordinadora_wc_cswc()->version, true );
        wp_localize_script( 'shipping_coordinadora_wc_cswc', 'shippingCoordinadora', array(
            'urlConfig' => admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipping_coordinadora_wc')
        ) );
    }
}