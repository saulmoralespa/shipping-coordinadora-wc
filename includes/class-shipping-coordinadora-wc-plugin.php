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
     * Absolute path to plugin lib dir
     *
     * @var string
     */
    public $lib_path;
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
        $this->lib_path = $this->plugin_path . trailingslashit( 'lib' );
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

        if (!class_exists('\WebService\Servientrega'))
            require_once ($this->lib_path . 'servientrega-webservice-php/src/WebService.php');
        require_once ($this->includes_path . 'class-shipping-coordinadora-wc-admin.php');
        require_once ($this->includes_path . 'class-method-shipping-coordinadora-wc.php');
        require_once ($this->includes_path . 'class-shipping-coordinadora-wc.php');
        $this->admin = new Shipping_Coordinadora_WC_Admin();

        add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
        add_action( 'shipping_coordinadora_wc_cswc_schedule',array('Shipping_Coordinadora_WC', 'update_cities'));
        add_filter( 'woocommerce_shipping_methods', array( $this, 'shipping_coordinadora_wc_add_method') );

        add_action( 'woocommerce_order_status_changed',array('Shipping_Coordinadora_WC', 'generate_guide_dispath'), 20, 4 );
    }

    public function plugin_action_links($links)
    {
        $plugin_links = array();
        $plugin_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&section=shipping_coordinadora_wc') . '">' . 'Configuraciones' . '</a>';
        $plugin_links[] = '<a href="https://saulmoralespa.github.io/shipping-coordinadora-wc/">' . 'Documentación' . '</a>';
        return array_merge( $plugin_links, $links );
    }

    public function shipping_coordinadora_wc_add_method( $methods ) {
        $methods['shipping_coordinadora_wc'] = 'WC_Shipping_Method_Shipping_Coordinadora_WC';
        return $methods;
    }

    public function log($message)
    {
        if (is_array($message) || is_object($message))
            $message = print_r($message, true);
        $logger = new WC_Logger();
        $logger->add('shipping-coordinadora', $message);
    }
}