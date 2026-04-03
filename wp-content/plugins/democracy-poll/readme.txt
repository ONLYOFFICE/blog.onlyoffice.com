=== Democracy Poll ===
Stable tag: trunk
Tested up to: 6.8.2
Contributors: Tkama
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: democracy, polls, vote, survey, review


WordPress polls plugin with multiple-choice, custom answers, cache compatibility, widgets, and shortcodes.


== Description ==

This plugin provides an intuitive and powerful system to create polls with features like:

* Single and multiple voting options (customizable)
* Allowing visitors to add custom answers (optional)
* Setting an end date for polls
* Restricting voting to registered users (optional)
* Multiple poll designs
* And more — see the changelog for details

**Democracy Poll** is compatible with major cache plugins, including WP Total Cache, WP Super Cache, Wordfence, Quick Cache, and others.

Designed for ease of use and performance, it offers:

* A "Quick Edit" button for admins, shown directly above a poll
* A plugin menu in the admin toolbar
* Inline inclusion of CSS & JS
* Conditional loading of CSS & JS (only when needed)
* And more — check the changelog for details

### More Info ###

Democracy Poll is a modern version of the original, well-regarded plugin by the same name. Although Andrew Sutherland’s version hadn't been updated since 2006, it introduced the innovative idea of allowing users to add their own answers. This version retains the core idea and name, but features fully rewritten code.

**Key features:**

* Create new polls
* Cache plugin compatibility (e.g. WP Total Cache, WP Super Cache)
* Option to allow users to add their own answers
* Multi-voting support
* Poll auto-closing after a specified end date
* Display random polls
* Restrict voting to registered users (optional)
* "Edit" button for admins for quick poll management
* Edit vote counts
* Option to let users change their votes
* Voter tracking via IP, cookies, or WP user ID (optional vote clearing)
* Embed polls in posts via `[democracy]` shortcode (visual editor button available)
* Widget support (optional)
* Admin bar menu for easy access (optional)
* Option to disable or inline CSS/JS
* Add custom notes under polls
* Customize designs via CSS themes

Multisite support is available from version 5.2.4.



== Usage ==

### Widget ###

1. Go to `WP Admin → Appearance → Widgets` and add the `Democracy Poll` widget
2. Place it in a sidebar
3. Configure settings
4. Done

### Template Code ###

In your theme file (e.g. `sidebar.php`), add:

`
<?php if ( function_exists( 'democracy_poll' ) ) { ?>
	<div class="sidebar-section">
		<h2>Polls</h2>
		<div class="my-poll">
			<?php democracy_poll(); ?>
		</div>
	</div>
<?php } ?>
`

* To show a specific poll: `<?php democracy_poll( 3 ); ?>` (replace `3` with your poll ID)
* To embed a specific poll in a post, use `[democracy id="2"]` shortcode.
* To embed a random poll in a post, use `[democracy]` shortcode.


#### Poll Archive ####

To show the poll archive:

`
<?php democracy_archives( $hide_active, $before_title, $after_title ); ?>
`


== Frequently Asked Questions ==

### Does this plugin clean itself up after uninstalling? ###

Yes. When you deactivate and delete the plugin, it removes all its options and data.


== Screenshots ==

1. Single vote view
2. Single result view
3. Multiple vote view
4. Admin polls list
5. Admin edit poll
6. Add poll admin page
7. General settings
8. Theme settings
9. Text customization



== Upgrade Notice ==

= 6.1.1 =
- FIX: esc_attr() added for inline js to fix possible bugs on some servers.

= 6.1.0 =
- CHG: DEM_VER constant removed use `DemocracyPoll\plugin()->ver` instead.
- CHG: DEMOC_URL constant removed use `DemocracyPoll\plugin()->url` instead. NOTE: Trailing slash removed.
- CHG: DEMOC_PATH constant removed use `DemocracyPoll\plugin()->dir` instead. NOTE: Trailing slash removed.
- CHG: DEMOC_MAIN_FILE constant removed.
- CHG: `DemPoll` class refactored significantly. Some of the proprerties moved to `Poll_Renderer` and `Poll_Service` classes.

= 6.0.4 =
* Requires PHP 7.4+

= 6.0.0 =
* Requires PHP 7.0+
* If you used plugin classes directly in your code, you may need to update them to match the new class names



== Changelog ==

= 6.1.0 =
- CHG: DEM_VER constant removed use `DemocracyPoll\plugin()->ver` instead.
- CHG: DEMOC_URL constant removed use `DemocracyPoll\plugin()->url` instead. NOTE: Trailing slash removed.
- CHG: DEMOC_PATH constant removed use `DemocracyPoll\plugin()->dir` instead. NOTE: Trailing slash removed.
- CHG: DEMOC_MAIN_FILE constant removed.
- IMP: DemPoll class refactored significantly. IT was decomposed into smaller classes - two new clasees added Poll_Renderer and Poll_Service.
- FIX: PHPStan fixes and improvements.
- IMP: Translation POT file updated. PO files updated. `.l10n.php` files added for better performance.

= 6.0.5 =
- IMP: Unit tests infrastructure added. Some Helpers methods are now tested.
- IMP: PHP Typehint added for some palces of the code.
- NEW: Poll_Answer class added to encapsulate poll answer data and improve code readability.
- DOC: All filters and actions documented.
- IMP: Other minor improvements.

= 6.0.4 =
- FIX: Init moved to `after_setup_theme` hook.
- NEW: Alphabet answers order added.
- IMP: democracy.js minor improvements (part refactored to vanilla js).
- IMP: CSS minor refactor.
- IMP: Minor improvements.
- UPD: Tested up to: WP 6.8.0
- UPD: js-cookie 2.2.0 >> 3.0.5.

= 6.0.3 =
* FIX: Poll widget did not work correctly if "select random poll" option was set.

= 6.0.2 =
* FIX: Fatal error with "WordFence" plugin: "Failed opening .../Helpers/wfConfig.php".

= 6.0.1 =
* FIX: Short-circuit recursion on plugin object construct for not logged-in users (v6.0.0 bug).
* IMP: Minor improvements.

= 6.0.0 =
* FIX: Unable to delete all answers or create a democracy poll without a starting answer.
* CHG: Minimal PHP version requirement set to 7.0.
* CHG: Class `Democracy_Poll` renamed to `Plugin` and moved under namespace.
* CHG: Functions `democr()` and `demopt()` renamed to `\DemocracyPoll\plugin()` and `\DemocracyPoll\options()`.
* CHG: Most classes moved under `DemocracyPoll` namespace.
* CHG: DemPoll object improvements: magic properties replaced with real ones.
* FIX: `democracy_shortcode` bug.
* FIX: Not logged-in user logs now get saved with user_id=0 and IP (not just IP).
* FIX: `Regenerate_democracy_css` fixes. Empty answer PHP notice fix.
* IMP: "Admin" classes refactored.
* IMP: Admin Pages code refactored.
* IMP: Classes autoloader implemented.
* IMP: Huge refactoring, minor code improvements, and decomposition.
* UPD: Updated `democracy-poll.pot`.

= 5.6.0 =
* FIX: Pagination links on archive page.

= 5.5.10 =
* FIX: CSS radio/checkbox styles changed from px to em.

= 5.5.9 =
* FIX: JS code fixes for jQuery 3.5 compatibility.

= 5.5.8 =
* ADD: `orderby` argument for `get_dem_polls()` function.

= 5.5.7 =
* ADD: Hook `get_dem_polls_sql_clauses`.

= 5.5.6.3 =
* FIX: `disabled` property not removed correctly on uncheck for multi-answer questions.

= 5.5.6.2 =
* ADD: Scroll to poll top when clicking Results, Vote, etc.

= 5.5.6.1 =
* ADD: `target="_blank"` attribute for copyright link.

= 5.5.6 =
* ADD: Pagination links at the bottom of the archive page.
* ADD: `[democracy_archives]` shortcode now accepts parameters like 'before_title', 'after_title', 'active', 'open', 'screen', 'per_page', 'add_from_posts'.
* ADD: `get_dem_polls( $args )` function.

= 5.5.5 =
* CHG: Replaced ACE code editor with native WordPress CodeMirror.

= 5.5.4 =
* ADD: `dem_get_ip` filter and Cloudflare IP support.
* ADD: Support for float numbers in the 'cookie_days' option.
* FIX: Expire time now set in UTC timezone.

= 5.5.3 =
* FIX: Compatibility with W3TC.
* FIX: Multiple voting limit check on backend (AJAX) — no more answers than allowed.
* IMP: Return WP_Error object on vote error and display it.

= 5.5.2 =
* ADD: `get_democracy_poll_results( $poll_id )` wrapper function to get poll results.
* ADD: Allow `<img>` tag in questions and answers.

= 5.5.1 =
* IMP: Admin design settings page improved.

= 5.5.0 =
* ADD: Post metabox to attach poll to post; use `get_post_poll_id()` on `is_singular()` pages.
* ADD: Progress line animation effect for vote results with adjustable speed.
* IMP: "Height collapsing" now doesn't work if intended to hide less than 100px.
* FIX: JS now included in footer properly when poll added via shortcode.
* IMP: Improved buttons and design on admin design settings page.

= 5.4.9 =
* ADD: 'demadmin_sanitize_poll_data' filter with second `$original_data` parameter.
* ADD: Block showing posts where poll is embedded at bottom of polls archive page.

= 5.4.8 =
* FIX: 'expire' parameter issue when logs written to DB.
* FIX: Replaced `wp_remote_get()` with `file_get_contents()` for geoplugin.net API.
* FIX: `jquery-ui.css` and images fix.

= 5.4.6 =
* FIX: "load_textdomain" error that blocked plugin activation.

= 5.4.5 =
* FIX: "Edit poll" link from frontend for users with poll edit rights.
* FIX: Incorrect use of `$this` for PHP 5.3 in `Democracy_Poll_Admin` class.

= 5.4.4 =
* CHG: Preparing to move all localization to translate.wordpress.org.
* FIX: MU activation notice: replaced `wp_get_sites()` with `get_sites()` (WP 4.6+).
* ADD: Hungarian translation (hu_HU) by Lesbat.

= 5.4.3 =
* ADD: Disable editing another user's poll if restricted by admin settings.
* ADD: Spanish (es_ES) localization.
* IMP: Improved accessibility protection in admin for additional roles.
* IMP: Block global plugin options updates for non-super_access roles.

= 5.4.2 =
* FIX: Minor fixes: function renaming and blocking direct file access.
* CHG: Added `jquery-ui.css` to plugin files.
* FIX: W3TC support fixes.
* ADD: Second parameter to 'dem_sanitize_answer_data' and 'dem_set_answers' filters.
* FIX: TinyMCE translation fix.
* CHG: Renamed main class `Dem` to `Democracy_Poll`.

= 5.4.1 =
* CHG: Improve activation logic with `activate_plugin()` outside wp-admin. Thanks to J.D. Grimes.

= 5.4 =
* FIX: XSS vulnerability fix (security issue).
* ADD: Nonce checks for all admin requests.
* CHG: Moved back `Democracy_Poll_Admin::update_options()` method.

= 5.3.6 =
* FIX: Removed unsafe `esc_sql()` usage. Thanks to J.D. Grimes.
* FIX: Multiple runs of `Democracy_Poll_Admin` trigger error fix.
* CHG: Moved `update_options()` to `Democracy_Poll`.

= 5.3.5 =
* FIX: User IP now detected only with `REMOTE_ADDR` (to avoid cheating).

= 5.3.4.6 =
* FIX: Added 'dem_add_user_answer' query var param to set `noindex`.
* ADD: Actions `dem_voted` and `dem_vote_deleted`.

= 5.3.4.5 =
* ADD: Filters `dem_vote_screen` and `dem_result_screen`.

= 5.3.4 =
* ADD: Poll creation date editing on poll edit page.
* ADD: Animation speed setting in design settings.
* ADD: "Don't show results link" global option.
* ADD: Show last poll option in widget.
* FIX: Bug where user couldn't add own answer if vote button hidden.
* CHG: Moved "dem__collapser" styles globally; customizable arrows via CSS.

= 5.3.3.2 =
* FIX: Stability for injecting "dem__collapser" style.

= 5.3.3.1 =
* ADD: Answer sorting in admin by votes and ID.

= 5.3.3 =
* FIX: Vote and revote buttons now fully removed from DOM with caching plugins.

= 5.3.2 =
* FIX: Cookie stability fix with page caching plugins.

= 5.3.1 =
* ADD: Filter `dem_poll_screen_choose`.
* FIX: Prevent JS errors by checking democracy element presence before init.
* CHG: JS init moved to `document.ready` instead of `load`.

= 5.3.0 =
* CHG: All plugin code translated to English (no hardcoded Russian text).

= 5.2.9 =
* FIX: PHP syntax bug in poll addition.

= 5.2.8 =
* ADD: New red Pinterest-style button. Some old 3D/glass buttons removed.
* ADD: Filters: `dem_vote_screen_answer`, `dem_result_screen_answer`, `demadmin_after_question`, `demadmin_after_answer`, `dem_sanitize_answer_data`, `demadmin_sanitize_poll_data`.

= 5.2.7 =
* FIX: "Don't show results" global option fix.
* FIX: Minor code fixes.

= 5.2.6 =
* FIX: "NEW" mark correctly added after adding a new answer.

= 5.2.5 =
* FIX: Replaced `wp_json_encode()` for WP < 4.1 support.
* CHG: Usability improvements.
* CHG: Set max+1 order number for user-added answers if answers have order.

= 5.2.4 =
* ADD: Multisite support.
* ADD: Migration mechanism from "WP Polls" plugin.
* FIX: Bug where one answer allowed for multiple-answer polls.
* CHG: Save IP to DB as-is (no ip2long()).
* CHG: Updated English translation.

= 5.2.3 =
* ADD: Show posts list using poll shortcode on poll edit page.
* ADD: Allow setting custom CSS class for poll buttons.
* ADD: Filters: `dem_super_access`, `dem_get_poll`, `dem_set_answers`.
* FIX: "Reset order" button bug fix on poll edit screen.
* FIX: "Additional CSS" emptying bug fix.
* FIX: Other minor fixes.
* CHG: Updated English translation.

= 5.2.2 =
* FIX: Actions (close, open, activate, deactivate) in polls list table were not applied immediately.
* FIX: Radio and checkbox styles.

= 5.2.1 =
* ADD: 'In posts' column in admin polls list to show where the poll shortcode is used.

= 5.2.0 =
* ADD: Hooks: `dem_poll_inserted`, `dem_before_insert_quest_data`.
* ADD: Two options to delete logs: only logs or logs with votes.
* ADD: Ability to delete a single answer log.
* ADD: "All voters" section at bottom of multiple polls.
* ADD: Delete answer logs when deleting an answer.
* ADD: Button to delete logs of closed polls.
* ADD: Hide "logs" link in polls list table if no log records exist.
* ADD: Collapse extremely tall polls with "max height" option; expand on answer click.
* ADD: CSS themes for radio and checkbox inputs; special classes and spans added.
* ADD: Ability to assign poll and log access to other WordPress roles.
* ADD: "NEW" mark for newly added answers (except by poll creator).
* ADD: "NEW" mark filter and clear button on logs table.
* ADD: Display country name and flag in logs table based on voter IP.
* ADD: Ability to sort answers manually in edit/add poll page.
* ADD: Option to randomize answer order.
* ADD: Single poll sort option to override global setting.
* FIX: Admin CSS bug on design screen in Firefox.
* CHG: Updated English translation.

= 5.1.1 =
* FIX: SEO - 404 response and "noindex" head tag for duplicate pages (`dem_act`, `dem_pid`, `show_addanswerfield` GET parameters).

= 5.1.0 =
* FIX: Changed DB IP field from `int(11)` to `bigint(20)` to fix wrong IP storage. Adjusted some other DB fields.

= 5.0.3 =
* FIX: Bugs with variables and antivirus checks.

= 5.0.2 =
* FIX: Incorrect answer setting in cache mode due to wrong screen detection.

= 5.0.1 =
* ADD: Expand answers list by clicking on the block in Polls list page.

= 5.0 =
* FIX: Replaced VOTE button with REVOTE button in cache mode after voting.
* ADD: Option to hide results until poll is closed (global and per poll).
* ADD: Edit & view links on admin logs page.
* ADD: Search field on admin polls list page.
* ADD: Show all answers (not only winners) in "Winner" column.
* ADD: Poll shortcode shown on edit poll page (auto-select on click).
* CHG: Sort answers by votes on edit poll page.

= 4.9.4 =
* FIX: Changed default DB charset from `utf8mb4` to `utf8`. Thanks to Nanotraktor.

= 4.9.3 =
* ADD: Single poll option to limit max answers in multiple-answer polls.
* ADD: Global option to hide vote button on non-multiple polls (click-to-vote).
* FIX: Disabled cache on archive page.

= 4.9.2 =
* FIX: Bootstrap `.label` class conflict; renamed to `.dem-label`.
* ADD: Auto-regenerate CSS on plugin admin page load.

= 4.9.1 =
* FIX: Polls admin table column order.

= 4.9.0 =
* ADD: Logs table in admin with ability to remove logs of a specific poll.
* ADD: 'date' field to `democracy_log` table.

= 4.8 =
* CHG: Completely revamped polls list table using WP_List_Table: sortable columns, pagination, and search ready.

= 4.7.8 =
* ADD: Default en_US localization if none available.

= 4.7.7 =
* ADD: de_DE localization. Thanks to Matthias Siebler.

= 4.7.6 =
* DEL: Removed no-JS support. Now poll requires JavaScript for better usability.

= 4.7.5 =
* CHG: Changed DB charset to `utf8mb4` to support emojis.

= 4.7.4 =
* CHG: Updated admin CSS styles.

= 4.7.3 =
* ADD: Custom frontend localization settings page to translate all poll phrases.

= 4.7.2 =
* CHG: JS result/vote view cache updated without animation for smoother UX.
* CHG: Democracy block height set on "load" instead of "document.ready".
* CHG: Minor improvements in `block.css` theme.

= 4.7.1 =
* ADD: Global options to disable "revote" and "democratic" features.
* ADD: Localization POT file and English translation.

= 4.7.0 =
* CHG: Moved "progress fill type" and "answers order" settings to Design options page.
* FIX: English localization fixes.

= 4.6.9 =
* CHG: Reworked answer field adding on new poll creation (add on focus).

= 4.6.8 =
* FIX: Bug introduced in 4.6.7 affecting options.

= 4.6.7 =
* ADD: Capability check for editing polls. Toolbar hidden for unauthorized users.

= 4.6.6 =
* FIX: Major voting status check bug fixed (critical release).
* CHG: Minor JS code changes.
* CHG: `notVote` cookie lifespan set to 1 hour.

= 4.6.5 =
* ADD: New theme `block.css`.
* ADD: Preset theme visibility and customization support.

= 4.6.4 =
* FIX: New democratic answers couldn't contain commas.

= 4.6.3 =
* FIX: Widget display issues due to code changes.
* IMP: Improved English localization.

= 4.6.2 =
* FIX: Major updates to poll themes and CSS structure.
* ADD: "Ace" CSS editor for easier theme customization.

= 4.6.1 =
* FIX: Minor changes to themes, translations, and CSS.
* ADD: Added screenshots to WP directory.

= 4.6.0 =
* ADD: Poll themes management.
* FIX: JS and CSS bug fixes.
* FIX: Auto-deactivate polls when closed.

= 4.5.9 =
* FIX: CSS fixes; prep for 4.6.0 update.
* ADD: Cache handling and "notVote" cookie optimization.

= 4.5.8 =
* ADD: AJAX loader images (SVG & CSS3 collection).
* ADD: Automatically set close date when poll closes.

= 4.5.7 =
* FIX: Revote button did not deduct votes if "keep-logs" option was disabled.

= 4.5.6 =
* ADD: Cache plugin compatibility (W3TC, WP Super Cache, WordFence, WP Rocket, Quick Cache).
* ADD: Settings page link to selected CSS file for easier customization.
* ADD: PHP 5.3+ requirement notice.
* CHG: Archive page ID stored instead of link.
* FIX: Multiple small bugs and optimizations.

= 4.5.5 =
* CHG: Archive link detection now based on ID, not URL.

= 4.5.4 =
* FIX: JS refactored: all scripts run via jQuery.
* FIX: Separated JS and CSS loading: CSS globally in head; JS only where needed.

= 4.5.3 =
* FIX: Code fixes for handling `$_POST` variables.

= 4.5.2 =
* FIX: Removed direct `wp-load.php` calls on AJAX requests; now uses WordPress environment.
* FIX: Safe SQL call improvements using `$wpdb` functions.
* FIX: Admin message fixes.

= 4.5.1 =
* FIX: Localization bug on activation.

= 4.5 =
* ADD: CSS style themes support.
* ADD: New "flat.css" theme.
* FIX: Multiple bug fixes.

= 4.4 =
* ADD: Full plugin functionality even with JavaScript disabled.
* FIX: Minor bug fixes.

= 4.3.1 =
* ADD: "Close" button for "add user answer text" field on multiple vote polls.
* FIX: Minor bug fix.

= 4.3 =
* ADD: TinyMCE button integration.
* FIX: Minor bug fix.

= 4.2 =
* ADD: Revote functionality.

= 4.1 =
* ADD: Restriction for "only registered users can vote".
* ADD: Minified versions of CSS and JS loaded automatically if available.
* ADD: Inline JS/CSS inclusion option for performance.
* ADD: Load scripts/styles only on pages with polls.
* ADD: Admin toolbar menu for faster poll management.

= 4.0 =
* ADD: Multiple voting option.
* ADD: Ability to change vote counts manually.
* ADD: Random poll selection from active polls.
* ADD: Poll expiration date feature.
* ADD: jQuery datepicker for poll expiration.
* ADD: Open/close polls functionality.
* ADD: Localization functionality (English translation).
* ADD: Switched to standard WP shortcodes `[democracy]`.
* ADD: Full jQuery support.
* ADD: Edit button for each poll (visible when logged in).
* ADD: Clear logs button.
* ADD: Smart "create archive page" button.
* FIX: Major code refactoring for future expansions.
* FIX: Improved CSS output for adaptive design.


