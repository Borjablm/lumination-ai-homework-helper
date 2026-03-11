=== Lumination AI Homework Helper ===
Contributors: luminationteam
Tags: homework, ai, math, education, latex
Requires at least: 6.4
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Step-by-step AI solutions for math and science homework. Upload an image, PDF, or type your problem — with full LaTeX rendering.

== Description ==

Lumination AI Homework Helper gives students a simple, clean interface to get step-by-step solutions to math and science problems.

**Requires Lumination Core (free)** — install it first.

= Features =

* **File upload** — drag-and-drop or browse for PNG, JPEG, or PDF files (max 10 MB)
* **Clipboard paste** — paste a screenshot directly with Ctrl+V
* **Text input** — type or paste any problem up to 10,000 characters
* **LaTeX rendering** — solutions display beautifully with inline and display math via MathJax (bundled in Core)
* **Step-by-step format** — AI responses are structured with clear headings and working
* **Access control** — restrict via the `lumination_core_can_submit` filter

= Shortcode =

Place `[lumination_homework_helper]` on any page or post.

= Getting Started =

1. Install and activate **Lumination Core**.
2. Go to **Tools → Lumination → API Configuration** and enter your API key and base URL.
3. Install and activate **Lumination AI Homework Helper**.
4. Add `[lumination_homework_helper]` to any page.

== Installation ==

1. Install **Lumination Core** first and configure your API credentials.
2. Upload the `lumination-ai-homework-helper` folder to `/wp-content/plugins/`.
3. Activate the plugin via the Plugins screen.
4. Add `[lumination_homework_helper]` to any page or post.

== Frequently Asked Questions ==

= Do I need Lumination Core? =

Yes. The homework helper requires Lumination Core for API access, analytics, and math rendering. If Core is not active, the plugin shows an admin notice and the shortcode outputs nothing.

= What file types are supported? =

PNG, JPEG, and PDF files up to 10 MB.

= Can I restrict who can use the homework helper? =

Yes. Use the `lumination_core_can_submit` filter. See the Homework Helper tab in Tools → Lumination for a code example.

= Where is my usage data? =

In **Tools → Lumination → Usage Analytics** (provided by Lumination Core).

== Changelog ==

= 1.0.0 =
* Initial release.
* Shortcode `[lumination_homework_helper]`.
* File upload with drag-and-drop and clipboard paste support.
* Text input up to 10,000 characters.
* LaTeX math rendering via Core's MathJax integration.
* Access control via `lumination_core_can_submit` filter.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
