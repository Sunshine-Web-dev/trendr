
$('*').bind('touchstart mousemove keydown onscroll scroll touchmove touchend touchcancel touchleave touch', function () {
  });


////////////////////
var touchmoved;
$('a').on('touchend', function(e){
    if(touchmoved != true){
        // you're on button click action
    }
}).on('touchmove', function(e){
    touchmoved = true;
}).on('touchstart', function(){
    touchmoved = true;
});

/////////////
$(document).on('touchstart', function() {
    detectTap = true; //detects all touch events
});
$(document).on('touchmove', function() {
    detectTap = false; //Excludes the scroll events from touch events
});
$(document).on('click touchend', function(event) {
    if (event.type == "click") detectTap = true; //detects click events 
       if (detectTap){
          //here you can write the function or codes you wanna execute on tap

       }
 });

/////////////////

$('html').on('click touchmove','li#f6',function(){
      $("#site-f2").hide();
      $("#site-f11 iframe").hide();
           $("#site-f12 iframe").hide();

    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();


     $("#site-f1 .activity").show();
      $("#site-f1").show();
    $("#site-content").show();


        $("iframe#blah ").hide();
        // $('iframe#blah ').css('opacity','0');
$('ul.publish-piece').css('column-count','3');
$('ul.publish-piece').css('column-gap','2px');
$('.article-piece .activity').css('margin-top','-2px');

      $('div.broadcast-knobs').css('display','none');
      $('div.ac-reply-content').css('display','none');
     $('div.activity-comments').css('display','none');

      $('div.broadcast-inn p').css('display','none');
      $('div.trs-img p').css('display','inline');


  });





$('html').on('click touchmove','li#f3',function(){
     $("#site-f1").hide();
     $("#site-f11  iframe").hide();
     $("#site-f12 iframe").hide();

     $("#site-f1").hide();
     $("#site-f1 .activity").hide();
    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();

    $("#site-f2").show();
    $("#site-content").show();


   //  $("#site-f2").hide();
   //  $("#site-f11 iframe").hide();
   //  $("#site-f1").hide();
   //  $("#site-f00 iframe").show();
        $("iframe#blah ").hide();
       //  $('iframe#blah ').css('opacity','0');

  });






$('html').on('click touchmove','li#f5',function(){
     $("#site-f1").show();       
      $("#site-f2").hide();
      $("#site-f11 iframe").hide();




      $("#site-f12 iframe").show();
      $("li#l-inbox").hide();



      $("#profile-links").hide();
      $("iframe#blah").hide();
        // $('iframe#blah ').css('opacity','0');
      $("#blah").hide();

      $("#site-content").hide();

  });

$('html').on('click touchmove','li#f2',function(){
      $("#site-f1").hide();
      $("#site-f2").hide();
           $("#site-f12 iframe").hide();
        $("iframe#blah ").hide();


      $("#site-f11 .frame").show();
       //  $('iframe#blah ').css('opacity','0');
     //$("ul#nav2").hide();       
      $("#site-content").hide();

  });




$('html').on('click touchmove','li#f6',function(){
      $("#site-f2").hide();
      $("#site-f11 iframe").hide();
           $("#site-f12 iframe").hide();

    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();


     $("#site-f1 .activity").show();
      $("#site-f1").show();
    $("#site-content").show();


        $("iframe#blah ").hide();
        // $('iframe#blah ').css('opacity','0');
$('ul.publish-piece').css('column-count','3');
$('ul.publish-piece').css('column-gap','2px');
$('.article-piece .activity').css('margin-top','-2px');

      $('div.broadcast-knobs').css('display','none');
      $('div.ac-reply-content').css('display','none');
     $('div.activity-comments').css('display','none');

      $('div.broadcast-inn p').css('display','none');
      $('div.trs-img p').css('display','inline');


  });





$('html').on('click touchend','li#f3',function(){
     $("#site-f1").hide();
     $("#site-f11  iframe").hide();
     $("#site-f12 iframe").hide();

     $("#site-f1").hide();
     $("#site-f1 .activity").hide();
    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();

    $("#site-f2").show();
    $("#site-content").show();


   //  $("#site-f2").hide();
   //  $("#site-f11 iframe").hide();
   //  $("#site-f1").hide();
   //  $("#site-f00 iframe").show();
        $("iframe#blah ").hide();
       //  $('iframe#blah ').css('opacity','0');

  });






$('html').on('click touchend','li#f5',function(){
     $("#site-f1").show();       
      $("#site-f2").hide();
      $("#site-f11 iframe").hide();




      $("#site-f12 iframe").show();
      $("li#l-inbox").hide();



      $("#profile-links").hide();
      $("iframe#blah").hide();
        // $('iframe#blah ').css('opacity','0');
      $("#blah").hide();

      $("#site-content").hide();

  });

$('html').on('click touchend','li#f2',function(){
      $("#site-f1").hide();
      $("#site-f2").hide();
           $("#site-f12 iframe").hide();
        $("iframe#blah ").hide();


      $("#site-f11 .frame").show();
       //  $('iframe#blah ').css('opacity','0');
     //$("ul#nav2").hide();       
      $("#site-content").hide();

  });


$('html').on('click touchend','li#f6',function(){
      $("#site-f2").hide();
      $("#site-f11 iframe").hide();
           $("#site-f12 iframe").hide();

    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();


     $("#site-f1 .activity").show();
      $("#site-f1").show();
    $("#site-content").show();


        $("iframe#blah ").hide();
        // $('iframe#blah ').css('opacity','0');
$('ul.publish-piece').css('column-count','3');
$('ul.publish-piece').css('column-gap','2px');
$('.article-piece .activity').css('margin-top','-2px');

      $('div.broadcast-knobs').css('display','none');
      $('div.ac-reply-content').css('display','none');
     $('div.activity-comments').css('display','none');

      $('div.broadcast-inn p').css('display','none');
      $('div.trs-img p').css('display','inline');


  });

//////////////////////




$('#main').on('touchstart touchmove touchend touchcancel', '.dataCard', function(event){
    if($(event.target).is('.dataCard')) {
        touchHandler(event);
    }
});


$("li#f6").on( 'touchstart touchmove touchend touchcancel', function(){
      $("#site-f2").hide();
      $("#site-f11 iframe").hide();
           $("#site-f12 iframe").hide();

    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();


     $("#site-f1 .activity").show();
      $("#site-f1").show();
    $("#site-content").show();


        $("iframe#blah ").hide();
        // $('iframe#blah ').css('opacity','0');
$('ul.publish-piece').css('column-count','3');
$('ul.publish-piece').css('column-gap','2px');
$('.article-piece .activity').css('margin-top','-2px');

      $('div.broadcast-knobs').css('display','none');
      $('div.ac-reply-content').css('display','none');
     $('div.activity-comments').css('display','none');

      $('div.broadcast-inn p').css('display','none');
      $('div.trs-img p').css('display','inline');


  });





$("li#f3").on( 'touchstart touchmove touchend touchcancel', function(){
     $("#site-f1").hide();
     $("#site-f11  iframe").hide();
     $("#site-f12 iframe").hide();

     $("#site-f1").hide();
     $("#site-f1 .activity").hide();
    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();

    $("#site-f2").show();
    $("#site-content").show();


   //  $("#site-f2").hide();
   //  $("#site-f11 iframe").hide();
   //  $("#site-f1").hide();
   //  $("#site-f00 iframe").show();
        $("iframe#blah ").hide();
       //  $('iframe#blah ').css('opacity','0');

  });






$("li#f5").on( 'touchstart touchmove touchend touchcancel', function(){
     $("#site-f1").show();       
      $("#site-f2").hide();
      $("#site-f11 iframe").hide();




      $("#site-f12 iframe").show();
      $("li#l-inbox").hide();



      $("#profile-links").hide();
      $("iframe#blah").hide();
        // $('iframe#blah ').css('opacity','0');
      $("#blah").hide();

      $("#site-content").hide();

  });

$("li#f2").on( 'touchstart touchmove touchend touchcancel', function(){
      $("#site-f1").hide();
      $("#site-f2").hide();
           $("#site-f12 iframe").hide();
        $("iframe#blah ").hide();


      $("#site-f11 .frame").show();
       //  $('iframe#blah ').css('opacity','0');
     //$("ul#nav2").hide();       
      $("#site-content").hide();

  });

//////////////////////





$('html').on('click touchstart mousemove keydown onscroll scroll touchmove touchend touchcancel touchleave touch','li#f6',function(){
      $("#site-f2").hide();
      $("#site-f11 iframe").hide();
           $("#site-f12 iframe").hide();

    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();


     $("#site-f1 .activity").show();
      $("#site-f1").show();
    $("#site-content").show();


        $("iframe#blah ").hide();
        // $('iframe#blah ').css('opacity','0');
$('ul.publish-piece').css('column-count','3');
$('ul.publish-piece').css('column-gap','2px');
$('.article-piece .activity').css('margin-top','-2px');

      $('div.broadcast-knobs').css('display','none');
      $('div.ac-reply-content').css('display','none');
     $('div.activity-comments').css('display','none');

      $('div.broadcast-inn p').css('display','none');
      $('div.trs-img p').css('display','inline');


  });





$('html').on('click touchstart mousemove keydown onscroll scroll touchmove touchend touchcancel touchleave touch','li#f3',function(){
     $("#site-f1").hide();
     $("#site-f11  iframe").hide();
     $("#site-f12 iframe").hide();

     $("#site-f1").hide();
     $("#site-f1 .activity").hide();
    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();

    $("#site-f2").show();
    $("#site-content").show();


   //  $("#site-f2").hide();
   //  $("#site-f11 iframe").hide();
   //  $("#site-f1").hide();
   //  $("#site-f00 iframe").show();
        $("iframe#blah ").hide();
       //  $('iframe#blah ').css('opacity','0');

  });






$('html').on('click touchstart mousemove keydown onscroll scroll touchmove touchend touchcancel touchleave touch','li#f5',function(){
     $("#site-f1").show();       
      $("#site-f2").hide();
      $("#site-f11 iframe").hide();




      $("#site-f12 iframe").show();
      $("li#l-inbox").hide();



      $("#profile-links").hide();
      $("iframe#blah").hide();
        // $('iframe#blah ').css('opacity','0');
      $("#blah").hide();

      $("#site-content").hide();

  });

$('html').on('click touchstart mousemove keydown onscroll scroll touchmove touchend touchcancel touchleave touch','li#f2',function(){
      $("#site-f1").hide();
      $("#site-f2").hide();
           $("#site-f12 iframe").hide();
        $("iframe#blah ").hide();


      $("#site-f11 .frame").show();
       //  $('iframe#blah ').css('opacity','0');
     //$("ul#nav2").hide();       
      $("#site-content").hide();

  });
