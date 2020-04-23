=== Wordpress Logging Service ===
Contributors: zaantar
Tags: log, logging
Donate link: http://zaantar.eu/financni-prispevek
Author URI: http://zaantar.eu
Plugin URI: http://wordpress.org/extend/plugins/wordpress-logging-service
Requires at least: 3.5
Tested up to: 3.6.1
Stable tag: 1.5.4
License: GPL2

Provides a simple API for storing miscellaneous log entries and their overview in admin area.

== Description ==

This plugin provides a simple API for storing miscellaneous log entries and their overview in admin area (or network 
admin area, if activated on a multisite). 

Developed for private use (heavily used by my other plugins), but has perspective for more extensive usage. I can't guarantee any support in the future nor further development, but it is to be expected. Kindly inform me about bugs, if you find any, or propose new features: [zaantar@zaantar.eu](mailto:zaantar@zaantar.eu?subject=[wordpress-logging-service]).

See Usage and FAQ for more information.

== Frequently Asked Questions ==

= How does this actually work? =

There are different log categories, under which are individual log entries grouped. It is recommended that every plugin using WLS has it's own log category. (Network) admin can then view the entries at Network administration --> Dashboard --> System logs or change settings at Network administration --> Settings--> Wordpress Logging Service.

= How to use the plugin with my own plugins? =

See the API section.

= Which plugins already use Wordpress Logging Service? =

* Almost all of my plugins. Most of them is not internationalized nor uploaded to wordpress.org yet, but if you speak 
Czech language, you could make some use of them: [here](http://zaantar.eu/programovani/pluginy-pro-wordpress/)
* If you find other developer's plugin or if you develop yourself one that uses WLS, please tell me (zaantar@gmail.com) so I can add it to this list.

== Changelog ==

= 1.5.4 =
* Fix: Typo generating a PHP notice.
* Fix: Code depending on non-existing setting (removed for time being). 
* Allow activation on a single blog even on multisite (however note that log entries are stored in a single table and can be shared by all wls-enabled users throughout the blog network).

= 1.5.3 =
* Reverse default ordering of the log entries, oldest show first now.
* Fix cumulating _wp_http_referer issue.

= 1.5.2 =
* Fix incorrectly displayed record's blog name in multisite.
* Add missing include for WP_List_Table class.

= 1.5.1 =
* Implement a possibility to bulk delete selected records or mark them as read.
* Fix missing information about blog in log entry table.
* Minor visual improvements and bugfixes.
* Code is still in quite bad shape. Needs more work, more time.

= 1.5 =
* Use WP_List_Table in a WP-standard way. 
* Minor other visual improvement.
* Partial code polishing.

= 1.4.16 =
* fix incorrect $wpdb->prepare usage to assure compatibility with WordPress 3.5

= 1.4.15 =
* wls_simple_log now uses current_time function (using local wordpress time instead of php server time)

= 1.4.14 =
* readme syntax fix
* options for different severity filters for notification in (network) admin menu and on overview page
* minor bug fixes and improvements

= 1.4.13 =
* fix: respect log_entries_per_page setting on single log category overview
* added severity filter on single log category overview
* minor visual improvements

= 1.4.12 =
* added donate button on settings page
* (undocumented) ability to show certain entry id
* updated FAQ and Usage information
* option: log entries per page count

= 1.4.11 =
* added POT file and Czech translation

= 1.4.10 =
* minor bugfixes

= 1.4.9 =
* show unseen entry count on severity filter buttons

= 1.4.8 =
* fixed sorting of unseen entries
* feature: filter unseen entries by severity
* feature: wls options page

= 1.4.7 =
* code maintenance (split into two files)
* minor visual changes on admin overview page

= 1.4.6 =
* I18zed and translated to English and Czech
* fixed bug in single-site mode
* minor appearance changes
* published to wordpress.org

= 1.4 =
* new feature: storing information about unread log entries
* listing unread entries from all log categories under log category overview
* automatic database tables upgrade from older versions, it *should* work from version 1.2 above.

= 1.2 =
* first really useable version

= 1 =
* First attempt for a functional plugin. FAIL

== API ==

Definitions:

`WLS`

* should be checked before using any wls function

`WLS_VERSION`
	
* should(!) contain current WLS version string
	
* severity levels:

	`WLS_NOCATEGORY = 0`
	
	`WLS_INFO = 1`
	
	`WLS_NOTICE = 2`
	
	`WLS_WARNING = 3`
	
	`WLS_ERROR = 4`
	
	`WLS_FATALERROR = 5`

Functions:

`wls_is_registered( $category_name );`
	
* returns `true`, if `$category_name` is registered
	
`wls_register( $category_name, $description );`
	
* registers `$category_name` as a log category.
* `$description` will be shown in admin area
	
`wls_clear( $category_name );`
	
* deletes all log entries of category $category_name
	
`wls_unregister( $category_name );`

* same as wls_clear & removes category $category_name from the list

`wls_log( $category_name, $text, $user_id, $date, $blog_id, $severity = 0 );`

* inserts a new log entry into specified category
* `$date` must be formated according to ISO 8601
* `$severity` should be one of defined severity levels (see above)

`wls_simple_log( $log_name, $text, $severity = 0 );`
	
* equivalent to `wls_log( $category_name, $text, get_current_user_id(), date( 'c' ), get_current_blog_id(), $severity);`
