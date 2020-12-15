<?php

/**
 * trendr - Activity Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - trs_dtheme_object_filter()
 *
 * @package trendr
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




jQuery(document).ready(function(){
     // $(".dim").show();

    $('.frame').find('html').hide();

    $('a').attr('target','blah');
    //$('a').setAttribute('target','blah');
    //$("html").fadeIn(2000);
    //$('a[href^="http://"]').not('a[href*=gusdecool]').attr('target','blah');

    $('html #frame').contents().find('#nav2').hide();
  // $("iframe#blah ").css ('height','0%');

     $('html #blah').contents().find('#nav2').hide();




   $('li#f1').addClass('active');

          

      $("#l-b").hide();
  
     // $(".standard-form").hide();


//////////////////////////////////////////////
///NAV2 Function
$('html').on(' click touchstart  scroll   touchmove touchend touchcancel touchleave touch','li#f0',function(){
                                    $(".trs-img embed").show();
      $(".dim").show();
     $("html #frame").contents().find(".dim").show();

$('.active').removeClass('active');
$(this).addClass('active');

  });





$('html').on(' click touchstart  scroll   touchmove touchend touchcancel touchleave touch','li#f1',function(){
                                    $(".trs-img embed").show();
    //  $(".dim").hide();
  //   $("html #frame").contents().find(".dim").hide();


      $("#l-b").hide();
      $("#l-c iframe").hide();
           $("#l-d iframe").hide();
      $("li#p-nav").hide();
    $("#inbox").hide();
    $("#info").hide();
    $("#profile").hide();

     $("#l-a .activity").show();
      $("#l-a").show();

        $(".site-content").show();

        $("#contour-image").show();

       $('#portrait-upload').hide();

      $("#l-b").hide();

$('.active').removeClass('active');
$(this).addClass('active');

  });

////f1.active



          $('html').on('   touchstart','#f1.active',function(){
        $("#f1").removeClass('openblah');

         $("iframe#blah ").hide ();
        $(".site-content").show();
         $("#l-a .activity").show();
          $("#l-a").show();

            });

 //Add class opneblah on a click          
$('#l-a ').on('click','a img.portrait',function(){
        $("#f1").addClass('openblah');

        $(".site-content").hide();

  });

// Keeping of #blah on nav2 navigation

$('html').on('click touchstart  scroll   touchmove touchend touchcancel touchleave touch','#f1.openblah',function(){
$(".trs-img embed").show();

        $(".site-content").hide();
        $("iframe#blah ").show ();
                $("html #frame").contents().find('#l-a').show();
                              // $("iframe#blah ").css ('height','100%');


  });


////f1  -- line sensetive
// removal of #blah on nav2 navigation

          $('html').on('   touchstart','#f1.active.openblah',function(){$(".trs-img embed").show();

        $("iframe#blah ").hide ();

       // $("iframe#blah ").css ('height','0%');
        $(".site-content").show();

            });



$('html').on('click  touchstart  scroll   touchmove touchend touchcancel touchleave touch','li#f2',function(){
                                    $(".trs-img embed").show();
     $("html #frame").contents().find(".dim").hide();

        $(".site-content").show();


    $("#l-b").show();

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
        //$("iframe#blah ").css ('height','100%');

  });



$('html').on('click  touchstart  scroll   touchmove touchend touchcancel touchleave touch','li#f3',function(){
    //  $(".dim").hide();
    $('.frame').contents().find('.trs-img embed').show();

     $("html #frame").contents().find(".dim").hide();

$('.site-content ').hide();

      $("#l-a").hide();
      $("#l-b").hide();
      $("#l-d iframe").hide();
      /// $("iframe#blah ").hide();
      $("#contour-image").show();
      $("li#p-nav").hide();

        $("iframe#blah ").hide();

      $("#l-c .frame").show();
      
     $("html #frame").contents().find('#l-a').show();


       $(" html #frame").contents().find('#portrait-upload').hide();

$('.active').removeClass('active');

$(this).addClass('active');
  });
          

////f3.active

          $('html').on('  touchstart','#f3.active',function(){
                $('.frame').contents().find('.trs-img embed').show();


        $("#f3").removeClass('openblah');

               $("#l-c iframe#frame").contents().find('iframe#blah').hide();
           //    $("iframe#blah ").css ('height','0%');
       
           });

 ////f3  add .openblah to a div 
         
    $('#l-c .frame').load(function(){

        var iframe = $('#l-c .frame').contents();

        iframe.find("a img.portrait").click(function(){
        $("#f3").addClass('openblah');
        });
});

// Keeping of #blah on nav2 navigation

$('html').on('click  touchstart  scroll   touchmove touchend touchcancel touchleave touch','#f3.openblah',function(){
    $('.frame').contents().find('.trs-img embed').show();

                $(".frame  ").contents().find('.site-content').hide();
               // $(".frame html ").contents().find('.container').hide();

                $("html #frame").contents().find('#l-a').hide();
                      //  $("iframe#blah ").css ('height','100%');


            $('.site-content ').hide();

  });


////f3  -- line sensetive
// removal of #blah on nav2 navigation
          $('html').on('  touchstart','#f3.active.openblah',function(){
                $('.frame').contents().find('.trs-img embed').show();


                $(".frame  ").contents().find('.site-content').show();
                $("html #frame").contents().find('#l-a').show();


            });





$('html').on('click  touchstart scroll touchmove touchend touchcancel touchleave touch','li#f4',function(){
      $('.frame').contents().find('.trs-img embed').show();


    //  $(".dim").hide();
     $("html #frame").contents().find(".dim").hide();
      $("#l-a").hide();
      $("#l-b").hide();
      $("#l-c iframe").hide();
      /// $("iframe#blah ").hide();
      $("#contour-image").show();
      $("li#p-nav").hide();

        $("iframe#blah ").hide();

      $("#l-d .frame").show();

       // $(".site-content").addClass('selected');
     $("html #frame").contents().find('#l-a').show();

      $(".site-content").hide();

                $(" html #frame").contents().find('#portrait-upload').show();
              $('html #frame').contents().find('.dimension .profile').hide();
                $(" html #frame").contents().find('.dimension-inn').show();
         //  $("#nav2 div").css('padding','1px');
     $("li#p1").show();       
$('.active').removeClass('active');
$(this).addClass('active');

  });

////f4.active

          $('html').on(' touchstart ','#f4.active',function(){
    $('.frame').contents().find('.trs-img embed').show();

               $("#l-d iframe#frame").contents().find('iframe#blah').hide();
              $("#f4").removeClass('openblah');
               //$("iframe#blah ").css ('height','0%');

            });

 ////f3  add .openblah to a div 
         
    $('#l-d iframe#frame').load(function(){

        var iframe = $('#l-d iframe#frame').contents();

        iframe.find("a img.portrait").click(function(){
        $("#f4").addClass('openblah');
        });
});

// Keeping of #blah on nav2 navigation

$('html').on(' touchstart  scroll   touchmove touchend touchcancel touchleave touch','#f4.openblah',function(){
    $('.frame').contents().find('.trs-img embed').show();

                $(".frame  ").contents().find('.site-content').hide();
               // $(".frame html ").contents().find('.container').hide();

                $("html #frame").contents().find('#l-a').hide();
                      //  $("iframe#blah ").css ('height','100%');

$('.site-content ').hide();


  });


////f3  -- line sensetive
// removal of #blah on nav2 navigation
          $('html').on('  touchstart','#f4.active.openblah',function(){
                $(".frame  ").contents().find('.site-content').show();
                $("html #frame").contents().find('#l-a').show();
    $('.frame').contents().find('.trs-img embed').show();


            });




$('html ').on('click ','a img.portrait',function(){
        $("iframe#blah ").show();
   //$(".site-content").addClass('selected');

       // $("iframe#blah ").css ('height','100%');


  });







$('html ').on('click','.post a ',function(){
      //  $("iframe#blah ").show();

  });

})


$('html').load(function(){
 // $("body").fadeIn(2000);
  //  $('.frame').contents().find('#nav2').hide();
   // $('.frame').contents().find('#nav2').hide();



});



      var fire = {
        init:function(){


       //     $('*').on('click touchstart  touchmove touchend touchcancel touchleave touch', function(){
           // });

            $('html').on('click touchstart  scroll   touchmove touchend touchcancel touchleave touch', function(){

    $('a').attr('target','blah');

               // $(' iframe#frame html  iframe#blah ').contents().find('html').hide();
               //$("iframe#blah ").hide();


               //hides navigation for #frame
              $('html #frame').contents().find('#nav2').hide();


               
               //returns vback the hidden site-content for #frame
                $(" html #frame").contents().find('.site-content').show();
               

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



$('#blah').load(function(){
 //  $("html").fadeIn(2000);
    $('#blah').contents().find('#nav2').hide();
    //$('#blah').contents().find('#nav2').hide();
//$("#blah").contents().find(".trs-img embed").show();
    $('#blah').contents().find('a').attr('target','blah');
$('a').attr('target','blah');
        $("iframe#blah ").show();
$("#blah").contents().find(".trs-img embed").show();

});






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