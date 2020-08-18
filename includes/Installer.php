<?php

namespace WpAdroit\Wac_Coupon;

/**
 * Class Installer
 * @package WPGenerator
 */
class Installer
{
    /**
     * Run the installer
     *
     * @return void
     */
    public function run()
    {
        $this->checkPlugin();
        $this->add_version();
        $this->create_tables();
        $this->create_options();
    }

    /**
     * Check if WooCommerce Exixts
     */
    public function checkPlugin()
    {
        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            wp_die(__("You Need to install wooCommerce for use These Plugin !!", "superwoo"), null, ['back_link' => 1]);
        }
    }

    /**
     * Add time and version on DB
     */
    public function add_version()
    {
        $installed = get_option('Woo Advance Coupon _installed');

        if (!$installed) {
            update_option('Woo Advance Coupon _installed', time());
        }

        update_option('Woo Advance Coupon _version', WAC_ASSETS_VERSION);
    }

    /**
     * Create necessary database tables
     *
     * @return void
     */
    public function create_tables()
    {
        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
    }

    /**
     * create options for plugins
     **/
    public function create_options()
    {
        if (!get_option("wac_first_time_purchase_coupon")) {
            add_option("wac_first_time_purchase_coupon", 0);
        }

        if (!get_option("wac_first_time_purchase_coupon_label")) {
            add_option("wac_first_time_purchase_coupon_label", "Discounted Amount");
        }

        if (!get_option("wac_woo_setting_show_product_discount")) {
            add_option("wac_woo_setting_show_product_discount", "yes");
        }

        if (!get_option("wac_woo_setting_multi")) {
            add_option("wac_woo_setting_multi", "yes");
        }

        if (!get_option("wac_woo_setting_url")) {
            add_option("wac_woo_setting_url", "coupon");
        }
    }
}
