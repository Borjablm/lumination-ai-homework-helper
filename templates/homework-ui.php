<?php
/**
 * Homework Helper UI Template
 *
 * Frontend interface for homework helper.
 * Receives $lhh_title, $lhh_description, $lhh_button_text, $lhh_button_class, $lhh_heading_tag from shortcode.
 *
 * @package    LuminationHomeworkHelper
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="lumination-homework-helper" id="luminationHomeworkHelper">
	<div class="lumination-container">
		<?php if ( ! empty( $lhh_title ) || ! empty( $lhh_description ) ) : ?>
		<!-- Header -->
		<div class="lumination-header">
			<?php if ( ! empty( $lhh_title ) ) : ?>
				<<?php echo esc_attr( $lhh_heading_tag ); ?>><?php echo esc_html( $lhh_title ); ?></<?php echo esc_attr( $lhh_heading_tag ); ?>>
			<?php endif; ?>
			<?php if ( ! empty( $lhh_description ) ) : ?>
				<p class="lumination-description"><?php echo esc_html( $lhh_description ); ?></p>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<!-- Input Section -->
		<div class="lumination-input-section">
			<!-- Drop Zone -->
			<div class="lumination-drop-zone" id="homeworkDropZone">
				<span class="drop-zone-icon" aria-hidden="true">📄</span>
				<p class="drop-zone-label"><?php esc_html_e( 'Drop your file here or click to browse', 'lumination-ai-homework-helper' ); ?></p>
				<p class="drop-zone-hint"><?php esc_html_e( 'Supports PNG, JPEG, PDF (max 10MB)', 'lumination-ai-homework-helper' ); ?></p>
				<p class="drop-zone-hint"><?php esc_html_e( 'You can also paste images with Ctrl+V', 'lumination-ai-homework-helper' ); ?></p>
				<p class="drop-zone-filename" id="uploadedFilename"></p>
				<input
					type="file"
					id="homeworkFileInput"
					accept=".png,.jpg,.jpeg,.pdf,image/png,image/jpeg,application/pdf"
					aria-label="<?php esc_attr_e( 'Upload file', 'lumination-ai-homework-helper' ); ?>"
				/>
			</div>

			<!-- Text Input -->
			<div class="lumination-text-input-container">
				<label for="homeworkText" class="lumination-label">
					<?php esc_html_e( 'Or type/paste your problem:', 'lumination-ai-homework-helper' ); ?>
				</label>
				<textarea
					id="homeworkText"
					class="lumination-textarea"
					placeholder="<?php esc_attr_e( 'Enter your problem here...', 'lumination-ai-homework-helper' ); ?>"
					rows="6"
					aria-label="<?php esc_attr_e( 'Problem text', 'lumination-ai-homework-helper' ); ?>"
				></textarea>
			</div>

			<!-- Submit Button -->
			<div class="lumination-submit-container">
				<button
					id="solveButton"
					class="<?php echo esc_attr( $lhh_button_class ); ?>"
					disabled
					type="button"
					title="<?php esc_attr_e( 'Enter a problem or upload a file first', 'lumination-ai-homework-helper' ); ?>"
					data-disabled-hint="<?php esc_attr_e( 'Enter a problem or upload a file first', 'lumination-ai-homework-helper' ); ?>"
				>
					<?php echo esc_html( $lhh_button_text ); ?>
				</button>
			</div>
		</div>

		<!-- Solution Display -->
		<div id="solutionContainer" class="lumination-solution" style="display: none;">
			<div class="lumination-solution-title"><?php esc_html_e( 'Step-by-Step Solution', 'lumination-ai-homework-helper' ); ?></div>
			<div id="solutionContent" class="lumination-content" role="region" aria-live="polite">
				<!-- Solution will be rendered here -->
			</div>
		</div>
	</div>
</div>
