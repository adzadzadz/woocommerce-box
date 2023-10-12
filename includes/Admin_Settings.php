<?php 

namespace WCB;

class Admin_Settings extends Base {

    protected Array $actions = [
        ['woocommerce_settings_tabs_settings_tab_mcs', 'settings_tab'],
        ['woocommerce_update_options_settings_tab_mcs', 'update_settings']
    ];

    protected Array $filters = [
        ['woocommerce_settings_tabs_array', 'add_settings_tab', 50, 1],
        // ['woocommerce_get_sections_mcs', 'set_sections', 50, 1]
    ];

    public function init()
    {
        parent::init();
    }

    public function settings_tab() {
        woocommerce_admin_fields( $this->get_settings() );
    }

    public function get_settings() {
        $settings = array(
            'section_title' => array(
                'name'     => __( 'Box Config', 'woocommerce-settings-tab-mcs' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_tab_mcs_section_title'
            ),
            'enable_box_charge' => array(
                'name'     => __( 'Charge customers for the boxes used?', 'woocommerce-settings-tab-mcs' ),
                'type'     => 'checkbox',
                'desc'     => __( 'Check this box to enable an additional charge for each box', 'woocommerce-settings-tab-mcs' ),
                'id'       => 'wc_settings_tab_mcs_enable_box_charge'
            ),
            'merge_to_shipping_cost' => array(
                'name'     => __( 'Merge to shipping cost', 'woocommerce-settings-tab-mcs' ),
                'type'     => 'checkbox',
                'desc'     => __( 'Merge the box charges to the shipping cost if checked.', 'woocommerce-settings-tab-mcs' ),
                'id'       => 'wc_settings_tab_mcs_enable_box_merge_shipping_cost'
            )
        );

        foreach ( $this->config['boxes'] as $box ) {
            $settings[ 'box_' . $box['size'] ] = array(
                'name' => __( 'Box ' . $box['size'] . ' price', 'woocommerce-settings-tab-mcs' ),
                'type' => 'number',
                'desc' => __( 'Add the box cost in $ (' . $box['size'] . ')', 'woocommerce-settings-tab-mcs' ),
                'id'   => 'wc_settings_tab_mcs_box_' . $box['size'],
                'custom_attributes' => array( 'step' => 'any', 'min' => '0' ),
            );
        }

        $settings['section_end'] = array(
            'type' => 'sectionend',
            'id' => 'wc_settings_tab_mcs_section_end'
        );

        return apply_filters( 'wc_settings_tab_mcs_settings', $settings );
    }

    public function add_settings_tab($settings_tabs ) {
        $settings_tabs['settings_tab_mcs'] = __( 'MCS', 'woocommerce-settings-tab-mcs' );
        return $settings_tabs;
    }

    public function update_settings() {
        woocommerce_update_options( $this->get_settings() );
    }

}