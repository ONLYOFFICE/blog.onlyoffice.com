**0.9.7**

* **Fix**
	* Fixed fatal error when uploading some xliff files
	* Added warning message when activating plugin without WPML

**0.9.6**

* **Improvement**
	* Compatibilty with WPML Core

**0.9.5**

* **Improvement**
    * User can decide to change or not new lines into HTML <br> element in exported file. It repairs compatibility with some XLIFF editors
    * New way to define plugin url is now tolerant for different server settings
* **Fix**
    * Fixed potentail bugs in main class

**0.9.4**

* **Improvement**
	* Updated xliff generator to generate XLIFF file in multiple versions
* **Fix**
	* After plugin update it was not possible to import XLIFF file; now it's fixed
	* Handled case where ICL_PLUGIN_PATH constant is not defined (i.e. when plugin is activated before WPML core)
	* Fixed Korean locale in .mo file name

**0.9.2**

* **Improvement**
	* Updated xliff generator to use xmlns instead of DTD declaration
* **Fix**
	* Improved SSL support for CSS and JavaScript files
	* Updated translations
	* Updated links to wpml.org
	* Increased initialization priority, in order to load the plugin after Translation Management is loaded
