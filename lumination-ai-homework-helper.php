<?php
/**
 * Lumination AI Homework Helper
 *
 * AI-powered step-by-step solutions for math and science problems.
 * Requires Lumination Core (v1.0.0+) for API access, analytics, and math rendering.
 *
 * @package           LuminationHomeworkHelper
 * @author            Lumination Team
 * @license           GPL-3.0-or-later
 * @link              https://lumination.ai
 * @copyright         2026 Lumination Team
 *
 * @wordpress-plugin
 * Plugin Name:       Lumination AI Homework Helper
 * Description:       Step-by-step AI-powered solutions for math and science homework. Upload an image or PDF, paste a problem, or type it out — and get a clear, worked solution with LaTeX math rendering. Requires Lumination Core.
 * Version:           1.1.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Lumination Team
 * Author URI:        https://lumination.ai
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       lumination-ai-homework-helper
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Constants ────────────────────────────────────────────────────────────────

define( 'LUMINATION_HH_VERSION', '1.1.0' );
define( 'LUMINATION_HH_FILE',    __FILE__ );
define( 'LUMINATION_HH_DIR',     plugin_dir_path( __FILE__ ) );
define( 'LUMINATION_HH_URL',     plugin_dir_url( __FILE__ ) );

// ── Auto-update via GitHub releases ──────────────────────────────────────────

require_once LUMINATION_HH_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

PucFactory::buildUpdateChecker(
	'https://github.com/Borjablm/lumination-ai-homework-helper/',
	__FILE__,
	'lumination-ai-homework-helper'
);

// ── Dependency check + initialisation ────────────────────────────────────────

add_action(
	'plugins_loaded',
	function () {
		// Confirm Lumination Core is active and meets minimum version.
		$core_ok = function_exists( 'lumination_core' )
				&& defined( 'LUMINATION_CORE_VERSION' )
				&& version_compare( LUMINATION_CORE_VERSION, '1.0.0', '>=' );

		if ( ! $core_ok ) {
			add_action(
				'admin_notices',
				function () {
					if ( ! current_user_can( 'activate_plugins' ) ) {
						return;
					}
					$msg = sprintf(
						wp_kses(
							/* translators: %s: URL to Plugins admin page */
							__( '<strong>Lumination AI Homework Helper</strong> requires <strong>Lumination Core</strong> (v1.0.0+) to be installed and active. <a href="%s">Manage plugins &rarr;</a>', 'lumination-ai-homework-helper' ),
							array(
								'strong' => array(),
								'a'      => array( 'href' => array() ),
							)
						),
						esc_url( admin_url( 'plugins.php' ) )
					);
					echo '<div class="notice notice-error is-dismissible"><p>' . $msg . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			);
			return; // Do not initialise extension features.
		}

		// Core confirmed — load extension classes and register hooks.
		require_once LUMINATION_HH_DIR . 'includes/class-homework-helper.php';
		require_once LUMINATION_HH_DIR . 'includes/class-homework-ajax.php';
		require_once LUMINATION_HH_DIR . 'includes/class-homework-helper-settings.php';

		// Register admin tab in Core's settings panel.
		add_action(
			'lumination_core_admin_tabs_init',
			function () {
				Lumination_Core_Settings::register_tab(
					array(
						'id'       => 'homework-helper',
						'label'    => __( 'Homework Helper', 'lumination-ai-homework-helper' ),
						'callback' => array( 'Lumination_Homework_Helper_Settings', 'render_tab' ),
						'priority' => 10,
					)
				);
			}
		);

		// Register extension settings on Core's settings_init hook.
		add_action(
			'lumination_core_settings_init',
			array( 'Lumination_Homework_Helper_Settings', 'register_settings' )
		);

		// Initialise shortcode, assets, and AJAX handlers.
		Lumination_Homework_Helper::init();
	},
	20 // Priority 20 — runs after Core (priority 10) has fully loaded.
);
