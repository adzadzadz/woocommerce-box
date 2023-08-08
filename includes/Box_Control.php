<?php

namespace WCB;

class Box_Control extends Base
{
    protected array $actions = [
        // add ajax action for box status change
        ['wp_ajax_wcb_update_box', 'update_box_status'],
        // add nopriv ajax for update_box_status
        ['wp_ajax_nopriv_wcb_update_box', 'update_box_status'],
        // enqueue scripts and styles
        ['wp_enqueue_scripts', 'enqueue_box_control_scripts'],
    ];

    private array $available_boxes = [];

    private array $selected_boxes = [];

    private Int $default_item_point_value = 50;

    public function init()
    {
        parent::init();
        // register shortcode
        add_shortcode('wcb_show_box', [$this, 'create_box_view_shortcode']);
        $this->set_boxes($this->config['boxes']);
    }

    public function get_selected_boxes(array $props = null)
    {
        if ($props) {
            $selected_boxes = [];
            foreach ($this->selected_boxes as $box) {
                $selected_boxes[] = $box->get_props($props);
            }
            return $selected_boxes;
        }
        return $this->selected_boxes;
    }

    public function set_boxes(array $boxes)
    {
        foreach ($boxes as $id => $prop) {
            $this->available_boxes[$id] = new Box($prop['size'], $prop['max_point_value'] ?? 0);
        }
        return $this;
    }

    public function get_boxes()
    {
        return $this->available_boxes;
    }

    public function get_woo_cart_items_total_point_value()
    {
        // get current cart items
        $cart_items = WC()->cart->get_cart();
        // get cart items point value
        $cart_items_point_value = 0;
        foreach ($cart_items as $cart_item) {
            $points = empty($cart_item['data']->get_meta('wcb_item_points'))
                ? $this->default_item_point_value
                : $cart_item['data']->get_meta('wcb_item_points');
            $cart_items_point_value += $points * $cart_item['quantity'];
        }
        return $cart_items_point_value;
    }

    public function assign_box($cart_items_point_value)
    {
        $left_over_points = $cart_items_point_value;

        while ($left_over_points > 0) {
            $i = count($this->available_boxes);
            $prev_box = null;
            // error_log("top: " . print_r($left_over_points, true));
            foreach ($this->available_boxes as $box) {
                $i--;

                if ($box->get_max_point_value() >= $left_over_points) {
                    $left_over_points -= $box->get_max_point_value();
                    $this->selected_boxes[] = $box;
                } 
                elseif ($prev_box && ($prev_box->get_max_point_value() * ($prev_box->get_quantity() + 1)) >= $left_over_points) {
                    $left_over_points -= ($prev_box->get_max_point_value() * ($prev_box->get_quantity() + 1));
                    $this->selected_boxes[] = $prev_box;
                    $this->selected_boxes[] = $prev_box;
                } 
                elseif ($prev_box && ($box->get_max_point_value() + $prev_box->get_max_point_value()) >= $left_over_points) {
                    $left_over_points -= ($box->get_max_point_value() + $prev_box->get_max_point_value());
                    $this->selected_boxes[] = $prev_box;
                    $this->selected_boxes[] = $box;
                } 
                elseif ($i == 0) {
                    $left_over_points -= $box->get_max_point_value();
                    $this->selected_boxes[] = $box;
                }

                // error_log("bot: " . print_r($left_over_points, true));
                $prev_box = $box;
                if ( $left_over_points <= 0) 
                    break;
            }
        }
        return $this;
    }

    public function get_summary(array $props = null)
    {
        $selected_boxes = $this->get_selected_boxes($props);

        $summary = [];
        $summary['max_point_value'] = 0;
        $summary['size'] = '';

        foreach ($selected_boxes as $box) {
            $summary['max_point_value'] += $box['max_point_value'];
            $summary['size'] .= "{$box['size']}: {$box['quantity']}, ";
        }

        return $summary;
    }

    public function update_box_status()
    {
        try {
            echo json_encode([
                'success' => 'Box status updated',
                'cart_items_point_value' => $pv = $this->get_woo_cart_items_total_point_value(),
                'boxes'   => $this->assign_box($pv)
                    ->get_summary(['size', 'max_point_value', 'current_point_value', 'quantity']),
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
        return $this->render('box_control', ['boxes' => $this->available_boxes]);
    }
}
