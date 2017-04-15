=== tinyRatings ===
Contributors: ideag
Donate link: https://www.patreon.com/arunas
Tags: ratings, stars, like, dislike, rate, 5 stars
Requires at least: 4.6
Tested up to: 4.7
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plain and simple ratings plugin. Rate anything.

== Description ==

tinyRatings is a fresh take on post ratings. It offers you simple rating styles - Like, Like/Dislike and the very traditional 5 stars rating. It takes advantage of the Rest API and the Dashicons font to deliver you fast, modern looking interface. Votes can be logged based on browser fingerprint (via FingerprintJS2 library), visitor's IP address, user id for logged in users.

Unlike other similar plugins tinyRatings is not limited to posts, pages and custom post types. You can also insert ratings for categories, tags, other taxonomy terms and event for different sections in the post. Developers will find action and filter hooks that will allow them to use tinyRatings in pretty much any scenario.

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

== Screenshots ==

1. tinyRatings in action on Twenty Seventeen theme
2. tinyRatings in action on Twenty Seventeen theme
3. tinyRatings Settings page

== Changelog ==

= 0.1.0 =
* first version to be submitted to wordpress.org

== Upgrade Notice ==

= 0.1.0 =
Initial version
