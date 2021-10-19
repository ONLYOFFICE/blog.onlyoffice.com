=== Trinity Audio ===

Contributors : TrinityAudio
Tags: TrinityAudio, Polly, Trinity, Text-to-Speech, Audio, Tts, Voice, AI, Podcast
Requires at least: 5.2
Requires PHP: 7.2
Tested up to: 5.8
Stable tag: trunk
License: GPLv3 ONLY
License URI : https://www.gnu.org/licenses/gpl-3.0.html


== Description ==
Instantly convert your content into audio in just a few clicks, with seamless integration, and give your audience the ability to listen to your content on the go.

Implement the default TrinityAudio player on your website with over 30 languages and much more AI-voices.


[Explore](https://trinityaudio.ai/) our advanced products and features to take advantage of our full service:

* Almost 50 Supported languages with over 400 accents and natural voices
* CMS to manage, edit, distribute audio content and create playlists
* Automatic real-time translation
* Multiple playing speed capabilities
* Dashboard with statistics and usability reports

__And much more!__

For quick start, please follow the instructions on this guide: [Implementation Guide](https://trinityaudio.ai/the-trinity-audio-wordpress-plugin-implementation-guide/)

== Customer Testimonial ==

*"At a time when consumption of digital audio is stronger than ever, we're excited to roll out this new audio feature to our customers to enhance their news experience and give them the ability to listen to local journalism where and when they want it. In addition, the integration of this technology across all of our sites will drive advertising revenue."*

__Jessica Gilbert__, McClatchy Senior Director of Product and Experience

== Trinity audio usage terms ==

The Trinity Audio plugin provides a Text to speech service, which is performed by Trinity Audio.

As such, the service is required to make calls to Trinity’s backend servers.
By activating the plugin you consent to our T&C as stated below:

[Service Terms & Conditions](https://trinityaudio.ai/wp-plugin-terms/)
[Privacy policy](https://trinityaudio.ai/privacy-policy/)

For more about Trinity Audio: [Trinity Audio](https://trinityaudio.ai/)

== Settings and configurations ==

The following are configuration options you can modify as you see fit:

* __Default language:__ The language of your content. In case you have multiple, you can also control the language on the post level.
* __Default Gender:__ the default gender voice for each post.
* __New post default:__ Specifies whether the player is automatically enabled for all new posts. Choose this option if you want [Trinity Audio](https://trinityaudio.ai/) to add an audio player for each new post.
* __Player position:__ The position of the audio player on your page. We recommend placing the player before the main article text.
* __Player label:__ Specifies optional text you’d like to be shown above the audio player (HTML tags are supported with this label).
* __Display "Powered by Trinity Audio":__ choose if you want to display credit next to the player. We’d very much appreciate it if you choose to give us credit.
* __Resource preconnect:__ this feature will allow us to load the player faster on your page so we strongly advise enabling it.
* __Add post title to audio:__ If enabled, each audio file will start by reading the post title.
* __Add post excerpt to audio:__ If enabled, each audio file will read an excerpt from the post at the beginning of the audio (after the title, if chosen).
* __Skip HTML tags:__ Use this if you want to avoid generating audio for specific parts of posts based on the tags (content blacklist).
* __Allow shortcodes:__ By default, the Trinity Audio player will read only the textual content of your post and will filter out all additional texts generated using plugins or shortcodes. If you would like to add shortcodes that you would like the player to read, please add them using comma delimiter. For example: gallery, myshortcode. After you change those settings, make sure to use “Activate for all posts” to allow those configurations to update.

Still here? Go turn your readers to listeners.

== Installation ==

Instantly convert your content from text to audio with the most natural sounding voices. Just follow these steps:

1. Install the Trinity Audio plugin from the ‘Add new plugin’ option on your WordPress installation.
2. Activate the plugin.
3. Go to the ‘Trinity Audio’ tab in your WordPress admin interface. T&Cs need to be accepted to complete the registration.
4. Configure the settings of your plugin and click save to finish the setup.

= Recommended setup process =

Here are the recommended steps for configuring the plugin:

1. Choose the default source language.
3. Choose “Before post” in the “player position” section.
4. Choose “Add to all new posts” on the “New post default” section.
5. Check the “Add post title to audio” title option.
6. Click “Save Changes”.
7. Wait for all posts to be updated.


If at any stage you have questions or need support, reach out to us at: [wp@trinityaudio.ai](wp@trinityaudio.ai) - we’d love to hear from you!


== Frequently Asked Questions ==

= What is Trinity Audio? =
    Trinity Audio's AI-driven solutions help you create a world of audio experiences for your readers, covering every stage of the audio journey from creation to distribution.
= Which languages are supported? =
     Arabic, Chinese (Mandarin), Danish, Dutch, English (muliple accents), French, German, Hindi, Icelandic, Italian, Japanese, Korean, Norwegian, Polish, Portuguese (muliple accents), Romanian, Russian, Spanish (muliple accents), Swedish, Turkish, Welsh and more!
= How much does Trinity Audio cost? =
    Trinity Audio WP plugin version enables to convert 5 content articles to audio per month at no cost. For a more robust version of the player or higher consumption requirements please [contact us](https://trinityaudio.ai/contact/).
Generated audio posts will be available for consumption regardless of package status.
= Can I choose a different gender/language on a specific post? =
    Yes you can! In the general settings, you can define the default gender voice and can change it for each individual post in the post menu.
= If I add the player to all posts be default, can I disable selected posts? =
    Yes - you can disable it from specific posts from the post menu.
= Where would the player be placed in my post? =
    The exact location is configurable from the settings tab.
= What text would the player read? =
    The player would read the article body and headline. The Headline can be removed easily by clicking the checkbox in the settings tab. Reading the excerpt is also supported and it can be added from the settings tab.

== Screenshots ==

1. Admin settings screen.
2. Admin settings screen - continued.
3. Player view as implemented in a post.
4. Example of using "Player Label".


== Changelog ==

= 4.0.6 =
*Release Date - Oct 12, 2021*
* Small fixes

= 4.0.5 =
*Release Date - Oct 12, 2021*
* Added handling player for NitroPack plugin

= 4.0.4 =
*Release Date - Oct 7, 2021*
* Added installkey to links

= 4.0.3 =
*Release Date - Sep 23, 2021*
* fixed account linking

= 4.0.2 =
*Release Date - Sep 15, 2021*
* UI fixes

= 4.0.1 =
*Release Date - Sep 14, 2021*
* Small fixes

= 4.0.0 =
*Release Date - Sep 14, 2021*

* Completely redesigned UI
* Improved UX
* Account key activation feature
* Notification bar

= 3.3.1 =
*Release Date - Aug 18, 2021*
* Fixed bug on save post, while Trinity Audio is not reachable

= 3.3.0 =
*Release Date - Aug 3, 2021*
* Added Trinity account key activation

= 3.2.1 =
*Release Date - June 17, 2021*

* Bug fixes and preparation for Trinity Audio account connection

= 3.2.0 =
*Release Date - June 14, 2021*

* Show recover `install key` input, only when registration flow is failed

= 3.1.2 =
*Release Date - Jun 7, 2021*

* fixed PHP warnings

= 3.1.1 =
*Release Date - May 25, 2021*

* Add support of article credits (feature releases)

= 3.1.0 =
*Release Date - May 21, 2021*

* Added ability to restore `install key` during registration process

= 3.0.5 =
*Release Date - May 17, 2021*

* Added pageURL tag parameter

= 3.0.4 =
*Release Date - Feb 8, 2021*

* Fixed Contact Us logs not getting sent
* Fixed the Info page output
* Fixed setting input styling

= 3.0.3 =
*Release Date - Dec 25, 2020*

* Security updates, input sanitizing
* Service Registration form

= 2.7.1 =
*Release Date - Sep 14, 2020*

* Removal of monetization functionality.

= 2.7.0 =
*Release Date - Jul 21, 2020*

* Enhancing bulk update for handling unstable internet connection
* Log posts that failed to update during `bulk update` in plugin `Logs`

= 2.6.0 =
*Release Date - Jun 23, 2020*

* fixed issue with WP shared hostings activate all posts
* fixed issue when plugin could't be uninstalled
* update minimal requirement to run plugin

Thanks a lot for feedback from our users!

= 2.5.1 =
*Release Date - Jun 16, 2020*

* small fixes
* increasing timeout
* improved sending info logs

= 2.5.0 =
*Release Date - Jun 15, 2020*

* added new debug information into Info section. How opening Info section, log file is generated and you can go to contact us and send it right to us. This file will be send automatically if "Include logs" is checked.

= 2.4.1 =
*Release Date - May 8, 2020*

* added new languages. Now we support: Arabic, Chinese(Mandarin), Danish, Dutch, English, French, German, Hindi, Icelandic, Italian, Japanese, Korean, Norwegian, Polish, Portuguese, Romanian, Russian, Spanish, Swedish, Turkish, Welsh 🎉

= 2.4.0 =
*Release Date - Apr 20, 2020*

* added info sub-menu, with credits info
* improved text parsing, using TTS Trinity Audio player core

= 2.3.4 =
*Release Date - Apr 15, 2020*

* fixed a bug with not showing content when player disabled for post.
Thanks to our users for reporting this issue!
* improved logging

= 2.3.3 =
*Release Date - Apr 14, 2020*

* fixed error log

= 2.3.2 =
*Release Date - Apr 14, 2020*

* improved UI for first initial install

= 2.3.1 =
*Release Date - Apr 10, 2020*

* removed message for initial install
* fixed issue with update settings

= 2.3.0 =
*Release Date - Apr 8, 2020*

* fixed issue with deactivation
* added detailed message when client has an issue with plugin activation
* added message for first time using users
* fix player styles in case some themes override them
* improved logging
* fixed issue with sending from default email even if it's changed in contact us page

= 2.2.6 =
*Release Date - Apr 7, 2020*

* changed language endpoint
* migrated to new language codes supported by AWS Polly
* fixed issue with Hindi gender in settings, only supported female gender for now
* moved to a new language endpoint. Expect more languages in next release 🎉
* player loading performance improved
* tested with latest Wordpress v5.4
* fixed issue plugin deleting
* fixed issue with reading text content

= 2.1.0 =
* added "Logs" submenu
* added "Contact us" submenu with ability to send logs to our end from "Logs"

= 2.0.2 =
*Release Date - May 17, 2020*

* added preconnect option in order to improve player load speed

= 2.0.1 =
* moved from `PHP_WP_CONFIG` variable to `TRINITY_TTS_WP_CONFIG`

= 2.0.0 =
* new major version of Trinity Audio Player 2.0.0!
* lots of improvements and bug fixes

= 1.2.5 =
* enable save button
* init default settings
* update ui

= 1.2.4 =
* advanced settings

= 1.2.3 =
* fix defaultLanguage issue
* disable player if posthash is empty
* disable player on pages

= 1.2.2 =
* add language for each post

= 1.2.1 =
* remove border

= 1.2.0 =
* fix bug for single page or single post

= 1.1.9 =
* add details type

= 1.1.8 =
* fix stay tuned

= 1.1.7 =
* add support for pages

= 1.1.6 =
* save plugin settings

= 1.1.5 =
* add events inside plugin form

= 1.1.4 =
* fixed issue with broken admin panel

= 1.1.3 =
* fix bug when loaded on single page

= 1.1.2 =
* remove events on labels

= 1.1.1 =
* added visual info for bulk update

= 1.1.0 =
* added background task

= 1.0.9 =
* bug fix

= 1.0.8 =
* bug fix

= 1.0.7 =
* Reduce amount of save text for plugin functionality

= 1.0.6 =
* Excerpt reading - change default value to false

= 1.0.5 =
* Bug fix: spelling

= 1.0.4 =
* Added support for potential failure upon plugins update

= 1.0.3 =
* Add backward compatibility support

= 1.0.2 =
* Add information on the plugin

= 1.0.1 =
* Bug fix: change functions order

= 1.0.0 =
* First version of Trinity Audio plugin


== Upgrade Notice ==

= 1.0 =
Next versions might include new languages, new features and bug fixes.

= 2.0 =
Rewritten version of plugin. Lots of code refactoring, bug fixing.

= 3.0 =
Security updates, input sanitizing
