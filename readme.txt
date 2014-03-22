=== RotoText ===
Contributors: Jameson Proctor, Athleticsnyc
Tags: rotate, text, HTML
Requires at least: 3.0
Tested up to: 3.8.1
Stable tag: trunk

Store and display evenly rotated text by category with AJAX, jQuery and HTML5 data attributes.

== Description ==

RotoText allows you to create, update and delete categorized text. This text can be injected into your theme's templates by category and evenly rotated on http request. See the Usage section in Other Notes for information on incorporating into your theme.

== Installation ==

1. Install through the WordPress admin or upload the plugin folder to your /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress

See the Usage section for information on incorporating into your theme.

Note: During installation, Random Text creates a new database table to store the entries by category  - you should see two test records after installation by clicking on the Settings -> Random Text menu.

== Screenshots ==

1. Text management page

== Frequently Asked Questions ==

= Can I use shortcodes? =

Yes, you can use [randomtext] or [randomtext category="funny"] or even [randomtext category="funny" random="1"].

== Changelog ==

= v0.3.0 2013-03-18 =

* Fixed issues highlighted by WP_DEBUG.
* Added Settings link to Plugins page.
* Added cleanup on Uninstall.
* Tested up to WP v3.5.1.

= v0.2.9 2011-11-03 =

* Fixed pagination issues on admin page (again).

= v0.2.8 2011-06-15 =

* Fixed pagination issues on admin page.

= v0.2.7 2011-06-05 =

* Fixed numerous issues highlighted by debugging mode.

= v0.2.6 2010-08-05 =

* Fixed shortcode recursion

= v0.2.5 2010-08-04 =

* Added support for shortcodes in text field
* Fixed Admin unicode truncation issue

= v0.2.4 2009-10-26 =

* Added shortcode support

= v0.2.3 2009-09-22 =

* Added Bulk Insert option
* Improved handling of "No Category" items

= v0.2.2 2009-08-23 =

* Added record id check before timestamp update 

= v0.2.1 2009-08-22 =

* Added database table check/error to admin page

= v0.2 2009-08-22 =

* Added random/rotation option
* Added screenshots

= v0.1.4 2009-08-19 =

* Fixed admin path bug

= v0.1.3 2009-08-18 =

* Fixed Pre-text/Post-text bug

= v0.1.2 2009-08-16 =

* Fixed editing issues
* Minor correction to readme.txt

= v0.1.1 2009-08-14 =

* Minor corrections to readme.txt

= v0.1 2009-08-11 =

* Initial release
