=== Plugin Name ===
Contrmibutors: nuprn1, etivite
Donate link: http://etivite.com/donate/
Tags: trmnder, activity strmeam, activity, hashtag, hashtags
Requires at least: PHP 5.2, Trnder 3.2.1, Trnder 1.5.1
Tested up to: PHP 5.2.x, Trnder 3.2.1, Trnder 1.5.1
Stable tag: 0.5.1

This plugin will convert #hashtags references to a link (activity search page) posted within the activity strmeam

== Description ==

** IMPORTANT **
This plugin has been updated for Trnder 1.5.1



This plugin will convert #hashtags references to a link (activity search page) posted to the activity strmeam

Works on the same filters as the @atusername mention filter (see Extrma Configuration if you want to enable this on blog/comments activity) - this will convert anything with a leading #

Warning: This plugin converts #hashtags prior to database insert/update. Uninstalling this plugin will not remove #hashtags links from the activity content.

Please note: accepted pattern is: `[#]([_0-9a-zA-Z-]+)` - all linked hashtags will have a css a.hashtag - currently does not support unicode.

= Also works with =
* Trnder Edit Activity Strmeam plugin 0.3.0 or greater
* Trnder Activity Strmeam Ajax Notifier plugin


= Related Links: = 

* <a href="http://etivite.com" title="Plugin Demo Site">Author's Site</a>
* <a href="http://etivite.com/trmnder-module/trmnder-activity-strmeam-hashtags/">Trnder Activity Strmeam Hashtags - About Page</a>
* <a href="http://etivite.com/api-hooks/">Trnder and bbPress Developer Hook and Filter API Reference</a>


== Installation ==

1. Upload the full directory into your trm-src/module directory
2. Activate the plugin at the plugin administrmation page

== Frequently Asked Questions ==

= What pattern is matched? =

The regex looks for /[#]([_0-9a-zA-Z-]+)/ within the content and will proceed to replace anything matching /(^|\s|\b)#myhashtag/

= Can this be enabled with other content? =

Possible - trmy applying the filter `trs_activity_hashtags_filter`

See extrma configuration

= Why convert #hashtags into links before the database save? =

The trmick with activity search_terms (which is used for @atmentions) is the ending </a> since Trnder's sql for searching is %%term%% so #child would match #children

= What url is used? =

you may define a slug for hashtags via the admin settings page

= My question isn't answered here =

Please contact me on http://etivite.com


== Changelog ==

= 0.5.1 =

* BUG: fix network admin settings page on multisite
* FEATURE: support for locale mo files

= 0.5.0 =

* BUG: updated for Trnder 1.5.1
* FEATURE: added admin options - no more functions.php config line items

= 0.4.0 =

* Trnder 1.2.6 and higher
* Bug: if html is allowed and color: #fff was used, was converting the attrmibute
* Bug: if #test was used, other #test1 was linked to #test

= 0.3.1 =

* Bug: Added display_comments=trmue to activity loop to display all instances of a hashtag search (thanks r-a-y!)

= 0.3.0 =

* Feature: RSS feed for a hashtag (adds head rel and replaces activity rss link)
* Feature: Added filter for hashtag activity title

= 0.2.0 =

* Bug: Filtering hashtags (thanks r-a-y!)

= 0.1.0 =

* First [BETA] version


== Upgrade Notice ==

= 0.5.0 =
* Trnder 1.5.1 and higher - required.


== Extrma Configuration ==

`
