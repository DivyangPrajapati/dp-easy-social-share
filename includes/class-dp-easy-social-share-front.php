<?php
/**
 * DP Easy Social Share Front Class
 *
 * Handles the front-end logic of the social share buttons.
 *
 * @package DP Easy Social Share
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DPESSR_Social_Share_Front {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('the_content', [$this, 'add_social_icons_to_content']);
    }

    /**
     * Enqueue front-end styles for the plugin.
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'dpessr-style', DPESSR_PLUGIN_URL . 'assets/css/style.css', false, DPESSR_PLUGIN_VERSION );
    }

    /**
     * Add social icons to the content based on plugin settings.
     *
     * @param string $content The original post content.
     * @return string The content with social icons added, if applicable.
     */
    public function add_social_icons_to_content($content) {
        $settings   = get_option( 'dpessr_settings' );
        $post_types = isset($settings['post_types']) && !empty($settings['post_types']) ? $settings['post_types'] : [];

        if (is_singular() && in_array(get_post_type(), $post_types, true)) {
            $social_buttons = '';
            $url    = get_permalink();
            $title  = get_the_title();

            if (!empty($settings['social_icons'])) {
                $social_buttons .= '<div class="dpessr-icons dpessr-colors-brand">';
                foreach ($settings['social_icons'] as $icon) {
                    $share_link = DPESSR_Social_Share_Helper::get_share_url($icon, $url, $title);
                    $social_buttons .= '<div class="dpessr-icon">';
                    $social_buttons .= '<a href="' . esc_url($share_link) . '" target="_blank" class="dpessr-link dpessr-' . esc_attr($icon) . '">' . DPESSR_Social_Share_Helper::get_svg_icon($icon) . '</a>';
                    $social_buttons .= '</div>';
                }
                $social_buttons .= '</div>';
            }

            if ($settings['display_position'] === 'above') {
                return $social_buttons . $content;
            } else {
                return $content . $social_buttons;
            }
        }

        return $content;
    }
}