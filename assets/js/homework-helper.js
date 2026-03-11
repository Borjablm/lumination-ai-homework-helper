/**
 * Homework Helper UI
 *
 * Handles file uploads, text input, and solution rendering.
 *
 * @package Lumination
 * @since 1.0.0
 */

(function($) {
	'use strict';

	/**
	 * Current state
	 */
	const state = {
		currentFile: null,
		isProcessing: false,
		solveButtonText: ''
	};

	/**
	 * DOM elements
	 */
	let $dropZone, $fileInput, $filename, $textarea, $solveButton, $solutionContainer, $solutionContent;

	/**
	 * Initialize
	 */
	function init() {
		// Cache DOM elements
		$dropZone = $('#homeworkDropZone');
		$fileInput = $('#homeworkFileInput');
		$filename = $('#uploadedFilename');
		$textarea = $('#homeworkText');
		$solveButton = $('#solveButton');
		$solutionContainer = $('#solutionContainer');
		$solutionContent = $('#solutionContent');

		// Capture the original button text (may be customised via shortcode).
		state.solveButtonText = $solveButton.text().trim();

		// Setup handlers
		setupDropZone();
		setupTextInput();
		setupSubmit();
		setupPasteHandler();
	}

	/**
	 * Setup drop zone for file uploads
	 */
	function setupDropZone() {
		// Click to browse
		$dropZone.on('click', function(e) {
			if (e.target !== $fileInput[0]) {
				$fileInput.click();
			}
		});

		// Prevent defaults for drag events
		$dropZone.on('dragenter dragover dragleave drop', function(e) {
			e.preventDefault();
			e.stopPropagation();
		});

		// Visual feedback on drag over
		$dropZone.on('dragenter dragover', function() {
			$dropZone.addClass('drag-over');
		});

		$dropZone.on('dragleave drop', function() {
			$dropZone.removeClass('drag-over');
		});

		// Handle drop
		$dropZone.on('drop', function(e) {
			const files = e.originalEvent.dataTransfer.files;
			if (files && files.length > 0) {
				handleFile(files[0]);
			}
		});

		// Handle file input change
		$fileInput.on('change', function() {
			if (this.files && this.files.length > 0) {
				handleFile(this.files[0]);
			}
		});
	}

	/**
	 * Handle file selection
	 *
	 * @param {File} file Selected file
	 */
	function handleFile(file) {
		// Validate file type
		const allowedTypes = ['image/png', 'image/jpeg', 'application/pdf'];
		if (!allowedTypes.includes(file.type)) {
			alert(luminationData.i18n.invalidFileType);
			return;
		}

		// Validate file size (10MB max)
		const maxSize = 10 * 1024 * 1024;
		if (file.size > maxSize) {
			alert(luminationData.i18n.fileTooLarge);
			return;
		}

		// Store file
		state.currentFile = file;

		// Update UI
		$dropZone.addClass('has-file');
		$filename.text(file.name);

		// Enable submit button
		updateSubmitButton();
	}

	/**
	 * Setup text input
	 */
	function setupTextInput() {
		$textarea.on('input', updateSubmitButton);
	}

	/**
	 * Update submit button state
	 */
	function updateSubmitButton() {
		const text = $textarea.val().trim();
		const hasInput = state.currentFile || text;
		const disabled = !hasInput || state.isProcessing;
		$solveButton.prop('disabled', disabled);
		$solveButton.attr('title', disabled && !state.isProcessing ? $solveButton.data('disabled-hint') : '');
	}

	/**
	 * Setup submit button
	 */
	function setupSubmit() {
		$solveButton.on('click', handleSubmit);
	}

	/**
	 * Handle submit
	 */
	async function handleSubmit() {
		if (state.isProcessing) {
			return;
		}

		// Show loading state
		state.isProcessing = true;
		$solveButton.prop('disabled', true).text(luminationData.i18n.solving);
		$solutionContent.html('<div class="lumination-loading">' + luminationData.i18n.processing + '</div>');
		$solutionContainer.show();

		try {
			let problemText = '';

			// If file, extract text first
			if (state.currentFile) {
				problemText = await extractTextFromFile(state.currentFile);
			} else {
				// Use text input
				problemText = $textarea.val().trim();
			}

			if (!problemText) {
				throw new Error(luminationData.i18n.noInput);
			}

			// Get solution
			const solution = await getSolution(problemText);

			// Render solution with math
			await renderSolution(solution);

		} catch (error) {
			$solutionContent.html(
				'<div class="lumination-error">' +
				(error.message || luminationData.i18n.errorOccurred) +
				'</div>'
			);
		} finally {
			state.isProcessing = false;
			$solveButton.prop('disabled', false).text(state.solveButtonText);
		}
	}

	/**
	 * Extract text from file via API
	 *
	 * @param {File} file File to process
	 * @returns {Promise<string>} Extracted text
	 */
	function extractTextFromFile(file) {
		return new Promise(function(resolve, reject) {
			const reader = new FileReader();

			reader.onload = function() {
				// Get base64 data (remove data URL prefix)
				const base64Data = reader.result.split(',')[1];

				// Call API
				$.ajax({
					url: luminationData.ajaxUrl,
					type: 'POST',
					data: {
						action: 'lumination_extract_text',
						nonce: luminationData.nonce,
						file_data: base64Data,
						mime_type: file.type,
						page_url: window.location.href
					},
					success: function(response) {
						if (response.success && response.data.text) {
							resolve(response.data.text);
						} else {
							reject(new Error(response.data.message || luminationData.i18n.errorOccurred));
						}
					},
					error: function() {
						reject(new Error(luminationData.i18n.errorOccurred));
					}
				});
			};

			reader.onerror = function() {
				reject(new Error('Failed to read file'));
			};

			reader.readAsDataURL(file);
		});
	}

	/**
	 * Get solution via API
	 *
	 * @param {string} problemText Problem text
	 * @returns {Promise<string>} Solution
	 */
	function getSolution(problemText) {
		return new Promise(function(resolve, reject) {
			$.ajax({
				url: luminationData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'lumination_solve_homework',
					nonce: luminationData.nonce,
					problem: problemText,
					page_url: window.location.href
				},
				success: function(response) {
					if (response.success && response.data.solution) {
						resolve(response.data.solution);
					} else {
						reject(new Error(response.data.message || luminationData.i18n.errorOccurred));
					}
				},
				error: function() {
					reject(new Error(luminationData.i18n.errorOccurred));
				}
			});
		});
	}

	/**
	 * Render solution with math
	 *
	 * @param {string} markdownText Solution markdown
	 * @returns {Promise} Resolves when rendering is complete
	 */
	async function renderSolution(markdownText) {
		if (typeof window.LuminationMathRenderer !== 'undefined') {
			await window.LuminationMathRenderer.render($solutionContent[0], markdownText);
		} else {
			// Fallback to basic markdown rendering
			if (typeof marked !== 'undefined') {
				$solutionContent.html(marked.parse(markdownText));
			} else {
				$solutionContent.text(markdownText);
			}
		}
	}

	/**
	 * Setup paste handler for images
	 */
	function setupPasteHandler() {
		$(document).on('paste', function(e) {
			const items = e.originalEvent.clipboardData.items;
			if (!items) {
				return;
			}

			for (let i = 0; i < items.length; i++) {
				const item = items[i];
				if (item.type.indexOf('image') !== -1) {
					e.preventDefault();
					const file = item.getAsFile();
					if (file) {
						handleFile(file);
					}
					break;
				}
			}
		});
	}

	/**
	 * Initialize on DOM ready
	 */
	$(document).ready(function() {
		if ($('#luminationHomeworkHelper').length) {
			init();
		}
	});

})(jQuery);
