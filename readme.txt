=== Topbar Countdown Notice ===
Contributors: antikton
Tags: topbar, countdown, notice, notification, schedule
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.0.4
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.buymeacoffee.com/antikton

A fully functional WordPress plugin that displays a customizable top bar with optional countdown timer and advanced scheduling capabilities.

== Description ==

Topbar Countdown Notice is a lightweight yet powerful plugin that allows you to display a customizable top bar on your WordPress site. It features an integrated countdown timer and advanced scheduling options, making it perfect for announcements, sales, maintenance notices, and more.

**Core Features**

*   **Global Top Bar:** Easily display a notification bar across your site.
*   **Flexible Scheduling:** Set start and end dates/times for the bar to appear automatically.
*   **Countdown Timer:** Drive urgency with a countdown timer targeting a specific date or the end date.
*   **Alternative Content:** Choose to hide the bar or show different content when the timer finishes (e.g., reveal discount coupons, announce "Sale is LIVE!", or show "Offer Ended" messages).
*   **Full Customization:** visual control over background colors, text colors, and padding.
*   **Rich Content Editor:** Use the familiar WordPress editor for your bar's content.

**Action on Finish**

When the countdown or scheduled time ends, you have full control:
*   **Hide the bar:** The bar disappears automatically.
*   **Show alternative content:** Replace the countdown/message with a new message and link (perfect for revealing discount codes when a sale starts, showing "Sale is LIVE!" messages, or displaying "Missed the sale?" notifications).

== Installation ==

1.  Upload the `topbar-countdown-notice` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to **Settings > Topbar Countdown** to configure the plugin.

== Frequently Asked Questions ==

= Can I use this for a scheduled sale? =
Yes! Set the Start Date/Time to when your sale begins and the End Date/Time to when it ends. You can enable the countdown to target the End Date.

= What happens when the countdown ends? =
You can choose to either hide the bar completely or show an alternative message (like "This offer has expired").

= Is it mobile friendly? =
Yes, the top bar is designed to be responsive and works on mobile devices.

= Can I reveal a discount code after the countdown ends? =
Absolutely! This is a popular use case. Set up a countdown to build anticipation, then use the "Show alternative content" option to reveal your discount code, coupon, or special offer when the timer reaches zero. You can even change the colors to make it stand out!

== Screenshots ==

1. Active countdown bar displaying a limited-time offer with countdown timer
2. Alternative content shown after countdown expires with discount code revealed
3. General & Schedule settings panel - configure activation and scheduling
4. Content & Countdown settings panel - set up messages and countdown timer
5. Action on Finish settings panel - define what happens when countdown ends
6. Appearance settings panel - customize colors, padding, and styling

== Changelog ==

= 1.0.4 =
*   Added: Professional banner images in multiple sizes for WordPress.org (772x250, 1544x500, 1200x300)
*   Improved: Reorganized assets - moved screenshots and banners to assets/ folder for WordPress.org compliance
*   Improved: Updated all documentation to reflect new asset structure

= 1.0.3 =
*   Added: Composer support with composer.json for Packagist installation
*   Added: Upgrade Notice section for WordPress.org compliance
*   Added: Donate link support in plugin metadata
*   Improved: Screenshots renamed to WordPress.org standard format (screenshot-1.png through screenshot-6.png)
*   Improved: Full WordPress.org readme validator compliance
*   Improved: Enhanced documentation for coupon reveal use cases

= 1.0.2 =
*   Added: Comprehensive README.md documentation for GitHub
*   Added: 6 professional screenshots showing frontend and admin panels
*   Improved: Enhanced documentation with usage examples and configuration details

= 1.0.1 =
*   Fixed: Alternative background color now applies correctly when content expires
*   Improved: Full compatibility with WordPress Plugin Check (PCP)
*   Improved: Removed deprecated load_plugin_textdomain function
*   Improved: All outputs properly escaped for security
*   Improved: Simplified codebase by removing unnecessary hooks
*   Updated: Hook names now use proper WordPress prefix standards

= 1.0.0 =
*   Initial release.
*   Complete scheduling system.
*   Countdown timer functionality.
*   Alternative content display.
*   Full customization options.

== Upgrade Notice ==

= 1.0.4 =
Added professional banner images and reorganized assets for WordPress.org. All screenshots and banners now in assets/ folder following WordPress.org best practices.

= 1.0.3 =
Added Composer support for easier installation via Packagist. Screenshots renamed to WordPress.org standard format. Full readme validator compliance achieved.

= 1.0.2 =
Enhanced documentation with comprehensive README.md, 6 professional screenshots, and detailed usage examples. Recommended update for better understanding of plugin features.

= 1.0.1 =
Important bug fix for alternative background color display. Security improvements and WordPress Plugin Check compatibility. Recommended update.

= 1.0.0 =
Initial release with full scheduling, countdown timer, and customization features.
