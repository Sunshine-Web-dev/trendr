			
        <img id="vid" autoplay="0"  src="https://jooinn.com/images/beautiful-sky-29.jpg" data-video="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img));?>?autoplay=0 " onerror="this.style.display='none'">




	         <video  preload="metadata" id ="video" poster="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?>#t=1"><source width="100%"   src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?> " playsinline controls></video>


      <video  preload="none" id ="video" poster="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?>#t=1"><source width="100%"   src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?> " playsinline controls></video>


    <video width="320" height="240" controls playsinline webkit-playsinline autoplay><source  src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?>#t=1"  ></video>

        
        <img id="vid" autoplay="0"  src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img));?>#t=1?autoplay=0" data-video="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img));?>?autoplay=0 " onerror="this.style.display='none'">

        ///////////////////////
		  <video  preload="none" id ="video" poster="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?>#t=1"><source width="100%" height="240"  src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?> "  ></video>
		  	
<video  id ="video" preload="metadata">
  <source src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?>#t=0.1" type="video/mp4">
</video>

	
     <video width="320" height="240" controls><source  src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img)); ?>"  ></video>
	
    <embed  style="display: none"  width="100%" height="240" src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img));?>#t=1" onerror="this.style.display='none'"/>


<object style="display: none" data="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img));?>" width="400" height="300"">



				<img name="data-video" id="data-video" class="data-video" data-video="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img));?>" style="display: "  width="100%" height="240" src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img));?>#t=1" />

			<img name="data-video" id="data-video" class="data-video" data-video="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img));?>#t=10" style="display: "  width="100%" height="240" src="<?php echo esc_url(med_get_image_url($activity_blog_id) . trim($img));?>#t=10" />


///////////////////

$('.trs-img img').on('click touchstart  scroll   touchmove touchend touchcancel touchleave touch', function(){
varvideo = '<embed src="'+ $(this).attr('data-video') +'"></embed>';
$(this).replaceWith(video); });




///////////////

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

///////////

    jQuery(document).ready(function(a){var b=/\[trs-vid\]https?:\/\/(?:www\.)?youtu(?:be\.com|\.be)\/(?:watch\?v=|v\/)?([A-Za-z0-9_\-]+)([a-zA-Z&=;_+0-9*#\-]*?)\[\/trs-vid\]/;
        var c='<div data-address="$1" class="youtube" style="background: url(https://i4.ytimg.com/vi/$1/hqdefault.jpg)"><span></span></div>';
        var d='<iframe data-address="$1" class="youtube" src="https://www.youtube.com/embed/$1?enablejsapi=1&hd=1&autohide=1&autoplay=0" frameborder="0" allowfullscreen></iframe>';
        a(".broadcast-inn").each(function(){var d=a(this);d.html(d.html().replace(b,c))});a(".broadcast-inn").delegate("div.youtube","click",function(){var b=a(this);b.replaceWith(d.replace(/\$1/g,b.attr("data-address")))})})  
////

$('html ').on('click ','#video.selected',function(){
    $(this).removeClass('selected');
   $('#video').removeClass('selected');

  
    });

$(document).on("click", "img", function(){
  video = '<embed src="'+ $(this).attr('data-video') +'" showcontrols="0" showcontrols="false"  autoplay="1"></embed>';
        $(this).replaceWith(video);

 });