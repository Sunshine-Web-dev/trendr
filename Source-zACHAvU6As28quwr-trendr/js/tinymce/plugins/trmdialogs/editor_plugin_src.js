/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	tinymce.create('tinymce.plugins.TRMDialogs', {
		init : function(ed, url) {
			tinymce.create('tinymce.TRMWindowManager:tinymce.InlineWindowManager', {
				TRMWindowManager : function(ed) {
					this.parent(ed);
				},

				open : function(f, p) {
					var t = this, element;

					if ( ! f.trmDialog )
						return this.parent( f, p );
					else if ( ! f.id )
						return;

					element = jQuery('#' + f.id);
					if ( ! element.length )
						return;

					t.features = f;
					t.params = p;
					t.onOpen.dispatch(t, f, p);
					t.element = t.windows[ f.id ] = element;

					// Store selection
					t.bookmark = t.editor.selection.getBookmark(1);

					// Create the dialog if necessary
					if ( ! element.data('trmdialog') ) {
						element.trmdialog({
							title: f.title,
							width: f.width,
							height: f.height,
							modal: true,
							dialogClass: 'trm-dialog',
							zIndex: 300000
						});
					}

					element.trmdialog('open');
				},
				close : function() {
					if ( ! this.features.trmDialog )
						return this.parent.apply( this, arguments );

					this.element.trmdialog('close');
				}
			});

			// Replace window manager
			ed.onBeforeRenderUI.add(function() {
				ed.windowManager = new tinymce.TRMWindowManager(ed);
			});
		},

		getInfo : function() {
			return {
				longname : 'TRMDialogs',
				author : 'trendr',
				authorurl : 'http://',
				infourl : 'http://',
				version : '0.1'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('trmdialogs', tinymce.plugins.TRMDialogs);
})();
