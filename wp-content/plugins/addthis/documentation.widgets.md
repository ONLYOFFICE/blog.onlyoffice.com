# Documentation for Widgets in the AddThis WordPress Plugin

## About
There are two ways to use widgets. First, users can drag and stop widgets into different widgets areas inside WordPress. Second, they provide developers with an easy way to add a widgets functionality into any part of their theme.

This documentions goes over the information a developer would need to add an AddThis widget directly into the code of their theme.

If you are not already familar with how to use a widget directly in PHP, see WordPress' documentation on [the_widget](https://codex.wordpress.org/Function_Reference/the_widget).

## List / Class Names

1. AddThisGlobalOptionsWidget
1. AddThisWidgetByDomClass

## Supported Arguments
WordPress' [the_widget](https://codex.wordpress.org/Function_Reference/the_widget) function takes three arguements.
1. `$widget` - A string. The name of the widget (one of the widgets in the above list)
1. `$instance` - An associative array.
1. `$args` - An associative array.

The AddThisGlobalOptionsWidget widget does not support any arguments. The rest support the following.

The AddThisWidgetByDomClass supports the following arguments.

Supported keys for `$instance`:
* title - a display title
* class - the class defined for the tool desired. You can find the class for the desired tool in the Get The Code section of that tool's edit screen.

Supported keys for `$args`:
* before_title - standard, see WordPress' [the_widget](https://codex.wordpress.org/Function_Reference/the_widget) documentation
* after_title - standard, see WordPress' [the_widget](https://codex.wordpress.org/Function_Reference/the_widget) documentation
* before_widget - standard, see WordPress' [the_widget](https://codex.wordpress.org/Function_Reference/the_widget) documentation
* after_widget - standard, see WordPress' [the_widget](https://codex.wordpress.org/Function_Reference/the_widget) documentation

## Code Examples

### echoing out script tags for addthis_widget.js script and your local settings
```php
the_widget('AddThisGlobalOptionsWidget');
```

### echoing out the HTML for original sharing buttons with the title 'please share' and the text ':)' after
```php
$instance = array(
    'title' => 'please share',
    'class' => 'addthis_sharing_toolbox',
);
$args = array(
    'after_widget' => ':)'
);
the_widget('AddThisWidgetByDomClass', $instance, $args);
```

### echoing out the HTML for sharing buttons with custom defined share url, title, description and image
```php
$instance = array(
    'title'             => 'please share',
    'class'             => 'addthis_sharing_tool_x4sd8',
    'share-url'         => 'https://www.example.com',
    'share-title'       => 'Check this out',
    'share-description' => 'An article about examples that everyone should read',
    'share-media'       => 'https://www.example.com/image.png',
);
the_widget('AddThisWidgetByDomClass', $instance);
```
