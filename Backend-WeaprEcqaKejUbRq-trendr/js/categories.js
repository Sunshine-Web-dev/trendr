jQuery(document).ready(function(d){var b=false,e,c,a;if(document.forms.addcat.category_parent){b=document.forms.addcat.category_parent.options}e=function(h,g){var f,i;f=d("<span>"+d("name",h).text()+"</span>").text();i=d("cat",h).attr("id");b[b.length]=new Option(f,i)};a=function(g,f){var i=d("cat",g).attr("id"),h;for(h=0;h<b.length;h++){if(i==b[h].value){b[h]=null}}};c=function(f){if("undefined"!=showNotice){return showNotice.warn()?f:false}return f};if(b){d("#the-list").trmList({addAfter:e,delBefore:c,delAfter:a})}else{d("#the-list").trmList({delBefore:c})}d('.delete a[class^="delete"]').live("click",function(){return false})});