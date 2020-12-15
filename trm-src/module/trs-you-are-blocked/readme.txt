=== TRS | You are blocked ===
Contributors: designbymerovingi
Tags:trendr, block, users, ignore
Requires at least: 3.1
Tested up to: 3.6.1
Stable tag: 1.0Beta5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Let your trendr users block other members from contacting them or viewing their profile.

== Description ==

You are blocked! is a very simple plugin for trendr powered websites allowing your users to block other members.
Your users can manage their blocked profiles under their profile settings and select to unblock them at any time.

**Blocked users will be prevented from**:

- Viewing your profile
- Send you messages or continue existing conversations
- Mentioning you using @yourname
- Add you as a friend
- See your activities

**You will be prevented from**:

- Viewing their profile
- See them in any member list
- Send them messages or continue existing conversations
- Add them as a friend
- Seeing their activities

**Other**:

- If you are friends with the person you select to block, your friendship will be terminated

**Customizations**:

The plugin uses two custom template files that you can override in your theme.
Simply create your own template in `yourtheme/members/single/` folder.

* blocking.php is used when someone you have blocked tries to view your profile
* blocked.php is used when you try and view a profile you block

**Limitation**:

* There are no blocking done in groups since groups have their own blocking mechanisms.
* This plugin only filters trendr features and pages. WordPress related actions such as creating a new post or commenting is not blocked!

**Requirements**:
* The TRS "Account Settings" Component must be enabled

== Installation ==

1. Upload `trendr-block` to the `/trm-src//` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Done


== Frequently Asked Questions ==

= Can members block Administrators? =

No. Members can not block anyone who has the "edit_users" capability.

= Can I change the capability to check for? =

Yes. Add `define( 'TRSB_ADMIN_CAP', 'capability' );` to your themes functions.php file.

= Can I unblock someone I have blocked? =

Yes. Visit your profiles "Settings" page where you should see the "Blocked Members" page link. Once on this page, you can select to unblock anyone in your list.

= Is it possible to show parts of a users profile instead of just hiding everything? =

Yes. Add your own templates for both the blocked and blocking files in the `yourtheme/members/single/` folder.


== Screenshots ==

1. **Manage Blocked Users** - Overview of the users you are blocking
2. **Members List** - Members that are not administrators get a new "Block" button in both the members list and directly in their profile.
3. **Blocked Profile** - What you see if you are trying to view a profile you are blocking.
4. **Blocked User** - What the blocked user sees if they try and visit your profile.

== Upgrade Notice ==

= 1.0Beta5 =
* Added proper filtering of the activities feed removing those we block and those who block us


== Other Notes ==

= Requirements =
* PHP version 5.2.4 or greater
* MySQL version 5.0 or greater
* trendr 1.8 or higher
* The TRS "Account Settings" Component must be enabled


== Changelog ==

= 1.0Beta5 =
* Added proper filtering of the activities feed removing those we block and those who block us
* Added TRSB_ADMIN_CAP constant to set which capability to exclude from being able to get blocked by members

= 1.0Beta4 =
* Added blocking of the activity content in the Activities feed

= 1.0Beta3 =
* Added blocking of users activities in members list
* Updated readme file

= 1.0Beta2 =
* Fixed Template Loading issue
* Moved assets into correct directory and removed from plugin (Thanks trs-help)
* Removed index.php files (Thanks trs-help)

= 1.0Beta1 =
* Official release.