(function() {
	tinymce.create('tinymce.plugins.trmLink', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var disabled = true;

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('TRM_Link', function() {
				if ( disabled )
					return;
				ed.windowManager.open({
					id : 'trm-link',
					width : 480,
					height : "auto",
					trmDialog : true,
					title : ed.getLang('advlink.link_desc')
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});

			// Register example button
			ed.addButton('link', {
				title : ed.getLang('advanced.link_desc'),
				cmd : 'TRM_Link'
			});

			ed.addShortcut('alt+shift+a', ed.getLang('advanced.link_desc'), 'TRM_Link');

			ed.onNodeChange.add(function(ed, cm, n, co) {
				disabled = co && n.nodeName != 'A';
			});
		},
		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'trendr Link Dialog',
				author : 'trendr',
				authorurl : 'http://',
				infourl : '',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('trmlink', tinymce.plugins.trmLink);
})();