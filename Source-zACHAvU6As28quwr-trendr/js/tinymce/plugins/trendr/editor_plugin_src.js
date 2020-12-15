/**
 * trendr plugin.
 */

(function() {
	var DOM = tinymce.DOM;

	tinymce.create('tinymce.plugins.trendr', {
		mceTout : 0,

		init : function(ed, url) {
			var t = this, tbId = ed.getParam('trendr_adv_toolbar', 'toolbar2'), last = 0, moreHTML, nextpageHTML, closeOnClick;
			moreHTML = '<img src="' + url + '/img/trans.gif" class="mceTRMmore mceItemNoResize" title="'+ed.getLang('trendr.trm_more_alt')+'" />';
			nextpageHTML = '<img src="' + url + '/img/trans.gif" class="mceTRMnextpage mceItemNoResize" title="'+ed.getLang('trendr.trm_page_alt')+'" />';

			if ( getUserSetting('hidetb', '0') == '1' )
				ed.settings.trendr_adv_hidden = 0;

			// Hides the specified toolbar and resizes the iframe
			ed.onPostRender.add(function() {
				var adv_toolbar = ed.controlManager.get(tbId);
				if ( ed.getParam('trendr_adv_hidden', 1) && adv_toolbar ) {
					DOM.hide(adv_toolbar.id);
					t._resizeIframe(ed, tbId, 28);
				}
			});

			// Register commands
			ed.addCommand('TRM_More', function() {
				ed.execCommand('mceInsertContent', 0, moreHTML);
			});

			ed.addCommand('TRM_Page', function() {
				ed.execCommand('mceInsertContent', 0, nextpageHTML);
			});

			ed.addCommand('TRM_Help', function() {
				ed.windowManager.open({
					url : tinymce.baseURL + '/trm-mce-help.php',
					width : 450,
					height : 420,
					inline : 1
				});
			});

			ed.addCommand('TRM_Adv', function() {
				var cm = ed.controlManager, id = cm.get(tbId).id;

				if ( 'undefined' == id )
					return;

				if ( DOM.isHidden(id) ) {
					cm.setActive('trm_adv', 1);
					DOM.show(id);
					t._resizeIframe(ed, tbId, -28);
					ed.settings.trendr_adv_hidden = 0;
					setUserSetting('hidetb', '1');
				} else {
					cm.setActive('trm_adv', 0);
					DOM.hide(id);
					t._resizeIframe(ed, tbId, 28);
					ed.settings.trendr_adv_hidden = 1;
					setUserSetting('hidetb', '0');
				}
			});

			ed.addCommand('TRM_Medialib', function() {
				var id = ed.getParam('trm_fullscreen_editor_id') || ed.getParam('fullscreen_editor_id') || ed.id,
					link = tinymce.DOM.select('#trm-' + id + '-media-buttons a.thickbox');

				if ( link && link[0] )
					link = link[0];
				else
					return;

				tb_show('', link.href);
				tinymce.DOM.setStyle( ['TB_overlay','TB_window','TB_load'], 'z-index', '999999' );
			});

			// Register buttons
			ed.addButton('trm_more', {
				title : 'trendr.trm_more_desc',
				cmd : 'TRM_More'
			});

			ed.addButton('trm_page', {
				title : 'trendr.trm_page_desc',
				image : url + '/img/page.gif',
				cmd : 'TRM_Page'
			});

			ed.addButton('trm_help', {
				title : 'trendr.trm_help_desc',
				cmd : 'TRM_Help'
			});

			ed.addButton('trm_adv', {
				title : 'trendr.trm_adv_desc',
				cmd : 'TRM_Adv'
			});

			// Add Media button
			ed.addButton('add_media', {
				title : 'trendr.add_media',
				image : url + '/img/image.gif',
				cmd : 'TRM_Medialib'
			});

			// Add Media buttons to fullscreen and handle align buttons for image captions
			ed.onBeforeExecCommand.add(function(ed, cmd, ui, val, o) {
				var DOM = tinymce.DOM, n, DL, DIV, cls, a, align;
				if ( 'mceFullScreen' == cmd ) {
					if ( 'mce_fullscreen' != ed.id && DOM.select('a.thickbox').length )
						ed.settings.theme_advanced_buttons1 += ',|,add_media';
				}

				if ( 'JustifyLeft' == cmd || 'JustifyRight' == cmd || 'JustifyCenter' == cmd ) {
					n = ed.selection.getNode();

					if ( n.nodeName == 'IMG' ) {
						align = cmd.substr(7).toLowerCase();
						a = 'align' + align;
						DL = ed.dom.getParent(n, 'dl.trm-caption');
						DIV = ed.dom.getParent(n, 'div.mceTemp');

						if ( DL && DIV ) {
							cls = ed.dom.hasClass(DL, a) ? 'alignnone' : a;
							DL.className = DL.className.replace(/align[^ '"]+\s?/g, '');
							ed.dom.addClass(DL, cls);

							if (cls == 'aligncenter')
								ed.dom.addClass(DIV, 'mceIEcenter');
							else
								ed.dom.removeClass(DIV, 'mceIEcenter');

							o.terminate = true;
							ed.execCommand('mceRepaint');
						} else {
							if ( ed.dom.hasClass(n, a) )
								ed.dom.addClass(n, 'alignnone');
							else
								ed.dom.removeClass(n, 'alignnone');
						}
					}
				}
			});

			ed.onInit.add(function(ed) {
				var bodyClass = ed.getParam('body_class', ''), body = ed.getBody();

				// add body classes
				if ( bodyClass )
					bodyClass = bodyClass.split(' ');
				else
					bodyClass = [];

				if ( ed.getParam('directionality', '') == 'rtl' )
					bodyClass.push('rtl');

				if ( tinymce.isIE9 )
					bodyClass.push('ie9');
				else if ( tinymce.isIE8 )
					bodyClass.push('ie8');
				else if ( tinymce.isIE7 )
					bodyClass.push('ie7');

				if ( ed.id != 'trm_mce_fullscreen' && ed.id != 'mce_fullscreen' )
					bodyClass.push('trm-editor');
				else if ( ed.id == 'mce_fullscreen' )
					bodyClass.push('mce-fullscreen');

				tinymce.each( bodyClass, function(cls){
					if ( cls )
						ed.dom.addClass(body, cls);
				});

				// make sure these run last
				ed.onNodeChange.add( function(ed, cm, e) {
					var DL;

					if ( e.nodeName == 'IMG' ) {
						DL = ed.dom.getParent(e, 'dl.trm-caption');
					} else if ( e.nodeName == 'DIV' && ed.dom.hasClass(e, 'mceTemp') ) {
						DL = e.firstChild;

						if ( ! ed.dom.hasClass(DL, 'trm-caption') )
							DL = false;
					}

					if ( DL ) {
						if ( ed.dom.hasClass(DL, 'alignleft') )
							cm.setActive('justifyleft', 1);
						else if ( ed.dom.hasClass(DL, 'alignright') )
							cm.setActive('justifyright', 1);
						else if ( ed.dom.hasClass(DL, 'aligncenter') )
							cm.setActive('justifycenter', 1);
					}
				});

				// remove invalid parent paragraphs when pasting HTML and/or switching to the HTML editor and back
				ed.onBeforeSetContent.add(function(ed, o) {
					if ( o.content ) {
						o.content = o.content.replace(/<p>\s*<(p|div|ul|ol|dl|table|blockquote|h[1-6]|fieldset|pre|address)( [^>]*)?>/gi, '<$1$2>');
						o.content = o.content.replace(/<\/(p|div|ul|ol|dl|table|blockquote|h[1-6]|fieldset|pre|address)>\s*<\/p>/gi, '</$1>');
					}
				});
			});

			// Word count
			if ( 'undefined' != typeof(jQuery) ) {
				ed.onKeyUp.add(function(ed, e) {
					var k = e.keyCode || e.charCode;

					if ( k == last )
						return;

					if ( 13 == k || 8 == last || 46 == last )
						jQuery(document).triggerHandler('trmcountwords', [ ed.getContent({format : 'raw'}) ]);

					last = k;
				});
			};

			// keep empty paragraphs :(
			ed.onSaveContent.addToTop(function(ed, o) {
				o.content = o.content.replace(/<p>(<br ?\/?>|\u00a0|\uFEFF)?<\/p>/g, '<p>&nbsp;</p>');
			});

			ed.onSaveContent.add(function(ed, o) {
				if ( ed.getParam('trmautop', true) && typeof(switchEditors) == 'object' ) {
					if ( ed.isHidden() )
						o.content = o.element.value;
					else
						o.content = switchEditors.pre_trmautop(o.content);
				}
			});

			/* disable for now
			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t._setEmbed(o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if ( o.get )
					o.content = t._getEmbed(o.content);
			});
			*/

			// Add listeners to handle more break
			t._handleMoreBreak(ed, url);

			// Add custom shortcuts
			ed.addShortcut('alt+shift+c', ed.getLang('justifycenter_desc'), 'JustifyCenter');
			ed.addShortcut('alt+shift+r', ed.getLang('justifyright_desc'), 'JustifyRight');
			ed.addShortcut('alt+shift+l', ed.getLang('justifyleft_desc'), 'JustifyLeft');
			ed.addShortcut('alt+shift+j', ed.getLang('justifyfull_desc'), 'JustifyFull');
			ed.addShortcut('alt+shift+q', ed.getLang('blockquote_desc'), 'mceBlockQuote');
			ed.addShortcut('alt+shift+u', ed.getLang('bullist_desc'), 'InsertUnorderedList');
			ed.addShortcut('alt+shift+o', ed.getLang('numlist_desc'), 'InsertOrderedList');
			ed.addShortcut('alt+shift+d', ed.getLang('striketrough_desc'), 'Strikethrough');
			ed.addShortcut('alt+shift+n', ed.getLang('spellchecker.desc'), 'mceSpellCheck');
			ed.addShortcut('alt+shift+a', ed.getLang('link_desc'), 'mceLink');
			ed.addShortcut('alt+shift+s', ed.getLang('unlink_desc'), 'unlink');
			ed.addShortcut('alt+shift+m', ed.getLang('image_desc'), 'TRM_Medialib');
			ed.addShortcut('alt+shift+g', ed.getLang('fullscreen.desc'), 'mceFullScreen');
			ed.addShortcut('alt+shift+z', ed.getLang('trm_adv_desc'), 'TRM_Adv');
			ed.addShortcut('alt+shift+h', ed.getLang('help_desc'), 'TRM_Help');
			ed.addShortcut('alt+shift+t', ed.getLang('trm_more_desc'), 'TRM_More');
			ed.addShortcut('alt+shift+p', ed.getLang('trm_page_desc'), 'TRM_Page');
			ed.addShortcut('ctrl+s', ed.getLang('save_desc'), function(){if('function'==typeof autosave)autosave();});

			if ( tinymce.isWebKit ) {
				ed.addShortcut('alt+shift+b', ed.getLang('bold_desc'), 'Bold');
				ed.addShortcut('alt+shift+i', ed.getLang('italic_desc'), 'Italic');
			}

			ed.onInit.add(function(ed) {
				tinymce.dom.Event.add(ed.getWin(), 'scroll', function(e) {
					ed.plugins.trendr._hideButtons();
				});
				tinymce.dom.Event.add(ed.getBody(), 'dragstart', function(e) {
					ed.plugins.trendr._hideButtons();
				});
			});

			ed.onBeforeExecCommand.add(function(ed, cmd, ui, val) {
				ed.plugins.trendr._hideButtons();
			});

			ed.onSaveContent.add(function(ed, o) {
				ed.plugins.trendr._hideButtons();
			});

			ed.onMouseDown.add(function(ed, e) {
				if ( e.target.nodeName != 'IMG' )
					ed.plugins.trendr._hideButtons();
			});

			closeOnClick = function(e){
				var id;

				if ( e.target.id == 'mceModalBlocker' || e.target.className == 'ui-widget-overlay' ) {
					for ( id in ed.windowManager.windows ) {
						ed.windowManager.close(null, id);
					}
				}
			}

			// close popups when clicking on the background
			tinymce.dom.Event.remove(document.body, 'click', closeOnClick);
			tinymce.dom.Event.add(document.body, 'click', closeOnClick);
		},

		getInfo : function() {
			return {
				longname : 'trendr Plugin',
				author : 'trendr', // add Moxiecode?
				authorurl : 'http://',
				infourl : 'http://',
				version : '3.0'
			};
		},

		// Internal functions
		_setEmbed : function(c) {
			return c.replace(/\[embed\]([\s\S]+?)\[\/embed\][\s\u00a0]*/g, function(a,b){
				return '<img width="300" height="200" src="' + tinymce.baseURL + '/plugins/trendr/img/trans.gif" class="trm-oembed mceItemNoResize" alt="'+b+'" title="'+b+'" />';
			});
		},

		_getEmbed : function(c) {
			return c.replace(/<img[^>]+>/g, function(a) {
				if ( a.indexOf('class="trm-oembed') != -1 ) {
					var u = a.match(/alt="([^\"]+)"/);
					if ( u[1] )
						a = '[embed]' + u[1] + '[/embed]';
				}
				return a;
			});
		},

		_showButtons : function(n, id) {
			var ed = tinyMCE.activeEditor, p1, p2, vp, DOM = tinymce.DOM, X, Y;

			vp = ed.dom.getViewPort(ed.getWin());
			p1 = DOM.getPos(ed.getContentAreaContainer());
			p2 = ed.dom.getPos(n);

			X = Math.max(p2.x - vp.x, 0) + p1.x;
			Y = Math.max(p2.y - vp.y, 0) + p1.y;

			DOM.setStyles(id, {
				'top' : Y+5+'px',
				'left' : X+5+'px',
				'display' : 'block'
			});

			if ( this.mceTout )
				clearTimeout(this.mceTout);

			this.mceTout = setTimeout( function(){ed.plugins.trendr._hideButtons();}, 5000 );
		},

		_hideButtons : function() {
			if ( !this.mceTout )
				return;

			if ( document.getElementById('trm_editbtns') )
				tinymce.DOM.hide('trm_editbtns');

			if ( document.getElementById('trm_gallerybtns') )
				tinymce.DOM.hide('trm_gallerybtns');

			clearTimeout(this.mceTout);
			this.mceTout = 0;
		},

		// Resizes the iframe by a relative height value
		_resizeIframe : function(ed, tb_id, dy) {
			var ifr = ed.getContentAreaContainer().firstChild;

			DOM.setStyle(ifr, 'height', ifr.clientHeight + dy); // Resize iframe
			ed.theme.deltaHeight += dy; // For resize cookie
		},

		_handleMoreBreak : function(ed, url) {
			var moreHTML, nextpageHTML;

			moreHTML = '<img src="' + url + '/img/trans.gif" alt="$1" class="mceTRMmore mceItemNoResize" title="'+ed.getLang('trendr.trm_more_alt')+'" />';
			nextpageHTML = '<img src="' + url + '/img/trans.gif" class="mceTRMnextpage mceItemNoResize" title="'+ed.getLang('trendr.trm_page_alt')+'" />';

			// Display morebreak instead if img in element path
			ed.onPostRender.add(function() {
				if (ed.theme.onResolveName) {
					ed.theme.onResolveName.add(function(th, o) {
						if (o.node.nodeName == 'IMG') {
							if ( ed.dom.hasClass(o.node, 'mceTRMmore') )
								o.name = 'trmmore';
							if ( ed.dom.hasClass(o.node, 'mceTRMnextpage') )
								o.name = 'trmpage';
						}

					});
				}
			});

			// Replace morebreak with images
			ed.onBeforeSetContent.add(function(ed, o) {
				if ( o.content ) {
					o.content = o.content.replace(/<!--more(.*?)-->/g, moreHTML);
					o.content = o.content.replace(/<!--nextpage-->/g, nextpageHTML);
				}
			});

			// Replace images with morebreak
			ed.onPostProcess.add(function(ed, o) {
				if (o.get)
					o.content = o.content.replace(/<img[^>]+>/g, function(im) {
						if (im.indexOf('class="mceTRMmore') !== -1) {
							var m, moretext = (m = im.match(/alt="(.*?)"/)) ? m[1] : '';
							im = '<!--more'+moretext+'-->';
						}
						if (im.indexOf('class="mceTRMnextpage') !== -1)
							im = '<!--nextpage-->';

						return im;
					});
			});

			// Set active buttons if user selected pagebreak or more break
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('trm_page', n.nodeName === 'IMG' && ed.dom.hasClass(n, 'mceTRMnextpage'));
				cm.setActive('trm_more', n.nodeName === 'IMG' && ed.dom.hasClass(n, 'mceTRMmore'));
			});
		}
	});

	// Register plugin
	tinymce.PluginManager.add('trendr', tinymce.plugins.trendr);
})();
