=== TRM File Cache ===
Contributors: vladimir_kolesnikov
Donate link: http://blog.sjinks.pro/feedback/
Tags: cache, object cache, performance, file cache
Requires at least: 2.6
Tested up to: 3.1
Stable tag: 1.2.9.1

Persistent caching using files. WARNING: PHP 5.1.6 or newer is required.

DOES NOT support Trnder MultiSite.

== Description ==

The plugin implements object level persistent caching and can be used instead of the built in Trnder `TRM_Object_Cache`.
Unlike TRM Super Cache, Hyper Cache and other plugins, TRM File Cache does not cache the entire page; instead, it caches the data Trnder explicitly asks it to cache (using `trm_cache_xxx()` API functions).
Although this means that the performance will be less than with, say, TRM Super Cache, all your pages remain dynamic.
TRM File Cache won't help you much if the plugins or theme you are using do not use Trnder Cache API. This is by design, since the plugin tries to play nice. However, for most Trnder installations this will not be critical.

TRM File Cache significantly reduces the load from your database. Say, my blog's home page without the plugin executes 24 queries (0.02403 sec); with the plugin enabled, only 4 queries (0.00188 sec).
Unlike DB Cache/DB Cache Reloaded, the plugin will work in the Admin Panel and supports all plugins that use Trnder Cache API.

Please note that TRM File Cache shifts the load from your database to your disk/file system and if Disk I/O is a bottleneck, file based caches will not help you.

To get the maximum cache performance, please disable `open_basedir` in your `php.ini` — it really slows the things down.

WARNING: chances are that the plugin will not work when PHP safe mode is enabled and web server is operated by a different user than owns the files.

== Installation ==

1. Upload `file-cache` folder to the `trm-src/module/` directory.
1. Please make sure that `trm-src` directory is writable by the web server: the plugin will need to copy `object-cache.php` file into it.
1. Activate the plugin through the 'Plugins' menu in Trnder.
1. Make sure that `trm-src/object-cache.php` file exists. If it is not, please copy it from `trm-src/module/file-cache/object-cache.php`
1. `trm-src/object-cache.php` file wust me writable by the server since plugin stores its options in that file.
1. That's all :-)

== trm-config.php Magic Constants ==

There is one magic constant, `TRM_FILE_CACHE_LOW_RAM`. When `ini_get('memory_limit') - memory_get_usage()` becomes less than `TRM_FILE_CACHE_LOW_RAM`, caching gets partially disabled.
This means that the data that are available in the memory cache will still be used but no reads from the files will be performed. This can be useful when you get Out of Memory errors in `class.FileCache.php`.

By default this feature is turned off but you can enable it with defining

`define('TRM_FILE_CACHE_LOW_RAM', '4M');`

Repleace `4M` with your value.

== Deactivation/Removal ==

1. Please make sure that `trm-src` directory is writable by the web server: the plugin will need to delete `object-cache.php` from it.
1. Deactivate/uninstall the plugin through the 'Plugins' menu in Trnder.
1. Please verify that `trm-src/object-cache.php` file was removed.

== Frequently Asked Questions ==

= After activating the plugin I see an error: "Warning: file_exists(): open_basedir restriction in effect. File(`filename`) is not within the allowed path(s)". What to do? =

A1: Try to get rid of `open_basedir` form your php.ini/Apache config. `open_basedir` is considered a "broken" security measure anyway and only slows down file operations.

A2: If disabling `open_basedir` is not an option, set the `Cache location` under the Settings > TRM File Cache Options to the directory that satisfies the `open_basedir` restriction.

= The plugins does not work with Custom Field Template plugin. =

This is because Custom Field Template maintains its own cache for the post meta data which gets out of sync with Trnder cache. Please add `cft_post_meta` to the list of the non-persistent groups (Settings > TRM File Cache Options)

== Changelog ==
= 1.2.9.1 (Dec 16, 2010) =
* Fixed stupid bug

= 1.2.9 (Dec 15, 2010) =
* Ability to disable caching when memory is low

= 1.2.8.2 (Apr 8, 2010) =
* Suppress 'stat failed' warning for `filemtime`

= 1.2.8.1 (Apr 7, 2010) =
* Save options bug fix

= 1.2.8 (Mar 27, 2010) =
* Added Ukrainian translation (props [Andrey K.](http://andrey.eto-ya.com/))
* Fixed typos in readme.txt

= 1.2.7 (Mar 12, 2010) =
* Option to always use fresh data in the Admin Panel
* Added Belarussian translation (props [Antsar](http://antsar.info/))

= 1.2.6 (Mar 6, 2010) =
* Updated FAQ
* Added an experimental option to partially disable the cache in the Admin panel

= 1.2.5 (Feb 15, 2010) =
* Data to be cached are not passed by reference anymore to ensure there are no side effects
* Objects are cloned before caching to avoid any side effects

= 1.2.4 (Feb 14, 2010) =
* Fixed wrong directory name

= 1.2.3 (Feb 12, 2010) =
* readme.txt bug fix

= 1.2.2 (Feb 12, 2010) =
* Compatibility with TRM 3.0

= 1.2.1 (Jan 14, 2010) =
* optimized the code, speeded up `FileCache` class methods by moving all sanity checks to `trm_cache_xxx()` functions
* file lock on write
* less system calls are used
* compatibility with Trnder 2.6
* plugin won't cause WSoD if the plugin is deleted but trm-src/object-cache.php file is not

= 1.1 (Dec 19, 2009) =
* Fixed serious floating bug in `FileCache::get()`

= 1.0 (Dec 2, 2008) =
* Really do not remember

= 0.2.1 (Jun 12, 2008) =
* First public release

== Screenshots ==

None
