=== Wordpress Video Plugin ===
Contributors: daburna
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=YFLULRR6CR99G
Tags: embedding, Video, embed, portal, Youtube, Sevenload, Google, MyVideo, Clipfish, Revver, Yahoo!, Brightcove, Aniboom, MSN, Liveleak, Collegehumor, slideshare, guba, GoalVideoz  
Requires at least: 3.0.0
Tested up to: 3.5
Stable tag: 0.759

A filter for WordPress that displays videos from many video services.

== Description ==

The Wordpress Video Plugin adds a filter for WordPress that allows easy video embedding of 66 supported video sites. 

The following sites are supported:

* 123video.nl
* Aniboom
* Archive.org
* Blip.tv
* Break
* Brightcove
* CBS
* Cellfish
* Clipfish
* Clipsyndicate
* Collegehumor
* ComedyCentral.com
* current.com
* D1G.com
* dotSUB
* Facebook
* Flickr Video
* FunnyorDie.com
* GameTrailers
* GameVideos
* Generic Flash
* Glumbert
* GoalVideoz
* Google Video
* Grouper
* Guba
* Hamburg1 Video
* hulu
* IFILM
* ISeeIt.TV
* Jumpcut
* Kewego
* Last.fm
* LiveLeak
* MEGAVIDEO
* Metacafe
* Mncast.com
* Mojvideo.com
* MPORA.com
* MQSTO.com
* MSN
* MyspaceTV
* MyVideo
* Novamov
* OnSMASH.com
* reason.tv
* ReelzChannel
* Revver
* screencast-o-matic.com
* Sevenload
* slideshare.com
* smotri.com
* Sumo.tv
* teachertube
* Trilulilu
* Tu.tv
* UnCut
* Veoh
* Videotube
* Vimeo
* Vsocial
* vzaar.com
* Wandeo
* wat.tv
* Yahoo! Video
* Youreporter
* Youtube
* Youtube Playlist

== Installation ==

Install Wordpress Video Plugin either via the WordPress.org plugin directory or download and extract it to your wp-content/plugins directory. 
Then activate it. Afterwards, you can include a video on your posts or pages. 

== Using the plugin ==

Every occurence of the expression [site id] (case unsensitive) will start as an embedded flash player. Replace 'site' with the name of the videosite and 'id' with the video id. You can find more detailed instruction for the supported sites here: http://www.daburna.de/dokuwiki/doku.php/instruction.

You are missing your favourite videosite? Please tell me here: http://www.daburna.de/dokuwiki/doku.php/wishlist.

== Changelog ==

= 0.759 - 05.05.2013 =
* added support for archive.org
* updated code for:
- MyVideo (iframe)
- 123video.nl (width & height configurable)
- sevenload (iframe, made width & height configurable)
- vimeo
- youtube
* shortened instruction

= 0.758 - 04.01.2013 =
* updated code for:
- aniboom (new size, new code)
- blip.tv (some enhancement)
* added localisation

= 0.757 - 19.11.2012 =
* updated code for:
- youtube (wmode=transparent)
- dailymotion (iframe)
- youtube playlist (wmode=transparent)

= 0.756 - 18.10.2011 =
* added support for:
- mpora
* updated code for:
- comedycentral
- google (fullscreen allowed, size changed)
- youtube playlist (iframe)

= 0.755 - 17.05.2011 =
* added support for:
- vzaar
* updated code for:
- Bliptv (is now iframe; made width & height configurable)
- Sumo.tv
* updated donate link
* added first implementation of an option page for administrators

= 0.754 - 28.01.2011 =
* bugfix for YouTube: wrong fullscreen-tag crashes iframe
* added support for: MQSTO.com code by bedio

= 0.753 - 21.01.2011 =
* updated code for YouTube; new standard is iframe

= 0.752 - 13.11.2010 =
* updated code for:
- Generic Flash Code by Francisco Monteagudo
- Facebook allows Fullscreen
- bugfix for  wrong size declaration at Dailymotion
- youtube encoding is now en_US

= 0.751 - 18.10.2010 =
* Bugfix for wrong header 
* corrected version numbers

= 0.75 - 13.10.2010 =
* added support for:
- ISeeIt.TV (code by David Fudge http://www.testarca.de)
- hulu (code by James Kass http://www.chucklehutch.com)
- CBS (code by James Kass http://www.chucklehutch.com)
* updated code for:
- brightcove (new player and size)
- vimeo (supports iPad, iPhone, Flash and beyond)

= 0.744 - 20.07.2010 - bugfix release =
* removed code for Youtube channel (maybe this caused the problems)

= 0.743 - 15.07.2010 - bugfix release =
* corrected missing arguments for 'generic_flash_plugin' and 'tt_plugin_mu'
* removed German sentences at Youtube Channel
* smaller standard videosize for youtube 425 x 344, because small blogs got problems with the bigger size

= 0.74 - 14.07.2010 =
* added Youtube channel (code by Eric Hartmann http://erichartmann.de/?page_id=5)
* added the_excerpt for every site
* updated code for youtube
- strict XHTML
- new standard videosize is 560 x 340

= 0.73 - 06.02.2010 =
* updated code for
- dailymotion (by dailymotion team; New IDs, check your old IDs!)
- slideshare (by Graham)

= 0.72 - 26.01.2010 =
* updated code for
- MySpaceTV (fullscreen + bigger size)
- Sevenload
- Clipfish
* added donate link at readme
* updated plugin info text (link to instruction page + quickguide)
* added support for novamov.com

= 0.71 - 30.06.2009 =
* updated code for brightcove (instruction update!)
* added history at readme file
* added support for:
- Facebook Video (code by roberto scano http://robertoscano.info)
- Flickr Video (code by an anonymous user)
- Kewego (code by Renaud Boyer)

= 0.70 - 24.03.2009 =
* added support for:
- screencast-o-matic
- current

* updated code:
- liveleak
- slideshare (The code has essentially changed. You embedded old presentations need an id update!)
- blip.tv (Maybe the old embedded videos need an id update after upgrading.)

= 0.69 - 03.01.2009 =
* added support for:
- OnSMASH
- dotSUB

* updated code:
- youtube: fullscreen is now possible
- myvideo: configuring size & fullscreen is possible
- clipfish: configuring size & fullscreen is possible
- yahoo!: configuring size & fullscreen is possible
- fixed a bug at vimeo code -> http://wordpress.org/support/topic/208723

= 0.68 - 04.10.2008 =
* added support for:
- wat.tv by Bertimus (http://www.born2buzz.com/)
- smotri.com
* fixed bug "errors in archives" -> http://wordpress.org/support/topic/183928

= 0.67 - 14.06.2008 =
* added support for:
- Generic Flash by Francisco Monteagudo
- youreporter by Giacomo
- clipsyndicate.com by Antonio SJ Musumeci
- Mojvideo.com
- GoalVideoz
- Guba

* added ability to show videos at rss feed
* updated veoh.com code

= 0.66 - 06.04.2008 =
* added support for:
- MEGAVIDEO
- ReelzChannel
- d1g.com
- Trilulilu
- Funny or Die
* removed stage6 code; site is shutdown
* updated code for vimeo 

= 0.65 - 10.03.2008 =
* fixed bug on line 119 and 92
* fixed problem with fullscreen player for dailymotion

= 0.64 - 02.03.2008 =
* added support for:
- mncast.com
- youtube playlist
- msn (soapbox)

= 0.63 - 29.12.2007 =
* added support for:
- Jumpcut
- Collegehumor
- Hamburg1 Video

= 0.62 - 28.11.2007 =
* added support for:
- comedycentral by Antonio (http://www.landofbile.com/blog/)
- reason.tv by Antonio 

= 0.61 - 25.11.2007 =
* added support for:
- implemented size by <a href="http://www.dickes-en.de">Wolfgang Neikes</a> 
- vsocial
- teachertube by -drmike
- SlideShare developed by <a href="www.mikrosklave.net">Peter Weiland</a>, included by <a href="http://www.bertdesign.de/blog">Markus Bertling</a>
* first try of making flashplayersize configurable -->test it with youtube

= 0.60 - 15.10.2007 =
* added support for:
- last.fm
- Sumo.tv
* testing svn-access to worpress plugin directory

= 0.57 - 05.10.2007 =
* added support for:
- Aniboom
- Brightcove
- 123video.nl

= 0.56 - 16.08.2007 =
* added support for:
- Cellfish.com

= 0.55 - 31.07.2007 =
* added support for:
- tu.tv
- Stage6

= 0.54 - 28.07.2007 =
* added support for:
- Yahoo! Video
- myspacetv
- IFILM.com
- VEOH.com
- GameTrailers.com - thanks to Johno
- GameVideos.com - thanks to Johno
- Glumbert - thanks to Dwayne Bailey
- Wandeo - thanks to PyPe

= 0.53 - 16.06.2007 =
* added support for LiveLeak.com - thanks to Whiteman

= 0.52 - 18.02.2007 =
* added patch for youtube ids, which were converted wrong, thanks to Sigve Indregard from http://indregard.no

= 0.51 - 17.02.2007 =
* renamed the plugin -> now it works with update plugin
* videos from videotube should not auto play anymore

= 0.5 - 15.02.2007 =
* added support for:
- break.com
- metacafe
- vimeo
- videotube
- blip.tv
- revver
- UnCut
- Grouper

* hopefully solved overlapping problem

= 0.4 - 28.12.2006 =
* added support for:
- sevenload
- dailymotion

= 0.3 - 15.12.2006 =
* removed wrong code for youtube videos
* better support for clipfish

= 0.2 - 13.12.2006 =
* first official version of this plugin
* added support for:
- MyVideo
- Clipfish

= 0.1 - 13.12.2006 =
* first version
* support for:
- Youtube
- Google Video
