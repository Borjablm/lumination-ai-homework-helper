<?php
/**
 * Homework Helper Settings Tab
 *
 * Renders the "Homework Helper" tab in the Core admin panel.
 * API configuration and appearance live in Core tabs, so this tab
 * focuses on usage reference and capability settings.
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
 * Homework Helper admin settings tab.
 *
 * @since 1.0.0
 */
class Lumination_Homework_Helper_Settings {

	/**
	 * Register Homework Helper settings with WordPress.
	 *
	 * Hook this into 'lumination_core_settings_init'.
	 *
	 * @since 1.1.0
	 */
	public static function register_settings() {
		register_setting( 'lumination_hh_settings', 'lumination_hh_use_theme_button', array(
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'default'           => 0,
		) );
	}

	/**
	 * Render the Homework Helper tab body.
	 *
	 * Called by the Core tab registry via the registered callback.
	 *
	 * @since 1.0.0
	 */
	public static function render_tab() {
		?>
		<div style="max-width: 800px; margin-top: 20px;">

			<?php /* ── Shortcode usage ── */ ?>
			<div class="card">
				<h2><?php esc_html_e( 'Shortcode', 'lumination-ai-homework-helper' ); ?></h2>
				<p>
					<?php esc_html_e( 'Embed the solver on any page or post:', 'lumination-ai-homework-helper' ); ?>
				</p>
				<p>
					<code>[lumination_homework_helper]</code>
				</p>
				<p class="description">
					<?php esc_html_e( 'The shortcode renders an input UI with file upload, drag-and-drop, clipboard paste (Ctrl+V), and step-by-step AI solutions with LaTeX math rendering.', 'lumination-ai-homework-helper' ); ?>
				</p>
			</div>

			<?php /* ── Shortcode parameters ── */ ?>
			<div class="card" style="margin-top: 20px;">
				<h2><?php esc_html_e( 'Shortcode Parameters', 'lumination-ai-homework-helper' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Use these optional parameters to customise the text per page. This lets you target different SEO keywords on different landing pages.', 'lumination-ai-homework-helper' ); ?>
				</p>
				<table class="widefat striped" style="margin-top: 12px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Parameter', 'lumination-ai-homework-helper' ); ?></th>
							<th><?php esc_html_e( 'Default', 'lumination-ai-homework-helper' ); ?></th>
							<th><?php esc_html_e( 'Description', 'lumination-ai-homework-helper' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><code>title</code></td>
							<td><em><?php esc_html_e( '(hidden)', 'lumination-ai-homework-helper' ); ?></em></td>
							<td><?php esc_html_e( 'Heading displayed above the tool. Hidden when omitted.', 'lumination-ai-homework-helper' ); ?></td>
						</tr>
						<tr>
							<td><code>description</code></td>
							<td><em><?php esc_html_e( '(hidden)', 'lumination-ai-homework-helper' ); ?></em></td>
							<td><?php esc_html_e( 'Subtitle paragraph below the heading. Hidden when omitted.', 'lumination-ai-homework-helper' ); ?></td>
						</tr>
						<tr>
							<td><code>button_text</code></td>
							<td><code><?php esc_html_e( 'Solve Problem', 'lumination-ai-homework-helper' ); ?></code></td>
							<td><?php esc_html_e( 'Label on the submit button.', 'lumination-ai-homework-helper' ); ?></td>
						</tr>
					</tbody>
				</table>

				<h3 style="margin-top: 16px;"><?php esc_html_e( 'Examples', 'lumination-ai-homework-helper' ); ?></h3>
				<pre style="background:#f0f0f0;padding:12px;border-radius:4px;overflow-x:auto;font-size:12px;"><?php echo esc_html(
'[lumination_homework_helper title="AI Math Solver" description="Upload or type any math problem to get a step-by-step solution." button_text="Solve"]

[lumination_homework_helper title="Chemistry Quiz Solver" description="Get detailed answers for chemistry questions."]

[lumination_homework_helper title="Homework Helper" description="Upload an image, PDF, or type your homework problem to get step-by-step AI-powered solutions."]'
); ?></pre>
				<p class="description">
					<?php esc_html_e( 'Omit all parameters for a clean, keyword-neutral interface that works in any context.', 'lumination-ai-homework-helper' ); ?>
				</p>
			</div>

			<?php /* ── Appearance ── */ ?>
			<div class="card" style="margin-top: 20px;">
				<h2><?php esc_html_e( 'Appearance', 'lumination-ai-homework-helper' ); ?></h2>
				<p>
					<?php
					$appearance_url = admin_url( 'tools.php?page=' . LUMINATION_CORE_ADMIN_SLUG . '&tab=appearance' );
					printf(
						wp_kses(
							/* translators: %s: link to Appearance tab */
							__( 'Brand colours are managed on the <a href="%s">Appearance tab</a> and apply to all Lumination tools.', 'lumination-ai-homework-helper' ),
							array( 'a' => array( 'href' => array() ) )
						),
						esc_url( $appearance_url )
					);
					?>
				</p>

				<form action="options.php" method="post" style="margin-top: 12px;">
					<?php settings_fields( 'lumination_hh_settings' ); ?>
					<label>
						<input
							type="checkbox"
							name="lumination_hh_use_theme_button"
							value="1"
							<?php checked( 1, (int) get_option( 'lumination_hh_use_theme_button', 0 ) ); ?>
						/>
						<?php esc_html_e( 'Use theme button styles instead of Lumination button styling', 'lumination-ai-homework-helper' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'When enabled, the submit button inherits your WordPress theme\'s button design.', 'lumination-ai-homework-helper' ); ?>
					</p>
					<?php submit_button( __( 'Save', 'lumination-ai-homework-helper' ), 'secondary', 'submit', false ); ?>
				</form>
			</div>

			<?php /* ── Supported file types ── */ ?>
			<div class="card" style="margin-top: 20px;">
				<h2><?php esc_html_e( 'Supported Input', 'lumination-ai-homework-helper' ); ?></h2>
				<ul>
					<li><?php esc_html_e( 'Images: PNG, JPEG (max 10 MB)', 'lumination-ai-homework-helper' ); ?></li>
					<li><?php esc_html_e( 'Documents: PDF (max 10 MB)', 'lumination-ai-homework-helper' ); ?></li>
					<li><?php esc_html_e( 'Text: typed or pasted problems (max 10,000 characters)', 'lumination-ai-homework-helper' ); ?></li>
				</ul>
			</div>

			<?php /* ── Access control info ── */ ?>
			<div class="card" style="margin-top: 20px;">
				<h2><?php esc_html_e( 'Access Control', 'lumination-ai-homework-helper' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: %s: filter name in code format */
						esc_html__( 'By default all visitors can use the tool. To restrict access, filter %s in your theme or a custom plugin:', 'lumination-ai-homework-helper' ),
						'<code>lumination_core_can_submit</code>'
					);
					?>
				</p>
				<pre style="background:#f0f0f0;padding:12px;border-radius:4px;overflow-x:auto;font-size:12px;"><?php echo esc_html(
'add_filter( \'lumination_core_can_submit\', function ( $allowed, $capability, $user_id ) {
    if ( \'homework\' === $capability ) {
        return is_user_logged_in(); // logged-in users only
    }
    return $allowed;
}, 10, 3 );'
); ?></pre>
			</div>

			<?php /* ── API configuration reminder ── */ ?>
			<div class="card" style="margin-top: 20px;">
				<h2><?php esc_html_e( 'API Configuration', 'lumination-ai-homework-helper' ); ?></h2>
				<p>
					<?php
					$config_url = admin_url( 'tools.php?page=' . LUMINATION_CORE_ADMIN_SLUG . '&tab=config' );
					printf(
						wp_kses(
							/* translators: %s: link to API Configuration tab */
							__( 'API credentials are managed on the <a href="%s">API Configuration tab</a>.', 'lumination-ai-homework-helper' ),
							array( 'a' => array( 'href' => array() ) )
						),
						esc_url( $config_url )
					);
					?>
				</p>
			</div>

		</div>
		<?php
	}
}
