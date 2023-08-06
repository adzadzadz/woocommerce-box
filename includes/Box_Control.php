<?php

namespace WCB;

class Box_Control extends Base
{

    private array $boxes = [];

    protected array $actions = [
        // add ajax action for box status change
        ['wp_ajax_wcb_update_box', 'update_box_status'],
        // add nopriv ajax for update_box_status
        ['wp_ajax_nopriv_wcb_update_box', 'update_box_status'],
        // enqueue scripts and styles
        ['wp_enqueue_scripts', 'enqueue_box_control_scripts'],
    ];

    public function init()
    {
        parent::init();
        // register shortcode
        add_shortcode('wcb_show_box', [$this, 'create_box_view_shortcode']);

        // create boxes
        foreach ($this->config['box'] as $size => $prop) {
            // var_dump($prop['max_point_value']); exit;
            $this->boxes[$size] = new Box($size, $prop['max_point_value'] ?? 0);
        }
        // foreach ($this->boxes as $size => $box) {
        //     $box->set_quantity(2);
        //     var_dump($box->get_max_point_value()); exit;
        // }
        // var_dump($this->boxes); exit;
    }

    public function set_boxes($boxes)
    {
        $this->boxes = $boxes;
    }

    public function get_boxes()
    {
        return $this->boxes;
    }

    public function get_woo_cart_items_total_point_value()
    {
        // get current cart items
        $cart_items = WC()->cart->get_cart();
        // get cart items point value
        $cart_items_point_value = 0;
        foreach ($cart_items as $cart_item) {
            $points = empty($cart_item['data']->get_meta('wcb_item_points')) ? 50 : $cart_item['data']->get_meta('wcb_item_points');
            $cart_items_point_value += $points * $cart_item['quantity'];
        }
        return $cart_items_point_value;
    }

    public function assign_box($cart_items_point_value)
    {
        // infinite loop
        for ($i = 1; $i > 0; $i++) {
            // assign box
            foreach ($this->boxes as $size => $box) {
                $box->set_quantity($i);
                if ($box->get_max_point_value() >= $cart_items_point_value) {
                    return [
                        'size' => $size,
                        'box'  => $box,
                    ];
                }
            }
        }
        return false;
    }

    public function update_box_status()
    {
        try {
            $total_cart_value = $this->get_woo_cart_items_total_point_value();
            $assigned_box = $this->assign_box($total_cart_value);
            $max_point_value = $assigned_box['box']->get_max_point_value();
            // get the rate of total_cart_value from box max_point_value
            $rate = ($total_cart_value / $max_point_value) * 100;

            echo json_encode([
                'box_size' => $assigned_box['size'],
                'box_quantity' => $assigned_box['box']->get_quantity(),
                'box_current_point_value' => $total_cart_value,
                'box_max_point_value' => $max_point_value,
                'rate' => number_format((float)$rate, 2, '.', ''),
                'cart_items' => WC()->cart->get_cart_contents_count(),
            ]);
        } catch (\Throwable $th) {
            echo json_encode(['error' => $th->getMessage()]);
        }
        wp_die();
    }

    public function enqueue_box_control_scripts()
    {
        wp_enqueue_script('wcb-box-control-ajax', MCS_WCB_PLUGIN_URL . 'assets/js/box-control.js', ['jquery'], null, true);
        wp_localize_script('wcb-box-control-ajax', 'wcb_box_control', ['ajax_url' => admin_url('admin-ajax.php')]);
        // wp_enqueue_style('wcb-box-control', MCS_WCB_PLUGIN_URL . 'assets/css/style.css', [], null, 'all');
    }

    public function create_box_view_shortcode($atts)
    {
        return $this->render('box_control', ['boxes' => $this->boxes]);
    }
}
