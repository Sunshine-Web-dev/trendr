function TRMSetAsThumbnail(c,b){var a=jQuery("a#trm-post-thumbnail-"+c);a.text(setPostThumbnailL10n.saving);jQuery.post(ajaxurl,{action:"set-post-thumbnail",post_id:post_id,thumbnail_id:c,_ajax_nonce:b,cookie:encodeURIComponent(document.cookie)},function(e){var d=window.dialogArguments||opener||parent||top;a.text(setPostThumbnailL10n.setThumbnail);if(e=="0"){alert(setPostThumbnailL10n.error)}else{jQuery("a.trm-post-thumbnail").show();a.text(setPostThumbnailL10n.done);a.fadeOut(2000);d.TRMSetThumbnailID(c);d.TRMSetThumbnailHTML(e)}})};