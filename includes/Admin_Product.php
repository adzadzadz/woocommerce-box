<?php

namespace WCB;

class Admin_Product extends Base
{

    protected Array $actions = [
        // adding custom fields to product variations
        ['woocommerce_variation_options', 'add_variation_options', 20, 3],
        // save custom fields for product variations
        ['woocommerce_save_product_variation', 'save_custom_field_variations', 10, 2],
        //  enqueuing scripts and styles
        ['admin_enqueue_scripts', 'enqueue_admin_scripts', 10, 1],
    ];

    public function enqueue_admin_scripts($hook)
    {
        if ('post.php' === $hook) {
            global $post;
            if ($post->post_type == 'product') {
                wp_enqueue_script('wcb-admin-product', MCS_WCB_PLUGIN_URL . 'assets/js/admin-product.js', ['jquery'], null, true);
                wp_enqueue_style('wcb-admin-product', MCS_WCB_PLUGIN_URL . 'assets/css/style.css', [], null, 'all');
            }
        }
    }

    public function add_variation_options($loop, $variation_data, $variation)
    {
        woocommerce_wp_text_input(
            [
                'id' => 'wcb_item_points[' . $loop . ']',
                'type' => 'number',
                'wrapper_class' => 'wcb_item_points_wrapper',
                'class' => 'wcb_item_points_input-field',
                'custom_attributes' => ['data-variation_id' => $variation->ID, 'data-variation_loop' => $loop],
                'label' => __('Box item points', 'woocommerce'),
                'desc_tip' => 'true',
                'description' => __('Enter the point value', 'woocommerce'),
                'value' => get_post_meta($variation->ID, 'wcb_item_points', true)
            ]
        );
    }

    public function save_custom_field_variations($variation_id, $i)
    {
        $wcb_item_points = $_POST['wcb_item_points'][$i];
        if (isset($wcb_item_points)) {
            update_post_meta($variation_id, 'wcb_item_points', esc_attr($wcb_item_points));
        }
    }
}
