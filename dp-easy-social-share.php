<?php
/**
 * Plugin Name: DP Easy Social Share
 * Description: A simple social sharing plugin for WordPress
 * Version: 1.0.0
 * Author: Divyang Prajapati
 * Author URI: https://github.com/DivyangPrajapati
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: dp-easy-social-share
 * Domain Path: /languages
 * 
 * @package DP Easy Social Share
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* Define plugin directory constants */
define( 'DPESSR_PLUGIN_VERSION', '1.0.0' );
define( 'DPESSR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DPESSR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class
 */
class DPESSR_Social_Share {

    /**
     * Plugin instance
     *
     * @var DPESSR_Social_Share
     */
    private static $instance;

    /**
     * Get plugin instance
     *
     * @return DPESSR_Social_Share
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once DPESSR_PLUGIN_DIR . 'includes/class-dp-easy-social-share-helper.php';
        require_once DPESSR_PLUGIN_DIR . 'includes/class-dp-easy-social-share-admin.php';
        require_once DPESSR_PLUGIN_DIR . 'includes/class-dp-easy-social-share-front.php';
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        add_action('init', [$this, 'init_plugin']);

        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), [$this, 'settings_link'] );
    }

    /**
     * Plugin activation handler
     */
    public function activate() {
        // Add default options if not exists
        if (!get_option('dpessr_settings')) {
            $default_options = [
                'social_icons'      => ['facebook', 'x', 'linkedin', 'email'],
                'post_types'        => ['post', 'page'],
                'display_position'  => 'below'
            ];

            update_option('dpessr_settings', $default_options);
        } 
    }

    /**
     * Plugin deactivation handler
     */
    public function deactivate() {
        // Cleanup if needed
    }

    /**
     * Add a settings link on the plugins page.
     *
     * @param array $links An array of existing action links.
     * @return array Modified action links array with the settings link.
     */
    function settings_link( $links ) {
        $settings_link = '<a href="admin.php?page=dp-easy-social-share">' . __( 'Settings', 'dp-easy-social-share' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Initialize plugin components
     */
    public function init_plugin() {
        new DPESSR_Social_Share_Admin();
        new DPESSR_Social_Share_Front();
    }
}

// Initialize plugin
DPESSR_Social_Share::get_instance();