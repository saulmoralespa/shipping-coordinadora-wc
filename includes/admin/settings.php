<?php
return array(
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
        'title'       => __( 'Debug' ),
        'label'       => __( 'Enable debug mode' ),
        'type'        => 'checkbox',
        'default'     => 'no',
        'description' => __( 'Enable debug mode to show debugging information on your cart/checkout.' ),
        'desc_tip' => true,
    ),
    'api'          => array(
        'title'       => __( 'Seguimiento de despachos' ),
        'type'        => 'title',
        'description' => __( 'Apikey y contraseña proporcionados por Coordinadora, el nit vinculado' ),
    ),

    'api_key'      => array(
        'title' => __( 'API Key' ),
        'type'  => 'text',
        'description' => __( ' 	Api key provisto por Coordinadora' ),
        'desc_tip' => true,
    ),

    'password_tracing' => array(
        'title' => __( 'Contraseña' ),
        'type'  => 'text',
        'description' => __( 'La clave del webservice para Seguimiento de envios' ),
        'desc_tip' => true,
    ),

    'nit' => array(
        'title' => __( 'NIT' ),
        'type'  => 'text',
        'description' => __( 'Nit asociado a un acuerdo Coordinadora Mercantil' ),
        'desc_tip' => true,
    )
);