<?php
/**
 * Plugin Name: Antikton Topbar Countdown
 * Plugin URI: https://github.com/antikton/antikton-topbar-countdown
 * Description: Display a customizable top bar with optional countdown timer and scheduling capabilities.
 * Version: 1.1.1
 * Author: Eduardo Pagán
 * Author URI: https://github.com/antikton
 * Text Domain: antikton-topbar-countdown
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
class Antikton_Topbar_Countdown {

	/**
	 * Plugin version
	 */
	const VERSION = '1.1.1';

	/**
	 * Option name for settings
	 */
	const OPTION_NAME = 'antitoco_settings';

	/**
	 * Text domain for translations
	 */
	const TEXT_DOMAIN = 'antikton-topbar-countdown';

	/**
	 * Initialize the plugin
	 */
	public static function init() {
		// Load text domain
		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
		
		// Add AJAX handler for getting server time
		add_action( 'wp_ajax_antitoco_get_server_time', array( __CLASS__, 'ajax_get_server_time' ) );
		
		// Admin hooks
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		
		// Add settings link in plugins list
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'add_settings_link' ) );
		
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
        $locale = apply_filters( 'antikton_topbar_countdown_locale', is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale(), 'antikton-topbar-countdown' );

        // Load .mo file from wp-content/languages/plugins/ first
        $mofile = WP_LANG_DIR . '/plugins/' . 'antikton-topbar-countdown' . '-' . $locale . '.mo';

        if ( file_exists( $mofile ) ) {
            return load_textdomain( 'antikton-topbar-countdown', $mofile );
        }

        // Fallback to plugin languages directory
        $plugin_dir = dirname( plugin_basename( __FILE__ ) );
        $mofile = WP_PLUGIN_DIR . '/' . $plugin_dir . '/languages/' . 'antikton-topbar-countdown' . '-' . $locale . '.mo';

        if ( file_exists( $mofile ) ) {
            return load_textdomain( 'antikton-topbar-countdown', $mofile );
        }

        // WordPress.org automatically loads translations for plugins
        // No need for load_plugin_textdomain since WordPress 4.6+
    }


    /**
	 * Add settings link to plugin actions
	 */
	public static function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'options-general.php?page=antikton-topbar-countdown' ),
			esc_html__( 'Ajustes', 'antikton-topbar-countdown' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

    /**
	 * Add admin menu page
	 */
	public static function add_admin_menu() {
		add_options_page(
			__( 'Antikton Topbar Countdown', 'antikton-topbar-countdown' ),
			__( 'Topbar Countdown', 'antikton-topbar-countdown' ),
			'manage_options',
			'antikton-topbar-countdown',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Register plugin settings
	 */
	public static function register_settings() {
		register_setting(
			'antitoco_settings_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			)
		);

		// Main section
		add_settings_section(
			'antitoco_main_section',
			__( 'General Settings', 'antikton-topbar-countdown' ),
			null,
			'antikton-topbar-countdown'
		);

		// Active checkbox
		add_settings_field(
			'active',
			__( 'Activate Bar', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'antikton-topbar-countdown',
			'antitoco_main_section',
			array( 'field' => 'active', 'label' => __( 'Show the top bar', 'antikton-topbar-countdown' ) )
		);

		// Scheduling section
		add_settings_section(
			'antitoco_schedule_section',
			__( 'Scheduling', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_schedule_section_description' ),
			'antikton-topbar-countdown'
		);

		// Start datetime
		add_settings_field(
			'start_datetime',
			__( 'Start Date/Time', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_datetime_field' ),
			'antikton-topbar-countdown',
			'antitoco_schedule_section',
			array( 'field' => 'start_datetime' )
		);

		// End datetime
		add_settings_field(
			'end_datetime',
			__( 'End Date/Time', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_datetime_field' ),
			'antikton-topbar-countdown',
			'antitoco_schedule_section',
			array( 'field' => 'end_datetime' )
		);

		// Finish action section
		add_settings_section(
			'antitoco_finish_section',
			__( 'Action on Finish', 'antikton-topbar-countdown' ),
			null,
			'antikton-topbar-countdown'
		);

		// Finish action radio
		add_settings_field(
			'finish_action',
			__( 'When End Date/Time is Reached', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_finish_action_field' ),
			'antikton-topbar-countdown',
			'antitoco_finish_section'
		);

		// Alternative text
		add_settings_field(
			'alternative_text',
			__( 'Alternative Text', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_editor_field' ),
			'antikton-topbar-countdown',
			'antitoco_finish_section',
			array( 'field' => 'alternative_text' )
		);

		// Alternative link
		add_settings_field(
			'alternative_link',
			__( 'Alternative Link', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_url_field' ),
			'antikton-topbar-countdown',
			'antitoco_finish_section',
			array( 'field' => 'alternative_link', 'new_tab_field' => 'alternative_link_new_tab' )
		);

		// Alternative link button text
		add_settings_field(
			'alternative_link_text',
			__( 'Alternative Link Button Text', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_text_field' ),
			'antikton-topbar-countdown',
			'antitoco_finish_section',
			array( 'field' => 'alternative_link_text', 'placeholder' => __( 'Learn More', 'antikton-topbar-countdown' ) )
		);

		// Alternative colors mode
		add_settings_field(
			'alternative_colors_mode',
			__( 'Alternative Colors', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_alternative_colors_mode_field' ),
			'antikton-topbar-countdown',
			'antitoco_finish_section'
		);

		// Alternative background color
		add_settings_field(
			'alternative_bg_color',
			__( 'Alternative Background Color', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_color_field' ),
			'antikton-topbar-countdown',
			'antitoco_finish_section',
			array( 'field' => 'alternative_bg_color' )
		);

		// Alternative text color
		add_settings_field(
			'alternative_text_color',
			__( 'Alternative Text Color', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_color_field' ),
			'antikton-topbar-countdown',
			'antitoco_finish_section',
			array( 'field' => 'alternative_text_color' )
		);

		// Content section
		add_settings_section(
			'antitoco_content_section',
			__( 'Bar Content', 'antikton-topbar-countdown' ),
			null,
			'antikton-topbar-countdown'
		);

		// Main content
		add_settings_field(
			'content',
			__( 'Main Content', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_editor_field' ),
			'antikton-topbar-countdown',
			'antitoco_content_section',
			array( 'field' => 'content' )
		);

		// Main link
		add_settings_field(
			'main_link',
			__( 'Main Link', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_url_field' ),
			'antikton-topbar-countdown',
			'antitoco_content_section',
			array( 'field' => 'main_link', 'new_tab_field' => 'main_link_new_tab' )
		);

		// Main link button text
		add_settings_field(
			'main_link_text',
			__( 'Main Link Button Text', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_text_field' ),
			'antikton-topbar-countdown',
			'antitoco_content_section',
			array( 'field' => 'main_link_text', 'placeholder' => __( 'Learn More', 'antikton-topbar-countdown' ) )
		);

		// Countdown section
		add_settings_section(
			'antitoco_countdown_section',
			__( 'Countdown Timer', 'antikton-topbar-countdown' ),
			null,
			'antikton-topbar-countdown'
		);

		// Countdown active
		add_settings_field(
			'countdown_active',
			__( 'Activate Countdown', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'antikton-topbar-countdown',
			'antitoco_countdown_section',
			array( 'field' => 'countdown_active', 'label' => __( 'Show countdown timer', 'antikton-topbar-countdown' ) )
		);

		// Countdown target mode
		add_settings_field(
			'countdown_target_mode',
			__( 'Countdown Target', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_countdown_target_mode_field' ),
			'antikton-topbar-countdown',
			'antitoco_countdown_section'
		);

		// Countdown target custom
		add_settings_field(
			'countdown_target_custom',
			__( 'Custom Target Date/Time', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_datetime_field' ),
			'antikton-topbar-countdown',
			'antitoco_countdown_section',
			array( 'field' => 'countdown_target_custom' )
		);

		// Countdown prefix (TinyMCE editor)
		add_settings_field(
			'countdown_prefix',
			__( 'Countdown Prefix Text', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_editor_field' ),
			'antikton-topbar-countdown',
			'antitoco_countdown_section',
			array( 'field' => 'countdown_prefix' )
		);

		// Show seconds
		add_settings_field(
			'countdown_show_seconds',
			__( 'Show Seconds', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'antikton-topbar-countdown',
			'antitoco_countdown_section',
			array( 'field' => 'countdown_show_seconds', 'label' => __( 'Display seconds in countdown', 'antikton-topbar-countdown' ) )
		);

		// Appearance section
		add_settings_section(
			'antitoco_appearance_section',
			__( 'Appearance', 'antikton-topbar-countdown' ),
			null,
			'antikton-topbar-countdown'
		);

		// Background color
		add_settings_field(
			'bg_color',
			__( 'Background Color', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_color_field' ),
			'antikton-topbar-countdown',
			'antitoco_appearance_section',
			array( 'field' => 'bg_color' )
		);

		// Text color
		add_settings_field(
			'text_color',
			__( 'Text Color', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_color_field' ),
			'antikton-topbar-countdown',
			'antitoco_appearance_section',
			array( 'field' => 'text_color' )
		);

		// Padding Top
		add_settings_field(
			'padding_top',
			__( 'Padding Top (px)', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_number_field' ),
			'antikton-topbar-countdown',
			'antitoco_appearance_section',
			array( 'field' => 'padding_top', 'default' => '12' )
		);

		// Padding Bottom
		add_settings_field(
			'padding_bottom',
			__( 'Padding Bottom (px)', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_number_field' ),
			'antikton-topbar-countdown',
			'antitoco_appearance_section',
			array( 'field' => 'padding_bottom', 'default' => '12' )
		);

		// Padding Left
		add_settings_field(
			'padding_left',
			__( 'Padding Left (px)', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_number_field' ),
			'antikton-topbar-countdown',
			'antitoco_appearance_section',
			array( 'field' => 'padding_left', 'default' => '20' )
		);

		// Padding Right
		add_settings_field(
			'padding_right',
			__( 'Padding Right (px)', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_number_field' ),
			'antikton-topbar-countdown',
			'antitoco_appearance_section',
			array( 'field' => 'padding_right', 'default' => '20' )
		);

		// Debug Mode
		add_settings_field(
			'debug_mode',
			__( 'Debug Mode', 'antikton-topbar-countdown' ),
			array( __CLASS__, 'render_checkbox_field' ),
			'antikton-topbar-countdown',
			'antitoco_appearance_section',
			array( 'field' => 'debug_mode', 'label' => __( 'Show console.log messages in browser (for debugging)', 'antikton-topbar-countdown' ) )
		);
	}

	/**
	 * Render schedule section description
	 */
	public static function render_schedule_section_description() {
		echo '<p>' . esc_html__( 'Configure when the bar should be visible. Leave both empty to always show the bar.', 'antikton-topbar-countdown' ) . '</p>';
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
			<?php esc_html_e( 'Hide the bar', 'antikton-topbar-countdown' ); ?>
		</label>
		<br>
		<label>
			<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[finish_action]" value="show_alternative" <?php checked( $value, 'show_alternative' ); ?>>
			<?php esc_html_e( 'Show alternative content', 'antikton-topbar-countdown' ); ?>
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
			esc_html__( 'Open in new tab', 'antikton-topbar-countdown' )
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
		echo '<p class="description">' . esc_html__( 'You can use HTML tags like <strong>, <em>, <span>, etc.', 'antikton-topbar-countdown' ) . '</p>';
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
			<?php esc_html_e( 'Same as End Date/Time', 'antikton-topbar-countdown' ); ?>
		</label>
		<br>
		<label>
			<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[countdown_target_mode]" value="custom" <?php checked( $value, 'custom' ); ?>>
			<?php esc_html_e( 'Custom Date/Time', 'antikton-topbar-countdown' ); ?>
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
			<?php esc_html_e( 'Same as main colors', 'antikton-topbar-countdown' ); ?>
		</label>
		<br>
		<label>
			<input type="radio" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[alternative_colors_mode]" value="custom" <?php checked( $value, 'custom' ); ?>>
			<?php esc_html_e( 'Custom colors', 'antikton-topbar-countdown' ); ?>
		</label>
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
			'<input type="text" name="%s[%s]" value="%s" class="antitoco-color-picker">',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $field ),
			esc_attr( $value )
		);
	}

	/**
	 * Render Help & Ideas tab content
	 */
	public static function render_help_ideas_tab() {
		?>
		<div class="antitoco-help-ideas-wrapper">
			<div class="antitoco-help-intro">
				<h2><?php esc_html_e( 'Configuration Ideas & Examples', 'antikton-topbar-countdown' ); ?></h2>
				<p><?php esc_html_e( 'Get inspired with these practical examples for different seasons and use cases. These are just ideas - customize them to fit your needs!', 'antikton-topbar-countdown' ); ?></p>
			</div>

			<!-- Seasonal Campaigns -->
			<div class="antitoco-idea-category">
				<h3><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Seasonal Campaigns', 'antikton-topbar-countdown' ); ?></h3>
				
				<div class="antitoco-idea-card">
					<h4>🎄 <?php esc_html_e( 'Christmas Sale (December)', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "🎅 <?php esc_html_e( 'Christmas Sale! Up to 50% OFF on all products', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Start Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'December 1st, 00:00', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'End Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'December 25th, 23:59', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Countdown:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Active, targeting End Date', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Action on Finish:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Hide the bar', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Colors:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Red background (#c41e3a), White text', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>

				<div class="antitoco-idea-card">
					<h4>🎆 <?php esc_html_e( 'New Year Countdown (December 31st)', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "🎉 <?php esc_html_e( 'New Year is coming!', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Start Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'December 31st, 00:00', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'End Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'January 1st, 00:00', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Countdown:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Active, show seconds', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Alternative Content:', 'antikton-topbar-countdown' ); ?></strong> "🎊 <?php esc_html_e( 'Happy New Year! 20% OFF with code: NEWYEAR', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Colors:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Gold background (#ffd700), Black text', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>

				<div class="antitoco-idea-card">
					<h4>❤️ <?php esc_html_e( 'Valentine\'s Day (February 14th)', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "💝 <?php esc_html_e( 'Valentine\'s Day Special - Perfect gifts for your loved ones', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Start Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'February 7th, 00:00', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'End Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'February 14th, 23:59', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Countdown:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Active', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Colors:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Pink background (#ff69b4), White text', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>

				<div class="antitoco-idea-card">
					<h4>🛍️ <?php esc_html_e( 'Black Friday (November)', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "⚡ <?php esc_html_e( 'BLACK FRIDAY - Up to 70% OFF!', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Start Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Last Friday of November, 00:00', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'End Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Sunday after Black Friday, 23:59', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Countdown:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Active, show seconds', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Colors:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Black background (#000000), Yellow text (#ffeb3b)', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>

				<div class="antitoco-idea-card">
					<h4>🌸 <?php esc_html_e( 'Spring Sale (March-April)', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "🌺 <?php esc_html_e( 'Spring Collection - Fresh arrivals now available!', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Start Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'March 20th, 00:00', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'End Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'April 30th, 23:59', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Countdown:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Disabled', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Colors:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Light green background (#90ee90), Dark green text (#006400)', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>

				<div class="antitoco-idea-card">
					<h4>☀️ <?php esc_html_e( 'Summer Sale (July-August)', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "🏖️ <?php esc_html_e( 'Summer Sale - Beat the heat with hot deals!', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Start Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'July 1st, 00:00', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'End Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'August 31st, 23:59', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Colors:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Orange background (#ff8c00), White text', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>
			</div>

			<!-- Product Launches -->
			<div class="antitoco-idea-category">
				<h3><span class="dashicons dashicons-products"></span> <?php esc_html_e( 'Product Launches & Events', 'antikton-topbar-countdown' ); ?></h3>
				
				<div class="antitoco-idea-card">
					<h4>🚀 <?php esc_html_e( 'Product Launch Countdown', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "🎯 <?php esc_html_e( 'New product launching soon!', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Countdown:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Active, targeting launch date', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Alternative Content:', 'antikton-topbar-countdown' ); ?></strong> "✨ <?php esc_html_e( 'NOW AVAILABLE! Get 20% OFF with code: LAUNCH20', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Alternative Colors:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Green background to indicate "live"', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>

				<div class="antitoco-idea-card">
					<h4>🎫 <?php esc_html_e( 'Webinar/Event Registration', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "📅 <?php esc_html_e( 'Free Webinar: Marketing Strategies 2024', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Countdown:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Active, targeting event start', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Action on Finish:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Show alternative: "Event is LIVE! Join now"', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>

				<div class="antitoco-idea-card">
					<h4>🎁 <?php esc_html_e( 'Coupon Reveal', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "🔒 <?php esc_html_e( 'Secret discount code reveals in...', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Countdown:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Active, show seconds', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Alternative Content:', 'antikton-topbar-countdown' ); ?></strong> "🎉 <?php esc_html_e( 'Use code SAVE30 for 30% OFF!', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Alternative Colors:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Bright color to grab attention', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>
			</div>

			<!-- Informational -->
			<div class="antitoco-idea-category">
				<h3><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'Informational & Announcements', 'antikton-topbar-countdown' ); ?></h3>
				
				<div class="antitoco-idea-card">
					<h4>⚠️ <?php esc_html_e( 'Maintenance Notice', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "🔧 <?php esc_html_e( 'Scheduled maintenance tonight 10 PM - 2 AM', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Start Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Day before maintenance, 08:00', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'End Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Maintenance end time', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Countdown:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Disabled', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Colors:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Orange background (#ff9800), Black text', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>

				<div class="antitoco-idea-card">
					<h4>📢 <?php esc_html_e( 'Important Announcement', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "📣 <?php esc_html_e( 'New shipping policy - Free shipping on orders over $50', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Start Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Empty (always show)', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'End Date:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Empty (permanent)', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Countdown:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Disabled', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>

				<div class="antitoco-idea-card">
					<h4>🎓 <?php esc_html_e( 'Course Enrollment Deadline', 'antikton-topbar-countdown' ); ?></h4>
					<div class="antitoco-idea-content">
						<p><strong><?php esc_html_e( 'Main Content:', 'antikton-topbar-countdown' ); ?></strong> "📚 <?php esc_html_e( 'Last chance to enroll! Course starts soon', 'antikton-topbar-countdown' ); ?>"</p>
						<p><strong><?php esc_html_e( 'Countdown:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Active, targeting enrollment deadline', 'antikton-topbar-countdown' ); ?></p>
						<p><strong><?php esc_html_e( 'Action on Finish:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Hide the bar', 'antikton-topbar-countdown' ); ?></p>
					</div>
				</div>
			</div>

			<!-- Tips Section -->
			<div class="antitoco-idea-category antitoco-tips-section">
				<h3><span class="dashicons dashicons-star-filled"></span> <?php esc_html_e( 'Pro Tips', 'antikton-topbar-countdown' ); ?></h3>
				<ul class="antitoco-tips-list">
					<li>💡 <?php esc_html_e( 'Use emojis to make your messages more eye-catching and friendly', 'antikton-topbar-countdown' ); ?></li>
					<li>🎨 <?php esc_html_e( 'Match colors with your brand or the season/event theme', 'antikton-topbar-countdown' ); ?></li>
					<li>⏰ <?php esc_html_e( 'Show seconds in countdown for last-minute urgency (last 24 hours)', 'antikton-topbar-countdown' ); ?></li>
					<li>🔄 <?php esc_html_e( 'Use alternative content to reveal special offers or discount codes', 'antikton-topbar-countdown' ); ?></li>
					<li>📱 <?php esc_html_e( 'Always test on mobile devices - keep messages short!', 'antikton-topbar-countdown' ); ?></li>
					<li>🎯 <?php esc_html_e( 'Create urgency with phrases like "Limited time", "Last chance", "Ending soon"', 'antikton-topbar-countdown' ); ?></li>
					<li>✨ <?php esc_html_e( 'Change bar colors when countdown ends to grab attention', 'antikton-topbar-countdown' ); ?></li>
				</ul>
			</div>
		</div>
	<?php
	}

	/**
	 * Render admin page
	 */
	public static function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		global $wp_settings_sections, $wp_settings_fields;
		$page = 'antikton-topbar-countdown';
		
		?>
		<div class="wrap antitoco-settings-wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<hr class="wp-header-end">
						<?php 
				// Display settings errors with a unique ID to prevent duplicates
				$settings_errors = get_settings_errors('antitoco_settings_group');
				if (!empty($settings_errors)) {
					settings_errors('antitoco_settings_group');
				}
				?>
			
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					
					<!-- Main Content (Left Column) -->
					<div id="post-body-content">
						
						<!-- Tabs Navigation -->
						<h2 class="nav-tab-wrapper antitoco-nav-tab-wrapper">
							<a href="#tab-general" class="nav-tab nav-tab-active">
								<span class="dashicons dashicons-admin-settings"></span> 
								<?php esc_html_e( 'General & Schedule', 'antikton-topbar-countdown' ); ?>
							</a>
							<a href="#tab-content" class="nav-tab">
								<span class="dashicons dashicons-text-page"></span> 
								<?php esc_html_e( 'Content & Countdown', 'antikton-topbar-countdown' ); ?>
							</a>
							<a href="#tab-finish" class="nav-tab">
								<span class="dashicons dashicons-flag"></span> 
								<?php esc_html_e( 'Action on Finish', 'antikton-topbar-countdown' ); ?>
							</a>
							<a href="#tab-appearance" class="nav-tab">
					<span class="dashicons dashicons-art"></span> 
					<?php esc_html_e( 'Appearance', 'antikton-topbar-countdown' ); ?>
				</a>
				<a href="#tab-help" class="nav-tab">
					<span class="dashicons dashicons-lightbulb"></span> 
					<?php esc_html_e( 'Help & Ideas', 'antikton-topbar-countdown' ); ?>
				</a>
			</h2>

						<form method="post" action="options.php" class="antitoco-main-form">
							<?php settings_fields( 'antitoco_settings_group' ); ?>

							<div class="antitoco-tab-container">
								<!-- Tab: General & Schedule -->
								<div id="tab-general" class="antitoco-tab-content">
									<?php 
									self::render_sections_by_id( $page, array( 'antitoco_main_section', 'antitoco_schedule_section' ) );
									?>
								</div>
								
								<!-- Tab: Content & Countdown -->
								<div id="tab-content" class="antitoco-tab-content" style="display:none;">
									<?php 
									self::render_sections_by_id( $page, array( 'antitoco_content_section', 'antitoco_countdown_section' ) );
									?>
								</div>

								<!-- Tab: Action on Finish -->
								<div id="tab-finish" class="antitoco-tab-content" style="display:none;">
									<?php 
									self::render_sections_by_id( $page, array( 'antitoco_finish_section' ) );
									?>
								</div>

								<!-- Tab: Appearance -->
								<div id="tab-appearance" class="antitoco-tab-content" style="display:none;">
									<?php 
									self::render_sections_by_id( $page, array( 'antitoco_appearance_section' ) );
									?>
								</div>

								<!-- Tab: Help & Ideas -->
								<div id="tab-help" class="antitoco-tab-content" style="display:none;">
									<?php self::render_help_ideas_tab(); ?>
								</div>
							</div>

							<div class="antitoco-submit-wrapper">
								<?php submit_button( __( 'Save Changes', 'antikton-topbar-countdown' ), 'primary large' ); ?>
							</div>
						</form>
					</div>

					<!-- Sidebar (Right Column) -->
					<div id="postbox-container-1" class="postbox-container">
						
						<!-- Quick Tips Box -->
						<div class="postbox antitoco-postbox">
							<div class="postbox-header"><h2 class="hndle"><span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e( 'Quick Tips', 'antikton-topbar-countdown' ); ?></h2></div>
							<div class="inside">
								<ul class="antitoco-tips-list">
									<li><strong><?php esc_html_e( 'Urgency:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Use the countdown to drive action.', 'antikton-topbar-countdown' ); ?></li>
									<li><strong><?php esc_html_e( 'Mobile:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Always test on smaller screens.', 'antikton-topbar-countdown' ); ?></li>
									<li><strong><?php esc_html_e( 'Contrast:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Ensure text is readable.', 'antikton-topbar-countdown' ); ?></li>
									<li><strong><?php esc_html_e( 'Clarity:', 'antikton-topbar-countdown' ); ?></strong> <?php esc_html_e( 'Keep messages short and sweet.', 'antikton-topbar-countdown' ); ?></li>
								</ul>
							</div>
						</div>

						<!-- Server Time Box -->
						<div class="postbox antitoco-postbox">
							<div class="postbox-header"><h2 class="hndle"><span class="dashicons dashicons-clock"></span> <?php esc_html_e( 'Server Time', 'antikton-topbar-countdown' ); ?></h2></div>
							<div class="inside">
								<div id="antitoco-server-time"><?php echo esc_html( current_time( 'Y-m-d H:i:s' ) ); ?></div>
								<input type="hidden" id="antitoco-server-time-offset" value="<?php echo esc_attr( time() - current_time('timestamp') ); ?>">
							</div>
						</div>

						<!-- Support Box -->
						<div class="postbox antitoco-postbox antitoco-support-box">
							<div class="postbox-header"><h2 class="hndle"><span class="dashicons dashicons-sos"></span> <?php esc_html_e( 'Support', 'antikton-topbar-countdown' ); ?></h2></div>
							<div class="inside">
								<p><?php esc_html_e( 'Need help or have a feature request?', 'antikton-topbar-countdown' ); ?></p>
								<p>
									<a href="https://wordpress.org/support/plugin/antikton-topbar-countdown/" target="_blank" class="button button-secondary button-hero antitoco-full-width-btn">
										<?php esc_html_e( 'Get Support', 'antikton-topbar-countdown' ); ?>
									</a>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

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
			'alternative_link_text'    => __( 'Learn More', 'antikton-topbar-countdown' ),
			'alternative_link_new_tab' => false,
			'content'                  => '',
			'main_link'                => '',
			'main_link_text'           => __( 'Learn More', 'antikton-topbar-countdown' ),
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
		if ( 'settings_page_antikton-topbar-countdown' !== $hook ) {
			return;
		}
		
		// WordPress color picker
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		
		// Admin CSS
		wp_enqueue_style(
			'antitoco-admin',
			plugins_url( 'assets/css/admin.css', __FILE__ ),
			array(),
			self::VERSION
		);
		
		// Add inline CSS for Help & Ideas tab
		$help_ideas_css = "
		.antitoco-help-ideas-wrapper {
			max-width: 1200px;
			margin: 0 auto;
		}
		.antitoco-help-intro {
			background: #fff;
			padding: 20px;
			border-left: 4px solid #2271b1;
			margin-bottom: 30px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		}
		.antitoco-help-intro h2 {
			margin-top: 0;
			color: #2271b1;
		}
		.antitoco-idea-category {
			background: #fff;
			padding: 25px;
			margin-bottom: 25px;
			border-radius: 4px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		}
		.antitoco-idea-category h3 {
			margin-top: 0;
			padding-bottom: 15px;
			border-bottom: 2px solid #f0f0f1;
			color: #1d2327;
			font-size: 18px;
		}
		.antitoco-idea-category h3 .dashicons {
			color: #2271b1;
			vertical-align: middle;
		}
		.antitoco-idea-card {
			background: #f6f7f7;
			padding: 20px;
			margin: 15px 0;
			border-radius: 4px;
			border-left: 4px solid #2271b1;
			transition: all 0.3s ease;
		}
		.antitoco-idea-card:hover {
			box-shadow: 0 2px 8px rgba(0,0,0,0.1);
			transform: translateX(5px);
		}
		.antitoco-idea-card h4 {
			margin: 0 0 15px 0;
			color: #1d2327;
			font-size: 16px;
		}
		.antitoco-idea-content p {
			margin: 8px 0;
			line-height: 1.6;
			color: #50575e;
		}
		.antitoco-idea-content strong {
			color: #1d2327;
			font-weight: 600;
		}
		.antitoco-tips-section {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: #fff;
		}
		.antitoco-tips-section h3 {
			color: #fff;
			border-bottom-color: rgba(255,255,255,0.3);
		}
		.antitoco-tips-section .dashicons {
			color: #ffd700 !important;
		}
		.antitoco-tips-list {
			list-style: none;
			padding: 0;
			margin: 15px 0 0 0;
		}
		.antitoco-tips-list li {
			padding: 12px 15px;
			margin: 10px 0;
			background: rgba(255,255,255,0.1);
			border-radius: 4px;
			backdrop-filter: blur(10px);
			line-height: 1.6;
		}
		/* Modern Admin Styles */
		.antitoco-settings-wrap { 
			max-width: 100%;
			margin: 0;
			padding: 0 20px;
			box-sizing: border-box;
		}
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
		.antitoco-tab-container { 
			background: #fff; 
			padding: 20px; 
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		}
		.antitoco-tab-content { 
			display: block; 
		}
		.antitoco-nav-tab-wrapper { 
			margin-bottom: 0 !important;
			border-bottom: 1px solid #ccd0d4;
		}
		.antitoco-nav-tab-wrapper .nav-tab { 
			display: inline-flex;
			align-items: center;
			gap: 5px;
		}
		.antitoco-nav-tab-wrapper .dashicons { 
			font-size: 16px;
			width: 16px;
			height: 16px;
		}
		.postbox.antitoco-postbox .dashicons {
			margin-right: 5px;
		}
		@media screen and (max-width: 1024px) {
			#post-body.columns-2 {
				flex-direction: column;
			}
			#postbox-container-1 {
				width: 100%;
				margin-top: 30px;
			}
		}
		.wp-picker-container { display: block; }
		.wp-picker-holder { position: absolute; z-index: 100; }
		.antitoco-postbox { border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
		.antitoco-postbox .postbox-header { border-bottom: 1px solid #f0f0f1; background: #fff; }
		.antitoco-postbox .hndle { font-size: 14px; display: flex; align-items: center; gap: 8px; }
		.antitoco-tips-list { margin: 0; padding: 0 10px; }
		.antitoco-tips-list li { margin-bottom: 10px; list-style: none; border-bottom: 1px dashed #eee; padding-bottom: 8px; }
		.antitoco-tips-list li:last-child { border-bottom: none; }
		.antitoco-full-width-btn { width: 100%; text-align: center; justify-content: center; margin-bottom: 10px !important; }
		.antitoco-support-box .dashicons-megaphone { color: #2271b1; margin-right: 5px; }
		";
		
		wp_add_inline_style( 'antitoco-admin', $help_ideas_css );
		
		// Admin JS
		wp_enqueue_script(
			'antitoco-admin',
			plugins_url( 'assets/js/admin.js', __FILE__ ),
			array( 'jquery', 'wp-color-picker' ),
			self::VERSION,
			true
		);
		
		// Add inline JavaScript for tab switching and server time
		$admin_inline_js = "
		jQuery(document).ready(function($) {
			// Tab Switching Logic
			$('.nav-tab-wrapper a').on('click', function(e) {
				e.preventDefault();
				
				// Active class
				$('.nav-tab-wrapper a').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
				
				// Show/Hide tabs
				$('.antitoco-tab-content').hide();
				var target = $(this).attr('href');
				$(target).fadeIn(200); // Smooth transition
			});
			
			// Function to update the server time display
			function updateServerTime() {
				var timeElement = document.getElementById('antitoco-server-time');
				if (!timeElement) return;
				
				// Get current browser time and adjust for server timezone offset
				var now = new Date();
				var offsetElement = document.getElementById('antitoco-server-time-offset');
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
				jQuery.get(ajaxurl, { action: 'antitoco_get_server_time' }, function(response) {
					if (response && response.success) {
						document.getElementById('antitoco-server-time-offset').value = response.offset;
					}
				});
			}, 60000); // Every minute
		});
		";
		
		wp_add_inline_script( 'antitoco-admin', $admin_inline_js );
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
			'antitoco-frontend',
			plugins_url( 'assets/css/frontend.css', __FILE__ ),
			array(),
			self::VERSION
		);
		
		// Frontend JS
		wp_enqueue_script(
			'antitoco-frontend',
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
			'antitoco-frontend',
			'antitocoData',
			array(
				'debugMode'               => ! empty( $settings['debug_mode'] ),
				'serverTime'              => time(), // Current server timestamp for sync
				'countdownTarget'         => $countdown_target,
				'labels'                  => array(
					'days'    => __( 'd', 'antikton-topbar-countdown' ),
					'hours'   => __( 'h', 'antikton-topbar-countdown' ),
					'minutes' => __( 'm', 'antikton-topbar-countdown' ),
					'seconds' => __( 's', 'antikton-topbar-countdown' ),
				),
				'showSeconds'             => ! empty( $settings['countdown_show_seconds'] ),
				'finishAction'            => ! empty( $settings['finish_action'] ) ? $settings['finish_action'] : 'hide',
				'alternativeText'         => ! empty( $settings['alternative_text'] ) ? $settings['alternative_text'] : '',
				'alternativeLink'         => ! empty( $settings['alternative_link'] ) ? $settings['alternative_link'] : '',
				'alternativeLinkText'     => ! empty( $settings['alternative_link_text'] ) ? $settings['alternative_link_text'] : __( 'Learn More', 'antikton-topbar-countdown' ),
				'alternativeLinkTab'      => ! empty( $settings['alternative_link_new_tab'] ),
				'alternativeColorsMode'   => ! empty( $settings['alternative_colors_mode'] ) ? $settings['alternative_colors_mode'] : 'same',
				'alternativeBgColor'      => $alternative_bg_color,
				'alternativeTextColor'    => $alternative_text_color,
			)
		);
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
		<div id="antitoco-topbar" class="antitoco-topbar" role="banner" style="background-color: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $text_color ); ?>; padding: <?php echo esc_attr( $padding_top ); ?>px <?php echo esc_attr( $padding_right ); ?>px <?php echo esc_attr( $padding_bottom ); ?>px <?php echo esc_attr( $padding_left ); ?>px;">
			<div class="antitoco-topbar-inner">
				<?php if ( $is_alternative ) : ?>
					<!-- Alternative content -->
					<div class="antitoco-content">
						<?php echo wp_kses_post( $settings['alternative_text'] ); ?>
					</div>
					
					<?php if ( ! empty( $settings['alternative_link'] ) ) : ?>
						<a href="<?php echo esc_url( $settings['alternative_link'] ); ?>" 
						   class="antitoco-link"
						   <?php echo ! empty( $settings['alternative_link_new_tab'] ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
							<?php echo ! empty( $settings['alternative_link_text'] ) ? esc_html( $settings['alternative_link_text'] ) : esc_html__( 'Learn More', 'antikton-topbar-countdown' ); ?>
						</a>
					<?php endif; ?>
				<?php else : ?>
					<!-- Normal content -->
					<div class="antitoco-content">
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
						<div class="antitoco-countdown-wrapper">
							<?php if ( ! empty( $settings['countdown_prefix'] ) ) : ?>
								<span class="antitoco-countdown-prefix"><?php echo wp_kses_post( $settings['countdown_prefix'] ); ?></span>
							<?php endif; ?>
							<span id="antitoco-countdown" class="antitoco-countdown"></span>
						</div>
					<?php endif; ?>
					
					<?php if ( ! empty( $settings['main_link'] ) ) : ?>
						<a href="<?php echo esc_url( $settings['main_link'] ); ?>" 
						   class="antitoco-link"
						   <?php echo ! empty( $settings['main_link_new_tab'] ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
							<?php echo ! empty( $settings['main_link_text'] ) ? esc_html( $settings['main_link_text'] ) : esc_html__( 'Learn More', 'antikton-topbar-countdown' ); ?>
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
Antikton_Topbar_Countdown::init();

// Register activation/deactivation hooks
register_activation_hook( __FILE__, array( 'Antikton_Topbar_Countdown', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Antikton_Topbar_Countdown', 'deactivate' ) );




