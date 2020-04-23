=== Easy Retweet ===
Contributors: sudar  
Tags: posts, Twitter, tweet, Retweet  
Requires at least: 4.4  
Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me  
Tested up to: 4.7  
Stable tag: 3.1.1  

Adds a Tweet button to your WordPress posts

== Description ==

Easy ReTweet is a WordPress Plugin, which let's you add Tweet this buttons for your WordPress posts.

### Usage

There are three ways you can add the retweet button. Automatic way, manual way and using shortcodes

#### Automatic way

Install the Plugin and choose the type and position of the button from the Plugin's settings page. You can also specifically enable/disable the button for each post or page from the write post/page screen.

#### Manual way

If you want more control over the way the button should be positioned, then you can manually call the button using the following code.

`if (function_exists('easy_retweet_button')) echo easy_retweet_button();`

#### Using shortcodes

You can also place the shortcode [easy-retweet] anywhere in your post. This shortcode will be replaced by the button when the post is rendered.

### Development

The development of the Plugin happens over at [github][6]. If you want to contribute to the Plugin, fork the [project at github][6] and send me a pull request.

If you are not familiar with either git or Github then refer to this [guide to see how fork and send pull request](http://sudarmuthu.com/blog/contributing-to-project-hosted-in-github).

If you are looking for ideas, then you can start with one of the following TODO items :)

### TODO

The following are the features that I am thinking of adding to the Plugin, when I get some free time. If you have any feature request or want to increase the priority of a particular feature, then let me know.

- Add Google Analytics tracking to shortcodes and template function
- Add tracking of tweet button clicks

### Support

- If you have found a bug/issue or have a feature request, then post them in [github issues][7]
- If you have a question about usage or need help to troubleshoot, then post in WordPress forums or leave a comment in [Plugins's home page][1]
- If you like the Plugin, then kindly leave a review/feedback at [WordPress repo page][8].
- If you find this Plugin useful or and wanted to say thank you, then there are ways to [make me happy](http://sudarmuthu.com/if-you-wanna-thank-me) :) and I would really appreciate if you can do one of those.
- Checkout other [WordPress Plugins][5] that I have written
- If anything else, then contact me in [twitter][3].

 [1]: http://sudarmuthu.com/wordpress/easy-retweet
 [3]: http://twitter.com/sudarmuthu
 [4]: http://sudarmuthu.com/blog
 [5]: http://sudarmuthu.com/wordpress
 [6]: https://github.com/sudar/easy-retweet
 [7]: https://github.com/sudar/easy-retweet/issues
 [8]: http://wordpress.org/extend/plugins/easy-retweet/

== Translation ==

The Plugin currently has translations for the following languages.

*   Belorussian (Thanks FatCow)
*   Spanish (Thanks Carlos Varela)
*   Brazilian Portuguese (Thanks Marcelo)
*   German (Thanks Jenny Beelens)
*   Bulgarian (Thanks Dimitar Kolevski)
*   Lithuanian (Thanks Nata)
*   French (Thanks Brian Flores)
*   Romanian (Thanks Alexander Ovsov)
*   Hindi (Thanks Love Chandel)
*   Irish (Thanks Vikas Arora)
*   Danish (Thanks Jorgen)

The pot file is available with the Plugin. If you are willing to do translation for the Plugin, use the pot file to create the .po files for your language and let me know. I will add it to the Plugin after giving credit to you.

== Installation ==

Extract the zip file and just drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

== Screenshots ==


1. General Settings


2. Twitter button Settings page


3. Enable/Disable button in the write post/page page

== Readme Generator ==

This Readme file was generated using <a href = 'http://sudarmuthu.com/wordpress/wp-readme'>wp-readme</a>, which generates readme files for WordPress Plugins.
== Changelog ==

= v0.1 (2009-07-13) =

*   Initial Release

= v0.2 (2009-07-20) =

*   Added option to add/remove button in archive pages.

= v0.3 (2009-07-21) =

*   Added support for translation.

= v0.4 (2009-07-22) =

*   Added option to add/remove button in home page.

= v0.5 (2009-07-24) =

*   Added option to edit the text that is displayed in the button.

= v0.6 (2009-07-26) =

*   Prevented the JavaScript file from getting included in admin pages.

= v0.7 (2009-07-27) =

*   Added an option to add text that can be added as Prefix to the Twitter message used for retweet.

= v0.8 (2009-07-28) =

*   Added support for shortcode to retweet button.

= v0.9 (2009-07-31) =

*   Fixed an issue with generated JavaScript. Thanks Dougal (http://dougal.gunters.org/).

= v1.0 (2009-08-02) =

*   Added option to enter your own Bit.ly username and api key.
*   Added option to sepcify your own attributes like rel or target to the retweet link.

= v1.1.0 (2009-08-05) =

*   The shorturls generated using your API key, will be linked with your account.
*   Printing js using PHP, for better performance of JavaScript.

= v1.2.0 (2009-08-18) =

*   Removed hard coded Plugin path to make it work even if the wp-content path is changed.

= v1.3.0 (2009-08-19) =
*   Added the ability to enable/disable button on per page/post basics.

= v1.4.0 (2009-10-15) =
*   Added the ability to enable/disable button on per page/post basics, event if template function is used.

= v1.5 (2010-01-02) =
*   Ability to specify custom message for twitter instead of the post title.
*   Also added Belorussian Translations (Thanks FatCow).

= v1.6 (2010-03-27) =
*   Added Spanish Translations (Thanks Carlos Varela).

= v2.0 (2010-11-29) =
*   Added support for official Tweet button

= v2.1 (2010-12-05) =
*   Fixed issue with the support for official twitter button

= v2.2 (2011-01-23) =
*   Fixed issue with permalink for official twitter button

= v2.3 (2011-01-23) =
*   Added Brazilian Portuguese translation

= v2.4 (2011-05-11) =
*   Added German translations

= v2.5 (2011-05-20) =
*   Added support for twitter intents and bit.ly pro accounts

= v2.6 (2011-05-21) =
*   Reworded the domain text in the settings page.

= v2.7 (2011-09-05) =
*   Enabled custom Bit.ly Pro domains (By Michelle McGinnis) and added Bulgarian and Lithuanian translations

= v2.8 (2011-11-13) =
*   Added French translations

= v2.9 (2012-03-13) =
*   Added translation support for Romanian

= v2.9.1 (2012-07-23) (Dev time: 0.5 hour) =
* Added translation support for Hindi

= v2.9.2 (2012-11-07) (Dev time: 0.5 hour) =
* Added translation support for Irish

= v3.0 (2013-06-03) - (Dev time: 1 hour) =
- Added support for Google Analytics tracking

= v3.0.1 (2013-06-11) - (Dev time: 0.5 hour) =
- Added Danish translations

= v3.0.2 (2014-06-19) - (Dev time: 0.5 hour) =
- Tweak: Removed old PHP4 compatible code
- Tweak: Make it compatible with Easy Digital Downloads plugin. (Issue #5)

= v3.0.3 (2015-01-29) - (Dev time: 0.5 hour) =
- Fix: utm_medium parameter is not appended to the url properly. (Issue #7)

= v3.0.4 (2015-07-26) - (Dev time: 0.5 hour) =
- Fix: Fixed issue custom retweet text. (Issue #8)

= v3.1 - (2016-02-14) - (Dev time: 1 hour) =
- New: Add support for latest Twitter buttons
- New: Removed support for old bit.ly buttons
- Fix: Fix warnings and add checks for defaults
- Fix: Use capability instead of user level
- Tweak: Removed PHP4 compatible code

= v3.1.1 - (2016-04-20) - (Dev time: 0.5 hour) =
- Fix: Fixed a PHP warning notice in Add New Post/page screen. (Issue #9)

== Upgrade Notice ==

= v3.1 =
Added support for the latest Twitter button

= v3.0.3 =
Fix issue in utm_medium parameter

= v3.0.4 =
Fixed issue custom retweet text.

= v3.1.1 =
Fixed a PHP warning notice in Add New Post/page screen
