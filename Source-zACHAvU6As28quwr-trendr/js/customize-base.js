if(typeof trm==="undefined"){var trm={}}(function(a,d){var b,g,c,f,e=Array.prototype.slice;g=function(h,i){var j=f(this,h,i);j.extend=this.extend;return j};c=function(){};f=function(i,h,j){var k;if(h&&h.hasOwnProperty("constructor")){k=h.constructor}else{k=function(){var l=i.apply(this,arguments);return l}}d.extend(k,i);c.prototype=i.prototype;k.prototype=new c();if(h){d.extend(k.prototype,h)}if(j){d.extend(k,j)}k.prototype.constructor=k;k.__super__=i.prototype;return k};b={};b.Class=function(l,k,i){var j,h=arguments;if(l&&k&&b.Class.applicator===l){h=k;d.extend(this,i||{})}j=this;if(this.instance){j=function(){return j.instance.apply(j,arguments)};d.extend(j,this)}j.initialize.apply(j,h);return j};b.Class.applicator={};b.Class.prototype.initialize=function(){};b.Class.prototype.extended=function(h){var i=this;while(typeof i.constructor!=="undefined"){if(i.constructor===h){return true}if(typeof i.constructor.__super__==="undefined"){return false}i=i.constructor.__super__}return false};b.Class.extend=g;b.Events={trigger:function(h){if(this.topics&&this.topics[h]){this.topics[h].fireWith(this,e.call(arguments,1))}return this},bind:function(i,h){this.topics=this.topics||{};this.topics[i]=this.topics[i]||d.Callbacks();this.topics[i].add.apply(this.topics[i],e.call(arguments,1));return this},unbind:function(i,h){if(this.topics&&this.topics[i]){this.topics[i].remove.apply(this.topics[i],e.call(arguments,1))}return this}};b.Value=b.Class.extend({initialize:function(i,h){this._value=i;this.callbacks=d.Callbacks();d.extend(this,h||{});this.set=d.proxy(this.set,this)},instance:function(){return arguments.length?this.set.apply(this,arguments):this.get()},get:function(){return this._value},set:function(i){var h=this._value;i=this._setter.apply(this,arguments);i=this.validate(i);if(null===i||this._value===i){return this}this._value=i;this.callbacks.fireWith(this,[i,h]);return this},_setter:function(h){return h},setter:function(i){var h=this.get();this._setter=i;this._value=null;this.set(h);return this},resetSetter:function(){this._setter=this.constructor.prototype._setter;this.set(this.get());return this},validate:function(h){return h},bind:function(h){this.callbacks.add.apply(this.callbacks,arguments);return this},unbind:function(h){this.callbacks.remove.apply(this.callbacks,arguments);return this},link:function(){var h=this.set;d.each(arguments,function(){this.bind(h)});return this},unlink:function(){var h=this.set;d.each(arguments,function(){this.unbind(h)});return this},sync:function(){var h=this;d.each(arguments,function(){h.link(this);this.link(h)});return this},unsync:function(){var h=this;d.each(arguments,function(){h.unlink(this);this.unlink(h)});return this}});b.Values=b.Class.extend({defaultConstructor:b.Value,initialize:function(h){d.extend(this,h||{});this._value={};this._deferreds={}},instance:function(h){if(arguments.length===1){return this.value(h)}return this.when.apply(this,arguments)},value:function(h){return this._value[h]},has:function(h){return typeof this._value[h]!=="undefined"},add:function(i,h){if(this.has(i)){return this.value(i)}this._value[i]=h;h.parent=this;if(h.extended(b.Value)){h.bind(this._change)}this.trigger("add",h);if(this._deferreds[i]){this._deferreds[i].resolve()}return this._value[i]},create:function(h){return this.add(h,new this.defaultConstructor(b.Class.applicator,e.call(arguments,1)))},each:function(i,h){h=typeof h==="undefined"?this:h;d.each(this._value,function(j,k){i.call(h,k,j)})},remove:function(i){var h;if(this.has(i)){h=this.value(i);this.trigger("remove",h);if(h.extended(b.Value)){h.unbind(this._change)}delete h.parent}delete this._value[i];delete this._deferreds[i]},when:function(){var i=this,j=e.call(arguments),h=d.Deferred();if(d.isFunction(j[j.length-1])){h.done(j.pop())}d.when.apply(d,d.map(j,function(k){if(i.has(k)){return}return i._deferreds[k]=i._deferreds[k]||d.Deferred()})).done(function(){var k=d.map(j,function(l){return i(l)});if(k.length!==j.length){i.when.apply(i,j).done(function(){h.resolveWith(i,k)});return}h.resolveWith(i,k)});return h.promise()},_change:function(){this.parent.trigger("change",this)}});d.extend(b.Values.prototype,b.Events);b.ensure=function(h){return typeof h=="string"?d(h):h};b.Element=b.Value.extend({initialize:function(j,i){var h=this,m=b.Element.synchronizer.html,l,n,k;this.element=b.ensure(j);this.events="";if(this.element.is("input, select, textarea")){this.events+="change";m=b.Element.synchronizer.val;if(this.element.is("input")){l=this.element.prop("type");if(b.Element.synchronizer[l]){m=b.Element.synchronizer[l]}if("text"===l||"password"===l){this.events+=" keyup"}}else{if(this.element.is("textarea")){this.events+=" keyup"}}}b.Value.prototype.initialize.call(this,null,d.extend(i||{},m));this._value=this.get();n=this.update;k=this.refresh;this.update=function(o){if(o!==k.call(h)){n.apply(this,arguments)}};this.refresh=function(){h.set(k.call(h))};this.bind(this.update);this.element.bind(this.events,this.refresh)},find:function(h){return d(h,this.element)},refresh:function(){},update:function(){}});b.Element.synchronizer={};d.each(["html","val"],function(h,j){b.Element.synchronizer[j]={update:function(i){this.element[j](i)},refresh:function(){return this.element[j]()}}});b.Element.synchronizer.checkbox={update:function(h){this.element.prop("checked",h)},refresh:function(){return this.element.prop("checked")}};b.Element.synchronizer.radio={update:function(h){this.element.filter(function(){return this.value===h}).prop("checked",true)},refresh:function(){return this.element.filter(":checked").val()}};d.support.postMessage=!!window.postMessage;b.Messenger=b.Class.extend({add:function(j,i,h){return this[j]=new b.Value(i,h)},initialize:function(j,h){var i=window.parent==window?null:window.parent;d.extend(this,h||{});this.add("channel",j.channel);this.add("url",j.url||"");this.add("targetWindow",j.targetWindow||i);this.add("origin",this.url()).link(this.url).setter(function(k){return k.replace(/([^:]+:\/\/[^\/]+).*/,"$1")});this.receive=d.proxy(this.receive,this);this.receive.guid=d.guid++;d(window).on("message",this.receive)},destroy:function(){d(window).off("message",this.receive)},receive:function(i){var h;i=i.originalEvent;if(!this.targetWindow()){return}if(this.origin()&&i.origin!==this.origin()){return}h=JSON.parse(i.data);if(!h||!h.id||typeof h.data==="undefined"){return}if((h.channel||this.channel())&&this.channel()!==h.channel){return}this.trigger(h.id,h.data)},send:function(j,i){var h;i=typeof i==="undefined"?null:i;if(!this.url()||!this.targetWindow()){return}h={id:j,data:i};if(this.channel()){h.channel=this.channel()}this.targetWindow().postMessage(JSON.stringify(h),this.origin())}});d.extend(b.Messenger.prototype,b.Events);b=d.extend(new b.Values(),b);b.get=function(){var h={};this.each(function(j,i){h[i]=j.get()});return h};a.customize=b})(trm,jQuery);