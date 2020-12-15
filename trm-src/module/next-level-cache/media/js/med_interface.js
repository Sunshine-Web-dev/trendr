
var _medActiveHandler = false;

(function($){
$(function() {

var $form;
var $text;
var $textContainer;


/**
 * Video insertion/preview handler.
 */
var MedVideoHandler = function () {
	$container = $(".med_controls_container");

	var resize = function () {
		$('#med_video_url').width($container.width());
	};

	var createMarkup = function () {
		var html = '<input type="text" id="med_video_url" name="med_video_url" placeholder="' + lanMed.paste_video_url + '" value="" />' +
			'<input type="button" id="med_video_url_preview" value="' + lanMed.preview + '" />';
		$container.empty().append(html);

		$(window).off("resize.med").on("resize.med", resize);
		resize();
		$('#med_video_url').focus(function () {
			$(this)
				.select()
				.addClass('changed')
			;
		});

		$('#med_video_url').keypress(function (e) {
			if (13 != e.which) return true;
			createVideoPreview();
			return false;
		});
		$('#med_video_url').change(createVideoPreview);
		$('#med_video_url_preview').click(createVideoPreview);
	};

	var createVideoPreview = function () {
		var url = $('#med_video_url').val();
		if (!url) return false;
		$('.med_preview_container').html('<div class="med_waiting"></div>');
		$.post(ajaxurl, {"action":"med_preview_video", "data":url}, function (data) {
			$('.med_preview_container').empty().html(data);
			$('.med_action_container').html(
				'<p><input type="button" class="button-primary med_primary_button" id="med_submit" value="' + lanMed.add_video + '" /> ' +
				'<input type="button" class="button" id="med_cancel" value="' + lanMed.cancel + '" /></p>'
			);
			$("#med_cancel_action").hide();
		});
	};

	var processForSave = function () {
		return {
			"med_video_url": $("#med_video_url").val()
		};
	};

	var init = function () {
		$('#submit-post').hide();
		createMarkup();
	};

	var destroy = function () {
		$container.empty();
		$('.med_preview_container').empty();
		$('.med_action_container').empty();
		$('#submit-post').show();
		$(window).off("resize.med");
	};

	init ();

	return {"destroy": destroy, "get": processForSave};
};


/**
 * Link insertion/preview handler.
 */
var MedLinkHandler = function () {
	$container = $(".med_controls_container");

	var resize = function () {
		$('#med_link_preview_url').width($container.width());
	};

	var createMarkup = function () {
		var html = '<input type="text" id="med_link_preview_url" name="med_link_preview_url" placeholder="' + lanMed.paste_link_url + '" value="" />' +
			'<input type="button" id="med_link_url_preview" value="' + lanMed.preview + '" />';
		$container.empty().append(html);

		$(window).off("resize.med").on("resize.med", resize);
		resize();
		$('#med_link_preview_url').focus(function () {
			$(this)
				.select()
				.addClass('changed')
			;
		});

		$('#med_link_preview_url').keypress(function (e) {
			if (13 != e.which) return true;
			createLinkPreview();
			return false;
		});
		$('#med_link_preview_url').change(createLinkPreview);
		$('#med_link_url_preview').click(createLinkPreview);
	};

	var createPreviewMarkup = function (data) {
		if (!data.url) {
			$('.med_preview_container').empty().html(data.title);
			return false;
		}
		var imgs = '';
		$.each(data.images, function(idx, img) {
			if (!img) return true;
			var url = img.match(/^http/) ? img : data.url + '/' + img;
			imgs += '<img class="med_link_preview_image" src="' + url + '" />';
		});
		var html = '<table border="0">' +
			'<tr>' +
				'<td>' +
					'<div class="med_link_preview_container">' +
						imgs +
						'<input type="hidden" name="med_link_img" value="" />' +
					'</div>' +
				'</td>' +
				'<td>' +
					'<div class="med_link_preview_title">' + data.title + '</div>' +
					'<input type="hidden" name="med_link_title" value="' + data.title + '" />' +
					'<div class="med_link_preview_url">' + data.url + '</div>' +
					'<input type="hidden" name="med_link_url" value="' + data.url + '" />' +
					'<div class="med_link_preview_body">' + data.text + '</div>' +
					'<input type="hidden" name="med_link_body" value="' + data.text + '" />' +
					'<div class="med_thumbnail_chooser">' +
						'<span class="med_left"><img class="med_thumbnail_chooser_left" src="' + _med_data.root_url + '/img/system/left.gif" />&nbsp;</span>' +
						'<span class="med_thumbnail_chooser_label">' + lanMed.choose_thumbnail + '</span>' +
						'<span class="med_right">&nbsp;<img class="med_thumbnail_chooser_right" src="' + _med_data.root_url + '/img/system/right.gif" /></span>' +
						'<br /><input type="checkbox" id="med_link_no_thumbnail" /> <label for="med_link_no_thumbnail">' + lanMed.no_thumbnail + '</label>' +
					'</div>' +
				'</td>' +
			'</tr>' +
		'</table>';
		$('.med_preview_container').empty().html(html);
		$('.med_action_container').html(
			'<p><input type="button" class="button-primary med_primary_button" id="med_submit" value="' + lanMed.add_link + '" /> ' +
			'<input type="button" class="button" id="med_cancel" value="' + lanMed.cancel + '" /></p>'
		);
		$("#med_cancel_action").hide();

		$('img.med_link_preview_image').hide();
		$('img.med_link_preview_image').first().show();
		$('input[name="med_link_img"]').val($('img.med_link_preview_image').first().attr('src'));

		//$('.med_thumbnail_chooser_left').click(function () {
		$('.med_thumbnail_chooser .med_left').click(function () {
			var $cur = $('img.med_link_preview_image:visible');
			var $prev = $cur.prev('.med_link_preview_image');
			if ($prev.length) {
				$cur.hide();
				$prev
					.width($('.med_link_preview_container').width())
					.show();
				$('input[name="med_link_img"]').val($prev.attr('src'));
			}
			return false;
		});
		//$('.med_thumbnail_chooser_right').click(function () {
		$('.med_thumbnail_chooser .med_right').click(function () {
			var $cur = $('img.med_link_preview_image:visible');
			var $next = $cur.next('.med_link_preview_image');
			if ($next.length) {
				$cur.hide();
				$next
					.width($('.med_link_preview_container').width())
					.show();
				$('input[name="med_link_img"]').val($next.attr('src'));
			}
			return false;
		});
		$("#med_link_no_thumbnail").click(function () {
			if ($("#med_link_no_thumbnail").is(":checked")) {
				$('img.med_link_preview_image:visible').hide();
				$('input[name="med_link_img"]').val('');
				$(".med_left, .med_right, .med_thumbnail_chooser_label").hide();
			} else {
				var $img = $('img.med_link_preview_image:first');
				$img.show();
				$(".med_left, .med_right, .med_thumbnail_chooser_label").show();
				$('input[name="med_link_img"]').val($img.attr('src'));
			}

		});
	};

	var createLinkPreview = function () {
		var url = $('#med_link_preview_url').val();
		if (!url) return false;
		$('.med_preview_container').html('<div class="med_waiting"></div>');
		$.post(ajaxurl, {"action":"med_preview_link", "data":url}, function (data) {
			createPreviewMarkup(data);
		});
	};

	var processForSave = function () {
		return {
			"med_link_url": $('input[name="med_link_url"]').val(),
			"med_link_image": $('input[name="med_link_img"]').val(),
			"med_link_title": $('input[name="med_link_title"]').val(),
			"med_link_body": $('input[name="med_link_body"]').val()
		};
	};

	var init = function () {
		$('#submit-post').hide();
		createMarkup();
	};

	var destroy = function () {
		$container.empty();
		$('.med_preview_container').empty();
		$('.med_action_container').empty();
		$('#submit-post').show();
		$(window).off("resize.med");
	};

	init ();

	return {"destroy": destroy, "get": processForSave};
};


/**
 * Photos insertion/preview handler.
 */
var MedPhotoHandler = function () {
	$container = $(".med_controls_container");

	var createMarkup = function () {
		var html = '<div id="med_tmp_photo"> </div>' +
			'<ul id="med_tmp_photo_list"></ul>' +
			'<input type="button" id="med_add_remote_image" value="' + lanMed.add_remote_image + '" /><div id="med_remote_image_container"></div>' +
			'<input type="button" id="med_remote_image_preview" value="' + lanMed.preview + '" />';
		$container.append(html);

		var uploader = new qq.FileUploader({
			"element": $('#med_tmp_photo')[0],
			"listElement": $('#med_tmp_photo_list')[0],
			"allowedExtensions": ['jpg', 'jpeg', 'png', 'gif','mp4'],
			"action": ajaxurl,
			"params": {
				"action": "med_preview_photo"
			},
            //modified code added resize
			resize : true,
			maxwidth : 1200,
			quality : 0.9,			
			"onSubmit": function (id) {
				if (!parseInt(lanMed._max_images, 10)) return true; // Skip check
				id = parseInt(id, 10);
				if (!id) id = $("img.med_preview_photo_item").length;
				if (!id) return true;
				if (id < parseInt(lanMed._max_images, 10)) return true;
				if (!$("#med-too_many_photos").length) $("#med_tmp_photo").append(
					'<p id="med-too_many_photos">' + lanMed.images_limit_exceeded + '</p>'
				);
				return false;
			},
			"onComplete": createPhotoPreview,
			template: '<div class="qq-uploader">' +
                '<div class="qq-upload-drop-area"><span>' + lanMed.drop_files + '</span></div>' +
                '<div class="qq-upload-button">' + lanMed.upload_file + '</div>' +
                '<ul class="qq-upload-list"></ul>' +
             '</div>'
		});

		$("#med_remote_image_preview").hide();
		$("#med_tmp_photo").click(function () {
			if ($("#med_add_remote_image").is(":visible")) $("#med_add_remote_image").hide();
		});
		$("#med_add_remote_image").click(function () {
			if (!$("#med_remote_image_preview").is(":visible")) $("#med_remote_image_preview").show();
			if ($("#med_tmp_photo").is(":visible")) $("#med_tmp_photo").hide();
			$("#med_add_remote_image").val(lanMed.add_another_remote_image);
			$("#med_remote_image_container").append(
				'<input type="text" class="med_remote_image" size="64" value="" /><br />'
			);
			$("#med_remote_image_container .med_remote_image").width($container.width());
		});
		$(document).on('change', "#med_remote_image_container .med_remote_image", createRemoteImagePreview);
		$("#med_remote_image_preview").click(createRemoteImagePreview);
	};

	var createRemoteImagePreview = function () {
		var imgs = [];
		$("#med_remote_image_container .med_remote_image").each(function () {
			imgs[imgs.length] = $(this).val();
		});
		$.post(ajaxurl, {"action":"med_preview_remote_image", "data":imgs}, function (data) {
			var html = '';
			$.each(data, function() {
				html += '<img class="med_preview_photo_item" src="' + this + '" width="80px" />' +
				'<input type="hidden" class="med_photos_to_add" name="med_photos[]" value="' + this + '" />';
			});
			$('.med_preview_container').html(html);
		});
		$('.med_action_container').html(
			'<p><input type="button" class="button-primary med_primary_button" id="med_submit" value="' + lanMed.add_photos + '" /> ' +
			'<input type="button" class="button" id="med_cancel" value="' + lanMed.cancel + '" /></p>'
		);
		$("#med_cancel_action").hide();
	};

	var createPhotoPreview = function (id, fileName, resp) {
		if ("error" in resp) return false;
		var html = '<img class="med_preview_photo_item" src="' + _med_data.temp_img_url + resp.file + '" width="80px" />' +
			'<input type="hidden" class="med_photos_to_add" name="med_photos[]" value="' + resp.file + '" />';
		$('.med_preview_container').append(html);
		$('.med_action_container').html(
			'<p><input type="button" class="button-primary med_primary_button" id="med_submit" value="' + lanMed.add_photos + '" /> ' +
			'<input type="button" class="button" id="med_cancel" value="' + lanMed.cancel + '" /></p>'
		);
		$("#med_cancel_action").hide();
	};

	var removeTempImages = function (rti_callback) {
		var $imgs = $('input.med_photos_to_add');
		if (!$imgs.length) return rti_callback();
		$.post(ajaxurl, {"action":"med_remove_temp_images", "data": $imgs.serialize().replace(/%5B%5D/g, '[]')}, function (data) {
			rti_callback();
		});
	};

	var processForSave = function () {
		var $imgs = $('input.med_photos_to_add');
		var imgArr = [];
		$imgs.each(function () {
			imgArr[imgArr.length] = $(this).val();
		});
		return {
			"med_photos": imgArr//$imgs.serialize().replace(/%5B%5D/g, '[]')
		};
	};

	var init = function () {
		$container.empty();
		$('.med_preview_container').empty();
		$('.med_action_container').empty();
		$('#submit-post').hide();
		createMarkup();
	};

	var destroy = function () {
		removeTempImages(function() {
			$container.empty();
			$('.med_preview_container').empty();
			$('.med_action_container').empty();
			$('#submit-post').show();
		});
	};

	removeTempImages(init);

	return {"destroy": destroy, "get": processForSave};
};


/* === End handlers  === */


/**
 * Main interface markup creation.
 */
function createMarkup () {
	var html = '<div class="med_actions_container med-theme-' + _med_data.theme.replace(/[^-_a-z0-9]/ig, '') + ' med-alignment-' + _med_data.alignment.replace(/[^-_a-z0-9]/ig, '') + '">' +
		'<div class="med_toolbar_container">' +
			'<a href="#photos" class="med_toolbarItem" title="' + lanMed.add_photos + '" id="med_addPhotos"><span>' + lanMed.add_photos + '</span></a>' +
			'&nbsp;' +
			'<a href="#videos" class="med_toolbarItem" title="' + lanMed.add_videos + '" id="med_addVideos"><span>' + lanMed.add_videos + '</span></a>' +
			'&nbsp;' +
			'<a href="#links" class="med_toolbarItem" title="' + lanMed.add_links + '" id="med_addLinks"><span>' + lanMed.add_links + '</span></a>' +
		'</div>' +
		'<div class="med_controls_container">' +
		'</div>' +
		'<div class="med_preview_container">' +
		'</div>' +
		'<div class="med_action_container">' +
		'</div>' +
		'<input type="button" id="med_cancel_action" value="' + lanMed.cancel + '" style="display:none" />' +
	'</div>';
	$form.wrap('<div class="med_form_container" />');
	$textContainer.after(html);
}


/**
 * Initializes the main interface.
 */
function init () {
	$form = $("#post-box");
	$text = $form.find('textarea[name="field"]');
	$textContainer = $form.find('#post-inner');
	createMarkup();
	$('#med_addPhotos').click(function () {
		if (_medActiveHandler) _medActiveHandler.destroy();
		_medActiveHandler = new MedPhotoHandler();
		$("#med_cancel_action").show();
		return false;
	});
	$('#med_addLinks').click(function () {
		if (_medActiveHandler) _medActiveHandler.destroy();
		_medActiveHandler = new MedLinkHandler();
		$("#med_cancel_action").show();
		return false;
	});
	$('#med_addVideos').click(function () {
		if (_medActiveHandler) _medActiveHandler.destroy();
		_medActiveHandler = new MedVideoHandler();
		$("#med_cancel_action").show();
		return false;
	});
	$('#med_cancel_action').click(function () {
		$(".med_toolbarItem.med_active").removeClass("med_active");
		_medActiveHandler.destroy();
		$("#med_cancel_action").hide();
		return false;
	});
	$(".med_toolbarItem").click(function () {
		$(".med_toolbarItem.med_active").removeClass("med_active");
		$(this).addClass("med_active");
	});
	$(document).on('click', '#med_submit', function () {
		var params = _medActiveHandler.get();
		var group_id = $('#whats-new-post-in').length ? $('#whats-new-post-in').val() : 0;
		$.post(ajaxurl, {
			"action": "med_update_activity_contents",
			"data": params,
			"content": $text.val(),
			"group_id": group_id
		}, function (data) {
			_medActiveHandler.destroy();
			$text.val('');
			$('#publish').prepend(data.activity);
			/**
			 * Handle image scaling in previews.
			 */
			$(".med_final_link img").each(function () {
				$(this).width($(this).parents('div').width());
			});
		});
	});
	$(document).on('click', '#med_cancel', function () {
		$(".med_toolbarItem.med_active").removeClass("med_active");
		_medActiveHandler.destroy();
	});
}

// Only initialize if we're supposed to.
/*
if (
	!('ontouchstart' in document.documentElement)
	||
	('ontouchstart' in document.documentElement && (/iPhone|iPod|iPad/i).test(navigator.userAgent))
	) {
	if ($("#post-box").is(":visible")) init();
}
*/
// Meh, just do it - newish Droids seem to work fine.
if ($("#post-box").is(":visible")) init();

/**
 * Handle image scaling in previews.
 */
$(".med_final_link img").each(function () {
	$(this).width($(this).parents('div').width());
});

});
})(jQuery);