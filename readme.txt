=== tinyRatings ===
Contributors: ideag
Donate link: https://www.patreon.com/arunas
Tags: ratings, stars, like, dislike, rate, 5 stars
Requires at least: 4.6
Tested up to: 4.9
Stable tag: 0.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plain and simple ratings plugin. Rate anything.

== Description ==

tinyRatings is a fresh take on post ratings. It offers you simple rating styles - Like, Like/Dislike and the very traditional 5 stars rating. It takes advantage of the Rest API and the Dashicons font to deliver you fast, modern looking interface. Votes can be logged based on browser fingerprint (via FingerprintJS2 library), visitor's IP address, user id for logged in users.

Unlike other similar plugins tinyRatings is not limited to posts, pages and custom post types. You can also insert ratings for categories, tags, other taxonomy terms and event for different sections in the post. Developers will find action and filter hooks that will allow them to use tinyRatings in pretty much any scenario.

If you like my work and want to support development of my open source WordPress plugins, please consider becoming my patron at [Patreon](https://www.patreon.com/arunas).

Also, try out my other plugins:

* [ShortCache](http://wordpress.org/plugins/shortcache) - a plugin that llows user to cache output of any shortcode by adding a `cache` attribute to it.
* [Content Cards](http://arunas.co/cc) - allows to Embed any link from the web easily as a beautiful Content Card;
* [Gust](http://arunas.co/gust) - a Ghost-like admin panel for WordPress, featuring Markdown based split-view editor;
* [tinyCoffee](http://arunas.co/tinycoffee) - A WordPress donate button plugin with a twist - ask your supporters to treat you to a coffee, beer or other beverage of your choice;
* [tinySocial](http://arunas.co/tinysocial) - a simple way to add social sharing links to Your WordPress posts via shortcodes;
* [tinyTOC](http://arunas.co/tinytoc) - automatic Table of Contents, based on H1-H6 headings in post content;
* [tinyIP](http://arunas.co/tinyip) - *Premium* - stop WordPress users from sharing login information, force users to be logged in only from one device at a time;

Banner photo credit: Glen Carrie / Unsplash.com

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/tinyratings` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the `Settings -> tinyRatings` screen to configure the plugin
1. Insert ratings into your posts/pages via `[tinyrating]` shortcode

== Frequently Asked Questions ==

= How do I insert the rating into post? =

Use `[tinyrating]` shortcode, or `<?php do_shortcode( '[tinyrating]' ); ?>` if you want to insert it directly into a template.

= How do I insert the rating into taxonomy term? =

Use `[taxrating]` shortcode, or `<?php do_shortcode( '[taxrating]' ); ?>` if you want to insert it directly into a template.

= How do I insert the rating for part of a post? =

Use `[listrating]` shortcode, or `<?php do_shortcode( '[listrating]' ); ?>` if you want to insert it directly into a template.

= Can I define my own use cases? =

Sure, just use a custom `type` attribute for `[tinyrating]` shortcode. For example, the plugin settings page uses `[tinyrating type="demo"]` to display a demo rating.

= What attributes does `[tinyrating]` shortcode have? =

Here is the list of available attributes:

* `style` - allows to choose rating appearance. Available styles currently include `star` (default), `like`, `likedislike` and `updown`.
* `id` - object id.
* `type` - object type, for example 'post', 'tax', 'list', 'demo', etc.
* `subtype` - object subtype, for example 'page', 'category', post ID for lists, etc.
* `inline` - changes the container div to `display:inline-block`.
* `float` - floats the container div to the left or to the right.
* `active` - pass `false` to disable new votes.

= How can I display top objects?

Via `[tinyrating_top]` shortcode or using "tinyRatings Top List" widget.

= What attributes does `[tinyrating_top]` shortcode have? =

Here is the list of available attributes:

* `style` - allows to choose rating appearance. Available styles currently include `star` (default), `like`, `likedislike` and `updown`.
* `type` - object type, for example 'post', 'tax', 'list', 'demo', etc.
* `subtype` - object subtype, for example 'page', 'category', post ID for lists, etc.
* `limit` - How many top objects should be displayed.
* `list_type` - Which type of list elements (`<ul>` or `<ol>`) should be used for the top list. By default, shortcode uses `<ol>` and widget - `<ul>`.
* `rating` - pass false to disable display of ratings next to object names in the list.
* `float` - gets passed down to `[tinyrating]` shortcode.
* `active` -  gets passed down to `[tinyrating]` shortcode.

== Screenshots ==

1. tinyRatings in action on Twenty Seventeen theme
2. tinyRatings in action on Twenty Seventeen theme
3. tinyRatings Settings page

== Changelog ==

= 0.2.0 =
* Fixed exhausted memory bug on star-style ratings with structured data enabled.
* Fixed various notices.

= 0.1.4 =
* Small bugfixes

= 0.1.3 =
* Added color and result box display settings.
* Added auto-append settings.

= 0.1.2 =
* Added shortcode to display top lists.
* Added a widget to display top lists.

= 0.1.1 =
* removing CDN fingerprintjs2 option as per wordpess.org request

= 0.1.0 =
* first version to be submitted to wordpress.org

== Upgrade Notice ==

= 0.1.0 =
Initial version
