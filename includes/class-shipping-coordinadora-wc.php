<?php
/**
 * Created by PhpStorm.
 * User: smp
 * Date: 7/03/19
 * Time: 05:59 PM
 */

use Servientrega\WebService;

class Shipping_Coordinadora_WC extends WC_Shipping_Method_Shipping_Coordinadora_WC
{

    public $coordinadora;

    public function __construct($instance_id = 0)
    {
        parent::__construct($instance_id);

        $this->coordinadora = new WebService($this->apikey, $this->password_tracings, $this->nit, $this->id_client, $this->user, $this->password_guides);
        $this->coordinadora->sandbox_mode($this->isTest);
    }

    public function update_cities()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shipping_coordinadora_cities';
        $sql = "DELETE FROM $table_name";
        $wpdb->query($sql);

        try{
            $cities = $cities = WebService::Cotizador_ciudades();
            foreach ($cities->item as  $city){

                if ($city->estado == 'activo'){
                    $name = explode(' (', $city->nombre);
                    $name = ucfirst(mb_strtolower($name[0]));
                    $wpdb->insert(
                        $table_name,
                        array(
                            'nombre' => $name,
                            'codigo' => $city->codigo,
                            'nombre_departamento' => $city->nombre_departamento
                        )
                    );
                }
            }
        }catch (\Exception $exception){
            shipping_coordinadora_wc_cswc()->log($exception->getMessage());
        }

    }

    public static function test_connection_tracing()
    {

        $instance = new self();

        $cart_prods = array(
            'ubl'      => '0',
            'alto'     => '70',
            'ancho'    => '10',
            'largo'    => '50',
            'peso'     => '1',
            'unidades' => '1',
        );

        $params = array(
            'div'            => '',
            'cuenta'         => $instance->code_account,
            'producto'       => '0',
            'origen'         => "13001000",
            'destino'        => '25175000',
            'valoracion'     => '50000',
            'nivel_servicio' => array(0),
            'detalle'        => array(
                'item' => $cart_prods,
            )
        );

        try {
            $instance->coordinadora->Cotizador_cotizar($params);
        } catch ( \Exception $ex ) {
            shipping_coordinadora_wc_cswc_notices( $ex->getMessage() );
        }

    }

    public static function test_connection_guides()
    {

        $instance = new self();

        $cart_prods = array(
            'ubl' => '0',
            'alto' => '70',
            'ancho' => '10',
            'largo' => '20',
            'peso' => '1',
            'unidades' => '1',
            'referencia' => 'referencepacket',
            'nombre_empaque' => 'name packet'
        );

        $params = array(
            'codigo_remision' => "",
            'fecha' => date('Y-m-d'),
            'id_remitente' => '0',
            'nit_remitente' => '',
            'nombre_remitente' => 'My shop',
            'direccion_remitente' => 'calle 45 2-23',
            'telefono_remitente' => '3170044722',
            'ciudad_remitente' => '05001000',
            'nit_destinatario' => '0',
            'div_destinatario' => '0',
            'nombre_destinatario' => 'Pedro Perez',
            'direccion_destinatario' => 'calle 40 20-40',
            'ciudad_destinatario' => '05001000',
            'telefono_destinatario' => '3189023450',
            'valor_declarado' => '90000',
            'codigo_cuenta' => $instance->code_account, //change manageable
            'codigo_producto' => 0,
            'nivel_servicio' => 1,
            'linea' => '',
            'contenido' => 'nada',
            'referencia' => '',
            'observaciones' => '',
            'estado' => 'IMPRESO', //recomendado para la generación del pdf
            'detalle' => array(
                'item' => $cart_prods
            ),
            'cuenta_contable' => '',
            'centro_costos' => '',
            'recaudos' => array(),
            'margen_izquierdo' => '',
            'margen_superior' => '',
            'id_rotulo' => 0,
            'usuario_vmi' => '',
            'formato_impresion' => '',
            'atributo1_nombre' => '',
            'atributo1_valor' => '',
            'notificaciones' => array(
                'tipo_medio' => '1',
                'destino_notificacion' => 'example@gmail.com'
            ),
            'atributos_retorno' => array(
                'nit' => '',
                'div' => '',
                'nombre' => '',
                'direccion' => '',
                'codigo_ciudad' => '',
                'telefono' => ''
            ),
            'nro_doc_radicados' => '',
            'nro_sobre' => '',
        );

        try{
            $instance = new self();
            $instance->coordinadora->Guias_generarGuia($params);
        }
        catch (\Exception $exception){
            shipping_coordinadora_wc_cswc_notices( $exception->getMessage() );
        }

    }

    public static function cotizar($params)
    {
        $res = null;

        try{
            $instance = new self();
            $res = $instance->coordinadora->Cotizador_cotizar($params);
            return $res;
        }catch (\Exception $exception){
            shipping_coordinadora_wc_cswc()->log($exception->getMessage());
        }

        return $res;
    }

    public function generate_guide_dispath($order_id, $old_status, $new_status, $order)
    {

        $instance = new self();

        if( $order->has_shipping_method($instance->id) ){

            $codigo_remision = get_post_meta($order_id, 'codigos_remision_guides_coordinadora', true);

            if (empty($codigo_remision) && $new_status === 'processing'){

                $guides = $instance->generate_guide($order);

                if (!empty($guides))
                    shipping_coordinadora_wc_cswc()->log($guides);

                    //update_post_meta($order_id, 'codigos_remision_guides_coordinadora', $guides);

            }

        }
    }


    public function generate_guide($order)
    {

        $guide = '';
        $instance = new self();

        $direccion_remitente = get_option( 'woocommerce_store_address' ) .
            " " .  get_option( 'woocommerce_store_address_2' ) .
            " " . get_option( 'woocommerce_store_city' );

        $nombre_destinatario = $order->get_billing_first_name() ? $order->get_billing_first_name() .
            " " . $order->get_billing_last_name() : $order->get_shipping_first_name() .
            " " . $order->get_shipping_last_name();

        $direccion_destinatario = $order->get_billing_address_1() ? $order->get_billing_address_1() .
            " " . $order->get_billing_address_2() : $order->get_shipping_address_1() .
            " " . $order->get_shipping_address_2();

        $state = $order->get_billing_state() ? $order->get_billing_state() : $order->get_shipping_state();
        $city = $order->get_billing_city() ? $order->get_billing_city() : $order->get_shipping_city();

        $ciudad_destinatario = self::destination_code($state, $city);

        foreach ( $order->get_items() as $item ) {
            $_product = wc_get_product( $item['product_id'] );

            $products[] = array(
                'ubl'      => '0',
                'alto'     => $_product->get_height(),
                'ancho'    => $_product->get_width(),
                'largo'    => $_product->get_length(),
                'peso'     => $_product->get_weight(),
                'unidades' => $item['quantity'],
                'referencia' => !empty($_product->get_sku()) ? $_product->get_sku() : $_product->get_slug(),
                'nombre_empaque' => $_product->get_name()
            );


        }


        $params = array(
            'codigo_remision' => "",
            'fecha' => $this->dateCurrent(),
            'id_remitente' => '0',
            'nit_remitente' => '',
            'nombre_remitente' => get_bloginfo('name'),
            'direccion_remitente' => $direccion_remitente,
            'telefono_remitente' => '3170044722',
            'ciudad_remitente' => $instance->city_sender,
            'nit_destinatario' => '0',
            'div_destinatario' => '0',
            'nombre_destinatario' => $nombre_destinatario,
            'direccion_destinatario' => $direccion_destinatario,
            'ciudad_destinatario' => $ciudad_destinatario->codigo,
            'telefono_destinatario' => $order->get_billing_phone(),
            'valor_declarado' => (string)$order->get_total(),
            'codigo_cuenta' => $instance->code_account,
            'codigo_producto' => 0,
            'nivel_servicio' => 1,
            'linea' => '',
            'contenido' => $_product->get_name(),
            'referencia' => (string)$order->get_id(),
            'observaciones' => '',
            'estado' => 'IMPRESO', //recomendado para la generación del pdf
            'detalle' => array(
                'item' => $products
            ),
            'cuenta_contable' => '',
            'centro_costos' => '',
            'recaudos' => array(),
            'margen_izquierdo' => '',
            'margen_superior' => '',
            'id_rotulo' => 0,
            'usuario_vmi' => '',
            'formato_impresion' => '',
            'atributo1_nombre' => '',
            'atributo1_valor' => '',
            'notificaciones' => array(
                'tipo_medio' => '1',
                'destino_notificacion' => $order->get_billing_email()
            ),
            'atributos_retorno' => array(
                'nit' => '',
                'div' => '',
                'nombre' => '',
                'direccion' => '',
                'codigo_ciudad' => '',
                'telefono' => ''
            ),
            'nro_doc_radicados' => '',
            'nro_sobre' => '',
        );

        try{
            $data = $this->coordinadora->Guias_generarGuia($params);
            $guides[]  = $data->codigo_remision;
        }
        catch (\Exception $exception){
            shipping_coordinadora_wc_cswc()->log($exception->getMessage());

        }


        return $guide;
    }

    public static function destination_code($state, $city)
    {
        global $wpdb;
        $table_name        = $wpdb->prefix . 'shipping_coordinadora_cities';

        $countries_obj        = new WC_Countries();
        $country_states_array = $countries_obj->get_states();
        $state_name           = $country_states_array['CO'][ $state ];
        $state_name           = self::short_name_location($state_name);

        $query = "SELECT codigo FROM $table_name WHERE nombre_departamento='$state_name' AND nombre='$city'";

        $result = $wpdb->get_row( $query );

        return $result;

    }

    public static function short_name_location($name_location)
    {
        if ( 'Valle del Cauca' === $name_location )
            $name_location =  'Valle';
        return $name_location;
    }

    public function dateCurrent()
    {
        $dateCurrent = date('Y-m-d', current_time( 'timestamp' ));

        return $dateCurrent;
    }
}