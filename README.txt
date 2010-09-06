=== Plugin Name ===
Contributors: flipstorm
Tags: custom, posts, pages, theme
Requires at least: 3.0.1
Tested up to: 3.0.1
Stable tag: trunk

FS Link Posts is a simple plugin to enable you to manually associate a post with other posts you’ve created.

== Description ==

It works with Posts, Pages and Custom Post Types, giving you a really simple way to make one post refer to another. Not to be confused with automated "Related Posts" plugins.

It's probably most handy for Custom Post Types, where you may want to display a list of similar items based on factors that are indeterminable for an automation algorithm.

**Current Limitations**
* It's only a one-way reference for now
* It's limited to linking posts of the same type

If you need help or have ideas for more features, please use the plugin forum.

== Installation ==

To install FS Link Posts:

1. Upload the folder `fs-link-posts` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php fs_linked_posts(get_post_ID()); ?>` inside The Loop in your templates

== Changelog ==

= 0.1 =
* This is the first version

== Coming Soon ==

* Admin panel for changing some basic options
* Linking posts of different types
* More flexible rendering of linked posts
* Ordering of linked posts