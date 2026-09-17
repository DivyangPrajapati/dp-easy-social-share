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
        add_filter('the_content', [$this, 'add_inline_social_networks_to_content']);
        add_action( 'wp_footer', [$this, 'render_floating_social_networks'] );
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
    public function add_inline_social_networks_to_content($content) {
        // Load settings
        $settings   = get_option( 'dpessr_share_settings', [] );
        $general    = $settings['general'] ?? [];
        $inline     = $settings['inline'] ?? [];

        $is_active  = !empty($inline['enabled']);
        $post_types = isset($inline['post_types']) && !empty($inline['post_types']) ? $inline['post_types'] : [];

        if ($is_active && is_singular() && in_array(get_post_type(), $post_types, true)) {
            $url            = get_permalink();
            $title          = get_the_title();
            $networks       = $general['networks'] ?? [];
            $social_buttons = $this->render_icons_html( $networks, $url, $title, 'dpessr-icons dpessr-colors-brand dpessr-inline-icons' );

            if ($inline['display_position'] === 'above') {
                return $social_buttons . $content;
            } else {
                return $content . $social_buttons;
            }
        }

        return $content;
    }

    /**
     * Render floating icons in the footer.
     *
     * @return void
     */
    public function render_floating_social_networks() {
        // Load settings
        $settings   = get_option( 'dpessr_share_settings', [] );
        $general    = $settings['general'] ?? [];
        $floating   = $settings['floating'] ?? [];

        $is_active  = !empty($floating['enabled']);
        $post_types = isset($floating['post_types']) && !empty($floating['post_types']) ? $floating['post_types'] : [];
        $position   = $floating['display_position'] ?? 'left';

        if ($is_active && is_singular() && in_array(get_post_type(), $post_types, true)) {
            $url            = get_permalink();
            $title          = get_the_title();
            $networks       = $general['networks'] ?? [];
            $wrapper_classes = 'dpessr-icons dpessr-colors-brand dpessr-floating dpessr-floating-' . esc_attr($position);

            echo $this->render_icons_html( $networks, $url, $title, $wrapper_classes );
        }
    }

    /**
     * Build the share-icons HTML markup for a given list of networks.
     *
     * Shared by both the inline (the_content) and floating (wp_footer)
     * renderers so the per-icon markup stays in exactly one place.
     *
     * @param array  $networks        List of enabled network keys (e.g. ['facebook', 'x']).
     * @param string $url             The URL to share.
     * @param string $title           The title to share.
     * @param string $wrapper_classes Space-separated class list for the outer wrapping <div>.
     * @return string The rendered icons HTML, or an empty string if there are no networks.
     */
    private function render_icons_html( $networks, $url, $title, $wrapper_classes ) {
        if ( empty( $networks ) ) {
            return '';
        }

        $html = '<div class="' . esc_attr( $wrapper_classes ) . '">';

        foreach ( $networks as $icon ) {
            $share_link   = DPESSR_Social_Share_Helper::get_share_url( $icon, $url, $title );
            $social_title = DPESSR_Social_Share_Helper::get_social_title( $icon );

            $html .= '<div class="dpessr-icon">';
            $html .= '<a href="' . esc_url( $share_link ) . '" target="_blank" rel="noopener nofollow" aria-label="Share on ' . esc_html( $social_title ) . '" title="Share on ' . esc_html( $social_title ) . '" class="dpessr-link dpessr-' . esc_attr( $icon ) . '">' . DPESSR_Social_Share_Helper::get_svg_icon( $icon ) . '</a>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}