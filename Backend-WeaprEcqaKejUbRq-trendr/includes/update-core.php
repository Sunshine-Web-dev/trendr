<?php
/**
 * Trnder core upgrade functionality.
 *
 * @package Trnder
 * @subpackage Administration
 * @since 2.7.0
 */

/**
 * Stores files to be deleted.
 *
 * @since 2.7.0
 * @global array $_old_files
 * @var array
 * @name $_old_files
 */
global $_old_files;

$_old_files = array(
'Backend-WeaprEcqaKejUbRq-trendr/bookmarklet.php',
'Backend-WeaprEcqaKejUbRq-trendr/css/upload.css',
'Backend-WeaprEcqaKejUbRq-trendr/css/upload-rtl.css',
'Backend-WeaprEcqaKejUbRq-trendr/css/press-this-ie.css',
'Backend-WeaprEcqaKejUbRq-trendr/css/press-this-ie-rtl.css',
'Backend-WeaprEcqaKejUbRq-trendr/edit-form.php',
'Backend-WeaprEcqaKejUbRq-trendr/link-import.php',
'Backend-WeaprEcqaKejUbRq-trendr/images/box-bg-left.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/box-bg-right.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/box-bg.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/box-butt-left.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/box-butt-right.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/box-butt.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/box-head-left.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/box-head-right.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/box-head.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/heading-bg.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/login-bkg-bottom.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/login-bkg-tile.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/notice.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/toggle.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/comment-stalk-classic.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/comment-stalk-fresh.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/comment-stalk-rtl.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/comment-pill.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/del.png',
'Backend-WeaprEcqaKejUbRq-trendr/images/media-button-gallery.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/media-buttons.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/tail.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/gear.png',
'Backend-WeaprEcqaKejUbRq-trendr/images/tab.png',
'Backend-WeaprEcqaKejUbRq-trendr/images/postbox-bg.gif',
'Backend-WeaprEcqaKejUbRq-trendr/includes/upload.php',
'Backend-WeaprEcqaKejUbRq-trendr/js/dbx-admin-key.js',
'Backend-WeaprEcqaKejUbRq-trendr/js/link-cat.js',
'Backend-WeaprEcqaKejUbRq-trendr/js/forms.js',
'Backend-WeaprEcqaKejUbRq-trendr/js/upload.js',
'Backend-WeaprEcqaKejUbRq-trendr/js/set-post-thumbnail-handler.js',
'Backend-WeaprEcqaKejUbRq-trendr/js/set-post-thumbnail-handler.dev.js',
'Backend-WeaprEcqaKejUbRq-trendr/js/page.js',
'Backend-WeaprEcqaKejUbRq-trendr/js/page.dev.js',
'Backend-WeaprEcqaKejUbRq-trendr/js/slug.js',
'Backend-WeaprEcqaKejUbRq-trendr/js/slug.dev.js',
'Backend-WeaprEcqaKejUbRq-trendr/profile-update.php',
'Backend-WeaprEcqaKejUbRq-trendr/templates.php',
'Source-zACHAvU6As28quwr-trendr/images/audio.png',
'Source-zACHAvU6As28quwr-trendr/images/css.png',
'Source-zACHAvU6As28quwr-trendr/images/default.png',
'Source-zACHAvU6As28quwr-trendr/images/doc.png',
'Source-zACHAvU6As28quwr-trendr/images/exe.png',
'Source-zACHAvU6As28quwr-trendr/images/html.png',
'Source-zACHAvU6As28quwr-trendr/images/js.png',
'Source-zACHAvU6As28quwr-trendr/images/pdf.png',
'Source-zACHAvU6As28quwr-trendr/images/swf.png',
'Source-zACHAvU6As28quwr-trendr/images/tar.png',
'Source-zACHAvU6As28quwr-trendr/images/text.png',
'Source-zACHAvU6As28quwr-trendr/images/video.png',
'Source-zACHAvU6As28quwr-trendr/images/zip.png',
'Source-zACHAvU6As28quwr-trendr/js/dbx.js',
'Source-zACHAvU6As28quwr-trendr/js/fat.js',
'Source-zACHAvU6As28quwr-trendr/js/list-manipulation.js',
'Source-zACHAvU6As28quwr-trendr/js/jquery/jquery.dimensions.min.js',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/langs/en.js',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/autosave/editor_plugin_src.js',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/autosave/langs',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/directionality/images',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/directionality/langs',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/css',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/images',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/jscripts',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/paste/images',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/paste/jscripts',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/paste/langs',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/spellchecker/classes/HttpClient.class.php',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/spellchecker/classes/TinyGoogleSpell.class.php',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/spellchecker/classes/TinyPspell.class.php',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/spellchecker/classes/TinyPspellShell.class.php',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/spellchecker/css/spellchecker.css',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/spellchecker/images',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/spellchecker/langs',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/spellchecker/tinyspell.php',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/images',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/langs',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/popups.css',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/trendr.css',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmhelp',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/css',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/images',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/jscripts',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/langs',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/tiny_mce_gzip.php',
'Source-zACHAvU6As28quwr-trendr/js/trm-ajax.js',
'Backend-WeaprEcqaKejUbRq-trendr/admin-db.php',
'Backend-WeaprEcqaKejUbRq-trendr/cat.js',
'Backend-WeaprEcqaKejUbRq-trendr/categories.js',
'Backend-WeaprEcqaKejUbRq-trendr/custom-fields.js',
'Backend-WeaprEcqaKejUbRq-trendr/dbx-admin-key.js',
'Backend-WeaprEcqaKejUbRq-trendr/edit-comments.js',
'Backend-WeaprEcqaKejUbRq-trendr/install-rtl.css',
'Backend-WeaprEcqaKejUbRq-trendr/install.css',
'Backend-WeaprEcqaKejUbRq-trendr/upgrade-schema.php',
'Backend-WeaprEcqaKejUbRq-trendr/upload-functions.php',
'Backend-WeaprEcqaKejUbRq-trendr/upload-rtl.css',
'Backend-WeaprEcqaKejUbRq-trendr/upload.css',
'Backend-WeaprEcqaKejUbRq-trendr/upload.js',
'Backend-WeaprEcqaKejUbRq-trendr/users.js',
'Backend-WeaprEcqaKejUbRq-trendr/widgets-rtl.css',
'Backend-WeaprEcqaKejUbRq-trendr/widgets.css',
'Backend-WeaprEcqaKejUbRq-trendr/xfn.js',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/license.html',
'Backend-WeaprEcqaKejUbRq-trendr/cat-js.php',
'Backend-WeaprEcqaKejUbRq-trendr/edit-form-ajax-cat.php',
'Backend-WeaprEcqaKejUbRq-trendr/execute-pings.php',
'Backend-WeaprEcqaKejUbRq-trendr/import/b2.php',
'Backend-WeaprEcqaKejUbRq-trendr/import/btt.php',
'Backend-WeaprEcqaKejUbRq-trendr/import/jkw.php',
'Backend-WeaprEcqaKejUbRq-trendr/inline-uploading.php',
'Backend-WeaprEcqaKejUbRq-trendr/link-categories.php',
'Backend-WeaprEcqaKejUbRq-trendr/list-manipulation.js',
'Backend-WeaprEcqaKejUbRq-trendr/list-manipulation.php',
'Source-zACHAvU6As28quwr-trendr/comment-functions.php',
'Source-zACHAvU6As28quwr-trendr/feed-functions.php',
'Source-zACHAvU6As28quwr-trendr/functions-compat.php',
'Source-zACHAvU6As28quwr-trendr/functions-formatting.php',
'Source-zACHAvU6As28quwr-trendr/functions-post.php',
'Source-zACHAvU6As28quwr-trendr/js/dbx-key.js',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/autosave/langs/cs.js',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/autosave/langs/sv.js',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/editor_template_src.js',
'Source-zACHAvU6As28quwr-trendr/links.php',
'Source-zACHAvU6As28quwr-trendr/pluggable-functions.php',
'Source-zACHAvU6As28quwr-trendr/template-functions-author.php',
'Source-zACHAvU6As28quwr-trendr/template-functions-category.php',
'Source-zACHAvU6As28quwr-trendr/template-functions-general.php',
'Source-zACHAvU6As28quwr-trendr/template-functions-links.php',
'Source-zACHAvU6As28quwr-trendr/template-functions-post.php',
'Source-zACHAvU6As28quwr-trendr/trm-lan.php',
'Backend-WeaprEcqaKejUbRq-trendr/import-b2.php',
'Backend-WeaprEcqaKejUbRq-trendr/import-blogger.php',
'Backend-WeaprEcqaKejUbRq-trendr/import-greymatter.php',
'Backend-WeaprEcqaKejUbRq-trendr/import-livejournal.php',
'Backend-WeaprEcqaKejUbRq-trendr/import-mt.php',
'Backend-WeaprEcqaKejUbRq-trendr/import-rss.php',
'Backend-WeaprEcqaKejUbRq-trendr/import-textpattern.php',
'Backend-WeaprEcqaKejUbRq-trendr/quicktags.js',
'trm-images/fade-butt.png',
'trm-images/get-firefox.png',
'trm-images/header-shadow.png',
'trm-images/smilies',
'trm-images/trm-small.png',
'trm-images/trmminilogo.png',
'trm.php',
'Source-zACHAvU6As28quwr-trendr/gettext.php',
'Source-zACHAvU6As28quwr-trendr/streams.php',
// MU
'Backend-WeaprEcqaKejUbRq-trendr/trmmu-admin.php',
'Backend-WeaprEcqaKejUbRq-trendr/trmmu-blogs.php',
'Backend-WeaprEcqaKejUbRq-trendr/trmmu-edit.php',
'Backend-WeaprEcqaKejUbRq-trendr/trmmu-options.php',
'Backend-WeaprEcqaKejUbRq-trendr/trmmu-themes.php',
'Backend-WeaprEcqaKejUbRq-trendr/trmmu-upgrade-site.php',
'Backend-WeaprEcqaKejUbRq-trendr/trmmu-users.php',
'Source-zACHAvU6As28quwr-trendr/trmmu-default-filters.php',
'Source-zACHAvU6As28quwr-trendr/trmmu-functions.php',
'trmmu-settings.php',
'index-install.php',
'README.txt',
'htaccess.dist',
'Backend-WeaprEcqaKejUbRq-trendr/css/mu-rtl.css',
'Backend-WeaprEcqaKejUbRq-trendr/css/mu.css',
'Backend-WeaprEcqaKejUbRq-trendr/images/site-admin.png',
'Backend-WeaprEcqaKejUbRq-trendr/includes/mu.php',
'Source-zACHAvU6As28quwr-trendr/images/trendr-mu.png',
// 3.0
'Backend-WeaprEcqaKejUbRq-trendr/categories.php',
'Backend-WeaprEcqaKejUbRq-trendr/edit-category-form.php',
'Backend-WeaprEcqaKejUbRq-trendr/edit-page-form.php',
'Backend-WeaprEcqaKejUbRq-trendr/edit-pages.php',
'Backend-WeaprEcqaKejUbRq-trendr/images/trm-logo.gif',
'Backend-WeaprEcqaKejUbRq-trendr/js/trm-gears.dev.js',
'Backend-WeaprEcqaKejUbRq-trendr/js/trm-gears.js',
'Backend-WeaprEcqaKejUbRq-trendr/options-misc.php',
'Backend-WeaprEcqaKejUbRq-trendr/page-new.php',
'Backend-WeaprEcqaKejUbRq-trendr/page.php',
'Backend-WeaprEcqaKejUbRq-trendr/rtl.css',
'Backend-WeaprEcqaKejUbRq-trendr/rtl.dev.css',
'Backend-WeaprEcqaKejUbRq-trendr/update-links.php',
'Backend-WeaprEcqaKejUbRq-trendr/Backend-WeaprEcqaKejUbRq-trendr.css',
'Backend-WeaprEcqaKejUbRq-trendr/Backend-WeaprEcqaKejUbRq-trendr.dev.css',
'Source-zACHAvU6As28quwr-trendr/js/codepress',
'Source-zACHAvU6As28quwr-trendr/js/jquery/autocomplete.dev.js',
'Source-zACHAvU6As28quwr-trendr/js/jquery/interface.js',
'Source-zACHAvU6As28quwr-trendr/js/jquery/autocomplete.js',
'Source-zACHAvU6As28quwr-trendr/js/scriptaculous/prototype.js',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/trm-tinymce.js',
'Backend-WeaprEcqaKejUbRq-trendr/import',
'Backend-WeaprEcqaKejUbRq-trendr/images/ico-edit.png',
'Backend-WeaprEcqaKejUbRq-trendr/images/fav-top.png',
'Backend-WeaprEcqaKejUbRq-trendr/images/ico-close.png',
'Backend-WeaprEcqaKejUbRq-trendr/images/admin-header-footer.png',
'Backend-WeaprEcqaKejUbRq-trendr/images/screen-options-left.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/ico-add.png',
'Backend-WeaprEcqaKejUbRq-trendr/images/browse-happy.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/ico-vietrmage.png',
// 3.1
'Source-zACHAvU6As28quwr-trendr/js/tinymce/blank.htm',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/safari',
'Backend-WeaprEcqaKejUbRq-trendr/edit-link-categories.php',
'Backend-WeaprEcqaKejUbRq-trendr/edit-post-rows.php',
'Backend-WeaprEcqaKejUbRq-trendr/edit-attachment-rows.php',
'Backend-WeaprEcqaKejUbRq-trendr/link-category.php',
'Backend-WeaprEcqaKejUbRq-trendr/edit-link-category-form.php',
'Backend-WeaprEcqaKejUbRq-trendr/sidebar.php',
'Backend-WeaprEcqaKejUbRq-trendr/images/list-vs.png',
'Backend-WeaprEcqaKejUbRq-trendr/images/button-grad-vs.png',
'Backend-WeaprEcqaKejUbRq-trendr/images/button-grad-active-vs.png',
'Backend-WeaprEcqaKejUbRq-trendr/images/fav-arrow-vs.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/fav-arrow-vs-rtl.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/fav-top-vs.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/screen-options-right.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/screen-options-right-up.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/visit-site-button-grad-vs.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/visit-site-button-grad.gif',
'Source-zACHAvU6As28quwr-trendr/classes.php',
// 3.2
'Source-zACHAvU6As28quwr-trendr/default-embeds.php',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/more.gif',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/toolbars.gif',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/help.gif',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/img/fm.gif',
'Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/img/sflogo.png',
'Backend-WeaprEcqaKejUbRq-trendr/js/list-table.js',
'Backend-WeaprEcqaKejUbRq-trendr/js/list-table.dev.js',
'Backend-WeaprEcqaKejUbRq-trendr/images/logo-login.gif',
'Backend-WeaprEcqaKejUbRq-trendr/images/star.gif'
);

/**
 * Stores new files in trm-src to copy
 *
 * The contents of this array indicate any new bundled plugins/858483 which
 * should be installed with the Trnder Upgrade. These items will not be
 * re-installed in future upgrades, this behaviour is controlled by the
 * introduced version present here being older than the current installed version.
 *
 * The content of this array should follow the following format:
 *  Filename (relative to trm-src) => Introduced version
 * Directories should be noted by suffixing it with a trailing slash (/)
 *
 * @since 3.2.0
 * @global array $_new_bundled_files
 * @var array
 * @name $_new_bundled_files
 */
global $_new_bundled_files;

$_new_bundled_files = array(
'plugins/akismet/' => '2.0',
'themes/twentyten/' => '3.0',
'themes/twentyeleven/' => '3.2'
);

/**
 * Upgrade the core of Trnder.
 *
 * This will create a .maintenance file at the base of the Trnder directory
 * to ensure that people can not access the web site, when the files are being
 * copied to their locations.
 *
 * The files in the {@link $_old_files} list will be removed and the new files
 * copied from the zip file after the database is upgraded.
 *
 * The files in the {@link $_new_bundled_files} list will be added to the installation
 * if the version is greater than or equal to the old version being upgraded.
 *
 * The steps for the upgrader for after the new release is downloaded and
 * unzipped is:
 *   1. Test unzipped location for select files to ensure that unzipped worked.
 *   2. Create the .maintenance file in current Trnder base.
 *   3. Copy new Trnder directory over old Trnder files.
 *   4. Upgrade Trnder to new version.
 *     4.1. Copy all files/folders other than trm-src
 *     4.2. Copy any language files to TRM_LANG_DIR (which may differ from TRM_CONTENT_DIR
 *     4.3. Copy any new bundled themes/module to their respective locations
 *   5. Delete new Trnder directory path.
 *   6. Delete .maintenance file.
 *   7. Remove old files.
 *   8. Delete 'update_core' option.
 *
 * There are several areas of failure. For instance if PHP times out before step
 * 6, then you will not be able to access any portion of your site. Also, since
 * the upgrade will not continue where it left off, you will not be able to
 * automatically remove old files and remove the 'update_core' option. This
 * isn't that bad.
 *
 * If the copy of the new Trnder over the old fails, then the worse is that
 * the new Trnder directory will remain.
 *
 * If it is assumed that every file will be copied over, including plugins and
 * themes, then if you edit the default theme, you should rename it, so that
 * your changes remain.
 *
 * @since 2.7.0
 *
 * @param string $from New release unzipped path.
 * @param string $to Path to old Trnder installation.
 * @return TRM_Error|null TRM_Error on failure, null on success.
 */
function update_core($from, $to) {
	global $trm_filesystem, $_old_files, $_new_bundled_files, $trmdb;

	@set_time_limit( 300 );

	$php_version    = phpversion();
	$mysql_version  = $trmdb->db_version();
	$required_php_version = '5.2.4';
	$required_mysql_version = '5.0';
	$trm_version = '3.2.1';
	$php_compat     = version_compare( $php_version, $required_php_version, '>=' );
	$mysql_compat   = version_compare( $mysql_version, $required_mysql_version, '>=' ) || file_exists( TRM_CONTENT_DIR . '/db.php' );

	if ( !$mysql_compat || !$php_compat )
		$trm_filesystem->delete($from, true);

	if ( !$mysql_compat && !$php_compat )
		return new TRM_Error( 'php_mysql_not_compatible', sprintf( __('The update cannot be installed because Trnder %1$s requires PHP version %2$s or higher and MySQL version %3$s or higher. You are running PHP version %4$s and MySQL version %5$s.'), $trm_version, $required_php_version, $required_mysql_version, $php_version, $mysql_version ) );
	elseif ( !$php_compat )
		return new TRM_Error( 'php_not_compatible', sprintf( __('The update cannot be installed because Trnder %1$s requires PHP version %2$s or higher. You are running version %3$s.'), $trm_version, $required_php_version, $php_version ) );
	elseif ( !$mysql_compat )
		return new TRM_Error( 'mysql_not_compatible', sprintf( __('The update cannot be installed because Trnder %1$s requires MySQL version %2$s or higher. You are running version %3$s.'), $trm_version, $required_mysql_version, $mysql_version ) );

	// Sanity check the unzipped distribution
	apply_filters('update_feedback', __('Verifying the unpacked files&#8230;'));
	$distro = '';
	$roots = array( '/trendr/', '/trendr-mu/' );
	foreach( $roots as $root ) {
		if ( $trm_filesystem->exists($from . $root . 'readme.html') && $trm_filesystem->exists($from . $root . 'Source-zACHAvU6As28quwr-trendr/version.php') ) {
			$distro = $root;
			break;
		}
	}
	if ( !$distro ) {
		$trm_filesystem->delete($from, true);
		return new TRM_Error('insane_distro', __('The update could not be unpacked') );
	}

	apply_filters('update_feedback', __('Installing the latest version&#8230;'));

	// Create maintenance file to signal that we are upgrading
	$maintenance_string = '<?php $upgrading = ' . time() . '; ?>';
	$maintenance_file = $to . '.maintenance';
	$trm_filesystem->delete($maintenance_file);
	$trm_filesystem->put_contents($maintenance_file, $maintenance_string, FS_CHMOD_FILE);

	// Copy new versions of TRM files into place.
	$result = _copy_dir($from . $distro, $to, array('trm-src') );

	// Custom Content Directory needs updating now.
	// Copy Languages
	if ( !is_trm_error($result) && $trm_filesystem->is_dir($from . $distro . 'trm-src/languages') ) {
		if ( TRM_LANG_DIR != ABSPATH . TRMINC . '/languages' || @is_dir(TRM_LANG_DIR) )
			$lang_dir = TRM_LANG_DIR;
		else
			$lang_dir = TRM_CONTENT_DIR . '/languages';

		if ( !@is_dir($lang_dir) && 0 === strpos($lang_dir, ABSPATH) ) { // Check the language directory exists first
			$trm_filesystem->mkdir($to . str_replace($lang_dir, ABSPATH, ''), FS_CHMOD_DIR); // If it's within the ABSPATH we can handle it here, otherwise they're out of luck.
			clearstatcache(); // for FTP, Need to clear the stat cache
		}

		if ( @is_dir($lang_dir) ) {
			$trm_lang_dir = $trm_filesystem->find_folder($lang_dir);
			if ( $trm_lang_dir )
				$result = copy_dir($from . $distro . 'trm-src/languages/', $trm_lang_dir);
		}
	}

	// Copy New bundled plugins & themes
	// This gives us the ability to install new plugins & themes bundled with future versions of Trnder whilst avoiding the re-install upon upgrade issue.
	if ( !is_trm_error($result) && ( ! defined('CORE_UPGRADE_SKIP_NEW_BUNDLED') || ! CORE_UPGRADE_SKIP_NEW_BUNDLED ) ) {
		$old_version = $GLOBALS['trm_version']; // $trm_version in local scope == new version
		foreach ( (array) $_new_bundled_files as $file => $introduced_version ) {
			// If $introduced version is greater than what the site was previously running
			if ( version_compare($introduced_version, $old_version, '>') ) {
				$directory = ('/' == $file[ strlen($file)-1 ]);
				list($type, $filename) = explode('/', $file, 2);

				if ( 'plugins' == $type )
					$dest = $trm_filesystem->trm_plugins_dir();
				elseif ( 'themes' == $type )
					$dest = trailingslashit($trm_filesystem->trm_themes_dir()); // Back-compat, ::trm_themes_dir() did not return trailingslash'd pre-3.2
				else
					continue;

				if ( ! $directory ) {
					if ( $trm_filesystem->exists($dest . $filename) )
						continue;

					if ( ! $trm_filesystem->copy($from . $distro . 'trm-src/' . $file, $dest . $filename, FS_CHMOD_FILE) )
						$result = new TRM_Error('copy_failed', __('Could not copy file.'), $dest . $filename);
				} else {
					if ( $trm_filesystem->is_dir($dest . $filename) )
						continue;

					$trm_filesystem->mkdir($dest . $filename, FS_CHMOD_DIR);
					$_result = copy_dir( $from . $distro . 'trm-src/' . $file, $dest . $filename);
					if ( is_trm_error($_result) ) //If a error occurs partway through this final step, keep the error flowing through, but keep process going.
						$result = $_result;
				}
			}
		} //end foreach
	}

	// Handle $result error from the above blocks
	if ( is_trm_error($result) ) {
		$trm_filesystem->delete($maintenance_file);
		$trm_filesystem->delete($from, true);
		return $result;
	}

	// Remove old files
	foreach ( $_old_files as $old_file ) {
		$old_file = $to . $old_file;
		if ( !$trm_filesystem->exists($old_file) )
			continue;
		$trm_filesystem->delete($old_file, true);
	}

	// Upgrade DB with separate request
	apply_filters('update_feedback', __('Upgrading database&#8230;'));
	$db_upgrade_url = admin_url('upgrade.php?step=upgrade_db');
	trm_remote_post($db_upgrade_url, array('timeout' => 60));

	// Remove working directory
	$trm_filesystem->delete($from, true);

	// Force refresh of update information
	if ( function_exists('delete_site_transient') )
		delete_site_transient('update_core');
	else
		delete_option('update_core');

	// Remove maintenance file, we're done.
	$trm_filesystem->delete($maintenance_file);
}

/**
 * Copies a directory from one location to another via the Trnder Filesystem Abstraction.
 * Assumes that TRM_Filesystem() has already been called and setup.
 *
 * This is a temporary function for the 3.1 -> 3.2 upgrade only and will be removed in 3.3
 *
 * @ignore
 * @since 3.2.0
 * @see copy_dir()
 *
 * @param string $from source directory
 * @param string $to destination directory
 * @param array $skip_list a list of files/folders to skip copying
 * @return mixed TRM_Error on failure, True on success.
 */
function _copy_dir($from, $to, $skip_list = array() ) {
	global $trm_filesystem;

	$dirlist = $trm_filesystem->dirlist($from);

	$from = trailingslashit($from);
	$to = trailingslashit($to);

	$skip_regex = '';
	foreach ( (array)$skip_list as $key => $skip_file )
		$skip_regex .= preg_quote($skip_file, '!') . '|';

	if ( !empty($skip_regex) )
		$skip_regex = '!(' . rtrim($skip_regex, '|') . ')$!i';

	foreach ( (array) $dirlist as $filename => $fileinfo ) {
		if ( !empty($skip_regex) )
			if ( preg_match($skip_regex, $from . $filename) )
				continue;

		if ( 'f' == $fileinfo['type'] ) {
			if ( ! $trm_filesystem->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ) {
				// If copy failed, chmod file to 0644 and try again.
				$trm_filesystem->chmod($to . $filename, 0644);
				if ( ! $trm_filesystem->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) )
					return new TRM_Error('copy_failed', __('Could not copy file.'), $to . $filename);
			}
		} elseif ( 'd' == $fileinfo['type'] ) {
			if ( !$trm_filesystem->is_dir($to . $filename) ) {
				if ( !$trm_filesystem->mkdir($to . $filename, FS_CHMOD_DIR) )
					return new TRM_Error('mkdir_failed', __('Could not create directory.'), $to . $filename);
			}
			$result = _copy_dir($from . $filename, $to . $filename, $skip_list);
			if ( is_trm_error($result) )
				return $result;
		}
	}
	return true;
}

?>
