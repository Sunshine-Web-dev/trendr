=== trendr Follow ===
Contributors: apeatling, r-a-y
Tags: trendr, following, followers, connections
Requires at least: TRM 3.2 / TRS 1.5
Tested up to: TRM 3.5.x / TRS 1.7
Stable tag: 1.2

== Description ==

Follow members on your trendr site with this nifty plugin.

The plugin works similar to the friends component, however the connection does not need to be accepted by the person being followed.  Just like Twitter!

This plugin adds:
* Following / Followers tabs to user profile pages
* Follow / Unfollow buttons on user profile pages and in the members directory
* A new "Following" activity directory tab
* An "Activity > Following" subnav tab to a user's profile page
* Menu items to the TRM Toolbar

For bug reports or to add patches or translation files, visit the [TRS Follow Github page](https://github.com/r-a-y/trendr-followers).

Translators will be forever enshrined in this readme! :)


== Installation ==

1. Download, install and activate the plugin.
1. To follow a user, simply visit their profile and hit the follow button under their name.


== Frequently Asked Questions ==

Check out the [TRS Follow wiki](https://github.com/r-a-y/trendr-followers/wiki).


== Changelog ==

= 1.2 =
* Add trendr 1.7 theme compatibility
* Add AJAX filtering to a user's "Following" and "Followers" pages
* Refactor plugin to use TRS 1.5's component API
* Bump version requirements to use at least TRS 1.5 (TRS 1.2 is no longer supported)
* Deprecate older templates and use newer format (/trendr/members/single/follow.php)
* Add ability to change the widget title
* Thanks to the Hamilton-Wentworth District School Board for sponsoring this release

= 1.1.1 =
* Show the following / followers tabs even when empty.
* Add better support for TRM Toolbar.
* Add better support for parent / child themes.
* Fix issues with following buttons when javascript is disabled.
* Fix issues with following activity overriding other member activity pages.
* Fix issue when a user has already been notified of their new follower.
* Fix issue when a user has disabled new follow notifications.
* Adjust some hooks so 3rd-party plugins can properly run their code.

= 1.1 =
* Add trendr 1.5 compatibility.
* Add TRM Admin Bar support.
* Add localization support.
* Add AJAX functionality to all follow buttons.
* Add follow button to group members page.
* Fix following count when a user is deleted.
* Fix dropdown activity filter for following tabs.
* Fix member profile following pagination
* Fix BuddyBar issues when a logged-in user is on another member's page.
* Thanks to mrjarbenne for sponsoring this release.

= 1.0 =
* Initial release.