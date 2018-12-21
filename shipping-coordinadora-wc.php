<?php
/*
Plugin Name: Shipping Coordinadora Woocommerce
Description: Shipping Coordinadora Woocommerce is available for Colombia
Version: 1.0.3
Author: Saul Morales Pacheco
Author URI: https://saulmoralespa.com
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
WC tested up to: 3.5
WC requires at least: 2.6
*/

if (!defined( 'ABSPATH' )) exit;

if(!defined('SHIPPING_COORDINADORA_WC_CSWC_VERSION')){
    define('SHIPPING_COORDINADORA_WC_CSWC_VERSION', '1.0.3');
}

add_action('plugins_loaded','shipping_coordinadora_wc_cswc_init',0);

function shipping_coordinadora_wc_cswc_init(){
    if (!shipping_coordinadora_wc_cswc_requirements()){
        return;
    }

    shipping_coordinadora_wc_cswc()->run_coordinadora_wc();

    if(get_option('shipping_coordinadora_wc_cswc_redirect', false)){
        delete_option('shipping_coordinadora_wc_cswc_redirect');
        wp_redirect(admin_url('admin.php?page=coordinadora-install-setp'));
    }
}

function shipping_coordinadora_wc_cswc_notices($notice){
    ?>
    <div class="error notice">
        <p><?php echo $notice; ?></p>
    </div>
    <?php
}

function shipping_coordinadora_wc_cswc_requirements(){
    if ( version_compare( '5.6.0', PHP_VERSION, '>' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() {
                shipping_coordinadora_wc_cswc_notices('Coordinadora shipping Woocommerce: Requiere la versión de php 5.6 o superior');
            });
        }
        return false;
    }

    $openssl_warning = 'Coordinadora shipping Woocommerce:: Requiere la extensión OpenSSL 1.0.1 o superior se encuentre instalada';

    if ( ! defined( 'OPENSSL_VERSION_TEXT' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() use($openssl_warning) {
                shipping_coordinadora_wc_cswc_notices($openssl_warning);
            });
        }
        return false;
    }

    preg_match( '/^(?:Libre|Open)SSL ([\d.]+)/', OPENSSL_VERSION_TEXT, $matches );
    if ( empty( $matches[1] ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() use($openssl_warning) {
                shipping_coordinadora_wc_cswc_notices($openssl_warning);
            });
        }
        return false;
    }

    if ( ! version_compare( $matches[1], '1.0.1', '>=' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() use($openssl_warning) {
                shipping_coordinadora_wc_cswc_notices($openssl_warning);
            });
        }
        return false;
    }

    if ( !extension_loaded( 'soap' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() {
                shipping_coordinadora_wc_cswc_notices('Requiere la extensión soap se encuentre instalada');
            });
        }
        return false;
    }

    if ( !in_array(
        'woocommerce/woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() {
                shipping_coordinadora_wc_cswc_notices('Requiere que se encuentre instalado y activo el plugin: Woocommerce');
            });
        }
        return false;
    }

    if ( !in_array(
        'departamentos-y-ciudades-de-colombia-para-woocommerce/departamentos-y-ciudades-de-colombia-para-woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() {
                shipping_coordinadora_wc_cswc_notices('Requiere que se encuentre instalado y activo el plugin:
                 Departamentos y ciudades de Colombia para Woocommerce');
            });
        }
        return false;
    }

    $woo_countries = new WC_Countries();
    $default_country = $woo_countries->get_base_country();

    if (!in_array($default_country, array('CO'))){
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() {
                $country = 'Requiere que el país donde se encuentra ubicada la tienda sea Colombia '  .
                    sprintf('%s',  '<a href="' . admin_url() .
                        'admin.php?page=wc-settings&tab=general#s2id_woocommerce_currency">' .
                        'Click para establecer' . '</a>' );
                shipping_coordinadora_wc_cswc_notices($country);
            });
        }
        return false;
    }

    return true;
}

function shipping_coordinadora_wc_cswc(){
    static $plugin;
    if (!isset($plugin)){
        require_once('includes/class-shipping-coordinadora-wc-plugin.php');
        $plugin = new Shipping_Coordinadora_WC_Plugin(__FILE__, SHIPPING_COORDINADORA_WC_CSWC_VERSION);
    }
    return $plugin;
}

function activate_shipping_coordinadora_wc_cswc(){
    global $wpdb;

    $table_name = $wpdb->prefix . "shipping_coordinadora_cities";
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		nombre varchar(6  0) NOT NULL,
		codigo varchar(8) NOT NULL,
		nombre_departamento varchar(60) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    update_option('shipping_coordinadora_wc_cswc_version',SHIPPING_COORDINADORA_WC_CSWC_VERSION);
    add_option('shipping_coordinadora_wc_cswc_redirect', true);
    wp_schedule_event( time(), 'daily', 'shipping_coordinadora_wc_cswc' );
}

function deactivation_shipping_coordinadora_wc_cswc(){
    wp_clear_scheduled_hook( 'shipping_coordinadora_wc_cswc' );
}

register_activation_hook(__FILE__,'activate_shipping_coordinadora_wc_cswc');
register_deactivation_hook( __FILE__, 'deactivation_shipping_coordinadora_wc_cswc' );