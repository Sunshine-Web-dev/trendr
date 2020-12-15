<?php

/**
 * trendr - Activity Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - trs_dtheme_object_filter()
 *
 * @ package trendr
 * @sutrsackage trs-default
 */
/**  if($('li#activity-following_recommend_featuredposts').is(':visible')){ //if the container is visible on the page



$('ul.publish-piece').css('column-count','1');
            $('div.broadcast-knobs').css('display','inline');
            $('div.ac-reply-content').css('display','inline');


  } else {
$('ul.publish-piece').css('column-count','5');
            $('div.broadcast-knobs').css('display','none');
            $('div.ac-reply-content').css('display','none');


  }

if ($("li#activity-following_recommend_featuredposts").hasClass("selected")) {
$('ul.publish-piece').css('column-count','5');
            $('div.broadcast-knobs').css('display','none');
            
            $('div.ac-reply-content').css('display','none');
}



moved from line 138-145 . load more causesissue with css column js jorizontal. moving here solves issue. changed from li load more to div load more
 */
?>

<?php do_action( 'trs_before_activity_loop' ); ?>

    <script> 


/**
 * With version 2.0+, we simply simulate the click and the themes do the loading for us :)
 */
jQuery( document ).ready( function ($) {



    // We'll use this variable to make sure we don't send the request again and again.
    var $window = $( window );

    // Check the window scroll event.
  //  $window.scroll( function () {
      $(window).on(" scroll   touchmove touchend touchcancel touchleave touch",function(){
        // Find the visible "load more" button.
        // since TRS does not remove the "load more" button, we need to find the last one that is visible.
        var $load_more_btn = $( '.infinite:visible' );
        // If there is no visible "load more" button, we've reached the last page of the activity stream.
        // If data attribute is set, we already triggered request for ths specific button.
        if ( ! $load_more_btn.get( 0 ) || $load_more_btn.data( 'autoloaded' ) ) {
            return;
        }

        // Find the offset of the button.
        var pos = $load_more_btn.offset();
        var offset = pos.top - 1;// 50 px before we reach the button.

        // If the window height+scrollTop is greater than the top offset of the "load more" button,
        // we have scrolled to the button's position. Let us load more activity.
        if ($window.scrollTop() + $window.height() > offset) {
            $load_more_btn.data( 'autoloaded', 1 );
            $load_more_btn.find( 'a' ).trigger( 'click' );
        }

    });
});// end of dom ready.

jQuery(document).ready(function(){
     // $(".dim").show();
              $('#header ').addClass('all');
              $('.site-content ').addClass('follow');

    $('.frame').find('html').hide();

    $('a').attr('target','blah');
    //$('a').setAttribute('target','blah');
    //$("html").fadeIn(2000);
    //$('a[href^="http://"]').not('a[href*=gusdecool]').attr('target','blah');

    $('html #frame').contents().find('#nav2').hide();
   //$("iframe#blah ").css ('height','0%');

     $('html #blah').contents().find('#nav2').hide();




   $('li#f1').addClass('active');

          

      $("#l-b").hide();
  
     // $(".standard-form").hide();


//////////////////////////////////////////////
///NAV2 Function
$('html').on('  touchstart  scroll   touchmove touchend touchcancel touchleave touch','li#f0',function(){
                                    $(".trs-img embed").show();
      $(".dim").show();
     $("html #frame").contents().find(".dim").show();
$('.active').removeClass('active');
$(this).addClass('active');

  });





$('html').on('click  touchstart  scroll   touchmove touchend touchcancel touchleave touch','li#f1',function(){

       $("iframe#blah ").stop();
        $("iframe#blah ").off();


      //line sensetive related to nav2  hide
   $(".trs-img embed").show();

      $(".dim").hide();
  //   $("html #frame").contents().find(".dim").hide();

$(".site-content").css('height','0%');

    $("#l-b").hide();
    $("#l-c iframe").hide();
    $("#l-d iframe").hide();

    $("li#p-nav").hide();
    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();

     $("#l-a .activity").show();
      $("#l-a").show();

              $('#header ').addClass('all');
              $('.site-content ').addClass('follow');

        $(".site-content").show();
         //   $('.container').show();
            
        $("#contour-image").show();

       $('#portrait-upload').hide();
            //$("#l-d #frame ").contents().find(".site-content").show();

     // $("#l-b").hide();
        //      $('#header ').removeClass('all');

$('.active').removeClass('active');
$(this).addClass('active');

  });

////f1.active



          $('html').on('   touchstart','#f1.active',function(){
                 $("iframe#blah ").stop();

        $("#f1").removeClass('openblah');

         $("iframe#blah ").hide ();

        $(".site-content").show();
         //   $('.container').show();   

                  $("#l-a .activity").show();
          $("#l-a").show();

            });

 //Add class opneblah on a click          
$('#l-a ').on('click','a img.portrait',function(){
        $("#f1").addClass('openblah');

        $(".site-content").hide();

    
  });

// Keeping of #blah on nav2 navigation

$('html').on(' touchstart  scroll   touchmove touchend touchcancel touchleave touch','#f1.openblah',function(){
     $("iframe#blah ").stop();
$(".site-content").css('height','0%');
        //$(".site-content").hide();
     //   $("iframe#blah ").show ();
                $("html #frame").contents().find('#l-a').show();
                             //  $("iframe#blah ").css ('height','100%');
        $("iframe#blah ").on();

  });


////f1  -- line sensetive
// removal of #blah on nav2 navigation

          $('html').on('   touchstart','#f1.active.openblah',function(){
                 $("iframe#blah ").stop();

        $("iframe#blah ").hide ();

       // $("iframe#blah ").css ('height','0%');
        $(".site-content").show();

            });



$('html').on('click  touchstart  scroll   touchmove touchend touchcancel touchleave touch','li#f2',function(){

                                    $(".trs-img embed").show();
     $("html #frame").contents().find(".dim").hide();
      $(".dim").hide();

        $(".site-content").show();
        //    $('.container').show();


    $("#l-b").show();
     $("#l-d iframe").hide();

     $("#l-c iframe").hide();
      $("li#p-nav").hide();

    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();
      $("#contour-image").show();

    $('#portrait-upload').hide();

     $("#l-a .activity").hide();
      $("#l-a").hide();

        //$(".site-content").removeClass('selected');

        $("iframe#blah ").hide();
              $('#header ').removeClass('all');


$('.active').removeClass('active');
$(this).addClass('active');  });


////f2.active


          $('html').on('  touchstart','#f2.active',function(){
    $("#l-b").show();

               $("#l-b iframe#frame").contents().find('iframe#blah').hide();
  
            });

////f2 #blah function

$('#l-b ').on('click touchstart scroll   touchmove touchend touchcancel touchleave touch','a ',function(){
$('.site-content ').css('display','none');
      //  $("iframe#blah ").css ('height','100%');

  });



$('html').on(' click touchstart  ','li#f3',function(){
     //  $(".dim").hide();
         $("iframe#blah ").stop();

        
          $(".trs-img embed").show();

      //$("html #frame ").contents().find('.container').hide();
           // $("html #frame ").contents().find(".site-content").hide();
//$('.site-content ').hide();

    //  $("#l-d #frame ").contents().find('.container').show();
            $("#l-c #frame ").contents().find('.trs-img embed').show();
            //$("#l-d #frame ").contents().find(".site-content").show();

             // $("#l-c #frame ").contents().find('.container ').addClass('all');
           //   $('#header ').addClass('all');
             // $('.site-content ').removeClass('follow');
            $('.site-content ').css('height','0%');

      $(".dim").hide();

      $("html #frame").contents().find(".dim").hide();
      $("#l-a").hide();
      $("#l-b").hide();
      $("#l-d iframe").hide();
      $("#l-c .frame").show();

     $("iframe#blah ").hide();

     $("html #frame").contents().find('#l-a').show();

      

      $("#contour-image").show();
      $("li#p-nav").hide();
      $(" html #frame").contents().find('#portrait-upload').show();
      $('html #frame').contents().find('.dimension .profile').hide();
      $(" html #frame").contents().find('.dimension-inn').show();



     //  $("#nav2 div").css('padding','1px');
     $("li#p1").show(); 

         //  Line sensetive- to hide nav - header
        $('.frame').contents().find('#nav2').hide();
        $('.frame').contents().find('#header').hide();
              $('#header ').removeClass('all');
$('.site-content ').removeClass('follow');
$('.active').removeClass('active');
$(this).addClass('active');
  });
          

////f3.active

          $('html').on('  touchstart','#f3.active',function(){
                 $("iframe#blah ").stop();

        $("#f3").removeClass('openblah');

               $("#l-c iframe#frame").contents().find('iframe#blah').hide();
               //$("iframe#blah ").css ('height','0%');
       $("  #frame").contents().find('.site-content').show();

           });

 ////f3  add .openblah to a div 
         
    $('#l-c .frame').load(function(){

        var iframe = $('#l-c .frame').contents();

        iframe.find("a").click(function(){
        $("#f3").addClass('openblah');

        });
});

// Keeping of #blah on nav2 navigation

$('html').on('  touchstart  scroll   touchmove touchend touchcancel touchleave touch','#f3.openblah',function(){
   $("iframe#blah ").stop();

               // $(".frame  ").contents().find('.site-content').hide();
                //$("html #frame ").contents().find('.container').hide();

                $("html #frame").contents().find('#l-a').hide();
                      //  $("iframe#blah ").css ('height','100%');


            $('.site-content ').css('height','0%');


  });


////f3  -- line sensetive
// removal of #blah on nav2 navigation
          $('html').on('  touchstart','#f3.active.openblah',function(){
                 $("iframe#blah ").stop();

                //$(".frame  ").contents().find('.site-content').show();
                $("html #frame").contents().find('#l-a').show();
                             //  $("html #frame ").contents().find('.container').show();
$("  #frame").contents().find('.site-content').show();

            });






$('html').on('click  touchstart scroll touchmove touchend touchcancel touchleave touch','li#f4',function(){
    //  $(".dim").hide();
         $("iframe#blah ").stop();

        
          $(".trs-img embed").show();

      //$("html #frame ").contents().find('.container').hide();
           // $("html #frame ").contents().find(".site-content").hide();
//$('.site-content ').hide();

    //  $("#l-d #frame ").contents().find('.container').show();
            $("#l-d #frame ").contents().find('.trs-img embed').show();
            //$("#l-d #frame ").contents().find(".site-content").show();


      $(".dim").hide();

      $("html #frame").contents().find(".dim").hide();
      $("#l-a").hide();
      $("#l-b").hide();
      $("#l-c iframe").hide();
      $("#l-d .frame").show();

     $("iframe#blah ").hide();

     $("html #frame").contents().find('#l-a').show();

      

      $("#contour-image").show();
      $("li#p-nav").hide();
      $(" html #frame").contents().find('#portrait-upload').show();
      $('html #frame').contents().find('.dimension .profile').hide();
      $(" html #frame").contents().find('.dimension-inn').show();



     //  $("#nav2 div").css('padding','1px');
     $("li#p1").show(); 

         //  Line sensetive- to hide nav - header
        $('.frame').contents().find('#nav2').hide();
        $('.frame').contents().find('#header').hide();
              $('#header ').removeClass('all');
$('.site-content ').removeClass('follow');
$('.active').removeClass('active');
$(this).addClass('active');

  });

////f4.active

          $('html').on(' touchstart ','#f4.active',function(){
     $("iframe#blah ").stop();


               $("#l-d iframe#frame").contents().find('iframe#blah').hide();
              $("#f4").removeClass('openblah');
             // $("iframe#blah ").css ('height','0%');
$("  #frame").contents().find('.site-content').show();

            });

 ////f4  add .openblah to a div 
         
    $('#l-d iframe#frame').load(function(){

        var iframe = $('#l-d iframe#frame').contents();

        iframe.find("a").click(function(){
        $("#f4").addClass('openblah');
        });
});

// Keeping of #blah on nav2 navigation

$('html').on(' touchstart  scroll   touchmove touchend touchcancel touchleave touch','#f4.openblah',function(){
     $("iframe#blah ").stop();

               // $(".frame  ").contents().find('.site-content').hide();
                //$("html #frame ").contents().find('.container').hide();

                $("html #frame").contents().find('#l-a').hide();
                      //  $("iframe#blah ").css ('height','100%');


            $('.site-content ').css('height','0%');

  });


////f3  -- line sensetive
// removal of #blah on nav2 navigation
          $('html').on('  touchstart','#f4.active.openblah',function(){
                 $("iframe#blah ").stop();

                //$(".frame  ").contents().find('.site-content').show();
                        //   $("html #frame ").contents().find('.container').show();

             $("html #frame").contents().find('#l-a').show();

//$(" html #frame").contents().find('.site-content').show();
$("  #frame").contents().find('.site-content').show();

            });




$('html ').on('click ','a img.portrait',function(){
     $("iframe#blah ").show();



  });







//$('html ').on('click',' a ',function(){
      //  $("iframe#blah ").show();

  //});

})





$('#blah').load(function(){
      $("#frame").hide();
                               $('.site-content').hide();

   $("iframe#blah ").show();

 //  $("html").fadeIn(2000);
  
    //$('#blah').contents().find('#nav2').hide();
//$("#blah").contents().find(".trs-img embed").show();
   // $('#blah').contents().find('a').attr('target','blah');
        //$("iframe#blah ").css ('height','100%');
$("#blah").contents().find(".trs-img embed").show();
  $('#blah').contents().find('#nav2').hide();
        $('#blah').contents().find('#header').hide();

});





//$('html').load(function(){
 // $("body").fadeIn(2000);
  //  $('.frame').contents().find('#nav2').hide();
   // $('.frame').contents().find('#nav2').hide();

  //$(".trs-img embed").show();

//});


      var fire = {
        init:function(){

   
           var time = 15;
            var scale = 1;

            var video_obj = null;

            document.getElementById('video').addEventListener('loadedmetadata', function() {
                 this.currentTime = time;
                 video_obj = this;

            }, false);

            document.getElementById('video').addEventListener('loadeddata', function() {
                 var video = document.getElementById('video');

                 var canvas = document.createElement("canvas");
                 canvas.width = video.videoWidth * scale;
                 canvas.height = video.videoHeight * scale;
                 canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

                 var img = document.createElement("img");
                img.src = canvas.toDataURL();
                $('#thumbnail').append(img);

                video_obj.currentTime = 0;

            }, false);

       //     $('*').on('click touchstart  touchmove touchend touchcancel touchleave touch', function(){
           // });

            $('html').on('click touchstart  scroll   touchmove touchend touchcancel touchleave touch', function(){

    $('a').attr('target','blah');

               // $(' iframe#frame html  iframe#blah ').contents().find('html').hide();
               //$("iframe#blah ").hide();


               //hides navigation for #frame
              $('html #frame').contents().find('#nav2').hide();


               
               //returns back the hidden site-content for #frame
                //$(" html #frame").contents().find('.site-content').show();
               

            });



           //Global #blah hide for all #nav2 
          //$('html').on('click touchstart scroll touchmove touchend touchcancel touchleave touch','#nav2 .active',function(){

             //hides second iframe #blah wheen a html is clicked // returnning #frame 
              // $("#l-d iframe#frame").contents().find('iframe#blah').hide();
       

              //support for #nav2 li#f1 non irame

            //});



          //Added individual support for each menu - to hide#vlah independently


            $('html #p1').on('click ', function(){

               //hides .profile from profile page
              $('html #frame').contents().find('.dimension .profile').toggle();

                $(" html #frame").contents().find('.dimension-inn').toggle();


            });

        }
      };
      fire.init();


  </script>   

<?php
//$GLOBALS['scopes'] = ['groups','friends'];
       // scope = 'scopes';

 if ( trs_has_activities( trs_ajax_querystring( 'activity' ). '&per_page=6'  ) ) : ?>

  <?php /* Show pagination if JS is not enabled, since the "Load More" link will do nothing */ ?>
  <noscript>
    <div class="pagination">
      <div class="pag-count"><?php trs_activity_pagination_count(); ?></div>
      <div class="pagination-links"><?php trs_activity_pagination_links(); ?></div>
    </div>
  </noscript>

  <?php if ( empty( $_POST['page'] ) ) : ?>

    <ul id="publish" class="publish-piece article-piece">

  <?php endif; ?>
<script>
    $(".openup").click(function(){
  //$('.hashtitle-e').toggle();
  //  $('.hashtags').toggle();
  //$('.hashtitle').toggle();
  //  $('.post-title h3').toggle();
      });
</script>   
  <?php while ( trs_activities() ) : trs_the_activity(); ?>

    <?php locate_template( array( 'activity/entry.php' ), true, false ); ?>

  <?php endwhile; ?>


  <?php if ( empty( $_POST['page'] ) ) : ?>

    </ul>

  <?php endif; ?>

<?php else : ?>

  <div id="message" class="info">
    <p><?php _e( 'Sorry, there was no activity found. Please try a different filter.', 'trendr' ); ?></p>
  </div>

<?php endif; ?>

  <?php if ( trs_activity_has_more_items() ) : ?>

    <div class="infinite" data-turbolinks="false">
      <a data-turbolinks="false" href="#more"><?php _e( 'Infinite load', 'trendr' ); ?></a>
    </div>
<script>

  $('div.dropdown').each(function() {
    var $dropdown = $(this);

    $("a.dropdown-link", $dropdown).click(function(e) {
      e.preventDefault();
      $div = $("div.confirm", $dropdown);
      $div.toggle();
      $("div.confirm").not($div).hide();
      return false;
    });

});

  $('html').click(function(){
    $("div.confirm").hide();              

  });
     

</script>
  <?php endif; ?>
<?php do_action( 'trs_after_activity_loop' ); ?>

<form action="" name="publish-spiral" id="publish-spiral" method="post">

  <?php trm_nonce_field( 'activity_filter', '_key_activity_filter' ); ?>

</form>