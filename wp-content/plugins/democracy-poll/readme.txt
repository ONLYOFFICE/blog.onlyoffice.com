=== Democracy Poll ===
Stable tag: trunk
Tested up to: 7.0.2
Contributors: Tkama
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: democracy, polls, vote, survey, review


WordPress polls plugin with multiple-choice, custom answers, cache compatibility, widgets, and shortcodes.

[Documentation](https://doiftrue.github.io/democracy-poll/)


== Description ==

This plugin provides an intuitive and powerful system for creating polls, with features such as:

* Customizable single-choice and multiple-choice voting
* Optional custom answers submitted by visitors
* Poll end dates
* Optional restriction of voting to registered users
* Multiple poll designs
* And more — see the changelog for details

**Democracy Poll** is compatible with major cache plugins, including W3 Total Cache, WP Super Cache, Wordfence, Quick Cache, and others.

Designed for ease of use and performance, it offers:

* A "Quick Edit" button for admins, shown directly above a poll
* A plugin menu in the admin toolbar
* Inline poll CSS
* Conditional JavaScript loading only when a poll is rendered
* And more — check the changelog for details

### More Info ###

Democracy Poll is a modern version of the original, well-regarded plugin by the same name. Andrew Sutherland introduced the original plugin in 2006, including the innovative ability for visitors to add their own answers. This version retains the name and core idea but has been completely rewritten.

**Key features:**

* Create new polls
* Cache plugin compatibility (e.g. W3 Total Cache, WP Super Cache)
* Allow visitors to add their own answers
* Multiple-choice voting support
* Automatic poll closing after a specified end date
* Display random polls
* Restrict voting to registered users (optional)
* Quick poll editing for administrators
* Edit vote counts
* Allow users to change their votes
* Voter tracking via IP address, cookies, or WordPress user ID, with optional vote clearing
* Embed polls in posts via `[democracy]` shortcode (visual editor button available)
* Widget support (optional)
* Admin bar menu for easy access (optional)
* Load front-end assets only when a poll is rendered
* Add custom notes under polls
* Customize designs via CSS themes

Multisite support has been available since version 5.2.4.

### Documentation ###

See the [complete Democracy Poll documentation](https://doiftrue.github.io/democracy-poll/) for setup instructions, user guides, shortcode reference, and developer integration points.



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
<?php democracy_archives( [ 'title_markup' => '<h2>{question}</h2>' ] ); ?>
`


== Frequently Asked Questions ==

### Does this plugin clean itself up after uninstalling? ###

Yes. Deleting the plugin removes all of its options and data.


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

See `COMPAT: ***` messages in the changelog below for any backward compatibility changes that may require code updates if you use the plugin's public API or properties directly in your code.


== Changelog ==

See `COMPAT: ***` messages in the changelog below for any backward compatibility changes that may require code updates if you use the plugin's public API or properties directly in your code.

= 6.5.0 =
* NEW: Added an option allowing different guest browsers to vote from the same IP address. Repeat votes are identified by a lightweight browser fingerprint when enabled.
* IMP: Vote logs are now always recorded and are the server-side source for repeat-vote checks and revoting.
* COMPAT: Removed the `keep_logs` option. Sites that had it disabled will start recording vote logs; their new same-IP voting option is enabled during migration to preserve the previous repeat-vote behavior.
* IMP: Combined the poll title wrapper settings into one HTML template using the `{question}` placeholder. Existing settings remain compatible.
* IMP: Poll rendering functions now accept a single title markup argument containing `{question}`. Legacy before/after arguments remain supported.
* COMPAT: Internal `Plugin::$msg`, `$initor`, `$admin`, and `$poll_ajax` properties were removed. Use `container()->get()` to access these services. The commonly used `Plugin::$opt` property remains available for backward compatibility.

= 6.4.0 =
* NEW: Added a global setting to sort order for answers on the results screen.
* FIX: Shortcode `[democracy id=last]` for last poll did not work correctly in some cases. See [request](https://wordpress.org/support/topic/shortcode-to-last-poll-not-working/)
* FIX: Users with plugin access roles could not create new polls. See [request](https://wordpress.org/support/topic/dempollget_db_data-poll-fails-when-editor-tries-to-add-new-poll-6-1-1/)
* FIX: There was no way to add custom answer if "Hide vote button" option is on, and it is non-multiple poll with the revote option enabled.
* FIX: Small bug when allowing users to add their own answers.
* CHG: Max poll height options disabled by default.
* IMP: Refresh poll edit form UI.
* IMP: Store votes for all polls in one cookie instead of creating a separate cookie per poll.
* COMPAT: `DemPoll::$dbdata` property removed. Use `DemPoll` props instead.
* COMPAT: `DemPoll` object renamed to `\DemocracyPoll\Poll`. Old DemPoll class name remains available via PHP class alias, but some public properties were removed/changed. So you may need to update your code if you use `DemPoll` or it's properties directly.
* COMPAT: `Poll_Service` class removed as well as `DemPoll::$service` property. Use methods of new `Poll_Cookies`, `Poll_Voting_Service`, `Poll_Logs` instead.
* COMPAT: Custom HTML returned by the `dem_vote_screen` or `dem_result_screen` filters must include the new `dem_*_js` classes used as JavaScript hooks.

= 6.3.1 =
* FIX: Restored the Text Customization settings page after a previous refactor.
* CHG: Switched the IP information provider to ipwho.is.
* IMP: IP information in the logs table now loads through Admin AJAX. Requests are queued to respect the provider rate limit, and the page no longer waits for missing IP data.
* IMP: Added a loading indicator and forced IP information refresh button.
* IMP: Updated translations and translated all code comments into English.

= 6.3.0 =
* Vote cancellation now requires server-side logs; revoting is disabled when logging is disabled.
* Fixed `CVE-2024-33920`, reported by Patchstack/WPScan (Missing Authorization / Broken Access Control):
	* Public `delVoted` could subtract votes using a user-controlled cookie.
	* SQL did not restrict answers to the current `qid`.
* Fixed admin IDOR via tampering with `POST['dmc_qid']`.
* Added translations for `fr_FR`, `hi_IN`, and `zh_CN`.

= 6.2.0 =
* CHG: Completely removed the jQuery dependency from the front end.
* IMP: Improved the vote and revote height animations.
* IMP: Modernized styles and added CSS variables.
* IMP: The "Max height" and "Line height" options now support CSS units instead of only pixels.
* CHG: Removed the `show_copyright` and `inline_js_css` options and their related logic.
* CHG: Removed the `disable_js` option and support for using the plugin without browser JavaScript. JavaScript is now required for better UX and performance.
* IMP: Refactored and improved the code, translated comments, and replaced `var` with `const` and `let` where appropriate.
* IMP: Added `package.json` and an ES module-based `gulpfile.js`, extracted several functions to `Utils.mjs`, and moved JavaScript and CSS files to `assets`.
* IMP: Added a Gulp build system and converted JavaScript files to ES6 modules.

= 6.1.2 =
* UPD: Tested up to WP 7.0.

= 6.1.1 =
* FIX: esc_attr() added for inline js to fix possible bugs on some servers.

= 6.1.0 =
* COMPAT: Removed the `DEM_VER` constant; use `DemocracyPoll\plugin()->ver` instead.
* COMPAT: Removed the `DEMOC_URL` constant; use `DemocracyPoll\plugin()->url` instead. Note that the trailing slash was removed.
* COMPAT: Removed the `DEMOC_PATH` constant; use `DemocracyPoll\plugin()->dir` instead. Note that the trailing slash was removed.
* COMPAT: Removed the `DEMOC_MAIN_FILE` constant.
* COMPAT: Significantly refactored `DemPoll` by decomposing it into smaller classes, including the new `Poll_Renderer` and `Poll_Service` classes.
* FIX: PHPStan fixes and improvements.
* IMP: Updated the POT and PO translation files and added `.l10n.php` files for better performance.

= 6.0.5 =
* IMP: Unit tests infrastructure added. Some Helpers methods are now tested.
* IMP: Added PHP type hints in several parts of the code.
* NEW: Poll_Answer class added to encapsulate poll answer data and improve code readability.
* DOC: All filters and actions documented.
* IMP: Other minor improvements.

= 6.0.4 =
* FIX: Init moved to `after_setup_theme` hook.
* NEW: Added alphabetical answer ordering.
* IMP: Improved `democracy.js` and refactored part of it to vanilla JavaScript.
* IMP: Minor CSS refactoring.
* IMP: Minor improvements.
* UPD: Tested up to WordPress 6.8.
* UPD: Updated js-cookie from 2.2.0 to 3.0.5.
* COMPAT: Requires PHP 7.4+

= 6.0.3 =
* FIX: Poll widget did not work correctly if "select random poll" option was set.

= 6.0.2 =
* FIX: Fatal error with "WordFence" plugin: "Failed opening .../Helpers/wfConfig.php".

= 6.0.1 =
* FIX: Short-circuit recursion on plugin object construct for not logged-in users (v6.0.0 bug).
* IMP: Minor improvements.

= 6.0.0 =
* COMPAT: Minimal PHP version requirement set to 7.0.
* COMPAT: Class `Democracy_Poll` renamed to `Plugin` and moved under namespace.
* COMPAT: Functions `democr()` and `demopt()` renamed to `\DemocracyPoll\plugin()` and `\DemocracyPoll\options()`.
* COMPAT: Most classes moved under `DemocracyPoll` namespace.
* FIX: Unable to delete all answers or create a democracy poll without a starting answer.
* FIX: `democracy_shortcode` bug.
* FIX: Not logged-in user logs now get saved with user_id=0 and IP (not just IP).
* FIX: `Regenerate_democracy_css` fixes. Empty answer PHP notice fix.
* IMP: `DemPoll` object improvements: magic properties replaced with real ones.
* IMP: "Admin" classes refactored.
* IMP: Admin Pages code refactored.
* IMP: Classes autoloader implemented.
* IMP: Huge refactoring, minor code improvements, and decomposition.
* UPD: Updated `democracy-poll.pot`.

Older changes are available in [changelog.txt](https://plugins.svn.wordpress.org/democracy-poll/trunk/changelog.txt).
