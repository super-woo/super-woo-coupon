<?php
/*
    Addon Name : Woo Advance Coupon
*/

// don't call the file directly
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once(ABSPATH . 'wp-admin/includes/plugin.php');

/**
 * Wac_main class
 *
 * @class Wac_main The class that holds the entire Wac_main plugin
 */
final class Wac_main
{
    /**
     * Plugin version
     *
     * @var string
     */
    const version = '1.0.0';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = [];

    /**
     * Constructor for the Wac_main class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    private function __construct()
    {
        $this->define_constants();
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        add_action('plugins_loaded', [$this, 'init_plugin']);
    }

    /**
     * Initializes the Wac_main() class
     *
     * Checks for an existing Wac_main() instance
     * and if it doesn't find one, creates it.
     *
     * @return Wac_main|bool
     */
    public static function init()
    {
        static $instance = false;

        if (!$instance) {
            $instance = new Wac_main();
        }

        return $instance;
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get($prop)
    {
        if (array_key_exists($prop, $this->container)) {
            return $this->container[$prop];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset($prop)
    {
        return isset($this->{$prop}) || isset($this->container[$prop]);
    }

    /**
     * Define the constants
     *
     * @return void
     */
    public function define_constants()
    {
        define('WAC_ASSETS_VERSION', self::version);
        define('WAC_ASSETS_FILE', __FILE__);
        define('WAC_ASSETS_PATH', dirname(WAC_ASSETS_FILE));
        define('WAC_ASSETS_INCLUDES', WAC_ASSETS_PATH . '/includes');
        define('WAC_ASSETS_URL', plugins_url('', WAC_ASSETS_FILE));
        define('WAC_ASSETS_ASSETS', WAC_ASSETS_URL . '/assets');
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function init_plugin()
    {
        $this->includes();
        $this->init_hooks();
        $this->checkPlugin();
    }

    /**
     * Check if WooCommerce Exixts
     */
    public function checkPlugin()
    {
        if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            deactivate_plugins(plugin_basename(__FILE__));
            add_action('admin_notices', [$this, 'deactivation_notice']);
        }
    }

    /**
     * Display Deactivation Notices
     **/
    public function deactivation_notice()
    {
        echo '<div class="notice notice-error is-dismissible">
             <p><small><code>Woo Advance Coupon</code></small> plugin is <b>Deactivated !!</b> It\'s require <small><code>WooCommerce</code></small> plugin</p>
         </div>';
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate()
    {
        $installer = new WpAdroit\Wac_Coupon\Installer();
        $installer->run();
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate()
    {
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes()
    {
        if ($this->is_request('admin')) {
            $this->container['admin'] = new WpAdroit\Wac_Coupon\Admin();
        }

        if ($this->is_request('frontend')) {
            $this->container['frontend'] = new WpAdroit\Wac_Coupon\Frontend();
        }

        if ($this->is_request('ajax')) {
            $this->container['ajax'] = new WpAdroit\Wac_Coupon\Ajax();
        }
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks()
    {
        add_action('init', [$this, 'init_classes']);

        // Localize our plugin
        add_action('init', [$this, 'localization_setup']);
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {
        if ($this->is_request('ajax')) {
            // $this->container['ajax'] =  new WpAdroit\Wac_Coupon\Ajax();
        }
        $this->container['assets'] = new WpAdroit\Wac_Coupon\Assets();
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup()
    {
        load_plugin_textdomain('superwoo', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();

            case 'ajax':
                return defined('DOING_AJAX');

            case 'rest':
                return defined('REST_REQUEST');

            case 'cron':
                return defined('DOING_CRON');

            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }
} // Wac_main

/**
 * Initialize the main plugin
 *
 * @return \Wac_main|bool
 */
function wac_main()
{
    return Wac_main::init();
}

/**
 *  kick-off the plugin
 */
wac_main();
