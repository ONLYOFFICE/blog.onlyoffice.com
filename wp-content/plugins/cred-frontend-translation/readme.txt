=== CRED Frontend Translation ===
Contributors: AmirHelzer, mihaimihai
Donate link: http://wp-types.com
Tags: WPML, translation, frontend, editing, Toolset
License: GPLv2
Requires at least: 3.5.1
Tested up to: 3.6.0
Stable tag: 1.1

An addon plugin for WPML, allowing front-end content translation and proofreading

== Description ==
CRED Frontend Translation allows translating content from front-end pages. It gets the translation roles from WPML and allows to build custom translation pages on the front-end.

Front-end translation has several advantages over translation from the WordPress admin:

* You can create your custom translation interface, using CRED forms.
* Translators can work more efficiently, as they see the exact context of the content they are translating.
* Translators need very little WordPress experience. All their interaction with your content comes from the front-end pages, using the interface that you design.

To use CRED Frontend Translation, you need to have [WPML](http://wpml.org/) and [CRED](http://wp-types.com/home/cred/). 

== Installation ==

1. Upload 'types' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= I can't see the translation controls on the front-end pages, what gives? =

For translation controls to appear, several things must happen:

* You need to create the translation forms for each content type that you want to translate.
* The front-end translation controls must be included in the site. The easiest way is by adding them as a widget.
* The logged in user must have translator capabilities in your site.

= Does this plugin run by itself? =

CRED Frontend Translation requires having both CRED and WPML activated. In WPML, you also need to have the 'Translation Management' module activated.

== Screenshots ==

1. A simple front-end translation form created with the plugin

== Changelog ==

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.0 =
* Initial release