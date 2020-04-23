=== Subscribe Sidebar plugin by Blubrry ===
Contributors: Angelo Mandato, Blubrry.com
Tags: subscribe, subscribe sidebar, widget, widgets, sidebar, feed, rss, twitter, atom, itunes, zune, google, yahoo, facebook, fan, page, podcast, podcasting, powerpress, podpress, buzz, profile, fan page
Requires at least: 2.5
Tested up to: 2.9.2
Stable tag: 1.3.1

Add a list of "Subscribe" links to your sidebar. Options include your blog and podcast feed, Twitter page, iTunes, Facebook Fan Page, Google Profiles and more.

== Description ==
This plugin displays subscription icons with links in the sidebar.

The Subscribe Sidebar plugin is easy to install and configure. It's configurable from the WordPress administration interface and can be added to the sidebar either by utilizing the dynamic widgets sidebar or by adding a few lines of code to your theme's sidebar template. The plugin detects the presence of a podcasting plugin (such as Blubrry PowerPress) and can display the podcast feed as a subscribe link in your sidebar. 

Subscribe Sidebar options:

* RSS 2 Feed
* Atom Feed
* Podcast Feed
* Add to Google Reader/Homepage
* Add to My Yahoo
* Add to iTunes (for podcasts only)
* Add to Zune (for podcasts only)
* Follow on Twitter
* Facebook Fan Page
* Google Profile/Buzz (New!)
* Icons with Drop Shadows (New!)

For the latest information visit the website.

http://www.blubrry.com/subscribe_sidebar/

= Want to Podcast from WordPress? =
Check out the [Blubrry PowerPress](http://wordpress.org/extend/plugins/powerpress/) podcasting plugin for WordPress. PowerPress brings the essential features for podcasting to WordPress. Developed by podcasters for podcasters, PowerPress offers full iTunes support, the Update iTunes Listing feature, web audio/video media players and more.

== Installation ==
1. Copy the entire directory from the downloaded zip file into the /wp-content/plugins/ folder.
2. Activate the "Subscribe Sidebar" plugin in the Plugin Management page.
3. Configure your Subscribe Sidebar by going to the "Options" > "Subscribe Sidebar" page.
3. Add to your sidebar:

		**Widgets Sidebar**
		If you are using the dynamic widgets sidebar in your theme, go to the "Presentation" > "Widgets" page and drag the “Subscribe” widget into the sidebar where you would like it to appear.
	
		**Traditional Sidebar**
		If you are not using the dynamic widgets sidebar, insert to following code into your theme's sidebar template.
		
		<?php if( function_exists('subscribe_sidebar') ) { ?>
		<li><?php subscribe_sidebar(); ?>
		</li>
		<?php } ?>
		
== Screenshots ==
1. Subscribe Sidebar example
2. Subscribe Sidebar in WordPress default theme
3. Administration Interface for configuring the Subscribe Sidebar
4. Administration Interface for Sidebar Arrangement

== Changelog ==

= 1.3.1 =
* Released on 02/18/2010
* Fixed bug that caused extra empty <li></li> at bottom of subscribe sidebar list.

= 1.3.0 =
* Released on 02/18/2010
* Added Google Profile/Buzz option (Thanks Kimberly for submitting code!).
* Added new Drop Shadow icons option

= 1.2.0 =
* Released on 12/16/2009
* Added facebook fan page option (Thanks Kimberly for submitting code!).
* Fixed URL to images error (Thanks ladycrow and digigirl for reporting!).
* Updated links to blubrry.com.
* If iTunes URL value blank and Blubrry PowerPress enabled, the iTunes one-click subscription URL is now detected from PowerPress settings.
* Example one-click iTunes URL added to settings page.
* Added modern iTunes icon as option.
* Added option to modify the heading title from the Widgets management screen.
* Updated readme changelog section for latest WordPress.org plugins directory standards.


= 1.1.2 =
* Released on 05/22/2009
* Fixed syntax error, unexpected T_DNUMBER on line 11.


= 1.1.1 =
* Released on 05/22/2009
* Fixed the tag version number so it now includes 3 parts.


= 1.1 =
* Released on 05/22/2009
* Removed level permissions check, this plugin no longer supports WP versions older than 2.5. Added option to customize the heading title text. Fixed widgets heading text error. updated the yahoo, google and iTunes icons.


= 1.0 =
* Released on 03/20/2008
* Initial release of Subscribe Sidebar plugin.

== Contributors ==
Angelo Mandato, CIO [RawVoice](http://www.rawvoice.com) - Plugin founder

Kimberly Jansen, [Blog](http://kimmy.blogit.co.nz) - Added Google Profile, Facebook Fan Page options and fixed styling bug in settings page.


== Feedback == 
 http://www.blubrry.com/subscribe_sidebar/

== Twitter == 
 http://twitter.com/blubrry
