<?php
/*
Plugin Name: Coordinadora shipping Woocommerce
Description: Coordinadora shipping Woocommerce is available for Colombia
Version: 1.0.0
Author: Saul Morales Pacheco
Author URI: https://saulmoralespa.com
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
WC tested up to: 3.5
WC requires at least: 2.6
*/

if (!defined( 'ABSPATH' )) exit;

if(!defined('COORDINADORA_SHIPPING_WC_CSWC_VERSION')){
    define('COORDINADORA_SHIPPING_WC_CSWC_VERSION', '1.0.0');
}

add_action('plugins_loaded','coordinadora_shipping_wc_cswc_init',0);

function coordinadora_shipping_wc_cswc_init(){
    if (!coordinadora_shipping_wc_cswc_requirements()){
        return;
    }

    coordinadora_shipping_wc_cswc()->run_coordinadora_wc();
}

function coordinadora_shipping_wc_cswc_notices($notice){
    ?>
    <div class="error notice">
        <p><?php echo $notice; ?></p>
    </div>
    <?php
}

function coordinadora_shipping_wc_cswc_requirements(){
    if ( version_compare( '5.6.0', PHP_VERSION, '>' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() {
                coordinadora_shipping_wc_cswc_notices('Coordinadora shipping Woocommerce: Requiere la versión de php 5.6 o superior');
            });
        }
        return false;
    }

    $openssl_warning = 'Coordinadora shipping Woocommerce:: Requiere la extensión OpenSSL 1.0.1 o superior se encuentre instalada';

    if ( ! defined( 'OPENSSL_VERSION_TEXT' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() use($openssl_warning) {
                coordinadora_shipping_wc_cswc_notices($openssl_warning);
            });
            do_action('notices_coordinadora_shipping_wc_cswc', $openssl_warning);
        }
        return false;
    }

    preg_match( '/^(?:Libre|Open)SSL ([\d.]+)/', OPENSSL_VERSION_TEXT, $matches );
    if ( empty( $matches[1] ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() use($openssl_warning) {
                coordinadora_shipping_wc_cswc_notices($openssl_warning);
            });
        }
        return false;
    }

    if ( ! version_compare( $matches[1], '1.0.1', '>=' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() use($openssl_warning) {
                coordinadora_shipping_wc_cswc_notices($openssl_warning);
            });
        }
        return false;
    }

    if ( !extension_loaded( 'soap' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action('admin_notices', function() {
                coordinadora_shipping_wc_cswc_notices('Requiere la extensión soap se encuentre instalada');
            });
        }
        return false;
    }

    return true;
}

function coordinadora_shipping_wc_cswc(){
    static $plugin;
    if (!isset($plugin)){
        require_once('includes/class-coordinadora-shipping-wc-plugin.php');
        $plugin = new Coordinadora_Shipping_WC_Plugin(__FILE__, COORDINADORA_SHIPPING_WC_CSWC_VERSION);
    }
    return $plugin;
}