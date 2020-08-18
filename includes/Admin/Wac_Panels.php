<?php

namespace WpAdroit\Wac_Coupon\Admin;

/**
 * Wac_Panel class
 * Woocommerce Custom Tabs
 */
class Wac_Panels
{
    public function __construct()
    {
        add_filter('woocommerce_coupon_data_tabs', [$this, 'wac_data_tabs'], 100, 1);
        add_filter('woocommerce_coupon_data_panels', [$this, 'wac_tabs_screen']);
        add_action('save_post', [$this, 'wac_save_coupon_data']);
    }

    public function wac_save_coupon_data($post_id)
    {
        if (isset($_POST["post_type"])) {
            if (!isset($_POST["wac_feature"]) & $_POST["post_type"] != "shop_coupon") {
                return;
            }
            $wac_data = [
                "list_id" => $_POST["wac_feature"],
                "overwrite_discount" => $_POST["overwrite_discount"]
            ];
            update_post_meta($post_id, "wac_coupon_panel", $wac_data);
        }
    }

    public function wac_data_tabs($tabs)
    {
        $tabs['wac_features'] = array(
            'label'     => __('Woo Coupon', 'superwoo'),
            'class'  => 'wac_coupon_panel',
            'target'     => 'wac_tabs_screen'
        );
        return $tabs;
    }

    public function wac_tabs_screen()
    {
?>
        <div id="wac_tabs_screen" class="panel woocommerce_options_panel">
            <div id="post">
                <wactabs />
            </div>
        </div>
<?php
    }
}
