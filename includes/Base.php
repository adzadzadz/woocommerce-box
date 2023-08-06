<?php 

namespace WCB;

abstract class Base {

    protected Array $actions;
    
    protected Array $config;

    public function __construct(Array $config = null, Array $props = null)
    {
        $this->config = $config ?? [];
        if (isset($props)) {
            foreach ($props as $key => $value) {
                $this->$key = $value;
            }
        }
        foreach ($this->actions as $action) {
            add_action($action[0], [$this, $action[1]], $action[2] ?? null, $action[3] ?? null);
        }
        $this->init();
    }

    public function init()
    {
        // override this method
    }

    public function render($path, $attr = [])
    {
        ob_start();
        extract($attr);
        include(plugin_dir_path(__FILE__) . "views/" . $path . ".php");
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

}