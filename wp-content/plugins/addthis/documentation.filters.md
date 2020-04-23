
# Documentation for Filters in the AddThis WordPress Plugin

## About
Filters allow developers to hook into plugin functionality in upgrade-safe ways to define very specific behavior by writing their own PHP code snippets.

## List
This is an exhaustive list of supported filters in this plugin that will remain the same between bugfix and minor version releases.

1. addthis_sharing_buttons_enable
1. addthis_sharing_buttons_above_enable
1. addthis_sharing_buttons_below_enable
1. addthis_sharing_buttons_tool
1. addthis_sharing_buttons_above_tool
1. addthis_sharing_buttons_below_tool
1. addthis_sharing_buttons_url
1. addthis_sharing_buttons_title
1. addthis_sharing_buttons_content_filter_priority
1. addthis_sharing_buttons_excerpt_filter_priority
1. addthis_follow_buttons_enable
1. addthis_follow_buttons_above_enable
1. addthis_follow_buttons_below_enable
1. addthis_follow_buttons_tool
1. addthis_follow_buttons_above_tool
1. addthis_follow_buttons_below_tool
1. addthis_follow_buttons_content_filter_priority
1. addthis_follow_buttons_excerpt_filter_priority
1. addthis_recommended_content_enable
1. addthis_recommended_content_above_enable
1. addthis_recommended_content_below_enable
1. addthis_recommended_content_tool
1. addthis_recommended_content_above_tool
1. addthis_recommended_content_below_tool
1. addthis_recommended_content_content_filter_priority
1. addthis_recommended_content_excerpt_filter_priority
1. addthis_custom_html_enable
1. addthis_custom_html_above_enable
1. addthis_custom_html_below_enable
1. addthis_share_array
1. addthis_share_json
1. addthis_config_array
1. addthis_config_json
1. addthis_layers_array
1. addthis_layers_json
1. addthis_config_javascript
1. addthis_content_filter_priority
1. addthis_excerpt_filter_priority

## Details about Particular Types of Filters

### Naming Convention

All filters in this plugin begin with `addthis_`. The part imidiately after that may describe a specific type of tool. The three types of tools are:
1. sharing_buttons: buttons for enabling you users to share links to the site with their social networks
1. follow_buttons: buttons for pointing a user at your profile or page on social networking sites
1. recommended_content: links to additional content on your site customized for the current visitor

Tools can automatically be added onto the beginning or end of content or excerpts. When the filter contains `_above_`, it will affect the tool added before (above) content. When the filter contains `_below_`, it will affect the tool added after (below) content.

### *_enabled
All the filters in this plugin ending with `_enable` can be used to turn on or off the automatic adding of an AddThis tool onto page or post content or excerpts. These filters are passed one boolean paramater and expect a boolean to be returned. Returning `true` (or anything considered truthy in PHP) will turn on the tool, returning `false` (or anything considered falsey in PHP) will turn it off.

Filters ending in `_above_enable` can be used to turn on or off a particular tool above a page or post content or excerpt.

Filters ending in `_below_enable` can be used to turn on or off a particular tool below a page or post content or excerpt.

The filters for any particular tool run in the following order:
1. addthis_sharing_buttons_enable
1. addthis_sharing_buttons_above_enable
1. addthis_sharing_buttons_below_enable

### *_tool

All the filters in the plugin ending with `_tool` can be used to change the AddThis tool automatic added onto the page or post content or excerpts. These filters are bassed one string paramater or a `false` boolean and expect a string to be returned. Returning `false` (or anything considered falsey in PHP) will turn the tool off.

Filters ending in `_above_tool` can be used to change the particular tool above a page or post content or excerpt.

Filters ending in `_below_tool` can be used to change the particular tool below a page or post content or excerpt.

The filters for any particular tool run in the following order:
1. addthis_sharing_buttons_tool
1. addthis_sharing_buttons_above_tool
1. addthis_sharing_buttons_below_tool

#### tool names for each inline tool type
* sharing buttons
  * at-above-post-homepage
  * at-above-post
  * at-above-post-page
  * at-above-post-cat-page
  * at-above-post-arch-page
  * at-below-post-homepage
  * at-below-post
  * at-below-post-page
  * at-below-post-cat-page
  * at-below-post-arch-page
* follow button
  * addthis_horizontal_follow_toolbox: (default) standard horizontal follow buttons
  * addthis_vertical_follow_toolbox: standard vertical follow buttons
  * addthis_custom_follow: custom horizontal follow buttons
* recommended content
  * addthis_recommended_vertical: vertical recommended content
  * at-below-post-recommended: (default) horizontal recommended content

Note: This is not a place for adding custom CSS classes onto AddThis tools. AddThis does not support custom CSS for any of its products.

#### using different sharing buttons on different pages

##### simplist solution
At AddThis.com you can configure inline sharing tools by location (above or below) and by template type (homepage, post, page, category, archive).

If you want on kind of sharing tool above content and abother below, you can set that up there.

Also, if you want one kind of sharing tool on posts and another on your homepage or pages, AddThis.com will let you set that up without any custom development.

Note: be careful not to enabled buttons in the same location and template type combo in multiple tools, as only one or the other will be used.

If you want something more particular, such as one type of sharing button on a custom post type and another on a different custom post type, that will require custom developement. The two ugly solutions below will go into some methods of accomplishing this.

##### really ugly solution

There are 10 tool names used for sharing buttons.
* at-above-post-homepage
* at-above-post
* at-above-post-page
* at-above-post-cat-page
* at-above-post-arch-page
* at-below-post-homepage
* at-below-post
* at-below-post-page
* at-below-post-cat-page
* at-below-post-arch-page

These correlate with the locations (above or below) and template types (homepage, post, page, category, archive) you can select when configuring inline sharing tools at AddThis.com. This plugin then adds tools by the reveant tool name for the location and template type onto content and excerpts.

With the use of *_tool filters, you can overwrite the tool name used for any location. If you enable different tools in different location/template type combos, you can then use the filters to completely overwite what tools show up where.

Note: be careful not to enabled buttons in the same location and template type combo in multiple tools, as only one or the other will be used.

##### slightly less ugly solution

The 10 sharing button tool names in the previous section are specifically used for AddThis profiles that are setup for WordPress. With custom *_tool filters, you can still use a normal (non-WordPress) profile type with this plugin.

However, setting the plugin up with a non-WordPress profile is a little tricky, and it will not work unless you've also added *_tools filters to use the below tool names.

Here are the tool names for normal (non-WordPress) profile types:
* sharing buttons
  * addthis_sharing_toolbox: square sharing buttons
  * addthis_native_toolbox: classic sharing buttons
  * addthis_custom_sharing: custom sharing buttons
  * addthis_responsive_sharing: responsive sharing buttons
  * addthis_jumbo_share: sharing buttons with jumbo share counter
* follow button
  * addthis_horizontal_follow_toolbox: (default) standard horizontal follow buttons
  * addthis_vertical_follow_toolbox: standard vertical follow buttons
  * addthis_custom_follow: custom horizontal follow buttons
* recommended content
  * addthis_recommended_vertical: vertical recommended content
  * addthis_recommended_horizontal: horizontal recommended content

###### using a non-WordPress AddThis profile
This plugin makes it hard to use an AddThis profile that isn't a WordPress type.

If you use the manual registration screen, it will not let you save changes to your profile ID or API key if your profile ID is not valid or not the right type (WordPress).

In the guided registration process, if a normal AddThis profile (not a WordPress profile) is selected, a warning will display. Clicking continue with a non-WordPress profile selected will change the type on the profile.

The way around this is to save a WordPress AddThis site profile into the plugin, and then go into AddThis.com and change the type on the profile after.

### addthis_sharing_buttons_url
This filter is applied to the URL used when someone clicks on a sharing button. This defaults to whatever is returned from [get_permalink](https://developer.wordpress.org/reference/functions/get_permalink/) function in WordPress.

This filter passes one string paramater (the result from [get_permalink](https://developer.wordpress.org/reference/functions/get_permalink/)) and expects a string or something flasey to be returned. Returning `false` (or anything considered falsey in PHP) will leave the sharing button to use the URL in the browser. Share counts will be based off shares of the URL passed.

If you wanted some shares use your homepages URL instead of the one on the page, or want to append a parameter onto the end of every shared URL, this is the filter to use.

Note: If you change the URL used for shares it will change your share counts. Share counts are specific to the URL the sharing buttons would share.

Facebook looks at the URL specified and then it'd OG tags to decide what URL is shared with Facebook. For more information about Facebook shares, please refer to our [Facebook Open Graph Tags](http://support.addthis.com/customer/en/portal/articles/1303425-facebook-open-graph-tags) support page. Also helpful, is the [Facebook Debugger](https://developers.facebook.com/tools/debug/) page.

### addthis_sharing_buttons_title
This filter is applied to the title used when someone clicks on a sharing button. This defaults to whatever is returned from [the_title_attribute](https://codex.wordpress.org/Function_Reference/the_title_attribute) function in WordPress. The results from this filter will then be put through [htmlspecialchars](http://php.net/manual/en/function.htmlspecialchars.php).

If you wanted to limit the title to 100 characters, or append some text, this is the filter to use.

Facebook ignores anything we specify with AddThis for titles. For more information about Facebook shares, please refer to our [Facebook Open Graph Tags](http://support.addthis.com/customer/en/portal/articles/1303425-facebook-open-graph-tags) support page. Also helpful, is the [Facebook Debugger](https://developers.facebook.com/tools/debug/) page.


### *_filter_priority
These filters pass one interger paramater and expects an interger to be returned.

They can be used to alter the order in which AddThis tools get added onto pages and excerpts. The higher the number, the later it is added. By default:
* follow buttons has a priority of 15.
* sharing buttons has a priority of 16.
* recommended content has a priroity of 17.

This filter can also be used to change the priority of a filter to make it run before or after a theme or another plugin runs its content filters.

Filters ending with `_content_filter_priority` are used to determine the priority for filters on `the_content`.

Filters ending with `_excerpt_filter_priority` are used for setting the priority for filters on `get_the_excerpt`, `the_excerpt` and `wp_trim_excerpt`.

### *_array
This filter passes one associative array paramater and expects an associative array to be returned.

There are three window level JavaScript variables that AddThis uses and which you can affect through filters:
* [addthis_share](http://support.addthis.com/customer/en/portal/articles/1337996-the-addthis_share-variable)
* [addthis_config](http://support.addthis.com/customer/en/portal/articles/1337994-the-addthis_config-variable)
* addthis_layers (which is then passed into [addthis.layers()](http://support.addthis.com/customer/en/portal/articles/1200473-smart-layers-api))

These window level variables are put together in PHP associate arrays, and then used with [json_encode](http://php.net/manual/en/function.json-encode.php) to create JavaScript code that is added onto the page. These filters hook into the code just before [json_encode](http://php.net/manual/en/function.json-encode.php) is used, and are an easier place to manipulate these values. You can use these filters to manipulate these variables.

Note: Overriding or deleting values in addthis_share and addthis_config can cause some of your Advanced Settings options within the plugin to be ignored.

Note: If you have configured any tools on your AddThis profile, addthis_layers will be ignored.

### *_json
This filter passes 2 paramaters:
1. a JSON string
1. an associative array.

It expects a JSON string to be returned.

There are three window level JavaScript variables that AddThis uses and which you can affect through filters:
* [addthis_share](http://support.addthis.com/customer/en/portal/articles/1337996-the-addthis_share-variable)
* [addthis_config](http://support.addthis.com/customer/en/portal/articles/1337994-the-addthis_config-variable)
* addthis_layers (which is then passed into [addthis.layers()](http://support.addthis.com/customer/en/portal/articles/1200473-smart-layers-api))

This is the more advanced way of manipulating the values in those variables. The JSON string input is the values of the associative array after being put through the [json_encode](http://php.net/manual/en/function.json-encode.php) function.

Note: Overriding or deleting values in addthis_share and addthis_config can cause some of your Advanced Settings options within the plugin to be ignored.

Note: If you have configured any tools on your AddThis profile, addthis_layers will be ignored.

### addthis_config_javascript
This filter passes one string paramater and expects a string to be returned.

This is the JavaScript from AddThis that is added onto every page, including definitions for [addthis_share](http://support.addthis.com/customer/en/portal/articles/1337996-the-addthis_share-variable), [addthis_config](http://support.addthis.com/customer/en/portal/articles/1337994-the-addthis_config-variable) and addthis_layers and the calling of [addthis.layers()](http://support.addthis.com/customer/en/portal/articles/1200473-smart-layers-api) once the AddThis script has been loaded onto page.

You can use this filter to append or prepend additional JavaScript.

Note: Using this filter to overriding or deleting values in addthis_share and addthis_config can cause some of your Advanced Settings options within the plugin to be ignored.

## Code Examples

### adding follow buttons below pages content/excerpts
The below code would add follow buttons onto the bottom of content or exerpts for pages but not posts.

```php
add_filter('addthis_follow_buttons_below_enable', 'my_add_follow_buttons_filter');
function my_add_follow_buttons_filter($enable) {
    global $post;
    if (is_page($post->ID)) {
        return true;
    } else {
        return false;
    }
}
```

### using the vertical recommended content tool with specific templates
The below code would change the recommended content tool from the default horizontal option to vertical on the skinnypage template.

```php
add_filter('addthis_recommended_content_tool', 'my_recommended_content_vertical_filter');
function my_recommended_content_vertical_filter($toolClass) {
    $template = get_page_template_slug();
    if ($template == 'skinnypage') {
        $toolClass = 'addthis_recommended_vertical';
    }
    return $toolClass;
}
```

### appending a query paramater onto shared URLs
The below code takes the permalink URL for the page and adds ?shared=true onto the end of it.

```php
add_filter('addthis_sharing_buttons_url', 'my_custom_inline_share_url_filter');
function my_custom_inline_share_url_filter($url) {
    $url = $url . '?shared=true';
    return $url;
}
```

### shortening a share title to 100 characters for inline sharing tools
The below code takes the title of the post and deletes anything after the first 100 characters.

```php
add_filter('addthis_sharing_buttons_title', 'my_custom_inline_share_title_filter');
function my_custom_inline_share_title_filter($title) {
    $title = substr($title, 0, 100);
    return $title;
}
```

### show recommended content above sharing buttons on posts and pages
The below code changes the priority of recommended content content filters, moving it above the default priority for sharing buttons.

```php
add_filter('addthis_recommended_content_content_filter_priority', 'my_recommended_content_priority_filter');
function my_recommended_content_priority_filter($priority) {
    return 15;
}
```

###  shortening the shate title to 100 characters for the sharing sidebar
The below code alters the content of [addthis_share](http://support.addthis.com/customer/en/portal/articles/1337996-the-addthis_share-variable) to shorten the shared title for a page.

```php
add_filter('addthis_share_array', 'my_custom_sidebar_share_title_filter');
function my_custom_sidebar_share_title_filter($addthis_share) {
    $title = get_the_title(); // null if not on a post
    if ($title) {
        $title = substr($title, 0, 100);
        $addthis_share['title'] = $title;
    }
    return $addthis_share;
}
```

### remove all addthis_config values
The below code removes all the settings from addthis_config on your pages

```php
add_filter('addthis_config_json', 'my_add_follow_buttons_filter', 10, 2);
/** the 2 in the 4th paramater in the above line is important if you want both
 * the JSON and array parameter if the 2 is left off, then only the JSON param
 * will be passed to your filter
 */
function my_add_follow_buttons_filter($configJson, $configArray) {
  // this is the JSON equivalent of an empty object
  return '{}';
}
```

Note: Overriding or deleting values in addthis_share and addthis_config can cause some of your Advanced Settings options within the plugin to be ignored.

### Overwrite all AddThis JavaScript on your pages
The below code overwirtes all the AddThis JavaScript added by this plugin onto your pages. You might want to do this if this plugin is conflicting with another AddThis plugin's JavaScript.

```php
add_filter('addthis_config_javascript', 'my_blow_away_addthis_javascript_filter');
function my_blow_away_addthis_javascript_filter($javascript) {
  $javascript = ""
    // this overwrites the variable completely and removes all AddThis
    // JavaScript added to your pages by this plugin.
    console.log('please do not copy and paste this code and use as is.);
  ";
  return $javascript;
}
```

Note: Overriding or deleting values in addthis_share and addthis_config can cause some of your Advanced Settings options within the plugin to be ignored.