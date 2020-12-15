git init
git add -A
git commit -m 'Fix bad repo'
git push


original - backend - hashtag - trendr3
1. renamed all trs- in main directory to trm  including trs-src trm-src  --- RENAMED FROM WWW
2. wp = trm 

frontpage working -- delete working

3. WP = TRM




frontpage working -- delete working

4. bp - trs   --- RENAMED FROM trm-src 
5. TRS - TRS

frontpage working -- delete working

6.trendr - trendr  Trnder - Trnder  Trnder - Trnder
7. trmain - trmain TRmain - TRmain TRmain - TRmain

8. Trs - Trs

Fixed DELETING ISSUE -- Related to hashtag ls line 349 was dashed removed

ISsue - media upload does not work

3/4/2018
Issues with enter.php and php 7.2
REFERENCE:https://core.trac.trendr.org/attachment/ticket/37071/fix-wp-login-error.patch

changed line, 489 	$user = trm_signon('', $secure_cookie);
TO 	                $user = trm_signon(array(), $secure_cookie);

3/4/2018
Issue with page.php line 20 TRENDB and line 9 TRENDM
change from 	<?php the_content( __( ) ); ?>

to  	<?php the_content( array()); ?>

3/4/2018

php-7.2.3 works great sofar, online approved. use 127.0.0.1 for mysqli databse setup.

php 7.x xampp favorite button does not work when error off

3/4/2018

Fixed issue related to php 7.2.3 and trended portraits causing fatal error

changed screen.php line 92 

from function tr_trend_prepare_list_user_portraits( $activity, $has_access )

to function tr_trend_prepare_list_user_portraits( $activity)

3/5/2018

Fixed issue relating upload media post not clickable after activity load more.

Added the Js function to activity-loop for post media to work

3/6/2018 

Fixed variouse issues
Fixed the problem of users popular hashtag click not showing b/c of following tab selected on explrer page.
Add the js function to rest the selected tab when click on popular tag on user's profile page.

3/27/2018

Fixed the med submit on filter select
added 				<?php locate_template( array( 'activity/activity-loop.php' ), true ); ?>

to index.php line 62


4/2/2018

Fixed trend button showing when logged out. 

added css display:none to trend-img
display:inline to .logged-in trend-img


4/2/2018

Removed the post button from logged out users . added 			<?php if ( is_user_logged_in() ) : ?> to header.php line 29

Removed the media submit - post-content from logged out users. added   	<?php if ( is_user_logged_in() ) : ?> to index.php line 62

_________________________________________________

Long list of updates from 4/2/2018 - 5/25/2018
_________________________________________________


When renaming vanilla #whats-new-submit in js file to #submit, the addclass .loading interfeard with settings, login submit buttons
submit-settings, #submit-login  

Also changed the #submit button name for activity post to #submit-post


Renamed BP-Dtheme in functions.php thee file toTR_Theme .. issues with 

Renames:

generic-button  global-knob
post-mention post-memo
activity-button post-knob
mention memo

trs-core-template
line 1507 activity => posts
line 1534 my-activity => profile-page
line 1504 xprofile => profile

tr-core-functions
line 598-621 changed activity time formatinf to more modern

renamed activity-time-since to expand
view-discussion expand

Important:
Older theme TRENDB  was called TRS-DTheme, The new theme now is called TR-Theme
Change all files accordingly

tr-core-cssjs vanilla changed to trendr trendr original - otherwise media resize upload for profile page would not work

Turnd off activity readmore > global.js or src.js line 371-391 > ajax.php line 338-362

removed in Reply to in entry.php

Very important cosmetic fix:
_______________________________

broadcast-p now is after image and youtube -- youtube not completely fixed

put medcode before @post [content]

importanet : added GEO location plugin called bp-checkins
--------------------------------
IMPORTANT: Plugin refused to activate on trendr 1.54 because of issue LOAD ISSUE.. trendr() RENAMED  to $bp .. 
MIGHT WORK FOR OTHER PLUGINS THAT REFUSE TO ACTIVATE ON EARLIER BP VERSIONS 
Front page location autocomplete and finder requires jquery UI 1.10.3 for trendr 3.2.1

Backend saving button fixe foer trendr 3.2.1. by adding function wp-unlashed to formating.php in  wp-includes

****************************************
EXTREMELY IMPORTANT BUT SIMPLE RENAMING ISSUE
****************************************
Renaming avatar to portrait.

Any trendr or plugin calling old avatar trs_core_avatar stc.. is completely incompatible with the same trendr or plugin that has portarit
extension. That is what cause trendr activity edit not to work on buddepress renamed. 

****************************************
6-18-18 FIXED ALL ISSUUES REGARDING MEDIA UPLOAD
*HASHTAG DEPENDENCY REMOVED
*ADDED GEO LOCATE COMPATIBLE
****************************************

Moved post-form.php to header.php to make media uploads global on all pages

---------------------------------------
6-20-18 NEXTLEVEL CASHE BEST SETTINGS  - Use with object-cache

DROP IN TRM-MAIN

define('CACHE_WRITE_WHITELIST','_trs_activity|_trs_activity_meta|_trs_notifications|_trs_notifications_meta|_usermeta');
define('CACHE_READ_WHITELIST','_trs_notifications|_trs_activity');
--------------------------
Renamed publish-flow to publish
-------------------------

Issues with portrairs showing differently on post comments, trended and other places
Replace: Thumb
To:full
in entry.php and comment.php screens.php etc

###############################

6/23/18

Activity Red more settings is located in trs-activity-filter.php
line 242

*****************************
6-29-18
Lightbox Integrationwith the help of permalink and iframe  to avoid ajax usage for lightbbox

Important :
//Line 1180 trs-activity-template
// 6-29-18 Modified permalink to diferenciate directory activity and user activity permalinks for lightbox effect

******************************

7-3-18
//7-3-18 modified for hashtag to work with follow plugin and also fixed the issue with hastag profile page showing followrs of the user

line 234 was added on hook.php

******************************

8-301-18

Added link postage https certificate

claas-http borrowed from wp 3.7
added mbdtring to the end of functions.php
added ca-bundle to certificates folder in js