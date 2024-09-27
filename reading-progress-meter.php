<?php

/**
 * @link              https://techalgospotlight.com
 * @since             1.0
 * @package           Reading_Progress_Meter
 *
 * @wordpress-plugin
 * Plugin Name:       Reading Progress Meter
 * Plugin URI:        https://techalgospotlight.com
 * Description:       A versatile reading position indicator that can be placed at the top, bottom in various templates or post types, offering flexibility and customization options.
 * Version:           1.0
 * Author:            TechAlgoSpotlight
 * Author URI:        https://krunalkanojiya.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       reading-progress-meter
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
class Reading_Progress_Meter
{
    /**
     * Instance of the class.
     *
     * @var Reading_Progress_Meter
     */
    private static $instance;

    /**
     * Constructor is private to prevent multiple instances.
     */
    private function __construct()
    {
        $this->load_dependencies();
        $this->define_hooks();
    }

    /**
     * Loads all the required dependencies for the plugin.
     */
    private function load_dependencies()
    {
        if (is_admin()) {
            $admin_file = plugin_dir_path(__FILE__) . 'admin/rpm-admin.php';
            if (file_exists($admin_file)) {
                require_once $admin_file;
            }
        } else {
            $public_file = plugin_dir_path(__FILE__) . 'public/rpm-public.php';
            if (file_exists($public_file)) {
                require_once $public_file;
            }
        }
    }

    /**
     * Registers all the hooks related to the plugin.
     */
    private function define_hooks()
    {
        if (is_admin()) {
            // Register admin hooks if needed
        } else {
            // Register public hooks if needed
        }
    }

    /**
     * Returns an instance of this class.
     *
     * @return Reading_Progress_Meter
     */
    public static function get_instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

// Initialize the plugin.
Reading_Progress_Meter::get_instance();
