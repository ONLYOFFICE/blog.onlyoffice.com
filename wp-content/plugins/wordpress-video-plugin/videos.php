<?php
/*
Plugin Name: Wordpress Video Plugin
Plugin URI: http://www.daburna.de/blog/2006/12/13/wordpress-video-plugin/
Description: The Wordpress Video Plugin adds a filter for WordPress that allows easy video embedding of 66 supported video sites. Enter [videosite id] at a post and you will see a video. For using the plugin, read the <a href="http://www.daburna.de/dokuwiki/doku.php/instruction" title="wordpress video plugin instruction">instruction page</a> or readme file!
Version: 0.759
Author: Oliver Wunder 
Author URI: http://www.daburna.de/
Text Domain: ow_wvp
Domain Path: /lang

*/


// Archive.org Code

define("ARCHIVEORG_WIDTH", 640); // default width
define("ARCHIVEORG_HEIGHT", 480); // default height
define("ARCHIVEORG_REGEXP", "/\[archive ([[:print:]]+)\]/");
define("ARCHIVEORG_TARGET", "<iframe src=\"http://archive.org/embed/###URL###\" width=\"###WIDTH###\" height=\"###HEIGHT###\" frameborder=\"0\" webkitallowfullscreen=\"true\" mozallowfullscreen=\"true\" allowfullscreen></iframe>");
																																																																																												
function archiveorg_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = ARCHIVEORG_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", ARCHIVEORG_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", ARCHIVEORG_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", ARCHIVEORG_WIDTH, $output);
		$output = str_replace("###HEIGHT###", ARCHIVEORG_HEIGHT, $output);	
	}
	return ($output);
}
function archiveorg_plugin($content)
{
	return (preg_replace_callback(ARCHIVEORG_REGEXP, 'archiveorg_plugin_callback', $content));
}

add_filter('the_content', 'archiveorg_plugin');
add_filter('the_content_feed', 'archiveorg_plugin');
add_filter('comment_text', 'archiveorg_plugin');
add_filter('the_excerpt', 'archiveorg_plugin');


// MPORA Code

define("MPORA_WIDTH", 480); // default width
define("MPORA_HEIGHT", 270); // default height
define("MPORA_REGEXP", "/\[mpora ([[:print:]]+)\]/");
define("MPORA_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\" id=\"mporaplayer_###URL###\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" type=\"application/x-shockwave-flash\" ><param name=\"movie\" value=\"http://video.mpora.com/ep/###URL###/\"></param><param name=\"wmode\" value=\"transparent\"></param><param name=\"allowScriptAccess\" value=\"always\"></param><param name=\"allowFullScreen\" value=\true\"></param><embed src=\"http://video.mpora.com/ep/###URL###/\" width=\"###WIDTH###\" height=\"###HEIGHT###\" wmode=\"transparent\" allowfullscreen=\"true\" allowscriptaccess=\"always\" type=\"application/x-shockwave-flash\"></embed></object>");

																																																																																												
function mpora_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = MPORA_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", MPORA_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", MPORA_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", MPORA_WIDTH, $output);
		$output = str_replace("###HEIGHT###", MPORA_HEIGHT, $output);	
	}
	return ($output);
}
function mpora_plugin($content)
{
	return (preg_replace_callback(MPORA_REGEXP, 'mpora_plugin_callback', $content));
}

add_filter('the_content', 'mpora_plugin');
add_filter('the_content_feed', 'mpora_plugin');
add_filter('comment_text', 'mpora_plugin');
add_filter('the_excerpt', 'mpora_plugin');

// VZAAR Code

define("VZAAR_WIDTH",	576);
define("VZAAR_HEIGHT",	324);
define("VZAAR_REGEXP",	"/\[vzaar ([[:print:]]+)\]/");
define("VZAAR_TARGET",	"<object id=\"video\" width=\"###WIDTH###\" height=\"###HEIGHT###\" type=\"application/x-shockwave-flash\" data=\"http://view.vzaar.com/###URL###.flashplayer\">
        <param name=\"movie\" value=\"http://view.vzaar.com/###URL###.flashplayer\">	
        <param name=\"allowScriptAccess\" value=\"always\">
        <param name=\"allowFullScreen\" value=\"true\">
        <param name=\"wmode\" value=\"transparent\">
        <param name=\"flashvars\" value=\"autoplay=true&border=none&brandText=vzaar+-+online+video+for+professionals&brandLink=http%3A%2F%2Fvzaar.com%2F&looping=true\">
        <embed src=\"http://view.vzaar.com/###URL###.flashplayer\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"###WIDTH###\" height=\"###HEIGHT###\" allowScriptAccess=\"always\" allowFullScreen=\"true\" flashvars=\"autoplay=true&brandText=vzaar+-+online+video+for+professionals&border=none&brandLink=http%3A%2F%2Fvzaar.com%2F&looping=true\"></embed>
        <video width=\"###WIDTH###\" height=\"###HEIGHT###\" src=\"http://view.vzaar.com/###URL###.mobile\" poster=\"http://view.vzaar.com/###URL###.image\" controls onclick=\"this.play();\"></video></object>");

function vzaar_plugin_callback( $match )
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = VZAAR_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", VZAAR_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", VZAAR_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", VZAAR_WIDTH, $output);
		$output = str_replace("###HEIGHT###", VZAAR_HEIGHT, $output);	
	}
	return ($output);
}
function vzaar_plugin( $content )
{
	return (preg_replace_callback(VZAAR_REGEXP, 'vzaar_plugin_callback', $content));
}

add_filter('the_content', 'vzaar_plugin');
add_filter('the_content_rss', 'vzaar_plugin');
add_filter('comment_text', 'vzaar_plugin');
add_filter('the_excerpt', 'vzaar_plugin');


// mqsto.com/video Code by bedio

define("MQSTOVIDEOCOM_WIDTH", 610);
define("MQSTOVIDEOCOM_HEIGHT", 488);
define("MQSTOVIDEOCOM_REGEXP", "/\[mqstovideocom ([[:print:]]+)\]/");
define("MQSTOVIDEOCOM_TARGET", "<object width=\"".MQSTOVIDEOCOM_WIDTH."\" height=\"".MQSTOVIDEOCOM_HEIGHT."\" allowfullscreen=\"true\" allowscriptaccess=\"always\" allownetworking=\"all\"><param name=\"movie\" value=\"http://mqsto.com/vids/###URL###\"><embed src=\"http://mqsto.com/vids/###URL###\" width=\"500\" height=\"400\" allowfullscreen=\"true\" allowscriptaccess=\"always\" allownetworking=\"all\"></embed></object>");


function mqstovideocom_plugin_callback($match) {
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$urltrailer = "http://mqsto.com/video/" . $tag_parts[1] . "";
	$urltrailer2 = file_get_contents($urltrailer);
	$first2=explode("http://mqsto.com/vids/",$urltrailer2);
	$second2=explode("\"><embed",$first2[1]);
	$output = MQSTOVIDEOCOM_TARGET;
	$output = str_replace("###URL###", $second2[0], $output);
	return ($output);
}

function mqstovideocom_plugin($content) {
	return preg_replace_callback(MQSTOVIDEOCOM_REGEXP, 'mqstovideocom_plugin_callback', $content);
}

add_filter('the_content', 'mqstovideocom_plugin');
add_filter('the_content_rss', 'mqstovideocom_plugin');
add_filter('comment_text', 'mqstovideocom_plugin');
add_filter('the_excerpt', 'mqstovideocom_plugin');


// CBS Code

define("CBS_WIDTH", 480); // default width
define("CBS_HEIGHT", 270); // default height
define("CBS_REGEXP", "/\[cbs ([[:print:]]+)\]/");
define("CBS_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://www.cbs.com/e/###URL###/cbs/1/\" /></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowScriptAccess\" value=\"always\"></param><embed width=\"###WIDTH###\" height=\"###HEIGHT###\" src=\"http://www.cbs.com/e/###URL###/cbs/1/\" allowFullScreen=\"true\" allowScriptAccess=\"always\" type=\"application/x-shockwave-flash\"></embed></object>");

function cbs_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = CBS_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", CBS_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", CBS_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", CBS_WIDTH, $output);
		$output = str_replace("###HEIGHT###", CBS_HEIGHT, $output);	
	}
	return ($output);
}
function cbs_plugin($content)
{
	return (preg_replace_callback(CBS_REGEXP, 'cbs_plugin_callback', $content));
}

add_filter('the_content', 'cbs_plugin',1);
add_filter('the_content_rss', 'cbs_plugin');
add_filter('comment_text', 'cbs_plugin');

// Hulu Code

define("HULU_WIDTH", 480); // default width
define("HULU_HEIGHT", 270); // default height
define("HULU_REGEXP", "/\[hulu ([[:print:]]+)\]/");
define("HULU_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0\"><param name=\"allowFullScreen\" value=\"true\" /><param name=\"src\" value=\"http://www.hulu.com/embed/###URL###\" /><param name=\"allowfullscreen\" value=\"true\" /><embed type=\"application/x-shockwave-flash\" width=\"###WIDTH###\" height=\"###HEIGHT###\" src=\"http://www.hulu.com/embed/###URL###\" allowfullscreen=\"true\"></embed></object>");


function hulu_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = HULU_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", HULU_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", HULU_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", HULU_WIDTH, $output);
		$output = str_replace("###HEIGHT###", HULU_HEIGHT, $output);	
	}
	return ($output);
}
function hulu_plugin($content)
{
	return (preg_replace_callback(HULU_REGEXP, 'hulu_plugin_callback', $content));
}

add_filter('the_content', 'hulu_plugin',1);
add_filter('the_content_rss', 'hulu_plugin');
add_filter('comment_text', 'hulu_plugin');


// ISeeIt.TV Code by David Fudge

define("ISEEITTV_WIDTH",	675);
define("ISEEITTV_HEIGHT",	380);
define("ISEEITTV_REGEXP",	"/\[iseeittv ([[:print:]]+)\]/");
define("ISEEITTV_TARGET",	"<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://www.iseeit.tv/video/###URL###\" /><param name=\"allowFullScreen\" value=\"true\" /><embed src=\"http://www.iseeit.tv/video/###URL###\" width=\"###WIDTH###\" height=\"###HEIGHT###\" allowFullScreen=\"true\" type=\"application/x-shockwave-flash\" /></object>");

function iseeittv_plugin_callback( $match )
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = ISEEITTV_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", ISEEITTV_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", ISEEITTV_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", ISEEITTV_WIDTH, $output);
		$output = str_replace("###HEIGHT###", ISEEITTV_HEIGHT, $output);	
	}
	return ($output);
}
function iseeittv_plugin( $content )
{
	return (preg_replace_callback(ISEEITTV_REGEXP, 'iseeittv_plugin_callback', $content));
}

add_filter('the_content', 'iseeittv_plugin');
add_filter('the_content_rss', 'iseeittv_plugin');
add_filter('comment_text', 'iseeittv_plugin');
add_filter('the_excerpt', 'iseeittv_plugin');


// Novamov Code

define("NOVAMOV_WIDTH", 590); // default width
define("NOVAMOV_HEIGHT", 430); // default height
define("NOVAMOV_REGEXP", "/\[novamov ([[:print:]]+)\]/");
define("NOVAMOV_TARGET", "<iframe style=\"overflow: hidden; border: 0; width: ".NOVAMOV_WIDTH."px; height: ".NOVAMOV_HEIGHT."px\" src=\"http://www.novamov.com/embed.php?v=###URL###\" scrolling=\"no\"></iframe>");
																																																																																					
function novamov_plugin_callback($match) {
	$output = NOVAMOV_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function novamov_plugin($content) {
	return preg_replace_callback(NOVAMOV_REGEXP, 'novamov_plugin_callback', $content);
}

add_filter('the_content', 'novamov_plugin');
add_filter('the_content_rss', 'novamov_plugin');
add_filter('comment_text', 'novamov_plugin');
add_filter('the_excerpt', 'novamov_plugin');

// Kewego Code

define("KEWEGO_WIDTH", 400); // default width
define("KEWEGO_HEIGHT", 300); // default height
define("KEWEGO_PLAYERKEY", "9c37f60da51b"); //player key 
define("KEWEGO_SKINKEY", "71703ed5cea1"); //skin key
define("KEWEGO_LCODE", "fr"); //language code
define("KEWEGO_REGEXP", "/\[kewego ([[:print:]]+)\]/");

define("KEWEGO_TARGET", "<object name=\"iLyROoafJCWA\" id=\"iLyROoafJCWA\" type=\"application/x-shockwave-flash\" data=\"http://sa.kewego.com/swf/p3/epix.swf\" width=\"###WIDTH###\" height=\"###HEIGHT###\">  <param name=\"flashVars\" value=\"language_code=###LCODE###&playerKey=###PKEY###&skinKey=###SKEY###&sig=###URL###&autostart=false\" />  <param name=\"movie\" value=\"http://sa.kewego.com/swf/p3/epix.swf\" />  <param name=\"allowFullScreen\" value=\"true\" />  <param name=\"allowscriptaccess\" value=\"always\" /></object>");

function kewego_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = KEWEGO_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	$output = str_replace("###LCODE###", KEWEGO_LCODE, $output);
	$output = str_replace("###PKEY###", KEWEGO_PLAYERKEY, $output);
	$output = str_replace("###SKEY###", KEWEGO_SKINKEY, $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", KEWEGO_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", KEWEGO_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", KEWEGO_WIDTH, $output);
		$output = str_replace("###HEIGHT###", KEWEGO_HEIGHT, $output);	
	}
	return ($output);
}
function kewego_plugin($content)
{
	return (preg_replace_callback(KEWEGO_REGEXP, 'kewego_plugin_callback', $content));
}

add_filter('the_content', 'kewego_plugin', 1);
add_filter('the_content_rss', 'kewego_plugin');
add_filter('comment_text', 'kewego_plugin');
add_filter('the_excerpt', 'kewego_plugin');

// FLICKR CODE by an anonymous user

define("FLICKR_WIDTH", 308); // default width
define("FLICKR_HEIGHT", 250); // default height
define("FLICKR_REGEXP", "/\[flickr ([[:print:]]+)\]/");
define("FLICKR_TARGET", "<object type=\"application/x-shockwave-flash\" width=\"###WIDTH###\" height=\"###HEIGHT###\" data=\"http://www.flickr.com/apps/video/stewart.swf?v=71377\"><param name=\"flashvars\" value=\"intl_lang=en-us&photo_secret=1669be43ac&photo_id=###URL###&hd_default=false\"></param><param name=\"movie\" value=\"http://www.flickr.com/apps/video/stewart.swf?v=71377\" /><param name=\"bgcolor\" value=\"#000000\"></param><param name=\"allowFullScreen\" value=\"true\" /><embed src=\"http://www.flickr.com/apps/video/stewart.swf?v=71377\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" bgcolor=\"#0000000\" flashvars=\"intl_lang=en-us&photo_secret=1669be43ac&photo_id=###URL###&hd_default=false\" width=\"###WIDTH###\" height=\"###HEIGHT###\"></embed></object>");

function flickr_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = FLICKR_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", FLICKR_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", FLICKR_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", FLICKR_WIDTH, $output);
		$output = str_replace("###HEIGHT###", FLICKR_HEIGHT, $output);	
	}
	return ($output);
}
function flickr_plugin($content)
{
	return (preg_replace_callback(FLICKR_REGEXP, 'flickr_plugin_callback', $content));
}

add_filter('the_content', 'flickr_plugin',1);
add_filter('the_content_rss', 'flickr_plugin');
add_filter('comment_text', 'flickr_plugin');
add_filter('the_excerpt', 'flickr_plugin');

// FB Code
// Code for FaceBook video
// credits: roberto scano http://robertoscano.info

define("FB_WIDTH", 470);
define("FB_HEIGHT", 306);
define("FB_REGEXP", "/\[FB ([[:print:]]+)\]/");
define("FB_TARGET", "<object width=\"".FB_WIDTH."\" height=\"".FB_HEIGHT."\"><param name=\"allowfullscreen\" value=\"true\" /><param name=\"allowscriptaccess\" value=\"always\" /><param name=\"movie\" value=\"http://www.facebook.com/v/###URL###\" /><embed src=\"http://www.facebook.com/v/###URL###\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"".FB_WIDTH."\" height=\"".FB_HEIGHT."\"></embed></object>");

function FB_plugin_callback($match) {
	$output = FB_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function FB_plugin($content) {
	return preg_replace_callback(FB_REGEXP, 'FB_plugin_callback',
$content);
}

add_filter('the_content', 'FB_plugin');
add_filter('the_content_rss', 'FB_plugin');
add_filter('comment_text', 'FB_plugin');
add_filter('the_excerpt', 'FB_plugin');

// current code

define("CURRENT_WIDTH", 400); // default width
define("CURRENT_HEIGHT", 342); // default height
define("CURRENT_REGEXP", "/\[current ([[:print:]]+)\]/");
define("CURRENT_TARGET", "<object width=\"###WIDTH###\"  height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://current.com/e/###URL###/en_US\"></param><param name=\"wmode\" value=\"transparent\"></param><param name=\"allowfullscreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"http://current.com/e/###URL###/en_US\" type=\"application/x-shockwave-flash\"  width=\"###WIDTH###\"  height=\"###HEIGHT###\" wmode=\"transparent\" allowfullscreen=\"true\" allowscriptaccess=\"always\"></embed></object>");

function current_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = CURRENT_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", CURRENT_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", CURRENT_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", CURRENT_WIDTH, $output);
		$output = str_replace("###HEIGHT###", CURRENT_HEIGHT, $output);	
	}
	return ($output);
}
function current_plugin($content)
{
	return (preg_replace_callback(CURRENT_REGEXP, 'current_plugin_callback', $content));
}

add_filter('the_content', 'current_plugin');
add_filter('the_content_rss', 'current_plugin');
add_filter('comment_text', 'current_plugin');
add_filter('the_excerpt', 'current_plugin');


// screencast-o-matic code

define("SCREENCAST_WIDTH", 504); // default width
define("SCREENCAST_HEIGHT", 424); // default height
define("SCREENCAST_REGEXP", "/\[screencast ([[:print:]]+)\]/");
define("SCREENCAST_TARGET", "<object width=\"###WIDTH###\"  height=\"###HEIGHT###\" data=\"http://www.screencast-o-matic.com/embed?sc=###URL###&w=500&np=0&v=2\" type=\"text/html\"></object>");

function screencast_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = SCREENCAST_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", SCREENCAST_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", SCREENCAST_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", SCREENCAST_WIDTH, $output);
		$output = str_replace("###HEIGHT###", SCREENCAST_HEIGHT, $output);	
	}
	return ($output);
}
function screencast_plugin($content)
{
	return (preg_replace_callback(SCREENCAST_REGEXP, 'screencast_plugin_callback', $content));
}

add_filter('the_content', 'screencast_plugin');
add_filter('the_content_rss', 'screencast_plugin');
add_filter('comment_text', 'screencast_plugin');
add_filter('the_excerpt', 'screencast_plugin');

// dotSUB code

define("DOTSUB_WIDTH", 420); // default width
define("DOTSUB_HEIGHT", 347); // default height
define("DOTSUB_REGEXP", "/\[dotsub ([[:print:]]+)\]/");
define("DOTSUB_TARGET", "<object width=\"###WIDTH###\"  height=\"###HEIGHT###\" data=\"http://dotsub.com/media/###URL###/e/m\" type=\"text/html\"></object>");

function dotsub_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = DOTSUB_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", DOTSUB_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", DOTSUB_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", DOTSUB_WIDTH, $output);
		$output = str_replace("###HEIGHT###", DOTSUB_HEIGHT, $output);	
	}
	return ($output);
}
function dotsub_plugin($content)
{
	return (preg_replace_callback(DOTSUB_REGEXP, 'dotsub_plugin_callback', $content));
}

add_filter('the_content', 'dotsub_plugin');
add_filter('the_content_rss', 'dotsub_plugin');
add_filter('comment_text', 'dotsub_plugin');
add_filter('the_excerpt', 'dotsub_plugin');

// OnSMASH code

define("ONSMASH_WIDTH", 448); // default width
define("ONSMASH_HEIGHT", 374); // default height
define("ONSMASH_REGEXP", "/\[onsmash ([[:print:]]+)\]/");
define("ONSMASH_TARGET", "<object width=\"###WIDTH###\"  height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://videos.onsmash.com/e/###URL###\"></param><param name=\"allowFullscreen\" value=\"true\"><param name=\"allowScriptAccess\" value=\"always\"></param><param name=\"allowNetworking\" value=\"all\"></param><embed src=\"http://videos.onsmash.com/e/###URL###\" type=\"application/x-shockwave-flash\" allowFullScreen=\"true\" allowNetworking=\"all\" allowScriptAccess=\"always\" width=\"###WIDTH###\"  height=\"###HEIGHT###\"></embed></object>");

function onsmash_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = ONSMASH_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", ONSMASH_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", ONSMASH_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", ONSMASH_WIDTH, $output);
		$output = str_replace("###HEIGHT###", ONSMASH_HEIGHT, $output);	
	}
	return ($output);
}
function onsmash_plugin($content)
{
	return (preg_replace_callback(ONSMASH_REGEXP, 'onsmash_plugin_callback', $content));
}

add_filter('the_content', 'onsmash_plugin');
add_filter('the_content_rss', 'onsmash_plugin');
add_filter('comment_text', 'onsmash_plugin');
add_filter('the_excerpt', 'onsmash_plugin');

// Smotri.Com code

define("SMOTRI_WIDTH", 400); // default width
define("SMOTRI_HEIGHT", 330); // default height
define("SMOTRI_REGEXP", "/\[smotri ([[:print:]]+)\]/");
define("SMOTRI_TARGET", "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://pics.smotri.com/scrubber_custom8.swf?file=###URL###&bufferTime=3&autoStart=false&str_lang=eng&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color_lightaqua.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml\" /><param name=\"allowScriptAccess\" value=\"always\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"bgcolor\" value=\"#ffffff\" /><embed src=\"http://pics.smotri.com/scrubber_custom8.swf?file=###URL###&bufferTime=3&autoStart=false&str_lang=eng&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color_lightaqua.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml\" quality=\"high\" allowscriptaccess=\"always\" allowfullscreen=\"true\" wmode=\"window\"  width=\"###WIDTH###\" height=\"###HEIGHT###\" type=\"application/x-shockwave-flash\"></embed></object>");

function smotri_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = SMOTRI_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", SMOTRI_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", SMOTRI_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", SMOTRI_WIDTH, $output);
		$output = str_replace("###HEIGHT###", SMOTRI_HEIGHT, $output);	
	}
	return ($output);
}
function smotri_plugin($content)
{
	return (preg_replace_callback(SMOTRI_REGEXP, 'smotri_plugin_callback', $content));
}

add_filter('the_content', 'smotri_plugin');
add_filter('the_content_rss', 'smotri_plugin');
add_filter('comment_text', 'smotri_plugin');
add_filter('the_excerpt', 'smotri_plugin');

// wat.tv code by Bertimus (http://www.born2buzz.com/)

define("WAT_WIDTH", 430); // default width
define("WAT_HEIGHT", 385); // default height
define("WAT_REGEXP", "/\[wat ([[:print:]]+)\]/");
define("WAT_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://www.wat.tv/swf2/###URL###\" /><param name=\"allowScriptAccess\" value=\"always\" /><param name=\"allowFullScreen\" value=\"true\" /><embed src=\"http://www.wat.tv/swf2/###URL###\" type=\"application/x-shockwave-flash\" width=\"###WIDTH###\" height=\"###HEIGHT###\" allowScriptAccess=\"always\" allowFullScreen=\"true\"></embed></object>");

function wat_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = WAT_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", WAT_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", WAT_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", WAT_WIDTH, $output);
		$output = str_replace("###HEIGHT###", WAT_HEIGHT, $output);	
	}
	return ($output);
}
function wat_plugin($content)
{
	return (preg_replace_callback(WAT_REGEXP, 'wat_plugin_callback', $content));
}

add_filter('the_content', 'wat_plugin');
add_filter('the_content_rss', 'wat_plugin');
add_filter('comment_text', 'wat_plugin');
add_filter('the_excerpt', 'wat_plugin');

// Guba code

define("GUBA_WIDTH", 375); // default width
define("GUBA_HEIGHT", 360); // default height
define("GUBA_REGEXP", "/\[guba ([[:print:]]+)\]/");
define("GUBA_TARGET", "<embed src=\"http://www.guba.com/f/root.swf?video_url=http://free.guba.com/uploaditem/###URL###/flash.flv&isEmbeddedPlayer=true\" quality=\"best\" bgcolor=\"#FFFFFF\" menu=\"true\" width=\"###WIDTH###\" height=\"###HEIGHT###\" name=\"root\" id=\"root\" align=\"middle\" scaleMode=\"noScale\" allowScriptAccess=\"always\" allowFullScreen=\"true\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\"></embed>");

function guba_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = GUBA_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", GUBA_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", GUBA_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", GUBA_WIDTH, $output);
		$output = str_replace("###HEIGHT###", GUBA_HEIGHT, $output);	
	}
	return ($output);
}
function guba_plugin($content)
{
	return (preg_replace_callback(GUBA_REGEXP, 'guba_plugin_callback', $content));
}

add_filter('the_content', 'guba_plugin');
add_filter('the_content_rss', 'guba_plugin');
add_filter('comment_text', 'guba_plugin');
add_filter('the_excerpt', 'guba_plugin');

// GoalVideoz code

define("GOALVIDEOZ_WIDTH", 425); // default width
define("GOALVIDEOZ_HEIGHT", 350); // default height
define("GOALVIDEOZ_REGEXP", "/\[goalvideoz ([[:print:]]+)\]/");
define("GOALVIDEOZ_TARGET", "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0\" width=\"###WIDTH###\" height=\"###HEIGHT###\" id=\"vpod2\ align=\"middle\"><param name=\"allowScriptAccess\" value=\"sameDomain\" /><param name=\"wmode\" value=\"transparent\"><param name=\"movie\" value=\"http://www.goalvideoz.com/vpod2.swf?id=###URL###\" /><param name=\"quality\" value=\"high\" /><embed src=\"http://www.goalvideoz.com/vpod2.swf?id=###URL###\" quality=\"high\" width=\"###WIDTH###\" height=\"###HEIGHT###\" name=\"vpod\" align=\"middle\" allowScriptAccess=\"sameDomain\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" /></object>");

function goalvideoz_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = GOALVIDEOZ_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", GOALVIDEOZ_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", GOALVIDEOZ_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", GOALVIDEOZ_WIDTH, $output);
		$output = str_replace("###HEIGHT###", GOALVIDEOZ_HEIGHT, $output);	
	}
	return ($output);
}
function goalvideoz_plugin($content)
{
	return (preg_replace_callback(GOALVIDEOZ_REGEXP, 'goalvideoz_plugin_callback', $content));
}

add_filter('the_content', 'goalvideoz_plugin');
add_filter('the_content_rss', 'goalvideoz_plugin');
add_filter('comment_text', 'goalvideoz_plugin');
add_filter('the_excerpt', 'goalvideoz_plugin');

// mojvideo.com Slovenska Video Skupnost code

define("MOJVIDEO_WIDTH", 400); // default width
define("MOJVIDEO_HEIGHT", 320); // default height
define("MOJVIDEO_REGEXP", "/\[mojvideo ([[:print:]]+)\]/");
define("MOJVIDEO_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://www.mojvideo.com/v/###URL###\"></param><param name=\"wmode\" value=\"transparent\"></param><embed src=\"http://www.mojvideo.com/v/###URL###\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"###WIDTH###\" height=\"###HEIGHT###\"></embed></object>");

function mojvideo_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = MOJVIDEO_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", MOJVIDEO_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", MOJVIDEO_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", MOJVIDEO_WIDTH, $output);
		$output = str_replace("###HEIGHT###", MOJVIDEO_HEIGHT, $output);	
	}
	return ($output);
}
function mojvideo_plugin($content)
{
	return (preg_replace_callback(MOJVIDEO_REGEXP, 'mojvideo_plugin_callback', $content));
}

add_filter('the_content', 'mojvideo_plugin');
add_filter('the_content_rss', 'mojvideo_plugin');
add_filter('comment_text', 'mojvideo_plugin');
add_filter('the_excerpt', 'mojvideo_plugin');

// ClipSyndicate Code by Antonio SJ Musumeci

define("CLIPSYN_WIDTH", 300); // default width
define("CLIPSYN_HEIGHT", 265); // default height
define("CLIPSYN_REGEXP", "/\[clipsyndicate ([[:print:]]+)\]/");
define("CLIPSYN_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://eplayer.clipsyndicate.com/cs_api/get_swf\"></param><param name=\"flashvars\" value=\"swfHome=eplayer.clipsyndicate.com&va_id=###URL###\"></param><embed src=\"http://eplayer.clipsyndicate.com/cs_api/get_swf\" flashvars=\"swfHome=eplayer.clipsyndicate.com&va_id=###URL###\" type=\"application/x-shockwave-flash\" width=\"###WIDTH###\" height=\"###HEIGHT###\"></embed></object>");

function clipsyn_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = CLIPSYN_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", CLIPSYN_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", CLIPSYN_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", CLIPSYN_WIDTH, $output);
		$output = str_replace("###HEIGHT###", CLIPSYN_HEIGHT, $output);	
	}
	return ($output);
}
function clipsyn_plugin($content)
{
	return (preg_replace_callback(CLIPSYN_REGEXP, 'clipsyn_plugin_callback', $content));
}

add_filter('the_content', 'clipsyn_plugin');
add_filter('the_content_rss', 'clipsyn_plugin');
add_filter('comment_text', 'clipsyn_plugin');
add_filter('the_excerpt', 'clipsyn_plugin');

// Youreporter Code by Giacomo

define("YOUREPORTER_WIDTH", 450);
define("YOUREPORTER_HEIGHT", 340);
define("YOUREPORTER_REGEXP", "/\[youreporter ([[:print:]]+)\]/");
define("YOUREPORTER_TARGET", "<embed src=\"http://www.youreporter.it/player/flv.swf\" width=\"".YOUREPORTER_WIDTH."\" height=\"".YOUREPORTER_HEIGHT."\"allowscriptaccess=\"always\" allowfullscreen=\"true\" flashvars=\"config=http://www.youreporter.it/player/ext/config.xml.php?vkey=###URL###%26colors=youreporter%26size=normale\" menu=\"false\" />");

function youreporter_plugin_callback($match) {
	$output = YOUREPORTER_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function youreporter_plugin($content) {
	return preg_replace_callback(YOUREPORTER_REGEXP,
'youreporter_plugin_callback', $content);
}

add_filter('the_content', 'youreporter_plugin');
add_filter('the_content_rss', 'youreporter_plugin');
add_filter('comment_text', 'youreporter_plugin');
add_filter('the_excerpt', 'youreporter_plugin');

// Generic Flash Code by Francisco Monteagudo

define("GENERIC_FLASH_WIDTH", 425);
define("GENERIC_FLASH_HEIGHT", 350);
define("GENERIC_FLASH_REGEXP", "/\[flash ([[:print:]]+)\]/");
define("GENERIC_FLASH_TARGET", "<object type=\"application/x-shockwave-flash\"\n         data=\"###URL###\"\n         width=\"###WIDTH###\"\n         height=\"###HEIGHT###\"\n         wmode=\"transparent\"\n/>\n</object>\n");

function generic_flash_plugin_callback($match) {
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = GENERIC_FLASH_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", GENERIC_FLASH_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", GENERIC_FLASH_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", GENERIC_FLASH_WIDTH, $output);
		$output = str_replace("###HEIGHT###", GENERIC_FLASH_HEIGHT, $output);	
	}
	return ($output);
}

function generic_flash_plugin($content) {
	return preg_replace_callback(GENERIC_FLASH_REGEXP, 'generic_flash_plugin_callback', $content);
}

add_filter('the_content', 'generic_flash_plugin');
add_filter('the_content_rss', 'generic_flash_plugin');
add_filter('comment_text', 'generic_flash_plugin');
add_filter('the_excerpt', 'generic_flash_plugin');


// Funny or Die

define("FUNNYORDIE_WIDTH", 464); // default width
define("FUNNYORDIE_HEIGHT", 388); // default height
define("FUNNYORDIE_REGEXP", "/\[funnyordie ([[:print:]]+)\]/");
define("FUNNYORDIE_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT####\" classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\"><param name=\"movie\" value=\"http://www2.funnyordie.com/public/flash/fodplayer.swf?6045\" /><param name=\"flashvars\" value=\"key=###URL###\" /><param name=\"allowfullscreen\" value=\"true\" /><embed width=\"###WIDTH###\" height=\"###HEIGHT###\" flashvars=\"key=###URL###\" allowfullscreen=\"true\" quality=\"high\" src=\"http://www2.funnyordie.com/public/flash/fodplayer.swf?6045\" type=\"application/x-shockwave-flash\"></embed></object>");

function funnyordie_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = FUNNYORDIE_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", FUNNYORDIE_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", FUNNYORDIE_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", FUNNYORDIE_WIDTH, $output);
		$output = str_replace("###HEIGHT###", FUNNYORDIE_HEIGHT, $output);	
	}
	return ($output);
}
function funnyordie_plugin($content)
{
	return (preg_replace_callback(FUNNYORDIE_REGEXP, 'funnyordie_plugin_callback', $content));
}

add_filter('the_content', 'funnyordie_plugin');
add_filter('the_content_rss', 'funnyordie_plugin');
add_filter('comment_text', 'funnyordie_plugin');
add_filter('the_excerpt', 'funnyordie_plugin');

// Trilulilu

define("TRILULILU_WIDTH", 448); // default width
define("TRILULILU_HEIGHT", 386); // default height
define("TRILULILU_REGEXP", "/\[trilulilu ([[:print:]]+)\]/");
define("TRILULILU_TARGET", "<script type=\"text/javascript\" language=\"javascript\" src=\"http://www.trilulilu.ro/embed-video/keskifa/###URL###\"></script><script type=\"text/javascript\" language=\"javascript\">show_###URL###(###WIDTH###, ###HEIGHT###);</script>");

function trilulilu_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = TRILULILU_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", TRILULILU_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", TRILULILU_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", TRILULILU_WIDTH, $output);
		$output = str_replace("###HEIGHT###", TRILULILU_HEIGHT, $output);	
	}
	return ($output);
}
function trilulilu_plugin($content)
{
	return (preg_replace_callback(TRILULILU_REGEXP, 'trilulilu_plugin_callback', $content));
}

add_filter('the_content', 'trilulilu_plugin');
add_filter('the_content_rss', 'trilulilu_plugin');
add_filter('comment_text', 'trilulilu_plugin');
add_filter('the_excerpt', 'trilulilu_plugin');

// d1g.com

define("D1G_WIDTH", 400); // default width
define("D1G_HEIGHT", 300); // default height
define("D1G_REGEXP", "/\[d1g ([[:print:]]+)\]/");
define("D1G_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param value=\"#000000\" name=\"bgcolor\"><param name=\"movie\" value=\"http://www.d1g.com/swf/embedded_video_player.swf?id=2378&usefullscreen=false&file=http://www.d1g.com/video/play_video/###URL###&autostart=false&overstretch=false&repeat=false&shuffle=false\"></param><embed src=\"http://www.d1g.com/swf/embedded_video_player.swf?id=2378&file=http://www.d1g.com/video/play_video/###URL###&usefullscreen=false&autostart=false&overstretch=false&repeat=false&shuffle=false\" type=\"application/x-shockwave-flash\" width=\"###WIDTH###\" height=\"###HEIGHT###\" bgcolor=\"#000000\"></embed></object>");

function d1g_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = D1G_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", D1G_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", D1G_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", D1G_WIDTH, $output);
		$output = str_replace("###HEIGHT###", D1G_HEIGHT, $output);	
	}
	return ($output);
}
function d1g_plugin($content)
{
	return (preg_replace_callback(D1G_REGEXP, 'd1g_plugin_callback', $content));
}

add_filter('the_content', 'd1g_plugin');
add_filter('the_content_rss', 'd1g_plugin');
add_filter('comment_text', 'd1g_plugin');
add_filter('the_excerpt', 'd1g_plugin');

// ReelzChannel

define("REELZCHANNEL_WIDTH", 480); // default width
define("REELZCHANNEL_HEIGHT", 300); // default height
define("REELZCHANNEL_REGEXP", "/\[reelzchannel ([[:print:]]+)\]/");
define("REELZCHANNEL_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://cache.reelzchannel.com/assets/flash/syndicatedPlayer.swf\" /><param name=\"wmode\" value=\"transparent\" /><param name=\"allowScriptAccess\" value=\"always\"/><param name=\"flashvars\" value=\"clipid=###URL###\"><embed src=\"http://cache.reelzchannel.com/assets/flash/syndicatedPlayer.swf\" AllowScriptAccess=\"always\" width=\"###WIDTH###\" height=\"###HEIGHT###\" type=\"application/x-shockwave-flash\" mode=\"transparent\" flashvars=\"clipid=###URL###\"/></object>");

function reelzchannel_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = REELZCHANNEL_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", REELZCHANNEL_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", REELZCHANNEL_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", REELZCHANNEL_WIDTH, $output);
		$output = str_replace("###HEIGHT###", REELZCHANNEL_HEIGHT, $output);	
	}
	return ($output);
}
function reelzchannel_plugin($content)
{
	return (preg_replace_callback(REELZCHANNEL_REGEXP, 'reelzchannel_plugin_callback', $content));
}

add_filter('the_content', 'reelzchannel_plugin');
add_filter('the_content_rss', 'reelzchannel_plugin');
add_filter('comment_text', 'reelzchannel_plugin');
add_filter('the_excerpt', 'reelzchannel_plugin');

// MEGAVIDEO

define("MEGAVIDEO_WIDTH", 432); // default width
define("MEGAVIDEO_HEIGHT", 351); // default height
define("MEGAVIDEO_REGEXP", "/\[megavideo ([[:print:]]+)\]/");
define("MEGAVIDEO_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://www.megavideo.com/v/###URL###.3920544471.0\"></param><param name=\"wmode\" value=\"transparent\"></param><embed src=\"http://www.megavideo.com/v/###URL###.3920544471.0\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"###WIDTH###\" height=\"###HEIGHT###\"></embed></object>");

function megavideo_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = MEGAVIDEO_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", MEGAVIDEO_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", MEGAVIDEO_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", MEGAVIDEO_WIDTH, $output);
		$output = str_replace("###HEIGHT###", MEGAVIDEO_HEIGHT, $output);	
	}
	return ($output);
}
function megavideo_plugin($content)
{
	return (preg_replace_callback(MEGAVIDEO_REGEXP, 'megavideo_plugin_callback', $content));
}

add_filter('the_content', 'megavideo_plugin');
add_filter('the_content_rss', 'megavideo_plugin');
add_filter('comment_text', 'megavideo_plugin');
add_filter('the_excerpt', 'megavideo_plugin');

// MSN Video (soapbox)

define("MSN_WIDTH", 432); // default width
define("MSN_HEIGHT", 364); // default height
define("MSN_REGEXP", "/\[msn ([[:print:]]+)\]/");
define("MSN_TARGET", "<embed src=\"http://images.video.msn.com/flash/soapbox1_1.swf\" quality=\"high\" width=\"###WIDTH###\" height=\"###HEIGHT###\" base=\"http://images.video.msn.com\" type=\"application/x-shockwave-flash\" allowFullScreen=\"true\" allowScriptAccess=\"always\" pluginspage=\"http://macromedia.com/go/getflashplayer\" flashvars=\"c=v&v=###URL###\"></embed>");

function msn_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = MSN_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", MSN_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", MSN_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", MSN_WIDTH, $output);
		$output = str_replace("###HEIGHT###", MSN_HEIGHT, $output);	
	}
	return ($output);
}
function msn_plugin($content)
{
	return (preg_replace_callback(MSN_REGEXP, 'msn_plugin_callback', $content));
}

add_filter('the_content', 'msn_plugin');
add_filter('the_content_rss', 'msn_plugin');
add_filter('comment_text', 'msn_plugin');
add_filter('the_excerpt', 'msn_plugin');

// Youtube Playlist Code

define("YTPLAYLIST_WIDTH", 560); // default width
define("YTPLAYLIST_HEIGHT", 315); // default height
define("YTPLAYLIST_REGEXP", "/\[youtubeplaylist ([[:print:]]+)\]/");
define("YTPLAYLIST_TARGET", "<iframe width=\"###WIDTH###\" height=\"###HEIGHT###\" src=\"http://www.youtube.com/embed/videoseries?list=PL###URL###&wmode=transparent\" frameborder=\"0\" allowfullscreen></iframe>");

function ytplaylist_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = YTPLAYLIST_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", YTPLAYLIST_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", YTPLAYLIST_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", YTPLAYLIST_WIDTH, $output);
		$output = str_replace("###HEIGHT###", YTPLAYLIST_HEIGHT, $output);	
	}
	return ($output);
}
function ytplaylist_plugin($content)
{
	return (preg_replace_callback(YTPLAYLIST_REGEXP, 'ytplaylist_plugin_callback', $content));
}

add_filter('the_content', 'ytplaylist_plugin',1);
add_filter('the_content_rss', 'ytplaylist_plugin',1);
add_filter('comment_text', 'ytplaylist_plugin');
add_filter('the_excerpt', 'ytplaylist_plugin');

// mncast.com

define("MNCAST_WIDTH", 520); // default width
define("MNCAST_HEIGHT", 449); // default height
define("MNCAST_REGEXP", "/\[mncast ([[:print:]]+)\]/");
define("MNCAST_TARGET", "<embed pluginspage=\"http://www.macromedia.com/go/getflashplayer\" src=\"http://dory.mncast.com/mncHMovie.swf?movieID=###URL###&skinNum=1\" width=\"###WIDTH###\" height=\"###HEIGHT###\" type=\"application/x-shockwave-flash\"></embed>");

function mncast_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = MNCAST_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", MNCAST_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", MNCAST_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", MNCAST_WIDTH, $output);
		$output = str_replace("###HEIGHT###", MNCAST_HEIGHT, $output);	
	}
	return ($output);
}
function mncast_plugin($content)
{
	return (preg_replace_callback(MNCAST_REGEXP, 'mncast_plugin_callback', $content));
}

add_filter('the_content', 'mncast_plugin');
add_filter('the_content_rss', 'mncast_plugin');
add_filter('comment_text', 'mncast_plugin');
add_filter('the_excerpt', 'mncast_plugin');

// Hamburg1

define("HH_WIDTH", 400); // default width
define("HH_HEIGHT", 368); // default height
define("HH_REGEXP", "/\[hamburg1 ([[:print:]]+)\]/");
define("HH_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://www.hamburg1video.de/p/de/###URL###.html\" width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://www.hamburg1video.de/p/de/###URL###.html\" /><param name=\"wmode\" value=\"transparent\" /><embed src=\"http://www.hamburg1video.de/p/de/###URL###.html\" width=\"###WIDTH###\" height=\"###HEIGHT###\" wmode=\"transparent\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\"></embed></object>");

function hh_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = HH_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", HH_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", HH_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", HH_WIDTH, $output);
		$output = str_replace("###HEIGHT###", HH_HEIGHT, $output);	
	}
	return ($output);
}
function hh_plugin($content)
{
	return (preg_replace_callback(HH_REGEXP, 'hh_plugin_callback', $content));
}

add_filter('the_content', 'hh_plugin');
add_filter('the_content_rss', 'hh_plugin');
add_filter('comment_text', 'hh_plugin');
add_filter('the_excerpt', 'hh_plugin');

// Collegehumor Code

define("COLLEGEHUMOR_WIDTH", 480); // default width
define("COLLEGEHUMOR_HEIGHT", 360); // default height
define("COLLEGEHUMOR_REGEXP", "/\[collegehumor ([[:print:]]+)\]/");
define("COLLEGEHUMOR_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=###URL###&fullscreen=1\" width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"allowfullscreen\" value=\"true\" /><param name=\"movie\" quality=\"best\" value=\"http://www.collegehumor.com/moogaloop/moogaloop.swf?clip_id=###URL###&fullscreen=1\" /></object>");

function collegehumor_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = COLLEGEHUMOR_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", COLLEGEHUMOR_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", COLLEGEHUMOR_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", COLLEGEHUMOR_WIDTH, $output);
		$output = str_replace("###HEIGHT###", COLLEGEHUMOR_HEIGHT, $output);	
	}
	return ($output);
}
function collegehumor_plugin($content)
{
	return (preg_replace_callback(COLLEGEHUMOR_REGEXP, 'collegehumor_plugin_callback', $content));
}

add_filter('the_content', 'collegehumor_plugin');
add_filter('the_content_rss', 'collegehumor_plugin');
add_filter('comment_text', 'collegehumor_plugin');
add_filter('the_excerpt', 'collegehumor_plugin');

// Jumpcut Code

define("JUMPCUT_WIDTH", 408); // default width
define("JUMPCUT_HEIGHT", 324); // default height
define("JUMPCUT_REGEXP", "/\[jumpcut ([[:print:]]+)\]/");
define("JUMPCUT_TARGET", "<embed type=\"application/x-shockwave-flash\" src=\"http://jumpcut.com/media/flash/jump.swf?id=###URL###&asset_type=movie&asset_id=###URL###&eb=1\" width=\"###WIDTH###\" height=\"###HEIGHT###\"></embed>");

function jumpcut_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = JUMPCUT_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", JUMPCUT_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", JUMPCUT_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", JUMPCUT_WIDTH, $output);
		$output = str_replace("###HEIGHT###", JUMPCUT_HEIGHT, $output);	
	}
	return ($output);
}
function jumpcut_plugin($content)
{
	return (preg_replace_callback(JUMPCUT_REGEXP, 'jumpcut_plugin_callback', $content));
}

add_filter('the_content', 'jumpcut_plugin');
add_filter('the_content_rss', 'jumpcut_plugin');
add_filter('comment_text', 'jumpcut_plugin');
add_filter('the_excerpt', 'jumpcut_plugin');

// ComedyCentral

define("CC_WIDTH",  512);
define("CC_HEIGHT", 288);
define("CC_REGEXP", "/\[comedycentral ([[:print:]]+)\]/");
define("CC_TARGET", "<div style=\"background-color:#000000;width:520px;\">
<div style=\"padding:4px;\">
<embed src=\"http://media.mtvnservices.com/mgid:cms:video:thedailyshow.com:###URL###\" width=\"512\" height=\"288\" type=\"application/x-shockwave-flash\" allowFullScreen=\"true\" allowScriptAccess=\"always\" base=\".\" flashVars=\"\"></embed>
</div></div>");

function cc_plugin_callback($match) {
	$output = CC_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function cc_plugin($content) {
	return preg_replace_callback(CC_REGEXP, 'cc_plugin_callback', $content);
}

add_filter('the_content', 'cc_plugin');
add_filter('the_content_rss', 'cc_plugin');
add_filter('comment_text', 'cc_plugin');
add_filter('the_excerpt', 'cc_plugin');

// Reason.tv

define("REASON_REGEXP", "/\[reason ([[:print:]]+)\]/");
define("REASON_TARGET", "<script type=\"text/javascript\" src=\"http://www.reason.tv/embed/video.php?id=###ID###\"></script>");

function reason_plugin_callback($match) {
	$output = REASON_TARGET;
	$output = str_replace("###ID###", $match[1], $output);
	return ($output);
}

function reason_plugin($content) {
	return preg_replace_callback(REASON_REGEXP, 'reason_plugin_callback', $content);
}

add_filter('the_content', 'reason_plugin');
add_filter('the_content_rss', 'reason_plugin');
add_filter('comment_text', 'reason_plugin');
add_filter('the_excerpt', 'reason_plugin');

// SlideShare Slides

define("SS_WIDTH", 425);
define("SS_HEIGHT", 355);
define("SS_REGEXP", "/\[slideshare ([[:print:]]+)\]/");
define("SS_TARGET", "<object style=\"margin:0px\" width=\"".SS_WIDTH."\" height=\"".SS_HEIGHT."\"><param name=\"movie\" value=\"http://static.slidesharecdn.com/swf/ssplayer2.swf?doc=###ID###\" /><param name=\"allowFullScreen\" value=\"true\"/><param name=\"allowScriptAccess\" value=\"always\"/><param name=\"wmode\" value=\"transparent\" /><embed src=\"http://static.slidesharecdn.com/swf/ssplayer2.swf?doc=###ID###\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"".SS_WIDTH."\" height=\"".SS_HEIGHT."\" wmode=\"transparent\"></embed></object>");

function ss_plugin_callback($match){
   $output = SS_TARGET;
	$output = str_replace("###ID###", $match[1], $output);
	return ($output);
}

function ss_plugin($content){
	return (preg_replace_callback(SS_REGEXP, 'ss_plugin_callback', $content));
}

add_filter('the_content', 'ss_plugin');
add_filter('the_content_rss', 'ss_plugin');
add_filter('comment_text', 'ss_plugin');
add_filter('the_excerpt', 'ss_plugin');

// Teachertube.com code

define("TT_WIDTH", 425);
define("TT_HEIGHT", 350);
define("TT_REGEXP", "/\[teachertube ([[:print:]]+)\]/");
define("TT_TARGET", "<embed src=\"http://www.teachertube.com/skin-p/flvplayer.swf\" allowfullscreen=\"true\" flashvars=\"&file=http://www.teachertube.com/flvideo/###tt_mu###.flv&image=http://www.teachertube.com/thumb/###tt_mu###.jpg&location=http://www.teachertube.com/skin-p/flvplayer.swf&logo=http://www.teachertube.com/images/greylogo.swf&frontcolor=0xffffff&backcolor=0x000000&lightcolor=0xFF0000&autostart=false&volume=80&overstretch=fit\" quality=\"high\" bgcolor=\"#000000\" wmode=\"transparent\" width=\"".TT_WIDTH."\" height=\"".TT_HEIGHT."\" loop=\"false\" align=\"middle\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\"> </embed>");

function tt_plugin_callback_mu($match)
{
//      $match[1] = str_replace("&w=425", "", $match[1]);
	$output = TT_TARGET;
	$output = str_replace("###tt_mu###", $match[1], $output);
            $output = str_replace('"','',$output);
	return ($output);
}

function tt_plugin_mu($content)
{
	return (preg_replace_callback(TT_REGEXP, 'tt_plugin_callback_mu', $content));
}

add_filter('the_content', 'tt_plugin_mu');
add_filter('the_content_rss', 'tt_plugin_mu');
add_filter('comment_text', 'tt_plugin_mu');
add_filter('the_excerpt', 'tt_plugin_mu');

// vsocial Code

define("VSOCIAL_WIDTH", 400);
define("VSOCIAL_HEIGHT", 410);
define("VSOCIAL_REGEXP", "/\[vsocial ([[:print:]]+)\]/");
define("VSOCIAL_TARGET", "<object width=\"".VSOCIAL_WIDTH."\" height=\"".VSOCIAL_HEIGHT."\"><embed src=\"http://static.vsocial.com/flash/ups.swf?d=###URL###&a=0\" type=\"application/x-shockwave-flash\" width=\"".VSOCIAL_WIDTH."\" height=\"".VSOCIAL_HEIGHT."\"></embed></object>");

function vsocial_plugin_callback($match) {
	$output = VSOCIAL_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function vsocial_plugin($content) {
	return preg_replace_callback(VSOCIAL_REGEXP, 'vsocial_plugin_callback', $content);
}

add_filter('the_content', 'vsocial_plugin');
add_filter('the_content_rss', 'vsocial_plugin');
add_filter('comment_text', 'vsocial_plugin');
add_filter('the_excerpt', 'vsocial_plugin');

// last.fm Code

define("LASTFM_WIDTH", 340);
define("LASTFM_HEIGHT", 289);
define("LASTFM_REGEXP", "/\[lastfm ([[:print:]]+)\]/");
define("LASTFM_TARGET", "<object width=\"".LASTFM_WIDTH."\" height=\"".LASTFM_HEIGHT."\"><embed src=\"http://cdn.last.fm/videoplayer/33/VideoPlayer.swf?=###URL###\" type=\"application/x-shockwave-flash\" width=\"".LASTFM_WIDTH."\" height=\"".LASTFM_HEIGHT."\" wmode=\"transparent\"></embed></object>");

function lastfm_plugin_callback($match) {
	$output = LASTFM_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function lastfm_plugin($content) {
	return preg_replace_callback(LASTFM_REGEXP, 'lastfm_plugin_callback', $content);
}

add_filter('the_content', 'lastfm_plugin');
add_filter('the_content_rss', 'lastfm_plugin');
add_filter('comment_text', 'lastfm_plugin');
add_filter('the_excerpt', 'lastfm_plugin');

// Sumo.tv Code


define("SUMOTV_WIDTH", 400);
define("SUMOTV_HEIGHT", 329);
define("SUMOTV_REGEXP", "/\[sumotv ([[:print:]]+)\]/");
define("SUMOTV_TARGET", "<object width=\"".SUMOTV_WIDTH."\" height=\"".SUMOTV_HEIGHT."\"><embed src=\"http://www.sumo.tv/embed.swf?file=###URL###.flv&autostart=false\" type=\"application/x-shockwave-flash\" width=\"".SUMOTV_WIDTH."\" height=\"".SUMOTV_HEIGHT."\" wmode=\"transparent\"></embed></object>");

function sumotv_plugin_callback($match) {
	$output = SUMOTV_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function sumotv_plugin($content) {
	return preg_replace_callback(SUMOTV_REGEXP, 'sumotv_plugin_callback', $content);
}

add_filter('the_content', 'sumotv_plugin');
add_filter('the_content_rss', 'sumotv_plugin');
add_filter('comment_text', 'sumotv_plugin');
add_filter('the_excerpt', 'sumotv_plugin');

/// 123video.nl code

define("VIDEONL_WIDTH", 608);
define("VIDEONL_HEIGHT", 362);
define("VIDEONL_REGEXP", "/\[123videonl ([[:print:]]+)\]/");
define("VIDEONL_TARGET", "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0\" id=\"123movie_###URL###\" width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://www.123video.nl/123videoPlayer.swf?mediaSrc=###URL###\" /><param name=\"quality\" value=\"high\" /><param name=\"allowScriptAccess\" value=\"always\"/> <param name=\"allowFullScreen\" value=\"true\"></param><embed src=\"http://www.123video.nl/123videoPlayer.swf?mediaSrc=###URL###\" quality=\"high\" width=\"###WIDTH###\" height=\"###HEIGHT###\" allowfullscreen=\"true\" type=\"application/x-shockwave-flash\"  allowscriptaccess=\"always\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" /></object>");

function videonl_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = VIDEONL_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", VIDEONL_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", VIDEONL_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", VIDEONL_WIDTH, $output);
		$output = str_replace("###HEIGHT###", VIDEONL_HEIGHT, $output);	
	}
	return ($output);
}

function videonl_plugin($content)
{
        return (preg_replace_callback(VIDEONL_REGEXP, 'videonl_plugin_callback', $content));
}

add_filter('the_content', 'videonl_plugin');
add_filter('the_content_rss', 'videonl_plugin');
add_filter('comment_text', 'videonl_plugin');
add_filter('the_excerpt', 'videonl_plugin');

// Brightcove code

define("BRIGHTCOVE_WIDTH", 486);
define("BRIGHTCOVE_HEIGHT", 412); 
define("BRIGHTCOVE_REGEXP", "/\[brightcove ([[:print:]]+)\]/");
define("BRIGHTCOVE_TARGET", "<embed src=\"http://c.brightcove.com/services/viewer/federated_f9/10172910001?isVid=1\" bgcolor=\"#FFFFFF\" flashVars=\"videoId=###URL###&playerID=10172910001&domain=embed&\" base=\"http://admin.brightcove.com\" name=\"flashObj\" width=\"".BRIGHTCOVE_WIDTH."\" height=\"".BRIGHTCOVE_HEIGHT."\" seamlesstabbing=\"false\" type=\"application/x-shockwave-flash\" swLiveConnect=\"true\" swLiveConnect=\"true\" pluginspage=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\"></embed>");

function brightcove_plugin_callback($match)
{
        $output = BRIGHTCOVE_TARGET;
        $output = str_replace("###URL###", $match[1], $output);
        return ($output);
}

function brightcove_plugin($content)
{
        return (preg_replace_callback(BRIGHTCOVE_REGEXP, 'brightcove_plugin_callback', $content));
}

add_filter('the_content', 'brightcove_plugin');
add_filter('the_content_rss', 'brightcove_plugin');
add_filter('comment_text', 'brightcove_plugin');
add_filter('the_excerpt', 'brightcove_plugin');

// Aniboom code

define("ANIBOOM_WIDTH", 594);
define("ANIBOOM_HEIGHT", 334);
define("ANIBOOM_REGEXP", "/\[aniboom ([[:print:]]+)\]/");
define("ANIBOOM_TARGET", "<object width=\"".ANIBOOM_WIDTH."\" height=\"".ANIBOOM_HEIGHT."\"><param name=\"http://api.aniboom.com/e/###URL###\" /><param name=\"allowScriptAccess\" value=\"sameDomain\" /><param name=\"allowfullscreen\" value=\"true\" /><param name=\"quality\" value=\"high\" /><embed src=\"http://api.aniboom.com/e/###URL###\" quality=\"high\" width=\"".ANIBOOM_WIDTH."\" height=\"".ANIBOOM_HEIGHT."\" allowscriptaccess=\"sameDomain\" allowfullscreen=\"true\" type=\"application/x-shockwave-flash\"></embed></object>");

function aniboom_plugin_callback($match)
{
        $output = ANIBOOM_TARGET;
        $output = str_replace("###URL###", $match[1], $output);
        return ($output);
}

function aniboom_plugin($content)
{
        return (preg_replace_callback(ANIBOOM_REGEXP, 'aniboom_plugin_callback', $content));
}

add_filter('the_content', 'aniboom_plugin');
add_filter('the_rss', 'aniboom_plugin');
add_filter('comment_text', 'aniboom_plugin');
add_filter('the_excerpt', 'aniboom_plugin'); 

// Cellfish.com code

define("CELLFISH_WIDTH", 420);
define("CELLFISH_HEIGHT", 315);
define("CELLFISH_REGEXP", "/\[cellfish ([[:print:]]+)\]/");
define("CELLFISH_TARGET", "<object width=\"".CELLFISH_WIDTH."\" height=\"".CELLFISH_HEIGHT."\"><param name=\"vmode\" value=\"window\"></param><embed src=\"http://cellfish.com/static/swf/player8.swf?Id=###URL###\" type=\"application/x-shockwave-flash\" wmode=\"window\" width=\"".CELLFISH_WIDTH."\" height=\"".CELLFISH_HEIGHT."\"></embed></object>");

function cellfish_plugin_callback($match)
{
        $output = CELLFISH_TARGET;
        $output = str_replace("###URL###", $match[1], $output);
        return ($output);
}

function cellfish_plugin($content)
{
        return (preg_replace_callback(CELLFISH_REGEXP, 'cellfish_plugin_callback', $content));
}

add_filter('the_content', 'cellfish_plugin');
add_filter('the_content_rss', 'cellfish_plugin');
add_filter('comment_text', 'cellfish_plugin');
add_filter('the_excerpt', 'cellfish_plugin'); 

// Tu.tv code

define("TUTV_WIDTH", 425);
define("TUTV_HEIGHT", 350);
define("TUTV_REGEXP", "/\[tutv ([[:print:]]+)\]/");
define("TUTV_TARGET", "<object width=\"".TUTV_WIDTH."\" height=\"".TUTV_HEIGHT."\"><param name=\"vmode\" value=\"transparent\"></param><embed src=\"http://www.tu.tv/tutvweb.swf?kpt=###URL###\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"".TUTV_WIDTH."\" height=\"".TUTV_HEIGHT."\"></embed></object>");

function tutv_plugin_callback($match)
{
        $output = TUTV_TARGET;
        $output = str_replace("###URL###", $match[1], $output);
        return ($output);
}

function tutv_plugin($content)
{
        return (preg_replace_callback(TUTV_REGEXP, 'tutv_plugin_callback', $content));
}

add_filter('the_content', 'tutv_plugin');
add_filter('the_content_rss', 'tutv_plugin');
add_filter('comment_text', 'tutv_plugin');
add_filter('the_excerpt', 'tutv_plugin'); 

// Yahoo! Video code

define("YAHOO_WIDTH", 512);
define("YAHOO_HEIGHT", 322);
define("YAHOO_REGEXP", "/\[yahoo ([[:print:]]+)\]/");
define("YAHOO_TARGET", "<object width=\"###WIDTH###\" height=\"###HEIGHT###\"><param name=\"movie\" value=\"http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.30\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"AllowScriptAccess\" VALUE=\"always\" /><param name=\"bgcolor\" value=\"#000000\" /><param name=\"flashVars\" value=\"id=###URL###&embed=1\" /><embed src=\"http://d.yimg.com/static.video.yahoo.com/yep/YV_YEP.swf?ver=2.2.30\" type=\"application/x-shockwave-flash\" width=\"###WIDTH###\" height=\"###HEIGHT###\" allowFullScreen=\"true\" AllowScriptAccess=\"always\" bgcolor=\"#000000\" flashVars=\"id=###URL###&embed=1\" ></embed></object>");

function yahoo_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = YAHOO_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", YAHOO_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", YAHOO_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", YAHOO_WIDTH, $output);
		$output = str_replace("###HEIGHT###", YAHOO_HEIGHT, $output);	
	}
	return ($output);
}

function yahoo_plugin($content)
{
        return (preg_replace_callback(YAHOO_REGEXP, 'yahoo_plugin_callback', $content));
}

add_filter('the_content', 'yahoo_plugin');
add_filter('the_content_rss', 'yahoo_plugin');
add_filter('comment_text', 'yahoo_plugin');
add_filter('the_excerpt', 'yahoo_plugin');        

// MyspaceTV code

define("MYSPACETV_WIDTH", 425);
define("MYSPACETV_HEIGHT", 360);
define("MYSPACETV_REGEXP", "/\[myspacetv ([[:print:]]+)\]/");
define("MYSPACETV_TARGET", "<object width=\"".MYSPACETV_WIDTH."\" height=\"".MYSPACETV_HEIGHT."\"><param name=\"allowFullScreen\" value=\"true\"/><param name=\"vmode\" value=\"transparent\"></param><param name=\"movie\" value=\"http://mediaservices.myspace.com/services/media/embed.aspx/m=###URL###,t=1,mt=video\"/><embed src=\"http://mediaservices.myspace.com/services/media/embed.aspx/m=###URL####,t=1,mt=video\" width=\"".MYSPACETV_WIDTH."\" height=\"".MYSPACETV_HEIGHT."\" allowFullScreen=\"true\" type=\"application/x-shockwave-flash\" wmode=\"transparent\"></embed></object>");

function myspacetv_plugin_callback($match)
{
        $output = MYSPACETV_TARGET;
        $output = str_replace("###URL###", $match[1], $output);
        return ($output);
}

function myspacetv_plugin($content)
{
        return (preg_replace_callback(MYSPACETV_REGEXP, 'myspacetv_plugin_callback', $content));
}

add_filter('the_content', 'myspacetv_plugin');
add_filter('the_content_rss', 'myspacetv_plugin');
add_filter('comment_text', 'myspacetv_plugin');
add_filter('the_excerpt', 'myspacetv_plugin');       

// Veoh code

define("VEOH_WIDTH", 410);
define("VEOH_HEIGHT", 341);
define("VEOH_REGEXP", "/\[veoh ([[:print:]]+)\]/");
define("VEOH_TARGET", "<embed src=\"http://www.veoh.com/veohplayer.swf?permalinkId=###URL###&id=anonymous&player=videodetailsembedded&videoAutoPlay=0\" allowFullScreen=\"true\" width=\"".VEOH_WIDTH."\" height=\"".VEOH_HEIGHT."\" bgcolor=\"#FFFFFF\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\"></embed>");

function veoh_plugin_callback($match)
{
        $output = VEOH_TARGET;
        $output = str_replace("###URL###", $match[1], $output);
        return ($output);
}

function veoh_plugin($content)
{
        return (preg_replace_callback(VEOH_REGEXP, 'veoh_plugin_callback', $content));
}

add_filter('the_content', 'veoh_plugin');
add_filter('the_content_rss', 'veoh_plugin');
add_filter('comment_text', 'veoh_plugin');
add_filter('the_excerpt', 'veoh_plugin');

// Wandeo Video Code

define("WANDEO_WIDTH", 352);
define("WANDEO_HEIGHT", 308);
define("WANDEO_REGEXP", "/\[wandeo ([[:print:]]+)\]/");
define("WANDEO_TARGET", "<object width=\"".WANDEO_WIDTH."\" height=\"".WANDEO_HEIGHT."\"><param name=\"vmode\" value=\"transparent\"></param><embed src=\"http://www.wandeo.com/v/###URL###\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"".WANDEO_WIDTH."\" height=\"".WANDEO_HEIGHT."\"></embed></object>");

function wandeo_plugin_callback($match) {
       $output = WANDEO_TARGET;
       $output = str_replace("###URL###", $match[1], $output);
       return ($output);
}

function wandeo_plugin($content) {
      return preg_replace_callback(WANDEO_REGEXP, 'wandeo_plugin_callback', $content);
}

add_filter('the_content', 'wandeo_plugin');
add_filter('the_content_rss', 'wandeo_plugin');
add_filter('comment_text', 'wandeo_plugin');
add_filter('the_excerpt', 'wandeo_plugin');

// glumbert code

define("GLUMBERT_WIDTH", 448);
define("GLUMBERT_HEIGHT", 336);
define("GLUMBERT_REGEXP", "/\[glumbert ([[:print:]]+)\]/");
define("GLUMBERT_TARGET", "<object width=\"".GLUMBERT_WIDTH."\" height=\"".GLUMBERT_HEIGHT."\"><param name=\"vmode\" value=\"transparent\"></param><embed src=\"http://www.glumbert.com/embed/###URL###\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"".GLUMBERT_WIDTH."\" height=\"".GLUMBERT_HEIGHT."\"></embed></object>");

function glumbert_plugin_callback($match)
{
        $output = GLUMBERT_TARGET;
        $output = str_replace("###URL###", $match[1], $output);
        return ($output);
}

function glumbert_plugin($content)
{
        return (preg_replace_callback(GLUMBERT_REGEXP,
'glumbert_plugin_callback', $content));
}

add_filter('the_content', 'glumbert_plugin');
add_filter('the_content_rss', 'glumbert_plugin');
add_filter('comment_text', 'glumbert_plugin');
add_filter('the_excerpt', 'glumbert_plugin');

// GameVideos Code

define("GameVideos_WIDTH", 425);
define("GameVideos_HEIGHT", 350);
define("GameVideos_REGEXP", "/\[gamevideos ([[:print:]]+)\]/");
define("GameVideos_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://www.gamevideos.com:80/swf/gamevideos11.swf?embedded=1&fullscreen=1&autoplay=0&src=http://www.gamevideos.com:80/video/videoListXML%3Fid%3D###URL###%26ordinal%3D1184588561564%26adPlay%3Dfalse\" quality=\"high\" play=\"true\" loop=\"true\" scale=\"showall\" wmode=\"window\" devicefont=\"false\" id=\"gamevideos6\" bgcolor=\"#FFFFFF\" name=\"gamevideos6\" menu=\"true\" allowscriptaccess=\"sameDomain\" allowFullScreen=\"true\" align=\"middle\" height=\"".GameVideos_HEIGHT."\" width=\"".GameVideos_WIDTH."\"/></object>");

function gamevideos_plugin_callback($match) {
	$output = GameVideos_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function gamevideos_plugin($content) {
	return preg_replace_callback(GameVideos_REGEXP, 'gamevideos_plugin_callback', $content);
}

add_filter('the_content', 'gamevideos_plugin');
add_filter('the_content_rss', 'gamevideos_plugin');
add_filter('comment_text', 'gamevideos_plugin');
add_filter('the_excerpt', 'gamevideos_plugin');

// GameTrailers Code

define("GameTrailers_WIDTH", 480);
define("GameTrailers_HEIGHT", 409);
define("GameTrailers_REGEXP", "/\[gametrailers ([[:print:]]+)\]/");
define("GameTrailers_TARGET", "<object id=\"gtembed\" width=\"".GameTrailers_WIDTH."\" height=\"".GameTrailers_HEIGHT."\">
<param name=\"movie\" value=\"http://www.gametrailers.com/remote_wrap.php?mid=###URL###\"/>
<param name=\"quality\" value=\"high\" /> 
<embed src=\"http://www.gametrailers.com/remote_wrap.php?mid=###URL###\" name=\"gtembed\" align=\"middle\" allowScriptAccess=\"sameDomain\" quality=\"high\"  type=\"application/x-shockwave-flash\" width=\"".GameTrailers_WIDTH."\" height=\"".GameTrailers_HEIGHT."\"></embed> </object>");


function gametrailers_plugin_callback($match) {
	$output = GameTrailers_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function gametrailers_plugin($content) {
	return preg_replace_callback(GameTrailers_REGEXP, 'gametrailers_plugin_callback', $content);
}

add_filter('the_content', 'gametrailers_plugin');
add_filter('the_content_rss', 'gametrailers_plugin');
add_filter('comment_text', 'gametrailers_plugin');
add_filter('the_excerpt', 'gametrailers_plugin');

// IFILM Code

define("IFILM_WIDTH", 448);
define("IFILM_HEIGHT", 365);
define("IFILM_REGEXP", "/\[ifilm ([[:print:]]+)\]/");
define("IFILM_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://www.ifilm.com/efp?flvbaseclip=###URL###\" width=\"".IFILM_WIDTH."\" height=\"".IFILM_HEIGHT."\" wmode=\"transparent\"><param name=\"movie\" value=\"http://www.ifilm.com/efp?flvbaseclip=###URL###\" /></object>");

function ifilm_plugin_callback($match) {
	$output = IFILM_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function ifilm_plugin($content) {
	return preg_replace_callback(IFILM_REGEXP, 'ifilm_plugin_callback', $content);
}

add_filter('the_content', 'ifilm_plugin');
add_filter('the_content_rss', 'ifilm_plugin');
add_filter('comment_text', 'ifilm_plugin');
add_filter('the_excerpt', 'ifilm_plugin');

//LiveLeak Code

define("LIVELEAK_WIDTH", 450);
define("LIVELEAK_HEIGHT", 370);
define("LIVELEAK_REGEXP", "/\[liveleak ([[:print:]]+)\]/");
define("LIVELEAK_TARGET", "<object width=\"".LIVELEAK_WIDTH."\" height=\"".LIVELEAK_HEIGHT."\"><param name=\"movie\" value=\"http://www.liveleak.com/e/###URL###\"></param><param name=\"wmode\" value=\"transparent\"></param><embed src=\"http://www.liveleak.com/e/###URL###\" type=\"application/x-shockwave-flash\" wmode=\"transparent\" width=\"".LIVELEAK_WIDTH."\" height=\"".LIVELEAK_HEIGHT."\"></embed></object>");


function liveleak_plugin_callback($match)
{
   $output = LIVELEAK_TARGET;
   $output = str_replace("###URL###", $match[1], $output);
   return ($output);
}

function liveleak_plugin($content)
{
   return (preg_replace_callback(LIVELEAK_REGEXP, 'liveleak_plugin_callback', $content));
}

add_filter('the_content', 'liveleak_plugin');
add_filter('the_content_rss', 'liveleak_plugin');
add_filter('comment_text', 'liveleak_plugin'); 
add_filter('the_excerpt', 'liveleak_plugin');

// Grouper Code

define("GROUPER_WIDTH", 400);
define("GROUPER_HEIGHT", 325);
define("GROUPER_REGEXP", "/\[grouper ([[:print:]]+)\]/");
define("GROUPER_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://grouper.com/mtg/mtgPlayer.swf?v=1.7ap=0&rf=-1&vfver=8&extid=-1&extsite=-1&id=###URL###\" width=\"".GROUPER_WIDTH."\" height=\"".GROUPER_HEIGHT."\" wmode=\"transparent\"><param name=\"movie\" value=\"http://grouper.com/mtg/mtgPlayer.swf?v=1.7ap=0&rf=-1&vfver=8&extid=-1&extsite=-1&id=###URL###\" /></object>");

function grouper_plugin_callback($match)
{
	$output = GROUPER_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function grouper_plugin($content)
{
	return (preg_replace_callback(GROUPER_REGEXP, 'grouper_plugin_callback', $content));
}

add_filter('the_content', 'grouper_plugin');
add_filter('the_content_rss', 'grouper_plugin');
add_filter('comment_text', 'grouper_plugin');
add_filter('the_excerpt', 'grouper_plugin');

// UnCut Code

define("UNCUT_WIDTH", 425);
define("UNCUT_HEIGHT", 350);
define("UNCUT_REGEXP", "/\[uncut ([[:print:]]+)\]/");
define("UNCUT_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://uncutvideo.aol.com/en-US/uc_videoplayer.swf?aID=1###URL###\" width=\"".UNCUT_WIDTH."\" height=\"".UNCUT_HEIGHT."\" wmode=\"transparent\"><param name=\"movie\" value=\"http://uncutvideo.aol.com/en-US/uc_videoplayer.swf?aID=1###URL###\" /></object>");

function uncut_plugin_callback($match)
{
	$output = UNCUT_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function uncut_plugin($content)
{
	return (preg_replace_callback(UNCUT_REGEXP, 'uncut_plugin_callback', $content));
}

add_filter('the_content', 'uncut_plugin');
add_filter('the_content_rss', 'uncut_plugin');
add_filter('comment_text', 'uncut_plugin');
add_filter('the_excerpt', 'uncut_plugin');

// Revver Code

define("REVVER_WIDTH", 480);
define("REVVER_HEIGHT", 392);
define("REVVER_REGEXP", "/\[revver ([[:print:]]+)\]/");
define("REVVER_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://flash.revver.com/player/1.0/player.swf?mediaId=###URL###\" width=\"".REVVER_WIDTH."\" height=\"".REVVER_HEIGHT."\" wmode=\"transparent\"><param name=\"movie\" value=\"http://flash.revver.com/player/1.0/player.swf?mediaId=###URL###\" /></object>");

function revver_plugin_callback($match)
{
	$output = REVVER_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function revver_plugin($content)
{
	return (preg_replace_callback(REVVER_REGEXP, 'revver_plugin_callback', $content));
}

add_filter('the_content', 'revver_plugin');
add_filter('the_content_rss', 'revver_plugin');
add_filter('comment_text', 'revver_plugin');
add_filter('the_excerpt', 'revver_plugin');

// blip.tv Code

define("BLIPTV_WIDTH", 480); 
define("BLIPTV_HEIGHT", 299); 
define("BLIPTV_REGEXP", "/\[bliptv ([[:print:]]+)\]/");
define("BLIPTV_TARGET", "<iframe src=\"http://blip.tv/play/###URL###.html?p=1\" width=\"###WIDTH###\" height=\"###HEIGHT###\" frameborder=\"0\" allowfullscreen></iframe><embed type=\"application/x-shockwave-flash\" src=\"http://a.blip.tv/api.swf####URL###\" style=\"display:none\"></embed>");

function bliptv_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = BLIPTV_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", BLIPTV_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", BLIPTV_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", BLIPTV_WIDTH, $output);
		$output = str_replace("###HEIGHT###", BLIPTV_HEIGHT, $output);	
	}
	return ($output);
}
function bliptv_plugin($content)
{
	return (preg_replace_callback(BLIPTV_REGEXP, 'bliptv_plugin_callback', $content));
}

add_filter('the_content', 'bliptv_plugin');
add_filter('the_content_feed', 'bliptv_plugin');
add_filter('comment_text', 'bliptv_plugin');
add_filter('the_excerpt', 'bliptv_plugin');


// Videotube Code

define("VIDEOTUBE_WIDTH", 480);
define("VIDEOTUBE_HEIGHT", 400);
define("VIDEOTUBE_REGEXP", "/\[videotube ([[:print:]]+)\]/");
define("VIDEOTUBE_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://www.videotube.de/ci/flash/videotube_player_4.swf?videoId=###URL###&svsf=0&lang=german&host=www.videotube.de\" width=\"".VIDEOTUBE_WIDTH."\" height=\"".VIDEOTUBE_HEIGHT."\" wmode=\"transparent\"><param name=\"movie\" value=\"http://www.videotube.de/ci/flash/videotube_player_4.swf?videoId=###URL###&svsf=0&lang=german&host=www.videotube.de\" /></object>");

function videotube_plugin_callback($match) {
	$output = VIDEOTUBE_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function videotube_plugin($content) {
	return preg_replace_callback(VIDEOTUBE_REGEXP, 'videotube_plugin_callback', $content);
}

add_filter('the_content', 'videotube_plugin');
add_filter('the_content_rss', 'videotube_plugin');
add_filter('comment_text', 'videotube_plugin');
add_filter('the_excerpt', 'videotube_plugin');

// Vimeo Code
 
define("VIMEO_WIDTH", 400); // default width
define("VIMEO_HEIGHT", 225); // default height
define("VIMEO_REGEXP", "/\[vimeo ([[:print:]]+)\]/");
define("VIMEO_TARGET", "<iframe src=\"http://player.vimeo.com/video/###URL###\" width=\"###WIDTH###\" height=\"###HEIGHT###\" frameborder=\"0\" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>");

function vimeo_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = VIMEO_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", VIMEO_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", VIMEO_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", VIMEO_WIDTH, $output);
		$output = str_replace("###HEIGHT###", VIMEO_HEIGHT, $output);	
	}
	return ($output);
}
function vimeo_plugin($content)
{
	return (preg_replace_callback(VIMEO_REGEXP, 'vimeo_plugin_callback', $content));
}

add_filter('the_content', 'vimeo_plugin');
add_filter('the_content_rss', 'vimeo_plugin');
add_filter('comment_text', 'vimeo_plugin');
add_filter('the_excerpt', 'vimeo_plugin');

// Metacafe Code
define("METACAFE_WIDTH", 400);
define("METACAFE_HEIGHT", 345);
define("METACAFE_REGEXP", "/\[metacafe ([[:print:]]+)\]/");
define("METACAFE_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://www.metacafe.com/fplayer/###URL###/.swf\" width=\"".METACAFE_WIDTH."\" height=\"".METACAFE_HEIGHT."\" wmode=\"transparent\"><param name=\"movie\" value=\"http://www.metacafe.com/fplayer/###URL###/.swf\" /></object>");

function metacafe_plugin_callback($match) {
	$output = METACAFE_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function metacafe_plugin($content) {
	return preg_replace_callback(METACAFE_REGEXP, 'metacafe_plugin_callback', $content);
}

add_filter('the_content', 'metacafe_plugin');
add_filter('the_content_rss', 'metacafe_plugin');
add_filter('comment_text', 'metacafe_plugin');
add_filter('the_excerpt', 'metacafe_plugin');

// Break.com Codes

define("BREAK_WIDTH", 425);
define("BREAK_HEIGHT", 350);
define("BREAK_REGEXP", "/\[break ([[:print:]]+)\]/");
define("BREAK_TARGET", "<object type=\"application/x-shockwave-flash\" data=\"http://embed.break.com/###URL###\" width=\"".BREAK_WIDTH."\" height=\"".BREAK_HEIGHT."\" wmode=\"transparent\"><param name=\"movie\" value=\"http://embed.break.com/###URL###\" /></object>");

function break_plugin_callback($match) {
	$output = BREAK_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function break_plugin($content) {
	return preg_replace_callback(BREAK_REGEXP, 'break_plugin_callback', $content);
}

add_filter('the_content', 'break_plugin');
add_filter('the_content_rss', 'break_plugin');
add_filter('comment_text', 'break_plugin');
add_filter('the_excerpt', 'break_plugin');

// MyVideo Code

define("MYVIDEO_WIDTH", 470);
define("MYVIDEO_HEIGHT", 406);
define("MYVIDEO_REGEXP", "/\[myvideo ([[:print:]]+)\]/");
define("MYVIDEO_TARGET", "<iframe src='http://www.myvideo.de/embed/###URL###' style='width:###WIDTH###px;height:###HEIGHT###px;border:0px none;padding:0;margin:0;' width='###WIDTH###' height='###HEIGHT###' frameborder='0' scrolling='no'></iframe>");

function myvideo_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = MYVIDEO_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", MYVIDEO_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", MYVIDEO_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", MYVIDEO_WIDTH, $output);
		$output = str_replace("###HEIGHT###", MYVIDEO_HEIGHT, $output);	
	}
	return ($output);
}
function myvideo_plugin($content)
{
	return (preg_replace_callback(MYVIDEO_REGEXP, 'myvideo_plugin_callback', $content));
}

add_filter('the_content', 'myvideo_plugin');
add_filter('the_content_rss', 'myvideo_plugin');
add_filter('comment_text', 'myvideo_plugin');
add_filter('the_excerpt', 'myvideo_plugin');

// Dailymotion Code

define("DAILYMOTION_WIDTH", 420);
define("DAILYMOTION_HEIGHT", 336);
define("DAILYMOTION_REGEXP", "/\[dailymotion[:\s]([[:print:]]+)\]/");
define("DAILYMOTION_TARGET", "<iframe frameborder=\"0\" width=\"###WIDTH###\" height=\"###HEIGHT###\" src=\"http://www.dailymotion.com/embed/video/###URL###\"></iframe>");

function dailymotion_plugin_callback($match) {
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$replacements = array(
		"###URL###" => preg_match('!/video/(.*?)(_|\s|$)!', $tag_parts[1], $m) ? $m[1] : $tag_parts[1],
		"###WIDTH###" => isset($tag_parts[2]) && $tag_parts[2] ? $tag_parts[2] : DAILYMOTION_WIDTH,
		"###HEIGHT###" => isset($tag_parts[3]) && $tag_parts[3] ? $tag_parts[3] : DAILYMOTION_HEIGHT,
	);
	return str_replace(array_keys($replacements), array_values($replacements), DAILYMOTION_TARGET);
}

function dailymotion_plugin($content) {
	return preg_replace_callback(DAILYMOTION_REGEXP, 'dailymotion_plugin_callback', $content);
}

add_filter('the_content', 'dailymotion_plugin');
add_filter('the_content_rss', 'dailymotion_plugin');
add_filter('comment_text', 'dailymotion_plugin');
add_filter('the_excerpt', 'dailymotion_plugin');

// Sevenload Code

define("SEVENLOAD_WIDTH", 500);
define("SEVENLOAD_HEIGHT", 281);
define("SEVENLOAD_REGEXP", "/\[sevenload ([[:print:]]+)\]/");
define("SEVENLOAD_TARGET", "<iframe src='http://embed.sevenload.com/widgets/singlePlayer/###URL###/?autoplay=false&env=slcom-ext' style='width:###WIDTH###px;height:###HEIGHT###px;overflow:hidden;border:0 solid #000;' webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>");

function sevenload_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = SEVENLOAD_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", SEVENLOAD_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", SEVENLOAD_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", SEVENLOAD_WIDTH, $output);
		$output = str_replace("###HEIGHT###", SEVENLOAD_HEIGHT, $output);	
	}
	return ($output);
}

function sevenload_plugin($content) {
	return preg_replace_callback(SEVENLOAD_REGEXP, 'sevenload_plugin_callback', $content);
}

add_filter('the_content', 'sevenload_plugin');
add_filter('the_content_rss', 'sevenload_plugin');
add_filter('comment_text', 'sevenload_plugin');
add_filter('the_excerpt', 'sevenload_plugin');

// Clipfish Code

define("CLIPFISH_WIDTH", 464);
define("CLIPFISH_HEIGHT", 384);
define("CLIPFISH_REGEXP", "/\[clipfish ([[:print:]]+)\]/");
define("CLIPFISH_TARGET", "<object codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0\" width=\"###WIDTH###\" height=\"###HEIGHT###\" > <param name=\"allowScriptAccess\" value=\"always\" /> <param name=\"movie\" value=\"http://www.clipfish.de/cfng/flash/clipfish_player_3.swf?as=0&videoid=###URL###&r=1&area=e&c=990000\" /> <param name=\"bgcolor\" value=\"#ffffff\" /> <param name=\"allowFullScreen\" value=\"true\" /> <embed src=\"http://www.clipfish.de/cfng/flash/clipfish_player_3.swf?as=0&vid=###URL###&r=1&area=e&c=990000\" quality=\"high\" bgcolor=\"#990000\" width=\"###WIDTH###\" height=\"###HEIGHT###\" name=\"player\" align=\"middle\" allowFullScreen=\"true\" allowScriptAccess=\"always\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\"></embed></object>");


function clipfish_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = CLIPFISH_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", CLIPFISH_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", CLIPFISH_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", CLIPFISH_WIDTH, $output);
		$output = str_replace("###HEIGHT###", CLIPFISH_HEIGHT, $output);	
	}
	return ($output);
}
function clipfish_plugin($content)
{
	return (preg_replace_callback(CLIPFISH_REGEXP, 'clipfish_plugin_callback', $content));
}

add_filter('the_content', 'clipfish_plugin');
add_filter('the_content_rss', 'clipfish_plugin');
add_filter('comment_text', 'clipfish_plugin');
add_filter('the_excerpt', 'clipfish_plugin');

// GoogleVideo Code

define("GOOGLE_WIDTH", 400);
define("GOOGLE_HEIGHT", 326);
define("GOOGLE_REGEXP", "/\[google ([[:print:]]+)\]/");
define("GOOGLE_TARGET", "<embed id=\"VideoPlayback\" src=\"http://video.google.de/googleplayer.swf?docid=###URL###&hl=de&fs=true\" style=\"width:".GOOGLE_WIDTH."px;height:".GOOGLE_HEIGHT."px\" allowFullScreen=\"true\" allowScriptAccess=\"always\" type=\"application/x-shockwave-flash\"> </embed>");

function google_plugin_callback($match) {
	$output = GOOGLE_TARGET;
	$output = str_replace("###URL###", $match[1], $output);
	return ($output);
}

function google_plugin($content) {
	return preg_replace_callback(GOOGLE_REGEXP, 'google_plugin_callback', $content);
}

add_filter('the_content', 'google_plugin');
add_filter('the_content_rss', 'google_plugin');
add_filter('comment_text', 'google_plugin');
add_filter('the_excerpt', 'google_plugin');


// Youtube Code

define("YOUTUBE_WIDTH", 425); // default width
define("YOUTUBE_HEIGHT", 344); // default height
define("YOUTUBE_REGEXP", "/\[youtube ([[:print:]]+)\]/");
define("YOUTUBE_TARGET", "<iframe width=\"###WIDTH###\" height=\"###HEIGHT###\" src=\"http://www.youtube.com/embed/###URL###?wmode=transparent\" frameborder=\"0\" allowfullscreen> </iframe>");
																																																																																												
function youtube_plugin_callback($match)
{
	$tag_parts = explode(" ", rtrim($match[0], "]"));
	$output = YOUTUBE_TARGET;
	$output = str_replace("###URL###", $tag_parts[1], $output);
	if (count($tag_parts) > 2) {
		if ($tag_parts[2] == 0) {
			$output = str_replace("###WIDTH###", YOUTUBE_WIDTH, $output);
		} else {
			$output = str_replace("###WIDTH###", $tag_parts[2], $output);
		}
		if ($tag_parts[3] == 0) {
			$output = str_replace("###HEIGHT###", YOUTUBE_HEIGHT, $output);
		} else {
			$output = str_replace("###HEIGHT###", $tag_parts[3], $output);
		}
	} else {
		$output = str_replace("###WIDTH###", YOUTUBE_WIDTH, $output);
		$output = str_replace("###HEIGHT###", YOUTUBE_HEIGHT, $output);	
	}
	return ($output);
}
function youtube_plugin($content)
{
	return (preg_replace_callback(YOUTUBE_REGEXP, 'youtube_plugin_callback', $content));
}

add_filter('the_content', 'youtube_plugin',1);
add_filter('the_content_feed', 'youtube_plugin');
add_filter('comment_text', 'youtube_plugin');
add_filter('the_excerpt', 'youtube_plugin');
add_filter('widget_text', 'youtube_plugin');

load_plugin_textdomain('ow_wvp', false, basename( dirname( __FILE__ ) ) . '/lang' );

function ow_wvp_option_page() {
?>
 
  <div class="wrap">
    <h2><?php _e('Wordpress Video Plugin Options', ow_wvp); ?></h2>
    <p>
		<?php _e('Thank you for using Wordpress Video Plugin!', ow_wvp); ?> 
    </p>
    <p><?php _e('This is a first implementation of an option page to configurate the Wordpress Video Plugin. Until now you can not configure anything from here. But in the future
    it is planed to configure size and other features.', ow_wvp); ?></p>
    <h2><?php _e('Usage', ow_wvp); ?></h2>
    <p><?php _e('Every occurence of the expression [site id] (case unsensitive) will start as an embedded flash player. Replace \'site\' with the name of the videosite and \'id\' with the video id. Visit this page for supported video sites: <a href="http://daburna.de/dokuwiki/doku.php/instruction">Instructions</a>', ow_wvp); ?></p>
 	<div style="width:300px;">
	<strong><?php _e('Donate', ow_wvp); ?></strong>
	<p><?php _e('If you like Wordpress Video Plugin, you can support its development by a donation:', ow_wvp); ?></p>
	<div style="text-align:center;">
<script type="text/javascript">
	var flattr_url = 'http://www.daburna.de/blog/2006/12/13/wordpress-video-plugin/';
</script>
<script src="http://api.flattr.com/button/load.js" type="text/javascript"></script>
	</div>
	<div style="text-align:center;">Paypal
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="YFLULRR6CR99G">
<input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online.">
<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/de_DE/i/scr/pixel.gif" width="1" height="1">
</form>
	</div>
	<div>
		<a href="http://www.amazon.de/wishlist/2X4I7LCHMRFWI/"><?php _e('My Amazon.de wishlist (German)', ow_wvp); ?></a>
	</div>
	</div>   
  </div>


<?php
} 


function ow_wvp_add_menu() {

  add_options_page('Wordpress-Video-Plugin', 'WP Video Plugin', 9, __FILE__, 'ow_wvp_option_page'); 
}
 

add_action('admin_menu', 'ow_wvp_add_menu'); 
?>