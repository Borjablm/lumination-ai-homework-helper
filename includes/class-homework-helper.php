<?php
/**
 * Homework Helper
 *
 * Registers the [lumination_homework_helper] shortcode, enqueues frontend assets,
 * and delegates AJAX handling to Lumination_Homework_Ajax.
 *
 * Math rendering is handled by Lumination Core (MathJax + math-renderer.js).
 * API requests and analytics are routed through Core static classes.
 *
 * @package    LuminationHomeworkHelper
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Homework Helper class.
 *
 * @since 1.0.0
 */
class Lumination_Homework_Helper {

	/**
	 * Initialize the extension.
	 *
	 * Called from the main plugin file after confirming Core is active.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		add_shortcode( 'lumination_homework_helper', array( __CLASS__, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );

		// Delegate AJAX registration to the AJAX class.
		Lumination_Homework_Ajax::register();
	}

	// -------------------------------------------------------------------------
	// Shortcode
	// -------------------------------------------------------------------------

	/**
	 * Render the homework helper shortcode output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public static function render_shortcode( $atts ) {
		if ( ! Lumination_Core_Security::can_submit( 'homework' ) ) {
			return '<div class="lumination-notice lumination-notice-error">' .
				esc_html__( 'Access is restricted on this site. Please contact the site administrator.', 'lumination-ai-homework-helper' ) .
				'</div>';
		}

		if ( ! Lumination_Core_API::is_configured() ) {
			return '<div class="lumination-notice lumination-notice-error">' .
				esc_html__( 'Lumination is not configured. Please ask the site administrator to set up the API connection.', 'lumination-ai-homework-helper' ) .
				'</div>';
		}

		$atts = shortcode_atts(
			array(
				'title'       => '',
				'description' => '',
				'button_text' => __( 'Solve Problem', 'lumination-ai-homework-helper' ),
				'heading'     => '',
			),
			$atts,
			'lumination_homework_helper'
		);

		// Sanitize shortcode attributes.
		$lhh_title        = sanitize_text_field( $atts['title'] );
		$lhh_description  = sanitize_text_field( $atts['description'] );
		$lhh_button_text  = sanitize_text_field( $atts['button_text'] );
		$lhh_heading_tag  = Lumination_Core_Settings::get_heading_tag( sanitize_text_field( $atts['heading'] ) );
		$lhh_button_class = get_option( 'lumination_hh_use_theme_button', '' )
			? 'wp-element-button'
			: 'lumination-btn-primary';

		ob_start();
		include LUMINATION_HH_DIR . 'templates/homework-ui.php';
		return ob_get_clean();
	}

	/**
	 * Build inline CSS from admin color settings.
	 *
	 * @since 1.1.0
	 * @return string Inline CSS string (without <style> tags), or empty if no colors set.
	 */
	private static function get_color_css() {
		$primary    = Lumination_Core_Settings::get_color( 'primary' );
		$hover      = Lumination_Core_Settings::get_color( 'primary_hover' );
		$text       = Lumination_Core_Settings::get_color( 'button_text' );
		$background = Lumination_Core_Settings::get_color( 'tool_background' );
		$tool_text  = Lumination_Core_Settings::get_color( 'tool_text' );

		$vars = array();
		if ( $primary ) {
			$vars[] = '--lumination-btn-bg:' . sanitize_hex_color( $primary );
			$vars[] = '--lumination-btn-border:' . sanitize_hex_color( $primary );
		}
		if ( $hover ) {
			$vars[] = '--lumination-btn-bg-hover:' . sanitize_hex_color( $hover );
			$vars[] = '--lumination-btn-border-hover:' . sanitize_hex_color( $hover );
		}
		if ( $text ) {
			$vars[] = '--lumination-btn-text:' . sanitize_hex_color( $text );
		}
		if ( $background ) {
			$vars[] = '--lumination-bg:' . sanitize_hex_color( $background );
		}
		if ( $tool_text ) {
			$vars[] = '--lumination-text:' . sanitize_hex_color( $tool_text );
		}

		if ( empty( $vars ) ) {
			return '';
		}

		return '.lumination-homework-helper{' . implode( ';', $vars ) . '}';
	}

	// -------------------------------------------------------------------------
	// Assets
	// -------------------------------------------------------------------------

	/**
	 * Enqueue frontend scripts and styles on pages that use the shortcode.
	 *
	 * MathJax + math-renderer.js are registered by Core and activated here
	 * via Lumination_Core_Math::enqueue(). Extension-specific scripts
	 * (marked, purify, homework-helper.js) are loaded separately.
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_assets() {
		global $post;

		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}

		if ( ! has_shortcode( $post->post_content, 'lumination_homework_helper' ) ) {
			return;
		}

		// CSS.
		wp_enqueue_style(
			'lumination-homework',
			LUMINATION_HH_URL . 'assets/css/homework-helper.css',
			array(),
			LUMINATION_HH_VERSION
		);

		// Inline color overrides from admin settings.
		$color_css = self::get_color_css();
		if ( $color_css ) {
			wp_add_inline_style( 'lumination-homework', $color_css );
		}

		// Markdown parser (bundled locally).
		wp_enqueue_script(
			'lumination-marked',
			LUMINATION_HH_URL . 'assets/js/vendor/marked.min.js',
			array(),
			'11.0.0',
			true
		);

		// HTML sanitizer (bundled locally).
		wp_enqueue_script(
			'lumination-purify',
			LUMINATION_HH_URL . 'assets/js/vendor/purify.min.js',
			array(),
			'3.0.6',
			true
		);

		// Opt in to Core math rendering (MathJax config + math-renderer.js).
		// Core's register_scripts() has already run; this call enqueues both
		// mathjax and lumination-core-math-renderer, and hooks the wp_head config.
		Lumination_Core_Math::enqueue( 'lumination-homework' );

		// Main homework helper JS — depends on jQuery, markdown, sanitizer, and Core math renderer.
		wp_enqueue_script(
			'lumination-homework',
			LUMINATION_HH_URL . 'assets/js/homework-helper.js',
			array( 'jquery', 'lumination-marked', 'lumination-purify', 'lumination-core-math-renderer' ),
			LUMINATION_HH_VERSION,
			true
		);

		wp_localize_script(
			'lumination-homework',
			'luminationData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'lumination_homework' ),
				'i18n'    => array(
					'solving'         => __( 'Solving…', 'lumination-ai-homework-helper' ),
					'processing'      => __( 'Processing your problem…', 'lumination-ai-homework-helper' ),
					'invalidFileType' => __( 'Invalid file type. Please upload PNG, JPEG, or PDF.', 'lumination-ai-homework-helper' ),
					'fileTooLarge'    => __( 'File too large. Maximum size is 10MB.', 'lumination-ai-homework-helper' ),
					'errorOccurred'   => __( 'An error occurred. Please try again.', 'lumination-ai-homework-helper' ),
					'noInput'         => __( 'Please provide a problem to solve (text or file).', 'lumination-ai-homework-helper' ),
				),
			)
		);
	}
}
