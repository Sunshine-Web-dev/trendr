<?php

if ( !defined('ABSPATH') )
	exit;

require(ABSPATH . 'Source-zACHAvU6As28quwr-trendr/version.php');

$man_version = md5( $tinymce_version . $manifest_version );
$mce_ver = "ver=$tinymce_version";

/**
 * Retrieve list of all cacheable TRM files
 *
 * Array format: file, version (optional), bool (whether to use src and set ignoreQuery, defaults to true)
 */
function &get_manifest() {
	global $mce_ver;

	$files = array(
		array('images/align-center.png'),
		array('images/align-left.png'),
		array('images/align-none.png'),
		array('images/align-right.png'),
		array('images/archive-link.png'),
		array('images/blue-grad.png'),
		array('images/bubble_bg.gif'),
		array('images/bubble_bg-rtl.gif'),
		array('images/button-grad.png'),
		array('images/button-grad-active.png'),
		array('images/comment-grey-bubble.png'),
		array('images/date-button.gif'),
		array('images/ed-bg.gif'),
		array('images/fade-butt.png'),
		array('images/fav.png'),
		array('images/fav-arrow.gif'),
		array('images/fav-arrow-rtl.gif'),
		array('images/generic.png'),
		array('images/gray-grad.png'),
		array('images/icons32.png'),
		array('images/icons32-vs.png'),
		array('images/list.png'),
		array('images/trmspin_light.gif'),
		array('images/trmspin_dark.gif'),
		array('images/logo.gif'),
		array('images/logo-ghost.png'),
		array('images/logo-login.gif'),
		array('images/media-button-image.gif'),
		array('images/media-button-music.gif'),
		array('images/media-button-other.gif'),
		array('images/media-button-video.gif'),
		array('images/menu.png'),
		array('images/menu-vs.png'),
		array('images/menu-arrows.gif'),
		array('images/menu-bits.gif'),
		array('images/menu-bits-rtl.gif'),
		array('images/menu-dark.gif'),
		array('images/menu-dark-rtl.gif'),
		array('images/no.png'),
		array('images/required.gif'),
		array('images/resize.gif'),
		array('images/screen-options-right.gif'),
		array('images/screen-options-right-up.gif'),
		array('images/se.png'),
		array('images/star.gif'),
		array('images/toggle-arrow.gif'),
		array('images/toggle-arrow-rtl.gif'),
		array('images/white-grad.png'),
		array('images/white-grad-active.png'),
		array('images/trendr-logo.png'),
		array('images/trm-logo.png'),
		array('images/xit.gif'),
		array('images/yes.png'),
		array('../Source-zACHAvU6As28quwr-trendr/images/crystal/archive.png'),
		array('../Source-zACHAvU6As28quwr-trendr/images/crystal/audio.png'),
		array('../Source-zACHAvU6As28quwr-trendr/images/crystal/code.png'),
		array('../Source-zACHAvU6As28quwr-trendr/images/crystal/default.png'),
		array('../Source-zACHAvU6As28quwr-trendr/images/crystal/document.png'),
		array('../Source-zACHAvU6As28quwr-trendr/images/crystal/interactive.png'),
		array('../Source-zACHAvU6As28quwr-trendr/images/crystal/text.png'),
		array('../Source-zACHAvU6As28quwr-trendr/images/crystal/video.png'),
		array('../Source-zACHAvU6As28quwr-trendr/images/crystal/spreadsheet.png'),
		array('../Source-zACHAvU6As28quwr-trendr/images/rss.png'),
		array('../Source-zACHAvU6As28quwr-trendr/images/blank.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/images/upload.png'),
		array('../Source-zACHAvU6As28quwr-trendr/js/thickbox/loadingAnimation.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/thickbox/tb-close.png'),
	);

	if ( @is_file('../Source-zACHAvU6As28quwr-trendr/js/tinymce/tiny_mce.js') ) :
	$mce = array(
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/trm-tinymce.php', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/tiny_mce.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/langs/trm-langs-en.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/utils/mctabs.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/utils/validate.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/utils/form_utils.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/utils/editable_selects.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/tiny_mce_popup.js', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/editor_template.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/source_editor.htm', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/anchor.htm', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/image.htm', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/link.htm', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/color_picker.htm', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/charmap.htm', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/js/color_picker.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/js/charmap.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/js/image.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/js/link.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/js/source_editor.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/js/anchor.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/skins/trm_theme/ui.css', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/skins/trm_theme/content.css', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/skins/trm_theme/dialog.css', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/fullscreen/editor_plugin.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/fullscreen/fullscreen.htm', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/editor_plugin.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/template.htm', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/skins/clearlooks2/window.css', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/media/editor_plugin.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/media/js/media.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/media/media.htm', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/media/css/content.css', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/media/css/media.css', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/paste/editor_plugin.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/paste/js/pasteword.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/paste/js/pastetext.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/paste/pasteword.htm', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/paste/blank.htm', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/paste/pastetext.htm', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/safari/editor_plugin.js', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/spellchecker/editor_plugin.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/spellchecker/css/content.css', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/tabfocus/editor_plugin.js', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/editor_plugin.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/css/content.css', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmeditimage/editor_plugin.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmeditimage/editimage.html', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmeditimage/js/editimage.js', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmeditimage/css/editimage.css', $mce_ver),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmeditimage/css/editimage-rtl.css', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmgallery/editor_plugin.js', $mce_ver),

		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/img/icons.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/img/colorpicker.jpg'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/img/fm.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/img/gotmoxie.png'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/img/sflogo.png'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/skins/trm_theme/img/butt2.png'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/skins/trm_theme/img/fade-butt.png'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/skins/trm_theme/img/tabs.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/skins/trm_theme/img/down_arrow.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/skins/default/img/progress.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/skins/default/img/menu_check.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/858483/advanced/skins/default/img/menu_arrow.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/skins/clearlooks2/img/drag.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/skins/clearlooks2/img/corners.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/skins/clearlooks2/img/buttons.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/skins/clearlooks2/img/horizontal.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/skins/clearlooks2/img/alert.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/skins/clearlooks2/img/button.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/skins/clearlooks2/img/confirm.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/inlinepopups/skins/clearlooks2/img/vertical.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/media/img/flash.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/media/img/quicktime.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/media/img/realmedia.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/media/img/shockwave.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/media/img/windowsmedia.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/media/img/trans.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/spellchecker/img/wline.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/more.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/more_bug.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/page.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/page_bug.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/toolbars.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/help.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/image.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/media.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/video.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trendr/img/audio.gif'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmeditimage/img/image.png'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmeditimage/img/delete.png'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmgallery/img/delete.png'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmgallery/img/edit.png'),
		array('../Source-zACHAvU6As28quwr-trendr/js/tinymce/module/trmgallery/img/gallery.png')
	);
	$files = array_merge($files, $mce);
	endif;

	return $files;
}
