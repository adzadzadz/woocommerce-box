<?php 

namespace WCB;

class Init {

    private Array $config;

    public function __construct($config = null)
    {
        $this->load_config($config);
        $this->init();
    }

    public function init()
    {
        // Check ig page is not admin
        if ( is_admin() ) {
            new Admin_Product();
        }
        new Box_Control($this->config);
    }

    public function load_config(Array $config)
    {
        $this->config = $config;
    }

}