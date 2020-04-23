=== Gravity Forms Multilingual - run Gravity Forms with WPML ===
Contributors: dominykasgel, adelval, Pawel Wawrzyniak
Donate link: http://wpml.org
Tags: CMS, Gravity Forms, forms, WPML, multilingual
Requires at least: 3.3
Tested up to: 3.9
Stable tag: 1.2
Version: 1.2

Allows using Gravity Forms multilingually with WPML

== Description ==

This 'glue' plugin makes it possible to use multilingual forms using Gravity Forms and WPML. You'll be able to translate form titles and descriptions, field labels, field values, page titles, text and image buttons, confirmation messages and redirections, and email notifications.

= Features =

* Makes your forms fully multilingual
* Rather than having separate forms for each language, you create a single form and translate it to other languages, using WPML Translation Editor. This makes it much easier to keep forms synchronized across languages. 
* Any change in the original form (e.g. addition/edition/deletion of fields, notifications or confirmations) is immediately reflected in the translated forms, with a 'needs update' warning and fields that you can fill with the new translated content for every translatable string.

= Documentation =

Please go to [Gravity Forms Multilingual Doc](http://wpml.org/documentation/related-projects/gravity-forms-multilingual/) page. You'll find instructions for translating form fields, settings, notifications and confirmations as well as other texts.

= Downloads =

You will need:

* [Gravity Forms](http://www.gravityforms.com/) version 1.8 and up.
* [WPML](http://wpml.org) version 3.1 and up - the multilingual WordPress plugin.

== Installation ==

1. Upload 'gravity-forms-multilingual' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Send forms to translation from WPML's Translation Dashboard, and edit the translations from WPML->Translations. 

== Changelog ==

= 1.2 =

* Fixed problem with translations of deleted fields
* Fixed small issue with "Translation of this document is complete" checkbox (should not be checked in some cases)

= 1.0 =

* Add readme.txt.
* Add filter to translate error messages.
* Translate page titles in multipage forms.
* Translate previous, next and last button texts (and button imageUrls) in multipage forms.
* Translate multiselect values, also in merge tags.
* Translate price labels for products/options.
* For choice fields (dropdowns, etc), translate the option label, not the actual value (needed for conditional logic to work).
* Translate multiple confirmations. Confirmations are translated for messages and for page and url redirections.
* Translate multiple notifications (emails). They are translated when then email To field is entered by the user, and sent to the user in the languageinwhich the form was submitted.
* Merge tags work correctly with translations.
* gform_pre_render now takes two arguments, and handles confirmations differently.
* Remove gform_confirmation filter, as it works in a different way in GF 1.7.
* Add actions for updating forms and form settings (confirmations and notifications). Changed original content (e.g. field labels) appears immediately in the Translation Editor with 'translation is finished' unchecked. Addition and deletion of fields is also handled correctly, without having to delete the translation job and resubmit a new one.
* Translation status is correctly reflected in the Translation Dashboard and the Translation Queue.
* Add action for form duplication from the Translation Dashboard. The duplicate is then available as a translation of the form that can be edited with the Translation Editor. 
* Add action for form deletion.
* Gravity forms appear in Translation Dashboard with the 'Any' filter
* Add our own filters 'gform_multilingual_form_keys' and 'gform_multilingual_field_keys' so that plugin authors can register additional keys for translation.
* Display warning when WPML or Gravity Forms are inactive and do not load plugin.
