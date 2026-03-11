<?php
/**
 * Lumination AI Homework Helper Uninstall
 *
 * Runs when the plugin is deleted via the WordPress admin.
 * Removes homework-helper-specific options.
 *
 * Usage data lives in the shared Core table (wp_lumination_usage) and is
 * removed when Lumination Core itself is deleted.
 *
 * @package    LuminationHomeworkHelper
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 */

// Only run when WordPress itself triggers uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove homework-helper-specific options.
delete_option( 'lumination_hh_use_theme_button' );

// Brand colours are managed by Core and removed in Core's uninstall.php.
// Rate-limit transients (lumination_rl_*) expire naturally via WordPress transient cleanup.
