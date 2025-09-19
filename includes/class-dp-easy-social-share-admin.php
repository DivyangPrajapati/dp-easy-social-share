<?php
/**
 * DP Easy Social Share Admin Class
 *
 * Manages the plugin's admin settings.
 *
 * @package DP Easy Social Share
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DPESSR_Social_Share_Admin {

    /**
     * Array of inline positions
     *
     * @var array
     */
     private $inline_positions;

    /**
     * Constructor method.
     *
     * @return void
     */
    public function __construct() {
        $this->inline_positions = $this->get_inline_positions();

        add_action( 'admin_menu', [$this, 'add_admin_menu'] );
        add_action( 'admin_init', [$this, 'register_settings'] );
    }

    /**
     * Add the settings page to the WordPress admin menu.
     *
     * @return void
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'DP Easy Social Share Settings', 'dp-easy-social-share' ),
            __( 'DP Easy Social Share', 'dp-easy-social-share' ),
            'manage_options',
            'dp-easy-social-share',
            [$this, 'render_admin_page'],
            'dashicons-share'
        );
    }

    /**
     * Render the plugin settings page.
     *
     * @return void
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'DP Easy Social Share Settings', 'dp-easy-social-share' ); ?></h1>

            <?php  settings_errors(); ?>
            
            <form method="post" action="options.php">
                <?php
                // Output security fields for the registered setting groups.
                settings_fields( 'dpessr_share_settings_group' );
                settings_fields( 'dpessr_share_inline_group' );

                // Output settings sections and fields.
                do_settings_sections( 'dp-easy-social-share' );

                // Submit button
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register plugin settings.
     *
     * @return void
     */
    public function register_settings() {
        register_setting( 'dpessr_share_settings_group', 'dpessr_share_settings', [
            'sanitize_callback' => [$this, 'sanitize_share_settings']
        ] );
        
        add_settings_section(
            'dpessr_share_section',
            __( 'General Settings', 'dp-easy-social-share' ),
            null,
            'dp-easy-social-share'
        );

        register_setting( 'dpessr_share_inline_group', 'dpessr_share_inline', [
            'sanitize_callback' => [ $this, 'sanitize_share_inline' ]
        ] );

        add_settings_field(
            'dpessr_social_networks',
            __( 'Social Icons', 'dp-easy-social-share' ),
            [$this, 'render_social_networks_field'],
            'dp-easy-social-share',
            'dpessr_share_section'
        );

        add_settings_field(
            'dpessr_inline_post_types',
            __( 'Post Types', 'dp-easy-social-share' ),
            [$this, 'render_inline_post_types_field'],
            'dp-easy-social-share',
            'dpessr_share_section'
        );

        add_settings_field(
            'dpessr_inline_display_position',
            __('Display Position', 'dp-easy-social-share'),
            [$this, 'render_inline_display_position_field'],
            'dp-easy-social-share',
            'dpessr_share_section'
        );
    }

    /**
     * Sanitize settings before saving to database.
     *
     * @param array $input User input.
     * @return array Sanitized input.
     */
    public function sanitize_share_settings( $input ) {
        $sanitized = [];

        if ( isset( $input['networks'] ) && is_array( $input['networks'] ) ) {
            $sanitized['networks'] = array_map( 'sanitize_text_field', $input['networks'] );
        }

        /* if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
            $sanitized['post_types'] = array_map( 'sanitize_text_field', $input['post_types'] );
        }
        
        if ( isset( $input['display_position'] ) && !empty($input['display_position']) && isset( $this->inline_positions[ $input['display_position'] ] ) ) {
            $sanitized['display_position'] = sanitize_text_field($input['display_position']);
        } */

        return $sanitized;
    }

    /**
     * Sanitize settings before saving to database.
     *
     * @param array $input User input.
     * @return array Sanitized input.
     */
    public function sanitize_share_inline( $input ) {
        $sanitized = [];

        if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
            $sanitized['post_types'] = array_map( 'sanitize_text_field', $input['post_types'] );
        }
        
        if ( isset( $input['display_position'] ) && !empty($input['display_position']) && isset( $this->inline_positions[ $input['display_position'] ] ) ) {
            $sanitized['display_position'] = sanitize_text_field($input['display_position']);
        }

        return $sanitized;
    }

    /**
     * Render social icons checkboxes field.
     *
     * @return void
     */
    public function render_social_networks_field() {
        $options    = get_option( 'dpessr_share_settings' );
        $icons      = DPESSR_Social_Share_Helper::get_social_icons();

        foreach ( $icons as $key => $icon ) {
            $checked = isset( $options['networks'] ) && in_array( $key, $options['networks'], true );
            echo '<label><input type="checkbox" name="dpessr_share_settings[networks][]" value="' . esc_attr( $key ) . '"' . checked( $checked, true, false ) . '> ' . esc_html( ucfirst( $icon['name'] ) ) . '</label><br>';
        }
    }

    /**
     * Render post types checkboxes field.
     *
     * @return void
     */
    public function render_inline_post_types_field() {
        $options    = get_option( 'dpessr_share_inline' );
        $post_types = get_post_types( [ 'public' => true ], 'names' );

        foreach ( $post_types as $post_type ) {
            $checked = isset( $options['post_types'] ) && in_array( $post_type, $options['post_types'], true );
            echo '<label><input type="checkbox" name="dpessr_share_inline[post_types][]" value="' . esc_attr( $post_type ) . '"' . checked( $checked, true, false ) . '> ' . esc_html( ucfirst( $post_type ) ) . '</label><br>';
        }
    }

    /**
     * Render Display Position redio field.
     *
     * @return void
     */
    public function render_inline_display_position_field() {
        $options    = get_option( 'dpessr_share_inline' );
        $position   = isset( $options['display_position'] ) && !empty( $options['display_position'] )  && isset( $this->inline_positions[ $options['display_position'] ] ) ? $options['display_position'] : 'below';

        foreach ($this->inline_positions as $key => $label) {
            $checked = ($position === $key);
            echo '<label><input type="radio" name="dpessr_share_inline[display_position]" value="' .esc_attr( $key ) . '"' . checked( $checked, true, false ) . '> ' . esc_html( $label ) . '</label><br>';
        }
    }

    /**
     * Get positions.
     *
     * @return void
     */
    public function get_inline_positions() {
        $positions = [
            'above'     => __('Above Post', 'dp-easy-social-share'), 
            'below'     => __('Below Post', 'dp-easy-social-share')
        ];

        return $positions;
    }
}