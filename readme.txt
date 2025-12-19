=== Antikton Topbar Countdown ===
Contributors: antikton
Tags: topbar, countdown, notice, notification, schedule
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.1.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.buymeacoffee.com/antikton

A fully functional WordPress plugin that displays a customizable top bar with optional countdown timer and advanced scheduling capabilities.

== Description ==

Antikton Topbar Countdown is a lightweight yet powerful plugin that allows you to display a customizable top bar on your WordPress site. It features an integrated countdown timer and advanced scheduling options, making it perfect for announcements, sales, maintenance notices, and more.

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

1.  Upload the `antikton-topbar-countdown` folder to the `/wp-content/plugins/` directory.
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

= 1.1.1 =
*   Updated: Modern, eye-catching plugin banners with updated "Antikton Topbar Countdown" branding
*   Improved: GitHub Actions workflow now automatically extracts changelog from readme.txt
*   Improved: GitHub Actions now generates plugin ZIP file and attaches it to releases
*   Improved: Enhanced release notes generation with version-specific changelog integration

= 1.1.0 =
*   **IMPORTANT:** WordPress.org compliance update - All functionality preserved
*   Changed: Class name from `Topbar_Countdown_Notice` to `Antikton_Topbar_Countdown` for uniqueness
*   Changed: Prefix from `tcn` to `antitoco` (8 characters) across all functions, options, and CSS classes
*   Changed: JavaScript object from `tcnData` to `antitocoData`
*   Changed: All AJAX actions now use `antitoco_` prefix
*   Changed: All settings groups now use `antitoco_` prefix
*   Changed: All CSS classes from `.tcn-*` to `.antitoco-*`
*   Changed: All script/style handles from `tcn-*` to `antitoco-*`
*   Removed: Custom CSS functionality per WordPress.org guidelines (use Customizer instead)
*   Improved: Topbar positioning changed from `fixed` to `relative` for better compatibility
*   Improved: All variables properly escaped for enhanced security
*   Added: Settings link in plugins list for easier access
*   Fixed: Admin CSS selectors updated to match new class structure
*   Security: Comprehensive escaping review - all outputs properly sanitized

= 1.0.10 =
*   Changed: Plugin renamed to "Antikton Topbar Countdown" for better distinction and WordPress.org compliance
*   Changed: Plugin slug updated from "topbar-countdown-notice" to "antikton-topbar-countdown"
*   Changed: Text domain updated to "antikton-topbar-countdown" across all files
*   Changed: Main plugin file renamed to "antikton-topbar-countdown.php"
*   Changed: All language files renamed to match new text domain (19 files: 9 .po + 9 .mo + 1 .pot)
*   Improved: Inline styles and scripts now properly enqueued using wp_add_inline_style() and wp_add_inline_script()
*   Improved: WordPress.org directory assets moved to separate folder for SVN upload
*   Improved: Full WordPress coding standards compliance for plugin review
*   Updated: Composer autoload configuration to reference new main file
*   Updated: All documentation files (README.md, composer.json) with new plugin name

= 1.0.9 =
*   Added: New "Help & Ideas" tab in settings with practical configuration examples
*   Added: 12 seasonal campaign examples (Christmas, New Year, Valentine's, Black Friday, Spring, Summer)
*   Added: Product launch and event registration examples (Product Launch, Webinar, Coupon Reveal)
*   Added: Informational announcements examples (Maintenance Notice, Important Announcements, Course Deadlines)
*   Added: 7 professional tips for creating effective countdown bars
*   Added: Beautiful gradient design for tips section with interactive hover effects
*   Added: Color suggestions with hex codes for each example
*   Improved: Enhanced user experience with visual, categorized examples
*   Improved: Better onboarding for new users with ready-to-use templates
*   Translation: Updated ALL 9 language files with 60+ new strings for Help & Ideas tab
*   Translation: Spanish (es_ES) - Complete translation of all new content
*   Translation: French (fr_FR) - Complete translation of all new content
*   Translation: German (de_DE) - Complete translation of all new content
*   Translation: Italian (it_IT) - Complete translation of all new content
*   Translation: Brazilian Portuguese (pt_BR) - Complete translation of all new content
*   Translation: Dutch (nl_NL) - Complete translation of all new content
*   Translation: Russian (ru_RU) - Complete translation with Cyrillic alphabet
*   Translation: Japanese (ja) - Complete translation with Japanese characters
*   Translation: Polish (pl_PL) - Complete translation with Polish plural forms
*   Translation: Total of 540+ new translation strings added across all languages

= 1.0.8 =
*   Added: Dutch (nl_NL) translation - Full plugin translation for Dutch-speaking users
*   Added: Russian (ru_RU) translation - Full plugin translation with Cyrillic alphabet support
*   Added: Japanese (ja) translation - Full plugin translation with Japanese characters
*   Added: Polish (pl_PL) translation - Full plugin translation for Polish-speaking users
*   Improved: Major internationalization milestone - Plugin now available in 10 languages!
*   Improved: Added support for complex plural forms (Russian, Polish, Japanese)

= 1.0.7 =
*   Added: Italian (it_IT) translation - Full plugin translation for Italian-speaking users
*   Added: Brazilian Portuguese (pt_BR) translation - Full plugin translation for Brazilian users
*   Improved: Major internationalization expansion covering Romance language family

= 1.0.6 =
*   Added: German (de_DE) translation - Full plugin translation for German-speaking users
*   Improved: Expanded internationalization support with complete .po file for German language

= 1.0.5 =
*   Added: French (fr_FR) translation - Full plugin translation for French-speaking users
*   Improved: Internationalization support with complete .po file for French language

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

= 1.1.0 =
CRITICAL WordPress.org compliance update. Changed prefix from 'tcn' to 'antitoco', removed custom CSS (use Customizer instead), improved security with proper escaping. All functionality preserved. Update required for WordPress.org approval.

= 1.0.10 =
IMPORTANT: Plugin renamed to "Antikton Topbar Countdown" for WordPress.org compliance. Slug changed to "antikton-topbar-countdown". All functionality preserved. Update recommended for continued WordPress.org support.

= 1.0.9 =
Major update! New "Help & Ideas" tab with 12+ practical examples for seasonal campaigns, product launches, and events. ALL 9 language files updated with 540+ new translation strings. Complete internationalization for Help & Ideas feature.

= 1.0.8 =
Major update! Added 4 new languages: Dutch, Russian, Japanese, and Polish. Plugin now available in 10 languages covering 80%+ of WordPress users worldwide!

= 1.0.7 =
Added Italian (it_IT) and Brazilian Portuguese (pt_BR) translations. Plugin now available in 6 languages!

= 1.0.6 =
Added complete German translation (de_DE) for German-speaking users. Plugin now fully available in German.

= 1.0.5 =
Added complete French translation (fr_FR) for French-speaking users. Plugin now fully available in French.

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
