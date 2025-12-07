<?php /** @noinspection ALL */
/**
 * Plugin Name: Topbar Countdown Notice
 * Plugin URI: https://github.com/antikton/topbar-countdown-notice
 * Description: Display a customizable top bar with optional countdown timer and scheduling capabilities.
 * Version: 1.0.8
 * Author: Eduardo Pagán
 * Author URI: https://github.com/antikton
 * Text Domain: topbar-countdown-notice
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Security: Prevent direct access
defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class
 */
class Topbar_Countdown_Notice {

	/**
	 * Plugin version
	 */
	const VERSION = '1.0.8';

	/**
	 * Option name for settings
	 */
	const OPTION_NAME = 'tcn_settings';

	/**
	 * Text domain for translations
	 */
	const TEXT_DOMAIN = 'topbar-countdown-notice';

	/**
	 * Initialize the plugin
     * @noinspection PhpUndefinedFunctionInspection
     */
	public static function init() {
		// Load text domain
		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
		
		// Add AJAX handler for getting server time
		add_action( 'wp_ajax_tcn_get_server_time', array( __CLASS__, 'ajax_get_server_time' ) );
		
		// Admin hooks
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		
		// Frontend hooks
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_frontend_assets' ) );
		add_action( 'wp_body_open', array( __CLASS__, 'display_topbar' ), 1 );
		add_action( 'wp_footer', array( __CLASS__, 'fallback_display_topbar' ) );
	}

	/**
	 * Load plugin text domain
	 */
    public static function load_textdomain() {
        // Load translations
        $locale = apply_filters( 'topbar_countdown_notice_locale', is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale(), 'topbar-countdown-notice' );

        // Load .mo file from wp-content/languages/plugins/ first
        $mofile = WP_LANG_DIR . '/plugins/' . 'topbar-countdown-notice' . '-' . $locale . '.mo';

        if ( file_exists( $mofile ) ) {
            return load_textdomain( 'topbar-countdown-notice', $mofile );
        }

        // Fallback to plugin languages directory
        $plugin_dir = dirname( plugin_basename( __FILE__ ) );
        $mofile = WP_PLUGIN_DIR . '/' . $plugin_dir . '/languages/' . 'topbar-countdown-notice' . '-' . $locale . '.mo';

        if ( file_exists( $mofile ) ) {
            return load_textdomain( 'topbar-countdown-notice', $mofile );
        }

        // WordPress.org automatically loads translations for plugins
        // No need for load_plugin_textdomain since WordPress 4.6+
    }


    /**
	 * Add admin menu page
	 */
	public static function add_admin_menu() {
		add_options_page(
			__( 'Topbar Countdown Notice', 'topbar-countdown-notice' ),
			__( 'Topbar Countdown', 'topbar-countdown-notice' ),
			'manage_options',
			'topbar-countdown-notice',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Register plugin settings
	 */
	public static function register_settings() {
		register_setting(
			'tcn_settings_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			)
		);

		// Main section
		add_settings_section(
			'tcn_main_section',
			__( 'General Settings', 'topbar-countdown-notice' ),
			null,
			'topbar-countdown-notice'
		);

		// Active checkbox
		add_settings_field(
			'active',
			__( 'Activate Bar', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'topbar-countdown-notice',
			'tcn_main_section',
			array( 'field' => 'active', 'label' => __( 'Show the top bar', 'topbar-countdown-notice' ) )
		);

		// Scheduling section
		add_settings_section(
			'tcn_schedule_section',
			__( 'Scheduling', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_schedule_section_description' ),
			'topbar-countdown-notice'
		);

		// Start datetime
		add_settings_field(
			'start_datetime',
			__( 'Start Date/Time', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_datetime_field' ),
			'topbar-countdown-notice',
			'tcn_schedule_section',
			array( 'field' => 'start_datetime' )
		);

		// End datetime
		add_settings_field(
			'end_datetime',
			__( 'End Date/Time', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_datetime_field' ),
			'topbar-countdown-notice',
			'tcn_schedule_section',
			array( 'field' => 'end_datetime' )
		);

		// Finish action section
		add_settings_section(
			'tcn_finish_section',
			__( 'Action on Finish', 'topbar-countdown-notice' ),
			null,
			'topbar-countdown-notice'
		);

		// Finish action radio
		add_settings_field(
			'finish_action',
			__( 'When End Date/Time is Reached', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_finish_action_field' ),
			'topbar-countdown-notice',
			'tcn_finish_section'
		);

		// Alternative text
		add_settings_field(
			'alternative_text',
			__( 'Alternative Text', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_editor_field' ),
			'topbar-countdown-notice',
			'tcn_finish_section',
			array( 'field' => 'alternative_text' )
		);

		// Alternative link
		add_settings_field(
			'alternative_link',
			__( 'Alternative Link', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_url_field' ),
			'topbar-countdown-notice',
			'tcn_finish_section',
			array( 'field' => 'alternative_link', 'new_tab_field' => 'alternative_link_new_tab' )
		);

		// Alternative link button text
		add_settings_field(
			'alternative_link_text',
			__( 'Alternative Link Button Text', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_text_field' ),
			'topbar-countdown-notice',
			'tcn_finish_section',
			array( 'field' => 'alternative_link_text', 'placeholder' => __( 'Learn More', 'topbar-countdown-notice' ) )
		);

		// Alternative colors mode
		add_settings_field(
			'alternative_colors_mode',
			__( 'Alternative Colors', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_alternative_colors_mode_field' ),
			'topbar-countdown-notice',
			'tcn_finish_section'
		);

		// Alternative background color
		add_settings_field(
			'alternative_bg_color',
			__( 'Alternative Background Color', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_color_field' ),
			'topbar-countdown-notice',
			'tcn_finish_section',
			array( 'field' => 'alternative_bg_color' )
		);

		// Alternative text color
		add_settings_field(
			'alternative_text_color',
			__( 'Alternative Text Color', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_color_field' ),
			'topbar-countdown-notice',
			'tcn_finish_section',
			array( 'field' => 'alternative_text_color' )
		);

		// Content section
		add_settings_section(
			'tcn_content_section',
			__( 'Bar Content', 'topbar-countdown-notice' ),
			null,
			'topbar-countdown-notice'
		);

		// Main content
		add_settings_field(
			'content',
			__( 'Main Content', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_editor_field' ),
			'topbar-countdown-notice',
			'tcn_content_section',
			array( 'field' => 'content' )
		);

		// Main link
		add_settings_field(
			'main_link',
			__( 'Main Link', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_url_field' ),
			'topbar-countdown-notice',
			'tcn_content_section',
			array( 'field' => 'main_link', 'new_tab_field' => 'main_link_new_tab' )
		);

		// Main link button text
		add_settings_field(
			'main_link_text',
			__( 'Main Link Button Text', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_text_field' ),
			'topbar-countdown-notice',
			'tcn_content_section',
			array( 'field' => 'main_link_text', 'placeholder' => __( 'Learn More', 'topbar-countdown-notice' ) )
		);

		// Countdown section
		add_settings_section(
			'tcn_countdown_section',
			__( 'Countdown Timer', 'topbar-countdown-notice' ),
			null,
			'topbar-countdown-notice'
		);

		// Countdown active
		add_settings_field(
			'countdown_active',
			__( 'Activate Countdown', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'topbar-countdown-notice',
			'tcn_countdown_section',
			array( 'field' => 'countdown_active', 'label' => __( 'Show countdown timer', 'topbar-countdown-notice' ) )
		);

		// Countdown target mode
		add_settings_field(
			'countdown_target_mode',
			__( 'Countdown Target', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_countdown_target_mode_field' ),
			'topbar-countdown-notice',
			'tcn_countdown_section'
		);

		// Countdown target custom
		add_settings_field(
			'countdown_target_custom',
			__( 'Custom Target Date/Time', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_datetime_field' ),
			'topbar-countdown-notice',
			'tcn_countdown_section',
			array( 'field' => 'countdown_target_custom' )
		);

		// Countdown prefix (TinyMCE editor)
		add_settings_field(
			'countdown_prefix',
			__( 'Countdown Prefix Text', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_editor_field' ),
			'topbar-countdown-notice',
			'tcn_countdown_section',
			array( 'field' => 'countdown_prefix' )
		);

		// Show seconds
		add_settings_field(
			'countdown_show_seconds',
			__( 'Show Seconds', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'topbar-countdown-notice',
			'tcn_countdown_section',
			array( 'field' => 'countdown_show_seconds', 'label' => __( 'Display seconds in countdown', 'topbar-countdown-notice' ) )
		);

		// Appearance section
		add_settings_section(
			'tcn_appearance_section',
			__( 'Appearance', 'topbar-countdown-notice' ),
			null,
			'topbar-countdown-notice'
		);

		// Background color
		add_settings_field(
			'bg_color',
			__( 'Background Color', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_color_field' ),
			'topbar-countdown-notice',
			'tcn_appearance_section',
			array( 'field' => 'bg_color' )
		);

		// Text color
		add_settings_field(
			'text_color',
			__( 'Text Color', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_color_field' ),
			'topbar-countdown-notice',
			'tcn_appearance_section',
			array( 'field' => 'text_color' )
		);

		// Padding Top
		add_settings_field(
			'padding_top',
			__( 'Padding Top (px)', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_number_field' ),
			'topbar-countdown-notice',
			'tcn_appearance_section',
			array( 'field' => 'padding_top', 'default' => '12' )
		);

		// Padding Bottom
		add_settings_field(
			'padding_bottom',
			__( 'Padding Bottom (px)', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_number_field' ),
			'topbar-countdown-notice',
			'tcn_appearance_section',
			array( 'field' => 'padding_bottom', 'default' => '12' )
		);

		// Padding Left
		add_settings_field(
			'padding_left',
			__( 'Padding Left (px)', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_number_field' ),
			'topbar-countdown-notice',
			'tcn_appearance_section',
			array( 'field' => 'padding_left', 'default' => '20' )
		);

		// Padding Right
		add_settings_field(
			'padding_right',
			__( 'Padding Right (px)', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_number_field' ),
			'topbar-countdown-notice',
			'tcn_appearance_section',
			array( 'field' => 'padding_right', 'default' => '20' )
		);

		// Custom CSS
		add_settings_field(
			'custom_css',
			__( 'Custom CSS', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_custom_css_field' ),
			'topbar-countdown-notice',
			'tcn_appearance_section'
		);

		// Debug Mode
		add_settings_field(
			'debug_mode',
			__( 'Debug Mode', 'topbar-countdown-notice' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'topbar-countdown-notice',
			'tcn_appearance_section',
			array( 'field' => 'debug_mode', 'label' => __( 'Show console.log messages in browser (for debugging)', 'topbar-countdown-notice' ) )
		);
	}

	/**
	 * Render schedule section description
	 */
	public static function render_schedule_section_description() {
		echo '<p>' . esc_html__( 'Configure when the bar should be visible. Leave both empty to always show the bar.', 'topbar-countdown-notice' ) . '</p>';
	}

	/**
	 * Render checkbox field
	 */
	public static function render_checkbox_field( $args ) {
		$settings = self::get_settings();
		$field = $args['field'];
		$label = isset( $args['label'] ) ? $args['label'] : '';
		$checked = ! empty( $settings[ $field ] );
		
		printf(
			'<label><input type="checkbox" name="%s[%s]" value="1" %s> %s</label>',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $field ),
			checked( $checked, true, false ),
			esc_html( $label )
		);
	}

	/**
	 * Render datetime field
	 */
	public static function render_datetime_field( $args ) {
		$settings = self::get_settings();
		$field = $args['field'];
		$value = isset( $settings[ $field ] ) ? $settings[ $field ] : '';
		
		// Convert timestamp to datetime-local format in site timezone
		if ( ! empty( $value ) && is_numeric( $value ) ) {
			$datetime = new DateTime( '@' . $value );
			$datetime->setTimezone( wp_timezone() );
			$value = $datetime->format( 'Y-m-d\TH:i' );
		}
		
		printf(
			'<input type="datetime-local" name="%s[%s]" value="%s" class="regular-text">',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $field ),
			esc_attr( $value )
		);
	}

	/**
	 * Render finish action field
	 */
	public static function render_finish_action_field() {
		$settings = self::get_settings();
		$value = isset( $settings['finish_action'] ) ? $settings['finish_action'] : 'hide';
		
		?>
		<label>
			<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[finish_action]" value="hide" <?php checked( $value, 'hide' ); ?>>
			<?php esc_html_e( 'Hide the bar', 'topbar-countdown-notice' ); ?>
		</label>
		<br>
		<label>
			<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[finish_action]" value="show_alternative" <?php checked( $value, 'show_alternative' ); ?>>
			<?php esc_html_e( 'Show alternative content', 'topbar-countdown-notice' ); ?>
		</label>
		<?php
	}

	/**
	 * Render editor field
	 */
	public static function render_editor_field( $args ) {
		$settings = self::get_settings();
		$field = $args['field'];
		$value = isset( $settings[ $field ] ) ? $settings[ $field ] : '';
		
		wp_editor(
			$value,
			$field . '_editor',
			array(
				'textarea_name' => self::OPTION_NAME . '[' . $field . ']',
				'textarea_rows' => 5,
				'media_buttons' => false,
				'teeny'         => true,
				'tinymce'       => array(
					'toolbar1' => 'bold,italic,link,unlink,bullist,numlist,undo,redo',
				),
			)
		);
	}

	/**
	 * Render URL field
	 */
	public static function render_url_field( $args ) {
		$settings = self::get_settings();
		$field = $args['field'];
		$new_tab_field = $args['new_tab_field'];
		$url_value = isset( $settings[ $field ] ) ? $settings[ $field ] : '';
		$new_tab_checked = ! empty( $settings[ $new_tab_field ] );
		
		printf(
			'<input type="url" name="%s[%s]" value="%s" class="regular-text" placeholder="https://"><br>',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $field ),
			esc_url( $url_value )
		);
		
		printf(
			'<label><input type="checkbox" name="%s[%s]" value="1" %s> %s</label>',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $new_tab_field ),
			checked( $new_tab_checked, true, false ),
			esc_html__( 'Open in new tab', 'topbar-countdown-notice' )
		);
	}

	/**
	 * Render text field
	 */
	public static function render_text_field( $args ) {
		$settings = self::get_settings();
		$field = $args['field'];
		$value = isset( $settings[ $field ] ) ? $settings[ $field ] : '';
		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
		
		printf(
			'<input type="text" name="%s[%s]" value="%s" class="regular-text" placeholder="%s">',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $field ),
			esc_attr( $value ),
			esc_attr( $placeholder )
		);
	}

	/**
	 * Render number field
	 */
	public static function render_number_field( $args ) {
		$settings = self::get_settings();
		$field = $args['field'];
		$default = isset( $args['default'] ) ? $args['default'] : '0';
		$value = isset( $settings[ $field ] ) ? $settings[ $field ] : $default;
		
		printf(
			'<input type="number" name="%s[%s]" value="%s" class="small-text" min="0" step="1">',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $field ),
			esc_attr( $value )
		);
	}

	/**
	 * Render HTML text field (allows HTML formatting)
	 */
	public static function render_html_text_field( $args ) {
		$settings = self::get_settings();
		$field = $args['field'];
		$value = isset( $settings[ $field ] ) ? $settings[ $field ] : '';
		$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
		
		printf(
			'<input type="text" name="%s[%s]" value="%s" class="regular-text" placeholder="%s">',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $field ),
			esc_attr( $value ),
			esc_attr( $placeholder )
		);
		echo '<p class="description">' . esc_html__( 'You can use HTML tags like <strong>, <em>, <span>, etc.', 'topbar-countdown-notice' ) . '</p>';
	}

	/**
	 * Render countdown target mode field
	 */
	public static function render_countdown_target_mode_field() {
		$settings = self::get_settings();
		$value = isset( $settings['countdown_target_mode'] ) ? $settings['countdown_target_mode'] : 'end_date';
		
		?>
		<label>
			<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[countdown_target_mode]" value="end_date" <?php checked( $value, 'end_date' ); ?>>
			<?php esc_html_e( 'Same as End Date/Time', 'topbar-countdown-notice' ); ?>
		</label>
		<br>
		<label>
			<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[countdown_target_mode]" value="custom" <?php checked( $value, 'custom' ); ?>>
			<?php esc_html_e( 'Custom Date/Time', 'topbar-countdown-notice' ); ?>
		</label>
		<?php
	}

	/**
	 * Render alternative colors mode field
	 */
	public static function render_alternative_colors_mode_field() {
		$settings = self::get_settings();
		$value = isset( $settings['alternative_colors_mode'] ) ? $settings['alternative_colors_mode'] : 'same';
		
		?>
		<label>
			<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[alternative_colors_mode]" value="same" <?php checked( $value, 'same' ); ?>>
			<?php esc_html_e( 'Same as main colors', 'topbar-countdown-notice' ); ?>
		</label>
		<br>
		<label>
			<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[alternative_colors_mode]" value="custom" <?php checked( $value, 'custom' ); ?>>
			<?php esc_html_e( 'Custom colors', 'topbar-countdown-notice' ); ?>
		</label>
		<?php
	}

	/**
	 * Render custom CSS field
	 */
	public static function render_custom_css_field() {
		$settings = self::get_settings();
		$value = isset( $settings['custom_css'] ) ? $settings['custom_css'] : '';
		
		?>
		<textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[custom_css]" rows="10" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Add custom CSS to style the top bar. Available classes:', 'topbar-countdown-notice' ); ?><br>
			<code>.tcn-topbar</code> - <?php esc_html_e( 'Main bar container', 'topbar-countdown-notice' ); ?><br>
			<code>.tcn-topbar-inner</code> - <?php esc_html_e( 'Inner content wrapper', 'topbar-countdown-notice' ); ?><br>
			<code>.tcn-content</code> - <?php esc_html_e( 'Main content area', 'topbar-countdown-notice' ); ?><br>
			<code>.tcn-countdown-wrapper</code> - <?php esc_html_e( 'Countdown container', 'topbar-countdown-notice' ); ?><br>
			<code>.tcn-countdown-prefix</code> - <?php esc_html_e( 'Countdown prefix text', 'topbar-countdown-notice' ); ?><br>
			<code>.tcn-countdown</code> - <?php esc_html_e( 'Countdown timer', 'topbar-countdown-notice' ); ?><br>
			<code>.tcn-link</code> - <?php esc_html_e( 'Action button/link', 'topbar-countdown-notice' ); ?>
		</p>
		<?php
	}

	/**
	 * Render color field
	 */
	public static function render_color_field( $args ) {
		$settings = self::get_settings();
		$field = $args['field'];
		$value = isset( $settings[ $field ] ) ? $settings[ $field ] : '';
		
		// Default colors
		$defaults = array(
			'bg_color'   => '#2c3e50',
			'text_color' => '#ffffff',
		);
		
		if ( empty( $value ) && isset( $defaults[ $field ] ) ) {
			$value = $defaults[ $field ];
		}
		
		printf(
			'<input type="text" name="%s[%s]" value="%s" class="tcn-color-picker">',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $field ),
			esc_attr( $value )
		);
	}

	/**
	 * Render admin page
	 */
	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		global $wp_settings_sections, $wp_settings_fields;
		$page = 'topbar-countdown-notice';
		
		?>
		<div class="wrap tcn-settings-wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<hr class="wp-header-end">
						<?php 
				// Display settings errors with a unique ID to prevent duplicates
				$settings_errors = get_settings_errors('tcn_settings_group');
				if (!empty($settings_errors)) {
					settings_errors('tcn_settings_group');
				}
				?>
			
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					
					<!-- Main Content (Left Column) -->
					<div id="post-body-content">
						
						<!-- Tabs Navigation -->
						<h2 class="nav-tab-wrapper tcn-nav-tab-wrapper">
							<a href="#tab-general" class="nav-tab nav-tab-active">
								<span class="dashicons dashicons-admin-settings"></span> 
								<?php esc_html_e( 'General & Schedule', 'topbar-countdown-notice' ); ?>
							</a>
							<a href="#tab-content" class="nav-tab">
								<span class="dashicons dashicons-text-page"></span> 
								<?php esc_html_e( 'Content & Countdown', 'topbar-countdown-notice' ); ?>
							</a>
							<a href="#tab-finish" class="nav-tab">
								<span class="dashicons dashicons-flag"></span> 
								<?php esc_html_e( 'Action on Finish', 'topbar-countdown-notice' ); ?>
							</a>
							<a href="#tab-appearance" class="nav-tab">
								<span class="dashicons dashicons-art"></span> 
								<?php esc_html_e( 'Appearance', 'topbar-countdown-notice' ); ?>
							</a>
						</h2>

						<form method="post" action="options.php" class="tcn-main-form">
							<?php settings_fields( 'tcn_settings_group' ); ?>

							<div class="tcn-tab-container">
								<!-- Tab: General & Schedule -->
								<div id="tab-general" class="tcn-tab-content">
									<?php 
									self::render_sections_by_id( $page, array( 'tcn_main_section', 'tcn_schedule_section' ) );
									?>
								</div>
								
								<!-- Tab: Content & Countdown -->
								<div id="tab-content" class="tcn-tab-content" style="display:none;">
									<?php 
									self::render_sections_by_id( $page, array( 'tcn_content_section', 'tcn_countdown_section' ) );
									?>
								</div>

								<!-- Tab: Action on Finish -->
								<div id="tab-finish" class="tcn-tab-content" style="display:none;">
									<?php 
									self::render_sections_by_id( $page, array( 'tcn_finish_section' ) );
									?>
								</div>

								<!-- Tab: Appearance -->
								<div id="tab-appearance" class="tcn-tab-content" style="display:none;">
									<?php 
									self::render_sections_by_id( $page, array( 'tcn_appearance_section' ) );
									?>
								</div>
							</div>

							<div class="tcn-submit-wrapper">
								<?php submit_button( __( 'Save Changes', 'topbar-countdown-notice' ), 'primary large' ); ?>
							</div>
						</form>
					</div>

					<!-- Sidebar (Right Column) -->
					<div id="postbox-container-1" class="postbox-container">
						
						<!-- Quick Tips Box -->
						<div class="postbox tcn-postbox">
							<div class="postbox-header"><h2 class="hndle"><span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e( 'Quick Tips', 'topbar-countdown-notice' ); ?></h2></div>
							<div class="inside">
								<ul class="tcn-tips-list">
									<li><strong><?php esc_html_e( 'Urgency:', 'topbar-countdown-notice' ); ?></strong> <?php esc_html_e( 'Use the countdown to drive action.', 'topbar-countdown-notice' ); ?></li>
									<li><strong><?php esc_html_e( 'Mobile:', 'topbar-countdown-notice' ); ?></strong> <?php esc_html_e( 'Always test on smaller screens.', 'topbar-countdown-notice' ); ?></li>
									<li><strong><?php esc_html_e( 'Contrast:', 'topbar-countdown-notice' ); ?></strong> <?php esc_html_e( 'Ensure text is readable.', 'topbar-countdown-notice' ); ?></li>
									<li><strong><?php esc_html_e( 'Clarity:', 'topbar-countdown-notice' ); ?></strong> <?php esc_html_e( 'Keep messages short and sweet.', 'topbar-countdown-notice' ); ?></li>
								</ul>
							</div>
						</div>

						<!-- Server Time Box -->
						<div class="postbox tcn-postbox">
							<div class="postbox-header"><h2 class="hndle"><span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'Server Time', 'topbar-countdown-notice' ); ?></h2></div>
							<div class="inside">
								<div id="tcn-server-time"><?php echo esc_html( current_time( 'Y-m-d H:i:s' ) ); ?></div>
								<input type="hidden" id="tcn-server-time-offset" value="<?php echo esc_attr( time() - current_time('timestamp') ); ?>">
							</div>
						</div>

						<!-- Support Box -->
						<div class="postbox tcn-postbox tcn-support-box">
							<div class="postbox-header"><h2 class="hndle"><span class="dashicons dashicons-sos"></span> <?php esc_html_e( 'Support', 'topbar-countdown-notice' ); ?></h2></div>
							<div class="inside">
								<p><?php esc_html_e( 'Need help or have a feature request?', 'topbar-countdown-notice' ); ?></p>
								<p>
									<a href="https://wordpress.org/support/plugin/topbar-countdown-notice/" target="_blank" class="button button-secondary button-hero tcn-full-width-btn">
										<?php esc_html_e( 'Get Support', 'topbar-countdown-notice' ); ?>
									</a>
								</p>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Tab Switching Logic
			$('.nav-tab-wrapper a').on('click', function(e) {
				e.preventDefault();
				
				// Active class
				$('.nav-tab-wrapper a').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
				
				// Show/Hide tabs
				$('.tcn-tab-content').hide();
				var target = $(this).attr('href');
				$(target).fadeIn(200); // Smooth transition
			});
			
			// Function to update the server time display
			function updateServerTime() {
				var timeElement = document.getElementById('tcn-server-time');
				if (!timeElement) return;
				
				// Get current browser time and adjust for server timezone offset
				var now = new Date();
				var offsetElement = document.getElementById('tcn-server-time-offset');
				var serverOffset = offsetElement ? parseInt(offsetElement.value) * 1000 : 0;
				var serverTime = new Date(now.getTime() - (now.getTimezoneOffset() * 60000) + serverOffset);
				
				// Format the time
				var year = serverTime.getFullYear();
				var month = String(serverTime.getMonth() + 1).padStart(2, '0');
				var day = String(serverTime.getDate()).padStart(2, '0');
				var hours = String(serverTime.getHours()).padStart(2, '0');
				var minutes = String(serverTime.getMinutes()).padStart(2, '0');
				var seconds = String(serverTime.getSeconds()).padStart(2, '0');
				
				timeElement.textContent = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
			}

			// Initial update and set interval
			updateServerTime();
			setInterval(updateServerTime, 1000);
			
			// Sync with server every minute to prevent drift
			setInterval(function() {
				jQuery.get(ajaxurl, { action: 'tcn_get_server_time' }, function(response) {
					if (response && response.success) {
						document.getElementById('tcn-server-time-offset').value = response.offset;
					}
				});
			}, 60000); // Every minute
		});
		</script>
		
		<style>
			/* Modern Admin Styles */
			.tcn-settings-wrap { 
				max-width: 100%;
				margin: 0;
				padding: 0 20px;
				box-sizing: border-box;
			}

			/* Main Layout */
			#post-body.columns-2 { 
				display: flex; 
				margin: 0;
				padding: 0;
			}
			#post-body-content { 
				flex: 1; 
				padding: 0 20px 0 0;
				margin: 0;
			}
			#postbox-container-1 { 
				width: 300px;
				padding: 0 0 0 20px;
				margin: 0;
				border-left: 1px solid #ccd0d4;
			}

			/* Tabs */
			.tcn-nav-tab-wrapper { 
				border: none !important; 
				padding: 20px 0 0 0 !important; 
				margin: 0 0 0 0 !important;
				border-bottom: 1px solid #ccd0d4 !important;
			}
			.tcn-nav-tab-wrapper .nav-tab {
				background: #f0f0f1;
				border: 1px solid #ccd0d4;
				border-bottom: none;
				margin: 0 5px -1px 0;
				border-radius: 4px 4px 0 0;
				padding: 12px 20px;
				font-weight: 600;
				color: #50575e;
				transition: all 0.2s ease;
				display: inline-flex;
				align-items: center;
				gap: 8px;
				height: 44px;
				box-sizing: border-box;
			}
			.tcn-nav-tab-wrapper .nav-tab:hover {
				background: #f6f7f7;
				color: #2271b1;
				border-bottom-color: #f6f7f7;
			}
			.tcn-nav-tab-wrapper .nav-tab-active, 
			.tcn-nav-tab-wrapper .nav-tab-active:hover {
				background: #fff;
				border-color: #ccd0d4;
				border-bottom: 1px solid #fff;
				color: #2271b1;
				position: relative;
				top: 1px;
				height: 45px;
			}
			.tcn-nav-tab-wrapper .dashicons { 
				font-size: 16px; 
				width: 16px; 
				height: 16px; 
				margin-right: 5px;
			}

			/* Content Card */
			.tcn-tab-container {
				background: #fff;
				border: 1px solid #ccd0d4;
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
				padding: 25px 30px 30px;
				border-radius: 0 4px 4px 4px;
				margin: 0 0 30px 0;
			}

			/* Form Elements */
			.tcn-tab-content h2 {
				font-size: 1.5em;
				margin: 1.8em 0 1.2em;
				padding: 0 0 10px 0 !important;
				border-bottom: 1px solid #dcdcde;
				color: #1d2327;
				line-height: 1.4;
			}
			.tcn-tab-content h2:first-child { 
				margin-top: 0.5em; 
			}
			.form-table { 
				margin-top: 15px;
			}
			.form-table th { 
				padding: 20px 10px 20px 0; 
				font-weight: 600; 
				width: 200px;
			}
			.form-table td { 
				padding: 15px 10px; 
			}
			.form-table td p.description {
				margin: 5px 0 0 0;
				color: #646970;
			}
			
			/* Sidebar Boxes */
			.postbox.tcn-postbox {
				margin-bottom: 20px;
			}
			.postbox.tcn-postbox .inside {
				padding: 0 12px 12px;
			}
			.postbox.tcn-postbox .postbox-header {
				border-bottom: 1px solid #dcdcde;
				padding: 0 12px;
			}
			.postbox.tcn-postbox .hndle {
				font-size: 14px;
				padding: 12px 0;
			}
			.postbox.tcn-postbox .dashicons {
				margin-right: 5px;
			}
			
			/* Responsive */
			@media screen and (max-width: 1024px) {
				#post-body.columns-2 {
					flex-direction: column;
				}
				#postbox-container-1 {
					width: 100%;
					margin-top: 30px;
				}
			}
			
			/* Color Picker Fix */
			.wp-picker-container { display: block; }
			.wp-picker-holder { position: absolute; z-index: 100; }

			/* Submit Button Area */
			.tcn-submit-wrapper {
				margin-top: 20px;
				padding-top: 10px;
			}

			/* Sidebar */
			.tcn-postbox { border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
			.tcn-postbox .postbox-header { border-bottom: 1px solid #f0f0f1; background: #fff; }
			.tcn-postbox .hndle { font-size: 14px; display: flex; align-items: center; gap: 8px; }
			.tcn-tips-list { margin: 0; padding: 0 10px; }
			.tcn-tips-list li { margin-bottom: 10px; list-style: none; border-bottom: 1px dashed #eee; padding-bottom: 8px; }
			.tcn-tips-list li:last-child { border-bottom: none; }
			
			.tcn-full-width-btn { width: 100%; text-align: center; justify-content: center; margin-bottom: 10px !important; }
			.tcn-support-box .dashicons-megaphone { color: #2271b1; margin-right: 5px; }
		</style>
		<?php
	}

	/**
	 * Helper to render specific settings sections
	 */
	private static function render_sections_by_id( $page, $section_ids ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[$page] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[$page] as $section ) {
			if ( in_array( $section['id'], $section_ids ) ) {
				if ( $section['title'] ) {
					echo '<h2>' . esc_html( $section['title'] ) . '</h2>';
				}

				if ( $section['callback'] ) {
					call_user_func( $section['callback'], $section );
				}

				if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[$page] ) || ! isset( $wp_settings_fields[$page][$section['id']] ) ) {
					continue;
				}

				echo '<table class="form-table" role="presentation">';
				do_settings_fields( $page, $section['id'] );
				echo '</table>';
			}
		}
	}

	/**
	 * Sanitize settings
	 */
	public static function sanitize_settings( $input ) {
		$sanitized = array();
		
		// Active checkbox
		$sanitized['active'] = ! empty( $input['active'] );
		
		// Datetime fields - convert from site timezone to UTC timestamp
		$datetime_fields = array( 'start_datetime', 'end_datetime', 'countdown_target_custom' );
		foreach ( $datetime_fields as $field ) {
			if ( ! empty( $input[ $field ] ) ) {
				// Parse datetime in site timezone
				$dt = DateTime::createFromFormat( 'Y-m-d\TH:i', $input[ $field ], wp_timezone() );
				if ( $dt ) {
					// Store as UTC timestamp
					$sanitized[ $field ] = $dt->getTimestamp();
				}
			}
		}
		
		// Finish action
		$sanitized['finish_action'] = in_array( $input['finish_action'], array( 'hide', 'show_alternative' ), true )
			? $input['finish_action']
			: 'hide';
		
		// Text fields with HTML
		$html_fields = array( 'alternative_text', 'content', 'countdown_prefix' );
		$allowed_html = array(
			'p'      => array(),
			'br'     => array(),
			'strong' => array(),
			'em'     => array(),
			'a'      => array( 'href' => array(), 'title' => array(), 'target' => array() ),
			'ul'     => array(),
			'ol'     => array(),
			'li'     => array(),
		);
		
		foreach ( $html_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$sanitized[ $field ] = wp_kses( $input[ $field ], $allowed_html );
			}
		}
		
		// URL fields
		$url_fields = array( 'alternative_link', 'main_link' );
		foreach ( $url_fields as $field ) {
			if ( ! empty( $input[ $field ] ) ) {
				$sanitized[ $field ] = esc_url_raw( $input[ $field ] );
			}
		}
		
		// Button text fields
		$text_fields = array( 'main_link_text', 'alternative_link_text' );
		foreach ( $text_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $input[ $field ] );
			}
		}
		
		// New tab checkboxes
		$sanitized['alternative_link_new_tab'] = ! empty( $input['alternative_link_new_tab'] );
		$sanitized['main_link_new_tab'] = ! empty( $input['main_link_new_tab'] );
		
		// Countdown
		$sanitized['countdown_active'] = ! empty( $input['countdown_active'] );
		
		// Countdown target mode
		$sanitized['countdown_target_mode'] = in_array( $input['countdown_target_mode'], array( 'end_date', 'custom' ), true )
			? $input['countdown_target_mode']
			: 'end_date';
		
		// Alternative colors mode
		$sanitized['alternative_colors_mode'] = in_array( $input['alternative_colors_mode'], array( 'same', 'custom' ), true )
			? $input['alternative_colors_mode']
			: 'same';
		
		// Colors
		$color_fields = array( 'bg_color', 'text_color', 'alternative_bg_color', 'alternative_text_color' );
		foreach ( $color_fields as $field ) {
			if ( ! empty( $input[ $field ] ) ) {
				$color = sanitize_hex_color( $input[ $field ] );
				if ( $color ) {
					$sanitized[ $field ] = $color;
				}
			}
		}
		
		// Padding fields
		$padding_fields = array( 'padding_top', 'padding_bottom', 'padding_left', 'padding_right' );
		foreach ( $padding_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$sanitized[ $field ] = absint( $input[ $field ] );
			}
		}
		
		// Countdown show seconds
		$sanitized['countdown_show_seconds'] = ! empty( $input['countdown_show_seconds'] );
		
		// Debug mode
		$sanitized['debug_mode'] = ! empty( $input['debug_mode'] );
		
		// Custom CSS
		if ( isset( $input['custom_css'] ) ) {
			$sanitized['custom_css'] = wp_strip_all_tags( $input['custom_css'] );
		}
		
		return $sanitized;
	}

	/**
	 * Get plugin settings
	 */
	public static function get_settings() {
		$defaults = array(
			'active'                   => false,
			'start_datetime'           => '',
			'end_datetime'             => '',
			'finish_action'            => 'hide',
			'alternative_text'         => '',
			'alternative_link'         => '',
			'alternative_link_text'    => __( 'Learn More', 'topbar-countdown-notice' ),
			'alternative_link_new_tab' => false,
			'content'                  => '',
			'main_link'                => '',
			'main_link_text'           => __( 'Learn More', 'topbar-countdown-notice' ),
			'main_link_new_tab'        => false,
			'countdown_active'         => false,
			'countdown_target_mode'    => 'end_date',
			'countdown_target_custom'  => '',
			'countdown_prefix'         => '',
			'countdown_show_seconds'   => false,
			'bg_color'                 => '#2c3e50',
			'text_color'               => '#ffffff',
			'padding_top'              => 12,
			'padding_bottom'           => 12,
			'padding_left'             => 20,
			'padding_right'            => 20,
			'custom_css'               => '',
			'debug_mode'               => false,
		);
		
		$settings = get_option( self::OPTION_NAME, array() );
		
		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Check if bar should be displayed based on scheduling
	 */
	public static function should_display_bar() {
		$settings = self::get_settings();
		// Check if active
		if ( empty( $settings['active'] ) ) {
			return false;
		}
		
		// Use time() to get current UTC timestamp (matches stored timestamps)
		$current_time = time();
		$start_time = ! empty( $settings['start_datetime'] ) ? $settings['start_datetime'] : null;
		$end_time = ! empty( $settings['end_datetime'] ) ? $settings['end_datetime'] : null;
		
		// No dates: always visible
		if ( ! $start_time && ! $end_time ) {
			return true;
		}
		
		// Only start: visible if current >= start
		if ( $start_time && ! $end_time ) {
			return $current_time >= $start_time;
		}
		
		// Only end: visible if current <= end
		if ( ! $start_time && $end_time ) {
			return $current_time <= $end_time;
		}
		
		// Both: visible if between start and end
		return $current_time >= $start_time && $current_time <= $end_time;
	}

	/**
	 * Check if we should show alternative content
	 */
	public static function should_show_alternative() {
		$settings = self::get_settings();
		
		if ( $settings['finish_action'] !== 'show_alternative' ) {
			return false;
		}
		
		// Use time() to get current UTC timestamp (matches stored timestamps)
		$current_time = time();
		$end_time = ! empty( $settings['end_datetime'] ) ? $settings['end_datetime'] : null;
		
		// Show alternative if end time has passed
		return $end_time && $current_time > $end_time;
	}

	/**
	 * Enqueue admin assets
	 */
	public static function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_topbar-countdown-notice' !== $hook ) {
			return;
		}
		
		// WordPress color picker
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		
		// Admin CSS
		wp_enqueue_style(
			'tcn-admin',
			plugins_url( 'assets/css/admin.css', __FILE__ ),
			array(),
			self::VERSION
		);
		
		// Admin JS
		wp_enqueue_script(
			'tcn-admin',
			plugins_url( 'assets/js/admin.js', __FILE__ ),
			array( 'jquery', 'wp-color-picker' ),
			self::VERSION,
			true
		);
	}

	/**
	 * Enqueue frontend assets
	 */
	public static function enqueue_frontend_assets() {
		// Only enqueue if bar should be displayed or alternative content
		if ( ! self::should_display_bar() && ! self::should_show_alternative() ) {
			return;
		}
		
		$settings = self::get_settings();
		
		// Frontend CSS
		wp_enqueue_style(
			'tcn-frontend',
			plugins_url( 'assets/css/frontend.css', __FILE__ ),
			array(),
			self::VERSION
		);
		
		// Frontend JS
		wp_enqueue_script(
			'tcn-frontend',
			plugins_url( 'assets/js/frontend.js', __FILE__ ),
			array(),
			time(), // Forzar recarga sin caché durante debugging
			true
		);
		
		// Localize script data
		$countdown_target = 0;
		if ( ! empty( $settings['countdown_active'] ) ) {
			// Determine countdown target based on mode
			if ( $settings['countdown_target_mode'] === 'custom' && ! empty( $settings['countdown_target_custom'] ) ) {
				$countdown_target = $settings['countdown_target_custom'];
			} elseif ( $settings['countdown_target_mode'] === 'end_date' && ! empty( $settings['end_datetime'] ) ) {
				$countdown_target = $settings['end_datetime'];
			}
		}
		
		// Determine alternative colors
		$alternative_bg_color = $settings['bg_color'];
		$alternative_text_color = $settings['text_color'];
		
		if ( ! empty( $settings['alternative_colors_mode'] ) && $settings['alternative_colors_mode'] === 'custom' ) {
			if ( ! empty( $settings['alternative_bg_color'] ) ) {
				$alternative_bg_color = $settings['alternative_bg_color'];
			}
			if ( ! empty( $settings['alternative_text_color'] ) ) {
				$alternative_text_color = $settings['alternative_text_color'];
			}
		}
		
		wp_localize_script(
			'tcn-frontend',
			'tcnData',
			array(
				'debugMode'               => ! empty( $settings['debug_mode'] ),
				'serverTime'              => time(), // Current server timestamp for sync
				'countdownTarget'         => $countdown_target,
				'labels'                  => array(
					'days'    => __( 'd', 'topbar-countdown-notice' ),
					'hours'   => __( 'h', 'topbar-countdown-notice' ),
					'minutes' => __( 'm', 'topbar-countdown-notice' ),
					'seconds' => __( 's', 'topbar-countdown-notice' ),
				),
				'showSeconds'             => ! empty( $settings['countdown_show_seconds'] ),
				'finishAction'            => ! empty( $settings['finish_action'] ) ? $settings['finish_action'] : 'hide',
				'alternativeText'         => ! empty( $settings['alternative_text'] ) ? $settings['alternative_text'] : '',
				'alternativeLink'         => ! empty( $settings['alternative_link'] ) ? $settings['alternative_link'] : '',
				'alternativeLinkText'     => ! empty( $settings['alternative_link_text'] ) ? $settings['alternative_link_text'] : __( 'Learn More', 'topbar-countdown-notice' ),
				'alternativeLinkTab'      => ! empty( $settings['alternative_link_new_tab'] ),
				'alternativeColorsMode'   => ! empty( $settings['alternative_colors_mode'] ) ? $settings['alternative_colors_mode'] : 'same',
				'alternativeBgColor'      => $alternative_bg_color,
				'alternativeTextColor'    => $alternative_text_color,
			)
		);
		
		// Add custom CSS if provided
		if ( ! empty( $settings['custom_css'] ) ) {
			wp_add_inline_style( 'tcn-frontend', $settings['custom_css'] );
		}
	}

	/**
	 * Display the top bar
	 */
	public static function display_topbar() {
		// Use static variable to prevent displaying twice
		static $displayed = false;
		if ( $displayed ) {
			return;
		}
		$displayed = true;
		
		// Check if should display
		$show_normal = self::should_display_bar();
		$show_alternative = self::should_show_alternative();
		
		if ( ! $show_normal && ! $show_alternative ) {
			return;
		}
		
		$settings = self::get_settings();
		// Determine which content to show
		$is_alternative = $show_alternative && ! $show_normal;
		
		// Build HTML
		$bg_color = ! empty( $settings['bg_color'] ) ? $settings['bg_color'] : '#2c3e50';
		$text_color = ! empty( $settings['text_color'] ) ? $settings['text_color'] : '#ffffff';
		
		// If showing alternative content and custom colors are enabled, use alternative colors
		if ( $is_alternative && ! empty( $settings['alternative_colors_mode'] ) && $settings['alternative_colors_mode'] === 'custom' ) {
			if ( ! empty( $settings['alternative_bg_color'] ) ) {
				$bg_color = $settings['alternative_bg_color'];
			}
			if ( ! empty( $settings['alternative_text_color'] ) ) {
				$text_color = $settings['alternative_text_color'];
			}
		}
		
		$padding_top = isset( $settings['padding_top'] ) ? absint( $settings['padding_top'] ) : 12;
		$padding_bottom = isset( $settings['padding_bottom'] ) ? absint( $settings['padding_bottom'] ) : 12;
		$padding_left = isset( $settings['padding_left'] ) ? absint( $settings['padding_left'] ) : 20;
		$padding_right = isset( $settings['padding_right'] ) ? absint( $settings['padding_right'] ) : 20;
		
		ob_start();
		?>
		<div id="tcn-topbar" class="tcn-topbar" role="banner" style="background-color: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $text_color ); ?>; padding: <?php echo esc_attr( $padding_top ); ?>px <?php echo esc_attr( $padding_right ); ?>px <?php echo esc_attr( $padding_bottom ); ?>px <?php echo esc_attr( $padding_left ); ?>px;">
			<div class="tcn-topbar-inner">
				<?php if ( $is_alternative ) : ?>
					<!-- Alternative content -->
					<div class="tcn-content">
						<?php echo wp_kses_post( $settings['alternative_text'] ); ?>
					</div>
					
					<?php if ( ! empty( $settings['alternative_link'] ) ) : ?>
						<a href="<?php echo esc_url( $settings['alternative_link'] ); ?>" 
						   class="tcn-link"
						   <?php echo ! empty( $settings['alternative_link_new_tab'] ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
							<?php echo ! empty( $settings['alternative_link_text'] ) ? esc_html( $settings['alternative_link_text'] ) : esc_html__( 'Learn More', 'topbar-countdown-notice' ); ?>
						</a>
					<?php endif; ?>
				<?php else : ?>
					<!-- Normal content -->
					<div class="tcn-content">
						<?php echo wp_kses_post( $settings['content'] ); ?>
					</div>
					
					<?php 
					// Determine if countdown should be displayed
					$show_countdown = false;
					if ( ! empty( $settings['countdown_active'] ) ) {
						if ( $settings['countdown_target_mode'] === 'custom' && ! empty( $settings['countdown_target_custom'] ) ) {
							$show_countdown = true;
						} elseif ( $settings['countdown_target_mode'] === 'end_date' && ! empty( $settings['end_datetime'] ) ) {
							$show_countdown = true;
						}
					}
					?>
					<?php if ( $show_countdown ) : ?>
						<div class="tcn-countdown-wrapper">
							<?php if ( ! empty( $settings['countdown_prefix'] ) ) : ?>
								<span class="tcn-countdown-prefix"><?php echo wp_kses_post( $settings['countdown_prefix'] ); ?></span>
							<?php endif; ?>
							<span id="tcn-countdown" class="tcn-countdown"></span>
						</div>
					<?php endif; ?>
					
					<?php if ( ! empty( $settings['main_link'] ) ) : ?>
						<a href="<?php echo esc_url( $settings['main_link'] ); ?>" 
						   class="tcn-link"
						   <?php echo ! empty( $settings['main_link_new_tab'] ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
							<?php echo ! empty( $settings['main_link_text'] ) ? esc_html( $settings['main_link_text'] ) : esc_html__( 'Learn More', 'topbar-countdown-notice' ); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<?php
		
		$html = ob_get_clean();
		
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Fallback display for themes without wp_body_open
	 */
	public static function fallback_display_topbar() {
		if ( ! did_action( 'wp_body_open' ) ) {
			self::display_topbar();
		}
	}

	/**
	 * Plugin activation
	 */
	public static function activate() {
		// Set default options if not exist
		if ( ! get_option( self::OPTION_NAME ) ) {
			$defaults = array(
				'active'      => false,
				'bg_color'    => '#2c3e50',
				'text_color'  => '#ffffff',
			);
			add_option( self::OPTION_NAME, $defaults );
		}
	}

	/**
	 * Plugin deactivation
	 */
	public static function deactivate() {
		// Nothing to do on deactivation
	}
}

// Initialize plugin
Topbar_Countdown_Notice::init();

// Register activation/deactivation hooks
register_activation_hook( __FILE__, array( 'Topbar_Countdown_Notice', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Topbar_Countdown_Notice', 'deactivate' ) );
