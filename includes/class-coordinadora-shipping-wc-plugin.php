<?php

class Coordinadora_Shipping_WC_Plugin
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
                    coordinadora_shipping_wc_cswc_notices($e->getMessage());
                });
            }
        }
    }

    protected function _run()
    {
        require_once ($this->includes_path . 'class-coordinadora-shipping-wc.php');
        add_filter( 'plugin_action_links_' . plugin_basename( $this->file), array( $this, 'plugin_action_links' ) );
        add_filter( 'woocommerce_shipping_methods', array( $this, 'coordinadora_shipping_wc_add_method') );
    }

    public function plugin_action_links($links)
    {
        $plugin_links = array();
        $plugin_links[] = '<a href="'.admin_url( 'admin.php?page=wc-settings&tab=shipping&section=coordinadora_shipping_wc').'">' . 'Configuraciones' . '</a>';
        $plugin_links[] = '<a href="https://saulmoralespa.github.io/coordinadora-shipping-wc/">' . 'Documentación' . '</a>';
        return array_merge( $plugin_links, $links );
    }

    public function coordinadora_shipping_wc_add_method($methods)
    {
        $methods[] = 'Coordinadora_Shipping_WC';
        return $methods;
    }
}