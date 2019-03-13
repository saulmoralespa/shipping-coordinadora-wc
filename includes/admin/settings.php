<?php

wc_enqueue_js( "
    jQuery( function( $ ) {
	
	let shipping_coordinadora_live_tracing_fields = '#woocommerce_shipping_coordinadora_wc_api_key, #woocommerce_shipping_coordinadora_wc_password_tracing, #woocommerce_shipping_coordinadora_wc_nit';
	let shipping_coordinadora_live_guides_fields = '#woocommerce_shipping_coordinadora_wc_id_client, #woocommerce_shipping_coordinadora_wc_user, #woocommerce_shipping_coordinadora_wc_password_guides, #woocommerce_shipping_coordinadora_wc_code_account';
	
	let shipping_coordinadora_sandbox_tracing_fields = '#woocommerce_shipping_coordinadora_wc_sandbox_api_key, #woocommerce_shipping_coordinadora_wc_sandbox_password_tracings, #woocommerce_shipping_coordinadora_wc_sandbox_nit';
	let shipping_coordinadora_sandbox_guides_fields = '#woocommerce_shipping_coordinadora_wc_sandbox_id_client, #woocommerce_shipping_coordinadora_wc_sandbox_user, #woocommerce_shipping_coordinadora_wc_sandbox_password_guides, #woocommerce_shipping_coordinadora_wc_sandbox_code_account';

	$( '#woocommerce_shipping_coordinadora_wc_environment' ).change(function(){

		$( shipping_coordinadora_sandbox_tracing_fields + ',' + shipping_coordinadora_live_tracing_fields ).closest( 'tr' ).hide();
		$( shipping_coordinadora_sandbox_guides_fields + ',' + shipping_coordinadora_live_guides_fields ).closest( 'tr' ).hide();

		if ( '0' === $( this ).val() ) {
		    $( '#woocommerce_shipping_coordinadora_wc_dispatches, #woocommerce_shipping_coordinadora_wc_dispatches + p' ).show();
		    $( '#woocommerce_shipping_coordinadora_wc_guides, #woocommerce_shipping_coordinadora_wc_guides + p' ).show();
			$( '#woocommerce_shipping_coordinadora_wc_sandbox_dispatches, #woocommerce_shipping_coordinadora_wc_sandbox_dispatches + p' ).hide();
			$( '#woocommerce_shipping_coordinadora_wc_sandbox_guides, #woocommerce_shipping_coordinadora_wc_sandbox_guides + p' ).hide();
			$( shipping_coordinadora_live_tracing_fields ).closest( 'tr' ).show();
			$( shipping_coordinadora_live_guides_fields ).closest( 'tr' ).show();
			
		}else{
		   $( '#woocommerce_shipping_coordinadora_wc_dispatches, #woocommerce_shipping_coordinadora_wc_dispatches + p' ).hide();
		   $( '#woocommerce_shipping_coordinadora_wc_guides, #woocommerce_shipping_coordinadora_wc_guides + p' ).hide();
		   $( '#woocommerce_shipping_coordinadora_wc_sandbox_dispatches, #woocommerce_shipping_coordinadora_wc_sandbox_dispatches + p' ).show();
		   $( '#woocommerce_shipping_coordinadora_wc_sandbox_guides, #woocommerce_shipping_coordinadora_wc_sandbox_guides + p' ).show();
		   $( shipping_coordinadora_sandbox_tracing_fields ).closest( 'tr' ).show();
		   $( shipping_coordinadora_sandbox_guides_fields ).closest( 'tr' ).show();
		}
	}).change();
});	
");

global $wpdb;
$table_name = $wpdb->prefix . 'shipping_coordinadora_cities';

$query = "SELECT * FROM $table_name";

$cities = $wpdb->get_results(
    $query
);

$sending_cities = array();

if (!empty($cities)){

    foreach ($cities as $city){
        $sending_cities[$city->codigo] = "$city->nombre, $city->nombre_departamento";
    }
}


$cities_not_loaded = '<a href="' . esc_url(admin_url( 'admin.php?page=coordinadora-install-setp' )) . '">' . __( 'Para cargar las ciudades, de clic aquí') . '</a>';

if (empty($sending_cities)){
    $sending_cities_select = array(
        'shipping_cities_not_select' => array(
            'title'       => __( 'Las ciudades no estan cargadas!!!'),
            'type'        => 'title',
            'description' => $cities_not_loaded,
        )
    );
}else{
    $sending_cities_select = array(
        'city_sender' => array(
            'title' => __('Ciudad del remitente (donde se encuentra ubica la tienda)'),
            'type'        => 'select',
            'class'       => 'wc-enhanced-select',
            'description' => __('Se recomienda selecionar ciudadades centrales'),
            'desc_tip' => true,
            'default' => true,
            'options'     => $sending_cities
        )
    );
}


return array_merge(
    array(
        'enabled' => array(
            'title' => __('Activar/Desactivar'),
            'type' => 'checkbox',
            'label' => __('Activar  Coordinadora'),
            'default' => 'no'
        ),
        'title'        => array(
            'title'       => __( 'Título método de envío' ),
            'type'        => 'text',
            'description' => __( 'Esto controla el título que el usuario ve durante el pago' ),
            'default'     => __( 'Coordinadora versión básica' ),
            'desc_tip'    => true,
        ),
        'debug'        => array(
            'title'       => __( 'Depurador' ),
            'label'       => __( 'Habilitar el modo de desarrollador' ),
            'type'        => 'checkbox',
            'default'     => 'no',
            'description' => __( 'Enable debug mode to show debugging information on your cart/checkout.' ),
            'desc_tip' => true,
        ),
        'environment' => array(
            'title' => __('Enntorno'),
            'type'        => 'select',
            'class'       => 'wc-enhanced-select',
            'description' => __('Entorno de pruebas o producción'),
            'desc_tip' => true,
            'default' => true,
            'options'     => array(
                false    => __( 'Producción'),
                true => __( 'Pruebas'),
            ),
        )
    ),
    $sending_cities_select,
    array(
        'dispatches'          => array(
            'title'       => __( 'Seguimiento de despachos' ),
            'type'        => 'title',
            'description' => __( 'Apikey, contraseña y el NIT asociado para el entorno de producción' ),
        ),

        'api_key'      => array(
            'title' => __( 'API Key' ),
            'type'  => 'text',
            'description' => __( 'Api key provisto por Coordinadora' ),
            'desc_tip' => true,
        ),

        'password_tracing' => array(
            'title' => __( 'Contraseña' ),
            'type'  => 'text',
            'description' => __( 'La clave del webservice para seguimiento de envios' ),
            'desc_tip' => true,
        ),

        'nit' => array(
            'title' => __( 'NIT' ),
            'type'  => 'text',
            'description' => __( 'Nit asociado a un acuerdo Coordinadora Mercantil' ),
            'desc_tip' => true,
        ),
        'sandbox_dispatches'          => array(
            'title'       => __( 'Seguimiento de despachos (pruebas)' ),
            'type'        => 'title',
            'description' => __( 'Apikey, contraseña y el NIT asociado para el entorno de pruebas' ),
        ),
        'sandbox_api_key'      => array(
            'title' => __( 'API Key' ),
            'type'  => 'text',
            'description' => __( 'Api key provisto por Coordinadora' ),
            'desc_tip' => true,
        ),

        'sandbox_password_tracings' => array(
            'title' => __( 'Contraseña' ),
            'type'  => 'text',
            'description' => __( 'La clave del webservice para seguimiento de envios' ),
            'desc_tip' => true,
        ),

        'sandbox_nit' => array(
            'title' => __( 'NIT' ),
            'type'  => 'text',
            'description' => __( 'Nit asociado a un acuerdo Coordinadora Mercantil' ),
            'desc_tip' => true,
        ),
        'guides'          => array(
            'title'       => __( 'Generación de guias' ),
            'type'        => 'title',
            'description' => __( 'id_cliente, usuario y contraseña para el entorno de producción' ),
        ),
        'id_client' => array(
            'title' => __( 'id_cliente' ),
            'type'  => 'text',
            'description' => __( 'id_cliente indica el acuerdo con que se va a liquidar' ),
            'desc_tip' => true,
        ),
        'user' => array(
            'title' => __( 'Usuario' ),
            'type'  => 'text',
            'description' => __( 'Usuario asignado' ),
            'desc_tip' => true,
        ),
        'password_guides' => array(
            'title' => __( 'Contraseña' ),
            'type'  => 'text',
            'description' => __( 'No confunda con la de seguimiento de despachos' ),
            'desc_tip' => true,
        ),
        'code_account' => array(
            'title' => __( 'Código de cuenta' ),
            'type'        => 'select',
            'class'       => 'wc-enhanced-select',
            'description' => __( 'El acuerdo de pago Cuenta Corriente, Acuerdo Semanal, Flete Pago' ),
            'desc_tip' => true,
            'default' => true,
            'options'     => array(
                1    => __( 'Cuenta Corriente'),
                2    => __( 'Acuerdo Semanal'),
                3 => __( 'Flete Pago'),
            ),
        ),
        'sandbox_guides'          => array(
            'title'       => __( 'Generación de guias (pruebas)' ),
            'type'        => 'title',
            'description' => __( 'id_cliente, usuario y contraseña para el entorno de pruebas' ),
        ),
        'sandbox_id_client' => array(
            'title' => __( 'id_cliente' ),
            'type'  => 'text',
            'description' => __( 'id_cliente indica el acuerdo con que se va a liquidar' ),
            'desc_tip' => true,
        ),
        'sandbox_user' => array(
            'title' => __( 'Usuario' ),
            'type'  => 'text',
            'description' => __( 'Usuario asignado' ),
            'desc_tip' => true,
        ),
        'sandbox_password_guides' => array(
            'title' => __( 'Contraseña' ),
            'type'  => 'text',
            'description' => __( 'No confunda con la de seguimiento de despachos' ),
            'desc_tip' => true,
        ),
        'sandbox_code_account' => array(
            'title' => __( 'Código de cuenta' ),
            'type'        => 'select',
            'class'       => 'wc-enhanced-select',
            'description' => __( 'El acuerdo de pago Cuenta Corriente, Acuerdo Semanal, Flete Pago' ),
            'desc_tip' => true,
            'default' => true,
            'options'     => array(
                1    => __( 'Cuenta Corriente'),
                2    => __( 'Acuerdo Semanal'),
                3 => __( 'Flete Pago'),
            ),
        ),
    )
);