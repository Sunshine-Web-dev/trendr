var tinymce=null,tinyMCEPopup,tinyMCE,trmImage;tinyMCEPopup={init:function(){var d=this,b,a,f,c,e;a=(""+document.location.search).replace(/^\?/,"").split("&");f={};for(c=0;c<a.length;c++){e=a[c].split("=");f[unescape(e[0])]=unescape(e[1])}if(f.mce_rdomain){document.domain=f.mce_rdomain}b=d.getWin();tinymce=b.tinymce;tinyMCE=b.tinyMCE;d.editor=tinymce.EditorManager.activeEditor;d.params=d.editor.windowManager.params;d.dom=d.editor.windowManager.createInstance("tinymce.dom.DOMUtils",document);d.editor.windowManager.onOpen.dispatch(d.editor.windowManager,window)},getWin:function(){return window.dialogArguments||opener||parent||top},getParam:function(b,a){return this.editor.getParam(b,a)},close:function(){var a=this,b=a.getWin();function c(){b.tb_remove();tinymce=tinyMCE=a.editor=a.dom=a.dom.doc=null}if(tinymce.isOpera){b.setTimeout(c,0)}else{c()}},execCommand:function(d,c,e,b){b=b||{};b.skip_focus=1;this.restoreSelection();return this.editor.execCommand(d,c,e,b)},storeSelection:function(){this.editor.windowManager.bookmark=tinyMCEPopup.editor.selection.getBookmark("simple")},restoreSelection:function(){var a=tinyMCEPopup;if(tinymce.isIE){a.editor.selection.moveToBookmark(a.editor.windowManager.bookmark)}}};tinyMCEPopup.init();trmImage={preInit:function(){var d=tinyMCEPopup.getWin(),c=d.document.styleSheets,a,b;for(b=0;b<c.length;b++){a=c.item(b).href;if(a&&a.indexOf("colors")!=-1){document.write('<link rel="stylesheet" href="'+a+'" type="text/css" media="all" />')}}},I:function(a){return document.getElementById(a)},current:"",link:"",link_rel:"",target_value:"",current_size_sel:"s100",width:"",height:"",align:"",img_alt:"",setTabs:function(b){var a=this;if("current"==b.className){return false}a.I("div_advanced").style.display=("tab_advanced"==b.id)?"block":"none";a.I("div_basic").style.display=("tab_basic"==b.id)?"block":"none";a.I("tab_basic").className=a.I("tab_advanced").className="";b.className="current";return false},img_seturl:function(b){var c=this,a=c.I("link_rel").value;if("current"==b){c.I("link_href").value=c.current;c.I("link_rel").value=c.link_rel}else{c.I("link_href").value=c.link;if(a){a=a.replace(/attachment|trm-att-[0-9]+/gi,"");c.I("link_rel").value=tinymce.trim(a)}}},imgAlignCls:function(b){var c=this,a=c.I("img_classes").value;c.I("img_demo").className=c.align=b;a=a.replace(/align[^ "']+/gi,"");a+=(" "+b);a=a.replace(/\s+/g," ").replace(/^\s/,"");if("aligncenter"==b){c.I("hspace").value="";c.updateStyle("hspace")}c.I("img_classes").value=a},showSize:function(e){var c=this,f=c.I("img_demo"),a=c.width,d=c.height,g=e.id||"s100",b;b=parseInt(g.substring(1))/200;f.width=Math.round(a*b);f.height=Math.round(d*b);c.showSizeClear();e.style.borderColor="#A3A3A3";e.style.backgroundColor="#E5E5E5"},showSizeSet:function(){var b=this,d,c,a;if((b.width*1.3)>parseInt(b.preloadImg.width)){d=b.I("s130"),c=b.I("s120"),a=b.I("s110");d.onclick=c.onclick=a.onclick=null;d.onmouseover=c.onmouseover=a.onmouseover=null;d.style.color=c.style.color=a.style.color="#aaa"}},showSizeRem:function(){var a=this,c=a.I("img_demo"),b=document.forms[0];c.width=Math.round(b.width.value*0.5);c.height=Math.round(b.height.value*0.5);a.showSizeClear();a.I(a.current_size_sel).style.borderColor="#A3A3A3";a.I(a.current_size_sel).style.backgroundColor="#E5E5E5";return false},showSizeClear:function(){var b=this.I("img_size").getElementsByTagName("div"),a;for(a=0;a<b.length;a++){b[a].style.borderColor="#f1f1f1";b[a].style.backgroundColor="#f1f1f1"}},imgEditSize:function(g){var d=this,i=document.forms[0],a,c,b,e,j;if(!d.preloadImg||!d.preloadImg.width||!d.preloadImg.height){return}a=parseInt(d.preloadImg.width),c=parseInt(d.preloadImg.height),b=d.width||a,e=d.height||c,j=g.id||"s100";size=parseInt(j.substring(1))/100;b=Math.round(b*size);e=Math.round(e*size);i.width.value=Math.min(a,b);i.height.value=Math.min(c,e);d.current_size_sel=j;d.demoSetSize()},demoSetSize:function(a){var c=this.I("img_demo"),b=document.forms[0];c.width=b.width.value?Math.round(b.width.value*0.5):"";c.height=b.height.value?Math.round(b.height.value*0.5):""},demoSetStyle:function(){var b=document.forms[0],a=this.I("img_demo"),c=tinyMCEPopup.editor.dom;if(a){c.setAttrib(a,"style",b.img_style.value);c.setStyle(a,"width","");c.setStyle(a,"height","")}},origSize:function(){var a=this,c=document.forms[0],b=a.I("s100");c.width.value=a.width=a.preloadImg.width;c.height.value=a.height=a.preloadImg.height;a.showSizeSet();a.demoSetSize();a.showSize(b)},init:function(){var a=tinyMCEPopup.editor,b;b=document.body.innerHTML;document.body.innerHTML=a.translate(b);window.setTimeout(function(){trmImage.setup()},500)},setup:function(){var p=this,k,b,l,e,i=document.forms[0],h=tinyMCEPopup.editor,j=p.I("img_demo"),g=tinyMCEPopup.dom,a,o="",n,m;document.dir=tinyMCEPopup.editor.getParam("directionality","");if(tinyMCEPopup.editor.getParam("trmeditimage_disable_captions",false)){p.I("cap_field").style.display="none"}tinyMCEPopup.restoreSelection();b=h.selection.getNode();if(b.nodeName!="IMG"){return}i.img_src.value=j.src=l=h.dom.getAttrib(b,"src");h.dom.setStyle(b,"float","");p.getImageData();k=h.dom.getAttrib(b,"class");if(a=g.getParent(b,"dl")){n=h.dom.getAttrib(a,"class");n=n.match(/align[^ "']+/i);if(n&&!g.hasClass(b,n)){k+=" "+n;tinymce.trim(k)}tinymce.each(a.childNodes,function(c){if(c.nodeName=="DD"&&g.hasClass(c,"trm-caption-dd")){o=c.innerHTML;return}})}i.img_cap.value=o;i.img_title.value=h.dom.getAttrib(b,"title");i.img_alt.value=h.dom.getAttrib(b,"alt");i.border.value=h.dom.getAttrib(b,"border");i.vspace.value=h.dom.getAttrib(b,"vspace");i.hspace.value=h.dom.getAttrib(b,"hspace");i.align.value=h.dom.getAttrib(b,"align");i.width.value=p.width=h.dom.getAttrib(b,"width");i.height.value=p.height=h.dom.getAttrib(b,"height");i.img_classes.value=k;i.img_style.value=h.dom.getAttrib(b,"style");if(g.getAttrib(b,"hspace")){p.updateStyle("hspace")}if(g.getAttrib(b,"border")){p.updateStyle("border")}if(g.getAttrib(b,"vspace")){p.updateStyle("vspace")}if(m=h.dom.getParent(b,"A")){i.link_href.value=p.current=h.dom.getAttrib(m,"href");i.link_title.value=h.dom.getAttrib(m,"title");i.link_rel.value=p.link_rel=h.dom.getAttrib(m,"rel");i.link_style.value=h.dom.getAttrib(m,"style");p.target_value=h.dom.getAttrib(m,"target");i.link_classes.value=h.dom.getAttrib(m,"class")}i.link_target.checked=(p.target_value&&p.target_value=="_blank")?"checked":"";e=l.substring(l.lastIndexOf("/"));e=e.replace(/-[0-9]{2,4}x[0-9]{2,4}/,"");p.link=l.substring(0,l.lastIndexOf("/"))+e;if(k.indexOf("alignleft")!=-1){p.I("alignleft").checked="checked";j.className=p.align="alignleft"}else{if(k.indexOf("aligncenter")!=-1){p.I("aligncenter").checked="checked";j.className=p.align="aligncenter"}else{if(k.indexOf("alignright")!=-1){p.I("alignright").checked="checked";j.className=p.align="alignright"}else{if(k.indexOf("alignnone")!=-1){p.I("alignnone").checked="checked";j.className=p.align="alignnone"}}}}if(p.width&&p.preloadImg.width){p.showSizeSet()}document.body.style.display=""},remove:function(){var a=tinyMCEPopup.editor,c,b;tinyMCEPopup.restoreSelection();b=a.selection.getNode();if(b.nodeName!="IMG"){return}if((c=a.dom.getParent(b,"div"))&&a.dom.hasClass(c,"mceTemp")){a.dom.remove(c)}else{if((c=a.dom.getParent(b,"A"))&&c.childNodes.length==1){a.dom.remove(c)}else{a.dom.remove(b)}}a.execCommand("mceRepaint");tinyMCEPopup.close();return},update:function(){var m=this,v=document.forms[0],g=tinyMCEPopup.editor,e,x,d=null,n,h,p,r,o=null,k=v.img_classes.value,l,q,u="",j,i,s,a,z,w="",c,y;tinyMCEPopup.restoreSelection();e=g.selection.getNode();if(e.nodeName!="IMG"){return}if(v.img_src.value===""){m.remove();return}if(v.img_cap.value!=""&&v.width.value!=""){o=1;k=k.replace(/align[^ "']+\s?/gi,"")}p=g.dom.getParent(e,"a");h=g.dom.getParent(e,"p");n=g.dom.getParent(e,"dl");r=g.dom.getParent(e,"div");tinyMCEPopup.execCommand("mceBeginUndoLevel");g.dom.setAttribs(e,{src:v.img_src.value,title:v.img_title.value,alt:v.img_alt.value,width:v.width.value,height:v.height.value,style:v.img_style.value,"class":k});if(v.link_href.value){if(p==null){if(!v.link_href.value.match(/https?:\/\//i)){v.link_href.value=tinyMCEPopup.editor.documentBaseURI.toAbsolute(v.link_href.value)}if(tinymce.isWebKit&&g.dom.hasClass(e,"aligncenter")){g.dom.removeClass(e,"aligncenter");d=1}tinyMCEPopup.execCommand("CreateLink",false,"#mce_temp_url#",{skip_undo:1});if(d){g.dom.addClass(e,"aligncenter")}tinymce.each(g.dom.select("a"),function(b){if(g.dom.getAttrib(b,"href")=="#mce_temp_url#"){g.dom.setAttribs(b,{href:v.link_href.value,title:v.link_title.value,rel:v.link_rel.value,target:(v.link_target.checked==true)?"_blank":"","class":v.link_classes.value,style:v.link_style.value})}})}else{g.dom.setAttribs(p,{href:v.link_href.value,title:v.link_title.value,rel:v.link_rel.value,target:(v.link_target.checked==true)?"_blank":"","class":v.link_classes.value,style:v.link_style.value})}}if(o){a=10+parseInt(v.width.value);z=(m.align=="aligncenter")?"mceTemp mceIEcenter":"mceTemp";if(n){g.dom.setAttribs(n,{"class":"trm-caption "+m.align,style:"width: "+a+"px;"});if(r){g.dom.setAttrib(r,"class",z)}if((i=g.dom.getParent(e,"dt"))&&(s=i.nextSibling)&&g.dom.hasClass(s,"trm-caption-dd")){g.dom.setHTML(s,v.img_cap.value)}}else{if((q=v.img_classes.value.match(/trm-image-([0-9]{1,6})/))&&q[1]){u="attachment_"+q[1]}if(v.link_href.value&&(w=g.dom.getParent(e,"a"))){if(w.childNodes.length==1){l=g.dom.getOuterHTML(w)}else{l=g.dom.getOuterHTML(w);l=l.match(/<a[^>]+>/i);l=l+g.dom.getOuterHTML(e)+"</a>"}}else{l=g.dom.getOuterHTML(e)}l='<dl id="'+u+'" class="trm-caption '+m.align+'" style="width: '+a+'px"><dt class="trm-caption-dt">'+l+'</dt><dd class="trm-caption-dd">'+v.img_cap.value+"</dd></dl>";j=g.dom.create("div",{"class":z},l);if(h){h.parentNode.insertBefore(j,h);if(h.childNodes.length==1){g.dom.remove(h)}else{if(w&&w.childNodes.length==1){g.dom.remove(w)}else{g.dom.remove(e)}}}else{if(c=g.dom.getParent(e,"TD,TH,LI")){c.appendChild(j);if(w&&w.childNodes.length==1){g.dom.remove(w)}else{g.dom.remove(e)}}}}}else{if(n&&r){if(v.link_href.value&&(y=g.dom.getParent(e,"a"))){l=g.dom.getOuterHTML(y)}else{l=g.dom.getOuterHTML(e)}h=g.dom.create("p",{},l);r.parentNode.insertBefore(h,r);g.dom.remove(r)}}if(v.img_classes.value.indexOf("aligncenter")!=-1){if(h&&(!h.style||h.style.textAlign!="center")){g.dom.setStyle(h,"textAlign","center")}}else{if(h&&h.style&&h.style.textAlign=="center"){g.dom.setStyle(h,"textAlign","")}}if(!v.link_href.value&&p){x=g.selection.getBookmark();g.dom.remove(p,1);g.selection.moveToBookmark(x)}tinyMCEPopup.execCommand("mceEndUndoLevel");g.execCommand("mceRepaint");tinyMCEPopup.close()},updateStyle:function(a){var e=tinyMCEPopup.dom,c,d=document.forms[0],b=e.create("img",{style:d.img_style.value});if(tinyMCEPopup.editor.settings.inline_styles){if(a=="align"){e.setStyle(b,"float","");e.setStyle(b,"vertical-align","");c=d.align.value;if(c){if(c=="left"||c=="right"){e.setStyle(b,"float",c)}else{b.style.verticalAlign=c}}}if(a=="border"){e.setStyle(b,"border","");c=d.border.value;if(c||c=="0"){if(c=="0"){b.style.border="0"}else{b.style.border=c+"px solid black"}}}if(a=="hspace"){e.setStyle(b,"marginLeft","");e.setStyle(b,"marginRight","");c=d.hspace.value;if(c){b.style.marginLeft=c+"px";b.style.marginRight=c+"px"}}if(a=="vspace"){e.setStyle(b,"marginTop","");e.setStyle(b,"marginBottom","");c=d.vspace.value;if(c){b.style.marginTop=c+"px";b.style.marginBottom=c+"px"}}d.img_style.value=e.serializeStyle(e.parseStyle(b.style.cssText));this.demoSetStyle()}},checkVal:function(a){if(a.value==""){if(a.id=="img_src"){a.value=this.I("img_demo").src||this.preloadImg.src}}},resetImageData:function(){var a=document.forms[0];a.width.value=a.height.value=""},updateImageData:function(){var d=document.forms[0],b=trmImage,a=d.width.value,c=d.height.value;if(!a&&c){a=d.width.value=b.width=Math.round(b.preloadImg.width/(b.preloadImg.height/c))}else{if(a&&!c){c=d.height.value=b.height=Math.round(b.preloadImg.height/(b.preloadImg.width/a))}}if(!a){d.width.value=b.width=b.preloadImg.width}if(!c){d.height.value=b.height=b.preloadImg.height}b.showSizeSet();b.demoSetSize();if(d.img_style.value){b.demoSetStyle()}},getImageData:function(){var a=trmImage,b=document.forms[0];a.preloadImg=new Image();a.preloadImg.onload=a.updateImageData;a.preloadImg.onerror=a.resetImageData;a.preloadImg.src=tinyMCEPopup.editor.documentBaseURI.toAbsolute(b.img_src.value)}};window.onload=function(){trmImage.init()};trmImage.preInit();