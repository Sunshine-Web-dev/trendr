=== trendr Checkins ===

Contributors: wbcomdesigns, vapvarun
Donate link: https://wbcomdesigns.com/donate/
Tags: trendr, check-ins , trendr Location, update check-ins, location
Requires at least: 3.0.1
Tested up to: 4.9.5
Stable tag: 4.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

This plugin allows trendr members to share their location when they are posting activities, you can add places where you visited, nearby locations based on google places.

Plugins have two mode of operations.
1) Auto Complete feature: You can add location for your choice start typing location name and it will suggest based on your inbut and you can select it.

2) Place Type Feature: You can ristrict your members to add locations based on nature of your website, like religious website can allows to add Church, Templates etc. Foodies websites can restrict check-ins for bars, pub and restaurant only.

Google Place API key is required for it, You can create your key from [Google Place Web Service Documentation](https://developers.google.com/places/web-service/) link.

This plugin also provides to select place types for your members, like if you are foodies you can select food-related place type and your members will able to post food-related places on your website activity stream. In the same way, you can select autocomplete box if you want to automatically fill up the location by google map API on member activity page.Both the options will show you a google map on frontend if you type and select a location either in autocomplete box or in place types list. Also, the plugin provides an x-profile field to set location at trendr profile page.

It will also show a google map for all the activity posts that has a location. If you need additional help you can contact us for [trendr Check-ins](https://wbcomdesigns.com/downloads/trendr-checkins/).


== Installation ==

1. Upload the entire trs-check-in folder to the /trm-content/plugins/ directory.

2. Activate the plugin through the 'Plugins' menu in trendr.

== Frequently Asked Questions ==

= Does This plugin requires trendr? =

Yes, It needs you to have trendr installed and activated.

= What is the use of API Key option provided in general settings section? =

With the help of Google Places API Key, a user can check-in with places autocomplete while updating post in trendr and list checked in a location in google map.

= Does this plugin require current location service? =

Yes, this plugin require location service and you can allow it from browser settings.

= How can we add a place when updating post? =

Just go to your profile section where you can update post, if you have checked place types option in check in setting where you can see a map marker icon clicking on which you can add your checked-in place but if you have checked autocomplete option then there you can see an auto-complete text box where you can type and select any location or place. After selection of any place, you can see a google map below it.

= Where can I see all check-ins activity? =

Check-ins filter option is provided in trendr filter drop-down option to list all check-ins activity.

= Where can I see favorite locations? =

All favorite locations are listed under Check-ins tab at trendr profile page.

= How to set location at profile page? =

The plugin provides x-profile location field to set location at trendr edit profile page.

= How to go for any custom development? =

If you need additional help you can contact us for <a href="https://wbcomdesigns.com/contact/" target="_blank" title="Wbcom Designs">Wbcom Designs</a>.

== Screenshots ==

1. screenshot-1 - shows the general settings in the plugin.

2. screenshot-2 - shows the front end panel, to check-ins by autocomplete.

3. screenshot-3 - shows the front end panel, to check-ins by place types.

4. screenshot-4 - shows the member profile, showing favorite locations.

5. screenshot-5 - Mobile view for check-ins inside activity.

== Changelog ==

= 1.1.0 =

* Enhancement- Multisite Support

= 1.0.8 =

* Enhancement- Code Quality Improviement with TRMCS
* Fix - Tanslation Fixes

= 1.0.7 =

* Fix - UI Improvements
* Fix - Error with PHP 7.0+ version.

= 1.0.6 =

* Fix - Location fixes

= 1.0.5 =

* Fix - A New option autocomplete is added in check-ins plugin setting. Now you can check either autocomplete or place types options. 
* Fix - If you check autocomplete, a autocomplete text box will be shown at the top of the page under textarea on member activity page where you can type and select any location from the list.
* Fix - All activity posts which have any place or location a google map will be shown below them to point that particular place.
* Enhancement - A new x-profile location field is added at trendr profile page from where a user can set location.

= 1.0.4 = 

* Fix - Dual File Fixes

= 1.0.3 =

* Fix - Location selection fixes

= 1.0.2 =

* Fix - Fixed Map Linking in activity for specific location

= 1.0.1 =

* Fix - Improved documentation and default Place type selection option

= 1.0.0 =

* Fix - Initial release.
