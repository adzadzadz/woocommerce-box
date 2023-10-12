<?php 

namespace WCB;

class Init {

    private Array $config;

    public function __construct($config = null)
    {
        $this->load_config($config);
        add_action( 'init', [$this, 'init'] );
        // $this->init();
    }

    public function init()
    {
        // Check ig page is not admin
        if ( is_admin() ) {
            new \WCB\Admin_Product();
            new \WCB\Admin_Settings($this->config);
        }
        new \WCB\Box_Control($this->config);
    }

    public function load_config(Array $config)
    {
        $this->config = $config;
    }

}