(function ($) {
$(function () {
	
var MedDocumentHandler = function () {
	$container = $(".med_controls_container");
	
	var createMarkup = function () {
		var html = '<div id="med_tmp_document"></div>' +
			'<ul id="med_tmp_document_list"></ul>'
		;
		$container.append(html);

		var uploader = new qq.FileUploader({
			"element": $('#med_tmp_document').get(0),
			"listElement": $('#med_tmp_document_list')[0],
			"allowedExtensions": _medDocumentsAllowedExtensions,
			"action": ajaxurl,
			"params": {
				"action": "med_preview_document"
			},
			"onComplete": createDocumentPreview
		});
		
	};
	
	var createDocumentPreview = function (id, fileName, resp) {
		if ("error" in resp) return false;
		var html = '<img class="med_preview_document_item" src="' + resp.icon + '" /> ' + resp.file + '<br />' +
			'<input type="hidden" class="med_documents_to_add" name="med_documents[]" value="' + resp.file + '" />';
		$('.med_preview_container').append(html);
		$('.med_action_container').html(
			'<p><input type="button" class="button-primary med_primary_button" id="med_submit" value="' + TRS_DThemeMedDocs.add_documents + '" /> ' +
			'<input type="button" class="button" id="med_cancel" value="' + TRS_DThemeMed.cancel + '" /></p>'
		);
		$("#med_cancel_action").hide();
	};
	
	var removeTempDocuments = function (rti_callback) {
		var $docs = $('input.med_documents_to_add');
		if (!$docs.length) return rti_callback();
		$.post(ajaxurl, {"action":"med_remove_temp_documents", "data": $docs.serialize().replace(/%5B%5D/g, '[]')}, function (data) {
			rti_callback();
		});
	};
	
	var processForSave = function () {
		var $docs = $('input.med_documents_to_add');
		var docArr = [];
		$docs.each(function () {
			docArr[docArr.length] = $(this).val();
		});
		return {
			"med_documents": docArr//$imgs.serialize().replace(/%5B%5D/g, '[]')
		};
	};
	
	var init = function () {
		$container.empty();
		$('.med_preview_container').empty();
		$('.med_action_container').empty();
		$('#aw-post-submit').hide();
		createMarkup();
	};
	
	var destroy = function () {
		removeTempDocuments(function() {
			$container.empty(); 
			$('.med_preview_container').empty(); 	
			$('.med_action_container').empty();
			$('#aw-post-submit').show();
		});
	};
	
	removeTempDocuments(init);
	
	return {"destroy": destroy, "get": processForSave};
};

$(".med_toolbar_container").append(
	'&nbsp;' +
	'<a href="#documents" title="' + TRS_DThemeMedDocs.add_documents + '" class="med_toolbarItem" id="med_addDocuments"><span>' + TRS_DThemeMedDocs.add_documents + '</span></a>'
);

$('#med_addDocuments').click(function () {
	if (_medActiveHandler) _medActiveHandler.destroy();
	var group_id = $('#field-post-in').length ? $('#field-post-in').val() : 0;
	if (parseInt(group_id)) {
		_medActiveHandler = new MedDocumentHandler();
		$("#med_cancel_action").show();
	} else {
		alert(TRS_DThemeMedDocs.no_group_selected);
	}
	return false;
});

});
})(jQuery);