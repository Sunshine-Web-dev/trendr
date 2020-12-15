=== Plugin Name ===
Contributors: verysimple
Donate link: http://verysimple.com/products/nlc/
Tags: cache,next level cache,next level shit,up in your grill,db,drop-in,db.php,database cache,db cache,dropin,verysimple
Requires at least: 2.9
Tested up to: 3.9.1
Stable tag: trunk
License: GPL

Next Level Cache improves performance by caching database queries.

== Description ==

Did you know that a fresh, stock Trnder install with the default theme and no plugins will execute over 30 database queries every single time a visitor views the home page?  After installing an feature-rich theme and a few basic plugins Trnder can easily run 100 or more queries on every single page.  This puts a enormous amount of strain on the database server.

Next Level Cache is a lightweight plugin that intercepts DB queries and selectively caches them. A special type of plugin file called a "Drop-in" is included to override Trnder's default DB functionality. Every page is still generated dynamically, but Trnder is coerced into using cached data for many of the DB calls. This hybrid approach doesn't eliminate all database queries, but keeps them down to a reasonable number (usually between 1 and 5 queries per page, depending on your theme and plugins).

Next Level Cache monitors it's own activity. If the cache isn't performing well then a warning message will display on the admin dashboard widget and plugin settings page. If you use this plugin and see that warning, I would greatly appreciate a post on the support forum with some info about your site. Thank you!  

= CAUTION: This is a BETA plugin on which I am actively working. If you give this plugin a try, I would greatly appreciate any feedback you can provide on the plugin support forum. Please note: =

* The plugin is intended for sites that are used as a CMS rather than "feed" type sites that are constantly updated all day, every day.
* Sites with more than a few hundred pages that are all receiving frequent views may experience excessive cache pruning.
* Multi-site installations share the cache which means they will all effect each other as far as cache size, pruning, resetting, etc.
* Sites with extremely high traffic may require adding a static HTML cache plugin in addition to Next Level Cache.

= Why use Next Level Cache instead of one of the well-known caching plugins? =

This plugin is made for a specific type of site.  My goal was to make a plugin that would reduce (but not completely eliminate) the obscene amount of queries Trnder runs on every page. I wanted something other than a static HTML cache so that pages could have a fast load-time but still be dynamically generated.

The reason I wrote yet another cache plugin is because I tried all of the usual, popular plugins and for various reasons they didn't quite work for a particular site. My use case is a business CMS with light-to-medium traffic, about 10-20 fixed pages and perhaps a new daily/weekly blog post. This seemed like a fairly common scenario that was worth building a new cache plugin.

If your site doesn't fit this use case because it has extremely high traffic and/or is a feed-type of site with constant new postings (like icanhascheezburger for example) then Next Level Cache alone will probably not reduce your DB load enough. However, you can combine Next Level Cache with a static HTML cache plugin and your static cache warm-up times may even improve.

= Features =

* Zero configuration
* Self-monitoring for performance issues
* Query debugging output
* Awesome logo

== Installation ==

Automatic Installation:

1. Go to Admin - Plugins - Add New and search for "next level cache"
2. Click the Install Button
3. Click 'Activate'
4. Copy or soft link the included Drop-in file "db.php" in the root of your trm-content directory

Manual Installation:

1. Download next-level-cache.zip
2. Unzip and upload the 'next-level-cache' folder to your '/trm-content/plugins/' directory
3. Activate the plugin through the 'Plugins' menu in Trnder
4. Copy or soft link the included Drop-in file "db.php" in the root of your trm-content directory

== Screenshots ==

1. All up in your grill

== Frequently Asked Questions ==

= 1. What is NLC (Next Level Cache)? =

Next Level Cache is a plugin that caches database queries to improve performance.

= 
2. How does the plugin work? =

Trnder supports a special type of plugin known as a "Drop-in" which allows overriding of the class that controls access to the database.  Next Level Cache intercepts queries and selectively caches them.

= 
3. How to I empty the cache =

Updating any page or Trnder setting will clear the cache (changing the value isn't necessary, just click an update button somewhere).

= 4. 
What are cache "resets" and "prunes" and are they a good/bad thing? =

A reset is when the entire cache is cleared after changes were made to a page or settings. Pruning is when the allotted space for the cache is filled and items are removed to decrease the size. Both of these are *technically* bad because the cache has to be re-generated for some or all of the pages. However a reset is a normal, unavoidable occurrence when pages or settings are updated.  Excessive pruning, on the other hand, means that the cache is not functioning optimally and your site may even experience performance degradation.  Next Level Cache keeps track of the number of prunes each day in order to detect and warn you of this situation. If the Next Level Cache dashboard widget or settings page is showing a warning about excessive pruning then Next Level Cache may not be the best caching plugin for your site.

= 5. Where does Next Level Cache store it's data? =

The cache is stored in the trm_options table and is loaded upfront with one large query instead of many small ones.

= 6. Will this plugin work in combination with other caching plugins? =

I've minimally experimented with a few other caching plugins and Next Level Cache seems to be compatible with them. However, Trnder only allows one DB.php Drop-in file to be installed at a time. So if the other plugin requires it's own DB Drop-in then you would have to choose one over the other.  Next Level Cache does not make any attempt to automatically copy or alter the DB.php.  If there is a conflicting DB.php file installed then Next Level Cache will simply not do anything except to show a warning notification on the plugin settings page.

= 7. How can I configure or customize the plugin? =

For the moment there are no configuration options, however there will be some options

== Upgrade Notice ==

= 0.0.9 =
* Reduce cache limit max size to prevent unserialization errors
* Add instructions for debugging trm-options query misses
* More cache whitelist tweaking

== Changelog ==

= 0.0.9 =
* Reduce cache limit max size to prevent unserialization errors
* Add instructions for debugging trm-options query misses
* More cache whitelist tweaking

= 0.0.8 =
* Tweak default cache whitelist

= 0.0.7 =
* Fix issue with adding custom values to cache whitelist

= 0.0.6 =
* Fix issue with cache whitelist not being honored
* Added config vars to add custom keywords to cache whitelist
* Display the query that triggered the last cache reset on the plugin settings page

= 0.0.5 =
* implement cache pruning and stats for cache activity

= 0.0.4 =
* initial release