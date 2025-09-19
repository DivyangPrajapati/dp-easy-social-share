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
     *
     * @return void
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('the_content', [$this, 'add_social_networks_to_content']);
    }

    /**
     * Enqueue front-end styles for the plugin.
     *
     * @return void
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'dpessr-style', DPESSR_PLUGIN_URL . 'assets/css/style.css', false, DPESSR_PLUGIN_VERSION );
    }

    /**
     * Add social networks to the content based on plugin settings.
     *
     * @param string $content The original post content.
     * @return string The content with social networks added, if applicable.
     */
    public function add_social_networks_to_content($content) {
        // Load settings
        $settings   = get_option( 'dpessr_share_settings', [] );
        $inline     = get_option( 'dpessr_share_inline', [] );

        $post_types = isset($inline['post_types']) && !empty($inline['post_types']) ? $inline['post_types'] : [];

        if (is_singular() && in_array(get_post_type(), $post_types, true)) {
            $social_buttons = '';
            $url    = get_permalink();
            $title  = get_the_title();

            if (!empty($settings['networks'])) {
                $social_buttons .= '<div class="dpessr-icons dpessr-colors-brand">';
                foreach ($settings['networks'] as $icon) {
                    $share_link     = DPESSR_Social_Share_Helper::get_share_url($icon, $url, $title);
                    $social_title   = DPESSR_Social_Share_Helper::get_social_title($icon);
                    $social_buttons .= '<div class="dpessr-icon">';
                    $social_buttons .= '<a href="' . esc_url($share_link) . '" target="_blank" rel="noopener nofollow" aria-label="Share on ' . esc_html($social_title) . '" title="Share on ' . esc_html($social_title) . '" class="dpessr-link dpessr-' . esc_attr($icon) . '">' . DPESSR_Social_Share_Helper::get_svg_icon($icon) . '</a>';
                    $social_buttons .= '</div>';
                }
                $social_buttons .= '</div>';
            }

            if ($inline['display_position'] === 'above') {
                return $social_buttons . $content;
            } else {
                return $content . $social_buttons;
            }
        }

        return $content;
    }
}