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
     * Array of floating sidebar positions
     *
     * @var array
     */
    private $floating_positions;

    /**
     * Constructor method.
     *
     * @return void
     */
    public function __construct() {
        $this->inline_positions     = $this->get_inline_positions();
        $this->floating_positions   = $this->get_floating_positions();

        add_action( 'admin_menu', [$this, 'add_admin_menu'] );
        add_action( 'current_screen', [$this, 'current_screen'] );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_assets'] );
        add_action( 'admin_init', [$this, 'register_settings'] );
        add_action( 'admin_init', [$this, 'handle_review_notice_action'] );
        add_action( 'admin_notices', [$this, 'maybe_render_review_notice'] );
    }

    /**
     * Handle the review notice action links (rate now / already rated / maybe later).
     *
     * Runs on admin_init, verifies the nonce and capability, records the user's
     * choice, then redirects back to a clean URL so refreshing the page can't
     * replay the action.
     *
     * @return void
     */
    public function handle_review_notice_action() {
        if ( ! isset( $_GET['dpessr-review-action'], $_GET['_wpnonce'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'dpessr_review_notice' ) ) {
            return;
        }

        $action = sanitize_text_field( wp_unslash( $_GET['dpessr-review-action'] ) );

        switch ( $action ) {
            case 'rated':
            case 'already-rated':
                update_option( 'dpessr_review_notice_status', 'dismissed' );
                break;
            case 'later':
                // Snooze: reset the clock to right now, so the 7-day wait starts over from this moment.
                update_option( 'dpessr_activated_time', time() );
                break;
        }

        $redirect_url = remove_query_arg( [ 'dpessr-review-action', '_wpnonce' ] );
        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Render a one-time, time-delayed "please rate us" admin notice.
     *
     * Only shown to admins, only after the plugin has been active for a
     * respectful amount of time, and never again once dismissed.
     *
     * @return void
     */
    public function maybe_render_review_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $screen = get_current_screen();

        if ( ! $screen || $screen->id !== 'toplevel_page_dp-easy-social-share' ) {
            return;
        }

        if ( get_option( 'dpessr_review_notice_status' ) === 'dismissed' ) {
            return;
        }

        $activated_time = get_option( 'dpessr_activated_time' );

        // If we don't know when it was activated (e.g. upgraded from an older version), start counting from now.
        if ( ! $activated_time ) {
            update_option( 'dpessr_activated_time', time() );
            return;
        }

        if ( ( time() - (int) $activated_time ) < ( 7 * DAY_IN_SECONDS ) ) {
            return;
        }

        $rate_mark_url    = add_query_arg( [
            'dpessr-review-action' => 'rated',
            '_wpnonce'             => wp_create_nonce( 'dpessr_review_notice' ),
        ] );
        $already_url = add_query_arg( [
            'dpessr-review-action' => 'already-rated',
            '_wpnonce'             => wp_create_nonce( 'dpessr_review_notice' ),
        ] );
        $later_url  = add_query_arg( [
            'dpessr-review-action' => 'later',
            '_wpnonce'             => wp_create_nonce( 'dpessr_review_notice' ),
        ] );

        ?>
        <div class="notice notice-info dpessr-review-notice">
            <p>
                <?php
                printf(
                    /* translators: %s: plugin name */
                    esc_html__( "You've been using %s for a week now, glad it's working for you! Could you spare a moment to leave a quick review? It really helps.", 'dp-easy-social-share' ),
                    '<strong>DP Easy Social Share</strong>'
                );
                ?>
            </p>
            <p>
                <a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/dp-easy-social-share/reviews/?filter=5#new-post' ); ?>" class="button button-primary dpessr-rate-link" target="_blank" rel="noopener noreferrer" data-mark-url="<?php echo esc_url( $rate_mark_url ); ?>">
                    <?php esc_html_e( '★★★★★ Sure, I\'ll rate it!', 'dp-easy-social-share' ); ?>
                </a>
                <a href="<?php echo esc_url( $already_url ); ?>" class="button">
                    <?php esc_html_e( "I've already left a review", 'dp-easy-social-share' ); ?>
                </a>
                <a href="<?php echo esc_url( $later_url ); ?>" class="button">
                    <?php esc_html_e( 'Maybe later', 'dp-easy-social-share' ); ?>
                </a>
            </p>
        </div>
        <?php
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
     * Hook into the current screen to add custom header.
     *
     * @param WP_Screen $screen The current screen object.
     * @return void
     */
    public function current_screen( $screen ) {
        // if the current screen is our plugin's settings page
        if ( $screen->id === 'toplevel_page_dp-easy-social-share' ) {
            add_action( 'in_admin_header', [$this, 'render_admin_header'] );
        }
        /* // You can enqueue scripts or styles specific to this page here
        wp_enqueue_style( 'dpessr-admin-style', DPESSR_PLUGIN_URL . 'assets/css/admin-style.css', false, DPESSR_PLUGIN_VERSION ); */
    }

    /**
     * Enqueue admin-specific styles and scripts.
     *
     * @param string $hook The current admin page.
     * @return void
     */
    public function enqueue_admin_assets( $hook ) {
        $screen = get_current_screen();
        
        if ( ! $screen ) {
            return;
        }

        // Check only your plugin settings page
        if ( $screen->id === 'toplevel_page_dp-easy-social-share' ) {
            // Load admin styles
            wp_enqueue_style( 'dpess-admin', DPESSR_PLUGIN_URL . 'assets/admin/css/admin.css', false, DPESSR_PLUGIN_VERSION);

            // Load admin JS
            wp_enqueue_script( 'dpess-admin', DPESSR_PLUGIN_URL . 'assets/admin/js/admin.js', [ 'jquery' ], DPESSR_PLUGIN_VERSION, true );
        }
    }

    /**
     * Render the custom admin header.
     *
     * @return void
     */
    public function render_admin_header() {
        ?>
        <div class="dpessr-admin-header">
            <div class="dpessr-admin-header-inner">
                <div class="dpessr-header-logo">
                    <img src="<?php echo DPESSR_PLUGIN_URL . 'assets/admin/images/logo.svg'; ?>" alt="DP Easy Social Share - Logo" class="dpessr-logo" />
                    <span class="dpessr-admin-header-title"><?php esc_html_e( 'DP Easy Social Share', 'dp-easy-social-share' ); ?></span>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render the plugin settings page.
     *
     * @return void
     */
    public function render_admin_page() {
        ?>
        <div class="wrap dpessr-admin">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'DP Easy Social Share Settings', 'dp-easy-social-share' ); ?></h1>

            <?php  settings_errors(); ?>

            <div class="dpessr-admin-box">
                <!-- Tabs -->
                <div class="dpessr-tabs">
                    <a href="#dpessr-general" class="dpessr-tab active">General</a>
                    <a href="#dpessr-inline" class="dpessr-tab">Inline Buttons</a>
                    <a href="#dpessr-floating" class="dpessr-tab">Floating Sidebar</a>
                </div>

                <form method="post" action="options.php" class="dpessr-form">
                    <?php settings_fields( 'dpessr_share_settings_group' ); ?>

                    <!-- Tab Contents -->
                    <div class="dpessr-tab-content">
                        <div class="dpessr-tab-panel active" id="dpessr-general">
                            <?php do_settings_sections( 'dpessr_share_general_page' ); // Output settings sections and fields. ?>
                        </div>

                        <div class="dpessr-tab-panel" id="dpessr-inline">
                            <?php do_settings_sections( 'dpessr_share_inline_page' ); // Output settings sections and fields. ?>
                        </div>

                        <div class="dpessr-tab-panel" id="dpessr-floating">
                            <?php do_settings_sections( 'dpessr_share_floating_page' ); // Output settings sections and fields. ?>
                        </div>
                    </div>

                    <?php submit_button(); ?>
                </form>
            </div>
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
        
        // General Settings Section
        add_settings_section(
            'dpessr_share_general_section',
            __( 'General Settings', 'dp-easy-social-share' ),
            null,
            'dpessr_share_general_page'
        );

        add_settings_field(
            'dpessr_social_networks',
            __( 'Social Icons', 'dp-easy-social-share' ),
            [$this, 'render_social_networks_field'],
            'dpessr_share_general_page',
            'dpessr_share_general_section'
        );

        // Inline Share Settings
        add_settings_section(
            'dpessr_share_inline_section',
            __( 'Inline Buttons Settings', 'dp-easy-social-share' ),
            null,
            'dpessr_share_inline_page'
        );

        add_settings_field(
            'dpessr_inline_enabled',
            __( 'Enable Inline Buttons', 'dp-easy-social-share' ),
            [$this, 'render_inline_enabled_field'],
            'dpessr_share_inline_page',
            'dpessr_share_inline_section'
        );

        add_settings_field(
            'dpessr_inline_post_types',
            __( 'Post Types', 'dp-easy-social-share' ),
            [$this, 'render_inline_post_types_field'],
            'dpessr_share_inline_page',
            'dpessr_share_inline_section'
        );

        add_settings_field(
            'dpessr_inline_display_position',
            __('Display Position', 'dp-easy-social-share'),
            [$this, 'render_inline_display_position_field'],
            'dpessr_share_inline_page',
            'dpessr_share_inline_section'
        );

        // Floating Sidebar Settings
        add_settings_section(
            'dpessr_share_floating_section',
            __( 'Floating Sidebar Settings', 'dp-easy-social-share' ),
            null,
            'dpessr_share_floating_page'
        );

        add_settings_field(
            'dpessr_floating_enabled',
            __( 'Enable Floating Sidebar', 'dp-easy-social-share' ),
            [$this, 'render_floating_enabled_field'],
            'dpessr_share_floating_page',
            'dpessr_share_floating_section'
        );

        add_settings_field(
            'dpessr_floating_post_types',
            __( 'Post Types', 'dp-easy-social-share' ),
            [$this, 'render_floating_post_types_field'],
            'dpessr_share_floating_page',
            'dpessr_share_floating_section'
        );

        add_settings_field(
            'dpessr_floating_display_position',
            __('Display Position', 'dp-easy-social-share'),
            [$this, 'render_floating_display_position_field'],
            'dpessr_share_floating_page',
            'dpessr_share_floating_section'
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

        $reset_options = DPESSR_Social_Share_Helper::get_reset_share_settings();

        $sanitized['general']   = isset( $input['general'] ) ? $this->sanitize_general_settings( $input['general'] ) : $reset_options['general'];
        $sanitized['inline']    = isset( $input['inline'] ) ? $this->sanitize_inline_settings( $input['inline'] ) : $reset_options['inline'];
        $sanitized['floating']  = isset( $input['floating'] ) ? $this->sanitize_floating_settings( $input['floating'] ) : $reset_options['floating'];

        return $sanitized;
    }

    /**
     * Sanitize general settings.
     *
     * @param array $input User input.
     * @return array Sanitized input.
     */
    private function sanitize_general_settings( $input ) {
        $sanitized = [];

        if ( isset( $input['networks'] ) && is_array( $input['networks'] ) ) {
            $sanitized['networks'] = array_map( 'sanitize_text_field', $input['networks'] );
        }

        return $sanitized;
    }

    /**
     * Sanitize inline settings.
     *
     * @param array $input User input.
     * @return array Sanitized input.
     */
    private function sanitize_inline_settings( $input ) {
        $sanitized = [];

        $sanitized['enabled'] = isset( $input['enabled'] ) ? 1 : 0;

        if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
            $sanitized['post_types'] = array_map( 'sanitize_text_field', $input['post_types'] );
        } else {
            $sanitized['post_types'] = [];
        }
        
        if ( isset( $input['display_position'] ) && !empty($input['display_position']) && isset( $this->inline_positions[ $input['display_position'] ] ) ) {
            $sanitized['display_position'] = sanitize_text_field($input['display_position']);
        } else {
            $sanitized['display_position'] = 'below';
        }

        return $sanitized;
    }

    /**
     * Sanitize floating settings.
     *
     * @param array $input User input.
     * @return array Sanitized input.
     */
    private function sanitize_floating_settings( $input ) {
        $sanitized = [];

        $sanitized['enabled'] = isset( $input['enabled'] ) ? 1 : 0;

        if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
            $sanitized['post_types'] = array_map( 'sanitize_text_field', $input['post_types'] );
        } else {
            $sanitized['post_types'] = [];
        }

        if ( isset( $input['display_position'] ) && !empty($input['display_position']) && isset( $this->floating_positions[ $input['display_position'] ] ) ) {
            $sanitized['display_position'] = sanitize_text_field( $input['display_position'] );
        } else {
            $sanitized['display_position'] = 'left';
        }

        return $sanitized; 
    }

    /**
     * Render social icons checkboxes field.
     *
     * @return void
     */
    public function render_social_networks_field() {
        $settings   = get_option( 'dpessr_share_settings' );
        $options    = isset($settings['general']) ? $settings['general'] : [];
        $icons      = DPESSR_Social_Share_Helper::get_social_icons();

        echo '<div class="dpessr-form-checkboxes">';
        foreach ( $icons as $key => $icon ) {
            $checked    = isset( $options['networks'] ) && in_array( $key, $options['networks'], true );
            $field_id   = 'dpessr-general-network-' . esc_attr( $key );
            echo '<div class="dpessr-form-checkbox">
                <input type="checkbox" class="dpessr-form-check-input" name="dpessr_share_settings[general][networks][]" id="' . $field_id . '" value="' . esc_attr( $key ) . '"' . checked( $checked, true, false ) . '>
                <label class="dpessr-form-check-label" for="' . $field_id . '" >'  . esc_html( ucfirst( $icon['name'] ) ) . '</label>
                </div>';
        }
        echo '</div>';
    }

    /**
     * Render inline enabled checkbox field.
     *
     * @return void
     */
    public function render_inline_enabled_field() {
        $settings   = get_option( 'dpessr_share_settings' );
        $options    = isset($settings['inline']) ? $settings['inline'] : [];
        $enabled    = isset( $options['enabled'] ) ? (bool) $options['enabled'] : false;

        echo '<div class="dpessr-switch">
            <input type="checkbox" id="dpessr-inline-enabled" class="dpessr-switch-input" name="dpessr_share_settings[inline][enabled]" value="1"' . checked( $enabled, true, false ) . '/>
            <label for="dpessr-inline-enabled" class="dpessr-switch-label"></label>
        </div>';
    }

    /**
     * Render post types checkboxes field for inline display.
     *
     * @return void
     */
    public function render_inline_post_types_field() {
        $settings   = get_option( 'dpessr_share_settings' );
        $options    = isset($settings['inline']) ? $settings['inline'] : [];
        $post_types = get_post_types( [ 'public' => true ], 'names' );

        echo '<div class="dpessr-form-checkboxes dpessr-inline-checkboxes">';
        foreach ( $post_types as $post_type ) {
            $checked    = isset( $options['post_types'] ) && in_array( $post_type, $options['post_types'], true );
            $field_id   = 'dpessr-inline-post-type-' . esc_attr( $post_type );
            echo '<div class="dpessr-form-checkbox">
                <input type="checkbox" class="dpessr-form-check-input" name="dpessr_share_settings[inline][post_types][]" id="' . $field_id . '" value="' . esc_attr( $post_type ) . '"' . checked( $checked, true, false ) . '>' .
                '<label class="dpessr-form-check-label" for="' . $field_id . '" >'  . esc_html( ucfirst( $post_type ) ) . '</label>' .
                '</div>';
        }
        echo '</div>';
    }

    /**
     * Render Display Position radio field for inline display.
     *
     * @return void
     */
    public function render_inline_display_position_field() {
        $settings   = get_option( 'dpessr_share_settings' );
        $options    = isset($settings['inline']) ? $settings['inline'] : [];
        $position   = isset( $options['display_position'] ) && !empty( $options['display_position'] ) && isset( $this->inline_positions[ $options['display_position'] ] ) ? $options['display_position'] : 'below';

        echo '<select name="dpessr_share_settings[inline][display_position]" class="dpessr-form-control">';
        foreach ($this->inline_positions as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($position, $key, false) . '>'
                . esc_html($label) . 
                '</option>';
        }
        echo '</select>';
    }
    
    /**
     * Render floating enabled checkbox field.
     *
     * @return void
     */
    public function render_floating_enabled_field() {
        $settings   = get_option( 'dpessr_share_settings' );
        $options    = isset($settings['floating']) ? $settings['floating'] : [];
        $enabled    = isset( $options['enabled'] ) ? (bool) $options['enabled'] : false;

        echo '<div class="dpessr-switch">
            <input type="checkbox" id="dpessr-floating-enabled" class="dpessr-switch-input" name="dpessr_share_settings[floating][enabled]" value="1"' . checked( $enabled, true, false ) . '/>
            <label for="dpessr-floating-enabled" class="dpessr-switch-label"></label>
        </div>';
    }

    /**
     * Render post types checkboxes field for floating sidebar.
     *
     * @return void
     */
    public function render_floating_post_types_field() {
        $settings   = get_option( 'dpessr_share_settings' );
        $options    = isset($settings['floating']) ? $settings['floating'] : [];
        $post_types = get_post_types( [ 'public' => true ], 'names' );

        echo '<div class="dpessr-form-checkboxes dpessr-inline-checkboxes">';
        foreach ( $post_types as $post_type ) {
            $checked    = isset( $options['post_types'] ) && in_array( $post_type, $options['post_types'], true );
            $field_id   = 'dpessr-floating-post-type-' . esc_attr( $post_type );
            echo '<div class="dpessr-form-checkbox">
                <input type="checkbox" class="dpessr-form-check-input" name="dpessr_share_settings[floating][post_types][]" id="' . $field_id . '" value="' . esc_attr( $post_type ) . '"' . checked( $checked, true, false ) . '>' .
                '<label class="dpessr-form-check-label" for="' . $field_id . '" >'  . esc_html( ucfirst( $post_type ) ) . '</label>' .
                '</div>';
        }
        echo '</div>';
    }

    /**
     * Render Display Position radio field for inline display.
     *
     * @return void
     */
    public function render_floating_display_position_field() {
        $settings   = get_option( 'dpessr_share_settings' );
        $options    = isset($settings['floating']) ? $settings['floating'] : [];
        $position   = isset( $options['display_position'] ) && !empty( $options['display_position'] ) && isset( $this->floating_positions[ $options['display_position'] ] ) ? $options['display_position'] : 'left';

        echo '<select name="dpessr_share_settings[floating][display_position]" class="dpessr-form-control">';
        foreach ($this->floating_positions as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($position, $key, false) . '>'
                . esc_html($label) . 
                '</option>';
        }
        echo '</select>';
    }

    /**
     * Get positions.
     *
     * @return array
     */
    public function get_inline_positions() {
        $positions = [
            'above'     => __('Above Post', 'dp-easy-social-share'), 
            'below'     => __('Below Post', 'dp-easy-social-share')
        ];

        return $positions;
    }

    /**
     * Get floating sidebar positions.
     *
     * @return array
     */
    public function get_floating_positions() {
        $positions = [
            'left'  => __('Left', 'dp-easy-social-share'), 
            'right' => __('Right', 'dp-easy-social-share')
        ];

        return $positions;
    }
}