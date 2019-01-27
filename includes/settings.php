<?php
return array(
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
    ),
    'api'          => array(
        'title'       => __( 'API Settings' ),
        'type'        => 'title',
        'description' => __( 'You need to obtain coordinadora account credentials by registering on via their website.' ),
    ),

    'api_key'      => array(
        'title' => __( 'API Key' ),
        'type'  => 'text',
    ),

    'api_password' => array(
        'title' => __( 'API Password' ),
        'type'  => 'text',
    ),
);