/**
 * TRM Fullscreen TinyMCE plugin
 *
 * Contains code from Moxiecode Systems AB released under LGPL License http://tinymce.moxiecode.com/license
 */

(function() {
	tinymce.create('tinymce.plugins.trmFullscreenPlugin', {

		init : function(ed, url) {
			var t = this, oldHeight = 0, s = {}, DOM = tinymce.DOM;

			// Register commands
			ed.addCommand('trmFullScreenClose', function() {
				// this removes the editor, content has to be saved first with tinyMCE.execCommand('trmFullScreenSave');
				if ( ed.getParam('trm_fullscreen_is_enabled') ) {
					DOM.win.setTimeout(function() {
						tinyMCE.remove(ed);
						DOM.remove('trm_mce_fullscreen_parent');
						tinyMCE.settings = tinyMCE.oldSettings; // Restore old settings
					}, 10);
				}
			});

			ed.addCommand('trmFullScreenSave', function() {
				var ed = tinyMCE.get('trm_mce_fullscreen'), edd;

				ed.focus();
				edd = tinyMCE.get( ed.getParam('trm_fullscreen_editor_id') );

				edd.setContent( ed.getContent({format : 'raw'}), {format : 'raw'} );
			});

			ed.addCommand('trmFullScreenInit', function() {
				var d, b, fsed;

				ed = tinyMCE.activeEditor;
				d = ed.getDoc();
				b = d.body;

				tinyMCE.oldSettings = tinyMCE.settings; // Store old settings

				tinymce.each(ed.settings, function(v, n) {
					s[n] = v;
				});

				s.id = 'trm_mce_fullscreen';
				s.trm_fullscreen_is_enabled = true;
				s.trm_fullscreen_editor_id = ed.id;
				s.theme_advanced_resizing = false;
				s.theme_advanced_statusbar_location = 'none';
				s.content_css = s.content_css ? s.content_css + ',' + s.trm_fullscreen_content_css : s.trm_fullscreen_content_css;
				s.height = tinymce.isIE ? b.scrollHeight : b.offsetHeight;

				tinymce.each(ed.getParam('trm_fullscreen_settings'), function(v, k) {
					s[k] = v;
				});

				fsed = new tinymce.Editor('trm_mce_fullscreen', s);
				fsed.onInit.add(function(edd) {
					var DOM = tinymce.DOM, buttons = DOM.select('a.mceButton', DOM.get('trm-fullscreen-buttons'));

					if ( !ed.isHidden() )
						edd.setContent( ed.getContent() );
					else
						edd.setContent( switchEditors.trmautop( edd.getElement().value ) );

					setTimeout(function(){ // add last
						edd.onNodeChange.add(function(ed, cm, e){
							tinymce.each(buttons, function(c) {
								var btn, cls;

								if ( btn = DOM.get( 'trm_mce_fullscreen_' + c.id.substr(6) ) ) {
									cls = btn.className;

									if ( cls )
										c.className = cls;
								}
							});
						});
					}, 1000);

					edd.dom.addClass(edd.getBody(), 'trm-fullscreen-editor');
					edd.focus();
				});

				fsed.render();

				if ( 'undefined' != fullscreen ) {
					fsed.dom.bind( fsed.dom.doc, 'mousemove', function(e){
						fullscreen.bounder( 'showToolbar', 'hideToolbar', 2000, e );
					});
				}
			});

			// Register buttons
			if ( 'undefined' != fullscreen ) {
				ed.addButton('trm_fullscreen', {
					title : 'fullscreen.desc',
					onclick : function(){ fullscreen.on(); }
				});
			}

			// END fullscreen
//----------------------------------------------------------------
			// START autoresize

			if ( ed.getParam('fullscreen_is_enabled') || !ed.getParam('trm_fullscreen_is_enabled') )
				return;

			/**
			 * This method gets executed each time the editor needs to resize.
			 */
			function resize() {
				var d = ed.getDoc(), DOM = tinymce.DOM, resizeHeight, myHeight;

				// Get height differently depending on the browser used
				if ( tinymce.isWebKit )
					myHeight = d.body.offsetHeight;
				else
					myHeight = d.body.scrollHeight;

				// Don't make it smaller than 300px
				resizeHeight = (myHeight > 300) ? myHeight : 300;

				// Resize content element
				if ( oldHeight != resizeHeight ) {
					DOM.setStyle(DOM.get(ed.id + '_ifr'), 'height', resizeHeight + 'px');
					oldHeight = resizeHeight;
					ed.getWin().scrollTo(0,0);
				}
			};

			// Add appropriate listeners for resizing content area
			ed.onInit.add(function(ed, l) {
				ed.onChange.add(resize);
				ed.onSetContent.add(resize);
				ed.onPaste.add(resize);
				ed.onKeyUp.add(resize);
				ed.onPostRender.add(resize);

				ed.getBody().style.overflowY = "hidden";
			});

			if (ed.getParam('autoresize_on_init', true)) {
				ed.onLoadContent.add(function(ed, l) {
					// Because the content area resizes when its content CSS loads,
					// and we can't easily add a listener to its onload event,
					// we'll just trigger a resize after a short loading period
					setTimeout(function() {
						resize();
					}, 1200);
				});
			}

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('trmAutoResize', resize);
		},

		getInfo : function() {
			return {
				longname : 'TRM Fullscreen',
				author : 'trendr',
				authorurl : 'http://',
				infourl : '',
				version : '1.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('trmfullscreen', tinymce.plugins.trmFullscreenPlugin);
})();
