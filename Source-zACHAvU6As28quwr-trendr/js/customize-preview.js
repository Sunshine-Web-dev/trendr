(function(b,d){var c=trm.customize,a;a=function(g,e,f){var h;return function(){var i=arguments;f=f||this;clearTimeout(h);h=setTimeout(function(){h=null;g.apply(f,i)},e)}};c.Preview=c.Messenger.extend({initialize:function(g,f){var e=this;c.Messenger.prototype.initialize.call(this,g,f);this.body=d(document.body);this.body.on("click.preview","a",function(h){h.preventDefault();e.send("scroll",0);e.send("url",d(this).prop("href"))});this.body.on("submit.preview","form",function(h){h.preventDefault()});this.window=d(window);this.window.on("scroll.preview",a(function(){e.send("scroll",e.window.scrollTop())},200));this.bind("scroll",function(h){e.window.scrollTop(h)})}});d(function(){c.settings=window._trmCustomizeSettings;if(!c.settings){return}var f,e;f=new c.Preview({url:window.location.href,channel:c.settings.channel});f.bind("settings",function(g){d.each(g,function(i,h){if(c.has(i)){c(i).set(h)}else{c.create(i,h)}})});f.trigger("settings",c.settings.values);f.bind("setting",function(g){var h;g=g.slice();if(h=c(g.shift())){h.set.apply(h,g)}});f.bind("sync",function(g){d.each(g,function(i,h){f.trigger(i,h)});f.send("synced")});f.bind("active",function(){if(c.settings.nonce){f.send("nonce",c.settings.nonce)}});f.send("ready");e=d.map(["color","image","position_x","repeat","attachment"],function(g){return"background_"+g});c.when.apply(c,e).done(function(j,i,m,h,l){var n=d(document.body),o=d("head"),g=d("#custom-background-css"),k;if(n.hasClass("custom-background")&&!g.length){return}k=function(){var p="";n.toggleClass("custom-background",!!(j()||i()));if(j()){p+="background-color: "+j()+";"}if(i()){p+='background-image: url("'+i()+'");';p+="background-position: top "+m()+";";p+="background-repeat: "+h()+";";p+="background-position: top "+l()+";"}g.remove();g=d('<style type="text/css" id="custom-background-css">body.custom-background { '+p+" }</style>").appendTo(o)};d.each(arguments,function(){this.bind(k)})})})})(trm,jQuery);