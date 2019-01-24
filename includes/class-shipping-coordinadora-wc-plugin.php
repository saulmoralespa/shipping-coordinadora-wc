<?php

class Shipping_Coordinadora_WC_Plugin
{
    /**
     * Filepath of main plugin file.
     *
     * @var string
     */
    public $file;
    /**
     * Plugin version.
     *
     * @var string
     */
    public $version;
    /**
     * Absolute plugin path.
     *
     * @var string
     */
    public $plugin_path;
    /**
     * Absolute plugin URL.
     *
     * @var string
     */
    public $plugin_url;
    /**
     * Absolute path to plugin includes dir.
     *
     * @var string
     */
    public $includes_path;
    /**
     * @var string
     */
    public $tracing_url_coordinadora;
    /**
     * @var WC_Logger
     */
    public $logger;
    /**
     * @var bool
     */
    private $_bootstrapped = false;

    public function __construct($file, $version)
    {
        $this->file = $file;
        $this->version = $version;

        $this->plugin_path   = trailingslashit( plugin_dir_path( $this->file ) );
        $this->plugin_url    = trailingslashit( plugin_dir_url( $this->file ) );
        $this->includes_path = $this->plugin_path . trailingslashit( 'includes' );
        $this->tracing_url_coordinadora = "http://sandbox.coordinadora.com/ags/1.4/server.php?wsdl";
        $this->logger = new WC_Logger();
    }

    public function run_coordinadora_wc()
    {
        try{
            if ($this->_bootstrapped){
                throw new Exception( 'Coordinadora shipping can only be called once');
            }
            $this->_run();
            $this->_bootstrapped = true;
        }catch (Exception $e){
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                add_action('admin_notices', function() use($e) {
                    shipping_coordinadora_wc_cswc_notices($e->getMessage());
                });
            }
        }
    }

    protected function _run()
    {
        require_once ($this->includes_path . 'class-shipping-coordinadora-wc.php');
        require_once ($this->includes_path . 'class-shipping-coordinadora-wc-admin.php');
        $this->admin = new Shipping_Coordinadora_WC_Admin();

        add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
        add_action( 'shipping_coordinadora_wc_cswc',array($this, 'update_cities'));
        add_action( 'woocommerce_shipping_init', 'shipping_coordinadora_wc_init' );
        add_filter( 'woocommerce_shipping_methods', array( $this, 'shipping_coordinadora_wc_add_method') );
    }

    public function plugin_action_links($links)
    {
        $plugin_links = array();
        $plugin_links[] = '<a href="'.admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipping_coordinadora_wc').'">' . 'Configuraciones' . '</a>';
        $plugin_links[] = '<a href="https://saulmoralespa.github.io/shipping-coordinadora-wc/">' . 'Documentación' . '</a>';
        return array_merge( $plugin_links, $links );
    }

    public function shipping_coordinadora_wc_add_method($methods)
    {
        $methods[] = 'Shipping_Coordinadora_WC';
        return $methods;
    }

    public function update_cities()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'shipping_coordinadora_cities';
        $sql = "DELETE FROM $table_name";
        $wpdb->query($sql);

        $client = New SoapClient($this->tracing_url_coordinadora);

        $res = $client->__call('Cotizador_ciudades', array());
        $cities = $res->Cotizador_ciudadesResult;

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

    }
}