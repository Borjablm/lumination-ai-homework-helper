<?php
/**
 * Homework Helper AJAX Handlers
 *
 * Static class wrapping the two AJAX actions used by the homework helper.
 * All API calls are routed through Lumination_Core_API; credentials are
 * never read here.
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
 * Homework Helper AJAX handler class.
 *
 * @since 1.0.0
 */
class Lumination_Homework_Ajax {

	/**
	 * Register AJAX hooks (called from Lumination_Homework_Helper::init).
	 *
	 * @since 1.0.0
	 */
	public static function register() {
		add_action( 'wp_ajax_lumination_extract_text',        array( __CLASS__, 'extract_text' ) );
		add_action( 'wp_ajax_nopriv_lumination_extract_text', array( __CLASS__, 'extract_text' ) );
		add_action( 'wp_ajax_lumination_solve_homework',        array( __CLASS__, 'solve_homework' ) );
		add_action( 'wp_ajax_nopriv_lumination_solve_homework', array( __CLASS__, 'solve_homework' ) );
	}

	// -------------------------------------------------------------------------
	// Extract text from image / PDF
	// -------------------------------------------------------------------------

	/**
	 * AJAX handler: extract text (or solve) from an uploaded file.
	 *
	 * Expects POST: nonce, file_data (base64), mime_type, page_url.
	 * Returns: { text: string } on success.
	 *
	 * @since 1.0.0
	 */
	public static function extract_text() {
		check_ajax_referer( 'lumination_homework', 'nonce' );

		if ( ! Lumination_Core_Security::can_submit( 'homework' ) ) {
			Lumination_Core_Security::log_event( 'Unauthorized homework extract attempt' );
			wp_send_json_error( array( 'message' => __( 'Access denied.', 'lumination-ai-homework-helper' ) ) );
		}

		$rate_check = Lumination_Core_Security::check_rate_limit( 'extract_text', 10, MINUTE_IN_SECONDS );
		if ( is_wp_error( $rate_check ) ) {
			wp_send_json_error( array( 'message' => $rate_check->get_error_message() ) );
		}

		// base64 data — cannot use sanitize_text_field (destroys data); custom sanitizer used below.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$file_data = isset( $_POST['file_data'] ) ? $_POST['file_data'] : '';
		$mime_type = isset( $_POST['mime_type'] ) ? sanitize_text_field( wp_unslash( $_POST['mime_type'] ) ) : '';

		if ( empty( $file_data ) ) {
			wp_send_json_error( array( 'message' => __( 'No file data provided.', 'lumination-ai-homework-helper' ) ) );
		}

		$file_data = Lumination_Core_Security::sanitize_base64( $file_data );
		if ( empty( $file_data ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid file data.', 'lumination-ai-homework-helper' ) ) );
		}

		$allowed_mimes = array( 'image/png', 'image/jpeg', 'application/pdf' );
		if ( ! in_array( $mime_type, $allowed_mimes, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid file type.', 'lumination-ai-homework-helper' ) ) );
		}

		// Call Core API: upload-solve endpoint with image/PDF base64.
		$result = Lumination_Core_API::request(
			'/lumination-ai/api/v1/features/upload-solve/solve-problem',
			array(
				'resized_img_attachment_b64' => $file_data,
				'text_input'                  => 'Extract and describe the content of this image.',
			),
			'lumination-hh-extract'
		);

		if ( is_wp_error( $result ) ) {
			Lumination_Core_Security::log_event( 'File extraction API error', array( 'error' => $result->get_error_message() ) );
			wp_send_json_error( array( 'message' => __( 'Failed to extract text from file. Please try again.', 'lumination-ai-homework-helper' ) ) );
		}

		// API-level error field.
		if ( isset( $result['success'] ) && ! $result['success'] ) {
			$msg = isset( $result['error'] ) ? $result['error'] : 'Unknown API error';
			wp_send_json_error( array( 'message' => $msg ) );
		}

		$text = isset( $result['output']['answer'] ) ? $result['output']['answer'] : '';
		if ( empty( $text ) ) {
			wp_send_json_error( array( 'message' => __( 'No text could be extracted from the file.', 'lumination-ai-homework-helper' ) ) );
		}

		// Log usage.
		$page_url   = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';
		$input_type = ( 'application/pdf' === $mime_type ) ? 'pdf' : 'image';

		Lumination_Core_Analytics::log_usage(
			'homework_helper',
			$page_url,
			isset( $result['token_count_input'] ) ? (int) $result['token_count_input'] : 0,
			isset( $result['token_count_output'] ) ? (int) $result['token_count_output'] : 0,
			isset( $result['credits_charged'] ) ? (float) $result['credits_charged'] : 0,
			$input_type
		);

		wp_send_json_success( array( 'text' => $text ) );
	}

	// -------------------------------------------------------------------------
	// Solve homework problem (text input)
	// -------------------------------------------------------------------------

	/**
	 * AJAX handler: solve a homework problem from text.
	 *
	 * Expects POST: nonce, problem (text), page_url.
	 * Returns: { solution: string } on success.
	 *
	 * @since 1.0.0
	 */
	public static function solve_homework() {
		check_ajax_referer( 'lumination_homework', 'nonce' );

		if ( ! Lumination_Core_Security::can_submit( 'homework' ) ) {
			Lumination_Core_Security::log_event( 'Unauthorized homework solve attempt' );
			wp_send_json_error( array( 'message' => __( 'Access denied.', 'lumination-ai-homework-helper' ) ) );
		}

		$rate_check = Lumination_Core_Security::check_rate_limit( 'solve_homework', 10, MINUTE_IN_SECONDS );
		if ( is_wp_error( $rate_check ) ) {
			wp_send_json_error( array( 'message' => $rate_check->get_error_message() ) );
		}

		$problem = isset( $_POST['problem'] ) ? sanitize_textarea_field( wp_unslash( $_POST['problem'] ) ) : '';

		if ( empty( $problem ) ) {
			wp_send_json_error( array( 'message' => __( 'No problem provided.', 'lumination-ai-homework-helper' ) ) );
		}

		if ( strlen( $problem ) > 10000 ) {
			wp_send_json_error( array( 'message' => __( 'Problem text too long (max 10,000 characters).', 'lumination-ai-homework-helper' ) ) );
		}

		// Call Core API: agent/chat for text-only solving.
		$result = Lumination_Core_API::request(
			'/lumination-ai/api/v1/agent/chat',
			array(
				'persist'  => false,
				'stream'   => false,
				'messages' => array(
					array(
						'role'    => 'user',
						/* translators: %s: homework problem text */
						'content' => sprintf( __( "Solve this step by step, showing your work clearly. Use proper headings and formatting:\n\n%s", 'lumination-ai-homework-helper' ), $problem ),
					),
				),
			),
			'lumination-hh-solve'
		);

		if ( is_wp_error( $result ) ) {
			Lumination_Core_Security::log_event( 'Homework solve API error', array( 'error' => $result->get_error_message() ) );
			wp_send_json_error( array( 'message' => __( 'Failed to get solution. Please try again.', 'lumination-ai-homework-helper' ) ) );
		}

		// Response is double-nested: response.response.
		$solution = isset( $result['response']['response'] ) ? $result['response']['response'] : '';

		if ( empty( $solution ) ) {
			wp_send_json_error( array( 'message' => __( 'No solution received from the API.', 'lumination-ai-homework-helper' ) ) );
		}

		// Log usage.
		$page_url = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : '';

		Lumination_Core_Analytics::log_usage(
			'homework_helper',
			$page_url,
			isset( $result['token_count_input'] ) ? (int) $result['token_count_input'] : 0,
			isset( $result['token_count_output'] ) ? (int) $result['token_count_output'] : 0,
			isset( $result['credits_charged'] ) ? (float) $result['credits_charged'] : 0,
			'text'
		);

		wp_send_json_success( array( 'solution' => $solution ) );
	}
}
