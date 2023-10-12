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
        // Inject box view shortcode
        ['wp_body_open', 'inject_box_view_shortcode'],
        // Inject box view shortcode after cart totals
        ['woocommerce_after_cart_totals', 'custom_html_after_cart_totals'],
        // Inject box view shortcode below the checkout total
        ['woocommerce_review_order_before_order_total', 'custom_html_after_cart_totals'],

        ['woocommerce_checkout_update_order_meta', 'save_checkout_field', 10, 1],
        ['woocommerce_after_shipping_rate', 'add_checkout_field'],
        ['woocommerce_admin_order_items_after_line_items', 'insert_row_after_line_items', 10, 1],

        ['wpo_wcpdf_after_order_details', 'wpo_wcpdf_box_value', 10, 2],
    ];

    protected array $filters = [
        ['wc_add_to_cart_message_html', 'push_add_to_cart_notification_update', 10, 2],
    ];

    private array $available_boxes = [];

    private array $selected_boxes = [];

    private \WCB\Box_Rules $rules;

    private Int $default_item_point_value = 50;

    public function init()
    {
        parent::init();
        // register shortcode
        add_shortcode('wcb_show_box', [$this, 'create_box_view_shortcode']);

        if (get_option('wc_settings_tab_mcs_enable_box_charge') == 'yes') {
            if (get_option('wc_settings_tab_mcs_enable_box_merge_shipping_cost') == 'yes') {
                
                if (!is_admin() && defined('DOING_AJAX')) {
                    add_filter('woocommerce_package_rates', [$this, 'merge_to_shipping_cost'], 30, 2);
                } else {
                    add_filter('woocommerce_cart_shipping_packages', [$this, 'wc_shipping_rate_cache_invalidation'], 100, 1);
                }
            } else {
                add_action('woocommerce_cart_calculate_fees', [$this, 'add_custom_shipping_fee'], 10, 1);
            }
        }

        error_log("Box Control Init");

        $this->rules = new \WCB\Box_Rules($this->config['rules']);
        $this->set_boxes($this->config['boxes']);
    }

    public function wc_shipping_rate_cache_invalidation($packages)
    {
        foreach ($packages as &$package) {
            $package['rate_cache'] = wp_rand(00000, 99999);
        }

        return $packages;
    }

    public function wpo_wcpdf_box_value($document_type, $order)
    {
        if (!empty($order) && $document_type == 'packing-slip') {
            $box_value = get_post_meta($order->get_id(), '_mcs_wc_box_value', true);
            echo "<div><strong>Shipping</strong>: {$box_value}</div>";
        }
    }

    public function add_custom_shipping_fee($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        // if (get_option('wc_settings_tab_mcs_enable_box_merge_shipping_cost') == 'yes')
        //     return;

        $additional_charge = $this->get_box_value_cost();
        $cart->add_fee(__('Shipping Box Fee', 'woocommerce'), $additional_charge);
    }

    public function merge_to_shipping_cost($rates, $package)
    {
        // if (get_option('wc_settings_tab_mcs_enable_box_merge_shipping_cost') == 'no')
        //     return $rates;

        $additional_charge = $this->get_box_value_cost(); // Set your additional charge here

        foreach ($rates as $rate_key => $rate) {
            // $method_id = $rate->get_method_id();
            // if ('flat_rate' === $method_id) {
            $new_cost = $rate->get_cost() + $additional_charge;
            $rate->set_cost($new_cost);
            // }
        }

        return $rates;
    }

    public function insert_row_after_line_items($order_id)
    {
        $box_value = get_post_meta($order_id, '_mcs_wc_box_value', true);
        echo '<tr class="order_item">';
        echo '<td class="">' . "<strong>Shipping</strong>" . '</td>'; // Replace 'New Row Label' with your desired label
        echo '<td class="">' . $box_value . '</td>';
        echo '</tr>';
    }

    public function add_checkout_field()
    {
        $text_value = $this->get_box_value_text()['text'];
        echo '<div id="mcs_wc_box_value_wrapper" style="display:none;">';
        woocommerce_form_field('mcs_wc_box_value', array(
            'type' => 'hidden',
            'class' => array('form-row-wide'),
            'label' => __('Shipping Box', 'text-domain'),
            'default' => $text_value,
        ), $text_value);
        echo '</div>';
    }

    public function save_checkout_field($order_id)
    {
        $mcs_wc_box_value = $_POST['mcs_wc_box_value'];
        if (!empty($mcs_wc_box_value))
            update_post_meta($order_id, '_mcs_wc_box_value', sanitize_text_field($mcs_wc_box_value));
    }

    public function custom_html_after_cart_totals()
    {
        echo $this->render('shortcode_box_view', [
            'id'    => 'mcs_wcb_box_view_cart_page',
            'boxes' => $this->available_boxes
        ]);
    }

    public function get_box_value_cost()
    {
        $assigned = $this->get_assigned_boxes();
        $add_cost = 0;

        foreach ($assigned['box']['size'] as $size) {
            $add_cost += get_option('wc_settings_tab_mcs_box_' . $size, '0');
        }

        return floatval($add_cost);
    }

    public function get_box_value_text()
    {
        $assigned = $this->get_assigned_boxes();
        // $add_cost = 0;
        $box_qty = [
            'small' => 0,
            'large' => 0
        ];

        foreach ($assigned['box']['size'] as $size) {
            $size = strtolower($size);
            if ($size == 'small') {
                // $add_cost += 30;
                $box_qty['small']++;
            } elseif ($size == 'large') {
                // $add_cost += 50;
                $box_qty['large']++;
            }
        }

        $progress = number_format(($assigned['pv'] / $assigned['box']['max_point_value']) * 100, 2);

        /**
         * Note: Disable rules for now
         */
        // if ($this->rules->is_min_box_fill_rate_reached(number_format(($assigned['pv'] / $assigned['box']['max_point_value']) * 100, 2))) {
        //     if ($this->rules->is_min_spent_reached()) {
        //         // $add_cost = 0; // reset cost to 0usd if min spent is reached
        //     }
        // }

        $box_label = '';
        if ($box_qty['small'] > 0) {
            $box_label .= "Small box x{$box_qty['small']}";
        }
        if ($box_qty['small'] > 0 && $box_qty['large'] > 0) {
            $box_label .= " and ";
        }
        if ($box_qty['large'] > 0) {
            $box_label .= "Large box x{$box_qty['large']}";
        }

        // $additional_charge = $add_cost; // Set your additional charge here
        $text = "{$box_label}  ({$progress}% full)."; // Set your label here

        return [
            'text' => $text
        ];
    }

    public function get_assigned_boxes()
    {
        $pv = $this->get_woo_cart_items_total_point_value();
        $box = $this->assign_box($pv)
            ->get_summary(['size', 'max_point_value', 'current_point_value', 'quantity']);
        return [
            'pv'  => $pv,
            'box' => $box
        ];
    }

    public function push_add_to_cart_notification_update($message, $products)
    {
        $assigned = $this->get_assigned_boxes();
        $custom_text = '<strong>Shipping:</strong>';

        foreach ($assigned['box']['size'] as $size) {
            $custom_text .= ' 1 ' . $size . ' box';
        }

        $progress = number_format(($assigned['pv'] / $assigned['box']['max_point_value']) * 100, 2);
        $custom_text .= " is <strong>$progress%</strong> full.";

        return $message . ' <p>' . $custom_text . '</p>';
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
        if (WC()->cart) {
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
        return 0;
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
                } elseif ($prev_box && ($prev_box->get_max_point_value() * ($prev_box->get_quantity() + 1)) >= $left_over_points) {
                    $left_over_points -= ($prev_box->get_max_point_value() * ($prev_box->get_quantity() + 1));
                    $this->selected_boxes[] = $prev_box;
                    $this->selected_boxes[] = $prev_box;
                } elseif ($prev_box && ($box->get_max_point_value() + $prev_box->get_max_point_value()) >= $left_over_points) {
                    $left_over_points -= ($box->get_max_point_value() + $prev_box->get_max_point_value());
                    $this->selected_boxes[] = $prev_box;
                    $this->selected_boxes[] = $box;
                } elseif ($i == 0) {
                    $left_over_points -= $box->get_max_point_value();
                    $this->selected_boxes[] = $box;
                }

                // error_log("bot: " . print_r($left_over_points, true));
                $prev_box = $box;
                if ($left_over_points <= 0)
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
        $summary['size'] = [];

        foreach ($selected_boxes as $box) {
            $summary['max_point_value'] += $box['max_point_value'];
            $summary['size'][] = ucfirst($box['size']);
        }

        return $summary;
    }

    public function update_box_status()
    {
        try {
            $pv = $this->get_woo_cart_items_total_point_value();
            $boxes = $this->assign_box($pv)
                ->get_summary(['size', 'max_point_value', 'current_point_value', 'quantity']);

            echo json_encode([
                'success'  => 'Box status updated',
                'cart_items_point_value' => $pv,
                'progress' => $pv == 0 ? 0 : number_format(($pv / $boxes['max_point_value']) * 100, 2),
                'boxes'    => $boxes
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
        wp_enqueue_style('wcb-box-control', MCS_WCB_PLUGIN_URL . 'assets/css/style.css', [], null, 'all');
    }

    public function create_box_view_shortcode($atts)
    {
        return $this->render('shortcode_box_view', ['boxes' => $this->available_boxes]);
    }

    public function inject_box_view_shortcode()
    {
        echo $this->render('shortcode_box_view', [
            'id'    => 'mcs_wcb_box_view_cart_widget',
            'boxes' => $this->available_boxes,
            'is_hidden' => true
        ]);
    }
}
