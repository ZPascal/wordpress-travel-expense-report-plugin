=== Travel Expense Report ===
Contributors: pascalzimmermann
Tags: travel, expense, report, pdf, dpsg
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Creates a form for travel expense reports with dynamic items and mileage allowances. This plugin allows users to submit their travel expenses, including purchases, mileage, and train tickets, along with receipts. It generates a PDF report and sends it via email.

Features:
* Dynamic form with receipt uploads
* Automatic calculation of mileage and passenger allowances
* PDF generation with all receipts included
* Email delivery to admin and optionally to the user
* Configurable settings within WordPress admin
* Internationalization support

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure settings under Settings -> Travel Expense Report.
4. Add the shortcode `[travel_expense_report]` to any page or post.

== Frequently Asked Questions ==

= How do I use the shortcode? =
Simply place `[travel_expense_report]` on any page.

= Can I change the mileage rates? =
Yes, you can change the kilometer and passenger allowances in the plugin settings.

== Screenshots ==

1. The travel expense report form on the frontend.
2. Admin settings page.

== Changelog ==

= 1.0.0 =
* Initial release.
