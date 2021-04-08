=== Amazon AWS CDN ===
Contributors: luckychingi
Tags: Amazon, AWS, CDN, Free, Cloudfront, Multisite
Donate link: https://wpadmin.ca/donation/
Requires at least: 4.4.2
Tested up to: 5.4.1
Requires PHP: 7.0
Stable tag: 1.5.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Setting up Amazon CloudFront Distribution can’t get any simple. Use Amazon Cloudfront as a <acronym title='Content Delivery Network'>CDN</acronym> for your WordPress Site. Create per site distribution for Multi-site setup. Let us know what features would you like to have in this plugin.


== Description ==
This plugin helps you setup your AWS CloudFront Distribution and serve static contents (Now supports WordPress Multisite setup). You can also use other CDNs which provides a custom CDN URL (E.G: cdn.YourAwesomeSite.com)

Special thanks to:
@techboomie 
@seocosenza

== Installation ==
= Using the WordPress Plugin Search =



1. Navigate to the `Add New` sub-page under the Plugins admin page.

2. Search for `AWS CDN By WPAdmin`.

3. The plugin should be listed first in the search results.

4. Click the `Install Now` link.

5. Lastly click the `Activate Plugin` link to activate the plugin.



= Uploading in WordPress Admin =



1. [Download the plugin zip file](https://downloads.wordpress.org/plugin/aws-cdn-by-wpadmin.1.5.4.zip) and save it to your computer.

2. Navigate to the `Add New` sub-page under the Plugins admin page.

3. Click the `Upload` link.

4. Select `aws-cdn-by-wpadmin` zip file from where you saved the zip file on your computer.

5. Click the `Install Now` button.

6. Lastly click the `Activate Plugin` link to activate the plugin.



= Using FTP =



1. [Download the plugin zip file](https://downloads.wordpress.org/plugin/aws-cdn-by-wpadmin.1.5.4.zip) and save it to your computer.

2. Extract the `aws-cdn-by-wpadmin` zip file.

3. Create a new directory named `aws-cdn-by-wpadmin` directory in the `../wp-content/plugins/` directory.

4. Upload the files from the folder extracted in Step 2.

4. Activate the plugin on the Plugins admin page.

== Frequently Asked Questions ==
 = CORS Error: No Access-Control-Allow-Origin header is present on the requested resource =
<h3>Apache</h3>
Add the following in your .htaccess file, immediately under '# END WordPress'
<code>
<FilesMatch "\.(ttf|ttc|otf|eot|woff|woff2|font.css)$">
<IfModule mod_headers.c>
Header add Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Origin "*"
</IfModule>
</FilesMatch>
</code>
<h3>Nginx</h3>
Add something like this to your vhost config
<code>
location ~* \.(eot|otf|ttf|woff|woff2)$ {
    add_header Access-Control-Allow-Origin *;
}
</code>
Refer to this article for more info: https://github.com/fontello/fontello/wiki/How-to-setup-server-to-serve-fonts
= How To Create An AWS User =
[Follow the steps in this article](https://wpadmin.ca/how-to-create-an-aws-user-with-limited-permissions-to-access-cloudfront-only/)


= Got a Question? =
[Send me an email](http://wpadmin.ca/contact-us/)

== Screenshots ==
1. screenshot-1.jpg
2. screenshot-2.jpg

== Changelog ==
V.1.5.4
Fixed the strpos() expects parameter 1 to be string in the log file

V.1.5.3
Fixed the Uncaught Error: Object of class stdClass could not be converted to string

V.1.5.2
Plugin now supports relative URLs.

V.1.5.1
Fixed the issue with broken customize theme when the plugin was activated.

V.1.5.0
Fixed ignored files list.


== Upgrade Notice ==
Bugs & Improvements