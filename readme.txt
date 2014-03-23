=== RotoText ===
Contributors: Jameson Proctor, Athleticsnyc
Tags: rotate, text, HTML
Requires at least: 3.0
Tested up to: 3.8.1
Stable tag: trunk

Create and categorize text then display on even rotation

== Description ==

RotoText allows you to create, update and delete categorized text that can be injected into your theme's templates by category and evenly rotated on http request. See the Usage section in Other Notes for information on incorporating into your theme.

== Installation ==

1. Install through the WordPress admin or upload the plugin folder to your /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress

See the Usage section for information on incorporating into your theme.

Note: During installation, Random Text creates a new database table to store the entries by category  - you should see two test records after installation by clicking on the Settings -> Random Text menu.

== Screenshots ==

== Frequently Asked Questions ==

= Can I use HTML? =

Yes, the Text To Display field supports both plain text and HTML.

== Changelog ==

= 1.0=

* Initial release

== Usage ==

After activating the plugin, navigate to Settings > RotoText in the WordPress admin to create some text to display. Then, add a div to a template with the class of krt_roto_text and a data attribute named category with the value of the text you would like to display - e.g. `<div class="krt_roto_text" data-category="my-category"></div>`