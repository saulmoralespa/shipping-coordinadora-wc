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

        $cart_prods = array(
            'ubl'      => '0',
            'alto'     => '70',
            'ancho'    => '100',
            'largo'    => '50',
            'peso'     => '1',
            'unidades' => '1',
        );

        $params = array(
            'div'            => '01',
            'cuenta'         => '2',
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
            $instance = new self();
            $instance->coordinadora->Cotizador_cotizar($params);
        } catch ( \Exception $ex ) {
            shipping_coordinadora_wc_cswc_notices( $ex->getMessage() );
        }

    }

    public static function test_connection_guides()
    {
        $cart_prods = array(
            'ubl' => '0',
            'alto' => '70',
            'ancho' => '100',
            'largo' => '200',
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
            'codigo_cuenta' => 2,
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
            'margen_izquierdo' => 1.5,
            'margen_superior' => 1.5,
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
                'codigo_ciudad' => '05001000',
                'telefono' => '3170044722'
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
}