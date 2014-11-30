=== cgs-latest-posts ===
Contributors: kartikg3
Tags: cgsociety, forum, posts, latest, widget
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Widget to list your latest CGSociety forum posts.

== Description ==

Widget to list your latest CGSociety forum posts.

**Additional features**

*	Displays profile picture and stats
*	Configurable display options
*	Configurable titles
*	Configurable number of posts showed
*	Widget uses cache for better performance

If you have any issues or concerns please post them on the forum.

== Installation ==

1.   Extract the zip file and drop the contents in the wp-content/plugins/ directory of your WordPress installation.
2.   Activate the Plugin from Plugins page.
3.   Go to Appearance -> Widgets and look for the CGSociety Latest Posts widget.

== Frequently Asked Questions ==

= Can more than one widget be added? =

Yes. You can add as many widgets as you want.

= Why does loading time increase with the number of CGS Latest Post widgets added? =

The CGSociety forum has a minimum request time between two consequetive requests. Hence, each widget instance has to wait for this interval.

However, the widgets use caching for fast performance, so the widgets don't need to load all details for every request.
You can set the cache timeout in Settings -> CGS Latest Posts Options page.

== Screenshots ==

1. The CGSociety Latest Posts widget
2. Widget configuration
3. Widget options page
4. Widget clear cache in action
5. The widget in action

== Changelog ==

= 1.0 =
* First official release.
