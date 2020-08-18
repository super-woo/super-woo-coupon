<?php

namespace WpAdroit\Wac_Coupon\Admin;

/**
 * Wac_setting class
 * Woocommerce Settings Tabs
 */
class Wac_Setting
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'wac_menu_items']);
        add_action('admin_init', [$this, 'wac_register_settings']);
    }

    public function wac_menu_items()
    {
        // add_submenu_page('superwoo-addons', 'SuperWoo Addons', 'Addons', 'manage_options', 'superwoo-addons');
        // $post_type_link = 'edit.php?post_type=woocoupon';
        // add_submenu_page('superwoo-addons', 'WooCoupons', 'Coupons', 'manage_options', $post_type_link);
        add_submenu_page('superwoo-addons', 'WooCoupon settings', 'Settings', "manage_options", 'woocoupon_settings', [$this, 'woocoupon_settings_content']);
    }

    /**
     * register settings options
     **/
    public function wac_register_settings()
    {
        register_setting('woocoupon_settings', 'wac_first_time_purchase_coupon');
        register_setting('woocoupon_settings', 'wac_first_time_purchase_coupon_label');
        register_setting('woocoupon_settings', 'wac_woo_setting_show_product_discount');
        register_setting('woocoupon_settings', 'wac_woo_setting_multi');
        register_setting('woocoupon_settings', 'wac_woo_setting_url');
    }

    public function woocoupon_settings_content()
    {
        $args = [
            "post_type" => "woocoupon",
            'post_status' => 'publish'
        ];
        $wac_data = get_posts($args);
        $wac_coupons = ["0" => "Select Discount"];
        foreach ($wac_data as $data) {
            $wac_coupons[$data->ID] = $data->post_title;
        }
?>
        <div class="wrap">
            <?php settings_errors(); ?>
            <h1>WooCoupon Settings</h1>
            <p>These settings can effect both coupons</p>
            <form method="post" action="options.php">
                <?php settings_fields('woocoupon_settings'); ?>
                <?php do_settings_sections('woocoupon_settings'); ?>
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="wac_first_time_purchase_coupon">
                                    <?php _e('Coupon for first Purchase', 'superwoo'); ?>
                                </label>
                            </th>
                            <td class="forminp forminp-select">
                                <select name="wac_first_time_purchase_coupon" id="wac_first_time_purchase_coupon" required>
                                    <?php foreach ($wac_coupons as $key => $value) : ?>
                                        <option value="<?php echo $key; ?>" <?php if ($key == get_option("wac_first_time_purchase_coupon")) {
                                                                                echo "selected";
                                                                            } ?>><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e('Select a discount from here which you want to enable for new customers', 'superwoo'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wac_first_time_purchase_coupon_label">
                                    <?php _e('First Purchase coupon label', 'superwoo'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="wac_first_time_purchase_coupon_label" id="wac_first_time_purchase_coupon_label" value="<?php echo esc_attr(get_option('wac_first_time_purchase_coupon_label')); ?>" required />
                                <p class="description"><?php _e('Display Label on cart', 'superwoo'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wac_woo_setting_show_product_discount">
                                    <?php _e('Show Product Discount', 'superwoo'); ?>
                                </label>
                            </th>
                            <td>
                                <select name="wac_woo_setting_show_product_discount" id="wac_woo_setting_show_product_discount" required>
                                    <option value="yes" <?php if ('yes' == get_option("wac_woo_setting_show_product_discount")) {
                                                            echo "selected";
                                                        } ?>>Yes</option>
                                    <option value="no" <?php if ('no' == get_option("wac_woo_setting_show_product_discount")) {
                                                            echo "selected";
                                                        } ?>>No</option>
                                </select>
                                <p class="description"><?php _e('Set "no" , if you want to hide product discount', 'superwoo'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wac_woo_setting_multi">
                                    <?php _e('Multi Coupon', 'superwoo'); ?>
                                </label>
                            </th>
                            <td>
                                <select name="wac_woo_setting_multi" id="wac_woo_setting_multi" required>
                                    <option value="yes" <?php if ('yes' == get_option("wac_woo_setting_multi")) {
                                                            echo "selected";
                                                        } ?>>Yes</option>
                                    <option value="no" <?php if ('no' == get_option("wac_woo_setting_multi")) {
                                                            echo "selected";
                                                        } ?>>No</option>
                                </select>
                                <p class="description"><?php _e('Set "no" , if you never want to apply Multi coupon in cart', 'superwoo'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="wac_woo_setting_url">
                                    <?php _e('Coupon Url slug Name', 'superwoo'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" name="wac_woo_setting_url" id="wac_woo_setting_url" value="<?php echo esc_attr(get_option('wac_woo_setting_url')); ?>" required />
                                <p class="description"><?php echo get_home_url() . '/?<b>' . get_option('wac_woo_setting_url') . '</b>=coupon_code'; ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(); ?>

            </form>
        </div>
<?php
    }
}
