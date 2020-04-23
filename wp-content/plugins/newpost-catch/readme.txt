=== Plugin Name ===
Contributors: s56bouya
Donate link: http://www.imamura.biz/blog/newpost-catch
Tags: widget, plugin, posts, sidebar, image, images, thumb, thumbnail
Requires at least: 3.3.1
Tested up to: 5.1.1
Stable tag: 1.3.7

Thumbnails in new articles setting widget.

== Description ==

Thumbnails in new articles setting widget.

== Frequently Asked Questions ==

### Installation

1. Unzip "Newpost Catch" archive.
2. Upload folder 'newpost-catch' to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Add the widget to your sidebar from Appearance->Widgets and configure the widget options.

Required PHP5.6+

### Apply your own css style

(Located in the plug-in directory) CSS "style.css" file the default

(Please create a directory under the "/wp-content/themes/theme directory/css/") CSS file for customization "newpost-catch.css"

**Priority**

> newpost-catch.css > style.css

Will be applied at.

With regard to CSS will either use the default CSS,

I used the CSS that you created in your own, please change to your liking.


### notice

With the version up of the plugin, so will be overwritten "style.css" file each time,

I think how to directly edit the "style.css" file and how would you or declined.

If you wish to apply a CSS style on its own,

In the "/wp-content/themes/theme directory/css/" as you please create a "newpost-catch.css".

Please the name of the file to create and "newpost-catch.css". The other is the file name, does not apply.


### Shortcode

Can use the shortcode in a textwidget and theme files.

> \[npc\]

#### parameter

* **id**(string) - Name of the id attribute "ul" element(default:npcatch)
* **post_type**(string) - Use post types. Retrieves posts by Post Types(default:post)
* **cat**(int) - Use category id(default:NULL)
* **width**(int) - Thumbnail width px(default:10)
* **height**(int) - Thumbnail height px(default:10)
* **posts_per_page**(int) - Number of post to show per page(default:5)
* **sticky**(boolean) - Sticky posts or not(on:1 off:0 default:0)
* **offset**(int) - Number of post to displace or pass over(default:0)
* **orderby**(string) - Sort retrieved posts by parameter(default:date)
* **order**(string) - Designates the ascending or descending order of the 'orderby' parameter(default:DESC)
* **date**(boolean) - Display date(on:1 off:0 default:0)
* **dynamic**(boolean) - Show only articles in the same category as the article being displayed. If you specify both "cat" parameters, "dynamic" priority(on:1 off:0 default:0)

**Example1. Want change id name of ul element.**

> \[npc id="test"\]

**Example2. Show only articles in the same category as the article being displayed.(post_type is "post" only. and is_single() == true )**

When the post other than, post_type of the current page is the value of the priority parameter cat

> \[npc dynamic="1"\]


== Screenshots ==

1. To display the eye-catching(Thumbnail) set to Latest Post.
2. Localized support is in order. 
3. Is simple to add the side bar, to set the size of the thumbnail, posts, post date

== Changelog ==

= 1.3.7 =
* Compatibility check with WP 5.1.1

= 1.3.6 =
* Compatibility check with WP 4.9.7

= 1.3.5 =
* Compatibility check with WP 4.8.2
* Fixed : Get widget checkbox value
* Change : Default CSS style
* Change : Default image no_thumb.png

= 1.3.4 =
* Compatibility check with WP 4.7.3.
* Change post_status only shows publish.
* Meta_key parameter is available with shortcode.

= 1.3.3 =
* Compatibility check with WP 4.7.2.
* Bugfix(thumbnail indication when shortcode was used).

= 1.3.2 =
* Compatibility check with WP 4.6.1.

= 1.3.1 =
* Required PHP5.3+(changed widgets_init hook).
* Abolished option page.
* Compatibility check with WP 4.5.

= 1.3.0 =
* Compatibility check with WP 4.4.2.

= 1.2.9 =
* Removed the same name method.
* Apply filters default thumbnail.

= 1.2.8 =
* The constructor method was corrected.

= 1.2.7 =
* The css reading order adjustment.

= 1.2.6 =
* Compatibility check with WP 4.1 and Twenty Fifteen Theme.

= 1.2.5 =
* Use post types. Retrieves posts by Post Types.

= 1.2.4 =
* Compatibility check with WP 4.0.

= 1.2.3 =
* Compatibility check with WP 3.9.

= 1.2.2 =
* Bugfix(Link to the Setting Thumbnails in the widget).

= 1.2.1 =
* The translation file modification.

= 1.2.0 =
* Add Admin Menu「Setting Thumbnails」.

= 1.1.9 =
* Compatibility check with WP 3.8.1.

= 1.1.8 =
* Fixed reading of newpost-catch.css file (when using child themes)

= 1.1.7 =
* Compatibility check with WP 3.8.

= 1.1.6 =
* Compatibility check with WP 3.7.1.
* Add Shortcode.

= 1.1.5 =
* Compatibility check with WP 3.6.

= 1.1.4 =
* Bugfix(the display the first image in the post).

= 1.1.3 =
* Bugfix.

= 1.1.2 =
* Compatibility check with WP 3.5.2.
* Bugfix.

= 1.1.1 =

* Update Screenshots No.2.

= 1.1.0 =
* Compatibility check with WP 3.5.1.
* Add option「Display date」「Display sticky post」「Use default css」「Display category(ies)」.

= 1.0.9 =
* Compatibility check with WP 3.5.

= 1.0.8 =
* Bugfix.

= 1.0.7 =
* Very minor changes that do not affect the operation of the plugin.

= 1.0.6 =
* Bugfix.

= 1.0.5 =
* Has been deleted in the action, webkit css3.

= 1.0.4 =
* If you do not have eye-catching image, the display the first image in the post.
* In the setting of the widget, so that you can change the ratio of variable width and height of the image size.

= 1.0.3 =
* Update the [FAQ page](http://wordpress.org/extend/plugins/newpost-catch/faq/)

= 1.0.2 =
* [Dedicated plugin public page.](http://www.imamura.biz/blog/newpost-catch/)
* Described in the [FAQ page](http://wordpress.org/extend/plugins/newpost-catch/faq/) "How to customize the css".

= 1.0.1 =
* Has been updated so that it does not include the "Stick this post to the front page".
* A change since the previous version.

= 1.0.0 =
* First stable version.

== Upgrade Notice ==

Nothing in particular.