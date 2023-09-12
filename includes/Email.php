<?php 

namespace WCB;

class Email extends Base {

    protected array $filters = [
        ['wc_get_template', 'wc_get_template', 10, 5],
    ];

    public function wcec_get_template($located, $template_name, $args, $template_path, $default_path) {
        $plugin_template_path = plugin_dir_path(__FILE__) . 'template/woocommerce/' . $template_name;
        if(file_exists($plugin_template_path)) {
            return $plugin_template_path;
        }
        return $located;
    }

    public function show_box_value()
    {
        
    }
}